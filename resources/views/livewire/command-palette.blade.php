<div>
    {{-- Backdrop --}}
    <div
        x-data="{ open: @entangle('open') }"
        x-show="open"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0"
        x-transition:enter-end="opacity-100"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100"
        x-transition:leave-end="opacity-0"
        @keydown.escape.window="$wire.close()"
        @keydown.ctrl.k.window.prevent="$wire.open()"
        @keydown.meta.k.window.prevent="$wire.open()"
        class="fixed inset-0 z-[100] flex items-start justify-center overflow-y-auto bg-slate-900/50 px-4 pt-[10vh] backdrop-blur-sm"
        style="display: none;"
    >
        {{-- Dialog --}}
        <div
            @click.away="$wire.close()"
            @keydown.arrow-down.prevent="$wire.selectNext()"
            @keydown.arrow-up.prevent="$wire.selectPrevious()"
            @keydown.enter.prevent="$wire.executeSelected()"
            x-show="open"
            x-transition:enter="transition ease-out duration-200"
            x-transition:enter-start="opacity-0 scale-95 translate-y-4"
            x-transition:enter-end="opacity-100 scale-100 translate-y-0"
            x-transition:leave="transition ease-in duration-150"
            x-transition:leave-start="opacity-100 scale-100"
            x-transition:leave-end="opacity-0 scale-95"
            class="relative mb-20 w-full max-w-2xl overflow-hidden rounded-xl border border-slate-200 bg-white shadow-2xl"
        >
            {{-- Search Input --}}
            <div class="border-b border-slate-200 p-4">
                <div class="relative">
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center pr-3">
                        <iconify-icon icon="lucide:search" class="text-xl text-slate-400"></iconify-icon>
                    </div>
                    <input
                        type="text"
                        wire:model.live.debounce.300ms="search"
                        placeholder="{{ __('جستجو در سیستم...') }}"
                        class="w-full rounded-lg border-0 bg-slate-50 py-3 pr-10 pl-4 text-base text-slate-900 placeholder-slate-400 focus:ring-2 focus:ring-indigo-500"
                        autofocus
                    />
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center gap-1 pl-3">
                        <kbd class="rounded bg-slate-200 px-2 py-1 text-xs font-semibold text-slate-600">ESC</kbd>
                    </div>
                </div>
            </div>

            {{-- Results --}}
            <div class="max-h-96 overflow-y-auto">
                @if(count($results) > 0)
                    <div class="divide-y divide-slate-100">
                        @foreach($results as $index => $result)
                            <a
                                href="{{ $result['url'] }}"
                                wire:click.prevent="selectedIndex = {{ $index }}; executeSelected()"
                                class="group flex items-center gap-4 px-4 py-3 transition-colors
                                    {{ $selectedIndex === $index ? 'bg-indigo-50' : 'hover:bg-slate-50' }}
                                "
                            >
                                {{-- Icon --}}
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg
                                    {{ $selectedIndex === $index ? 'bg-indigo-100' : 'bg-slate-100' }}
                                ">
                                    <iconify-icon 
                                        icon="{{ $result['icon'] }}" 
                                        class="text-xl
                                            {{ $selectedIndex === $index ? 'text-indigo-600' : 'text-slate-600' }}
                                        "
                                    ></iconify-icon>
                                </div>

                                {{-- Content --}}
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate text-sm font-semibold
                                            {{ $selectedIndex === $index ? 'text-indigo-900' : 'text-slate-900' }}
                                        ">
                                            {{ $result['title'] }}
                                        </p>
                                        @if(isset($result['shortcut']))
                                            <kbd class="rounded bg-slate-200 px-1.5 py-0.5 text-[10px] font-semibold text-slate-600">
                                                {{ $result['shortcut'] }}
                                            </kbd>
                                        @endif
                                    </div>
                                    <p class="mt-0.5 truncate text-xs
                                        {{ $selectedIndex === $index ? 'text-indigo-600' : 'text-slate-500' }}
                                    ">
                                        {{ $result['description'] }}
                                    </p>
                                </div>

                                {{-- Category Badge --}}
                                <div class="shrink-0">
                                    <span class="rounded-full px-2 py-1 text-xs font-medium
                                        {{ $selectedIndex === $index 
                                            ? 'bg-indigo-100 text-indigo-700' 
                                            : 'bg-slate-100 text-slate-600' 
                                        }}
                                    ">
                                        {{ $result['category'] }}
                                    </span>
                                </div>

                                {{-- Arrow --}}
                                <div class="shrink-0">
                                    <iconify-icon 
                                        icon="lucide:arrow-left" 
                                        class="text-base
                                            {{ $selectedIndex === $index ? 'text-indigo-400' : 'text-slate-300' }}
                                        "
                                    ></iconify-icon>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @else
                    {{-- Empty State --}}
                    <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                        <iconify-icon icon="lucide:search-x" class="text-5xl"></iconify-icon>
                        <p class="mt-4 text-sm font-medium">{{ __('نتیجه‌ای یافت نشد') }}</p>
                        <p class="mt-1 text-xs">{{ __('کلمه کلیدی دیگری را امتحان کنید') }}</p>
                    </div>
                @endif
            </div>

            {{-- Footer --}}
            <div class="flex items-center justify-between border-t border-slate-200 bg-slate-50 px-4 py-3">
                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <div class="flex items-center gap-1">
                        <kbd class="rounded bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-600 shadow-sm">↑</kbd>
                        <kbd class="rounded bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-600 shadow-sm">↓</kbd>
                        <span>{{ __('انتخاب') }}</span>
                    </div>
                    <div class="flex items-center gap-1">
                        <kbd class="rounded bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-600 shadow-sm">↵</kbd>
                        <span>{{ __('باز کردن') }}</span>
                    </div>
                </div>
                <div class="flex items-center gap-1 text-xs text-slate-500">
                    <span>{{ __('باز کردن با') }}</span>
                    <kbd class="rounded bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-600 shadow-sm">Ctrl</kbd>
                    <span>+</span>
                    <kbd class="rounded bg-white px-1.5 py-0.5 text-[10px] font-semibold text-slate-600 shadow-sm">K</kbd>
                </div>
            </div>
        </div>
    </div>
</div>

