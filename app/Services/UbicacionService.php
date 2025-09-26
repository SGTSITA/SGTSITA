<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use App\Traits\GlobalGpsTrait as GlobalGps;
use App\Traits\SkyAngelGpsTrait as SkyAngel;
use App\Traits\JimiGpsTrait;
use App\Traits\LegoGpsTrait as LegoGps;
use App\Traits\CommonGpsTrait;
use Illuminate\Support\Facades\DB;
use App\Models\Empresas;

class UbicacionService
{
    public function obtenerUbicacionByImeiString(string $imeiString)
    {
        echo "Procesando IMEI String: $imeiString\n";
        $resultados = [];
        // $imeiString = "TP-001|865468051839242|1797|https://open.iopgps.com"
        [$contenedor, $imei, $id_contenedor, $tipoGps] = explode('|', $imeiString);

        // Buscamos info del equipo y empresa
        $RfcyEquipo = $this->buscartipoProveedor($contenedor, $id_contenedor, $imei);
        [$Rfc, $equipo, $empresaIdRastro, $TipoEquipo] = explode('|', $RfcyEquipo);
        $empresaIdRastro = (int) $empresaIdRastro;

        $esDatoEmp =  "NO";

        $ubicacion = null;
        $tipoGpsResponse = "";

        switch ($tipoGps) {
            case 'https://open.iopgps.com': // Global GPS
                $data = GlobalGps::getDeviceRealTimeLocation($imei);
                $tipoGpsResponse = "Global GPS";
                $ubicacionApiResponse = $data->data;

                $ubicacion = [
                    'lat' => $ubicacionApiResponse['lat'] ?? null,
                    'lng' => $ubicacionApiResponse['lng'] ?? null,
                    'velocidad' => 0,
                    'imei' => $imei,
                    'deviceName' => '',
                    'mcType' => '',
                    'datac' => $data,
                    'esDatoEmp' => $esDatoEmp,
                    'tipoEquipo' => $TipoEquipo,
                    'tipoGPS' => $tipoGpsResponse
                ];
                break;

            case 'http://sta.skyangel.com.mx:8085/api/tracks/v1': // SkyAngel
                $username = config('services.SkyAngelGps.username');
                $password = config('services.SkyAngelGps.password');
                $accessToken = SkyAngel::getAccessToken($username, $password);
                $data = SkyAngel::getLocation($accessToken);
                $ubicacionApiResponse = $data->data;
                $tipoGpsResponse = "SkyGPS";

                $ubicacion = [
                    'lat' => $ubicacionApiResponse['latitude'] ?? null,
                    'lng' => $ubicacionApiResponse['longitude'] ?? null,
                    'velocidad' => 0,
                    'imei' => $imei,
                    'deviceName' => $ubicacionApiResponse['economico'] ?? null,
                    'mcType' => '',
                    'datac' => $data,
                    'esDatoEmp' => $esDatoEmp,
                    'tipoEquipo' => $TipoEquipo,
                    'tipoGPS' => $tipoGpsResponse
                ];
                break;

             case 'https://us-open.tracksolidpro.com/route/rest'://jimi -Concox
                            //Datos de dispositivo por IMEI: El metodo soporta multiples IMEIS, Separe cada imei por coma (,). Maximo 100 IMEIS

                            $adicionales['imeis'] = $imei;//'869066062080354'; //El IMEI deberá corresponder a una unidad registrada

                            //Pasar el RFC de la empresa previamente configurada
                            $credenciales = JimiGpsTrait::getAuthenticationCredentials($Rfc); 
                          
                            $data = ($credenciales['success']) 
                            ? JimiGpsTrait::callGpsApi('jimi.device.location.get',$credenciales['accessAccount'],$adicionales)
                            : []
                            ;
                          // $ubicacion = $this->detalleDispositivo($imei);
                          $ubicacionApi = collect($data['result'])->first();

                            $ubicacion = [
                                'lat'   => $ubicacionApi['lat'],
                                'lng'  => $ubicacionApi['lng'],
                                'velocidad' => $ubicacionApi['speed'] ?? null,
                                'imei'      => $ubicacionApi['imei'] ?? null,
                                'deviceName'      => $ubicacionApi['deviceName'] ?? null,
                                'mcType'      => $ubicacionApi['mcType'] ?? null,
                                'datac' =>  $data,
                                'esDatoEmp' => $esDatoEmp,
                                'tipoEquipo' => $TipoEquipo,
                                'tipoGPS' => $tipoGpsresponse
                            ];
                           
                           $tipoGpsresponse="jimi";
                            break;

                    case 'wialon': // 'https://alxdevelopments.com': //LegoGps
                            $credenciales = CommonGpsTrait::getAuthenticationCredentials($Rfc,3);
                            $data = ($credenciales['success']) ? LegoGps::getLocation($credenciales['accessAccount']) : [];
                              $ubicacionApi = $data?->data[0] ?? null;

                            $ubicacion = [
                                'lat'   => $ubicacionApi['latitud'] ?? 0,
                                'lng'  => $ubicacionApi['longitud'] ?? 0,
                                'velocidad' => $ubicacionApi['velocidad'] ?? null,
                                'imei'      => $ubicacionApi['imei'] ?? null,
                                'deviceName'      => $ubicacionApi['unidad'] ?? null,
                                'mcType'      => "",
                                'datac' =>  $data,
                                'esDatoEmp' => $esDatoEmp,
                                'tipoEquipo' => $TipoEquipo,
                                'tipoGPS' => $tipoGpsresponse
                            ]; 
                             $tipoGpsresponse="LegoGps";
                        break;
                        case 'TrackerGps':
                            $credenciales = CommonGpsTrait::getAuthenticationCredentials($Rfc,4);
                            $data = GpsTrackerMXTrait::getMutiDevicePosition($credenciales['accessAccount']);
                            break;
                         default:
                             $ubicacion =[
                                'mesage'=>'No encontrado el servicio de GPS',
                                    'lat' => 0,
                                    'lng' => 0,
                                    'fecha' => null,
                                     'datac' =>  null,
                                     'tipoEquipo' => null,
                                     'esDatoEmp' => null,
                                        'tipoGPS' => null
                             ];
                             break;

                            }
                echo "Resultado para IMEI $imeiString: " . json_encode($ubicacion) . "\n";
        
        return $ubicacion;
    }


