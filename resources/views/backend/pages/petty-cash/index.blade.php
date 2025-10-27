@php
    $isAdminUser = isset($isAdminUser)
        ? (bool) $isAdminUser
        : (bool) (auth()->user()?->hasRole(['Superadmin', 'Admin']) ?? false);
    $selectedLedgerMetrics = $selectedLedgerMetrics ?? [];
    $visibleLedgers = $visibleLedgers ?? $ledgers;
    $showAllCards = $showAllCards ?? false;
    $showAllTransactions = $showAllTransactions ?? collect();
    $chargeTimeline = isset($chargeTimeline) ? collect($chargeTimeline) : collect();
    $analytics = $analytics ?? null;
    $analyticsFilters = $analyticsFilters ?? ['period' => 'last_30'];
    $analyticsPeriodOptions = $analyticsPeriodOptions ?? [];
    if (! isset($adminArchives)) {
        $adminArchives = collect();
    }
    if (! isset($branchArchives)) {
        $branchArchives = collect();
    }
    $cardPalettes = [
        ['border' => 'border-indigo-200', 'badge_bg' => 'bg-indigo-100', 'badge_text' => 'text-indigo-700', 'progress' => 'bg-indigo-500', 'row_bg' => 'bg-indigo-50', 'dot' => 'bg-indigo-400'],
        ['border' => 'border-emerald-200', 'badge_bg' => 'bg-emerald-100', 'badge_text' => 'text-emerald-700', 'progress' => 'bg-emerald-500', 'row_bg' => 'bg-emerald-50', 'dot' => 'bg-emerald-400'],
        ['border' => 'border-amber-200', 'badge_bg' => 'bg-amber-100', 'badge_text' => 'text-amber-700', 'progress' => 'bg-amber-500', 'row_bg' => 'bg-amber-50', 'dot' => 'bg-amber-400'],
        ['border' => 'border-rose-200', 'badge_bg' => 'bg-rose-100', 'badge_text' => 'text-rose-700', 'progress' => 'bg-rose-500', 'row_bg' => 'bg-rose-50', 'dot' => 'bg-rose-400'],
        ['border' => 'border-sky-200', 'badge_bg' => 'bg-sky-100', 'badge_text' => 'text-sky-700', 'progress' => 'bg-sky-500', 'row_bg' => 'bg-sky-50', 'dot' => 'bg-sky-400'],
    ];
    $statusStyles = [
        'approved' => ['label' => __('تایید شده'), 'badge' => 'bg-emerald-100 text-emerald-700', 'dot' => 'bg-emerald-500'],
        'submitted' => ['label' => __('در انتظار تایید'), 'badge' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-500'],
        'draft' => ['label' => __('پیش‌نویس'), 'badge' => 'bg-slate-100 text-slate-600', 'dot' => 'bg-slate-400'],
        'rejected' => ['label' => __('رد شده'), 'badge' => 'bg-rose-100 text-rose-700', 'dot' => 'bg-rose-500'],
        'needs_changes' => ['label' => __('نیاز به اصلاح'), 'badge' => 'bg-amber-100 text-amber-700', 'dot' => 'bg-amber-500'],
        'under_review' => ['label' => __('در حال بررسی مدیریت'), 'badge' => 'bg-purple-100 text-purple-700', 'dot' => 'bg-purple-500'],
    ];
    $typeLabels = [
        'charge' => __('شارژ'),
        'expense' => __('هزینه'),
        'adjustment' => __('تعدیل'),
    ];
    $branchPaletteMap = $visibleLedgers->values()->mapWithKeys(function ($ledgerItem, $index) use ($cardPalettes) {
        $palette = $cardPalettes[$index % count($cardPalettes)];
        return [$ledgerItem->id => $palette];
    })->toArray();
    $ledgerRouteParams = function (?int $ledgerId = null, ?bool $forceShowAll = null) use ($showAllCards) {
        $params = [];
        if ($ledgerId) {
            $params['ledger'] = $ledgerId;
        }
        $shouldShowAll = $forceShowAll ?? $showAllCards;
        if ($shouldShowAll) {
            $params['show_all'] = 1;
        }
        return $params;
    };
    $formatUserName = function ($user) {
        if (! $user) {
            return null;
        }
        if (! empty($user->full_name)) {
            return $user->full_name;
        }
        $combined = trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? ''));
        if ($combined !== '') {
            return $combined;
        }
        return $user->name ?? $user->email ?? null;
    };
@endphp

