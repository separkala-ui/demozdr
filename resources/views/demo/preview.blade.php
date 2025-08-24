<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta http-equiv="X-UA-Compatible" content="ie=edge">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <title>@yield('title', config('app.name'))</title>

        <link rel="icon" href="{{ config('settings.site_favicon') ?? asset('favicon.ico') }}" type="image/x-icon">

        @include('backend.layouts.partials.theme-colors')
        @yield('before_vite_build')

        @livewireStyles
        @viteReactRefresh
        @vite(['resources/js/app.js', 'resources/css/app.css'], 'build')
        @stack('styles')
        @yield('before_head')

        @if (!empty(config('settings.global_custom_css')))
        <style>
            {!! config('settings.global_custom_css') !!}
        </style>
        @endif

        @include('backend.layouts.partials.integration-scripts')

        @php echo ld_apply_filters('admin_head', ''); @endphp
    </head>

    <body>

        <!-- Beautiful Demo Header -->
        <header
            class="sticky top-0 flex w-full items-center justify-between px-6 py-4 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm rounded-b-lg z-10 mb-6"
        >
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-primary-600 dark:text-primary-400 font-bold text-lg hover:underline">
                    <img src="{{ asset('favicon.ico') }}" alt="{{ config('app.name', 'Admin Dashboard') }}" class="h-6 w-6">
                    {{ config('app.name', 'Admin Dashboard') }}
                </a>
                <span class="hidden md:inline-block text-gray-400 dark:text-gray-500 mx-2">|</span>
                <span class="text-base font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Demo Preview') }}
                </span>
            </div>
            <nav class="flex gap-4">
                <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 rounded text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    {{ __('Dashboard') }}
                </a>
                <a href="https://laradashboard.com/docs" target="_blank" class="px-3 py-2 rounded text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    {{ __('Docs') }}
                </a>
            </nav>
        </header>

        <div class="flex flex-col md:flex-row gap-8 p-4 mx-auto max-w-screen-2xl md:p-6 pt-0">
            <!-- Sub-sidebar for component navigation -->
            <aside class="w-full md:w-64 md:min-w-[220px] md:max-w-xs border-r border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-lg shadow-sm h-fit md:sticky md:top-20">
                <nav class="py-6 px-4">
                    <h3 class="font-bold text-lg mb-4">Components</h3>
                    <ul class="space-y-2">
                        <li><a href="#forms-demo" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Forms</a></li>
                        <li><a href="#table-demo" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Table</a></li>
                        <li><a href="#alerts-demo" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Alerts</a></li>
                        <li><a href="#drawer-demo" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Drawer</a></li>
                        <li><a href="#media-demo" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">Media</a></li>
                    </ul>
                </nav>

                <nav class="py-6 px-4">
                    <h3 class="font-bold text-lg mb-4">
                        {{ __('Other usage') }}
                    </h3>
                    <ul class="space-y-2">
                        <li>
                            <a href="#forms-demo" class="block px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800">
                                {{ __('Render a page') }}
                            </a>
                        </li>
                    </ul>
                </nav>
            </aside>

            <div class="flex-1">
                <section id="forms-demo" class="mb-12 px-4 py-5 bg-white dark:bg-gray-800">
                    @include('demo.forms')
                </section>
            </div>
        </div>

        <x-toast-notifications />

        {!! ld_apply_filters('admin_footer_before', '') !!}

        @stack('scripts')

        @if (!empty(config('settings.global_custom_js')))
        <script>
            {!! config('settings.global_custom_js') !!}
        </script>
        @endif

        @livewireScriptConfig
        {!! ld_apply_filters('admin_footer_after', '') !!}
    </body>
</html>