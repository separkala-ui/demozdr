<?php

namespace App\Http\Controllers\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class GeminiSettingsController extends Controller
{
    public function index()
    {
        $formData = [
            'gemini_enabled' => (bool) config('settings.smart-invoice.gemini.enabled', config('smart-invoice.gemini.enabled', true)),
            'gemini_api_key' => config('settings.smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key')),
            'gemini_model' => config('settings.smart-invoice.gemini.model', config('smart-invoice.gemini.model', 'gemini-2.5-flash')),
            'gemini_timeout' => (int) config('settings.smart-invoice.gemini.timeout', config('smart-invoice.gemini.timeout', 45)),
            'confidence_threshold' => (float) config('settings.smart-invoice.confidence_threshold', config('smart-invoice.confidence_threshold', 0.6)),
            'validation_tolerance' => (float) config('settings.smart-invoice.validation.tolerance', config('smart-invoice.validation.tolerance', 1000)),
        ];

        $serviceStatus = [
            'gemini_enabled' => $formData['gemini_enabled'],
            'has_api_key' => filled($formData['gemini_api_key']),
            'model' => $formData['gemini_model'],
            'timeout' => $formData['gemini_timeout'],
        ];

        return view('backend.pages.settings.smart-invoice', compact('formData', 'serviceStatus'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'gemini_api_key' => ['required', 'string'],
            'gemini_enabled' => ['sometimes', 'boolean'],
            'gemini_model' => ['nullable', 'string'],
            'gemini_timeout' => ['nullable', 'integer', 'between:5,180'],
            'confidence_threshold' => ['nullable', 'numeric', 'between:0,1'],
            'validation_tolerance' => ['nullable', 'numeric', 'min:0'],
        ]);

        $geminiEnabled = $request->boolean('gemini_enabled');
        $apiKey = $validated['gemini_api_key'];
        $model = $validated['gemini_model'] ?? config('smart-invoice.gemini.model', 'gemini-2.5-flash');
        $timeout = (int) ($validated['gemini_timeout'] ?? config('smart-invoice.gemini.timeout', 45));
        $confidenceThreshold = isset($validated['confidence_threshold'])
            ? (float) $validated['confidence_threshold']
            : (float) config('smart-invoice.confidence_threshold', 0.6);
        $validationTolerance = isset($validated['validation_tolerance'])
            ? (float) $validated['validation_tolerance']
            : (float) config('smart-invoice.validation.tolerance', 1000);

        /** @var SettingService $settingService */
        $settingService = app(SettingService::class);

        $settings = [
            'smart-invoice.gemini.enabled' => $geminiEnabled,
            'smart-invoice.gemini.api_key' => $apiKey,
            'smart-invoice.gemini.model' => $model,
            'smart-invoice.gemini.timeout' => $timeout,
            'smart-invoice.confidence_threshold' => $confidenceThreshold,
            'smart-invoice.validation.tolerance' => $validationTolerance,
            // Legacy fallbacks for code paths که هنوز از ساختار قدیمی استفاده می‌کنند
            'smart_invoice_gemini_enabled' => $geminiEnabled ? '1' : '0',
            'smart_invoice_gemini_api_key' => $apiKey,
            'smart_invoice_gemini_model' => $model,
            'smart_invoice_gemini_timeout' => (string) $timeout,
            'smart_invoice_confidence_threshold' => (string) $confidenceThreshold,
            'smart_invoice_validation_tolerance' => (string) $validationTolerance,
        ];

        foreach ($settings as $key => $value) {
            $settingService->addSetting($key, $value);
        }

        config([
            'smart-invoice.gemini.enabled' => $geminiEnabled,
            'smart-invoice.gemini.api_key' => $apiKey,
            'smart-invoice.gemini.model' => $model,
            'smart-invoice.gemini.timeout' => $timeout,
            'smart-invoice.confidence_threshold' => $confidenceThreshold,
            'smart-invoice.validation.tolerance' => $validationTolerance,
        ]);

        try {
            Artisan::call('config:clear');
        } catch (\Throwable $exception) {
            report($exception);
        }

        return redirect()
            ->route('admin.settings.smart-invoice.index')
            ->with('success', __('تنظیمات جمینای با موفقیت ذخیره شد.'));
    }
}
