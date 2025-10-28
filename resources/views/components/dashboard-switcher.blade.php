@php
    $user = auth()->user();
    $isSuperAdmin = $user && $user->hasRole('Superadmin');
    $hasFormAccess = $user && $user->can('form.view');
    $currentPath = request()->path();
@endphp

<div class="relative dashboard-switcher-wrapper">
    <!-- Dashboard Switcher Button -->
    <button 
        type="button"
        onclick="toggleDashboardMenu()"
        class="flex items-center gap-2 px-3 py-2 text-sm font-medium rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors"
        style="color: inherit;"
    >
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"></path>
        </svg>
        <span class="hidden md:inline">داشبوردها</span>
        <svg class="w-4 h-4 transition-transform dashboard-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
        </svg>
    </button>

    <!-- Dropdown Menu -->
    <div 
        id="dashboard-menu"
        class="hidden absolute left-0 mt-2 w-72 bg-white dark:bg-gray-800 rounded-lg shadow-xl border border-gray-200 dark:border-gray-700 overflow-hidden z-50"
    >
        <div class="p-2">
            <div class="px-3 py-2 text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase border-b border-gray-200 dark:border-gray-700 mb-2">
                جابجایی بین پنل‌ها
            </div>

            <!-- Admin Dashboard -->
            <a href="{{ route('admin.dashboard') }}" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors {{ str_starts_with($currentPath, 'admin') && !str_contains($currentPath, 'horizon') && !str_contains($currentPath, 'filament') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                </svg>
                <div class="flex-1 min-w-0">
                    <div class="font-medium truncate">داشبورد اصلی</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">پنل مدیریت کلی</div>
                </div>
                @if(str_starts_with($currentPath, 'admin') && !str_contains($currentPath, 'horizon') && !str_contains($currentPath, 'filament'))
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                @endif
            </a>

            @if($hasFormAccess || $isSuperAdmin)
            <!-- Filament Forms Dashboard -->
            <a href="/filament/form-templates" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors {{ str_contains($currentPath, 'filament') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <div class="flex-1 min-w-0">
                    <div class="font-medium truncate">داشبورد فرم‌ساز</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">مدیریت فرم‌ها (Filament)</div>
                </div>
                @if(str_contains($currentPath, 'filament'))
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                @endif
            </a>
            @endif

            @if($isSuperAdmin)
            <!-- Horizon Dashboard -->
            <a href="/admin/horizon-welcome" 
               class="flex items-center gap-3 px-3 py-2.5 rounded-md hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors {{ str_contains($currentPath, 'horizon') ? 'bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 font-medium' : 'text-gray-700 dark:text-gray-300' }}">
                <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                </svg>
                <div class="flex-1 min-w-0">
                    <div class="font-medium truncate">داشبورد Horizon</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate">مدیریت Job Queue</div>
                </div>
                @if(str_contains($currentPath, 'horizon'))
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                @endif
            </a>
            @endif
        </div>

        <div class="border-t border-gray-200 dark:border-gray-700 p-2 bg-gray-50 dark:bg-gray-900">
            <div class="px-3 py-1.5 text-xs text-gray-500 dark:text-gray-400">
                <span class="font-medium">نقش فعلی:</span> 
                <span class="font-semibold text-gray-700 dark:text-gray-300">{{ $user->getRoleNames()->first() ?? 'کاربر' }}</span>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle dashboard menu
function toggleDashboardMenu() {
    const menu = document.getElementById('dashboard-menu');
    const arrow = document.querySelector('.dashboard-arrow');
    
    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        if (arrow) arrow.style.transform = 'rotate(180deg)';
    } else {
        menu.classList.add('hidden');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    }
}

// Close menu when clicking outside
document.addEventListener('click', function(event) {
    const wrapper = document.querySelector('.dashboard-switcher-wrapper');
    const menu = document.getElementById('dashboard-menu');
    
    if (wrapper && !wrapper.contains(event.target)) {
        menu?.classList.add('hidden');
        const arrow = document.querySelector('.dashboard-arrow');
        if (arrow) arrow.style.transform = 'rotate(0deg)';
    }
});
</script>

<style>
.dashboard-arrow {
    transition: transform 0.2s ease;
}
</style>
