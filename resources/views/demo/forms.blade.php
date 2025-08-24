

<h3 class="text-lg mb-3 font-bold p-3">
    {{ __('Form Components') }}
</h3>

<div class="flex flex-col gap-5">
    <!-- Text Inputs -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Text Inputs') }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Use the following component for text inputs: text, email, number, phone, search') }}
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
                {!! ld_render_code_block(resource_path('views/demo/forms/input.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.input')
            </div>
        </div>
    </div>

    <!-- Password Input -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Password Input') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/password.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.password')
            </div>
        </div>
    </div>

    <!-- File Input -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('File Input') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/file-input.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.file-input')
            </div>
        </div>
    </div>

    <!-- Media Selector -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Media Selector') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/media-selector.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.media-selector')
            </div>
        </div>
    </div>

    <!-- Select -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Select') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/select.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.select')
            </div>
        </div>
    </div>

    <!-- Combobox -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Combobox') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/combobox.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.combobox')
            </div>
        </div>
    </div>

    <!-- Checkbox -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Checkbox') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/checkbox.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.checkbox')
            </div>
        </div>
    </div>

    <!-- Radio -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Radio') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/radio.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.radio')
            </div>
        </div>
    </div>

    <!-- Textarea -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Textarea') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/textarea.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.textarea')
            </div>
        </div>
    </div>

    <!-- Date Picker -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Date Picker') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/date-picker.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.date-picker')
            </div>
        </div>
    </div>

    <!-- DateTime Picker -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('DateTime Picker') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/datetime-picker.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.datetime-picker')
            </div>
        </div>
    </div>

    <!-- Range Input -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Range Input') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/range-input.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.range-input')
            </div>
        </div>
    </div>

    <!-- Input Group -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Input Group') }}</h4>
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
                {!! ld_render_code_block(resource_path('views/demo/forms/input-group.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.forms.input-group')
            </div>
        </div>
    </div>
</div>