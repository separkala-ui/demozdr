<!DOCTYPE html>
<html lang="fa" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>هورایزن - داشبورد مدیریت صف</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 dark:bg-gray-900">
    <!-- Navigation Bar -->
    <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700 px-4 py-3 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto flex items-center justify-between">
            <div class="flex items-center gap-4">
                <span class="text-sm font-medium text-gray-700 dark:text-gray-300">پنل‌های موجود:</span>
                
                <!-- Admin Dashboard -->
                <a href="{{ route('admin.dashboard') }}" 
                   class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
                    </svg>
                    داشبورد اصلی
                </a>

                <!-- Filament Dashboard -->
                <a href="/filament/form-templates" 
                   class="inline-flex items-center gap-2 px-3 py-1.5 text-sm rounded-md text-gray-600 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    فرم‌ساز
                </a>

                <!-- Horizon (Current) -->
                <div class="inline-flex items-center gap-2 px-3 py-1.5 text-sm bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-md font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Horizon (فعلی)
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            </div>

            <div class="text-sm text-gray-500 dark:text-gray-400">
                کاربر: <span class="font-semibold">{{ auth()->user()->full_name }}</span>
            </div>
        </div>
    </div>

    <div class="min-h-screen flex items-center justify-center p-4">
        <div class="max-w-4xl w-full">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="inline-flex items-center gap-3 mb-4">
                    <svg class="w-16 h-16 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    <h1 class="text-4xl font-bold text-gray-900 dark:text-white">Laravel Horizon</h1>
                </div>
                <p class="text-xl text-gray-600 dark:text-gray-400">داشبورد قدرتمند مدیریت صف و Job Queue</p>
            </div>

            <!-- Cards -->
            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <!-- Dashboard Card -->
                <a href="/horizon/dashboard" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:shadow-xl transition-all border-2 border-transparent hover:border-purple-500">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-purple-100 dark:bg-purple-900/30 rounded-lg">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">داشبورد اصلی</h3>
                            <p class="text-gray-600 dark:text-gray-400">نمای کلی و آمار Job ها</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">مشاهده تعداد Job های در حال اجرا، تکمیل شده و ناموفق</p>
                </a>

                <!-- Monitoring Card -->
                <a href="/horizon/monitoring" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:shadow-xl transition-all border-2 border-transparent hover:border-blue-500">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-blue-100 dark:bg-blue-900/30 rounded-lg">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">نظارت زنده</h3>
                            <p class="text-gray-600 dark:text-gray-400">پایش لحظه‌ای صف‌ها</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">نظارت بر عملکرد Worker ها و زمان اجرا</p>
                </a>

                <!-- Failed Jobs Card -->
                <a href="/horizon/failed" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:shadow-xl transition-all border-2 border-transparent hover:border-red-500">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-red-100 dark:bg-red-900/30 rounded-lg">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">کارهای ناموفق</h3>
                            <p class="text-gray-600 dark:text-gray-400">مدیریت خطاها</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">مشاهده و تلاش مجدد Job های ناموفق</p>
                </a>

                <!-- Metrics Card -->
                <a href="/horizon/metrics" class="block p-6 bg-white dark:bg-gray-800 rounded-lg shadow-lg hover:shadow-xl transition-all border-2 border-transparent hover:border-green-500">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="p-3 bg-green-100 dark:bg-green-900/30 rounded-lg">
                            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white">معیارها و نمودارها</h3>
                            <p class="text-gray-600 dark:text-gray-400">آمار تفصیلی</p>
                        </div>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">نمودارهای عملکرد و آمار دقیق</p>
                </a>
            </div>

            <!-- Info Box -->
            <div class="bg-blue-50 dark:bg-blue-900/20 border-l-4 border-blue-500 p-6 rounded-lg mb-8">
                <div class="flex items-start gap-4">
                    <svg class="w-6 h-6 text-blue-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <div>
                        <h4 class="font-bold text-blue-900 dark:text-blue-100 mb-2">Horizon چیست؟</h4>
                        <p class="text-blue-800 dark:text-blue-200 text-sm leading-relaxed">
                            Laravel Horizon یک داشبورد قدرتمند برای نظارت و مدیریت Job Queue های لاراول است. 
                            با این ابزار می‌توانید به صورت Real-time عملکرد صف‌های خود را رصد کرده، 
                            Job های ناموفق را شناسایی و مجدداً اجرا کنید، و از معیارهای دقیق برای بهینه‌سازی استفاده کنید.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Back Button -->
            <div class="text-center">
                <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gray-200 dark:bg-gray-700 text-gray-800 dark:text-gray-200 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    <span>بازگشت به داشبورد اصلی</span>
                </a>
            </div>
        </div>
    </div>
</body>
</html>

