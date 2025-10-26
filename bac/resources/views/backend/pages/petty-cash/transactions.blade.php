@php
    $isAdminUser = isset($isAdminUser) ? (bool) $isAdminUser : (bool) (auth()->user()?->hasRole(['Superadmin', 'Admin']) ?? false);
    $pendingChargeRequests = isset($pendingChargeRequests) ? collect($pendingChargeRequests) : collect();
    $statusOptions = [
        'draft' => __('پیش‌نویس'),
        'submitted' => __('ارسال‌شده'),
        'approved' => __('تایید‌شده'),
        'rejected' => __('رد‌شده'),
        'needs_changes' => __('نیاز به اصلاح'),
        'under_review' => __('در حال بررسی'),
    ];
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
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div class="flex-1">
                    <h1 class="text-xl font-semibold text-slate-800">
                        <i class="fas fa-plus-circle ml-2 text-indigo-600"></i>
                        {{ __('ثبت تراکنش‌های تنخواه') }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{ __('هزینه‌ها، شارژها یا تعدیلات را با فرم زیر ثبت و مدیریت کنید. پس از ذخیره، تراکنش در داشبورد تنخواه قابل مشاهده خواهد بود.') }}
                    </p>
                    @php
                        $custodianName = $formatUserName($ledger->assignedUser ?? null);
                        $custodianTextClass = $ledger->assignedUser ? 'text-slate-500' : 'text-amber-600';
                        $custodianIconClass = $ledger->assignedUser ? 'text-slate-400' : 'text-amber-500';
                    @endphp
                    <div class="mt-2 flex items-center gap-2 text-xs {{ $custodianTextClass }}">
                        <i class="fas fa-user-circle {{ $custodianIconClass }}"></i>
                        <span>
                            {{ $ledger->assignedUser
                                ? __('مسئول تنخواه: :name', ['name' => $custodianName])
                                : __('مسئول تنخواه تعیین نشده است. لطفاً با مدیریت برای ثبت مسئول هماهنگ کنید.') }}
                        </span>
                    </div>
                </div>
                
                @if($isAdminUser && $availableLedgers->count() > 1)
                    <div class="flex items-center gap-3">
                        <label for="branch-select" class="text-sm font-medium text-slate-700">{{ __('انتخاب شعبه:') }}</label>
                        <select id="branch-select" 
                                onchange="changeBranch(this.value)"
                                class="rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($availableLedgers as $availableLedger)
                                <option value="{{ $availableLedger->id }}" 
                                        {{ $availableLedger->id == $ledger->id ? 'selected' : '' }}>
                                    {{ $availableLedger->branch_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                @endif
                
                <a href="{{ route('admin.petty-cash.index', ['ledger' => $ledger->id]) }}"
                   class="inline-flex items-center gap-2 rounded-md border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 shadow-sm hover:bg-slate-50">
                    <i class="fas fa-arrow-right"></i>
                    {{ __('بازگشت به داشبورد تنخواه') }}
                </a>
            </div>
        </div>


        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            @livewire('petty-cash.transaction-form', ['ledger' => $ledger], key('transaction-form-page-'.$ledger->id))
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <h2 class="text-base font-semibold text-slate-700">{{ __('آخرین تراکنش‌های شعبه') }}</h2>
            <p class="mt-1 text-xs text-slate-500">{{ __('مروری سریع بر جدیدترین تراکنش‌های ثبت شده برای این شعبه.') }}</p>

            <div class="mt-4">
                @livewire('petty-cash.transactions-table', ['ledger' => $ledger], key('transactions-table-page-'.$ledger->id))
            </div>
        </div>
    </div>
</x-layouts.backend-layout>

<script>
function changeBranch(ledgerId) {
    // Store the selected branch in session storage to maintain it after operations
    sessionStorage.setItem('selectedBranch', ledgerId);
    
    // Redirect to the same page with the new ledger
    window.location.href = '{{ route("admin.petty-cash.transactions", ":ledger") }}'.replace(':ledger', ledgerId);
}

// Restore selected branch from session storage on page load
document.addEventListener('DOMContentLoaded', function() {
    const selectedBranch = sessionStorage.getItem('selectedBranch');
    if (selectedBranch) {
        const branchSelect = document.getElementById('branch-select');
        if (branchSelect && branchSelect.value !== selectedBranch) {
            branchSelect.value = selectedBranch;
        }
    }
});

// Listen for Livewire events to store branch selection
document.addEventListener('livewire:init', () => {
    Livewire.on('store-selected-branch', (event) => {
        sessionStorage.setItem('selectedBranch', event.branchId);
    });
});
</script>
