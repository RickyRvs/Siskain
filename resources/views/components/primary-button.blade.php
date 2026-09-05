<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#16231D] text-white text-sm font-semibold rounded-lg hover:bg-[#D4A73C] hover:text-[#16231D] focus:outline-none focus:ring-2 focus:ring-[#D4A73C] focus:ring-offset-2 transition-colors disabled:opacity-50 disabled:pointer-events-none']) }}>
    {{ $slot }}
</button>