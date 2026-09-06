<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Produk & Stok</h2>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 p-4 bg-[#EAF3EE] text-[#2F6F4E] rounded-lg">{{ session('success') }}</div>
            @endif

            {{-- Statistik ringkas --}}
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                <div class="bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-xl p-5">
                    <p class="text-xs font-medium text-[#8A8272] uppercase">Total Produk</p>
                    <p class="text-2xl font-semibold text-[#1F2A24] mt-1">{{ $totalProducts }}</p>
                </div>
                <div class="bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-xl p-5">
                    <p class="text-xs font-medium text-[#8A8272] uppercase">Stok Menipis</p>
                    <p class="text-2xl font-semibold {{ $lowStockCount > 0 ? 'text-[#B5482E]' : 'text-[#1F2A24]' }} mt-1">{{ $lowStockCount }}</p>
                </div>
                <div class="bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-xl p-5">
                    <p class="text-xs font-medium text-[#8A8272] uppercase">Nilai Stok (Modal)</p>
                    <p class="text-2xl font-semibold text-[#1F2A24] mt-1">Rp {{ number_format($totalStockValue, 0, ',', '.') }}</p>
                </div>
            </div>

            {{-- Filter --}}
            <form method="GET" class="mb-5 flex flex-wrap gap-2 items-center bg-white ring-1 ring-[#E7E1D3] rounded-xl p-4">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari produk..." class="w-full sm:w-auto rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C] text-sm">

                <select name="category_id" onchange="this.form.submit()" class="w-full sm:w-auto rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C] text-sm">
                    <option value="">Semua Kategori</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>

                <label class="flex items-center gap-1.5 text-sm text-[#1F2A24]">
                    <input type="checkbox" name="low_stock" value="1" {{ request('low_stock') ? 'checked' : '' }} onchange="this.form.submit()" class="rounded border-[#E7E1D3] text-[#D4A73C] focus:ring-[#D4A73C]">
                    Stok menipis
                </label>

                <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#F6F3EC] text-[#1F2A24] rounded-lg border border-[#E7E1D3] hover:bg-[#EFEAE0] transition text-sm">Cari</button>

                @if (request()->anyFilled(['search', 'category_id', 'low_stock']))
                    <a href="{{ route('products.index') }}" class="text-sm text-[#8A8272] hover:underline">Reset</a>
                @endif

                <button
                    type="button"
                    x-data
                    @click="$dispatch('open-modal', 'create-product')"
                    class="w-full sm:w-auto sm:ml-auto px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] font-semibold rounded-lg hover:bg-[#E0B559] transition text-sm"
                >
                    + Tambah Produk
                </button>
            </form>

            {{-- Grid produk --}}
            @if ($products->isEmpty())
                <div class="bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-xl p-10 text-center text-[#8A8272]">
                    Belum ada produk yang cocok.
                </div>
            @else
                <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-3">
                    @foreach ($products as $product)
                        <div class="bg-white ring-1 ring-[#E7E1D3] shadow-sm rounded-lg overflow-hidden flex flex-col">
                            <div class="aspect-[4/3] bg-[#F6F3EC] flex items-center justify-center overflow-hidden">
                                @if ($product->photo)
                                    <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}" class="w-full h-full object-cover">
                                @else
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-[#C9C2AF]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5V7.5A1.5 1.5 0 0 1 4.5 6h15A1.5 1.5 0 0 1 21 7.5v9a1.5 1.5 0 0 1-1.5 1.5h-15A1.5 1.5 0 0 1 3 16.5Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="m3 15 4.5-4.5a2 2 0 0 1 2.83 0L15 15m-3-3 1.5-1.5a2 2 0 0 1 2.83 0L21 15" />
                                        <circle cx="8" cy="9.5" r="1.25" />
                                    </svg>
                                @endif
                            </div>

                            <div class="p-3 flex flex-col flex-1">
                                <div class="flex items-start justify-between gap-1.5">
                                    <h3 class="text-sm font-medium text-[#1F2A24] leading-snug line-clamp-1">{{ $product->name }}</h3>
                                    @if ($product->has_variant)
                                        <span class="shrink-0 text-[9px] font-medium bg-[#FBF0D9] text-[#B5842A] px-1.5 py-0.5 rounded-full">Varian</span>
                                    @endif
                                </div>
                                <p class="text-[11px] text-[#8A8272] mt-0.5">{{ $product->category->name }}</p>

                                <p class="text-sm font-semibold text-[#1F2A24] mt-1.5">Rp {{ number_format($product->price_jual, 0, ',', '.') }}</p>

                                <div class="flex items-center justify-between mt-1.5">
                                    @if ($product->tracks_stock)
                                        <span class="text-[10px] {{ $product->isLowStock() ? 'bg-[#FBEAE4] text-[#B5482E]' : 'bg-[#F6F3EC] text-[#1F2A24]' }} px-1.5 py-0.5 rounded-full font-medium">
                                            Stok: {{ $product->stock }}
                                        </span>
                                    @else
                                        <span class="text-[10px] bg-[#EAF3EE] text-[#2F6F4E] px-1.5 py-0.5 rounded-full font-medium">
                                            Tanpa Stok
                                        </span>
                                    @endif
                                    @if ($product->sku)
                                        <span class="text-[9px] text-[#8A8272] truncate">{{ $product->sku }}</span>
                                    @endif
                                </div>

                                @if ($product->ingredients->isNotEmpty())
                                    <p class="text-[9px] text-[#8A8272] mt-1 truncate" title="{{ $product->ingredients->pluck('name')->join(', ') }}">
                                        Resep: {{ $product->ingredients->pluck('name')->join(', ') }}
                                    </p>
                                @endif

                                <div class="mt-2 pt-2 border-t border-[#E7E1D3] flex items-center justify-between text-xs">
                                    <div class="space-x-2">
                                        @if ($product->has_variant)
                                            <a href="{{ route('products.variants.index', $product) }}" class="text-[#B5842A] hover:underline">Varian</a>
                                        @endif
                                        <button
                                            type="button"
                                            x-data
                                            @click="$dispatch('open-modal', 'edit-product-{{ $product->id }}')"
                                            class="text-[#1B6E6E] hover:underline"
                                        >
                                            Edit
                                        </button>
                                    </div>
                                    <form action="{{ route('products.destroy', $product) }}" method="POST" onsubmit="return confirm('Hapus produk ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="text-[#B5482E] hover:underline">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Modal Edit Produk (satu per kartu) --}}
                        <x-modal name="edit-product-{{ $product->id }}" max-width="xl">
                            <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7E1D3]">
                                <h3 class="font-semibold text-[#1F2A24]">Edit Produk</h3>
                                <button type="button" x-data @click="$dispatch('close-modal', 'edit-product-{{ $product->id }}')" class="text-[#8A8272] hover:text-[#1F2A24]">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>

                            <form
                                action="{{ route('products.update', $product) }}"
                                method="POST"
                                enctype="multipart/form-data"
                                class="p-4 sm:p-6 max-h-[75vh] overflow-y-auto"
                                x-data="{
                                    tracksStock: {{ $product->tracks_stock ? 'true' : 'false' }},
                                    rows: {{ json_encode($product->ingredients->map(fn ($i) => ['ingredient_id' => $i->id, 'qty_used' => (float) $i->pivot->qty_used])->values()) }},
                                    priceModalDisplay: '',
                                    priceJualDisplay: '',
                                    formatRupiah(value) {
                                        let angka = String(value).replace(/\D/g, '');
                                        if (!angka) return '';
                                        return new Intl.NumberFormat('id-ID').format(angka);
                                    },
                                    unformatRupiah(value) {
                                        return String(value).replace(/\D/g, '') || '0';
                                    },
                                    init() {
                                        this.priceModalDisplay = this.formatRupiah('{{ (int) $product->price_modal }}');
                                        this.priceJualDisplay = this.formatRupiah('{{ (int) $product->price_jual }}');
                                    }
                                }"
                            >
                                @csrf @method('PUT')

                                @if ($product->photo)
                                    <img src="{{ Storage::url($product->photo) }}" class="w-24 h-24 object-cover rounded-lg ring-1 ring-[#E7E1D3] mb-4">
                                @endif

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Kategori</label>
                                    <select name="category_id" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                                        @foreach ($categories as $category)
                                            <option value="{{ $category->id }}" {{ $product->category_id == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Nama Produk</label>
                                    <input type="text" name="name" value="{{ $product->name }}" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Kode Produk (opsional)</label>
                                    <input type="text" name="sku" value="{{ $product->sku }}" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                </div>

                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Ganti Foto (opsional)</label>
                                    <input type="file" name="photo" class="w-full text-sm text-[#1F2A24] file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-[#F6F3EC] file:text-[#1F2A24] hover:file:bg-[#EFEAE0]">
                                </div>

                                <div class="grid grid-cols-2 gap-4 mb-4">
                                    <div>
                                        <label class="block text-sm font-medium text-[#1F2A24] mb-1">Harga Modal</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8A8272] text-sm pointer-events-none">Rp</span>
                                            <input
                                                type="text"
                                                inputmode="numeric"
                                                x-model="priceModalDisplay"
                                                @input="priceModalDisplay = formatRupiah($event.target.value)"
                                                class="w-full pl-9 rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                                                required
                                            >
                                        </div>
                                        <input type="hidden" name="price_modal" :value="unformatRupiah(priceModalDisplay)">
                                        @error('price_modal') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                    <div>
                                        <label class="block text-sm font-medium text-[#1F2A24] mb-1">Harga Jual</label>
                                        <div class="relative">
                                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8A8272] text-sm pointer-events-none">Rp</span>
                                            <input
                                                type="text"
                                                inputmode="numeric"
                                                x-model="priceJualDisplay"
                                                @input="priceJualDisplay = formatRupiah($event.target.value)"
                                                class="w-full pl-9 rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                                                required
                                            >
                                        </div>
                                        <input type="hidden" name="price_jual" :value="unformatRupiah(priceJualDisplay)">
                                        @error('price_jual') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
                                    </div>
                                </div>

                                {{-- Toggle: produk ini punya stok sendiri atau tidak --}}
                                <div class="mb-4 p-3 rounded-lg border border-[#E7E1D3] bg-[#FAF8F2]">
                                    <label class="flex items-center gap-2 text-sm font-medium text-[#1F2A24]">
                                        <input type="checkbox" name="tracks_stock" value="1" x-model="tracksStock" class="rounded border-[#E7E1D3] text-[#D4A73C] focus:ring-[#D4A73C]">
                                        Produk ini melacak stok sendiri
                                    </label>
                                    <p class="text-xs text-[#8A8272] mt-1">
                                        Matikan untuk produk kayak Es Teh yang dibikin on-demand (gak ada batas stok produknya sendiri).
                                        Kalau produk ini butuh bahan baku, atur di bagian "Resep Bahan Baku" di bawah.
                                    </p>
                                </div>

                                <div class="mb-6" x-show="tracksStock" x-cloak>
                                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Stok Minimum</label>
                                    <input type="number" name="min_stock" value="{{ $product->min_stock }}" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" :required="tracksStock">
                                    <p class="text-xs text-[#8A8272] mt-1">Stok saat ini: {{ $product->stock }} (ubah lewat menu Stok, bukan di sini)</p>
                                </div>

                                {{-- Resep bahan baku --}}
                                <div class="mb-6 p-3 rounded-lg border border-[#E7E1D3]">
                                    <p class="text-sm font-medium text-[#1F2A24] mb-1">Resep Bahan Baku (opsional)</p>
                                    <p class="text-xs text-[#8A8272] mb-3">
                                        Tiap 1 unit produk ini terjual, stok bahan baku yang didaftarkan di sini otomatis kepotong.
                                    </p>

                                    <template x-for="(row, index) in rows" :key="index">
                                        <div class="flex flex-col sm:flex-row gap-2 mb-2">
                                            <select :name="`ingredients[${index}][ingredient_id]`" x-model="row.ingredient_id"
                                                    class="w-full sm:flex-1 text-sm rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                                <option value="">-- Pilih Bahan --</option>
                                                @foreach ($ingredients as $ingredient)
                                                    <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit }})</option>
                                                @endforeach
                                            </select>
                                            <div class="flex gap-2">
                                                <input type="number" step="0.01" min="0.01" :name="`ingredients[${index}][qty_used]`" x-model="row.qty_used"
                                                       placeholder="Qty/unit" class="flex-1 sm:flex-none sm:w-28 text-sm rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                                <button type="button" x-on:click="rows.splice(index, 1)" class="shrink-0 px-2 py-2 text-[#B94A3D] hover:bg-[#FBEAE6] rounded-lg">&times;</button>
                                            </div>
                                        </div>
                                    </template>

                                    <button type="button" x-on:click="rows.push({ ingredient_id: '', qty_used: '' })"
                                            class="text-sm text-[#1B6E6E] hover:underline font-medium">+ Tambah Bahan</button>
                                </div>

                                <div class="flex flex-col sm:flex-row justify-end gap-2">
                                    <button type="button" x-data @click="$dispatch('close-modal', 'edit-product-{{ $product->id }}')" class="px-4 py-2 bg-[#F6F3EC] text-[#1F2A24] rounded-lg border border-[#E7E1D3] hover:bg-[#EFEAE0] transition">Batal</button>
                                    <button type="submit" class="px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] font-semibold rounded-lg hover:bg-[#E0B559] transition">Update</button>
                                </div>
                            </form>
                        </x-modal>
                    @endforeach
                </div>

                <div class="mt-6">{{ $products->links() }}</div>
            @endif

        </div>
    </div>

    {{-- Modal Tambah Produk --}}
    <x-modal name="create-product" max-width="xl">
        <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7E1D3]">
            <h3 class="font-semibold text-[#1F2A24]">Tambah Produk</h3>
            <button type="button" x-data @click="$dispatch('close-modal', 'create-product')" class="text-[#8A8272] hover:text-[#1F2A24]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>

        <form
            action="{{ route('products.store') }}"
            method="POST"
            enctype="multipart/form-data"
            class="p-4 sm:p-6 max-h-[75vh] overflow-y-auto"
            x-data="{
                tracksStock: {{ old('tracks_stock', true) ? 'true' : 'false' }},
                rows: [],
                priceModalDisplay: '',
                priceJualDisplay: '',
                formatRupiah(value) {
                    let angka = String(value).replace(/\D/g, '');
                    if (!angka) return '';
                    return new Intl.NumberFormat('id-ID').format(angka);
                },
                unformatRupiah(value) {
                    return String(value).replace(/\D/g, '') || '0';
                },
                init() {
                    this.priceModalDisplay = this.formatRupiah('{{ old('price_modal', 0) }}');
                    this.priceJualDisplay = this.formatRupiah('{{ old('price_jual', 0) }}');
                }
            }"
        >
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1">Kategori</label>
                <select name="category_id" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                    <option value="">-- Pilih Kategori --</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                    @endforeach
                </select>
                @error('category_id') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1">Nama Produk</label>
                <input type="text" name="name" value="{{ old('name') }}" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                @error('name') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1">Kode Produk (opsional)</label>
                <input type="text" name="sku" value="{{ old('sku') }}" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                @error('sku') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-[#1F2A24] mb-1">Foto Produk</label>
                <input type="file" name="photo" class="w-full text-sm text-[#1F2A24] file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-[#F6F3EC] file:text-[#1F2A24] hover:file:bg-[#EFEAE0]">
                @error('photo') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-4">
                <div>
                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Harga Modal</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8A8272] text-sm pointer-events-none">Rp</span>
                        <input
                            type="text"
                            inputmode="numeric"
                            x-model="priceModalDisplay"
                            @input="priceModalDisplay = formatRupiah($event.target.value)"
                            class="w-full pl-9 rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                            required
                        >
                    </div>
                    <input type="hidden" name="price_modal" :value="unformatRupiah(priceModalDisplay)">
                    @error('price_modal') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Harga Jual</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8A8272] text-sm pointer-events-none">Rp</span>
                        <input
                            type="text"
                            inputmode="numeric"
                            x-model="priceJualDisplay"
                            @input="priceJualDisplay = formatRupiah($event.target.value)"
                            class="w-full pl-9 rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                            required
                        >
                    </div>
                    <input type="hidden" name="price_jual" :value="unformatRupiah(priceJualDisplay)">
                    @error('price_jual') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Toggle: produk ini punya stok sendiri atau tidak --}}
            <div class="mb-4 p-3 rounded-lg border border-[#E7E1D3] bg-[#FAF8F2]">
                <label class="flex items-center gap-2 text-sm font-medium text-[#1F2A24]">
                    <input type="checkbox" name="tracks_stock" value="1" x-model="tracksStock" class="rounded border-[#E7E1D3] text-[#D4A73C] focus:ring-[#D4A73C]">
                    Produk ini melacak stok sendiri
                </label>
                <p class="text-xs text-[#8A8272] mt-1">
                    Aktifkan untuk produk fisik kayak Aqua botol (stoknya dikurangi tiap terjual, ada alert stok menipis).
                    Matikan untuk produk yang dibikin on-demand kayak Es Teh — kalau produk ini butuh bahan baku,
                    atur di bagian "Resep Bahan Baku" di bawah.
                </p>
                @error('tracks_stock') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-2 gap-4 mb-6" x-show="tracksStock" x-cloak>
                <div>
                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Stok Awal</label>
                    <input type="number" name="stock" value="{{ old('stock', 0) }}" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" :required="tracksStock">
                    @error('stock') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-[#1F2A24] mb-1">Stok Minimum</label>
                    <input type="number" name="min_stock" value="{{ old('min_stock', 0) }}" class="w-full rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" :required="tracksStock">
                    @error('min_stock') <p class="text-[#B5482E] text-sm mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            {{-- Resep bahan baku --}}
            <div class="mb-6 p-3 rounded-lg border border-[#E7E1D3]">
                <p class="text-sm font-medium text-[#1F2A24] mb-1">Resep Bahan Baku (opsional)</p>
                <p class="text-xs text-[#8A8272] mb-3">
                    Kalau produk ini makan bahan baku (misal Es Teh Susu pakai Susu), daftarkan di sini.
                    Tiap 1 unit produk ini terjual, stok bahan bakunya otomatis kepotong.
                </p>

                <template x-for="(row, index) in rows" :key="index">
                    <div class="flex flex-col sm:flex-row gap-2 mb-2">
                        <select :name="`ingredients[${index}][ingredient_id]`" x-model="row.ingredient_id"
                                class="w-full sm:flex-1 text-sm rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                            <option value="">-- Pilih Bahan --</option>
                            @foreach ($ingredients as $ingredient)
                                <option value="{{ $ingredient->id }}">{{ $ingredient->name }} ({{ $ingredient->unit }})</option>
                            @endforeach
                        </select>
                        <div class="flex gap-2">
                            <input type="number" step="0.01" min="0.01" :name="`ingredients[${index}][qty_used]`" x-model="row.qty_used"
                                   placeholder="Qty/unit" class="flex-1 sm:flex-none sm:w-28 text-sm rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                            <button type="button" x-on:click="rows.splice(index, 1)" class="shrink-0 px-2 py-2 text-[#B94A3D] hover:bg-[#FBEAE6] rounded-lg">&times;</button>
                        </div>
                    </div>
                </template>

                <button type="button" x-on:click="rows.push({ ingredient_id: '', qty_used: '' })"
                        class="text-sm text-[#1B6E6E] hover:underline font-medium">+ Tambah Bahan</button>

                @if ($ingredients->isEmpty())
                    <p class="text-xs text-[#B5842A] mt-2">Belum ada bahan baku. Tambahkan dulu lewat menu "Bahan Baku" di sidebar.</p>
                @endif
            </div>

            <div class="flex flex-col sm:flex-row justify-end gap-2">
                <button type="button" x-data @click="$dispatch('close-modal', 'create-product')" class="px-4 py-2 bg-[#F6F3EC] text-[#1F2A24] rounded-lg border border-[#E7E1D3] hover:bg-[#EFEAE0] transition">Batal</button>
                <button type="submit" class="px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] font-semibold rounded-lg hover:bg-[#E0B559] transition">Simpan</button>
            </div>
        </form>
    </x-modal>

    {{-- Auto-buka modal Tambah Produk kalau validasi gagal saat submit --}}
    @if ($errors->any() && old('category_id') !== null)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                window.dispatchEvent(new CustomEvent('open-modal', { detail: 'create-product' }));
            });
        </script>
    @endif
</x-app-layout>