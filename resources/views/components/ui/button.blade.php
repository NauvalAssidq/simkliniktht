@props(['variant' => 'primary', 'type' => 'button'])

@php
    $baseClasses = 'inline-flex items-center justify-center px-4 py-2 rounded-lg text-sm font-bold transition-all focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed';
    
    $variantClasses = match ($variant) {
        'primary'   => 'bg-primary-600 hover:bg-primary-700 text-white focus:ring-primary-500 shadow-sm hover:shadow active:scale-[0.98]',
        'secondary' => 'bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 focus:ring-slate-500',
        'danger'    => 'bg-rose-600 hover:bg-rose-700 text-white focus:ring-rose-500 shadow-sm hover:shadow',
        'ghost'     => 'bg-transparent hover:bg-slate-100 text-slate-600',
        default     => 'bg-primary-600 hover:bg-primary-700 text-white',
    };
@endphp

<button type="{{ $type }}" {{ $attributes->merge(['class' => "$baseClasses $variantClasses"]) }}>
    {{ $slot }}
</button>
