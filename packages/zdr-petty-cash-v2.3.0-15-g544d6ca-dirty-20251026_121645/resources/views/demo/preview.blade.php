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

        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/styles/github-dark.min.css" />
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/highlight.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/php.min.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.9.0/languages/markdown.min.js"></script>
        <script>document.addEventListener('DOMContentLoaded', function() { document.querySelectorAll('pre code').forEach((el) => { hljs.highlightElement(el); }); });</script>

        <style>
            pre code.hljs {
                border-radius: 6px;
                font-size: 12px;
            }
        </style>

        @php
            if (!function_exists('ld_render_demo_code_block')) {
                function ld_render_demo_code_block($input, $lang = 'php', $isFile = true) {
                    if ($isFile) {
                        $code = file_exists($input) ? file_get_contents($input) : '';
                    } else {
                        $code = $input;
                    }
                    $escaped = htmlspecialchars($code);
                    $id = 'codeblock_' . uniqid();
                    return '
                        <div class="relative mb-2 group">
                            <button type="button" class="absolute right-2 top-2 px-2 py-1 text-xs bg-gray-200 dark:bg-gray-700 rounded hover:bg-gray-300 dark:hover:bg-gray-600 transition copy-btn hidden group-hover:block" data-target="' . $id . '">Copy</button>
                            <pre class="border rounded-md border-gray-100 dark:border-gray-800"><code id="' . $id . '" class="language-' . $lang . '">' . $escaped . '</code></pre>
                        </div>
                    ';
                }
            }
        @endphp
    </head>

    <body>
        <header class="sticky top-0 flex w-full items-center justify-between px-6 py-4 bg-white dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800 shadow-sm rounded-b-lg z-10 mb-6">
            <div class="flex items-center gap-3">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-2 text-primary-600 dark:text-primary-400 font-bold text-lg hover:underline">
                    <img src="{{ asset('favicon.ico') }}" alt="{{ config('app.name', 'Admin Dashboard') }}" class="h-6 w-6">
                    {{ config('app.name', 'Admin Dashboard') }}
                </a>
                <span class="hidden md:inline-block text-gray-400 dark:text-gray-500 mx-2">|</span>
                <span class="hidden md:inline-block text-base font-medium text-gray-700 dark:text-gray-300">
                    {{ __('Demo Preview') }}
                </span>
            </div>
            <nav class="flex gap-4">
                <a href="{{ route('admin.dashboard') }}" class="flex justify-center gap-1 items-center px-3 py-2 rounded text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    {{ __('Dashboard') }}
                    <iconify-icon icon="lucide:layout-dashboard" class="inline-block ml-1" />
                </a>
                <a href="https://laradashboard.com/docs" target="_blank" class="flex justify-center gap-1 items-center px-3 py-2 rounded text-sm font-semibold text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-800 transition">
                    {{ __('Docs') }}
                    <iconify-icon icon="lucide:external-link" class="inline-block ml-1" />
                </a>
            </nav>
        </header>

        <div class="flex flex-col md:flex-row gap-8 p-4 mx-auto max-w-screen-2xl md:p-6 pt-0">
            <aside class="w-full md:w-64 md:min-w-[220px] md:max-w-xs border-r border-gray-100 dark:border-gray-800 bg-white dark:bg-gray-900 rounded-lg shadow-sm h-fit md:sticky md:top-20">
                <nav class="py-6 px-4">
                    <h3 class="font-bold mb-4">{{ __('Components') }}</h3>
                    <ul class="space-y-2">
                        <li><a href="#forms-demo" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-sm"><iconify-icon icon="lucide:form-input" class="inline-block mr-2"></iconify-icon> {{ __('Forms') }}</a></li>
                        <li><a href="#dropdown-demo" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-sm"><iconify-icon icon="lucide:chevron-down" class="inline-block mr-2"></iconify-icon> {{ __('Dropdown') }}</a></li>
                        <li><a href="#alerts-demo" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-sm"><iconify-icon icon="lucide:alert-circle" class="inline-block mr-2"></iconify-icon> {{ __('Alerts') }}</a></li>
                        <li><a href="#buttons-demo" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-sm"><iconify-icon icon="lucide:mouse-pointer-click" class="inline-block mr-2"></iconify-icon> {{ __('Buttons') }}</a></li>
                        <li><a href="#media-demo" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-sm"><iconify-icon icon="lucide:image" class="inline-block mr-2"></iconify-icon> {{ __('Media') }}</a></li>
                        <li><a href="#card-demo" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-sm"><iconify-icon icon="lucide:square" class="inline-block mr-2"></iconify-icon> {{ __('Card') }}</a></li>
                        <li><a href="#datatable-demo" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-sm"><iconify-icon icon="lucide:table" class="inline-block mr-2"></iconify-icon> {{ __('Datatable') }}</a></li>
                        <li><a href="#modal-demo" class="sidebar-link flex items-center gap-2 px-3 py-2 rounded hover:bg-gray-100 dark:hover:bg-gray-800 text-sm"><iconify-icon icon="lucide:expand" class="inline-block mr-2"></iconify-icon> {{ __('Modal') }}</a></li>
                    </ul>
                </nav>
            </aside>

            <div class="flex-1 preview-content">
                <section id="forms-demo" class="scroll-mt-20">
                    @include('demo.forms')
                </section>
                <section id="dropdown-demo" class="scroll-mt-20 mt-10">
                    @include('demo.dropdown')
                </section>
                <section id="alerts-demo" class="scroll-mt-20 mt-10">
                    @include('demo.alerts')
                </section>
                <section id="buttons-demo" class="scroll-mt-20 mt-10">
                    @include('demo.buttons')
                </section>
                <section id="media-demo" class="scroll-mt-20 mt-10">
                    @include('demo.media')
                </section>
                <section id="card-demo" class="scroll-mt-20 mt-10">
                    @include('demo.card')
                </section>
                <section id="datatable-demo" class="scroll-mt-20 mt-10">
                    @include('demo.datatable')
                </section>
                <section id="modal-demo" class="scroll-mt-20 mt-10">
                    @include('demo.modal')
                </section>
            </div>
        </div>

        <x-toast-notifications />

        @stack('scripts')

        @if (!empty(config('settings.global_custom_js')))
        <script>
            {!! config('settings.global_custom_js') !!}
        </script>
        @endif

        <script>
        document.addEventListener('DOMContentLoaded', function() {
            const sectionIds = ['forms-demo', 'dropdown-demo', 'alerts-demo', 'buttons-demo', 'media-demo', 'card-demo', 'datatable-demo', 'modal-demo', 'drawer-demo'];
            const sidebarLinks = Array.from(document.querySelectorAll('.sidebar-link'));

            function setActiveSidebar(hash) {
                sidebarLinks.forEach(function(link) {
                    if (link.getAttribute('href') === hash) {
                        link.classList.add('bg-gray-200', 'dark:bg-gray-700', 'font-bold');
                    } else {
                        link.classList.remove('bg-gray-200', 'dark:bg-gray-700', 'font-bold');
                    }
                });
            }

            function updateSidebarOnScroll() {
                const scrollPosition = window.scrollY || window.pageYOffset;
                const offset = 120; // adjust for sticky header
                let found = false;
                for (let i = sectionIds.length - 1; i >= 0; i--) {
                    const section = document.getElementById(sectionIds[i]);
                    if (section) {
                        const rect = section.getBoundingClientRect();
                        const top = rect.top + window.scrollY - offset;
                        if (scrollPosition >= top) {
                            setActiveSidebar('#' + sectionIds[i]);
                            history.replaceState(null, '', '#' + sectionIds[i]);
                            found = true;
                            break;
                        }
                    }
                }
                if (!found) {
                    setActiveSidebar('');
                }
            }

            window.addEventListener('scroll', updateSidebarOnScroll);
            window.addEventListener('resize', updateSidebarOnScroll);
            window.addEventListener('hashchange', function() {
                setActiveSidebar(window.location.hash);
            });
            // Initial highlight
            setActiveSidebar(window.location.hash);
            updateSidebarOnScroll();

            // Copy button logic
            document.querySelectorAll('.copy-btn').forEach(function(btn) {
                btn.addEventListener('click', function() {
                    var targetId = btn.getAttribute('data-target');
                    var code = document.getElementById(targetId);
                    if (code) {
                        var text = code.innerText;
                        navigator.clipboard.writeText(text).then(function() {
                            btn.textContent = 'Copied!';
                            setTimeout(function() { btn.textContent = 'Copy'; }, 1200);
                        });
                    }
                });
            });
        });
        </script>

        @livewireScriptConfig
    </body>
</html>