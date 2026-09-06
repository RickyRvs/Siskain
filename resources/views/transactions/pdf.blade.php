<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Struk {{ $transaction->invoice_number }}</title>
    <style>
        @page { margin: 24px 20px; }
        body { font-family: 'Courier New', monospace; font-size: 11px; color: #111111; }
        .receipt { width: 280px; margin: 0 auto; }
        .center { text-align: center; }
        .store-name { font-size: 14px; font-weight: bold; text-transform: uppercase; }
        .muted { color: #555555; font-size: 10px; }
        .divider { border-top: 1px dashed #000000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { padding: 2px 0; vertical-align: top; }
        .right { text-align: right; }
        .bold { font-weight: bold; }
        .item-name { padding-top: 6px; }
        .total-row td { border-top: 1px solid #000000; padding-top: 5px; font-size: 12px; }
        .section-title { font-size: 10px; font-weight: bold; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px; }
    </style>
</head>
<body>
    <div class="receipt">

        {{-- Kop toko --}}
        <div class="center">
            <div class="store-name">{{ $tenant->name ?? config('app.name') }}</div>
            @if (!empty($tenant?->address))
                <div class="muted">{{ $tenant->address }}</div>
            @endif
            @if (!empty($tenant?->phone))
                <div class="muted">{{ $tenant->phone }}</div>
            @endif
        </div>

        <div class="divider"></div>

        {{-- Info transaksi --}}
        <table>
            <tr><td>Invoice</td><td class="right bold">{{ $transaction->invoice_number }}</td></tr>
            <tr><td>Tanggal</td><td class="right">{{ $transaction->created_at->format('d/m/Y H:i') }}</td></tr>
            <tr><td>Kasir</td><td class="right">{{ $transaction->user->name }}</td></tr>
            <tr><td>Customer</td><td class="right">{{ $transaction->customer->name ?? 'Umum' }}</td></tr>
            <tr><td>Status</td><td class="right bold">{{ ucfirst($transaction->status) }}</td></tr>
        </table>

        <div class="divider"></div>

        {{-- Item --}}
        <table>
            @foreach ($transaction->items as $item)
                <tr>
                    <td colspan="2" class="item-name">
                        {{ $item->product->name }}@if ($item->variant) &mdash; {{ $item->variant->name }}@endif
                    </td>
                </tr>
                <tr>
                    <td>{{ $item->qty }} x Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                    <td class="right">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                </tr>
            @endforeach
        </table>

        <div class="divider"></div>

        {{-- Ringkasan --}}
        <table>
            <tr><td>Subtotal</td><td class="right">Rp {{ number_format($transaction->subtotal, 0, ',', '.') }}</td></tr>
            <tr><td>Diskon</td><td class="right">Rp {{ number_format($transaction->discount, 0, ',', '.') }}</td></tr>
            <tr><td>Pajak</td><td class="right">Rp {{ number_format($transaction->tax, 0, ',', '.') }}</td></tr>
            <tr><td>Biaya Tambahan</td><td class="right">Rp {{ number_format($transaction->additional_fee, 0, ',', '.') }}</td></tr>
            <tr class="total-row"><td class="bold">Total</td><td class="right bold">Rp {{ number_format($transaction->total, 0, ',', '.') }}</td></tr>
            <tr><td>Dibayar</td><td class="right">Rp {{ number_format($transaction->paid_amount, 0, ',', '.') }}</td></tr>
            <tr><td>Kembalian</td><td class="right">Rp {{ number_format($transaction->change_amount, 0, ',', '.') }}</td></tr>
            @if ($transaction->status === 'piutang')
                <tr><td class="bold">Sisa Piutang</td><td class="right bold">Rp {{ number_format($transaction->sisaPiutang(), 0, ',', '.') }}</td></tr>
            @endif
        </table>

        {{-- Riwayat pembayaran --}}
        @if ($transaction->payments->isNotEmpty())
            <div class="divider"></div>
            <div class="section-title">Riwayat Pembayaran</div>
            <table>
                @foreach ($transaction->payments as $payment)
                    <tr>
                        <td>
                            {{ $payment->paid_at?->format('d/m/Y') ?? $payment->created_at->format('d/m/Y') }}
                            @if ($payment->note) ({{ $payment->note }}) @endif
                        </td>
                        <td class="right">Rp {{ number_format($payment->amount, 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </table>
        @endif

        <div class="divider"></div>
        <div class="center muted">Terima kasih atas kunjungan Anda</div>
    </div>
</body>
</html>