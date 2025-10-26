<?php

namespace App\Services\PettyCash\Data;

use Carbon\CarbonImmutable;

class SmartInvoiceExtraction
{
    public function __construct(
        public readonly ?float $totalAmount,
        public readonly ?float $taxAmount,
        public readonly ?string $currency,
        public readonly ?CarbonImmutable $issuedAt,
        public readonly ?string $referenceNumber,
        public readonly ?string $paymentReference,
        public readonly ?string $vendorName,
        public readonly ?string $customerName,
        public readonly array $lineItems,
        public readonly array $analytics,
        public readonly float $confidence,
        public readonly float $ocrScore,
        public readonly array $rawPayload,
    ) {
    }

    public static function fromArray(array $payload): self
    {
        $issuedAt = isset($payload['invoice_date']) && $payload['invoice_date']
            ? CarbonImmutable::parse($payload['invoice_date'])
            : null;

        return new self(
            totalAmount: isset($payload['total_amount']) ? (float) $payload['total_amount'] : null,
            taxAmount: isset($payload['tax_amount']) ? (float) $payload['tax_amount'] : null,
            currency: $payload['currency'] ?? 'IRR',
            issuedAt: $issuedAt,
            referenceNumber: $payload['reference_number'] ?? null,
            paymentReference: $payload['payment_reference'] ?? null,
            vendorName: $payload['vendor_name'] ?? null,
            customerName: $payload['customer_name'] ?? null,
            lineItems: $payload['line_items'] ?? [],
            analytics: $payload['analytics'] ?? [],
            confidence: (float) ($payload['confidence'] ?? 0),
            ocrScore: (float) ($payload['ocr_score'] ?? 0),
            rawPayload: $payload,
        );
    }

    public function asMeta(bool $includeAnalytics = true): array
    {
        $payload = [
            'total_amount' => $this->totalAmount,
            'tax_amount' => $this->taxAmount,
            'currency' => $this->currency,
            'issued_at' => $this->issuedAt?->toIso8601String(),
            'reference_number' => $this->referenceNumber,
            'payment_reference' => $this->paymentReference,
            'vendor_name' => $this->vendorName,
            'customer_name' => $this->customerName,
            'line_items' => $this->lineItems,
            'confidence' => $this->confidence,
            'ocr_score' => $this->ocrScore,
        ];

        if ($includeAnalytics) {
            $payload['analytics'] = $this->analytics;
        }

        return array_filter($payload, fn ($value) => $value !== null && $value !== []);
    }
}
