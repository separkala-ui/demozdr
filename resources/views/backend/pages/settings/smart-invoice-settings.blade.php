@php
    $defaultFormData = [
        'primary_service' => config('settings.smart-invoice.primary_service', config('smart-invoice.primary_service', 'gemini')),
        'fallback_service' => config('settings.smart-invoice.fallback_service', config('smart-invoice.fallback_service', 'python')),
        'gemini_enabled' => (bool) config('settings.smart-invoice.gemini.enabled', config('smart-invoice.gemini.enabled', false)),
        'analytics_enabled' => (bool) config('settings.smart-invoice.analytics', config('smart-invoice.analytics', true)),
        'confidence_threshold' => config('settings.smart-invoice.confidence_threshold', config('smart-invoice.confidence_threshold', 0.5)),
        'notes' => config('settings.smart_invoice_notes', ''),
        'gemini_api_key' => config('settings.smart-invoice.gemini.api_key', config('smart-invoice.gemini.api_key')),
        'gemini_model' => config('settings.smart-invoice.gemini.model', config('smart-invoice.gemini.model', 'gemini-2.5-flash')),
        'gemini_timeout' => config('settings.smart-invoice.gemini.timeout', config('smart-invoice.gemini.timeout', 30)),
        'python_endpoint' => config('settings.smart-invoice.endpoint', config('settings.smart_invoice_service_url', config('smart-invoice.endpoint'))),
        'python_timeout' => config('settings.smart-invoice.timeout', config('settings.smart_invoice_timeout', config('smart-invoice.timeout', 45))),
        'python_api_key' => config('settings.smart-invoice.api_key', config('settings.smart_invoice_api_key', config('smart-invoice.api_key'))),
    ];

    $formData = array_merge($defaultFormData, $formData ?? []);
    $availableModels = collect($availableModels ?? []);
    $serviceStatus = $serviceStatus ?? [
        'python' => ['enabled' => false],
        'gemini' => ['enabled' => false],
    ];

    $serviceOptions = [
        'gemini' => 'Google Gemini AI',
        'python' => 'Python OCR Service',
    ];

    $primaryValue = old('primary_service', $formData['primary_service']);
    $fallbackValue = old('fallback_service', $formData['fallback_service']);

    if ($primaryValue === $fallbackValue) {
        foreach ($serviceOptions as $value => $label) {
            if ($value !== $primaryValue) {
                $fallbackValue = $value;
                break;
            }
        }
    }

    $formData['primary_service'] = $primaryValue;
    $formData['fallback_service'] = $fallbackValue;
@endphp

