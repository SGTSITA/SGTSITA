<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumCotizacionAcceso;
use App\Models\DocumCotizacion;
use App\Models\Proveedor;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CotizacionAccesoController extends Controller
{
    public function generar(Request $request, $documento_id)
    {
        $eslocal = $request->eslocal;
        $documCotizacion = DocumCotizacion::with('cotizacion')->findorfail($documento_id);

       $validacionProveedor = $this->validateprovempresa($documCotizacion, $eslocal);

if ($validacionProveedor !== true) {
    return $validacionProveedor;
}



        DocumCotizacionAcceso::where('documento_id', $documento_id)
            ->update(['activo' => false]);

        $password = random_int(1000, 9999);
        $filesNames = json_encode($request->input('archivos', []));

        $acceso = DocumCotizacionAcceso::create([
            'documento_id' => $documento_id,
            'token' => Str::random(60),
            'password_hash' => Hash::make($password),
            'expires_at' => now()->addDays(7),
            'user_id' => auth()->user()->id,
            'shared_files' => $filesNames,
            'proveedor_id' => $request->input('proveedor_id'),
            'last_access_at' => Carbon::now(),
            'last_ip' => $request->ip(),
            'user_agent' => $request->userAgent(),
            ]);

        return response()->json([
            'link' => url("/externos/ver-documentos/{$acceso->token}"),
            'password' => $password,
            'message' => 'Enlace generado correctamente.',
            'titulo' => 'link generado',
            'success' => true,
        ]);
    }


     function validateprovempresa(DocumCotizacion $documCotizacion, $eslocal)  {

        $empresaCoti = $documCotizacion->Cotizacion->id_empresa;
        $proveedorIdcoti = $documCotizacion->Cotizacion->id_proveedor;
     $proveedor_local = Proveedor::where('id', $proveedorIdcoti)
    ->where('tipo_viaje', 'local')
    ->exists();





        if(!$eslocal && $empresaCoti && !$proveedorIdcoti ){
            $mesajeadd='';
         /*    if( $proveedor_local){
   $mesajeadd='Proveedor tiene asignado de local';
            } */

            return response()->json([
                        'link' => "",
                        'password' => null,
                        'message' => 'Viaje aun sin asignar Linea de transporte. Edite el viaje foraneo , asigne LT y posteriormente comparta estos documentos'  .  $mesajeadd,
                        'titulo' => 'link no generado',
                        'success' => false,
                    ]);

        }


       return true;



     }
}
