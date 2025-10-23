<?php

namespace App\Services\PettyCash;

class GeminiPromptBuilder
{
    public function buildExtractionPrompt(array $context = []): string
    {
        $receiptHint = '';
        if (! empty($context['has_receipt'])) {
            $receiptHint = "A POS receipt is attached alongside the invoice. Use it as supporting evidence for totals, discounts, payment references, and timestamps.";
        }

        $contextHint = '';
        if (! empty($context['context'])) {
            $contextHint = "Business context: {$context['context']}";
        }

        return <<<PROMPT
You are an Iranian invoice OCR and data extraction expert. Analyse the uploaded invoice or receipt (persian/english mixed text, typed or handwritten) and respond with a SINGLE JSON object that follows the schema below exactly. {$receiptHint} {$contextHint}

GLOBAL INSTRUCTIONS
1. Output strictly valid JSON; never wrap in markdown fences or add commentary.
2. Every field from the schema must appear. Use null (for text) or 0 (for numeric sums) if the value is missing.
3. Fields ending with _fa must stay in Persian (use the source language whenever possible).
4. Detect the working currency carefully:
   • Look for «ریال», «﷼», «Rial», «IRR» versus «تومان», «تـ», «Toman», «IRT», «هزار ریال».
   • If the printed amount is تومان/IRT/هزار ریال, multiply by 10 and store ریال values.
   • Write the detected source in financial_summary.raw_currency and describe the evidence in financial_summary.currency_source_note.
5. Understand both Persian and Western numerals; remove separators such as «,» or «٬». Convert words like «دو میلیون و پانصد هزار» to digits when confidence allows.
6. Extract every monetary component you see: subtotal, discount, tax/vat, transport/delivery, service fee, other charges, prepayment/advance, rounded difference. If any component is truly absent, set it to 0.
7. raw_payload.content must include the exact text (or OCR lines) used for the decision so auditors can verify it later.
8. If totals do not balance, keep the captured numbers, set validation flags to false, and explain the mismatch in validation.issues.
9. Do not infer or invent extra rows. If an amount is illegible, leave it null or 0 and log the issue.
10. Prefer numerical digits; convert words such as «دو میلیون و پانصد هزار» to digits when certain.

REQUIRED JSON SCHEMA
{
  "document_type_fa": "نوع سند (فاکتور رسمی، پیش‌فاکتور، رسید تعمیرات و ...)",
  "invoice_serial_number": "شماره سند یا سریال",
  "origin_of_document_fa": "نام فروشنده یا صادرکننده",
  "date_jalali": "YYYY/MM/DD یا null (اگر فقط میلادی است null)",
  "time": "HH:MM:SS یا null",
  "seller_info": {
    "name_fa": "نام فروشنده",
    "economic_code_or_national_id": "کد اقتصادی/ملی یا null",
    "phone_number": "شماره تماس یا null",
    "address_fa": "آدرس یا null",
    "postal_code": "کد پستی یا null"
  },
  "buyer_info": {
    "name_fa": "نام خریدار یا null",
    "national_code": "کد ملی یا null",
    "phone_number": "شماره تماس یا null",
    "address_fa": "آدرس یا null"
  },
  "items_details": {
    "item_structure": [
      {
        "row_number": 1,
        "product_or_service_description_fa": "شرح کالا/خدمت",
        "quantity_numerical": 0.0,
        "unit_fa": "واحد یا null",
        "unit_price_in_rial_numerical": 0.0,
        "discount_per_item_in_rial_numerical": 0.0,
        "tax_per_item_in_rial_numerical": 0.0,
        "total_price_in_rial_numerical": 0.0,
        "total_after_discount_in_rial_numerical": 0.0,
        "currency_source": "IRR|IRT|unknown"
      }
    ]
  },
  "financial_summary": {
    "subtotal_in_rial_numerical": 0.0,
    "total_discount_in_rial_numerical": 0.0,
    "vat_and_tolls_amount_in_rial_numerical": 0.0,
    "transport_total_in_rial_numerical": 0.0,
    "service_total_in_rial_numerical": 0.0,
    "other_charges_in_rial_numerical": 0.0,
    "prepayment_in_rial_numerical": 0.0,
    "final_amount_in_rial_numerical": 0.0,
    "raw_currency": "IRR|IRT|unknown",
    "currency_source_note": "توضیح کوتاه درباره تشخیص واحد پول"
  },
  "payment_and_banking_details": {
    "payment_method_fa": "روش پرداخت یا null",
    "bank_name_fa": "نام بانک یا null",
    "reference_number_or_sheba": "شماره مرجع یا شبا یا null",
    "account_holder_fa": "نام صاحب حساب یا null"
  },
  "validation": {
    "items_sum_matches_subtotal": true,
    "calculations_verified": true,
    "confidence_score": 0.95,
    "issues": ["لیست فارسی توضیحات یا خالی"]
  },
  "raw_payload": {
    "content": "متن OCR شده یا خطوط کلیدی فاکتور"
  }
}

BOOKKEEPING RULES
• Sum(total_after_discount_in_rial_numerical) across items MUST equal financial_summary.subtotal_in_rial_numerical; otherwise set validation.items_sum_matches_subtotal = false and describe why.
• subtotal + vat + transport + service + other_charges - total_discount - prepayment MUST equal final_amount. If it fails, set validation.calculations_verified = false and explain.
• Confidence score (0→1) is based on: image quality (30%), data completeness (40%), monetary consistency + currency certainty (30%).
• Capture any delivery/service/installation fees, rounding differences, or surcharges that appear anywhere on the document.

INVOICE VARIATIONS
- Formal tax invoices: expect sections for فروشنده/خریدار، کد اقتصادی، مالیات و عوارض، هزینه حمل، پیش‌پرداخت.
- Retail POS slips: ممکن است فقط جدول اقلام و «سود شما» یا «تخفیف» داشته باشند؛ مقادیر جمع را در financial_summary وارد کنید.
- Service & repair sheets: شامل اطلاعات سریال، قطعات، اجرت و هزینه ارسال؛ هر ردیف را جداگانه ثبت کنید.
- Restaurants or manual forms: جمع به حروف را برای تأیید نهایی استفاده کنید، اما فقط عدد را در final_amount ثبت کنید.
- اگر تاریخ فقط میلادی است، date_jalali را null قرار دهید و تاریخ میلادی را در raw_payload.content ذکر کنید.
- هرگز داده بسازید؛ اگر چیزی ناخواناست، مقدار را null/0 بگذارید و در validation.issues توضیح دهید.

پس از تکمیل استخراج، فقط همان شیء JSON بالا را برگردان و آن را از طریق فراخوانی تابع set_invoice ارسال کن.
PROMPT;
    }

