<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithEvents;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Events\AfterSheet;

class GastosDetalleExport implements FromView, WithStyles, WithEvents
{
    protected $datos;

    public function __construct($datos)
    {
        $this->datos = collect($datos);
    }

    public function view(): View
    {
        return view('reporteria.utilidad.gastos_excel', [
            'datos' => $this->datos
        ]);
    }

    public function styles(Worksheet $sheet)
    {
        // Fuente general más grande
        $sheet->getDefaultRowDimension()->setRowHeight(22);
        $sheet->getStyle('A1:Z1000')->getFont()->setSize(12);

        return [];
    }

    public function registerEvents(): array
{
    return [
        AfterSheet::class => function (AfterSheet $event) {
            $sheet = $event->sheet;
            $delegate = $sheet->getDelegate();

            // Título general
            $sheet->mergeCells('A1:E1');
            $sheet->setCellValue('A1', 'Detalle de Gastos por Contenedor');
            $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
            $sheet->getStyle('A1')->getAlignment()->setHorizontal('center');

            // Total general
            $total = $this->datos->sum('Monto');
            $sheet->mergeCells('A2:E2');
            $sheet->setCellValue('A2', 'Total general: $' . number_format($total, 2, '.', ','));
            $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(13);
            $sheet->getStyle('A2')->getAlignment()->setHorizontal('center');

            $highestRow = $sheet->getHighestRow();

            for ($row = 3; $row <= $highestRow; $row++) {
                $valor = $sheet->getCell("A{$row}")->getValue();

                // Si es fila tipo "Contenedor: XYZ"
                if (str_contains($valor, 'Contenedor:')) {
                    $delegate->getStyle("A{$row}:E{$row}")
                        ->getFont()->setBold(true)->setSize(12);
                    continue;
                }

                // Si es fila de encabezado de tabla ("Motivo")
                if ($valor === 'Motivo') {
                    $delegate->getStyle("A{$row}:E{$row}")
                        ->getFont()->setBold(true)->setSize(12);

                    $delegate->getStyle("A{$row}:E{$row}")
                        ->getFill()->setFillType('solid')->getStartColor()->setRGB('D9D9D9');

                    $delegate->getStyle("A{$row}:E{$row}")
                        ->getBorders()->getAllBorders()->setBorderStyle('thin'); // Solo aquí hay bordes
                }
            }

            // Autosize columnas
            foreach (range('A', 'E') as $col) {
                $delegate->getColumnDimension($col)->setAutoSize(true);
            }
        }
    ];
}

}
