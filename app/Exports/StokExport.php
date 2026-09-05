<?php

namespace App\Exports;

use App\Models\Product;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class StokExport implements FromCollection, WithHeadings, WithMapping
{
    public function collection()
    {
        return Product::orderBy('name')->get();
    }

    public function headings(): array
    {
        return ['Produk', 'Stok', 'Min Stok', 'Status', 'Harga Modal', 'Harga Jual', 'Nilai Modal', 'Nilai Jual'];
    }

    public function map($p): array
    {
        return [
            $p->name,
            $p->stock,
            $p->min_stock,
            $p->stock <= $p->min_stock ? 'Menipis' : 'Aman',
            $p->price_modal,
            $p->price_jual,
            $p->stock * $p->price_modal,
            $p->stock * $p->price_jual,
        ];
    }
}