<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center gap-2 px-5 py-2.5 bg-[#1F2A24] text-white text-sm font-semibold rounded-lg hover:bg-[#16201B] focus:outline-none focus:ring-2 focus:ring-[#D4A73C] focus:ring-offset-2 transition disabled:opacity-50 disabled:pointer-events-none']) }}>
    {{ $slot }}
</button>