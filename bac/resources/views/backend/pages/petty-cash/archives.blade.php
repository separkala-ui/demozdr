<x-layouts.backend-layout :breadcrumbs="$breadcrumbs">
    <div class="space-y-6">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h1 class="text-xl font-semibold text-slate-800">
                        <i class="fas fa-archive ml-2 text-indigo-600"></i>
                        {{ __('بایگانی اسناد تنخواه') }}
                    </h1>
                    <p class="text-sm text-slate-500">
                        {{ __('مرور و مدیریت اسناد بایگانی‌شده شعب. برای مشاهده جزئیات بیشتر می‌توانید از فیلترها استفاده کنید.') }}
                    </p>
                </div>
                @if($canManageArchives)
                    <span class="inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-xs font-semibold text-emerald-600">
                        <i class="fas fa-shield-alt"></i>
                        {{ __('حالت مدیریت (سوپر ادمین)') }}
                    </span>
                @endif
            </div>

            <form method="get" class="mt-6 grid grid-cols-1 gap-4 lg:grid-cols-4">
                <div>
                    <label class="text-xs font-semibold text-slate-500">{{ __('شعبه') }}</label>
                    <select name="ledger_id" class="mt-1 block w-full rounded-md border-slate-300 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500">
                        <option value="">{{ __('همه شعب') }}</option>
                        @foreach($ledgers as $item)
                            <option value="{{ $item->id }}" @selected($filters['ledger_id'] == $item->id)>{{ $item->branch_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">{{ __('از تاریخ') }}</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="text-xs font-semibold text-slate-500">{{ __('تا تاریخ') }}</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="mt-1 block w-full rounded-md border-slate-300 px-3 py-2 text-sm shadow focus:border-indigo-500 focus:ring-indigo-500">
                </div>
                <div class="flex items-end gap-2">
                    <button type="submit" class="inline-flex w-full items-center justify-center gap-2 rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow hover:bg-indigo-500">
                        <i class="fas fa-filter"></i>
                        {{ __('اعمال فیلتر') }}
                    </button>
                    <a href="{{ route('admin.petty-cash.archives.index') }}" class="inline-flex items-center justify-center rounded-md border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-600 hover:bg-slate-50">
                        {{ __('حذف فیلتر') }}
                    </a>
                </div>
            </form>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-0 shadow-sm">
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-right text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold uppercase tracking-wide text-slate-600">
                        <tr>
                            <th class="px-4 py-2">{{ __('شناسه') }}</th>
                            <th class="px-4 py-2">{{ __('شعبه') }}</th>
                            <th class="px-4 py-2">{{ __('شروع دوره') }}</th>
                            <th class="px-4 py-2">{{ __('پایان دوره') }}</th>
                            <th class="px-4 py-2">{{ __('تعداد تراکنش') }}</th>
                            <th class="px-4 py-2">{{ __('جمع ورودی (ریال)') }}</th>
                            <th class="px-4 py-2">{{ __('جمع خروجی (ریال)') }}</th>
                            <th class="px-4 py-2">{{ __('تایید کننده') }}</th>
                            <th class="px-4 py-2 text-center">{{ __('اقدامات') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-xs text-slate-600">
                        @forelse($archives as $archive)
                            <tr class="hover:bg-slate-50">
                                <td class="px-4 py-3 font-semibold text-slate-800">#{{ $archive->id }}</td>
                                <td class="px-4 py-3">{{ $archive->ledger->branch_name ?? __('نامشخص') }}</td>
                                <td class="px-4 py-3">{{ $archive->opened_at ? verta($archive->opened_at)->format('Y/m/d H:i') : '—' }}</td>
                                <td class="px-4 py-3">{{ $archive->closed_at ? verta($archive->closed_at)->format('Y/m/d H:i') : '—' }}</td>
                                <td class="px-4 py-3">{{ $archive->transactions_count ?? 0 }}</td>
                                <td class="px-4 py-3">{{ number_format($archive->total_charges ?? 0) }}</td>
                                <td class="px-4 py-3">{{ number_format($archive->total_expenses ?? 0) }}</td>
                                <td class="px-4 py-3">{{ optional($archive->closer)->full_name ?? optional($archive->closer)->name ?? __('نامشخص') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center justify-end gap-2">
                                        <a href="{{ route('admin.petty-cash.archives.show', $archive->id) }}" class="inline-flex items-center gap-1 rounded-md bg-blue-50 px-2 py-1 text-[11px] font-semibold text-blue-600 hover:bg-blue-100">
                                            <i class="fas fa-eye"></i>
                                            {{ __('مشاهده تنخواه') }}
                                        </a>
                                        <a href="{{ route('admin.petty-cash.print', ['ledger' => $archive->ledger_id, 'cycle' => $archive->id]) }}" target="_blank" class="inline-flex items-center gap-1 rounded-md border border-slate-300 px-2 py-1 text-[11px] font-semibold text-slate-600 hover:bg-slate-100">
                                            <i class="fas fa-print"></i>
                                            {{ __('چاپ') }}
                                        </a>
                                        @if($archive->report_path)
                                            <a href="{{ route('admin.petty-cash.archives.download', ['ledger' => $archive->ledger_id, 'cycle' => $archive->id]) }}" class="inline-flex items-center gap-1 rounded-md bg-emerald-50 px-2 py-1 text-[11px] font-semibold text-emerald-600 hover:bg-emerald-100">
                                                <i class="fas fa-file-excel"></i>
                                                {{ __('دانلود') }}
                                            </a>
                                        @endif
                                        @if($canManageArchives)
                                            <a href="{{ route('admin.petty-cash.archives.edit', $archive->id) }}" class="inline-flex items-center gap-1 rounded-md bg-indigo-50 px-2 py-1 text-[11px] font-semibold text-indigo-600 hover:bg-indigo-100">
                                                <i class="fas fa-edit"></i>
                                                {{ __('ویرایش') }}
                                            </a>
                                            <form method="post" action="{{ route('admin.petty-cash.archives.destroy', $archive->id) }}" onsubmit="return confirm('{{ __('آیا از حذف این سند مطمئن هستید؟ تراکنش‌ها از وضعیت آرشیو خارج می‌شوند.') }}');">
                                                @csrf
                                                @method('delete')
                                                <button type="submit" class="inline-flex items-center gap-1 rounded-md bg-rose-500 px-2 py-1 text-[11px] font-semibold text-white hover:bg-rose-600">
                                                    <i class="fas fa-trash"></i>
                                                    {{ __('حذف') }}
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="px-4 py-6 text-center text-sm text-slate-500">
                                    {{ __('تا کنون سند بایگانی‌شده‌ای ثبت نشده است.') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="border-t border-slate-100 px-4 py-3">
                {{ $archives->links() }}
            </div>
        </div>
    </div>
</x-layouts.backend-layout>
