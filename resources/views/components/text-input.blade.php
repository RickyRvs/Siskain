@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full border-0 border-b-2 border-[#E2DAC4] bg-transparent px-0 py-2 text-[#16231D] text-[15px] placeholder:text-[#B4AD9A] focus:border-[#16231D] focus:ring-0 transition-colors']) }}>