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
        .status-aman { color: #2F6F4E; }
        .status-menipis { color: #B5482E; font-weight: bold; }
        .status-netral { color: #8A8272; }
    </style>
</head>
<body>
    <h2>Laporan Stok</h2>
    <p class="subtitle">Dicetak {{ now()->translatedFormat('d M Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>Produk</th>
                <th class="right">Stok</th>
                <th class="right">Min Stok</th>
                <th>Status</th>
                <th class="right">Harga Modal</th>
                <th class="right">Harga Jual</th>
                <th class="right">Nilai Modal</th>
                <th class="right">Nilai Jual</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($stockRecap as $p)
                @php
                    $tidakDilacak = $p['status'] === 'Tidak dilacak';
                    $statusClass = match ($p['status']) {
                        'Menipis' => 'status-menipis',
                        'Aman' => 'status-aman',
                        default => 'status-netral',
                    };
                @endphp
                <tr>
                    <td>{{ $p['name'] }}</td>
                    <td class="right">{{ $tidakDilacak ? '-' : $p['stock'] }}</td>
                    <td class="right">{{ $tidakDilacak ? '-' : $p['min_stock'] }}</td>
                    <td class="{{ $statusClass }}">{{ $p['status'] }}</td>
                    <td class="right">{{ $p['price_modal'] === null ? '-' : 'Rp ' . number_format($p['price_modal'], 0, ',', '.') }}</td>
                    <td class="right">{{ $p['price_jual'] === null ? '-' : 'Rp ' . number_format($p['price_jual'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($p['nilai_modal'], 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($p['nilai_jual'], 0, ',', '.') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center;">Belum ada produk</td>
                </tr>
            @endforelse
        </tbody>
        @if ($stockRecap->isNotEmpty())
            <tfoot>
                <tr>
                    <td colspan="6"><strong>Total</strong></td>
                    <td class="right"><strong>Rp {{ number_format($stockRecap->sum('nilai_modal'), 0, ',', '.') }}</strong></td>
                    <td class="right"><strong>Rp {{ number_format($stockRecap->sum('nilai_jual'), 0, ',', '.') }}</strong></td>
                </tr>
            </tfoot>
        @endif
    </table>
</body>
</html>