<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class CostosProveedorSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(protected array $rows) {}

    public function title(): string
    {
        return 'Costos Proveedor';
    }

    public function headings(): array
    {
        return [
            'ID Cotización',
            'ID Asignación',
            'Estatus',
            '# Contenedor',
            'Cliente',
            'Fecha Entrega',
            'Fecha Registro',
            'Destino',
            'Proveedor',
            'Tipo Proveedor',
            'Transportista',
            'Operador',
            'Unidad (Tracto)',
            'Peso Contenedor (Ton)',
            'Sobrepeso (Ton)',
            'Costo Viaje Proveedor',
            'Burreo',
            'Maniobra',
            'Estadía',
            'Precio Sobrepeso',
            'Total Tonelada (Sobrepeso)',
            'Base 1',
            'Base 2',
            'Otros',
            'IVA',
            'Retención',
            'Total Proveedor',
            'Total Pagado (CXP)',
            'Saldo Pendiente (CXP)',
            'Estado Costo',
        ];
    }

    public function array(): array
    {
        $export = [];
        foreach ($this->rows as $r) {
            $export[] = [
                $r['id'] ?? '',
                $r['id_asignacion'] ?? '',
                $r['estatus'] ?? '',
                $r['contenedor'] ?? '',
                $r['cliente'] ?? '',
                $r['fecha_entrega'] ?? '',
                $r['fecha_creacion'] ?? '',
                $r['destino'] ?? '',
                $r['proveedor'] ?? '',
                $r['tipo_proveedor'] ?? '',
                $r['transportista'] ?? '',
                $r['operador'] ?? '',
                $r['unidad'] ?? '',
                floatval($r['peso_contenedor'] ?? 0),
                floatval($r['sobrepeso'] ?? 0),
                floatval($r['precio_proveedor'] ?? 0),
                floatval($r['burreo_proveedor'] ?? 0),
                floatval($r['maniobra_proveedor'] ?? 0),
                floatval($r['estadia_proveedor'] ?? 0),
                floatval($r['sobrepeso_proveedor'] ?? 0),
                floatval($r['total_tonelada'] ?? 0),
                floatval($r['base1_proveedor'] ?? 0),
                floatval($r['base2_proveedor'] ?? 0),
                floatval($r['otro_proveedor'] ?? 0),
                floatval($r['iva_proveedor'] ?? 0),
                floatval($r['retencion_proveedor'] ?? 0),
                floatval($r['total_proveedor'] ?? 0),
                floatval($r['total_pagado'] ?? 0),
                floatval($r['saldo_pendiente'] ?? 0),
                $r['estado_costo_label'] ?? '',
            ];
        }
        return $export;
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF'], 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['argb' => 'FF15803D'] // Emerald green
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                    'wrapText' => true
                ],
            ],
        ];
    }
}
