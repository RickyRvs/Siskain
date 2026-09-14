<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class StokExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
     * @param Collection $stockRecap hasil dari ReportController::buildStockRecap()
     */
    public function __construct(protected Collection $stockRecap) {}

    public function title(): string
    {
        return 'Stok';
    }

    public function collection(): Collection
    {
        return $this->stockRecap;
    }

    public function headings(): array
    {
        return ['Produk', 'Stok', 'Min Stok', 'Status', 'Harga Modal', 'Harga Jual', 'Nilai Modal', 'Nilai Jual'];
    }

    public function map($p): array
    {
        return [
            $p['name'],
            $p['stock'],
            $p['min_stock'],
            $p['status'],
            $p['nilai_modal'] > 0 ? $p['nilai_modal'] / max($p['stock'], 1) : 0,
            $p['nilai_jual'] > 0 ? $p['nilai_jual'] / max($p['stock'], 1) : 0,
            $p['nilai_modal'],
            $p['nilai_jual'],
        ];
    }
}