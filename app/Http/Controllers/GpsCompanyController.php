<?php

namespace App\Http\Controllers;

use App\Models\GpsCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use App\Models\ServicioGps;
use App\Traits\JimiGpsTrait;
use App\Traits\LegoGpsTrait as LegoGps;
use App\Traits\CommonGpsTrait;
use App\Traits\GpsTrackerMXTrait;
use App\Traits\BeyondGPSTrait;
use App\Traits\WialonGpsTrait;

class GpsCompanyController extends Controller
{
    public function index()
    {
        return view('gps.index');
    }

    public function setupGps(){
        $empresaId = auth()->user()->id_empresa;
        $gpsCompanies = GpsCompany::with(['serviciosGps' => function($q) use ($empresaId) {
            $q->where('id_empresa', $empresaId);
        }])->get();
        
        $companies = $gpsCompanies->map(function ($g) {
            $g->estado = $g->serviciosGps->isNotEmpty() ? 'Activo' : 'No contratado';
            return $g;
        });

        return view('gps.setup',["companies" => $companies]);
    }

    //Obtiene la configuración GPS-Proveedor
    function getConfig(Request $r){
        $empresaId = auth()->user()->id_empresa;
       
        $gpsConfig = GpsCompany::leftJoin('servicio_gps_empresa as ge','gps_company.id',"=",'ge.id_gps_company')
                                ->where('gps_company.id',$r->gps)
                                ->get();

       $cuenta = $gpsConfig->where('id_empresa',$empresaId);
       $account = (sizeof($cuenta) > 0) ? json_decode(Crypt::decryptString($gpsConfig[0]->account_info)) : [];
       
       return response()->json(["data"=>$gpsConfig,"account" => ($account)]);
    }

    public function setConfig(Request $r){

        /*
            - Primero validaremos las credenciales del GPS Seleccionado. 
            - Si la conexion al servicio Falla con las credenciales proporcionadas no se Guardaran cambios
        */
        $empresaId = auth()->user()->id_empresa;
        $detailAccount = $r->account;
        $credenciales = [];
        foreach($detailAccount as $a){
            $credenciales[$a['field']] =  $a['valor'];
        }

        

        switch($r->gps){
            case 1:
                break;
            case 2:
                $token = JimiGpsTrait::getGpsAccessToken($empresaId,$credenciales);
                break;
            case 3:
                $token = LegoGpsTrait::validateOwner($credenciales);
                break;
            case 4:
                $token = GpsTrackerMXTrait::getGpsAccessToken($empresaId,$credenciales);
                break;
            case 5:
                $token = BeyondGPSTrait::validateOwner($credenciales);
                break;
            case 6:
                $token = true;
                break;
        }

        if(!$token){
            return response()->json([
                "Titulo" => "Credenciales de acceso incorrectas", 
                "Mensaje" => "No se puede guardar la configuración porque las credenciales de acceso no son correctas", 
                "TMensaje" => "warning"
            ]);
        }

        $servicio = ServicioGps::firstOrNew([
            'id_empresa' => $empresaId,
            'id_gps_company' => $r->gps
        ]);

        $servicio->account_info = Crypt::encryptString(json_encode($r->account));
        $servicio->save();

        return response()->json(["Titulo" => "Correcto", "Mensaje" => "Se guardó la configuración para la compañia GPS", "TMensaje" => "success","token"=>$token]);
    }

    public function data()
    {
        return GpsCompany::withTrashed()->get();
    }


    public function store(Request $request)
    {
        return GpsCompany::create($request->all());
    }

    public function update(Request $request, $id)
    {
        $company = GpsCompany::findOrFail($id);

        // Descarta campos que no deben actualizarse
        $data = $request->except(['_token', '_method', 'id']);

        $company->update($data);

        return response()->json(['success' => true, 'data' => $company]);
    }


    public function destroy($id)
    {
        $company = GpsCompany::findOrFail($id);
        $company->delete();
        return response()->json(['success' => true]);
    }

    public function restore($id)
    {
        $company = GpsCompany::withTrashed()->findOrFail($id);
        $company->restore();

        return response()->json(['success' => true]);
    }

    public function testGpsApi(){
        
        $toTest = 'JimiGps';

        $empresaId = auth()->user()->id_empresa;

        switch($toTest){
            case 'LegoGPS':
                $credenciales = CommonGpsTrait::getAuthenticationCredentials('AECC890930E41',3);
                $data = ($credenciales['success']) ? LegoGps::getLocation($credenciales['accessAccount']) : [];
                break;
            case 'JimiGps':
                 //Datos de dispositivo por IMEI
                $adicionales['imeis'] = '356153592336785'; //869066061506169 y  356153592336785
                $credenciales = JimiGpsTrait::getAuthenticationCredentials('XAXA890930E41');

                $data = JimiGpsTrait::callGpsApi('jimi.device.location.get',$credenciales['accessAccount'],$adicionales);
                break;
            case 'TrackerGps':
                $credenciales = CommonGpsTrait::getAuthenticationCredentials('AECC890930E41',4);
                $data = GpsTrackerMXTrait::getMutiDevicePosition($credenciales['accessAccount']);
                break;
            case 'BeyondGps':
                $credenciales = CommonGpsTrait::getAuthenticationCredentials('AECC890930E41',5);
                $data = BeyondGPSTrait::getLocation($credenciales['accessAccount']['user'],$credenciales['accessAccount']['password']);
                break;
            case 'WialonGps':
                $credenciales = CommonGpsTrait::getAuthenticationCredentials('AECC890930E41',6);
             //    return $credenciales;
                $data = WialonGpsTrait::getLocation($credenciales['accessAccount']['token']);
                break;                
            default:
            $data = "Bad GPS Config";
        }
        
        return $data;
    }
    
}
