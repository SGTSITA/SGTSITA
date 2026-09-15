<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;

class BalanceGeneralExport implements FromView, ShouldAutoSize, WithStyles
{
    protected array $balance;
    protected string $fechaCorte;

    public function __construct(array $balance, string $fechaCorte)
    {
        $this->balance = $balance;
        $this->fechaCorte = $fechaCorte;
    }

    public function view(): View
    {
        return view('reporteria.balance_general.excel', [
            'balance' => $this->balance,
            'fechaCorte' => $this->fechaCorte,
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14]],
            2 => ['font' => ['italic' => true, 'size' => 10]],
            4 => ['font' => ['bold' => true]],
        ];
    }
}
