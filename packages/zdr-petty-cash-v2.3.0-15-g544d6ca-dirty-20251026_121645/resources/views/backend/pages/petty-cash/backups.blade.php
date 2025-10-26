<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <!-- Header -->
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800">
                        <i class="ml-2 text-blue-600 fas fa-database"></i>
                        {{ __('مدیریت بک‌آپ‌های تنخواه') }}
                    </h1>
                    <p class="text-sm text-slate-500">{{ __('مشاهده و مدیریت فایل‌های بک‌آپ شعبات حذف شده') }}</p>
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

        <!-- Backup Files -->
        <div class="rounded-lg border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-6 py-4">
                <h2 class="text-lg font-semibold text-slate-800">{{ __('فایل‌های بک‌آپ') }}</h2>
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
