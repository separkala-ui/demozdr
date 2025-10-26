<?php

namespace App\Http\Controllers\Backend\Settings;

use App\Http\Controllers\Controller;
use App\Services\SettingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Validation\Rule;

class GeminiSettingsController extends Controller
{
    public function index()
    {
        $formData = [
            'provider' => config('settings.smart-invoice.provider', config('smart-invoice.provider', 'gemini')),
            'gemini_enabled' => (bool) config('settings.smart-invoice.gemini.enabled', config('smart-invoice.gemini.enabled', true)),
            'gemini_api_key' => config('settings.smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key')),
            'gemini_model' => config('settings.smart-invoice.gemini.model', config('smart-invoice.gemini.model', 'gemini-2.5-flash')),
            'gemini_timeout' => (int) config('settings.smart-invoice.gemini.timeout', config('smart-invoice.gemini.timeout', 45)),
            'confidence_threshold' => (float) config('settings.smart-invoice.confidence_threshold', config('smart-invoice.confidence_threshold', 0.6)),
            'validation_tolerance' => (float) config('settings.smart-invoice.validation.tolerance', config('smart-invoice.validation.tolerance', 1000)),
            'openai_enabled' => (bool) config('settings.smart-invoice.openai.enabled', config('smart-invoice.openai.enabled', false)),
            'openai_api_key' => config('settings.smart-invoice.openai.api_key', config('smart-invoice.openai.api_key')), 
            'openai_model' => config('settings.smart-invoice.openai.model', config('smart-invoice.openai.model', 'gpt-4o-mini')),
            'openai_timeout' => (int) config('settings.smart-invoice.openai.timeout', config('smart-invoice.openai.timeout', 60)),
            'openai_max_tokens' => (int) config('settings.smart-invoice.openai.max_output_tokens', config('smart-invoice.openai.max_output_tokens', 4096)),
            'openai_fallback' => (bool) config('settings.smart-invoice.openai.fallback_to_gemini', config('smart-invoice.openai.fallback_to_gemini', true)),
        ];

        $serviceStatus = [
            'provider' => $formData['provider'],
            'gemini_enabled' => $formData['gemini_enabled'],
            'has_api_key' => filled($formData['gemini_api_key']),
            'model' => $formData['gemini_model'],
            'timeout' => $formData['gemini_timeout'],
            'openai_enabled' => $formData['openai_enabled'],
            'openai_has_api_key' => filled($formData['openai_api_key']),
            'openai_model' => $formData['openai_model'],
            'openai_fallback' => $formData['openai_fallback'],
        ];

