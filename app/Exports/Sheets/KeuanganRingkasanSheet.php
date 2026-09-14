<?php

namespace App\Exports\Sheets;

use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class KeuanganRingkasanSheet implements FromArray, WithHeadings, WithTitle
{
    public function __construct(protected array $summary, protected Carbon $start, protected Carbon $end) {}

    public function title(): string
    {
        return 'Ringkasan';
    }

    public function headings(): array
    {
        return ['Keterangan', 'Nilai'];
    }

    public function array(): array
    {
        $s = $this->summary;

        return [
            ['Periode', $this->start->translatedFormat('d M Y') . ' - ' . $this->end->translatedFormat('d M Y')],
            ['Jumlah Transaksi', $s['jumlah_transaksi']],
            ['- Lunas', $s['jumlah_lunas']],
            ['- Piutang', $s['jumlah_piutang']],
            ['Omzet (Lunas)', $s['omzet']],
            ['Subtotal', $s['subtotal']],
            ['Diskon', $s['diskon']],
            ['Pajak', $s['pajak']],
            ['Biaya Tambahan', $s['biaya_tambahan']],
            ['Modal (HPP)', $s['modal']],
            ['Profit Kotor', $s['profit']],
            ['Margin (%)', $s['margin']],
            ['Rata-rata per Transaksi', $s['rata_rata_transaksi']],
            ['Nilai Piutang Baru', $s['piutang_nilai']],
            ['Piutang Sudah Dibayar', $s['piutang_sudah_dibayar']],
            ['Sisa Piutang', $s['piutang_sisa']],
            ['Kas Masuk (per tgl bayar)', $s['kas_masuk']],
            ['Pengeluaran Operasional', $s['pengeluaran_operasional']],
            ['Laba Bersih', $s['laba_bersih']],
        ];
    }
}