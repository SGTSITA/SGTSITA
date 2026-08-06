<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ConsumoUnidadesExport implements FromArray, ShouldAutoSize, WithColumnWidths, WithEvents, WithStyles, WithTitle
{
    protected array $reporte;
    protected array $filtros;

    public function __construct(array $reporte, array $filtros)
    {
        $this->reporte = $reporte;
        $this->filtros = $filtros;
    }

    public function title(): string
    {
        return 'Consumo unidad';
    }

    public function array(): array
    {
        $resumen = $this->reporte['resumen'] ?? [];
        $rows = $this->reporte['rows'] ?? [];

        $data = [];

        $data[] = ['Reporte de consumo por unidad'];
        $data[] = [
            'Unidad',
            $this->filtros['unidad'] ?? 'S/N',
            'Fecha inicio',
            $this->filtros['fecha_inicio'] ?? '',
            'Fecha fin',
            $this->filtros['fecha_fin'] ?? '',
        ];

        $data[] = [];

        $data[] = [
            'Viajes',
            'Con datos',
            'Sin datos',
            'Total KM',
            'Litros cálculo',
            'Litros capturados',
            'KM / Litro',
        ];

        $data[] = [
            $resumen['total_viajes'] ?? 0,
            $resumen['viajes_con_datos'] ?? 0,
            $resumen['viajes_sin_datos'] ?? 0,
            $resumen['total_km'] ?? 0,
            $resumen['total_litros_calculo'] ?? 0,
            $resumen['total_litros_capturados'] ?? 0,
            $resumen['rendimiento_promedio'] ?? 'S/N',
        ];

        $data[] = [];

        $data[] = [
            'Fecha inicio',
            'Fecha fin',
            'Contenedor',
            'Peso',
            'Operador',
            'Origen',
            'Destino',
            'KM',
            'Litros capturados',
            'Litros cálculo',
            'Rendimiento KM/L',
            'Tomado de contenedor',
            'Observación',
        ];

        foreach ($rows as $row) {
            $data[] = [
                $row['fecha_inicio'] ?? 'S/N',
                $row['fecha_fin'] ?? 'S/N',
                $row['contenedor'] ?? 'S/N',
                $row['peso_contenedor'] ?? 0,
                $row['operador'] ?? 'S/N',
                $row['origen'] ?? 'S/N',
                $row['destino'] ?? 'S/N',
                $row['km_recorridos'] ?? 0,
                $row['litros_capturados_viaje'] ?? 0,
                $row['litros_calculo_consumo'] ?? 0,
                $row['rendimiento_km_litro'] ?? 'S/N',
                $row['litros_tomados_de_contenedor'] ?? 'S/N',
                $row['observacion'] ?? 'Completo',
            ];
        }

        return $data;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                    'size' => 16,
                ],
            ],
            4 => [
                'font' => [
                    'bold' => true,
                ],
            ],
            5 => [
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'fill' => [
                    'fillType' => 'solid',
                    'startColor' => ['rgb' => '344767'],
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 14,
            'B' => 14,
            'C' => 24,
            'D' => 12,
            'E' => 26,
            'F' => 32,
            'G' => 32,
            'H' => 12,
            'I' => 18,
            'J' => 16,
            'K' => 18,
            'L' => 26,
            'M' => 38,
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $ultimaFila = $sheet->getHighestRow();

                $sheet->mergeCells('A1:M1');
                $sheet->getStyle('A1:M1')->getAlignment()->setHorizontal('center');

                $sheet->getStyle('A4:G5')->getAlignment()->setHorizontal('center');
                $sheet->getStyle('A5:M5')->getAlignment()->setHorizontal('center');

                $sheet->getStyle("A6:M{$ultimaFila}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle('thin');

                $sheet->getStyle("A6:M{$ultimaFila}")
                    ->getAlignment()
                    ->setVertical('top')
                    ->setWrapText(true);

                $sheet->getStyle("H8:K{$ultimaFila}")
                    ->getNumberFormat()
                    ->setFormatCode('#,##0.000');

                $sheet->freezePane('A6');
                $sheet->setAutoFilter("A5:M{$ultimaFila}");
            },
        ];
    }
}
