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
