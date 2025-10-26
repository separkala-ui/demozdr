<div class="rounded-lg border border-slate-200 bg-white p-5 shadow-sm">
    <div class="mb-4 flex items-center justify-between">
        <div>
            <h3 class="text-base font-semibold text-slate-800">{{ __('دسترسی سریع') }}</h3>
            <p class="text-xs text-slate-500">{{ __('عملیات پرکاربرد') }}</p>
        </div>
        <iconify-icon icon="lucide:zap" class="text-2xl text-amber-500"></iconify-icon>
    </div>

    <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6">
        @foreach($actions as $action)
            @php
                $colorClasses = [
                    'blue' => 'bg-blue-50 hover:bg-blue-100 text-blue-600 border-blue-200',
                    'purple' => 'bg-purple-50 hover:bg-purple-100 text-purple-600 border-purple-200',
                    'emerald' => 'bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border-emerald-200',
                    'pink' => 'bg-pink-50 hover:bg-pink-100 text-pink-600 border-pink-200',
                    'slate' => 'bg-slate-50 hover:bg-slate-100 text-slate-600 border-slate-200',
                    'amber' => 'bg-amber-50 hover:bg-amber-100 text-amber-600 border-amber-200',
                    'green' => 'bg-green-50 hover:bg-green-100 text-green-600 border-green-200',
                    'indigo' => 'bg-indigo-50 hover:bg-indigo-100 text-indigo-600 border-indigo-200',
                    'red' => 'bg-red-50 hover:bg-red-100 text-red-600 border-red-200',
                    'cyan' => 'bg-cyan-50 hover:bg-cyan-100 text-cyan-600 border-cyan-200',
                    'orange' => 'bg-orange-50 hover:bg-orange-100 text-orange-600 border-orange-200',
                ];
                
                $colorClass = $colorClasses[$action['color']] ?? $colorClasses['slate'];
            @endphp

            @if(isset($action['action']))
                {{-- Livewire Action --}}
                <button
                    type="button"
                    wire:click="{{ $action['action'] }}"
                    class="group flex flex-col items-center justify-center gap-2 rounded-lg border p-4 transition-all hover:shadow-md {{ $colorClass }}"
                    title="{{ $action['description'] }}"
                >
                    <iconify-icon icon="{{ $action['icon'] }}" class="text-3xl"></iconify-icon>
                    <span class="text-center text-xs font-semibold">{{ $action['label'] }}</span>
                </button>
            @elseif(isset($action['url']))
                {{-- Direct URL --}}
                <a
                    href="{{ $action['url'] }}"
                    class="group flex flex-col items-center justify-center gap-2 rounded-lg border p-4 transition-all hover:shadow-md {{ $colorClass }}"
                    title="{{ $action['description'] }}"
                >
                    <iconify-icon icon="{{ $action['icon'] }}" class="text-3xl"></iconify-icon>
                    <span class="text-center text-xs font-semibold">{{ $action['label'] }}</span>
                </a>
            @else
                {{-- Route --}}
                <a
                    href="{{ route($action['route']) }}"
                    class="group flex flex-col items-center justify-center gap-2 rounded-lg border p-4 transition-all hover:shadow-md {{ $colorClass }}"
                    title="{{ $action['description'] }}"
                >
                    <iconify-icon icon="{{ $action['icon'] }}" class="text-3xl"></iconify-icon>
                    <span class="text-center text-xs font-semibold">{{ $action['label'] }}</span>
                </a>
            @endif
        @endforeach
    </div>

    @if(count($actions) === 0)
        <div class="flex flex-col items-center justify-center py-8 text-slate-400">
            <iconify-icon icon="lucide:inbox" class="text-5xl"></iconify-icon>
            <p class="mt-2 text-sm">{{ __('هیچ عملیاتی در دسترس نیست') }}</p>
        </div>
    @endif
</div>

