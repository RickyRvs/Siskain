<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-3">
                <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
                <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Detail Transaksi</h2>
            </div>
            <a href="{{ route('transactions.index') }}" class="inline-flex items-center gap-1.5 text-sm text-[#8A8272] hover:text-[#1F2A24]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali
            </a>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6 print:py-0"
         @if ($canAddItems)
         x-data="addItemsForm({
             products: {{ $products->map(fn($p) => [
                 'id' => $p->id,
                 'name' => $p->name,
                 'price' => (float) $p->price_jual,
                 'stock' => (int) $p->stock,
                 'has_variant' => (bool) $p->has_variant,
                 'tracks_stock' => (bool) $p->tracks_stock,
                 'photo' => $p->photo ? \Storage::url($p->photo) : null,
                 'variants' => $p->variants->map(fn($v) => [
                     'id' => $v->id,
                     'name' => $v->name,
                     'price' => (float) $v->price_jual,
                     'stock' => (int) $v->stock,
                 ])->values(),
             ])->values() }}
         })"
         @endif>
        <div class="max-w-2xl mx-auto px-3 sm:px-6 lg:px-8 print:px-0 print:max-w-none space-y-3 sm:space-y-4 print:space-y-0">

            @if (session('success'))
                <div class="p-3 sm:p-4 bg-[#EAF3EE] border border-[#CFE6DA] text-[#2F6F4E] rounded-lg text-sm flex items-center gap-2 print:hidden">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-3 sm:p-4 bg-[#FBEAE6] border border-[#F0CFC4] text-[#B5482E] rounded-lg text-sm flex items-center gap-2 print:hidden">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden
                        print:rounded-none print:ring-0 print:shadow-none print:border-0
                        print:w-[320px] print:mx-auto print:font-mono print:text-black" id="struk">

                <div class="hidden print:block text-center px-4 pt-5 pb-3">
                    <p class="font-bold text-[13px] uppercase tracking-wide">{{ $tenant->name ?? config('app.name') }}</p>
                    @if (!empty($tenant?->address))
                        <p class="text-[10px] mt-0.5 leading-snug">{{ $tenant->address }}</p>
                    @endif
                    @if (!empty($tenant?->phone))
                        <p class="text-[10px]">{{ $tenant->phone }}</p>
                    @endif
                </div>

                <!-- Header -->
                <div class="px-4 sm:px-6 py-4 sm:py-5 print:px-4 print:py-3 flex flex-wrap items-start justify-between gap-2 border-b border-dashed border-[#E7E1D3] print:border-black">
                    <div class="min-w-0">
                        <p class="text-xs text-[#8A8272] mb-0.5 print:hidden">Invoice</p>
                        <div class="flex items-center gap-2">
                            <p class="font-mono font-semibold text-[#1F2A24] print:text-[13px] text-sm sm:text-base break-all">{{ $transaction->invoice_number }}</p>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $transaction->invoice_number }}')"
                                    class="text-[#8A8272] hover:text-[#1F2A24] print:hidden shrink-0" title="Salin nomor invoice">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-[#8A8272] print:text-black mt-1">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @php
                        $badge = match($transaction->status) {
                            'lunas' => 'bg-[#EAF3EE] text-[#2F6F4E]',
                            'piutang' => 'bg-[#FBF0DA] text-[#B5842A]',
                            'batal' => 'bg-[#FBEAE6] text-[#B5482E]',
                            default => 'bg-[#F6F3EC] text-[#8A8272]',
                        };
                    @endphp
                    <span class="shrink-0 px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}
                                 print:bg-transparent print:border print:border-black print:rounded-none print:px-1.5 print:py-0.5 print:text-[10px] print:uppercase print:tracking-wide">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </div>

                <!-- Kasir & customer -->
                <div class="px-4 sm:px-6 py-3 sm:py-4 print:px-4 print:py-2 flex flex-col xs:flex-row flex-wrap justify-between gap-1 sm:gap-2 text-sm print:text-[11px] border-b border-dashed border-[#E7E1D3] print:border-black">
                    <p class="text-[#8A8272] print:text-black">Kasir <span class="text-[#1F2A24] font-medium">{{ $transaction->user->name }}</span></p>
                    <p class="text-[#8A8272] print:text-black">Customer <span class="text-[#1F2A24] font-medium">{{ $transaction->displayCustomerName() }}</span></p>
                </div>

                <!-- Items -->
                <div class="px-4 sm:px-6 py-3 sm:py-4 print:px-4 print:py-2">

                    {{-- Tabel: tampil di layar sm ke atas --}}
                    <div class="hidden sm:block overflow-x-auto print:hidden">
                        <table class="min-w-full text-sm">
                            <thead>
                                <tr class="text-left text-[#8A8272] text-xs uppercase tracking-wide">
                                    <th class="pb-2 font-medium">Item</th>
                                    <th class="pb-2 font-medium text-right">Qty</th>
                                    <th class="pb-2 font-medium text-right">Harga</th>
                                    <th class="pb-2 font-medium text-right">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[#EFEAE0]">
                                @foreach ($transaction->items as $item)
                                    <tr>
                                        <td class="py-2.5 text-[#1F2A24] whitespace-nowrap">
                                            {{ $item->product->name }}
                                            @if ($item->variant)
                                                <span class="block text-xs text-[#8A8272]">{{ $item->variant->name }}</span>
                                            @endif
                                        </td>
                                        <td class="py-2.5 text-right text-[#8A8272]">{{ $item->qty }}</td>
                                        <td class="py-2.5 text-right text-[#8A8272] whitespace-nowrap">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                        <td class="py-2.5 text-right text-[#1F2A24] font-medium whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Card list: tampil di HP (di bawah sm), lebih longgar & gak perlu scroll horizontal --}}
                    <div class="sm:hidden print:hidden divide-y divide-[#EFEAE0]">
                        @foreach ($transaction->items as $item)
                            <div class="py-2.5 flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="text-sm text-[#1F2A24] font-medium truncate">{{ $item->product->name }}</p>
                                    @if ($item->variant)
                                        <p class="text-xs text-[#8A8272]">{{ $item->variant->name }}</p>
                                    @endif
                                    <p class="text-xs text-[#8A8272] mt-0.5">{{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                                <p class="text-sm font-medium text-[#1F2A24] shrink-0 whitespace-nowrap">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</p>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden print:block text-[11px] divide-y divide-dashed divide-black">
                        @foreach ($transaction->items as $item)
                            <div class="py-1.5">
                                <div class="flex justify-between gap-2">
                                    <span>{{ $item->product->name }}@if ($item->variant) &mdash; {{ $item->variant->name }}@endif</span>
                                </div>
                                <div class="flex justify-between gap-2 text-black/80">
                                    <span>{{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}</span>
                                    <span>Rp {{ number_format($item->subtotal, 0, ',', '.') }}</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Ringkasan -->
                <div class="px-4 sm:px-6 py-3 sm:py-4 print:px-4 print:py-2 border-t border-dashed border-[#E7E1D3] print:border-black space-y-1.5 print:space-y-1 text-sm print:text-[11px]">
                    <div class="flex justify-between text-[#8A8272] print:text-black"><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272] print:text-black"><span>Diskon</span><span>Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272] print:text-black"><span>Pajak</span><span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272] print:text-black"><span>Biaya Tambahan</span><span>Rp {{ number_format($transaction->additional_fee, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between font-semibold text-[#1F2A24] text-base print:text-[13px] pt-2 border-t border-[#E7E1D3] print:border-black mt-2">
                        <span>Total</span><span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                    </div>

                    <div class="flex justify-between text-[#8A8272] print:text-black pt-1"><span>Dibayar</span><span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272] print:text-black"><span>Kembalian</span><span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>

                    @if ($transaction->status === 'piutang')
                        @php $sisa = $transaction->sisaPiutang(); @endphp
                        <div class="flex justify-between font-semibold text-[#B5842A] print:text-black pt-1">
                            <span>Sisa Piutang</span><span>Rp {{ number_format($sisa, 0, ',', '.') }}</span>
                        </div>
                        @php
                            $pct = $transaction->total > 0 ? min(100, round((($transaction->total - $sisa) / $transaction->total) * 100)) : 0;
                        @endphp
                        <div class="pt-1 print:hidden">
                            <div class="h-1.5 rounded-full bg-[#F0ECE0] overflow-hidden">
                                <div class="h-full rounded-full bg-[#D4A73C]" style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="text-[11px] text-[#8A8272] mt-1">{{ $pct }}% terbayar</p>
                        </div>
                    @endif
                </div>

                <!-- Riwayat pembayaran -->
                @if ($transaction->payments->isNotEmpty())
                    <div class="px-4 sm:px-6 py-3 sm:py-4 print:px-4 print:py-2 border-t border-dashed border-[#E7E1D3] print:border-black">
                        <p class="text-xs print:text-[10px] font-medium text-[#8A8272] print:text-black uppercase tracking-wide mb-3 print:mb-1.5">Riwayat Pembayaran</p>
                        <div class="space-y-3 print:space-y-1.5">
                            @foreach ($transaction->payments as $payment)
                                <div class="flex items-start gap-3 print:gap-2">
                                    <div class="w-2 h-2 rounded-full bg-[#D4A73C] mt-1.5 shrink-0 print:hidden"></div>
                                    <div class="flex-1 min-w-0 flex flex-wrap items-center justify-between gap-1 text-sm print:text-[11px]">
                                        <div class="min-w-0">
                                            <p class="text-[#1F2A24] print:text-black truncate">
                                                {{ $payment->note ?? 'Pembayaran' }}
                                                @if (!empty($payment->payment_method))
                                                    <span class="print:hidden inline-block ml-1 px-1.5 py-0.5 rounded text-[10px] font-medium bg-[#F0ECE0] text-[#8A8272] align-middle">{{ ucfirst($payment->payment_method) }}</span>
                                                @endif
                                            </p>
                                            <p class="text-xs print:text-[10px] text-[#8A8272] print:text-black/70">{{ $payment->paid_at?->format('d/m/Y') ?? $payment->created_at->format('d/m/Y') }}</p>
                                        </div>
                                        <span class="font-medium text-[#2F6F4E] print:text-black shrink-0">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <div class="hidden print:block text-center px-4 pt-3 pb-5 text-[10px] border-t border-dashed border-black mt-1">
                    <p>Terima kasih atas kunjungan Anda</p>
                </div>

                <!-- Form bayar piutang dipindah ke halaman Piutang Customer, gak ditampilkan di sini lagi biar gak dobel. -->
            </div>

            <!-- Aksi -->
            <div class="space-y-2 print:hidden">
                @if ($canAddItems)
                    <button type="button" @click="addOpen = true"
                            class="w-full inline-flex items-center justify-center gap-1.5 px-4 py-2.5 bg-[#1F2A24] text-white text-sm font-medium rounded-lg hover:bg-[#16201B] transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Tambah Item ke Invoice Ini
                    </button>
                    <p class="text-[11px] text-[#8A8272] -mt-1">Customer pesan lagi? Tambah item ini nempel ke invoice yang sama, selama masih hari ini.</p>
                @endif

                <div class="flex flex-col sm:flex-row sm:flex-wrap gap-2">
                    <button onclick="window.print()"
                            class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 sm:py-2 bg-white ring-1 ring-[#E7E1D3] text-[#1F2A24] text-sm font-medium rounded-lg hover:bg-[#F6F3EC] transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                        </svg>
                        Cetak Struk
                    </button>
                    <a href="{{ route('transactions.pdf', $transaction) }}"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 sm:py-2 bg-white ring-1 ring-[#E7E1D3] text-[#1F2A24] text-sm font-medium rounded-lg hover:bg-[#F6F3EC] transition">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H8a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Download PDF
                    </a>
                    <a href="https://wa.me/?text={{ urlencode('Struk '.$transaction->invoice_number.' - Total Rp '.number_format($transaction->total, 0, ',', '.')) }}"
                       target="_blank"
                       class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 sm:py-2 bg-[#25D366] text-white text-sm font-medium rounded-lg hover:bg-[#1DA851] transition">
                        Share ke WhatsApp
                    </a>
                </div>

                @if ($transaction->status !== 'batal')
                    <div class="pt-2 border-t border-dashed border-[#E7E1D3]">
                        <div class="relative" x-data="{ showCancel: false }">
                            <button @click="showCancel = !showCancel" @click.outside="showCancel = false"
                                    class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-4 py-2.5 sm:py-2 bg-white ring-1 ring-[#F0CFC4] text-[#B5482E] text-sm font-medium rounded-lg hover:bg-[#FBEAE6] transition">
                                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Batalkan Transaksi
                            </button>

                            <div x-show="showCancel" x-cloak x-transition
                                 class="absolute left-0 right-0 sm:left-0 sm:right-auto mt-2 w-full sm:w-80 bg-white rounded-xl ring-1 ring-[#F0CFC4] shadow-lg p-4 z-10">
                                <p class="text-sm font-medium text-[#B5482E] mb-1">Yakin batalkan transaksi ini?</p>
                                <p class="text-xs text-[#8A8272] mb-3">Stok produk &amp; bahan baku yang sudah dipotong akan dikembalikan otomatis. Tindakan ini tidak bisa dibatalkan.</p>
                                <form action="{{ route('transactions.cancel', $transaction) }}" method="POST"
                                      onsubmit="return confirm('Batalkan transaksi {{ $transaction->invoice_number }}? Stok akan dikembalikan.');">
                                    @csrf
                                    @method('PATCH')
                                    <input type="text" name="reason" placeholder="Alasan (opsional)"
                                           class="w-full text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#B5482E] focus:ring-[#B5482E] mb-2">
                                    <div class="flex gap-2">
                                        <button type="button" @click="showCancel = false"
                                                class="flex-1 px-3 py-2 bg-white ring-1 ring-[#E7E1D3] text-[#1F2A24] text-sm font-medium rounded-lg hover:bg-[#F6F3EC] transition">
                                            Batal
                                        </button>
                                        <button type="submit"
                                                class="flex-1 px-3 py-2 bg-[#B5482E] text-white text-sm font-medium rounded-lg hover:bg-[#9A3B25] transition">
                                            Ya, Batalkan
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            @if ($canAddItems)
                <!-- ==================== MODAL TAMBAH ITEM ==================== -->
                <div x-show="addOpen" x-cloak
                     class="fixed inset-0 z-[70] flex sm:items-center sm:justify-center"
                     style="display:none">
                    <div class="absolute inset-0 bg-[#1F2A24]/50" @click="addOpen = false"></div>

                    <form action="{{ route('transactions.addItems', $transaction) }}" method="POST" @submit="beforeSubmit"
                          class="relative bg-white w-full h-full sm:h-auto sm:w-full sm:max-w-md sm:max-h-[88vh] sm:rounded-2xl sm:shadow-2xl flex flex-col">
                        @csrf

                        <template x-for="(item, index) in items" :key="item.key">
                            <span>
                                <input type="hidden" :name="'items['+index+'][product_id]'" :value="item.product_id">
                                <input type="hidden" :name="'items['+index+'][product_variant_id]'" :value="item.product_variant_id">
                                <input type="hidden" :name="'items['+index+'][qty]'" :value="item.qty">
                            </span>
                        </template>
                        <input type="hidden" name="payment_method" x-model="paymentMethod">
                        <input type="hidden" name="additional_paid_amount" :value="additionalPaid">
                        <input type="hidden" name="is_piutang" :value="isPiutang ? 1 : 0">

                        <!-- Header -->
                        <div class="px-5 py-4 border-b border-[#F0ECE0] shrink-0 flex items-center justify-between">
                            <div>
                                <h3 class="font-semibold text-[#1F2A24]">Tambah Item</h3>
                                <p class="text-xs text-[#8A8272]">Invoice {{ $transaction->invoice_number }}</p>
                            </div>
                            <button type="button" @click="addOpen = false" class="text-[#8A8272] hover:text-[#1F2A24] p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                            </button>
                        </div>

                        <!-- Search -->
                        <div class="px-5 pt-3 pb-2 shrink-0">
                            <div class="relative">
                                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-[#B0A98F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M17 10a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" x-model="search" placeholder="Cari produk..."
                                       class="w-full pl-9 pr-3 py-2 text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                            </div>
                        </div>

                        <!-- Body: scrollable -->
                        <div class="flex-1 overflow-y-auto px-5">
                            <!-- Grid produk -->
                            <div class="grid grid-cols-3 gap-2 pb-3">
                                <template x-for="p in filteredProducts()" :key="p.id">
                                    <button type="button" @click="addToCart(p)" :disabled="p.tracks_stock && p.stock <= 0 && !p.has_variant"
                                            class="relative text-left bg-white rounded-lg ring-1 ring-[#E7E1D3] p-2 hover:ring-[#D4A73C] active:scale-95 transition disabled:opacity-40 disabled:cursor-not-allowed">
                                        <span x-show="qtyInCart(p.id) > 0" x-cloak
                                              class="absolute top-1 right-1 min-w-[16px] h-4 px-1 rounded-full bg-[#D4A73C] text-[#1F2A24] text-[10px] font-bold flex items-center justify-center"
                                              x-text="qtyInCart(p.id)"></span>
                                        <p class="text-xs font-medium text-[#1F2A24] leading-snug line-clamp-2 min-h-[2em]" x-text="p.name"></p>
                                        <p class="text-xs font-semibold text-[#5B5647] mt-1" x-text="'Rp ' + formatRp(p.price)"></p>
                                    </button>
                                </template>
                                <template x-if="filteredProducts().length === 0">
                                    <p class="col-span-3 py-6 text-center text-xs text-[#B0A98F]">Produk tidak ditemukan.</p>
                                </template>
                            </div>

                            <!-- Keranjang tambahan -->
                            <div class="border-t border-dashed border-[#E7E1D3] pt-2 pb-3" x-show="items.length > 0" x-cloak>
                                <p class="text-xs font-medium text-[#8A8272] uppercase tracking-wide mb-1.5">Item Tambahan</p>
                                <div class="divide-y divide-[#F0ECE0]">
                                    <template x-for="(item, index) in items" :key="item.key">
                                        <div class="py-2 flex items-center gap-2">
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm text-[#1F2A24] truncate" x-text="item.name"></p>
                                                <p class="text-xs text-[#8A8272]" x-show="item.variant_name" x-text="item.variant_name"></p>
                                            </div>
                                            <button type="button" @click="changeQty(index, -1)"
                                                    class="w-6 h-6 flex items-center justify-center rounded-full border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F6F3EC]">-</button>
                                            <span class="w-5 text-center text-sm tabular-nums" x-text="item.qty"></span>
                                            <button type="button" @click="changeQty(index, 1)"
                                                    class="w-6 h-6 flex items-center justify-center rounded-full border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F6F3EC]">+</button>
                                            <span class="text-sm font-medium text-[#1F2A24] w-20 text-right" x-text="'Rp ' + formatRp(item.price * item.qty)"></span>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="px-5 pt-3 pb-[calc(1rem+env(safe-area-inset-bottom))] border-t border-[#F0ECE0] bg-[#FAF8F2] space-y-3 shrink-0">
                            <div class="flex justify-between text-sm text-[#8A8272]">
                                <span>Total tambahan</span><span x-text="'Rp ' + formatRp(addedTotal)"></span>
                            </div>
                            <div class="flex justify-between font-semibold text-[#1F2A24]">
                                <span>Total invoice baru</span><span x-text="'Rp ' + formatRp({{ $transaction->total }} + addedTotal)"></span>
                            </div>

                            <div>
                                <label class="block text-xs text-[#8A8272] mb-1">Tambahan Bayar (opsional)</label>
                                <div class="flex gap-2">
                                    <select x-model="paymentMethod" class="text-sm border-[#DDD5C2] rounded-md shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                        <option value="tunai">Tunai</option>
                                        <option value="transfer">Transfer</option>
                                        <option value="qris">QRIS</option>
                                        <option value="lainnya">Lainnya</option>
                                    </select>
                                    <div class="relative flex-1">
                                        <span class="absolute left-2 top-1/2 -translate-y-1/2 text-[10px] text-[#8A8272] pointer-events-none">Rp</span>
                                        <input type="text" inputmode="numeric" x-model="additionalPaidDisplay"
                                               @input="additionalPaid = unformatRp($event.target.value); additionalPaidDisplay = formatRp(additionalPaid)"
                                               placeholder="0"
                                               class="w-full pl-7 text-sm border-[#DDD5C2] rounded-md shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                                    </div>
                                    <button type="button" @click="additionalPaid = addedTotal; additionalPaidDisplay = formatRp(addedTotal)"
                                            class="shrink-0 px-2.5 text-xs font-medium text-[#1F2A24] bg-white ring-1 ring-[#DDD5C2] rounded-md hover:bg-[#F6F3EC]">
                                        Pas
                                    </button>
                                </div>
                                <p class="text-[11px] text-[#8A8272] mt-1">
                                    Kosongkan / isi kurang dari total tambahan kalau item ini mau dicatat sebagai piutang.
                                </p>
                            </div>

                            <button type="submit" :disabled="items.length === 0 || submitting"
                                    class="w-full py-3 rounded-lg bg-[#1F2A24] text-white font-medium hover:bg-[#16201B] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                <span x-show="!submitting" x-text="'Simpan Item Tambahan — Rp ' + formatRp(addedTotal)"></span>
                                <span x-show="submitting" x-cloak>Memproses...</span>
                            </button>
                        </div>
                    </form>
                </div>

                <!-- ==================== MODAL PILIH VARIAN (item tambahan) ==================== -->
                <div x-show="variantPicker.product" x-cloak
                     class="fixed inset-0 z-[80] flex items-center justify-center p-4"
                     style="display: none;">
                    <div class="absolute inset-0 bg-[#1F2A24]/50" @click="variantPicker.product = null"></div>
                    <div class="relative bg-white rounded-xl shadow-lg w-full max-w-sm p-5" x-show="variantPicker.product">
                        <h4 class="font-semibold text-[#1F2A24] mb-1" x-text="variantPicker.product?.name"></h4>
                        <p class="text-xs text-[#8A8272] mb-3">Pilih varian</p>
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
            @endif
        </div>
    </div>

    @if ($canAddItems)
        <script>
            function addItemsForm({ products }) {
                return {
                    products,
                    items: [],
                    search: '',
                    addOpen: false,
                    paymentMethod: '{{ $transaction->payment_method }}',
                    additionalPaid: 0,
                    additionalPaidDisplay: '0',
                    isPiutang: false,
                    submitting: false,
                    variantPicker: { product: null },

                    get addedTotal() {
                        return this.items.reduce((sum, item) => sum + (item.price * item.qty || 0), 0);
                    },

                    filteredProducts() {
                        const q = this.search.trim().toLowerCase();
                        if (!q) return this.products;
                        return this.products.filter(p => p.name.toLowerCase().includes(q));
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
                                stock: tracksStock ? stock : 0,
                                qty: 1,
                            });
                        }
                    },

                    changeQty(index, delta) {
                        const item = this.items[index];
                        const next = item.qty + delta;
                        if (next < 1) {
                            this.items.splice(index, 1);
                            return;
                        }
                        if (item.stock > 0 && next > item.stock) return;
                        item.qty = next;
                    },

                    formatRp(value) {
                        return new Intl.NumberFormat('id-ID').format(value || 0);
                    },

                    unformatRp(value) {
                        const digits = String(value).replace(/\D/g, '');
                        return digits ? parseInt(digits, 10) : 0;
                    },

                    beforeSubmit(e) {
                        if (this.items.length === 0) {
                            e.preventDefault();
                            return;
                        }
                        this.submitting = true;
                    },
                };
            }
        </script>
    @endif
</x-app-layout>