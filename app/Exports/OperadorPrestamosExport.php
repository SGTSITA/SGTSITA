<?php
namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class OperadorPrestamosExport implements FromView, ShouldAutoSize, WithStyles, WithEvents
{
    use Exportable;

    public $operador;
    public $prestamos;
    public $totalPrestamos;
    public $totalAdelantos;
    public $totalDeuda;
    public $totalAbonos;
    public $saldoFinal;

    public function __construct(
        $operador,
        $prestamos,
        $totalPrestamos,
        $totalAdelantos,
        $totalDeuda,
        $totalAbonos,
        $saldoFinal
    ) {
        $this->operador = $operador;
        $this->prestamos = $prestamos;
        $this->totalPrestamos = $totalPrestamos;
        $this->totalAdelantos = $totalAdelantos;
        $this->totalDeuda = $totalDeuda;
        $this->totalAbonos = $totalAbonos;
        $this->saldoFinal = $saldoFinal;
    }

    public function view(): View
    {
        return view('reporteria.operadores.prestamos_excel', [
            'operador' => $this->operador,
            'prestamos' => $this->prestamos,
            'totalPrestamos' => $this->totalPrestamos,
            'totalAdelantos' => $this->totalAdelantos,
            'totalDeuda' => $this->totalDeuda,
            'totalAbonos' => $this->totalAbonos,
            'saldoFinal' => $this->saldoFinal,
            'isExcel' => true,
        ]);
    }
public function styles(Worksheet $sheet)
{
    $sheet->mergeCells('A1:F1');
    $sheet->mergeCells('A2:F2');
    $sheet->mergeCells('A3:F3');

    return [
        'A1:F1' => [
            'font' => [
                'bold' => true,
                'size' => 16,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ],

        'A2:F2' => [
            'font' => [
                'bold' => true,
                'size' => 12,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ],

        'A3:F3' => [
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_RIGHT,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ],
    ];
}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $highestRow = $sheet->getHighestRow();
                $highestColumn = $sheet->getHighestColumn();
                $highestColumnIndex = Coordinate::columnIndexFromString($highestColumn);

                for ($row = 1; $row <= $highestRow; $row++) {
                    for ($col = 1; $col <= $highestColumnIndex; $col++) {
                        $sheet->getStyleByColumnAndRow($col, $row)->getAlignment()
                            ->setVertical(Alignment::VERTICAL_CENTER);

                        $sheet->getStyleByColumnAndRow($col, $row)->getBorders()->getAllBorders()
                            ->setBorderStyle(Border::BORDER_THIN);
                    }
                }

                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $sheet->getColumnDimension($columnLetter)->setAutoSize(true);
                }

                $sheet->calculateColumnWidths();

                for ($col = 1; $col <= $highestColumnIndex; $col++) {
                    $columnLetter = Coordinate::stringFromColumnIndex($col);
                    $width = $sheet->getColumnDimension($columnLetter)->getWidth();
                    $sheet->getColumnDimension($columnLetter)->setWidth($width + 2);
                }

                $sheet->getPageSetup()
                    ->setOrientation(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE);

                $sheet->getPageMargins()->setTop(0.2);
                $sheet->getPageMargins()->setRight(0.2);
                $sheet->getPageMargins()->setLeft(0.2);
                $sheet->getPageMargins()->setBottom(0.2);

                $sheet->getPageSetup()->setFitToWidth(1);
                $sheet->getPageSetup()->setFitToHeight(0);

                $sheet->getPageSetup()
                    ->setPaperSize(\PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::PAPERSIZE_LETTER);
            },
        ];
    }
}
