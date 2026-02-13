@props(['variant' => 'neutral'])

@php
    $classes = match ($variant) {
        'primary' => 'bg-primary-50 text-primary-700 border-primary-100',
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-100',
        'warning' => 'bg-amber-50 text-amber-700 border-amber-100',
        'danger'  => 'bg-rose-50 text-rose-700 border-rose-100',
        default   => 'bg-slate-50 text-slate-600 border-slate-200',
    };
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center px-2.5 py-0.5 rounded text-xs font-bold border $classes"]) }}>
    {{ $slot }}
</span>
