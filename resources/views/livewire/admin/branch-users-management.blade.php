<div>
    <!-- Title -->
    <div class="mb-6 flex items-center gap-3 border-b border-slate-200 pb-4">
        <div class="flex h-10 w-10 items-center justify-center rounded-full bg-sky-100">
            <iconify-icon icon="lucide:users-check" class="text-xl text-sky-600"></iconify-icon>
        </div>
        <div>
            <h3 class="font-semibold text-slate-800">{{ __('مدیریت کاربران شعبه') }}</h3>
            <p class="text-xs text-slate-500">{{ __('کاربران مختلفی را به این شعبه با دسترسی‌های مختلف اضافه کنید') }}</p>
        </div>
    </div>

    <!-- Add User Form -->
    <div class="mb-6 space-y-4 rounded-lg bg-slate-50 p-4">
        <h4 class="font-medium text-slate-700">{{ __('افزودن کاربر جدید') }}</h4>
        
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-12">
            <!-- Search Input -->
            <div class="relative sm:col-span-5">
                <label for="search" class="mb-1 block text-xs font-medium text-slate-600">{{ __('جستجوی کاربر') }}</label>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       id="search"
                       class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                       placeholder="{{ __('نام یا ایمیل (حداقل ۳ حرف)') }}">
                
                <!-- Search Results Dropdown -->
                @if(count($searchResults) > 0)
                    <ul class="absolute left-0 right-0 top-full z-10 mt-1 max-h-48 overflow-y-auto rounded-md border border-slate-300 bg-white shadow-lg">
                        @foreach($searchResults as $user)
                            <li>
                                <button type="button"
                                        wire:click="selectUser({{ $user->id }})"
                                        class="flex w-full px-3 py-2 text-left text-sm hover:bg-slate-100">
                                    <div>
                                        <p class="font-medium text-slate-900">{{ $user->full_name }}</p>
                                        <p class="text-xs text-slate-500">{{ $user->email }}</p>
                                    </div>
                                </button>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <!-- Access Type Dropdown -->
            <div class="sm:col-span-4">
                <label for="access_type" class="mb-1 block text-xs font-medium text-slate-600">{{ __('سطح دسترسی') }}</label>
                <select wire:model="selectedAccessType" id="access_type" class="block w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                    @foreach($accessTypes as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Add Button -->
            <div class="flex items-end sm:col-span-3">
                <button type="button"
                        wire:click="addUser"
                        wire:loading.attr="disabled"
                        class="w-full rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 disabled:opacity-50">
                    {{ __('افزودن') }}
                </button>
            </div>
        </div>

        <!-- Validation Errors -->
        @error('selectedUserId')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Current Users List -->
    <div>
        <h4 class="mb-3 font-medium text-slate-700">{{ __('کاربران فعلی') }} <span class="text-xs text-slate-500">({{ count($branchUsers) }})</span></h4>
        
        @if(count($branchUsers) > 0)
            <div class="space-y-2">
                @foreach($branchUsers as $branchUser)
                    <div class="flex items-center justify-between rounded-lg border border-slate-200 bg-white p-3">
                        <div class="flex items-center gap-3">
                            <div class="flex h-8 w-8 items-center justify-center rounded-full bg-slate-100">
                                <i class="fas fa-user text-slate-600 text-xs"></i>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-slate-900">{{ $branchUser->user->full_name }}</p>
                                <p class="text-xs text-slate-500">{{ $branchUser->user->email }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center rounded-full bg-blue-100 px-2.5 py-0.5 text-xs font-medium text-blue-800">
                                {{ $accessTypes[$branchUser->access_type] ?? $branchUser->access_type }}
                            </span>
                            <button type="button"
                                    wire:click="removeUser({{ $branchUser->id }})"
                                    wire:confirm="{{ __('آیا از حذف دسترسی این کاربر مطمئن هستید؟') }}"
                                    class="rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-600 hover:bg-red-100">
                                {{ __('حذف') }}
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="rounded-lg border border-dashed border-slate-300 bg-slate-50 p-4 text-center text-sm text-slate-500">
                {{ __('هنوز کاربری به این شعبه اختصاص داده نشده است.') }}
            </div>
        @endif
    </div>
</div>
