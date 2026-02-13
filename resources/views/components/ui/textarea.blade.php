@props(['disabled' => false])

<textarea {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full rounded-lg border border-slate-200 p-3 text-sm font-medium text-slate-700 placeholder-slate-400 focus:border-primary-500 focus:ring-1 focus:ring-primary-500 focus:outline-none transition-all shadow-none min-h-[100px] resize-none']) !!}>{{ $slot }}</textarea>
