<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Hero -->
            <div class="rounded-2xl bg-gradient-to-br from-[#0F2E2B] to-[#1B4640] px-6 py-6 sm:px-8 sm:py-7 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <p class="text-white/60 text-sm">{{ now()->translatedFormat('l, d F Y') }}</p>
                    <h2 class="text-2xl font-semibold text-white mt-0.5">Halo, {{ Auth::user()->name }} 👋</h2>
                    <p class="text-white/60 text-sm mt-1">Berikut ringkasan singkat sebelum mulai kerja.</p>
                </div>
                <a href="{{ route('transactions.create') }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-2.5 bg-[#D4A73C] text-[#0F2E2B] text-sm font-semibold rounded-lg hover:bg-[#E0B559] transition shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Transaksi Baru
                </a>
            </div>

            <!-- Aksi Cepat (bar horizontal, langsung terlihat) -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-3">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                    <a href="{{ route('transactions.create') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-[#F6F3EC] transition group">
                        <div class="w-10 h-10 shrink-0 rounded-lg bg-[#FBF0DA] text-[#B5842A] flex items-center justify-center group-hover:bg-[#F5E3B8]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-[#1F2A24] truncate">Transaksi Baru</p>
                            <p class="text-xs text-[#8A8272] truncate">Catat penjualan baru</p>
                        </div>
                    </a>

                    <a href="{{ route('products.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-[#F6F3EC] transition group">
                        <div class="w-10 h-10 shrink-0 rounded-lg bg-[#EAF3EE] text-[#2F6F4E] flex items-center justify-center group-hover:bg-[#DAEBE1]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-[#1F2A24] truncate">Produk & Stok</p>
                            <p class="text-xs text-[#8A8272] truncate">Kelola produk dan stok</p>
                        </div>
                    </a>

                    <a href="{{ route('customers.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-[#F6F3EC] transition group">
                        <div class="w-10 h-10 shrink-0 rounded-lg bg-[#E9F1F1] text-[#1B6E6E] flex items-center justify-center group-hover:bg-[#D9E9E9]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-4a4 4 0 100-8 4 4 0 000 8zm6 8v-2a4 4 0 00-4-4h-4a4 4 0 00-4 4v2" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-[#1F2A24] truncate">Customer</p>
                            <p class="text-xs text-[#8A8272] truncate">Data pelanggan</p>
                        </div>
                    </a>

                    <a href="{{ route('transactions.index') }}"
                       class="flex items-center gap-3 rounded-lg px-3 py-2.5 hover:bg-[#F6F3EC] transition group">
                        <div class="w-10 h-10 shrink-0 rounded-lg bg-[#FBEAE6] text-[#B5482E] flex items-center justify-center group-hover:bg-[#F5D9D2]">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="min-w-0">
                            <p class="text-sm font-medium text-[#1F2A24] truncate">Riwayat Transaksi</p>
                            <p class="text-xs text-[#8A8272] truncate">Cari transaksi lama</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Kartu statistik utama: Omzet, Laba, Kas, Transaksi -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 hover:shadow-md transition">
                    <p class="text-xs text-[#8A8272] mb-1">Omzet Hari Ini <span class="text-[#B5A97A]">(Lunas)</span></p>
                    <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($todayRevenue, 0, ',', '.') }}</p>
                    <p class="text-xs mt-1 {{ $revenueGrowth >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">
                        {{ $revenueGrowth >= 0 ? '▲' : '▼' }} {{ abs($revenueGrowth) }}% dari kemarin
                    </p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 hover:shadow-md transition">
                    <p class="text-xs text-[#8A8272] mb-1">Laba Kotor Hari Ini</p>
                    <p class="text-xl font-semibold {{ $todayGrossProfit >= 0 ? 'text-[#1F2A24]' : 'text-[#B5482E]' }}">Rp {{ number_format($todayGrossProfit, 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">Margin {{ $todayMargin }}%</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 hover:shadow-md transition">
                    <p class="text-xs text-[#8A8272] mb-1">Kas Masuk Hari Ini</p>
                    <p class="text-xl font-semibold text-[#2F6F4E]">Rp {{ number_format($todayKasMasuk, 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">Termasuk cicilan piutang lama</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 hover:shadow-md transition">
                    <p class="text-xs text-[#8A8272] mb-1">Transaksi Hari Ini</p>
                    <p class="text-xl font-semibold text-[#1F2A24]">{{ $todayTransactionCount }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">{{ $todayLunasCount }} lunas &middot; {{ $todayPiutangCount }} piutang</p>
                </div>
            </div>

            <!-- Kartu statistik sekunder: Piutang & Stok -->
            <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 hover:shadow-md transition">
                    <p class="text-xs text-[#8A8272] mb-1">Piutang Baru Hari Ini</p>
                    <p class="text-xl font-semibold {{ $todayPiutangBaru > 0 ? 'text-[#B5482E]' : 'text-[#1F2A24]' }}">Rp {{ number_format($todayPiutangBaru, 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">Belum masuk omzet</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 hover:shadow-md transition">
                    <p class="text-xs text-[#8A8272] mb-1">Piutang Aktif (semua)</p>
                    <p class="text-xl font-semibold {{ $piutangTotal > 0 ? 'text-[#B5482E]' : 'text-[#1F2A24]' }}">Rp {{ number_format($piutangTotal, 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">{{ $piutangCount }} transaksi belum lunas</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 hover:shadow-md transition">
                    <p class="text-xs text-[#8A8272] mb-1">Produk Stok Menipis</p>
                    <p class="text-xl font-semibold {{ $lowStockCount > 0 ? 'text-[#B5482E]' : 'text-[#1F2A24]' }}">{{ $lowStockCount }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">Perlu restock segera</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 hover:shadow-md transition">
                    <p class="text-xs text-[#8A8272] mb-1">Omzet 7 Hari Terakhir</p>
                    <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($weekOmzet, 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">+Rp {{ number_format($weekPiutangBaru, 0, ',', '.') }} piutang baru</p>
                </div>
            </div>

            <!-- Grafik penjualan & metode pembayaran -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="xl:col-span-2 bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-4">Omzet & Piutang Baru &mdash; 7 Hari Terakhir</h3>
                    <div class="h-64">
                        <canvas id="salesTrendChart"></canvas>
                    </div>
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-4">Metode Pembayaran Hari Ini <span class="text-[#B5A97A] font-normal">(Lunas)</span></h3>
                    @if ($paymentBreakdown->isEmpty())
                        <p class="text-sm text-[#8A8272] py-6 text-center">Belum ada transaksi lunas hari ini</p>
                    @else
                        @php $totalPayments = $paymentBreakdown->sum(); @endphp
                        <div class="space-y-3">
                            @foreach ($paymentBreakdown as $method => $jumlah)
                                @php $percent = $totalPayments > 0 ? round(($jumlah / $totalPayments) * 100) : 0; @endphp
                                <div>
                                    <div class="flex justify-between text-xs text-[#1F2A24] mb-1">
                                        <span class="capitalize">{{ $method }}</span>
                                        <span class="text-[#8A8272]">{{ $jumlah }}x ({{ $percent }}%)</span>
                                    </div>
                                    <div class="h-2 rounded-full bg-[#F6F3EC] overflow-hidden">
                                        <div class="h-full bg-[#D4A73C] rounded-full" style="width: {{ $percent }}%"></div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>

            <!-- Insight: produk terlaris, piutang teratas, stok kritis -->
            <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Produk Terlaris <span class="text-[#B5A97A] font-normal">(30 Hari, Lunas)</span></h3>
                    @forelse ($topProducts as $p)
                        <div class="flex items-center justify-between py-2 border-b border-[#EFEAE0] last:border-0">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[#1F2A24] truncate">{{ $p['name'] }}</p>
                                <p class="text-xs text-[#8A8272]">{{ $p['qty'] }} terjual</p>
                            </div>
                            <p class="text-sm font-medium text-[#1F2A24] shrink-0">Rp {{ number_format($p['revenue'], 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Belum ada penjualan lunas dalam 30 hari terakhir</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Piutang Terbesar</h3>
                    @forelse ($topDebtors as $d)
                        <div class="flex items-center justify-between py-2 border-b border-[#EFEAE0] last:border-0">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-[#1F2A24] truncate">{{ $d['name'] }}</p>
                                <p class="text-xs text-[#8A8272]">{{ $d['jumlah_invoice'] }} invoice</p>
                            </div>
                            <p class="text-sm font-medium text-[#B5482E] shrink-0">Rp {{ number_format($d['sisa'], 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Tidak ada piutang aktif</p>
                    @endforelse
                    @if ($topDebtors->isNotEmpty())
                        <a href="{{ route('customers.piutang') }}" class="text-sm text-[#B5842A] font-medium hover:text-[#8A6420] mt-3 inline-block">Lihat semua piutang &rarr;</a>
                    @endif
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Stok Kritis</h3>
                    @forelse ($lowStockProducts as $prod)
                        <div class="flex items-center justify-between py-2 border-b border-[#EFEAE0] last:border-0">
                            <p class="text-sm font-medium text-[#1F2A24] truncate">{{ $prod->name }}</p>
                            <p class="text-sm font-medium text-[#B5482E] shrink-0">{{ $prod->stock }} / min {{ $prod->min_stock }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Semua stok aman</p>
                    @endforelse
                    @if ($lowStockProducts->isNotEmpty())
                        <a href="{{ route('products.index', ['low_stock' => 1]) }}" class="text-sm text-[#B5842A] font-medium hover:text-[#8A6420] mt-3 inline-block">Lihat semua &rarr;</a>
                    @endif
                </div>
            </div>

            <!-- Aktivitas terbaru (full width, 2 kolom biar tidak sempit) -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-sm font-medium text-[#8A8272]">Aktivitas Terbaru</h3>
                    @if ($recentTransactions->isNotEmpty())
                        <a href="{{ route('transactions.index') }}" class="text-sm text-[#B5842A] font-medium hover:text-[#8A6420]">Lihat semua &rarr;</a>
                    @endif
                </div>

                @php
                    $statusBadge = [
                        'lunas' => 'bg-[#EAF3EE] text-[#2F6F4E]',
                        'piutang' => 'bg-[#FBEAE6] text-[#B5482E]',
                        'batal' => 'bg-[#F2F2F2] text-[#8A8272]',
                    ];
                @endphp

                @if ($recentTransactions->isNotEmpty())
                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-x-6">
                        @foreach ($recentTransactions as $trx)
                            <a href="{{ route('transactions.show', $trx) }}" class="flex items-center justify-between py-2.5 border-b border-[#EFEAE0] hover:bg-[#F6F3EC] -mx-2 px-2 rounded-lg">
                                <div class="min-w-0 flex items-center gap-2">
                                    <span class="shrink-0 text-[10px] font-medium uppercase px-1.5 py-0.5 rounded {{ $statusBadge[$trx->status] ?? 'bg-[#F2F2F2] text-[#8A8272]' }}">{{ $trx->status }}</span>
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-[#1F2A24] truncate">{{ $trx->invoice_number }}</p>
                                        <p class="text-xs text-[#8A8272]">{{ $trx->customer->name ?? 'Pelanggan Umum' }} &middot; {{ $trx->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <p class="text-sm font-medium text-[#1F2A24] shrink-0">Rp {{ number_format($trx->total, 0, ',', '.') }}</p>
                            </a>
                        @endforeach
                    </div>
                @else
                    <div class="flex flex-col items-center justify-center text-center py-8">
                        <div class="w-11 h-11 rounded-full bg-[#F6F3EC] text-[#8A8272] flex items-center justify-center mb-3">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m4 0V7a2 2 0 00-2-2H9a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2z" />
                            </svg>
                        </div>
                        <p class="text-sm text-[#8A8272]">Belum ada transaksi hari ini</p>
                        <a href="{{ route('transactions.create') }}" class="text-sm text-[#B5842A] font-medium hover:text-[#8A6420] mt-1">Buat transaksi pertama &rarr;</a>
                    </div>
                @endif
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.4/chart.umd.min.js"></script>
    <script>
        const salesTrend = @json($salesTrend);

        new Chart(document.getElementById('salesTrendChart'), {
            data: {
                labels: salesTrend.map(d => d.tanggal),
                datasets: [
                    {
                        type: 'line',
                        label: 'Omzet (Lunas)',
                        data: salesTrend.map(d => d.omzet),
                        borderColor: '#D4A73C',
                        backgroundColor: 'rgba(212, 167, 60, 0.12)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointBackgroundColor: '#D4A73C',
                        order: 1,
                    },
                    {
                        type: 'bar',
                        label: 'Piutang Baru',
                        data: salesTrend.map(d => d.piutang_baru),
                        backgroundColor: 'rgba(181, 72, 46, 0.35)',
                        borderColor: '#B5482E',
                        borderWidth: 1,
                        order: 2,
                    },
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top' },
                    tooltip: {
                        callbacks: {
                            label: (ctx) => `${ctx.dataset.label}: Rp ${new Intl.NumberFormat('id-ID').format(ctx.parsed.y)}`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value)
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-app-layout>