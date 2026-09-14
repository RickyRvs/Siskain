<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class KeuanganHarianSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected Collection $dailyRecap) {}

    public function title(): string
    {
        return 'Rekap Harian';
    }

    public function collection(): Collection
    {
        return $this->dailyRecap;
    }

    public function headings(): array
    {
        return ['Tanggal', 'Transaksi', 'Lunas', 'Piutang', 'Omzet', 'Modal', 'Profit', 'Margin (%)', 'Piutang Baru'];
    }

    public function map($row): array
    {
        return [
            $row['tanggal']->format('d-m-Y'),
            $row['jumlah_transaksi'],
            $row['jumlah_lunas'],
            $row['jumlah_piutang'],
            $row['omzet'],
            $row['modal'],
            $row['profit'],
            $row['margin'],
            $row['piutang_baru'],
        ];
    }
}