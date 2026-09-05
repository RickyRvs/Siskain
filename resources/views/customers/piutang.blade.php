<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
            <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Piutang Customer</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            @if (session('success'))
                <div class="mb-4 px-4 py-3 bg-[#EAF3EC] border border-[#BFDCC7] text-[#1F5C33] rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ session('success') }}
                </div>
            @endif

            @if (session('error'))
                <div class="mb-4 px-4 py-3 bg-[#FBEAE7] border border-[#EFC3BA] text-[#8F372D] rounded-lg text-sm flex items-center gap-2">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/></svg>
                    {{ session('error') }}
                </div>
            @endif

            @forelse ($customers as $customer)
                @php
                    $customerTotal = $customer->transactions->sum('total');
                    $customerSisa = $customer->transactions->sum(fn ($t) => $t->sisaPiutang());
                    $customerPaidPct = $customerTotal > 0 ? round((($customerTotal - $customerSisa) / $customerTotal) * 100) : 0;
                    $isThisPayFailing = old('pay_type') === 'total' && (int) old('pay_customer_id') === $customer->id;
                @endphp
                <div class="bg-white border border-[#E7E1D2] rounded-xl shadow-sm mb-4 overflow-hidden">
                    <div class="px-4 sm:px-6 py-4 border-b border-[#E7E1D2] flex flex-wrap items-center justify-between gap-4 bg-[#FAF8F2]">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-[#F3E7C4] text-[#8A6D1D] flex items-center justify-center text-sm font-semibold shrink-0">
                                {{ strtoupper(substr($customer->name, 0, 1)) }}
                            </div>
                            <div class="min-w-0">
                                <h3 class="font-semibold text-[#1F2A24] truncate">{{ $customer->name }}</h3>
                                <p class="text-xs text-[#8A8371]">{{ $customer->transactions->count() }} invoice belum lunas · {{ $customerPaidPct }}% terbayar</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4 w-full sm:w-auto justify-between sm:justify-normal">
                            <div class="text-right shrink-0">
                                <p class="text-xs text-[#8A8371] uppercase tracking-wide">Sisa Piutang</p>
                                <p class="text-lg font-semibold text-[#B94A3D]">Rp {{ number_format($customerSisa, 0, ',', '.') }}</p>
                            </div>
                            <button type="button" x-data
                                x-on:click="$dispatch('open-modal', 'pay-total-{{ $customer->id }}')"
                                title="Bayar sekaligus untuk semua invoice, dialokasikan otomatis dari yang terlama"
                                class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium rounded-lg bg-[#1F2A24] text-white hover:bg-[#16201B] transition-colors shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a1.5 1.5 0 001.5-1.5V6.75a1.5 1.5 0 00-1.5-1.5h-15a1.5 1.5 0 00-1.5 1.5v10.5a1.5 1.5 0 001.5 1.5z" />
                                </svg>
                                Bayar Total
                            </button>
                        </div>
                    </div>

                    <div class="divide-y divide-[#F0ECE0]">
                        @foreach ($customer->transactions as $transaction)
                            @php
                                $sisa = $transaction->sisaPiutang();
                                $paid = $transaction->total - $sisa;
                                $pct = $transaction->total > 0 ? min(100, round(($paid / $transaction->total) * 100)) : 0;
                            @endphp
                            <div class="px-4 sm:px-6 py-4">
                                <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                                    <div>
                                        <span class="text-sm font-medium text-[#1F2A24]">{{ $transaction->invoice_number }}</span>
                                        <span class="text-xs text-[#8A8371] ml-2">Total Rp {{ number_format($transaction->total, 0, ',', '.') }}</span>
                                    </div>
                                    <span class="text-sm font-semibold text-[#B94A3D]">
                                        Sisa Rp {{ number_format($sisa, 0, ',', '.') }}
                                    </span>
                                </div>

                                <div class="flex flex-col sm:flex-row sm:items-center gap-3">
                                    <div class="flex items-center gap-3 flex-1">
                                        <div class="flex-1 h-2 rounded-full bg-[#F0ECE0] overflow-hidden">
                                            <div class="h-full rounded-full bg-[#D4A73C]" style="width: {{ $pct }}%"></div>
                                        </div>
                                        <span class="text-xs font-medium text-[#8A8371] w-10 text-right shrink-0">{{ $pct }}%</span>
                                    </div>

                                    <form action="{{ route('transactions.pay-piutang', $transaction) }}" method="POST" class="flex items-center gap-2 shrink-0">
                                        @csrf
                                        <input type="number" name="amount" placeholder="Jumlah bayar" min="1" max="{{ $sisa }}"
                                            class="flex-1 sm:w-32 px-2.5 py-1.5 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C]"
                                            required>
                                        <button type="submit" class="shrink-0 px-3 py-1.5 bg-[#1F2A24] text-white rounded-lg text-sm font-medium hover:bg-[#16201B]">Bayar</button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Modal: bayar total (lump sum, dialokasikan otomatis ke invoice terlama dulu) --}}
                <x-modal name="pay-total-{{ $customer->id }}" maxWidth="sm">
                    <form action="{{ route('customers.pay-piutang', $customer) }}" method="POST" class="p-6">
                        @csrf
                        <input type="hidden" name="pay_type" value="total">
                        <input type="hidden" name="pay_customer_id" value="{{ $customer->id }}">

                        <h3 class="font-semibold text-[#1F2A24] mb-1">Bayar Total: {{ $customer->name }}</h3>
                        <p class="text-xs text-[#8A8371] mb-4">
                            Total sisa piutang dari {{ $customer->transactions->count() }} invoice: Rp {{ number_format($customerSisa, 0, ',', '.') }}.
                            Jumlah yang dibayar akan otomatis dipakai untuk melunasi invoice yang paling lama dulu, sisanya (kalau ada) masuk ke invoice berikutnya.
                        </p>

                        <div class="mb-5">
                            <label class="block text-sm font-medium text-[#1F2A24] mb-1.5">Jumlah Bayar</label>
                            <input type="number" name="amount" min="1" max="{{ $customerSisa }}"
                                value="{{ $isThisPayFailing ? old('amount') : '' }}"
                                class="w-full px-3 py-2 text-sm border border-[#DDD5C2] rounded-lg focus:outline-none focus:ring-2 focus:ring-[#D4A73C]/40 focus:border-[#D4A73C]"
                                placeholder="Misal: 50000" required>
                            @if ($isThisPayFailing) @error('amount') <p class="text-[#B94A3D] text-xs mt-1.5">{{ $message }}</p> @enderror @endif
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row sm:justify-end gap-2">
                            <button type="button" x-on:click="$dispatch('close-modal', 'pay-total-{{ $customer->id }}')"
                                class="px-4 py-2 text-sm font-medium rounded-lg border border-[#DDD5C2] text-[#5B5647] hover:bg-[#F7F4EC]">Batal</button>
                            <button type="submit" class="px-4 py-2 text-sm font-medium rounded-lg bg-[#1F2A24] text-white hover:bg-[#16201B]">Bayar</button>
                        </div>
                    </form>
                </x-modal>

                @if ($isThisPayFailing)
                    <div x-data x-init="$nextTick(() => $dispatch('open-modal', 'pay-total-{{ $customer->id }}'))"></div>
                @endif
            @empty
                <div class="bg-white border border-[#E7E1D2] rounded-xl shadow-sm p-10 text-center">
                    <p class="text-[#5B5647] text-sm">Tidak ada piutang saat ini — semua transaksi sudah lunas.</p>
                </div>
            @endforelse

            <a href="{{ route('customers.index') }}" class="inline-flex items-center gap-1.5 text-sm text-[#1F2A24] font-medium hover:text-[#D4A73C]">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Kembali ke Customer
            </a>
        </div>
    </div>
</x-app-layout>