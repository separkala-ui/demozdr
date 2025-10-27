<div class="w-full">
    <!-- Tab Navigation -->
    <div class="mb-6">
        <div class="border-b border-stroke dark:border-dark-3">
            <div class="flex flex-wrap gap-2">
                <button 
                    wire:click="switchTab('list')"
                    class="inline-flex items-center gap-2 px-6 py-3 text-base font-medium border-b-2 transition-colors
                        {{ $activeTab === 'list' ? 'border-primary text-primary dark:text-white' : 'border-transparent text-dark-3 hover:text-dark dark:text-dark-6 dark:hover:text-white' }}"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                    </svg>
                    <span>لیست الگوهای فرم</span>
                    @if($templates->count() > 0)
                        <span class="inline-flex items-center justify-center px-2 py-1 text-xs font-bold leading-none text-white bg-primary rounded-full">
                            {{ $templates->count() }}
                        </span>
                    @endif
                </button>
                
                <button 
                    wire:click="switchTab('create')"
                    class="inline-flex items-center gap-2 px-6 py-3 text-base font-medium border-b-2 transition-colors
                        {{ $activeTab === 'create' ? 'border-primary text-primary dark:text-white' : 'border-transparent text-dark-3 hover:text-dark dark:text-dark-6 dark:hover:text-white' }}"
                >
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>{{ $editingTemplate ? 'ویرایش فرم' : 'فرم جدید' }}</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if (session()->has('success'))
        <div class="mb-6 flex items-center gap-3 rounded-lg border border-success bg-success-light-5 p-4 dark:bg-success-dark-5" role="alert">
            <svg class="w-6 h-6 text-success" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="text-base font-medium text-dark dark:text-white">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Tab Content: List -->
    <div class="{{ $activeTab === 'list' ? 'block' : 'hidden' }}">
        <div class="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
            <div class="flex items-center justify-between border-b border-stroke px-7 py-4 dark:border-dark-3">
                <h3 class="text-xl font-semibold text-dark dark:text-white">
                    📝 لیست الگوهای فرم
                </h3>
                <button 
                    wire:click="create"
                    class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    <span>الگوی جدید</span>
                </button>
            </div>
            <div class="p-7">
                @if ($templates->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto">
                            <thead>
                                <tr class="bg-gray-2 text-right dark:bg-dark-2">
                                    <th class="px-4 py-3 font-medium text-dark dark:text-white">عنوان</th>
                                    <th class="px-4 py-3 font-medium text-dark dark:text-white">دسته‌بندی</th>
                                    <th class="px-4 py-3 font-medium text-dark dark:text-white">فیلدها</th>
                                    <th class="px-4 py-3 font-medium text-dark dark:text-white">وضعیت</th>
                                    <th class="px-4 py-3 font-medium text-dark dark:text-white">ایجادکننده</th>
                                    <th class="px-4 py-3 font-medium text-dark dark:text-white">تاریخ</th>
                                    <th class="px-4 py-3 font-medium text-dark dark:text-white">عملیات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($templates as $template)
                                    <tr class="border-b border-stroke dark:border-dark-3 hover:bg-gray-2 dark:hover:bg-dark-2">
                                        <td class="px-4 py-3">
                                            <span class="font-semibold text-dark dark:text-white">{{ $template->title }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($template->category === 'qc')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-info px-3 py-1 text-sm font-medium text-white">
                                                    🔍 کنترل کیفیت
                                                </span>
                                            @elseif ($template->category === 'inspection')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-warning px-3 py-1 text-sm font-medium text-white">
                                                    🔎 بازرسی
                                                </span>
                                            @elseif ($template->category === 'production')
                                                <span class="inline-flex items-center gap-1 rounded-full bg-danger px-3 py-1 text-sm font-medium text-white">
                                                    🏭 تولید
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-dark-3 px-3 py-1 text-sm font-medium text-white">
                                                    📋 سایر
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="inline-flex items-center justify-center rounded-full bg-primary px-2.5 py-1 text-sm font-medium text-white">
                                                {{ $template->fields()->count() }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if ($template->is_active)
                                                <span class="inline-flex items-center gap-1 rounded-full bg-success px-3 py-1 text-sm font-medium text-white">
                                                    ✓ فعال
                                                </span>
                                            @else
                                                <span class="inline-flex items-center gap-1 rounded-full bg-danger px-3 py-1 text-sm font-medium text-white">
                                                    ✗ غیرفعال
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3 text-dark dark:text-white">
                                            {{ $template->creator->full_name ?? 'نامشخص' }}
                                        </td>
                                        <td class="px-4 py-3 text-dark-5 dark:text-dark-6">
                                            {{ $template->created_at->format('Y-m-d H:i') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <button 
                                                    wire:click="edit({{ $template->id }})"
                                                    class="inline-flex items-center justify-center rounded-lg bg-blue-600 p-2.5 text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-200"
                                                    title="ویرایش">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                                    </svg>
                                                </button>
                                                <button 
                                                    wire:click="delete({{ $template->id }})"
                                                    onclick="return confirm('آیا مطمئن هستید؟')"
                                                    class="inline-flex items-center justify-center rounded-lg bg-red-600 p-2.5 text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 transition-all duration-200"
                                                    title="حذف">
                                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                    </svg>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center py-12">
                        <svg class="w-16 h-16 text-dark-5 dark:text-dark-6 mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        <h3 class="text-lg font-semibold text-dark dark:text-white mb-2">هیچ الگویی یافت نشد</h3>
                        <p class="text-sm text-dark-5 dark:text-dark-6 mb-6">برای شروع، اولین الگوی فرم خود را ایجاد کنید.</p>
                        <button 
                            wire:click="create"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-blue-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>الگوی جدید</span>
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Tab Content: Create/Edit -->
    <div class="{{ $activeTab === 'create' ? 'block' : 'hidden' }}">
        <div class="rounded-[10px] bg-white shadow-1 dark:bg-gray-dark dark:shadow-card">
            <div class="border-b border-stroke px-7 py-4 dark:border-dark-3">
                <h3 class="text-xl font-semibold text-dark dark:text-white">
                    {{ $editingTemplate ? '✏️ ویرایش فرم' : '➕ ایجاد فرم جدید' }}
                </h3>
            </div>
            <div class="p-7">
                <form wire:submit="save">
                    <div class="grid grid-cols-1 gap-5 md:grid-cols-2">
                        <div class="mb-4.5">
                            <label class="mb-2.5 block text-dark dark:text-white">
                                عنوان فرم <span class="text-danger">*</span>
                            </label>
                            <input 
                                type="text" 
                                wire:model="formData.title"
                                placeholder="مثلاً: فرم کنترل کیفیت"
                                class="w-full rounded-lg border border-stroke bg-transparent py-[15px] px-5 text-dark outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-gray-2 dark:border-dark-3 dark:bg-dark-2 dark:text-white dark:focus:border-primary @error('formData.title') border-danger @enderror"
                            />
                            @error('formData.title')
                                <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="mb-4.5">
                            <label class="mb-2.5 block text-dark dark:text-white">
                                دسته‌بندی <span class="text-danger">*</span>
                            </label>
                            <select 
                                wire:model="formData.category"
                                class="w-full rounded-lg border border-stroke bg-transparent py-[15px] px-5 text-dark outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-gray-2 dark:border-dark-3 dark:bg-dark-2 dark:text-white dark:focus:border-primary @error('formData.category') border-danger @enderror"
                            >
                                <option value="qc">🔍 کنترل کیفیت</option>
                                <option value="inspection">🔎 بازرسی شعبه</option>
                                <option value="production">🏭 تولید</option>
                                <option value="other">📋 سایر</option>
                            </select>
                            @error('formData.category')
                                <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <div class="mb-4.5">
                        <label class="mb-2.5 block text-dark dark:text-white">
                            توضیحات
                        </label>
                        <textarea 
                            wire:model="formData.description"
                            rows="4"
                            placeholder="توضیحات فرم..."
                            class="w-full rounded-lg border border-stroke bg-transparent py-4 px-5 text-dark outline-none transition focus:border-primary active:border-primary disabled:cursor-default disabled:bg-gray-2 dark:border-dark-3 dark:bg-dark-2 dark:text-white dark:focus:border-primary @error('formData.description') border-danger @enderror"
                        ></textarea>
                        @error('formData.description')
                            <p class="mt-1.5 text-sm text-danger">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="mb-6">
                        <label class="flex cursor-pointer select-none items-center">
                            <div class="relative">
                                <input 
                                    type="checkbox" 
                                    wire:model="formData.is_active"
                                    class="sr-only"
                                />
                                <div class="box block h-6 w-12 rounded-full bg-dark-5 transition"></div>
                                <div class="dot absolute top-1 right-1 flex h-4 w-4 items-center justify-center rounded-full bg-white transition"></div>
                            </div>
                            <div class="mr-3 text-sm font-medium text-dark dark:text-white">
                                فعال
                            </div>
                        </label>
                    </div>

                    <div class="border-t border-stroke pt-5 dark:border-dark-3"></div>

                    <div class="flex gap-3">
                        <button 
                            type="submit" 
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-green-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span>ذخیره</span>
                        </button>
                        <button 
                            type="button" 
                            wire:click="switchTab('list')"
                            class="inline-flex items-center justify-center gap-2 rounded-lg bg-gray-600 px-6 py-3 text-base font-semibold text-white shadow-sm hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                            </svg>
                            <span>بازگشت</span>
                        </button>
                    </div>
                </form>

                @if ($editingTemplate)
                    <div class="mt-6 border-t border-stroke pt-4 dark:border-dark-3">
                        <p class="text-sm text-dark-5 dark:text-dark-6">
                            <strong>تاریخ ایجاد:</strong> {{ $editingTemplate->created_at->format('Y-m-d H:i') }}
                            <br>
                            <strong>آخرین تغییر:</strong> {{ $editingTemplate->updated_at->format('Y-m-d H:i') }}
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
