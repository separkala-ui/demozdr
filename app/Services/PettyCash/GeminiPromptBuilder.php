<?php

namespace App\Services\PettyCash;

class GeminiPromptBuilder
{
    public function buildExtractionPrompt(array $context = []): string
    {
        return <<<'PROMPT'
You are an expert Iranian invoice and receipt data extraction system. Extract ALL information from the provided image with maximum accuracy.

CRITICAL REQUIREMENTS:
1. Extract data in the EXACT structure provided below
2. Use Persian (Farsi) for all text fields with _fa suffix
3. All numerical amounts must be in Rials (not Tomans) - multiply by 10 if needed
4. Handle incomplete or damaged invoices gracefully
5. Validate that totals match line item sums
6. Return ONLY valid JSON, no markdown formatting

MANDATORY JSON STRUCTURE:
{
  "document_type_fa": "نوع سند (فاکتور/رسید/قبض)",
  "invoice_serial_number": "شماره سند",
  "origin_of_document_fa": "منشاء سند",
  "date_jalali": "تاریخ شمسی YYYY/MM/DD",
  "time": "زمان HH:MM:SS",
  "seller_info": {
    "name_fa": "نام فروشنده",
    "economic_code_or_national_id": "کد ملی/اقتصادی",
    "phone_number": "شماره تماس",
    "address_fa": "آدرس کامل"
  },
  "buyer_info": {
    "name_fa": "نام خریدار",
    "national_code": "کد ملی",
    "phone_number": "شماره تماس",
    "address_fa": "آدرس"
  },
  "items_details": {
    "item_structure": [
      {
        "row_number": 1,
        "product_or_service_description_fa": "شرح کالا/خدمات",
        "quantity_numerical": 0.0,
        "unit_fa": "واحد",
        "unit_price_in_rial_numerical": 0.0,
        "discount_per_item_in_rial_numerical": 0.0,
        "total_price_in_rial_numerical": 0.0,
        "total_after_discount_in_rial_numerical": 0.0
      }
    ]
  },
  "financial_summary": {
    "subtotal_in_rial_numerical": 0.0,
    "total_discount_in_rial_numerical": 0.0,
    "vat_and_tolls_amount_in_rial_numerical": 0.0,
    "transport_total_in_rial_numerical": 0.0,
    "service_total_in_rial_numerical": 0.0,
    "final_amount_in_rial_numerical": 0.0
  },
  "payment_and_banking_details": {
    "payment_method_fa": "نقدی/کارت/چک/اعتباری",
    "bank_name_fa": "نام بانک",
    "reference_number_or_sheba": "شماره مرجع/شبا"
  },
  "validation": {
    "items_sum_matches_subtotal": true,
    "calculations_verified": true,
    "confidence_score": 0.95
  }
}

VALIDATION RULES:
1. Sum of item totals MUST equal subtotal
2. Subtotal + tax + transport + service - discount = final_amount
3. If amounts don't match, flag in validation
4. Confidence score based on:
   - Image quality (0-30%)
   - Data completeness (0-40%)
   - Calculation accuracy (0-30%)

SPECIAL CASES:
- Torn/damaged: Extract what's visible, mark low confidence
- Handwritten: Do your best, mark medium confidence
- Multiple pages: Extract all, maintain order
- Tomans vs Rials: Always convert to Rials (multiply by 10)
- Missing data: Use null or 0, never fabricate
- Incomplete JSON: Complete all open brackets and quotes

IMPORTANT: Return complete, valid JSON only. No markdown, no explanations.

Extract now:
PROMPT;
    }

    public function buildValidationPrompt(array $extractedData): string
    {
        $json = json_encode($extractedData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        
        return <<<PROMPT
Verify this extracted invoice data for accuracy and consistency:

{$json}

Check:
1. Do line items sum to subtotal?
2. Does subtotal + fees - discounts = final amount?
3. Are dates valid and logical?
4. Is seller/buyer information complete?
5. Are there any impossible values (negative prices, etc)?

Return validation report in JSON:
{
  "is_valid": true/false,
  "errors": ["list of errors"],
  "warnings": ["list of warnings"],
  "corrected_data": { ... },
  "confidence_adjustment": -0.1
}
PROMPT;
    }

    public function buildRepairPrompt(string $incompleteJson): string
    {
        return <<<PROMPT
The following JSON is incomplete or malformed. Repair it to be valid JSON:

{$incompleteJson}

Rules:
1. Close all open brackets and quotes
2. Complete any incomplete objects
3. Ensure proper JSON syntax
4. Maintain the invoice structure
5. Use null for missing values

Return only the repaired, valid JSON.
PROMPT;
    }
}
