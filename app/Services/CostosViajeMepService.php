<?php

namespace App\Services;

use App\Models\Asignaciones;
use App\Models\CostoMEPPendiente;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CostosViajeMepService
{
    /**
     * Obtiene asignaciones disponibles para que el proveedor MEP registre costos.
     */
    public function getCostosViajes(?string $rfcEmpresa, ?string $fi = null, ?string $ff = null)
    {
        if (!$rfcEmpresa) {
            return collect();
        }

        if ($fi && $ff) {
            $fi = date('Y-m-d', strtotime($fi));
            $ff = date('Y-m-d', strtotime($ff));
        } else {
            $fi = now()->subDays(7)->toDateString();
            $ff = now()->toDateString();
        }

        // Subconsulta: IDs ya presentes en costo_mep_pendientes (cualquier estatus)
        $sub = CostoMEPPendiente::select('id_asignacion');

        // Asignaciones del PROVEEDOR y en el RANGO, que NO existan en costo_mep_pendientes
        $asignaciones = Asignaciones::with('Contenedor.Cotizacion', 'Proveedor')
            ->whereHas('Proveedor', fn($q) => $q->where('rfc', $rfcEmpresa))
            ->whereHas('Contenedor.Cotizacion', function ($q) use ($fi, $ff) {
                $q->where('estatus', '!=', 'Cancelada')
                  ->whereDate('created_at', '>=', $fi)
                  ->whereDate('created_at', '<=', $ff);
            })
            ->whereNotIn('id', $sub)
            ->get();

        return $asignaciones->map(function ($a) {
            $cot = $a->Contenedor?->Cotizacion;
            $doc = $a->Contenedor;
            $z = fn() => 0;

            return [
                'id'               => $a->id,
                'contenedor'       => $doc?->num_contenedor ?? '-',
                'destino'          => $cot?->destino ?? '-',
                'estatus'          => $cot?->estatus ?? '-',
                'peso_contenedor'  => (float) ($cot?->peso_contenedor ?? 0),
                'sobrepeso'        => (float) ($cot?->sobrepeso ?? 0),
                'precio_viaje'     => $z(),
                'burreo'           => $z(),
                'maniobra'         => $z(),
                'estadia'          => $z(),
                'otro'             => $z(),
                'iva'              => $z(),
                'retencion'        => $z(),
                'base1'            => $z(),
                'base2'            => $z(),
                'precio_sobrepeso' => $z(),
                'total'            => $z(),
            ];
        })->values();
    }

    /**
     * Registra o actualiza una propuesta de costo MEP en estado pendiente.
     */
    public function guardarCambio(array $validated): array
    {
        $validated['estatus'] = 'pendiente';

        CostoMEPPendiente::updateOrCreate(
            ['id_asignacion' => $validated['id_asignacion']],
            $validated
        );

        return ['success' => true, 'message' => 'Cambio registrado correctamente.'];
    }

    /**
     * Consulta lista de costos MEP pendientes de aprobación.
     */
    public function getPendientes()
    {
        $pendientes = CostoMEPPendiente::with('asignacion.Proveedor', 'asignacion.Contenedor.Cotizacion')
            ->where('estatus', 'pendiente')
            ->whereHas('asignacion.Contenedor.Cotizacion', fn($q) => $q->where('estatus', '!=', 'CANCELADO'))
            ->get();

        return $pendientes->map(function ($p) {
            $cot = $p->asignacion?->Contenedor?->Cotizacion;
            $doc = $p->asignacion?->Contenedor;

            $campos = [
                'precio_viaje', 'burreo', 'maniobra', 'estadia', 'otro',
                'iva', 'retencion', 'base1', 'base2', 'sobrepeso', 'precio_sobrepeso', 'total'
            ];

            $highlight = [];
            foreach ($campos as $campo) {
                $original = $cot?->$campo ?? 0;
                $nuevo = $p->$campo ?? 0;
                $highlight[$campo] = floatval($original) != floatval($nuevo);
            }

            return array_merge([
                'id' => $p->id,
                'nombre_proveedor' => $p->asignacion?->Proveedor?->nombre ?? '-',
                'asignacion_id' => $p->id_asignacion,
                'contenedor' => $doc?->num_contenedor ?? '-',
                'destino' => $cot?->destino ?? '-',
                'estatus' => $cot?->estatus ?? '-',
                'precio_viaje' => $p->precio_viaje,
                'burreo' => $p->burreo,
                'maniobra' => $p->maniobra,
                'estadia' => $p->estadia,
                'otro' => $p->otro,
                'iva' => $p->iva,
                'retencion' => $p->retencion,
                'base1' => $p->base1,
                'base2' => $p->base2,
                'peso_contenedor' => (float) ($cot?->peso_contenedor ?? 0),
                'sobrepeso' => $cot?->sobrepeso ?? 0,
                'total' => $p->total,
                'precio_sobrepeso' => $p->precio_sobrepeso,
            ], [
                'highlight' => $highlight
            ]);
        });
    }

    /**
     * Conteo rápido de pendientes.
     */
    public function contarPendientes(): int
    {
        return CostoMEPPendiente::where('estatus', 'pendiente')->count();
    }

    /**
     * Compara los costos originales de la asignación contra la propuesta MEP.
     */
    public function compararCostos(int $id): array
    {
        $pendiente = CostoMEPPendiente::with('asignacion.Proveedor', 'asignacion.Contenedor')->findOrFail($id);
        $asignacion = $pendiente->asignacion;
        $contenedor = $asignacion?->Contenedor;
        $proveedor = $asignacion?->Proveedor;

        $campos = [
            'precio' => 'precio_viaje',
            'burreo' => 'burreo',
            'maniobra' => 'maniobra',
            'estadia' => 'estadia',
            'otro' => 'otro',
            'iva' => 'iva',
            'retencion' => 'retencion',
            'base1_proveedor' => 'base1',
            'base2_proveedor' => 'base2',
            'sobrepeso_proveedor' => 'sobrepeso',
            'precio_sobrepeso' => 'precio_sobrepeso',
            'total_proveedor' => 'total'
        ];

        $original = [];
        $nuevo = [];

        foreach ($campos as $campoBD => $aliasFrontend) {
            $original[$aliasFrontend] = $asignacion?->$campoBD ?? '-';
            $nuevo[$aliasFrontend] = $pendiente->$aliasFrontend ?? '-';
        }

        return [
            'original' => $original,
            'nuevo' => $nuevo,
            'asignacion' => [
                'num_contenedor' => $contenedor?->num_contenedor ?? '-',
                'proveedor' => $proveedor?->nombre ?? 'No definido',
                'fecha_inicio' => $asignacion?->fecha_inicio
                    ? Carbon::parse($asignacion->fecha_inicio)->format('Y-m-d')
                    : '-',
            ],
            'fecha_solicitud' => optional($pendiente->created_at)->format('Y-m-d'),
        ];
    }

    /**
     * Aprueba la propuesta de costo MEP.
     */
    public function aceptarCambio(int $id): array
    {
        $pendiente = CostoMEPPendiente::findOrFail($id);

        if ($pendiente->estatus !== 'pendiente') {
            return [
                'success' => false,
                'message' => 'Este cambio ya no está pendiente.'
            ];
        }

        $pendiente->estatus = 'aprobado';
        $pendiente->save();

        return [
            'success' => true,
            'message' => 'Cambio aprobado.'
        ];
    }

    /**
     * Rechaza la propuesta de costo MEP con motivo y campos observados.
     */
    public function rechazarCambio(int $id, ?string $motivo, ?array $campoObservado): array
    {
        $pendiente = CostoMEPPendiente::findOrFail($id);
        $pendiente->estatus = 'rechazado';
        $pendiente->motivo_cambio = $motivo ?? 'Sin motivo especificado';
        $pendiente->campo_observado = $campoObservado ?? null;
        $pendiente->save();

        return [
            'success' => true,
            'message' => 'Cambio rechazado correctamente.'
        ];
    }

    /**
     * Obtiene métricas agregadas por estatus para el dashboard de costos MEP.
     */
    public function contarPorEstatus(): array
    {
        $conteos = CostoMEPPendiente::selectRaw('estatus, COUNT(*) as total')
            ->groupBy('estatus')
            ->pluck('total', 'estatus');

        $pendientes = (int) ($conteos['pendiente'] ?? 0);
        $aprobados  = (int) ($conteos['aprobado']  ?? 0);
        $rechazados = (int) ($conteos['rechazado'] ?? 0);
        $porRevisar = $pendientes;
        $total      = $pendientes + $aprobados + $rechazados;

        return compact('porRevisar', 'pendientes', 'aprobados', 'rechazados', 'total');
    }

    /**
     * Obtiene el listado completo de propuestas de cambio de costos MEP.
     */
    public function getCambios()
    {
        $cambios = CostoMEPPendiente::with('asignacion.Proveedor', 'asignacion.Contenedor.Cotizacion')
            ->whereHas('asignacion.Contenedor.Cotizacion', fn($q) => $q->where('estatus', '!=', 'CANCELADO'))
            ->get();

        return $cambios->map(function ($p) {
            $cot = $p->asignacion?->Contenedor?->Cotizacion;
            $doc = $p->asignacion?->Contenedor;

            return [
                'id'               => $p->id,
                'estatus_cambio'   => $p->estatus,
                'asignacion_id'    => $p->id_asignacion,
                'contenedor'       => $doc?->num_contenedor ?? '-',
                'destino'          => $cot?->destino ?? '-',
                'precio_viaje'     => (float) $p->precio_viaje,
                'burreo'           => (float) $p->burreo,
                'maniobra'         => (float) $p->maniobra,
                'estadia'          => (float) $p->estadia,
                'otro'             => (float) $p->otro,
                'iva'              => (float) $p->iva,
                'retencion'        => (float) $p->retencion,
                'base1'            => (float) $p->base1,
                'base2'            => (float) $p->base2,
                'sobrepeso'        => (float) ($cot?->sobrepeso ?? 0),
                'precio_sobrepeso' => (float) $p->precio_sobrepeso,
                'total'            => (float) $p->total,
                'created_at'       => optional($p->created_at)->format('Y-m-d H:i:s'),
            ];
        });
    }

    /**
     * Consulta el detalle individual de un cambio de costo MEP.
     */
    public function detalleCambio(int $id): array
    {
        $pendiente = CostoMEPPendiente::with('asignacion.Proveedor', 'asignacion.Contenedor.Cotizacion')
            ->findOrFail($id);

        $cot = $pendiente->asignacion?->Contenedor?->Cotizacion;
        $doc = $pendiente->asignacion?->Contenedor;

        return [
            'id'               => $pendiente->id,
            'estatus_cambio'   => $pendiente->estatus,
            'asignacion_id'    => $pendiente->id_asignacion,
            'contenedor'       => $doc?->num_contenedor ?? '-',
            'destino'          => $cot?->destino ?? '-',
            'precio_viaje'     => (float) $pendiente->precio_viaje,
            'burreo'           => (float) $pendiente->burreo,
            'maniobra'         => (float) $pendiente->maniobra,
            'estadia'          => (float) $pendiente->estadia,
            'otro'             => (float) $pendiente->otro,
            'iva'              => (float) $pendiente->iva,
            'retencion'        => (float) $pendiente->retencion,
            'base1'            => (float) $pendiente->base1,
            'base2'            => (float) $pendiente->base2,
            'sobrepeso'        => (float) ($cot?->sobrepeso ?? 0),
            'precio_sobrepeso' => (float) $pendiente->precio_sobrepeso,
            'total'            => (float) $pendiente->total,
            'motivo_cambio'    => $pendiente->motivo_cambio,
            'campo_observado'  => $pendiente->campo_observado ?? [],
            'fecha_cambio'     => $pendiente->updated_at?->format('Y-m-d'),
            'fecha_viaje'      => $pendiente->asignacion?->fecha_inicio
                ? Carbon::parse($pendiente->asignacion->fecha_inicio)->format('Y-m-d')
                : '-',
        ];
    }

    /**
     * Reenvía un cambio de costo con nuevos valores tras haber sido observado.
     */
    public function reenviarCambio(int $id, array $validated): array
    {
        $validated['estatus'] = 'pendiente';
        $validated['campo_observado'] = [];

        $pendiente = CostoMEPPendiente::findOrFail($id);
        $pendiente->update($validated);

        return [
            'success' => true,
            'message' => 'Cambio reenviado correctamente.'
        ];
    }
}
