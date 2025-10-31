<div
    class="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card"
    wire:poll.30s="poll"
>
    <div class="flex items-center justify-between border-b border-stroke px-6 py-4 dark:border-dark-3">
        <div>
            <h3 class="text-lg font-semibold text-dark dark:text-white">وضعیت به‌روزرسانی سیستم</h3>
            <p class="text-sm text-dark-5 dark:text-dark-6">
                نسخه فعلی: <span class="font-semibold text-primary">{{ $status['current_version'] ?? 'نامشخص' }}</span>
            </p>
        </div>
        <div class="flex items-center gap-2">
            <button
                wire:click="refreshStatus"
                wire:loading.attr="disabled"
                wire:target="refreshStatus"
                class="inline-flex items-center gap-2 rounded-lg border border-primary px-4 py-2 text-sm font-medium text-primary transition hover:bg-primary/10 disabled:cursor-not-allowed disabled:opacity-70"
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 4v6h6M20 20v-6h-6" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20A8 8 0 005 6.74M15 4A8 8 0 0119 17.26" />
                </svg>
                <span>بررسی مجدد</span>
            </button>
            <button
                wire:click="startUpdate"
                wire:loading.attr="disabled"
                wire:target="startUpdate"
                class="inline-flex items-center gap-2 rounded-lg bg-primary px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-primary/80 disabled:cursor-not-allowed disabled:opacity-70"
                @if(!($status['has_update'] ?? false) || $isUpdating) disabled @endif
            >
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 3H21V9" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 14.5L21 3.5" />
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 21H20" />
                </svg>
                <span>شروع به‌روزرسانی</span>
            </button>
        </div>
    </div>

    <div class="space-y-4 px-6 py-5 text-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="rounded-lg border border-stroke p-4 dark:border-dark-3">
                <p class="text-xs text-dark-5 dark:text-dark-6">آخرین نسخه موجود</p>
                <p class="mt-1 text-lg font-semibold text-dark dark:text-white">
                    {{ $status['latest_details']['version'] ?? $status['latest_details']['tag'] ?? $status['latest_details']['short_hash'] ?? \Illuminate\Support\Str::limit($status['latest_version'] ?? '—', 10, '') }}
                </p>
                <p class="text-xs text-dark-5 dark:text-dark-6">
                    {{ $status['latest_details']['message'] ?? '—' }}
                </p>
            </div>

            <div class="rounded-lg border border-stroke p-4 dark:border-dark-3">
                <p class="text-xs text-dark-5 dark:text-dark-6">وضعیت</p>
                @if(! ($status['enabled'] ?? true))
                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-warning px-3 py-1 text-xs font-semibold text-white">غیرفعال</span>
                @elseif($isUpdating)
                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-primary px-3 py-1 text-xs font-semibold text-white">
                        <svg class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 3v3m6.364 0.636l-2.121 2.121M21 12h-3m-0.636 6.364l-2.121-2.121M12 21v-3m-6.364-0.636l2.121-2.121M3 12h3m0.636-6.364l2.121 2.121" />
                        </svg>
                        در حال به‌روزرسانی
                    </span>
                @elseif($status['has_update'] ?? false)
                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-success px-3 py-1 text-xs font-semibold text-white">به‌روزرسانی موجود است</span>
                @else
                    <span class="mt-1 inline-flex items-center gap-1 rounded-full bg-success/10 px-3 py-1 text-xs font-semibold text-success dark:text-success">سیستم به‌روز است</span>
                @endif
            </div>

            <div class="rounded-lg border border-stroke p-4 dark:border-dark-3">
                <p class="text-xs text-dark-5 dark:text-dark-6">آخرین بررسی</p>
                <p class="mt-1 text-lg font-semibold text-dark dark:text-white">
                    {{ $lastCheckedAtLabel ?? 'ثبت نشده' }}
                </p>
            </div>
        </div>

        @if($lastLog)
            <div class="rounded-lg border border-stroke p-4 dark:border-dark-3">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-semibold text-dark dark:text-white">
                        آخرین عملیات: {{ optional($lastLog->created_at)->format('Y-m-d H:i') }}
                    </h4>
                    <span class="inline-flex items-center gap-1 rounded-full bg-dark-3 px-3 py-1 text-xs font-semibold text-white">
                        وضعیت: {{ $this->translateStatus($lastLog->status) }}
                    </span>
                </div>
                <pre class="mt-3 max-h-60 overflow-y-auto whitespace-pre-wrap rounded-lg bg-gray-100 p-3 text-xs text-dark dark:bg-dark-2 dark:text-gray-200">{{ $lastLog->log }}</pre>
            </div>
        @endif
    </div>
</div>
