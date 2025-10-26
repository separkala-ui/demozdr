@props(['tabs' => [], 'activeTab' => null])

@php
    $activeTab = $activeTab ?? ($tabs[0]['id'] ?? 'tab1');
@endphp

<div x-data="{ activeTab: '{{ $activeTab }}' }" class="w-full">
    {{-- Tab Navigation --}}
    <div class="border-b border-slate-200 bg-white">
        <div class="flex flex-wrap gap-1 px-2 pt-2" role="tablist">
            @foreach($tabs as $tab)
                <button
                    @click="activeTab = '{{ $tab['id'] }}'"
                    :class="activeTab === '{{ $tab['id'] }}' 
                        ? 'border-indigo-500 bg-indigo-50 text-indigo-700 font-semibold' 
                        : 'border-transparent text-slate-600 hover:bg-slate-50 hover:text-slate-800'"
                    class="flex items-center gap-2 rounded-t-lg border-b-2 px-4 py-2.5 text-sm transition-all"
                    role="tab"
                    :aria-selected="activeTab === '{{ $tab['id'] }}'"
                >
                    @if(isset($tab['icon']))
                        <iconify-icon icon="{{ $tab['icon'] }}" class="text-lg"></iconify-icon>
                    @endif
                    <span>{{ $tab['label'] }}</span>
                    @if(isset($tab['badge']) && $tab['badge'] > 0)
                        <span 
                            :class="activeTab === '{{ $tab['id'] }}' ? 'bg-indigo-500 text-white' : 'bg-slate-200 text-slate-700'"
                            class="flex h-5 min-w-[20px] items-center justify-center rounded-full px-1.5 text-[10px] font-bold"
                        >
                            {{ $tab['badge'] }}
                        </span>
                    @endif
                </button>
            @endforeach
        </div>
    </div>

    {{-- Tab Content --}}
    <div class="bg-slate-50 p-6">
        {{ $slot }}
    </div>
</div>

