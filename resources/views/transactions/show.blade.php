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

    <div class="py-6">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

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

            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden" id="struk">
                <!-- Header -->
                <div class="px-6 py-5 flex flex-wrap items-start justify-between gap-2 border-b border-dashed border-[#E7E1D3]">
                    <div>
                        <p class="text-xs text-[#8A8272] mb-0.5">Invoice</p>
                        <div class="flex items-center gap-2">
                            <p class="font-mono font-semibold text-[#1F2A24]">{{ $transaction->invoice_number }}</p>
                            <button type="button" onclick="navigator.clipboard.writeText('{{ $transaction->invoice_number }}')"
                                    class="text-[#8A8272] hover:text-[#1F2A24]" title="Salin nomor invoice">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                            </button>
                        </div>
                        <p class="text-xs text-[#8A8272] mt-1">{{ $transaction->created_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @php
                        $badge = match($transaction->status) {
                            'lunas' => 'bg-[#EAF3EE] text-[#2F6F4E]',
                            'piutang' => 'bg-[#FBF0DA] text-[#B5842A]',
                            default => 'bg-[#F6F3EC] text-[#8A8272]',
                        };
                    @endphp
                    <span class="px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">{{ ucfirst($transaction->status) }}</span>
                </div>

                <!-- Kasir & customer -->
                <div class="px-6 py-4 flex flex-wrap justify-between gap-2 text-sm border-b border-dashed border-[#E7E1D3]">
                    <p class="text-[#8A8272]">Kasir <span class="text-[#1F2A24] font-medium">{{ $transaction->user->name }}</span></p>
                    <p class="text-[#8A8272]">Customer <span class="text-[#1F2A24] font-medium">{{ $transaction->customer->name ?? 'Umum' }}</span></p>
                </div>

                <!-- Items -->
                <div class="px-6 py-4">
                    <div class="overflow-x-auto -mx-6 px-6">
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
                </div>

                <!-- Ringkasan -->
                <div class="px-6 py-4 border-t border-dashed border-[#E7E1D3] space-y-1.5 text-sm">
                    <div class="flex justify-between text-[#8A8272]"><span>Subtotal</span><span>Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272]"><span>Diskon</span><span>Rp {{ number_format($transaction->discount, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272]"><span>Pajak</span><span>Rp {{ number_format($transaction->tax, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272]"><span>Biaya Tambahan</span><span>Rp {{ number_format($transaction->additional_fee, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between font-semibold text-[#1F2A24] text-base pt-2 border-t border-[#E7E1D3] mt-2">
                        <span>Total</span><span>Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-[#8A8272] pt-1"><span>Dibayar di Awal</span><span>Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</span></div>
                    <div class="flex justify-between text-[#8A8272]"><span>Kembalian</span><span>Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</span></div>

                    @if ($transaction->status === 'piutang')
                        @php $sisa = $transaction->sisaPiutang(); @endphp
                        <div class="flex justify-between font-semibold text-[#B5842A] pt-1">
                            <span>Sisa Piutang</span><span>Rp {{ number_format($sisa, 0, ',', '.') }}</span>
                        </div>
                        @php
                            $pct = $transaction->total > 0 ? min(100, round((($transaction->total - $sisa) / $transaction->total) * 100)) : 0;
                        @endphp
                        <div class="pt-1">
                            <div class="h-1.5 rounded-full bg-[#F0ECE0] overflow-hidden">
                                <div class="h-full rounded-full bg-[#D4A73C]" style="width: {{ $pct }}%"></div>
                            </div>
                            <p class="text-[11px] text-[#8A8272] mt-1">{{ $pct }}% terbayar</p>
                        </div>
                    @endif
                </div>

                <!-- Riwayat pembayaran -->
                @if ($transaction->payments->isNotEmpty())
                    <div class="px-6 py-4 border-t border-dashed border-[#E7E1D3]">
                        <p class="text-xs font-medium text-[#8A8272] uppercase tracking-wide mb-3">Riwayat Pembayaran</p>
                        <div class="space-y-3">
                            @foreach ($transaction->payments as $payment)
                                <div class="flex items-start gap-3">
                                    <div class="w-2 h-2 rounded-full bg-[#D4A73C] mt-1.5 shrink-0"></div>
                                    <div class="flex-1 flex flex-wrap items-center justify-between gap-1 text-sm">
                                        <div>
                                            <p class="text-[#1F2A24]">{{ $payment->note ?? 'Pembayaran' }}</p>
                                            <p class="text-xs text-[#8A8272]">{{ $payment->paid_at?->format('d/m/Y') ?? $payment->created_at->format('d/m/Y') }}</p>
                                        </div>
                                        <span class="font-medium text-[#2F6F4E]">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Form bayar piutang -->
                @if ($transaction->status === 'piutang')
                    <div class="px-6 py-4 border-t border-dashed border-[#E7E1D3] bg-[#FBF7EC]">
                        <p class="text-xs font-medium text-[#8A6D1D] uppercase tracking-wide mb-2">Catat Pembayaran Piutang</p>
                        <form action="{{ route('transactions.pay-piutang', $transaction) }}" method="POST" class="flex flex-wrap gap-2">
                            @csrf
                            <input type="number" name="amount" placeholder="Jumlah bayar" min="1" max="{{ $transaction->sisaPiutang() }}"
                                   class="flex-1 min-w-[140px] text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" required>
                            <input type="text" name="note" placeholder="Catatan (opsional)"
                                   class="flex-1 min-w-[140px] text-sm border-[#DDD5C2] rounded-lg shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                            <button type="submit" class="w-full sm:w-auto px-4 py-2 bg-[#1F2A24] text-white text-sm font-medium rounded-lg hover:bg-[#16201B]">Bayar</button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Aksi -->
            <div class="flex flex-wrap gap-2">
                <button onclick="window.print()"
                        class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-white ring-1 ring-[#E7E1D3] text-[#1F2A24] text-sm font-medium rounded-lg hover:bg-[#F6F3EC] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 9V2h12v7M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2M6 14h12v8H6v-8z" />
                    </svg>
                    Cetak Struk
                </button>
                <a href="https://wa.me/?text={{ urlencode('Struk '.$transaction->invoice_number.' - Total Rp '.number_format($transaction->total, 0, ',', '.')) }}"
                   target="_blank"
                   class="inline-flex items-center justify-center gap-1.5 px-4 py-2 bg-[#25D366] text-white text-sm font-medium rounded-lg hover:bg-[#1DA851] transition">
                    Share ke WhatsApp
                </a>
            </div>
        </div>
    </div>
</x-app-layout>