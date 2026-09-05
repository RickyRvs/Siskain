<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
            <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Riwayat Stok</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-4">

            @if (session('success'))
                <div class="p-4 bg-[#EAF3EE] border border-[#CFE6DA] text-[#2F6F4E] rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-[#FBEAE6] border border-[#F0CFC4] text-[#B5482E] rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            <!-- Kartu ringkasan -->
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white rounded-xl ring-1 ring-[#CFE6DA] shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4 text-[#2F6F4E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19V5m0 0l-6 6m6-6l6 6"/></svg>
                        <p class="text-xs text-[#2F6F4E] uppercase tracking-wide">Stok Masuk Hari Ini</p>
                    </div>
                    <p class="text-2xl font-semibold text-[#1F2A24]">+{{ number_format($stats['today_in'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#F0CFC4] shadow-sm p-5">
                    <div class="flex items-center gap-2 mb-1">
                        <svg class="w-4 h-4 text-[#B5482E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m0 0l-6-6m6 6l6-6"/></svg>
                        <p class="text-xs text-[#B5482E] uppercase tracking-wide">Stok Keluar Hari Ini</p>
                    </div>
                    <p class="text-2xl font-semibold text-[#1F2A24]">-{{ number_format($stats['today_out'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-[#1F2A24] rounded-xl p-5 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-[#D4A73C]/10"></div>
                    <p class="text-xs text-[#B9C2BC] uppercase tracking-wide mb-1">Total Pergerakan Hari Ini</p>
                    <p class="text-2xl font-semibold text-white">{{ $stats['today_count'] }}</p>
                </div>
            </div>

            <!-- Filter bar -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4 flex flex-wrap gap-3 items-center">
                <form method="GET" class="flex flex-wrap gap-2 items-center flex-1">
                    <select name="source_type" class="text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" onchange="this.form.submit()">
                        <option value="">Semua Jenis Item</option>
                        <option value="product" {{ request('source_type') === 'product' ? 'selected' : '' }}>Produk</option>
                        <option value="ingredient" {{ request('source_type') === 'ingredient' ? 'selected' : '' }}>Bahan Baku</option>
                    </select>
                    <select name="item" class="text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" onchange="this.form.submit()">
                        <option value="">Semua Item</option>
                        <optgroup label="Produk">
                            @foreach ($products as $product)
                                <option value="product-{{ $product->id }}" {{ request('item') === 'product-'.$product->id ? 'selected' : '' }}>{{ $product->name }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="Bahan Baku">
                            @foreach ($ingredients as $ingredient)
                                <option value="ingredient-{{ $ingredient->id }}" {{ request('item') === 'ingredient-'.$ingredient->id ? 'selected' : '' }}>{{ $ingredient->name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                    <select name="type" class="text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" onchange="this.form.submit()">
                        <option value="">Semua Tipe</option>
                        <option value="in" {{ request('type') === 'in' ? 'selected' : '' }}>Masuk</option>
                        <option value="out" {{ request('type') === 'out' ? 'selected' : '' }}>Keluar</option>
                    </select>
                    <input type="date" name="date" value="{{ request('date') }}"
                           class="text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" onchange="this.form.submit()">
                    @if (request('source_type') || request('item') || request('type') || request('date'))
                        <a href="{{ route('stock-movements.index') }}" class="text-sm text-[#8A8272] hover:text-[#1F2A24] inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Reset
                        </a>
                    @endif
                </form>
                <a href="{{ route('stock-movements.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] text-sm font-semibold rounded-lg hover:bg-[#E0B559] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Penyesuaian Stok
                </a>
            </div>

            <!-- Tabel -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E7E1D3]">
                        <thead class="bg-[#F6F3EC]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Item</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Jenis</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Tipe</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-[#8A8272] uppercase tracking-wide">Qty</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Catatan</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Oleh</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Tanggal</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-[#8A8272] uppercase tracking-wide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7E1D3]">
                            @forelse ($movements as $movement)
                                <tr class="hover:bg-[#F6F3EC]/60 transition">
                                    <td class="px-6 py-4">
                                        <p class="text-sm font-medium text-[#1F2A24]">{{ $movement->item_name }}</p>
                                        @if ($movement->variant_name)
                                            <p class="text-xs text-[#8A8272]">{{ $movement->variant_name }}</p>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($movement->source_type === 'product')
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[#EFF1EC] text-[#5B5647]">Produk</span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-1 rounded-full text-xs font-medium bg-[#F5EEDD] text-[#8A6A2C]">Bahan Baku</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        @if ($movement->type === 'in')
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-[#EAF3EE] text-[#2F6F4E]">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 19V5m0 0l-6 6m6-6l6 6"/></svg>
                                                Masuk
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium bg-[#FBEAE6] text-[#B5482E]">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 5v14m0 0l-6-6m6 6l6-6"/></svg>
                                                Keluar
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right text-sm font-medium tabular-nums {{ $movement->type === 'in' ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">
                                        {{ $movement->type === 'in' ? '+' : '-' }}{{ $movement->qty }}{{ $movement->unit ? ' '.$movement->unit : '' }}
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#5B5647] max-w-[180px] truncate" title="{{ $movement->note }}">{{ $movement->note ?? '—' }}</td>
                                    <td class="px-6 py-4 text-sm text-[#8A8272]">{{ $movement->user_name }}</td>
<td class="px-6 py-4 text-sm text-[#8A8272]">{{ \Illuminate\Support\Carbon::parse($movement->created_at)->translatedFormat('d M Y, H:i') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('stock-movements.show', $movement->id) }}?source={{ $movement->source_type }}" class="inline-flex items-center gap-1 text-sm text-[#1B6E6E] hover:text-[#144F4F] font-medium">
                                            Detail
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="px-6 py-16 text-center">
                                        <p class="text-sm text-[#8A8272]">Belum ada riwayat stok{{ request('source_type') || request('item') || request('type') || request('date') ? ' yang cocok dengan filter ini' : '' }}.</p>
                                        @if (request('source_type') || request('item') || request('type') || request('date'))
                                            <a href="{{ route('stock-movements.index') }}" class="mt-2 inline-block text-sm text-[#D4A73C] font-medium hover:underline">Hapus filter</a>
                                        @else
                                            <a href="{{ route('stock-movements.create') }}" class="mt-2 inline-block text-sm text-[#D4A73C] font-medium hover:underline">+ Catat penyesuaian stok pertama</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>{{ $movements->links() }}</div>
        </div>
    </div>
</x-app-layout>