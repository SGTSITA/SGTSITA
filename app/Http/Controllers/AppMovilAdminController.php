<?php

namespace App\Http\Controllers;

use App\Models\BitacoraViajeOperador;
use App\Models\Asignaciones;
use App\Models\coordenadashistorial;
use App\Models\Gasto;
use App\Models\GastosOperadores;
use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Services\GastosService;
use App\Models\Configuracion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class AppMovilAdminController extends Controller
{
    protected $gastosService;

    public function __construct(GastosService $gastosService)
    {
        $this->middleware('auth');
        $this->gastosService = $gastosService;
    }

    public function index(Request $request)
    {
        $query = BitacoraViajeOperador::with(['Asignacion.Operador', 'Asignacion.Contenedor.Cotizacion']);

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->whereHas('Asignacion.Operador', function($q) use ($buscar) {
                $q->where('nombre', 'like', "%{$buscar}%");
            })->orWhereHas('Asignacion.Contenedor', function($q) use ($buscar) {
                $q->where('num_contenedor', 'like', "%{$buscar}%");
            });
        }

        $bitacoras = $query->orderBy('created_at', 'desc')->paginate(15);
        $configuracion = auth()->user()->Empresa->Configuracion ?? Configuracion::first();

        return view('app_movil_admin.index', compact('bitacoras', 'configuracion'));
    }

    public function create()
    {
        // Solo asignaciones que no tengan bitacora registrada
        $asignaciones = Asignaciones::whereNotExists(function($query) {
            $query->select(DB::raw(1))
                  ->from('bitacora_viajes_operadores')
                  ->whereRaw('bitacora_viajes_operadores.id_asignacion = asignaciones.id');
        })
        ->with(['Operador', 'Contenedor'])
        ->orderBy('created_at', 'desc')
        ->limit(100)
        ->get();

        $configuracion = auth()->user()->Empresa->Configuracion ?? Configuracion::first();
        return view('app_movil_admin.create', compact('asignaciones', 'configuracion'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'id_asignacion' => 'required|exists:asignaciones,id'
        ]);

        $asignacion = Asignaciones::find($request->id_asignacion);

        $bitacora = BitacoraViajeOperador::create([
            'id_asignacion' => $asignacion->id,
            'id_operador'   => $asignacion->id_operador,
            'viaje_iniciado' => Carbon::now()
        ]);

        Session::flash('success', 'Registro de Bitácora creado con éxito.');
        return redirect()->route('app-movil-admin.edit', $bitacora->id);
    }

    public function edit($id)
    {
        $bitacora = BitacoraViajeOperador::with(['Asignacion.Operador', 'Asignacion.Contenedor.Cotizacion'])->findOrFail($id);
        $configuracion = auth()->user()->Empresa->Configuracion ?? Configuracion::first();

        // Verificar si existen gastos pagados asociados a esta asignación
        $idAsignacion = $bitacora->id_asignacion;
        $dieselPagado = Gasto::where('origen_legacy_id', $idAsignacion)
            ->where('origen_legacy', 'like', 'asignacion_planeacion%')
            ->where('concepto', 'like', '%Diesel%')
            ->where('estatus', 'pagado')
            ->exists();

        $ureaPagada = Gasto::where('origen_legacy_id', $idAsignacion)
            ->where('origen_legacy', 'like', 'asignacion_planeacion%')
            ->where('concepto', 'like', '%Urea%')
            ->where('estatus', 'pagado')
            ->exists();

        return view('app_movil_admin.edit', compact('bitacora', 'configuracion', 'dieselPagado', 'ureaPagada'));
    }

    public function update(Request $request, $id)
    {
        $bitacora = BitacoraViajeOperador::findOrFail($id);
        $asignacion = Asignaciones::find($bitacora->id_asignacion);
        
        $request->validate([
            'latitud'        => 'nullable|numeric',
            'longitud'       => 'nullable|numeric',
            'latitud_carga'  => 'nullable|numeric',
            'longitud_carga' => 'nullable|numeric',
            'latitud_fin'    => 'nullable|numeric',
            'longitud_fin'   => 'nullable|numeric',
            'litros'         => 'nullable|numeric',
            'costo'          => 'nullable|numeric',
            'litros_urea'    => 'nullable|numeric',
            'costo_urea'     => 'nullable|numeric',
            'odometro'       => 'nullable|numeric',
        ]);

        $idAsignacion = $bitacora->id_asignacion;

        // Comprobación de gastos pagados
        $dieselPagadoExistente = Gasto::where('origen_legacy_id', $idAsignacion)
            ->where('origen_legacy', 'like', 'asignacion_planeacion%')
            ->where('concepto', 'like', '%Diesel%')
            ->where('estatus', 'pagado')
            ->exists();

        $ureaPagadaExistente = Gasto::where('origen_legacy_id', $idAsignacion)
            ->where('origen_legacy', 'like', 'asignacion_planeacion%')
            ->where('concepto', 'like', '%Urea%')
            ->where('estatus', 'pagado')
            ->exists();

        // Guardar coordenadas de historial si cambiaron y fueron especificadas
        if ($request->filled('latitud') && $request->filled('longitud')) {
            if ($bitacora->latitud != $request->latitud || $bitacora->longitud != $request->longitud) {
                coordenadashistorial::create([
                    'latitud' => $request->latitud,
                    'longitud' => $request->longitud,
                    'registrado_en' => Carbon::now(),
                    'ubicacionable_id' => $asignacion->id_camion,
                    'ubicacionable_type' => 'App\Models\Equipo',
                    'tipo' => 'OperadorMovil'
                ]);
            }
        }

        if ($request->filled('latitud_carga') && $request->filled('longitud_carga')) {
            if ($bitacora->latitud_carga != $request->latitud_carga || $bitacora->longitud_carga != $request->longitud_carga) {
                coordenadashistorial::create([
                    'latitud' => $request->latitud_carga,
                    'longitud' => $request->longitud_carga,
                    'registrado_en' => Carbon::now(),
                    'ubicacionable_id' => $asignacion->id_camion,
                    'ubicacionable_type' => 'App\Models\Equipo',
                    'tipo' => 'OperadorMovil'
                ]);
            }
        }

        // Lógica de imágenes
        $path = public_path('/uploads/diesel/' . $idAsignacion);
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }

        // Reemplazar / Agregar Comprobante Diésel
        $fileName = $bitacora->comprobante;
        if ($request->hasFile('comprobante_diesel_file')) {
            if ($dieselPagadoExistente && !$request->filled('forzar_pago_diesel')) {
                return redirect()->back()->withErrors(['costo' => 'El gasto de Diesel ya se encuentra pagado y no se puede modificar la foto a menos que se fuerce la acción.']);
            }
            $file = $request->file('comprobante_diesel_file');
            $fileSuffix = uniqid() . '_diesel_admin.jpg';
            $file->move($path, $fileSuffix);
            $fileName = json_encode(['uploads/diesel/' . $idAsignacion . '/' . $fileSuffix]);
        }

        // Reemplazar / Agregar Comprobante Urea
        $ureaFileName = $bitacora->comprobante_urea;
        if ($request->hasFile('comprobante_urea_file')) {
            if ($ureaPagadaExistente && !$request->filled('forzar_pago_urea')) {
                return redirect()->back()->withErrors(['costo_urea' => 'El gasto de Urea ya se encuentra pagado y no se puede modificar la foto a menos que se fuerce la acción.']);
            }
            $file = $request->file('comprobante_urea_file');
            $fileSuffix = uniqid() . '_urea_admin.jpg';
            $file->move($path, $fileSuffix);
            $ureaFileName = json_encode(['uploads/diesel/' . $idAsignacion . '/' . $fileSuffix]);
        }

        // Reemplazar / Agregar Fotos de Carga
        $fotosCarga = json_decode($bitacora->fotos_carga, true) ?: [];
        if ($request->hasFile('fotos_carga_files')) {
            $pathCarga = public_path('/uploads/carga_contenedor/' . $idAsignacion);
            if (!file_exists($pathCarga)) {
                mkdir($pathCarga, 0777, true);
            }
            foreach ($request->file('fotos_carga_files') as $file) {
                $fileSuffix = uniqid() . '_carga_admin.jpg';
                $file->move($pathCarga, $fileSuffix);
                $fotosCarga[] = 'uploads/carga_contenedor/' . $idAsignacion . '/' . $fileSuffix;
            }
        }
        if ($request->filled('eliminar_fotos_carga')) {
            $eliminar = $request->input('eliminar_fotos_carga');
            $fotosCarga = array_values(array_filter($fotosCarga, function($f) use ($eliminar) {
                return !in_array($f, $eliminar);
            }));
        }

        // Reemplazar / Agregar Fotos de Entrega
        $fotosFin = json_decode($bitacora->fotos_fin, true) ?: [];
        if ($request->hasFile('fotos_fin_files')) {
            $pathFin = public_path('/uploads/entrega_contenedor/' . $idAsignacion);
            if (!file_exists($pathFin)) {
                mkdir($pathFin, 0777, true);
            }
            foreach ($request->file('fotos_fin_files') as $file) {
                $fileSuffix = uniqid() . '_entrega_admin.jpg';
                $file->move($pathFin, $fileSuffix);
                $fotosFin[] = 'uploads/entrega_contenedor/' . $idAsignacion . '/' . $fileSuffix;
            }
        }
        if ($request->filled('eliminar_fotos_fin')) {
            $eliminar = $request->input('eliminar_fotos_fin');
            $fotosFin = array_values(array_filter($fotosFin, function($f) use ($eliminar) {
                return !in_array($f, $eliminar);
            }));
        }

        // Si cambia costo de diésel
        if ($request->filled('costo') && floatval($request->costo) != floatval($bitacora->costo)) {
            if ($dieselPagadoExistente && !$request->filled('forzar_pago_diesel')) {
                return redirect()->back()->withErrors(['costo' => 'El gasto de Diesel ya está pagado. Debe confirmar la advertencia para realizar cambios sobre una entidad pagada.']);
            }

            // Afectar entidad gastos
            $doc = DocumCotizacion::find($asignacion->id_contenedor);
            $idCotizacion = $doc ? $doc->id_cotizacion : null;

            // Buscar gasto operador existente o crear/actualizar
            $gastoOperador = GastosOperadores::updateOrCreate(
                ['id_asignacion' => $idAsignacion, 'tipo' => 'Diesel'],
                [
                    'id_operador' => $asignacion->id_operador,
                    'id_cotizacion' => $idCotizacion,
                    'cantidad' => $request->costo,
                    'comprobante' => $fileName,
                    'fecha_pago' => Carbon::now()
                ]
            );

            try {
                $this->gastosService->registrarDesdeGastoOperador($gastoOperador);
            } catch (\Exception $e) {
                \Log::error("Error actualizando gasto de diesel en admin panel: " . $e->getMessage());
            }
        }

        // Si cambia costo de urea
        if ($request->filled('costo_urea') && floatval($request->costo_urea) != floatval($bitacora->costo_urea)) {
            if ($ureaPagadaExistente && !$request->filled('forzar_pago_urea')) {
                return redirect()->back()->withErrors(['costo_urea' => 'El gasto de Urea ya está pagado. Debe confirmar la advertencia para realizar cambios sobre una entidad pagada.']);
            }

            // Invocar el registro de Urea
            $idEmpresa = $asignacion->id_empresa;
            $contenedor = DocumCotizacion::find($asignacion->id_contenedor);
            if ($contenedor) {
                $cotizacion = Cotizaciones::find($contenedor->id_cotizacion);
                if ($cotizacion && !$idEmpresa) {
                    $idEmpresa = $cotizacion->id_empresa;
                }
            }
            if (!$idEmpresa) {
                $idEmpresa = 27;
            }

            $con = DB::table('gasto_conceptos')->where('clave', 'GUREA')->orWhere('nombre', 'like', '%Urea%')->first();
            $conceptoId = $con ? $con->id : 42;

            try {
                $this->gastosService->registrar([
                    'id_empresa'          => $idEmpresa,
                    'categoria_gasto_id'  => 1,
                    'gasto_concepto_id'   => $conceptoId,
                    'concepto'            => 'GU001 - Urea',
                    'monto_total'         => floatval($request->costo_urea),
                    'tipo_gasto'          => 'viaje',
                    'metodo_imputacion'   => 'directo',
                    'estatus'             => $ureaPagadaExistente ? 'pagado' : 'pendiente_pago',
                    'fecha_gasto'         => Carbon::now()->toDateString(),
                    'origen_legacy'       => 'asignacion_planeacionGU001 - Urea',
                    'origen_legacy_id'    => $asignacion->id,
                ]);
            } catch (\Exception $e) {
                \Log::error("Error actualizando gasto de urea en admin panel: " . $e->getMessage());
            }
        }

        // Actualizar litros de diesel o urea en cotizacion
        $doc = DocumCotizacion::find($asignacion->id_contenedor);
        $idCotizacion = $doc ? $doc->id_cotizacion : null;
        $cotizacion = Cotizaciones::find($idCotizacion);
        if ($cotizacion) {
            if (isset($request->litros)) {
                $cotizacion->litros_diesel = $request->litros;
            }
            if (isset($request->litros_urea)) {
                $cotizacion->litros_urea = $request->litros_urea;
            }
            $cotizacion->update();
        }

        // Actualizar Bitácora
        $bitacora->update([
            'latitud'      => $request->latitud,
            'longitud'     => $request->longitud,
            'latitud_carga' => $request->latitud_carga,
            'longitud_carga' => $request->longitud_carga,
            'latitud_fin'  => $request->latitud_fin,
            'longitud_fin' => $request->longitud_fin,
            'litros'       => $request->litros,
            'costo'        => $request->costo,
            'litros_urea'  => $request->litros_urea,
            'costo_urea'   => $request->costo_urea,
            'odometro'     => $request->odometro,
            'comprobante'  => $fileName,
            'comprobante_urea' => $ureaFileName,
            'fotos_carga'  => json_encode($fotosCarga),
            'fotos_fin'    => json_encode($fotosFin),
        ]);

        Session::flash('edit', 'La bitácora de viaje se ha actualizado con éxito.');
        return redirect()->route('app-movil-admin.index');
    }

    public function destroy($id)
    {
        $bitacora = BitacoraViajeOperador::findOrFail($id);
        $bitacora->delete();

        Session::flash('success', 'Registro de bitácora eliminado con éxito.');
        return redirect()->route('app-movil-admin.index');
    }
}
