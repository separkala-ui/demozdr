<?php

namespace App\Livewire\PettyCash;

use App\Models\PettyCashCycle;
use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use App\Services\PettyCash\PettyCashArchiveService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class SettlementPanel extends Component
{
    public PettyCashLedger $ledger;

    public ?PettyCashCycle $cycle = null;

    public ?string $note = null;

    public ?string $adminNote = null;

    protected $listeners = [
        'petty-cash-transaction-saved' => 'refreshState',
        'petty-cash-transaction-deleted' => 'refreshState',
    ];

    public function mount(PettyCashLedger $ledger): void
    {
        $this->ledger = $ledger;
        $this->refreshState();
    }

    public function refreshState(): void
    {
        $this->cycle = $this->ledger->currentCycle()->first();
        $this->note = null;
        $this->adminNote = null;
    }

    public function requestSettlement(): void
    {
        $this->authorizeUser();

        if (Auth::user()?->hasRole(['Superadmin', 'Admin'])) {
            session()->flash('error', __('مدیران نمی‌توانند از این بخش درخواست تسویه ثبت کنند.'));
            return;
        }

        if (! $this->cycle || $this->cycle->status !== 'open') {
            session()->flash('error', __('در حال حاضر امکان تسویه وجود ندارد.'));
            return;
        }

        $hasPendingTransactions = $this->ledger->transactions()
            ->whereNull('archive_cycle_id')
            ->where('status', '!=', PettyCashTransaction::STATUS_APPROVED)
            ->exists();

        if ($hasPendingTransactions) {
            session()->flash('error', __('برای ثبت درخواست تسویه، ابتدا وضعیت تراکنش‌های در انتظار را مشخص کنید.'));
            return;
        }

        $this->cycle->update([
            'status' => 'pending_close',
            'requested_close_by' => Auth::id(),
            'requested_close_at' => now(),
            'request_note' => $this->note,
        ]);

        $this->refreshState();
        $this->dispatch('petty-cash-cycle-updated');
        session()->flash('success', __('درخواست تسویه برای مدیریت ارسال شد.'));
    }

    public function approveSettlement(PettyCashArchiveService $archiveService): void
    {
        $this->authorizeAdmin();

        if (! $this->cycle || $this->cycle->status !== 'pending_close') {
            session()->flash('error', __('درخواستی برای تسویه یافت نشد.'));
            return;
        }

        try {
            $archiveService->finalizeCycle($this->cycle, $this->ledger, Auth::user(), $this->adminNote);
            $this->refreshState();
            $this->dispatch('petty-cash-cycle-updated');
            session()->flash('success', __('فصل تنخواه با موفقیت بسته و در بایگانی ثبت شد.'));
        } catch (\Throwable $throwable) {
            Log::error('Failed to finalize petty cash cycle', [
                'ledger_id' => $this->ledger->id,
                'cycle_id' => $this->cycle?->id,
                'error' => $throwable->getMessage(),
            ]);

            session()->flash('error', __('ثبت آرشیو تسویه با خطا مواجه شد. لطفاً دوباره تلاش کنید.'));
        }
    }

    public function rejectSettlement(): void
    {
        $this->authorizeAdmin();

        if (! $this->cycle || $this->cycle->status !== 'pending_close') {
            session()->flash('error', __('درخواستی برای لغو وجود ندارد.'));
            return;
        }

        $this->cycle->update([
            'status' => 'open',
            'requested_close_by' => null,
            'requested_close_at' => null,
            'request_note' => null,
        ]);

        $this->refreshState();
        $this->dispatch('petty-cash-cycle-updated');
        session()->flash('success', __('درخواست تسویه لغو شد.'));
    }

    public function render()
    {
        return view('livewire.petty-cash.settlement-panel', [
            'transactionsPendingCount' => $this->ledger->transactions()
                ->whereNull('archive_cycle_id')
                ->where('status', '!=', PettyCashTransaction::STATUS_APPROVED)
                ->count(),
        ]);
    }

    protected function authorizeUser(): void
    {
        if (! Auth::check()) {
            abort(403);
        }
    }

    protected function authorizeAdmin(): void
    {
        $user = Auth::user();

        if (! $user || ! $user->hasRole(['Superadmin', 'Admin'])) {
            abort(403);
        }
    }
}
