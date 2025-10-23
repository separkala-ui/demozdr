<?php

namespace App\Services\PettyCash;

class InvoiceDataNormalizer
{
    public function normalize(array $rawData): array
    {
        // Unwrap markdown if present
        $data = $this->unwrapMarkdown($rawData);
        
        return [
            'document_info' => $this->extractDocumentInfo($data),
            'seller_info' => $this->extractSellerInfo($data),
            'buyer_info' => $this->extractBuyerInfo($data),
            'financial_summary' => $this->extractFinancialSummary($data),
            'items' => $this->extractItems($data),
            'payment_details' => $this->extractPaymentDetails($data),
            'dates' => $this->extractDates($data),
            'metadata' => $this->extractMetadata($data),
        ];
    }

    private function unwrapMarkdown(array $data): array
    {
        if (isset($data['raw_payload']['content'])) {
            $content = $data['raw_payload']['content'];
            $content = preg_replace('/```json\s*/', '', $content);
            $content = preg_replace('/```\s*$/', '', $content);
            $content = trim($content);
            
            $decoded = json_decode($content, true);
            return $decoded ?? $data;
        }
        
        return $data;
    }

    private function extractDocumentInfo(array $data): array
    {
        return [
            'type' => $this->findValue($data, [
                'document_type_fa',
                'document_type',
                'type',
                'invoice_type'
            ]),
            'number' => $this->findValue($data, [
                'invoice_serial_number',
                'invoice_number',
                'document_number',
                'serial_number'
            ]),
            'origin' => $this->findValue($data, [
                'origin_of_document_fa',
                'origin',
                'source'
            ]),
        ];
    }

    private function extractSellerInfo(array $data): array
    {
        $sellerData = $data['seller_info'] ?? $data['vendor'] ?? $data['seller'] ?? [];
        
        return [
            'name' => $this->findValue($sellerData, ['name_fa', 'name', 'vendor_name']),
            'national_id' => $this->findValue($sellerData, [
                'economic_code_or_national_id',
                'national_id',
                'tax_id',
                'economic_code'
            ]),
            'phone' => $this->findValue($sellerData, ['phone_number', 'phone', 'tel']),
            'address' => $this->findValue($sellerData, ['address_fa', 'address']),
        ];
    }

    private function extractBuyerInfo(array $data): array
    {
        $buyerData = $data['buyer_info'] ?? $data['customer'] ?? $data['buyer'] ?? [];
        
        return [
            'name' => $this->findValue($buyerData, ['name_fa', 'name', 'customer_name']),
            'national_code' => $this->findValue($buyerData, ['national_code', 'national_id']),
            'phone' => $this->findValue($buyerData, ['phone_number', 'phone', 'tel']),
            'address' => $this->findValue($buyerData, ['address_fa', 'address']),
        ];
    }

    private function extractFinancialSummary(array $data): array
    {
        $financial = $data['financial_summary'] ?? $data['totals'] ?? $data['summary'] ?? [];
        
        return [
            'subtotal' => $this->parseAmount($this->findValue($financial, [
                'subtotal_in_rial_numerical',
                'subtotal',
                'sub_total',
                'total_before_tax'
            ])),
            'tax' => $this->parseAmount($this->findValue($financial, [
                'vat_and_tolls_amount_in_rial_numerical',
                'tax_amount',
                'vat',
                'tax'
            ])),
            'discount' => $this->parseAmount($this->findValue($financial, [
                'total_discount_in_rial_numerical',
                'discount_amount',
                'discount'
            ])),
            'transport' => $this->parseAmount($this->findValue($financial, [
                'transport_total_in_rial_numerical',
                'shipping',
                'delivery_cost'
            ])),
            'service_fee' => $this->parseAmount($this->findValue($financial, [
                'service_total_in_rial_numerical',
                'service_charge',
                'service_fee'
            ])),
            'other_charges' => $this->parseAmount($this->findValue($financial, [
                'other_charges_in_rial_numerical',
                'other_fees',
                'misc_fees',
                'additional_charges'
            ])),
            'prepayment' => $this->parseAmount($this->findValue($financial, [
                'prepayment_in_rial_numerical',
                'prepayment',
                'advance_payment',
                'deposit'
            ])),
            'final_amount' => $this->parseAmount($this->findValue($financial, [
                'final_amount_in_rial_numerical',
                'total_amount',
                'grand_total',
                'final_total'
            ])),
            'raw_currency' => $this->findValue($financial, [
                'raw_currency',
                'detected_currency',
                'currency'
            ]),
            'currency_source_note' => $this->findValue($financial, [
                'currency_source_note',
                'currency_note',
                'currency_evidence'
            ]),
        ];
    }

