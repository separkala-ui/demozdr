<div class="space-y-3">
    @if($announcements->isEmpty())
        {{-- خالی - چیزی نمایش نده --}}
    @else
        @foreach($announcements as $announcement)
            @php
                $typeColors = [
                    'info' => ['bg' => 'bg-blue-50', 'border' => 'border-blue-200', 'text' => 'text-blue-800', 'icon_color' => 'text-blue-500'],
                    'success' => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-800', 'icon_color' => 'text-emerald-500'],
                    'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-200', 'text' => 'text-amber-800', 'icon_color' => 'text-amber-500'],
                    'danger' => ['bg' => 'bg-rose-50', 'border' => 'border-rose-200', 'text' => 'text-rose-800', 'icon_color' => 'text-rose-500'],
                ];
                $colors = $typeColors[$announcement->type] ?? $typeColors['info'];
            @endphp

            <div 
                class="announcement-card relative overflow-hidden rounded-lg border {{ $colors['border'] }} {{ $colors['bg'] }} p-4 shadow-sm transition-all hover:shadow-md {{ $announcement->is_pinned ? 'ring-2 ring-offset-2 ' . $colors['border'] : '' }}"
                wire:init="markAsViewed({{ $announcement->id }})"
            >
                <div class="flex items-start gap-3">
                    {{-- آیکون --}}
                    <div class="flex-shrink-0">
                        <iconify-icon 
                            icon="{{ $announcement->icon ?? $announcement->default_icon }}" 
                            class="text-2xl {{ $colors['icon_color'] }}"
                        ></iconify-icon>
                    </div>
                    
                    {{-- محتوا --}}
                    <div class="flex-1 min-w-0">
                        <div class="flex items-start justify-between gap-2">
                            <h4 class="font-bold {{ $colors['text'] }}">
                                @if($announcement->is_pinned)
                                    <iconify-icon icon="lucide:pin" class="inline text-sm"></iconify-icon>
                                @endif
                                {{ $announcement->title }}
                            </h4>
                        </div>
                        
                        <p class="mt-1 text-sm {{ $colors['text'] }} opacity-90">
                            {{ $announcement->content }}
                        </p>
                        
                        @if($announcement->action_url)
                            <a 
                                href="{{ $announcement->action_url }}" 
                                class="mt-2 inline-flex items-center gap-1 text-xs font-semibold {{ $colors['text'] }} hover:underline"
                            >
                                {{ $announcement->action_text ?? __('مشاهده') }}
                                <iconify-icon icon="lucide:arrow-left" class="text-sm"></iconify-icon>
                            </a>
                        @endif

                        {{-- Priority Badge --}}
                        @if($announcement->priority === 'urgent' || $announcement->priority === 'high')
                            <span class="mt-2 inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-[10px] font-bold
                                {{ $announcement->priority === 'urgent' ? 'bg-rose-200 text-rose-900' : 'bg-amber-200 text-amber-900' }}">
                                <iconify-icon icon="lucide:alert-triangle" class="text-xs"></iconify-icon>
                                {{ $announcement->priority === 'urgent' ? __('فوری') : __('مهم') }}
                            </span>
                        @endif
                    </div>

                    {{-- دکمه بستن --}}
                    <button 
                        wire:click="dismiss({{ $announcement->id }})"
                        class="flex-shrink-0 rounded-full p-1 transition-colors hover:bg-white/50"
                        title="{{ __('بستن') }}"
                    >
                        <iconify-icon icon="lucide:x" class="text-lg {{ $colors['text'] }} opacity-60"></iconify-icon>
                    </button>
                </div>

                {{-- Decorative element برای urgent --}}
                @if($announcement->priority === 'urgent')
                    <div class="absolute -right-2 -top-2 h-16 w-16 animate-pulse rounded-full bg-rose-400/20 blur-xl"></div>
                @endif
            </div>
        @endforeach

        {{-- دکمه نمایش همه --}}
        @if($totalCount > 3 && !$showAll)
            <button 
                wire:click="toggleShowAll"
                class="w-full rounded-lg border-2 border-dashed border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700"
            >
                <iconify-icon icon="lucide:chevron-down" class="inline"></iconify-icon>
                {{ __('مشاهده همه اطلاعیه‌ها') }} ({{ $totalCount }})
            </button>
        @elseif($showAll && $totalCount > 3)
            <button 
                wire:click="toggleShowAll"
                class="w-full rounded-lg border-2 border-dashed border-slate-300 px-4 py-2 text-sm font-semibold text-slate-600 transition-colors hover:border-indigo-400 hover:bg-indigo-50 hover:text-indigo-700"
            >
                <iconify-icon icon="lucide:chevron-up" class="inline"></iconify-icon>
                {{ __('نمایش کمتر') }}
            </button>
        @endif
    @endif
</div>
