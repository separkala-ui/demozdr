@props(['message' => null])

<div {{ $attributes->merge(['class' => 'fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm']) }}>
    <div class="rounded-lg bg-white p-6 shadow-xl">
        <div class="flex flex-col items-center gap-4">
            <x-loading.spinner size="lg" />
            @if($message)
                <p class="text-sm font-medium text-slate-700">{{ $message }}</p>
            @endif
        </div>
    </div>
</div>