    private function extractItems(array $data): array
    {
        $items = [];
        
        // Try different possible structures
        $rawItems = $data['items_details']['item_structure'] ?? 
                   $data['line_items'] ?? 
                   $data['items'] ?? 
                   [];

        foreach ($rawItems as $item) {
            $items[] = [
                'row_number' => $item['row_number'] ?? null,
                'description' => $this->findValue($item, [
                    'product_or_service_description_fa',
                    'description',
                    'item_name',
                    'product_name'
                ]),
                'quantity' => $this->parseAmount($this->findValue($item, [
                    'quantity_numerical',
                    'quantity',
                    'qty'
                ])),
                'unit_price' => $this->parseAmount($this->findValue($item, [
                    'unit_price_in_rial_numerical',
                    'unit_price',
                    'price'
                ])),
                'discount' => $this->parseAmount($this->findValue($item, [
                    'discount_per_item_in_rial_numerical',
                    'discount',
                    'item_discount'
                ])),
                'tax' => $this->parseAmount($this->findValue($item, [
                    'tax_per_item_in_rial_numerical',
                    'item_tax',
                    'tax'
                ])),
                'total' => $this->parseAmount($this->findValue($item, [
                    'total_after_discount_in_rial_numerical',
                    'total_price_in_rial_numerical',
                    'total',
                    'line_total'
                ])),
                'currency_source' => $this->findValue($item, [
                    'currency_source',
                    'currency',
                    'unit_currency'
                ]),
            ];
        }

        return $items;
    }

    private function extractPaymentDetails(array $data): array
    {
        $payment = $data['payment_and_banking_details'] ?? 
                  $data['payment_details'] ?? 
                  $data['payment'] ?? 
                  [];
        
        return [
            'method' => $this->findValue($payment, [
                'payment_method_fa',
                'payment_method',
                'method'
            ]),
            'bank_name' => $this->findValue($payment, [
                'bank_name_fa',
                'bank_name',
                'bank'
            ]),
            'reference' => $this->findValue($payment, [
                'reference_number_or_sheba',
                'reference_number',
                'transaction_id'
            ]),
        ];
    }

    private function extractDates(array $data): array
    {
        return [
            'jalali' => $this->findValue($data, ['date_jalali', 'jalali_date', 'date_fa']),
            'gregorian' => $this->findValue($data, ['date', 'issue_date', 'transaction_date']),
            'time' => $this->findValue($data, ['time', 'time_fa', 'issue_time']),
        ];
    }

    private function extractMetadata(array $data): array
    {
        return [
            'confidence' => $data['confidence'] ?? null,
            'source' => $data['source'] ?? null,
            'raw_data' => $data,
        ];
    }

    private function findValue(array $data, array $possibleKeys): mixed
    {
        foreach ($possibleKeys as $key) {
            if (isset($data[$key]) && $data[$key] !== '' && $data[$key] !== null) {
                return $data[$key];
            }
        }
        
        return null;
    }

    private function parseAmount($value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        // Convert Persian/Arabic numbers
        $persian = ['۰', '۱', '۲', '۳', '۴', '۵', '۶', '۷', '۸', '۹'];
        $arabic = ['٠', '١', '٢', '٣', '٤', '٥', '٦', '٧', '٨', '٩'];
        $english = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

        $value = str_replace($persian, $english, (string)$value);
        $value = str_replace($arabic, $english, $value);
        
        // Remove non-numeric characters except decimal point
        $value = preg_replace('/[^0-9.]/', '', $value);

        return is_numeric($value) ? (float)$value : null;
    }
}
