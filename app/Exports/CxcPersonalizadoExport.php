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
    public $empresaCuentaId;

    public function __construct($cotizaciones, $user = null, $empresaCuentaId = null)
    {
        $this->cotizaciones = $cotizaciones;
        $this->user = $user ?? auth()->user();
        $this->empresaCuentaId = $empresaCuentaId ?? $cotizaciones->first()?->id_empresa;
    }

    public function view(): View
    {
        $cotizaciones = $this->cotizaciones;
        $user = $this->user;
        $empresaCuentaId = $this->empresaCuentaId;
        $isExcel = true;

        return view('reporteria.cxc.excel_personalizado', compact('cotizaciones', 'user', 'empresaCuentaId', 'isExcel'));
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $detailLastRow = min($highestRow, 9 + $this->cotizaciones->count());

                // Preserve generous space for beneficiary and bank details.
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);
                for ($row = 1; $row <= $highestRow; $row++) {
                    $rowHasBankDetails = false;
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $value = $cell->getValue();
                        if (is_string($value) && str_contains($value, '##ROW_ALTO##')) {
                            $cell->setValue(str_replace('##ROW_ALTO##', '', $value));
                            $rowHasBankDetails = true;
                        }
                    }
                    if ($rowHasBankDetails) {
                        $sheet->getRowDimension($row)->setRowHeight(80);
                        $sheet->getStyle("A{$row}:{$highestColumn}{$row}")
                            ->getAlignment()
                            ->setWrapText(true)
                            ->setVertical(Alignment::VERTICAL_CENTER);
                    }
                }

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
                $sheet->getStyle('E3:H4')->getFont()->setBold(true);
                $sheet->getStyle('E3:H4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

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

                // 5. Formato de la tabla de cotizaciones, sin aplicar estilos
                // monetarios/totales al bloque bancario que se agrega debajo.
                if ($detailLastRow >= 9) {
                    $currencyFormat = '"$"#,##0.00;("$"#,##0.00);"$0"';

                    // Columnas calculadas en gris claro (#F2F4F8)
                    foreach (['M', 'Q', 'R', 'S'] as $calcCol) {
                        $sheet->getStyle("{$calcCol}9:{$calcCol}{$detailLastRow}")
                            ->getFill()
                            ->setFillType(Fill::FILL_SOLID)
                            ->getStartColor()
                            ->setRGB('F2F4F8');
                        $sheet->getStyle("{$calcCol}9:{$calcCol}{$detailLastRow}")
                            ->getFont()
                            ->setBold(true);
                    }

                    // Formato de moneda para columnas G a S
                    $sheet->getStyle("G9:S{$detailLastRow}")
                        ->getNumberFormat()
                        ->setFormatCode($currencyFormat);
                    $sheet->getStyle("G9:S{$detailLastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Formato de peso (Col F)
                    $sheet->getStyle("F9:F{$detailLastRow}")
                        ->getNumberFormat()
                        ->setFormatCode('#,##0.00');
                    $sheet->getStyle("F9:F{$detailLastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                    // Centrar Fecha y Contenedor
                    $sheet->getStyle("A9:B{$detailLastRow}")
                        ->getAlignment()
                        ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Bordes en toda la tabla
                    $sheet->getStyle("A9:T{$detailLastRow}")
                        ->getBorders()
                        ->getAllBorders()
                        ->setBorderStyle(Border::BORDER_THIN)
                        ->getColor()
                        ->setRGB('D0D0D0');

                    // Fila de totales del reporte personalizado.
                    $sheet->getStyle("A{$detailLastRow}:T{$detailLastRow}")->applyFromArray([
                        'font' => ['bold' => true, 'name' => 'Calibri'],
                        'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E9ECEF']],
                        'borders' => [
                            'top' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0F243D']],
                            'bottom' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '0F243D']],
                        ],
                    ]);
                }

                // Anchos controlados conforme a la plantilla: textos descriptivos
                // reciben más espacio y las columnas numéricas permanecen compactas.
                $columnWidths = [
                    13, 16, 21, 24, 16, 12, 16, 13, 12, 12,
                    14, 12, 16, 16, 11, 13, 16, 17, 16, 27,
                ];
                foreach ($columnWidths as $index => $width) {
                    $columnLetter = Coordinate::stringFromColumnIndex($index + 1);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(false);
                    $sheet->getColumnDimension($columnLetter)->setWidth($width);
                }

                $sheet->getStyle('A8:T8')->getAlignment()->setWrapText(true);
                $sheet->getStyle('A9:T' . $detailLastRow)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                $sheet->getStyle('C9:E' . $detailLastRow)->getAlignment()->setWrapText(true);
                $sheet->getStyle('T9:T' . $detailLastRow)->getAlignment()->setWrapText(true);
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);
                $sheet->getPageSetup()->setPrintArea('A1:' . $highestColumn . $highestRow);

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