     function buscartipoProveedor($num_Contenendor,$idKey,$imei){
        //TP-001|865468051839242|5|https://open.iopgps.com
        $datosAll= null;

         $existeContenedor = DB::table('docum_cotizacion')->where('docum_cotizacion.num_contenedor','=',$num_Contenendor)->exists();
        $Equipo = "";
        $TipoEquipo = "";
        if ($existeContenedor){
            //dd($existeContenedor);
            $asignaciones = DB::table('asignaciones')
            ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')
            ->join('gps_company','gps_company.id','=','equipos.gps_company_id')
            ->leftjoin('equipos as eq_chasis', 'eq_chasis.id', '=', 'asignaciones.id_chasis')

            ->select(
                'docum_cotizacion.id as id_contenedor',
                'asignaciones.id',
                'asignaciones.id_camion',
                'docum_cotizacion.num_contenedor',
                'asignaciones.fecha_inicio',
                'asignaciones.fecha_fin',
                
                'equipos.imei', 
                'equipos.id_equipo',
                'equipos.marca',
                'equipos.modelo',
                'equipos.placas',
                
                'gps_company.url_conexion as tipoGps',
                'eq_chasis.imei as imei_chasis',
                'eq_chasis.id_equipo as id_equipo_chasis',
                DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END as beneficiario_id"),
                DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato")
            );

            $beneficiarios = DB::table(function ($query) {
                $query->select('id', 'nombre', 'telefono',DB::raw("'buscarEmpresaRFC' as RFC"), DB::raw("'Propio' as tipo_contrato"), 'id_empresa')
                    ->from('operadores')
                    ->union(
                        DB::table('proveedores')
                            ->select('id', 'nombre', 'telefono','RFC', DB::raw("'Subcontratado' as tipo_contrato"), 'id_empresa')
                    );
            }, 'beneficiarios');

  
            $datosAll = DB::table('cotizaciones')
            ->select(
                'cotizaciones.id as id_cotizacion',
                'asig.id as id_asignacion',
                
                'clients.nombre as cliente',
                'cotizaciones.origen',
                'cotizaciones.destino',
                'asig.num_contenedor as contenedor', 
                'cotizaciones.estatus',
                'asig.imei',
                'asig.id_equipo',
                'asig.id_contenedor',
                'asig.tipo_contrato',
                'asig.fecha_inicio',
                'asig.fecha_fin',
                'asig.tipoGps',
                'asig.imei_chasis',
                'asig.id_equipo_chasis',
                'cotizaciones.id_empresa',
                'beneficiarios.RFC'
            )
            ->join('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
            
            ->joinSub($asignaciones, 'asig', function ($join) {
            $join->on('asig.id_contenedor', '=', 'cotizaciones.id'); 
            })
        
            ->joinSub($beneficiarios, 'beneficiarios', function ($join) {
                $join->on('asig.beneficiario_id', '=', 'beneficiarios.id')
                    ->on('asig.tipo_contrato', '=', 'beneficiarios.tipo_contrato');
            })
            ->whereNotNull('asig.imei') ->where('cotizaciones.estatus', '=', 'Aprobada')
            ->where('asig.num_contenedor', '=', $num_Contenendor)
            ->first();


            if($imei=== $datosAll?->imei){
                //corresponde al equipo del contendor
                $Equipo = $datosAll?->id_equipo;
                $TipoEquipo = 'Camion';
            }
            else if($imei === $datosAll?->imei_chasis){
                //corresponde al equipo del chasis
                $Equipo = $datosAll?->id_equipo_chasis;
                $TipoEquipo = 'Chasis';
            }
            

           // dd($datosAll);
        } else {

            $datosAll = DB::table('equipos')
            ->join('gps_company','gps_company.id','=','equipos.gps_company_id')
            ->join('empresas','empresas.id','=','equipos.id_empresa')
        
            ->select(             
                    'equipos.imei',
                    'equipos.id_equipo',
                
                    'gps_company.url_conexion as tipoGps',
                    
                    'equipos.id_empresa',
                    'empresas.RFC'
                )
            ->where('equipos.id','=',$idKey)->first();
        }
   


         if( $datosAll ){
            $RFCContenedor = $datosAll?->RFC;
          //  $Equipo = $datosAll?->id_equipo;
            $empresaIdRastreo = $datosAll?->id_empresa;
            if( $RFCContenedor==='buscarEmpresaRFC'){
                 $empresaIdRastreo = (int) $empresaIdRastreo;
                //buscamos el rfc de la empresa pues no tiene asignado un proveedor....
                $empresas = Empresas::where('id','=',$empresaIdRastreo)->orderBy('created_at', 'desc')->first();
               // dd($empresas);
                $RFCContenedor =   $empresas->rfc; //minusculas 
                //dd($RFCContenedor);
            }


                return   $RFCContenedor . '|'. $Equipo . '|'.  $empresaIdRastreo .'|'. $TipoEquipo;

            
         }
         
    }
}
