<div
    x-data="{ loading: @js($skeleton ?? false) }"
    class="rounded-md border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]"
>
    <template x-if="loading">
        <x-card.card-skeleton />
    </template>
    <template x-if="!loading">
        <div>
            @isset($header)
                <div class="p-5 space-y-6 sm:p-6 border-b font-semibold {{ $headerClass ?? '' }}">
                    {{ $header }}
                </div>
            @endisset

            <div class="p-5 space-y-6 sm:p-6 {{ isset($footer) ? 'border-b' : '' }} {{ $bodyClass ?? '' }}">
                {{ $slot }}
            </div>

            @isset($footer)
            <div class="p-5 space-y-6 sm:p-6 {{ $footerClass ?? '' }}">
                {{ $footer }}
            </div>
            @endisset
        </div>
    </template>
</div>