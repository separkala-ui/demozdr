@props(['items' => 3])

<div {{ $attributes->merge(['class' => 'animate-pulse space-y-3']) }}>
    @for($i = 0; $i < $items; $i++)
        <div class="rounded-lg border border-slate-200 bg-white p-4">
            <div class="flex items-start gap-3">
                <div class="h-12 w-12 rounded-full bg-slate-200"></div>
                <div class="flex-1 space-y-2">
                    <div class="h-4 w-3/4 rounded bg-slate-200"></div>
                    <div class="h-3 w-full rounded bg-slate-100"></div>
                    <div class="h-3 w-2/3 rounded bg-slate-100"></div>
                </div>
            </div>
        </div>
    @endfor
</div>

