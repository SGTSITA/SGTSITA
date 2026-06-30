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

   

        public function __construct(UbicacionService $ubiService)
        {
            parent::__construct();
            $this->ubiService = $ubiService;
        }

    public function handle()
    {
       // $idCliente = 0;
        //$cliendID = auth()->user()->id_cliente;
       //if($cliendID !== 0)
       //{
       // $idCliente =$cliendID;
       //}
         

               
        //$idEmpresa = Auth::User()->id_empresa;
        $asignaciones = DB::table('asignaciones')
        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
        ->join('gps_company','gps_company.id','=','equipos.gps_company_id')
        ->join('equipos as eq_chasis', 'eq_chasis.id', '=', 'asignaciones.id_chasis')

        ->select(
            'docum_cotizacion.id as id_contenedor',
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
            $join->on('asig.id_contenedor', '=', 'cotizaciones.id'); 
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
                            $this->info("Ubicación encontrada para {$nomContenedor}: Lat {$ubicacion['lat']}, Lng {$ubicacion['lng']}");
                            Log::info("[Rastreo Automático] Contenedor {$nomContenedor} (ID: {$id_contenedor}): Ubicación encontrada Lat: {$ubicacion['lat']}, Lng: {$ubicacion['lng']}");
                            
                            CoordenadasHistorial::create([
                                'latitud' => $ubicacion['lat'],
                                'longitud' => $ubicacion['lng'],
                                'registrado_en' => now(),
                                'user_id' => 1, // siempre sera 1 es admin del sistema
                                'ubicacionable_id' => $id_contenedor,
                                'ubicacionable_type' => 'rastreo service',
                                'tipo' => $res['TipoEquipo'] ?? $res['tipogps'] ?? 'desconocido',
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
}
