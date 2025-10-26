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
        {{-- Modern Header with Branch Info --}}
        <div class="overflow-hidden rounded-lg border border-slate-200 bg-gradient-to-l from-slate-50 to-white shadow-sm">
            <div class="border-b border-slate-200 bg-white p-5">
                <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    {{-- Title & Branch Name --}}
                    <div class="flex items-center gap-4">
                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 shadow-md">
                            <iconify-icon icon="lucide:receipt" class="text-2xl text-white"></iconify-icon>
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-slate-800">{{ __('ثبت تراکنش‌های تنخواه') }}</h1>
                            <div class="mt-1 flex items-center gap-2">
                                <iconify-icon icon="lucide:building-2" class="text-sm text-indigo-500"></iconify-icon>
                                <span id="current-branch-name" class="text-sm font-semibold text-indigo-600">{{ $ledger->branch_name }}</span>
                            </div>
                        </div>
                    </div>

                    {{-- Branch Selector (Admin) --}}
                    @if($isAdminUser && $availableLedgers->count() > 1)
                        <div class="flex items-center gap-3">
                            <iconify-icon icon="lucide:git-branch" class="text-xl text-slate-400"></iconify-icon>
                            <select id="branch-select" 
                                    onchange="changeBranch(this.value)"
                                    class="rounded-lg border-slate-300 px-4 py-2.5 text-sm font-medium shadow-sm transition-all focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500">
                                @foreach($availableLedgers as $availableLedger)
                                    <option value="{{ $availableLedger->id }}" 
                                            {{ $availableLedger->id == $ledger->id ? 'selected' : '' }}
                                            data-branch-name="{{ $availableLedger->branch_name }}">
                                        {{ $availableLedger->branch_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    {{-- Back Button --}}
                    <a href="{{ route('admin.petty-cash.index', ['ledger' => $ledger->id]) }}"
                       class="group inline-flex items-center gap-2 rounded-lg border-2 border-slate-300 bg-white px-4 py-2.5 font-medium text-slate-700 shadow-sm transition-all hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700">
                        <iconify-icon icon="lucide:arrow-right" class="text-lg transition-transform group-hover:scale-110"></iconify-icon>
                        <span>{{ __('بازگشت به داشبورد') }}</span>
                    </a>
                </div>
            </div>

            {{-- Info Bar --}}
            <div class="bg-slate-50/50 px-5 py-3">
                <div class="flex flex-wrap items-center gap-4 text-xs">
                    <div class="flex items-center gap-2 text-slate-600">
                        <iconify-icon icon="lucide:info" class="text-sm text-indigo-500"></iconify-icon>
                        <span>{{ __('هزینه‌ها، شارژها یا تعدیلات را با فرم زیر ثبت و مدیریت کنید') }}</span>
                    </div>
                    @php
                        $custodianName = $formatUserName($ledger->assignedUser ?? null);
                    @endphp
                    <div class="flex items-center gap-2 {{ $ledger->assignedUser ? 'text-emerald-700' : 'text-amber-700' }}">
                        <iconify-icon icon="lucide:user-check" class="text-sm"></iconify-icon>
                        <span class="font-medium">
                            {{ $ledger->assignedUser
                                ? __('مسئول: :name', ['name' => $custodianName])
                                : __('مسئول تعیین نشده') }}
                        </span>
                    </div>
                </div>
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
    // Get selected option to extract branch name
    const select = document.getElementById('branch-select');
    const selectedOption = select.options[select.selectedIndex];
    const branchName = selectedOption.getAttribute('data-branch-name') || selectedOption.text;
    
    // Update branch name in header immediately
    const branchNameDisplay = document.getElementById('current-branch-name');
    if (branchNameDisplay) {
        branchNameDisplay.textContent = branchName;
    }
    
    // Store in session storage
    sessionStorage.setItem('selectedBranch', ledgerId);
    sessionStorage.setItem('selectedBranchName', branchName);
    
    // Redirect to the same page with the new ledger
    window.location.href = '{{ route("admin.petty-cash.transactions", ":ledger") }}'.replace(':ledger', ledgerId);
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    const branchSelect = document.getElementById('branch-select');
    const branchNameDisplay = document.getElementById('current-branch-name');
    
    // Update branch name from select on page load
    if (branchSelect && branchNameDisplay) {
        const selectedOption = branchSelect.options[branchSelect.selectedIndex];
        const branchName = selectedOption.getAttribute('data-branch-name') || selectedOption.text;
        branchNameDisplay.textContent = branchName;
    }
    
    // Restore selected branch from session storage
    const selectedBranch = sessionStorage.getItem('selectedBranch');
    if (selectedBranch && branchSelect) {
        if (branchSelect.value !== selectedBranch) {
            branchSelect.value = selectedBranch;
            // Update branch name after restoring
            const selectedOption = branchSelect.options[branchSelect.selectedIndex];
            const branchName = selectedOption.getAttribute('data-branch-name') || selectedOption.text;
            if (branchNameDisplay) {
                branchNameDisplay.textContent = branchName;
            }
        }
    }
});

// Listen for Livewire events
document.addEventListener('livewire:init', () => {
    Livewire.on('store-selected-branch', (event) => {
        sessionStorage.setItem('selectedBranch', event.branchId);
    });
});
</script>
