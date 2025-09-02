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

public function getCostosViajes(Request $request)
{
    $rfcEmpresa = auth()->user()->Empresa?->rfc;
    if (!$rfcEmpresa) {
        return response()->json([]);
    }

    // Fechas (por defecto últimos 7 días)
    $fi = $request->input('fecha_inicio');
    $ff = $request->input('fecha_fin');
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
        ->whereNotIn('id', $sub) // ⬅ clave: solo “nuevos”, no están en costo_mep_pendientes
        ->get();

    // Respuesta para el front (valores editables en 0)
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

    // Siempre forzar estatus pendiente al crear o actualizar
    $validated['estatus'] = 'pendiente';

    CostoMEPPendiente::updateOrCreate(
        ['id_asignacion' => $validated['id_asignacion']], // Condición
        $validated // Datos a actualizar o crear
    );

    return response()->json(['success' => true, 'message' => 'Cambio registrado correctamente.']);
}

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



public function vistaPendientes()
{
    return view('mep.costos.pendientes');
}

public function contarPendientes()
{
   $pendientes = CostoMEPPendiente::where('estatus', 'pendiente')->count();

    return response()->json(['total' => $pendientes]);
}

public function compararCostos($id)
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

    return response()->json([
        'original' => $original,
        'nuevo' => $nuevo,
        'asignacion' => [
            'num_contenedor' => $contenedor?->num_contenedor ?? '-',
            'proveedor' => $proveedor?->nombre ?? 'No definido',
            'fecha_inicio' => $asignacion?->fecha_inicio
    ? \Carbon\Carbon::parse($asignacion->fecha_inicio)->format('Y-m-d')
    : '-',
        ],
        'fecha_solicitud' => optional($pendiente->created_at)->format('Y-m-d'),
    ]);
}


public function aceptarCambio(Request $request, $id)
{
    $pendiente = CostoMEPPendiente::findOrFail($id);

    // Opcional: evita reaprobar o aprobar rechazados
    if ($pendiente->estatus !== 'pendiente') {
        return response()->json([
            'success' => false,
            'message' => 'Este cambio ya no está pendiente.'
        ], 422);
    }

    // Solo actualizar estatus del pendiente
    $pendiente->estatus = 'aprobado';
    $pendiente->save();

    return response()->json([
        'success' => true,
        'message' => 'Cambio aprobado.'
    ]);
}


public function rechazarCambio(Request $request, $id)
{
    $request->validate([
        'motivo' => 'nullable|string|max:500',
        'campo_observado' => 'nullable|array'
    ]);

    $pendiente = CostoMEPPendiente::findOrFail($id);
    $pendiente->estatus = 'rechazado';
    $pendiente->motivo_cambio = $request->motivo ?? 'Sin motivo especificado';
    $pendiente->campo_observado = $request->campo_observado ?? null;
    $pendiente->save();

    return response()->json(['success' => true, 'message' => 'Cambio rechazado correctamente.']);
}













//////////////////////////////////////////////////////////////////////////////////////

public function dashboard()
{
    $conteos = \App\Models\CostoMEPPendiente::selectRaw('estatus, COUNT(*) as total')
        ->groupBy('estatus')
        ->pluck('total','estatus');

    $pendientes = (int) ($conteos['pendiente'] ?? 0);
    $aprobados  = (int) ($conteos['aprobado']  ?? 0);
    $rechazados = (int) ($conteos['rechazado'] ?? 0);
    $porRevisar = $pendientes;
    $total      = $pendientes + $aprobados + $rechazados;
    return view('mep.costos.dashboard', compact('porRevisar','pendientes','aprobados','rechazados','total'));
}

public function contarPorEstatus()
{
    $conteos = \App\Models\CostoMEPPendiente::selectRaw('estatus, COUNT(*) as total')
        ->groupBy('estatus')
        ->pluck('total','estatus');

    $pendientes = (int) ($conteos['pendiente'] ?? 0);
    $aprobados  = (int) ($conteos['aprobado']  ?? 0);
    $rechazados = (int) ($conteos['rechazado'] ?? 0);
    $porRevisar = $pendientes;
    $total      = $pendientes + $aprobados + $rechazados;
    return response()->json(compact('porRevisar','pendientes','aprobados','rechazados','total'));
}

public function vistaViajesCambios()
{
    // Solo renderiza la vista; el JS consume /costos/mep/cambios/data
    return view('mep.costos.viajes_cambios');
}

public function getCambios()
{
    $cambios = \App\Models\CostoMEPPendiente::with('asignacion.Proveedor', 'asignacion.Contenedor.Cotizacion')
        ->whereHas('asignacion.Contenedor.Cotizacion', fn($q) => $q->where('estatus', '!=', 'CANCELADO'))
        ->get();

    return $cambios->map(function ($p) {
        $cot = $p->asignacion?->Contenedor?->Cotizacion;
        $doc = $p->asignacion?->Contenedor;

        return [
            'id'               => $p->id,
            'estatus_cambio'   => $p->estatus, // pendiente | aprobado | rechazado
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
    
    
    
 public function detalleCambio($id)
{
    $pendiente = \App\Models\CostoMEPPendiente::with('asignacion.Proveedor', 'asignacion.Contenedor.Cotizacion')
        ->findOrFail($id);

    $cot = $pendiente->asignacion?->Contenedor?->Cotizacion;
    $doc = $pendiente->asignacion?->Contenedor;

    return response()->json([
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
                                ? \Carbon\Carbon::parse($pendiente->asignacion->fecha_inicio)->format('Y-m-d')
                                : '-',
    ]);
}


public function reenviarCambio(Request $request, $id)
{
    $validated = $request->validate([
        'precio_viaje'        => 'nullable|numeric',
        'burreo'              => 'nullable|numeric',
        'maniobra'            => 'nullable|numeric',
        'estadia'             => 'nullable|numeric',
        'otro'                => 'nullable|numeric',
        'iva'                 => 'nullable|numeric',
        'retencion'           => 'nullable|numeric',
        'base1'               => 'nullable|numeric',
        'base2'               => 'nullable|numeric',
        'sobrepeso'           => 'nullable|numeric',
        'precio_sobrepeso'    => 'nullable|numeric',
        'total'               => 'nullable|numeric',
        'motivo_cambio'       => 'nullable|string|max:500',
    ]);

    $validated['estatus'] = 'pendiente';
    $validated['campo_observado'] = []; // Vaciar observados

    $pendiente = \App\Models\CostoMEPPendiente::findOrFail($id);
    $pendiente->update($validated);

    return response()->json([
        'success' => true,
        'message' => 'Cambio reenviado correctamente.'
    ]);
}



}