    public function buildFallbackPrompt(array $context = []): string
    {
        $receiptHint = '';
        if (! empty($context['has_receipt'])) {
            $receiptHint = "A POS receipt may be attached. Use it for cross-checking totals, payment references, and timestamps.";
        }

        $contextHint = '';
        if (! empty($context['context'])) {
            $contextHint = "Business context: {$context['context']}";
        }

        return <<<PROMPT
You are a senior Persian invoice analyst. Read the uploaded document and return ONLY a strict JSON object matching the schema below. Do not add Markdown, explanations, or surrounding text. {$receiptHint} {$contextHint}

REQUIRED RULES
1. Output must be valid JSON (UTF-8) with double quotes around keys and string values.
2. All numeric monetary values must be in Rials (IRR). If the document uses تومان/IRT, multiply by 10.
3. Use null for unknown text fields, and 0 for missing numeric values.
4. Convert Persian digits to Western digits.
5. Verify sums: line items -> subtotal; subtotal + fees - discounts - prepayment + taxes -> final_amount_in_rial_numerical.
6. Capture every line item that has a description or numeric amount. Ignore empty rows.
7. If a section is absent, keep the field but set it to null/0.

SCHEMA
{
  "document_type_fa": "...",
  "invoice_serial_number": "...",
  "origin_of_document_fa": "...",
  "date_jalali": "...",
  "time": "...",
  "seller_info": {
    "name_fa": "...",
    "economic_code_or_national_id": "...",
    "phone_number": "...",
    "address_fa": "...",
    "postal_code": "..."
  },
  "buyer_info": {
    "name_fa": "...",
    "national_code": "...",
    "phone_number": "...",
    "address_fa": "..."
  },
  "items_details": {
    "item_structure": [
      {
        "row_number": 1,
        "product_or_service_description_fa": "...",
        "quantity_numerical": 0,
        "unit_fa": "...",
        "unit_price_in_rial_numerical": 0,
        "discount_per_item_in_rial_numerical": 0,
        "tax_per_item_in_rial_numerical": 0,
        "total_price_in_rial_numerical": 0,
        "total_after_discount_in_rial_numerical": 0,
        "currency_source": "IRR|IRT|unknown"
      }
    ]
  },
  "financial_summary": {
    "subtotal_in_rial_numerical": 0,
    "total_discount_in_rial_numerical": 0,
    "vat_and_tolls_amount_in_rial_numerical": 0,
    "transport_total_in_rial_numerical": 0,
    "service_total_in_rial_numerical": 0,
    "other_charges_in_rial_numerical": 0,
    "prepayment_in_rial_numerical": 0,
    "final_amount_in_rial_numerical": 0,
    "raw_currency": "IRR|IRT|unknown",
    "currency_source_note": "..."
  },
  "payment_and_banking_details": {
    "payment_method_fa": "...",
    "bank_name_fa": "...",
    "reference_number_or_sheba": "...",
    "account_holder_fa": "..."
  },
  "validation": {
    "items_sum_matches_subtotal": true,
    "calculations_verified": true,
    "confidence_score": 0.0,
    "issues": []
  },
  "raw_payload": {
    "content": ""
  }
}

ADDITIONAL NOTES
- If a numeric column contains non-numeric characters (e.g., commas, separators), normalize it.
- If the invoice is handwritten, transcribe the best guess; if unreadable, set null and describe in validation.issues.
- Mention any mismatch or uncertainty in validation.issues (Farsi).
- Do not include markdown fences, comments, or extra text. Return only the JSON object.
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
