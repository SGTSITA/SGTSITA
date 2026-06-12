<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\DocumCotizacionAcceso;
use App\Models\DocumCotizacion;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class CotizacionAccesoController extends Controller
{
    public function generar(Request $request, $documento_id)
    {

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
            'success' => true,
        ]);
    }
}
