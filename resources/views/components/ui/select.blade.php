@props(['disabled' => false])

<select {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full rounded-lg border border-slate-300 p-2.5 text-sm font-medium text-slate-700 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all shadow-sm']) !!}>
    {{ $slot }}
</select>
