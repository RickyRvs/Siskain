<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
                <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Detail Riwayat Stok</h2>
            </div>
            <a href="{{ route('stock-movements.index') }}" class="inline-flex items-center gap-1.5 text-sm text-[#8A8272] hover:text-[#1F2A24]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-lg mx-auto sm:px-6 lg:px-8">
            <div class="bg-white border border-[#E7E1D3] rounded-xl shadow-sm overflow-hidden">

                <!-- Header: tipe & qty jadi hero -->
                <div class="px-6 py-6 flex items-center gap-4 border-b border-dashed border-[#E7E1D3]
                            {{ $movement->type === 'in' ? 'bg-[#EAF3EE]' : 'bg-[#FBEAE6]' }}">
                    <div class="w-12 h-12 rounded-full flex items-center justify-center shrink-0
                                {{ $movement->type === 'in' ? 'bg-white text-[#2F6F4E]' : 'bg-white text-[#B5482E]' }}">
                        @if ($movement->type === 'in')
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-6 6m6-6l6 6"/></svg>
                        @else
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m0 0l-6-6m6 6l6-6"/></svg>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs uppercase tracking-wide {{ $movement->type === 'in' ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">
                            {{ $movement->type === 'in' ? 'Stok Masuk' : 'Stok Keluar' }}
                        </p>
                        <p class="text-2xl font-semibold {{ $movement->type === 'in' ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">
                            {{ $movement->type === 'in' ? '+' : '-' }}{{ $movement->qty }}
                            @if ($source === 'ingredient')
                                <span class="text-base font-normal">{{ $movement->ingredient->unit }}</span>
                            @endif
                        </p>
                    </div>
                </div>

                <!-- Detail -->
                <div class="px-6 py-5 space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-[#8A8272]">Jenis Item</span>
                        @if ($source === 'ingredient')
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[#F5EEDD] text-[#8A6A2C]">Bahan Baku</span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[#EFF1EC] text-[#5B5647]">Produk</span>
                        @endif
                    </div>
                    <div class="flex justify-between items-start">
                        <span class="text-[#8A8272]">{{ $source === 'ingredient' ? 'Bahan Baku' : 'Produk' }}</span>
                        <span class="text-right font-medium text-[#1F2A24]">
                            @if ($source === 'ingredient')
                                {{ $movement->ingredient->name }}
                            @else
                                {{ $movement->product->name }}
                                @if ($movement->variant)
                                    <span class="block text-xs font-normal text-[#8A8272]">{{ $movement->variant->name }}</span>
                                @endif
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#8A8272]">Stok saat ini</span>
                        <span class="font-medium text-[#1F2A24]">
                            @if ($source === 'ingredient')
                                {{ $movement->ingredient->stock }} {{ $movement->ingredient->unit }}
                            @else
                                {{ $movement->variant->stock ?? $movement->product->stock }}
                            @endif
                        </span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#8A8272]">Catatan</span>
                        <span class="text-right text-[#1F2A24] max-w-[220px]">{{ $movement->note ?? '—' }}</span>
                    </div>
                    <div class="pt-3 border-t border-[#F0ECE0] flex justify-between">
                        <span class="text-[#8A8272]">Dicatat oleh</span>
                        <span class="font-medium text-[#1F2A24]">{{ $movement->user->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-[#8A8272]">Tanggal</span>
                        <span class="text-[#1F2A24]">{{ $movement->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                </div>

                <div class="px-6 py-3 bg-[#FAF8F2] border-t border-[#F0ECE0]">
                    <p class="text-xs text-[#8A8272]">Riwayat stok bersifat permanen dan tidak dapat diedit atau dihapus. Untuk koreksi, buat penyesuaian baru dengan tipe kebalikannya.</p>
                </div>
            </div>

            <a href="{{ route('stock-movements.index') }}" class="mt-4 inline-flex items-center gap-1.5 text-sm text-[#1F2A24] font-medium hover:text-[#D4A73C]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Riwayat Stok
            </a>
        </div>
    </div>
</x-app-layout>