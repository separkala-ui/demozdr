<h3 class="text-lg mb-3 font-bold p-3">
    {{ __('Alert Components') }}
</h3>



<div class="space-y-6 mb-12">
    <!-- Error Alert -->
    <div x-data="{ showCode: false }" class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div><h4 class="text-lg">Error Alert</h4></div>
            <div>
                <button type="button" class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition" @click="showCode = !showCode">
                    <span x-show="!showCode">Code</span>
                    <span x-show="showCode">Preview</span>
                </button>
            </div>
        </div>
        <div x-show="showCode">
            {!! ld_render_code_block(resource_path('views/demo/alerts/error.blade.php'), 'html') !!}
        </div>
        <div x-show="!showCode">
            @include('demo.alerts.error')
        </div>
    </div>

    <!-- Errors Alert (Validation) -->
    <div x-data="{ showCode: false }" class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div><h4 class="text-lg">Errors Alert (Validation)</h4></div>
            <div>
                <button type="button" class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition" @click="showCode = !showCode">
                    <span x-show="!showCode">Code</span>
                    <span x-show="showCode">Preview</span>
                </button>
            </div>
        </div>
        <div x-show="showCode">
            {!! ld_render_code_block(resource_path('views/demo/alerts/errors.blade.php'), 'html') !!}
        </div>
        <div x-show="!showCode">
            @include('demo.alerts.errors')
        </div>
    </div>

    <!-- Success Alert -->
    <div x-data="{ showCode: false }" class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div><h4 class="text-lg">Success Alert</h4></div>
            <div>
                <button type="button" class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition" @click="showCode = !showCode">
                    <span x-show="!showCode">Code</span>
                    <span x-show="showCode">Preview</span>
                </button>
            </div>
        </div>
        <div x-show="showCode">
            {!! ld_render_code_block(resource_path('views/demo/alerts/success.blade.php'), 'html') !!}
        </div>
        <div x-show="!showCode">
            @include('demo.alerts.success')
        </div>
    </div>

    <!-- Info Alert -->
    <div x-data="{ showCode: false }" class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div><h4 class="text-lg">Info Alert</h4></div>
            <div>
                <button type="button" class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition" @click="showCode = !showCode">
                    <span x-show="!showCode">Code</span>
                    <span x-show="showCode">Preview</span>
                </button>
            </div>
        </div>
        <div x-show="showCode">
            {!! ld_render_code_block(resource_path('views/demo/alerts/info.blade.php'), 'html') !!}
        </div>
        <div x-show="!showCode">
            @include('demo.alerts.info')
        </div>
    </div>

    <!-- Warning Alert -->
    <div x-data="{ showCode: false }" class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div><h4 class="text-lg">Warning Alert</h4></div>
            <div>
                <button type="button" class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition" @click="showCode = !showCode">
                    <span x-show="!showCode">Code</span>
                    <span x-show="showCode">Preview</span>
                </button>
            </div>
        </div>
        <div x-show="showCode">
            {!! ld_render_code_block(resource_path('views/demo/alerts/warning.blade.php'), 'html') !!}
        </div>
        <div x-show="!showCode">
            @include('demo.alerts.warning')
        </div>
    </div>

    <!-- Default Alert -->
    <div x-data="{ showCode: false }" class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div class="flex justify-between items-center mb-4">
            <div><h4 class="text-lg">Default Alert</h4></div>
            <div>
                <button type="button" class="px-3 py-1 rounded bg-gray-100 dark:bg-gray-800 text-xs font-semibold text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-200 dark:hover:bg-gray-700 transition" @click="showCode = !showCode">
                    <span x-show="!showCode">Code</span>
                    <span x-show="showCode">Preview</span>
                </button>
            </div>
        </div>
        <div x-show="showCode">
            {!! ld_render_code_block(resource_path('views/demo/alerts/default.blade.php'), 'html') !!}
        </div>
        <div x-show="!showCode">
            @include('demo.alerts.default')
        </div>
    </div>
</div>
