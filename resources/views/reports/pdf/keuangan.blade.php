<!DOCTYPE html>
<html>
<head>
    <style>
        body { font-family: sans-serif; font-size: 12px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f2f2f2; }
        .right { text-align: right; }
    </style>
</head>
<body>
    <h2>Laporan Keuangan</h2>
    <p>{{ $start->translatedFormat('d M Y') }} - {{ $end->translatedFormat('d M Y') }}</p>

    <table>
        <tr><td>Jumlah Transaksi</td><td class="right">{{ $summary['jumlah_transaksi'] }} ({{ $summary['jumlah_lunas'] }} lunas, {{ $summary['jumlah_piutang'] }} piutang)</td></tr>
        <tr><td>Omzet (Lunas)</td><td class="right">Rp {{ number_format($summary['omzet'],0,',','.') }}</td></tr>
        <tr><td>Modal</td><td class="right">Rp {{ number_format($summary['modal'],0,',','.') }}</td></tr>
        <tr><td>Profit Kotor</td><td class="right">Rp {{ number_format($summary['profit'],0,',','.') }}</td></tr>
        <tr><td>Margin</td><td class="right">{{ $summary['margin'] }}%</td></tr>
        <tr><td>Rata-rata per Transaksi</td><td class="right">Rp {{ number_format($summary['rata_rata_transaksi'],0,',','.') }}</td></tr>
        <tr><td>Pengeluaran Operasional</td><td class="right">Rp {{ number_format($summary['pengeluaran_operasional'],0,',','.') }}</td></tr>
        <tr><td><strong>Laba Bersih</strong></td><td class="right"><strong>Rp {{ number_format($summary['laba_bersih'],0,',','.') }}</strong></td></tr>
        <tr><td>Kas Masuk (per tanggal bayar)</td><td class="right">Rp {{ number_format($summary['kas_masuk'],0,',','.') }}</td></tr>
        <tr><td>Piutang Baru Periode Ini</td><td class="right">Rp {{ number_format($summary['piutang_nilai'],0,',','.') }}</td></tr>
        <tr><td>Sisa Piutang Periode Ini</td><td class="right">Rp {{ number_format($summary['piutang_sisa'],0,',','.') }}</td></tr>
    </table>

    <h3>Rekap Harian</h3>
    <table>
        <thead>
            <tr><th>Tanggal</th><th>Transaksi</th><th>Modal</th><th>Omzet</th><th>Profit</th><th>Margin</th></tr>
        </thead>
        <tbody>
        @foreach ($dailyRecap as $row)
            <tr>
                <td>{{ $row['tanggal']->translatedFormat('d M Y') }}</td>
                <td class="right">{{ $row['jumlah_transaksi'] }}</td>
                <td class="right">Rp {{ number_format($row['modal'],0,',','.') }}</td>
                <td class="right">Rp {{ number_format($row['omzet'],0,',','.') }}</td>
                <td class="right">Rp {{ number_format($row['profit'],0,',','.') }}</td>
                <td class="right">{{ $row['margin'] }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>

    @if ($productRecap->isNotEmpty())
    <h3>Produk Terlaris</h3>
    <table>
        <thead>
            <tr><th>Produk</th><th class="right">Qty</th><th class="right">Omzet</th><th class="right">Modal</th><th class="right">Profit</th><th class="right">Margin</th></tr>
        </thead>
        <tbody>
        @foreach ($productRecap as $row)
            <tr>
                <td>{{ $row['name'] }}</td>
                <td class="right">{{ $row['qty'] }}</td>
                <td class="right">Rp {{ number_format($row['omzet'],0,',','.') }}</td>
                <td class="right">Rp {{ number_format($row['modal'],0,',','.') }}</td>
                <td class="right">Rp {{ number_format($row['profit'],0,',','.') }}</td>
                <td class="right">{{ $row['margin'] }}%</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif

    @if ($expenseRecap->isNotEmpty())
    <h3>Pengeluaran per Kategori</h3>
    <table>
        <thead>
            <tr><th>Kategori</th><th class="right">Jumlah</th><th class="right">Total</th></tr>
        </thead>
        <tbody>
        @foreach ($expenseRecap as $row)
            <tr>
                <td>{{ $row['category'] }}</td>
                <td class="right">{{ $row['jumlah'] }}</td>
                <td class="right">Rp {{ number_format($row['total'],0,',','.') }}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
    @endif
</body>
</html>