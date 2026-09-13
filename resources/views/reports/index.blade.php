<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-3">
            <div class="w-1.5 h-7 rounded-full bg-[#D4A73C]"></div>
            <h2 class="font-semibold text-xl text-[#1F2A24] leading-tight">Laporan &amp; Rekap</h2>
        </div>
    </x-slot>

    <div class="py-4 sm:py-6" x-data="{ tab: 'harian' }">
        <div class="max-w-7xl mx-auto px-3 sm:px-6 lg:px-8 space-y-4 sm:space-y-6">

            <!-- ==================== FILTER PERIODE ==================== -->
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4" x-data="{ showCustom: {{ $period === 'custom' ? 'true' : 'false' }} }">
                <div class="flex flex-wrap items-center gap-2">
                    <a href="{{ route('reports.index', ['period' => 'today']) }}"
                       class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition {{ $period === 'today' ? 'bg-[#1F2A24] text-white border-[#1F2A24]' : 'bg-white text-[#5B5647] border-[#DDD5C2] hover:border-[#B0A98F]' }}">
                        Hari Ini
                    </a>
                    <a href="{{ route('reports.index', ['period' => 'week']) }}"
                       class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition {{ $period === 'week' ? 'bg-[#1F2A24] text-white border-[#1F2A24]' : 'bg-white text-[#5B5647] border-[#DDD5C2] hover:border-[#B0A98F]' }}">
                        Minggu Ini
                    </a>
                    <a href="{{ route('reports.index', ['period' => 'month']) }}"
                       class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition {{ $period === 'month' ? 'bg-[#1F2A24] text-white border-[#1F2A24]' : 'bg-white text-[#5B5647] border-[#DDD5C2] hover:border-[#B0A98F]' }}">
                        Bulan Ini
                    </a>
                    <button type="button" @click="showCustom = !showCustom"
                            class="px-3.5 py-1.5 rounded-full text-sm font-medium border transition {{ $period === 'custom' ? 'bg-[#1F2A24] text-white border-[#1F2A24]' : 'bg-white text-[#5B5647] border-[#DDD5C2] hover:border-[#B0A98F]' }}">
                        Custom
                    </button>

                    <span class="ml-0 sm:ml-auto text-xs text-[#8A8272] w-full sm:w-auto">
                        Periode: <span class="font-medium text-[#1F2A24]">{{ $start->format('d/m/Y') }} &ndash; {{ $end->format('d/m/Y') }}</span>
                    </span>
                </div>

                <form method="GET" action="{{ route('reports.index') }}" x-show="showCustom" x-cloak
                      class="mt-3 pt-3 border-t border-dashed border-[#E7E1D3] flex flex-col sm:flex-row flex-wrap items-stretch sm:items-center gap-2">
                    <input type="hidden" name="period" value="custom">
                    <div class="flex items-center gap-1.5">
                        <input type="date" name="start" value="{{ request('start', $start->format('Y-m-d')) }}"
                               class="text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                        <span class="text-xs text-[#8A8272]">s/d</span>
                        <input type="date" name="end" value="{{ request('end', $end->format('Y-m-d')) }}"
                               class="text-sm rounded-lg border-[#DDD5C2] shadow-sm focus:border-[#D4A73C] focus:ring-[#D4A73C]">
                    </div>
                    <button type="submit" class="px-4 py-2 bg-[#1F2A24] text-white text-sm font-medium rounded-lg hover:bg-[#16201B] transition">
                        Terapkan
                    </button>
                </form>
            </div>

            <!-- ==================== KARTU RINGKASAN ==================== -->
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="bg-[#1F2A24] rounded-xl p-4 sm:p-5 relative overflow-hidden">
                    <div class="absolute -right-4 -bottom-4 w-20 h-20 rounded-full bg-[#D4A73C]/10"></div>
                    <p class="text-xs text-[#B9C2BC] uppercase tracking-wide mb-1">Omzet</p>
                    <p class="text-xl sm:text-2xl font-semibold text-white truncate">Rp {{ number_format($summary['omzet'], 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8FA096] mt-0.5">{{ $summary['jumlah_lunas'] }} transaksi lunas</p>
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4 sm:p-5">
                    <p class="text-xs text-[#8A8272] uppercase tracking-wide mb-1">Profit Kotor</p>
                    <p class="text-xl sm:text-2xl font-semibold text-[#2F6F4E] truncate">Rp {{ number_format($summary['profit'], 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-0.5">margin {{ $summary['margin'] }}%</p>
                    <p class="text-xs font-medium {{ $summary['laba_bersih'] >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }} mt-1 pt-1 border-t border-dashed border-[#E7E1D3]">
                        Laba Bersih: Rp {{ number_format($summary['laba_bersih'], 0, ',', '.') }}
                    </p>
                </div>

                <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4 sm:p-5">
                    <p class="text-xs text-[#8A8272] uppercase tracking-wide mb-1">Kas Masuk</p>
                    <p class="text-xl sm:text-2xl font-semibold text-[#1F2A24] truncate">Rp {{ number_format($summary['kas_masuk'], 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-0.5">termasuk cicilan piutang</p>
                </div>

                <a href="{{ route('reports.index', array_merge(request()->query(), [])) }}#piutang-tab"
                   @click.prevent="tab = 'piutang'; document.getElementById('report-tabs')?.scrollIntoView({behavior:'smooth'})"
                   class="bg-white rounded-xl ring-1 ring-[#F0CFC4] shadow-sm p-4 sm:p-5 hover:bg-[#FBEAE6]/40 transition">
                    <p class="text-xs text-[#B5482E] uppercase tracking-wide mb-1">Piutang Aktif</p>
                    <p class="text-xl sm:text-2xl font-semibold text-[#B5482E] truncate">Rp {{ number_format($summary['piutang_sisa'], 0, ',', '.') }}</p>
                    <p class="text-xs text-[#8A8272] mt-0.5">{{ $summary['jumlah_piutang'] }} invoice belum lunas</p>
                </a>
            </div>

            <!-- Baris ke-2: angka pendukung, lebih kecil -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 text-sm">
                <div class="bg-white rounded-lg ring-1 ring-[#E7E1D3] px-4 py-3">
                    <p class="text-[11px] text-[#8A8272] uppercase tracking-wide">Rata-rata / Transaksi</p>
                    <p class="font-semibold text-[#1F2A24] mt-0.5 truncate">Rp {{ number_format($summary['rata_rata_transaksi'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg ring-1 ring-[#E7E1D3] px-4 py-3">
                    <p class="text-[11px] text-[#8A8272] uppercase tracking-wide">Diskon Diberikan</p>
                    <p class="font-semibold text-[#1F2A24] mt-0.5 truncate">Rp {{ number_format($summary['diskon'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg ring-1 ring-[#E7E1D3] px-4 py-3">
                    <p class="text-[11px] text-[#8A8272] uppercase tracking-wide">Pajak Terkumpul</p>
                    <p class="font-semibold text-[#1F2A24] mt-0.5 truncate">Rp {{ number_format($summary['pajak'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg ring-1 ring-[#E7E1D3] px-4 py-3">
                    <p class="text-[11px] text-[#8A8272] uppercase tracking-wide">Piutang Sudah Dibayar</p>
                    <p class="font-semibold text-[#1F2A24] mt-0.5 truncate">Rp {{ number_format($summary['piutang_sudah_dibayar'], 0, ',', '.') }}</p>
                </div>
                <div class="bg-white rounded-lg ring-1 ring-[#E7E1D3] px-4 py-3">
                    <p class="text-[11px] text-[#8A8272] uppercase tracking-wide">Pengeluaran Operasional</p>
                    <p class="font-semibold text-[#B5482E] mt-0.5 truncate">Rp {{ number_format($summary['pengeluaran_operasional'], 0, ',', '.') }}</p>
                </div>
            </div>

            <!-- ==================== TABS DETAIL ==================== -->
            <div id="report-tabs" class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm overflow-hidden">
                <div class="flex overflow-x-auto border-b border-[#E7E1D3]" style="scrollbar-width:none">
                    <button type="button" @click="tab = 'harian'"
                            class="shrink-0 px-4 sm:px-5 py-3 text-sm font-medium border-b-2 transition"
                            :class="tab === 'harian' ? 'border-[#D4A73C] text-[#1F2A24]' : 'border-transparent text-[#8A8272] hover:text-[#1F2A24]'">
                        Rekap Harian
                    </button>
                    <button type="button" @click="tab = 'produk'"
                            class="shrink-0 px-4 sm:px-5 py-3 text-sm font-medium border-b-2 transition"
                            :class="tab === 'produk' ? 'border-[#D4A73C] text-[#1F2A24]' : 'border-transparent text-[#8A8272] hover:text-[#1F2A24]'">
                        Produk Terlaris
                    </button>
                    <button type="button" @click="tab = 'metode'"
                            class="shrink-0 px-4 sm:px-5 py-3 text-sm font-medium border-b-2 transition"
                            :class="tab === 'metode' ? 'border-[#D4A73C] text-[#1F2A24]' : 'border-transparent text-[#8A8272] hover:text-[#1F2A24]'">
                        Metode &amp; Status
                    </button>
                    <button type="button" @click="tab = 'piutang'" id="piutang-tab"
                            class="shrink-0 px-4 sm:px-5 py-3 text-sm font-medium border-b-2 transition"
                            :class="tab === 'piutang' ? 'border-[#D4A73C] text-[#1F2A24]' : 'border-transparent text-[#8A8272] hover:text-[#1F2A24]'">
                        Piutang
                    </button>
                    <button type="button" @click="tab = 'pengeluaran'"
                            class="shrink-0 px-4 sm:px-5 py-3 text-sm font-medium border-b-2 transition"
                            :class="tab === 'pengeluaran' ? 'border-[#D4A73C] text-[#1F2A24]' : 'border-transparent text-[#8A8272] hover:text-[#1F2A24]'">
                        Pengeluaran
                    </button>
                </div>

                <!-- ===== TAB: REKAP HARIAN ===== -->
                <div x-show="tab === 'harian'" x-cloak class="p-4 sm:p-5">
                    @php $maxOmzetHarian = $dailyRecap->max('omzet') ?: 1; @endphp

                    @if ($dailyRecap->isEmpty())
                        <p class="text-sm text-[#8A8272] py-8 text-center">Belum ada data pada periode ini.</p>
                    @else
                        {{-- Tabel: sm ke atas --}}
                        <div class="hidden sm:block overflow-x-auto -mx-5 px-5">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-[#8A8272] text-xs uppercase tracking-wide">
                                        <th class="pb-2 font-medium">Tanggal</th>
                                        <th class="pb-2 font-medium text-right">Transaksi</th>
                                        <th class="pb-2 font-medium">Omzet</th>
                                        <th class="pb-2 font-medium text-right">Modal</th>
                                        <th class="pb-2 font-medium text-right">Profit</th>
                                        <th class="pb-2 font-medium text-right">Margin</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EFEAE0]">
                                    @foreach ($dailyRecap as $row)
                                        <tr>
                                            <td class="py-2.5 text-[#1F2A24] whitespace-nowrap">
                                                {{ $row['tanggal']->translatedFormat('d M') }}
                                                <span class="block text-[11px] text-[#8A8272]">
                                                    {{ $row['jumlah_lunas'] }} lunas
                                                    @if ($row['jumlah_piutang'] > 0)
                                                        &middot; {{ $row['jumlah_piutang'] }} piutang
                                                    @endif
                                                </span>
                                            </td>
                                            <td class="py-2.5 text-right text-[#8A8272]">{{ $row['jumlah_transaksi'] }}</td>
                                            <td class="py-2.5">
                                                <div class="flex items-center gap-2 min-w-[160px]">
                                                    <div class="h-1.5 flex-1 rounded-full bg-[#F0ECE0] overflow-hidden">
                                                        <div class="h-full rounded-full bg-[#D4A73C]" style="width: {{ round(($row['omzet'] / $maxOmzetHarian) * 100) }}%"></div>
                                                    </div>
                                                    <span class="text-[#1F2A24] font-medium whitespace-nowrap">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</span>
                                                </div>
                                            </td>
                                            <td class="py-2.5 text-right text-[#8A8272] whitespace-nowrap">Rp {{ number_format($row['modal'], 0, ',', '.') }}</td>
                                            <td class="py-2.5 text-right font-medium whitespace-nowrap {{ $row['profit'] >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">
                                                Rp {{ number_format($row['profit'], 0, ',', '.') }}
                                            </td>
                                            <td class="py-2.5 text-right text-[#8A8272]">{{ $row['margin'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        {{-- Card list: HP --}}
                        <div class="sm:hidden divide-y divide-[#EFEAE0]">
                            @foreach ($dailyRecap as $row)
                                <div class="py-3">
                                    <div class="flex items-center justify-between gap-2 mb-1.5">
                                        <p class="text-sm font-medium text-[#1F2A24]">{{ $row['tanggal']->translatedFormat('d M Y') }}</p>
                                        <p class="text-sm font-semibold text-[#1F2A24]">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-[#F0ECE0] overflow-hidden mb-2">
                                        <div class="h-full rounded-full bg-[#D4A73C]" style="width: {{ round(($row['omzet'] / $maxOmzetHarian) * 100) }}%"></div>
                                    </div>
                                    <div class="flex flex-wrap gap-x-3 gap-y-0.5 text-xs text-[#8A8272]">
                                        <span>{{ $row['jumlah_lunas'] }} lunas @if($row['jumlah_piutang'] > 0) &middot; {{ $row['jumlah_piutang'] }} piutang @endif</span>
                                        <span>Modal Rp {{ number_format($row['modal'], 0, ',', '.') }}</span>
                                        <span class="{{ $row['profit'] >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">Profit Rp {{ number_format($row['profit'], 0, ',', '.') }} ({{ $row['margin'] }}%)</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- ===== TAB: PRODUK TERLARIS ===== -->
                <div x-show="tab === 'produk'" x-cloak class="p-4 sm:p-5">
                    @if ($productRecap->isEmpty())
                        <p class="text-sm text-[#8A8272] py-8 text-center">Belum ada produk terjual pada periode ini.</p>
                    @else
                        @php $maxOmzetProduk = $productRecap->max('omzet') ?: 1; @endphp

                        <div class="hidden sm:block overflow-x-auto -mx-5 px-5">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-[#8A8272] text-xs uppercase tracking-wide">
                                        <th class="pb-2 font-medium w-8">#</th>
                                        <th class="pb-2 font-medium">Produk</th>
                                        <th class="pb-2 font-medium text-right">Qty</th>
                                        <th class="pb-2 font-medium">Omzet</th>
                                        <th class="pb-2 font-medium text-right">Profit</th>
                                        <th class="pb-2 font-medium text-right">Margin</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EFEAE0]">
                                    @foreach ($productRecap as $i => $row)
                                        <tr>
                                            <td class="py-2.5 text-[#B0A98F]">{{ $i + 1 }}</td>
                                            <td class="py-2.5 text-[#1F2A24] font-medium whitespace-nowrap">{{ $row['name'] }}</td>
                                            <td class="py-2.5 text-right text-[#8A8272]">{{ $row['qty'] }}</td>
                                            <td class="py-2.5">
                                                <div class="flex items-center gap-2 min-w-[160px]">
                                                    <div class="h-1.5 flex-1 rounded-full bg-[#F0ECE0] overflow-hidden">
                                                        <div class="h-full rounded-full bg-[#D4A73C]" style="width: {{ round(($row['omzet'] / $maxOmzetProduk) * 100) }}%"></div>
                                                    </div>
                                                    <span class="text-[#1F2A24] whitespace-nowrap">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</span>
                                                </div>
                                            </td>
                                            <td class="py-2.5 text-right whitespace-nowrap {{ $row['profit'] >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">
                                                Rp {{ number_format($row['profit'], 0, ',', '.') }}
                                            </td>
                                            <td class="py-2.5 text-right text-[#8A8272]">{{ $row['margin'] }}%</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="sm:hidden divide-y divide-[#EFEAE0]">
                            @foreach ($productRecap as $i => $row)
                                <div class="py-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <p class="text-sm font-medium text-[#1F2A24]"><span class="text-[#B0A98F] mr-1">{{ $i + 1 }}.</span>{{ $row['name'] }}</p>
                                        <p class="text-sm font-semibold text-[#1F2A24] shrink-0">Rp {{ number_format($row['omzet'], 0, ',', '.') }}</p>
                                    </div>
                                    <div class="h-1.5 rounded-full bg-[#F0ECE0] overflow-hidden my-2">
                                        <div class="h-full rounded-full bg-[#D4A73C]" style="width: {{ round(($row['omzet'] / $maxOmzetProduk) * 100) }}%"></div>
                                    </div>
                                    <div class="flex flex-wrap gap-x-3 text-xs text-[#8A8272]">
                                        <span>{{ $row['qty'] }} terjual</span>
                                        <span class="{{ $row['profit'] >= 0 ? 'text-[#2F6F4E]' : 'text-[#B5482E]' }}">Profit Rp {{ number_format($row['profit'], 0, ',', '.') }} ({{ $row['margin'] }}%)</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- ===== TAB: METODE & STATUS ===== -->
                <div x-show="tab === 'metode'" x-cloak class="p-4 sm:p-5 space-y-6">
                    <div>
                        <p class="text-xs font-medium text-[#8A8272] uppercase tracking-wide mb-3">Metode Pembayaran (transaksi lunas)</p>
                        @if ($paymentRecap->isEmpty())
                            <p class="text-sm text-[#8A8272] py-4 text-center">Belum ada data.</p>
                        @else
                            @php
                                $methodLabel = fn ($m) => match ($m) {
                                    'tunai' => 'Tunai', 'transfer' => 'Transfer Bank', 'qris' => 'QRIS', default => 'Lainnya',
                                };
                                $maxOmzetMetode = $paymentRecap->max('omzet') ?: 1;
                            @endphp
                            <div class="space-y-2.5">
                                @foreach ($paymentRecap as $row)
                                    <div class="flex items-center gap-3">
                                        <span class="w-24 sm:w-28 shrink-0 text-sm text-[#1F2A24]">{{ $methodLabel($row->payment_method) }}</span>
                                        <div class="h-2 flex-1 rounded-full bg-[#F0ECE0] overflow-hidden">
                                            <div class="h-full rounded-full bg-[#1F2A24]" style="width: {{ round(($row->omzet / $maxOmzetMetode) * 100) }}%"></div>
                                        </div>
                                        <span class="w-28 sm:w-36 shrink-0 text-right text-sm font-medium text-[#1F2A24] whitespace-nowrap">Rp {{ number_format($row->omzet, 0, ',', '.') }}</span>
                                        <span class="w-16 shrink-0 text-right text-xs text-[#8A8272]">{{ $row->jumlah }}x</span>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <div class="pt-4 border-t border-dashed border-[#E7E1D3]">
                        <p class="text-xs font-medium text-[#8A8272] uppercase tracking-wide mb-3">Status Transaksi</p>
                        @if ($statusRecap->isEmpty())
                            <p class="text-sm text-[#8A8272] py-4 text-center">Belum ada data.</p>
                        @else
                            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                @foreach ($statusRecap as $row)
                                    @php
                                        $sBadge = match ($row->status) {
                                            'lunas' => ['bg-[#EAF3EE]', 'text-[#2F6F4E]'],
                                            'piutang' => ['bg-[#FBF0DA]', 'text-[#B5842A]'],
                                            default => ['bg-[#F6F3EC]', 'text-[#8A8272]'],
                                        };
                                    @endphp
                                    <div class="rounded-lg {{ $sBadge[0] }} px-3.5 py-3">
                                        <p class="text-xs font-medium {{ $sBadge[1] }} uppercase tracking-wide">{{ ucfirst($row->status) }}</p>
                                        <p class="text-base font-semibold {{ $sBadge[1] }} mt-0.5">{{ $row->jumlah }}<span class="text-xs font-normal"> transaksi</span></p>
                                        <p class="text-xs {{ $sBadge[1] }}/80 mt-0.5">Rp {{ number_format($row->nilai, 0, ',', '.') }}</p>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                </div>

                <!-- ===== TAB: PIUTANG ===== -->
                <div x-show="tab === 'piutang'" x-cloak class="p-4 sm:p-5">
                    @if ($piutangRecap->isEmpty())
                        <p class="text-sm text-[#8A8272] py-8 text-center">Tidak ada piutang pada periode ini.</p>
                    @else
                        <div class="hidden sm:block overflow-x-auto -mx-5 px-5">
                            <table class="min-w-full text-sm">
                                <thead>
                                    <tr class="text-left text-[#8A8272] text-xs uppercase tracking-wide">
                                        <th class="pb-2 font-medium">Invoice</th>
                                        <th class="pb-2 font-medium">Customer</th>
                                        <th class="pb-2 font-medium">Tanggal</th>
                                        <th class="pb-2 font-medium text-right">Total</th>
                                        <th class="pb-2 font-medium text-right">Dibayar</th>
                                        <th class="pb-2 font-medium text-right">Sisa</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-[#EFEAE0]">
                                    @foreach ($piutangRecap as $row)
                                        <tr>
                                            <td class="py-2.5 font-mono text-[#1F2A24] whitespace-nowrap">{{ $row['invoice'] }}</td>
                                            <td class="py-2.5 text-[#1F2A24]">{{ $row['customer'] }}</td>
                                            <td class="py-2.5 text-[#8A8272] whitespace-nowrap">{{ $row['tanggal']->format('d/m/Y') }}</td>
                                            <td class="py-2.5 text-right text-[#8A8272] whitespace-nowrap">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                                            <td class="py-2.5 text-right text-[#2F6F4E] whitespace-nowrap">Rp {{ number_format($row['dibayar'], 0, ',', '.') }}</td>
                                            <td class="py-2.5 text-right font-medium text-[#B5482E] whitespace-nowrap">Rp {{ number_format($row['sisa'], 0, ',', '.') }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="sm:hidden divide-y divide-[#EFEAE0]">
                            @foreach ($piutangRecap as $row)
                                <div class="py-3">
                                    <div class="flex items-start justify-between gap-2">
                                        <div class="min-w-0">
                                            <p class="font-mono text-sm text-[#1F2A24] truncate">{{ $row['invoice'] }}</p>
                                            <p class="text-xs text-[#8A8272]">{{ $row['customer'] }} &middot; {{ $row['tanggal']->format('d/m/Y') }}</p>
                                        </div>
                                        <p class="text-sm font-semibold text-[#B5482E] shrink-0">Rp {{ number_format($row['sisa'], 0, ',', '.') }}</p>
                                    </div>
                                    <p class="text-xs text-[#8A8272] mt-1">Total Rp {{ number_format($row['total'], 0, ',', '.') }} &middot; Dibayar Rp {{ number_format($row['dibayar'], 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- ===== TAB: PENGELUARAN ===== -->
                <div x-show="tab === 'pengeluaran'" x-cloak class="p-4 sm:p-5">
                    @if ($expenseRecap->isEmpty())
                        <p class="text-sm text-[#8A8272] py-8 text-center">Tidak ada pengeluaran pada periode ini.</p>
                    @else
                        @php $maxTotalExpense = $expenseRecap->max('total') ?: 1; @endphp
                        <div class="space-y-2.5">
                            @foreach ($expenseRecap as $row)
                                <div class="flex items-center gap-3">
                                    <span class="w-32 sm:w-44 shrink-0 text-sm text-[#1F2A24] truncate">{{ $row['category'] }}</span>
                                    <div class="h-2 flex-1 rounded-full bg-[#F0ECE0] overflow-hidden">
                                        <div class="h-full rounded-full bg-[#B5482E]" style="width: {{ round(($row['total'] / $maxTotalExpense) * 100) }}%"></div>
                                    </div>
                                    <span class="w-28 sm:w-36 shrink-0 text-right text-sm font-medium text-[#1F2A24] whitespace-nowrap">Rp {{ number_format($row['total'], 0, ',', '.') }}</span>
                                    <span class="w-16 shrink-0 text-right text-xs text-[#8A8272]">{{ $row['jumlah'] }}x</span>
                                </div>
                            @endforeach
                        </div>
                        <p class="text-xs text-[#8A8272] mt-4 pt-4 border-t border-dashed border-[#E7E1D3]">
                            Mau catat pengeluaran baru? Buka menu <a href="{{ route('expenses.index') }}" class="text-[#1B6E6E] hover:underline">Pengeluaran</a>.
                        </p>
                    @endif
                </div>
            </div>

            <!-- ==================== UNDUH LAPORAN ==================== -->
            @php
                $exportQuery = ['period' => $period];
                if ($period === 'custom') {
                    $exportQuery['start'] = $start->format('Y-m-d');
                    $exportQuery['end'] = $end->format('Y-m-d');
                }
            @endphp
            <div class="bg-white rounded-xl ring-1 ring-[#E7E1D3] shadow-sm p-4 sm:p-5">
                <p class="text-xs font-medium text-[#8A8272] uppercase tracking-wide mb-3">Unduh Laporan</p>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div class="border border-[#E7E1D3] rounded-lg p-3.5">
                        <p class="text-sm font-medium text-[#1F2A24] mb-0.5">Laporan Keuangan</p>
                        <p class="text-xs text-[#8A8272] mb-3">Omzet, modal, profit sesuai periode terpilih.</p>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.export.pdf', array_merge(['type' => 'keuangan'], $exportQuery)) }}"
                               class="flex-1 text-center px-3 py-2 bg-white ring-1 ring-[#DDD5C2] text-[#1F2A24] text-xs font-medium rounded-lg hover:bg-[#F6F3EC] transition">PDF</a>
                            <a href="{{ route('reports.export.excel', array_merge(['type' => 'keuangan'], $exportQuery)) }}"
                               class="flex-1 text-center px-3 py-2 bg-[#1F2A24] text-white text-xs font-medium rounded-lg hover:bg-[#16201B] transition">Excel</a>
                        </div>
                    </div>

                    <div class="border border-[#E7E1D3] rounded-lg p-3.5">
                        <p class="text-sm font-medium text-[#1F2A24] mb-0.5">Laporan Stok</p>
                        <p class="text-xs text-[#8A8272] mb-3">Stok &amp; nilai persediaan produk saat ini.</p>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.export.pdf', ['type' => 'stok']) }}"
                               class="flex-1 text-center px-3 py-2 bg-white ring-1 ring-[#DDD5C2] text-[#1F2A24] text-xs font-medium rounded-lg hover:bg-[#F6F3EC] transition">PDF</a>
                            <a href="{{ route('reports.export.excel', ['type' => 'stok']) }}"
                               class="flex-1 text-center px-3 py-2 bg-[#1F2A24] text-white text-xs font-medium rounded-lg hover:bg-[#16201B] transition">Excel</a>
                        </div>
                    </div>

                    <div class="border border-[#E7E1D3] rounded-lg p-3.5">
                        <p class="text-sm font-medium text-[#1F2A24] mb-0.5">Laporan Piutang</p>
                        <p class="text-xs text-[#8A8272] mb-3">Daftar piutang aktif sesuai periode terpilih.</p>
                        <div class="flex gap-2">
                            <a href="{{ route('reports.export.pdf', array_merge(['type' => 'piutang'], $exportQuery)) }}"
                               class="flex-1 text-center px-3 py-2 bg-white ring-1 ring-[#DDD5C2] text-[#1F2A24] text-xs font-medium rounded-lg hover:bg-[#F6F3EC] transition">PDF</a>
                            <a href="{{ route('reports.export.excel', array_merge(['type' => 'piutang'], $exportQuery)) }}"
                               class="flex-1 text-center px-3 py-2 bg-[#1F2A24] text-white text-xs font-medium rounded-lg hover:bg-[#16201B] transition">Excel</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>