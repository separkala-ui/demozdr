<div class="space-y-6">
    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <div class="flex items-center gap-3">
                <iconify-icon icon="lucide:check-circle" class="text-2xl text-emerald-500"></iconify-icon>
                <p class="font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div class="rounded-lg border border-rose-200 bg-rose-50 p-4">
            <div class="flex items-center gap-3">
                <iconify-icon icon="lucide:x-circle" class="text-2xl text-rose-500"></iconify-icon>
                <p class="font-medium text-rose-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- Search & Filter --}}
    <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
        <div class="flex-1">
            <input 
                wire:model.live="search" 
                type="text" 
                placeholder="{{ __('جستجو در تنظیمات...') }}"
                class="w-full rounded-lg border-slate-300 px-4 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
            >
        </div>
        
        <select 
            wire:model.live="categoryFilter" 
            class="rounded-lg border-slate-300 px-4 py-2.5 shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500"
        >
            <option value="all">{{ __('همه دسته‌ها') }}</option>
            @foreach($categories as $cat)
                <option value="{{ $cat }}">{{ __(ucfirst($cat)) }}</option>
            @endforeach
        </select>
    </div>

    {{-- Settings Table --}}
    <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">{{ __('عنوان') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">{{ __('دسته') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">{{ __('نوع') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">{{ __('مقدار') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">{{ __('وضعیت') }}</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase text-slate-500">{{ __('عملیات') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200 bg-white">
                    @forelse($settings as $setting)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4">
<div>
                                    <p class="font-medium text-slate-900">{{ $setting->title_fa }}</p>
                                    @if($setting->description_fa)
                                        <p class="mt-0.5 text-xs text-slate-500">{{ Str::limit($setting->description_fa, 60) }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="inline-flex rounded-full px-2 py-1 text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ __(ucfirst($setting->category)) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-slate-600">{{ __(ucfirst($setting->type)) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($editingId === $setting->id)
                                    <div class="flex items-center gap-2">
                                        <input 
                                            wire:model="editingValue" 
                                            type="text" 
                                            class="w-32 rounded border-slate-300 px-2 py-1 text-sm focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500"
                                            autofocus
                                        >
                                        @if($setting->type === 'percentage')
                                            <span class="text-sm text-slate-500">%</span>
                                        @endif
                                    </div>
                                    @error('editingValue')
                                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                                    @enderror
                                @else
                                    <span class="font-mono text-sm font-semibold text-indigo-600">
                                        {{ $setting->value }}
                                        @if($setting->type === 'percentage')
                                            <span class="text-slate-500">%</span>
                                        @endif
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <button 
                                    wire:click="toggleActive({{ $setting->id }})"
                                    class="inline-flex items-center gap-1 rounded-full px-3 py-1 text-xs font-semibold transition-colors
                                        {{ $setting->is_active ? 'bg-emerald-100 text-emerald-700 hover:bg-emerald-200' : 'bg-slate-100 text-slate-600 hover:bg-slate-200' }}"
                                >
                                    <iconify-icon icon="{{ $setting->is_active ? 'lucide:check' : 'lucide:x' }}" class="text-sm"></iconify-icon>
                                    {{ $setting->is_active ? __('فعال') : __('غیرفعال') }}
                                </button>
                            </td>
                            <td class="px-6 py-4">
                                @if($setting->is_editable)
                                    @if($editingId === $setting->id)
                                        <div class="flex gap-2">
                                            <button 
                                                wire:click="saveSetting"
                                                class="inline-flex items-center gap-1 rounded bg-indigo-600 px-3 py-1.5 text-xs font-semibold text-white hover:bg-indigo-700"
                                            >
                                                <iconify-icon icon="lucide:save" class="text-sm"></iconify-icon>
                                                {{ __('ذخیره') }}
                                            </button>
                                            <button 
                                                wire:click="cancelEdit"
                                                class="inline-flex items-center gap-1 rounded bg-slate-200 px-3 py-1.5 text-xs font-semibold text-slate-700 hover:bg-slate-300"
                                            >
                                                <iconify-icon icon="lucide:x" class="text-sm"></iconify-icon>
                                                {{ __('انصراف') }}
                                            </button>
                                        </div>
                                    @else
                                        <button 
                                            wire:click="editSetting({{ $setting->id }})"
                                            class="inline-flex items-center gap-1 rounded bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700 hover:bg-amber-200"
                                        >
                                            <iconify-icon icon="lucide:edit" class="text-sm"></iconify-icon>
                                            {{ __('ویرایش') }}
                                        </button>
                                    @endif
                                @else
                                    <span class="text-xs text-slate-400">{{ __('غیرقابل ویرایش') }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <iconify-icon icon="lucide:inbox" class="mx-auto text-6xl text-slate-300"></iconify-icon>
                                <p class="mt-4 text-sm text-slate-500">{{ __('تنظیماتی یافت نشد') }}</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($settings->hasPages())
            <div class="border-t border-slate-200 bg-slate-50 px-6 py-4">
                {{ $settings->links() }}
            </div>
        @endif
    </div>
</div>
