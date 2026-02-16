@props(['disabled' => false, 'label' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col gap-1']) }}>
    @if($label)
        <label class="text-sm font-medium text-slate-700">
            {{ $label }}
            @if($attributes->has('required'))
                <span class="text-rose-500">*</span>
            @endif
        </label>
    @endif
    
    <input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'w-full rounded-lg p-2 border border-neutral-300 focus:border-primary-500 focus:ring-primary-500']) !!}>
    
    @error($attributes->get('name'))
        <span class="text-xs text-rose-500">{{ $message }}</span>
    @enderror
</div>
