<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use Illuminate\Contracts\View\View;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Border;

class CxcExport implements FromView, ShouldAutoSize, WithStyles, WithEvents
{
    use Exportable;

    public $vCotizaciones;
    public $vFechaCarbon;
    public $vBancosOficiales;
    public $vBancosNoOficiales;
    public $vCotizacion;
    public $vUser;

    public function __construct($cotizaciones, $fechaCarbon, $bancosOficiales, $bancosNoOficiales, $cotizacion, $user)
    {
        $this->vCotizaciones = $cotizaciones;
        $this->vFechaCarbon = $fechaCarbon;
        $this->vBancosOficiales = $bancosOficiales;
        $this->vBancosNoOficiales = $bancosNoOficiales;
        $this->vCotizacion = $cotizacion;
        $this->vUser = $user;
    }

    public function view(): View
    {
        $cotizaciones = $this->vCotizaciones;
        $fechaCarbon = $this->vFechaCarbon;
        $bancos_oficiales = $this->vBancosOficiales;
        $bancos_no_oficiales = $this->vBancosNoOficiales;
        $cotizacion = $this->vCotizacion;
        $user = $this->vUser;
        $isExcel = true;

        return view('reporteria.cxc.pdf', compact('cotizaciones', 'fechaCarbon', 'bancos_oficiales', 'bancos_no_oficiales', 'cotizacion', 'user', 'isExcel'));
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('A1:D1');
        $sheet->mergeCells('A5:D5');
        $sheet->mergeCells('H2:M2');

        return [


            'A1' => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                    'color' => ['rgb' => '000000'],
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'FFFFFF'],
                ],
            ],


            'H2:M2' => [
                'font' => [
                    'bold' => true,
                    'size' => 14,
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'outline' => [
                        'borderStyle' => Border::BORDER_MEDIUM,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ],

        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet;

                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

                for ($row = 1; $row <= $highestRow; $row++) {

                    $esFilaAlta = false;

                    for ($col = 1; $col <= $highestColumnIndex; $col++) {

                        $cell = $sheet->getCellByColumnAndRow($col, $row);
                        $value = $cell->getValue();

                        if ($value && str_contains($value, '##ROW_ALTO##')) {

                            $esFilaAlta = true;

                            // limpiar texto
                            $cell->setValue(str_replace('##ROW_ALTO##', '', $value));
                        }
                    }

                    if ($esFilaAlta) {
                        $sheet->getRowDimension($row)->setRowHeight(80);
                    }
                }

                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);

                    $dimension = $sheet->getColumnDimension($columnLetter);

                    $dimension->setAutoSize(true);
                }


                $sheet->calculateColumnWidths();


                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);

                    $width = $sheet->getColumnDimension($columnLetter)->getWidth();


                    $sheet->getColumnDimension($columnLetter)->setWidth($width + 2);
                }


                // foreach (range('A', 'Z') as $column) {
                //     $sheet->getColumnDimension($column)->setWidth(20); // Establecer el ancho de la columna a 20
                // }

                // Configuraciones de impresión
                $sheet->getPageSetup()->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                // Establecer márgenes de la página
                $sheet->getPageMargins()->setTop(0.2);  // Aumento del margen superior
                $sheet->getPageMargins()->setRight(0.2); // Aumento del margen derecho
                $sheet->getPageMargins()->setLeft(0.2);  // Aumento del margen izquierdo
                $sheet->getPageMargins()->setBottom(0.2); // Aumento del margen inferior

                // Ajustar el contenido a una sola página de ancho
                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);




                // Cambiar el tamaño del papel a tamaño carta (8.5 x 11 pulgadas)
                $sheet->getPageSetup()->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
            },
        ];
    }

}
