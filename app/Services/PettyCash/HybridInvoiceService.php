<?php

namespace App\Services\PettyCash;

use App\Exceptions\SmartInvoiceException;
use App\Services\PettyCash\Data\SmartInvoiceExtraction;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

class HybridInvoiceService
{
    public function __construct(
        private readonly SmartInvoiceService $pythonService,
        private readonly GeminiInvoiceService $geminiService
    ) {
    }

    public function isEnabled(): bool
    {
        return $this->pythonService->isEnabled() || $this->geminiService->isEnabled();
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

        $primaryService = config('smart-invoice.primary_service', 'python');
        $fallbackService = config('smart-invoice.fallback_service', 'gemini');

        // Try primary service first
        try {
            if ($primaryService === 'python' && $this->pythonService->isEnabled()) {
                Log::info('Using Python service for invoice extraction');
                return $this->pythonService->extractFromUploads($invoice, $receipt, $context);
            } elseif ($primaryService === 'gemini' && $this->geminiService->isEnabled()) {
                Log::info('Using Gemini service for invoice extraction');
                return $this->geminiService->extractFromUploads($invoice, $receipt, $context);
            }
        } catch (\Throwable $exception) {
            Log::warning('Primary service failed, trying fallback', [
                'primary' => $primaryService,
                'error' => $exception->getMessage()
            ]);
        }

        // Try fallback service
        try {
            if ($fallbackService === 'python' && $this->pythonService->isEnabled()) {
                Log::info('Using Python fallback service for invoice extraction');
                return $this->pythonService->extractFromUploads($invoice, $receipt, $context);
            } elseif ($fallbackService === 'gemini' && $this->geminiService->isEnabled()) {
                Log::info('Using Gemini fallback service for invoice extraction');
                return $this->geminiService->extractFromUploads($invoice, $receipt, $context);
            }
        } catch (\Throwable $exception) {
            Log::error('Both services failed', [
                'primary' => $primaryService,
                'fallback' => $fallbackService,
                'error' => $exception->getMessage()
            ]);
        }

        throw SmartInvoiceException::requestFailed(
            __('هیچ سرویس هوشمند فاکتور در دسترس نیست.')
        );
    }

    public function getAvailableServices(): array
    {
        $services = [];

        if ($this->pythonService->isEnabled()) {
            $services['python'] = [
                'name' => 'Python OCR Service',
                'endpoint' => config('settings.smart-invoice.endpoint', config('smart-invoice.endpoint')),
                'status' => 'active'
            ];
        }

        if ($this->geminiService->isEnabled()) {
            $services['gemini'] = [
                'name' => 'Google Gemini AI',
                'model' => config('settings.smart-invoice.gemini.model', config('smart-invoice.gemini.model')),
                'status' => 'active'
            ];
        }

        return $services;
    }

    public function getServiceStatus(): array
    {
        return [
            'python' => [
                'enabled' => $this->pythonService->isEnabled(),
                'endpoint' => config('settings.smart-invoice.endpoint', config('smart-invoice.endpoint')),
            ],
            'gemini' => [
                'enabled' => $this->geminiService->isEnabled(),
                'api_key_set' => filled(config('settings.smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key'))),
                'model' => config('settings.smart-invoice.gemini.model', config('smart-invoice.gemini.model')),
            ],
            'primary_service' => config('settings.smart-invoice.primary_service', config('smart-invoice.primary_service', 'python')),
            'fallback_service' => config('settings.smart-invoice.fallback_service', config('smart-invoice.fallback_service', 'gemini')),
        ];
    }

    public function analyticsEnabled(): bool
    {
        return (bool) config('settings.smart-invoice.analytics', config('smart-invoice.analytics', false));
    }

    public function getConfidenceThreshold(): float
    {
        return (float) config('smart-invoice.confidence_threshold', 0.5);
    }

    public function getTimeout(): int
    {
        return (int) config('smart-invoice.timeout', 45);
    }
}
