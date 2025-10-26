@props(['size' => 'md', 'color' => 'indigo'])

@php
    $sizeClasses = [
        'sm' => 'h-4 w-4',
        'md' => 'h-8 w-8',
        'lg' => 'h-12 w-12',
        'xl' => 'h-16 w-16',
    ];
    
    $colorClasses = [
        'indigo' => 'border-indigo-600',
        'blue' => 'border-blue-600',
        'emerald' => 'border-emerald-600',
        'amber' => 'border-amber-600',
        'rose' => 'border-rose-600',
        'slate' => 'border-slate-600',
    ];
    
    $sizeClass = $sizeClasses[$size] ?? $sizeClasses['md'];
    $colorClass = $colorClasses[$color] ?? $colorClasses['indigo'];
@endphp

<div {{ $attributes->merge(['class' => 'flex items-center justify-center']) }}>
    <div class="{{ $sizeClass }} animate-spin rounded-full border-4 border-slate-200 {{ $colorClass }} border-t-transparent"></div>
</div>

