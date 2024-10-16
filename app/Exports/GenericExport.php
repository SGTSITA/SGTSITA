<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class GenericExport implements FromArray, WithHeadings, WithStyles
{
    public $vDataExport;
    public $vTitleColumns;
    public function __construct($titleColumns,$dataExport) {
        $this->vDataExport = $dataExport;
        $this->vTitleColumns = $titleColumns;
    }

    public function array(): array
    {
        return $this->vDataExport;
    }

    public function headings(): array
    {
        return $this->vTitleColumns;
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
