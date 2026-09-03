<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SociosUtilidadExport implements FromCollection, WithHeadings, WithTitle, WithStyles
{
    private array $data;
    private string $empresa;
    private string $fechaGeneracion;
    private string $tipoReporte;

    public function __construct(array $data, string $empresa, string $tipoReporte = 'completo')
    {
        $this->data = $data;
        $this->empresa = $empresa;
        $this->tipoReporte = $tipoReporte;
        $this->fechaGeneracion = now()->format('d-m-Y H:i');
    }

    public function title(): string
    {
        return 'Reporte de Utilidad Socios';
    }

    public function collection()
    {
        $rows = [];

        // Header info rows
        $rows[] = ['REPORTE DE DISTRIBUCIÓN DE UTILIDADES - SOCIOS'];
        $rows[] = ['Empresa:', $this->empresa];
        $rows[] = ['Fecha Generación:', $this->fechaGeneracion];
        $rows[] = ['Periodo:', $this->data['fecha_desde'] . ' al ' . $this->data['fecha_hasta']];
        $rows[] = [];

        // 1. Summary Cards Section
        $rows[] = ['RESUMEN DEL PERIODO'];
        if ($this->tipoReporte === 'completo') {
            $rows[] = ['Utilidad Bruta Viajes:', (float)$this->data['total_utilidad_bruta_viajes']];
            $rows[] = ['Gastos Indirectos Mes:', (float)$this->data['total_gastos_periodo']];
            $rows[] = ['Pago Total a Socios:', (float)$this->data['total_distribuido_socios']];
            $rows[] = ['Utilidad Neta Empresa:', (float)$this->data['utilidad_neta_empresa']];
        } else {
            $rows[] = ['Total Asignado a Socio:', (float)$this->data['total_distribuido_socios']];
        }
        $rows[] = [];

        // 2. Partners Split table headings
        $rows[] = ['DISTRIBUCIÓN AGRUPADA POR SOCIO'];
        $rows[] = ['Socio', 'Unidad Pactada', 'Regla de Pago', 'Viajes Realizados', 'Monto Distribuido'];
        foreach ($this->data['socios_desglose'] as $soc) {
            $rows[] = [
                $soc['socio'],
                $soc['unidad'],
                $soc['factor'],
                $soc['viajes_realizados'],
                (float)$soc['monto_distribuido']
            ];
        }
        $rows[] = [];

        if ($this->tipoReporte === 'completo') {
            // 3. Viajes Breakdown headings
            $rows[] = ['DESGLOSE INDIVIDUAL DE VIAJES'];
            $rows[] = ['Fecha Viaje', 'Contenedor', 'Cliente', 'Unidad', 'Estatus', 'Utilidad Viaje'];
            foreach ($this->data['viajes_desglose'] as $v) {
                $rows[] = [
                    $v['fecha_viaje'] ? date('d-m-Y', strtotime($v['fecha_viaje'])) : 'S/N',
                    $v['contenedor'],
                    $v['cliente'] ?? '--',
                    $v['unidad'],
                    $v['estatus_viaje'] ?? 'S/N',
                    (float)$v['utilidad_viaje']
                ];
            }
        }

        return collect($rows);
    }

    public function headings(): array
    {
        return [];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);
        $sheet->getStyle('A7')->getFont()->setBold(true)->setSize(12);
        
        // Find headings positions dynamically to style them
        $highestRow = $sheet->getHighestRow();
        for ($i = 1; $i <= $highestRow; $i++) {
            $val = $sheet->getCell('A' . $i)->getValue();
            if (in_array($val, ['RESUMEN DEL PERIODO', 'DISTRIBUCIÓN AGRUPADA POR SOCIO', 'DESGLOSE INDIVIDUAL DE VIAJES'])) {
                $sheet->getStyle('A' . $i)->getFont()->setBold(true)->setSize(12);
                $sheet->getStyle('A' . ($i + 1) . ':E' . ($i + 1))->getFont()->setBold(true);
            }
        }
        return [];
    }
}
