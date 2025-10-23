<?php

namespace App\Services\PettyCash;

use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class PettyCashService
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {
    }

    /**
     * Record a new charge into the ledger.
     */
    public function recordCharge(PettyCashLedger $ledger, array $payload, User $user): PettyCashTransaction
    {
        $amount = (float) ($payload['amount'] ?? 0);
        $this->assertAmountWithinLimits($ledger, $amount, PettyCashTransaction::TYPE_CHARGE);

        return $this->persistTransaction($ledger, [
            'type' => PettyCashTransaction::TYPE_CHARGE,
            'status' => PettyCashTransaction::STATUS_APPROVED,
            'requested_by' => $user->id,
            'approved_by' => $user->id,
            'approved_at' => Carbon::now(),
        ] + $payload);
    }

    /**
     * Record a new expense in draft/submitted state.
     */
    public function recordExpense(PettyCashLedger $ledger, array $payload, User $user): PettyCashTransaction
    {
        $amount = (float) ($payload['amount'] ?? 0);
        $this->assertAmountWithinLimits($ledger, $amount, $payload['type'] ?? PettyCashTransaction::TYPE_EXPENSE);

        return $this->persistTransaction($ledger, [
            'type' => PettyCashTransaction::TYPE_EXPENSE,
            'status' => $payload['status'] ?? PettyCashTransaction::STATUS_SUBMITTED,
            'requested_by' => $user->id,
        ] + $payload);
    }

    public function approveTransaction(PettyCashTransaction $transaction, User $approver): PettyCashTransaction
    {
        if ($transaction->status === PettyCashTransaction::STATUS_APPROVED) {
            return $transaction;
        }

        if ($transaction->status === PettyCashTransaction::STATUS_REJECTED) {
            throw ValidationException::withMessages([
                'status' => __('The transaction has already been rejected.'),
            ]);
        }

        $transaction->status = PettyCashTransaction::STATUS_APPROVED;
        $transaction->approved_by = $approver->id;
        $transaction->approved_at = Carbon::now();
        $transaction->meta = array_filter(array_merge($transaction->meta ?? [], [
            'revision_requested_by' => null,
            'revision_requested_at' => null,
            'revision_note' => null,
            'suspicious' => false,
            'suspicious_marked_by' => null,
            'suspicious_marked_at' => null,
        ]), fn ($value) => $value !== null);

        return $this->persistAndUpdateLedger($transaction);
    }

    public function rejectTransaction(PettyCashTransaction $transaction, User $rejector, ?string $reason = null): PettyCashTransaction
    {
        if ($transaction->status === PettyCashTransaction::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'status' => __('The transaction is already approved and cannot be rejected.'),
            ]);
        }

        $transaction->status = PettyCashTransaction::STATUS_REJECTED;
        $transaction->rejected_by = $rejector->id;
        $transaction->rejected_at = Carbon::now();
        $transaction->meta = array_filter(array_merge($transaction->meta ?? [], [
            'rejection_reason' => $reason,
            'revision_requested_by' => null,
            'revision_requested_at' => null,
            'revision_note' => null,
        ]), fn ($value) => $value !== null);

        $transaction->save();

        return $transaction;
    }

    public function sendBackForRevision(PettyCashTransaction $transaction, User $manager, ?string $note = null): PettyCashTransaction
    {
        if ($transaction->status === PettyCashTransaction::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'status' => __('The transaction is already approved and cannot be sent for revision.'),
            ]);
        }

        $transaction->status = PettyCashTransaction::STATUS_NEEDS_CHANGES;
        $transaction->approved_by = null;
        $transaction->approved_at = null;
        $transaction->meta = array_merge($transaction->meta ?? [], [
            'revision_requested_by' => $manager->id,
            'revision_requested_at' => Carbon::now()->toISOString(),
            'revision_note' => $note,
        ]);

        $transaction->save();

        return $transaction;
    }

    public function markSuspicious(PettyCashTransaction $transaction, User $manager, ?string $note = null): PettyCashTransaction
    {
        if ($transaction->status === PettyCashTransaction::STATUS_APPROVED) {
            throw ValidationException::withMessages([
                'status' => __('Approved transactions cannot be flagged as suspicious.'),
            ]);
        }

        $transaction->status = PettyCashTransaction::STATUS_UNDER_REVIEW;
        $transaction->meta = array_merge($transaction->meta ?? [], [
            'suspicious' => true,
            'suspicious_marked_by' => $manager->id,
            'suspicious_marked_at' => Carbon::now()->toISOString(),
            'suspicious_note' => $note,
        ]);

        $transaction->save();

        return $transaction;
    }

    public function recalcLedger(PettyCashLedger $ledger): void
    {
        $approved = $ledger->approvedTransactions()
            ->selectRaw("
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_charges,
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_expenses,
                SUM(CASE WHEN type = ? THEN amount ELSE 0 END) as total_adjustments
            ", [
                PettyCashTransaction::TYPE_CHARGE,
                PettyCashTransaction::TYPE_EXPENSE,
                PettyCashTransaction::TYPE_ADJUSTMENT,
            ])
            ->first();

        $charges = (float) ($approved->total_charges ?? 0);
        $expenses = (float) ($approved->total_expenses ?? 0);
        $adjustments = (float) ($approved->total_adjustments ?? 0);

        // Calculate new balance: opening_balance + charges + adjustments - expenses
        $newBalance = $ledger->opening_balance + $charges + $adjustments - $expenses;

        // Update the ledger balance
        $ledger->current_balance = $newBalance;
        $ledger->save();

        // Log the recalculation for debugging
        \Log::info("PettyCash Ledger {$ledger->id} balance recalculated: " .
                  "Opening: {$ledger->opening_balance}, " .
                  "Charges: {$charges}, " .
                  "Adjustments: {$adjustments}, " .
                  "Expenses: {$expenses}, " .
                  "New Balance: {$newBalance}");
    }

    protected function persistTransaction(PettyCashLedger $ledger, array $attributes): PettyCashTransaction
    {
        return $this->db->transaction(function () use ($ledger, $attributes) {
            /** @var PettyCashTransaction $transaction */
            $transaction = $ledger->transactions()->create($attributes);

            if ($transaction->status === PettyCashTransaction::STATUS_APPROVED) {
                $this->recalcLedger($ledger->fresh());
            }

            return $transaction;
        });
    }

    protected function persistAndUpdateLedger(PettyCashTransaction $transaction): PettyCashTransaction
    {
        return $this->db->transaction(function () use ($transaction) {
            $transaction->save();
            $ledger = $transaction->ledger()->lockForUpdate()->first();
            $this->recalcLedger($ledger);

            if ($transaction->type === PettyCashTransaction::TYPE_CHARGE) {
                $ledger->last_charge_at = Carbon::now();
                $ledger->save();
            }

            return $transaction;
        });
    }

    public function deleteTransaction(PettyCashTransaction $transaction): void
    {
        $this->db->transaction(function () use ($transaction) {
            $ledger = $transaction->ledger()->lockForUpdate()->first();
            $transaction->delete();

            if ($ledger) {
                $this->recalcLedger($ledger);
            }
        });
    }

    public function getChargeUsageTimeline(PettyCashLedger $ledger, int $limit = 10): array
    {
        $charges = $ledger->transactions()
            ->where('status', PettyCashTransaction::STATUS_APPROVED)
            ->where('type', PettyCashTransaction::TYPE_CHARGE)
            ->orderBy('transaction_date', 'asc')
            ->get();

        if ($charges->isEmpty()) {
            return [];
        }

        $expenses = $ledger->transactions()
            ->where('status', PettyCashTransaction::STATUS_APPROVED)
            ->where('type', PettyCashTransaction::TYPE_EXPENSE)
            ->orderBy('transaction_date', 'asc')
            ->get();

        $adjustments = $ledger->transactions()
            ->where('status', PettyCashTransaction::STATUS_APPROVED)
            ->where('type', PettyCashTransaction::TYPE_ADJUSTMENT)
            ->orderBy('transaction_date', 'asc')
            ->get();

        $timeline = [];

        $expenseIndex = 0;
        $adjustmentIndex = 0;
        $expenseCount = $expenses->count();
        $adjustmentCount = $adjustments->count();

        $charges = $charges->values();

        foreach ($charges as $index => $charge) {
            $rangeStart = $charge->transaction_date;
            $nextCharge = $charges->get($index + 1);
            $rangeEnd = $nextCharge ? $nextCharge->transaction_date : null;

            $chargeExpenses = collect();
            while ($expenseIndex < $expenseCount) {
                $expense = $expenses->get($expenseIndex);
                if ($expense->transaction_date < $rangeStart) {
                    $expenseIndex++;
                    continue;
                }
                if ($rangeEnd && $expense->transaction_date >= $rangeEnd) {
                    break;
                }

                $chargeExpenses = $chargeExpenses->push($expense);
                $expenseIndex++;
            }

            $chargeAdjustments = collect();
            while ($adjustmentIndex < $adjustmentCount) {
                $adjustment = $adjustments->get($adjustmentIndex);
                if ($adjustment->transaction_date < $rangeStart) {
                    $adjustmentIndex++;
                    continue;
                }
                if ($rangeEnd && $adjustment->transaction_date >= $rangeEnd) {
                    break;
                }

                $chargeAdjustments = $chargeAdjustments->push($adjustment);
                $adjustmentIndex++;
            }

            $adjustmentsTotal = (float) $chargeAdjustments->sum('amount');
            $expensesTotal = (float) $chargeExpenses->sum('amount');

            $timeline[] = [
                'charge' => $charge,
                'next_charge_at' => $rangeEnd,
                'expenses' => $chargeExpenses,
                'expenses_total' => $expensesTotal,
                'adjustments' => $chargeAdjustments,
                'adjustments_total' => $adjustmentsTotal,
                'remaining_after_expenses' => ($charge->amount + $adjustmentsTotal) - $expensesTotal,
            ];
        }

        $timeline = collect($timeline)->sortByDesc(fn ($entry) => $entry['charge']->transaction_date)->values();

        if ($limit > 0) {
            $timeline = $timeline->take($limit)->values();
        }

        return $timeline->all();
    }

    public function getLedgerSnapshot(PettyCashLedger $ledger): array
    {
        $pendingSums = $ledger->transactions()
            ->selectRaw('type, SUM(amount) as total')
            ->where('status', '!=', PettyCashTransaction::STATUS_APPROVED)
            ->groupBy('type')
            ->pluck('total', 'type');

        $approvedSums = $ledger->transactions()
            ->selectRaw('type, SUM(amount) as total')
            ->where('status', PettyCashTransaction::STATUS_APPROVED)
            ->groupBy('type')
            ->pluck('total', 'type');

        $pendingExpenses = (float) ($pendingSums[PettyCashTransaction::TYPE_EXPENSE] ?? 0);
        $pendingCharges = (float) ($pendingSums[PettyCashTransaction::TYPE_CHARGE] ?? 0);
        $pendingAdjustments = (float) ($pendingSums[PettyCashTransaction::TYPE_ADJUSTMENT] ?? 0);

        $approvedExpenses = (float) ($approvedSums[PettyCashTransaction::TYPE_EXPENSE] ?? 0);
        $approvedCharges = (float) ($approvedSums[PettyCashTransaction::TYPE_CHARGE] ?? 0);
        $approvedAdjustments = (float) ($approvedSums[PettyCashTransaction::TYPE_ADJUSTMENT] ?? 0);

        $pendingTransactionsCount = $ledger->transactions()
            ->where('status', '!=', PettyCashTransaction::STATUS_APPROVED)
            ->count();

        $pendingDelta = $pendingCharges + $pendingAdjustments - $pendingExpenses;
        $pendingBalance = $ledger->current_balance + $pendingDelta;

        $lastTransactionAt = $ledger->transactions()->max('transaction_date');

        return [
            'pending_expenses_total' => $pendingExpenses,
            'pending_charges_total' => $pendingCharges,
            'pending_adjustments_total' => $pendingAdjustments,
            'pending_transactions_count' => $pendingTransactionsCount,
            'pending_balance_delta' => $pendingDelta,
            'pending_balance' => $pendingBalance,
            'approved_expenses_total' => $approvedExpenses,
            'approved_charges_total' => $approvedCharges,
            'approved_adjustments_total' => $approvedAdjustments,
            'last_transaction_at' => $lastTransactionAt ? Carbon::parse($lastTransactionAt) : null,
        ];
    }

    protected function assertAmountWithinLimits(PettyCashLedger $ledger, float $amount, string $type): void
    {
        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' => __('مبلغ باید بیشتر از صفر باشد.'),
            ]);
        }

        if ($ledger->max_transaction_amount > 0 && $amount > (float) $ledger->max_transaction_amount) {
            throw ValidationException::withMessages([
                'amount' => __('مبلغ این تراکنش نمی‌تواند از سقف تعیین‌شده (:limit ریال) بیشتر باشد.', ['limit' => number_format($ledger->max_transaction_amount)]),
            ]);
        }

        if ($type === PettyCashTransaction::TYPE_CHARGE && $ledger->max_charge_request_amount > 0 && $amount > (float) $ledger->max_charge_request_amount) {
            throw ValidationException::withMessages([
                'amount' => __('مبلغ درخواست شارژ شما بیشتر از سقف مجاز (:limit ریال) است.', ['limit' => number_format($ledger->max_charge_request_amount)]),
            ]);
        }
    }
}
