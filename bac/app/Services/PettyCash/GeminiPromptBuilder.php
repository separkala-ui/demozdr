<?php

namespace App\Services\PettyCash;

class GeminiPromptBuilder
{
    public function buildExtractionPrompt(array $context = []): string
    {
        $segments = [];

        if (! empty($context['context'])) {
            $segments[] = "Context: {$context['context']}";
        }

        if (! empty($context['has_receipt'])) {
            $segments[] = 'A supporting POS receipt is attached; use it to confirm totals, payment reference and timestamp.';
        }

        $contextLine = empty($segments) ? '' : implode(' ', $segments);

        return <<<PROMPT
You are an automated extraction agent for Iranian invoices. Analyse the attached invoice/receipt image(s) and return the result exclusively by calling the tool `set_invoice`.

Key rules (keep responses concise):
- Leave *_fa fields in Persian. Record monetary values exactly as shown on the document, without performing unit conversions. Capture the observed currency label in `financial_summary.raw_currency` and explain the evidence in `financial_summary.currency_source_note`.
- Use null for unknown text, 0 for missing numbers. Do not fabricate data.
- If sums do not balance (e.g. subtotal differs from line totals), keep the observed numbers, set the appropriate validation flags to false and explain briefly in `validation.issues`.
- Include at most three short OCR evidence snippets inside `raw_payload.content`; avoid copying large text blocks.
- Capture every visible line item; if an amount is unreadable, record it as 0 and mention the issue in `validation.issues`.

{$contextLine}
PROMPT;
    }

    public function buildFallbackPrompt(array $context = []): string
    {
        $segments = [];

        if (! empty($context['context'])) {
            $segments[] = "Context: {$context['context']}";
        }

        if (! empty($context['has_receipt'])) {
            $segments[] = 'Receipt provided. Use it to confirm totals and payment reference.';
        }

        $contextLine = empty($segments) ? '' : implode(' ', $segments);

        return <<<PROMPT
You are a senior Persian invoice analyst. Read the uploaded document and output a single valid JSON object. Do not add Markdown or commentary.

Rules:
- Keep *_fa fields in Persian. Record monetary values exactly as printed and note the observed currency inside `financial_summary.raw_currency` together with a short explanation in `financial_summary.currency_source_note`.
- Use null/0 for unknown values; do not invent data.
- Explain inconsistencies (e.g. mismatched totals) briefly inside `validation.issues` and adjust the validation flags.
- Add up to three short OCR snippets to `raw_payload.content` so auditors can verify the result; avoid long verbatim copies.
- Capture every visible line item; if an amount is unreadable, record it as 0 and mention the issue.

{$contextLine}
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
