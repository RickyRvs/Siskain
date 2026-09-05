@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-[13px] font-medium text-[#8A8272]']) }}>
    {{ $value ?? $slot }}
</label>