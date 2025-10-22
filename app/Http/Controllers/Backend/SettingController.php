<?php

declare(strict_types=1);

namespace App\Http\Controllers\Backend;

use App\Enums\ActionType;
use App\Enums\Hooks\SettingFilterHook;
use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\CacheService;
use App\Services\EnvWriter;
use App\Services\ImageService;
use App\Services\RecaptchaService;
use App\Services\SettingService;
use App\Support\Facades\Hook;
use Illuminate\Contracts\Support\Renderable;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    public function __construct(
        private readonly SettingService $settingService,
        private readonly EnvWriter $envWriter,
        private readonly CacheService $cacheService,
        private readonly ImageService $imageService,
        private readonly RecaptchaService $recaptchaService,
    ) {
    }

    public function index($tab = null): Renderable
    {
        // $this->authorize('update', Setting::class); // Temporarily disabled

        $tab = $tab ?? request()->input('tab', 'general');

        // Handle Smart Invoice tab
        if ($tab === 'smart-invoice') {
            $hybridService = app(\App\Services\PettyCash\HybridInvoiceService::class);
            $modelService = app(\App\Services\PettyCash\GeminiModelService::class);
            $serviceStatus = $hybridService->getServiceStatus();
            $availableModels = $modelService->getRecommendedModels();
            
            // Set default values if not configured
            $settingService = app(\App\Services\SettingService::class);
            
            // Set defaults only if not already set
            if (!$settingService->getSetting('smart-invoice.gemini.model')) {
                $settingService->addSetting('smart-invoice.gemini.model', 'gemini-2.5-flash');
            }
            
            if (!$settingService->getSetting('smart-invoice.primary_service')) {
                $settingService->addSetting('smart-invoice.primary_service', 'gemini');
            }
            
            if (!$settingService->getSetting('smart-invoice.fallback_service')) {
                $settingService->addSetting('smart-invoice.fallback_service', 'python');
            }

            if (!$settingService->getSetting('smart-invoice.gemini.enabled')) {
                $settingService->addSetting('smart-invoice.gemini.enabled', true);
            }

            if (!$settingService->getSetting('smart-invoice.analytics')) {
                $settingService->addSetting('smart-invoice.analytics', true);
            }

            if (!$settingService->getSetting('smart-invoice.confidence_threshold')) {
                $settingService->addSetting('smart-invoice.confidence_threshold', 0.5);
            }
            
            return view('backend.pages.settings.index', compact('tab', 'serviceStatus', 'availableModels'))
                ->with([
                    'breadcrumbs' => [
                        'title' => __('Smart Invoice Settings'),
                    ],
                    'availableModels' => $availableModels,
                ]);
        }

        return view('backend.pages.settings.index', compact('tab'))
            ->with([
                'breadcrumbs' => [
                    'title' => __('Settings'),
                ],
            ]);
    }

    public function store(Request $request)
    {
        // Temporarily disable authorization for testing
        // $this->authorize('update', Setting::class);

        // Handle Smart Invoice settings
        if ($request->has('primary_service') || $request->has('gemini_api_key')) {
            return $this->handleSmartInvoiceSettings($request);
        }

        // Restrict specific fields in demo mode.
        if (config('app.demo_mode', false)) {
            $restrictedFields = Hook::applyFilters(SettingFilterHook::SETTINGS_RESTRICTED_FIELDS, [
                'app_name',
                'google_analytics_script',
                'recaptcha_site_key',
                'recaptcha_secret_key',
                'recaptcha_enabled_pages',
                'recaptcha_score_threshold',
                'admin_login_route',
                'disable_default_admin_redirect',
            ]);
            $fields = $request->except($restrictedFields);
        } else {
            $fields = $request->all();
        }

        // Validate admin login route if provided
        if ($request->has('admin_login_route')) {
            $request->validate([
                'admin_login_route' => 'required|regex:/^[a-zA-Z0-9\-\_\/]+$/|min:3|max:50',
            ], [
                'admin_login_route.regex' => 'The admin login route can only contain letters, numbers, hyphens, underscores and forward slashes.',
            ]);
        }

        $uploadPath = 'uploads/settings';

        // Handle checkbox fields that might not be present when unchecked
        $checkboxFields = ['disable_default_admin_redirect'];
        foreach ($checkboxFields as $checkboxField) {
            // Skip restricted fields in demo mode
            if (config('app.demo_mode', false) && in_array($checkboxField, $restrictedFields ?? [])) {
                continue;
            }

            if (! isset($fields[$checkboxField]) && $request->has('_token')) {
                // If the form was submitted but checkbox wasn't checked, set to 0
                $fields[$checkboxField] = '0';
            }
        }

        foreach ($fields as $fieldName => $fieldValue) {
            if ($request->hasFile($fieldName)) {
                $this->imageService->deleteImageFromPublic((string) config($fieldName));
                $fileUrl = $this->imageService->storeImageAndGetUrl($request, $fieldName, $uploadPath);
                $this->settingService->addSetting($fieldName, $fileUrl);
            } elseif ($fieldName === 'recaptcha_enabled_pages') {
                // Validate enabled pages against allowed list.
                $enabledPages = $request->input('recaptcha_enabled_pages', []);
                $validPages = array_keys($this->recaptchaService::getAvailablePages());
                $enabledPages = array_intersect($enabledPages, $validPages);
                $this->settingService->addSetting($fieldName, json_encode(array_values($enabledPages)));
            } else {
                $this->settingService->addSetting($fieldName, $fieldValue);
            }
        }

        $this->envWriter->batchWriteKeysToEnvFile($fields);

        $this->storeActionLog(ActionType::UPDATED, [
            'settings' => $fields,
        ]);

        return redirect()->back()->with('success', 'Settings saved successfully.');
    }

    private function handleSmartInvoiceSettings(Request $request)
    {
        // Delegate to SmartInvoiceSettingsController
        $smartInvoiceController = new \App\Http\Controllers\Backend\Settings\SmartInvoiceSettingsController(
            app(\App\Services\PettyCash\HybridInvoiceService::class),
            app(\App\Services\PettyCash\GeminiModelService::class)
        );

        return $smartInvoiceController->update($request);
    }
}
