<?php
namespace App\Http\Controllers\MEP;

use App\Http\Controllers\Controller;
use App\Models\Asignaciones;
use App\Models\CostoMEPPendiente;
use Illuminate\Http\Request;

class CostosViajeMEPController extends Controller
{
    public function index()
    {
        return view('mep.costos.index');
    }

   public function getCostosViajes()
{
    $rfcEmpresa = auth()->user()->Empresa?->rfc;

    if (!$rfcEmpresa) {
        return response()->json([]);
    }

    $asignaciones = Asignaciones::with('Contenedor.Cotizacion', 'Proveedor')
        ->whereHas('Proveedor', fn($q) => $q->where('rfc', $rfcEmpresa))
        ->whereHas('Contenedor.Cotizacion', fn($q) => $q->where('estatus', '!=', 'Cancelada'))
        ->get();

    return $asignaciones->map(function ($a) {
        $cot = $a->Contenedor?->Cotizacion;
        $doc = $a->Contenedor; // DocumCotizacion

        return [
            'id' => $a->id,
            'precio_viaje' => $a?->precio ?? 0,
            'burreo' => $a?->burreo ?? 0,
            'maniobra' => $a?->maniobra ?? 0,
            'estadia' => $a?->estadia ?? 0,
            'otro' => $a?->otro ?? 0,
            'iva' => $a?->iva ?? 0,
            'retencion' => $a?->retencion ?? 0,
           'base1' => $a->base1_proveedor ?? 0,
            'base2' => $a->base2_proveedor ?? 0,
            'sobrepeso' => $a?->sobrepeso_proveedor ?? 0,
            'precio_sobrepeso' => $cot?->precio_sobre_peso ?? 0,
            'total' => $a?->total_proveedor ?? 0,
            // NUEVOS CAMPOS CORRECTOS
            'contenedor' => $doc?->num_contenedor ?? '-',
            'destino' => $cot?->destino ?? '-',
            'estatus' => $cot?->estatus ?? '-',
        ];
    });
}




    public function guardarCambio(Request $request)
    {
        $validated = $request->validate([
            'id_asignacion' => 'required|exists:asignaciones,id',
            'precio_viaje' => 'nullable|numeric',
            'burreo' => 'nullable|numeric',
            'maniobra' => 'nullable|numeric',
            'estadia' => 'nullable|numeric',
            'otro' => 'nullable|numeric',
            'iva' => 'nullable|numeric',
            'retencion' => 'nullable|numeric',
            'base1' => 'nullable|numeric',
            'base2' => 'nullable|numeric',
            'sobrepeso' => 'nullable|numeric',
            'precio_sobrepeso' => 'nullable|numeric',
              'total' => 'nullable|numeric',
            'motivo_cambio' => 'nullable|string|max:500',
        ]);

        CostoMEPPendiente::create($validated);

        return response()->json(['success' => true, 'message' => 'Cambio enviado para verificación.']);
    }

 public function getPendientes()
{
    $rfcEmpresa = auth()->user()->Empresa?->rfc;

    if (!$rfcEmpresa) {
        return response()->json([]);
    }

    $pendientes = CostoMEPPendiente::with('asignacion.Proveedor', 'asignacion.Contenedor.Cotizacion')
        ->where('estatus', 'pendiente')
        ->whereHas('asignacion.Proveedor', fn($q) => $q->where('rfc', $rfcEmpresa))
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
        'sobrepeso' => $p->sobrepeso,
        'total' => $p->total,
        'precio_sobrepeso' => $p->precio_sobrepeso,
    ], [
        'highlight' => $highlight
    ]);
});

}


public function vistaPendientes()
{
    return view('mep.costos.pendientes');
}

}

