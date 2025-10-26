<div class="rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-800">{{ __('وضعیت سیستم') }}</h3>
                <p class="text-xs text-slate-500">{{ __('سلامت سرویس‌ها') }}</p>
            </div>
            <button 
                wire:click="loadHealth" 
                class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                title="{{ __('بروزرسانی') }}"
            >
                <iconify-icon icon="lucide:refresh-cw" class="text-lg"></iconify-icon>
            </button>
        </div>
    </div>

    <div class="p-5">
        {{-- Overall Status --}}
        <div class="mb-6 rounded-lg border-2 p-4
            @if($health['overall'] === 'healthy') border-emerald-200 bg-emerald-50
            @elseif($health['overall'] === 'warning') border-amber-200 bg-amber-50
            @else border-rose-200 bg-rose-50
            @endif
        ">
            <div class="flex items-center gap-3">
                <div class="flex-shrink-0">
                    @if($health['overall'] === 'healthy')
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-emerald-100">
                            <iconify-icon icon="lucide:check-circle" class="text-2xl text-emerald-600"></iconify-icon>
                        </div>
                    @elseif($health['overall'] === 'warning')
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-amber-100">
                            <iconify-icon icon="lucide:alert-triangle" class="text-2xl text-amber-600"></iconify-icon>
                        </div>
                    @else
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-100">
                            <iconify-icon icon="lucide:alert-octagon" class="text-2xl text-rose-600"></iconify-icon>
                        </div>
                    @endif
                </div>
                <div class="flex-1">
                    <h4 class="font-semibold
                        @if($health['overall'] === 'healthy') text-emerald-800
                        @elseif($health['overall'] === 'warning') text-amber-800
                        @else text-rose-800
                        @endif
                    ">
                        @if($health['overall'] === 'healthy')
                            {{ __('سیستم سالم است') }}
                        @elseif($health['overall'] === 'warning')
                            {{ __('توجه لازم است') }}
                        @else
                            {{ __('مشکل جدی!') }}
                        @endif
                    </h4>
                    <p class="text-xs
                        @if($health['overall'] === 'healthy') text-emerald-700
                        @elseif($health['overall'] === 'warning') text-amber-700
                        @else text-rose-700
                        @endif
                    ">
                        @if($health['overall'] === 'healthy')
                            {{ __('تمام سرویس‌ها به درستی کار می‌کنند') }}
                        @elseif($health['overall'] === 'warning')
                            {{ __('برخی سرویس‌ها نیاز به بررسی دارند') }}
                        @else
                            {{ __('سرویس‌های مهم با مشکل مواجه هستند') }}
                        @endif
                    </p>
                </div>
            </div>
        </div>

        {{-- Health Checks --}}
        <div class="space-y-3">
            {{-- Database --}}
            <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3 transition-all hover:shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-indigo-50">
                        <iconify-icon icon="lucide:database" class="text-lg text-indigo-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ __('پایگاه داده') }}</p>
                        <p class="text-xs text-slate-500">{{ $health['database']['details'] ?? '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600">{{ $health['database']['message'] ?? '' }}</span>
                    @if(($health['database']['status'] ?? '') === 'healthy')
                        <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                    @elseif(($health['database']['status'] ?? '') === 'warning')
                        <div class="h-2 w-2 rounded-full bg-amber-500"></div>
                    @else
                        <div class="h-2 w-2 rounded-full bg-rose-500"></div>
                    @endif
                </div>
            </div>

            {{-- Cache --}}
            <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3 transition-all hover:shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-purple-50">
                        <iconify-icon icon="lucide:zap" class="text-lg text-purple-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ __('کش') }}</p>
                        <p class="text-xs text-slate-500">{{ $health['cache']['details'] ?? '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600">{{ $health['cache']['message'] ?? '' }}</span>
                    @if(($health['cache']['status'] ?? '') === 'healthy')
                        <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                    @elseif(($health['cache']['status'] ?? '') === 'warning')
                        <div class="h-2 w-2 rounded-full bg-amber-500"></div>
                    @else
                        <div class="h-2 w-2 rounded-full bg-rose-500"></div>
                    @endif
                </div>
            </div>

            {{-- Storage --}}
            <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3 transition-all hover:shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-cyan-50">
                        <iconify-icon icon="lucide:hard-drive" class="text-lg text-cyan-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ __('فضای دیسک') }}</p>
                        <p class="text-xs text-slate-500">{{ $health['storage']['details'] ?? '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600">{{ $health['storage']['message'] ?? '' }}</span>
                    @if(($health['storage']['status'] ?? '') === 'healthy')
                        <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                    @elseif(($health['storage']['status'] ?? '') === 'warning')
                        <div class="h-2 w-2 rounded-full bg-amber-500"></div>
                    @else
                        <div class="h-2 w-2 rounded-full bg-rose-500"></div>
                    @endif
                </div>
            </div>

            {{-- Queue --}}
            <div class="flex items-center justify-between rounded-lg border border-slate-200 p-3 transition-all hover:shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="flex h-10 w-10 items-center justify-center rounded-full bg-orange-50">
                        <iconify-icon icon="lucide:layers" class="text-lg text-orange-600"></iconify-icon>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-slate-800">{{ __('صف کارها') }}</p>
                        <p class="text-xs text-slate-500">{{ $health['queue']['details'] ?? '' }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="text-xs font-semibold text-slate-600">{{ $health['queue']['message'] ?? '' }}</span>
                    @if(($health['queue']['status'] ?? '') === 'healthy')
                        <div class="h-2 w-2 rounded-full bg-emerald-500"></div>
                    @elseif(($health['queue']['status'] ?? '') === 'warning')
                        <div class="h-2 w-2 rounded-full bg-amber-500"></div>
                    @else
                        <div class="h-2 w-2 rounded-full bg-rose-500"></div>
                    @endif
                </div>
            </div>
        </div>

        {{-- System Info --}}
        <div class="mt-4 rounded-lg bg-slate-50 p-3">
            <div class="grid grid-cols-2 gap-2 text-xs">
                <div class="flex items-center gap-2 text-slate-600">
                    <iconify-icon icon="lucide:server" class="text-sm"></iconify-icon>
                    <span>PHP {{ PHP_VERSION }}</span>
                </div>
                <div class="flex items-center gap-2 text-slate-600">
                    <iconify-icon icon="lucide:package" class="text-sm"></iconify-icon>
                    <span>Laravel {{ app()->version() }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

