<?php

namespace App\Console\Commands;

use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PettyCashArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'petty-cash:archive {--period=daily : Archive period (daily, 3days, weekly)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Archive old petty cash transactions and carry forward balances';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $period = $this->option('period');

        switch ($period) {
            case 'daily':
                $this->archiveDaily();
                break;
            case '3days':
                $this->archive3Days();
                break;
            case 'weekly':
                $this->archiveWeekly();
                break;
            default:
                $this->error('Invalid period. Use: daily, 3days, or weekly');
                return 1;
        }

        $this->info("Petty cash archive completed for period: {$period}");
        return 0;
    }

    private function archiveDaily()
    {
        $yesterday = Carbon::yesterday();
        $this->archiveTransactions($yesterday, $yesterday, 'daily');
    }

    private function archive3Days()
    {
        $threeDaysAgo = Carbon::today()->subDays(3);
        $yesterday = Carbon::yesterday();
        $this->archiveTransactions($threeDaysAgo, $yesterday, '3days');
    }

    private function archiveWeekly()
    {
        $weekAgo = Carbon::today()->subWeek();
        $yesterday = Carbon::yesterday();
        $this->archiveTransactions($weekAgo, $yesterday, 'weekly');
    }

    private function archiveTransactions(Carbon $fromDate, Carbon $toDate, string $period)
    {
        $this->info("Archiving transactions from {$fromDate->format('Y-m-d')} to {$toDate->format('Y-m-d')}");

        // Get all active ledgers
        $ledgers = PettyCashLedger::where('is_active', true)->get();

        foreach ($ledgers as $ledger) {
            $this->processLedgerArchive($ledger, $fromDate, $toDate, $period);
        }
    }

    private function processLedgerArchive(PettyCashLedger $ledger, Carbon $fromDate, Carbon $toDate, string $period)
    {
        // Get approved transactions for the period
        $transactions = $ledger->transactions()
            ->where('status', PettyCashTransaction::STATUS_APPROVED)
            ->whereDate('transaction_date', '>=', $fromDate)
            ->whereDate('transaction_date', '<=', $toDate)
            ->get();

        if ($transactions->isEmpty()) {
            $this->line("No transactions to archive for ledger: {$ledger->branch_name}");
            return;
        }

        // Calculate total charges and expenses for the period
        $totalCharges = $transactions->where('type', PettyCashTransaction::TYPE_CHARGE)->sum('amount');
        $totalExpenses = $transactions->where('type', PettyCashTransaction::TYPE_EXPENSE)->sum('amount');
        $totalAdjustments = $transactions->where('type', PettyCashTransaction::TYPE_ADJUSTMENT)->sum('amount');

        $netAmount = $totalCharges + $totalAdjustments - $totalExpenses;

        if ($netAmount == 0) {
            $this->line("No net amount to carry forward for ledger: {$ledger->branch_name}");
            return;
        }

        DB::transaction(function () use ($ledger, $transactions, $netAmount, $period, $fromDate, $toDate) {
            // Create a carry-over transaction for the next day
            $carryOverTransaction = new PettyCashTransaction([
                'ledger_id' => $ledger->id,
                'type' => PettyCashTransaction::TYPE_ADJUSTMENT,
                'status' => PettyCashTransaction::STATUS_APPROVED,
                'amount' => $netAmount,
                'currency' => 'IRR',
                'transaction_date' => Carbon::today(),
                'description' => "باقی‌مانده بایگانی شده ({$period}) از {$fromDate->format('Y-m-d')} تا {$toDate->format('Y-m-d')}",
                'reference_number' => "ARCHIVE-{$period}-" . time(),
                'approved_by' => null, // System generated
                'approved_at' => now(),
                'meta' => [
                    'archive_period' => $period,
                    'archive_from_date' => $fromDate->format('Y-m-d'),
                    'archive_to_date' => $toDate->format('Y-m-d'),
                    'original_transaction_ids' => $transactions->pluck('id')->toArray(),
                    'system_generated' => true,
                ]
            ]);

            $carryOverTransaction->save();

            // Update ledger's last reconciled date
            $ledger->last_reconciled_at = now();
            $ledger->save();

            $this->line("Archived {$transactions->count()} transactions for ledger {$ledger->branch_name} with net amount: {$netAmount}");
        });
    }
}
