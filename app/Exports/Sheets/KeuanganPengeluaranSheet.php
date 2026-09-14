<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class KeuanganPengeluaranSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected Collection $expenseRecap) {}

    public function title(): string
    {
        return 'Pengeluaran';
    }

    public function collection(): Collection
    {
        return $this->expenseRecap;
    }

    public function headings(): array
    {
        return ['Kategori', 'Jumlah Transaksi', 'Total'];
    }

    public function map($row): array
    {
        return [
            $row['category'],
            $row['jumlah'],
            $row['total'],
        ];
    }
}