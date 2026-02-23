<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

class CuentaBancosExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithEvents
{
    protected $movimientos;
    protected $cuenta;
    protected $saldoAnterior;
    protected $total_depositos;
    protected $total_cargos;
    protected $saldo_actual;

    public function __construct($movimientos, $cuenta, $saldoAnterior, $total_depositos, $total_cargos, $saldo_actual)
    {
        $this->movimientos = $movimientos;
        $this->cuenta = $cuenta;
        $this->saldoAnterior = $saldoAnterior;
        $this->total_depositos = $total_depositos;
        $this->total_cargos = $total_cargos;
        $this->saldo_actual = $saldo_actual;
    }

    public function collection()
    {
        return $this->movimientos;
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Concepto',
            'Referencia',
            'Cargo',
            'Abono',
            'Saldo',
            'Origen',
        ];
    }
    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();


                $sheet->insertNewRowBefore(1, 9);

                for ($i = 1; $i <= 8; $i++) {
                    $sheet->mergeCells("A{$i}:G{$i}");
                }


                $sheet->setCellValue('A1', 'ESTADO DE CUENTA');
                $sheet->setCellValue('A2', 'Beneficiario: ' . ($this->cuenta->nombre_beneficiario ?? ''));
                $sheet->setCellValue('A3', 'Banco: ' . ($this->cuenta->catBanco->nombre ?? ''));
                $sheet->setCellValue('A4', 'Cuenta: ' . $this->cuenta->cuenta_bancaria);
                $sheet->setCellValue('A5', 'Saldo Inicial: ' . number_format($this->saldoAnterior, 2));
                $sheet->setCellValue('A6', 'Depositos: ' . number_format($this->total_depositos, 2));
                $sheet->setCellValue('A7', 'Cargos: ' . number_format($this->total_cargos, 2));
                $sheet->setCellValue('A8', 'Saldo actual: ' . number_format($this->cuenta->saldo_actual, 2));


                $sheet->getStyle('A1:A8')->getAlignment()->setHorizontal(
                    \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER
                );


                $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(16);


                $sheet->getStyle('A2:A8')->getFont()->setSize(11);

                $sheet->getStyle('A10:G10')->getFont()->setBold(true);
            },
        ];
    }

    public function map($mov): array
    {
        return [
                      \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y'),
            $mov->concepto,
            $mov->referencia,
            $mov->tipo == 'cargo' ? $mov->monto : null,
            $mov->tipo == 'abono' ? $mov->monto : null,
            $mov->saldo_resultante,
            strtoupper(substr($mov->origen, 0, 3)),
        ];
    }

    public function columnFormats(): array
    {
        return [
            'D' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'E' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
            'F' => NumberFormat::FORMAT_CURRENCY_USD_INTEGER,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]],
        ];
    }
}
