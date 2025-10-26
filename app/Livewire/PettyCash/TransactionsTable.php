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

    public int $page = 1;

    public bool $showPreviewModal = false;

    public array $previewTransaction = [];

    public bool $showApproveModal = false;

    public ?int $approvalTransactionId = null;

    public string $approvalNote = '';

    public bool $showRejectModal = false;

    public ?int $rejectTransactionId = null;

    public string $rejectNote = '';

    public bool $showRevisionModal = false;

    public ?int $revisionTransactionId = null;

    public string $revisionNote = '';

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
            session()->flash('error', __('شما مجاز به تایید تراکنش‌ها نیستید.'));
            return;
        }

        $this->approvalTransactionId = $transactionId;
        $this->approvalNote = '';
        $this->resetErrorBag(['approvalNote']);
        $this->showApproveModal = true;

        // Dispatch browser event to open modal via Alpine
        $this->dispatch('open-approve-modal');
        $this->dispatch('store-selected-branch', ['branchId' => $this->ledger->id]);
    }

    public function requestDelete(int $transactionId): void
    {
        if (! $this->userCanManageTransactions()) {
            return;
        }

        $this->dispatchToForm('petty-cash-transaction-delete', $transactionId);
        
        // Dispatch event to store current branch in session storage
        $this->dispatch('store-selected-branch', ['branchId' => $this->ledger->id]);
    }

    public function requestReject(int $transactionId): void
    {
        if (! $this->userCanManageTransactions()) {
            session()->flash('error', __('شما مجاز به رد تراکنش‌ها نیستید.'));
            return;
        }

        $transaction = $this->ledger->transactions()->find($transactionId);

        if (! $transaction) {
            session()->flash('error', __('تراکنش یافت نشد.'));
            return;
        }

        $this->rejectTransactionId = $transactionId;
        $this->rejectNote = '';
        $this->resetErrorBag(['rejectNote']);
        $this->showRejectModal = true;

        // Dispatch browser event to open modal via Alpine
        $this->dispatch('open-reject-modal');
        $this->dispatch('store-selected-branch', ['branchId' => $this->ledger->id]);
    }

    public function openRevisionModal(int $transactionId): void
    {
        if (! $this->userCanManageTransactions()) {
            return;
        }

        $this->revisionTransactionId = $transactionId;
        $this->revisionNote = '';
        $this->showRevisionModal = true;
    }

    public function requestRevision(): void
    {
        if (! $this->userCanManageTransactions()) {
            return;
        }

        $this->validate([
            'revisionNote' => 'required|string|min:5|max:500',
        ], [
            'revisionNote.required' => 'لطفاً دلیل ارسال برای بازبینی را وارد کنید.',
            'revisionNote.min' => 'دلیل باید حداقل ۵ کاراکتر باشد.',
            'revisionNote.max' => 'دلیل نباید بیشتر از ۵۰۰ کاراکتر باشد.',
        ]);

        $transaction = $this->ledger->transactions()->find($this->revisionTransactionId);

        if (! $transaction) {
            session()->flash('error', __('تراکنش یافت نشد.'));
            $this->showRevisionModal = false;
            return;
        }

        try {
            if ($transaction->status === PettyCashTransaction::STATUS_NEEDS_CHANGES) {
                session()->flash('success', __('این تراکنش هم‌اکنون در وضعیت بازبینی است.'));
                $this->showRevisionModal = false;
                return;
            }

            app(PettyCashService::class)->sendBackForRevision($transaction, Auth::user(), $this->revisionNote);
            session()->flash('success', __('برای بازبینی به کاربر شعبه ارسال شد.'));
            $this->showRevisionModal = false;
            $this->revisionNote = '';
            $this->revisionTransactionId = null;
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

    public function showPreview(int $transactionId): void
    {
        $transaction = $this->ledger->transactions()
            ->with([
                'requester:id,first_name,last_name,email',
                'approver:id,first_name,last_name,email',
                'ledger.assignedUser:id,first_name,last_name,email',
            ])
            ->find($transactionId);

        if (! $transaction) {
            return;
        }

        $custodian = $transaction->ledger?->assignedUser;
        $custodianName = $custodian?->full_name
            ?? ($custodian?->first_name || $custodian?->last_name
                ? trim(($custodian?->first_name ?? '') . ' ' . ($custodian?->last_name ?? ''))
                : null);
        if (! $custodianName) {
            $custodianName = $custodian?->name ?? $custodian?->email;
        }

        $this->previewTransaction = [
            'id' => $transaction->id,
            'reference_number' => $transaction->reference_number,
            'type' => $transaction->type,
            'status' => $transaction->status,
            'amount' => (float) $transaction->amount,
            'category' => $transaction->category,
            'description' => $transaction->description,
            'transaction_date' => $transaction->transaction_date ? Verta::instance($transaction->transaction_date)->format('Y/m/d H:i') : null,
            'created_at' => Verta::instance($transaction->created_at)->format('Y/m/d H:i'),
            'updated_at' => Verta::instance($transaction->updated_at)->format('Y/m/d H:i'),
            'requester' => $transaction->requester?->full_name ?? $transaction->requester?->name ?? $transaction->requester?->email,
            'approver' => $transaction->approver?->full_name ?? $transaction->approver?->name ?? $transaction->approver?->email,
            'custodian' => $custodianName,
            'meta' => $transaction->meta ?? [],
            'attachments' => [
                'invoice' => $transaction->getMedia('invoice')->map(fn ($media) => [
                    'name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'preview_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                    'mime_type' => $media->mime_type,
                ])->toArray(),
                'bank_receipt' => $transaction->getMedia('bank_receipt')->map(fn ($media) => [
                    'name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'preview_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                    'mime_type' => $media->mime_type,
                ])->toArray(),
                'charge_request' => $transaction->getMedia('charge_request')->map(fn ($media) => [
                    'name' => $media->file_name,
                    'url' => $media->getUrl(),
                    'preview_url' => $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl(),
                    'mime_type' => $media->mime_type,
                ])->toArray(),
            ],
        ];

        $this->showPreviewModal = true;
    }

    public function closePreview(): void
    {
        $this->showPreviewModal = false;
        $this->previewTransaction = [];
    }

    public function closeApproveModal(): void
    {
        $this->showApproveModal = false;
        $this->approvalTransactionId = null;
        $this->approvalNote = '';
        $this->resetErrorBag(['approvalNote']);
    }

    public function closeRejectModal(): void
    {
        $this->showRejectModal = false;
        $this->rejectTransactionId = null;
        $this->rejectNote = '';
        $this->resetErrorBag(['rejectNote']);
    }

    public function approveSelectedTransaction(): void
    {
        if (! $this->userCanManageTransactions()) {
            session()->flash('error', __('شما مجاز به تایید تراکنش‌ها نیستید.'));
            return;
        }

        if (! $this->approvalTransactionId) {
            return;
        }

        $transaction = $this->ledger->transactions()->find($this->approvalTransactionId);
        $user = Auth::user();

        if (! $transaction || ! $user) {
            return;
        }

        try {
            $note = trim($this->approvalNote) !== '' ? trim($this->approvalNote) : null;
            app(PettyCashService::class)->approveTransaction($transaction, $user, $note);

            session()->flash('success', __('تراکنش با موفقیت تایید شد.'));
            $this->dispatch('petty-cash-transaction-saved');
        } catch (ValidationException $exception) {
            session()->flash('error', $exception->getMessage());
        } catch (\Throwable $throwable) {
            report($throwable);
            session()->flash('error', __('در تایید تراکنش خطایی رخ داد.'));
        }

        $this->closeApproveModal();
    }

    public function rejectSelectedTransaction(): void
    {
        if (! $this->userCanManageTransactions()) {
            session()->flash('error', __('شما مجاز به رد تراکنش‌ها نیستید.'));
            return;
        }

        if (! $this->rejectTransactionId) {
            $this->addError('rejectNote', __('تراکنش انتخاب نشده است.'));
            return;
        }

        $note = trim((string) $this->rejectNote);

        if ($note === '') {
            $this->addError('rejectNote', __('لطفاً دلیل رد تراکنش را وارد کنید.'));
            return;
        }

        $transaction = $this->ledger->transactions()->find($this->rejectTransactionId);
        $user = Auth::user();

        if (! $transaction || ! $user) {
            $this->addError('rejectNote', __('تراکنش برای رد یافت نشد.'));
            return;
        }

        try {
            app(PettyCashService::class)->rejectTransaction($transaction, $user, $note);

            session()->flash('success', __('تراکنش با موفقیت رد شد.'));
            $this->dispatch('petty-cash-transaction-saved');
            $this->dispatch('store-selected-branch', ['branchId' => $this->ledger->id]);
            $this->closeRejectModal();
        } catch (ValidationException $exception) {
            $errors = collect($exception->errors())->flatten()->filter()->all();
            $message = $errors[0] ?? $exception->getMessage();
            $this->addError('rejectNote', $message);
        } catch (\Throwable $throwable) {
            report($throwable);
            session()->flash('error', __('در رد کردن تراکنش خطایی رخ داد.'));
        }
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
            ->whereNull('archive_cycle_id')
            ->when($this->parseJalaliDate($this->dateFrom), fn ($query, $from) => $query->whereDate('transaction_date', '>=', $from))
            ->when($this->parseJalaliDate($this->dateTo), fn ($query, $to) => $query->whereDate('transaction_date', '<=', $to))
            ->orderByDesc('transaction_date');

        return $query->paginate(15);
    }

    protected function getPendingChargeRequests()
    {
        return $this->ledger->transactions()
            ->where('type', PettyCashTransaction::TYPE_CHARGE)
            ->whereIn('status', [
                PettyCashTransaction::STATUS_SUBMITTED,
                PettyCashTransaction::STATUS_UNDER_REVIEW,
            ])
            ->with(['requester.branch', 'ledger'])
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function render()
    {
        return view('livewire.petty-cash.transactions-table', [
            'transactions' => $this->transactions,
            'pendingChargeRequests' => $this->getPendingChargeRequests(),
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
