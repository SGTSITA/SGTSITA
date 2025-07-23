<?php

namespace App\Http\Controllers;

use App\Models\GpsCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use App\Models\ServicioGps;
use App\Traits\JimiGpsTrait;

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
        $servicio = ServicioGps::firstOrNew([
            'id_empresa' => auth()->user()->id_empresa,
            'id_gps_company' => $r->gps
        ]);

        $empresaId = auth()->user()->id_empresa;
        $detailAccount = $r->account;
        $credenciales = [];
        foreach($detailAccount as $a){
            $credenciales[$a['field']] =  $a['valor'];
        }

        $token = JimiGpsTrait::getGpsAccessToken($empresaId,$credenciales);

        if(!$token){
        return response()->json([
            "Titulo" => "Credenciales de acceso incorrectas", 
            "Mensaje" => "No se puede guardar la configuración porque las credenciales de acceso no son correctas", 
            "TMensaje" => "warning"
        ]);

        }

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
    
}
