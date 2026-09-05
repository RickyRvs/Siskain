<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1F2A24; }
        h2 { margin-bottom: 2px; }
        p.subtitle { color: #8A8272; margin-top: 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #ccc; padding: 4px 6px; text-align: left; }
        th { background: #f2f2f2; }
        .right { text-align: right; }
        .sisa { color: #B5482E; font-weight: bold; }
    </style>
</head>
<body>
    <h2>Laporan Piutang</h2>
    <p class="subtitle">{{ $start->translatedFormat('d M Y') }} &mdash; {{ $end->translatedFormat('d M Y') }}</p>

    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Pelanggan</th>
                <th class="right">Total</th>
                <th class="right">Dibayar</th>
                <th class="right">Sisa</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($piutangRecap as $row)
                <tr>
                    <td>{{ $row['tanggal']->translatedFormat('d M Y') }}</td>
                    <td>{{ $row['customer'] }}</td>
                    <td class="right">Rp {{ number_format($row['total'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($row['dibayar'], 0, ',', '.') }}</td>
                    <td class="right sisa">Rp {{ number_format($row['sisa'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" style="text-align:center;">Tidak ada piutang pada periode ini</td>
                </tr>
            @endforelse
        </tbody>
        @if ($piutangRecap->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="2"><strong>Total</strong></td>
                    <td class="right"><strong>Rp {{ number_format($piutangRecap->sum('total'), 0, ',', '.') }}</strong></td>
                    <td class="right"><strong>Rp {{ number_format($piutangRecap->sum('dibayar'), 0, ',', '.') }}</strong></td>
                    <td class="right"><strong>Rp {{ number_format($piutangRecap->sum('sisa'), 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>