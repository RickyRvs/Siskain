<?php

namespace App\Exports;

use App\Models\Transaction;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class PiutangExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(protected Carbon $start, protected Carbon $end) {}

    public function collection()
    {
        return Transaction::where('status', 'piutang')
            ->whereBetween('created_at', [$this->start, $this->end])
            ->with('customer')
            ->withSum('payments', 'amount')
            ->get();
    }

    public function headings(): array
    {
        return ['Tanggal', 'Pelanggan', 'Total', 'Dibayar', 'Sisa'];
    }

    public function map($t): array
    {
        return [
            $t->created_at->format('d-m-Y'),
            $t->customer->name ?? 'Pelanggan Umum',
            $t->total,
            $t->payments_sum_amount ?? 0,
            $t->total - ($t->payments_sum_amount ?? 0),
        ];
    }
}