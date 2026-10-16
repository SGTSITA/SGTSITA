<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;

class ContabilidadReporteExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        protected string $tipoReporte,
        protected array $data,
        protected string $nombresEmpresas,
        protected ?string $equipoNombre,
        protected string $fechaCorte,
        protected ?string $fechaInicio = null
    ) {}

    public function view(): View
    {
        $vista = match ($this->tipoReporte) {
            'estado_resultados'   => 'contabilidad.excel.estado-resultados',
            'balanza_comprobacion'=> 'contabilidad.excel.balanza-comprobacion',
            default               => 'contabilidad.excel.balance-general',
        };

        return view($vista, [
            'tipoReporte'     => $this->tipoReporte,
            'data'            => $this->data,
            'nombresEmpresas' => $this->nombresEmpresas,
            'equipoNombre'    => $this->equipoNombre,
            'fechaCorte'      => $this->fechaCorte,
            'fechaInicio'     => $this->fechaInicio,
        ]);
    }

    public function title(): string
    {
        return match ($this->tipoReporte) {
            'estado_resultados'   => 'Estado de Resultados',
            'balanza_comprobacion'=> 'Balanza de Comprobación',
            default               => 'Balance General',
        };
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 14, 'color' => ['rgb' => '1F497D']]],
            2 => ['font' => ['bold' => true, 'size' => 12]],
            3 => ['font' => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '595959']]],
            4 => ['font' => ['bold' => true, 'size' => 10]],
        ];
    }
}
