<div x-data="{ showModal: @entangle('showModal').live }"
     x-show="showModal"
     x-on:keydown.escape.window="showModal = false"
     class="fixed inset-0 z-50 overflow-y-auto"
     aria-labelledby="modal-title"
     role="dialog"
     aria-modal="true"
     style="display: none;">

    <div class="flex min-h-screen items-end justify-center px-4 pt-4 pb-20 text-center sm:block sm:p-0">
        <!-- Overlay -->
        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-gray-500/75 transition-opacity"
             @click="showModal = false"
             aria-hidden="true"></div>

        <!-- Modal Panel -->
        <span class="hidden sm:inline-block sm:h-screen sm:align-middle" aria-hidden="true">&#8203;</span>

        <div x-show="showModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             class="relative inline-block w-full max-w-3xl transform overflow-hidden rounded-lg bg-white text-right shadow-xl transition-all sm:my-8 sm:align-middle">

            <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="sm:flex sm:items-start">
                    <div class="mx-auto flex h-12 w-12 flex-shrink-0 items-center justify-center rounded-full bg-sky-100 sm:mx-0 sm:h-10 sm:w-10">
                        <iconify-icon icon="lucide:users" class="text-xl text-sky-600"></iconify-icon>
                    </div>
                    <div class="mt-3 flex-grow text-center sm:mt-0 sm:mr-4 sm:text-right">
                        <h3 class="text-lg font-bold leading-6 text-gray-900" id="modal-title">
                            مدیریت کاربران شعبه: {{ $ledger?->branch_name }}
                        </h3>
                        <p class="mt-1 text-sm text-gray-500">
                            کاربران مورد نظر را به این شعبه اضافه کرده و سطح دسترسی آن‌ها را مشخص کنید.
                        </p>
                    </div>
                </div>

                <!-- Session Messages -->
                @if (session()->has('success'))
                    <div class="mt-4 rounded-md bg-green-50 p-4">
                        <p class="text-sm font-medium text-green-800">{{ session('success') }}</p>
                    </div>
                @endif
                @if (session()->has('error'))
                    <div class="mt-4 rounded-md bg-red-50 p-4">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                @endif

                <!-- Add User Form -->
                <div class="mt-6 border-t border-gray-200 pt-6">
                    <h4 class="font-semibold text-gray-800">افزودن کاربر جدید</h4>
                    <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-6">
                        <div class="relative sm:col-span-3">
                            <label for="search" class="block text-sm font-medium text-gray-700">جستجوی کاربر (نام یا ایمیل)</label>
                            <input type="text"
                                   wire:model.live.debounce.300ms="search"
                                   id="search"
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                   placeholder="حداقل ۳ حرف وارد کنید...">
                            
                            @if(count($searchResults) > 0)
                                <ul class="absolute z-10 mt-1 w-full rounded-md border border-gray-300 bg-white shadow-lg">
                                    @foreach($searchResults as $user)
                                        <li class="cursor-pointer px-4 py-2 hover:bg-gray-100"
                                            wire:click="selectUser({{ $user->id }})">
                                            {{ $user->full_name }} ({{ $user->email }})
                                        </li>
                                    @endforeach
                                </ul>
                            @endif
                        </div>
                        <div class="sm:col-span-2">
                            <label for="access_type" class="block text-sm font-medium text-gray-700">سطح دسترسی</label>
                            <select wire:model="selectedAccessType" id="access_type" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                @foreach($accessTypes as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="sm:col-span-1 flex items-end">
                            <button type="button"
                                    wire:click="addUser"
                                    wire:loading.attr="disabled"
                                    class="inline-flex w-full items-center justify-center rounded-md border border-transparent bg-indigo-600 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50">
                                افزودن
                            </button>
                        </div>
                    </div>
                    @error('selectedUserId') <p class="mt-2 text-sm text-red-600">لطفاً یک کاربر از نتایج جستجو انتخاب کنید.</p> @enderror
                </div>

                <!-- Branch Users List -->
                <div class="mt-6 border-t border-gray-200 pt-6">
                     <h4 class="font-semibold text-gray-800">کاربران فعلی شعبه</h4>
                     <div class="mt-4 flow-root">
                        <div class="-my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
                            <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
                                <table class="min-w-full divide-y divide-gray-300">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="py-3.5 pr-4 pl-3 text-right text-sm font-semibold text-gray-900">نام کاربر</th>
                                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">ایمیل</th>
                                            <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">سطح دسترسی</th>
                                            <th scope="col" class="relative py-3.5 pl-3 pr-4">
                                                <span class="sr-only">حذف</span>
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        @forelse($branchUsers as $branchUser)
                                            <tr>
                                                <td class="whitespace-nowrap py-4 pr-4 pl-3 text-sm font-medium text-gray-900">{{ $branchUser->user->full_name }}</td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">{{ $branchUser->user->email }}</td>
                                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                                    <span class="inline-flex items-center rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                        {{ $accessTypes[$branchUser->access_type] ?? $branchUser->access_type }}
                                                    </span>
                                                </td>
                                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-left text-sm font-medium">
                                                    <button type="button"
                                                            wire:click="removeUser({{ $branchUser->id }})"
                                                            wire:confirm="آیا از حذف دسترسی این کاربر مطمئن هستید؟"
                                                            class="text-red-600 hover:text-red-900">
                                                        حذف
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="py-6 text-center text-sm text-gray-500">
                                                    هنوز کاربری به این شعبه اختصاص داده نشده است.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-4 py-3 sm:flex sm:flex-row-reverse sm:px-6">
                <button type="button"
                        @click="showModal = false"
                        wire:click="closeModal"
                        class="mt-3 inline-flex w-full justify-center rounded-md border border-gray-300 bg-white px-4 py-2 text-base font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 sm:mt-0 sm:w-auto sm:text-sm">
                    بستن
                </button>
            </div>
        </div>
    </div>
</div>
