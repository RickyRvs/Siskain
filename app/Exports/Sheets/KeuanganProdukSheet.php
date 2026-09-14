<?php

namespace App\Exports\Sheets;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class KeuanganProdukSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    public function __construct(protected Collection $productRecap) {}

    public function title(): string
    {
        return 'Produk Terlaris';
    }

    public function collection(): Collection
    {
        return $this->productRecap;
    }

    public function headings(): array
    {
        return ['Produk', 'Qty Terjual', 'Omzet', 'Modal', 'Profit', 'Margin (%)'];
    }

    public function map($row): array
    {
        return [
            $row['name'],
            $row['qty'],
            $row['omzet'],
            $row['modal'],
            $row['profit'],
            $row['margin'],
        ];
    }
}