<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class PiutangExport implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    /**
     * @param Collection $piutangRecap hasil dari ReportController::buildPiutangRecap()
     */
    public function __construct(protected Collection $piutangRecap) {}

    public function title(): string
    {
        return 'Piutang';
    }

    public function collection(): Collection
    {
        return $this->piutangRecap;
    }

    public function headings(): array
    {
        return ['Tanggal', 'No Invoice', 'Pelanggan', 'Total', 'Dibayar', 'Sisa'];
    }

    public function map($row): array
    {
        return [
            $row['tanggal']->format('d-m-Y'),
            $row['invoice'],
            $row['customer'],
            $row['total'],
            $row['dibayar'],
            $row['sisa'],
        ];
    }
}