<x-layouts.backend-layout :breadcrumbs="[['title' => __('آپلود فاکتور برای دیباگ'), 'url' => route('admin.petty-cash.debug.upload')]]">
    <div class="space-y-6">
        @if (session('success'))
            <div class="rounded-md border border-green-300 bg-green-50 p-4 text-green-800">
                <p class="font-semibold">{{ session('success') }}</p>
                @if (session('uploaded_paths'))
                    <ul class="mt-2 list-disc space-y-1 pl-5 text-xs">
                        @foreach (session('uploaded_paths') as $path)
                            <li class="break-all">{{ $path }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        @endif

        @if ($errors->any())
            <div class="rounded-md border border-red-300 bg-red-50 p-4 text-red-800">
                <p class="font-semibold">{{ __('خطا در آپلود فایل‌ها:') }}</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-md border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-white/[0.03]">
            <h2 class="text-lg font-semibold text-slate-800 dark:text-white/90">
                {{ __('آپلود موقت فاکتور برای خطایابی') }}
            </h2>
            <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">
                {{ __('فایل‌های تصویری یا PDF را انتخاب کنید. فایل‌ها در مسیر زیر ذخیره می‌شوند تا تیم فنی بتواند آن‌ها را بررسی کند.') }}
            </p>
            <p class="mt-1 break-words rounded bg-slate-100 p-2 text-xs font-mono text-slate-600 dark:bg-slate-800 dark:text-slate-200">
                {{ $storagePath }}
            </p>

            <form class="mt-6 space-y-4" method="POST" enctype="multipart/form-data"
                  action="{{ route('admin.petty-cash.debug.upload.store') }}">
                @csrf

                <div>
                    <label class="form-label" for="invoice_files">
                        {{ __('انتخاب فایل‌ها (حداکثر ۲۰ مگابایت برای هر فایل)') }}
                    </label>
                    <input
                        id="invoice_files"
                        type="file"
                        name="invoice_files[]"
                        class="form-control"
                        accept="image/*,.pdf"
                        multiple
                        required
                    />
                </div>

                <p class="text-xs text-slate-500 dark:text-slate-400">
                    {{ __('پس از آپلود موفق، مسیر ذخیره هر فایل نمایش داده می‌شود. همان مسیرها را برای بررسی ارسال کنید.') }}
                </p>

                <div class="flex justify-end">
                    <x-buttons.button type="submit" variant="primary">
                        {{ __('آپلود فایل‌ها') }}
                    </x-buttons.button>
                </div>
            </form>
        </div>

        <div class="rounded-md border border-slate-200 bg-white p-6 dark:border-slate-700 dark:bg-white/[0.03]">
            <h3 class="text-base font-semibold text-slate-800 dark:text-white/90">
                {{ __('فایل‌های ذخیره‌شده') }}
            </h3>

            @if ($files->isEmpty())
                <p class="mt-3 text-sm text-slate-500 dark:text-slate-400">
                    {{ __('هنوز فایلی آپلود نشده است.') }}
                </p>
            @else
                <div class="mt-4 overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200 text-sm dark:divide-slate-700">
                        <thead class="bg-slate-50 dark:bg-slate-900/40">
                            <tr>
                                <th class="px-4 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">
                                    {{ __('نام فایل') }}
                                </th>
                                <th class="px-4 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">
                                    {{ __('حجم') }}
                                </th>
                                <th class="px-4 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">
                                    {{ __('تاریخ بارگذاری') }}
                                </th>
                                <th class="px-4 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">
                                    {{ __('مسیر ذخیره‌شده') }}
                                </th>
                                <th class="px-4 py-2 text-right font-semibold text-slate-600 dark:text-slate-300">
                                    {{ __('دریافت') }}
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-200 dark:divide-slate-700">
                            @foreach ($files as $file)
                                <tr>
                                    <td class="px-4 py-2 font-mono text-xs break-all">
                                        {{ $file['name'] }}
                                    </td>
                                    <td class="px-4 py-2 text-xs text-slate-500">
                                        {{ number_format($file['size'] / 1024, 1) }} KB
                                    </td>
                                    <td class="px-4 py-2 text-xs text-slate-500">
                                        {{ \Carbon\Carbon::createFromTimestamp($file['uploaded_at'])->format('Y/m/d H:i:s') }}
                                    </td>
                                    <td class="px-4 py-2 text-xs font-mono break-all text-slate-600 dark:text-slate-300">
                                        {{ $file['path'] }}
                                    </td>
                                    <td class="px-4 py-2 text-xs text-primary">
                                        <a class="hover:underline" href="{{ $file['download_url'] }}">
                                            {{ __('دانلود') }}
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</x-layouts.backend-layout>
