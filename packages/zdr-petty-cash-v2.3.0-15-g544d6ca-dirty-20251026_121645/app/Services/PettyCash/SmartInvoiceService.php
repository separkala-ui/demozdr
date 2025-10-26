<?php

namespace App\Services\PettyCash;

use App\Exceptions\SmartInvoiceException;
use App\Services\PettyCash\Data\SmartInvoiceExtraction;
use Carbon\Carbon;
use Hekmatinasser\Verta\Verta;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SmartInvoiceService
{
    private const RETRYABLE_STATUS_CODES = [408, 409, 429, 500, 502, 503, 504];

    private int $maxRetries = 2;

    public function __construct(
        private readonly HttpFactory $http
    ) {
    }

    public function isEnabled(): bool
    {
        $provider = $this->getProvider();

        if ($provider === 'openai') {
            $enabled = $this->getSetting('smart-invoice.openai.enabled', config('smart-invoice.openai.enabled', false));
            $apiKey = $this->getSetting('smart-invoice.openai.api_key', config('smart-invoice.openai.api_key'));

            return (bool) $enabled && filled($apiKey);
        }

        if (! $this->getSetting('smart-invoice.openai.fallback_to_gemini', config('smart-invoice.openai.fallback_to_gemini', true))) {
            return false;
        }

        $enabled = $this->getSetting('smart-invoice.gemini.enabled', config('smart-invoice.gemini.enabled'));
        $apiKey = $this->getSetting('smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key'));

        return (bool) $enabled && filled($apiKey);
    }

    public function analyticsEnabled(): bool
    {
        return (bool) $this->getSetting('smart-invoice.analytics', config('smart-invoice.analytics'));
    }

    public function getConfidenceThreshold(): float
    {
        return (float) $this->getSetting('smart-invoice.confidence_threshold', config('smart-invoice.confidence_threshold', 0.6));
    }

    public function getValidationTolerance(): float
    {
        return (float) $this->getSetting(
            'smart-invoice.validation.tolerance',
            config('smart-invoice.validation.tolerance', 1000)
        );
    }

    /**
     * @param  TemporaryUploadedFile|UploadedFile|null  $invoice
     * @param  TemporaryUploadedFile|UploadedFile|null  $receipt
     * @param  array<string, mixed>  $context
     */
    public function extractFromUploads($invoice, $receipt, array $context = []): SmartInvoiceExtraction
    {
        if (! $this->isEnabled()) {
            throw SmartInvoiceException::serviceDisabled();
        }

        if (! $invoice && ! $receipt) {
            throw SmartInvoiceException::attachmentsMissing();
        }

        $provider = $this->getProvider();

        if ($provider === 'openai') {
            return $this->extractUsingOpenAi($invoice, $receipt, $context);
        }

        return $this->extractUsingGemini($invoice, $receipt, $context);
    }

    /**
     * @return array<int, array{mode:string,prompt:string,name:string}>
     */
    protected function buildPromptVariants(array $context, bool $hasReceipt): array
    {
        $promptContext = $this->buildPromptContext($context);
        $builder = new GeminiPromptBuilder();

        return [
            [
                'mode' => 'function',
                'prompt' => $builder->buildExtractionPrompt([
                    'has_receipt' => $hasReceipt,
                    'context' => $promptContext,
                ]),
                'name' => 'function_call',
            ],
            [
                'mode' => 'text',
                'prompt' => $builder->buildFallbackPrompt([
                    'has_receipt' => $hasReceipt,
                    'context' => $promptContext,
                ]),
                'name' => 'fallback_text',
            ],
        ];
    }

    protected function performGeminiRequest($invoice, $receipt, string $prompt, string $mode): Response
    {
        $apiKey = $this->getSetting('smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key'));
        $model = $this->getSetting('smart-invoice.gemini.model', config('smart-invoice.gemini.model', 'gemini-2.5-flash'));
        $timeout = $this->getSetting('smart-invoice.gemini.timeout', config('smart-invoice.gemini.timeout', 45));
        $maxOutputTokens = (int) $this->getSetting(
            'smart-invoice.gemini.max_output_tokens',
            config('smart-invoice.gemini.max_output_tokens', 8192)
        );
        if ($maxOutputTokens < 2048) {
            $maxOutputTokens = 2048;
        }

        if (! filled($apiKey)) {
            throw SmartInvoiceException::serviceDisabled();
        }

        $parts = $this->buildRequestParts($invoice, $receipt, $prompt);

        if (count($parts) <= 1) {
            throw SmartInvoiceException::requestFailed(__('فایل تصویر قابل خواندن نیست.'));
        }

        Log::debug('Smart invoice request payload prepared', [
            'model' => $model,
            'timeout' => $timeout,
            'has_invoice_part' => (bool) $invoice,
            'has_receipt_part' => (bool) $receipt,
            'mode' => $mode,
        ]);

        $requestData = [
            'contents' => [
                [
                    'parts' => $parts,
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topK' => 32,
                'topP' => 1,
                'maxOutputTokens' => $maxOutputTokens,
            ],
        ];

        if ($mode === 'function') {
            $requestData['tools'] = $this->buildFunctionTools();
            $requestData['toolConfig'] = [
                'functionCallingConfig' => [
                    'mode' => 'ANY',
                ],
            ];
        }

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        return $this->http
            ->timeout((int) $timeout)
            ->withHeaders([
                'Content-Type' => 'application/json',
            ])
            ->post($url, $requestData);
    }

    protected function performOpenAiRequest($invoice, $receipt, string $prompt): Response
    {
        $apiKey = $this->getSetting('smart-invoice.openai.api_key', config('smart-invoice.openai.api_key'));
        $model = $this->getSetting('smart-invoice.openai.model', config('smart-invoice.openai.model', 'gpt-4o-mini'));
        $timeout = (int) $this->getSetting('smart-invoice.openai.timeout', config('smart-invoice.openai.timeout', 60));
        $maxTokens = (int) $this->getSetting('smart-invoice.openai.max_output_tokens', config('smart-invoice.openai.max_output_tokens', 4096));

        if (! filled($apiKey)) {
            throw SmartInvoiceException::serviceDisabled();
        }

        $messages = [
            [
                'role' => 'system',
                'content' => [
                    ['type' => 'input_text', 'text' => $prompt],
                ],
            ],
            [
                'role' => 'user',
                'content' => array_filter([
                    ...$this->buildOpenAiImageContent($invoice),
                    ...$this->buildOpenAiImageContent($receipt),
                    ['type' => 'input_text', 'text' => 'Analyse the invoice and call set_invoice with the extracted data.'],
                ]),
            ],
        ];

        $requestData = [
            'model' => $model,
            'input' => $messages,
            'max_output_tokens' => max($maxTokens, 2048),
            'tools' => [
                [
                    'name' => 'set_invoice',
                    'type' => 'function',
                    'description' => 'Return the structured invoice extraction result.',
                    'parameters' => $this->invoiceResponseSchema()['parameters'],
                ],
            ],
            'tool_choice' => [
                'type' => 'function',
                'name' => 'set_invoice',
            ],
        ];

        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $apiKey,
            'Content-Type' => 'application/json',
        ])->timeout($timeout)
            ->post('https://api.openai.com/v1/responses', $requestData);
    }

    protected function shouldRetryStatus(int $status, int $attempt): bool
    {
        return $attempt < $this->maxRetries && in_array($status, self::RETRYABLE_STATUS_CODES, true);
    }

    protected function sleepBeforeRetry(int $attempt): void
    {
        $delaySeconds = min(2.5, 0.5 * ($attempt + 1));
        usleep((int) ($delaySeconds * 1_000_000));
    }

    protected function parseGeminiResponse(array $payload, array $context = []): array
    {
        $mode = $context['__response_mode'] ?? 'function';
        $collected = $this->collectCandidateData($payload);
        $structured = $collected['structured'];
        $rawText = trim(implode("\n", array_map(
            static fn ($text) => is_string($text) ? $text : '',
            $collected['texts'] ?? []
        )));
        $usageMetadata = $payload['usageMetadata'] ?? null;

        if (is_array($structured) && ! empty($structured)) {
            $content = json_encode(
                $structured,
                JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
            ) ?: '';

            $extractedData = $this->transformStructuredPayload($structured, $content);
            $extractedData['raw_payload']['mode'] = $mode;
            $extractedData['raw_payload']['usage'] = $usageMetadata;
            if (isset($payload['responseId'])) {
                $extractedData['raw_payload']['response_id'] = $payload['responseId'];
            }
            if ($rawText !== '') {
                $extractedData['raw_payload']['raw_text'] = $rawText;
            }

            $processed = $this->postProcessExtraction($extractedData, $context);

            return [
                'status' => $this->hasMeaningfulData($processed) ? 'success' : 'empty',
                'data' => $processed,
            ];
        }

        if ($rawText !== '') {
            Log::debug('Gemini raw content received', [
                'length' => strlen($rawText),
                'preview' => mb_substr($rawText, 0, 400),
                'mode' => $mode,
            ]);

            $structuredFromText = $this->parseJsonContent($rawText);

            if ($structuredFromText !== null) {
                $extractedData = $this->transformStructuredPayload($structuredFromText, $rawText);
                $extractedData['raw_payload']['mode'] = $mode;
                $extractedData['raw_payload']['raw_text'] = $rawText;
                $extractedData['raw_payload']['usage'] = $usageMetadata;
                if (isset($payload['responseId'])) {
                    $extractedData['raw_payload']['response_id'] = $payload['responseId'];
                }

                $processed = $this->postProcessExtraction($extractedData, $context);

                return [
                    'status' => $this->hasMeaningfulData($processed) ? 'success' : 'empty',
                    'data' => $processed,
                ];
            }

            Log::warning('Gemini response did not contain valid JSON payload', [
                'content_preview' => mb_substr($rawText, 0, 400),
                'mode' => $mode,
            ]);

            $this->persistRawContentForDebug($rawText);
        } else {
            Log::warning('Gemini response returned empty payload', [
                'mode' => $mode,
            ]);
        }

        $emptyPayloadRaw = [
            'content' => $rawText,
            'mode' => $mode,
            'usage' => $usageMetadata,
        ];

        if ($rawText !== '') {
            $emptyPayloadRaw['raw_text'] = $rawText;
        }

        if (isset($payload['responseId'])) {
            $emptyPayloadRaw['response_id'] = $payload['responseId'];
        }

        $emptyPayload = $this->emptyExtractionPayload([
            'raw_payload' => $emptyPayloadRaw,
        ]);

        $processed = $this->postProcessExtraction($emptyPayload, $context);

        return [
            'status' => 'empty',
            'data' => $processed,
            'message' => 'no_structured_data',
        ];
    }

    protected function extractUsingOpenAi($invoice, $receipt, array $context): SmartInvoiceExtraction
    {
        $prompt = (new GeminiPromptBuilder())->buildExtractionPrompt([
            'has_receipt' => (bool) $receipt,
            'context' => $this->buildPromptContext($context),
        ]);

        $response = $this->performOpenAiRequest($invoice, $receipt, $prompt);

        if ($response->failed()) {
            $status = $response->status();
            $message = $response->json('error.message') ?? $response->body();

            if ($status === 429 && $this->canFallbackToGemini()) {
                Log::warning('OpenAI rate limit reached during request', [
                    'error' => $message,
                ]);

                return SmartInvoiceExtraction::fromArray(
                    $this->extractUsingGemini($invoice, $receipt, $context)->asMeta()
                );
            }

            throw SmartInvoiceException::requestFailed($message ?: __('پاسخ نامعتبر از سرویس OpenAI دریافت شد.'));
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw SmartInvoiceException::requestFailed(__('پاسخ نامعتبر از سرویس OpenAI دریافت شد.'));
        }

        $parsed = $this->parseOpenAiResponse($payload, $context);

        return SmartInvoiceExtraction::fromArray($parsed);
    }

    protected function extractUsingGemini($invoice, $receipt, array $context): SmartInvoiceExtraction
    {
        Log::info('Smart invoice extraction started', [
            'has_invoice' => (bool) $invoice,
            'has_receipt' => (bool) $receipt,
            'context' => array_filter($context),
            'analytics_enabled' => $this->analyticsEnabled(),
        ]);

        $promptVariants = $this->buildPromptVariants($context, (bool) $receipt);
        $lastResult = null;

        foreach ($promptVariants as $variant) {
            $attempt = 0;

            while ($attempt <= $this->maxRetries) {
                try {
                    $response = $this->performGeminiRequest($invoice, $receipt, $variant['prompt'], $variant['mode']);
                } catch (\Throwable $exception) {
                    Log::warning('Gemini request threw exception', [
                        'mode' => $variant['mode'],
                        'variant' => $variant['name'],
                        'attempt' => $attempt + 1,
                        'message' => $exception->getMessage(),
                    ]);

                    if ($attempt < $this->maxRetries) {
                        $this->sleepBeforeRetry($attempt);
                        $attempt++;
                        continue;
                    }

                    throw SmartInvoiceException::requestFailed(
                        __('smart_invoice.service_error', ['message' => $this->stringifyMessage($exception->getMessage())])
                    );
                }

                if ($response->failed()) {
                    $status = $response->status();
                    $message = $response->json('error.message') ?? $response->json('detail') ?? $response->body();

                    Log::warning('Gemini invoice extraction failed', [
                        'status' => $status,
                        'body' => $message,
                        'mode' => $variant['mode'],
                        'variant' => $variant['name'],
                        'attempt' => $attempt + 1,
                    ]);

                    if ($this->shouldRetryStatus($status, $attempt)) {
                        $this->sleepBeforeRetry($attempt);
                        $attempt++;
                        continue;
                    }

                    throw SmartInvoiceException::requestFailed(
                        __('smart_invoice.service_error', ['message' => $this->stringifyMessage($message) ?: __('پاسخ نامعتبر')])
                    );
                }

                $payload = $response->json();

                if (! is_array($payload)) {
                    if ($attempt < $this->maxRetries) {
                        $this->sleepBeforeRetry($attempt);
                        $attempt++;
                        continue;
                    }

                    throw SmartInvoiceException::requestFailed(__('پاسخ نامعتبر از سرویس هوشمند دریافت شد.'));
                }

                Log::info('Smart invoice raw payload received', [
                    'has_candidates' => isset($payload['candidates']),
                    'keys' => array_keys($payload),
                    'mode' => $variant['mode'],
                    'variant' => $variant['name'],
                    'attempt' => $attempt + 1,
                ]);

                $parsed = $this->parseGeminiResponse($payload, array_merge($context, [
                    '__response_mode' => $variant['mode'],
                    '__response_attempt' => $attempt + 1,
                    '__prompt_variant' => $variant['name'],
                ]));

                $lastResult = $parsed;

                if ($parsed['status'] === 'success') {
                    Log::info('Smart invoice extraction completed', [
                        'mode' => $variant['mode'],
                        'variant' => $variant['name'],
                        'attempt' => $attempt + 1,
                        'total_amount' => $parsed['data']['total_amount'] ?? null,
                        'confidence' => $parsed['data']['confidence'] ?? null,
                        'issues' => $parsed['data']['analytics']['validation']['issues'] ?? [],
                    ]);

                    return SmartInvoiceExtraction::fromArray($parsed['data']);
                }

                Log::warning('Smart invoice extraction returned empty payload', [
                    'mode' => $variant['mode'],
                    'variant' => $variant['name'],
                    'attempt' => $attempt + 1,
                    'reason' => $parsed['message'] ?? 'no_structured_data',
                ]);

                break;
            }
        }

        if (! $lastResult) {
            $lastResult = [
                'status' => 'empty',
                'data' => $this->postProcessExtraction(
                    $this->emptyExtractionPayload(),
                    array_merge($context, ['__response_mode' => 'unavailable'])
                ),
            ];
        }

        return SmartInvoiceExtraction::fromArray($lastResult['data']);
    }

    protected function parseJsonContent(string $content): ?array
    {
        $structured = $this->decodeJsonString($content);
        $partial = null;

        if ($structured !== null && $this->looksLikeInvoicePayload($structured)) {
            return $structured;
        }

        if ($structured !== null) {
            Log::info('Gemini JSON parsed but appeared incomplete, attempting fallback repair', [
                'keys' => array_keys($structured),
            ]);
            $partial = $structured;
        }

        $truncated = $this->truncateTrailingFragment($content);
        if ($truncated !== null && $truncated !== '') {
            $structured = $this->decodeJsonString($truncated);

            if ($structured !== null && $this->looksLikeInvoicePayload($structured)) {
                Log::info('Recovered Gemini JSON after trimming trailing fragment', [
                    'original_length' => mb_strlen($content),
                    'trimmed_length' => mb_strlen($truncated),
                ]);

                return $structured;
            }

            if ($structured !== null) {
                Log::info('Trimmed Gemini JSON still incomplete, escalating to repair', [
                    'keys' => array_keys($structured),
                ]);
                $partial ??= $structured;
            }
        }

        $repaired = $this->repairMalformedJson($content);

        if (is_array($repaired) && $this->looksLikeInvoicePayload($repaired)) {
            return $repaired;
        }

        if ($repaired === null) {
            if ($partial !== null) {
                Log::warning('Gemini JSON repair failed; falling back to partial payload', [
                    'keys' => array_keys($partial),
                ]);

                return $partial;
            }

            return null;
        }

        Log::warning('Gemini JSON repair produced incomplete payload; using best-effort data', [
            'keys' => array_keys($repaired),
        ]);

        return $partial ?? $repaired;
    }

    /**
     * @param  array<string, mixed>  $override
     * @return array<string, mixed>
     */
    protected function emptyExtractionPayload(array $override = []): array
    {
        return array_merge([
            'total_amount' => null,
            'invoice_number' => null,
            'reference_number' => null,
            'currency' => 'IRR',
            'confidence' => 0.0,
            'ocr_score' => 0.0,
            'line_items' => [],
            'analytics' => [],
            'raw_payload' => $override ?: ['content' => ''],
        ], $override);
    }

    protected function extractJsonFromContent(string $content): ?array
    {
        if ($content === '') {
            return null;
        }

        $cleanContent = $this->normaliseJsonWrapper($content);

        if (preg_match('/\{.*\}/s', $cleanContent, $matches)) {
            $decoded = $this->decodeJsonString($matches[0]);
            if ($decoded !== null) {
                return $decoded;
            }
        }

        return $this->decodeJsonString($cleanContent);
    }

    protected function decodeJsonString(string $content): ?array
    {
        $clean = $this->normaliseJsonWrapper($content);
        if ($clean === '') {
            return null;
        }

        $decoded = json_decode($clean, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        $patched = $this->basicJsonRepair($clean);
        if ($patched !== null) {
            return $patched;
        }

        return null;
    }

    protected function looksLikeInvoicePayload(array $payload): bool
    {
        $hasItems = ! empty($payload['line_items'] ?? [])
            || ! empty($payload['items'] ?? [])
            || ! empty($payload['itemsDetails'] ?? [])
            || ! empty($payload['items_details']['item_structure'] ?? [])
            || ! empty($payload['lineItems'] ?? []);

        $hasSummary = ! empty($payload['financial_summary'] ?? [])
            || ! empty($payload['totals'] ?? [])
            || ! empty($payload['summary'] ?? [])
            || ! empty($payload['financialSummary'] ?? []);

        return $hasItems && $hasSummary;
    }

    /**
     * Attempt to strip an incomplete trailing segment from malformed JSON output.
     */
    protected function truncateTrailingFragment(string $content): ?string
    {
        $clean = $this->normaliseJsonWrapper($content);
        if ($clean === '') {
            return null;
        }

        $length = strlen($clean);
        $inString = false;
        $escape = false;
        $stack = [];
        $lastSafeIndex = null;

        for ($i = 0; $i < $length; $i++) {
            $char = $clean[$i];

            if ($inString) {
                if ($escape) {
                    $escape = false;
                    continue;
                }

                if ($char === '\\') {
                    $escape = true;
                    continue;
                }

                if ($char === '"') {
                    $inString = false;
                }

                continue;
            }

            if ($char === '"') {
                $inString = true;
                continue;
            }

            if ($char === '{') {
                $stack[] = '}';
                continue;
            }

            if ($char === '[') {
                $stack[] = ']';
                continue;
            }

            if ($char === '}' || $char === ']') {
                if (! empty($stack) && end($stack) === $char) {
                    array_pop($stack);
                }

                if (empty($stack)) {
                    $lastSafeIndex = $i + 1;
                }

                continue;
            }

            if ($char === ',' && count($stack) === 1) {
                $lastSafeIndex = $i;
            }
        }

        if (! empty($stack) && $lastSafeIndex !== null) {
            $trimmed = rtrim(substr($clean, 0, $lastSafeIndex), ", \t\n\r");
            $closing = '';

            while (! empty($stack)) {
                $closing .= array_pop($stack);
            }

            return $trimmed . $closing;
        }

        return null;
    }

    /**
     * @return array{structured:?array, texts:array<int,string>}
     */
    protected function collectCandidateData(array $payload): array
    {
        $structured = null;
        $texts = [];

        $candidates = $payload['candidates'] ?? [];
        if (empty($candidates)) {
            throw SmartInvoiceException::requestFailed(__('smart_invoice.processing_failed'));
        }

        foreach ($candidates as $candidate) {
            $parts = $candidate['content']['parts'] ?? [];
            if (empty($parts)) {
                continue;
            }

            foreach ($parts as $part) {
                if (isset($part['functionCall']['args']) && is_array($part['functionCall']['args'])) {
                    if ($structured === null || ! empty($part['functionCall']['args'])) {
                        $structured = $part['functionCall']['args'];
                    }
                    continue;
                }

                if (isset($part['jsonObject']) && is_array($part['jsonObject'])) {
                    if ($structured === null || ! empty($part['jsonObject'])) {
                        $structured = $part['jsonObject'];
                    }
                    continue;
                }

                if (isset($part['structValue']['fields']) && is_array($part['structValue']['fields'])) {
                    if ($structured === null || ! empty($part['structValue']['fields'])) {
                        $structured = $part['structValue']['fields'];
                    }
                    continue;
                }

                if (isset($part['text']) && is_string($part['text'])) {
                    $text = trim($part['text']);
                    if ($text !== '') {
                        $texts[] = $text;
                    }
                }
            }
        }

        return [
            'structured' => $structured,
            'texts' => $texts,
        ];
    }

    protected function normaliseJsonWrapper(string $content): string
    {
        $clean = trim($content);
        if ($clean === '') {
            return '';
        }

        $clean = preg_replace('/```(?:json)?/i', '', $clean) ?? $clean;
        $clean = preg_replace('/```$/', '', $clean) ?? $clean;

        return trim($clean);
    }

    protected function basicJsonRepair(string $clean): ?array
    {
        $patched = preg_replace('/,(\s*[}\]])/u', '$1', $clean) ?? $clean;

        $openCurly = substr_count($patched, '{');
        $closeCurly = substr_count($patched, '}');
        if ($openCurly > $closeCurly) {
            $patched .= str_repeat('}', $openCurly - $closeCurly);
        }

        $openSquare = substr_count($patched, '[');
        $closeSquare = substr_count($patched, ']');
        if ($openSquare > $closeSquare) {
            $patched .= str_repeat(']', $openSquare - $closeSquare);
        }

        $decoded = json_decode($patched, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return null;
    }

    /**
     * @param  array<string,mixed>  $data
     * @return array<string,mixed>
     */
    protected function transformStructuredPayload(array $data, string $content): array
    {
        $normalizer = new InvoiceDataNormalizer();
        $normalized = $normalizer->normalize($data);

        $financial = $normalized['financial_summary'] ?? [];
        $items = $normalized['items'] ?? [];
        $summaryRows = $normalized['metadata']['summary_rows'] ?? [];
        $seller = $normalized['seller_info'] ?? [];
        $buyer = $normalized['buyer_info'] ?? [];
        $payment = $normalized['payment_details'] ?? [];
        $document = $normalized['document_info'] ?? [];
        $dates = $normalized['dates'] ?? [];
        $validation = $data['validation'] ?? [];
        $analyticsMeta = $normalized['metadata'] ?? [];

        $lineItems = [];
        foreach ($items as $item) {
            $lineItems[] = array_filter([
                'description' => $item['description'] ?? null,
                'quantity' => $item['quantity'] ?? null,
                'unit_price' => $item['unit_price'] ?? null,
                'discount' => $item['discount'] ?? null,
                'tax' => $item['tax'] ?? null,
                'total' => $item['total'] ?? null,
                'currency_source' => $item['currency_source'] ?? null,
            ], static fn ($value) => $value !== null);
        }

        $itemStructures = array_map(function (array $item): array {
            return array_filter([
                'row_number' => $item['row_number'] ?? null,
                'product_or_service_description_fa' => $item['description'] ?? null,
                'quantity_numerical' => $item['quantity'] ?? null,
                'unit_fa' => $item['unit'] ?? ($item['unit_label'] ?? null),
                'unit_price_in_rial_numerical' => $item['unit_price'] ?? null,
                'discount_per_item_in_rial_numerical' => $item['discount'] ?? null,
                'tax_per_item_in_rial_numerical' => $item['tax'] ?? null,
                'total_price_in_rial_numerical' => $item['total'] ?? null,
                'total_after_discount_in_rial_numerical' => $item['total_after_discount'] ?? $item['total'] ?? null,
                'currency_source' => $item['currency_source'] ?? null,
            ], static fn ($value) => $value !== null && $value !== '');
        }, $items);

        $financial = $this->reconcileFinancialSummary($financial, $lineItems);

        foreach ($summaryRows as $summaryRow) {
            $amount = (float) ($summaryRow['amount'] ?? 0);
            if ($amount === 0.0) {
                continue;
            }

            switch ($summaryRow['type']) {
                case 'tax':
                    $financial['tax'] = ($financial['tax'] ?? 0) + $amount;
                    break;
                case 'discount':
                    $financial['discount'] = ($financial['discount'] ?? 0) + $amount;
                    break;
                case 'final_total':
                    $financial['final_amount'] = $amount;
                    break;
            }
        }

        $financialSummary = array_filter([
            'subtotal_in_rial_numerical' => $financial['subtotal'] ?? null,
            'total_discount_in_rial_numerical' => $financial['discount'] ?? null,
            'vat_and_tolls_amount_in_rial_numerical' => $financial['tax'] ?? null,
            'transport_total_in_rial_numerical' => $financial['transport'] ?? null,
            'service_total_in_rial_numerical' => $financial['service_fee'] ?? null,
            'other_charges_in_rial_numerical' => $financial['other_charges'] ?? null,
            'prepayment_in_rial_numerical' => $financial['prepayment'] ?? null,
            'final_amount_in_rial_numerical' => $financial['final_amount'] ?? $financial['computed_final_amount'] ?? null,
            'raw_currency' => $financial['raw_currency'] ?? ($analyticsMeta['currency'] ?? null),
            'currency_source_note' => $financial['currency_source_note'] ?? null,
        ], static fn ($value) => $value !== null && $value !== '');

        $analytics = [
            'currency' => [
                'raw_currency' => $financial['raw_currency'] ?? 'IRR',
                'currency_source_note' => $financial['currency_source_note'] ?? null,
            ],
            'validation' => [
                'items_sum_matches_subtotal' => $validation['items_sum_matches_subtotal'] ?? $financial['line_items_match'] ?? null,
                'calculations_verified' => $validation['calculations_verified'] ?? $financial['final_matches_breakdown'] ?? null,
                'issues' => array_values(array_unique(array_filter(array_merge(
                    $validation['issues'] ?? [],
                    $financial['issues'] ?? []
                )))),
            ],
            'financial_breakdown' => [
                'subtotal' => $financial['subtotal'] ?? null,
                'tax' => $financial['tax'] ?? null,
                'transport' => $financial['transport'] ?? null,
                'service_fee' => $financial['service_fee'] ?? null,
                'other_charges' => $financial['other_charges'] ?? null,
                'prepayment' => $financial['prepayment'] ?? null,
                'discount' => $financial['discount'] ?? null,
                'line_items_total' => $financial['line_items_total'] ?? null,
                'computed_final_amount' => $financial['computed_final_amount'] ?? null,
            ],
        ];

        if (! empty($document)) {
            $analytics['document'] = $document;
        }

        if (! empty($dates)) {
            $analytics['dates'] = $dates;
        }

        if (! empty($analyticsMeta)) {
            $analytics['metadata'] = $analyticsMeta;
        }

        if (isset($financial['difference']) && $financial['difference'] > 0) {
            $analytics['validation']['difference'] = $financial['difference'];
        }

        $confidence = $this->calculateConfidence(
            $validation,
            $financial,
            $data['confidence'] ?? null
        );

        $cleanContent = trim(preg_replace('/```(?:json)?/i', '', $content) ?? $content);
        $jsonContent = json_encode(
            $data,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
        ) ?: $cleanContent;

        $rawPaymentData = $data['payment_and_banking_details'] ?? [];
        $invoiceDate = $this->normalizeInvoiceDate($financial, $dates, $data);

        $paymentReference = $payment['reference'] ?? ($rawPaymentData['reference_number_or_sheba'] ?? null);

        return [
            'total_amount' => $financial['final_amount'] ?? $financial['computed_final_amount'] ?? null,
            'tax_amount' => $financial['tax'] ?? null,
            'currency' => $financial['raw_currency'] ?? 'IRR',
            'invoice_date' => $invoiceDate,
            'reference_number' => $data['invoice_serial_number'] ?? $document['number'] ?? null,
            'payment_reference' => $paymentReference,
            'vendor_name' => $seller['name'] ?? ($data['seller_info']['name_fa'] ?? null),
            'customer_name' => $buyer['name'] ?? ($data['buyer_info']['name_fa'] ?? null),
            'line_items' => $lineItems,
            'items_details' => [
                'item_structure' => $itemStructures,
            ],
            'financial_summary' => $financialSummary,
            'seller_info' => $seller,
            'buyer_info' => $buyer,
            'payment_and_banking_details' => $payment,
            'document_info' => $document,
            'dates' => $dates,
            'analytics' => $analytics,
            'confidence' => $confidence,
            'ocr_score' => (float) ($data['ocr_score'] ?? 0.0),
            'raw_payload' => [
                'structured' => $data,
                'content' => $jsonContent,
                'model_output' => $cleanContent,
                'document_info' => $document,
                'dates' => $dates,
                'metadata' => $analyticsMeta,
            ],
        ];
    }

    /**
     * @param  array<string,mixed>  $extracted
     * @param  array<string,mixed>  $context
     * @return array<string,mixed>
     */
    protected function postProcessExtraction(array $extracted, array $context): array
    {
        $analytics = $extracted['analytics'] ?? [];
        $financial = $analytics['financial_breakdown'] ?? [];
        $validation = $analytics['validation'] ?? [];

        $tolerance = $this->getValidationTolerance();

        $metadata = $analytics['metadata'] ?? [];
        if (! empty($context['__response_mode'])) {
            $metadata['response_mode'] = $context['__response_mode'];
        }
        if (! empty($context['__prompt_variant'])) {
            $metadata['prompt_variant'] = $context['__prompt_variant'];
        }
        if (! empty($context['__response_attempt'])) {
            $metadata['response_attempt'] = $context['__response_attempt'];
        }
        $analytics['metadata'] = $metadata;

        $lineItemsTotal = $financial['line_items_total'] ?? null;
        $subtotal = $financial['subtotal'] ?? null;
        $finalAmount = $financial['computed_final_amount'] ?? $extracted['total_amount'] ?? null;

        if ($lineItemsTotal !== null && $subtotal !== null) {
            $validation['items_sum_matches_subtotal'] = abs($lineItemsTotal - $subtotal) <= $tolerance;
        }

        if ($finalAmount !== null && $financial['computed_final_amount'] !== null) {
            $validation['calculations_verified'] = abs($finalAmount - $financial['computed_final_amount']) <= $tolerance;
        }

        $validation['issues'] = array_values(array_unique(array_filter($validation['issues'] ?? [])));

        $analytics['validation'] = $validation;
        $analytics['context'] = array_filter([
            'ledger_id' => $context['ledger_id'] ?? null,
            'transaction_type' => $context['transaction_type'] ?? null,
            'existing_amount' => $context['existing_amount'] ?? null,
            'response_mode' => $context['__response_mode'] ?? null,
            'response_attempt' => $context['__response_attempt'] ?? null,
        ]);

        $extracted['analytics'] = $analytics;

        if (! isset($extracted['total_amount']) && isset($financial['computed_final_amount'])) {
            $extracted['total_amount'] = $financial['computed_final_amount'];
        }

        if (! isset($extracted['tax_amount']) && isset($financial['tax'])) {
            $extracted['tax_amount'] = $financial['tax'];
        }

        Log::debug('Smart invoice post processing metrics', [
            'line_items_total' => $financial['line_items_total'] ?? null,
            'subtotal' => $financial['subtotal'] ?? null,
            'final_amount' => $financial['final_amount'] ?? null,
            'computed_final_amount' => $financial['computed_final_amount'] ?? null,
            'issues' => $validation['issues'] ?? [],
        ]);

        return $this->ensureCurrencyConsistency($extracted);
    }

    protected function ensureCurrencyConsistency(array $extracted): array
    {
        $rawPayload = $extracted['raw_payload'] ?? [];
        $detectedCurrency = $this->detectCurrencyLabelFromRawPayload($rawPayload);

        $analyticsCurrency = $extracted['analytics']['currency']['raw_currency'] ?? null;
        $financialCurrency = $extracted['financial_summary']['raw_currency'] ?? null;
        $currentCurrency = $financialCurrency ?? $analyticsCurrency ?? ($extracted['currency'] ?? null) ?? 'IRR';

        if (! isset($extracted['analytics']['currency']) || ! is_array($extracted['analytics']['currency'])) {
            $extracted['analytics']['currency'] = [];
        }

        if ($detectedCurrency) {
            $extracted['analytics']['currency']['detected_currency'] = $detectedCurrency;
            $currentCurrency = $detectedCurrency;
        }

        $shouldEnforceNormalisation = (bool) $this->getSetting(
            'smart-invoice.validation.enforce_currency_normalisation',
            config('smart-invoice.validation.enforce_currency_normalisation', true)
        );

        if ($shouldEnforceNormalisation && $currentCurrency === 'IRT') {
            $extracted = $this->convertExtractionToRial($extracted, 10);
            $currentCurrency = 'IRR';

            if (isset($extracted['financial_summary'])) {
                $note = $extracted['financial_summary']['currency_source_note'] ?? '';
                $note = trim($note . ' مقادیر تشخیص داده‌شده به تومان، به ریال تبدیل شدند.');
                $extracted['financial_summary']['currency_source_note'] = $note;
            }

            $extracted['analytics']['currency']['conversion'] = 'irt_to_irr';
        }

        $extracted['analytics']['currency']['raw_currency'] = $currentCurrency;
        $extracted['currency'] = $currentCurrency;

        if (isset($extracted['financial_summary'])) {
            $extracted['financial_summary']['raw_currency'] = $currentCurrency;
        }

        return $extracted;
    }

    protected function parseOpenAiResponse(array $payload, array $context): array
    {
        $output = $payload['output'] ?? [];
        $structured = null;
        $rawTexts = [];

        foreach ($output as $message) {
            $contents = $message['content'] ?? [];
            foreach ($contents as $content) {
                $type = $content['type'] ?? null;
                if ($type === 'tool_call') {
                    $toolCall = $content['tool_call'] ?? $content;
                    $arguments = $toolCall['arguments'] ?? null;
                    if (is_string($arguments) && trim($arguments) !== '') {
                        $decoded = $this->decodeJsonString($arguments);
                        if (is_array($decoded)) {
                            $structured = $decoded;
                        }
                    } elseif (isset($toolCall['output']) && is_array($toolCall['output'])) {
                        $structured = $toolCall['output'];
                    }
                    continue;
                }

                if ($type === 'text' || $type === 'output_text') {
                    $rawTexts[] = $content['text'] ?? '';
                    continue;
                }

                if ($type === 'json') {
                    $decoded = $this->decodeJsonString($content['json'] ?? '');
                    if (is_array($decoded)) {
                        $structured = $decoded;
                    }
                }
            }
        }

        $usage = $payload['usage'] ?? null;
        $responseId = $payload['id'] ?? null;

        if (is_array($structured) && ! empty($structured)) {
            $content = json_encode($structured, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) ?: '';
            $transformed = $this->transformStructuredPayload($structured, $content);
            $transformed['raw_payload']['mode'] = 'function';
            $transformed['raw_payload']['usage'] = $usage;
            if ($responseId) {
                $transformed['raw_payload']['response_id'] = $responseId;
            }
            if (! empty($rawTexts)) {
                $transformed['raw_payload']['raw_text'] = implode("\n", $rawTexts);
            }

            $processed = $this->postProcessExtraction($transformed, $context);

            return $processed;
        }

        $rawText = trim(implode("\n", $rawTexts));
        if ($rawText !== '') {
            $decoded = $this->parseJsonContent($rawText);
            if (is_array($decoded)) {
                $transformed = $this->transformStructuredPayload($decoded, $rawText);
                $transformed['raw_payload']['mode'] = 'text';
                $transformed['raw_payload']['usage'] = $usage;
                $transformed['raw_payload']['raw_text'] = $rawText;
                if ($responseId) {
                    $transformed['raw_payload']['response_id'] = $responseId;
                }

                return $this->postProcessExtraction($transformed, $context);
            }
        }

        $emptyPayload = $this->emptyExtractionPayload([
            'raw_payload' => array_filter([
                'mode' => 'text',
                'raw_text' => $rawText,
                'usage' => $usage,
                'response_id' => $responseId,
            ]),
        ]);

        Log::warning('OpenAI smart invoice returned empty payload', [
            'raw_text_preview' => mb_substr($rawText ?? '', 0, 400),
            'usage' => $usage,
            'response_id' => $responseId,
        ]);

        return $this->postProcessExtraction($emptyPayload, $context);
    }

    protected function detectCurrencyLabelFromRawPayload(array $rawPayload): ?string
    {
        $textSources = [];

        foreach (['raw_text', 'content', 'model_output'] as $key) {
            if (! empty($rawPayload[$key]) && is_string($rawPayload[$key])) {
                $textSources[] = $rawPayload[$key];
            }
        }

        if (empty($textSources)) {
            return null;
        }

        $text = $this->normaliseTextForCurrencyDetection(implode(' ', $textSources));

        if ($text === '') {
            return null;
        }

        $rialAliases = ['ریال', 'rial'];
        $tomanAliases = ['تومان', 'تومن', 'toman', 'tomaan', 'irt'];

        $rialCount = 0;
        foreach ($rialAliases as $alias) {
            $rialCount += substr_count($text, $alias);
        }

        $tomanCount = 0;
        foreach ($tomanAliases as $alias) {
            $tomanCount += substr_count($text, $alias);
        }

        if ($tomanCount > 0 && $rialCount === 0) {
            return 'IRT';
        }

        if ($rialCount > 0 && $tomanCount === 0) {
            return 'IRR';
        }

        if ($rialCount > 0 || $tomanCount > 0) {
            return $rialCount >= $tomanCount ? 'IRR' : 'IRT';
        }

        return null;
    }

    protected function normaliseTextForCurrencyDetection(string $text): string
    {
        $replacements = [
            'ي' => 'ی',
            'ك' => 'ک',
            'ۀ' => 'ه',
            'ة' => 'ه',
            'ؤ' => 'و',
            'إ' => 'ا',
            'أ' => 'ا',
        ];

        $normalised = strtr($text, $replacements);
        $normalised = preg_replace('/\s+/', ' ', $normalised ?? '') ?? '';

        return mb_strtolower($normalised, 'UTF-8');
    }

    protected function convertExtractionToRial(array $extracted, float $factor): array
    {
        $apply = function ($value) use ($factor) {
            return is_numeric($value) ? (float) $value * $factor : $value;
        };

        if (isset($extracted['total_amount'])) {
            $extracted['total_amount'] = $apply($extracted['total_amount']);
        }

        if (isset($extracted['tax_amount'])) {
            $extracted['tax_amount'] = $apply($extracted['tax_amount']);
        }

        if (isset($extracted['line_items']) && is_array($extracted['line_items'])) {
            $extracted['line_items'] = array_map(function (array $item) use ($apply) {
                foreach (['unit_price', 'discount', 'tax', 'total'] as $key) {
                    if (isset($item[$key])) {
                        $item[$key] = $apply($item[$key]);
                    }
                }

                return $item;
            }, $extracted['line_items']);
        }

        if (isset($extracted['items_details']['item_structure']) && is_array($extracted['items_details']['item_structure'])) {
            $extracted['items_details']['item_structure'] = array_map(function (array $item) use ($apply) {
                foreach ([
                    'unit_price_in_rial_numerical',
                    'discount_per_item_in_rial_numerical',
                    'tax_per_item_in_rial_numerical',
                    'total_price_in_rial_numerical',
                    'total_after_discount_in_rial_numerical',
                ] as $key) {
                    if (isset($item[$key])) {
                        $item[$key] = $apply($item[$key]);
                    }
                }

                return $item;
            }, $extracted['items_details']['item_structure']);
        }

        if (isset($extracted['financial_summary']) && is_array($extracted['financial_summary'])) {
            foreach ([
                'subtotal_in_rial_numerical',
                'total_discount_in_rial_numerical',
                'vat_and_tolls_amount_in_rial_numerical',
                'transport_total_in_rial_numerical',
                'service_total_in_rial_numerical',
                'other_charges_in_rial_numerical',
                'prepayment_in_rial_numerical',
                'final_amount_in_rial_numerical',
            ] as $key) {
                if (isset($extracted['financial_summary'][$key])) {
                    $extracted['financial_summary'][$key] = $apply($extracted['financial_summary'][$key]);
                }
            }
        }

        if (isset($extracted['analytics']['financial_breakdown']) && is_array($extracted['analytics']['financial_breakdown'])) {
            foreach ([
                'subtotal',
                'tax',
                'transport',
                'service_fee',
                'other_charges',
                'prepayment',
                'discount',
                'line_items_total',
                'computed_final_amount',
            ] as $key) {
                if (isset($extracted['analytics']['financial_breakdown'][$key])) {
                    $extracted['analytics']['financial_breakdown'][$key] = $apply($extracted['analytics']['financial_breakdown'][$key]);
                }
            }
        }

        if (isset($extracted['analytics']['validation']['difference'])) {
            $extracted['analytics']['validation']['difference'] = $apply($extracted['analytics']['validation']['difference']);
        }

        if (isset($extracted['raw_payload']['structured']) && is_array($extracted['raw_payload']['structured'])) {
            $extracted['raw_payload']['structured'] = $this->convertStructuredPayloadAmounts(
                $extracted['raw_payload']['structured'],
                $factor
            );
        }

        return $extracted;
    }

    protected function convertStructuredPayloadAmounts(array $structured, float $factor): array
    {
        $numericKeys = [
            'subtotal_in_rial_numerical',
            'total_discount_in_rial_numerical',
            'vat_and_tolls_amount_in_rial_numerical',
            'transport_total_in_rial_numerical',
            'service_total_in_rial_numerical',
            'other_charges_in_rial_numerical',
            'prepayment_in_rial_numerical',
            'final_amount_in_rial_numerical',
            'amount',
            'tax_amount',
        ];

        $apply = function ($value) use ($factor) {
            return is_numeric($value) ? (float) $value * $factor : $value;
        };

        foreach ($numericKeys as $key) {
            if (isset($structured[$key])) {
                $structured[$key] = $apply($structured[$key]);
            }
        }

        if (isset($structured['items_details']['item_structure']) && is_array($structured['items_details']['item_structure'])) {
            $structured['items_details']['item_structure'] = array_map(function (array $item) use ($apply) {
                foreach ([
                    'unit_price_in_rial_numerical',
                    'discount_per_item_in_rial_numerical',
                    'tax_per_item_in_rial_numerical',
                    'total_price_in_rial_numerical',
                    'total_after_discount_in_rial_numerical',
                ] as $key) {
                    if (isset($item[$key])) {
                        $item[$key] = $apply($item[$key]);
                    }
                }

                return $item;
            }, $structured['items_details']['item_structure']);
        }

        if (isset($structured['line_items']) && is_array($structured['line_items'])) {
            $structured['line_items'] = array_map(function (array $item) use ($apply) {
                foreach (['unit_price', 'discount', 'tax', 'total'] as $key) {
                    if (isset($item[$key])) {
                        $item[$key] = $apply($item[$key]);
                    }
                }

                return $item;
            }, $structured['line_items']);
        }

        if (isset($structured['financial_summary']) && is_array($structured['financial_summary'])) {
            foreach ([
                'subtotal_in_rial_numerical',
                'total_discount_in_rial_numerical',
                'vat_and_tolls_amount_in_rial_numerical',
                'transport_total_in_rial_numerical',
                'service_total_in_rial_numerical',
                'other_charges_in_rial_numerical',
                'prepayment_in_rial_numerical',
                'final_amount_in_rial_numerical',
            ] as $key) {
                if (isset($structured['financial_summary'][$key])) {
                    $structured['financial_summary'][$key] = $apply($structured['financial_summary'][$key]);
                }
            }
        }

        return $structured;
    }

    protected function reconcileFinancialSummary(array $financial, array $lineItems): array
    {
        $tolerance = $this->getValidationTolerance();

        if (! isset($financial['issues']) || ! is_array($financial['issues'])) {
            $financial['issues'] = [];
        }

        $lineItemsTotal = $this->calculateLineItemsTotal($lineItems);
        if ($lineItemsTotal !== null && ! isset($financial['subtotal'])) {
            $financial['subtotal'] = $lineItemsTotal;
        }

        $subtotal = $financial['subtotal'] ?? null;

        $components = [
            'tax' => $financial['tax'] ?? null,
            'transport' => $financial['transport'] ?? null,
            'service_fee' => $financial['service_fee'] ?? null,
            'other_charges' => $financial['other_charges'] ?? null,
            'discount' => $financial['discount'] ?? null,
            'prepayment' => $financial['prepayment'] ?? null,
        ];

        $computedFinal = null;
        if ($subtotal !== null) {
            $computedFinal = $subtotal;
            $computedFinal += $components['tax'] ?? 0;
            $computedFinal += $components['transport'] ?? 0;
            $computedFinal += $components['service_fee'] ?? 0;
            $computedFinal += $components['other_charges'] ?? 0;
            $computedFinal -= $components['discount'] ?? 0;
            $computedFinal -= $components['prepayment'] ?? 0;
        }

        if ($computedFinal === null && $lineItemsTotal !== null) {
            $computedFinal = $lineItemsTotal;
        }

        $finalAmount = $financial['final_amount'] ?? null;

        if ($finalAmount === null && $computedFinal !== null) {
            $finalAmount = $computedFinal;
        }

        if ($finalAmount !== null && $computedFinal !== null) {
            $difference = abs($finalAmount - $computedFinal);
            if ($difference > $tolerance) {
                $financial['difference'] = $difference;
                $financial['issues'][] = __('اختلاف جمع نهایی با اجزای محاسباتی بیش از حد مجاز است. جمع محاسبه‌شده جایگزین شد.');
                $finalAmount = $computedFinal;
            }
            $financial['final_matches_breakdown'] = $difference <= $tolerance;
        }

        if ($finalAmount !== null) {
            $financial['final_amount'] = $finalAmount;
        }

        if ($lineItemsTotal !== null && $subtotal !== null) {
            $lineItemDiff = abs($lineItemsTotal - $subtotal);
            if ($lineItemDiff > $tolerance) {
                $financial['issues'][] = __('مجموع اقلام با جمع سطر فاکتور هم‌خوانی ندارد.');
            }
            $financial['line_items_match'] = $lineItemDiff <= $tolerance;
        }

        $financial['line_items_total'] = $lineItemsTotal;
        $financial['computed_final_amount'] = $computedFinal;

        return $financial;
    }

    protected function hasMeaningfulData(array $extracted): bool
    {
        $scalarFields = [
            'total_amount',
            'tax_amount',
            'invoice_number',
            'reference_number',
            'vendor_name',
            'customer_name',
        ];

        foreach ($scalarFields as $field) {
            if (isset($extracted[$field]) && $extracted[$field] !== null && $extracted[$field] !== '' && $extracted[$field] !== 0) {
                return true;
            }
        }

        if (! empty($extracted['line_items'])) {
            return true;
        }

        $financial = $extracted['analytics']['financial_breakdown'] ?? [];
        foreach (['subtotal', 'tax', 'transport', 'service_fee', 'computed_final_amount', 'line_items_total'] as $key) {
            if (isset($financial[$key]) && $financial[$key]) {
                return true;
            }
        }

        return false;
    }

    protected function calculateLineItemsTotal(array $lineItems): ?float
    {
        $totals = array_map(static function (array $item) {
            $description = mb_strtolower($item['description'] ?? '');

            if ($description !== '') {
                if (str_contains($description, 'مالیات') || str_contains($description, 'عوارض') || str_contains($description, 'جمع کل') || str_contains($description, 'قابل پرداخت')) {
                    return null;
                }
            }

            if (isset($item['total']) && is_numeric($item['total'])) {
                return (float) $item['total'];
            }

            if (isset($item['quantity'], $item['unit_price']) && is_numeric($item['quantity']) && is_numeric($item['unit_price'])) {
                return (float) $item['quantity'] * (float) $item['unit_price'];
            }

            return null;
        }, $lineItems);

        $totals = array_filter($totals, static fn ($value) => $value !== null);

        if (empty($totals)) {
            return null;
        }

        return array_sum($totals);
    }

    /**
     * @param  array<string,mixed>  $validation
     * @param  array<string,mixed>  $financial
     */
    protected function calculateConfidence(array $validation, array $financial, ?float $modelConfidence): float
    {
        $confidence = is_numeric($modelConfidence) ? (float) $modelConfidence : 0.6;

        $confidence = max(0, min(1, $confidence));

        if (($financial['final_matches_breakdown'] ?? true) === false) {
            $confidence -= 0.2;
        }

        if (($financial['line_items_match'] ?? true) === false) {
            $confidence -= 0.1;
        }

        if (empty($financial['final_amount']) && empty($financial['computed_final_amount'])) {
            $confidence -= 0.15;
        }

        if (! empty($validation['issues'])) {
            $confidence -= min(0.1 * count($validation['issues']), 0.2);
        }

        if (($financial['raw_currency'] ?? 'IRR') !== 'IRR') {
            $confidence -= 0.05;
        }

        return max(0, min(1, $confidence));
    }

    /**
     * @param  array<string,mixed>  $financial
     * @param  array<string,mixed>  $dates
     * @param  array<string,mixed>  $data
     */
    protected function normalizeInvoiceDate(array $financial, array $dates, array $data): ?string
    {
        $gregorian = $dates['gregorian'] ?? null;
        if (is_string($gregorian) && trim($gregorian) !== '') {
            try {
                return Carbon::parse($gregorian)->toIso8601String();
            } catch (\Throwable) {
                // continue with Jalali parsing
            }
        }

        $jalali = $dates['jalali'] ?? ($data['date_jalali'] ?? null);
        $time = $dates['time'] ?? ($data['time'] ?? null);

        if (is_string($jalali) && trim($jalali) !== '') {
            try {
                [$datePart, $timePart] = array_pad(preg_split('/\s+/', trim($jalali)), 2, null);
                $datePart = str_replace('-', '/', $datePart ?? '');
                [$year, $month, $day] = array_map('intval', array_pad(explode('/', $datePart), 3, null));

                if ($year && $month && $day) {
                    $verta = new Verta();
                    $verta->setDate($year, $month, $day);
                    $carbon = $verta->toCarbon();

                    $timeSource = $time ?: $timePart;
                    if ($timeSource) {
                        [$hour, $minute, $second] = array_map('intval', array_pad(explode(':', $timeSource), 3, 0));
                        $carbon->setTime($hour, $minute, $second);
                    }

                    return $carbon->toIso8601String();
                }
            } catch (\Throwable $exception) {
                Log::debug('Failed to normalise Jalali date', ['error' => $exception->getMessage()]);
            }
        }

        if (isset($financial['metadata']['dates']['gregorian'])) {
            try {
                return Carbon::parse($financial['metadata']['dates']['gregorian'])->toIso8601String();
            } catch (\Throwable) {
                // ignore
            }
        }

        return null;
    }

    protected function buildPromptContext(array $context): string
    {
        $segments = [];

        if (! empty($context['ledger_id'])) {
            $segments[] = "Ledger ID: {$context['ledger_id']}";
        }

        if (! empty($context['transaction_type'])) {
            $segments[] = "Transaction type: {$context['transaction_type']}";
        }

        if (! empty($context['existing_amount'])) {
            $segments[] = "Existing amount in form: {$context['existing_amount']}";
        }

        if (! empty($segments)) {
            return implode(' | ', $segments);
        }

        return '';
    }

    protected function getPromptText(array $context = []): string
    {
        return (new GeminiPromptBuilder())->buildExtractionPrompt($context);
    }

    /**
     * @param  TemporaryUploadedFile|UploadedFile|null  $invoice
     * @param  TemporaryUploadedFile|UploadedFile|null  $receipt
     */
    protected function buildRequestParts($invoice, $receipt, string $prompt): array
    {
        $parts = [
            [
                'text' => $prompt,
            ],
        ];

        if ($invoice) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $this->getMimeType($invoice),
                    'data' => base64_encode($this->readStream($invoice)),
                ],
            ];
        }

        if ($receipt) {
            $parts[] = [
                'inline_data' => [
                    'mime_type' => $this->getMimeType($receipt),
                    'data' => base64_encode($this->readStream($receipt)),
                ],
            ];
        }

        return $parts;
    }

    protected function buildOpenAiImageContent($file): array
    {
        if ($file === null) {
            return [];
        }

        $url = $this->ensurePublicOpenAiUrl($file);

        if (! $url) {
            return [];
        }

        return [
            [
                'type' => 'input_image',
                'image_url' => $url,
            ],
        ];
    }

    protected function ensurePublicOpenAiUrl($file): ?string
    {
        try {
            $disk = Storage::disk('public');
            $directory = 'smart-invoice/openai';

            if ($file instanceof TemporaryUploadedFile) {
                $path = $file->store($directory, 'public');
            } elseif ($file instanceof UploadedFile) {
                $path = $disk->putFile($directory, $file);
            } else {
                $stream = $this->readStream($file);
                if ($stream === '') {
                    return null;
                }
                $path = $directory . '/' . Str::random(20) . '.' . ($file->extension() ?? 'png');
                $disk->put($path, $stream);
            }

            if (! $path) {
                return null;
            }

            $relativeUrl = $disk->url($path);

            if (str_starts_with($relativeUrl, 'http')) {
                return $relativeUrl;
            }

            return rtrim(config('app.url'), '/') . $relativeUrl;
        } catch (\Throwable $exception) {
            Log::error('Failed to generate public URL for OpenAI vision', [
                'message' => $exception->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function buildFunctionTools(): array
    {
        return [
            [
                'functionDeclarations' => [
                    $this->invoiceResponseSchema(),
                ],
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function invoiceResponseSchema(): array
    {
        $lineItemSchema = (object) [
            'type' => 'object',
            'properties' => (object) [
                'row_number' => (object) ['type' => 'number'],
                'product_or_service_description_fa' => (object) ['type' => 'string'],
                'quantity_numerical' => (object) ['type' => 'number'],
                'unit_fa' => (object) ['type' => 'string'],
                'unit_price_in_rial_numerical' => (object) ['type' => 'number'],
                'discount_per_item_in_rial_numerical' => (object) ['type' => 'number'],
                'tax_per_item_in_rial_numerical' => (object) ['type' => 'number'],
                'total_price_in_rial_numerical' => (object) ['type' => 'number'],
                'total_after_discount_in_rial_numerical' => (object) ['type' => 'number'],
                'currency_source' => (object) ['type' => 'string'],
            ],
        ];

        return [
            'name' => 'set_invoice',
            'description' => 'Return the structured invoice extraction result.',
            'parameters' => (object) [
                'type' => 'object',
                'properties' => (object) [
                    'document_type_fa' => (object) ['type' => 'string'],
                    'invoice_serial_number' => (object) ['type' => 'string'],
                    'origin_of_document_fa' => (object) ['type' => 'string'],
                    'date_jalali' => (object) ['type' => 'string'],
                    'time' => (object) ['type' => 'string'],
                    'seller_info' => (object) [
                        'type' => 'object',
                        'properties' => (object) [
                            'name_fa' => (object) ['type' => 'string'],
                            'economic_code_or_national_id' => (object) ['type' => 'string'],
                            'phone_number' => (object) ['type' => 'string'],
                            'address_fa' => (object) ['type' => 'string'],
                            'postal_code' => (object) ['type' => 'string'],
                        ],
                    ],
                    'buyer_info' => (object) [
                        'type' => 'object',
                        'properties' => (object) [
                            'name_fa' => (object) ['type' => 'string'],
                            'national_code' => (object) ['type' => 'string'],
                            'phone_number' => (object) ['type' => 'string'],
                            'address_fa' => (object) ['type' => 'string'],
                        ],
                    ],
                    'items_details' => (object) [
                        'type' => 'object',
                        'properties' => (object) [
                            'item_structure' => (object) [
                                'type' => 'array',
                                'items' => $lineItemSchema,
                            ],
                        ],
                    ],
                    'financial_summary' => (object) [
                        'type' => 'object',
                        'properties' => (object) [
                            'subtotal_in_rial_numerical' => (object) ['type' => 'number'],
                            'total_discount_in_rial_numerical' => (object) ['type' => 'number'],
                            'vat_and_tolls_amount_in_rial_numerical' => (object) ['type' => 'number'],
                            'transport_total_in_rial_numerical' => (object) ['type' => 'number'],
                            'service_total_in_rial_numerical' => (object) ['type' => 'number'],
                            'other_charges_in_rial_numerical' => (object) ['type' => 'number'],
                            'prepayment_in_rial_numerical' => (object) ['type' => 'number'],
                            'final_amount_in_rial_numerical' => (object) ['type' => 'number'],
                            'raw_currency' => (object) ['type' => 'string'],
                            'currency_source_note' => (object) ['type' => 'string'],
                        ],
                    ],
                    'payment_and_banking_details' => (object) [
                        'type' => 'object',
                        'properties' => (object) [
                            'payment_method_fa' => (object) ['type' => 'string'],
                            'bank_name_fa' => (object) ['type' => 'string'],
                            'reference_number_or_sheba' => (object) ['type' => 'string'],
                            'account_holder_fa' => (object) ['type' => 'string'],
                        ],
                    ],
                    'validation' => (object) [
                        'type' => 'object',
                        'properties' => (object) [
                            'items_sum_matches_subtotal' => (object) ['type' => 'boolean'],
                            'calculations_verified' => (object) ['type' => 'boolean'],
                            'confidence_score' => (object) ['type' => 'number'],
                            'issues' => (object) [
                                'type' => 'array',
                                'items' => (object) ['type' => 'string'],
                            ],
                        ],
                    ],
                    'raw_payload' => (object) [
                        'type' => 'object',
                        'properties' => (object) [
                            'content' => (object) ['type' => 'string'],
                        ],
                    ],
                    'analytics' => (object) [
                        'type' => 'object',
                    ],
                ],
            ],
        ];
    }

    protected function repairMalformedJson(string $content): ?array
    {
        $trimmed = trim($content);
        if ($trimmed === '') {
            return null;
        }

        $apiKey = $this->getSetting('smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key'));
        $model = $this->getSetting('smart-invoice.gemini.model', config('smart-invoice.gemini.model', 'gemini-2.5-flash'));
        $timeout = $this->getSetting('smart-invoice.gemini.timeout', config('smart-invoice.gemini.timeout', 45));

        $prompt = (new GeminiPromptBuilder())->buildRepairPrompt($trimmed);
        $requestData = [
            'contents' => [
                [
                    'parts' => [
                        [
                            'text' => $prompt,
                        ],
                    ],
                ],
            ],
            'generationConfig' => [
                'temperature' => 0.1,
                'topK' => 32,
                'topP' => 1,
                'maxOutputTokens' => 1024,
            ],
        ];

        $url = "https://generativelanguage.googleapis.com/v1beta/models/{$model}:generateContent?key={$apiKey}";

        try {
            $response = $this->http
                ->timeout((int) $timeout)
                ->withHeaders([
                    'Content-Type' => 'application/json',
                ])
                ->post($url, $requestData);

            if ($response->failed()) {
                return null;
            }

            $result = $response->json('candidates.0.content.parts.0.text');
            if (! is_string($result) || trim($result) === '') {
                return null;
            }

            $repairedText = trim(preg_replace('/```(?:json)?/i', '', $result) ?? $result);
            $decoded = json_decode($repairedText, true);

            if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
                Log::warning('Gemini repair returned invalid JSON', [
                    'error' => json_last_error_msg(),
                    'preview' => mb_substr($repairedText, 0, 400),
                ]);

                return null;
            }

            Log::info('Gemini JSON repaired successfully');

            return $decoded;
        } catch (\Throwable $exception) {
            Log::warning('Gemini JSON repair failed', ['message' => $exception->getMessage()]);

            return null;
        }
    }

    protected function persistRawContentForDebug(string $content): void
    {
        if ($content === '') {
            return;
        }

        $directory = storage_path('logs/smart-invoice');
        if (! is_dir($directory)) {
            try {
                mkdir($directory, 0775, true);
            } catch (\Throwable $exception) {
                Log::warning('Failed to create smart-invoice log directory', ['message' => $exception->getMessage()]);

                return;
            }
        }

        $filename = sprintf(
            '%s/%s_%s.txt',
            $directory,
            now()->format('Ymd_His'),
            Str::random(6)
        );

        try {
            File::put($filename, $content);
            Log::info('Saved Gemini raw content for debugging', ['path' => $filename]);
        } catch (\Throwable $exception) {
            Log::warning('Failed to persist Gemini raw content', ['message' => $exception->getMessage()]);
        }
    }

    protected function getMimeType($file): string
    {
        $mimeType = $file->getMimeType();

        return match ($mimeType) {
            'image/jpeg' => 'image/jpeg',
            'image/png' => 'image/png',
            'image/webp' => 'image/webp',
            'application/pdf' => 'application/pdf',
            default => 'image/jpeg',
        };
    }

    protected function readStream($file): string
    {
        $path = $file->getRealPath();

        if (! $path || ! is_readable($path)) {
            throw SmartInvoiceException::requestFailed(__('فایل بارگذاری شده قابل خواندن نیست.'));
        }

        $binary = file_get_contents($path);
        if ($binary === false) {
            throw SmartInvoiceException::requestFailed(__('خواندن داده‌های فایل ناموفق بود.'));
        }

        return $binary;
    }

    protected function stringifyMessage(mixed $message): string
    {
        if (is_string($message)) {
            return $message;
        }

        if (is_array($message) || is_object($message)) {
            return json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '[unserializable]';
        }

        if (is_bool($message)) {
            return $message ? 'true' : 'false';
        }

        return (string) $message;
    }

    /**
     * @return mixed
     */
    protected function getSetting(string $key, mixed $default = null)
    {
        $legacyKeys = $this->legacySettingKeys($key);

        $candidates = array_merge([$key], $legacyKeys);

        foreach ($candidates as $candidate) {
            $value = Config::get('settings.' . $candidate);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }

    /**
     * @return array<int, string>
     */
    protected function legacySettingKeys(string $key): array
    {
        return match ($key) {
            'smart-invoice.provider' => ['smart_invoice_provider'],
            'smart-invoice.gemini.enabled' => ['smart_invoice_gemini_enabled'],
            'smart-invoice.gemini.api_key' => ['smart_invoice_gemini_api_key'],
            'smart-invoice.gemini.model' => ['smart_invoice_gemini_model'],
            'smart-invoice.gemini.timeout' => ['smart_invoice_gemini_timeout'],
            'smart-invoice.gemini.max_output_tokens' => ['smart_invoice_gemini_max_tokens'],
            'smart-invoice.openai.enabled' => ['smart_invoice_openai_enabled'],
            'smart-invoice.openai.api_key' => ['smart_invoice_openai_api_key'],
            'smart-invoice.openai.model' => ['smart_invoice_openai_model'],
            'smart-invoice.openai.timeout' => ['smart_invoice_openai_timeout'],
            'smart-invoice.openai.max_output_tokens' => ['smart_invoice_openai_max_tokens'],
            'smart-invoice.confidence_threshold' => ['smart_invoice_confidence_threshold'],
            'smart-invoice.analytics' => ['smart_invoice_analytics'],
            'smart-invoice.validation.tolerance' => ['smart_invoice_validation_tolerance'],
            default => [],
        };
    }

    protected function canFallbackToGemini(): bool
    {
        $enabled = $this->getSetting('smart-invoice.gemini.enabled', config('smart-invoice.gemini.enabled'));
        $apiKey = $this->getSetting('smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key'));

        return (bool) $enabled && filled($apiKey);
    }

    protected function getProvider(): string
    {
        return $this->getSetting('smart-invoice.provider', config('smart-invoice.provider', 'gemini'));
    }
}
