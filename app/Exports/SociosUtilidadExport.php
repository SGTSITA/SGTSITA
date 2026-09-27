<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class SociosUtilidadExport implements FromCollection, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    private array $data;
    private string $empresa;
    private string $fechaGeneracion;
    private string $tipoReporte;
    private ?int $socioId;

    public function __construct(array $data, string $empresa, string $tipoReporte = 'completo', ?int $socioId = null)
    {
        $this->data = $data;
        $this->empresa = $empresa;
        $this->tipoReporte = $tipoReporte;
        $this->socioId = $socioId ?? $data['filtro_socio_id'] ?? null;
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
        $rows[] = ['REPORTE DE DISTRIBUCIÓN DE UTILIDADES (SOCIOS)'];
        $rows[] = ['Empresa:', $this->empresa];
        $rows[] = ['Fecha Generación:', $this->fechaGeneracion];
        $rows[] = ['Periodo:', ($this->data['fecha_desde'] ?? '') . ' al ' . ($this->data['fecha_hasta'] ?? '')];
        $rows[] = ['Socios en Reporte:', ($this->data['total_socios_configurados'] ?? count($this->data['socios_desglose'] ?? [])) . ' socio(s)'];
        $rows[] = [];

        // 1. Summary Section
        if ($this->tipoReporte === 'completo') {
            $rows[] = ['RESUMEN DEL PERIODO'];
            $rows[] = ['Utilidad Bruta Viajes:', (float)($this->data['total_utilidad_bruta_viajes'] ?? 0)];
            $rows[] = ['Gastos Indirectos Mes:', (float)($this->data['total_gastos_periodo'] ?? 0)];
            $rows[] = ['Utilidad a repartir:', (float)($this->data['utilidad_neta_distribuible'] ?? 0)];
            $rows[] = ['Total Pagado / Adelantado:', (float)($this->data['total_pagado_socios'] ?? 0)];
            $rows[] = [];

            // 2. Partners Split table headings
            $rows[] = ['DISTRIBUCIÓN AGRUPADA POR SOCIO'];
            if (!empty($this->socioId)) {
                $rows[] = ['Socio', 'Unidad Pactada', 'Regla de Pago', 'Viajes Realizados', 'Utilidad Socio', 'Total Pagado', 'Saldo Pendiente'];
                foreach ($this->data['socios_desglose'] ?? [] as $soc) {
                    $rows[] = [
                        $soc['socio'],
                        $soc['unidad'],
                        $soc['factor'],
                        $soc['viajes_realizados'],
                        (float)$soc['monto_distribuido'],
                        (float)$soc['total_pagado'],
                        (float)$soc['saldo_pendiente']
                    ];
                }
            } else {
                $rows[] = ['Socio', 'Unidad Pactada', 'Regla de Pago', 'Viajes Realizados', 'Utilidad a Repartir', 'Utilidad Socio', 'Total Pagado', 'Saldo Pendiente'];
                foreach ($this->data['socios_desglose'] ?? [] as $soc) {
                    $rows[] = [
                        $soc['socio'],
                        $soc['unidad'],
                        $soc['factor'],
                        $soc['viajes_realizados'],
                        (float)$soc['utilidad_a_repartir'],
                        (float)$soc['monto_distribuido'],
                        (float)$soc['total_pagado'],
                        (float)$soc['saldo_pendiente']
                    ];
                }
            }
            $rows[] = [];

            // 3. Viajes Breakdown headings
            $rows[] = ['DESGLOSE INDIVIDUAL DE VIAJES'];
            $rows[] = ['Fecha Viaje', 'Contenedor', 'Cliente', 'Unidad', 'Estatus', 'Utilidad Viaje'];
            foreach ($this->data['viajes_desglose'] ?? [] as $v) {
                $rows[] = [
                    $v['fecha_viaje'] ? date('d-m-Y', strtotime($v['fecha_viaje'])) : 'S/N',
                    $v['contenedor'],
                    $v['cliente'] ?? '--',
                    $v['unidad'],
                    $v['estatus_viaje'] ?? 'S/N',
                    (float)$v['utilidad_viaje']
                ];
            }
        } else {
            // TIPO REPORTE === 'socio' (Por Socio sin viajes - Agrupado por Unidad con Desglose de Pagos por Socio)
            $rows[] = ['RESUMEN DE UTILIDAD EN ESTE PERIODO PARA SOCIOS'];
            $rows[] = ['Utilidad Total Correspondiente a Socios:', (float)($this->data['total_distribuido_socios'] ?? 0)];
            $rows[] = [];

            if (!empty($this->data['unidades_desglose'])) {
                foreach ($this->data['unidades_desglose'] as $unidad) {
                    $rows[] = ['UNIDAD / VEHÍCULO: ' . $unidad['unidad']];
                    if (!empty($this->socioId)) {
                        $rows[] = [
                            'Viajes Realizados:',
                            $unidad['viajes_realizados']
                        ];
                    } else {
                        $rows[] = [
                            'Viajes Realizados:',
                            $unidad['viajes_realizados'],
                            'Utilidad a Repartir Unidad:',
                            (float)$unidad['utilidad_a_repartir']
                        ];
                    }
                    $rows[] = [];

                    foreach ($unidad['socios'] as $soc) {
                        $rows[] = ['SOCIO: ' . $soc['socio']];
                        $rows[] = ['Regla de Pago', 'Utilidad Socio', 'Total Pagado Histórico', 'Saldo Pendiente'];
                        $rows[] = [
                            $soc['factor'],
                            (float)$soc['monto_distribuido'],
                            (float)$soc['total_pagado'],
                            (float)$soc['saldo_pendiente']
                        ];
                        $rows[] = [];

                        $rows[] = ['PAGOS Y ABONOS REGISTRADOS EN EL PERIODO'];
                        if (!empty($soc['pagos'])) {
                            $rows[] = ['Fecha Pago', 'Concepto / Referencia', 'Cuenta / Banco Origen', 'Monto Pagado'];
                            foreach ($soc['pagos'] as $pago) {
                                $rows[] = [
                                    $pago['fecha_formateada'],
                                    $pago['concepto'],
                                    $pago['banco'],
                                    (float)$pago['monto']
                                ];
                            }
                            $rows[] = [
                                'TOTAL PAGADO EN EL PERIODO:',
                                '',
                                '',
                                (float)$soc['total_pagos_periodo']
                            ];
                        } else {
                            $rows[] = ['Aún no se han realizado pagos / abonos a este socio en este periodo.'];
                        }
                        $rows[] = ['Firma de Conformidad:', $soc['socio']];
                        $rows[] = [];
                    }
                    $rows[] = [];
                }
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

        // Find headings positions dynamically to style them
        $highestRow = $sheet->getHighestRow();
        for ($i = 1; $i <= $highestRow; $i++) {
            $val = (string) $sheet->getCell('A' . $i)->getValue();

            if (
                str_starts_with($val, 'RESUMEN') ||
                str_starts_with($val, 'DISTRIBUCIÓN') ||
                str_starts_with($val, 'DESGLOSE') ||
                str_starts_with($val, 'UNIDAD / VEHÍCULO')
            ) {
                $sheet->getStyle('A' . $i)->getFont()->setBold(true)->setSize(12);
                if (str_starts_with($val, 'UNIDAD / VEHÍCULO')) {
                    $sheet->getStyle('A' . $i . ':D' . $i)->getFont()->setBold(true);
                }
            }

            if (str_starts_with($val, 'SOCIO:') || str_starts_with($val, 'PAGOS Y ABONOS')) {
                $sheet->getStyle('A' . $i)->getFont()->setBold(true);
            }

            if (in_array($val, [
                'Socio',
                'Fecha Viaje',
                'Regla de Pago',
                'Fecha Pago'
            ])) {
                $sheet->getStyle('A' . $i . ':H' . $i)->getFont()->setBold(true);
            }

            if (str_starts_with($val, 'TOTAL PAGADO EN EL PERIODO:') || str_starts_with($val, 'Firma de Conformidad:')) {
                $sheet->getStyle('A' . $i . ':E' . $i)->getFont()->setBold(true);
            }
        }
        return [];
    }
}
