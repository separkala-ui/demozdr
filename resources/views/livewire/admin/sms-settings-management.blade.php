<div class="space-y-6">
    {{-- Status Banner --}}
    <div class="bg-gradient-to-r {{ $enabled ? 'from-green-500 to-emerald-600' : 'from-gray-400 to-gray-500' }} rounded-xl p-6 text-white shadow-lg">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <div class="bg-white/20 backdrop-blur-sm p-3 rounded-lg">
                    <iconify-icon icon="lucide:{{ $enabled ? 'check-circle' : 'x-circle' }}" class="text-4xl"></iconify-icon>
                </div>
                <div>
                    <h3 class="text-2xl font-bold">سیستم پیامک {{ $enabled ? 'فعال' : 'غیرفعال' }} است</h3>
                    <p class="text-white/90 mt-1">
                        @if($enabled && $logOnly)
                            <iconify-icon icon="lucide:alert-triangle" class="text-yellow-300"></iconify-icon>
                            حالت Log-Only فعال است (فقط لاگ می‌شود، ارسال نمی‌شود)
                        @elseif($enabled)
                            <iconify-icon icon="lucide:send" class="text-white"></iconify-icon>
                            پیامک‌ها واقعاً ارسال می‌شوند
                        @else
                            سیستم غیرفعال است
                        @endif
                    </p>
                </div>
            </div>
            <button wire:click="getCredit" class="bg-white/20 hover:bg-white/30 backdrop-blur-sm px-6 py-3 rounded-lg transition-all duration-200 flex items-center gap-2 font-semibold">
                <iconify-icon icon="lucide:wallet" class="text-xl"></iconify-icon>
                <span>دریافت اعتبار</span>
            </button>
        </div>
    </div>

    {{-- Main Settings Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-indigo-50 to-purple-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <iconify-icon icon="lucide:settings" class="text-indigo-600"></iconify-icon>
                <span>تنظیمات عمومی</span>
            </h3>
        </div>

        <div class="p-6 space-y-6">
            {{-- Enable/Disable Toggle --}}
            <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg">
                <div class="flex-1">
                    <label class="text-base font-semibold text-gray-900 flex items-center gap-2">
                        <iconify-icon icon="lucide:power" class="text-indigo-600"></iconify-icon>
                        <span>فعال‌سازی سیستم پیامک</span>
                    </label>
                    <p class="text-sm text-gray-600 mt-1">با غیرفعال کردن، هیچ پیامکی ارسال نمی‌شود</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="enabled" class="sr-only peer">
                    <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-indigo-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-indigo-600"></div>
                </label>
            </div>

            {{-- Log-Only Mode Toggle --}}
            <div class="flex items-center justify-between p-4 bg-yellow-50 rounded-lg border border-yellow-200">
                <div class="flex-1">
                    <label class="text-base font-semibold text-gray-900 flex items-center gap-2">
                        <iconify-icon icon="lucide:file-text" class="text-yellow-600"></iconify-icon>
                        <span>حالت Log-Only (فقط لاگ)</span>
                    </label>
                    <p class="text-sm text-gray-600 mt-1">
                        <iconify-icon icon="lucide:info" class="text-yellow-600"></iconify-icon>
                        برای تست: پیامک ارسال نمی‌شود، فقط در لاگ ثبت می‌شود
                    </p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer">
                    <input type="checkbox" wire:model.live="logOnly" class="sr-only peer">
                    <div class="w-14 h-7 bg-gray-300 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-yellow-300 rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-0.5 after:start-[4px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-6 after:w-6 after:transition-all peer-checked:bg-yellow-500"></div>
                </label>
            </div>

            {{-- API Key --}}
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <iconify-icon icon="lucide:key" class="text-indigo-600"></iconify-icon>
                    <span>کلید API (API Key)</span>
                </label>
                <input 
                    type="text" 
                    wire:model="apiKey" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 font-mono text-sm"
                    placeholder="OWZjZTMwYTkt..."
                    dir="ltr"
                >
                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                    <iconify-icon icon="lucide:info"></iconify-icon>
                    کلید API را از پنل IPPanel دریافت کنید
                </p>
            </div>

            {{-- Originator --}}
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <iconify-icon icon="lucide:phone" class="text-indigo-600"></iconify-icon>
                    <span>شماره فرستنده (Originator)</span>
                </label>
                <input 
                    type="text" 
                    wire:model="originator" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="+985000..."
                    dir="ltr"
                >
                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                    <iconify-icon icon="lucide:info"></iconify-icon>
                    شماره خط ارسال پیامک (از پنل IPPanel)
                </p>
            </div>

            {{-- Finance Manager Mobile --}}
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2 flex items-center gap-2">
                    <iconify-icon icon="lucide:user-check" class="text-indigo-600"></iconify-icon>
                    <span>موبایل مدیر مالی</span>
                </label>
                <input 
                    type="text" 
                    wire:model="financeManagerMobile" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500"
                    placeholder="0912..."
                    dir="ltr"
                >
                <p class="text-xs text-gray-500 mt-1 flex items-center gap-1">
                    <iconify-icon icon="lucide:info"></iconify-icon>
                    برای ارسال پیامک‌های مهم مانند درخواست شارژ
                </p>
            </div>

            {{-- Save Button --}}
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button 
                    wire:click="saveSettings" 
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-8 py-3 rounded-lg font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all duration-200"
                >
                    <iconify-icon icon="lucide:save" class="text-xl"></iconify-icon>
                    <span>ذخیره تنظیمات</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Pattern Management Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-cyan-50 to-sky-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <iconify-icon icon="lucide:clipboard-list" class="text-sky-600"></iconify-icon>
                <span>مدیریت پترن‌های پیامک</span>
            </h3>
        </div>

        <div class="p-6 space-y-6">
            @foreach($allPatterns as $key => $pattern)
                <div>
                    <label for="pattern_{{ $key }}" class="block text-sm font-semibold text-gray-900 mb-2">
                        {{ $pattern['description'] }}
                    </label>
                    <input 
                        type="text" 
                        id="pattern_{{ $key }}"
                        wire:model="patterns.{{ $key }}" 
                        class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500 font-mono text-sm"
                        placeholder="کد پترن..."
                        dir="ltr"
                    >
                    <p class="text-xs text-gray-500 mt-1">
                        متغیرها: <code class="bg-gray-100 px-1.5 py-0.5 rounded text-sky-700">{{ implode(', ', $pattern['variables']) }}</code>
                    </p>
                </div>
            @endforeach

            {{-- Save Button for Patterns (same as main save) --}}
            <div class="flex justify-end pt-4 border-t border-gray-200">
                <button 
                    wire:click="saveSettings" 
                    class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white px-8 py-3 rounded-lg font-semibold flex items-center gap-2 shadow-lg hover:shadow-xl transition-all duration-200"
                >
                    <iconify-icon icon="lucide:save" class="text-xl"></iconify-icon>
                    <span>ذخیره همه تنظیمات</span>
                </button>
            </div>
        </div>
    </div>

    {{-- Test SMS Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-green-50 to-emerald-50 px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                <iconify-icon icon="lucide:send" class="text-green-600"></iconify-icon>
                <span>تست ارسال پیامک</span>
            </h3>
        </div>

        <div class="p-6 space-y-4">
            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">شماره موبایل</label>
                <input 
                    type="text" 
                    wire:model="testMobile" 
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="09123456789"
                    dir="ltr"
                >
            </div>

            <div>
                <label class="block text-sm font-semibold text-gray-900 mb-2">متن پیام</label>
                <textarea 
                    wire:model="testMessage" 
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="این یک پیام تستی است..."
                ></textarea>
                <p class="text-xs text-gray-500 mt-1">حداکثر 500 کاراکتر</p>
            </div>

            <div class="flex items-center gap-3">
                <button 
                    wire:click="sendTestSMS" 
                    class="bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-700 hover:to-emerald-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2 shadow-md hover:shadow-lg transition-all duration-200"
                >
                    <iconify-icon icon="lucide:send" class="text-xl"></iconify-icon>
                    <span>ارسال پیامک تستی</span>
                </button>

                @if($testResult)
                    <button 
                        wire:click="clearTestResult" 
                        class="text-gray-600 hover:text-gray-800 px-4 py-3 rounded-lg border border-gray-300 hover:border-gray-400 transition-all duration-200"
                    >
                        پاک کردن نتیجه
                    </button>
                @endif
            </div>

            @if($testResult)
                <div class="mt-4 p-4 {{ $testResult['success'] ? 'bg-green-50 border border-green-200' : 'bg-red-50 border border-red-200' }} rounded-lg">
                    <div class="flex items-start gap-3">
                        <iconify-icon 
                            icon="lucide:{{ $testResult['success'] ? 'check-circle' : 'x-circle' }}" 
                            class="text-2xl {{ $testResult['success'] ? 'text-green-600' : 'text-red-600' }}"
                        ></iconify-icon>
                        <div class="flex-1">
                            <h4 class="font-semibold {{ $testResult['success'] ? 'text-green-900' : 'text-red-900' }} mb-2">
                                {{ $testResult['success'] ? 'موفق!' : 'خطا!' }}
                            </h4>
                            <pre class="text-sm {{ $testResult['success'] ? 'text-green-800' : 'text-red-800' }} whitespace-pre-wrap font-mono bg-white/50 p-3 rounded">{{ json_encode($testResult, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Logs Card --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="bg-gradient-to-r from-blue-50 to-cyan-50 px-6 py-4 border-b border-gray-200">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-gray-900 flex items-center gap-2">
                    <iconify-icon icon="lucide:file-text" class="text-blue-600"></iconify-icon>
                    <span>لاگ‌های سیستم</span>
                </h3>
                <button 
                    wire:click="loadLogs" 
                    class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 shadow-sm hover:shadow transition-all duration-200 text-sm"
                >
                    <iconify-icon icon="lucide:refresh-cw" class="text-lg"></iconify-icon>
                    <span>بارگذاری لاگ‌ها</span>
                </button>
            </div>
        </div>

        @if($showLogs)
            <div class="p-6">
                @if(count($logs) > 0)
                    <div class="bg-gray-900 text-gray-100 p-4 rounded-lg font-mono text-xs overflow-x-auto max-h-96 overflow-y-auto">
                        @foreach($logs as $log)
                            <div class="mb-1">{{ $log }}</div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500">
                        <iconify-icon icon="lucide:inbox" class="text-4xl mb-2"></iconify-icon>
                        <p>هیچ لاگی یافت نشد</p>
                    </div>
                @endif
            </div>
        @else
            <div class="p-6 text-center text-gray-500">
                <iconify-icon icon="lucide:file-text" class="text-4xl mb-2"></iconify-icon>
                <p>روی دکمه "بارگذاری لاگ‌ها" کلیک کنید</p>
            </div>
        @endif
    </div>

    {{-- Help Card --}}
    <div class="bg-gradient-to-br from-purple-50 to-indigo-50 rounded-xl shadow-sm border border-purple-200 p-6">
        <div class="flex items-start gap-4">
            <div class="bg-purple-100 p-3 rounded-lg">
                <iconify-icon icon="lucide:help-circle" class="text-3xl text-purple-600"></iconify-icon>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">راهنمای استفاده</h3>
                <ul class="space-y-2 text-sm text-gray-700">
                    <li class="flex items-start gap-2">
                        <iconify-icon icon="lucide:chevron-left" class="text-purple-600 mt-1"></iconify-icon>
                        <span>برای تست سیستم، ابتدا <strong>"حالت Log-Only"</strong> را فعال کنید</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <iconify-icon icon="lucide:chevron-left" class="text-purple-600 mt-1"></iconify-icon>
                        <span>کلید API و شماره فرستنده را از <a href="https://ippanel.com" target="_blank" class="text-indigo-600 hover:underline">پنل IPPanel</a> دریافت کنید</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <iconify-icon icon="lucide:chevron-left" class="text-purple-600 mt-1"></iconify-icon>
                        <span>در حالت Log-Only، پیامک‌ها فقط در لاگ ثبت می‌شوند و ارسال نمی‌شوند</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <iconify-icon icon="lucide:chevron-left" class="text-purple-600 mt-1"></iconify-icon>
                        <span>برای استفاده در Production، <strong>"حالت Log-Only"</strong> را غیرفعال کنید</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <iconify-icon icon="lucide:chevron-left" class="text-purple-600 mt-1"></iconify-icon>
                        <span>مستندات کامل را در فایل <code class="bg-white px-2 py-1 rounded">docs/SMS-SYSTEM-GUIDE.md</code> مطالعه کنید</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</div>