        return view('backend.pages.settings.smart-invoice', compact('formData', 'serviceStatus'));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'provider' => ['required', 'in:gemini,openai'],
            'gemini_api_key' => ['sometimes', 'string', Rule::requiredIf($request->boolean('gemini_enabled'))],
            'gemini_enabled' => ['sometimes', 'boolean'],
            'gemini_model' => ['nullable', 'string'],
            'gemini_timeout' => ['nullable', 'integer', 'between:5,180'],
            'openai_api_key' => ['sometimes', 'string', Rule::requiredIf($request->boolean('openai_enabled'))],
            'openai_enabled' => ['sometimes', 'boolean'],
            'openai_model' => ['nullable', 'string'],
            'openai_timeout' => ['nullable', 'integer', 'between:5,180'],
            'openai_max_tokens' => ['nullable', 'integer', 'between:512,16384'],
            'openai_fallback' => ['sometimes', 'boolean'],
            'confidence_threshold' => ['nullable', 'numeric', 'between:0,1'],
            'validation_tolerance' => ['nullable', 'numeric', 'min:0'],
        ]);

        $provider = $validated['provider'];
        $geminiEnabled = $request->boolean('gemini_enabled');
        $geminiApiKey = $validated['gemini_api_key'] ?? config('smart-invoice.gemini.api_key');
        $model = $validated['gemini_model'] ?? config('smart-invoice.gemini.model', 'gemini-2.5-flash');
        $timeout = (int) ($validated['gemini_timeout'] ?? config('smart-invoice.gemini.timeout', 45));
        $confidenceThreshold = isset($validated['confidence_threshold'])
            ? (float) $validated['confidence_threshold']
            : (float) config('smart-invoice.confidence_threshold', 0.6);
        $validationTolerance = isset($validated['validation_tolerance'])
            ? (float) $validated['validation_tolerance']
            : (float) config('smart-invoice.validation.tolerance', 1000);

        $openAiEnabled = $request->boolean('openai_enabled');
        $openAiApiKey = $validated['openai_api_key'] ?? config('smart-invoice.openai.api_key');
        $openAiModel = $validated['openai_model'] ?? config('smart-invoice.openai.model', 'gpt-4o-mini');
        $openAiTimeout = (int) ($validated['openai_timeout'] ?? config('smart-invoice.openai.timeout', 60));
        $openAiMaxTokens = (int) ($validated['openai_max_tokens'] ?? config('smart-invoice.openai.max_output_tokens', 4096));
        $openAiFallback = $request->boolean('openai_fallback', true);

        /** @var SettingService $settingService */
        $settingService = app(SettingService::class);

        $settings = [
            'smart-invoice.provider' => $provider,
            'smart-invoice.gemini.enabled' => $geminiEnabled,
            'smart-invoice.gemini.api_key' => $geminiApiKey,
            'smart-invoice.gemini.model' => $model,
            'smart-invoice.gemini.timeout' => $timeout,
            'smart-invoice.confidence_threshold' => $confidenceThreshold,
            'smart-invoice.validation.tolerance' => $validationTolerance,
            'smart-invoice.openai.enabled' => $openAiEnabled,
            'smart-invoice.openai.api_key' => $openAiApiKey,
            'smart-invoice.openai.model' => $openAiModel,
            'smart-invoice.openai.timeout' => $openAiTimeout,
            'smart-invoice.openai.max_output_tokens' => $openAiMaxTokens,
            'smart-invoice.openai.fallback_to_gemini' => $openAiFallback,
            // Legacy fallbacks for code paths که هنوز از ساختار قدیمی استفاده می‌کنند
            'smart_invoice_provider' => $provider,
            'smart_invoice_gemini_enabled' => $geminiEnabled ? '1' : '0',
            'smart_invoice_gemini_api_key' => $geminiApiKey,
            'smart_invoice_gemini_model' => $model,
            'smart_invoice_gemini_timeout' => (string) $timeout,
            'smart_invoice_confidence_threshold' => (string) $confidenceThreshold,
            'smart_invoice_validation_tolerance' => (string) $validationTolerance,
            'smart_invoice_openai_enabled' => $openAiEnabled ? '1' : '0',
            'smart_invoice_openai_api_key' => $openAiApiKey,
            'smart_invoice_openai_model' => $openAiModel,
            'smart_invoice_openai_timeout' => (string) $openAiTimeout,
            'smart_invoice_openai_max_tokens' => (string) $openAiMaxTokens,
            'smart_invoice_openai_fallback_to_gemini' => $openAiFallback ? '1' : '0',
        ];

        foreach ($settings as $key => $value) {
            $settingService->addSetting($key, $value);
        }

        config([
            'smart-invoice.provider' => $provider,
            'smart-invoice.gemini.enabled' => $geminiEnabled,
            'smart-invoice.gemini.api_key' => $geminiApiKey,
            'smart-invoice.gemini.model' => $model,
            'smart-invoice.gemini.timeout' => $timeout,
            'smart-invoice.confidence_threshold' => $confidenceThreshold,
            'smart-invoice.validation.tolerance' => $validationTolerance,
            'smart-invoice.openai.enabled' => $openAiEnabled,
            'smart-invoice.openai.api_key' => $openAiApiKey,
            'smart-invoice.openai.model' => $openAiModel,
            'smart-invoice.openai.timeout' => $openAiTimeout,
            'smart-invoice.openai.max_output_tokens' => $openAiMaxTokens,
            'smart-invoice.openai.fallback_to_gemini' => $openAiFallback,
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
