<?php

namespace App\Http\Controllers\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Services\PettyCash\HybridInvoiceService;
use App\Services\PettyCash\GeminiModelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class SmartInvoiceSettingsController extends Controller
{
    public function __construct(
        private readonly HybridInvoiceService $hybridService,
        private readonly GeminiModelService $modelService
    ) {
    }

    public function index()
    {
        $serviceStatus = $this->hybridService->getServiceStatus();
        $availableModels = $this->modelService->getRecommendedModels();

        $formData = [
            'primary_service' => config('settings.smart-invoice.primary_service', config('smart-invoice.primary_service', 'gemini')),
            'fallback_service' => config('settings.smart-invoice.fallback_service', config('smart-invoice.fallback_service', 'python')),
            'gemini_enabled' => (bool) config('settings.smart-invoice.gemini.enabled', config('smart-invoice.gemini.enabled', false)),
            'analytics_enabled' => (bool) config('settings.smart-invoice.analytics', config('smart-invoice.analytics', true)),
            'confidence_threshold' => config('settings.smart-invoice.confidence_threshold', config('smart-invoice.confidence_threshold', 0.5)),
            'gemini_api_key' => config('settings.smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key')),
            'gemini_model' => config('settings.smart-invoice.gemini.model', config('smart-invoice.gemini.model', 'gemini-2.5-flash')),
            'gemini_timeout' => config('settings.smart-invoice.gemini.timeout', config('smart-invoice.gemini.timeout', 30)),
            'python_endpoint' => config('settings.smart-invoice.endpoint', config('settings.smart_invoice_service_url', config('smart-invoice.endpoint'))),
            'python_timeout' => config('settings.smart-invoice.timeout', config('settings.smart_invoice_timeout', config('smart-invoice.timeout', 45))),
            'python_api_key' => config('settings.smart-invoice.api_key', config('settings.smart_invoice_api_key', config('smart-invoice.api_key'))),
            'notes' => config('settings.smart_invoice_notes', ''),
        ];

        return view('backend.pages.settings.smart-invoice-settings', [
            'availableModels' => $availableModels,
            'serviceStatus' => $serviceStatus,
            'formData' => $formData,
        ]);
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'primary_service' => ['required', 'in:python,gemini'],
            'fallback_service' => ['required', 'in:python,gemini', 'different:primary_service'],
            'python_endpoint' => 'nullable|url',
            'python_timeout' => 'nullable|integer|min:10|max:120',
            'gemini_enabled' => 'sometimes|boolean',
            'gemini_api_key' => 'nullable|string',
            'gemini_model' => 'nullable|string',
            'gemini_timeout' => 'nullable|integer|min:10|max:120',
            'confidence_threshold' => 'nullable|numeric|min:0|max:1',
            'analytics_enabled' => 'sometimes|boolean',
            'python_api_key' => 'nullable|string',
            'smart_invoice_notes' => 'nullable|string',
        ]);

        $settingService = app(\App\Services\SettingService::class);

        $geminiEnabled = $request->boolean('gemini_enabled');
        $analyticsEnabled = $request->boolean('analytics_enabled');

        $settings = [
            'smart-invoice.primary_service' => $validated['primary_service'],
            'smart-invoice.fallback_service' => $validated['fallback_service'],
            'smart-invoice.endpoint' => $validated['python_endpoint'] ?? '',
            'smart-invoice.timeout' => $validated['python_timeout'] ?? 45,
            'smart-invoice.api_key' => $validated['python_api_key'] ?? null,
            'smart-invoice.gemini.enabled' => $geminiEnabled,
            'smart-invoice.gemini.api_key' => $validated['gemini_api_key'] ?? null,
            'smart-invoice.gemini.model' => $validated['gemini_model'] ?? 'gemini-2.5-flash',
            'smart-invoice.gemini.timeout' => $validated['gemini_timeout'] ?? 30,
            'smart-invoice.confidence_threshold' => $validated['confidence_threshold'] ?? 0.5,
            'smart-invoice.analytics' => $analyticsEnabled,
            'smart_invoice_notes' => $validated['smart_invoice_notes'] ?? null,
            // Legacy keys for backward compatibility
            'smart_invoice_primary_service' => $validated['primary_service'],
            'smart_invoice_fallback_service' => $validated['fallback_service'],
            'smart_invoice_service_url' => $validated['python_endpoint'] ?? '',
            'smart_invoice_timeout' => $validated['python_timeout'] ?? 45,
            'smart_invoice_api_key' => $validated['python_api_key'] ?? null,
            'smart_invoice_analytics' => $analyticsEnabled ? '1' : '0',
            'smart_invoice_confidence_threshold' => $validated['confidence_threshold'] ?? 0.5,
            'smart_invoice_gemini_enabled' => $geminiEnabled ? '1' : '0',
            'smart_invoice_gemini_api_key' => $validated['gemini_api_key'] ?? null,
            'smart_invoice_gemini_model' => $validated['gemini_model'] ?? 'gemini-2.5-flash',
            'smart_invoice_gemini_timeout' => $validated['gemini_timeout'] ?? 30,
        ];

        foreach ($settings as $key => $value) {
            $settingService->addSetting($key, $value);
        }

        config([
            'smart-invoice.primary_service' => $settings['smart-invoice.primary_service'],
            'smart-invoice.fallback_service' => $settings['smart-invoice.fallback_service'],
            'smart-invoice.endpoint' => $settings['smart-invoice.endpoint'],
            'smart-invoice.timeout' => $settings['smart-invoice.timeout'],
            'smart-invoice.api_key' => $settings['smart-invoice.api_key'],
            'smart-invoice.gemini.enabled' => $settings['smart-invoice.gemini.enabled'],
            'smart-invoice.gemini.api_key' => $settings['smart-invoice.gemini.api_key'],
            'smart-invoice.gemini.model' => $settings['smart-invoice.gemini.model'],
            'smart-invoice.gemini.timeout' => $settings['smart-invoice.gemini.timeout'],
            'smart-invoice.confidence_threshold' => $settings['smart-invoice.confidence_threshold'],
            'smart-invoice.analytics' => $settings['smart-invoice.analytics'],
            'settings.smart_invoice_notes' => $settings['smart_invoice_notes'],
        ]);

        // Clear config cache
        try {
            Artisan::call('config:clear');
        } catch (\Exception $e) {
            \Log::warning('Could not clear config cache', ['error' => $e->getMessage()]);
        }

        return redirect()
            ->route('admin.settings.smart-invoice.index')
            ->with('success', 'تنظیمات هوشمند فاکتور با موفقیت ذخیره شد.');
    }

    public function testServices()
    {
        try {
            $services = $this->hybridService->getAvailableServices();
            
            return response()->json([
                'success' => true,
                'services' => $services,
                'message' => 'تست سرویس‌ها با موفقیت انجام شد.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در تست سرویس‌ها: ' . $e->getMessage()
            ], 500);
        }
    }

    public function refreshModels()
    {
        try {
            $models = $this->modelService->refreshModels();
            
            return response()->json([
                'success' => true,
                'models' => $models,
                'message' => 'مدل‌ها با موفقیت به‌روزرسانی شدند.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'خطا در به‌روزرسانی مدل‌ها: ' . $e->getMessage()
            ], 500);
        }
    }
}
