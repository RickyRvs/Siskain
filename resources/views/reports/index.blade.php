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

                <div class="flex flex-col sm:flex-row sm:flex-wrap items-stretch sm:items-center gap-2">
                    <div class="flex gap-2 overflow-x-auto -mx-1 px-1 sm:mx-0 sm:px-0 sm:overflow-visible">
                        <a href="{{ route('reports.index', ['period' => 'today']) }}"
                           class="shrink-0 px-4 py-2 text-sm font-medium rounded-lg ring-1 {{ $period === 'today' ? 'bg-[#0F2E2B] text-white ring-[#0F2E2B]' : 'bg-white text-[#1F2A24] ring-[#E7E1D3] hover:ring-[#D4A73C]/60' }}">
                            Hari Ini
                        </a>
                        <a href="{{ route('reports.index', ['period' => 'week']) }}"
                           class="shrink-0 px-4 py-2 text-sm font-medium rounded-lg ring-1 {{ $period === 'week' ? 'bg-[#0F2E2B] text-white ring-[#0F2E2B]' : 'bg-white text-[#1F2A24] ring-[#E7E1D3] hover:ring-[#D4A73C]/60' }}">
                            Minggu Ini
                        </a>
                        <a href="{{ route('reports.index', ['period' => 'month']) }}"
                           class="shrink-0 px-4 py-2 text-sm font-medium rounded-lg ring-1 {{ $period === 'month' ? 'bg-[#0F2E2B] text-white ring-[#0F2E2B]' : 'bg-white text-[#1F2A24] ring-[#E7E1D3] hover:ring-[#D4A73C]/60' }}">
                            Bulan Ini
                        </a>
                    </div>

                    <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap items-center gap-2">
                        <input type="hidden" name="period" value="custom">
                        <input type="date" name="start" value="{{ $period === 'custom' ? $start->format('Y-m-d') : '' }}"
                               class="flex-1 min-w-[9.5rem] sm:flex-none sm:w-auto text-sm rounded-lg ring-1 ring-[#E7E1D3] px-3 py-2 text-[#1F2A24] focus:ring-[#D4A73C] focus:border-transparent">
                        <span class="text-sm text-[#8A8272]">s/d</span>
                        <input type="date" name="end" value="{{ $period === 'custom' ? $end->format('Y-m-d') : '' }}"
                               class="flex-1 min-w-[9.5rem] sm:flex-none sm:w-auto text-sm rounded-lg ring-1 ring-[#E7E1D3] px-3 py-2 text-[#1F2A24] focus:ring-[#D4A73C] focus:border-transparent">
                        <button type="submit"
                                class="shrink-0 px-4 py-2 text-sm font-medium rounded-lg ring-1 {{ $period === 'custom' ? 'bg-[#0F2E2B] text-white ring-[#0F2E2B]' : 'bg-white text-[#1F2A24] ring-[#E7E1D3] hover:ring-[#D4A73C]/60' }}">
                            Terapkan
                        </button>
                    </form>

                    <!-- Dropdown Export -->
                    <div class="relative" x-data="{ open: false }">
                        <button @click="open = !open" type="button"
                                class="w-full sm:w-auto px-4 py-2 text-sm font-medium rounded-lg ring-1 bg-white text-[#1F2A24] ring-[#E7E1D3] hover:ring-[#D4A73C]/60 flex items-center justify-center sm:justify-start gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v2a2 2 0 002 2h12a2 2 0 002-2v-2M7 10l5 5 5-5M12 15V3" />
                            </svg>
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

            <!-- Navigasi cepat antar section -->
            <div class="flex gap-2 overflow-x-auto -mx-1 px-1 pb-1">
                <a href="#ringkasan" class="shrink-0 px-3 py-1.5 text-xs font-medium rounded-full bg-white ring-1 ring-[#E7E1D3] text-[#6B6456] hover:ring-[#D4A73C]/60 hover:text-[#1F2A24] transition">Ringkasan</a>
                <a href="#grafik" class="shrink-0 px-3 py-1.5 text-xs font-medium rounded-full bg-white ring-1 ring-[#E7E1D3] text-[#6B6456] hover:ring-[#D4A73C]/60 hover:text-[#1F2A24] transition">Grafik Tren</a>
                <a href="#pembayaran" class="shrink-0 px-3 py-1.5 text-xs font-medium rounded-full bg-white ring-1 ring-[#E7E1D3] text-[#6B6456] hover:ring-[#D4A73C]/60 hover:text-[#1F2A24] transition">Pembayaran & Status</a>
                <a href="#produk" class="shrink-0 px-3 py-1.5 text-xs font-medium rounded-full bg-white ring-1 ring-[#E7E1D3] text-[#6B6456] hover:ring-[#D4A73C]/60 hover:text-[#1F2A24] transition">Kontribusi Produk</a>
                <a href="#harian" class="shrink-0 px-3 py-1.5 text-xs font-medium rounded-full bg-white ring-1 ring-[#E7E1D3] text-[#6B6456] hover:ring-[#D4A73C]/60 hover:text-[#1F2A24] transition">Rekap Harian</a>
                <a href="#piutang" class="shrink-0 px-3 py-1.5 text-xs font-medium rounded-full bg-white ring-1 ring-[#E7E1D3] text-[#6B6456] hover:ring-[#D4A73C]/60 hover:text-[#1F2A24] transition">Detail Piutang</a>
            </div>

            <!-- Catatan definisi -->
            <div class="flex items-start gap-3 bg-[#FBF7EA] ring-1 ring-[#EAD9A0] rounded-xl px-4 py-3 text-xs text-[#6B5E33]">
                <svg class="w-4 h-4 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                <p>
                    <strong>Omzet & Profit</strong> di bawah cuma dihitung dari transaksi yang sudah <strong>lunas</strong>.
                    Transaksi <strong>piutang</strong> belum diakui sebagai omzet — nilainya ditampilkan terpisah di kartu "Piutang" dan tabel detail piutang, sampai statusnya berubah jadi lunas.
                </p>
            </div>

            <div id="ringkasan" class="scroll-mt-6 space-y-4">

                <!-- Kartu ringkasan utama: Omzet, Modal, Profit, Transaksi -->
                <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#FBF0DA] text-[#B5842A] flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a1 1 0 011-1h1a1 1 0 011 1v6m4 0v-9a1 1 0 011-1h1a1 1 0 011 1v9M5 19v-3a1 1 0 011-1h1a1 1 0 011 1v3M3 19h18" />
                                </svg>
                            </span>
                            <p class="text-xs text-[#8A8272]">Omzet (Lunas)</p>
                        </div>
                        <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#E9F1F1] text-[#1B6E6E] flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
                                </svg>
                            </span>
                            <p class="text-xs text-[#8A8272]">Total Modal (Lunas)</p>
                        </div>
                        <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($summary['modal'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center gap-3">
                                <span class="w-9 h-9 shrink-0 rounded-lg {{ $summary['profit'] >= 0 ? 'bg-[#EAF3EE] text-[#2F6F4E]' : 'bg-[#FBEAE6] text-[#B5482E]' }} flex items-center justify-center">
                                    <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />
                                    </svg>
                                </span>
                                <p class="text-xs text-[#8A8272]">Profit Kotor</p>
                            </div>
                            <span class="text-[10px] font-medium px-1.5 py-0.5 rounded {{ $summary['profit'] >= 0 ? 'bg-[#EAF3EE] text-[#2F6F4E]' : 'bg-[#FBEAE6] text-[#B5482E]' }}">{{ $summary['margin'] }}% margin</span>
                        </div>
                        <p class="text-xl font-semibold {{ $summary['profit'] >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">
                            Rp {{ number_format($summary['profit'], 0, ',', '.') }}
                        </p>
                    </div>
                    <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#FBEAE6] text-[#B5482E] flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-6a2 2 0 012-2h2a2 2 0 012 2v6m4 0V7a2 2 0 00-2-2H9a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2z" />
                                </svg>
                            </span>
                            <p class="text-xs text-[#8A8272]">Transaksi Lunas</p>
                        </div>
                        <p class="text-xl font-semibold text-[#1F2A24]">{{ $summary['jumlah_lunas'] }}</p>
                        <p class="text-xs text-[#8A8272] mt-1">
                            Rata-rata Rp {{ number_format($summary['rata_rata_transaksi'], 0, ',', '.') }}/transaksi
                            &middot; {{ $summary['jumlah_piutang'] }} transaksi piutang
                        </p>
                    </div>
                </div>

                <!-- Kartu ringkasan: Piutang & Kas -->
                <div class="grid grid-cols-2 xl:grid-cols-4 gap-4">
                    <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#FBEAE6] text-[#B5482E] flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5.07 19h13.86c1.54 0 2.5-1.67 1.73-3L13.73 4c-.77-1.33-2.69-1.33-3.46 0L3.34 16c-.77 1.33.19 3 1.73 3z" />
                                </svg>
                            </span>
                            <p class="text-xs text-[#8A8272]">Piutang Baru (periode ini)</p>
                        </div>
                        <p class="text-xl font-semibold text-[#B5482E]">Rp {{ number_format($summary['piutang_nilai'], 0, ',', '.') }}</p>
                        <p class="text-xs text-[#8A8272] mt-1">Belum masuk omzet</p>
                    </div>
                    <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#EAF3EE] text-[#2F6F4E] flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </span>
                            <p class="text-xs text-[#8A8272]">Sudah Dicicil (dari piutang baru)</p>
                        </div>
                        <p class="text-xl font-semibold text-[#1F2A24]">Rp {{ number_format($summary['piutang_sudah_dibayar'], 0, ',', '.') }}</p>
                        @php
                            $piutangProgress = $summary['piutang_nilai'] > 0
                                ? min(100, round(($summary['piutang_sudah_dibayar'] / $summary['piutang_nilai']) * 100))
                                : 0;
                        @endphp
                        <div class="h-1.5 rounded-full bg-[#F6F3EC] overflow-hidden mt-2">
                            <div class="h-full bg-[#2F6F4E] rounded-full" style="width: {{ $piutangProgress }}%"></div>
                        </div>
                    </div>
                    <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#FBEAE6] text-[#B5482E] flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m5-4a4 4 0 100-8 4 4 0 000 8zm6 8v-2a4 4 0 00-4-4h-4a4 4 0 00-4 4v2" />
                                </svg>
                            </span>
                            <p class="text-xs text-[#8A8272]">Sisa Piutang Belum Tertagih</p>
                        </div>
                        <p class="text-xl font-semibold text-[#B5482E]">Rp {{ number_format($summary['piutang_sisa'], 0, ',', '.') }}</p>
                    </div>
                    <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="w-9 h-9 shrink-0 rounded-lg bg-[#EAF3EE] text-[#2F6F4E] flex items-center justify-center">
                                <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12V7H5a2 2 0 010-4h14v4M3 5v14a2 2 0 002 2h16v-5M18 12a2 2 0 000 4h4v-4h-4z" />
                                </svg>
                            </span>
                            <p class="text-xs text-[#8A8272]">Kas Masuk (periode ini)</p>
                        </div>
                        <p class="text-xl font-semibold text-[#2F6F4E]">Rp {{ number_format($summary['kas_masuk'], 0, ',', '.') }}</p>
                        <p class="text-xs text-[#8A8272] mt-1">Termasuk cicilan piutang lama</p>
                    </div>
                </div>

                <!-- Rincian penjualan gaya struk -->
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-4">Rincian Penjualan (Lunas)</h3>
                    <div class="max-w-md font-mono text-sm space-y-2.5">
                        <div class="flex flex-col sm:flex-row sm:items-baseline gap-0.5 sm:gap-2">
                            <span class="text-[#6B6456] shrink-0">Subtotal Penjualan</span>
                            <span class="hidden sm:block flex-1 border-b border-dotted border-[#D8D2C2] translate-y-[-3px]"></span>
                            <span class="text-[#1F2A24] sm:text-right">Rp {{ number_format($summary['subtotal'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-baseline gap-0.5 sm:gap-2">
                            <span class="text-[#6B6456] shrink-0">Total Diskon</span>
                            <span class="hidden sm:block flex-1 border-b border-dotted border-[#D8D2C2] translate-y-[-3px]"></span>
                            <span class="text-[#B5482E] sm:text-right">&minus; Rp {{ number_format($summary['diskon'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-baseline gap-0.5 sm:gap-2">
                            <span class="text-[#6B6456] shrink-0">Total Pajak</span>
                            <span class="hidden sm:block flex-1 border-b border-dotted border-[#D8D2C2] translate-y-[-3px]"></span>
                            <span class="text-[#1F2A24] sm:text-right">+ Rp {{ number_format($summary['pajak'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-baseline gap-0.5 sm:gap-2">
                            <span class="text-[#6B6456] shrink-0">Biaya Tambahan</span>
                            <span class="hidden sm:block flex-1 border-b border-dotted border-[#D8D2C2] translate-y-[-3px]"></span>
                            <span class="text-[#1F2A24] sm:text-right">+ Rp {{ number_format($summary['biaya_tambahan'], 0, ',', '.') }}</span>
                        </div>
                        <div class="flex flex-col sm:flex-row sm:items-baseline gap-0.5 sm:gap-2 pt-2 border-t border-[#EFEAE0] font-semibold">
                            <span class="text-[#1F2A24] shrink-0">Omzet (Lunas)</span>
                            <span class="hidden sm:block flex-1 border-b border-dotted border-[#D8D2C2] translate-y-[-3px]"></span>
                            <span class="text-[#1F2A24] sm:text-right">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grafik tren -->
            <div id="grafik" class="scroll-mt-6 bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                <h3 class="text-sm font-medium text-[#8A8272] mb-4">Tren Omzet, Modal, Profit & Piutang Baru</h3>
                <div class="h-72">
                    <canvas id="reportTrendChart"></canvas>
                </div>
            </div>

            <!-- Breakdown metode pembayaran & status -->
            <div id="pembayaran" class="scroll-mt-6 grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Metode Pembayaran (Lunas)</h3>
                    @php $totalPaymentOmzet = collect($paymentRecap)->sum('omzet'); @endphp
                    @forelse ($paymentRecap as $row)
                        @php $payPercent = $totalPaymentOmzet > 0 ? round(($row->omzet / $totalPaymentOmzet) * 100) : 0; @endphp
                        <div class="py-2.5 border-b border-[#EFEAE0] last:border-0">
                            <div class="flex items-center justify-between mb-1.5">
                                <p class="text-sm text-[#1F2A24] capitalize">{{ $row->payment_method }}</p>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-[#1F2A24]">Rp {{ number_format($row->omzet, 0, ',', '.') }}</p>
                                    <p class="text-xs text-[#8A8272]">{{ $row->jumlah }} transaksi &middot; {{ $payPercent }}%</p>
                                </div>
                            </div>
                            <div class="h-1.5 rounded-full bg-[#F6F3EC] overflow-hidden">
                                <div class="h-full bg-[#D4A73C] rounded-full" style="width: {{ $payPercent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Belum ada data pada periode ini</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5">
                    <h3 class="text-sm font-medium text-[#8A8272] mb-3">Status Transaksi</h3>
                    @php
                        $totalStatusNilai = collect($statusRecap)->sum('nilai');
                        $statusColor = [
                            'lunas' => '#2F6F4E',
                            'piutang' => '#B5482E',
                            'batal' => '#8A8272',
                        ];
                    @endphp
                    @forelse ($statusRecap as $row)
                        @php
                            $statusPercent = $totalStatusNilai > 0 ? round(($row->nilai / $totalStatusNilai) * 100) : 0;
                            $barColor = $statusColor[$row->status] ?? '#8A8272';
                        @endphp
                        <div class="py-2.5 border-b border-[#EFEAE0] last:border-0">
                            <div class="flex items-center justify-between mb-1.5">
                                <p class="text-sm text-[#1F2A24] capitalize">{{ $row->status }}</p>
                                <div class="text-right">
                                    <p class="text-sm font-medium text-[#1F2A24]">Rp {{ number_format($row->nilai, 0, ',', '.') }}</p>
                                    <p class="text-xs text-[#8A8272]">{{ $row->jumlah }} transaksi &middot; {{ $statusPercent }}%</p>
                                </div>
                            </div>
                            <div class="h-1.5 rounded-full bg-[#F6F3EC] overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ $statusPercent }}%; background-color: {{ $barColor }}"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-[#8A8272] py-6 text-center">Belum ada data pada periode ini</p>
                    @endforelse
                </div>
            </div>

            <!-- Kontribusi produk -->
            <div id="produk" class="scroll-mt-6 bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 overflow-x-auto">
                <h3 class="text-sm font-medium text-[#8A8272] mb-3">Kontribusi Produk &mdash; Top 15 (Lunas)</h3>
                <table class="w-full min-w-[760px] text-sm">
                    <thead>
                        <tr class="text-left text-xs text-[#8A8272] border-b border-[#EFEAE0]">
                            <th class="py-2 pr-3 font-medium w-8">#</th>
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
                            @php
                                $rankStyle = match (true) {
                                    $loop->index === 0 => 'bg-[#FBF0DA] text-[#B5842A]',
                                    $loop->index === 1 => 'bg-[#F2F0EC] text-[#6B6456]',
                                    $loop->index === 2 => 'bg-[#F5E3D3] text-[#9C5B2E]',
                                    default => 'bg-transparent text-[#8A8272]',
                                };
                            @endphp
                            <tr class="border-b border-[#EFEAE0] last:border-0 hover:bg-[#FAF8F3]">
                                <td class="py-2 pr-3">
                                    <span class="inline-flex w-5 h-5 items-center justify-center rounded-full text-[11px] font-semibold {{ $rankStyle }}">{{ $loop->iteration }}</span>
                                </td>
                                <td class="py-2 pr-3 text-[#1F2A24]">{{ $p['name'] }}</td>
                                <td class="py-2 pr-3 text-right text-[#1F2A24]">{{ $p['qty'] }}</td>
                                <td class="py-2 pr-3 text-right text-[#8A8272]">Rp {{ number_format($p['modal'], 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right text-[#1F2A24]">Rp {{ number_format($p['omzet'], 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right {{ $p['profit'] >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">Rp {{ number_format($p['profit'], 0, ',', '.') }}</td>
                                <td class="py-2 text-right text-[#8A8272]">{{ $p['margin'] }}%</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="py-8 text-center text-[#8A8272]">Belum ada penjualan lunas pada periode ini</td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if (collect($productRecap)->isNotEmpty())
                        <tfoot>
                            <tr class="border-t-2 border-[#E7E1D3] font-semibold text-[#1F2A24]">
                                <td class="py-2 pr-3"></td>
                                <td class="py-2 pr-3">Total</td>
                                <td class="py-2 pr-3 text-right">{{ collect($productRecap)->sum('qty') }}</td>
                                <td class="py-2 pr-3 text-right text-[#8A8272]">Rp {{ number_format(collect($productRecap)->sum('modal'), 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right">Rp {{ number_format(collect($productRecap)->sum('omzet'), 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right {{ collect($productRecap)->sum('profit') >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">Rp {{ number_format(collect($productRecap)->sum('profit'), 0, ',', '.') }}</td>
                                <td class="py-2 text-right"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <!-- Rekap harian -->
            <div id="harian" class="scroll-mt-6 bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 overflow-x-auto">
                <h3 class="text-sm font-medium text-[#8A8272] mb-3">Rekap Harian</h3>
                <table class="w-full min-w-[860px] text-sm">
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
                            <tr class="border-b border-[#EFEAE0] last:border-0 hover:bg-[#FAF8F3]">
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
                    @if (collect($dailyRecap)->isNotEmpty())
                        <tfoot>
                            <tr class="border-t-2 border-[#E7E1D3] font-semibold text-[#1F2A24]">
                                <td class="py-2 pr-3">Total</td>
                                <td class="py-2 pr-3 text-right">{{ collect($dailyRecap)->sum('jumlah_lunas') }}</td>
                                <td class="py-2 pr-3 text-right text-[#8A8272]">{{ collect($dailyRecap)->sum('jumlah_piutang') }}</td>
                                <td class="py-2 pr-3 text-right text-[#8A8272]">Rp {{ number_format(collect($dailyRecap)->sum('modal'), 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right">Rp {{ number_format(collect($dailyRecap)->sum('omzet'), 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right {{ collect($dailyRecap)->sum('profit') >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">Rp {{ number_format(collect($dailyRecap)->sum('profit'), 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right"></td>
                                <td class="py-2 text-right text-[#B5482E]">Rp {{ number_format(collect($dailyRecap)->sum('piutang_baru'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>

            <!-- Detail piutang periode ini -->
            <div id="piutang" class="scroll-mt-6 bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-5 overflow-x-auto">
                <h3 class="text-sm font-medium text-[#8A8272] mb-3">Detail Piutang &mdash; Transaksi Belum Lunas (periode ini)</h3>
                <table class="w-full min-w-[720px] text-sm">
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
                            <tr class="border-b border-[#EFEAE0] last:border-0 hover:bg-[#FAF8F3]">
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
                    @if (collect($piutangRecap)->isNotEmpty())
                        <tfoot>
                            <tr class="border-t-2 border-[#E7E1D3] font-semibold">
                                <td class="py-2 pr-3" colspan="3">Total</td>
                                <td class="py-2 pr-3 text-right text-[#1F2A24]">Rp {{ number_format(collect($piutangRecap)->sum('total'), 0, ',', '.') }}</td>
                                <td class="py-2 pr-3 text-right text-[#2F6F4E]">Rp {{ number_format(collect($piutangRecap)->sum('dibayar'), 0, ',', '.') }}</td>
                                <td class="py-2 text-right text-[#B5482E]">Rp {{ number_format(collect($piutangRecap)->sum('sisa'), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    @endif
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