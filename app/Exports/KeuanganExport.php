<?php

namespace App\Exports;

use App\Exports\Sheets\KeuanganHarianSheet;
use App\Exports\Sheets\KeuanganPengeluaranSheet;
use App\Exports\Sheets\KeuanganProdukSheet;
use App\Exports\Sheets\KeuanganRingkasanSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class KeuanganExport implements WithMultipleSheets
{
    /**
     * @param array $data hasil dari ReportController::dataFor('keuangan', ...)
     *                     (keys: start, end, summary, dailyRecap, productRecap, piutangRecap, expenseRecap)
     */
    public function __construct(protected array $data) {}

    public function sheets(): array
    {
        return [
            new KeuanganRingkasanSheet($this->data['summary'], $this->data['start'], $this->data['end']),
            new KeuanganHarianSheet($this->data['dailyRecap']),
            new KeuanganProdukSheet($this->data['productRecap']),
            new PiutangExport($this->data['piutangRecap']),
            new KeuanganPengeluaranSheet($this->data['expenseRecap']),
        ];
    }
}