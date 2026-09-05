<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
                <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Transaksi Baru</h2>
            </div>
            <a href="{{ route('transactions.index') }}" class="text-sm text-[#8A8272] hover:text-[#1F2A24]">&larr; Kembali ke daftar</a>
        </div>
    </x-slot>

    <div class="py-6"
         x-data="posForm({
             products: {{ $products->map(fn($p) => [
                 'id' => $p->id,
                 'name' => $p->name,
                 'price' => (float) $p->price_jual,
                 'stock' => (int) $p->stock,
                 'has_variant' => (bool) $p->has_variant,
                 'tracks_stock' => (bool) $p->tracks_stock,
                 'photo' => $p->photo ? \Storage::url($p->photo) : null,
                 'category' => optional($p->category ?? null)->name,
                 'variants' => $p->variants->map(fn($v) => [
                     'id' => $v->id,
                     'name' => $v->name,
                     'price' => (float) $v->price_jual,
                     'stock' => (int) $v->stock,
                 ])->values(),
             ])->values() }}
         })">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('error'))
                <div class="mb-4 p-4 bg-[#FBEAE6] border border-[#F0CFC4] text-[#B5482E] rounded-lg text-sm">{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-4 bg-[#FBEAE6] border border-[#F0CFC4] text-[#B5482E] rounded-lg text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ route('transactions.store') }}" method="POST" @submit="beforeSubmit">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 items-start">

                    <!-- ==================== KOLOM KIRI: KERANJANG ==================== -->
                    <div class="lg:col-span-2 lg:sticky lg:top-6 space-y-4 order-2 lg:order-1">

                        <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden flex flex-col lg:max-h-[calc(100vh-3rem)]">
                            <!-- Header keranjang -->
                            <div class="px-5 py-4 border-b border-[#F0ECE0]">
                                <div class="flex items-center justify-between mb-3">
                                    <h3 class="font-semibold text-[#1F2A24]">Keranjang</h3>
                                    <span class="text-xs font-medium text-[#B5842A] bg-[#FBF0DA] px-2 py-0.5 rounded-full" x-text="items.length + ' item'"></span>
                                </div>
                                <select name="customer_id" class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                    <option value="">Customer &mdash; Umum</option>
                                    @foreach ($customers as $customer)
                                        <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Daftar item -->
                            <div class="flex-1 overflow-y-auto divide-y divide-[#F0ECE0] px-5">
                                <template x-if="items.length === 0">
                                    <div class="py-10 text-center text-sm text-[#B0A98F]">
                                        Keranjang masih kosong.<br>Klik produk di sebelah kanan untuk menambahkan.
                                    </div>
                                </template>

                                <template x-for="(item, index) in items" :key="item.key">
                                    <div class="py-3 flex items-start gap-3">
                                        <!-- Thumbnail item di keranjang -->
                                        <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 flex items-center justify-center"
                                             :class="!item.photo ? 'bg-[#F3E7C4]' : ''">
                                            <template x-if="item.photo">
                                                <img :src="item.photo" :alt="item.name" class="w-full h-full object-cover">
                                            </template>
                                            <template x-if="!item.photo">
                                                <span class="text-xs font-semibold text-[#8A6D1D]" x-text="item.name.substring(0,2).toUpperCase()"></span>
                                            </template>
                                        </div>

                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-[#1F2A24] truncate" x-text="item.name"></p>
                                            <p class="text-xs text-[#8A8272]" x-show="item.variant_name" x-text="item.variant_name"></p>
                                            <div class="flex items-center gap-2 mt-1.5">
                                                <button type="button" @click="changeQty(index, -1)"
                                                        class="w-6 h-6 flex items-center justify-center rounded-md border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F6F3EC]">&minus;</button>
                                                <span class="w-6 text-center text-sm tabular-nums" x-text="item.qty"></span>
                                                <button type="button" @click="changeQty(index, 1)"
                                                        class="w-6 h-6 flex items-center justify-center rounded-md border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F6F3EC]">+</button>
                                                <span class="text-xs text-[#8A8272] ml-1" x-show="item.stock > 0" x-text="'stok ' + item.stock"></span>
                                            </div>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-sm font-medium text-[#1F2A24]" x-text="'Rp ' + formatRp(item.price * item.qty)"></p>
                                            <button type="button" @click="removeItem(index)" class="text-xs text-[#B94A3D] hover:text-[#8F372D] mt-1">Hapus</button>
                                        </div>

                                        <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                                        <input type="hidden" :name="'items['+index+'][product_variant_id]'" :value="item.product_variant_id">
                                        <input type="hidden" :name="'items['+index+'][qty]'" :value="item.qty">
                                    </div>
                                </template>
                            </div>

                            <!-- Diskon / pajak / biaya -->
                            <div class="px-5 py-4 border-t border-[#F0ECE0] grid grid-cols-3 gap-2">
                                <div>
                                    <label class="block text-xs text-[#8A8272] mb-1">Diskon</label>
                                    <input type="number" name="discount" x-model.number="discount" @input="recalc" value="0" min="0"
                                           class="w-full text-sm border-[#DDD5C2] rounded-md shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                </div>
                                <div>
                                    <label class="block text-xs text-[#8A8272] mb-1">Pajak</label>
                                    <input type="number" name="tax" x-model.number="tax" @input="recalc" value="0" min="0"
                                           class="w-full text-sm border-[#DDD5C2] rounded-md shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                </div>
                                <div>
                                    <label class="block text-xs text-[#8A8272] mb-1">Biaya Lain</label>
                                    <input type="number" name="additional_fee" x-model.number="additionalFee" @input="recalc" value="0" min="0"
                                           class="w-full text-sm border-[#DDD5C2] rounded-md shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                </div>
                            </div>

                            <!-- Ringkasan & pembayaran -->
                            <div class="px-5 py-4 border-t border-[#F0ECE0] bg-[#FAF8F2] space-y-3">
                                <div class="space-y-1 text-sm">
                                    <div class="flex justify-between text-[#8A8272]"><span>Subtotal</span><span x-text="'Rp ' + formatRp(subtotal)"></span></div>
                                    <div class="flex justify-between font-semibold text-[#1F2A24] text-base pt-1 border-t border-[#E7E1D3]">
                                        <span>Total</span><span x-text="'Rp ' + formatRp(total)"></span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs text-[#8A8272] mb-1">Metode Bayar</label>
                                        <select name="payment_method" class="w-full text-sm border-[#DDD5C2] rounded-md shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                                            <option value="tunai">Tunai</option>
                                            <option value="transfer">Transfer</option>
                                            <option value="qris">QRIS</option>
                                            <option value="lainnya">Lainnya</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs text-[#8A8272] mb-1">Dibayar</label>
                                        <input type="number" name="paid_amount" x-model.number="paidAmount" value="0" min="0" required
                                               class="w-full text-sm border-[#DDD5C2] rounded-md shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                    </div>
                                </div>

                                <p class="text-xs" :class="paidAmount - total >= 0 ? 'text-[#2F6F4E]' : 'text-[#B94A3D]'"
                                   x-text="(paidAmount - total >= 0 ? 'Kembalian: Rp ' : 'Kurang: Rp ') + formatRp(Math.abs(paidAmount - total))"></p>

                                <label class="flex items-center gap-2 text-sm text-[#5B5647]">
                                    <input type="checkbox" name="is_piutang" value="1" class="rounded border-[#DDD5C2] text-[#D4A73C] focus:ring-[#D4A73C]">
                                    Catat sebagai piutang
                                </label>

                                <button type="submit" :disabled="items.length === 0"
                                        class="w-full py-3 rounded-lg bg-[#1F2A24] text-white font-medium hover:bg-[#16201B] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    Simpan Transaksi &mdash; <span x-text="'Rp ' + formatRp(total)"></span>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ==================== KOLOM KANAN: KATALOG PRODUK ==================== -->
                    <div class="lg:col-span-3 space-y-4 order-1 lg:order-2">

                        <!-- Search -->
                        <div class="relative">
                            <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#B0A98F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                            <input type="text" x-model="search" placeholder="Cari produk..."
                                   class="w-full pl-10 pr-4 py-2.5 text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                        </div>

                        <!-- Kategori -->
                        <div class="flex gap-2 overflow-x-auto pb-1" x-show="categories.length > 1">
                            <template x-for="cat in categories" :key="cat">
                                <button type="button" @click="activeCategory = cat"
                                        class="shrink-0 px-3.5 py-1.5 rounded-full text-sm font-medium border transition"
                                        :class="activeCategory === cat ? 'bg-[#1F2A24] text-white border-[#1F2A24]' : 'bg-white text-[#5B5647] border-[#DDD5C2] hover:border-[#B0A98F]'"
                                        x-text="cat"></button>
                            </template>
                        </div>

                        <!-- Grid produk -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-3">
                            <template x-for="p in filteredProducts()" :key="p.id">
                                <button type="button" @click="addToCart(p)" :disabled="p.tracks_stock && p.stock <= 0 && !p.has_variant"
                                        class="text-left bg-white rounded-xl ring-1 ring-[#E7E1D3] overflow-hidden hover:ring-[#D4A73C] hover:shadow-md transition disabled:opacity-40 disabled:cursor-not-allowed">

                                    <!-- Thumbnail produk: foto asli kalau ada, fallback ke inisial -->
                                    <div class="h-24 flex items-center justify-center overflow-hidden"
                                         :class="!p.photo ? categoryColor(p.category).bg : 'bg-[#F6F3EC]'">
                                        <template x-if="p.photo">
                                            <img :src="p.photo" :alt="p.name" class="w-full h-full object-cover" loading="lazy">
                                        </template>
                                        <template x-if="!p.photo">
                                            <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm font-semibold"
                                                  :class="[categoryColor(p.category).bg, categoryColor(p.category).text, 'ring-2 ring-white']"
                                                  x-text="p.name.substring(0,2).toUpperCase()"></span>
                                        </template>
                                    </div>

                                    <div class="p-3">
                                        <p class="text-sm font-medium text-[#1F2A24] leading-snug line-clamp-2" x-text="p.name"></p>
                                        <div class="flex items-center justify-between mt-1.5">
                                            <span class="text-sm font-semibold text-[#1F2A24]" x-text="'Rp ' + formatRp(p.price)"></span>
                                            <span class="text-[11px] text-[#B5842A]" x-show="p.tracks_stock && !p.has_variant && p.stock <= 5 && p.stock > 0" x-text="'sisa ' + p.stock"></span>
                                            <span class="text-[11px] text-[#B94A3D] font-medium" x-show="p.tracks_stock && !p.has_variant && p.stock <= 0">Habis</span>
                                        </div>
                                    </div>
                                </button>
                            </template>

                            <template x-if="filteredProducts().length === 0">
                                <div class="col-span-full py-16 text-center text-sm text-[#B0A98F]">
                                    Tidak ada produk yang cocok.
                                </div>
                            </template>
                        </div>
                    </div>
                </div>
            </form>

            <!-- ==================== MODAL PILIH VARIAN ==================== -->
            <div x-show="variantPicker.product" x-cloak
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 style="display: none;">
                <div class="absolute inset-0 bg-[#1F2A24]/50" @click="variantPicker.product = null"></div>
                <div class="relative bg-white rounded-xl shadow-lg w-full max-w-sm p-5" x-show="variantPicker.product">
                    <div class="flex items-center gap-3 mb-1">
                        <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 flex items-center justify-center bg-[#F3E7C4]">
                            <template x-if="variantPicker.product?.photo">
                                <img :src="variantPicker.product.photo" class="w-full h-full object-cover">
                            </template>
                            <template x-if="!variantPicker.product?.photo">
                                <span class="text-xs font-semibold text-[#8A6D1D]" x-text="variantPicker.product?.name.substring(0,2).toUpperCase()"></span>
                            </template>
                        </div>
                        <h4 class="font-semibold text-[#1F2A24]" x-text="variantPicker.product?.name"></h4>
                    </div>
                    <p class="text-xs text-[#8A8272] mb-4 ml-[52px]">Pilih varian</p>
                    <div class="space-y-2 max-h-72 overflow-y-auto">
                        <template x-for="v in (variantPicker.product?.variants || [])" :key="v.id">
                            <button type="button" @click="pickVariant(v)" :disabled="v.stock <= 0"
                                    class="w-full flex items-center justify-between px-3.5 py-2.5 rounded-lg border border-[#E7E1D3] hover:border-[#D4A73C] hover:bg-[#FAF8F2] disabled:opacity-40 disabled:cursor-not-allowed text-left">
                                <span class="text-sm text-[#1F2A24]" x-text="v.name"></span>
                                <span class="text-right">
                                    <span class="block text-sm font-medium text-[#1F2A24]" x-text="'Rp ' + formatRp(v.price)"></span>
                                    <span class="block text-[11px] text-[#8A8272]" x-text="v.stock > 0 ? 'stok ' + v.stock : 'habis'"></span>
                                </span>
                            </button>
                        </template>
                    </div>
                    <button type="button" @click="variantPicker.product = null" class="mt-4 w-full py-2 text-sm text-[#8A8272] hover:text-[#1F2A24]">Batal</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function posForm({ products }) {
            const CATEGORY_COLORS = [
                { bg: 'bg-[#F3E7C4]', text: 'text-[#8A6D1D]' },
                { bg: 'bg-[#EAF3EE]', text: 'text-[#2F6F4E]' },
                { bg: 'bg-[#FBEAE6]', text: 'text-[#B5482E]' },
                { bg: 'bg-[#E6EEF0]', text: 'text-[#1B6E6E]' },
                { bg: 'bg-[#EFE8F5]', text: 'text-[#6B4E9A]' },
                { bg: 'bg-[#F6F3EC]', text: 'text-[#5B5647]' },
            ];

            return {
                products,
                items: [],
                search: '',
                activeCategory: 'Semua',
                discount: 0,
                tax: 0,
                additionalFee: 0,
                paidAmount: 0,
                subtotal: 0,
                total: 0,
                variantPicker: { product: null },
                _pendingProduct: null,

                get categories() {
                    const set = new Set(this.products.map(p => p.category || 'Lainnya'));
                    return ['Semua', ...Array.from(set)];
                },

                categoryColor(name) {
                    const list = this.categories.filter(c => c !== 'Semua');
                    const idx = Math.max(0, list.indexOf(name || 'Lainnya'));
                    return CATEGORY_COLORS[idx % CATEGORY_COLORS.length];
                },

                filteredProducts() {
                    const q = this.search.trim().toLowerCase();
                    return this.products.filter(p => {
                        const matchCategory = this.activeCategory === 'Semua' || (p.category || 'Lainnya') === this.activeCategory;
                        const matchSearch = !q || p.name.toLowerCase().includes(q);
                        return matchCategory && matchSearch;
                    });
                },

                addToCart(product) {
                    if (product.has_variant && product.variants.length > 0) {
                        this.variantPicker.product = product;
                        return;
                    }
                    this.upsertItem(product, null);
                },

                pickVariant(variant) {
                    this.upsertItem(this.variantPicker.product, variant);
                    this.variantPicker.product = null;
                },

                upsertItem(product, variant) {
                    const key = product.id + '-' + (variant ? variant.id : '0');
                    const existing = this.items.find(i => i.key === key);
                    const stock = variant ? variant.stock : product.stock;
                    const tracksStock = variant ? true : product.tracks_stock;

                    if (existing) {
                        if (!tracksStock || stock <= 0 || existing.qty < stock) existing.qty++;
                    } else {
                        this.items.push({
                            key,
                            product_id: product.id,
                            product_variant_id: variant ? variant.id : '',
                            name: product.name,
                            variant_name: variant ? variant.name : '',
                            price: variant ? variant.price : product.price,
                            photo: product.photo,
                            stock: tracksStock ? stock : 0,
                            qty: 1,
                        });
                    }
                    this.recalc();
                },

                changeQty(index, delta) {
                    const item = this.items[index];
                    const next = item.qty + delta;
                    if (next < 1) return;
                    if (item.stock > 0 && next > item.stock) return;
                    item.qty = next;
                    this.recalc();
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                    this.recalc();
                },

                recalc() {
                    this.subtotal = this.items.reduce((sum, item) => sum + (item.price * item.qty || 0), 0);
                    const raw = this.subtotal - (this.discount || 0) + (this.tax || 0) + (this.additionalFee || 0);
                    this.total = Math.max(0, raw);
                },

                formatRp(value) {
                    return new Intl.NumberFormat('id-ID').format(value || 0);
                },

                beforeSubmit() {
                    this.recalc();
                },
            };
        }
    </script>
</x-app-layout>