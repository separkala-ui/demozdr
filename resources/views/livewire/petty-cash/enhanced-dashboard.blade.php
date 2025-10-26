<div class="space-y-6">
    {{-- Period Selector --}}
    <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-4">
        <div>
            <h2 class="text-lg font-semibold text-slate-800">{{ __('تحلیل مالی شعبه') }} - {{ $ledger->branch_name }}</h2>
            <p class="text-sm text-slate-500">{{ __('داده‌های لحظه‌ای و تحلیل روندها') }}</p>
        </div>
        
        <div class="flex items-center gap-2">
            @foreach(['week' => 'هفته', 'month' => 'ماه', 'quarter' => 'فصل', 'year' => 'سال'] as $key => $label)
                <button
                    wire:click="changePeriod('{{ $key }}')"
                    class="rounded-lg px-4 py-2 text-sm font-medium transition-colors
                        {{ $period === $key 
                            ? 'bg-indigo-600 text-white' 
                            : 'bg-slate-100 text-slate-600 hover:bg-slate-200' 
                        }}
                    "
                >
                    {{ __($label) }}
                </button>
            @endforeach
            
            <button wire:click="$refresh" class="rounded-lg bg-slate-100 p-2 text-slate-600 hover:bg-slate-200">
                <iconify-icon icon="lucide:refresh-cw" class="text-lg"></iconify-icon>
            </button>
        </div>
    </div>

    {{-- KPI Cards Grid --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        {{-- موجودی فعلی --}}
        <div class="relative overflow-hidden rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">{{ __('موجودی فعلی') }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">
                        {{ $kpis['current_balance']['formatted'] }}
                        <span class="text-sm text-slate-500">{{ __('ریال') }}</span>
                    </p>
                    <div class="mt-2 flex items-center gap-2">
                        <div class="h-2 w-full rounded-full bg-slate-200">
                            <div 
                                class="h-2 rounded-full transition-all
                                    {{ $kpis['current_balance']['status'] === 'critical' ? 'bg-rose-500' : 
                                       ($kpis['current_balance']['status'] === 'warning' ? 'bg-amber-500' : 'bg-emerald-500') }}
                                "
                                style="width: {{ $kpis['current_balance']['percentage'] }}%"
                            ></div>
                        </div>
                        <span class="text-xs font-semibold text-slate-600">{{ $kpis['current_balance']['percentage'] }}%</span>
                    </div>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full
                    {{ $kpis['current_balance']['status'] === 'critical' ? 'bg-rose-100' : 
                       ($kpis['current_balance']['status'] === 'warning' ? 'bg-amber-100' : 'bg-emerald-100') }}
                ">
                    <iconify-icon icon="lucide:wallet" class="text-2xl
                        {{ $kpis['current_balance']['status'] === 'critical' ? 'text-rose-600' : 
                           ($kpis['current_balance']['status'] === 'warning' ? 'text-amber-600' : 'text-emerald-600') }}
                    "></iconify-icon>
                </div>
            </div>
        </div>

        {{-- کل هزینه‌ها --}}
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <p class="text-xs font-medium text-slate-500">{{ __('کل هزینه‌ها') }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">
                        {{ $kpis['total_expenses']['formatted'] }}
                    </p>
                    @if(isset($kpis['total_expenses']['trend']))
                        <div class="mt-2 flex items-center gap-1">
                            <iconify-icon 
                                icon="{{ $kpis['total_expenses']['trend']['direction'] === 'up' ? 'lucide:trending-up' : 'lucide:trending-down' }}"
                                class="text-sm {{ $kpis['total_expenses']['trend']['direction'] === 'up' ? 'text-rose-500' : 'text-emerald-500' }}"
                            ></iconify-icon>
                            <span class="text-xs font-semibold {{ $kpis['total_expenses']['trend']['direction'] === 'up' ? 'text-rose-600' : 'text-emerald-600' }}">
                                {{ $kpis['total_expenses']['trend']['value'] }}%
                            </span>
                            <span class="text-xs text-slate-500">{{ __('نسبت به دوره قبل') }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-rose-100">
                    <iconify-icon icon="lucide:trending-down" class="text-2xl text-rose-600"></iconify-icon>
                </div>
            </div>
        </div>

        {{-- میانگین هزینه روزانه --}}
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">{{ __('میانگین روزانه') }}</p>
                    <p class="mt-2 text-2xl font-bold text-slate-900">
                        {{ $kpis['avg_daily_expense']['formatted'] }}
                    </p>
                    <p class="mt-2 text-xs text-slate-500">
                        {{ __('هزینه در هر روز') }}
                    </p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                    <iconify-icon icon="lucide:calendar" class="text-2xl text-blue-600"></iconify-icon>
                </div>
            </div>
        </div>

        {{-- Burn Rate --}}
        <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500">{{ __('پیش‌بینی اتمام') }}</p>
                    <p class="mt-2 text-2xl font-bold
                        {{ $kpis['burn_rate']['status'] === 'critical' ? 'text-rose-600' : 
                           ($kpis['burn_rate']['status'] === 'warning' ? 'text-amber-600' : 'text-emerald-600') }}
                    ">
                        {{ $kpis['burn_rate']['days'] }}
                        <span class="text-sm">{{ __('روز') }}</span>
                    </p>
                    <p class="mt-2 text-xs text-slate-500">
                        {{ __('تا پایان موجودی') }}
                    </p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-full
                    {{ $kpis['burn_rate']['status'] === 'critical' ? 'bg-rose-100' : 
                       ($kpis['burn_rate']['status'] === 'warning' ? 'bg-amber-100' : 'bg-emerald-100') }}
                ">
                    <iconify-icon icon="lucide:flame" class="text-2xl
                        {{ $kpis['burn_rate']['status'] === 'critical' ? 'text-rose-600' : 
                           ($kpis['burn_rate']['status'] === 'warning' ? 'text-amber-600' : 'text-emerald-600') }}
                    "></iconify-icon>
                </div>
            </div>
        </div>
    </div>

    {{-- Row 2: More KPIs --}}
    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
        {{-- تعداد تراکنش‌ها --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">{{ __('تعداد تراکنش') }}</p>
                    <p class="mt-1 text-xl font-bold text-slate-800">{{ $kpis['transaction_count']['value'] }}</p>
                </div>
                <iconify-icon icon="lucide:list" class="text-2xl text-indigo-500"></iconify-icon>
            </div>
        </div>

        {{-- میانگین تراکنش --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">{{ __('میانگین تراکنش') }}</p>
                    <p class="mt-1 text-xl font-bold text-slate-800">{{ $kpis['avg_transaction']['formatted'] }}</p>
                </div>
                <iconify-icon icon="lucide:calculator" class="text-2xl text-purple-500"></iconify-icon>
            </div>
        </div>

        {{-- در انتظار --}}
        <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-amber-700">{{ __('در انتظار بررسی') }}</p>
                    <p class="mt-1 text-xl font-bold text-amber-900">
                        {{ $kpis['pending_count']['value'] }}
                        <span class="text-sm text-amber-600">تراکنش</span>
                    </p>
                </div>
                <iconify-icon icon="lucide:clock" class="text-2xl text-amber-600"></iconify-icon>
            </div>
        </div>

        {{-- کارایی --}}
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs text-slate-500">{{ __('نرخ کارایی') }}</p>
                    <p class="mt-1 text-xl font-bold
                        {{ $kpis['efficiency_rate']['status'] === 'excellent' ? 'text-emerald-600' : 
                           ($kpis['efficiency_rate']['status'] === 'good' ? 'text-blue-600' : 'text-amber-600') }}
                    ">
                        {{ $kpis['efficiency_rate']['value'] }}%
                    </p>
                </div>
                <iconify-icon icon="lucide:zap" class="text-2xl text-emerald-500"></iconify-icon>
            </div>
        </div>
    </div>

    {{-- Comparison & Predictions --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
        {{-- مقایسه با دوره قبل --}}
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <h3 class="text-sm font-semibold text-slate-800">{{ __('مقایسه با دوره قبل') }}</h3>
            <div class="mt-4 space-y-3">
                <div>
                    <p class="text-xs text-slate-500">{{ __('دوره فعلی') }}</p>
                    <p class="text-lg font-bold text-slate-900">{{ number_format($comparison['current']) }}</p>
                </div>
                <div>
                    <p class="text-xs text-slate-500">{{ __('دوره قبل') }}</p>
                    <p class="text-lg font-semibold text-slate-600">{{ number_format($comparison['previous']) }}</p>
                </div>
                <div class="flex items-center gap-2 rounded-lg p-3
                    {{ $comparison['direction'] === 'up' ? 'bg-rose-50' : 'bg-emerald-50' }}
                ">
                    <iconify-icon 
                        icon="{{ $comparison['direction'] === 'up' ? 'lucide:arrow-up' : 'lucide:arrow-down' }}"
                        class="text-xl {{ $comparison['direction'] === 'up' ? 'text-rose-600' : 'text-emerald-600' }}"
                    ></iconify-icon>
                    <span class="text-sm font-bold {{ $comparison['direction'] === 'up' ? 'text-rose-700' : 'text-emerald-700' }}">
                        {{ abs($comparison['change']) }}%
                    </span>
                    <span class="text-xs text-slate-600">
                        {{ $comparison['direction'] === 'up' ? __('افزایش') : __('کاهش') }}
                    </span>
                </div>
            </div>
        </div>

        {{-- پیش‌بینی --}}
        <div class="rounded-lg border border-slate-200 bg-gradient-to-br from-indigo-50 to-purple-50 p-5">
            <h3 class="text-sm font-semibold text-slate-800">{{ __('پیش‌بینی هزینه‌ها') }}</h3>
            <div class="mt-4 space-y-3">
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-600">{{ __('7 روز آینده') }}</span>
                    <span class="font-semibold text-slate-800">{{ number_format($predictions['next_7_days']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-600">{{ __('30 روز آینده') }}</span>
                    <span class="font-semibold text-slate-800">{{ number_format($predictions['next_30_days']) }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-xs text-slate-600">{{ __('تا پایان ماه') }}</span>
                    <span class="font-bold text-indigo-700">{{ number_format($predictions['end_of_month']) }}</span>
                </div>
            </div>
            <p class="mt-3 text-[10px] text-slate-500">
                <iconify-icon icon="lucide:info" class="text-xs"></iconify-icon>
                {{ __('پیش‌بینی بر اساس میانگین هزینه روزانه') }}
            </p>
        </div>

        {{-- بیشترین هزینه‌ها --}}
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <h3 class="text-sm font-semibold text-slate-800">{{ __('بالاترین هزینه‌ها') }}</h3>
            <div class="mt-4 space-y-2">
                @forelse($topExpenses as $expense)
                    <div class="flex items-center justify-between rounded-lg bg-slate-50 p-2">
                        <div class="flex-1 min-w-0">
                            <p class="truncate text-xs font-medium text-slate-800">{{ $expense['description'] ?? '-' }}</p>
                            <p class="text-[10px] text-slate-500">{{ $expense['reference'] }}</p>
                        </div>
                        <span class="text-sm font-bold text-rose-600">{{ number_format($expense['amount']) }}</span>
                    </div>
                @empty
                    <p class="text-center text-xs text-slate-400">{{ __('بدون داده') }}</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Charts Row --}}
    <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
        {{-- Trend Chart --}}
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <h3 class="text-sm font-semibold text-slate-800">{{ __('روند هزینه‌های روزانه') }}</h3>
            <div class="mt-4 h-64 flex items-center justify-center text-slate-400">
                <div class="text-center">
                    <iconify-icon icon="lucide:line-chart" class="text-5xl"></iconify-icon>
                    <p class="mt-2 text-xs">{{ __('نمودار در حال توسعه...') }}</p>
                    <p class="mt-1 text-[10px]">{{ count($trends['daily_expenses']) }} {{ __('روز داده') }}</p>
                </div>
            </div>
        </div>

        {{-- Category Breakdown --}}
        <div class="rounded-lg border border-slate-200 bg-white p-5">
            <h3 class="text-sm font-semibold text-slate-800">{{ __('تفکیک بر اساس دسته') }}</h3>
            <div class="mt-4 space-y-3">
                @foreach($categoryBreakdown as $category)
                    <div>
                        <div class="flex items-center justify-between text-xs">
                            <span class="font-medium text-slate-700">{{ $category['category'] }}</span>
                            <span class="font-bold text-slate-900">{{ number_format($category['amount']) }}</span>
                        </div>
                        <div class="mt-1 h-2 w-full rounded-full bg-slate-100">
                            <div 
                                class="h-2 rounded-full bg-indigo-500"
                                style="width: {{ $category['percentage'] }}%"
                            ></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Status Breakdown --}}
    <div class="rounded-lg border border-slate-200 bg-white p-5">
        <h3 class="text-sm font-semibold text-slate-800">{{ __('وضعیت تراکنش‌ها') }}</h3>
        <div class="mt-4 grid grid-cols-2 gap-4 md:grid-cols-4">
            @foreach($statusBreakdown as $statusItem)
                @php
                    $statusConfig = match($statusItem['status']) {
                        'submitted' => [
                            'label' => 'در انتظار تایید',
                            'icon' => 'lucide:clock',
                            'bg' => 'bg-amber-50',
                            'border' => 'border-amber-200',
                            'text' => 'text-amber-700',
                            'icon_color' => 'text-amber-500',
                            'count_color' => 'text-amber-900'
                        ],
                        'approved' => [
                            'label' => 'تایید شده',
                            'icon' => 'lucide:check-circle',
                            'bg' => 'bg-emerald-50',
                            'border' => 'border-emerald-200',
                            'text' => 'text-emerald-700',
                            'icon_color' => 'text-emerald-500',
                            'count_color' => 'text-emerald-900'
                        ],
                        'rejected' => [
                            'label' => 'رد شده',
                            'icon' => 'lucide:x-circle',
                            'bg' => 'bg-rose-50',
                            'border' => 'border-rose-200',
                            'text' => 'text-rose-700',
                            'icon_color' => 'text-rose-500',
                            'count_color' => 'text-rose-900'
                        ],
                        'needs_changes' => [
                            'label' => 'نیاز به اصلاح',
                            'icon' => 'lucide:alert-circle',
                            'bg' => 'bg-orange-50',
                            'border' => 'border-orange-200',
                            'text' => 'text-orange-700',
                            'icon_color' => 'text-orange-500',
                            'count_color' => 'text-orange-900'
                        ],
                        'draft' => [
                            'label' => 'پیش‌نویس',
                            'icon' => 'lucide:file-text',
                            'bg' => 'bg-slate-50',
                            'border' => 'border-slate-200',
                            'text' => 'text-slate-700',
                            'icon_color' => 'text-slate-500',
                            'count_color' => 'text-slate-900'
                        ],
                        'under_review' => [
                            'label' => 'در حال بررسی',
                            'icon' => 'lucide:search',
                            'bg' => 'bg-purple-50',
                            'border' => 'border-purple-200',
                            'text' => 'text-purple-700',
                            'icon_color' => 'text-purple-500',
                            'count_color' => 'text-purple-900'
                        ],
                        default => [
                            'label' => $statusItem['status'],
                            'icon' => 'lucide:help-circle',
                            'bg' => 'bg-gray-50',
                            'border' => 'border-gray-200',
                            'text' => 'text-gray-700',
                            'icon_color' => 'text-gray-500',
                            'count_color' => 'text-gray-900'
                        ]
                    };
                @endphp
                <div class="group relative overflow-hidden rounded-lg border {{ $statusConfig['border'] }} {{ $statusConfig['bg'] }} p-4 transition-all hover:shadow-md">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-2">
                                <iconify-icon icon="{{ $statusConfig['icon'] }}" class="text-lg {{ $statusConfig['icon_color'] }}"></iconify-icon>
                                <p class="text-xs font-semibold {{ $statusConfig['text'] }}">{{ $statusConfig['label'] }}</p>
                            </div>
                            <p class="mt-2 text-2xl font-bold {{ $statusConfig['count_color'] }}">{{ $statusItem['count'] }}</p>
                            <div class="mt-1 flex items-center gap-1">
                                <iconify-icon icon="lucide:coins" class="text-xs {{ $statusConfig['icon_color'] }}"></iconify-icon>
                                <p class="text-[10px] font-medium {{ $statusConfig['text'] }}">{{ number_format($statusItem['amount']) }} ریال</p>
                            </div>
                        </div>
                    </div>
                    {{-- Decorative element --}}
                    <div class="absolute -bottom-1 -right-1 h-16 w-16 rounded-full {{ $statusConfig['bg'] }} opacity-50 blur-2xl transition-all group-hover:scale-150"></div>بر
                </div>
            @endforeach
        </div>
    </div>
</div>

