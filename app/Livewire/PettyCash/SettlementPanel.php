<?php

namespace App\Livewire\PettyCash;

use App\Models\PettyCashCycle;
use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use Illuminate\Support\Facades\Auth;
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
        session()->flash('success', __('درخواست تسویه برای مدیریت ارسال شد.'));
    }

    public function approveSettlement(): void
    {
        $this->authorizeAdmin();

        if (! $this->cycle || $this->cycle->status !== 'pending_close') {
            session()->flash('error', __('درخواستی برای تسویه یافت نشد.'));
            return;
        }

        $this->cycle->update([
            'status' => 'closed',
            'closed_by' => Auth::id(),
            'closed_at' => now(),
            'closing_balance' => $this->ledger->current_balance,
            'closing_note' => $this->adminNote,
        ]);

        $this->ledger->update(['opening_balance' => $this->ledger->current_balance]);

        $this->ledger->cycles()->create([
            'status' => 'open',
            'opened_at' => now(),
            'opening_balance' => $this->ledger->current_balance,
        ]);

        $this->refreshState();
        session()->flash('success', __('فصل تنخواه با موفقیت بسته و فصل جدید ایجاد شد.'));
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
        session()->flash('success', __('درخواست تسویه لغو شد.'));
    }

    public function render()
    {
        return view('livewire.petty-cash.settlement-panel', [
            'transactionsPendingCount' => $this->ledger->transactions()->where('status', '!=', PettyCashTransaction::STATUS_APPROVED)->count(),
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
