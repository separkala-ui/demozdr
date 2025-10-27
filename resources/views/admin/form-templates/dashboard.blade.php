@extends('backend.layouts.app')

@section('admin-content')
<div class="w-full px-4 md:px-6 lg:px-8 py-6">
    <!-- Header with Back Button -->
    <div class="mb-6 flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-dark dark:text-white">
                📊 داشبورد مدیریت فرم‌ها
            </h1>
            <p class="mt-2 text-sm text-dark-5 dark:text-dark-6">
                آمار و گزارشات سیستم فرم‌ساز عملیاتی
            </p>
        </div>
        <a 
            href="{{ route('admin.dashboard') }}"
            class="inline-flex items-center gap-2 rounded-lg bg-gray-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 transition-all duration-200">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            <span>بازگشت به پنل اصلی</span>
        </a>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-4 mb-6">
        <!-- Total Templates Card -->
        <div class="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-dark-5 dark:text-dark-6">
                            کل الگوهای فرم
                        </p>
                        <h3 class="mt-2 text-3xl font-bold text-dark dark:text-white">
                            {{ $totalTemplates }}
                        </h3>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20">
                        <svg class="h-7 w-7 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-800 dark:bg-green-900/20 dark:text-green-400">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        {{ $activeTemplates }} فعال
                    </span>
                </div>
            </div>
        </div>

        <!-- Total Reports Card -->
        <div class="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-dark-5 dark:text-dark-6">
                            کل گزارش‌ها
                        </p>
                        <h3 class="mt-2 text-3xl font-bold text-dark dark:text-white">
                            {{ $totalReports }}
                        </h3>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                        <svg class="h-7 w-7 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1 rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800 dark:bg-blue-900/20 dark:text-blue-400">
                        <svg class="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                        </svg>
                        امروز: {{ $todayReports }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Categories Card -->
        <div class="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-dark-5 dark:text-dark-6">
                            دسته‌بندی‌ها
                        </p>
                        <h3 class="mt-2 text-3xl font-bold text-dark dark:text-white">
                            4
                        </h3>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/20">
                        <svg class="h-7 w-7 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-1 text-xs text-dark-5 dark:text-dark-6">
                    <span>🔍 کنترل کیفیت</span>
                    <span>•</span>
                    <span>🔎 بازرسی</span>
                    <span>•</span>
                    <span>🏭 تولید</span>
                </div>
            </div>
        </div>

        <!-- Fields Card -->
        <div class="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="text-sm font-medium text-dark-5 dark:text-dark-6">
                            کل فیلدها
                        </p>
                        <h3 class="mt-2 text-3xl font-bold text-dark dark:text-white">
                            {{ $totalFields }}
                        </h3>
                    </div>
                    <div class="flex h-14 w-14 items-center justify-center rounded-full bg-orange-100 dark:bg-orange-900/20">
                        <svg class="h-7 w-7 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </div>
                </div>
                <div class="mt-4 flex items-center gap-2">
                    <span class="text-xs text-dark-5 dark:text-dark-6">
                        میانگین: {{ $avgFieldsPerTemplate }} فیلد در هر فرم
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2 mb-6">
        <!-- Templates by Category -->
        <div class="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
            <div class="border-b border-stroke px-7 py-4 dark:border-dark-3">
                <h3 class="text-lg font-semibold text-dark dark:text-white">
                    📊 توزیع الگوها بر اساس دسته‌بندی
                </h3>
            </div>
            <div class="p-7">
                <div class="space-y-4">
                    @foreach($templatesByCategory as $category)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-dark dark:text-white">
                                {{ $category['label'] }}
                            </span>
                            <span class="text-sm font-bold text-primary">
                                {{ $category['count'] }}
                            </span>
                        </div>
                        <div class="h-2 w-full rounded-full bg-gray-200 dark:bg-dark-3">
                            <div 
                                class="h-2 rounded-full {{ $category['color'] }}" 
                                style="width: {{ $category['percentage'] }}%">
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Recent Activity -->
        <div class="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
            <div class="border-b border-stroke px-7 py-4 dark:border-dark-3">
                <h3 class="text-lg font-semibold text-dark dark:text-white">
                    🕐 فعالیت‌های اخیر
                </h3>
            </div>
            <div class="p-7">
                <div class="space-y-4">
                    @foreach($recentTemplates as $template)
                    <div class="flex items-center gap-4 rounded-lg border border-stroke p-3 dark:border-dark-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20">
                            <svg class="h-5 w-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h4 class="text-sm font-semibold text-dark dark:text-white">
                                {{ $template->title }}
                            </h4>
                            <p class="text-xs text-dark-5 dark:text-dark-6">
                                توسط {{ $template->creator->full_name ?? 'نامشخص' }} • {{ $template->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <a 
                            href="{{ route('admin.form-templates.index') }}"
                            class="inline-flex items-center justify-center rounded-lg bg-blue-600 p-2 text-white hover:bg-blue-700">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                        </a>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
        <div class="border-b border-stroke px-7 py-4 dark:border-dark-3">
            <h3 class="text-lg font-semibold text-dark dark:text-white">
                ⚡ دسترسی سریع
            </h3>
        </div>
        <div class="p-7">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                <a 
                    href="{{ route('admin.form-templates.index') }}"
                    class="flex items-center gap-4 rounded-lg border-2 border-dashed border-stroke p-6 transition-all hover:border-primary hover:bg-primary/5 dark:border-dark-3 dark:hover:border-primary">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/20">
                        <svg class="h-6 w-6 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-dark dark:text-white">مدیریت الگوها</h4>
                        <p class="text-sm text-dark-5 dark:text-dark-6">ایجاد و ویرایش فرم‌ها</p>
                    </div>
                </a>

                <a 
                    href="/filament/form-reports"
                    class="flex items-center gap-4 rounded-lg border-2 border-dashed border-stroke p-6 transition-all hover:border-primary hover:bg-primary/5 dark:border-dark-3 dark:hover:border-primary">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/20">
                        <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-dark dark:text-white">گزارش‌ها</h4>
                        <p class="text-sm text-dark-5 dark:text-dark-6">مشاهده گزارش‌های ثبت شده</p>
                    </div>
                </a>

                <a 
                    href="{{ route('admin.settings') }}"
                    class="flex items-center gap-4 rounded-lg border-2 border-dashed border-stroke p-6 transition-all hover:border-primary hover:bg-primary/5 dark:border-dark-3 dark:hover:border-primary">
                    <div class="flex h-12 w-12 items-center justify-center rounded-full bg-purple-100 dark:bg-purple-900/20">
                        <svg class="h-6 w-6 text-purple-600 dark:text-purple-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-dark dark:text-white">تنظیمات</h4>
                        <p class="text-sm text-dark-5 dark:text-dark-6">پیکربندی سیستم</p>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

