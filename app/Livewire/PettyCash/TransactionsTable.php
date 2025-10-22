<?php

namespace App\Livewire\PettyCash;

use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use App\Services\PettyCash\PettyCashService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Hekmatinasser\Verta\Verta;
use Livewire\Component;
use Livewire\WithPagination;

class TransactionsTable extends Component
{
    use WithPagination;

    protected $listeners = [
        'petty-cash-transaction-saved' => '$refresh',
        'petty-cash-transaction-deleted' => '$refresh',
        'petty-cash-transactions-refresh' => '$refresh',
    ];

    public PettyCashLedger $ledger;

    public ?string $status = null;

    public ?string $type = null;

    public ?string $search = null;

    public ?string $dateFrom = null;

    public ?string $dateTo = null;

    public ?string $period = null;

    protected $queryString = [
        'status' => ['except' => null],
        'type' => ['except' => null],
        'search' => ['except' => null],
        'dateFrom' => ['except' => null],
        'dateTo' => ['except' => null],
        'period' => ['except' => null],
        'page' => ['except' => 1],
    ];

    public function mount(PettyCashLedger $ledger): void
    {
        $this->ledger = $ledger;
    }

    public function updating($name, $value): void
    {
        if (in_array($name, ['status', 'type', 'search', 'dateFrom', 'dateTo', 'period'])) {
            $this->resetPage();
        }
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        $this->applyPeriodFilter();
        $this->resetPage();
    }

    public function requestEdit(int $transactionId): void
    {
        $transaction = $this->ledger->transactions()->find($transactionId);

        if (! $transaction) {
            return;
        }

        if (! $this->userCanManageTransactions() && ! $this->userCanReviseTransaction($transaction)) {
            session()->flash('error', __('شما مجاز به ویرایش این تراکنش نیستید.'));
            return;
        }

        $this->dispatchToForm('petty-cash-transaction-edit', $transactionId);
    }

    public function requestApprove(int $transactionId): void
    {
        if (! $this->userCanManageTransactions()) {
            return;
        }

        $this->dispatchToForm('petty-cash-transaction-approve', $transactionId);
    }

    public function requestDelete(int $transactionId): void
    {
        if (! $this->userCanManageTransactions()) {
            return;
        }

        $this->dispatchToForm('petty-cash-transaction-delete', $transactionId);
    }

    public function requestReject(int $transactionId): void
    {
        if (! $this->userCanManageTransactions()) {
            return;
        }

        $transaction = $this->ledger->transactions()->find($transactionId);

        if (! $transaction) {
            return;
        }

        try {
            app(PettyCashService::class)->rejectTransaction($transaction, Auth::user());
            session()->flash('success', __('تراکنش با موفقیت رد شد.'));
            $this->dispatch('petty-cash-transaction-saved');
        } catch (ValidationException $exception) {
            session()->flash('error', $exception->getMessage());
        } catch (\Throwable $throwable) {
            session()->flash('error', __('در رد کردن تراکنش خطایی رخ داد.'));
            report($throwable);
        }
    }

    public function requestRevision(int $transactionId): void
    {
        if (! $this->userCanManageTransactions()) {
            return;
        }

        $transaction = $this->ledger->transactions()->find($transactionId);

        if (! $transaction) {
            return;
        }

        try {
            if ($transaction->status === PettyCashTransaction::STATUS_NEEDS_CHANGES) {
                session()->flash('success', __('این تراکنش هم‌اکنون در وضعیت بازبینی است.'));
                return;
            }

            app(PettyCashService::class)->sendBackForRevision($transaction, Auth::user());
            session()->flash('success', __('برای بازبینی به کاربر شعبه ارسال شد.'));
            $this->dispatch('petty-cash-transaction-saved');
        } catch (ValidationException $exception) {
            session()->flash('error', $exception->getMessage());
        } catch (\Throwable $throwable) {
            session()->flash('error', __('ارسال برای بازبینی با خطا مواجه شد.'));
            report($throwable);
        }
    }

    public function markSuspicious(int $transactionId): void
    {
        if (! $this->userCanManageTransactions()) {
            return;
        }

        $transaction = $this->ledger->transactions()->find($transactionId);

        if (! $transaction) {
            return;
        }

        try {
            if (($transaction->meta['suspicious'] ?? false) === true) {
                session()->flash('success', __('این تراکنش پیش‌تر به عنوان مشکوک علامت‌گذاری شده است.'));
                return;
            }

            app(PettyCashService::class)->markSuspicious($transaction, Auth::user());
            session()->flash('success', __('رفتار مشکوک ثبت و در حال بررسی مدیریت است.'));
            $this->dispatch('petty-cash-transaction-saved');
        } catch (ValidationException $exception) {
            session()->flash('error', $exception->getMessage());
        } catch (\Throwable $throwable) {
            session()->flash('error', __('ثبت رفتار مشکوک با خطا مواجه شد.'));
            report($throwable);
        }
    }

