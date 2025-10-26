<?php

namespace App\Services\PettyCash;

use App\Models\PettyCashCycle;
use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\DatabaseManager;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class PettyCashArchiveService
{
    public function __construct(
        private readonly DatabaseManager $db,
    ) {
    }

    public function finalizeCycle(PettyCashCycle $cycle, PettyCashLedger $ledger, User $approver, ?string $adminNote = null): PettyCashCycle
    {
        $cycle->loadMissing('ledger');

        /** @var PettyCashCycle $updatedCycle */
        $updatedCycle = $this->db->transaction(function () use ($cycle, $ledger, $approver, $adminNote) {
            $closedAt = Carbon::now();

            $pendingExists = $ledger->transactions()
                ->whereNull('archive_cycle_id')
                ->where('status', '!=', PettyCashTransaction::STATUS_APPROVED)
                ->exists();

            if ($pendingExists) {
                throw new \RuntimeException('Cannot finalize cycle while pending transactions exist.');
            }

            $approvedTransactions = $ledger->transactions()
                ->whereNull('archive_cycle_id')
                ->where('status', PettyCashTransaction::STATUS_APPROVED)
                ->whereDate('transaction_date', '>=', $cycle->opened_at?->startOfDay() ?? $ledger->created_at ?? $closedAt->clone()->startOfDay())
                ->whereDate('transaction_date', '<=', $closedAt->clone()->endOfDay())
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get();

            $totals = $this->calculateTotals($approvedTransactions);
            $summary = $this->buildSummary($cycle, $ledger, $approver, $adminNote, $totals, $approvedTransactions, $closedAt);

            $cycle->forceFill([
                'status' => 'closed',
                'closed_by' => $approver->id,
                'closed_at' => $closedAt,
                'closing_balance' => $ledger->current_balance,
                'closing_note' => $adminNote,
                'transactions_count' => $totals['transactions_count'],
                'expenses_count' => $totals['expenses_count'],
                'total_charges' => $totals['total_charges'],
                'total_expenses' => $totals['total_expenses'],
                'total_adjustments' => $totals['total_adjustments'],
                'summary' => $summary,
                'report_path' => $this->generateExcelReport($ledger, $cycle, $approvedTransactions, $summary),
            ])->save();

            if ($approvedTransactions->isNotEmpty()) {
                PettyCashTransaction::whereIn('id', $approvedTransactions->pluck('id'))
                    ->update([
                        'archive_cycle_id' => $cycle->id,
                        'archived_at' => $closedAt,
                    ]);
            }

            $ledger->forceFill([
                'opening_balance' => $ledger->current_balance,
                'last_reconciled_at' => $closedAt,
            ])->save();

            $ledger->cycles()->create([
                'status' => 'open',
                'opened_at' => $closedAt,
                'opening_balance' => $ledger->current_balance,
            ]);

            return $cycle->fresh(['archivedTransactions', 'ledger']);
        });

        return $updatedCycle;
    }

    /**
     * @param  Collection<int, PettyCashTransaction>  $transactions
     * @return array<string, mixed>
     */
    protected function calculateTotals(Collection $transactions): array
    {
        $totalCharges = (float) $transactions->where('type', PettyCashTransaction::TYPE_CHARGE)->sum('amount');
        $totalExpenses = (float) $transactions->where('type', PettyCashTransaction::TYPE_EXPENSE)->sum('amount');
        $totalAdjustments = (float) $transactions->where('type', PettyCashTransaction::TYPE_ADJUSTMENT)->sum('amount');
        $expensesCount = $transactions->where('type', PettyCashTransaction::TYPE_EXPENSE)->count();

        return [
            'transactions_count' => $transactions->count(),
            'expenses_count' => $expensesCount,
            'total_charges' => $totalCharges,
            'total_expenses' => $totalExpenses,
            'total_adjustments' => $totalAdjustments,
        ];
    }

    /**
     * @param  Collection<int, PettyCashTransaction>  $transactions
     * @return array<string, mixed>
     */
    protected function buildSummary(
        PettyCashCycle $cycle,
        PettyCashLedger $ledger,
        User $approver,
        ?string $adminNote,
        array $totals,
        Collection $transactions,
        Carbon $closedAt,
        ?float $closingBalanceOverride = null
    ): array {
        $firstTransactionAt = $transactions->min('transaction_date');
        $lastTransactionAt = $transactions->max('transaction_date');

        return [
            'ledger' => [
                'id' => $ledger->id,
                'branch_name' => $ledger->branch_name,
            ],
            'cycle' => [
                'id' => $cycle->id,
                'opened_at' => optional($cycle->opened_at)->toISOString(),
                'closed_at' => $closedAt->toISOString(),
                'closing_note' => $adminNote,
            ],
            'transactions' => [
                'count' => $totals['transactions_count'],
                'expenses_count' => $totals['expenses_count'],
                'first_at' => $firstTransactionAt ? Carbon::parse($firstTransactionAt)->toISOString() : null,
                'last_at' => $lastTransactionAt ? Carbon::parse($lastTransactionAt)->toISOString() : null,
                'total_charges' => $totals['total_charges'],
                'total_expenses' => $totals['total_expenses'],
                'total_adjustments' => $totals['total_adjustments'],
            ],
            'balances' => [
                'opening' => (float) $cycle->opening_balance,
                'closing' => $closingBalanceOverride ?? (float) $ledger->current_balance,
            ],
            'approver' => [
                'id' => $approver->id,
                'name' => $approver->full_name ?? $approver->name ?? $approver->email,
            ],
        ];
    }

    /**
     * @param  Collection<int, PettyCashTransaction>  $transactions
     */
    protected function generateExcelReport(
        PettyCashLedger $ledger,
        PettyCashCycle $cycle,
        Collection $transactions,
        array $summary
    ): ?string {
        try {
            $directory = 'petty-cash/archives/ledger-' . $ledger->id;
            $filename = sprintf(
                'cycle-%d_%s.xls',
                $cycle->id,
                Str::of($summary['cycle']['closed_at'] ?? now()->toISOString())->replace([':', '-'], '')->replace('T', '_')->substr(0, 15)
            );

            $html = $this->buildExcelHtml($ledger, $cycle, $summary, $transactions);

            Storage::disk('local')->makeDirectory($directory);
            $relativePath = $directory . '/' . $filename;
            Storage::disk('local')->put($relativePath, $html);

            $this->adjustBackupOwnership(Storage::disk('local')->path($relativePath));

            return $relativePath;
        } catch (\Throwable $throwable) {
            Log::error('Failed to generate petty cash archive report', [
                'ledger_id' => $ledger->id,
                'cycle_id' => $cycle->id,
                'error' => $throwable->getMessage(),
            ]);

            return null;
        }
    }

    /**
     * @param  Collection<int, PettyCashTransaction>  $transactions
     */
    protected function buildExcelHtml(
        PettyCashLedger $ledger,
        PettyCashCycle $cycle,
        array $summary,
        Collection $transactions
    ): string {
        $headerRows = [
            ['گزارش تسویه تنخواه شعبه', $ledger->branch_name],
            ['شناسه فصل', $cycle->id],
            ['تاریخ شروع فصل', optional($cycle->opened_at)->format('Y-m-d H:i')],
            ['تاریخ پایان فصل', isset($summary['cycle']['closed_at']) ? Carbon::parse($summary['cycle']['closed_at'])->format('Y-m-d H:i') : null],
            ['تأیید کننده', $summary['approver']['name'] ?? null],
            ['تعداد کل تراکنش‌ها', $summary['transactions']['count'] ?? 0],
            ['تعداد فاکتور هزینه', $summary['transactions']['expenses_count'] ?? 0],
            ['جمع ورودی (شارژ)', number_format($summary['transactions']['total_charges'] ?? 0)],
            ['جمع خروجی (هزینه)', number_format($summary['transactions']['total_expenses'] ?? 0)],
            ['جمع تعدیلات', number_format($summary['transactions']['total_adjustments'] ?? 0)],
            ['مانده ابتدای فصل', number_format($summary['balances']['opening'] ?? 0)],
            ['مانده پایان فصل', number_format($summary['balances']['closing'] ?? 0)],
            ['توضیحات مدیر', $summary['cycle']['closing_note'] ?? ''],
        ];

        $detailRows = $transactions->map(function (PettyCashTransaction $transaction) {
            return [
                'ردیف' => $transaction->id,
                'تاریخ تراکنش' => optional($transaction->transaction_date)->format('Y-m-d H:i'),
                'نوع' => $transaction->type,
                'وضعیت' => $transaction->status,
                'مبلغ' => (float) $transaction->amount,
                'شرح' => $transaction->description,
                'شماره مرجع' => $transaction->reference_number,
                'ثبت توسط' => optional($transaction->requester)->full_name ?? $transaction->requested_by,
                'تأیید توسط' => optional($transaction->approver)->full_name ?? $transaction->approved_by,
            ];
        })->all();

        $escape = static fn (?string $value) => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta charset="utf-8"><style>table{border-collapse:collapse;}td,th{border:1px solid #999;padding:4px;font-family:tahoma,arial;font-size:12px;}</style></head><body>';

        $html .= '<table>';
        foreach ($headerRows as $row) {
            $html .= '<tr><th>' . $escape($row[0]) . '</th><td>' . $escape($row[1]) . '</td></tr>';
        }
        $html .= '</table><br>';

        $html .= '<table>';
        $html .= '<tr>';
        if (! empty($detailRows)) {
            foreach (array_keys($detailRows[0]) as $heading) {
                $html .= '<th>' . $escape($heading) . '</th>';
            }
            $html .= '</tr>';

            foreach ($detailRows as $row) {
                $html .= '<tr>';
                foreach ($row as $value) {
                    $html .= '<td>' . $escape(is_numeric($value) ? number_format((float) $value, 2) : $value) . '</td>';
                }
                $html .= '</tr>';
            }
        } else {
            $html .= '<th>' . $escape('تراکنشی یافت نشد') . '</th></tr><tr><td>' . $escape('هیچ تراکنش تایید شده‌ای برای این دوره ثبت نشده است.') . '</td></tr>';
        }
        $html .= '</table>';

        $html .= '</body></html>';

        return $html;
    }

    protected function adjustBackupOwnership(string $absolutePath): void
    {
        if (! config('petty-cash.backups.adjust_permissions', false)) {
            return;
        }

        if (! file_exists($absolutePath)) {
            return;
        }

        if (function_exists('posix_geteuid') && posix_geteuid() !== 0) {
            return;
        }

        $owner = config('petty-cash.backups.owner');
        $group = config('petty-cash.backups.group');

        if ($owner && function_exists('chown')) {
            @chown($absolutePath, $owner);
        }

        if ($group && function_exists('chgrp')) {
            @chgrp($absolutePath, $group);
        }
    }

    public function updateCycle(PettyCashCycle $cycle, array $attributes, User $editor): PettyCashCycle
    {
        $cycle->loadMissing('ledger');

        return $this->db->transaction(function () use ($cycle, $attributes, $editor) {
            $ledger = $cycle->ledger;
            $closingNote = $attributes['closing_note'] ?? $cycle->closing_note;
            $regenerateReport = (bool) ($attributes['regenerate_report'] ?? false);
            $approver = $cycle->closer ?? $editor;

            $transactions = $cycle->archivedTransactions()
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->get();

            $totals = $this->calculateTotals($transactions);
            $summary = $this->buildSummary(
                $cycle,
                $ledger,
                $approver,
                $closingNote,
                $totals,
                $transactions,
                $cycle->closed_at ?? Carbon::now(),
                $cycle->closing_balance ?? $ledger->current_balance
            );

            if (! empty($cycle->report_path) && $regenerateReport && Storage::disk('local')->exists($cycle->report_path)) {
                Storage::disk('local')->delete($cycle->report_path);
            }

            $reportPath = $cycle->report_path;
            if ($regenerateReport) {
                $reportPath = $this->generateExcelReport($ledger, $cycle, $transactions, $summary);
            }

            $cycle->forceFill([
                'closing_note' => $closingNote,
                'transactions_count' => $totals['transactions_count'],
                'expenses_count' => $totals['expenses_count'],
                'total_charges' => $totals['total_charges'],
                'total_expenses' => $totals['total_expenses'],
                'total_adjustments' => $totals['total_adjustments'],
                'summary' => $summary,
                'report_path' => $reportPath,
            ])->save();

            return $cycle->fresh(['ledger', 'closer', 'archivedTransactions']);
        });
    }

    public function deleteCycle(PettyCashCycle $cycle): void
    {
        $cycle->loadMissing('ledger');

        $this->db->transaction(function () use ($cycle) {
            if (! $cycle->closed_at) {
                throw new \RuntimeException('Only closed cycles can be deleted.');
            }

            $transactionIds = $cycle->archivedTransactions()->pluck('id');

            if ($transactionIds->isNotEmpty()) {
                PettyCashTransaction::whereIn('id', $transactionIds)
                    ->update([
                        'archive_cycle_id' => null,
                        'archived_at' => null,
                    ]);
            }

            if ($cycle->report_path && Storage::disk('local')->exists($cycle->report_path)) {
                Storage::disk('local')->delete($cycle->report_path);
            }

            $cycle->delete();
        });
    }
}
