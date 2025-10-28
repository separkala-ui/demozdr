@props(['rows' => 5])

<div {{ $attributes->merge(['class' => 'animate-pulse']) }}>
    <div class="rounded-lg border border-slate-200 bg-white">
        {{-- Header --}}
        <div class="border-b border-slate-200 p-4">
            <div class="flex items-center justify-between">
                <div class="h-6 w-48 rounded bg-slate-200"></div>
                <div class="h-9 w-32 rounded bg-slate-200"></div>
            </div>
        </div>

        {{-- Table Rows --}}
        <div class="divide-y divide-slate-200">
            @for($i = 0; $i < $rows; $i++)
                <div class="flex items-center gap-4 p-4">
                    <div class="h-10 w-10 rounded-full bg-slate-200"></div>
                    <div class="flex-1 space-y-2">
                        <div class="h-4 w-3/4 rounded bg-slate-200"></div>
                        <div class="h-3 w-1/2 rounded bg-slate-100"></div>
                    </div>
                    <div class="h-8 w-24 rounded bg-slate-200"></div>
                </div>
            @endfor
        </div>
    </div>
</div>

