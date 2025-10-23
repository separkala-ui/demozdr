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
        return [
            'vegetables' => 'تره بار',
            'protein' => 'پروتئینی',
            'transport' => 'حمل و نقل',
            'repairs' => 'ملزومات تعمیرات',
            'cleaning' => 'مواد شوینده',
            'utilities' => 'قبوض آب و برق',
            'fuel' => 'سوخت',
            'maintenance' => 'نگهداری و تعمیرات',
            'supplies' => 'لوازم مصرفی',
            'marketing' => 'تبلیغات و بازاریابی',
            'insurance' => 'بیمه',
            'rent' => 'اجاره',
            'equipment' => 'تجهیزات',
            'furniture' => 'مبلمان',
            'electronics' => 'الکترونیک',
            'security' => 'امنیت',
            'waste' => 'دفع زباله',
            'other' => 'سایر',
        ];
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
        $rules = $this->rules();

        foreach ($this->entries as $index => $entry) {
            $ruleKey = 'entries.' . $index . '.invoice_attachment';
            $receiptRuleKey = 'entries.' . $index . '.receipt_attachment';

            if ($this->entryLooksComplete($entry)) {
                $rules[$ruleKey] = 'required|file|max:4096';
                $rules[$receiptRuleKey] = 'required|file|max:4096';
            } else {
                $rules[$ruleKey] = 'nullable|file|max:4096';
                $rules[$receiptRuleKey] = 'nullable|file|max:4096';
            }
        }

        $this->validate($rules);

        $user = Auth::user();

        if (! $user) {
            abort(403);
        }

        $resolvedEntries = $this->resolveEntries();

        if ($resolvedEntries->isEmpty()) {
            $this->addError('entries', __('لطفاً حداقل یک ردیف را تکمیل کنید.'));
            return;
        }

        $processed = 0;

        foreach ($resolvedEntries as $index => $entry) {
            if ($this->editingTransactionId && $index === 0) {
                $transaction = $this->ledger->transactions()->find($this->editingTransactionId);

                if (! $transaction) {
                    continue;
                }

                if (! $this->userCanEditTransaction($transaction)) {
                    $this->addError('entries.' . $index . '.type', __('شما مجاز به ویرایش این تراکنش نیستید.'));
                    return;
                }

                $payload = $this->buildPayload($entry);

                if (! $this->userCanManageTransactions()) {
                    $payload['status'] = PettyCashTransaction::STATUS_SUBMITTED;
                }

                $transaction->fill($payload);

                if (($payload['status'] ?? null) === PettyCashTransaction::STATUS_APPROVED) {
                    if (! $user->hasRole(['Superadmin', 'Admin'])) {
                        $this->addError('entries.' . $index . '.status', __('فقط مدیر ارشد و مدیر می‌توانند تراکنش‌ها را تایید کنند.'));
                        return;
                    }

                    $transaction = $service->approveTransaction($transaction, $user);
                } else {
                    $transaction->save();
                }

                $this->syncEntryAttachments($transaction, $entry, $user->id);
                $processed++;

                continue;
            }

            if ($entry['type'] === PettyCashTransaction::TYPE_CHARGE) {
                if (! $user->hasRole(['Superadmin', 'Admin'])) {
                    $this->addError('entries.' . $index . '.type', __('فقط مدیر ارشد و مدیر می‌توانند تنخواه را شارژ کنند.'));
                    return;
                }

                $transaction = $service->recordCharge($this->ledger, $this->buildPayload($entry), $user);
            } else {
                $payload = $this->buildPayload($entry);
                $payload['type'] = $entry['type'];
                $transaction = $service->recordExpense($this->ledger, $payload, $user);
            }

            if ($transaction) {
                $this->syncEntryAttachments($transaction, $entry, $user->id);
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
                'invoice_attachment' => null,
                'receipt_attachment' => null,
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
        if (! empty($this->entries[$index]['reference_number'])) {
            return;
        }

        $branchName = $extractedData['origin_of_document_fa'] ?? 
                     $extractedData['seller_info']['name_fa'] ?? 
                     'شعبه مرکزی';
        
        // Clean branch name for serial number
        $cleanBranchName = $this->cleanBranchNameForSerial($branchName);
        
        // Generate sequential number without date
        $sequentialNumber = $this->getNextSequentialNumber();
        
        // Get invoice serial from extracted data
        $invoiceSerial = $extractedData['invoice_serial_number'] ?? 
                        $extractedData['transaction_id_or_request_number'] ?? 
                        null;
        
        // Generate auto serial number
        if ($invoiceSerial) {
            $this->entries[$index]['reference_number'] = "{$cleanBranchName}-{$sequentialNumber}-{$invoiceSerial}";
        } else {
            $this->entries[$index]['reference_number'] = "{$cleanBranchName}-{$sequentialNumber}";
        }
    }

    private function ensureSerialNumberAssigned(int $index): string
    {
        if (empty($this->entries[$index]['reference_number'])) {
            $this->generateAutoSerialNumber($index, []);
        }

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
        $text = 'Serial: ' . $serial;

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

    private function cleanBranchNameForSerial(string $branchName): string
    {
        // Remove common words and clean the name
        $removeWords = ['شرکت', 'موسسه', 'فروشگاه', 'رستوران', 'کافه', 'سازمان', 'موسسه'];
        $cleanName = $branchName;
        
        foreach ($removeWords as $word) {
            $cleanName = str_replace($word, '', $cleanName);
        }
        
        // Remove spaces and special characters
        $cleanName = preg_replace('/[^آ-ی0-9]/', '', trim($cleanName));
        
        // Limit to 10 characters
        return mb_substr($cleanName, 0, 10);
    }

    private function getNextSequentialNumber(): string
    {
        // Get the last transaction for this ledger to determine next number
        $lastTransaction = PettyCashTransaction::where('ledger_id', $this->ledger->id)
            ->orderBy('id', 'desc')
            ->first();
        
        if ($lastTransaction && $lastTransaction->reference_number) {
            // Extract number from reference number pattern
            if (preg_match('/-(\d+)(?:-|$)/', $lastTransaction->reference_number, $matches)) {
                $lastNumber = (int) $matches[1];
                return str_pad($lastNumber + 1, 4, '0', STR_PAD_LEFT);
            }
        }
        
        // Start from 1 if no previous transactions
        return '0001';
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
        }
        
        if ($category) {
            $this->entries[$index]['category'] = $category;
        }
    }

    private function generateDescriptionFromExtractedData(array $extractedData): string
    {
        // Get document type
        $documentType = $extractedData['document_type_fa'] ?? '';
        
        // Get items for description
        $items = $extractedData['items_details']['item_structure'] ?? [];
        $itemCount = count($items);
        
        // Get total amount
        $totalAmount = $extractedData['financial_summary']['final_amount_in_rial_numerical'] ?? 0;
        $formattedAmount = number_format($totalAmount);
        
        // Generate table description
        $tableDescription = $this->generateTableDescription($extractedData);
        
        // Generate description based on available data
        if ($itemCount > 0) {
            $firstItem = $items[0]['product_or_service_description_fa'] ?? '';
            if ($itemCount == 1) {
                return "خرید {$firstItem} - {$formattedAmount} ریال\n\n{$tableDescription}";
            } else {
                return "خرید {$itemCount} قلم کالا شامل {$firstItem} - {$formattedAmount} ریال\n\n{$tableDescription}";
            }
        } elseif ($documentType) {
            return "{$documentType} - {$formattedAmount} ریال\n\n{$tableDescription}";
        } else {
            return "تراکنش استخراج شده - {$formattedAmount} ریال\n\n{$tableDescription}";
        }
    }

    private function generateTableDescription(array $extractedData): string
    {
        $items = $extractedData['items_details']['item_structure'] ?? [];
        $financialSummary = $extractedData['financial_summary'] ?? [];
        $sellerInfo = $extractedData['seller_info'] ?? [];
        $buyerInfo = $extractedData['buyer_info'] ?? [];
        $paymentDetails = $extractedData['payment_and_banking_details'] ?? [];
        
        $table = "┌─────────────────────────────────────────────────────────────────────────────────────────────────┐\n";
        $table .= "│                           جدول آیتم‌های خریداری شده                           │\n";
        $table .= "├─────┬─────────────────────┬────────┬──────────┬──────────┬─────────────────────────────┤\n";
        $table .= "│ ردیف│ شرح کالا/خدمات     │ تعداد │ قیمت واحد│ تخفیف   │ مجموع                        │\n";
        $table .= "├─────┼─────────────────────┼────────┼──────────┼──────────┼─────────────────────────────┤\n";
        
        $subtotal = 0;
        foreach ($items as $item) {
            $rowNumber = $item['row_number'] ?? '';
            $description = $item['product_or_service_description_fa'] ?? '';
            $quantity = $item['quantity_numerical'] ?? '';
            $unitPrice = isset($item['unit_price_in_rial_numerical']) ? number_format($item['unit_price_in_rial_numerical']) : '';
            $discount = isset($item['discount_per_item_in_rial_numerical']) && $item['discount_per_item_in_rial_numerical'] > 0 ? number_format($item['discount_per_item_in_rial_numerical']) : '-';
            $totalPrice = isset($item['total_after_discount_in_rial_numerical']) && $item['total_after_discount_in_rial_numerical'] ? number_format($item['total_after_discount_in_rial_numerical']) : (isset($item['total_price_in_rial_numerical']) ? number_format($item['total_price_in_rial_numerical']) : '');
            
            // Truncate description if too long
            $description = mb_strlen($description) > 20 ? mb_substr($description, 0, 17) . '...' : $description;
            
            $table .= sprintf("│ %-3s │ %-19s │ %-6s │ %-8s │ %-8s │ %-27s │\n", 
                $rowNumber, $description, $quantity, $unitPrice, $discount, $totalPrice);
            
            if (isset($item['total_price_in_rial_numerical'])) {
                $subtotal += (float) $item['total_price_in_rial_numerical'];
            }
        }
        
        // Add taxes row
        if (isset($financialSummary['vat_and_tolls_amount_in_rial_numerical']) && $financialSummary['vat_and_tolls_amount_in_rial_numerical']) {
            $taxAmount = number_format($financialSummary['vat_and_tolls_amount_in_rial_numerical']);
            $table .= "├─────┼─────────────────────┼────────┼──────────┼──────────┼─────────────────────────────┤\n";
            $table .= sprintf("│ %-3s │ %-19s │ %-6s │ %-8s │ %-8s │ %-27s │\n", 
                "مالیات", "مالیات و عوارض", "-", "-", "-", $taxAmount);
        }
        
        // Add final amount row
        if (isset($financialSummary['final_amount_in_rial_numerical']) && $financialSummary['final_amount_in_rial_numerical']) {
            $finalAmount = number_format($financialSummary['final_amount_in_rial_numerical']);
            $table .= "├─────┼─────────────────────┼────────┼──────────┼──────────┼─────────────────────────────┤\n";
            $table .= sprintf("│ %-3s │ %-19s │ %-6s │ %-8s │ %-8s │ %-27s │\n", 
                "مجموع", "مبلغ نهایی", "-", "-", "-", $finalAmount);
        }
        
        $table .= "└─────┴─────────────────────┴────────┴──────────┴──────────┴─────────────────────────────┘\n";
        
        // Add seller and buyer information
        $table .= $this->generateSellerBuyerInfo($sellerInfo, $buyerInfo, $paymentDetails);
        
        return $table;
    }

    private function generateSellerBuyerInfo(array $sellerInfo, array $buyerInfo, array $paymentDetails): string
    {
        $info = "\n┌─────────────────────────────────────────────────────────────────────────────────┐\n";
        $info .= "│                              اطلاعات طرفین معامله                            │\n";
        $info .= "├─────────────────────────────────┬─────────────────────────────────────────────┤\n";
        
        // Seller information
        $info .= "│ اطلاعات فروشنده:                │                                             │\n";
        $info .= "├─────────────────────────────────┼─────────────────────────────────────────────┤\n";
        
        $sellerName = $sellerInfo['name_fa'] ?? 'نامشخص';
        $sellerPhone = $sellerInfo['phone_number'] ?? 'نامشخص';
        $sellerAddress = $sellerInfo['address_fa'] ?? 'نامشخص';
        $sellerEconomicCode = $sellerInfo['economic_code_or_national_id'] ?? 'نامشخص';
        
        $info .= sprintf("│ نام فروشنده: %-20s │ %-43s │\n", 
            mb_substr($sellerName, 0, 20), mb_substr($sellerName, 20, 43));
        $info .= sprintf("│ تلفن: %-24s │ %-43s │\n", 
            mb_substr($sellerPhone, 0, 24), mb_substr($sellerPhone, 24, 43));
        $info .= sprintf("│ کد اقتصادی: %-19s │ %-43s │\n", 
            mb_substr($sellerEconomicCode, 0, 19), mb_substr($sellerEconomicCode, 19, 43));
        $info .= sprintf("│ آدرس: %-23s │ %-43s │\n", 
            mb_substr($sellerAddress, 0, 23), mb_substr($sellerAddress, 23, 43));
        
        $info .= "├─────────────────────────────────┼─────────────────────────────────────────────┤\n";
        
        // Buyer information (Branch)
        $info .= "│ اطلاعات خریدار (شعبه):          │                                             │\n";
        $info .= "├─────────────────────────────────┼─────────────────────────────────────────────┤\n";
        
        $buyerName = $buyerInfo['name_fa'] ?? $this->ledger->name ?? 'نامشخص';
        $buyerPhone = $buyerInfo['phone_number'] ?? 'نامشخص';
        $buyerAddress = $buyerInfo['address_fa'] ?? 'نامشخص';
        $buyerNationalCode = $buyerInfo['national_code'] ?? 'نامشخص';
        
        $info .= sprintf("│ نام شعبه: %-21s │ %-43s │\n", 
            mb_substr($buyerName, 0, 21), mb_substr($buyerName, 21, 43));
        $info .= sprintf("│ تلفن شعبه: %-19s │ %-43s │\n", 
            mb_substr($buyerPhone, 0, 19), mb_substr($buyerPhone, 19, 43));
        $info .= sprintf("│ کد ملی: %-22s │ %-43s │\n", 
            mb_substr($buyerNationalCode, 0, 22), mb_substr($buyerNationalCode, 22, 43));
        $info .= sprintf("│ آدرس شعبه: %-19s │ %-43s │\n", 
            mb_substr($buyerAddress, 0, 19), mb_substr($buyerAddress, 19, 43));
        
        // Payment information
        $info .= "├─────────────────────────────────┼─────────────────────────────────────────────┤\n";
        $info .= "│ اطلاعات پرداخت:                 │                                             │\n";
        $info .= "├─────────────────────────────────┼─────────────────────────────────────────────┤\n";
        
        $paymentMethod = $paymentDetails['payment_method_fa'] ?? 'نامشخص';
        $bankName = $paymentDetails['bank_name_fa'] ?? 'نامشخص';
        $referenceNumber = $paymentDetails['reference_number_or_sheba'] ?? 'نامشخص';
        
        $info .= sprintf("│ روش پرداخت: %-19s │ %-43s │\n", 
            mb_substr($paymentMethod, 0, 19), mb_substr($paymentMethod, 19, 43));
        $info .= sprintf("│ نام بانک: %-21s │ %-43s │\n", 
            mb_substr($bankName, 0, 21), mb_substr($bankName, 21, 43));
        $info .= sprintf("│ شماره مرجع: %-19s │ %-43s │\n", 
            mb_substr($referenceNumber, 0, 19), mb_substr($referenceNumber, 19, 43));
        
        $info .= "└─────────────────────────────────┴─────────────────────────────────────────────┘\n";
        
        return $info;
    }

    private function convertJalaliToGregorian(string $jalaliDate): string
    {
        try {
            // Handle incomplete dates like "1403//"
            if (strpos($jalaliDate, '//') !== false) {
                return Verta::now()->format('Y-m-d H:i');
            }
            
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
                        'day' => $day
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
        // Use final amount from financial summary
        if (isset($extractedData['financial_summary']['final_amount_in_rial_numerical']) && $extractedData['financial_summary']['final_amount_in_rial_numerical']) {
            $amount = $this->parseAmount($extractedData['financial_summary']['final_amount_in_rial_numerical']);
            $this->entries[$index]['amount'] = $amount;
        }

        // Set transaction date from jalali date
        if (isset($extractedData['date_jalali']) && $extractedData['date_jalali']) {
            try {
                $jalaliDate = $extractedData['date_jalali'];
                // If date is incomplete, use current jalali date
                if (strlen($jalaliDate) < 8) {
                    $this->entries[$index]['transaction_date'] = Verta::now()->format('Y-m-d H:i');
                } else {
                    // Parse jalali date and convert to gregorian for form
                    $this->entries[$index]['transaction_date'] = $this->convertJalaliToGregorian($jalaliDate);
                }
            } catch (\Exception $e) {
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
                     
        if ($vendorName) {
            $this->entries[$index]['description'] = __('smart_invoice.default_description', ['vendor' => $vendorName]);
        } else {
            // Create a meaningful description from extracted data
            $description = $this->generateDescriptionFromExtractedData($extractedData);
            $this->entries[$index]['description'] = $description;
        }

        // Auto-categorize based on extracted data
        $this->autoCategorizeTransaction($index, $extractedData);

        // Calculate total from line items (new structure)
        if (isset($extractedData['items_details']['item_structure']) && is_array($extractedData['items_details']['item_structure'])) {
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

        // Use financial summary if available (subtotal first, then final)
        if (!isset($this->entries[$index]['amount'])) {
            if (isset($extractedData['financial_summary']['subtotal_in_rial_numerical'])) {
                $subtotalAmount = $this->parseAmount($extractedData['financial_summary']['subtotal_in_rial_numerical']);
                if ($subtotalAmount > 0) {
                    $this->entries[$index]['amount'] = $subtotalAmount;
                }
            } elseif (isset($extractedData['financial_summary']['final_amount_in_rial_numerical'])) {
                $finalAmount = $this->parseAmount($extractedData['financial_summary']['final_amount_in_rial_numerical']);
                if ($finalAmount > 0) {
                    $this->entries[$index]['amount'] = $finalAmount;
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

        if (! $invoice || ! $receipt) {
            $message = __('برای استفاده از تکمیل هوشمند، بارگذاری فاکتور و رسید الزامی است.');
            $this->addError('entries.' . $index . '.invoice_attachment', $message);
            $this->addError('entries.' . $index . '.receipt_attachment', $message);
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
            $extractedDataForView = array_merge($summary, [
                'items_details' => $structuredPayload['items_details'] ?? [],
                'financial_summary' => $structuredPayload['financial_summary'] ?? [],
                'line_items' => $structuredPayload['line_items'] ?? [],
                'analytics' => $result->analytics ?? [],
                'raw_payload' => $rawPayload,
            ]);

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
            $meta['smart_invoice'] = array_merge($meta['smart_invoice'] ?? [], $result->asMeta($smartInvoiceService->analyticsEnabled()), [
                'raw_payload' => $result->rawPayload,
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

    protected function syncEntryAttachments(PettyCashTransaction $transaction, array $entry, int $userId): void
    {
        if (isset($entry['invoice_attachment']) && $entry['invoice_attachment']) {
            $transaction->clearMediaCollection('invoice');
            $transaction->addMedia($entry['invoice_attachment']->getRealPath())
                ->usingFileName($entry['invoice_attachment']->getClientOriginalName())
                ->withCustomProperties([
                    'uploaded_by' => $userId,
                ])
                ->toMediaCollection('invoice');
        }

        if (isset($entry['receipt_attachment']) && $entry['receipt_attachment']) {
            $transaction->clearMediaCollection('bank_receipt');
            $transaction->addMedia($entry['receipt_attachment']->getRealPath())
                ->usingFileName($entry['receipt_attachment']->getClientOriginalName())
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
        ];
    }

    protected function syncSmartEntriesState(): void
    {
        $synced = [];

        foreach ($this->entries as $index => $_entry) {
            $synced[$index] = $this->smartEntriesState[$index] ?? $this->makeSmartEntryState();
        }

        $this->smartEntriesState = $synced;
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
            ->map(function ($entry) {
                return [
                    'type' => $entry['type'] ?? PettyCashTransaction::TYPE_EXPENSE,
                    'status' => $entry['status'] ?? PettyCashTransaction::STATUS_SUBMITTED,
                    'transaction_date' => $entry['transaction_date'],
                    'amount' => (float) $entry['amount'],
                    'currency' => $entry['currency'] ?? 'IRR',
                    'reference_number' => $entry['reference_number'] ?? null,
                    'description' => $entry['description'] ?? null,
                    'invoice_attachment' => $entry['invoice_attachment'] ?? null,
                    'receipt_attachment' => $entry['receipt_attachment'] ?? null,
                    'meta' => $entry['meta'] ?? [],
                ];
            })
            ->values();
    }

    protected function buildPayload(array $entry): array
    {
        return [
            'amount' => $entry['amount'],
            'currency' => $entry['currency'] ?? 'IRR',
            'transaction_date' => $this->parseDateInput($entry['transaction_date']),
            'reference_number' => $entry['reference_number'] ?? null,
            'description' => $entry['description'] ?? null,
            'status' => $entry['status'] ?? PettyCashTransaction::STATUS_SUBMITTED,
            'meta' => $entry['meta'] ?? null,
        ];
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
            'entries.*.invoice_attachment' => 'nullable|file|max:4096',
            'entries.*.receipt_attachment' => 'nullable|file|max:4096',
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
