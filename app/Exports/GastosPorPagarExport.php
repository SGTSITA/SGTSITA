<?php

namespace App\Exports;

use App\Models\GastoImputacion;
use App\Models\Gasto;
use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Carbon\Carbon;

class GastosPorPagarExport implements FromView
{
    protected $ids;
    protected $status;

    public function __construct(array $ids = [], $status = 'por_pagar')
    {
        $this->ids = $ids;
        $this->status = $status;
    }

    public function getGastosData()
    {
        $idEmpresa = auth()->user()->id_empresa;

        // Base query for GastoImputacion
        $query = GastoImputacion::join('gastos', 'gastos.id', '=', 'gasto_imputaciones.gasto_id')
            ->whereNull('gastos.deleted_at')
            ->where('gastos.id_empresa', $idEmpresa)
            ->whereIn('gasto_imputaciones.tipo_imputacion', ['operador', 'viaje'])
            ->select('gasto_imputaciones.*', 'gastos.concepto as motivo_gasto');

        if (!empty($this->ids)) {
            $query->whereIn('gasto_imputaciones.id', $this->ids);
        } else {
            if ($this->status === 'por_pagar') {
                $query->where('gastos.estatus', '!=', 'cancelado')
                      ->where('gastos.estatus', '!=', 'pagado');
            } elseif ($this->status === 'pagados') {
                $query->where('gastos.estatus', 'pagado');
            } elseif ($this->status === 'todos') {
                $query->where('gastos.estatus', '!=', 'cancelado');
            }
        }

        $gastos = $query->get();

        $gastos->load([
            'imputable',
            'gasto.categoria',
            'gasto.conceptoCatalogo',
            'gasto.pagos.cuentaBancaria',
            'gasto.vinculos' => function ($q) {
                $q->where('tipo_vinculo', 'asignacion');
            },
            'gasto.vinculos.vinculable.Operador',
            'gasto.vinculos.vinculable.Contenedor.Cotizacion.Cliente',
            'gasto.vinculos.vinculable.Contenedor.Cotizacion.Subcliente'
        ]);

        if ($this->status === 'por_pagar') {
            // Estructura Plana
            $mapped = $gastos->map(function ($g) {
                $vinculoAsignacion = $g->gasto?->vinculos?->first();
                $asignacion = $vinculoAsignacion ? $vinculoAsignacion->vinculable : null;

                return [
                    'id' => $g->id,
                    'operador' => $g->tipo_imputacion === 'operador' ? ($g->imputable?->nombre ?? '-') : ($asignacion?->Operador?->nombre ?? '-'),
                    'cliente' => optional($asignacion?->Contenedor?->Cotizacion?->Cliente)->nombre ?? '-',
                    'subcliente' => optional($asignacion?->Contenedor?->Cotizacion?->Subcliente)->nombre ?? '-',
                    'num_contenedor' => optional($asignacion?->Contenedor)->num_contenedor ?? '-',
                    'monto' => $g->monto_imputado ?? 0,
                    'motivo' => $g->motivo_gasto ?? 'Gasto pendiente',
                    'fecha_movimiento' => $g->created_at ? Carbon::parse($g->created_at)->format('d/m/Y') : '-',
                    'fecha_aplicacion' => $g->fecha_imputacion ? Carbon::parse($g->fecha_imputacion)->format('d/m/Y') : '-',
                ];
            });
            return ['status' => $this->status, 'gastos' => $mapped];
        } else {
            // Estructura Agrupada por Categoria > Subcategoria
            $mapped = $gastos->map(function ($g) {
                $vinculoAsignacion = $g->gasto?->vinculos?->first();
                $asignacion = $vinculoAsignacion ? $vinculoAsignacion->vinculable : null;

                $categoria = $g->gasto?->categoria?->categoria ?? 'Sin Categoría';
                $subcategoria = $g->gasto?->conceptoCatalogo?->nombre ?? 'Sin Subcategoría';

                $cuentaBanco = '-';
                if ($g->gasto && $g->gasto->pagos->isNotEmpty()) {
                    $pago = $g->gasto->pagos->last();
                    if ($pago->cuentaBancaria) {
                        $cuentaStr = $pago->cuentaBancaria->cuenta_bancaria;
                        $cuentaBanco = substr($cuentaStr, -4);
                        if (strlen($cuentaStr) < 4) {
                            $cuentaBanco = $cuentaStr;
                        }
                    }
                }

                $vinculos = [];
                if (optional($asignacion?->Contenedor)->num_contenedor) {
                    $vinculos[] = 'Contenedor: ' . $asignacion->Contenedor->num_contenedor;
                }
                if ($g->tipo_imputacion === 'operador' && $g->imputable?->nombre) {
                    $vinculos[] = 'Operador: ' . $g->imputable->nombre;
                } elseif ($g->tipo_imputacion === 'viaje' && optional($asignacion)->Operador?->nombre) {
                    $vinculos[] = 'Operador: ' . $asignacion->Operador->nombre;
                }

                return [
                    'id' => $g->id,
                    'categoria' => $categoria,
                    'subcategoria' => $subcategoria,
                    'motivo' => $g->motivo_gasto ?? 'Gasto',
                    'importe' => $g->monto_imputado ?? 0,
                    'fecha' => $g->created_at ? Carbon::parse($g->created_at)->format('d/m/Y') : '-',
                    'fecha_aplicacion' => $g->fecha_imputacion ? Carbon::parse($g->fecha_imputacion)->format('d/m/Y') : '-',
                    'cuenta_banco' => $cuentaBanco,
                    'vinculos' => implode(', ', $vinculos),
                ];
            });

            $grouped = [];
            foreach ($mapped as $item) {
                $grouped[$item['categoria']][$item['subcategoria']][] = $item;
            }

            return ['status' => $this->status, 'gastos' => $grouped];
        }
    }

    public function view(): View
    {
        return view('reporteria.gxp.excel', $this->getGastosData());
    }
}

