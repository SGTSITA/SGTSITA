<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cotizaciones;
use App\Models\CostosViajesExtra;

class CostosViajesController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if (!$user->hasRole('Proveedor')) {
            abort(403, 'No autorizado');
        }

        $rfc = $user->rfc;

        $cotizaciones = Cotizaciones::whereHas('DocCotizacion.Asignaciones.Proveedor', function ($query) use ($rfc) {
                $query->where('rfc', $rfc);
            })
            ->where('restante', '>', 0)
            ->where('estatus', '!=', 'Cancelada')
            ->with(['DocCotizacion.Asignaciones.Proveedor', 'DocCotizacion', 'Subcliente'])
            ->get();

        $data = $cotizaciones->map(function ($c) {
            $extra = CostosViajesExtra::where('viaje_id', $c->id)->first();
            return [
                'viaje_id' => $c->id,
                'num_contenedor' => $c->DocCotizacion->num_contenedor ?? '',
                'subcliente' => $c->Subcliente->nombre ?? '',
                'tipo_viaje' => $c->tipo_viaje,
                'estatus' => $c->estatus,
                'carta_porte' => (bool) $c->carta_porte,
                'carta_porte_xml' => (bool) $c->carta_porte_xml,
                'base1' => $extra->base1 ?? '',
                'base2' => $extra->base2 ?? '',
            ];
        })->values();

        return view('mep.costos_viajes.index', compact('data'));
    }

   public function guardar(Request $request)
{
    try {
        $datos = $request->all();

        if (!is_array($datos)) {
            throw new \Exception('La estructura de datos enviada no es válida.');
        }

        \DB::beginTransaction();

        foreach ($datos as $dato) {
            $viajeId = $dato['viaje_id'] ?? null;

            if (!$viajeId) {
                throw new \Exception('Falta el ID de viaje en uno de los elementos.');
            }

            // Asegura que base1 y base2 sean valores válidos o null
            $base1 = isset($dato['base1']) && is_numeric($dato['base1']) ? number_format((float)$dato['base1'], 4, '.', '') : null;
            $base2 = isset($dato['base2']) && is_numeric($dato['base2']) ? number_format((float)$dato['base2'], 4, '.', '') : null;

            CostosViajesExtra::updateOrCreate(
                ['viaje_id' => $viajeId],
                ['base1' => $base1, 'base2' => $base2]
            );
        }

        \DB::commit();
        return response()->json(['message' => 'Costos guardados correctamente'], 200);

    } catch (\Exception $e) {
        \DB::rollBack();

        // Opcional: puedes loguear el error para seguimiento
        \Log::error('Error al guardar costos de viaje extra: ' . $e->getMessage());

        return response()->json([
            'message' => 'Error al guardar costos',
            'error' => $e->getMessage(),
        ], 500);
    }
}


}
