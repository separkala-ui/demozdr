<?php

namespace App\Livewire\PettyCash;
use App\Models\PettyCashCycle;
use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class NotificationCenter extends Component
{
    public int $pendingTransactionsCount = 0;

    public int $pendingChargeRequestsCount = 0;

    public int $pendingArchivesCount = 0;

    /**
     * @var array<string, \Illuminate\Support\Collection<int, mixed>>
     */
    public array $items = [
        'transactions' => null,
        'charge_requests' => null,
        'archives' => null,
    ];

    protected $listeners = [
        'petty-cash-transaction-saved' => 'refreshCounts',
        'petty-cash-transaction-deleted' => 'refreshCounts',
        'petty-cash-transactions-refresh' => 'refreshCounts',
        'petty-cash-cycle-updated' => 'refreshCounts',
    ];

    public function mount(): void
    {
        $this->loadData();
    }

    public function refreshCounts(): void
    {
        $this->loadData();
    }

    public function render()
    {
        return view('livewire.petty-cash.notification-center');
    }

    protected function loadData(): void
    {
        $this->pendingTransactionsCount = 0;
        $this->pendingChargeRequestsCount = 0;
        $this->pendingArchivesCount = 0;

        $emptyCollection = collect();
        $this->items = [
            'transactions' => $emptyCollection,
            'charge_requests' => $emptyCollection,
            'archives' => $emptyCollection,
        ];

        $user = Auth::user();

        if (! $user) {
            return;
        }

        $ledgerIds = [];

        if ($user->hasRole(['Superadmin', 'Admin'])) {
            $ledgerIds = PettyCashLedger::pluck('id')->all();
        } elseif ($user->branch_id) {
            $ledgerIds = [(int) $user->branch_id];
        }

        if (empty($ledgerIds)) {
            return;
        }

        $pendingTransactionsQuery = PettyCashTransaction::query()
            ->whereIn('ledger_id', $ledgerIds)
            ->where('status', PettyCashTransaction::STATUS_SUBMITTED);

        $this->pendingTransactionsCount = (clone $pendingTransactionsQuery)->count();

        $pendingTransactions = $pendingTransactionsQuery
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'ledger_id', 'amount', 'reference_number', 'description', 'created_at']);

        $pendingChargeRequestsQuery = PettyCashTransaction::query()
            ->whereIn('ledger_id', $ledgerIds)
            ->where('type', PettyCashTransaction::TYPE_CHARGE)
            ->where('status', PettyCashTransaction::STATUS_SUBMITTED);

        $this->pendingChargeRequestsCount = (clone $pendingChargeRequestsQuery)->count();

        $pendingChargeRequests = $pendingChargeRequestsQuery
            ->orderByDesc('transaction_date')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'ledger_id', 'amount', 'reference_number', 'description', 'created_at']);

        $pendingArchivesQuery = PettyCashCycle::query()
            ->whereIn('ledger_id', $ledgerIds)
            ->where('status', 'pending_close');

        $this->pendingArchivesCount = (clone $pendingArchivesQuery)->count();

        $pendingArchives = $pendingArchivesQuery
            ->with(['ledger:id,branch_name'])
            ->orderByDesc('requested_close_at')
            ->orderByDesc('id')
            ->limit(5)
            ->get(['id', 'ledger_id', 'requested_close_at', 'requested_close_by', 'request_note']);

        $this->items = [
            'transactions' => $pendingTransactions,
            'charge_requests' => $pendingChargeRequests,
            'archives' => $pendingArchives,
        ];
    }
}
