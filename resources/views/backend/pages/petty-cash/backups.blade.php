<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        @if(session('success'))
            <div class="rounded-lg border border-green-200 bg-green-50 p-4 text-sm text-green-800 shadow-sm">
                <div class="flex items-start gap-2">
                    <i class="fas fa-check-circle mt-0.5"></i>
                    <p>{{ session('success') }}</p>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div class="rounded-lg border border-red-200 bg-red-50 p-4 text-sm text-red-800 shadow-sm">
                <div class="flex items-start gap-2">
                    <i class="fas fa-exclamation-triangle mt-0.5"></i>
                    <p>{{ session('error') }}</p>
                </div>
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 shadow-sm">
                <div class="flex items-start gap-2">
                    <i class="fas fa-info-circle mt-0.5"></i>
                    <div>
                        <p class="font-semibold mb-1">{{ __('خطا در ورودی‌ها:') }}</p>
                        <ul class="list-disc space-y-1 ps-4">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        @if(session('created_backup'))
            <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 text-sm text-blue-800 shadow-sm">
                <div class="flex items-start gap-2">
                    <i class="fas fa-database mt-0.5"></i>
                    <p>{{ __('فایل بک‌آپ :name با موفقیت ایجاد شد.', ['name' => session('created_backup')]) }}</p>
                </div>
            </div>
        @endif

        <!-- Header -->
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800 flex items-center gap-2">
                        <i class="text-blue-600 fas fa-database"></i>
                        {{ __('مدیریت بک‌آپ‌ها') }}
                    </h1>
                    <p class="text-sm text-slate-500">{{ __('در این صفحه می‌توانید بک‌آپ‌های تنخواه و پایگاه داده را مدیریت کنید.') }}</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('admin.petty-cash.index') }}"
                       class="inline-flex items-center gap-2 rounded-md border border-slate-300 bg-white px-4 py-2 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-arrow-right"></i>
                        {{ __('بازگشت به لیست شعبات') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Database Backup Actions -->
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-server text-indigo-600"></i>
                    {{ __('بک‌آپ پایگاه داده') }}
                </h2>
                <p class="text-sm text-slate-500">{{ __('تهیه و ارسال بک‌آپ پایگاه داده، دانلود و بازیابی دستی.') }}</p>
            </div>

            <div class="grid gap-6 p-6 md:grid-cols-2">
                <form method="POST" action="{{ route('admin.petty-cash.backups.database.create') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-600">{{ __('ارسال به ایمیل (اختیاری)') }}</label>
                        <input type="email" name="email" value="{{ old('email', $defaultBackupEmail) }}"
                               class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               placeholder="example@email.com">
                        @error('email')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-slate-500">{{ __('در صورت خالی بودن، به ایمیل پیش‌فرض ارسال می‌شود.') }}</p>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                        <i class="fas fa-cloud-download-alt"></i>
                        {{ __('تهیه بک‌آپ و ارسال ایمیل') }}
                    </button>
                </form>

                <form method="POST" action="{{ route('admin.petty-cash.backups.database.restore') }}" enctype="multipart/form-data" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-slate-600">{{ __('انتخاب فایل بک‌آپ جهت بازیابی') }}</label>
                        <input type="file" name="backup_file"
                               class="mt-1 w-full rounded-md border-slate-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                               accept=".sql,.sql.gz">
                        @error('backup_file')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-red-500">{{ __('هشدار: بازیابی باعث جایگزینی کامل اطلاعات پایگاه داده می‌شود. قبل از ادامه از اطلاعات فعلی بک‌آپ بگیرید.') }}</p>
                    </div>
                    <button type="submit"
                            class="inline-flex items-center justify-center gap-2 rounded-md bg-rose-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-rose-700 focus:outline-none focus:ring-2 focus:ring-rose-500 focus:ring-offset-2"
                            onclick="return confirm('{{ __('آیا از بازیابی پایگاه داده مطمئن هستید؟ تمامی اطلاعات فعلی جایگزین خواهد شد.') }}')">
                        <i class="fas fa-history"></i>
                        {{ __('بازیابی از فایل') }}
                    </button>
                </form>
            </div>
        </div>

        <!-- Database Backup List -->
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-hdd text-indigo-600"></i>
                    {{ __('فایل‌های بک‌آپ پایگاه داده') }}
                </h2>
                @php $latestDbBackup = $databaseBackups->first(); @endphp
                <span class="text-xs text-slate-500">{{ __('آخرین ایجاد: ') }}{{ $latestDbBackup['created_at_formatted'] ?? __('--') }}</span>
            </div>

            <div class="p-6">
                @if($databaseBackups->isEmpty())
                    <div class="text-center py-12">
                        <i class="fas fa-database text-4xl text-slate-300 mb-4"></i>
                        <p class="text-slate-500">{{ __('هیچ بک‌آپی از پایگاه داده موجود نیست.') }}</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200 text-sm">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-4 py-2 text-right font-medium text-slate-600">{{ __('نام فایل') }}</th>
                                    <th class="px-4 py-2 text-right font-medium text-slate-600">{{ __('حجم') }}</th>
                                    <th class="px-4 py-2 text-right font-medium text-slate-600">{{ __('تاریخ ایجاد') }}</th>
                                    <th class="px-4 py-2 text-right font-medium text-slate-600">{{ __('عملیات') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                @foreach($databaseBackups as $file)
                                    <tr class="bg-white">
                                        <td class="px-4 py-3 font-medium text-slate-800">{{ $file['name'] }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $file['size_formatted'] }}</td>
                                        <td class="px-4 py-3 text-slate-600">{{ $file['created_at_formatted'] }}</td>
                                        <td class="px-4 py-3">
                                            <div class="flex flex-wrap gap-2">
                                                <a href="{{ route('admin.petty-cash.backups.database.download', $file['name']) }}"
                                                   class="inline-flex items-center gap-1 rounded-md bg-green-600 px-3 py-1 text-white hover:bg-green-700">
                                                    <i class="fas fa-download"></i>
                                                    {{ __('دانلود') }}
                                                </a>
                                                <form method="POST" action="{{ route('admin.petty-cash.backups.database.email', $file['name']) }}" class="inline-flex items-center gap-2">
                                                    @csrf
                                                    <input type="email" name="email" value="{{ old('email', $defaultBackupEmail) }}"
                                                           class="hidden md:block w-40 rounded-md border-slate-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                           placeholder="{{ __('ایمیل مقصد') }}">
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 rounded-md bg-blue-600 px-3 py-1 text-white hover:bg-blue-700">
                                                        <i class="fas fa-paper-plane"></i>
                                                        {{ __('ارسال ایمیل') }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.petty-cash.backups.database.restore-existing', $file['name']) }}"
                                                      onsubmit="return confirm('{{ __('آیا از بازیابی این فایل مطمئن هستید؟ تمامی اطلاعات فعلی جایگزین خواهد شد.') }}')">
                                                    @csrf
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 rounded-md bg-amber-600 px-3 py-1 text-white hover:bg-amber-700">
                                                        <i class="fas fa-undo"></i>
                                                        {{ __('بازیابی') }}
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('admin.petty-cash.backups.database.delete', $file['name']) }}"
                                                      onsubmit="return confirm('{{ __('آیا از حذف این فایل بک‌آپ مطمئن هستید؟') }}')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit"
                                                            class="inline-flex items-center gap-1 rounded-md bg-red-600 px-3 py-1 text-white hover:bg-red-700">
                                                        <i class="fas fa-trash"></i>
                                                        {{ __('حذف') }}
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Backup Files -->
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-800">{{ __('بک‌آپ‌های تنخواه حذف شده') }}</h2>
                <p class="text-sm text-slate-500">{{ __('این بخش شامل بک‌آپ‌هایی است که هنگام حذف شعبه تنخواه ایجاد شده‌اند.') }}</p>
            </div>

            <div class="p-6">
                @if($backupFiles->isEmpty())
                    <div class="text-center py-12">
                        <i class="fas fa-database text-4xl text-slate-300 mb-4"></i>
                        <p class="text-slate-500">{{ __('هیچ فایل بک‌آپی یافت نشد.') }}</p>
                    </div>
                @else
                    <div class="space-y-4">
                        @foreach($backupFiles as $file)
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <div class="flex h-12 w-12 items-center justify-center rounded-full bg-blue-100">
                                            <i class="fas fa-file-code text-blue-600"></i>
                                        </div>
                                        <div>
                                            <h3 class="font-semibold text-slate-800">{{ $file['name'] }}</h3>
                                            <p class="text-sm text-slate-600">
                                                {{ __('اندازه: :size', ['size' => $file['size_formatted']]) }} •
                                                {{ __('ایجاد شده: :date', ['date' => verta($file['created_at'])->format('Y/m/d H:i')]) }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex gap-2">
                                        <a href="{{ route('admin.petty-cash.backup.download', $file['name']) }}"
                                           class="inline-flex items-center gap-2 rounded-md bg-green-600 px-3 py-1 text-sm font-medium text-white hover:bg-green-700">
                                            <i class="fas fa-download"></i>
                                            {{ __('دانلود') }}
                                        </a>
                                        <form method="POST" action="{{ route('admin.petty-cash.backup.delete', $file['name']) }}" class="inline"
                                              onsubmit="return confirm('{{ __('آیا از حذف این فایل بک‌آپ مطمئن هستید؟') }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="inline-flex items-center gap-2 rounded-md bg-red-600 px-3 py-1 text-sm font-medium text-white hover:bg-red-700">
                                                <i class="fas fa-trash"></i>
                                                {{ __('حذف') }}
                                            </button>
                                        </form>
                                    </div>
                                </div>

                                @if(isset($file['metadata']))
                                    <div class="mt-4 rounded-lg bg-white p-3">
                                        <h4 class="text-sm font-medium text-slate-700 mb-2">{{ __('اطلاعات بک‌آپ') }}</h4>
                                        <div class="grid grid-cols-2 gap-4 text-sm">
                                            <div>
                                                <span class="font-medium text-slate-600">{{ __('شعبه حذف شده:') }}</span>
                                                <span class="text-slate-800">{{ $file['metadata']['ledger']['branch_name'] ?? 'نامشخص' }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-slate-600">{{ __('تعداد تراکنش‌ها:') }}</span>
                                                <span class="text-slate-800">{{ count($file['metadata']['transactions'] ?? []) }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-slate-600">{{ __('حذف شده توسط:') }}</span>
                                                <span class="text-slate-800">{{ $file['metadata']['deleted_by_name'] ?? 'نامشخص' }}</span>
                                            </div>
                                            <div>
                                                <span class="font-medium text-slate-600">{{ __('دلیل حذف:') }}</span>
                                                <span class="text-slate-800">{{ $file['metadata']['reason'] ?? 'ثبت نشده' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    @if($backupFiles->hasPages())
                        <div class="mt-6">
                            {{ $backupFiles->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>
    </div>
</x-layouts.backend-layout>
