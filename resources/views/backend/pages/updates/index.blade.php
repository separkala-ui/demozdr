<x-layouts.backend-layout :breadcrumbs="[
    'title' => 'به‌روزرسانی سیستم',
    'show_home' => false,
    'show_current' => true,
]">
    <div class="space-y-6">
        @livewire('dashboard.system-update-status')
    </div>
</x-layouts.backend-layout>