<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        {{-- Modern Header with Enhanced Navigation --}}
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-gradient-to-l from-slate-50 to-white shadow-sm">
            {{-- Top Section: Title + Branch Selector --}}
            <div class="border-b border-slate-200 bg-white p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 shadow-md">
                            <iconify-icon icon="lucide:wallet" class="text-2xl text-white"></iconify-icon>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-slate-800">{{ __('مدیریت تنخواه شعب') }}</h1>
                            <p class="text-sm text-slate-500">{{ __('نظارت بر مانده، هزینه‌ها و شارژهای هر شعبه') }}</p>
                        </div>
                    </div>

                    @if($isAdminUser && $ledgers->isNotEmpty())
                        <div class="flex items-center gap-3">
                            <iconify-icon icon="lucide:building-2" class="text-xl text-slate-400"></iconify-icon>
                            <select
                                id="ledgerSwitcher"
                                class="block rounded-lg border-slate-300 px-4 py-2.5 text-sm font-medium shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
                                onchange="window.location.href=this.value;"
                            >
                                @foreach($ledgers as $ledgerOption)
                                    <option value="{{ route('admin.petty-cash.index', $ledgerRouteParams($ledgerOption->id)) }}"
                                        @if($selectedLedger && $selectedLedger->id === $ledgerOption->id) selected @endif>
                                        {{ $ledgerOption->branch_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Action Navigation Bar --}}
            @if($isAdminUser)
                <div class="bg-slate-50/50 p-4">
                    <div class="flex flex-wrap items-center gap-3">
                        {{-- Primary Action --}}
                        <a href="{{ route('admin.petty-cash.create') }}"
                           class="group relative inline-flex items-center gap-2.5 overflow-hidden rounded-lg bg-gradient-to-r from-indigo-600 to-purple-600 px-5 py-3 font-semibold text-white shadow-md transition-all hover:scale-105 hover:shadow-lg">
                            <iconify-icon icon="lucide:plus-circle" class="text-xl transition-transform group-hover:rotate-90"></iconify-icon>
                            <span>{{ __('ایجاد دفتر تنخواه') }}</span>
                            <div class="absolute inset-0 -z-10 bg-gradient-to-r from-indigo-700 to-purple-700 opacity-0 transition-opacity group-hover:opacity-100"></div>
                        </a>

                        {{-- Secondary Actions --}}
                        @if($ledgers->isNotEmpty())
                            <a href="{{ $showAllCards ? route('admin.petty-cash.index', $ledgerRouteParams($selectedLedger?->id, false)) : route('admin.petty-cash.index', $ledgerRouteParams($selectedLedger?->id, true)) }}"
                               class="group inline-flex items-center gap-2 rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 font-medium text-slate-700 shadow-sm transition-all hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700">
                                <iconify-icon icon="{{ $showAllCards ? 'lucide:folder' : 'lucide:folders' }}" class="text-lg transition-transform group-hover:scale-110"></iconify-icon>
                                <span>{{ $showAllCards ? __('نمایش شعبه انتخابی') : __('نمایش همه شعبه‌ها') }}</span>
                            </a>
                        @endif

                        @if($isAdminUser)
                            <a href="{{ route('admin.petty-cash.archives.index') }}"
                               class="group inline-flex items-center gap-2 rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 font-medium text-slate-700 shadow-sm transition-all hover:border-amber-400 hover:bg-amber-50 hover:text-amber-700">
                                <iconify-icon icon="lucide:archive" class="text-lg transition-transform group-hover:scale-110"></iconify-icon>
                                <span>{{ __('داشبورد اسناد بایگانی') }}</span>
                            </a>
                        @endif

                        @can('petty_cash.ledger.delete')
                            <a href="{{ route('admin.petty-cash.backups') }}"
                               class="group inline-flex items-center gap-2 rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 font-medium text-slate-700 shadow-sm transition-all hover:border-emerald-400 hover:bg-emerald-50 hover:text-emerald-700">
                                <iconify-icon icon="lucide:database" class="text-lg transition-transform group-hover:scale-110"></iconify-icon>
                                <span>{{ __('مدیریت بک‌آپ‌ها') }}</span>
                            </a>
                        @endcan

                        @if(auth()->user()?->hasRole('Superadmin'))
                            <form method="POST" action="{{ route('admin.petty-cash.module-backup') }}" class="inline-block">
                                @csrf
                                <button type="submit"
                                        class="group inline-flex items-center gap-2 rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 font-medium text-slate-700 shadow-sm transition-all hover:border-blue-400 hover:bg-blue-50 hover:text-blue-700">
                                    <iconify-icon icon="lucide:package" class="text-lg transition-transform group-hover:scale-110"></iconify-icon>
                                    <span>{{ __('دریافت بسته نصب') }}</span>
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            {{-- Success Message --}}
            @if(session('success'))
                <div class="border-t border-green-200 bg-gradient-to-r from-green-50 to-emerald-50 p-4">
                    <div class="flex items-center gap-3">
                        <div class="flex h-8 w-8 items-center justify-center rounded-full bg-green-500">
                            <iconify-icon icon="lucide:check" class="text-lg text-white"></iconify-icon>
                        </div>
                        <p class="font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                </div>
            @endif
        </div>

        @if($isAdminUser && ($showAllCards || ! $selectedLedger))
            <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                @forelse($visibleLedgers as $ledgerItem)
                    @php
                        $palette = $cardPalettes[$loop->index % count($cardPalettes)];
                    @endphp
                    <div class="rounded-lg border {{ $palette['border'] }} bg-white p-4 shadow-sm">
                        <div class="flex items-start justify-between gap-2">
                            <div class="space-y-1">
                                <div class="flex items-center gap-2">
                                    <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $palette['badge_bg'] }} {{ $palette['badge_text'] }}">
                                        {{ __('شعبه') }}
                                    </span>
                                    <h3 class="text-base font-semibold text-slate-700">{{ $ledgerItem->branch_name }}</h3>
                                </div>
                                <p class="text-xs text-slate-500">{{ __('سقف تنخواه شعبه :amount ریال', ['amount' => number_format($ledgerItem->limit_amount)]) }}</p>
                            </div>

                            <div class="flex gap-2">
                                <a href="{{ route('admin.petty-cash.index', $ledgerRouteParams($ledgerItem->id, false)) }}"
                                   class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-3 py-1 text-xs font-medium text-indigo-600 hover:bg-indigo-100">
                                    <i class="fas fa-eye text-indigo-500"></i>
                                    {{ __('مشاهده') }}
                                </a>
                                <button type="button"
                                        wire:click="$dispatch('openBranchUsersModal', { ledgerId: {{ $ledgerItem->id }} })"
                                        class="inline-flex items-center gap-1 rounded-md bg-sky-50 px-3 py-1 text-xs font-medium text-sky-600 hover:bg-sky-100 transition-colors hover:bg-sky-200">
                                    <iconify-icon icon="lucide:users" class="text-sky-500"></iconify-icon>
                                    {{ __('کاربران') }}
                                </button>
                                @can('petty_cash.ledger.edit')
                                    <a href="{{ route('admin.petty-cash.edit', $ledgerItem->id) }}"
                                       class="inline-flex items-center gap-1 rounded-md bg-yellow-50 px-3 py-1 text-xs font-medium text-yellow-600 hover:bg-yellow-100">
                                        <i class="fas fa-edit text-yellow-500"></i>
                                        {{ __('ویرایش') }}
                                    </a>
                                @endcan
                                @can('petty_cash.ledger.delete')
                                    <a href="{{ route('admin.petty-cash.delete', $ledgerItem->id) }}"
                                       class="inline-flex items-center gap-1 rounded-md bg-red-50 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-100">
                                        <i class="fas fa-trash text-red-500"></i>
                                        {{ __('حذف') }}
                                    </a>
                                @endcan
                            </div>
                        </div>

                        <dl class="mt-4 space-y-2 text-sm text-slate-600">
                            <div class="flex items-center justify-between">
                                <dt>{{ __('مانده تایید شده') }}</dt>
                                <dd class="font-semibold text-slate-800">{{ number_format($ledgerItem->current_balance) }} {{ __('ریال') }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('مسئول تنخواه') }}</dt>
                                @if($ledgerItem->assignedUser)
                                    <dd class="font-semibold text-slate-800">
                                        {{ $formatUserName($ledgerItem->assignedUser) }}
                                    </dd>
                                @else
                                    <dd class="font-semibold text-amber-600">
                                        {{ __('مسئول انتخاب نشده') }}
                                    </dd>
                                @endif
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('مانده در انتظار تایید') }}</dt>
                                <dd class="font-semibold {{ ($ledgerItem->pending_balance_delta ?? 0) < 0 ? 'text-red-600' : 'text-amber-600' }}">
                                    {{ number_format($ledgerItem->pending_balance ?? $ledgerItem->current_balance) }} {{ __('ریال') }}
                                </dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('ورودی دوره') }}</dt>
                                <dd>{{ number_format($ledgerItem->opening_balance) }} {{ __('ریال') }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('هزینه‌های در انتظار تایید') }}</dt>
                                <dd>{{ number_format($ledgerItem->pending_expenses_total ?? 0) }} {{ __('ریال') }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('شارژهای در انتظار تایید') }}</dt>
                                <dd>{{ number_format($ledgerItem->pending_charges_total ?? 0) }} {{ __('ریال') }}</dd>
                            </div>
                            <div class="flex items-center justify-between">
                                <dt>{{ __('تعداد تراکنش‌های در انتظار تایید') }}</dt>
                                <dd>{{ $ledgerItem->pending_transactions_count ?? 0 }}</dd>
                            </div>
                            @if(($ledgerItem->pending_charge_requests_count ?? 0) > 0)
                                <div class="flex items-center justify-between">
                                    <dt>{{ __('درخواست شارژ در انتظار') }}</dt>
                                    <dd class="font-semibold text-amber-600">{{ $ledgerItem->pending_charge_requests_count }}</dd>
                                </div>
                            @endif
                            @if(($ledgerItem->archived_cycles_count ?? 0) > 0)
                                <div class="flex items-center justify-between">
                                    <dt>{{ __('اسناد بایگانی شده') }}</dt>
                                    <dd class="font-semibold text-indigo-600">{{ $ledgerItem->archived_cycles_count }}</dd>
                                </div>
                                @php
                                    $lastArchive = $ledgerItem->last_archived_cycle ?? null;
                                @endphp
                                @if($lastArchive)
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <dt>{{ __('آخرین تسویه ثبت شده') }}</dt>
                                        <dd class="text-right">
                                            {{ isset($lastArchive['closed_at']) ? verta($lastArchive['closed_at'])->format('Y/m/d H:i') : '—' }}
                                            <span class="block text-[11px] text-slate-400">{{ __('تعداد تراکنش: :count', ['count' => $lastArchive['transactions_count'] ?? 0]) }}</span>
                                        </dd>
                                    </div>
                                @endif
                            @endif
                            <div class="flex items-center justify-between">
                                <dt>{{ __('آخرین تراکنش') }}</dt>
                                <dd>
                                    @if(!empty($ledgerItem->last_transaction_at))
                                        {{ verta($ledgerItem->last_transaction_at)->format('Y/m/d H:i') }}
                                    @else
                                        <span class="text-slate-400">{{ __('ثبت نشده') }}</span>
                                    @endif
                                </dd>
                            </div>
                        </dl>

                        @php
                            $limit = (float) $ledgerItem->limit_amount;
                            $balance = (float) $ledgerItem->current_balance;
                            $consumed = $limit > 0 ? max(0, min(100, round((($limit - $balance) / $limit) * 100, 2))) : null;
                        @endphp

                        @if(! is_null($consumed))
                            <div class="mt-4">
                                <div class="flex justify-between text-xs text-slate-500">
                                    <span>{{ __('درصد مصرف شده') }}</span>
                                    <span>{{ $consumed }}%</span>
                                </div>
                                <div class="mt-1 h-2 rounded-full bg-slate-100">
                                    <div class="h-2 rounded-full {{ $palette['progress'] }}" style="width: {{ $consumed }}%"></div>
            </div>
                @if(method_exists($branchArchives, 'links'))
                    <div class="mt-3 border-t border-slate-100 pt-3 text-xs">
                        {{ $branchArchives->links() }}
                    </div>
                @endif
            </div>
        @endif
                    </div>
                @empty
                    <div class="col-span-1 rounded-lg border border-dashed border-slate-300 bg-slate-50 p-6 text-center text-sm text-slate-500 lg:col-span-3">
                        {{ __('هنوز دفتری برای تنخواه ایجاد نشده است. پس از اجرای مایگریشن و افزودن شعبه‌ها، اطلاعات در اینجا نمایش داده می‌شود.') }}
                    </div>
                @endforelse
            </div>
        @endif

        @if($selectedLedger && $isAdminUser && $adminArchives->count())
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    @php
                        $adminArchivesTotal = method_exists($adminArchives, 'total') ? $adminArchives->total() : $adminArchives->count();
                    @endphp
                    <h2 class="text-sm font-semibold text-slate-700">
                        {{ __('اسناد بایگانی شده شعبه :branch', ['branch' => $selectedLedger->branch_name]) }}
                    </h2>
                    <span class="text-xs text-slate-500">{{ __('نمایش :count سند اخیر', ['count' => $adminArchivesTotal]) }}</span>
                </div>
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-left text-xs">
                        <thead class="bg-slate-50 text-slate-500">
                            <tr>
                                <th class="px-3 py-2 font-medium">{{ __('شناسه فصل') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('شروع') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('پایان') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('تعداد تراکنش') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('ورودی (ریال)') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('خروجی (ریال)') }}</th>
                                <th class="px-3 py-2 font-medium">{{ __('تایید کننده') }}</th>
                                <th class="px-3 py-2 font-medium text-center">{{ __('دانلود') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 bg-white text-slate-600">
                            @foreach($adminArchives as $archive)
                                <tr>
                                    <td class="px-3 py-2 font-medium text-slate-700">#{{ $archive->id }}</td>
                                    <td class="px-3 py-2">{{ $archive->opened_at ? verta($archive->opened_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="px-3 py-2">{{ $archive->closed_at ? verta($archive->closed_at)->format('Y/m/d H:i') : '—' }}</td>
                                    <td class="px-3 py-2">{{ $archive->transactions_count ?? 0 }}</td>
                                    <td class="px-3 py-2">{{ number_format($archive->total_charges ?? 0) }}</td>
                                    <td class="px-3 py-2">{{ number_format($archive->total_expenses ?? 0) }}</td>
                                    <td class="px-3 py-2">{{ optional($archive->closer)->full_name ?? optional($archive->closer)->name ?? __('نامشخص') }}</td>
                                    <td class="px-3 py-2 text-center space-x-2 space-x-reverse">
                                        <a href="{{ route('admin.petty-cash.print', ['ledger' => $selectedLedger->id, 'cycle' => $archive->id]) }}" target="_blank" class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                            <i class="fas fa-print"></i>
                                            {{ __('چاپ') }}
                                        </a>
                                        @if($archive->report_path)
                                            <a href="{{ route('admin.petty-cash.archives.download', ['ledger' => $selectedLedger->id, 'cycle' => $archive->id]) }}" class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-600 hover:bg-emerald-100">
                                                <i class="fas fa-file-excel"></i>
                                                {{ __('دانلود') }}
                                            </a>
                                        @else
                                            <span class="text-slate-400">{{ __('ناموجود') }}</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @if(method_exists($adminArchives, 'links'))
                    <div class="mt-3 border-t border-slate-100 pt-3 text-xs">
                        {{ $adminArchives->links() }}
                    </div>
                @endif
            </div>
        @endif

        @if($selectedLedger && ! $isAdminUser && $branchArchives->count())
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold text-slate-700">
                        {{ __('تسویه‌های بایگانی شده شما') }}
                    </h2>
                    @php
                        $branchArchivesTotal = method_exists($branchArchives, 'total') ? $branchArchives->total() : $branchArchives->count();
                    @endphp
                    <span class="text-xs text-slate-500">{{ __('نمایش آخرین :count مورد', ['count' => $branchArchivesTotal]) }}</span>
                </div>
                <div class="mt-4 space-y-3">
                    @foreach($branchArchives as $archive)
                        <div class="rounded-md border border-slate-100 bg-slate-50 p-3">
                            <div class="flex items-center justify-between text-xs text-slate-500">
                                <span>{{ __('شناسه فصل') }} #{{ $archive->id }}</span>
                                <span>{{ __('تاریخ تسویه') }}: {{ $archive->closed_at ? verta($archive->closed_at)->format('Y/m/d H:i') : '—' }}</span>
                            </div>
                            <div class="mt-2 grid grid-cols-2 gap-2 text-xs text-slate-600 md:grid-cols-4">
                                <div>
                                    <p class="text-slate-500">{{ __('تعداد تراکنش') }}</p>
                                    <p class="font-medium text-slate-700">{{ $archive->transactions_count ?? 0 }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">{{ __('ورودی') }}</p>
                                    <p class="font-medium text-emerald-600">{{ number_format($archive->total_charges ?? 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">{{ __('خروجی') }}</p>
                                    <p class="font-medium text-rose-600">{{ number_format($archive->total_expenses ?? 0) }}</p>
                                </div>
                                <div>
                                    <p class="text-slate-500">{{ __('مانده پایان') }}</p>
                                    <p class="font-medium text-slate-700">{{ number_format($archive->closing_balance ?? 0) }}</p>
                                </div>
                            </div>
                            <div class="mt-3 flex items-center justify-between text-xs text-slate-500">
                                <span>{{ __('تایید شده توسط') }} {{ optional($archive->closer)->full_name ?? optional($archive->closer)->name ?? __('مدیر') }}</span>
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.petty-cash.print', ['ledger' => $selectedLedger->id, 'cycle' => $archive->id]) }}" target="_blank" class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-2 py-1 text-xs font-medium text-slate-600 hover:bg-slate-100">
                                        <i class="fas fa-print"></i>
                                        {{ __('مشاهده سند') }}
                                    </a>
                                    @if($archive->report_path)
                                        <a href="{{ route('admin.petty-cash.archives.download', ['ledger' => $selectedLedger->id, 'cycle' => $archive->id]) }}" class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-xs font-medium text-emerald-600 hover:bg-emerald-100">
                                            <i class="fas fa-file-excel"></i>
                                            {{ __('دانلود فایل') }}
                                        </a>
                                    @endif
                                </div>
                            </div>
                            @if($archive->closing_note)
                                <p class="mt-2 text-[11px] text-slate-500">{{ __('توضیح مدیر') }}: {{ $archive->closing_note }}</p>
                            @endif
                        </div>
                    @endforeach
                </div>
                @if(method_exists($branchArchives, 'links'))
                    <div class="mt-3 border-t border-slate-100 pt-3 text-xs">
                        {{ $branchArchives->links() }}
                    </div>
                @endif
            </div>
        @endif

        @if($showAllCards)
            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex items-center justify-between gap-4">
                    <div>
                        <h2 class="text-base font-semibold text-slate-700">{{ __('فهرست تراکنش‌ها در همه شعب') }}</h2>
                        <p class="text-xs text-slate-500">{{ __('حداکثر ۵۰ تراکنش اخیر نمایش داده می‌شود. برای بررسی دقیق، شعبه مربوطه را انتخاب کنید.') }}</p>
                    </div>
                    <span class="text-xs font-medium text-slate-500">{{ __('تعداد رکوردها: :count', ['count' => $showAllTransactions->count()]) }}</span>
                </div>

                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm">
                        <thead class="bg-slate-50 text-xs uppercase text-slate-500">
                            <tr>
                                <th class="px-4 py-3 text-right">{{ __('شعبه') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('نوع تراکنش') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('مقدار') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('وضعیت') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('تاریخ ثبت') }}</th>
                                <th class="px-4 py-3 text-right">{{ __('شناسه') }}</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($showAllTransactions as $transaction)
                                @php
                                    $status = $statusStyles[$transaction->status] ?? $statusStyles['draft'];
                                    $typeLabel = $typeLabels[$transaction->type] ?? $transaction->type;
                                    $branchName = $transaction->ledger->branch_name ?? __('نامشخص');
                                    $dateLabel = $transaction->transaction_date ? verta($transaction->transaction_date)->format('Y/m/d H:i') : __('ثبت نشده');
                                    $palette = $branchPaletteMap[$transaction->ledger_id] ?? [
                                        'border' => 'border-slate-200',
                                        'badge_bg' => 'bg-slate-100',
                                        'badge_text' => 'text-slate-600',
                                        'progress' => 'bg-slate-400',
                                        'row_bg' => $loop->odd ? 'bg-white' : 'bg-slate-50',
                                        'dot' => 'bg-slate-400',
                                    ];
                                @endphp
                                <tr class="transition-colors border-r-4 {{ $palette['border'] ?? 'border-slate-200' }} {{ $palette['row_bg'] ?? ($loop->odd ? 'bg-white' : 'bg-slate-50') }} hover:bg-white">
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <div class="flex items-center justify-end gap-2">
                                            <span class="inline-flex items-center gap-2 rounded-full px-3 py-0.5 text-xs font-semibold {{ $palette['badge_bg'] ?? 'bg-slate-100' }} {{ $palette['badge_text'] ?? 'text-slate-600' }}">
                                                <span class="h-2 w-2 rounded-full {{ $palette['dot'] ?? 'bg-slate-400' }}"></span>
                                                {{ $branchName }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-600">{{ $typeLabel }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 font-semibold text-slate-800">{{ number_format($transaction->amount) }} {{ __('ریال') }}</td>
                                    <td class="whitespace-nowrap px-4 py-3">
                                        <span class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-semibold {{ $status['badge'] }}">
                                            {{ $status['label'] }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-500">{{ $dateLabel }}</td>
                                    <td class="whitespace-nowrap px-4 py-3 text-slate-400">#{{ $transaction->id }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-4 py-6 text-center text-sm text-slate-500">
                                        {{ __('تراکنش ثبت شده‌ای برای نمایش وجود ندارد.') }}
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        @endif

        @if($selectedLedger)
            @php
                $metrics = $selectedLedgerMetrics ?? [];
                $pendingCount = $metrics['pending_transactions_count'] ?? 0;
                $pendingCharges = $metrics['pending_charges_total'] ?? 0;
                $pendingExpenses = $metrics['pending_expenses_total'] ?? 0;
            @endphp

            {{-- Alerts Panel --}}
            @if($isAdminUser || auth()->user()->hasRole(['Admin', 'Superadmin']))
                <div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="text-base font-semibold text-slate-800">{{ __('هشدارها و اعلان‌ها') }}</h3>
                            <p class="text-xs text-slate-500">{{ __('موارد نیازمند توجه شما') }}</p>
                        </div>
                        <button 
                            wire:click="$refresh" 
                            class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                            title="{{ __('بروزرسانی') }}"
                        >
                            <iconify-icon icon="lucide:refresh-cw" class="text-lg"></iconify-icon>
                        </button>
                    </div>
                    @livewire('petty-cash.alerts-panel', ['ledger' => $selectedLedger], key('alerts-'.$selectedLedger->id))
                </div>
            @endif

            {{-- Enhanced Financial Dashboard V2.0 --}}
            @if($selectedLedger)
                @livewire('petty-cash.enhanced-dashboard', ['ledger' => $selectedLedger], key('enhanced-dashboard-'.$selectedLedger->id))
            @endif

            {{-- Old Simple Cards (Keep for comparison/fallback) --}}
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('سقف مجاز شعبه') }}</p>
                    <p class="mt-1 text-lg font-semibold text-slate-800">{{ number_format($selectedLedger->limit_amount) }} {{ __('ریال') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('مانده تایید شده') }}</p>
                    <p class="mt-1 text-lg font-semibold text-emerald-600">{{ number_format($selectedLedger->current_balance) }} {{ __('ریال') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('تراکنش‌های در انتظار تایید') }}</p>
                    <p class="mt-1 text-lg font-semibold text-amber-600">{{ $pendingCount }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('جمع مبالغ در انتظار') }}</p>
                    <p class="mt-1 text-lg font-semibold text-slate-800">{{ number_format(($pendingCharges ?? 0) - ($pendingExpenses ?? 0)) }} {{ __('ریال') }}</p>
                </div>
            </div>

            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                    <div>
                        <p class="text-xs text-slate-500">{{ __('مسئول تنخواه این شعبه') }}</p>
                        <p class="mt-1 text-base font-semibold text-slate-800">
                            {{ $selectedLedger->assignedUser ? $formatUserName($selectedLedger->assignedUser) : __('مسئول انتخاب نشده') }}
                        </p>
                        @if($selectedLedger->assignedUser && $selectedLedger->assignedUser->email)
                            <p class="text-xs text-slate-500">{{ $selectedLedger->assignedUser->email }}</p>
                        @endif
                    </div>
                    @if(! $selectedLedger->assignedUser)
                        <span class="inline-flex items-center gap-2 rounded-md border border-amber-300 bg-amber-50 px-3 py-2 text-xs font-semibold text-amber-700">
                            <i class="fas fa-exclamation-triangle text-amber-500"></i>
                            {{ __('مسئول انتخاب نشده · لطفاً از بخش ویرایش شعبه مسئول را تعیین کنید.') }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('صاحب حساب') }}</p>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $selectedLedger->account_holder ?? __('تعریف نشده') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('شماره حساب') }}</p>
                    <p class="mt-1 text-sm font-mono text-slate-800">{{ $selectedLedger->account_number ?? __('تعریف نشده') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('شماره شبا') }}</p>
                    <p class="mt-1 text-sm font-mono text-slate-800">{{ $selectedLedger->iban ?? __('تعریف نشده') }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                    <p class="text-xs text-slate-500">{{ __('شماره کارت') }}</p>
                    <p class="mt-1 text-sm font-mono text-slate-800">{{ $selectedLedger->card_number ?? __('تعریف نشده') }}</p>
                </div>
            </div>
            @if($analytics)
                @php
                    $analyticsSummary = $analytics['summary'] ?? [];
                    $analyticsCategories = collect($analytics['category_breakdown'] ?? []);
                    $analyticsVendors = collect($analytics['vendor_breakdown'] ?? []);
                    $analyticsTrend = collect($analytics['daily_trend'] ?? []);
                    $analyticsRecent = collect($analytics['recent_transactions'] ?? []);
                @endphp

                <div class="mt-6 space-y-6 rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
                    <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                        <div>
                            <h2 class="text-base font-semibold text-slate-700">{{ __('تحلیل هزینه‌های تنخواه') }}</h2>
                            <p class="text-xs text-slate-500">
                                {{ __('بازه گزارش: :label', ['label' => $analytics['period']['label'] ?? __('نامشخص')]) }}
                                @if(!empty($analytics['period']['from']) || !empty($analytics['period']['to']))
                                    <span class="mx-1 text-slate-400">·</span>
                                    {{ ($analytics['period']['from'] ?? '---') }} {{ __('تا') }} {{ ($analytics['period']['to'] ?? '---') }}
                                @endif
                            </p>
                        </div>

                        <form method="get" class="flex flex-col gap-2 md:flex-row md:items-center md:gap-3" x-data="{ period: '{{ $analyticsFilters['period'] ?? 'last_30' }}' }">
                            <input type="hidden" name="ledger" value="{{ $selectedLedger->id }}">
                            @if($showAllCards)
                                <input type="hidden" name="show_all" value="1">
                            @endif
                            <select name="analytics_period" x-model="period" class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                @foreach($analyticsPeriodOptions as $periodKey => $periodLabel)
                                    <option value="{{ $periodKey }}" @selected(($analyticsFilters['period'] ?? 'last_30') === $periodKey)>{{ $periodLabel }}</option>
                                @endforeach
                            </select>
                            <div class="flex flex-col gap-2 md:flex-row md:items-center" x-show="period === 'custom'" x-cloak>
                                <input
                                    type="text"
                                    name="analytics_from"
                                    value="{{ $analyticsFilters['from'] ?? '' }}"
                                    class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="{{ __('از تاریخ (مثال: 1404/01/01)') }}"
                                    x-data
                                    x-init="window.initJalaliDatepicker && window.initJalaliDatepicker($el, { enableTime: false })"
                                />
                                <input
                                    type="text"
                                    name="analytics_to"
                                    value="{{ $analyticsFilters['to'] ?? '' }}"
                                    class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="{{ __('تا تاریخ (مثال: 1404/01/30)') }}"
                                    x-data
                                    x-init="window.initJalaliDatepicker && window.initJalaliDatepicker($el, { enableTime: false })"
                                />
                            </div>
                            <button type="submit" class="inline-flex items-center justify-center rounded-md bg-indigo-600 px-3 py-2 text-xs font-semibold text-white shadow hover:bg-indigo-500 focus:outline-none focus:ring-2 focus:ring-indigo-400 focus:ring-offset-1">
                                <i class="fas fa-sync ml-1"></i>
                                {{ __('اعمال بازه') }}
                            </button>
                        </form>
                    </div>

                    @if(($analyticsSummary['transaction_count'] ?? 0) === 0)
                        <div class="rounded-md border border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-600">
                            {{ __('در این بازه تراکنش تایید شده‌ای برای تحلیل وجود ندارد.') }}
                        </div>
                    @else
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4">
                            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                <p class="text-xs text-slate-500">{{ __('جمع هزینه‌های تایید شده') }}</p>
                                <p class="mt-1 text-lg font-semibold text-slate-800">{{ number_format($analyticsSummary['total_expense'] ?? 0) }} {{ __('ریال') }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                <p class="text-xs text-slate-500">{{ __('تعداد تراکنش‌ها') }}</p>
                                <p class="mt-1 text-lg font-semibold text-slate-800">{{ $analyticsSummary['transaction_count'] ?? 0 }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                <p class="text-xs text-slate-500">{{ __('میانگین هر تراکنش') }}</p>
                                <p class="mt-1 text-lg font-semibold text-slate-800">{{ number_format((int) round($analyticsSummary['average_amount'] ?? 0)) }} {{ __('ریال') }}</p>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 shadow-sm">
                                <p class="text-xs text-slate-500">{{ __('میانگین هزینه روزانه') }}</p>
                                <p class="mt-1 text-lg font-semibold text-slate-800">{{ number_format((int) round($analyticsSummary['average_per_day'] ?? 0)) }} {{ __('ریال') }}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-slate-700">{{ __('سهم دسته‌بندی‌ها') }}</h3>
                                    <span class="text-xs text-slate-500">{{ __('بر اساس مبلغ هزینه شده') }}</span>
                                </div>
                                <div id="petty-category-chart" class="mt-4 h-64"></div>
                                <ul class="mt-4 space-y-2 text-xs text-slate-600">
                                    @foreach($analyticsCategories->take(5) as $category)
                                        <li class="flex items-center justify-between">
                                            <span>{{ $category['label'] }}</span>
                                            <span class="font-semibold">{{ number_format((int) ($category['total_amount'] ?? 0)) }} {{ __('ریال') }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 shadow-sm">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-semibold text-slate-700">{{ __('روند هزینه‌ها') }}</h3>
                                    <span class="text-xs text-slate-500">{{ __('طی بازه انتخابی') }}</span>
                                </div>
                                <div id="petty-trend-chart" class="mt-4 h-64"></div>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                <h3 class="text-sm font-semibold text-slate-700">{{ __('پراکندگی فروشندگان') }}</h3>
                                <table class="mt-3 w-full table-fixed divide-y divide-slate-200 text-xs">
                                    <thead class="bg-slate-50 text-slate-500">
                                        <tr>
                                            <th class="px-3 py-2 text-right">{{ __('فروشنده') }}</th>
                                            <th class="px-3 py-2 text-right">{{ __('تعداد') }}</th>
                                            <th class="px-3 py-2 text-right">{{ __('جمع مبلغ') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-slate-100 text-slate-600">
                                        @forelse($analyticsVendors as $vendor)
                                            <tr>
                                                <td class="px-3 py-2">{{ $vendor['vendor_name'] ?? __('نامشخص') }}</td>
                                                <td class="px-3 py-2">{{ $vendor['transactions_count'] ?? 0 }}</td>
                                                <td class="px-3 py-2 font-semibold">{{ number_format((int) ($vendor['total_amount'] ?? 0)) }} {{ __('ریال') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="px-3 py-4 text-center text-slate-500">{{ __('اطلاعات فروشنده‌ای ثبت نشده است.') }}</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="rounded-lg border border-slate-200 bg-white p-4 shadow-sm">
                                <h3 class="text-sm font-semibold text-slate-700">{{ __('جدیدترین هزینه‌ها') }}</h3>
                                <ul class="mt-3 space-y-3 text-xs text-slate-600">
                                    @forelse($analyticsRecent as $recent)
                                        <li class="rounded-md border border-slate-200 bg-slate-50 px-3 py-2">
                                            <div class="flex items-center justify-between">
                                                <span class="font-semibold text-slate-700">{{ number_format((int) ($recent['amount'] ?? 0)) }} {{ __('ریال') }}</span>
                                                <span class="text-slate-400">{{ $recent['date_fa'] ?? ($recent['date'] ?? '') }}</span>
                                            </div>
                                            <div class="mt-1 text-slate-700">{{ $recent['description'] ?? __('بدون شرح') }}</div>
                                            <div class="mt-1 flex flex-wrap items-center gap-2 text-slate-500">
                                                <span class="inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-[11px] font-semibold text-indigo-600">
                                                    {{ $recent['category_label'] ?? __('نامشخص') }}
                                                </span>
                                                @if(!empty($recent['vendor_name']))
                                                    <span class="text-[11px] text-slate-500">{{ __('فروشنده: :vendor', ['vendor' => $recent['vendor_name']]) }}</span>
                                                @endif
                                                @if(!empty($recent['reference_number']))
                                                    <span class="text-[11px] text-slate-400">{{ __('شناسه: :ref', ['ref' => $recent['reference_number']]) }}</span>
                                                @endif
                                            </div>
                                        </li>
                                    @empty
                                        <li class="rounded-md border border-slate-200 bg-slate-50 px-3 py-4 text-center text-slate-500">
                                            {{ __('هنوز هزینه‌ای در این بازه ثبت نشده است.') }}
                                        </li>
                                    @endforelse
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
        @endif


        @if(! $selectedLedger && ! $showAllCards)
            <div class="rounded-lg border border-dashed border-slate-300 bg-white p-8 text-center text-sm text-slate-600">
                {{ __('هنوز شعبه‌ای به حساب کاربری شما متصل نشده است. برای دریافت دسترسی با مدیر سیستم تماس بگیرید.') }}
            </div>
        @endif
    </div>
    
    @livewire('admin.branch-users-manager')

@if($analytics && ($analytics['summary']['transaction_count'] ?? 0) > 0)
    @push('scripts')
        <script>
            (function () {
                const analyticsData = @json($analytics);

                const formatCurrency = (value) => {
                    return Math.round(value || 0).toLocaleString('fa-IR');
                };

                const renderCharts = () => {
                    const categoryEl = document.getElementById('petty-category-chart');
                    if (categoryEl) {
                        const categories = (analyticsData.category_breakdown || []).map(item => item.label || item.key);
                        const categorySeries = (analyticsData.category_breakdown || []).map(item => Math.round(item.total_amount || 0));

                        if (!categorySeries.length || categorySeries.every(value => value === 0)) {
                            categoryEl.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-slate-500">{{ __('داده‌ای برای نمایش وجود ندارد.') }}</div>';
                        } else {
                            const categoryChart = new window.ApexCharts(categoryEl, {
                                chart: {
                                    type: 'donut',
                                    height: 260,
                                },
                                labels: categories,
                                series: categorySeries,
                                dataLabels: {
                                    enabled: true,
                                    formatter: function (_, opts) {
                                        const rawValue = categorySeries[opts.seriesIndex] || 0;
                                        return formatCurrency(rawValue) + ' {{ __('ریال') }}';
                                    },
                                },
                                tooltip: {
                                    y: {
                                        formatter: function (value) {
                                            return formatCurrency(value) + ' {{ __('ریال') }}';
                                        },
                                    },
                                },
                                legend: {
                                    position: 'bottom',
                                    fontFamily: 'inherit',
                                },
                                colors: ['#6366f1', '#f97316', '#10b981', '#f59e0b', '#ec4899', '#14b8a6', '#0ea5e9'],
                            });

                            categoryChart.render();
                        }
                    }

                    const trendEl = document.getElementById('petty-trend-chart');
                    if (trendEl) {
                        const trendSeries = (analyticsData.daily_trend || []).map(item => Math.round(item.total_amount || 0));
                        const trendLabels = (analyticsData.daily_trend || []).map(item => item.date_fa || item.date || '');

                        if (!trendSeries.length || trendSeries.every(value => value === 0)) {
                            trendEl.innerHTML = '<div class="flex h-full items-center justify-center text-xs text-slate-500">{{ __('داده‌ای برای نمایش وجود ندارد.') }}</div>';
                        } else {
                            const trendChart = new window.ApexCharts(trendEl, {
                                chart: {
                                    type: 'area',
                                    height: 260,
                                    toolbar: { show: false },
                                },
                                dataLabels: { enabled: false },
                                stroke: {
                                    curve: 'smooth',
                                    width: 2,
                                    colors: ['#6366f1'],
                                },
                                fill: {
                                    type: 'gradient',
                                    gradient: {
                                        shadeIntensity: 0.4,
                                        opacityFrom: 0.6,
                                        opacityTo: 0.05,
                                    },
                                },
                                series: [{
                                    name: '{{ __('هزینه') }}',
                                    data: trendSeries,
                                }],
                                xaxis: {
                                    categories: trendLabels,
                                    labels: {
                                        rotate: -45,
                                        style: {
                                            fontFamily: 'inherit',
                                        },
                                    },
                                },
                                yaxis: {
                                    labels: {
                                        formatter: function (value) {
                                            return formatCurrency(value);
                                        },
                                    },
                                },
                                tooltip: {
                                    y: {
                                        formatter: function (value) {
                                            return formatCurrency(value) + ' {{ __('ریال') }}';
                                        },
                                    },
                                },
                            });

                            trendChart.render();
                        }
                    }
                };

                const ensureApex = () => {
                    if (typeof window.ApexCharts !== 'undefined') {
                        renderCharts();
                        return;
                    }

                    const script = document.createElement('script');
                    script.src = 'https://cdn.jsdelivr.net/npm/apexcharts';
                    script.defer = true;
                    script.onload = renderCharts;
                    document.head.appendChild(script);
                };

                ensureApex();
            })();
        </script>
    @endpush
@endif
</x-layouts.backend-layout>
