<div class="rounded-lg border border-slate-200 bg-white shadow-sm">
    <div class="border-b border-slate-200 p-5">
        <div class="flex items-center justify-between">
            <div>
                <h3 class="text-base font-semibold text-slate-800">{{ __('فعالیت‌های اخیر') }}</h3>
                <p class="text-xs text-slate-500">{{ __('آخرین رویدادهای سیستم') }}</p>
            </div>
            <div class="flex items-center gap-2">
                <button 
                    wire:click="loadActivities" 
                    class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                    title="{{ __('بروزرسانی') }}"
                >
                    <iconify-icon icon="lucide:refresh-cw" class="text-lg"></iconify-icon>
                </button>
                @if(auth()->user()->can('action-log-view'))
                    <a 
                        href="{{ route('admin.action-logs.index') }}" 
                        class="rounded-md p-2 text-slate-500 hover:bg-slate-100 hover:text-slate-700"
                        title="{{ __('مشاهده همه') }}"
                    >
                        <iconify-icon icon="lucide:arrow-left" class="text-lg"></iconify-icon>
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="p-5">
        @if(count($activities) > 0)
            <div class="relative">
                {{-- Timeline Line --}}
                <div class="absolute right-4 top-0 bottom-0 w-0.5 bg-slate-200"></div>

                {{-- Activities --}}
                <div class="space-y-4">
                    @foreach($activities as $activity)
                        @php
                            $colorClasses = [
                                'blue' => 'bg-blue-100 text-blue-600',
                                'slate' => 'bg-slate-100 text-slate-600',
                                'green' => 'bg-green-100 text-green-600',
                                'amber' => 'bg-amber-100 text-amber-600',
                                'red' => 'bg-red-100 text-red-600',
                                'emerald' => 'bg-emerald-100 text-emerald-600',
                                'rose' => 'bg-rose-100 text-rose-600',
                                'cyan' => 'bg-cyan-100 text-cyan-600',
                                'orange' => 'bg-orange-100 text-orange-600',
                                'purple' => 'bg-purple-100 text-purple-600',
                                'indigo' => 'bg-indigo-100 text-indigo-600',
                                'teal' => 'bg-teal-100 text-teal-600',
                            ];
                            
                            $colorClass = $colorClasses[$activity['color']] ?? $colorClasses['slate'];
                        @endphp

                        <div class="relative flex gap-4 pr-12">
                            {{-- Icon --}}
                            <div class="absolute right-0 z-10 flex h-8 w-8 items-center justify-center rounded-full border-2 border-white {{ $colorClass }}">
                                <iconify-icon icon="{{ $activity['icon'] }}" class="text-base"></iconify-icon>
                            </div>

                            {{-- Content --}}
                            <div class="flex-1 rounded-lg border border-slate-200 bg-slate-50 p-3 transition-all hover:shadow-sm">
                                <div class="flex items-start justify-between gap-2">
                                    <div class="flex-1">
                                        <p class="text-sm font-medium text-slate-800">
                                            {{ $activity['title'] ?? __('فعالیت') }}
                                        </p>
                                        <div class="mt-1 flex items-center gap-2 text-xs text-slate-500">
                                            <span class="flex items-center gap-1">
                                                <iconify-icon icon="lucide:user" class="text-xs"></iconify-icon>
                                                {{ $activity['user_name'] }}
                                            </span>
                                            <span>•</span>
                                            <span 
                                                class="flex items-center gap-1" 
                                                title="{{ $activity['time_full'] }}"
                                            >
                                                <iconify-icon icon="lucide:clock" class="text-xs"></iconify-icon>
                                                {{ $activity['time'] }}
                                            </span>
                                        </div>
                                    </div>

                                    {{-- Type Badge --}}
                                    <span class="shrink-0 rounded-full px-2 py-0.5 text-[10px] font-semibold uppercase {{ $colorClass }}">
                                        {{ __($activity['type']) }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- View All Link --}}
            @if(auth()->user()->can('action-log-view'))
                <div class="mt-4 text-center">
                    <a 
                        href="{{ route('admin.action-logs.index') }}" 
                        class="inline-flex items-center gap-1 text-sm font-semibold text-indigo-600 hover:text-indigo-700"
                    >
                        {{ __('مشاهده تمام فعالیت‌ها') }}
                        <iconify-icon icon="lucide:arrow-left" class="text-base"></iconify-icon>
                    </a>
                </div>
            @endif
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-12 text-slate-400">
                <iconify-icon icon="lucide:activity" class="text-6xl"></iconify-icon>
                <p class="mt-4 text-sm font-medium">{{ __('هیچ فعالیتی ثبت نشده است') }}</p>
                <p class="mt-1 text-xs">{{ __('فعالیت‌ها به صورت خودکار ثبت می‌شوند') }}</p>
            </div>
        @endif
    </div>
</div>

