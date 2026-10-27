<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class CxcPersonalizadoExport implements FromView, ShouldAutoSize, WithEvents
{
    use Exportable;

    public $cotizaciones;
    public $user;

    public function __construct($cotizaciones, $user = null)
    {
        $this->cotizaciones = $cotizaciones;
        $this->user = $user ?? auth()->user();
    }

    public function view(): View
    {
        $cotizaciones = $this->cotizaciones;
        $user = $this->user;
        $isExcel = true;

        return view('reporteria.cxc.excel_personalizado', compact('cotizaciones', 'user', 'isExcel'));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();

                // 1. Unir celdas de la tabla de metadatos (Filas 3 a 6)
                $sheet->mergeCells('B3:C3');
                $sheet->mergeCells('E3:F3');
                $sheet->mergeCells('G3:H3');

                $sheet->mergeCells('B4:C4');
                $sheet->mergeCells('E4:F4');
                $sheet->mergeCells('G4:H4');

                $sheet->mergeCells('B5:C5');
                $sheet->mergeCells('E5:F5');
                $sheet->mergeCells('G5:H5');

                $sheet->mergeCells('B6:C6');

                // 2. Título principal en A1
                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16)->getColor()->setRGB('1F3864');
                $sheet->getRowDimension(1)->setRowHeight(26);

                // 3. Estilos de la tabla de metadatos (A3:H6)
                $styleLabel = [
                    'font' => ['bold' => true, 'size' => 10, 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DDEBF7']],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0C4DE']]],
                ];
                $sheet->getStyle('A3:A6')->applyFromArray($styleLabel);
                $sheet->getStyle('E3:F5')->applyFromArray($styleLabel);

                $styleValue = [
                    'font' => ['size' => 10, 'name' => 'Calibri'],
                    'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'D9D9D9']]],
                ];
                $sheet->getStyle('B3:C6')->applyFromArray($styleValue);
                $sheet->getStyle('G3:H4')->applyFromArray($styleValue);
                $sheet->getStyle('G3:H4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Total del periodo (G5:H5)
                $sheet->getStyle('G5:H5')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 13, 'name' => 'Calibri', 'color' => ['rgb' => '0F5132']],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'C6E0B4']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'A2C88E']]],
                ]);
                $sheet->getStyle('G5')->getNumberFormat()->setFormatCode('"$"#,##0.00;("$"#,##0.00);"-"');

                // 4. Encabezados de la tabla (Fila 8, A8:T8)
                $sheet->getRowDimension(8)->setRowHeight(28);
                $sheet->getStyle('A8:T8')->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['rgb' => 'FFFFFF'], 'name' => 'Calibri'],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F3864']],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '0F243D']]],
                ]);

                // 5. Formato de datos (Filas 9 a $highestRow)
                if ($highestRow >= 9) {
                    $currencyFormat = '"$"#,##0.00;("$"#,##0.00);"$0"';

                    // Columnas calculadas en gris claro (#F2F4F8)
                    foreach (['M', 'Q', 'R', 'S'] as $calcCol) {
                        $sheet->getStyle("{$calcCol}9:{$calcCol}{$highestRow}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('F2F4F8');
                        $sheet->getStyle("{$calcCol}9:{$calcCol}{$highestRow}")
                            ->getFont()
                            ->setBold(true);
                    }

                    // Formato de moneda para columnas G a S
                    $sheet->getStyle("G9:S{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode($currencyFormat);
                    $sheet->getStyle("G9:S{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Formato de peso (Col F)
                    $sheet->getStyle("F9:F{$highestRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                    $sheet->getStyle("F9:F{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Centrar Fecha y Contenedor
                    $sheet->getStyle("A9:B{$highestRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Bordes en toda la tabla
                    $sheet->getStyle("A9:T{$highestRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()
                        ->setRGB('D0D0D0');

                    // Fila de totales (última fila)
                    $sheet->getStyle("A{$highestRow}:T{$highestRow}")->applyFromArray([
                        'font' => ['bold' => true, 'name' => 'Calibri'],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']],
                        'borders' => [
                            'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0F243D']],
                            'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0F243D']],
                        ],
                    ]);
                }

                // 6. Auto-ajuste de columnas
                for ($col = 1; $col <= 20; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }
                $sheet->calculateColumnWidths();
                for ($col = 1; $col <= 20; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $w = $sheet->getColumnDimension($columnLetter)->getWidth();
                    $sheet->getColumnDimension($columnLetter)->setWidth(max($w + 3, 11));
                }

                // Configuración de impresión
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
                $sheet->getPageMargins()->setTop(0.3);
                $sheet->getPageMargins()->setRight(0.3);
                $sheet->getPageMargins()->setLeft(0.3);
                $sheet->getPageMargins()->setBottom(0.3);
            },
        ];
    }
}
