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

    <div class="py-6 print:py-0">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 print:px-0 print:max-w-none space-y-4 print:space-y-0">

            @if (session('success'))
                <div class="p-4 bg-[#EAF3EE] border border-[#CFE6DA] text-[#2F6F4E] rounded-lg text-sm flex items-center gap-2 print:hidden">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="p-4 bg-[#FBEAE6] border border-[#F0CFC4] text-[#B5482E] rounded-lg text-sm flex items-center gap-2 print:hidden">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            {{-- Kartu struk. Saat di-print: lebar dipersempit ala kertas thermal,
                 bayangan/ring dibuang, warna dipaksa hitam-putih supaya rapi di kertas. --}}
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden
                        print:rounded-none print:ring-0 print:shadow-none print:border-0
                        print:w-[320px] print:mx-auto print:font-mono print:text-black" id="struk">

                {{-- Kop toko: cuma tampil pas print, biar ada identitas usahanya di kertas --}}
                @php $tenant = auth()->user()->tenant ?? null; @endphp
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
                <div class="px-6 py-5 print:px-4 print:py-3 flex flex-wrap items-start justify-between gap-2 border-b border-dashed border-[#E7E1D3] print:border-black">
                    <div>
                        <p class="text-xs text-[#8A8272] mb-0.5 print:hidden">Invoice</p>
                        <div class="flex items-center gap-2">
                            <p class="font-mono font-semibold text-[#1F2A24] print:text-[13px]">{{ $transaction->invoice_number }}</p>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $transaction->invoice_number }}')"
                                    class="text-[#8A8272] hover:text-[#1F2A24] print:hidden" title="Salin nomor invoice">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-[#8A8272] print:text-black mt-1">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @php
                        $badge = match($transaction->status) {
                            'lunas' => 'bg-[#EAF3EE] text-[#2F6F4E]',
                            'piutang' => 'bg-[#FBF0DA] text-[#B5842A]',
                            default => 'bg-[#F6F3EC] text-[#8A8272]',
                        };
                    @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}
                                 print:bg-transparent print:border print:border-black print:rounded-none print:px-1.5 print:py-0.5 print:text-[10px] print:uppercase print:tracking-wide">
                        {{ ucfirst($transaction->status) }}
                    </span>
                </div>

                <!-- Kasir & customer -->
                <div class="px-6 py-4 print:px-4 print:py-2 flex flex-wrap justify-between gap-2 text-sm print:text-[11px] border-b border-dashed border-[#E7E1D3] print:border-black">
                    <p class="text-[#8A8272] print:text-black">Kasir <span class="text-[#1F2A24] font-medium">{{ $transaction->user->name }}</span></p>
                    <p class="text-[#8A8272] print:text-black">Customer <span class="text-[#1F2A24] font-medium">{{ $transaction->customer->name ?? 'Umum' }}</span></p>
                </div>

                <!-- Items: tabel dipakai di layar, list bertumpuk dipakai pas print
                     (kolom tabel gampang gepeng/kepotong di kertas sempit) -->
                <div class="px-6 py-4 print:px-4 print:py-2">
                    <div class="overflow-x-auto -mx-6 px-6 print:hidden">
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
                <div class="px-6 py-4 print:px-4 print:py-2 border-t border-dashed border-[#E7E1D3] print:border-black space-y-1.5 print:space-y-1 text-sm print:text-[11px]">
                    <div class="flex justify-between text-[#8A8272] print:text-black"><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272] print:text-black"><span>Diskon</span><span>Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272] print:text-black"><span>Pajak</span><span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272] print:text-black"><span>Biaya Tambahan</span><span>Rp {{ number_format($transaction->additional_fee, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between font-semibold text-[#1F2A24] text-base print:text-[13px] pt-2 border-t border-[#E7E1D3] print:border-black mt-2">
                        <span>Total</span><span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[#8A8272] print:text-black pt-1"><span>Dibayar di Awal</span><span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
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
                    <div class="px-6 py-4 print:px-4 print:py-2 border-t border-dashed border-[#E7E1D3] print:border-black">
                        <p class="text-xs print:text-[10px] font-medium text-[#8A8272] print:text-black uppercase tracking-wide mb-3 print:mb-1.5">Riwayat Pembayaran</p>
                        <div class="space-y-3 print:space-y-1.5">
                            @foreach ($transaction->payments as $payment)
                                <div class="flex items-start gap-3 print:gap-2">
                                    <div class="w-2 h-2 rounded-full bg-[#D4A73C] mt-1.5 shrink-0 print:hidden"></div>
                                    <div class="flex-1 flex flex-wrap items-center justify-between gap-1 text-sm print:text-[11px]">
                                        <div>
                                            <p class="text-[#1F2A24] print:text-black">{{ $payment->note ?? 'Pembayaran' }}</p>
                                            <p class="text-xs print:text-[10px] text-[#8A8272] print:text-black/70">{{ $payment->paid_at?->format('d/m/Y') ?? $payment->created_at->format('d/m/Y') }}</p>
                                        </div>
                                        <span class="font-medium text-[#2F6F4E] print:text-black">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Footer ucapan terima kasih, cuma tampil pas print --}}
                <div class="hidden print:block text-center px-4 pt-3 pb-5 text-[10px] border-t border-dashed border-black mt-1">
                    <p>Terima kasih atas kunjungan Anda</p>
                </div>

                <!-- Form bayar piutang -->
                @if ($transaction->status === 'piutang')
                    <div class="px-6 py-4 border-t border-dashed border-[#E7E1D3] bg-[#FBF7EC] print:hidden">
                        <p class="text-xs font-medium text-[#8A6D1D] uppercase tracking-wide mb-2">Catat Pembayaran Piutang</p>
                        <form action="{{ route('transactions.pay-piutang', $transaction) }}" method="POST" class="flex flex-wrap gap-2"
                              x-data="{
                                  amountDisplay: '',
                                  sisaPiutang: {{ (int) $transaction->sisaPiutang() }},
                                  formatRupiah(value) {
                                      let angka = String(value).replace(/\D/g, '');
                                      if (!angka) return '';
                                      return new Intl.NumberFormat('id-ID').format(angka);
                                  },
                                  unformatRupiah(value) {
                                      return String(value).replace(/\D/g, '') || '0';
                                  }
                              }">
                            @csrf
                            <div class="flex-1 min-w-[140px] relative">
                                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-[#8A8272] text-sm pointer-events-none">Rp</span>
                                <input
                                    type="text"
                                    inputmode="numeric"
                                    placeholder="Jumlah bayar"
                                    x-model="amountDisplay"
                                    @input="amountDisplay = formatRupiah($event.target.value)"
                                    class="w-full pl-9 text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]"
                                    required
                                >
                                <input type="hidden" name="amount" :value="unformatRupiah(amountDisplay)">
                                <p class="text-[11px] text-[#8A8272] mt-1">Maks. Rp {{ number_format($transaction->sisaPiutang(), 0, ',', '.') }}</p>
                            </div>
                            <input type="text" name="note" placeholder="Catatan (opsional)"
                                   class="flex-1 min-w-[140px] text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#1F2A24] text-white text-sm font-medium rounded-lg hover:bg-[#16201B]">Bayar</button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Aksi -->
            <div class="flex flex-wrap gap-2 print:hidden">
                <button onclick="window.print()"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white ring-1 ring-[#E7E1D3] text-[#1F2A24] text-sm font-medium rounded-lg hover:bg-[#F6F3EC] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                    </svg>
                    Cetak Struk
                </button>
                <a href="{{ route('transactions.pdf', $transaction) }}"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white ring-1 ring-[#E7E1D3] text-[#1F2A24] text-sm font-medium rounded-lg hover:bg-[#F6F3EC] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H8a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Download PDF
                </a>
                <a href="https://wa.me/?text={{ urlencode('Struk '.$transaction->invoice_number.' - Total Rp '.number_format($transaction->total, 0, ',', '.')) }}"
                   target="_blank"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-[#25D366] text-white text-sm font-medium rounded-lg hover:bg-[#1DA851] transition">
                    Share ke WhatsApp
                </a>

                @if ($transaction->status !== 'batal')
                    <div class="relative ml-auto" x-data="{ showCancel: false }">
                        <button @click="showCancel = !showCancel" @click.outside="showCancel = false"
                                class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white ring-1 ring-[#F0CFC4] text-[#B5482E] text-sm font-medium rounded-lg hover:bg-[#FBEAE6] transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Batalkan Transaksi
                        </button>

                        <div x-show="showCancel" x-cloak x-transition
                             class="absolute right-4 sm:right-8 mt-2 w-[calc(100%-2rem)] sm:w-80 bg-white rounded-xl ring-1 ring-[#F0CFC4] shadow-lg p-4 z-10">
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
                @endif
            </div>
        </div>
    </div>
</x-app-layout>