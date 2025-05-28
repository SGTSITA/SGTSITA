<?php

namespace App\Exports;

use App\Models\GastosOperadores;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;

class GastosPorPagarExport implements FromView
{
    protected $ids;

    public function __construct(array $ids = [])
    {
        $this->ids = $ids;
    }

    public function getGastosData()
    {
        $query = GastosOperadores::with([
            'Asignaciones.Proveedor',
            'Asignaciones.Contenedor.Cotizacion.Cliente',
            'Asignaciones.Contenedor.Cotizacion.Subcliente'
        ])
        ->whereHas('Asignaciones', fn ($q) => $q->where('id_empresa', auth()->user()->id_empresa))
        ->where('estatus', '!=', 'Pagado');

        if (!empty($this->ids)) {
            $query->whereIn('id', $this->ids);
        }

        return $query->get()->map(function ($g) {
            $a = $g->Asignaciones;
            return [
                'id' => $g->id,
                'operador' => optional($g->Operador)->nombre ?? '-',

                'cliente' => optional($a?->Contenedor?->Cotizacion?->Cliente)->nombre ?? '-',
                'subcliente' => optional($a?->Contenedor?->Cotizacion?->Subcliente)->nombre ?? '-',
                'num_contenedor' => optional($a?->Contenedor)->num_contenedor ?? '-',
                'monto' => $g->cantidad ?? 0,
                'motivo' => $g->tipo ?? 'Gasto pendiente',
                'fecha_movimiento' => $g->created_at ? Carbon::parse($g->created_at)->format('d/m/Y') : '-',
                'fecha_aplicacion' => $g->fecha_pago ? Carbon::parse($g->fecha_pago)->format('d/m/Y') : '-',
            ];
        });
    }

    public function view(): View
    {
        return view('reporteria.gxp.excel', [
            'gastos' => $this->getGastosData()
        ]);
    }
}
