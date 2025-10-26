<div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="relative border-b border-slate-200 bg-gradient-to-l from-slate-50 to-white p-6 md:p-8">

        <div class="relative grid grid-cols-1 gap-6 md:grid-cols-3">
            {{-- Main Welcome Section --}}
            <div class="md:col-span-2">
                <div class="flex items-start gap-4">
                    {{-- Avatar --}}
                    <div class="flex-shrink-0">
                        <div class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-slate-200 bg-slate-100 shadow-sm">
                            @if($user->avatar)
                                <img src="{{ $user->avatar }}" alt="{{ $user->name }}" class="h-full w-full rounded-full object-cover">
                            @else
                                <span class="text-2xl font-bold text-slate-700">
                                    {{ mb_substr($user->name, 0, 1) }}
                                </span>
                            @endif
                        </div>
                    </div>

                    {{-- Greeting & Info --}}
                    <div class="flex-1">
                        <h2 class="text-xl font-semibold text-slate-800 md:text-2xl">
                            {{ $greeting }}، {{ $user->name }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-600">
                            {{ verta()->format('l، j F Y') }}
                        </p>

                        {{-- User Role & Department --}}
                        <div class="mt-3 flex flex-wrap items-center gap-2">
                            @if($user->roles->isNotEmpty())
                                <span class="inline-flex items-center gap-1.5 rounded-md border border-indigo-200 bg-indigo-50 px-2.5 py-1 text-xs font-medium text-indigo-700">
                                    <iconify-icon icon="lucide:shield" class="text-sm"></iconify-icon>
                                    {{ $user->roles->first()->name }}
                                </span>
                            @endif
                            
                            <span class="inline-flex items-center gap-1.5 rounded-md border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-medium text-slate-600">
                                <iconify-icon icon="lucide:building" class="text-sm"></iconify-icon>
                                {{ $user->branch->name ?? __('دفتر مرکزی') }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Quick Stats Section --}}
            <div class="md:col-span-1">
                <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                    <h3 class="mb-3 text-xs font-semibold uppercase tracking-wide text-slate-500">{{ __('آمار سیستم') }}</h3>
                    
                    <div class="space-y-3">
                        {{-- Online Users --}}
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <div class="flex h-6 w-6 items-center justify-center rounded bg-emerald-100">
                                    <iconify-icon icon="lucide:users" class="text-sm text-emerald-600"></iconify-icon>
                                </div>
                                {{ __('آنلاین') }}
                            </span>
                            <span class="text-sm font-semibold text-slate-800">{{ number_format($stats['online_users']) }}</span>
                        </div>

                        {{-- Today's New Users --}}
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <div class="flex h-6 w-6 items-center justify-center rounded bg-blue-100">
                                    <iconify-icon icon="lucide:user-plus" class="text-sm text-blue-600"></iconify-icon>
                                </div>
                                {{ __('جدید امروز') }}
                            </span>
                            <span class="text-sm font-semibold text-slate-800">{{ number_format($stats['today_users']) }}</span>
                        </div>

                        {{-- Total Users --}}
                        <div class="flex items-center justify-between">
                            <span class="flex items-center gap-2 text-xs font-medium text-slate-600">
                                <div class="flex h-6 w-6 items-center justify-center rounded bg-slate-100">
                                    <iconify-icon icon="lucide:users-round" class="text-sm text-slate-600"></iconify-icon>
                                </div>
                                {{ __('کل کاربران') }}
                            </span>
                            <span class="text-sm font-semibold text-slate-800">{{ number_format($stats['total_users']) }}</span>
                        </div>

                        {{-- Pending Transactions (if available) --}}
                        @if(isset($stats['pending_transactions']) && $stats['pending_transactions'] > 0)
                            <div class="mt-3 border-t border-slate-200 pt-3">
                                <div class="flex items-center justify-between rounded-md bg-amber-50 p-2">
                                    <span class="flex items-center gap-2 text-xs font-medium text-amber-700">
                                        <iconify-icon icon="lucide:alert-circle" class="text-sm"></iconify-icon>
                                        {{ __('در انتظار بررسی') }}
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

    </div>
</div>

