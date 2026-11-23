<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RastreoIntervals; 
use App\Services\UbicacionService;
use App\Models\coordenadashistorial;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class cmdRastreoInterval extends Command
{
    protected $signature = 'rastreo:intervalConfig';

    protected $description = 'Revisa la base de datos las cotizaciones planeadas y si encuentra ejecuta el rastro';

    /**
     * @var UbicacionService
     */
    protected $ubiService;

    public function __construct(UbicacionService $ubiService)
    {
        parent::__construct();
        $this->ubiService = $ubiService;
    }

    public function handle()
    {
        $asignaciones = DB::table('asignaciones')
            ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
            ->join('gps_company','gps_company.id','=','equipos.gps_company_id')
            ->join('equipos as eq_chasis', 'eq_chasis.id', '=', 'asignaciones.id_chasis')
            ->select(
                'docum_cotizacion.id as id_contenedor',
                'docum_cotizacion.id_cotizacion',
                'asignaciones.id',
                'asignaciones.id_camion',
                'docum_cotizacion.num_contenedor',
                'asignaciones.fecha_inicio',
                'asignaciones.fecha_fin',
                'equipos.imei', 
                'gps_company.url_conexion as tipoGps',
                'eq_chasis.imei as imei_chasis',
                DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END as beneficiario_id"),
                DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato")
            );

        $beneficiarios = DB::table(function ($query) {
            $query->select('id', 'nombre', 'telefono', DB::raw("'Propio' as tipo_contrato"), 'id_empresa')
                ->from('operadores')
                ->union(
                    DB::table('proveedores')
                        ->select('id', 'nombre', 'telefono', DB::raw("'Subcontratado' as tipo_contrato"), 'id_empresa')
                );
        }, 'beneficiarios');

        $now = now();
        $datos = DB::table('cotizaciones')
            ->select(
                'cotizaciones.id as id_cotizacion',
                'asig.id as id_asignacion',
                'coordenadas.id as id_coordenada',
                'clients.nombre as cliente',
                'cotizaciones.origen',
                'cotizaciones.destino',
                'asig.num_contenedor as contenedor', 
                'cotizaciones.estatus',
                'asig.imei',
                'asig.id_contenedor',
                'asig.tipo_contrato',
                'asig.fecha_inicio',
                'asig.fecha_fin',
                'asig.tipoGps',
                'asig.imei_chasis'
            )
            ->join('clients', 'cotizaciones.id_cliente', '=', 'clients.id') 
            ->joinSub($asignaciones, 'asig', function ($join) {
                $join->on('asig.id_cotizacion', '=', 'cotizaciones.id'); 
            })
            ->LeftJoin('coordenadas', 'coordenadas.id_asignacion', '=', 'asig.id') 
            ->joinSub($beneficiarios, 'beneficiarios', function ($join) {
                $join->on('asig.beneficiario_id', '=', 'beneficiarios.id')
                    ->on('asig.tipo_contrato', '=', 'beneficiarios.tipo_contrato');
            })
            ->whereNotNull('asig.imei')
            ->where('cotizaciones.estatus', '=', 'Aprobada')
            ->where('asig.fecha_inicio', '<=', $now)
            ->where('asig.fecha_fin', '>=', $now)
            ->get();
      
        if ($datos->isNotEmpty()) {
            Log::info("[Rastreo Automático] Iniciando escaneo de contenedores activos en ruta. Total encontrados: " . $datos->count());
            
            $items = [];
            foreach ($datos as $dato) {
                $items[] = [
                    'tipo' => 'Contenedor',
                    'id' => $dato->id_contenedor
                ];
            }

            try {
                $resultadosGps = $this->ubiService->obtenerUbicacionPorItems($items);
                
                if (is_array($resultadosGps) || is_object($resultadosGps)) {
                    foreach ($resultadosGps as $res) {
                        $ubicacion = $res['ubicacion'] ?? null;
                        $status = $res['status'] ?? false;
                        $id_contenedor = $res['id_contenendor'] ?? null;
                        $nomContenedor = $res['contenedor'] ?? 'Contenedor';

                        if ($status && $ubicacion && isset($ubicacion['lat']) && floatval($ubicacion['lat']) !== 0.0 && $id_contenedor) {
                            $nuevaLat = floatval($ubicacion['lat']);
                            $nuevaLng = floatval($ubicacion['lng']);

                            // Verificar última coordenada registrada para este contenedor
                            $ultima = coordenadashistorial::where('ubicacionable_id', $id_contenedor)
                                ->where('ubicacionable_type', \App\Models\DocumCotizacion::class)
                                ->orderByDesc('id')
                                ->first();

                            if ($ultima && !empty($ultima->latitud) && !empty($ultima->longitud)) {
                                $distancia = $this->calcularDistanciaMetros(
                                    floatval($ultima->latitud),
                                    floatval($ultima->longitud),
                                    $nuevaLat,
                                    $nuevaLng
                                );

                                $minutosDesdeUltima = $ultima->registrado_en
                                    ? \Carbon\Carbon::parse($ultima->registrado_en)->diffInMinutes(now())
                                    : 999;

                                // Si la unidad no se ha movido al menos 50 metros y la última lectura fue hace menos de 2 horas (120 min), omitir inserción
                                if ($distancia < 50 && $minutosDesdeUltima < 120) {
                                    $this->info("Contenedor {$nomContenedor} sin movimiento ({$distancia}m hace {$minutosDesdeUltima} min). Omitiendo duplicado.");
                                    continue;
                                }
                            }

                            $this->info("Ubicación registrada para {$nomContenedor}: Lat {$nuevaLat}, Lng {$nuevaLng}");
                            Log::info("[Rastreo Automático] Contenedor {$nomContenedor} (ID: {$id_contenedor}): Ubicación registrada Lat: {$nuevaLat}, Lng: {$nuevaLng}");
                            
                            coordenadashistorial::create([
                                'latitud' => $nuevaLat,
                                'longitud' => $nuevaLng,
                                'registrado_en' => now(),
                                'user_id' => 1, // siempre sera 1 es admin del sistema
                                'ubicacionable_id' => $id_contenedor,
                                'ubicacionable_type' => \App\Models\DocumCotizacion::class,
                                'tipo' => $res['TipoEquipo'] ?? $res['tipogps'] ?? 'desconocido',
                                'status_api' => $status ? 1 : 0,
                                'id_compania_gps' => $res['gps_company_id'] ?? null,
                                'valorSolicitado' => $res['value'] ?? $res['imei'] ?? null,
                            ]);
                        } else {
                            $this->warn("No se obtuvo ubicación válida para el item: " . ($res['contenedor'] ?? 'Desconocido'));
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->error("Error general en el proceso de rastreo por ítems: " . $e->getMessage());
                Log::error("[Rastreo Automático] Error general en el escaneo por ítems. Detalle: " . $e->getMessage());
            }
        }
    }

    /**
     * Calcula la distancia en metros entre dos coordenadas geográficas utilizando la fórmula de Haversine.
     */
    protected function calcularDistanciaMetros(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radio de la Tierra en metros
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a = sin($dLat / 2) * sin($dLat / 2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($earthRadius * $c, 2);
    }
}
