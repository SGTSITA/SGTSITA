<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ScbReporteExport implements
    FromCollection,
    WithHeadings,
    WithMapping,
    ShouldAutoSize,
    WithEvents,
    WithColumnFormatting,
    WithStyles
{
    protected array $reporte;

    public function __construct(array $reporte)
    {
        $this->reporte = $reporte;
    }

  public function collection(): Collection
{
    if (!$this->esDetallado()) {
        return collect($this->reporte['rows'] ?? []);
    }

    $rows = collect();

    foreach (($this->reporte['rows'] ?? []) as $mov) {
        $rows->push([
            'row_type' => 'movimiento',
            'fecha' => $mov['fecha'] ?? '',
            'concepto' => $mov['concepto'] ?? '',
            'referencia' => $mov['referencia_bancaria'] ?? 'S/N',
            'cargo' => (float) ($mov['cargo'] ?? 0),
            'abono' => (float) ($mov['abono'] ?? 0),
            'saldo' => (float) ($mov['saldo'] ?? 0),
        ]);

        foreach (($mov['detalles'] ?? []) as $detalle) {
            $rows->push([
                'row_type' => 'detalle',
                'fecha' => '',
                'concepto' => '   Unidad: ' . ($detalle['unidad'] ?? 'S/N') . ' | ' . ($detalle['descripcion'] ?? ''),
                'referencia' => $detalle['referencia'] ?? 'S/N',
                'cargo' => ((float) ($mov['cargo'] ?? 0)) > 0 ? (float) ($detalle['monto'] ?? 0) : 0,
                'abono' => ((float) ($mov['abono'] ?? 0)) > 0 ? (float) ($detalle['monto'] ?? 0) : 0,
                'saldo' => null,
            ]);
        }

        $rows->push([
            'row_type' => 'total_detalles',
            'fecha' => '',
            'concepto' => 'TOTAL DETALLES',
            'referencia' => '',
            'cargo' => ((float) ($mov['cargo'] ?? 0)) > 0 ? (float) ($mov['total_detalles'] ?? 0) : 0,
            'abono' => ((float) ($mov['abono'] ?? 0)) > 0 ? (float) ($mov['total_detalles'] ?? 0) : 0,
            'saldo' => null,
        ]);
    }

    return $rows;
}

   public function headings(): array
{
    if ($this->esDetallado()) {
        return [
            'Fecha',
            'Movimiento / Detalle',
            'Referencia',
            'Cargo',
            'Abono',
            'Saldo',
        ];
    }

    return [
        'Fecha',
        'Concepto',
        'Referencia',
        'Cargo',
        'Abono',
        'Saldo',
        'Detalles',
    ];
}

   public function map($row): array
{
    $row = is_array($row) ? $row : $row->toArray();

    if ($this->esDetallado()) {
        return [
            $row['fecha'] ?? '',
            $row['concepto'] ?? '',
            $row['referencia'] ?? '',
            $row['cargo'] ?: null,
            $row['abono'] ?: null,
            $row['saldo'],
        ];
    }

    return [
        $row['fecha'] ?? '',
        $row['concepto'] ?? '',
        $row['referencia'] ?? 'S/N',
        (float) ($row['cargo'] ?? 0),
        (float) ($row['abono'] ?? 0),
        (float) ($row['saldo'] ?? 0),
        (int) ($row['detalles_count'] ?? 0),
    ];
}

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

              $ultimaColumna = $this->esDetallado() ? 'F' : 'G';


                $sheet->insertNewRowBefore(1, 9);

                for ($i = 1; $i <= 8; $i++) {
                    $sheet->mergeCells("A{$i}:{$ultimaColumna}{$i}");
                }

                $titulo = $this->reporte['titulo'] ?? 'Reporte SCB';

                $cuenta = $this->reporte['cuenta'] ?? [];

                $banco = $cuenta['banco'] ?? 'S/N';
                $beneficiario = $cuenta['beneficiario'] ?? 'S/N';
                $numeroCuenta = $cuenta['numero_cuenta'] ?? 'Sin cuenta';

                $fechaInicio = $this->reporte['fecha_inicio'] ?? '';
                $fechaFin = $this->reporte['fecha_fin'] ?? '';

                $saldoInicial = (float) ($this->reporte['saldo_inicial'] ?? 0);
                $totalCargos = (float) ($this->reporte['total_cargos'] ?? 0);
                $totalAbonos = (float) ($this->reporte['total_abonos'] ?? 0);
                $saldoFinal = (float) ($this->reporte['saldo_final'] ?? 0);

                $sheet->setCellValue('A1', strtoupper($titulo));
                $sheet->setCellValue('A2', 'Beneficiario: ' . $beneficiario);
                $sheet->setCellValue('A3', 'Banco: ' . $banco);
                $sheet->setCellValue('A4', 'Cuenta: ' . $numeroCuenta);
                $sheet->setCellValue('A5', 'Periodo: ' . $fechaInicio . ' al ' . $fechaFin);
                $sheet->setCellValue('A6', 'Saldo inicial: $' . number_format($saldoInicial, 2));
                $sheet->setCellValue('A7', 'Cargos: $' . number_format($totalCargos, 2) . ' | Abonos: $' . number_format($totalAbonos, 2));
                $sheet->setCellValue('A8', 'Saldo final: $' . number_format($saldoFinal, 2));

                $sheet->getStyle("A1:{$ultimaColumna}1")->getFont()
                    ->setBold(true)
                    ->setSize(16)
                    ->getColor()
                    ->setRGB('1D4ED8');

                $sheet->getStyle("A2:A8")->getFont()->setSize(11);

                $sheet->getStyle("A1:A8")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $sheet->getStyle("A10:{$ultimaColumna}10")->getFont()
                    ->setBold(true)
                    ->getColor()
                    ->setRGB('FFFFFF');

                $sheet->getStyle("A10:{$ultimaColumna}10")->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setRGB('1D4ED8');

                $sheet->getStyle("A10:{$ultimaColumna}10")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                $ultimaFila = $sheet->getHighestRow();

                $sheet->getStyle("A10:{$ultimaColumna}{$ultimaFila}")
                    ->getBorders()
                    ->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN)
                    ->getColor()
                    ->setRGB('D1D5DB');

                $sheet->getStyle("A11:{$ultimaColumna}{$ultimaFila}")
                    ->getAlignment()
                    ->setVertical(Alignment::VERTICAL_CENTER);

$sheet->getStyle("D11:F{$ultimaFila}")
    ->getAlignment()
    ->setHorizontal(Alignment::HORIZONTAL_RIGHT);

                $sheet->freezePane('A11');
            },
        ];
    }

   public function columnFormats(): array
{
    $moneyFormat = '"$"#,##0.00;[Red]-"$"#,##0.00';

    if ($this->esDetallado()) {
        return [
            'D' => $moneyFormat,
            'E' => $moneyFormat,
            'F' => $moneyFormat,
        ];
    }

    return [
        'D' => $moneyFormat,
        'E' => $moneyFormat,
        'F' => $moneyFormat,
    ];
}

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => [
                    'bold' => true,
                ],
            ],
        ];
    }

    private function esDetallado(): bool
    {
        return ($this->reporte['tipo_reporte'] ?? '') === 'detallado';
    }
}
