<?php

namespace App\Http\Controllers;

use App\Models\GpsCompany;
use Illuminate\Http\Request;

class GpsCompanyController extends Controller
{
    public function index()
    {
        return view('gps.index');
    }

    public function setupGps(){
      //  $companies = GpsCompany::withTrashed()->get();
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
