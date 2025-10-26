<div class="space-y-6">
    {{-- Flash Messages --}}
    @if(session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <p class="font-medium text-emerald-800">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Create Button & Filters --}}
    <div class="flex items-center justify-between">
        <button 
            wire:click="openCreateModal"
            class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 font-semibold text-white hover:bg-indigo-700"
        >
            <iconify-icon icon="lucide:plus" class="text-lg"></iconify-icon>
            {{ __('ایجاد اطلاعیه جدید') }}
        </button>

        <select wire:model.live="typeFilter" class="rounded-lg border-slate-300 px-4 py-2.5">
            <option value="all">{{ __('همه انواع') }}</option>
            <option value="info">{{ __('اطلاع') }}</option>
            <option value="success">{{ __('موفقیت') }}</option>
            <option value="warning">{{ __('هشدار') }}</option>
            <option value="danger">{{ __('خطر') }}</option>
        </select>
    </div>

    {{-- Announcements List --}}
    <div class="space-y-4">
        @forelse($announcements as $announcement)
            <div class="overflow-hidden rounded-lg border border-slate-200 bg-white shadow-sm">
                <div class="p-6">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3">
                                <h3 class="text-lg font-bold text-slate-900">{{ $announcement->title }}</h3>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold
                                    {{ $announcement->type === 'info' ? 'bg-blue-100 text-blue-700' : '' }}
                                    {{ $announcement->type === 'success' ? 'bg-emerald-100 text-emerald-700' : '' }}
                                    {{ $announcement->type === 'warning' ? 'bg-amber-100 text-amber-700' : '' }}
                                    {{ $announcement->type === 'danger' ? 'bg-rose-100 text-rose-700' : '' }}">
                                    {{ __(ucfirst($announcement->type)) }}
                                </span>
                                <span class="rounded-full px-2.5 py-0.5 text-xs font-semibold bg-slate-100 text-slate-700">
                                    {{ __(ucfirst($announcement->priority)) }}
                                </span>
                            </div>
                            <p class="mt-2 text-sm text-slate-600">{{ $announcement->content }}</p>
                            
                            <div class="mt-3 flex items-center gap-4 text-xs text-slate-500">
                                <span><iconify-icon icon="lucide:eye"></iconify-icon> {{ $announcement->view_count }} بازدید</span>
                                <span><iconify-icon icon="lucide:calendar"></iconify-icon> {{ $announcement->created_at->diffForHumans() }}</span>
                            </div>
                        </div>

                        <div class="flex gap-2">
                            <button 
                                wire:click="toggleActive({{ $announcement->id }})"
                                class="rounded px-3 py-1.5 text-xs font-semibold
                                    {{ $announcement->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                                {{ $announcement->is_active ? 'فعال' : 'غیرفعال' }}
                            </button>
                            <button 
                                wire:click="openEditModal({{ $announcement->id }})"
                                class="rounded bg-amber-100 px-3 py-1.5 text-xs font-semibold text-amber-700">
                                ویرایش
                            </button>
                            <button 
                                wire:click="delete({{ $announcement->id }})"
                                wire:confirm="آیا مطمئن هستید؟"
                                class="rounded bg-rose-100 px-3 py-1.5 text-xs font-semibold text-rose-700">
                                حذف
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="rounded-lg border border-slate-200 bg-white p-12 text-center">
                <iconify-icon icon="lucide:inbox" class="mx-auto text-6xl text-slate-300"></iconify-icon>
                <p class="mt-4 text-sm text-slate-500">{{ __('اطلاعیه‌ای یافت نشد') }}</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($announcements->hasPages())
        <div class="mt-6">
            {{ $announcements->links() }}
        </div>
    @endif

    {{-- Modal for Create/Edit (Simplified) --}}
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" x-data @click.self="$wire.showModal = false">
            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-2xl rounded-lg bg-white p-6 shadow-xl">
                    <h3 class="text-lg font-bold">{{ $announcementId ? 'ویرایش اطلاعیه' : 'ایجاد اطلاعیه جدید' }}</h3>
                    
                    <form wire:submit.prevent="save" class="mt-4 space-y-4">
                        <div>
                            <label class="text-sm font-semibold">عنوان</label>
                            <input wire:model="title" type="text" class="mt-1 w-full rounded border-slate-300 px-3 py-2" required>
                            @error('title') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div>
                            <label class="text-sm font-semibold">محتوا</label>
                            <textarea wire:model="content" rows="4" class="mt-1 w-full rounded border-slate-300 px-3 py-2" required></textarea>
                            @error('content') <span class="text-xs text-rose-600">{{ $message }}</span> @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-semibold">نوع</label>
                                <select wire:model="type" class="mt-1 w-full rounded border-slate-300 px-3 py-2">
                                    <option value="info">اطلاع</option>
                                    <option value="success">موفقیت</option>
                                    <option value="warning">هشدار</option>
                                    <option value="danger">خطر</option>
                                </select>
                            </div>

                            <div>
                                <label class="text-sm font-semibold">اولویت</label>
                                <select wire:model="priority" class="mt-1 w-full rounded border-slate-300 px-3 py-2">
                                    <option value="low">کم</option>
                                    <option value="normal">عادی</option>
                                    <option value="high">بالا</option>
                                    <option value="urgent">فوری</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2">
                                <input wire:model="is_active" type="checkbox" class="rounded border-slate-300">
                                <span class="text-sm">فعال</span>
                            </label>
                            <label class="flex items-center gap-2">
                                <input wire:model="is_pinned" type="checkbox" class="rounded border-slate-300">
                                <span class="text-sm">سنجاق شده</span>
                            </label>
                        </div>

                        <div class="flex gap-3 border-t pt-4">
                            <button type="submit" class="rounded bg-indigo-600 px-4 py-2 font-semibold text-white hover:bg-indigo-700">
                                ذخیره
                            </button>
                            <button type="button" @click="$wire.showModal = false" class="rounded bg-slate-200 px-4 py-2 font-semibold text-slate-700">
                                انصراف
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
