<?php

namespace App\Livewire\PettyCash;

use App\Exceptions\SmartInvoiceException;
use App\Models\PettyCashLedger;
use App\Models\PettyCashTransaction;
use App\Services\PettyCash\Data\SmartInvoiceExtraction;
use App\Services\PettyCash\SmartInvoiceService;
use App\Services\PettyCash\PettyCashService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Hekmatinasser\Verta\Verta;
use Livewire\Component;
use Livewire\WithFileUploads;

class TransactionForm extends Component
{
    use WithFileUploads;

    protected $listeners = [
        'petty-cash-transaction-edit' => 'loadTransaction',
        'petty-cash-transaction-approve' => 'approveTransactionFromTable',
        'petty-cash-transaction-delete' => 'deleteTransaction',
    ];

    public PettyCashLedger $ledger;

    public ?PettyCashTransaction $transaction = null;

    protected ?int $serialCursor = null;

    public ?int $id = null;

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $entries = [];

    /**
     * @var array<int, array<string, mixed>>
     */
    public array $smartEntriesState = [];

    public ?int $editingTransactionId = null;

    public function getItemCategories(): array
    {
        return config('petty-cash.categories', [
            'vegetables' => 'تره بار و محصولات تازه',
            'protein' => 'محصولات پروتئینی',
            'transport' => 'حمل و نقل',
            'repairs' => 'تعمیرات و نگهداری',
            'cleaning' => 'مواد و خدمات نظافتی',
            'utilities' => 'قبوض آب، برق، گاز و خدمات شهری',
            'fuel' => 'سوخت و انرژی',
            'supplies' => 'لوازم مصرفی و اداری',
            'marketing' => 'تبلیغات و بازاریابی',
            'insurance' => 'بیمه و خدمات مالی',
            'rent' => 'اجاره و اجرت',
            'equipment' => 'تجهیزات و ابزار',
            'furniture' => 'مبلمان و دکوراسیون',
            'electronics' => 'کالاهای الکترونیکی',
            'security' => 'حفاظت و امنیت',
            'waste' => 'دفع ضایعات',
            'other' => 'سایر هزینه‌ها',
        ]);
    }

    public function mount(PettyCashLedger $ledger, ?PettyCashTransaction $transaction = null): void
    {
        $this->ledger = $ledger;
        $this->refreshLedgerSnapshot();
        $this->initializeDefaults();

        if ($transaction) {
            $this->loadTransaction([
                'id' => $transaction->id,
                'ledger_id' => $ledger->id,
            ]);
        }
    }

    public function submit(PettyCashService $service): void
    {
        if ($this->ledger->exists) {
            $this->ledger = $this->ledger->fresh();
        }

        $rules = $this->rules();

        foreach ($this->entries as $index => $entry) {
            $ruleKey = 'entries.' . $index . '.invoice_attachment';
            $receiptRuleKey = 'entries.' . $index . '.receipt_attachment';
            $categoryRuleKey = 'entries.' . $index . '.category';

            if ($this->entryLooksComplete($entry)) {
                $requiresInvoice = true;

                if ($this->editingTransactionId && $index === 0 && $this->transaction) {
                    if ($this->transaction->hasMedia('invoice') && empty($entry['invoice_attachment'])) {
                        $requiresInvoice = false;
                    }
                }

                $rules[$ruleKey] = $requiresInvoice ? 'required|file|max:4096' : 'nullable|file|max:4096';
                $rules[$receiptRuleKey] = 'nullable|file|max:4096';
                $rules[$categoryRuleKey] = 'required|string|max:100';
            } else {
                $rules[$ruleKey] = 'nullable|file|max:4096';
                $rules[$receiptRuleKey] = 'nullable|file|max:4096';
                $rules[$categoryRuleKey] = 'nullable|string|max:100';
            }
        }

        $this->validate($rules);

        $maxChargeLimit = (float) ($this->ledger->max_charge_request_amount ?? 0);
        $maxTransactionLimit = (float) ($this->ledger->max_transaction_amount ?? 0);

        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        // Ensure serial numbers are assigned BEFORE resolving entries
        foreach ($this->entries as $index => $entry) {
            if ($this->entryLooksComplete($entry)) {
                $this->ensureSerialNumberAssigned($index);
            }
        }

        $resolvedEntries = $this->resolveEntries();

        if ($resolvedEntries->isEmpty()) {
            $this->addError('entries', __('لطفاً حداقل یک ردیف را تکمیل کنید.'));
            return;
        }

        $processed = 0;

        foreach ($resolvedEntries as $loopIndex => $entry) {
            $originalIndex = $entry['__original_index'] ?? $loopIndex;
            unset($entry['__original_index']);

            $amountValue = $this->parseAmount((string) ($entry['amount'] ?? 0));

            if ($maxTransactionLimit > 0 && $amountValue > $maxTransactionLimit) {
                $this->addError('entries.' . $originalIndex . '.amount', __('مبلغ این تراکنش نمی‌تواند از سقف مجاز (:limit ریال) بیشتر باشد.', ['limit' => number_format($maxTransactionLimit)]));
                return;
            }

            if ($entry['type'] === PettyCashTransaction::TYPE_CHARGE) {
                if ($maxChargeLimit > 0 && $amountValue > $maxChargeLimit) {
                    $this->addError('entries.' . $originalIndex . '.amount', __('مبلغ درخواست شارژ شما بیشتر از سقف مجاز (:limit ریال) است.', ['limit' => number_format($maxChargeLimit)]));
                    return;
                }

                // Allow all users to create charge requests (they will need approval)
                // Removed the restriction that only managers can create charges
            }

            if ($this->editingTransactionId && $loopIndex === 0) {
                $transaction = $this->ledger->transactions()->find($this->editingTransactionId);

                if (! $transaction) {
                    continue;
                }

                if (! $this->userCanEditTransaction($transaction)) {
                    $this->addError('entries.' . $originalIndex . '.type', __('شما مجاز به ویرایش این تراکنش نیستید.'));
                    return;
                }

                $payload = $this->buildPayload($entry);

                if (($entry['type'] ?? $transaction->type) === PettyCashTransaction::TYPE_CHARGE) {
                    $origin = $entry['meta']['charge_origin'] ?? $transaction->charge_origin ?? 'quick_entry';
                    $payload = $this->applyChargeOrigin($payload, $origin);
                }

                if (! $this->userCanManageTransactions()) {
                    $payload['status'] = PettyCashTransaction::STATUS_SUBMITTED;
                    $payload['approved_by'] = null;
                    $payload['approved_at'] = null;
                    $payload['rejected_by'] = null;
                    $payload['rejected_at'] = null;

                    $meta = $payload['meta'] ?? [];
                    if (! is_array($meta)) {
                        $meta = [];
                    }

                    unset(
                        $meta['revision_requested_by'],
                        $meta['revision_requested_at'],
                        $meta['revision_note'],
                        $meta['rejection_reason']
                    );

                    $meta = array_filter($meta, static fn ($value) => $value !== null && $value !== '');
                    $payload['meta'] = ! empty($meta) ? $meta : null;
                }

                $transaction->fill($payload);

                if (($payload['status'] ?? null) === PettyCashTransaction::STATUS_APPROVED) {
                    if (! $user->hasRole(['Superadmin', 'Admin'])) {
                        $this->addError('entries.' . $originalIndex . '.status', __('فقط مدیر ارشد و مدیر می‌توانند تراکنش‌ها را تایید کنند.'));
                        return;
                    }

                    $transaction = $service->approveTransaction($transaction, $user);
                } else {
                    $transaction->save();
                }

                $this->syncEntryAttachments($transaction, $this->entries[$originalIndex] ?? $entry, $user->id, $originalIndex);
                $processed++;

                continue;
            }

            $payload = $this->buildPayload($entry);

            if ($entry['type'] === PettyCashTransaction::TYPE_CHARGE) {
                $origin = $entry['meta']['charge_origin'] ?? 'quick_entry';
                $payload = $this->applyChargeOrigin($payload, $origin);
                $transaction = $service->recordCharge($this->ledger, $payload, $user);
            } else {
                $payload['type'] = $entry['type'];
                $transaction = $service->recordExpense($this->ledger, $payload, $user);
            }

            if ($transaction) {
                $this->syncEntryAttachments($transaction, $this->entries[$originalIndex] ?? $entry, $user->id, $originalIndex);
                $processed++;
            }
        }

        if ($processed > 0) {
            $this->dispatch('petty-cash-transaction-saved');
        }

        $this->initializeDefaults();
        $this->refreshLedgerSnapshot();
    }

