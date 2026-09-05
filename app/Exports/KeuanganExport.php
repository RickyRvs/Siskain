<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class KeuanganExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Carbon $start, protected Carbon $end) {}

    public function collection()
    {
        return Transaction::whereBetween('created_at', [$this->start, $this->end])
            ->where('status', '!=', 'batal')
            ->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'No Transaksi', 'Metode Bayar', 'Status', 'Subtotal', 'Diskon', 'Pajak', 'Total'];
    }

    public function map($t): array
    {
        return [
            $t->created_at->format('d-m-Y H:i'),
            $t->invoice_number ?? $t->id,
            $t->payment_method,
            $t->status,
            $t->subtotal,
            $t->discount,
            $t->tax,
            $t->total,
        ];
    }
}