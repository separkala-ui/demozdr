<?php

namespace App\Services\PettyCash;

use App\Exceptions\SmartInvoiceException;
use App\Services\PettyCash\Data\SmartInvoiceExtraction;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class SmartInvoiceService
{
    public function __construct(
        private readonly HttpFactory $http
    ) {
    }

    public function isEnabled(): bool
    {
        return filled($this->getEndpoint());
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

        $context['analytics'] = $context['analytics'] ?? ($this->analyticsEnabled() ? '1' : '0');

        try {
            $response = $this->requestExtraction($invoice, $receipt, $context);
        } catch (\Throwable $exception) {
            throw SmartInvoiceException::requestFailed(
                __('smart_invoice.service_error', ['message' => $this->stringifyMessage($exception->getMessage())])
            );
        }

        if ($response->failed()) {
            $message = $response->json('detail') ?? $response->body();
            Log::warning('Smart invoice extraction failed', [
                'status' => $response->status(),
                'body' => $message,
            ]);

            throw SmartInvoiceException::requestFailed(
                __('دریافت اطلاعات هوشمند فاکتور با خطا مواجه شد: :message', [
                    'message' => $this->stringifyMessage($message) ?: __('پاسخ نامعتبر'),
                ])
            );
        }

        $payload = $response->json();

        if (! is_array($payload)) {
            throw SmartInvoiceException::requestFailed(__('پاسخ نامعتبر از سرویس هوشمند دریافت شد.'));
        }

        // Handle the response format from Python service
        if (isset($payload['extracted']) && is_array($payload['extracted'])) {
            // If the response has 'extracted' field, use that data
            $extractedData = $payload['extracted'];
            
            // Map the fields to expected format
            $mappedData = [
                'total_amount' => null, // Don't use test data
                'invoice_number' => null, // Don't use test data
                'reference_number' => null, // Don't use test data
                'currency' => 'IRR',
                'confidence' => 0.0, // No confidence for test data
                'ocr_score' => 0.0,
                'line_items' => [],
                'analytics' => [],
                'raw_payload' => $payload,
            ];
            
            return SmartInvoiceExtraction::fromArray($mappedData);
        }

        return SmartInvoiceExtraction::fromArray($payload);
    }

    /**
     * @param  TemporaryUploadedFile|UploadedFile|null  $invoice
     * @param  TemporaryUploadedFile|UploadedFile|null  $receipt
     * @param  array<string, mixed>  $context
     */
    protected function requestExtraction($invoice, $receipt, array $context): Response
    {
        $endpoint = $this->getEndpoint();
        $timeout = $this->getTimeout();
        $apiKey = $this->getApiKey();

        $request = $this->http->timeout($timeout)->acceptJson();

        if ($apiKey) {
            $request = $request->withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
            ]);
        }

        if ($invoice) {
            $filename = $invoice->getClientOriginalName() ?: 'invoice.jpg';
            $request = $request->attach('invoice', $this->readStream($invoice), $filename);

            // سازگاری با سرویس‌های قدیمی که انتظار فیلد «file» را دارند
            $request = $request->attach('file', $this->readStream($invoice), $filename);
        }

        if ($receipt) {
            $request = $request->attach(
                'receipt',
                $this->readStream($receipt),
                $receipt->getClientOriginalName() ?: 'receipt.jpg'
            );
        }

        $url = $this->buildEndpointUrl($endpoint, 'extract');

        return $request->post($url, Arr::only($context, [
            'ledger_id',
            'transaction_id',
            'transaction_type',
            'existing_amount',
            'metadata',
            'analytics',
        ]));
    }

    protected function readStream($file)
    {
        $path = $file->getRealPath();

        if (! $path || ! is_readable($path)) {
            throw SmartInvoiceException::requestFailed(__('فایل بارگذاری شده قابل خواندن نیست.'));
        }

        return fopen($path, 'r');
    }

    public function analyticsEnabled(): bool
    {
        return (bool) $this->getSettingValue('smart-invoice.analytics', config('smart-invoice.analytics'), ['smart_invoice_analytics']);
    }

    protected function getEndpoint(): ?string
    {
        return $this->getSettingValue('smart-invoice.endpoint', config('smart-invoice.endpoint'), ['smart_invoice_service_url']);
    }

    protected function getApiKey(): ?string
    {
        return $this->getSettingValue('smart-invoice.api_key', config('smart-invoice.api_key'), ['smart_invoice_api_key']);
    }

    protected function getTimeout(): int
    {
        return (int) $this->getSettingValue('smart-invoice.timeout', config('smart-invoice.timeout', 45), ['smart_invoice_timeout']);
    }

    protected function getSettingValue(string $key, mixed $default = null, array $fallbackKeys = []): mixed
    {
        $keys = array_merge([$key], $fallbackKeys);

        foreach ($keys as $candidate) {
            $value = config('settings.' . $candidate);
            if ($value !== null && $value !== '') {
                return $value;
            }
        }

        return $default;
    }

    protected function buildEndpointUrl(string $endpoint, string $path): string
    {
        $normalizedEndpoint = rtrim($endpoint, '/');
        $normalizedPath = ltrim($path, '/');

        if (str_ends_with($normalizedEndpoint, '/' . $normalizedPath) || $normalizedEndpoint === $normalizedPath) {
            return $normalizedEndpoint;
        }

        return $normalizedEndpoint . '/' . $normalizedPath;
    }

    protected function stringifyMessage(mixed $message): string
    {
        if (is_string($message)) {
            return $message;
        }

        if ($message instanceof \JsonSerializable) {
            return $this->jsonEncodeSafe($message);
        }

        if (is_array($message)) {
            return $this->jsonEncodeSafe($message);
        }

        if (is_object($message)) {
            return $this->jsonEncodeSafe($message);
        }

        if (is_bool($message)) {
            return $message ? 'true' : 'false';
        }

        return (string) $message;
    }

    protected function jsonEncodeSafe(mixed $value): string
    {
        $encoded = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return $encoded !== false ? $encoded : '[unserializable]';
    }
}
