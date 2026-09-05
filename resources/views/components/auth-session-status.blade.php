@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'p-3 bg-[#EAF3EE] border border-[#CFE6DA] text-[#2F6F4E] rounded-lg text-sm']) }}>
        {{ $status }}
    </div>
@endif