<div class="space-y-6">
    <div class="space-y-8 border border-gray-200 rounded-md bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">انتخاب سرویس و حالت کار</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                سرویس اصلی و جایگزین هوشمندسازی فاکتور را مشخص کنید. برای بهترین نتیجه، Gemini را به‌عنوان سرویس اصلی و Python را به‌عنوان پشتیبان تنظیم کنید.
            </p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-2">
                <label class="form-label" for="primary_service">سرویس اصلی</label>
                <select
                    id="primary_service"
                    name="primary_service"
                    class="form-control"
                >
                    @foreach ($serviceOptions as $value => $label)
                        <option value="{{ $value }}" @selected($formData['primary_service'] === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400">سرویسی که ابتدا برای استخراج استفاده می‌شود.</p>
                @error('primary_service')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="space-y-2">
                <label class="form-label" for="fallback_service">سرویس جایگزین</label>
                <select
                    id="fallback_service"
                    name="fallback_service"
                    class="form-control"
                >
                    @foreach ($serviceOptions as $value => $label)
                        <option value="{{ $value }}" @selected($formData['fallback_service'] === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                <p class="text-xs text-gray-500 dark:text-gray-400">در صورت خطای سرویس اصلی از این گزینه استفاده می‌شود.</p>
                @error('fallback_service')
                    <p class="text-xs text-red-500 mt-1">{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-2">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input
                        type="checkbox"
                        id="gemini_enabled"
                        name="gemini_enabled"
                        value="1"
                        class="form-checkbox"
                        @checked(old('gemini_enabled', $formData['gemini_enabled']))
                    >
                    <span>فعال‌سازی Gemini</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400">برای استفاده از Google Gemini باید API Key معتبر تعریف شود.</p>
            </div>

            <div class="space-y-2">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                    <input
                        type="checkbox"
                        id="analytics_enabled"
                        name="analytics_enabled"
                        value="1"
                        class="form-checkbox"
                        @checked(old('analytics_enabled', $formData['analytics_enabled']))
                    >
                    <span>ذخیرهٔ تحلیل‌های استخراج</span>
                </label>
                <p class="text-xs text-gray-500 dark:text-gray-400">متادیتای کلید <code>meta.smart_invoice.analytics</code> برای گزارش‌دهی استفاده می‌شود.</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-2">
                <label class="form-label" for="confidence_threshold">آستانهٔ هشدار ضریب اطمینان</label>
                <input
                    type="number"
                    id="confidence_threshold"
                    name="confidence_threshold"
                    class="form-control"
                    value="{{ old('confidence_threshold', $formData['confidence_threshold']) }}"
                    min="0"
                    max="1"
                    step="0.05"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400">در صورت کمتر بودن از این مقدار پیام «بررسی دستی» نشان داده می‌شود.</p>
            </div>

            <div class="space-y-2">
                <label class="form-label" for="smart_invoice_notes">یادداشت‌های داخلی</label>
                <textarea
                    id="smart_invoice_notes"
                    name="smart_invoice_notes"
                    rows="3"
                    class="form-control"
                    placeholder="مسیر venv، سرویس systemd یا سایر نکات نگهداری"
                >{{ old('smart_invoice_notes', $formData['notes']) }}</textarea>
            </div>
        </div>

        <div class="rounded-md border border-slate-200 bg-slate-50 p-4 text-xs text-slate-700 dark:border-slate-700 dark:bg-slate-800/60 dark:text-slate-200">
            <p class="font-semibold mb-2">وضعیت سرویس‌ها</p>
            <div class="grid gap-3 lg:grid-cols-2">
                <div class="flex items-start gap-2">
                    <span class="mt-0.5">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full {{ ($serviceStatus['python']['enabled'] ?? false) ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    </span>
                    <div>
                        <p class="font-medium">Python OCR Service</p>
                        <p class="mt-1">Endpoint: {{ $serviceStatus['python']['endpoint'] ?? '—' }}</p>
                    </div>
                </div>
                <div class="flex items-start gap-2">
                    <span class="mt-0.5">
                        <span class="inline-flex h-2.5 w-2.5 rounded-full {{ ($serviceStatus['gemini']['enabled'] ?? false) ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    </span>
                    <div>
                        <p class="font-medium">Google Gemini AI</p>
                        <p class="mt-1">Model: {{ $serviceStatus['gemini']['model'] ?? '—' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="space-y-8 border border-gray-200 rounded-md bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">پیکربندی Google Gemini</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">برای استفاده از استخراج مبتنی بر هوش مصنوعی گوگل، کلید و مدل مناسب را انتخاب کنید.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-2">
                <label class="form-label" for="gemini_api_key">Gemini API Key</label>
                <input
                    type="text"
                    id="gemini_api_key"
                    name="gemini_api_key"
                    class="form-control"
                    value="{{ old('gemini_api_key', $formData['gemini_api_key']) }}"
                    placeholder="AIza..."
                />
            </div>
            <div class="space-y-2">
                <label class="form-label" for="gemini_timeout">حداکثر زمان انتظار (ثانیه)</label>
                <input
                    type="number"
                    id="gemini_timeout"
                    name="gemini_timeout"
                    class="form-control"
                    value="{{ old('gemini_timeout', $formData['gemini_timeout']) }}"
                    min="10"
                    max="120"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400">زمان پیشنهادی ۳۰ تا ۴۵ ثانیه است.</p>
            </div>
        </div>

        <div class="space-y-2">
            <label class="form-label" for="gemini_model">انتخاب مدل Gemini</label>
            <div class="flex items-center gap-2">
                <select id="gemini_model" name="gemini_model" class="form-control">
                    @forelse($availableModels as $model)
                        <option value="{{ $model['name'] }}" @selected(old('gemini_model', $formData['gemini_model']) === $model['name'])>
                            {{ $model['display_name'] }} {{ $model['recommended'] ? '· (پیشنهادی)' : '' }}
                        </option>
                    @empty
                        <option value="" disabled>{{ __('No Gemini models available. Refresh to try again.') }}</option>
                    @endforelse
                </select>
                <button
                    type="button"
                    data-refresh-models
                    class="inline-flex items-center gap-1 rounded-md border border-slate-200 px-3 py-2 text-xs font-medium text-slate-600 hover:bg-slate-50 dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-700/50"
                >
                    <i class="fas fa-rotate mr-1"></i>
                    بروزرسانی مدل‌ها
                </button>
            </div>
            <p class="text-xs text-gray-500 dark:text-gray-400">در صورت تغییر سرویس، لیست مدل‌ها را بروزرسانی کنید تا اطلاعات API به‌روز شود.</p>
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-700 dark:border-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200">
            <p class="font-semibold">نکات استفاده از Gemini</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>اطمینان حاصل کنید API Key روی پروژه‌ای با فعال بودن Gemini Vision قرار دارد.</li>
                <li>تصاویر واضح و ۳۰۰DPI بهترین نتیجه را می‌دهند؛ از اسکن تار پرهیز کنید.</li>
                <li>در صورت مشاهده خروجی ناقص، مدل متفاوتی را تست کنید.</li>
            </ul>
        </div>
    </div>

    <div class="space-y-8 border border-gray-200 rounded-md bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
        <div>
            <h3 class="text-base font-semibold text-gray-800 dark:text-white/90">پیکربندی سرویس Python OCR</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">آدرس و پارامترهای سرویس داخلی را وارد کنید. انتهای آدرس را بدون <code>/extract</code> بنویسید.</p>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="space-y-2">
                <label class="form-label" for="python_endpoint">آدرس سرویس</label>
                <input
                    type="text"
                    id="python_endpoint"
                    name="python_endpoint"
                    class="form-control"
                    value="{{ old('python_endpoint', $formData['python_endpoint']) }}"
                    placeholder="http://127.0.0.1:8000"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400">مثال: <code>http://192.168.1.10:8000</code></p>
            </div>

            <div class="space-y-2">
                <label class="form-label" for="python_timeout">حداکثر زمان انتظار (ثانیه)</label>
                <input
                    type="number"
                    id="python_timeout"
                    name="python_timeout"
                    class="form-control"
                    value="{{ old('python_timeout', $formData['python_timeout']) }}"
                    min="10"
                    max="180"
                />
                <p class="text-xs text-gray-500 dark:text-gray-400">اگر پردازش طولانی است مقدار ۶۰ یا ۹۰ ثانیه را انتخاب کنید.</p>
            </div>
        </div>

        <div class="space-y-2">
            <label class="form-label" for="python_api_key">توکن دسترسی (اختیاری)</label>
            <input
                type="text"
                id="python_api_key"
                name="python_api_key"
                class="form-control"
                value="{{ old('python_api_key', $formData['python_api_key']) }}"
                placeholder="در صورت نیاز به احراز هویت Bearer"
            />
        </div>

        <div class="rounded-md border border-indigo-200 bg-indigo-50 p-4 text-sm text-indigo-700 dark:border-indigo-800 dark:bg-indigo-500/10 dark:text-indigo-200">
            <p class="font-semibold">چک‌لیست راه‌اندازی سرویس پایتون</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li>Python 3.10+ به همراه بسته‌های <code>ffmpeg</code>، <code>libsm6</code>، <code>libxext6</code> نصب شده باشد.</li>
                <li>دستور <code>pip install -r python/pettycash_ai/requirements.txt</code> داخل محیط مجازی اجرا شود.</li>
                <li>سرویس با <code>uvicorn python.pettycash_ai.main:app --host 0.0.0.0 --port 8000</code> اجرا و پشت فایروال نگهداری شود.</li>
                <li>پس از ذخیره آدرس، دکمهٔ «Smart invoice auto-fill» را در یک ردیف تست کنید.</li>
            </ul>
        </div>

        <div class="rounded-md border border-amber-200 bg-amber-50 p-4 text-sm leading-6 text-amber-900 dark:border-amber-600/60 dark:bg-amber-500/10 dark:text-amber-100">
            <p class="font-semibold">عیب‌یابی سریع</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">
                <li><strong>Not Found:</strong> آدرس سرویس را بدون <code>/extract</code> وارد کنید و مطمئن شوید uvicorn در حال اجراست.</li>
                <li><strong>Field required (file):</strong> سرویس قدیمی است؛ ارسال دو فیلد <code>file</code> و <code>invoice</code> همزمان انجام می‌شود.</li>
                <li><strong>Confidence 0%:</strong> تصویر واضح نیست یا مبلغ شناسایی نشده؛ به متای ذخیره‌شده در ستون <code>meta.smart_invoice</code> مراجعه کنید.</li>
            </ul>
        </div>

        <div class="flex justify-end border-t border-gray-100 pt-5 dark:border-gray-800">
            <button type="submit" class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow transition hover:bg-primary/90">
                <i class="fas fa-save ml-1"></i>
                ذخیره تنظیمات فاکتور هوشمند
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const primarySelect = document.getElementById('primary_service');
            const fallbackSelect = document.getElementById('fallback_service');

            const updateFallbackState = () => {
                if (!primarySelect || !fallbackSelect) {
                    return;
                }

                const fallbackOptions = Array.from(fallbackSelect.options);
                fallbackOptions.forEach((option) => {
                    if (!option.value) {
                        option.disabled = false;
                        return;
                    }

                    option.disabled = option.value === primarySelect.value;
                });

                if (fallbackSelect.value === primarySelect.value) {
                    const alternative = fallbackOptions.find((option) => !option.disabled);
                    if (alternative) {
                        fallbackSelect.value = alternative.value;
                    }
                }
            };

            if (primarySelect && fallbackSelect) {
                updateFallbackState();
                primarySelect.addEventListener('change', updateFallbackState);
                fallbackSelect.addEventListener('change', updateFallbackState);
            }

            const refreshButton = document.querySelector('[data-refresh-models]');
            if (!refreshButton) {
                return;
            }

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
            const modelSelect = document.getElementById('gemini_model');
            const notify = (type, message) => {
                window.dispatchEvent(new CustomEvent('flash', {
                    detail: { type, message },
                }));

                if (window.toastr && typeof window.toastr[type] === 'function') {
                    window.toastr[type](message);
                } else {
                    const logger = type === 'error' ? console.error : console.log;
                    logger(message);
                }
            };

            refreshButton.addEventListener('click', async () => {
                if (!modelSelect) {
                    return;
                }

                const originalLabel = refreshButton.innerHTML;
                refreshButton.disabled = true;
                refreshButton.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> در حال بروزرسانی';

                try {
                    const response = await fetch('{{ route('admin.settings.smart-invoice.refresh-models') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({}),
                    });

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'خطای ناشناخته');
                    }

                    const currentValue = modelSelect.value;
                    modelSelect.innerHTML = '';

                    data.models.forEach((model) => {
                        const option = document.createElement('option');
                        option.value = model.name;
                        option.textContent = `${model.display_name}${model.recommended ? ' · (پیشنهادی)' : ''}`;

                        if (currentValue === model.name) {
                            option.selected = true;
                        }

                        modelSelect.appendChild(option);
                    });

                    if (!modelSelect.value && data.models.length > 0) {
                        modelSelect.value = data.models[0].name;
                    }

                    notify('success', data.message || 'لیست مدل‌ها بروزرسانی شد.');
                } catch (error) {
                    notify('error', error.message || 'بروزرسانی مدل‌ها با خطا مواجه شد.');
                } finally {
                    refreshButton.disabled = false;
                    refreshButton.innerHTML = originalLabel;
                }
            });
        });
    </script>
@endpush
