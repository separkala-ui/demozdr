<div class="space-y-3">
    @if(count($alerts) === 0)
        <!-- No Alerts - Success State -->
        <div class="flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 p-4">
            <iconify-icon icon="lucide:check-circle" class="text-2xl text-emerald-600"></iconify-icon>
            <div class="flex-1">
                <h4 class="font-semibold text-emerald-800">{{ __('همه چیز عالی است!') }}</h4>
                <p class="mt-1 text-sm text-emerald-700">
                    {{ __('هیچ هشدار یا مشکلی برای این شعبه وجود ندارد.') }}
                </p>
            </div>
        </div>
    @else
        <!-- Alerts List -->
        @foreach($alerts as $alert)
            @php
                $typeClasses = [
                    'danger' => [
                        'border' => 'border-rose-200',
                        'bg' => 'bg-rose-50',
                        'icon' => 'text-rose-600',
                        'title' => 'text-rose-800',
                        'text' => 'text-rose-700',
                        'button' => 'bg-rose-600 hover:bg-rose-700',
                    ],
                    'warning' => [
                        'border' => 'border-amber-200',
                        'bg' => 'bg-amber-50',
                        'icon' => 'text-amber-600',
                        'title' => 'text-amber-800',
                        'text' => 'text-amber-700',
                        'button' => 'bg-amber-600 hover:bg-amber-700',
                    ],
                    'info' => [
                        'border' => 'border-indigo-200',
                        'bg' => 'bg-indigo-50',
                        'icon' => 'text-indigo-600',
                        'title' => 'text-indigo-800',
                        'text' => 'text-indigo-700',
                        'button' => 'bg-indigo-600 hover:bg-indigo-700',
                    ],
                ];
                
                $classes = $typeClasses[$alert['type']] ?? $typeClasses['info'];
            @endphp
            
            <div class="flex items-start gap-3 rounded-lg border {{ $classes['border'] }} {{ $classes['bg'] }} p-4 transition-all hover:shadow-md">
                <iconify-icon icon="{{ $alert['icon'] }}" class="text-2xl {{ $classes['icon'] }}"></iconify-icon>
                
                <div class="flex-1">
                    <h4 class="font-semibold {{ $classes['title'] }}">{{ $alert['title'] }}</h4>
                    <p class="mt-1 text-sm {{ $classes['text'] }}">
                        {{ $alert['message'] }}
                    </p>
                    
                    @if($alert['action'])
                        <div class="mt-3">
                            <a 
                                href="{{ $alert['action']['route'] }}" 
                                class="inline-flex items-center gap-1 rounded-md {{ $classes['button'] }} px-3 py-1.5 text-xs font-semibold text-white transition-colors"
                            >
                                <iconify-icon icon="lucide:arrow-left" class="text-sm"></iconify-icon>
                                {{ $alert['action']['label'] }}
                            </a>
                        </div>
                    @endif
                </div>
                
                <!-- Priority Badge (optional, for debugging) -->
                @if(config('app.debug'))
                    <div class="flex-shrink-0">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-slate-100 text-xs font-semibold text-slate-600">
                            {{ $alert['priority'] }}
                        </span>
                    </div>
                @endif
            </div>
        @endforeach
    @endif
</div>

