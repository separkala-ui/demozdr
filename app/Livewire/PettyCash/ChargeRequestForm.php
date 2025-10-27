<?php

namespace App\Livewire\PettyCash;

use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class ChargeRequestForm extends Component
{
    use WithFileUploads;

    public PettyCashLedger $ledger;

    public ?PettyCashTransaction $activeRequest = null;

    public ?PettyCashTransaction $revisionRequest = null;

    public ?float $amount = null;

    public ?string $note = null;

    /**
     * @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null
     */
    public $attachment = null;

    public array $quickAmounts = [
        10_000_000,
        30_000_000,
        50_000_000,
        70_000_000,
        100_000_000,
    ];

    protected $listeners = [
        'petty-cash-transaction-saved' => 'refreshState',
        'petty-cash-transaction-deleted' => 'refreshState',
        'petty-cash-transactions-refresh' => 'refreshState',
    ];

    public function mount(PettyCashLedger $ledger): void
    {
        $this->ledger = $ledger;
        $this->refreshState();
    }

    public function refreshState(): void
    {
        $userId = Auth::id();

        if (! $userId) {
            $this->activeRequest = null;
            $this->revisionRequest = null;
            return;
        }

        $this->activeRequest = $this->ledger->transactions()
            ->where('type', PettyCashTransaction::TYPE_CHARGE)
            ->whereIn('status', [
                PettyCashTransaction::STATUS_SUBMITTED,
                PettyCashTransaction::STATUS_UNDER_REVIEW,
            ])
            ->where('requested_by', $userId)
            ->orderByDesc('transaction_date')
            ->first();

        $this->revisionRequest = $this->ledger->transactions()
            ->where('type', PettyCashTransaction::TYPE_CHARGE)
            ->where('status', PettyCashTransaction::STATUS_NEEDS_CHANGES)
            ->where('requested_by', $userId)
            ->orderByDesc('transaction_date')
            ->first();
    }

    public function selectQuickAmount(int $value): void
    {
        if ($this->isLocked()) {
            return;
        }

        if (! in_array($value, $this->quickAmounts, true)) {
            return;
        }

        $this->ledger = $this->ledger->fresh();
        $limit = (float) ($this->ledger->max_charge_request_amount ?? 0);

        if ($limit > 0 && $value > $limit) {
            $this->addError('amount', __('مبلغ انتخاب‌شده بیش از سقف مجاز (:limit ریال) است.', [
                'limit' => number_format($limit),
            ]));
            $this->amount = $limit;

            return;
        }

        $this->resetErrorBag('amount');
        $this->amount = $value;
    }

    public function submit(): void
    {
        if (! Auth::check()) {
            abort(403);
        }

        if ($this->isLocked()) {
            return;
        }

        $this->ledger = $this->ledger->fresh();
        $limit = (float) ($this->ledger->max_charge_request_amount ?? 0);

        $this->amount = $this->normalizeAmount($this->amount);

        $validated = $this->validate([
            'amount' => [
                'required',
                'numeric',
                'min:100000',
                function (string $attribute, $value, callable $fail) use ($limit) {
                    if ($limit > 0 && (float) $value > $limit) {
                        $fail(__('مبلغ درخواست شارژ نمی‌تواند از سقف مجاز (:limit ریال) بیشتر باشد.', [
                            'limit' => number_format($limit),
                        ]));
                    }
                },
            ],
            'note' => ['nullable', 'string', 'max:500'],
            'attachment' => ['nullable', 'file', 'max:4096'],
        ], [], [
            'amount' => __('مبلغ درخواست شارژ'),
            'note' => __('توضیح'),
            'attachment' => __('مستندات'),
        ]);

        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $existingPending = $this->ledger->transactions()
            ->where('type', PettyCashTransaction::TYPE_CHARGE)
            ->whereIn('status', [
                PettyCashTransaction::STATUS_SUBMITTED,
                PettyCashTransaction::STATUS_UNDER_REVIEW,
            ])
            ->where('requested_by', $user->id)
            ->exists();

        if ($existingPending) {
            $this->refreshState();
            session()->flash('error', __('در حال حاضر یک درخواست شارژ در حال بررسی دارید.'));
            return;
        }

        $transaction = new PettyCashTransaction([
            'type' => PettyCashTransaction::TYPE_CHARGE,
            'status' => PettyCashTransaction::STATUS_SUBMITTED,
            'amount' => $validated['amount'],
            'currency' => 'IRR',
            'transaction_date' => now(),
            'reference_number' => null,
            'description' => $validated['note'] ?? null,
            'meta' => [
                'charge_request' => [
                    'requested_by' => $user->id,
                    'requested_by_name' => $user->full_name,
                    'requested_at' => now()->toISOString(),
                    'quick_amount' => in_array((int) $validated['amount'], $this->quickAmounts, true),
                    'note' => $validated['note'] ?? null,
                    'source' => 'request_form',
                ],
            ],
            'requested_by' => $user->id,
        ]);

        $transaction->charge_origin = 'request_form';

        $this->ledger->transactions()->save($transaction);

        if ($this->attachment) {
            $transaction->addMedia($this->attachment->getRealPath())
                ->usingFileName($this->attachment->getClientOriginalName())
                ->withCustomProperties([
                    'uploaded_by' => $user->id,
                    'context' => 'charge_request',
                ])
                ->toMediaCollection('charge_request');
        }

        $this->amount = null;
        $this->note = null;
        $this->attachment = null;

        $this->refreshState();

        $this->dispatch('petty-cash-transactions-refresh');

        // Send SMS to Finance Manager
        try {
            sms()->sendChargeRequestSMS(
                $user->full_name,
                $this->ledger->branch_name,
                (string) $validated['amount'],
                verta()->now()->format('Y/n/j H:i')
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Charge Request SMS failed: ' . $e->getMessage());
            // Do not block user for SMS failure
        }

        session()->flash('success', __('درخواست شارژ با موفقیت ثبت شد و برای تایید مدیریت ارسال گردید.'));
    }

    public function isLocked(): bool
    {
        if (! $this->activeRequest) {
            return false;
        }

        return in_array($this->activeRequest->status, [
            PettyCashTransaction::STATUS_SUBMITTED,
            PettyCashTransaction::STATUS_UNDER_REVIEW,
        ], true);
    }

    protected function normalizeAmount(mixed $value): float
    {
        if (is_numeric($value)) {
            return (float) $value;
        }

        $clean = preg_replace('/[^0-9.-]/u', '', (string) $value);

        if ($clean === '' || $clean === '-' || $clean === '.-' || $clean === '-.' || $clean === '.') {
            return 0.0;
        }

        return (float) $clean;
    }


    public function render()
    {
        return view('livewire.petty-cash.charge-request-form', [
            'userHasPendingRequest' => $this->isLocked(),
        ]);
    }
}
