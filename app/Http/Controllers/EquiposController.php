<?php

namespace App\Http\Controllers;

use App\Models\GpsCompany;
use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Crypt;

class EquiposController extends Controller
{
    public function index()
    {
        $fechaActual = date('Y-m-d');

        $equipos_dolys = Equipo::where('id_empresa', auth()->user()->id_empresa)
            ->where('tipo', 'Dolys')
            ->where('activo', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $equipos_chasis = Equipo::where('id_empresa', auth()->user()->id_empresa)
            ->where('tipo', 'Chasis / Plataforma')
            ->where('activo', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $equipos_camiones = Equipo::where('id_empresa', auth()->user()->id_empresa)
            ->where('tipo', 'Tractos / Camiones')
            ->where('activo', true)
            ->orderBy('created_at', 'desc')
            ->get();
        $gps_companies  = GpsCompany::all();


        return view('equipos.index', compact(
            'equipos_dolys',
            'equipos_chasis',
            'equipos_camiones',
            'fechaActual',
            'gps_companies'
        ));
    }





    public function store(Request $request)
    {
        $proveedor = new Equipo();

        // === Tractos / Camiones ===
        if ($request->get('marca') != null) {
            $proveedor->tipo = 'Tractos / Camiones';
            $proveedor->folio = $request->get('folio');
            $proveedor->id_equipo = $request->get('id_equipo');
            $proveedor->marca = $request->get('marca');
            $proveedor->motor = $request->get('motor');
            $proveedor->placas = $request->get('placas');
            $proveedor->year = $request->get('year');
            $proveedor->num_serie = $request->get('num_serie');
            $proveedor->modelo = $request->get('modelo');
            $proveedor->acceso = $request->get('acceso');
            $proveedor->fecha = $request->get('fecha');
            $proveedor->activo = true;

            if ($request->hasFile("tarjeta_circulacion")) {
                $file = $request->file('tarjeta_circulacion');
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move(public_path('/equipos'), $fileName);
                $proveedor->tarjeta_circulacion = $fileName;
            }

            if ($request->hasFile("poliza_seguro")) {
                $file = $request->file('poliza_seguro');
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move(public_path('/equipos'), $fileName);
                $proveedor->poliza_seguro = $fileName;
            }
            $proveedor->user_id = auth()->user()->id;
            $proveedor->save();

            // === Chasis / Plataforma ===
        } elseif ($request->get('marca_chasis') != null) {
            $proveedor->tipo = 'Chasis / Plataforma';
            $proveedor->folio = $request->get('folio');
            $proveedor->id_equipo = $request->get('id_equipo_chasis');
            $proveedor->marca = $request->get('marca_chasis');
            $proveedor->motor = $request->get('motor_chasis');
            $proveedor->placas = $request->get('placas_chasis');
            $proveedor->year = $request->get('year_chasis');
            $proveedor->num_serie = $request->get('num_serie_chasis');
            $proveedor->modelo = $request->get('modelo_chasis');
            $proveedor->acceso = $request->get('acceso_chasis');
            $proveedor->fecha = $request->get('fecha_chasis');
            $proveedor->activo = true;

            if ($request->hasFile("tarjeta_circulacion_chasis")) {
                $file = $request->file('tarjeta_circulacion_chasis');
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move(public_path('/equipos'), $fileName);
                $proveedor->tarjeta_circulacion = $fileName;
            }

            if ($request->hasFile("poliza_seguro_chasis")) {
                $file = $request->file('poliza_seguro_chasis');
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move(public_path('/equipos'), $fileName);
                $proveedor->poliza_seguro = $fileName;
            }


            $proveedor->user_id = auth()->user()->id;


            $proveedor->save();

            // === Dolys ===
        } elseif ($request->get('marca_doly') != null) {
            $proveedor->tipo = 'Dolys';
            $proveedor->folio = $request->get('folio');
            $proveedor->id_equipo = $request->get('id_equipo_doly');
            $proveedor->year = $request->get('year_doly');
            $proveedor->marca = $request->get('marca_doly');
            $proveedor->placas = $request->get('placas_doly');
            $proveedor->num_serie = $request->get('num_serie_doly');
            $proveedor->modelo = $request->get('modelo_doly');
            $proveedor->acceso = $request->get('acceso_doly');
            $proveedor->fecha = $request->get('fecha_doly');
            $proveedor->activo = true;

            if ($request->hasFile("tarjeta_circulacion_doly")) {
                $file = $request->file('tarjeta_circulacion_doly');
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move(public_path('/equipos'), $fileName);
                $proveedor->tarjeta_circulacion = $fileName;
            }

            if ($request->hasFile("poliza_seguro_doly")) {
                $file = $request->file('poliza_seguro_doly');
                $fileName = uniqid() . $file->getClientOriginalName();
                $file->move(public_path('/equipos'), $fileName);
                $proveedor->poliza_seguro = $fileName;
            }

            $proveedor->user_id = auth()->user()->id;

            $proveedor->save();
        }

        Session::flash('success', 'Se ha guardado sus datos con éxito');
        return redirect()->back()->with('success', 'Equipo creado con éxito.');
    }


    public function update(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        $data = []; // respaldo

        if ($request->tipo === 'Tractos / Camiones') {
            $data = $request->only([
                'id_equipo', 'fecha', 'year', 'marca', 'modelo', 'placas',
                'num_serie', 'motor', 'acceso'
            ]);
        } elseif ($request->tipo === 'Chasis / Plataforma') {
            $data = $request->only([
                'id_equipo', 'fecha', 'year', 'marca', 'modelo', 'placas',
                'num_serie', 'motor', 'acceso', 'folio'
            ]);
        } elseif ($request->tipo === 'Dolys') {
            $data = $request->only([
                'id_equipo', 'fecha', 'year', 'marca', 'modelo', 'placas',
                'num_serie', 'acceso'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Tipo de equipo no reconocido.'
            ]);
        }

        if ($request->hasFile("tarjeta_circulacion")) {
            $file = $request->file("tarjeta_circulacion");
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move(public_path("/equipos"), $fileName);
            $data['tarjeta_circulacion'] = $fileName;
        }

        if ($request->hasFile("poliza_seguro")) {
            $file = $request->file("poliza_seguro");
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move(public_path("/equipos"), $fileName);
            $data['poliza_seguro'] = $fileName;
        }

        $equipo->update($data);

        return response()->json(['success' => true, 'message' => 'Equipo actualizado con éxito']);
    }


    public function desactivar(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        $tipo = $request->input('tipo');

        if ($tipo === 'desactivado') {
            $equipo->activo = false;
        } elseif ($tipo === 'activado') {
            $equipo->activo = true;
        }

        $equipo->save();

        return response()->json([
            'success' => true,
            'message' => $tipo === 'desactivado' ? 'Equipo desactivado' : 'Equipo reactivado'
        ]);
    }


    //Nuevos controlladores

    public function data()
    {
        $empresa = auth()->user()->id_empresa;
        return response()->json(Equipo::where('id_empresa', $empresa)->get());
    }

    public function asignarGps(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);
        $equipo->gps_company_id = $request->gps_company_id ?: null;
        $equipo->imei = $request->imei ?: null;
        $equipo->save();

        return response()->json([
            'success' => true,
            'message' => 'Asignación de GPS e IMEI actualizada correctamente.'
        ]);
    }


    public function index_gps()
    {

        $userProveedores = User::find(auth()->user()->id);

        $proveedorIds = $userProveedores->proveedores()->pluck('proveedor_id');


        $usuariosRelacionados = User::whereHas('proveedores', function ($q) use ($proveedorIds) {
            $q->whereIn('proveedor_id', $proveedorIds);
        })->pluck('id');

        $equipos = Equipo::whereIn('user_id', $usuariosRelacionados)
        ->leftjoin('gps_company', 'gps_company.id', '=', 'equipos.gps_company_id')
         ->where('equipos.activo', true)
         ->orderBy('equipos.created_at', 'desc')
         ->select([
        'equipos.*',
        'gps_company.nombre as gps_nombre',
        'gps_company.id as gps_company_id_join'
    ])
         ->get();

        $equipos = $equipos->map(function ($e) {

            if ($e->credenciales_gps) {
                try {
                    $e->credenciales_gps = json_decode(
                        Crypt::decryptString($e->credenciales_gps),
                        true
                    );
                } catch (\Exception $ex) {
                    $e->credenciales_gps = null;
                }
            }

            return $e;
        });


        //  dd($equipos);

        $gps_companies = GpsCompany::all();

        return view('equipos.index-gps', compact('equipos', 'gps_companies'));
    }


    public function updateMep(Request $request)
    {
        $mesagep = 'Equipo Actualizado con exito';

        if ($request->equipo_id) {

            $equipo = Equipo::find($request->equipo_id);

            $equipo->update([
                'id_equipo' => $request->numero_equipo,
                'imei' => $request->imei,
                'marca' => $request->marca,
                'placas' => $request->placas,
                'tipo' => $request->tipo_equipo,
                'num_serie' => $request->num_serie,

            ]);

        } else {
            $mesagep = 'Equipo Guardado con exito';
            Equipo::create([
                'id_equipo' => $request->numero_equipo,
                'imei' => $request->imei,
                'marca' => $request->marca,
                'placas' => $request->placas,
                'num_serie' => $request->num_serie,
                'tipo' => $request->tipo_equipo,
                'user_id' => auth()->user()->id,

            ]);
        }

        return response()->json([
            'success' => true,
            'message' => $mesagep
        ]);


    }


}