    protected function dispatchToForm(string $eventName, int $transactionId): void
    {
        $this->dispatch($eventName, [
            'id' => $transactionId,
            'ledger_id' => $this->ledger->id,
        ])->to(\App\Livewire\PettyCash\TransactionForm::class);
    }

    protected function userCanManageTransactions(): bool
    {
        $user = Auth::user();

        return $user && $user->hasRole(['Superadmin', 'Admin']);
    }

    protected function userCanReviseTransaction(PettyCashTransaction $transaction): bool
    {
        $user = Auth::user();

        if (! $user) {
            return false;
        }

        return (int) $transaction->requested_by === $user->id
            && $transaction->status === PettyCashTransaction::STATUS_NEEDS_CHANGES;
    }

    public function canEditTransaction(PettyCashTransaction $transaction): bool
    {
        return $this->userCanManageTransactions() || $this->userCanReviseTransaction($transaction);
    }

    protected function parseJalaliDate(?string $value): ?Carbon
    {
        if (! $value) {
            return null;
        }

        try {
            $normalized = $this->normalizeJalaliInput($value);
            return Verta::parseFormat('Y-m-d', $normalized)->toCarbon();
        } catch (\Throwable $e) {
            try {
                return Carbon::parse($value);
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }

    protected function normalizeJalaliInput(string $value): string
    {
        $persianDigits = ['۰','۱','۲','۳','۴','۵','۶','۷','۸','۹'];
        $arabicDigits = ['٠','١','٢','٣','٤','٥','٦','٧','٨','٩'];
        $englishDigits = ['0','1','2','3','4','5','6','7','8','9'];

        $normalized = str_replace($persianDigits, $englishDigits, $value);
        $normalized = str_replace($arabicDigits, $englishDigits, $normalized);

        return str_replace('/', '-', trim($normalized));
    }

    protected function applyPeriodFilter(): void
    {
        if (!$this->period) {
            return;
        }

        $today = Verta::today();

        switch ($this->period) {
            case 'today':
                $this->dateFrom = $today->format('Y-m-d');
                $this->dateTo = $today->format('Y-m-d');
                break;
            case 'yesterday':
                $yesterday = $today->copy()->subDay();
                $this->dateFrom = $yesterday->format('Y-m-d');
                $this->dateTo = $yesterday->format('Y-m-d');
                break;
            case '3days':
                $this->dateFrom = $today->copy()->subDays(3)->format('Y-m-d');
                $this->dateTo = $today->format('Y-m-d');
                break;
            case '7days':
                $this->dateFrom = $today->copy()->subDays(7)->format('Y-m-d');
                $this->dateTo = $today->format('Y-m-d');
                break;
            case 'month':
                $this->dateFrom = $today->toCarbon()->startOfMonth()->format('Y-m-d');
                $this->dateTo = $today->toCarbon()->endOfMonth()->format('Y-m-d');
                break;
        }
    }

    public function getTransactionsProperty()
    {
        $query = $this->ledger->transactions()
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->when($this->type, fn ($query) => $query->where('type', $this->type))
            ->when($this->search, function ($query) {
                $term = "%{$this->search}%";
                $query->where(function ($sub) use ($term) {
                    $sub->where('description', 'like', $term)
                        ->orWhere('reference_number', 'like', $term);
                });
            })
            ->when($this->parseJalaliDate($this->dateFrom), fn ($query, $from) => $query->whereDate('transaction_date', '>=', $from))
            ->when($this->parseJalaliDate($this->dateTo), fn ($query, $to) => $query->whereDate('transaction_date', '<=', $to))
            ->orderByDesc('transaction_date');

        return $query->paginate(15);
    }

    public function render()
    {
        return view('livewire.petty-cash.transactions-table', [
            'transactions' => $this->transactions,
            'statusOptions' => [
                PettyCashTransaction::STATUS_DRAFT => __('پیش‌نویس'),
                PettyCashTransaction::STATUS_SUBMITTED => __('ارسال‌شده'),
                PettyCashTransaction::STATUS_APPROVED => __('تایید‌شده'),
                PettyCashTransaction::STATUS_REJECTED => __('رد‌شده'),
                PettyCashTransaction::STATUS_NEEDS_CHANGES => __('نیاز به اصلاح'),
                PettyCashTransaction::STATUS_UNDER_REVIEW => __('در حال بررسی'),
            ],
            'typeOptions' => [
                PettyCashTransaction::TYPE_EXPENSE => __('هزینه'),
                PettyCashTransaction::TYPE_CHARGE => __('شارژ'),
                PettyCashTransaction::TYPE_ADJUSTMENT => __('تعدیل'),
            ],
            'periodOptions' => [
                'today' => __('امروز'),
                'yesterday' => __('دیروز'),
                '3days' => __('سه روز گذشته'),
                '7days' => __('هفت روز گذشته'),
                'month' => __('ماه جاری'),
            ],
        ]);
    }
}
