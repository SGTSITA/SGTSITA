<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class CotizacionesCXC implements FromArray, WithHeadings, WithStyles
{
    public $vDataExport;
    public function __construct($dataExport) {
        $this->vDataExport = $dataExport;
    }

    public function array(): array
    {
        return $this->vDataExport;
    }

    public function headings(): array
    {
        return [
            'Número',
            'Fecha Inicio',
            'Cliente',
            'SubCliente',
            'Origen',
            'Destino',
            'Contenedor',
            'Estatus'
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true,'size' => 14]], // Negrita para la primera fila

            // Aplicar ajuste de texto (wrap text) a todas las celdas
            'A' => ['alignment' => ['wrapText' => true]],
            'B' => ['alignment' => ['wrapText' => true]],
            'C' => ['alignment' => ['wrapText' => true]],
            'D' => ['alignment' => ['wrapText' => true]],
        ];
    }
}
