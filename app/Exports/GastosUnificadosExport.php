<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GastosUnificadosExport implements FromView, ShouldAutoSize, WithStyles
{
    public function __construct(protected array $data)
    {
    }

    public function view(): View
    {
        return view('gastos.exports.reporte_unificado_excel', [
            'categorias' => $this->data['categorias'],
            'filas' => $this->data['filas'],
            'totales_columnas' => $this->data['totales_columnas'],
            'gran_total' => $this->data['gran_total'],
            'from' => $this->data['from'],
            'to' => $this->data['to'],
            'agrupacion' => $this->data['agrupacion'] ?? 'subcategoria',
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 13]],
            4 => [
                'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '1B365D'],
                ],
            ],
        ];
    }
}
