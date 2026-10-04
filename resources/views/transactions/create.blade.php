<x-app-layout>
    {{-- SweetAlert2 via CDN. Kalau layout utama sudah load SweetAlert2, baris script ini boleh dihapus. --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    {{-- Biar alert tampil di atas semua modal kasir (z-[70]/[75]/[80]) --}}
    <style>.swal2-container { z-index: 99999 !important; }</style>

    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
                <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Kasir</h2>
            </div>
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1 text-sm text-[#8A8272] hover:text-[#1F2A24]">
                Daftar transaksi
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6"
         @keydown.escape.window="closeAllModals()"
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
             ])->values() }},
             customers: {{ $customers->map(fn($c) => ['id' => $c->id, 'name' => $c->name])->values() }},
             expectedCash: {{ $storeSession ? (float) $sessionSummary['expected_cash'] : 0 }}
         })">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 pb-28">

            @if (session('error'))
                <div class="mb-4 p-3.5 sm:p-4 bg-[#FBEAE6] border border-[#F0CFC4] text-[#B5482E] rounded-xl text-sm">{{ session('error') }}</div>
            @endif

            @if (session('success'))
                <div class="mb-4 p-3.5 sm:p-4 bg-[#EAF3EE] border border-[#CFE6DA] text-[#2F6F4E] rounded-xl text-sm">{{ session('success') }}</div>
            @endif

            @if ($errors->any())
                <div class="mb-4 p-3.5 sm:p-4 bg-[#FBEAE6] border border-[#F0CFC4] text-[#B5482E] rounded-xl text-sm">
                    <ul class="list-disc list-inside space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (!$storeSession)
                {{-- ==================== TOKO BELUM DIBUKA ==================== --}}
                <div class="max-w-md mx-auto mt-2 sm:mt-8 bg-white rounded-2xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden">
                    <div class="px-5 sm:px-6 pt-6 pb-4 text-center">
                        <span class="inline-flex w-14 h-14 rounded-full bg-[#FBF0DA] text-[#B5842A] items-center justify-center mb-3">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z"/></svg>
                        </span>
                        <h3 class="font-semibold text-[#1F2A24] text-lg">Toko belum dibuka</h3>
                        <p class="text-sm text-[#8A8272] mt-1 leading-relaxed">
                            Isi uang laci awal lalu buka toko. Semua transaksi dan pengeluaran sampai toko ditutup dihitung satu sesi, walaupun sudah lewat tengah malam.
                        </p>
                    </div>

                    <form action="{{ route('store-sessions.open') }}" method="POST" @submit="opening = true" class="px-5 sm:px-6 pb-6 space-y-3">
                        @csrf
                        <div>
                            <label class="block text-xs font-medium text-[#8A8272] mb-1.5">Uang laci awal</label>
                            <div class="relative">
                                <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-[#8A8272] pointer-events-none">Rp</span>
                                <input type="text" inputmode="numeric" x-model="openingCashDisplay"
                                       @focus="$event.target.select()"
                                       @input="openingCash = unformatRp($event.target.value); openingCashDisplay = formatRp(openingCash)"
                                       class="w-full pl-10 py-3 text-base sm:text-sm tabular-nums border-[#DDD5C2] rounded-xl shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                <input type="hidden" name="opening_cash" :value="openingCash">
                            </div>
                            <div class="flex flex-wrap gap-2 mt-2.5">
                                @foreach ([50000, 100000, 200000, 300000] as $nominal)
                                    <button type="button" @click="setOpeningCash({{ $nominal }})"
                                            class="px-3 py-1.5 rounded-full text-xs font-medium ring-1 ring-[#DDD5C2] bg-white text-[#5B5647] hover:ring-[#D4A73C] hover:bg-[#FAF8F2] active:scale-95 transition">
                                        {{ number_format($nominal, 0, ',', '.') }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <button type="submit" :disabled="opening"
                                class="w-full py-3.5 rounded-xl bg-[#1F2A24] text-white font-semibold hover:bg-[#16201B] active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed transition">
                            <span x-show="!opening">Buka Toko</span>
                            <span x-show="opening" x-cloak>Memproses...</span>
                        </button>
                    </form>
                </div>
            @else
                {{-- ==================== STATUS TOKO (buka) ==================== --}}
                <div class="mb-4 bg-white rounded-2xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden">
                    <div class="px-4 py-3 flex flex-wrap items-center gap-x-4 gap-y-3">
                        <div class="flex items-center gap-2.5 min-w-0 flex-1 basis-52">
                            <span class="relative flex h-2.5 w-2.5 shrink-0">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-[#2F6F4E] opacity-60"></span>
                                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-[#2F6F4E]"></span>
                            </span>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-[#1F2A24] leading-tight">Toko buka</p>
                                <p class="text-xs text-[#8A8272] truncate">
                                    Sejak {{ $storeSession->opened_at->format('d/m H:i') }}
                                    @if ($storeSession->opener) &middot; {{ $storeSession->opener->name }} @endif
                                </p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2 w-full sm:w-auto sm:flex">
                            <button type="button" @click="expenseOpen = true"
                                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-white ring-1 ring-[#DDD5C2] text-[#1F2A24] text-sm font-medium rounded-xl hover:bg-[#F6F3EC] active:scale-95 transition">
                                <svg class="w-4 h-4 text-[#B5842A] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Pengeluaran
                            </button>
                            <button type="button" @click="closeOpen = true"
                                    class="inline-flex items-center justify-center gap-1.5 px-3.5 py-2.5 bg-[#FBEAE6] ring-1 ring-[#F0CFC4] text-[#B5482E] text-sm font-medium rounded-xl hover:bg-[#F8DDD6] active:scale-95 transition">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 5.636a9 9 0 1012.728 0M12 3v9"/></svg>
                                Tutup Toko
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 divide-x divide-[#F0ECE0] border-t border-[#F0ECE0] bg-[#FAF8F2]">
                        <div class="px-3 sm:px-4 py-2.5 min-w-0">
                            <p class="text-[10px] uppercase tracking-wide text-[#8A8272]">Kas laci</p>
                            <p class="text-sm sm:text-base font-semibold text-[#1F2A24] truncate tabular-nums">Rp {{ number_format($sessionSummary['expected_cash'], 0, ',', '.') }}</p>
                        </div>
                        <div class="px-3 sm:px-4 py-2.5 min-w-0">
                            <p class="text-[10px] uppercase tracking-wide text-[#8A8272]">Omzet sesi</p>
                            <p class="text-sm sm:text-base font-semibold text-[#2F6F4E] truncate tabular-nums">Rp {{ number_format($sessionSummary['omzet'], 0, ',', '.') }}</p>
                        </div>
                        <div class="px-3 sm:px-4 py-2.5 min-w-0">
                            <p class="text-[10px] uppercase tracking-wide text-[#8A8272]">Transaksi</p>
                            <p class="text-sm sm:text-base font-semibold text-[#1F2A24] tabular-nums">{{ $sessionSummary['transaction_count'] }}</p>
                        </div>
                    </div>
                </div>

                {{-- Katalog selalu lebar penuh. Keranjang cuma muncul sebagai modal/bottom sheet
                     setelah bar "Lihat keranjang" di bawah diklik. --}}
                <form id="posForm" action="{{ route('transactions.store') }}" method="POST" @submit="beforeSubmit">
                    @csrf

                    {{-- Semua input tersembunyi dibungkus satu div --}}
                    <div class="hidden" aria-hidden="true">
                        {{-- Diskon/pajak/biaya tambahan belum dipakai — dikirim tetap 0 --}}
                        <input type="hidden" name="discount" value="0">
                        <input type="hidden" name="tax" value="0">
                        <input type="hidden" name="additional_fee" value="0">

                        <template x-for="(item, index) in items" :key="item.key">
                            <span>
                                <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                                <input type="hidden" :name="'items['+index+'][product_variant_id]'" :value="item.product_variant_id">
                                <input type="hidden" :name="'items['+index+'][qty]'" :value="item.qty">
                            </span>
                        </template>
                        <input type="hidden" name="customer_id" :value="matchedCustomerId">
                        <input type="hidden" name="customer_name" :value="customerName.trim()">
                        <input type="hidden" name="payment_method" :value="paymentMethod">
                        <input type="hidden" name="paid_amount" :value="isPiutang ? 0 : paidAmount">
                        <input type="hidden" name="is_piutang" :value="isPiutang ? 1 : 0">
                    </div>

                    <!-- ==================== KATALOG PRODUK ==================== -->
                    <div class="space-y-3 min-w-0">
                        <!-- Search + kategori (sticky) -->
                        <div class="sticky top-0 z-10 -mx-3 sm:mx-0 px-3 sm:px-0 pt-1 pb-2 space-y-2.5 bg-[#FAF8F2]/95 backdrop-blur-sm">
                            <div class="relative">
                                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 w-4 h-4 text-[#B0A98F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="search" x-model="search" placeholder="Cari produk..." autocomplete="off"
                                       class="w-full pl-10 pr-10 py-3 text-base sm:text-sm border-[#DDD5C2] rounded-xl shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                <button type="button" x-show="search" x-cloak @click="search = ''" aria-label="Hapus pencarian"
                                        class="absolute right-2 top-1/2 -translate-y-1/2 w-7 h-7 rounded-full flex items-center justify-center text-[#8A8272] hover:bg-[#F6F3EC]">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>

                            <div class="flex gap-2 overflow-x-auto pb-1 -mb-1" x-show="categories.length > 1" style="scrollbar-width:none">
                                <template x-for="cat in categories" :key="cat">
                                    <button type="button" @click="activeCategory = cat"
                                            class="shrink-0 px-4 py-2 rounded-full text-sm font-medium border transition active:scale-95"
                                            :class="activeCategory === cat ? 'bg-[#1F2A24] text-white border-[#1F2A24]' : 'bg-white text-[#5B5647] border-[#DDD5C2] hover:border-[#B0A98F]'"
                                            x-text="cat"></button>
                                </template>
                            </div>
                        </div>

                        <!-- Grid produk -->
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-2.5 sm:gap-3.5">
                            <template x-for="p in filteredProducts()" :key="p.id">
                                <button type="button" @click="addToCart(p)" :disabled="soldOut(p)"
                                        class="group relative text-left bg-white rounded-2xl overflow-hidden transition-all duration-150 hover:shadow-md active:scale-[0.97] disabled:opacity-50 disabled:hover:shadow-none disabled:cursor-not-allowed"
                                        :class="qtyInCart(p.id) > 0 ? 'ring-2 ring-[#D4A73C]' : 'ring-1 ring-[#E7E1D3] hover:ring-[#D4A73C]'">

                                    <!-- Badge qty di keranjang -->
                                    <span x-show="qtyInCart(p.id) > 0" x-cloak
                                          class="absolute top-2 right-2 z-10 min-w-[22px] h-[22px] px-1.5 rounded-full bg-[#D4A73C] text-[#1F2A24] text-xs font-bold flex items-center justify-center shadow-sm tabular-nums"
                                          x-text="qtyInCart(p.id)"></span>

                                    <div class="relative aspect-[5/4] flex items-center justify-center overflow-hidden"
                                         :class="!p.photo ? categoryColor(p.category).bg : 'bg-[#F6F3EC]'">
                                        <template x-if="p.photo">
                                            <img :src="p.photo" :alt="p.name"
                                                 class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105"
                                                 :class="soldOut(p) ? 'grayscale opacity-60' : ''"
                                                 loading="lazy">
                                        </template>
                                        <template x-if="!p.photo">
                                            <span class="w-12 h-12 rounded-full flex items-center justify-center text-sm font-semibold shadow-sm ring-2 ring-white"
                                                  :class="[categoryColor(p.category).bg, categoryColor(p.category).text, soldOut(p) ? 'opacity-50' : '']"
                                                  x-text="p.name.substring(0,2).toUpperCase()"></span>
                                        </template>

                                        <span x-show="p.tracks_stock && !p.has_variant && p.stock <= 5 && p.stock > 0"
                                              class="absolute top-2 left-2 text-[10px] font-semibold text-[#8A6D1D] bg-[#FBF0DA]/95 px-2 py-0.5 rounded-full shadow-sm"
                                              x-text="'Sisa ' + p.stock"></span>

                                        <span x-show="soldOut(p)" x-cloak class="absolute inset-0 flex items-center justify-center">
                                            <span class="text-[11px] font-semibold text-white bg-[#1F2A24]/80 px-3 py-1 rounded-full">Habis</span>
                                        </span>

                                        <span x-show="!soldOut(p)"
                                              class="absolute bottom-2 right-2 w-8 h-8 rounded-full bg-[#1F2A24] text-white flex items-center justify-center shadow-md group-active:scale-90 group-hover:bg-[#D4A73C] group-hover:text-[#1F2A24] transition">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                        </span>
                                    </div>

                                    <div class="p-2.5 sm:p-3">
                                        <p class="text-sm font-medium text-[#1F2A24] leading-snug line-clamp-2 min-h-[2.5em]" x-text="p.name"></p>
                                        <div class="flex items-center justify-between gap-1 mt-1">
                                            <span class="text-sm font-semibold text-[#1F2A24] tabular-nums" x-text="'Rp ' + formatRp(p.price)"></span>
                                            <span x-show="p.has_variant" class="text-[10px] font-medium text-[#8A8272] bg-[#F6F3EC] px-1.5 py-0.5 rounded-full">varian</span>
                                        </div>
                                    </div>
                                </button>
                            </template>

                            <template x-if="filteredProducts().length === 0">
                                <div class="col-span-full py-16 text-center text-sm text-[#B0A98F]">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-[#DDD5C2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <span x-text="products.length === 0 ? 'Belum ada produk aktif yang bisa dijual.' : 'Tidak ada produk yang cocok.'"></span>
                                </div>
                            </template>
                        </div>
                    </div>

                    <!-- ==================== KERANJANG + CHECKOUT (modal / bottom sheet) ==================== -->
                    <div x-show="cartOpen" x-cloak
                         class="fixed inset-0 z-[70] flex items-end sm:items-center justify-center"
                         style="display:none">
                        <div class="absolute inset-0 bg-[#1F2A24]/50" @click="cartOpen = false"></div>

                        <div class="relative bg-white w-full max-h-[92dvh] rounded-t-3xl sm:rounded-2xl sm:max-w-md flex flex-col shadow-2xl"
                             x-transition:enter="transition ease-out duration-200"
                             x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">

                            <!-- Pegangan sheet (HP) -->
                            <div class="sm:hidden pt-2.5 pb-1 flex justify-center shrink-0">
                                <span class="w-10 h-1 rounded-full bg-[#DDD5C2]"></span>
                            </div>

                            <!-- Header -->
                            <div class="px-5 py-3 border-b border-[#F0ECE0] shrink-0 flex items-center justify-between gap-3">
                                <div class="flex items-center gap-2.5">
                                    <h3 class="font-semibold text-[#1F2A24]">Keranjang</h3>
                                    <span class="text-xs font-medium text-[#B5842A] bg-[#FBF0DA] px-2 py-0.5 rounded-full tabular-nums" x-text="items.length + ' item'"></span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <button type="button" x-show="items.length > 0" x-cloak @click="clearCart()"
                                            class="text-xs font-medium text-[#B94A3D] hover:bg-[#FBEAE6] px-2.5 py-1.5 rounded-lg transition">
                                        Kosongkan
                                    </button>
                                    <button type="button" @click="cartOpen = false" aria-label="Tutup keranjang"
                                            class="text-[#8A8272] hover:text-[#1F2A24] p-1.5 rounded-lg hover:bg-[#F6F3EC]">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                                    </button>
                                </div>
                            </div>

                            <!-- Customer: di luar area scroll, biar dropdown-nya gak ke-clip overflow-y-auto -->
                            <div class="px-5 pt-3 pb-3 border-b border-[#F0ECE0] shrink-0 relative" @click.outside="customerOpen = false">
                                <label class="block text-xs font-medium text-[#8A8272] mb-1.5">Customer</label>
                                <div class="relative">
                                    <input type="text" x-model="customerName" autocomplete="off"
                                           @focus="customerOpen = true"
                                           @click="customerOpen = true"
                                           @input="customerOpen = true"
                                           placeholder="Umum (ketik nama, atau kosongkan)"
                                           class="w-full text-base sm:text-sm py-2.5 border-[#DDD5C2] rounded-xl shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C] pr-10">
                                    <button type="button" @click="customerOpen = !customerOpen" aria-label="Pilih customer"
                                            class="absolute right-0 top-0 h-full px-3 flex items-center text-[#8A8272]">
                                        <svg class="w-4 h-4 transition-transform" :class="customerOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                    </button>

                                    <div x-show="customerOpen" x-cloak
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         class="absolute z-30 mt-1.5 w-full bg-white border border-[#E7E1D3] rounded-xl shadow-lg overflow-hidden">
                                        <div class="max-h-44 overflow-y-auto py-1">
                                            <button type="button" @click="customerName = ''; customerOpen = false"
                                                    class="w-full text-left px-3.5 py-2.5 text-sm text-[#8A8272] hover:bg-[#F6F3EC] transition">
                                                Umum (tanpa nama)
                                            </button>
                                            <template x-for="c in filteredCustomers()" :key="c.id">
                                                <button type="button" @click="customerName = c.name; customerOpen = false"
                                                        class="w-full text-left px-3.5 py-2.5 text-sm text-[#1F2A24] hover:bg-[#F6F3EC] transition"
                                                        x-text="c.name"></button>
                                            </template>
                                            <p x-show="filteredCustomers().length === 0" class="px-3.5 py-2.5 text-sm text-[#B0A98F]">
                                                Gak ada customer cocok, lanjut ketik aja buat nama baru.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Body: daftar item (scroll) -->
                            <div class="flex-1 overflow-y-auto min-h-[120px] overscroll-contain">
                                <div class="px-5 divide-y divide-[#F0ECE0]">
                                    <template x-for="(item, index) in items" :key="item.key">
                                        <div class="py-3 flex items-start gap-3">
                                            <div class="w-11 h-11 rounded-xl overflow-hidden shrink-0 flex items-center justify-center"
                                                 :class="!item.photo ? 'bg-[#F3E7C4]' : ''">
                                                <template x-if="item.photo">
                                                    <img :src="item.photo" :alt="item.name" class="w-full h-full object-cover">
                                                </template>
                                                <template x-if="!item.photo">
                                                    <span class="text-xs font-semibold text-[#8A6D1D]" x-text="item.name.substring(0,2).toUpperCase()"></span>
                                                </template>
                                            </div>

                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-[#1F2A24] leading-snug" x-text="item.name"></p>
                                                <p class="text-xs text-[#8A8272]" x-show="item.variant_name" x-text="item.variant_name"></p>
                                                <p class="text-xs text-[#8A8272] tabular-nums" x-text="'Rp ' + formatRp(item.price) + ' / item'"></p>

                                                <div class="flex items-center gap-1 mt-2">
                                                    <button type="button" @click="changeQty(index, -1)" aria-label="Kurangi"
                                                            class="w-9 h-9 flex items-center justify-center rounded-full border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F6F3EC] active:scale-90 transition">
                                                        <svg x-show="item.qty > 1" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19.5 12h-15"/></svg>
                                                        <svg x-show="item.qty <= 1" x-cloak class="w-4 h-4 text-[#B94A3D]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9.5 7V5.5a1 1 0 011-1h3a1 1 0 011 1V7m-8 0l.6 12.1a1.5 1.5 0 001.5 1.4h6.8a1.5 1.5 0 001.5-1.4L18 7"/></svg>
                                                    </button>
                                                    <span class="w-8 text-center text-sm font-semibold tabular-nums" x-text="item.qty"></span>
                                                    <button type="button" @click="changeQty(index, 1)" aria-label="Tambah"
                                                            class="w-9 h-9 flex items-center justify-center rounded-full border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F6F3EC] active:scale-90 transition">
                                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                                                    </button>
                                                    <span class="text-[11px] text-[#8A8272] ml-1.5 bg-[#F6F3EC] px-2 py-0.5 rounded-full" x-show="item.stock > 0" x-text="'stok ' + item.stock"></span>
                                                </div>
                                            </div>

                                            <div class="text-right shrink-0 flex flex-col items-end">
                                                <p class="text-sm font-semibold text-[#1F2A24] tabular-nums" x-text="'Rp ' + formatRp(item.price * item.qty)"></p>
                                                <button type="button" @click="removeItem(index)" title="Hapus item" aria-label="Hapus item"
                                                        class="inline-flex items-center justify-center w-9 h-9 mt-1 -mr-1.5 rounded-full text-[#B94A3D] hover:bg-[#FBEAE6] active:scale-90 transition">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9.5 7V5.5a1 1 0 011-1h3a1 1 0 011 1V7m-8 0l.6 12.1a1.5 1.5 0 001.5 1.4h6.8a1.5 1.5 0 001.5-1.4L18 7"/></svg>
                                                </button>
                                            </div>
                                        </div>
                                    </template>
                                </div>

                                <div x-show="items.length === 0" class="py-10 px-6 text-center">
                                    <svg class="w-10 h-10 mx-auto mb-2 text-[#DDD5C2]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.8-4.807 2.03-7.408.078-.867-.517-1.649-1.387-1.649H5.106M7.5 14.25L5.106 5.25M9.75 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm9 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z"/></svg>
                                    <p class="text-sm text-[#8A8272]">Keranjang masih kosong.</p>
                                    <p class="text-xs text-[#B0A98F] mt-0.5">Ketuk produk untuk menambahkannya.</p>
                                </div>
                            </div>

                            <!-- Footer: total & pembayaran -->
                            <div class="px-5 pt-3 pb-[calc(1rem+env(safe-area-inset-bottom))] border-t border-[#F0ECE0] bg-[#FAF8F2] rounded-b-2xl space-y-3 shrink-0">
                                <div class="flex items-baseline justify-between">
                                    <span class="text-sm text-[#8A8272]">Total</span>
                                    <span class="text-2xl font-bold text-[#1F2A24] tabular-nums" x-text="'Rp ' + formatRp(total)"></span>
                                </div>

                                <!-- Metode bayar -->
                                <div class="grid grid-cols-4 gap-1.5">
                                    <template x-for="m in [{k:'tunai',l:'Tunai'},{k:'transfer',l:'Transfer'},{k:'qris',l:'QRIS'},{k:'lainnya',l:'Lainnya'}]" :key="m.k">
                                        <button type="button" @click="paymentMethod = m.k"
                                                class="py-2.5 rounded-xl text-xs sm:text-sm font-medium border transition active:scale-95"
                                                :class="paymentMethod === m.k ? 'bg-[#1F2A24] text-white border-[#1F2A24]' : 'bg-white text-[#5B5647] border-[#DDD5C2] hover:border-[#B0A98F]'"
                                                x-text="m.l"></button>
                                    </template>
                                </div>

                                <!-- Piutang -->
                                <label class="flex items-center gap-2.5 text-sm text-[#5B5647] cursor-pointer select-none">
                                    <input type="checkbox" x-model="isPiutang"
                                           @change="if (isPiutang) { paidAmount = 0; paidAmountDisplay = '0'; }"
                                           class="w-5 h-5 rounded border-[#DDD5C2] text-[#D4A73C] focus:ring-[#D4A73C]">
                                    Catat sebagai piutang (belum dibayar)
                                </label>

                                <div x-show="!isPiutang" x-cloak class="space-y-2">
                                    <label class="block text-xs font-medium text-[#8A8272]">Dibayar</label>
                                    <div class="relative">
                                        <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-[#8A8272] pointer-events-none">Rp</span>
                                        <input type="text" inputmode="numeric" x-model="paidAmountDisplay"
                                               @focus="$event.target.select()"
                                               @input="paidAmount = unformatRp($event.target.value); paidAmountDisplay = formatRp(paidAmount)"
                                               class="w-full pl-10 py-3 text-lg sm:text-base font-semibold tabular-nums border-[#DDD5C2] rounded-xl shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                    </div>

                                    <div class="flex flex-wrap gap-1.5">
                                        <button type="button" @click="setExactAmount()"
                                                class="px-3 py-1.5 text-xs font-semibold text-[#1F2A24] bg-[#F3E7C4] rounded-full hover:bg-[#EDDDA8] active:scale-95 transition">
                                            Uang Pas
                                        </button>
                                        <template x-for="amt in quickAmounts" :key="amt">
                                            <button type="button" @click="setPaid(amt)"
                                                    class="px-3 py-1.5 text-xs font-medium text-[#5B5647] bg-white ring-1 ring-[#DDD5C2] rounded-full hover:ring-[#D4A73C] active:scale-95 transition tabular-nums"
                                                    x-text="formatRp(amt)"></button>
                                        </template>
                                    </div>

                                    <p class="text-sm font-medium rounded-lg px-3 py-2"
                                       :class="paidAmount - total >= 0 ? 'text-[#2F6F4E] bg-[#EAF3EE]' : 'text-[#B5842A] bg-[#FBF0DA]'"
                                       x-text="paidAmount - total >= 0
                                           ? 'Kembalian: Rp ' + formatRp(paidAmount - total)
                                           : 'Kurang Rp ' + formatRp(total - paidAmount) + ' — sisanya dicatat sebagai piutang'"></p>
                                </div>

                                <p x-show="isPiutang" x-cloak class="text-xs text-[#B5842A] bg-[#FBF0DA] rounded-lg px-3 py-2.5 leading-relaxed">
                                    Nominal dibayar dikunci ke Rp 0. Cicilan atau pelunasan piutangnya dicatat nanti di halaman detail transaksi.
                                </p>

                                <button type="submit" :disabled="items.length === 0 || submitting"
                                        class="w-full py-3.5 rounded-xl bg-[#1F2A24] text-white font-semibold hover:bg-[#16201B] active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                    <span x-show="!submitting" x-text="(isPiutang ? 'Simpan Piutang — Rp ' : 'Simpan Transaksi — Rp ') + formatRp(total)"></span>
                                    <span x-show="submitting" x-cloak>Memproses...</span>
                                </button>
                            </div>
                        </div>
                    </div>
                </form>

                <!-- ==================== BAR KERANJANG DI BAWAH (semua ukuran layar) ==================== -->
                <button type="button"
                        x-show="items.length > 0 && !cartOpen"
                        x-cloak x-transition
                        @click="cartOpen = true"
                        class="fixed z-40 inset-x-3 sm:inset-x-auto sm:right-6 sm:w-96 bottom-[calc(0.75rem+env(safe-area-inset-bottom))]
                               flex items-center justify-between gap-3 bg-[#1F2A24] text-white rounded-2xl pl-4 pr-5 py-3.5
                               shadow-[0_8px_24px_rgba(15,46,43,0.4)] hover:bg-[#16201B] active:scale-[0.98] transition">
                    <span class="flex items-center gap-3 min-w-0">
                        <span class="relative shrink-0">
                            <svg class="w-6 h-6 text-[#D4A73C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121-2.3 1.8-4.807 2.03-7.408.078-.867-.517-1.649-1.387-1.649H5.106M7.5 14.25L5.106 5.25M9.75 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm9 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                            </svg>
                            <span class="absolute -top-2 -right-2 min-w-[18px] h-[18px] px-1 rounded-full bg-[#D4A73C] text-[#1F2A24] text-[10px] font-bold flex items-center justify-center tabular-nums"
                                  x-text="items.length"></span>
                        </span>
                        <span class="text-sm font-medium truncate">Lihat keranjang</span>
                    </span>
                    <span class="text-base font-bold tabular-nums whitespace-nowrap" x-text="'Rp ' + formatRp(total)"></span>
                </button>

                <!-- ==================== MODAL PILIH VARIAN ==================== -->
                <div x-show="variantPicker.product" x-cloak
                     class="fixed inset-0 z-[80] flex items-end sm:items-center justify-center sm:p-4"
                     style="display: none;">
                    <div class="absolute inset-0 bg-[#1F2A24]/50" @click="variantPicker.product = null"></div>
                    <div class="relative bg-white w-full sm:max-w-sm rounded-t-3xl sm:rounded-2xl shadow-2xl p-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))] max-h-[85dvh] flex flex-col"
                         x-transition:enter="transition ease-out duration-200"
                         x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                         x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                        <div class="sm:hidden -mt-2 mb-3 flex justify-center"><span class="w-10 h-1 rounded-full bg-[#DDD5C2]"></span></div>

                        <div class="flex items-center gap-3 mb-1 shrink-0">
                            <div class="w-11 h-11 rounded-xl overflow-hidden shrink-0 flex items-center justify-center bg-[#F3E7C4]">
                                <template x-if="variantPicker.product?.photo">
                                    <img :src="variantPicker.product.photo" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!variantPicker.product?.photo">
                                    <span class="text-xs font-semibold text-[#8A6D1D]" x-text="variantPicker.product?.name.substring(0,2).toUpperCase()"></span>
                                </template>
                            </div>
                            <div class="min-w-0">
                                <h4 class="font-semibold text-[#1F2A24] leading-tight" x-text="variantPicker.product?.name"></h4>
                                <p class="text-xs text-[#8A8272]">Pilih varian</p>
                            </div>
                        </div>

                        <div class="space-y-2 mt-4 overflow-y-auto overscroll-contain">
                            <template x-for="v in (variantPicker.product?.variants || [])" :key="v.id">
                                <button type="button" @click="pickVariant(v)" :disabled="v.stock <= 0"
                                        class="w-full flex items-center justify-between gap-3 px-4 py-3 rounded-xl border border-[#E7E1D3] hover:border-[#D4A73C] hover:bg-[#FAF8F2] active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed text-left transition">
                                    <span class="text-sm font-medium text-[#1F2A24]" x-text="v.name"></span>
                                    <span class="text-right shrink-0">
                                        <span class="block text-sm font-semibold text-[#1F2A24] tabular-nums" x-text="'Rp ' + formatRp(v.price)"></span>
                                        <span class="block text-[11px] text-[#8A8272]" x-text="v.stock > 0 ? 'stok ' + v.stock : 'habis'"></span>
                                    </span>
                                </button>
                            </template>
                        </div>
                        <button type="button" @click="variantPicker.product = null" class="mt-4 w-full py-2.5 text-sm text-[#8A8272] hover:text-[#1F2A24] shrink-0">Batal</button>
                    </div>
                </div>

                <!-- ==================== MODAL CATAT PENGELUARAN ==================== -->
                <div x-show="expenseOpen" x-cloak
                     class="fixed inset-0 z-[75] flex items-end sm:items-center justify-center sm:p-4"
                     style="display:none">
                    <div class="absolute inset-0 bg-[#1F2A24]/50" @click="expenseOpen = false"></div>

                    <form action="{{ route('store-sessions.expense') }}" method="POST" @submit="expenseSubmitting = true"
                          class="relative bg-white w-full sm:max-w-sm rounded-t-3xl sm:rounded-2xl shadow-2xl p-5 pb-[calc(1.25rem+env(safe-area-inset-bottom))] max-h-[92dvh] overflow-y-auto space-y-3.5"
                          x-transition:enter="transition ease-out duration-200"
                          x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                          x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                        @csrf

                        <div class="sm:hidden -mt-2 flex justify-center"><span class="w-10 h-1 rounded-full bg-[#DDD5C2]"></span></div>

                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-[#1F2A24]">Catat Pengeluaran</h3>
                                <p class="text-xs text-[#8A8272]">Otomatis mengurangi kas laci sesi ini.</p>
                            </div>
                            <button type="button" @click="expenseOpen = false" aria-label="Tutup" class="text-[#8A8272] hover:text-[#1F2A24] p-1.5 -mr-1.5 rounded-lg hover:bg-[#F6F3EC]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        @if ($expenseTypes->isEmpty())
                            <p class="text-sm text-[#B5842A] bg-[#FBF0DA] rounded-xl px-3.5 py-3 leading-relaxed">
                                Belum ada jenis pengeluaran yang aktif. Tambahkan dulu di menu Pengeluaran (Kelola Jenis Pengeluaran).
                            </p>
                        @else
                            <div>
                                <label class="block text-xs font-medium text-[#8A8272] mb-1.5">Jenis pengeluaran</label>
                                <select name="expense_type_id" required @change="applyExpenseDefault($event)"
                                        class="w-full text-base sm:text-sm py-2.5 border-[#DDD5C2] rounded-xl shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                    <option value="">Pilih jenis...</option>
                                    @foreach ($expenseTypes as $type)
                                        <option value="{{ $type->id }}" data-default="{{ (float) ($type->default_amount ?? 0) }}">{{ $type->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[#8A8272] mb-1.5">Nominal</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-[#8A8272] pointer-events-none">Rp</span>
                                    <input type="text" inputmode="numeric" x-model="expenseDisplay" placeholder="0"
                                           @focus="$event.target.select()"
                                           @input="expenseAmount = unformatRp($event.target.value); expenseDisplay = formatRp(expenseAmount)"
                                           class="w-full pl-10 py-3 text-lg sm:text-base font-semibold tabular-nums border-[#DDD5C2] rounded-xl shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                    <input type="hidden" name="amount" :value="expenseAmount">
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[#8A8272] mb-1.5">Catatan (opsional)</label>
                                <input type="text" name="note" maxlength="255" placeholder="Mis. beli es batu 2 kantong"
                                       class="w-full text-base sm:text-sm py-2.5 border-[#DDD5C2] rounded-xl shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                            </div>

                            <button type="submit" :disabled="expenseAmount <= 0 || expenseSubmitting"
                                    class="w-full py-3.5 rounded-xl bg-[#1F2A24] text-white font-semibold hover:bg-[#16201B] active:scale-[0.98] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                <span x-show="!expenseSubmitting">Simpan Pengeluaran</span>
                                <span x-show="expenseSubmitting" x-cloak>Memproses...</span>
                            </button>
                        @endif
                    </form>
                </div>

                <!-- ==================== MODAL TUTUP TOKO ==================== -->
                <div x-show="closeOpen" x-cloak
                     class="fixed inset-0 z-[75] flex items-end sm:items-center justify-center sm:p-4"
                     style="display:none">
                    <div class="absolute inset-0 bg-[#1F2A24]/50" @click="closeOpen = false"></div>

                    <form action="{{ route('store-sessions.close') }}" method="POST" @submit="closeSubmit($event)"
                          class="relative bg-white w-full sm:max-w-md rounded-t-3xl sm:rounded-2xl shadow-2xl max-h-[92dvh] flex flex-col"
                          x-transition:enter="transition ease-out duration-200"
                          x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-95"
                          x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
                        @csrf

                        <div class="sm:hidden pt-2.5 pb-1 flex justify-center shrink-0"><span class="w-10 h-1 rounded-full bg-[#DDD5C2]"></span></div>

                        <div class="px-5 py-3 sm:py-4 border-b border-[#F0ECE0] shrink-0 flex items-start justify-between gap-3">
                            <div>
                                <h3 class="font-semibold text-[#1F2A24]">Tutup Toko</h3>
                                <p class="text-xs text-[#8A8272]">Sesi {{ $storeSession->opened_at->format('d/m H:i') }} &ndash; sekarang</p>
                            </div>
                            <button type="button" @click="closeOpen = false" aria-label="Tutup" class="text-[#8A8272] hover:text-[#1F2A24] p-1.5 -mr-1.5 rounded-lg hover:bg-[#F6F3EC]">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <div class="flex-1 overflow-y-auto overscroll-contain px-5 py-4 space-y-4">
                            <!-- Ringkasan penjualan sesi -->
                            <div class="grid grid-cols-3 gap-2 text-center">
                                <div class="bg-[#F6F3EC] rounded-xl py-2.5 px-1">
                                    <p class="text-[10px] uppercase tracking-wide text-[#8A8272]">Transaksi</p>
                                    <p class="text-base font-semibold text-[#1F2A24] tabular-nums">{{ $sessionSummary['transaction_count'] }}</p>
                                </div>
                                <div class="bg-[#EAF3EE] rounded-xl py-2.5 px-1">
                                    <p class="text-[10px] uppercase tracking-wide text-[#2F6F4E]">Omzet lunas</p>
                                    <p class="text-sm font-semibold text-[#2F6F4E] tabular-nums">Rp {{ number_format($sessionSummary['omzet'], 0, ',', '.') }}</p>
                                </div>
                                <div class="bg-[#FBF0DA] rounded-xl py-2.5 px-1">
                                    <p class="text-[10px] uppercase tracking-wide text-[#B5842A]">Piutang baru</p>
                                    <p class="text-sm font-semibold text-[#B5842A] tabular-nums">Rp {{ number_format($sessionSummary['piutang_baru'], 0, ',', '.') }}</p>
                                </div>
                            </div>

                            <!-- Hitungan kas laci -->
                            <div>
                                <div class="rounded-xl ring-1 ring-[#E7E1D3] divide-y divide-[#F0ECE0] text-sm overflow-hidden">
                                    <div class="flex justify-between px-4 py-2.5 text-[#8A8272]">
                                        <span>Uang laci awal</span>
                                        <span class="tabular-nums">Rp {{ number_format($sessionSummary['opening_cash'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between px-4 py-2.5 text-[#8A8272]">
                                        <span>+ Pembayaran tunai masuk</span>
                                        <span class="tabular-nums">Rp {{ number_format($sessionSummary['cash_in'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between px-4 py-2.5 text-[#8A8272]">
                                        <span>&minus; Pengeluaran</span>
                                        <span class="tabular-nums">Rp {{ number_format($sessionSummary['expenses'], 0, ',', '.') }}</span>
                                    </div>
                                    <div class="flex justify-between px-4 py-3 font-semibold text-[#1F2A24] bg-[#FAF8F2]">
                                        <span>Kas seharusnya di laci</span>
                                        <span class="tabular-nums">Rp {{ number_format($sessionSummary['expected_cash'], 0, ',', '.') }}</span>
                                    </div>
                                </div>
                                <p class="text-[11px] text-[#8A8272] mt-1.5">Transfer dan QRIS tidak ikut dihitung karena tidak masuk laci.</p>
                            </div>

                            <!-- Hitung fisik -->
                            <div>
                                <label class="block text-xs font-medium text-[#8A8272] mb-1.5">Uang di laci (hasil hitung fisik)</label>
                                <div class="relative">
                                    <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-sm text-[#8A8272] pointer-events-none">Rp</span>
                                    <input type="text" inputmode="numeric" x-model="closeCountedDisplay" placeholder="0"
                                           @focus="$event.target.select()"
                                           @input="closeCounted = unformatRp($event.target.value); closeCountedDisplay = formatRp(closeCounted)"
                                           class="w-full pl-10 py-3 text-lg sm:text-base font-semibold tabular-nums border-[#DDD5C2] rounded-xl shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                    <input type="hidden" name="closing_cash_counted" :value="closeCounted">
                                </div>

                                <p x-show="closeCountedDisplay !== ''" x-cloak class="text-sm font-medium rounded-lg px-3 py-2 mt-2"
                                   :class="closeDiff === 0 ? 'text-[#2F6F4E] bg-[#EAF3EE]' : (closeDiff > 0 ? 'text-[#B5842A] bg-[#FBF0DA]' : 'text-[#B94A3D] bg-[#FBEAE6]')"
                                   x-text="closeDiff === 0 ? 'Kas pas, tidak ada selisih.' : ((closeDiff > 0 ? 'Lebih Rp ' : 'Kurang Rp ') + formatRp(Math.abs(closeDiff)))"></p>
                            </div>

                            <div>
                                <label class="block text-xs font-medium text-[#8A8272] mb-1.5">Catatan (opsional)</label>
                                <input type="text" name="note" maxlength="255" placeholder="Mis. selisih karena kembalian kurang"
                                       class="w-full text-base sm:text-sm py-2.5 border-[#DDD5C2] rounded-xl shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                            </div>
                        </div>

                        <div class="px-5 pt-3 pb-[calc(1rem+env(safe-area-inset-bottom))] border-t border-[#F0ECE0] bg-[#FAF8F2] sm:rounded-b-2xl shrink-0 flex gap-2.5">
                            <button type="button" @click="closeOpen = false"
                                    class="flex-1 py-3 rounded-xl bg-white ring-1 ring-[#E7E1D3] text-[#1F2A24] text-sm font-medium hover:bg-[#F6F3EC] active:scale-95 transition">
                                Batal
                            </button>
                            <button type="submit" :disabled="closeCountedDisplay === '' || closing"
                                    class="flex-1 py-3 rounded-xl bg-[#B5482E] text-white text-sm font-semibold hover:bg-[#9A3B25] active:scale-95 disabled:opacity-40 disabled:cursor-not-allowed transition">
                                <span x-show="!closing">Tutup Toko</span>
                                <span x-show="closing" x-cloak>Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>
            @endif
        </div>
    </div>

    <script>
        function posForm({ products, customers, expectedCash }) {
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
                customers,
                expectedCash,
                items: [],
                search: '',
                activeCategory: 'Semua',
                customerName: '',
                customerOpen: false,
                paymentMethod: 'tunai',
                isPiutang: false,
                paidAmount: 0,
                paidAmountDisplay: '0',
                subtotal: 0,
                total: 0,
                cartOpen: false,
                submitting: false,
                variantPicker: { product: null },

                // --- Sesi toko ---
                opening: false,
                openingCash: 0,
                openingCashDisplay: '0',

                expenseOpen: false,
                expenseAmount: 0,
                expenseDisplay: '',
                expenseSubmitting: false,

                closeOpen: false,
                closeCounted: 0,
                closeCountedDisplay: '',
                closing: false,

                init() {
                    // Kunci scroll halaman di belakang saat modal/bottom sheet terbuka.
                    this.$watch('lockScroll', (locked) => {
                        document.body.classList.toggle('overflow-hidden', locked);
                    });
                },

                get lockScroll() {
                    return this.cartOpen
                        || this.expenseOpen
                        || this.closeOpen
                        || !!this.variantPicker.product;
                },

                get closeDiff() {
                    return this.closeCounted - this.expectedCash;
                },

                get categories() {
                    const set = new Set(this.products.map(p => p.category || 'Lainnya'));
                    return ['Semua', ...Array.from(set)];
                },

                get matchedCustomerId() {
                    const name = this.customerName.trim().toLowerCase();
                    if (!name) return '';
                    const found = this.customers.find(c => c.name.trim().toLowerCase() === name);
                    return found ? found.id : '';
                },

                // Saran nominal uang tunai: pembulatan ke atas ke kelipatan umum (maks. 3).
                get quickAmounts() {
                    const t = this.total;
                    if (t <= 0) return [];
                    const result = new Set();
                    [5000, 10000, 20000, 50000, 100000].forEach(step => {
                        const v = Math.ceil(t / step) * step;
                        if (v > t) result.add(v);
                    });
                    return Array.from(result).sort((a, b) => a - b).slice(0, 3);
                },

                closeAllModals() {
                    this.cartOpen = false;
                    this.expenseOpen = false;
                    this.closeOpen = false;
                    this.variantPicker.product = null;
                    this.customerOpen = false;
                },

                filteredCustomers() {
                    const q = this.customerName.trim().toLowerCase();
                    if (!q) return this.customers;
                    return this.customers.filter(c => c.name.toLowerCase().includes(q));
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

                soldOut(p) {
                    return p.tracks_stock && p.stock <= 0 && !p.has_variant;
                },

                qtyInCart(productId) {
                    return this.items.filter(i => i.product_id === productId).reduce((sum, i) => sum + i.qty, 0);
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
                    if (next < 1) {
                        this.removeItem(index);
                        return;
                    }
                    if (item.stock > 0 && next > item.stock) return;
                    item.qty = next;
                    this.recalc();
                },

                removeItem(index) {
                    this.items.splice(index, 1);
                    this.recalc();
                    if (this.items.length === 0) this.cartOpen = false;
                },

                clearCart() {
                    if (this.items.length === 0) return;
                    Swal.fire({
                        icon: 'question',
                        title: 'Kosongkan keranjang?',
                        text: 'Semua item di keranjang akan dihapus.',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, kosongkan',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#1F2A24',
                        cancelButtonColor: '#B5482E',
                    }).then((r) => {
                        if (!r.isConfirmed) return;
                        this.items = [];
                        this.recalc();
                        this.cartOpen = false;
                    });
                },

                setExactAmount() {
                    this.setPaid(this.total);
                },

                setPaid(value) {
                    this.paidAmount = value;
                    this.paidAmountDisplay = this.formatRp(value);
                },

                setOpeningCash(value) {
                    this.openingCash = value;
                    this.openingCashDisplay = this.formatRp(value);
                },

                applyExpenseDefault(e) {
                    const d = Number(e.target.selectedOptions[0]?.dataset.default || 0);
                    if (d > 0) {
                        this.expenseAmount = d;
                        this.expenseDisplay = this.formatRp(d);
                    }
                },

                recalc() {
                    this.subtotal = this.items.reduce((sum, item) => sum + (item.price * item.qty || 0), 0);
                    this.total = this.subtotal;
                },

                formatRp(value) {
                    return new Intl.NumberFormat('id-ID').format(value || 0);
                },

                unformatRp(value) {
                    const digits = String(value).replace(/\D/g, '');
                    return digits ? parseInt(digits, 10) : 0;
                },

                beforeSubmit(e) {
                    this.recalc();

                    if (this.items.length === 0) {
                        e.preventDefault();
                        return;
                    }

                    // Cegah piutang "gak sengaja": kasir lupa isi nominal dibayar.
                    if (!this.isPiutang && this.paidAmount < this.total) {
                        e.preventDefault();
                        const form = e.target;
                        const kurang = this.formatRp(this.total - this.paidAmount);
                        Swal.fire({
                            icon: 'warning',
                            title: 'Pembayaran kurang',
                            text: 'Kurang Rp ' + kurang + '. Sisanya akan dicatat sebagai piutang. Lanjutkan?',
                            showCancelButton: true,
                            confirmButtonText: 'Ya, lanjutkan',
                            cancelButtonText: 'Batal',
                            confirmButtonColor: '#1F2A24',
                            cancelButtonColor: '#B5482E',
                        }).then((r) => {
                            if (!r.isConfirmed) return;
                            this.submitting = true;
                            form.submit(); // tidak memicu event submit lagi, jadi tidak looping
                        });
                        return;
                    }

                    this.submitting = true;
                },

                closeSubmit(e) {
                    e.preventDefault();
                    const form = e.target;
                    Swal.fire({
                        icon: 'warning',
                        title: 'Tutup toko sekarang?',
                        text: 'Setelah ditutup, transaksi baru harus membuka toko lagi.',
                        showCancelButton: true,
                        confirmButtonText: 'Ya, tutup toko',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#B5482E',
                        cancelButtonColor: '#8A8272',
                    }).then((r) => {
                        if (!r.isConfirmed) return;
                        this.closing = true;
                        form.submit();
                    });
                },
            };
        }
    </script>
</x-app-layout>