    public function resetFormState(): void
    {
        $this->initializeDefaults();
        $this->refreshLedgerSnapshot();
    }

    public function loadTransaction($payload = null): void
    {
        if ($this->belongsToAnotherLedger($payload)) {
            return;
        }

        $transactionId = $this->extractTransactionId($payload);

        if (! $transactionId) {
            $this->initializeDefaults();

            return;
        }

        $transaction = $this->ledger->transactions()->find($transactionId);

        if (! $transaction) {
            return;
        }

        if (! $this->userCanEditTransaction($transaction)) {
            session()->flash('error', __('شما مجاز به ویرایش این تراکنش نیستید.'));
            $this->initializeDefaults();
            return;
        }

        $this->transaction = $transaction;
        $this->editingTransactionId = $transaction->id;
        $this->serialCursor = null;

        $entryStatus = $transaction->status;

        if (! $this->userCanManageTransactions() && $transaction->status === PettyCashTransaction::STATUS_NEEDS_CHANGES) {
            $entryStatus = PettyCashTransaction::STATUS_SUBMITTED;
        }

        $this->entries = [
            [
                'type' => $transaction->type,
                'status' => $entryStatus,
                'transaction_date' => $transaction->transaction_date
                    ? Verta::instance($transaction->transaction_date)->format('Y-m-d H:i')
                    : Verta::now()->format('Y-m-d H:i'),
                'amount' => (float) $transaction->amount,
                'currency' => $transaction->currency,
                'reference_number' => $transaction->reference_number,
                'description' => $transaction->description,
                'category' => $transaction->category,
                'invoice_attachment' => null,
                'receipt_attachment' => null,
                'manager_note' => data_get($transaction->meta, 'approval_note'),
                'meta' => $transaction->meta ?? [],
            ],
        ];

        $this->ensureTrailingEmptyRow();
        $this->syncSmartEntriesState();
        $this->refreshLedgerSnapshot();
    }

    public function removeRow(int $index): void
    {
        if (! isset($this->entries[$index])) {
            return;
        }

        unset($this->entries[$index]);
        $this->entries = array_values($this->entries);
        if (isset($this->smartEntriesState[$index])) {
            unset($this->smartEntriesState[$index]);
            $this->smartEntriesState = array_values($this->smartEntriesState);
        }

        if ($index === 0) {
            $this->transaction = null;
            $this->editingTransactionId = null;
        }

        if (empty($this->entries)) {
            $this->entries[] = $this->makeEmptyEntry();
        }

        $this->ensureTrailingEmptyRow();
        $this->syncSmartEntriesState();
    }

    public function updatedEntries(): void
    {
        $this->ensureTrailingEmptyRow();
        $this->syncSmartEntriesState();

        foreach (array_keys($this->entries) as $index) {
            $this->refreshCategoryStatus($index);
        }
    }

