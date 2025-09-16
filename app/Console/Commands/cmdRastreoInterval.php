<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RastreoIntervals; 
use App\Services\UbicacionService;
use App\Models\coordenadashistorial;
use Illuminate\Support\Facades\DB;

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
   
       // ->when($idCliente !== 0, function ($query) use ($idCliente) {
       // return $query->where('cotizaciones.id_cliente', $idCliente);
       // })   
        ->where('cotizaciones.estatus', '=', 'Aprobada')
       // ->where('cotizaciones.id_empresa', '=', $idEmpresa)
        ->get();
    
        if($datos){
        foreach ($datos as $dato) {

                $imei = $dato->contenedor .'|'.$dato->imei.'|'. $dato->id_contenedor.'|'. $dato->tipoGps; 

                $ubicacion = $this->ubiService->obtenerUbicacionByImeiString($imei);
                if ($ubicacion && isset($ubicacion['lat']) && $ubicacion['lat'] !== null) {
                        $this->info("Ubicación encontrada: " . json_encode($ubicacion));
                        
                              CoordenadasHistorial::create([
                            'latitud' => $ubicacion['lat'],
                            'longitud' => $ubicacion['lng'],
                            'registrado_en' => now(),
                            'user_id' => 1, //siempre sera 1 es admin del sistema
                            'ubicacionable_id' => $dato->id_contenedor,
                            'ubicacionable_type' => 'rastreo service',
                            'tipo' => $ubicacion['tipoGPS'] ?? 'desconocido',
                        ]);
                   
                     
                    } else {
                        $this->warn("No se encontró ubicación para IMEI: $imei");
                    }
                }

        }
        


    }
}