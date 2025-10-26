<div class="overflow-hidden rounded-lg border border-slate-200 bg-gradient-to-br from-indigo-500 via-purple-500 to-pink-500 shadow-lg">
    <div class="relative p-6 md:p-8">
        {{-- Background Pattern --}}
        <div class="absolute inset-0 opacity-10">
            <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg">
                <defs>
                    <pattern id="welcome-pattern" x="0" y="0" width="40" height="40" patternUnits="userSpaceOnUse">
                        <circle cx="20" cy="20" r="2" fill="white"/>
                    </pattern>
                </defs>
                <rect width="100%" height="100%" fill="url(#welcome-pattern)"/>
            </svg>
        </div>

        <div class="relative grid grid-cols-1 gap-6 md:grid-cols-3">
            {{-- Main Welcome Section --}}
            <div class="md:col-span-2">
                <div class="flex items-start gap-4">
                    {{-- Avatar --}}
                    <div class="flex-shrink-0">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full bg-white/20 backdrop-blur-sm ring-4 ring-white/30">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-full w-full rounded-full object-cover">
                            @else
                                <span class="text-2xl font-bold text-white">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Greeting & Info --}}
                    <div class="flex-1">
                        <h2 class="text-2xl font-bold text-white md:text-3xl">
                            {{ $greeting }}، {{ $user->name }}! 👋
                        </h2>
                        <p class="mt-2 text-sm text-white/90 md:text-base">
                            {{ $motivationalQuote }}
                        </p>

                        {{-- User Role & Last Login --}}
                        <div class="mt-4 flex flex-wrap items-center gap-3">
                            @if($user->roles->isNotEmpty())
                                <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                    <iconify-icon icon="lucide:shield" class="text-sm"></iconify-icon>
                                    {{ $user->roles->first()->name }}
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-1 rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                                <iconify-icon icon="lucide:clock" class="text-sm"></iconify-icon>
                                {{ __('آخرین ورود:') }} {{ $stats['your_last_login'] }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Stats Section --}}
            <div class="md:col-span-1">
                <div class="rounded-lg bg-white/10 p-4 backdrop-blur-sm">
                    <h3 class="mb-3 text-sm font-semibold text-white/90">{{ __('آمار لحظه‌ای') }}</h3>
                    
                    <div class="space-y-2">
                        {{-- Online Users --}}
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs text-white/80">
                                <iconify-icon icon="lucide:users" class="text-base"></iconify-icon>
                                {{ __('آنلاین') }}
                            </span>
                            <span class="text-sm font-bold text-white">{{ number_format($stats['online_users']) }}</span>
                        </div>

                        {{-- Today's New Users --}}
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs text-white/80">
                                <iconify-icon icon="lucide:user-plus" class="text-base"></iconify-icon>
                                {{ __('کاربر جدید امروز') }}
                            </span>
                            <span class="text-sm font-bold text-white">{{ number_format($stats['today_users']) }}</span>
                        </div>

                        {{-- Total Users --}}
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs text-white/80">
                                <iconify-icon icon="lucide:users-round" class="text-base"></iconify-icon>
                                {{ __('کل کاربران') }}
                            </span>
                            <span class="text-sm font-bold text-white">{{ number_format($stats['total_users']) }}</span>
                        </div>

                        {{-- Pending Transactions (if available) --}}
                        @if(isset($stats['pending_transactions']) && $stats['pending_transactions'] > 0)
                            <div class="mt-3 border-t border-white/20 pt-2">
                                <div class="flex items-center justify-between">
                                    <span class="flex items-center gap-2 text-xs text-white/80">
                                        <iconify-icon icon="lucide:file-clock" class="text-base"></iconify-icon>
                                        {{ __('تراکنش در انتظار') }}
                                    </span>
                                    <span class="rounded-full bg-amber-500 px-2 py-0.5 text-xs font-bold text-white">
                                        {{ number_format($stats['pending_transactions']) }}
                                    </span>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Current Date & Time --}}
        <div class="relative mt-6 border-t border-white/20 pt-4">
            <div class="flex items-center justify-between text-xs text-white/80">
                <div class="flex items-center gap-2">
                    <iconify-icon icon="lucide:calendar" class="text-base"></iconify-icon>
                    <span>{{ verta()->format('l، j F Y') }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <iconify-icon icon="lucide:sun" class="text-base"></iconify-icon>
                    <span>{{ __('روزتان پربرکت') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>