    private function convertPersianToEnglish(string $number): string
    {
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];
        return str_replace($persian, $english, $number);
    }

    private function parseAmount(string $amount): float
    {
        // Handle different number formats
        $amount = trim($amount);

        // Convert Persian numbers to English
        $amount = $this->convertPersianToEnglish($amount);

        // Remove any non-numeric characters except decimal point and minus
        $amount = preg_replace('/[^0-9.-]/', '', $amount);

        // Handle empty or invalid amounts
        if (empty($amount) || !is_numeric($amount)) {
            return 0.0;
        }

        return (float) $amount;
    }

    private function generateAutoSerialNumber(int $index, array $extractedData): void
    {
        // Only generate if reference_number is empty
        if (!empty($this->entries[$index]['reference_number'])) {
            return;
        }

        $branchCode = str_pad((string) ($this->ledger->id ?? 0), 3, '0', STR_PAD_LEFT);

        $sequentialNumber = $this->getNextSequentialNumber();
        $reference = "{$branchCode}-{$sequentialNumber}";

        $this->entries[$index]['reference_number'] = $reference;
    }

    private function ensureSerialNumberAssigned(int $index): string
    {
        $this->generateAutoSerialNumber($index, $this->entries[$index] ?? []);

        return (string) ($this->entries[$index]['reference_number'] ?? '');
    }

    private function applySerialToAttachments(int $index, string $serial): void
    {
        $entry = $this->entries[$index] ?? [];
        if (empty($serial) || empty($entry)) {
            return;
        }

        if (($this->smartEntriesState[$index]['serial_applied'] ?? false) === true) {
            return;
        }

        foreach (['invoice_attachment', 'receipt_attachment'] as $field) {
            if (! isset($entry[$field]) || ! $entry[$field]) {
                continue;
            }

            $file = $entry[$field];
            if (! method_exists($file, 'getRealPath')) {
                continue;
            }

            $path = $file->getRealPath();
            if (! $path || ! is_readable($path) || ! is_writable($path)) {
                continue;
            }

            $this->annotateImageWithSerial($path, $serial);
        }

        $this->smartEntriesState[$index]['serial_applied'] = true;
    }

    private function annotateImageWithSerial(string $path, string $serial): void
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        $createMap = [
            'jpg' => 'imagecreatefromjpeg',
            'jpeg' => 'imagecreatefromjpeg',
            'png' => 'imagecreatefrompng',
            'webp' => 'imagecreatefromwebp',
        ];

        $saveMap = [
            'jpg' => 'imagejpeg',
            'jpeg' => 'imagejpeg',
            'png' => 'imagepng',
            'webp' => 'imagewebp',
        ];

        $create = $createMap[$extension] ?? null;
        $save = $saveMap[$extension] ?? null;

        if (! $create || ! function_exists($create) || ! $save || ! function_exists($save)) {
            return;
        }

        $image = @$create($path);
        if (! $image) {
            return;
        }

        if (in_array($extension, ['png', 'webp'], true)) {
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        $color = imagecolorallocate($image, 220, 30, 30);
        $text = $serial;

        $fontWidth = imagefontwidth(5);
        $fontHeight = imagefontheight(5);

        $margin = 20;
        $x = imagesx($image) - ($fontWidth * strlen($text)) - $margin;
        $y = imagesy($image) - $fontHeight - $margin;

        if ($x < 10) {
            $x = 10;
        }

        if ($y < 10) {
            $y = 10;
        }

        imagestring($image, 5, (int) $x, (int) $y, $text, $color);

        if ($save === 'imagejpeg') {
            $save($image, $path, 95);
        } else {
            $save($image, $path);
        }

        imagedestroy($image);
    }

    private function getNextSequentialNumber(): string
    {
        if ($this->serialCursor === null) {
            $lastNumber = 0;

            // Get ALL transactions from database and find maximum sequential number
            $allTransactions = PettyCashTransaction::where('ledger_id', $this->ledger->id)
                ->whereNotNull('reference_number')
                ->pluck('reference_number');
            
            foreach ($allTransactions as $refNumber) {
                if ($refNumber && preg_match('/-(\d+)(?:-|$)/', (string) $refNumber, $matches)) {
                    $candidate = (int) $matches[1];
                    if ($candidate > $lastNumber) {
                        $lastNumber = $candidate;
                    }
                }
            }

            // Also check current entries for manually entered reference numbers
            foreach ($this->entries as $entry) {
                if (! empty($entry['reference_number'])) {
                    $refNumber = (string) $entry['reference_number'];
                    
                    // Extract number from formats like: 001-0005, 001-0005-ABC, or just 0005
                    if (preg_match('/-(\d+)(?:-|$)/', $refNumber, $matches)) {
                        $candidate = (int) $matches[1];
                        if ($candidate > $lastNumber) {
                            $lastNumber = $candidate;
                        }
                    } elseif (preg_match('/^(\d+)$/', $refNumber, $matches)) {
                        // Handle pure numeric reference numbers
                        $candidate = (int) $matches[1];
                        if ($candidate > $lastNumber) {
                            $lastNumber = $candidate;
                        }
                    }
                }
            }

            $this->serialCursor = $lastNumber;
        }

        $this->serialCursor++;

        return str_pad((string) $this->serialCursor, 4, '0', STR_PAD_LEFT);
    }

    private function verifyAmountConsistency(int $index, array $extractedData): void
    {
        $calculatedTotal = 0;
        $extractedTotal = 0;
        $finalAmount = 0;

        // Calculate from line items
        if (isset($extractedData['items_details']['item_structure']) && is_array($extractedData['items_details']['item_structure'])) {
            foreach ($extractedData['items_details']['item_structure'] as $item) {
                if (isset($item['total_price_in_rial_numerical'])) {
                    $amount = $this->parseAmount($item['total_price_in_rial_numerical']);
                    if ($amount > 0) {
                        $calculatedTotal += $amount;
                    }
                }
            }
            
        // Add taxes and fees to calculated total
        if (isset($extractedData['financial_summary']['vat_and_tolls_amount_in_rial_numerical'])) {
            $taxAmount = $this->parseAmount($extractedData['financial_summary']['vat_and_tolls_amount_in_rial_numerical']);
            if ($taxAmount > 0) {
                $calculatedTotal += $taxAmount;
            }
        }
        
        // Add transport costs
        if (isset($extractedData['financial_summary']['transport_total_in_rial_numerical'])) {
            $transportAmount = $this->parseAmount($extractedData['financial_summary']['transport_total_in_rial_numerical']);
            if ($transportAmount > 0) {
                $calculatedTotal += $transportAmount;
            }
        }
        
        // Add service costs
        if (isset($extractedData['financial_summary']['service_total_in_rial_numerical'])) {
            $serviceAmount = $this->parseAmount($extractedData['financial_summary']['service_total_in_rial_numerical']);
            if ($serviceAmount > 0) {
                $calculatedTotal += $serviceAmount;
            }
        }

        if (isset($extractedData['financial_summary']['other_charges_in_rial_numerical'])) {
            $otherCharges = $this->parseAmount($extractedData['financial_summary']['other_charges_in_rial_numerical']);
            if ($otherCharges > 0) {
                $calculatedTotal += $otherCharges;
            }
        }

        if (isset($extractedData['financial_summary']['prepayment_in_rial_numerical'])) {
            $prepayment = $this->parseAmount($extractedData['financial_summary']['prepayment_in_rial_numerical']);
            if ($prepayment > 0) {
                $calculatedTotal -= $prepayment;
            }
        }

        }

        // Get extracted total
        if (isset($extractedData['financial_summary']['subtotal_in_rial_numerical'])) {
            $extractedTotal = $this->parseAmount($extractedData['financial_summary']['subtotal_in_rial_numerical']);
        } elseif (isset($extractedData['financial_summary']['final_amount_in_rial_numerical'])) {
            $extractedTotal = $this->parseAmount($extractedData['financial_summary']['final_amount_in_rial_numerical']);
        }

        // Get final amount
        if (isset($extractedData['financial_summary']['final_amount_in_rial_numerical'])) {
            $finalAmount = $this->parseAmount($extractedData['financial_summary']['final_amount_in_rial_numerical']);
        }

        // Store verification data in smart entries state
        if (!isset($this->smartEntriesState[$index])) {
            $this->smartEntriesState[$index] = [];
        }

        $tolerance = (float) config('smart-invoice.validation.tolerance', 1000);

        $this->smartEntriesState[$index]['amount_verification'] = [
            'calculated_total' => $calculatedTotal,
            'extracted_total' => $extractedTotal,
            'final_amount' => $finalAmount,
            'has_discrepancy' => $calculatedTotal > 0 && $extractedTotal > 0 && abs($calculatedTotal - $extractedTotal) > $tolerance,
            'discrepancy_amount' => $calculatedTotal > 0 && $extractedTotal > 0 ? abs($calculatedTotal - $extractedTotal) : 0,
            'tolerance' => $tolerance,
        ];
    }

    private function refreshCategoryStatus(int $index, bool $forceManualNotice = false): void
    {
        if (! isset($this->smartEntriesState[$index])) {
            $this->smartEntriesState[$index] = $this->makeSmartEntryState();
        }

        $categoryKey = $this->entries[$index]['category'] ?? null;
        $categories = $this->getItemCategories();
        $categoryLabel = $categoryKey ? ($categories[$categoryKey] ?? $categoryKey) : null;

        if ($categoryKey && $categoryLabel) {
            $this->smartEntriesState[$index]['category_status'] = 'ready';
            $this->smartEntriesState[$index]['category_message'] = __('دسته‌بندی انتخاب‌شده: :category', ['category' => $categoryLabel]);

            return;
        }

        $shouldFlag = $forceManualNotice || $this->entryLooksComplete($this->entries[$index] ?? []);

        if ($shouldFlag) {
            $this->smartEntriesState[$index]['category_status'] = 'manual_required';
            $this->smartEntriesState[$index]['category_message'] = __('دسته‌بندی به صورت خودکار تشخیص داده نشد. لطفاً دسته‌بندی مناسب را انتخاب کنید.');
        } else {
            $this->smartEntriesState[$index]['category_status'] = 'unknown';
            $this->smartEntriesState[$index]['category_message'] = null;
        }
    }

    private function translateValidationIssues($issues): array
    {
        if (! is_array($issues)) {
            return [];
        }

        $translated = [];

        foreach ($issues as $issue) {
            if (! is_string($issue) || trim($issue) === '') {
                continue;
            }

            $translated[] = $this->translateValidationIssue($issue);
        }

        return array_values(array_unique($translated));
    }

    private function translateValidationIssue(string $issue): string
    {
        $clean = trim($issue);

        if ($clean === '') {
            return $clean;
        }

        $patterns = [
            '/The calculated total for item\s+(\d+)\s+\((.+?)\)\s+does not match the observed total\s+\((.+?)\)\.?/i' =>
                static function (array $matches): string {
                    return "مبلغ محاسبه‌شده برای ردیف {$matches[1]} ({$matches[2]}) با مبلغ درج‌شده ({$matches[3]}) مطابقت ندارد.";
                },
            '/The sum of individual item total prices\s+\((.+?)\)\s+does not match the invoice subtotal\s+\((.+?)\)\.?/i' =>
                static function (array $matches): string {
                    return "جمع مبالغ اقلام ({$matches[1]}) با جمع سطر فاکتور ({$matches[2]}) برابر نیست.";
                },
            '/The sum of individual item discounts\s+\((.+?)\)\s+does not match the total discount in the summary\s+\((.+?)\)\.?/i' =>
                static function (array $matches): string {
                    return "جمع تخفیف‌های اقلام ({$matches[1]}) با تخفیف درج‌شده در خلاصه ({$matches[2]}) هم‌خوانی ندارد.";
                },
            '/The final amount payable\s+\(([\d,., ]+)\)\s+does not reconcile with subtotal\s+\(([\d,., ]+)\)\s+- discount\s+\(([\d,., ]+)\)\s+- prepayment\s+\(([\d,., ]+)\)\s+=\s+([\d,., ]+)/i' =>
                static function (array $matches): string {
                    $payable = trim(rtrim($matches[1], " ."));
                    $subtotal = trim(rtrim($matches[2], " ."));
                    $discount = trim(rtrim($matches[3], " ."));
                    $prepayment = trim(rtrim($matches[4], " ."));
                    $computed = trim(rtrim($matches[5], " ."));

                    return "مبلغ نهایی قابل پرداخت ({$payable}) با محاسبه {$subtotal} - {$discount} - {$prepayment} = {$computed} سازگار نیست.";
                },
            '/Prices for items \'(.+?)\' through \'(.+?)\' are not specified on the invoice and have been recorded as 0\./i' =>
                static function (array $matches): string {
                    return "قیمت اقلام از «{$matches[1]}» تا «{$matches[2]}» در فاکتور درج نشده و با مقدار صفر ثبت شده است.";
                },
        ];

        foreach ($patterns as $pattern => $formatter) {
            if (preg_match($pattern, $clean, $matches)) {
                return $formatter($matches);
            }
        }

        if (str_contains($clean, 'The provided receipt is for')) {
            return 'رسید پیوست‌شده با تاریخ یا مبالغ فاکتور هم‌خوانی ندارد و تایید پرداخت ممکن نیست.';
        }

        if (str_contains($clean, 'does not match')) {
            return "این مقدار با داده‌های درج‌شده در فاکتور مطابقت ندارد ({$clean}).";
        }

        return "نیاز به بررسی: {$clean}";
    }

    private function autoCategorizeTransaction(int $index, array $extractedData): void
    {
        $category = null;
        
        // Check vendor name for category hints
        $vendorName = strtolower($extractedData['seller_info']['name_fa'] ?? '');
        $originName = strtolower($extractedData['origin_of_document_fa'] ?? '');
        
        // Check items for category hints
        $items = $extractedData['items_details']['item_structure'] ?? [];
        $itemDescriptions = array_column($items, 'product_or_service_description_fa');
        $allText = strtolower(implode(' ', $itemDescriptions));
        
        // Category detection logic
        if (str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'سبزی') || 
            str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'تره بار') ||
            str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'میوه') ||
            str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'صیفی')) {
            $category = 'vegetables';
        } elseif (str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'گوشت') || 
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'مرغ') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'ماهی') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'پروتئین')) {
            $category = 'protein';
        } elseif (str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'حمل') || 
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'نقل') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'تاکسی') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'کرایه')) {
            $category = 'transport';
        } elseif (str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'تعمیر') || 
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'نگهداری') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'تعمیرات')) {
            $category = 'repairs';
        } elseif (str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'شوینده') || 
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'پاک کننده') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'مواد شوینده')) {
            $category = 'cleaning';
        } elseif (str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'برق') || 
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'آب') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'گاز') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'قبض')) {
            $category = 'utilities';
        } elseif (str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'بنزین') || 
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'سوخت') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'گازوئیل')) {
            $category = 'fuel';
        } elseif (str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'منابع انسانی') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'استخدام') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'حقوق') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'دستمزد') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'آموزش کارکنان')) {
            $category = 'human_resources';
        } elseif (str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'غذا') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'پذیرایی') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'ناهار') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'کترینگ') ||
                   str_contains($vendorName . ' ' . $originName . ' ' . $allText, 'رستوران')) {
            $category = 'staff_meals';
        } elseif (str_contains($allText, 'لبنی') ||
                   str_contains($allText, 'شیر') ||
                   str_contains($allText, 'ماست') ||
                   str_contains($allText, 'دوغ') ||
                   str_contains($allText, 'پنیر')) {
            $category = 'dairy';
        } elseif (str_contains($allText, 'سوپر') ||
                   str_contains($allText, 'سوپرمارکت') ||
                   str_contains($allText, 'کالاهای اساسی') ||
                   str_contains($allText, 'خواروبار') ||
                   str_contains($vendorName . ' ' . $originName, 'سوپر')) {
            $category = 'grocery';
        }
        
        if ($category) {
            $this->entries[$index]['category'] = $category;
        }

        $this->refreshCategoryStatus($index, true);
    }

    private function generateDescriptionFromExtractedData(array $extractedData): string
    {
        return $this->generateTableDescription($extractedData);
    }

    private function generateTableDescription(array $extractedData): string
    {
        $documentType = trim($extractedData['document_type_fa'] ?? $extractedData['origin_of_document_fa'] ?? '');
        $documentType = $documentType !== '' ? $documentType : 'صورتحساب کالا و خدمات';

        $financialSummary = $extractedData['financial_summary'] ?? [];
        $totalCandidate = $financialSummary['final_amount_in_rial_numerical']
            ?? $financialSummary['subtotal_in_rial_numerical']
            ?? ($extractedData['total_amount'] ?? null);
        $totalAmount = $totalCandidate !== null ? $this->parseAmount((string) $totalCandidate) : null;

        $items = $extractedData['items_details']['item_structure'] ?? [];
        if (empty($items) && ! empty($extractedData['line_items'])) {
            $items = array_map(static function (array $item): array {
                return [
                    'product_or_service_description_fa' => $item['description'] ?? $item['item_description_fa'] ?? null,
                    'quantity_numerical' => $item['quantity'] ?? null,
                ];
            }, $extractedData['line_items']);
        }

        $itemCount = count($items);
        $itemPreviewParts = [];

        foreach (array_slice($items, 0, 3) as $item) {
            $description = trim($item['product_or_service_description_fa'] ?? '');
            if ($description === '') {
                continue;
            }

            $quantityRaw = $item['quantity_numerical'] ?? null;
            $quantity = $quantityRaw !== null ? $this->parseAmount((string) $quantityRaw) : null;

            if ($quantity !== null && $quantity > 0) {
                $formattedQty = fmod($quantity, 1.0) === 0.0
                    ? (string) (int) $quantity
                    : rtrim(rtrim(number_format($quantity, 2), '0'), '.');
                $description .= ' × ' . $formattedQty;
            }

            $itemPreviewParts[] = Str::limit($description, 40);
        }

        if ($itemCount > count($itemPreviewParts)) {
            $itemPreviewParts[] = 'و ' . ($itemCount - count($itemPreviewParts)) . ' قلم دیگر';
        }

        $vendorName = $extractedData['seller_info']['name_fa']
            ?? $extractedData['vendor_name']
            ?? ($extractedData['origin_of_document_fa'] ?? null);

        $invoiceNumber = $extractedData['invoice_serial_number']
            ?? $extractedData['invoice_number']
            ?? $extractedData['document_info']['number'] ?? null;

        $date = $extractedData['date_jalali']
            ?? $extractedData['dates']['jalali']
            ?? null;

        $segments = array_filter([
            $documentType,
            ! empty($itemPreviewParts) ? 'اقلام: ' . implode('، ', $itemPreviewParts) : null,
            $totalAmount !== null ? 'مبلغ نهایی: ' . number_format($totalAmount) . ' ریال' : null,
            $vendorName ? 'فروشنده: ' . $vendorName : null,
            $invoiceNumber ? 'شماره فاکتور: ' . $invoiceNumber : null,
            $date ? 'تاریخ: ' . $date : null,
        ]);

        return Str::limit(implode(' | ', $segments), 220);
    }
    private function convertJalaliToGregorian(string $jalaliDate): string
    {
        try {
            // Handle incomplete dates like "1403//"
            if (strpos($jalaliDate, '//') !== false || empty($jalaliDate)) {
                return Verta::now()->format('Y-m-d H:i');
            }
            
            // Clean the date string
            $jalaliDate = trim($jalaliDate);
            
            // Parse jalali date (format: YYYY/MM/DD or YYYY/MM/DD HH:MM:SS)
            $parts = explode(' ', $jalaliDate);
            $datePart = $parts[0];
            $timePart = $parts[1] ?? '00:00:00';
            
            $dateComponents = explode('/', $datePart);
            if (count($dateComponents) >= 3) {
                $year = (int) $dateComponents[0];
                $month = (int) $dateComponents[1];
                $day = (int) $dateComponents[2];
                
                // Validate date components
                if ($year < 1300 || $year > 1500 || $month < 1 || $month > 12 || $day < 1 || $day > 31) {
                    \Log::warning('Invalid jalali date components', [
                        'year' => $year,
                        'month' => $month,
                        'day' => $day,
                        'original_date' => $jalaliDate
                    ]);
                    return Verta::now()->format('Y-m-d H:i');
                }
                
                // Create Verta instance and convert to gregorian
                $verta = new Verta();
                $verta->setDate($year, $month, $day);
                $gregorian = $verta->toCarbon();
                
                // Add time if provided
                if ($timePart !== '00:00:00') {
                    $timeComponents = explode(':', $timePart);
                    if (count($timeComponents) >= 2) {
                        $gregorian->setTime(
                            (int) $timeComponents[0],
                            (int) $timeComponents[1],
                            (int) ($timeComponents[2] ?? 0)
                        );
                    }
                }
                
                return $gregorian->format('Y-m-d H:i');
            }
        } catch (\Exception $e) {
            // Fallback to current date
        }
        
        return Verta::now()->format('Y-m-d H:i');
    }

    protected function resolveStructuredExtraction(int $index, ?array $structuredPayload = null): ?array
    {
        if ($structuredPayload) {
            return $structuredPayload;
        }

        $state = $this->smartEntriesState[$index] ?? [];

        if (isset($state['structured']) && is_array($state['structured'])) {
            return $state['structured'];
        }

        if (isset($state['raw_payload']['structured']) && is_array($state['raw_payload']['structured'])) {
            return $state['raw_payload']['structured'];
        }

        if (isset($state['raw_payload']['items_details']) || isset($state['raw_payload']['financial_summary'])) {
            return $state['raw_payload'];
        }

        if (isset($state['raw_payload']['raw_payload']) && is_array($state['raw_payload']['raw_payload'])) {
            $nested = $state['raw_payload']['raw_payload'];

            if (isset($nested['structured']) && is_array($nested['structured'])) {
                return $nested['structured'];
            }

            if (isset($nested['items_details']) || isset($nested['financial_summary'])) {
                return $nested;
            }

            if (isset($nested['content']) && is_string($nested['content'])) {
                $decoded = $this->decodeLegacyExtractionContent($nested['content'], $index);
                if ($decoded) {
                    return $decoded;
                }
            }
        }

        if (isset($state['raw_payload']['content']) && is_string($state['raw_payload']['content'])) {
            return $this->decodeLegacyExtractionContent($state['raw_payload']['content'], $index);
        }

        if (isset($state['extracted_data'])) {
            $legacy = $state['extracted_data'];

            if (isset($legacy['structured']) && is_array($legacy['structured'])) {
                return $legacy['structured'];
            }

            if (isset($legacy['raw_payload']['content']) && is_string($legacy['raw_payload']['content'])) {
                return $this->decodeLegacyExtractionContent($legacy['raw_payload']['content'], $index);
            }

            if (isset($legacy['items_details']) || isset($legacy['financial_summary'])) {
                return $legacy;
            }
        }

        return null;
    }

    protected function decodeLegacyExtractionContent(string $content, int $index): ?array
    {
        $cleanContent = preg_replace('/```json\s*/', '', $content);
        $cleanContent = preg_replace('/```$/', '', $cleanContent ?? '');
        $cleanContent = trim($cleanContent ?? '');

        $openBraces = substr_count($cleanContent, '{');
        $closeBraces = substr_count($cleanContent, '}');

        if ($openBraces > $closeBraces) {
            $missingBraces = $openBraces - $closeBraces;
            $lastCompleteObject = strrpos($cleanContent, '}');

            if ($lastCompleteObject !== false) {
                $cleanContent = substr($cleanContent, 0, $lastCompleteObject + 1);
            }

            $cleanContent .= str_repeat('}', $missingBraces);
        }

        $openSquare = substr_count($cleanContent, '[');
        $closeSquare = substr_count($cleanContent, ']');
        if ($openSquare > $closeSquare) {
            $cleanContent .= str_repeat(']', $openSquare - $closeSquare);
        }

        $cleanContent = preg_replace('/,(\s*[}\]])/u', '$1', $cleanContent) ?? $cleanContent;

        $decoded = json_decode($cleanContent, true);

        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        \Log::error('JSON decode error', [
            'index' => $index,
            'error' => json_last_error_msg(),
            'content' => substr($cleanContent, 0, 500),
        ]);

        return null;
    }

    protected function buildExtractionSummary(SmartInvoiceExtraction $result, array $structured): array
    {
        $financial = $structured['financial_summary'] ?? [];
        $sellerInfo = $structured['seller_info'] ?? [];
        $documentInfo = $structured['document_info'] ?? [];
        $paymentDetails = $structured['payment_and_banking_details'] ?? [];
        $dates = $structured['dates'] ?? [];

        $total = $financial['final_amount_in_rial_numerical'] ?? $financial['subtotal_in_rial_numerical'] ?? $result->totalAmount;
        $vendor = $sellerInfo['name_fa'] ?? ($structured['origin_of_document_fa'] ?? $result->vendorName);
        $invoiceNumber = $structured['invoice_serial_number'] ?? ($documentInfo['number'] ?? null);
        $reference = $paymentDetails['reference_number_or_sheba'] ?? ($structured['reference_number'] ?? null);

        $issuedAt = $structured['date_jalali'] ?? ($dates['jalali'] ?? null);
        if (! $issuedAt && $result->issuedAt) {
            $issuedAt = Verta::instance($result->issuedAt)->format('Y-m-d H:i');
        }

        $currency = $financial['raw_currency'] ?? $result->currency ?? 'IRR';

        return [
            'total_amount' => $total,
            'vendor_name' => $vendor,
            'invoice_number' => $invoiceNumber,
            'reference_number' => $reference,
            'issued_at' => $issuedAt,
            'currency' => $currency,
        ];
    }

    protected function extractStructuredFromRaw(mixed $rawPayload): array
    {
        if (is_string($rawPayload)) {
            $decoded = $this->decodeLegacyExtractionContent($rawPayload, -1);
            if (is_array($decoded)) {
                $rawPayload = $decoded;
            }
        }

        if (! is_array($rawPayload)) {
            return [];
        }

        if (isset($rawPayload['structured']) && is_array($rawPayload['structured'])) {
            return $rawPayload['structured'];
        }

        if (isset($rawPayload['items_details']) || isset($rawPayload['financial_summary'])) {
            return $rawPayload;
        }

        return [];
    }

    public function applyExtractedData(int $index, ?array $structuredPayload = null): void
    {
        $extractedData = $this->resolveStructuredExtraction($index, $structuredPayload);

        if (! $extractedData) {
            \Log::warning('No valid extracted data found', ['index' => $index]);
            return;
        }

        $this->smartEntriesState[$index]['structured'] = $extractedData;
        if (empty($this->smartEntriesState[$index]['summary'])) {
            $financial = $extractedData['financial_summary'] ?? [];
            $seller = $extractedData['seller_info'] ?? [];
            $documentInfo = $extractedData['document_info'] ?? [];
            $paymentDetails = $extractedData['payment_and_banking_details'] ?? [];
            $dates = $extractedData['dates'] ?? [];

            $this->smartEntriesState[$index]['summary'] = [
                'total_amount' => $financial['final_amount_in_rial_numerical'] ?? $financial['subtotal_in_rial_numerical'] ?? null,
                'vendor_name' => $seller['name_fa'] ?? ($extractedData['origin_of_document_fa'] ?? null),
                'invoice_number' => $extractedData['invoice_serial_number'] ?? ($documentInfo['number'] ?? null),
                'reference_number' => $paymentDetails['reference_number_or_sheba'] ?? ($extractedData['reference_number'] ?? null),
                'issued_at' => $extractedData['date_jalali'] ?? ($dates['jalali'] ?? null),
                'currency' => $financial['raw_currency'] ?? 'IRR',
            ];
        }

        // Apply extracted data to form fields
        // Use total_amount first (includes tax), then final_amount from financial summary
        if (isset($extractedData['total_amount']) && $extractedData['total_amount']) {
            $amount = $this->parseAmount($extractedData['total_amount']);
            $this->entries[$index]['amount'] = $amount;
            \Log::info('Using total_amount from root', ['amount' => $amount]);
        } elseif (isset($extractedData['financial_summary']['final_amount_in_rial_numerical']) && $extractedData['financial_summary']['final_amount_in_rial_numerical']) {
            $amount = $this->parseAmount($extractedData['financial_summary']['final_amount_in_rial_numerical']);
            $this->entries[$index]['amount'] = $amount;
            \Log::info('Using final_amount from financial_summary', ['amount' => $amount]);
        }

        // Set transaction date from jalali date
        if (isset($extractedData['date_jalali']) && $extractedData['date_jalali']) {
            try {
                $jalaliDate = $extractedData['date_jalali'];
                \Log::info('Processing Jalali date', ['jalali_date' => $jalaliDate]);
                
                // If date is incomplete, use current jalali date
                if (strlen($jalaliDate) < 8 || strpos($jalaliDate, '//') !== false || empty($jalaliDate)) {
                    $this->entries[$index]['transaction_date'] = Verta::now()->format('Y-m-d H:i');
                } else {
                    // Parse jalali date and convert to gregorian for form
                    $convertedDate = $this->convertJalaliToGregorian($jalaliDate);
                    $this->entries[$index]['transaction_date'] = $convertedDate;
                    \Log::info('Date converted successfully', [
                        'jalali_date' => $jalaliDate,
                        'converted_date' => $convertedDate
                    ]);
                }
            } catch (\Exception $e) {
                \Log::warning('Date conversion failed', [
                    'jalali_date' => $extractedData['date_jalali'] ?? null,
                    'error' => $e->getMessage()
                ]);
                $this->entries[$index]['transaction_date'] = Verta::now()->format('Y-m-d H:i');
            }
        } else {
            // Use current jalali date if no date extracted
            $this->entries[$index]['transaction_date'] = Verta::now()->format('Y-m-d H:i');
        }

        // Generate auto serial number with branch name
        $this->generateAutoSerialNumber($index, $extractedData);

        // Set description based on seller info or origin
        $vendorName = $extractedData['seller_info']['name_fa'] ?? 
                     $extractedData['origin_of_document_fa'] ?? 
                     null;
                     
        // Always generate a concise summary for the transaction list
        $summaryDescription = $this->generateTableDescription($extractedData);
        $this->entries[$index]['description'] = $summaryDescription;

        \Log::info('Generated smart invoice summary', [
            'description_length' => strlen($summaryDescription),
            'has_vendor' => ! empty($vendorName)
        ]);

        // Auto-categorize based on extracted data
        $this->autoCategorizeTransaction($index, $extractedData);

        // Calculate total from line items (new structure) - only if amount not already set
        if (!isset($this->entries[$index]['amount']) && isset($extractedData['items_details']['item_structure']) && is_array($extractedData['items_details']['item_structure'])) {
            $calculatedTotal = 0;
            foreach ($extractedData['items_details']['item_structure'] as $item) {
                if (isset($item['total_price_in_rial_numerical'])) {
                    $amount = $this->parseAmount($item['total_price_in_rial_numerical']);
                    if ($amount > 0) {
                        $calculatedTotal += $amount;
                    }
                }
            }

        // Add taxes and fees to the total
        if (isset($extractedData['financial_summary']['vat_and_tolls_amount_in_rial_numerical'])) {
            $taxAmount = $this->parseAmount($extractedData['financial_summary']['vat_and_tolls_amount_in_rial_numerical']);
            if ($taxAmount > 0) {
                $calculatedTotal += $taxAmount;
            }
        }
        
        // Add transport costs
        if (isset($extractedData['financial_summary']['transport_total_in_rial_numerical'])) {
            $transportAmount = $this->parseAmount($extractedData['financial_summary']['transport_total_in_rial_numerical']);
            if ($transportAmount > 0) {
                $calculatedTotal += $transportAmount;
            }
        }
        
        // Add service costs
        if (isset($extractedData['financial_summary']['service_total_in_rial_numerical'])) {
            $serviceAmount = $this->parseAmount($extractedData['financial_summary']['service_total_in_rial_numerical']);
            if ($serviceAmount > 0) {
                $calculatedTotal += $serviceAmount;
            }
        }

            if ($calculatedTotal > 0) {
                $this->entries[$index]['amount'] = $calculatedTotal;
                \Log::info('Using calculated total from line items', ['amount' => $calculatedTotal]);
            }
        }
        // Fallback to old structure
        elseif (isset($extractedData['line_items']) && is_array($extractedData['line_items'])) {
            $calculatedTotal = 0;
            foreach ($extractedData['line_items'] as $item) {
                if (isset($item['total'])) {
                    $amount = $this->parseAmount($item['total']);
                    if ($amount > 0) {
                        $calculatedTotal += $amount;
                    }
                }
            }

            if ($calculatedTotal > 0) {
                $this->entries[$index]['amount'] = $calculatedTotal;
            }
        }

        // Use financial summary if available (final amount with tax first, then subtotal)
        if (!isset($this->entries[$index]['amount'])) {
            if (isset($extractedData['financial_summary']['final_amount_in_rial_numerical'])) {
                $finalAmount = $this->parseAmount($extractedData['financial_summary']['final_amount_in_rial_numerical']);
                if ($finalAmount > 0) {
                    $this->entries[$index]['amount'] = $finalAmount;
                    \Log::info('Using final amount with tax', ['amount' => $finalAmount]);
                }
            } elseif (isset($extractedData['financial_summary']['subtotal_in_rial_numerical'])) {
                $subtotalAmount = $this->parseAmount($extractedData['financial_summary']['subtotal_in_rial_numerical']);
                if ($subtotalAmount > 0) {
                    $this->entries[$index]['amount'] = $subtotalAmount;
                    \Log::info('Using subtotal amount', ['amount' => $subtotalAmount]);
                }
            }
        }

        // Verify amount consistency and show warning if needed
        $this->verifyAmountConsistency($index, $extractedData);

        $this->dispatch('$refresh');
    }

    public function runSmartExtraction(int $index, SmartInvoiceService $smartInvoiceService): void
    {
        if (! isset($this->entries[$index])) {
            return;
        }

        $entry = $this->entries[$index];
        $invoice = $entry['invoice_attachment'] ?? null;
        $receipt = $entry['receipt_attachment'] ?? null;

        if (! $invoice) {
            $message = __('برای استفاده از تکمیل هوشمند، بارگذاری فاکتور الزامی است.');
            $this->addError('entries.' . $index . '.invoice_attachment', $message);
            $this->smartEntriesState[$index] = [
                'status' => 'error',
                'message' => $message,
                'confidence' => null,
            ];
            return;
        }

        $serialNumber = $this->ensureSerialNumberAssigned($index);
        $this->applySerialToAttachments($index, $serialNumber);

        $this->smartEntriesState[$index] = [
            'status' => 'loading',
            'message' => null,
            'confidence' => null,
        ];

        try {
            $result = $smartInvoiceService->extractFromUploads($invoice, $receipt, [
                'ledger_id' => $this->ledger->id,
                'transaction_type' => $entry['type'] ?? PettyCashTransaction::TYPE_EXPENSE,
                'existing_amount' => $entry['amount'] ?? null,
            ]);

            // Force populate form fields with extracted data
            if ($result->totalAmount !== null) {
                $this->entries[$index]['amount'] = $result->totalAmount;
            }

            if ($result->issuedAt) {
                $this->entries[$index]['transaction_date'] = Verta::instance($result->issuedAt)->format('Y-m-d H:i');
            }

            if ($result->referenceNumber) {
                $this->entries[$index]['reference_number'] = $result->referenceNumber;
            } elseif ($result->paymentReference && empty($this->entries[$index]['reference_number'])) {
                $this->entries[$index]['reference_number'] = $result->paymentReference;
            }

            if ($result->vendorName && empty($this->entries[$index]['description'])) {
                $this->entries[$index]['description'] = __('smart_invoice.default_description', ['vendor' => $result->vendorName]);
            }

            // Calculate total from line items if available
            if (!empty($result->lineItems)) {
                $calculatedTotal = 0;
                foreach ($result->lineItems as $item) {
                    if (isset($item['total']) && is_numeric($item['total'])) {
                        $calculatedTotal += (float) $item['total'];
                    }
                }
                
                // If calculated total is different from extracted total, use calculated
                if ($calculatedTotal > 0 && $calculatedTotal != $result->totalAmount) {
                    $this->entries[$index]['amount'] = $calculatedTotal;
                }
            }

            $rawPayload = $result->rawPayload ?? [];
            $structuredSource = $rawPayload['structured']
                ?? ($rawPayload['raw_payload']['structured'] ?? ($rawPayload['raw_payload'] ?? $rawPayload));

            $structuredPayload = $this->extractStructuredFromRaw($structuredSource);
            $summary = $this->buildExtractionSummary($result, $structuredPayload);

        $analyticsIssues = [];
        if (isset($result->analytics['validation']['issues']) && is_array($result->analytics['validation']['issues'])) {
            $analyticsIssues = $result->analytics['validation']['issues'];
        }

        $structuredIssues = [];
        if (isset($structuredPayload['analytics']['validation']['issues']) && is_array($structuredPayload['analytics']['validation']['issues'])) {
            $structuredIssues = $structuredPayload['analytics']['validation']['issues'];
        }

        $translatedValidation = $this->translateValidationIssues(! empty($analyticsIssues) ? $analyticsIssues : $structuredIssues);

            $extractedDataForView = array_merge($summary, [
                'items_details' => $structuredPayload['items_details'] ?? [],
                'financial_summary' => $structuredPayload['financial_summary'] ?? [],
                'line_items' => $structuredPayload['line_items'] ?? [],
            'analytics' => array_replace_recursive($result->analytics ?? [], [
                'validation' => [
                    'issues' => $translatedValidation,
                ],
            ]),
                'raw_payload' => $rawPayload,
            ]);

        if (isset($extractedDataForView['analytics']['validation'])) {
            $extractedDataForView['analytics']['validation']['issues'] = $translatedValidation;
        }

            $existingState = $this->smartEntriesState[$index] ?? [];
            $this->smartEntriesState[$index] = array_merge($existingState, [
                'status' => 'success',
                'message' => __('smart_invoice.extraction_success'),
                'confidence' => $result->confidence,
                'summary' => $summary,
                'structured' => $structuredPayload,
                'raw_payload' => $structuredSource,
                'debug_payload' => $rawPayload,
                'extracted_data' => $extractedDataForView,
            ]);

            // Auto-apply extracted data to form
            $this->applyExtractedData($index, $structuredPayload);

            // Also dispatch a specific event to update the form
            $this->dispatch('smart-invoice-extracted', [
                'index' => $index,
                'data' => [
                    'amount' => $this->entries[$index]['amount'] ?? null,
                    'transaction_date' => $this->entries[$index]['transaction_date'] ?? null,
                    'reference_number' => $this->entries[$index]['reference_number'] ?? null,
                    'description' => $this->entries[$index]['description'] ?? null,
                ]
            ]);

            $meta = $this->entries[$index]['meta'] ?? [];
            $metaAnalytics = is_array($result->analytics ?? null) ? $result->analytics : [];
            if (isset($metaAnalytics['validation'])) {
                $metaAnalytics['validation']['issues'] = $translatedValidation;
            }

            $meta['smart_invoice'] = array_merge($meta['smart_invoice'] ?? [], $result->asMeta($smartInvoiceService->analyticsEnabled()), [
                'raw_payload' => $result->rawPayload,
                'analytics' => array_replace_recursive($metaAnalytics, [
                    'validation' => [
                        'issues' => $translatedValidation,
                    ],
                ]),
            ]);
            $this->entries[$index]['meta'] = $meta;
        } catch (SmartInvoiceException $exception) {
            $this->smartEntriesState[$index] = [
                'status' => 'error',
                'message' => $exception->getMessage(),
                'confidence' => null,
            ];
            $this->addError('entries.' . $index . '.invoice_attachment', $exception->getMessage());
        } catch (\Throwable $throwable) {
            report($throwable);
            $message = $throwable->getMessage() ?: __('smart_invoice.unexpected_error');
            $this->smartEntriesState[$index] = [
                'status' => 'error',
                'message' => $message,
                'confidence' => null,
            ];
            $this->addError('entries.' . $index . '.invoice_attachment', $message);
        }
    }

    public function approveTransactionFromTable($payload = null): void
    {
        if ($this->belongsToAnotherLedger($payload)) {
            return;
        }

        $transactionId = $this->extractTransactionId($payload);

        $service = app(PettyCashService::class);
        $transaction = $transactionId
            ? $this->ledger->transactions()->find($transactionId)
            : null;
        $user = Auth::user();

        if (! $transaction || ! $user) {
            return;
        }

        if (! $this->userCanManageTransactions()) {
            session()->flash('error', __('شما مجاز به تایید تراکنش‌ها نیستید.'));
            return;
        }

        $service->approveTransaction($transaction, $user);
        $this->dispatch('petty-cash-transaction-saved');
        $this->initializeDefaults();
        $this->refreshLedgerSnapshot();
    }

    public function deleteTransaction($payload = null): void
    {
        if ($this->belongsToAnotherLedger($payload)) {
            return;
        }

        $transactionId = $this->extractTransactionId($payload);

        $service = app(PettyCashService::class);
        $transaction = $transactionId
            ? $this->ledger->transactions()->find($transactionId)
            : null;

        if (! $transaction) {
            return;
        }

        if (! $this->userCanManageTransactions()) {
            session()->flash('error', __('شما مجاز به حذف تراکنش‌ها نیستید.'));
            return;
        }

        $service->deleteTransaction($transaction);
        $this->dispatch('petty-cash-transaction-deleted');
        $this->initializeDefaults();
        $this->refreshLedgerSnapshot();
    }

    public function render()
    {
        return view('livewire.petty-cash.transaction-form', [
            'ledger' => $this->ledger,
            'smartEntriesState' => $this->smartEntriesState,
        ]);
    }

    protected function parseDateInput(string $value): Carbon
    {
        $value = $this->normalizeJalaliInput($value);

        try {
            return Verta::parseFormat('Y-m-d H:i', $value)->toCarbon();
        } catch (\Throwable $throwable) {
            return Carbon::parse($value);
        }
    }

    protected function syncEntryAttachments(PettyCashTransaction $transaction, array $entry, int $userId, int $entryIndex): void
    {
        $serial = $this->ensureSerialNumberAssigned($entryIndex);
        $this->applySerialToAttachments($entryIndex, $serial);

        $sourceEntry = $this->entries[$entryIndex] ?? $entry;

        if (isset($sourceEntry['invoice_attachment']) && $sourceEntry['invoice_attachment']) {
            $transaction->clearMediaCollection('invoice');
            $transaction->addMedia($sourceEntry['invoice_attachment']->getRealPath())
                ->usingFileName($sourceEntry['invoice_attachment']->getClientOriginalName())
                ->withCustomProperties([
                    'uploaded_by' => $userId,
                ])
                ->toMediaCollection('invoice');
        }

        if (isset($sourceEntry['receipt_attachment']) && $sourceEntry['receipt_attachment']) {
            $transaction->clearMediaCollection('bank_receipt');
            $transaction->addMedia($sourceEntry['receipt_attachment']->getRealPath())
                ->usingFileName($sourceEntry['receipt_attachment']->getClientOriginalName())
                ->withCustomProperties([
                    'uploaded_by' => $userId,
                ])
                ->toMediaCollection('bank_receipt');
        }
    }

    protected function initializeDefaults(): void
    {
        $this->transaction = null;
        $this->editingTransactionId = null;
        $this->serialCursor = null;
        $this->entries = [
            $this->makeEmptyEntry(),
        ];
        $this->smartEntriesState = [
            $this->makeSmartEntryState(),
        ];

        $this->ensureTrailingEmptyRow();
        $this->syncSmartEntriesState();
    }

    protected function makeEmptyEntry(): array
    {
        return [
            'type' => PettyCashTransaction::TYPE_EXPENSE,
            'status' => PettyCashTransaction::STATUS_SUBMITTED,
            'transaction_date' => Verta::now()->format('Y-m-d H:i'),
            'amount' => null,
            'currency' => 'IRR',
            'reference_number' => null,
            'description' => null,
            'category' => null,
            'invoice_attachment' => null,
            'receipt_attachment' => null,
            'manager_note' => null,
            'meta' => [],
        ];
    }

    protected function makeSmartEntryState(): array
    {
        return [
            'status' => 'idle',
            'message' => null,
            'confidence' => null,
            'summary' => null,
            'structured' => null,
            'raw_payload' => null,
            'extracted_data' => null,
            'category_status' => 'unknown',
            'category_message' => null,
        ];
    }

    protected function syncSmartEntriesState(): void
    {
        $synced = [];

        foreach ($this->entries as $index => $_entry) {
            $synced[$index] = $this->smartEntriesState[$index] ?? $this->makeSmartEntryState();
        }

        $this->smartEntriesState = $synced;

        foreach (array_keys($this->entries) as $index) {
            $this->refreshCategoryStatus($index);
        }
    }

    protected function ensureTrailingEmptyRow(): void
    {
        if (empty($this->entries)) {
            $this->entries = [$this->makeEmptyEntry()];
            return;
        }

        $lastEntry = $this->entries[array_key_last($this->entries)];

        if ($this->entryLooksComplete($lastEntry)) {
            $this->entries[] = $this->makeEmptyEntry();
        }

        $this->entries = array_values($this->entries);
    }

    protected function entryLooksComplete(?array $entry): bool
    {
        if (! is_array($entry)) {
            return false;
        }

        $amount = $entry['amount'] ?? null;
        $date = $entry['transaction_date'] ?? null;

        return $amount !== null
            && $amount !== ''
            && is_numeric($amount)
            && (float) $amount > 0
            && ! empty($date);
    }

    protected function resolveEntries()
    {
        return collect($this->entries)
            ->filter(fn ($entry) => $this->entryLooksComplete($entry))
            ->map(function ($entry, $originalIndex) {
                $meta = $entry['meta'] ?? [];
                if (! is_array($meta)) {
                    $meta = [];
                }

                $note = trim((string) ($entry['manager_note'] ?? ''));
                if ($note !== '') {
                    $meta['approval_note'] = $note;
                } else {
                    unset($meta['approval_note']);
                }

                return [
                    'type' => $entry['type'] ?? PettyCashTransaction::TYPE_EXPENSE,
                    'status' => $entry['status'] ?? PettyCashTransaction::STATUS_SUBMITTED,
                    'transaction_date' => $entry['transaction_date'],
                    'amount' => $this->parseAmount((string) ($entry['amount'] ?? 0)),
                    'currency' => $entry['currency'] ?? 'IRR',
                    'reference_number' => $entry['reference_number'] ?? null,
                    'description' => $entry['description'] ?? null,
                    'category' => $entry['category'] ?? null,
                    'invoice_attachment' => $entry['invoice_attachment'] ?? null,
                    'receipt_attachment' => $entry['receipt_attachment'] ?? null,
                    'meta' => $meta,
                    '__original_index' => $originalIndex,
                ];
            })
            ->values();
    }

    protected function buildPayload(array $entry): array
    {
        $meta = $entry['meta'] ?? [];
        if (! is_array($meta)) {
            $meta = [];
        }

        $note = trim((string) ($entry['manager_note'] ?? ''));
        if ($note !== '') {
            $meta['approval_note'] = $note;
        } else {
            unset($meta['approval_note']);
        }

        return [
            'amount' => $this->parseAmount((string) ($entry['amount'] ?? 0)),
            'currency' => $entry['currency'] ?? 'IRR',
            'transaction_date' => $this->parseDateInput($entry['transaction_date']),
            'reference_number' => $entry['reference_number'] ?? null,
            'description' => $entry['description'] ?? null,
            'status' => $entry['status'] ?? PettyCashTransaction::STATUS_SUBMITTED,
            'category' => $entry['category'] ?? null,
            'meta' => ! empty($meta) ? $meta : null,
        ];
    }

    protected function applyChargeOrigin(array $payload, string $origin): array
    {
        $payload['charge_origin'] = $origin;

        $meta = $payload['meta'] ?? [];
        if (! is_array($meta)) {
            $meta = [];
        }

        $chargeMeta = $meta['charge_request'] ?? [];
        if (! is_array($chargeMeta)) {
            $chargeMeta = [];
        }

        $chargeMeta['source'] = $origin;
        $meta['charge_request'] = $chargeMeta;

        $payload['meta'] = $meta;

        return $payload;
    }

    protected function rules(): array
    {
        return [
            'entries' => 'array|min:1',
            'entries.*.type' => 'nullable|string|in:charge,expense,adjustment',
            'entries.*.status' => 'nullable|string|in:draft,submitted,approved,rejected,needs_changes,under_review',
            'entries.*.transaction_date' => 'nullable|string',
            'entries.*.amount' => 'nullable|numeric|min:0',
            'entries.*.currency' => 'nullable|string|in:IRR',
            'entries.*.reference_number' => 'nullable|string|max:100',
            'entries.*.description' => 'nullable|string|max:2000',
            'entries.*.category' => 'nullable|string|max:100',
            'entries.*.invoice_attachment' => 'nullable|file|max:4096',
            'entries.*.receipt_attachment' => 'nullable|file|max:4096',
            'entries.*.manager_note' => 'nullable|string|max:1000',
            'entries.*.meta' => 'nullable|array',
        ];
    }

    protected function extractTransactionId($payload): ?int
    {
        if (is_array($payload)) {
            if (isset($payload['id'])) {
                return (int) $payload['id'];
            }

            if (isset($payload['transaction_id'])) {
                return (int) $payload['transaction_id'];
            }

            if (isset($payload['transactionId'])) {
                return (int) $payload['transactionId'];
            }

            if (isset($payload['TransactionId'])) {
                return (int) $payload['TransactionId'];
            }

            if (count($payload) === 1 && isset($payload[0])) {
                return is_numeric($payload[0]) ? (int) $payload[0] : null;
            }
        }

        if (is_object($payload)) {
            if (isset($payload->id)) {
                return (int) $payload->id;
            }

            if (isset($payload->transaction_id)) {
                return (int) $payload->transaction_id;
            }

            if (isset($payload->transactionId)) {
                return (int) $payload->transactionId;
            }

            if (isset($payload->TransactionId)) {
                return (int) $payload->TransactionId;
            }

            if (method_exists($payload, 'toArray')) {
                return $this->extractTransactionId($payload->toArray());
            }
        }

        if (is_numeric($payload)) {
            return (int) $payload;
        }

        return null;
    }

    protected function belongsToAnotherLedger($payload): bool
    {
        if (is_array($payload) && isset($payload['ledger_id'])) {
            return (int) $payload['ledger_id'] !== $this->ledger->id;
        }

        if (is_array($payload) && isset($payload['ledgerId'])) {
            return (int) $payload['ledgerId'] !== $this->ledger->id;
        }

        if (is_object($payload) && isset($payload->ledger_id)) {
            return (int) $payload->ledger_id !== $this->ledger->id;
        }

        if (is_object($payload) && isset($payload->ledgerId)) {
            return (int) $payload->ledgerId !== $this->ledger->id;
        }

        return false;
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

    protected function userCanEditTransaction(PettyCashTransaction $transaction): bool
    {
        return $this->userCanManageTransactions() || $this->userCanReviseTransaction($transaction);
    }

    protected function refreshLedgerSnapshot(): void
    {
        if ($this->ledger->exists) {
            $this->ledger = $this->ledger->fresh();
        }

        $metrics = app(\App\Services\PettyCash\PettyCashService::class)->getLedgerSnapshot($this->ledger);

        foreach ($metrics as $key => $value) {
            $this->ledger->setAttribute($key, $value);
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

}
