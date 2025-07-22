<?php

namespace App\Http\Controllers;

use App\Models\GpsCompany;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

use App\Models\ServicioGps;

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
        $gpsConfig = GpsCompany::where('id',$r->gps)->with(['serviciosGps' => function($q) use ($empresaId) {
            $q->where('id_empresa', $empresaId);
        }])->first();

       // $account = ($gpsConfig->servicios_gps->first());

        return response()->json(["data"=>$gpsConfig]);
    }

    public function setConfig(Request $r){
        $servicio = ServicioGps::firstOrNew([
            'id_empresa' => auth()->user()->id_empresa,
            'id_gps_company' => $r->gps
        ]);

        $secret = Crypt::encryptString($r->accessKey);

        $account = ["appId" => $r->userName, "accessKey" => $secret];

        $servicio->account_info = json_encode($account);

        $servicio->save();

        return response()->json(["Titulo" => "Correcto", "Mensaje" => "Se guardó la configuración para la compañia GPS", "TMensaje" => "success"]);
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
