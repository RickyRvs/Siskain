<x-app-layout>
    <div class="py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

            <!-- Header + filter periode + export -->
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                <div>
                    <h2 class="text-xl font-semibold text-[#1F2A24]">Laporan & Rekap</h2>
                    <p class="text-sm text-[#8A8272] mt-0.5">
                        {{ $start->translatedFormat('d M Y') }} &mdash; {{ $end->translatedFormat('d M Y') }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('reports.index', ['period' => 'today']) }}"
                       class="px-4 py-2 text-sm font-medium rounded-lg ring-1 {{ $period === 'today' ? 'bg-[#0F2E2B] text-white ring-[#0F2E2B]' : 'bg-white text-[#1F2A24] ring-[#E7E1D3] hover:ring-[#D4A73C]/60' }}">
                        Hari Ini
                    </a>
                    <a href="{{ route('reports.index', ['period' => 'week']) }}"
                       class="px-4 py-2 text-sm font-medium rounded-lg ring-1 {{ $period === 'week' ? 'bg-[#0F2E2B] text-white ring-[#0F2E2B]' : 'bg-white text-[#1F2A24] ring-[#E7E1D3] hover:ring-[#D4A73C]/60' }}">
                        Minggu Ini
                    </a>
                    <a href="{{ route('reports.index', ['period' => 'month']) }}"
                       class="px-4 py-2 text-sm font-medium rounded-lg ring-1 {{ $period === 'month' ? 'bg-[#0F2E2B] text-white ring-[#0F2E2B]' : 'bg-white text-[#1F2A24] ring-[#E7E1D3] hover:ring-[#D4A73C]/60' }}">
                        Bulan Ini
                    </a>

                    <form action="{{ route('reports.index') }}" method="GET" class="flex items-center gap-2">
                        <input type="hidden" name="period" value="custom">
                        <input type="date" name="start" value="{{ $period === 'custom' ? $start->format('Y-m-d') : '' }}"
                               class="text-sm rounded-lg ring-1 ring-[#E7E1D3] px-3 py-2 text-[#1F2A24] focus:ring-[#D4A73C] focus:border-transparent">
                        <span class="text-sm text-[#8A8272]">s/d</span>
                        <input type="date" name="end" value="{{ $period === 'custom' ? $end->format('Y-m-d') : '' }}"
                               class="text-sm rounded-lg ring-1 ring-[#E7E1D3] px-3 py-2 text-[#1F2A24] focus:ring-[#D4A73C] focus:border-transparent">
                        <button type="submit"
                                class="px-4 py-2 text-sm font-medium rounded-lg ring-1 {{ $period === 'custom' ? 'bg-[#0F2E2B] text-white ring-[#0F2E2B]' : 'bg-white text-[#1F2A24] ring-[#E7E1D3] hover:ring-[#D4A73C]/60' }}">
                            Terapkan
                        </button>
                    </form>

                    <!-- Dropdown Export -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                                class="px-4 py-2 text-sm font-medium rounded-lg ring-1 bg-white text-[#1F2A24] ring-[#E7E1D3] hover:ring-[#D4A73C]/60 flex items-center gap-2">
                            Export
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak
                             class="absolute right-0 mt-2 w-52 bg-white rounded-lg ring-1 ring-[#E7E1D3] shadow-lg z-10 overflow-hidden">

                            <p class="px-3 pt-2 text-xs text-[#8A8272]">Keuangan</p>
                            <a href="{{ route('reports.export.pdf', ['type' => 'keuangan', 'period' => $period, 'start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')]) }}"
                               class="block px-3 py-2 text-sm hover:bg-[#F7F4EC]">PDF</a>
                            <a href="{{ route('reports.export.excel', ['type' => 'keuangan', 'period' => $period, 'start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')]) }}"
                               class="block px-3 py-2 text-sm hover:bg-[#F7F4EC]">Excel</a>

                            <p class="px-3 pt-2 text-xs text-[#8A8272] border-t border-[#EFEAE0] mt-1">Stok</p>
                            <a href="{{ route('reports.export.pdf', ['type' => 'stok']) }}"
                               class="block px-3 py-2 text-sm hover:bg-[#F7F4EC]">PDF</a>
                            <a href="{{ route('reports.export.excel', ['type' => 'stok']) }}"
                               class="block px-3 py-2 text-sm hover:bg-[#F7F4EC]">Excel</a>

                            <p class="px-3 pt-2 text-xs text-[#8A8272] border-t border-[#EFEAE0] mt-1">Piutang</p>
                            <a href="{{ route('reports.export.pdf', ['type' => 'piutang', 'period' => $period, 'start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')]) }}"
                               class="block px-3 py-2 text-sm hover:bg-[#F7F4EC]">PDF</a>
                            <a href="{{ route('reports.export.excel', ['type' => 'piutang', 'period' => $period, 'start' => $start->format('Y-m-d'), 'end' => $end->format('Y-m-d')]) }}"
                               class="block px-3 py-2 text-sm hover:bg-[#F7F4EC]">Excel</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Catatan definisi -->
            <div class="bg-[#FBF7EA] ring-1 ring-[#EAD9A0] rounded-xl px-4 py-3 text-xs text-[#6B5E33]">
                <strong>Omzet & Profit</strong> di bawah cuma dihitung dari transaksi yang sudah <strong>lunas</strong>.
                Transaksi <strong>piutang</strong> belum diakui sebagai omzet — nilainya ditampilkan terpisah di kartu "Piutang" dan tabel detail piutang, sampai statusnya berubah jadi lunas.
            </div>

            <!-- Kartu ringkasan: Omzet (Lunas) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <p class="text-xs text-[#8A8272] mb-1">Omzet (Lunas)</p>
                    <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <p class="text-xs text-[#8A8272] mb-1">Total Modal (Lunas)</p>
                    <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($summary['modal'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <p class="text-xs text-[#8A8272] mb-1">Profit Kotor</p>
                    <p class="text-xl font-semibold {{ $summary['profit'] >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">
                        Rp {{ number_format($summary['profit'], 0, ',', '.') }}
                    </p>
                    <p class="text-xs text-[#8A8272] mt-1">Margin {{ $summary['margin'] }}%</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <p class="text-xs text-[#8A8272] mb-1">Transaksi Lunas</p>
                    <p class="text-xl font-semibold text-[#1F2A24]">{{ $summary['jumlah_lunas'] }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">
                        Rata-rata Rp {{ number_format($summary['rata_rata_transaksi'], 0, ',', '.') }}/transaksi
                        &middot; {{ $summary['jumlah_piutang'] }} transaksi piutang
                    </p>
                </div>
            </div>

            <!-- Kartu ringkasan: Piutang & Kas -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <p class="text-xs text-[#8A8272] mb-1">Piutang Baru (periode ini)</p>
                    <p class="text-xl font-semibold text-[#B5482E]">Rp {{ number_format($summary['piutang_nilai'], 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">Belum masuk omzet</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <p class="text-xs text-[#8A8272] mb-1">Sudah Dicicil (dari piutang baru)</p>
                    <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($summary['piutang_sudah_dibayar'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <p class="text-xs text-[#8A8272] mb-1">Sisa Piutang Belum Tertagih</p>
                    <p class="text-xl font-semibold text-[#B5482E]">Rp {{ number_format($summary['piutang_sisa'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <p class="text-xs text-[#8A8272] mb-1">Kas Masuk (periode ini)</p>
                    <p class="text-xl font-semibold text-[#2F6F4E]">Rp {{ number_format($summary['kas_masuk'], 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-1">Termasuk cicilan piutang lama</p>
                </div>
            </div>

            <!-- Rincian subtotal, diskon, pajak, biaya tambahan (Lunas) -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4">
                    <p class="text-xs text-[#8A8272] mb-1">Subtotal Penjualan</p>
                    <p class="text-base font-medium text-[#1F2A24]">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4">
                    <p class="text-xs text-[#8A8272] mb-1">Total Diskon</p>
                    <p class="text-base font-medium text-[#1F2A24]">Rp {{ number_format($summary['diskon'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4">
                    <p class="text-xs text-[#8A8272] mb-1">Total Pajak</p>
                    <p class="text-base font-medium text-[#1F2A24]">Rp {{ number_format($summary['pajak'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4">
                    <p class="text-xs text-[#8A8272] mb-1">Biaya Tambahan</p>
                    <p class="text-base font-medium text-[#1F2A24]">Rp {{ number_format($summary['biaya_tambahan'], 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- Grafik tren -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                <h3 class="text-sm font-medium text-[#8A8272] mb-4">Tren Omzet, Modal, Profit & Piutang Baru</h3>
                <div class="h-72">
                    <canvas id="reportTrendChart"></canvas>
                </div>
            </div>

            <!-- Breakdown metode pembayaran & status -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Metode Pembayaran (Lunas)</h3>
                    @forelse ($paymentRecap as $row)
                        <div class="flex items-center justify-between py-2 border-b border-[#EFEAE0] last:border-0">
                            <p class="text-sm text-[#1F2A24] capitalize">{{ $row->payment_method }}</p>
                            <div class="text-right">
                                <p class="text-sm font-medium text-[#1F2A24]">Rp {{ number_format($row->omzet, 0, ',', '.') }}</p>
                                <p class="text-xs text-[#8A8272]">{{ $row->jumlah }} transaksi</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Belum ada data pada periode ini</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Status Transaksi</h3>
                    @forelse ($statusRecap as $row)
                        <div class="flex items-center justify-between py-2 border-b border-[#EFEAE0] last:border-0">
                            <p class="text-sm text-[#1F2A24] capitalize">{{ $row->status }}</p>
                            <div class="text-right">
                                <p class="text-sm font-medium text-[#1F2A24]">Rp {{ number_format($row->nilai, 0, ',', '.') }}</p>
                                <p class="text-xs text-[#8A8272]">{{ $row->jumlah }} transaksi</p>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Belum ada data pada periode ini</p>
                    @endforelse
                </div>
            </div>

            <!-- Kontribusi produk -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 overflow-x-auto">
                <h3 class="text-sm font-medium text-[#8A8272] mb-3">Kontribusi Produk &mdash; Top 15 (Lunas)</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-[#8A8272] border-b border-[#EFEAE0]">
                            <th class="py-2 pr-3 font-medium">Produk</th>
                            <th class="py-2 pr-3 font-medium text-right">Qty Terjual</th>
                            <th class="py-2 pr-3 font-medium text-right">Modal</th>
                            <th class="py-2 pr-3 font-medium text-right">Omzet</th>
                            <th class="py-2 pr-3 font-medium text-right">Profit</th>
                            <th class="py-2 font-medium text-right">Margin</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($productRecap as $p)
                            <tr class="border-b border-[#EFEAE0] last:border-0">
                                <td class="py-2 pr-3 text-[#1F2A24]">{{ $p['name'] }}</td>
                                <td class="py-2 pr-3 text-right text-[#1F2A24]">{{ $p['qty'] }}</td>
                                <td class="py-2 pr-3 text-right text-[#8A8272]">Rp {{ number_format($p['modal'], 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right text-[#1F2A24]">Rp {{ number_format($p['omzet'], 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right {{ $p['profit'] >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">Rp {{ number_format($p['profit'], 0, ',', '.') }}</td>
                                <td class="py-2 text-right text-[#8A8272]">{{ $p['margin'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-[#8A8272]">Belum ada penjualan lunas pada periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Rekap harian -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 overflow-x-auto">
                <h3 class="text-sm font-medium text-[#8A8272] mb-3">Rekap Harian</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-[#8A8272] border-b border-[#EFEAE0]">
                            <th class="py-2 pr-3 font-medium">Tanggal</th>
                            <th class="py-2 pr-3 font-medium text-right">Lunas</th>
                            <th class="py-2 pr-3 font-medium text-right">Piutang</th>
                            <th class="py-2 pr-3 font-medium text-right">Modal</th>
                            <th class="py-2 pr-3 font-medium text-right">Omzet</th>
                            <th class="py-2 pr-3 font-medium text-right">Profit</th>
                            <th class="py-2 pr-3 font-medium text-right">Margin</th>
                            <th class="py-2 font-medium text-right">Piutang Baru</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($dailyRecap as $row)
                            <tr class="border-b border-[#EFEAE0] last:border-0">
                                <td class="py-2 pr-3 text-[#1F2A24]">{{ $row['tanggal']->translatedFormat('D, d M Y') }}</td>
                                <td class="py-2 pr-3 text-right text-[#1F2A24]">{{ $row['jumlah_lunas'] }}</td>
                                <td class="py-2 pr-3 text-right text-[#8A8272]">{{ $row['jumlah_piutang'] }}</td>
                                <td class="py-2 pr-3 text-right text-[#8A8272]">Rp {{ number_format($row['modal'], 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right text-[#1F2A24]">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right {{ $row['profit'] >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">Rp {{ number_format($row['profit'], 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right text-[#8A8272]">{{ $row['margin'] }}%</td>
                                <td class="py-2 text-right text-[#B5482E]">Rp {{ number_format($row['piutang_baru'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="py-8 text-center text-[#8A8272]">Belum ada data pada periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Detail piutang periode ini -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 overflow-x-auto">
                <h3 class="text-sm font-medium text-[#8A8272] mb-3">Detail Piutang &mdash; Transaksi Belum Lunas (periode ini)</h3>
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-xs text-[#8A8272] border-b border-[#EFEAE0]">
                            <th class="py-2 pr-3 font-medium">Tanggal</th>
                            <th class="py-2 pr-3 font-medium">No Invoice</th>
                            <th class="py-2 pr-3 font-medium">Pelanggan</th>
                            <th class="py-2 pr-3 font-medium text-right">Total</th>
                            <th class="py-2 pr-3 font-medium text-right">Sudah Dibayar</th>
                            <th class="py-2 font-medium text-right">Sisa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($piutangRecap as $row)
                            <tr class="border-b border-[#EFEAE0] last:border-0">
                                <td class="py-2 pr-3 text-[#1F2A24]">{{ $row['tanggal']->translatedFormat('d M Y') }}</td>
                                <td class="py-2 pr-3 text-[#8A8272]">{{ $row['invoice'] }}</td>
                                <td class="py-2 pr-3 text-[#1F2A24]">{{ $row['customer'] }}</td>
                                <td class="py-2 pr-3 text-right text-[#1F2A24]">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right text-[#2F6F4E]">Rp {{ number_format($row['dibayar'], 0, ',', '.') }}</td>
                                <td class="py-2 text-right text-[#B5482E]">Rp {{ number_format($row['sisa'], 0, ',', '.') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="py-8 text-center text-[#8A8272]">Tidak ada piutang pada periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

        </div>
    </div>

    @php
        $chartData = $dailyRecap->map(function ($r) {
            return [
                'label' => $r['tanggal']->translatedFormat('d M'),
                'omzet' => $r['omzet'],
                'modal' => $r['modal'],
                'profit' => $r['profit'],
                'piutang_baru' => $r['piutang_baru'],
            ];
        })->values();
    @endphp

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const dailyRecap = @json($chartData);

            new Chart(document.getElementById('reportTrendChart'), {
                data: {
                    labels: dailyRecap.map(d => d.label),
                    datasets: [
                        {
                            type: 'line',
                            label: 'Omzet (Lunas)',
                            data: dailyRecap.map(d => d.omzet),
                            borderColor: '#D4A73C',
                            backgroundColor: 'rgba(212,167,60,0.08)',
                            borderWidth: 2,
                            tension: 0.3,
                            pointRadius: 2,
                            pointHoverRadius: 4,
                            fill: false,
                        },
                        {
                            type: 'line',
                            label: 'Modal',
                            data: dailyRecap.map(d => d.modal),
                            borderColor: '#B08D57',
                            backgroundColor: 'rgba(176,141,87,0.08)',
                            borderWidth: 2,
                            borderDash: [5, 4],
                            tension: 0.3,
                            pointRadius: 2,
                            pointHoverRadius: 4,
                            fill: false,
                        },
                        {
                            type: 'line',
                            label: 'Profit',
                            data: dailyRecap.map(d => d.profit),
                            borderColor: '#2F6F4E',
                            backgroundColor: 'rgba(47,111,78,0.12)',
                            borderWidth: 2,
                            tension: 0.3,
                            pointRadius: 2,
                            pointHoverRadius: 4,
                            fill: true,
                        },
                        {
                            type: 'bar',
                            label: 'Piutang Baru',
                            data: dailyRecap.map(d => d.piutang_baru),
                            backgroundColor: 'rgba(181,72,46,0.35)',
                            borderColor: '#B5482E',
                            borderWidth: 1,
                            order: 10,
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
                            ticks: { callback: (value) => 'Rp ' + new Intl.NumberFormat('id-ID').format(value) }
                        }
                    }
                }
            });
        });
    </script>
@endpush
</x-app-layout>