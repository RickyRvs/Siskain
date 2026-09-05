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
        <tr><td>Omzet</td><td class="right">Rp {{ number_format($summary['omzet'],0,',','.') }}</td></tr>
        <tr><td>Modal</td><td class="right">Rp {{ number_format($summary['modal'],0,',','.') }}</td></tr>
        <tr><td>Profit</td><td class="right">Rp {{ number_format($summary['profit'],0,',','.') }}</td></tr>
        <tr><td>Margin</td><td class="right">{{ $summary['margin'] }}%</td></tr>
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
</body>
</html>