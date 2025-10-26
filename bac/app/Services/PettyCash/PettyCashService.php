<?php

namespace App\Services\PettyCash;

use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use App\Models\User;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Carbon;
use Hekmatinasser\Verta\Verta;
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
        $amount = $this->normaliseAmount($payload['amount'] ?? 0);
        $payload['amount'] = $amount;
        $this->assertAmountWithinLimits($ledger, $amount, PettyCashTransaction::TYPE_CHARGE);

        // Check if user can approve charges (managers only)
        $canApprove = $user->hasRole(['Superadmin', 'Admin']) || $user->can('petty_cash.transaction.approve');
        
        // Temporary: Force all charges to require approval for testing
        $canApprove = false;
        
        $status = $canApprove ? PettyCashTransaction::STATUS_APPROVED : PettyCashTransaction::STATUS_SUBMITTED;
        $approvedBy = $canApprove ? $user->id : null;
        $approvedAt = $canApprove ? Carbon::now() : null;

        return $this->persistTransaction($ledger, [
            'type' => PettyCashTransaction::TYPE_CHARGE,
            'status' => $status,
            'requested_by' => $user->id,
            'approved_by' => $approvedBy,
            'approved_at' => $approvedAt,
        ] + $payload);
    }

    /**
     * Record a new expense in draft/submitted state.
     */
    public function recordExpense(PettyCashLedger $ledger, array $payload, User $user): PettyCashTransaction
    {
        $amount = $this->normaliseAmount($payload['amount'] ?? 0);
        $payload['amount'] = $amount;
        $this->assertAmountWithinLimits($ledger, $amount, $payload['type'] ?? PettyCashTransaction::TYPE_EXPENSE);

        return $this->persistTransaction($ledger, [
            'type' => PettyCashTransaction::TYPE_EXPENSE,
            'status' => $payload['status'] ?? PettyCashTransaction::STATUS_SUBMITTED,
            'requested_by' => $user->id,
        ] + $payload);
    }

    public function approveTransaction(PettyCashTransaction $transaction, User $approver, ?string $note = null): PettyCashTransaction
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
            'rejection_reason' => null,
            'approval_note' => $note,
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

        $note = trim((string) $reason);

        if ($note === '') {
            throw ValidationException::withMessages([
                'approval_note' => __('لطفاً دلیل رد تراکنش را وارد کنید.'),
            ]);
        }

        $transaction->status = PettyCashTransaction::STATUS_REJECTED;
        $transaction->rejected_by = $rejector->id;
        $transaction->rejected_at = Carbon::now();
        $meta = array_merge($transaction->meta ?? [], [
            'approval_note' => $note,
            'rejection_reason' => $note,
            'revision_requested_by' => null,
            'revision_requested_at' => null,
            'revision_note' => null,
        ]);
        $transaction->meta = array_filter($meta, fn ($value) => ! is_null($value) && $value !== '');

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
            ->whereNull('archive_cycle_id')
            ->orderBy('transaction_date', 'asc')
            ->get();

        if ($charges->isEmpty()) {
            return [];
        }

        $expenses = $ledger->transactions()
            ->where('status', PettyCashTransaction::STATUS_APPROVED)
            ->where('type', PettyCashTransaction::TYPE_EXPENSE)
            ->whereNull('archive_cycle_id')
            ->orderBy('transaction_date', 'asc')
            ->get();

        $adjustments = $ledger->transactions()
            ->where('status', PettyCashTransaction::STATUS_APPROVED)
            ->where('type', PettyCashTransaction::TYPE_ADJUSTMENT)
            ->whereNull('archive_cycle_id')
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
            ->whereNull('archive_cycle_id')
            ->groupBy('type')
            ->pluck('total', 'type');

        $approvedSums = $ledger->transactions()
            ->selectRaw('type, SUM(amount) as total')
            ->where('status', PettyCashTransaction::STATUS_APPROVED)
            ->whereNull('archive_cycle_id')
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
            ->whereNull('archive_cycle_id')
            ->count();

        $pendingDelta = $pendingCharges + $pendingAdjustments - $pendingExpenses;
        $pendingBalance = $ledger->current_balance + $pendingDelta;

        $lastTransactionAt = $ledger->transactions()
            ->whereNull('archive_cycle_id')
            ->max('transaction_date');

        $archivedCyclesCount = $ledger->archivedCycles()->count();
        $pendingChargeRequestsCount = $ledger->transactions()
            ->whereNull('archive_cycle_id')
            ->where('type', PettyCashTransaction::TYPE_CHARGE)
            ->whereIn('status', [
                PettyCashTransaction::STATUS_SUBMITTED,
                PettyCashTransaction::STATUS_UNDER_REVIEW,
                PettyCashTransaction::STATUS_NEEDS_CHANGES,
            ])
            ->count();
        $lastArchivedCycle = $ledger->archivedCycles()->with('closer:id,first_name,last_name')->first();

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
            'archived_cycles_count' => $archivedCyclesCount,
            'last_archived_cycle' => $lastArchivedCycle ? [
                'id' => $lastArchivedCycle->id,
                'closed_at' => optional($lastArchivedCycle->closed_at)->toISOString(),
                'transactions_count' => $lastArchivedCycle->transactions_count,
                'total_charges' => (float) $lastArchivedCycle->total_charges,
                'total_expenses' => (float) $lastArchivedCycle->total_expenses,
                'total_adjustments' => (float) $lastArchivedCycle->total_adjustments,
                'report_path' => $lastArchivedCycle->report_path,
                'closed_by' => [
                    'id' => $lastArchivedCycle->closer?->id,
                    'name' => $lastArchivedCycle->closer?->full_name ?? $lastArchivedCycle->closer?->name,
                ],
            ] : null,
            'pending_charge_requests_count' => $pendingChargeRequestsCount,
        ];
    }


    public function getLedgerAnalytics(PettyCashLedger $ledger, array $filters = []): array
    {
        $periodKey = $filters['period'] ?? 'last_30';
        $now = Carbon::now();
        $from = null;
        $to = null;
        $periodLabel = '';

        switch ($periodKey) {
            case 'today':
                $from = $now->copy()->startOfDay();
                $periodLabel = __('امروز');
                break;
            case 'last_7':
                $from = $now->copy()->subDays(6)->startOfDay();
                $periodLabel = __('۷ روز گذشته');
                break;
            case 'current_month':
                $from = $now->copy()->startOfMonth();
                $periodLabel = __('ماه جاری');
                break;
            case 'last_90':
                $from = $now->copy()->subDays(89)->startOfDay();
                $periodLabel = __('۹۰ روز گذشته');
                break;
            case 'last_180':
                $from = $now->copy()->subDays(179)->startOfDay();
                $periodLabel = __('۶ ماه گذشته');
                break;
            case 'current_year':
                $from = $now->copy()->startOfYear();
                $periodLabel = __('سال جاری');
                break;
            case 'custom':
                $periodLabel = __('بازه سفارشی');
                if (! empty($filters['from'])) {
                    try {
                        $from = Carbon::parse($filters['from'])->startOfDay();
                    } catch (\Throwable $exception) {
                        $from = null;
                    }
                }

                if (! empty($filters['to'])) {
                    try {
                        $to = Carbon::parse($filters['to'])->endOfDay();
                    } catch (\Throwable $exception) {
                        $to = null;
                    }
                }
                break;
            default:
                $periodKey = 'last_30';
                $from = $now->copy()->subDays(29)->startOfDay();
                $periodLabel = __('۳۰ روز گذشته');
                break;
        }

        if ($to === null) {
            $to = $now->copy()->endOfDay();
        }

        $baseQuery = $ledger->transactions()
            ->where('status', PettyCashTransaction::STATUS_APPROVED);

        if ($from) {
            $baseQuery->where('transaction_date', '>=', $from);
        }

        if ($to) {
            $baseQuery->where('transaction_date', '<=', $to);
        }

        $expenseQuery = (clone $baseQuery)->where('type', PettyCashTransaction::TYPE_EXPENSE);

        $overall = (clone $expenseQuery)
            ->selectRaw('SUM(amount) as total_amount, COUNT(*) as transaction_count, AVG(amount) as average_amount, MAX(amount) as max_amount, MIN(amount) as min_amount')
            ->first();

        $totalExpense = (float) ($overall->total_amount ?? 0);
        $transactionCount = (int) ($overall->transaction_count ?? 0);
        $averageAmount = $transactionCount > 0 ? (float) ($overall->average_amount ?? 0) : 0.0;
        $maxAmount = (float) ($overall->max_amount ?? 0);
        $minAmount = (float) ($overall->min_amount ?? 0);

        $categoriesMap = config('petty-cash.categories', []);

        $categoryBreakdown = (clone $expenseQuery)
            ->selectRaw('category as category_key, COUNT(*) as transactions_count, SUM(amount) as total_amount')
            ->whereNotNull('category')
            ->groupBy('category')
            ->orderByDesc('total_amount')
            ->get()
            ->map(function ($row) use ($categoriesMap, $totalExpense) {
                $total = (float) ($row->total_amount ?? 0);
                $count = (int) ($row->transactions_count ?? 0);

                return [
                    'key' => $row->category_key,
                    'label' => $categoriesMap[$row->category_key] ?? ($row->category_key ?? __('نامشخص')),
                    'total_amount' => $total,
                    'transactions_count' => $count,
                    'average_amount' => $count > 0 ? $total / $count : 0.0,
                    'share' => $totalExpense > 0 ? $total / $totalExpense : 0.0,
                ];
            })
            ->values();

        $vendorBreakdown = (clone $expenseQuery)
            ->selectRaw("COALESCE(NULLIF(JSON_UNQUOTE(JSON_EXTRACT(meta, '$.smart_invoice.vendor_name')), ''), 'نامشخص') as vendor_name")
            ->selectRaw('SUM(amount) as total_amount')
            ->selectRaw('COUNT(*) as transactions_count')
            ->groupBy('vendor_name')
            ->orderByDesc('total_amount')
            ->limit(10)
            ->get()
            ->map(function ($row) {
                return [
                    'vendor_name' => $row->vendor_name,
                    'total_amount' => (float) ($row->total_amount ?? 0),
                    'transactions_count' => (int) ($row->transactions_count ?? 0),
                ];
            })
            ->values();

        $dailyTrend = (clone $expenseQuery)
            ->selectRaw('DATE(transaction_date) as tx_date, SUM(amount) as total_amount, COUNT(*) as transactions_count')
            ->groupByRaw('DATE(transaction_date)')
            ->orderBy('tx_date')
            ->get()
            ->map(function ($row) {
                $date = Carbon::parse($row->tx_date);

                return [
                    'date' => $date->toDateString(),
                    'date_fa' => Verta::instance($date)->format('Y/m/d'),
                    'total_amount' => (float) ($row->total_amount ?? 0),
                    'transactions_count' => (int) ($row->transactions_count ?? 0),
                ];
            })
            ->values();

        $activeDays = max(1, $dailyTrend->count());
        $averagePerDay = $activeDays > 0 ? $totalExpense / $activeDays : 0.0;

        $recentTransactions = (clone $expenseQuery)
            ->select(['id', 'transaction_date', 'amount', 'category', 'description', 'meta', 'reference_number'])
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(6)
            ->get()
            ->map(function (PettyCashTransaction $transaction) use ($categoriesMap) {
                $vendor = data_get($transaction->meta, 'smart_invoice.vendor_name')
                    ?? data_get($transaction->meta, 'smart_invoice.vendor')
                    ?? data_get($transaction->meta, 'vendor_name');

                $date = $transaction->transaction_date ? Carbon::parse($transaction->transaction_date) : null;

                return [
                    'id' => $transaction->id,
                    'amount' => (float) $transaction->amount,
                    'category_key' => $transaction->category,
                    'category_label' => $categoriesMap[$transaction->category] ?? ($transaction->category ?? __('نامشخص')),
                    'description' => $transaction->description,
                    'vendor_name' => $vendor,
                    'reference_number' => $transaction->reference_number,
                    'date' => $date?->toDateTimeString(),
                    'date_fa' => $date ? Verta::instance($date)->format('Y/m/d H:i') : null,
                ];
            })
            ->values();

        return [
            'period' => [
                'key' => $periodKey,
                'label' => $periodLabel,
                'from' => $from?->toDateString(),
                'to' => $to?->toDateString(),
            ],
            'summary' => [
                'total_expense' => $totalExpense,
                'transaction_count' => $transactionCount,
                'average_amount' => $averageAmount,
                'average_per_day' => $averagePerDay,
                'max_transaction' => $maxAmount,
                'min_transaction' => $minAmount,
            ],
            'category_breakdown' => $categoryBreakdown->toArray(),
            'vendor_breakdown' => $vendorBreakdown->toArray(),
            'daily_trend' => $dailyTrend->toArray(),
            'recent_transactions' => $recentTransactions->toArray(),
        ];
    }

    protected function normaliseAmount(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/[^0-9.-]/u', '', (string) $value);

        if ($clean === '' || $clean === '-' || $clean === '.' || $clean === '-.' || $clean === '.-') {
            return 0.0;
        }

        return (float) $clean;
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
