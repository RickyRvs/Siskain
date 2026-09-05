<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
            <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Transaksi</h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-4">

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
                <div class="bg-[#1F2A24] rounded-xl p-5 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-[#D4A73C]/10"></div>
                    <p class="text-xs text-[#B9C2BC] uppercase tracking-wide mb-1">Transaksi Hari Ini</p>
                    <p class="text-2xl font-semibold text-white">{{ $stats['today_count'] }}</p>
                    <p class="text-xs text-[#8FA096] mt-0.5">transaksi (tidak termasuk batal)</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <p class="text-xs text-[#8A8272] uppercase tracking-wide mb-1">Omzet Hari Ini</p>
                    <p class="text-2xl font-semibold text-[#1F2A24]">Rp {{ number_format($stats['today_omzet'], 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-0.5">total transaksi lunas &amp; piutang</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#F0CFC4] shadow-sm p-5">
                    <p class="text-xs text-[#B5482E] uppercase tracking-wide mb-1">Piutang Aktif</p>
                    <p class="text-2xl font-semibold text-[#B5482E]">Rp {{ number_format($stats['piutang_total'], 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-0.5">{{ $stats['piutang_count'] }} invoice belum lunas</p>
                </div>
            </div>

            <!-- Filter bar -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4 flex flex-wrap gap-3 items-center">
                <form method="GET" class="flex flex-wrap gap-2 items-center flex-1">
                    <select name="status" class="text-sm rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" onchange="this.form.submit()">
                        <option value="">Semua Status</option>
                        <option value="lunas" {{ request('status') === 'lunas' ? 'selected' : '' }}>Lunas</option>
                        <option value="piutang" {{ request('status') === 'piutang' ? 'selected' : '' }}>Piutang</option>
                        <option value="batal" {{ request('status') === 'batal' ? 'selected' : '' }}>Batal</option>
                    </select>
                    <input type="date" name="date" value="{{ request('date') }}"
                           class="text-sm rounded-lg border-[#E7E1D3] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]" onchange="this.form.submit()">
                    @if (request('status') || request('date'))
                        <a href="{{ route('transactions.index') }}" class="text-sm text-[#8A8272] hover:text-[#1F2A24] inline-flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            Reset
                        </a>
                    @endif
                </form>
                <a href="{{ route('transactions.create') }}"
                   class="inline-flex items-center gap-1.5 px-4 py-2 bg-[#D4A73C] text-[#0F2E2B] text-sm font-semibold rounded-lg hover:bg-[#E0B559] transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Transaksi Baru
                </a>
            </div>

            <!-- Tabel -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-[#E7E1D3]">
                        <thead class="bg-[#F6F3EC]">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Invoice</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Customer</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Kasir</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-[#8A8272] uppercase tracking-wide">Total</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-[#8A8272] uppercase tracking-wide">Tanggal</th>
                                <th class="px-6 py-3 text-right text-xs font-medium text-[#8A8272] uppercase tracking-wide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7E1D3]">
                            @forelse ($transactions as $transaction)
                                <tr class="hover:bg-[#F6F3EC]/60 transition">
                                    <td class="px-6 py-4">
                                        <a href="{{ route('transactions.show', $transaction) }}" class="font-mono text-sm text-[#1F2A24] hover:text-[#B5842A]">{{ $transaction->invoice_number }}</a>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-2.5">
                                            <div class="w-7 h-7 rounded-full bg-[#F3E7C4] text-[#8A6D1D] flex items-center justify-center text-[11px] font-semibold shrink-0">
                                                {{ strtoupper(substr($transaction->customer->name ?? 'U', 0, 1)) }}
                                            </div>
                                            <span class="text-sm text-[#1F2A24]">{{ $transaction->customer->name ?? 'Umum' }}</span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#8A8272]">{{ $transaction->user->name }}</td>
                                    <td class="px-6 py-4 text-sm font-medium text-[#1F2A24] text-right">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td>
                                    <td class="px-6 py-4">
                                        @php
                                            $badge = match($transaction->status) {
                                                'lunas' => 'bg-[#EAF3EE] text-[#2F6F4E]',
                                                'piutang' => 'bg-[#FBF0DA] text-[#B5842A]',
                                                default => 'bg-[#F6F3EC] text-[#8A8272]',
                                            };
                                            $dot = match($transaction->status) {
                                                'lunas' => 'bg-[#2F6F4E]',
                                                'piutang' => 'bg-[#B5842A]',
                                                default => 'bg-[#8A8272]',
                                            };
                                        @endphp
                                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-medium {{ $badge }}">
                                            <span class="w-1.5 h-1.5 rounded-full {{ $dot }}"></span>
                                            {{ ucfirst($transaction->status) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-sm text-[#8A8272]">{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                                    <td class="px-6 py-4 text-right">
                                        <a href="{{ route('transactions.show', $transaction) }}" class="inline-flex items-center gap-1 text-sm text-[#1B6E6E] hover:text-[#144F4F] font-medium">
                                            Detail
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="px-6 py-16 text-center">
                                        <p class="text-sm text-[#8A8272]">Belum ada transaksi{{ request('status') || request('date') ? ' yang cocok dengan filter ini' : '' }}.</p>
                                        @if (request('status') || request('date'))
                                            <a href="{{ route('transactions.index') }}" class="mt-2 inline-block text-sm text-[#D4A73C] font-medium hover:underline">Hapus filter</a>
                                        @else
                                            <a href="{{ route('transactions.create') }}" class="mt-2 inline-block text-sm text-[#D4A73C] font-medium hover:underline">+ Buat transaksi pertama</a>
                                        @endif
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div>{{ $transactions->links() }}</div>
        </div>
    </div>
</x-app-layout>