<h3 class="text-lg mb-3 font-bold p-3">
    {{ __('Button Components') }}
</h3>

<div class="flex flex-col gap-5">
    <!-- Normal Button -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Normal Button') }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Reusable button component supporting type and custom classes.') }}
                    </p>
                </div>
                <div>
                    <button type="button"
                        class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        @click="showCode = !showCode">
                        <span x-show="!showCode">{{ __('Code') }}</span>
                        <span x-show="showCode">{{ __('Preview') }}</span>
                    </button>
                </div>
            </div>
            <div x-show="showCode">
                {!! ld_render_demo_code_block(resource_path('views/demo/buttons/button.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.buttons.button')
            </div>
        </div>
    </div>
    <!-- Action Buttons -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Action Buttons') }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Common action buttons for CRUD and navigation.') }}
                    </p>
                </div>
                <div>
                    <button type="button"
                        class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        @click="showCode = !showCode">
                        <span x-show="!showCode">{{ __('Code') }}</span>
                        <span x-show="showCode">{{ __('Preview') }}</span>
                    </button>
                </div>
            </div>
            <div x-show="showCode">
                {!! ld_render_demo_code_block(resource_path('views/demo/buttons/action-buttons.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.buttons.action-buttons')
            </div>
        </div>
    </div>

    <!-- Submit Buttons -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Submit Buttons') }}</h4>
                </div>
                <div>
                    <button type="button"
                        class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        @click="showCode = !showCode">
                        <span x-show="!showCode">{{ __('Code') }}</span>
                        <span x-show="showCode">{{ __('Preview') }}</span>
                    </button>
                </div>
            </div>
            <div x-show="showCode">
                {!! ld_render_demo_code_block(resource_path('views/demo/buttons/submit-buttons.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.buttons.submit-buttons')
            </div>
        </div>
    </div>

    <!-- Drawer Close Button -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Drawer Close Button') }}</h4>
                </div>
                <div>
                    <button type="button"
                        class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        @click="showCode = !showCode">
                        <span x-show="!showCode">{{ __('Code') }}</span>
                        <span x-show="showCode">{{ __('Preview') }}</span>
                    </button>
                </div>
            </div>
            <div x-show="showCode">
                {!! ld_render_demo_code_block(resource_path('views/demo/buttons/drawer-close.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.buttons.drawer-close')
            </div>
        </div>
    </div>

    <!-- Action Item Button -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Action Item Button') }}</h4>
                </div>
                <div>
                    <button type="button"
                        class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition"
                        @click="showCode = !showCode">
                        <span x-show="!showCode">{{ __('Code') }}</span>
                        <span x-show="showCode">{{ __('Preview') }}</span>
                    </button>
                </div>
            </div>
            <div x-show="showCode">
                {!! ld_render_demo_code_block(resource_path('views/demo/buttons/action-item.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.buttons.action-item')
            </div>
        </div>
    </div>
</div>
