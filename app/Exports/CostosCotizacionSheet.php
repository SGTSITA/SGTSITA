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

class CostosCotizacionSheet implements FromArray, WithHeadings, WithTitle, WithStyles, ShouldAutoSize
{
    public function __construct(protected array $rows) {}

    public function title(): string
    {
        return 'Costos Cotización';
    }

    public function headings(): array
    {
        return [
            'ID Cotización',
            'Estatus',
            '# Contenedor',
            'Cliente',
            'Fecha Entrega',
            'Fecha Cita / Modulación',
            'Fecha Registro',
            'Destino',
            'Proveedor / Empresa',
            'Transportista',
            'Operador',
            'Unidad (Tracto)',
            'Peso Contenedor (Ton)',
            'Peso Reglamentario (Ton)',
            'Sobrepeso (Ton)',
            'Precio Viaje (Flete)',
            'Burreo',
            'Maniobra',
            'Estadía',
            'Otros',
            'Precio Sobrepeso',
            'Total Sobrepeso',
            'Base 1 (Factura)',
            'Base 2 (Taref)',
            'IVA (16%)',
            'Retención (4%)',
            'Total Cotización',
            'Total Cobrado (CXC)',
            'Saldo Pendiente (CXC)',
            'Estado Costo',
        ];
    }

    public function array(): array
    {
        $export = [];
        foreach ($this->rows as $r) {
            $export[] = [
                $r['id'] ?? '',
                $r['estatus'] ?? '',
                $r['contenedor'] ?? '',
                $r['cliente'] ?? '',
                $r['fecha_entrega'] ?? '',
                $r['fecha_modulacion'] ?? '',
                $r['fecha_creacion'] ?? '',
                $r['destino'] ?? '',
                $r['proveedor_empresa'] ?? '',
                $r['transportista'] ?? '',
                $r['operador'] ?? '',
                $r['unidad'] ?? '',
                floatval($r['peso_contenedor'] ?? 0),
                floatval($r['peso_reglamentario'] ?? 0),
                floatval($r['sobrepeso'] ?? 0),
                floatval($r['precio_viaje'] ?? 0),
                floatval($r['burreo'] ?? 0),
                floatval($r['maniobra'] ?? 0),
                floatval($r['estadia'] ?? 0),
                floatval($r['otro'] ?? 0),
                floatval($r['precio_sobre_peso'] ?? 0),
                floatval($r['total_sobrepeso'] ?? 0),
                floatval($r['base_factura'] ?? 0),
                floatval($r['base_taref'] ?? 0),
                floatval($r['iva'] ?? 0),
                floatval($r['retencion'] ?? 0),
                floatval($r['total'] ?? 0),
                floatval($r['total_cobrado'] ?? 0),
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
                    'startColor' => ['argb' => 'FF1E293B'] // Slate navy
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
