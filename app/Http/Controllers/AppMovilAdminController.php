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
use App\Models\GlobalConfig;
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

        if (auth()->user()->es_admin !== 1) {
            $idEmpresa = auth()->user()->id_empresa;
            $query->whereHas('Asignacion', function($q) use ($idEmpresa) {
                $q->where('id_empresa', $idEmpresa);
            });
        }

        if ($request->filled('buscar')) {
            $buscar = $request->input('buscar');
            $query->where(function($q) use ($buscar) {
                $q->whereHas('Asignacion.Operador', function($q2) use ($buscar) {
                    $q2->where('nombre', 'like', "%{$buscar}%");
                })->orWhereHas('Asignacion.Contenedor', function($q2) use ($buscar) {
                    $q2->where('num_contenedor', 'like', "%{$buscar}%");
                });
            });
        }

        $bitacoras = $query->orderBy('created_at', 'desc')->paginate(15);
        $configuracion = auth()->user()->Empresa->Configuracion ?? Configuracion::first();

        // 1. Configuración de Documentos del Operador
        $configDocs = GlobalConfig::where('key', 'documentos_operador')->first();
        $documentosSeleccionados = [];
        if ($configDocs && !empty($configDocs->value)) {
            $decoded = json_decode($configDocs->value, true);
            if (is_array($decoded)) {
                $documentosSeleccionados = $decoded;
            }
        } else {
            $documentosSeleccionados = ['doda', 'boleta_liberacion', 'carta_porte'];
        }

        // Catálogo de Documentos disponibles (Sección Documentación de Cotizaciones)
        $documentosDisponibles = [
            'doc_ccp' => [
                'label' => 'Formato CCP',
                'descripcion' => 'Complemento Carta Porte del contenedor',
                'icono' => 'fa-file-alt'
            ],
            'boleta_liberacion' => [
                'label' => 'Boleta de Liberación',
                'descripcion' => 'Boleta que autoriza la liberación del contenedor',
                'icono' => 'fa-file-signature'
            ],
            'doda' => [
                'label' => 'DODA',
                'descripcion' => 'Documento de Operación para Despacho Aduanero',
                'icono' => 'fa-file-invoice'
            ],
            'carta_porte' => [
                'label' => 'Carta Porte (PDF)',
                'descripcion' => 'Comprobante y carta de porte en formato PDF',
                'icono' => 'fa-file-pdf'
            ],
            'carta_porte_xml' => [
                'label' => 'Carta Porte (XML)',
                'descripcion' => 'Archivo XML fiscal de la carta de porte',
                'icono' => 'fa-file-code'
            ],
            'boleta_vacio' => [
                'label' => 'Prealta - Boleta de Vacío',
                'descripcion' => 'Comprobante de devolución o recepción de vacío',
                'icono' => 'fa-box-open'
            ],
            'doc_eir' => [
                'label' => 'EIR - Comprobante de Vacío',
                'descripcion' => 'Equipment Interchange Receipt / Comprobante de vacío',
                'icono' => 'fa-clipboard-check'
            ],
            'evidencia_descarga' => [
                'label' => 'Evidencia de Descarga',
                'descripcion' => 'Comprobante / foto de entrega en destino',
                'icono' => 'fa-check-circle'
            ],
            'comprobante_pago_pdf' => [
                'label' => 'Complemento de Pago (PDF)',
                'descripcion' => 'Comprobante fiscal de complemento de pago en PDF',
                'icono' => 'fa-file-invoice-dollar'
            ],
            'comprobante_pago_xml' => [
                'label' => 'Complemento de Pago (XML)',
                'descripcion' => 'Archivo XML fiscal del complemento de pago',
                'icono' => 'fa-file-code'
            ],
            'boleta_patio' => [
                'label' => 'Boleta de Patio',
                'descripcion' => 'Documento / evidencia de ingreso o maniobra en patio',
                'icono' => 'fa-warehouse'
            ],
            'cima' => [
                'label' => 'Documento CIMA',
                'descripcion' => 'Documento de maniobra o autorización CIMA',
                'icono' => 'fa-file-contract'
            ],
        ];

        // 2. Configuración de Tiempo de Notificación de Captura de Gastos
        $configNotif = GlobalConfig::where('key', 'tiempo_notificacion_captura_gastos')->first();
        $notifDia = '6'; // Default: Sábado
        $notifHora = '10:00'; // Default: 10:00 AM
        if ($configNotif && !empty($configNotif->value)) {
            $parts = explode(' ', trim($configNotif->value));
            if (count($parts) >= 2) {
                $notifDia = $parts[0];
                $notifHora = $parts[1];
            } elseif (count($parts) == 1) {
                if (str_contains($parts[0], ':')) {
                    $notifHora = $parts[0];
                } else {
                    $notifDia = $parts[0];
                }
            }
        }

        $diasSemana = [
            '1' => 'Lunes',
            '2' => 'Martes',
            '3' => 'Miércoles',
            '4' => 'Jueves',
            '5' => 'Viernes',
            '6' => 'Sábado',
            '7' => 'Domingo',
        ];

        // 3. Todas las configuraciones globales
        $todasLasConfigs = GlobalConfig::orderBy('key', 'asc')->get();

        // 4. Logs de la App Móvil (almacenados en storage/logs/app_movil/)
        $logDir = storage_path('logs/app_movil');
        $availableDates = [];
        if (file_exists($logDir)) {
            $files = glob($logDir . '/app_logs_*.json');
            foreach ($files as $f) {
                if (preg_match('/app_logs_(\d{4}-\d{2}-\d{2})\.json$/', $f, $matches)) {
                    $availableDates[] = $matches[1];
                }
            }
            rsort($availableDates);
        }

        $selectedLogDate = $request->input('log_fecha', !empty($availableDates) ? $availableDates[0] : Carbon::now()->format('Y-m-d'));
        $logBusqueda = $request->input('log_buscar', '');
        $logNivel = $request->input('log_nivel', '');

        $appLogs = [];
        $totalLogsDia = 0;
        $totalErrorsDia = 0;
        $totalHttpDia = 0;

        $logFilePath = $logDir . "/app_logs_{$selectedLogDate}.json";
        if (file_exists($logFilePath)) {
            $rawLogs = json_decode(file_get_contents($logFilePath), true) ?: [];
            $totalLogsDia = count($rawLogs);

            foreach ($rawLogs as $l) {
                if (($l['level'] ?? '') === 'ERROR' || !empty($l['error'])) {
                    $totalErrorsDia++;
                }
                if (($l['level'] ?? '') === 'HTTP') {
                    $totalHttpDia++;
                }
            }

            // Mostrar los más recientes primero
            $rawLogs = array_reverse($rawLogs);

            // Filtrar
            foreach ($rawLogs as $logItem) {
                if ($logNivel && ($logItem['level'] ?? '') !== $logNivel) {
                    continue;
                }
                if ($logBusqueda) {
                    $haystack = strtolower(json_encode($logItem));
                    if (!str_contains($haystack, strtolower($logBusqueda))) {
                        continue;
                    }
                }
                $appLogs[] = $logItem;
            }
        }

        return view('app_movil_admin.index', compact(
            'bitacoras',
            'configuracion',
            'documentosSeleccionados',
            'documentosDisponibles',
            'notifDia',
            'notifHora',
            'diasSemana',
            'todasLasConfigs',
            'availableDates',
            'selectedLogDate',
            'logBusqueda',
            'logNivel',
            'appLogs',
            'totalLogsDia',
            'totalErrorsDia',
            'totalHttpDia'
        ));
    }

    public function create()
    {
        // Solo asignaciones que no tengan bitacora registrada
        $queryAsignaciones = Asignaciones::whereNotExists(function($query) {
            $query->select(DB::raw(1))
                  ->from('bitacora_viajes_operadores')
                  ->whereRaw('bitacora_viajes_operadores.id_asignacion = asignaciones.id');
        });

        if (auth()->user()->es_admin !== 1) {
            $queryAsignaciones->where('id_empresa', auth()->user()->id_empresa);
        }

        $asignaciones = $queryAsignaciones->with(['Operador', 'Contenedor'])
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

    /**
     * Actualizar configuraciones principales de la App Móvil (Documentos y Notificaciones).
     */
    public function updateConfigs(Request $request)
    {
        // 1. Documentos del operador
        $docs = $request->input('documentos_operador', []);
        if (!is_array($docs)) {
            $docs = [];
        }
        GlobalConfig::setVal(
            'documentos_operador',
            json_encode(array_values($docs)),
            'Documentos permitidos para visualización y descarga por el operador en la App Móvil'
        );

        // 2. Tiempo de notificación de recordatorio de captura de gastos
        $dia = $request->input('notif_dia', '6');
        $hora = $request->input('notif_hora', '10:00');
        $valorTiempo = trim("{$dia} {$hora}");

        GlobalConfig::setVal(
            'tiempo_notificacion_captura_gastos',
            $valorTiempo,
            'Día de la semana (1-7) y hora (HH:mm) para enviar recordatorio de captura de gastos al operador'
        );

        Session::flash('success', 'Configuraciones de la App Móvil actualizadas con éxito.');
        return redirect()->route('app-movil-admin.index', ['tab' => 'config']);
    }

    /**
     * Guardar una nueva configuración personalizada o editar una existente en global_configs.
     */
    public function storeCustomConfig(Request $request)
    {
        $request->validate([
            'key' => 'required|string|max:191',
            'value' => 'nullable|string',
            'description' => 'nullable|string|max:255',
        ]);

        GlobalConfig::setVal(
            trim($request->key),
            $request->value,
            $request->description
        );

        Session::flash('success', 'Parámetro de configuración guardado con éxito.');
        return redirect()->route('app-movil-admin.index', ['tab' => 'config']);
    }

    /**
     * Eliminar un registro de global_configs.
     */
    public function destroyConfig($id)
    {
        $config = GlobalConfig::findOrFail($id);
        $config->delete();

        Session::flash('success', 'Parámetro de configuración eliminado con éxito.');
        return redirect()->route('app-movil-admin.index', ['tab' => 'config']);
    }

    /**
     * Limpiar logs de la app móvil.
     */
    public function limpiarLogs(Request $request)
    {
        $fecha = $request->input('fecha');
        $logDir = storage_path('logs/app_movil');

        if ($fecha === 'all') {
            if (file_exists($logDir)) {
                $files = glob($logDir . '/app_logs_*.json');
                foreach ($files as $f) {
                    @unlink($f);
                }
            }
            Session::flash('success', 'Todos los logs de la app móvil fueron eliminados correctamente.');
        } elseif ($fecha) {
            $file = $logDir . "/app_logs_{$fecha}.json";
            if (file_exists($file)) {
                @unlink($file);
                Session::flash('success', "Los logs del día {$fecha} fueron eliminados.");
            }
        }

        return redirect()->route('app-movil-admin.index', ['tab' => 'logs_app', 'log_fecha' => Carbon::now()->format('Y-m-d')]);
    }

    /**
     * Descargar archivo JSON de logs de la app móvil para una fecha.
     */
    public function descargarLogs(Request $request)
    {
        $fecha = $request->input('fecha', Carbon::now()->format('Y-m-d'));
        $logFile = storage_path("logs/app_movil/app_logs_{$fecha}.json");

        if (file_exists($logFile)) {
            return response()->download($logFile, "app_logs_{$fecha}.json", [
                'Content-Type' => 'application/json',
            ]);
        }

        Session::flash('error', 'No se encontró el archivo de logs para la fecha seleccionada.');
        return redirect()->route('app-movil-admin.index', ['tab' => 'logs_app', 'log_fecha' => $fecha]);
    }
}
