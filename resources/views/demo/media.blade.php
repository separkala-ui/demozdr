<h3 class="text-lg mb-3 font-bold p-3">
    {{ __('Media Components') }}
</h3>

<div class="flex flex-col gap-5">
    <!-- Media Modal Demo -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Media Modal') }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Use the following component to open a media modal for selecting files.') }}
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
                {!! ld_render_code_block(resource_path('views/demo/media/media-modal-example.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.media.media-modal-example')
            </div>
        </div>
    </div>

    <!-- Media Selector Demo -->
    <div class="px-4 py-5 bg-white dark:bg-gray-800 rounded-md shadow-sm">
        <div x-data="{ showCode: false }">
            <div class="flex justify-between items-center mb-4">
                <div>
                    <h4 class="text-lg">{{ __('Media Selector Button') }}</h4>
                    <p class="text-sm text-gray-500 dark:text-gray-400">
                        {{ __('Use the following component to select and preview media files.') }}
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
                {!! ld_render_code_block(resource_path('views/demo/media/media-selector-example.blade.php'), 'html') !!}
            </div>
            <div x-show="!showCode">
                @include('demo.media.media-selector-example')
            </div>
        </div>
    </div>
</div>
