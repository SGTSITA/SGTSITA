<?php

namespace App\Http\Controllers;

use App\Models\Asignaciones;
use App\Models\Bancos;
use App\Models\BancoDinero;
use App\Models\Cotizaciones;
use App\Models\Equipo;
use App\Models\Operador;
use App\Models\Planeacion;
use App\Models\Proveedor;
use App\Models\Client;
use App\Models\User;
use App\Models\Coordenadas;
use App\Models\GastosOperadores;
use App\Models\BancoDineroOpe;
use App\Models\ViaticosOperador;
use App\Models\DocumCotizacion;
use App\Models\DineroContenedor;
use App\Traits\CommonTrait as common;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class PlaneacionController extends Controller
{
    public function index()
    {
        return view('planeacion.index');
    }

    public function programarViaje()
    {
        $proveedores = Proveedor::catalogoPrincipal()->where('id_empresa', '=', auth()->user()->id_empresa)
        ->wherein('tipo', ['servicio de burreo', 'servicio de viaje'])
        ->get();

        $equipos = Equipo::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $operadores = Operador::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $bancos = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->where('saldo', '>', '0')->get();

        return view('planeacion.planeacion-step', compact('equipos', 'operadores', 'proveedores', 'bancos'));
    }

    public function anularPlaneacion(Request $request)
    {
        try {

            DB::beginTransaction();
            $cotizaciones = Cotizaciones::find($request->idCotizacion);
            $documenCotizacion = DocumCotizacion::where('id_cotizacion', $request->idCotizacion)->first(); //primero buscamos el id contenedor
            $asignaciones = Asignaciones::where('id_contenedor', '=', $documenCotizacion->id)->first(); //corregir mandar id contenenedor, y no cotizacion???

            if (!is_null($asignaciones->id_operador) && !is_null($asignaciones->id_banco1_dinero_viaje)) {


                Bancos::where('id', '=', $asignaciones->id_banco1_dinero_viaje)->update(["saldo" => DB::raw("saldo + ". $asignaciones->dinero_viaje)]);

                $banco = new BancoDineroOpe();
                $banco->id_operador = $asignaciones->id_operador;

                $banco->monto1 = $asignaciones->dinero_viaje;
                $banco->metodo_pago1 = 'Devolución';
                $banco->descripcion_gasto = "Dinero para Viaje (Devolución)";
                $banco->id_banco1 = $asignaciones->id_banco1_dinero_viaje;

                $contenedoresAbonos[] = [
                    'num_contenedor' => $request->numContenedor,
                    'abono' => $asignaciones->dinero_viaje
                ];
                $contenedoresAbonosJson = json_encode($contenedoresAbonos);

                $banco->contenedores = $contenedoresAbonosJson;

                $banco->tipo = 'Entrada';
                $banco->fecha_pago = date('Y-m-d');
                $banco->save();



            }

            if (!is_null($cotizaciones->referencia_full)) {
                $contenedor2 = Cotizaciones::where('referencia_full', $cotizaciones->referencia_full)->update(["estatus_planeacion" => 0]);
            }

            $cotizaciones->estatus = 'Aprobada';
            $cotizaciones->estatus_planeacion = 0;
            $cotizaciones->update();

            //validar si ay gastos operador y eliminarlos y si hay pagados hacer devolucion de banco

            $gastosOperador = GastosOperadores::where('id_asignacion', $asignaciones->id)
            ->where('estatus', 'Pagado')
            ->where('id_banco', '!=', null)

            ->get();

            //recorrer los gastos pagados para hacer devolucion
            foreach ($gastosOperador as $gasto) {
                Bancos::where('id', '=', $gasto->id_banco)->update(["saldo" => DB::raw("saldo + ". $gasto->cantidad)]);

                $banco = new BancoDineroOpe();
                $banco->id_operador = $asignaciones->id_operador;

                $banco->monto1 = $gasto->cantidad;
                $banco->metodo_pago1 = 'Transferencia';
                $banco->descripcion_gasto = "Gasto Anulado:  ".$gasto->concepto;
                $banco->id_banco1 = $gasto->id_banco;

                $contenedoresAbonos2[] = [
                    'num_contenedor' => $request->numContenedor,
                    'abono' => $gasto->cantidad
                ];
                $contenedoresAbonosJson2 = json_encode($contenedoresAbonos2);

                $banco->contenedores = $contenedoresAbonosJson2;

                $banco->tipo = 'Entrada';
                $banco->fecha_pago = date('Y-m-d');
                $banco->save();


            }




            GastosOperadores::where('id_asignacion', $asignaciones->id)->delete();



            Coordenadas::where('id_asignacion', $asignaciones->id)->delete();
            $asignaciones->delete();

            DineroContenedor::where('id_contenedor', $documenCotizacion->id)->delete();


            ViaticosOperador::where('id_cotizacion', $cotizaciones->id)->delete();




            DB::commit();

            return response()->json(["Titulo" => "Programa cancelado","Mensaje" => "Se canceló el programa del viaje correctamente", "TMensaje" => "success"]);
        } catch (\Throwable $t) {
            DB::rollback();
            return response()->json(["Titulo" => "Error","Mensaje" => "Error 500: ".$t->getMessage(), "TMensaje" => "error"]);

        }


    }

    public function finalizarViaje(Request $request)
    {
        $cotizaciones = Cotizaciones::find($request->idCotizacion);
        $cotizaciones->estatus = 'Finalizado';
        $cotizaciones->update();
        return response()->json(["Titulo" => "Viaje finalizado","Mensaje" => "Has finalizado correctamente el viaje", "TMensaje" => "success"]);
    }

    public function infoViaje(Request $request)
    {
        $asignaciones = Asignaciones::where('id_contenedor', '=', $request->id)->first();
        $cotizacion = Cotizaciones::where('id', '=', $request->id)->first();

        $documentos = Cotizaciones::query()
        ->where('cotizaciones.id', $request->id)
        ->leftJoin('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->leftJoin('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        ->leftJoin('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
        ->leftjoin('equipos', 'asignaciones.id_camion', '=', 'equipos.id')
        ->leftjoin('equipos as chasis', 'asignaciones.id_chasis', '=', 'chasis.id')
        ->select(
            'cotizaciones.id',
            'clients.nombre as cliente',
            'docum_cotizacion.num_contenedor',
            'docum_cotizacion.doc_ccp',
            'docum_cotizacion.cima',
            'docum_cotizacion.boleta_liberacion',
            'docum_cotizacion.doda',
            'cotizaciones.referencia_full',
            'cotizaciones.carta_porte',
            'cotizaciones.carta_porte_xml',
            'cotizaciones.img_boleta AS boleta_vacio',
            'docum_cotizacion.doc_eir',
            'asignaciones.id_proveedor',
            'asignaciones.fecha_inicio',
            'asignaciones.fecha_fin',
            'equipos.placas as placas_camion',
            'equipos.id_equipo as id_equipo_camion',
            'equipos.marca as marca_camion',
            'equipos.imei as imei_camion',
            'chasis.id_equipo as id_equipo_chasis',
            'chasis.imei as imei_chasis',
            DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END as beneficiario_id"),
            DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato")
        )
        ->get();



        $documentos = Cotizaciones::query()
        ->where('cotizaciones.id', $request->id)
        ->leftJoin('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
        ->leftJoin('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
        ->leftJoin('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
        ->leftjoin('equipos', 'asignaciones.id_camion', '=', 'equipos.id')
        ->leftjoin('equipos as chasis', 'asignaciones.id_chasis', '=', 'chasis.id')
        ->select(
            'cotizaciones.id',
            'clients.nombre as cliente',
            'docum_cotizacion.num_contenedor',
            'docum_cotizacion.doc_ccp',
            'docum_cotizacion.cima',
            'docum_cotizacion.boleta_liberacion',
            'docum_cotizacion.doda',
            'cotizaciones.referencia_full',
            'cotizaciones.carta_porte',
            'cotizaciones.carta_porte_xml',
            'cotizaciones.img_boleta AS boleta_vacio',
            'docum_cotizacion.doc_eir',
            'asignaciones.id_proveedor',
            'asignaciones.fecha_inicio',
            'asignaciones.fecha_fin',
            'equipos.placas as placas_camion',
            'equipos.id_equipo as id_equipo_camion',
            'equipos.marca as marca_camion',
            'equipos.imei as imei_camion',
            'chasis.id_equipo as id_equipo_chasis',
            'chasis.imei as imei_chasis',
            DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END as beneficiario_id"),
            DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato")
        )
        ->get();



        $beneficiariosSubquery = DB::table(function ($query) {
            $query->select(
                'operadores.id',
                'operadores.nombre',
                'operadores.telefono',
                'empresas.rfc as RFC',
                DB::raw("'Propio' as tipo_contrato"),
                'operadores.id_empresa',
                'empresas.nombre as nombreempresa'
            )
            ->from('operadores')
            ->join('empresas', 'empresas.id', '=', 'operadores.id_empresa')

            ->union(
                DB::table('proveedores')
                    ->select(
                        'proveedores.id',
                        'proveedores.nombre',
                        'proveedores.telefono',
                        'proveedores.RFC',
                        DB::raw("'Subcontratado' as tipo_contrato"),
                        'proveedores.id_empresa',
                        'empresas.nombre as nombreempresa'
                    )
                    ->join('empresas', 'empresas.id', '=', 'proveedores.id_empresa')
            );
        });

        $InfoViajeExtra = Cotizaciones::query()
            ->where('cotizaciones.id', $request->id)
            ->leftJoin('docum_cotizacion', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')
            ->leftJoin('asignaciones', 'docum_cotizacion.id', '=', 'asignaciones.id_contenedor')
            ->leftJoin('clients', 'cotizaciones.id_cliente', '=', 'clients.id')
            ->leftJoin('equipos', 'asignaciones.id_camion', '=', 'equipos.id')
            ->leftJoin('equipos as chasis', 'asignaciones.id_chasis', '=', 'chasis.id')


            ->leftJoinSub(
                $beneficiariosSubquery,
                'beneficiarios',
                function ($join) {
                    $join->on(
                        DB::raw("beneficiarios.id"),
                        '=',
                        DB::raw("(CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END)")
                    )
                    ->whereRaw("beneficiarios.tipo_contrato = CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END");
                }
            )

            ->select(
                'cotizaciones.id',
                'clients.nombre as cliente',
                'docum_cotizacion.num_contenedor',
                'cotizaciones.referencia_full',
                'cotizaciones.cp_contacto_entrega',
                'asignaciones.id_proveedor',
                'asignaciones.fecha_inicio',
                'asignaciones.fecha_fin',
                'equipos.placas as placas_camion',
                'equipos.id_equipo as id_equipo_camion',
                'equipos.marca as marca_camion',
                'equipos.imei as imei_camion',
                'chasis.id_equipo as id_equipo_chasis',
                'chasis.imei as imei_chasis',
                DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN asignaciones.id_operador ELSE asignaciones.id_proveedor END as beneficiario_id"),
                DB::raw("CASE WHEN asignaciones.id_proveedor IS NULL THEN 'Propio' ELSE 'Subcontratado' END as tipo_contrato"),
                'beneficiarios.nombre as beneficiario_nombre',
                'beneficiarios.telefono as beneficiario_telefono',
                'beneficiarios.RFC as beneficiario_rfc',
                'beneficiarios.nombreempresa as empresa_beneficiario'
            )
            ->get();



        $misDocumentos =
        $documentos->map(function ($cot) {
            $numContenedor = $cot->num_contenedor;
            $docCCP = $cot->doc_ccp;
            $doda = $cot->doda;
            $boletaLiberacion = $cot->boleta_liberacion;
            $cartaPorte = $cot->carta_porte;
            $cartaPorteXml = $cot->carta_porte_xml;
            $boletaVacio = $cot->boleta_vacio;
            $docEir = $cot->doc_eir;
            $tipo = "--";

            if (!is_null($cot->referencia_full)) {
                $secundaria = Cotizaciones::where('referencia_full', $cot->referencia_full)
                ->where('jerarquia', 'Secundario')
                ->with('DocCotizacion.Asignaciones')
                ->first();

                $docCCP = ($docCCP && $secundaria->DocCotizacion->doc_ccp) ? true : false;
                $doda = ($doda && $secundaria->DocCotizacion->doda) ? true : false;
                $docEir = (!is_null($docEir) && !is_null($secundaria->DocCotizacion->doc_eir)) ? true : false;

                $boletaLiberacion = ($boletaLiberacion && $secundaria->DocCotizacion->boleta_liberacion) ? true : false;
                $cartaPorte = ($cartaPorte && $secundaria->carta_porte) ? true : false;
                $cartaPorteXml = ($cartaPorteXml && $secundaria->carta_porte_xml) ? true : false;

                $boletaVacio = ($boletaVacio && $secundaria->img_boleta) ? true : false;


                if ($secundaria && $secundaria->DocCotizacion) {
                    $numContenedor .= ' / ' . $secundaria->DocCotizacion->num_contenedor;
                }
                $tipo = "Full";
            }

            return [
                "id" => $cot->id,
                "cliente" => $cot->cliente,
                "num_contenedor" => $numContenedor,
                "doc_ccp" => $docCCP,
                "boleta_liberacion" => $boletaLiberacion,
                "doda" => $doda,
                "cima" => $cot->cima,
                "carta_porte" => $cartaPorte,
                "carta_porte_xml" => $cartaPorteXml,
                "boleta_vacio" => $boletaVacio,
                "doc_eir" => $docEir,
                "id_proveedor" => $cot->id_proveedor,
                "fecha_inicio" => $cot->fecha_inicio,
                "fecha_fin" => $cot->fecha_fin,
                "tipo" => $tipo
            ];
        });




        if ($asignaciones->Proveedor == null) {
            return [
                        "nombre" => $asignaciones->Operador->nombre ?? '',
                        "tipo" => "Viaje Propio",
                        "cotizacion" => $cotizacion,
                        "cliente" => $cotizacion->Cliente,
                        "subcliente" => $cotizacion->Subcliente,
                        "documentos" => $documentos->first(),
                        "documents" => $misDocumentos->first(),
                        "datosExtraviaje" => $InfoViajeExtra->first()
                    ];
        }

        return [
                    "nombre" => $asignaciones->Proveedor->nombre,
                    "tipo" => "Viaje subcontratado",
                    "cotizacion" => $cotizacion,
                    "cliente" => $cotizacion->Cliente,
                    "subcliente" => $cotizacion->Subcliente,
                    "documentos" => $documentos->first(),
                    "documents" => $misDocumentos->first(),
                    "datosExtraviaje" => $InfoViajeExtra->first()
                ];

    }

    public function initBoard(Request $request)
    {

        $userProveedores = User::find(auth()->user()->id);


        // if ($userProveedores->proveedores()->exists()) {
        //     $cotizacionesQuery->whereIn(
        //         'id_proveedor',
        //         $userProveedores->proveedores()->pluck('proveedor_id')
        //     );
        // }
        $proveedorIds = $userProveedores->proveedores()->pluck('proveedor_id');


        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
                        ->join('cotizaciones', 'docum_cotizacion.id_cotizacion', '=', 'cotizaciones.id')
                        ->where('asignaciones.fecha_inicio', '>=', $request->fromDate)
                        ->where('asignaciones.id_empresa', '=', auth()->user()->id_empresa)
                        ->where('cotizaciones.estatus', 'Aprobada')
                        ->where('estatus_planeacion', '=', 1)
                         ->when($userProveedores->proveedores()->exists(), function ($query) use ($proveedorIds) {
                             $query->whereIn('cotizaciones.id_proveedor', $proveedorIds);
                         })
                        ->select('asignaciones.*', 'docum_cotizacion.num_contenedor', 'cotizaciones.id_cliente', 'cotizaciones.referencia_full', 'cotizaciones.tipo_viaje')
                        ->orderBy('fecha_inicio')
                        ->get();

        $extractor = $planeaciones->map(function ($p) {
            $itemNumContenedor = $p->num_contenedor;
            if (!is_null($p->referencia_full)) {
                $cotizacionFull = Cotizaciones::where('referencia_full', $p->referencia_full)->where('jerarquia', 'Secundario')->first();
                $contenedorSecundario = DocumCotizacion::where("id_cotizacion", $cotizacionFull->id)->first();
                $itemNumContenedor .= " / ".$contenedorSecundario->num_contenedor;
            }
            return [
                    "fecha_inicio" => $p->fecha_inicio,
                    "fecha_fin" => $p->fecha_fin,
                    "id_contenedor" => $p->id_contenedor,
                    "id_cliente" => $p->id_cliente,
                    "num_contenedor" => $itemNumContenedor
                   ];
        });

        $clientes = $planeaciones->unique('id_cliente')->pluck('id_cliente');
        $clientesData = Client::whereIn('id', $clientes)->selectRaw('id, nombre as name, '."'true'".' as expanded')->get();

        $board = [];
        $board[] = ["name" => "Clientes", "id" => "S", "expanded" => true, "children" => $clientesData];

        $fecha = Carbon::now()->subdays(10)->format('Y-m-d');
        return response()->json(["boardCentros" => $board,"extractor" => $extractor,"scrollDate" => $fecha, "TMensaje" => "success","planeaciones" => $planeaciones]);
    }



    public function asignacion(Request $request)
    {

        $validadarSaldos = 'SI';
        $CantineroViaje = $request->filled('txtDineroViaje') ? $request->get('txtDineroViaje') : 0;
        $idEmpresa = auth()->user()->id_empresa;
        //validar si el banco tiene dinero antes de todo
        if ($CantineroViaje > 0) {

            $bancovalidar = Bancos::where('id', '=', $request->get('cmbBanco'))->where('id_empresa', '=', $idEmpresa)->first();
            if ($bancovalidar->saldo <  $CantineroViaje) {
                $validadarSaldos = 'NO';
                return response()->json([
                  "TMensaje" => "error",
                  "Titulo" => "Saldo bancos",
                  "Mensaje" => "No se puede realizar la programacion de este viaje, el saldo del banco no es suficiente",
                  'success' => false,
                  'cotizacion_data' => []
                ]);


                //  dd($bancovalidar, $validadarSaldos);
            }
            // dd($bancovalidar, $validadarSaldos.$bancovalidar->saldo, $CantineroViaje);
        }


        //validacion saldo otros gastos


        $otrosGastos = json_decode($request->filasOtrosGastos, true);

        // dd($otrosGastos);
        if ($otrosGastos && is_array($otrosGastos)) {
            $validadarSaldos = 'SI';
            $montosPorBanco = [];


            foreach ($otrosGastos as $gasto) {

                $monto = floatval($gasto['monto'] ?? 0);
                $esPagoInmediato = !empty($gasto['pagoInmediato']);
                $idBanco = !empty($gasto['banco']) ? intval($gasto['banco']) : null;


                //dd($gasto, $validadarSaldos);
                if ($esPagoInmediato && $idBanco) {

                    // Acumular monto por banco
                    if (!isset($montosPorBanco[$idBanco])) {
                        $montosPorBanco[$idBanco] = 0;
                    }

                    $montosPorBanco[$idBanco] += $monto;
                }
                //  dd($idBanco, $esPagoInmediato, $monto);
            }
            //  dd($montosPorBanco);
            // 🔎 Ahora validar saldos reales
            foreach ($montosPorBanco as $idBanco => $totalMonto) {

                $banco = Bancos::where('id_empresa', $idEmpresa)
                               ->where('id', $idBanco)
                               ->first();


                if (!$banco) {
                    $validadarSaldos = 'NO';
                    return response()->json([
                        "Titulo" => "Banco no encontrado",
                        "Mensaje" => "El banco seleccionado no existe o no pertenece a la empresa.",
                        "TMensaje" => "error",
                         'success' => false,
                    ]);

                }
                //  dd($banco, $validadarSaldos);


                if ($banco->saldo < $totalMonto) {
                    $validadarSaldos = 'NO';
                    return response()->json([
                        "Titulo" => "Saldo insuficiente",
                        "Mensaje" => "El banco {$banco->nombre} no cuenta con saldo suficiente para cubrir el total de {$totalMonto}.",
                        "TMensaje" => "error",
                         'success' => false,
                    ]);
                    break;
                }
            }


        }


        //finaliza y sigue si ay dinero
        if ($validadarSaldos === 'SI') {
            $cotizacion_data = [];
            // dd($validadarSaldos);
            $numContenedores = json_decode($request->get('num_contenedor'));
            $numContenedor = $numContenedores[0];
            // $numContenedor = ($request->cmbTipoUnidad == "Full") ? substr($numContenedor,0,12) : $numContenedor;

            $fechaInicio = common::TransformaFecha($request->txtFechaInicio);
            $fechaFinal = common::TransformaFecha($request->txtFechaFinal);

            $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)->first();
            $cotizacion = Cotizaciones::where('id', '=', $contenedor->id_cotizacion)->first();

            try {

                DB::beginTransaction();
                $asignaciones = new Asignaciones();

                $asignaciones->id_contenedor = $contenedor->id_cotizacion;
                $asignaciones->fecha_inicio = $fechaInicio;
                $asignaciones->fecha_fin = $fechaFinal . ' 23:00:00';
                $asignaciones->fehca_inicio_guard = $fechaInicio;
                $asignaciones->fehca_fin_guard = $fechaFinal . ' 23:00:00';

                $asignaciones->save();

                $viajePropio = 0;

                if ($request->tipoViaje == "propio") {
                    $asignaciones->id_chasis = $request->get('cmbChasis');
                    $asignaciones->id_chasis2 = $request->get('cmbChasis2');
                    $asignaciones->id_dolys = $request->get('cmbDoly');
                    $asignaciones->id_camion = $request->get('cmbCamion');
                    $asignaciones->id_operador = $request->get('cmbOperador');
                    $asignaciones->sueldo_viaje = $request->get('txtSueldoOperador');
                    $asignaciones->estatus_pagado = 'Pendiente Pago';
                    $asignaciones->dinero_viaje = $request->get('txtDineroViaje');

                    $asignaciones->id_banco1_dinero_viaje = $request->get('cmbBanco');
                    $asignaciones->cantidad_banco1_dinero_viaje = $request->get('txtDineroViaje');

                    $sueldoOperador = $request->filled('txtSueldoOperador') ? $request->get('txtSueldoOperador') : 0;


                    $resta = $sueldoOperador - $CantineroViaje;
                    $asignaciones->pago_operador = $resta;
                    $asignaciones->restante_pago_operador = $resta;
                    $asignaciones->tipo_contrato = 'Propio';


                    if (is_null($request->get('cmbProveedor')) && $CantineroViaje > 0) { //Agregue para validar el proveedor con sgt elemental no tiene porceso de pagos

                        $contenedoresAbonos = [];
                        $contenedorAbono = [
                            'num_contenedor' => $contenedor->num_contenedor,
                            'abono' =>  $CantineroViaje
                        ];

                        array_push($contenedoresAbonos, $contenedorAbono);

                        Bancos::where('id', '=', $request->get('cmbBanco'))->update(["saldo" => DB::raw("saldo - ". $CantineroViaje)]);
                        BancoDineroOpe::insert([[
                                                'id_operador' => $request->get('cmbOperador'),
                                                'id_banco1' => $request->get('cmbBanco'),
                                                'monto1' => $CantineroViaje,
                                                'fecha_pago' => date('Y-m-d'),
                                                'tipo' => 'Salida',
                                                'id_empresa' => auth()->user()->id_empresa,
                                                'contenedores' => json_encode($contenedoresAbonos),
                                                'descripcion_gasto' => 'Dinero para viaje'
                                            ]]);

                        $dineroViaje = new DineroContenedor();
                        $dineroViaje->id_contenedor = $asignaciones->id_contenedor;
                        $dineroViaje->id_banco = $request->get('cmbBanco');
                        $dineroViaje->motivo = 'Dinero para viaje';
                        $dineroViaje->monto =  $CantineroViaje;
                        $dineroViaje->fecha_entrega_monto = date('Y-m-d');
                        $dineroViaje->save();




                    }


                    $viajePropio = 1;

                } else {

                    $asignaciones->id_proveedor = $request->get('cmbProveedor');
                    $asignaciones->precio = $request->get('precio_proveedor');
                    $asignaciones->burreo = $request->get('burreo_proveedor');
                    $asignaciones->maniobra = $request->get('maniobra_proveedor');
                    $asignaciones->estadia = $request->get('estadia_proveedor');
                    $asignaciones->otro = $request->get('otro_proveedor');
                    $asignaciones->iva = $request->get('iva_proveedor');
                    $asignaciones->retencion = $request->get('retencion_proveedor');
                    $asignaciones->total_proveedor = $request->get('total_proveedor');
                    $asignaciones->sobrepeso_proveedor = $request->get('sobrepeso_proveedor');
                    $asignaciones->base1_proveedor = $request->get('base_factura');
                    $asignaciones->base2_proveedor = $request->get('base_taref');
                    $asignaciones->total_tonelada = round(floatVal($request->get('sobrepeso_proveedor')) * floatVal($request->get('cantidad_sobrepeso_proveedor')), 4);
                    $cotizacion->prove_restante = $asignaciones->total_proveedor;
                    $asignaciones->tipo_contrato = 'Subcontratado';
                }

                /*


                 $asignaciones->id_banco2_dinero_viaje = $request->get('id_banco2_dinero_viaje');
                 $asignaciones->cantidad_banco2_dinero_viaje = $request->get('cantidad_banco2_dinero_viaje');


                 */


                $asignaciones->update();

                $cotizacion->estatus_planeacion = 1;
                $cotizacion->tipo_viaje = $request->get('cmbTipoUnidad');
                $cotizacion->update();



                $cotizacion_data = [
                    "tipo_viaje" => $cotizacion->tipo_viaje,
                ];

                if (sizeof($numContenedores) == 2) {
                    $fullUUID = Common::generarUuidV4();
                    foreach ($numContenedores as $i => $cont) {
                        $contenedor = DocumCotizacion::where('num_contenedor', $cont)->first();
                        $cotizacion = Cotizaciones::where('id', '=', $contenedor->id_cotizacion)->first();
                        $cotizacion->referencia_full = $fullUUID;
                        $cotizacion->jerarquia = ($i == 0) ? 'Principal' : 'Secundario';
                        //  Log::debug("index: $i contenedor: $cont jerarquia: $cotizacion->jerarquia");
                        $cotizacion->estatus_planeacion = 1;
                        $cotizacion->tipo_viaje = 'Full';
                        $cotizacion->update();

                    }
                }


                DB::commit();

                //se envia aki los nuevos parametros los gastos despues de actualizar los datos de asignacion
                if ($viajePropio) {
                    //nuevos cambios en form planeacion propio
                    if ($request->filled('filasOtrosGastos')) {
                        log::info('Guardando otros gastos de planeacion propio...');

                        $resultado = self::guardarOtrosGastosPlaneacion($request, $contenedor->num_contenedor, $request->get('cmbOperador'));

                    }
                }

                return response()->json([
                    "TMensaje" => "success",
                    "Titulo" => "Planeado correctamente",
                    "Mensaje" => "Se ha programado correctamente el viaje del contenedor",
                    'success' => true,
                    'cotizacion_data' => $cotizacion_data
                ]);

            } catch (\Throwable $t) {
                DB::rollback();
                Log::channel('daily')->info('No se guardó planeacion: '.$t->getMessage());
                return response()->json(["TMensaje" => "warning","Titulo" => "No se pudo planear", "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud",'success' => true, 'cotizacion_data' => $cotizacion_data,'ERROR-ADMIN' => $t->getMessage()]);

            }



        }




    }

    public function reprogramarViajes(Request $request)
    {
        try {
            DB::beginTransaction();
            $viajes = json_decode($request->ajustes);
            foreach ($viajes as $v) {
                $asignaciones = Asignaciones::where('id_contenedor', $v->id)->first();
                //$asignaciones->id_contenedor = $contenedor->id_cotizacion;
                $fechaInicio = str_replace('T', ' ', $v->start);
                $fechaFinal = str_replace('T', ' ', $v->end);
                $asignaciones->fecha_inicio = $fechaInicio;
                $asignaciones->fecha_fin = $fechaFinal ;
                $asignaciones->fehca_inicio_guard = $fechaInicio;
                $asignaciones->fehca_fin_guard = $fechaFinal ;
                $asignaciones->save();
            }

            DB::commit();

            return response()->json(["TMensaje" => "success","Titulo" => "Reprogramación exitosa", "Mensaje" => "Se ha realizado la reprogramación de fechas exitosamente",'success' => true]);

        } catch (\Throwable $t) {
            DB::rollback();
            Log::channel('daily')->info('No se guardó Reprogramacion viajes: '.$t->getMessage());
            return response()->json(["TMensaje" => "warning","Titulo" => "No se pudo reprogramar", "Mensaje" => "Ocurrio un error mientras procesabamos su solicitud",'success' => true]);
        }
    }

    public function equipos(Request $request)
    {
        $fechaInicio = $request->fecha_inicio;
        $fechaFin = $request->fecha_fin;

        if ($fechaInicio  &&  $fechaFin) {
            $camionesAsignados = Asignaciones::where('id_empresa', '=', auth()->user()->id_empresa)
            ->whereNotNull('id_camion')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio', '<=', $fechaFin)
                      ->where('fecha_fin', '>=', $fechaInicio);
            })
            ->pluck('id_camion');

            $camionesNoAsignados = Equipo::where('id_empresa', '=', auth()->user()->id_empresa)
            ->where('tipo', 'LIKE', '%Camiones%')
            ->whereNotIn('id', $camionesAsignados)
            ->orWhereNotIn('id', function ($query) {
                $query->select('id_camion')->from('asignaciones')->whereNull('id_camion');
            })
            ->get();

            $chasisAsignados = Asignaciones::where('id_empresa', '=', auth()->user()->id_empresa)
            ->whereNotNull('id_chasis')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio', '<=', $fechaFin)
                      ->where('fecha_fin', '>=', $fechaInicio);
            })
            ->pluck('id_chasis');

            $chasisNoAsignados = Equipo::where('id_empresa', '=', auth()->user()->id_empresa)
            ->where('tipo', 'LIKE', '%Chasis%')
                ->whereNotIn('id', $chasisAsignados)
                ->orWhereNotIn('id', function ($query) {
                    $query->select('id_chasis')->from('asignaciones')->whereNull('id_chasis');
                })
                ->get();

            $dolysAsignados = Asignaciones::where('id_empresa', '=', auth()->user()->id_empresa)
            ->whereNotNull('id_camion')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio', '<=', $fechaFin)
                        ->where('fecha_fin', '>=', $fechaInicio);
            })
            ->pluck('id_camion');

            $dolysNoAsignados = Equipo::where('id_empresa', '=', auth()->user()->id_empresa)
            ->where('tipo', 'LIKE', '%Dolys%')
                ->whereNotIn('id', $dolysAsignados)
                ->orWhereNotIn('id', function ($query) {
                    $query->select('id_camion')->from('asignaciones')->whereNull('id_camion');
                })
                ->get();

            $operadorAsignados = Asignaciones::where('id_empresa', '=', auth()->user()->id_empresa)
            ->whereNotNull('id_operador')
            ->where(function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('fecha_inicio', '<=', $fechaFin)
                        ->where('fecha_fin', '>=', $fechaInicio);
            })
            ->pluck('id_operador');

            $operadorNoAsignados = Operador::where('id_empresa', '=', auth()->user()->id_empresa)
            ->whereNotIn('id', $operadorAsignados)
                ->orWhereNotIn('id', function ($query) {
                    $query->select('id_operador')->from('asignaciones')->whereNull('id_operador');
                })
                ->get();


            $bancos = Bancos::where('id_empresa', '=', auth()->user()->id_empresa)->where('saldo', '>', '0')->get();

            return view('planeacion.resultado_equipos', ['bancos' => $bancos, 'dolysNoAsignados' => $dolysNoAsignados, 'camionesNoAsignados' => $camionesNoAsignados, 'chasisNoAsignados' => $chasisNoAsignados, 'operadorNoAsignados' => $operadorNoAsignados]);

        }
    }

    public static function guardarOtrosGastosPlaneacion($r, $num_Contenedor, $idOperadorViaje)
    {
        DB::beginTransaction();
        $respuesta = null;

        try {
            $otrosGastos = json_decode($r->filasOtrosGastos, true);
            Log::info('Otros gastos recibidos:', $otrosGastos);

            if (empty($otrosGastos) || !is_array($otrosGastos)) {
                return [
                    "Titulo" => "Sin datos",
                    "Mensaje" => "No se recibieron gastos válidos.",
                    "TMensaje" => "warning"
                ];
            }

            $datosGasto = [];
            $bancoDinero = [];
            $nuevosGastos = [];
            $idEmpresa = auth()->user()->id_empresa;

            //  Solo se permiten estos tipos peticion don  jose
            $descripcionGastosPermitidos = [
                'GCM01' => 'GCM01 - Comisión',
                'GDI02' => 'GDI02 - Diesel',
                'GBV01' => 'GBV01 - Burrero Vacio'
            ];

            foreach ($otrosGastos as $gasto) {
                Log::info('Procesando gasto:', $gasto);

                $motivo = $gasto['motivo'] ?? null;
                $monto = floatval($gasto['monto'] ?? 0);
                $esPagoInmediato = !empty($gasto['pagoInmediato']);
                $idBanco = !empty($gasto['banco']) ? intval($gasto['banco']) : null;
                $numContenedor = $num_Contenedor;

                // Validaciones básicas
                if (!$motivo || !isset($descripcionGastosPermitidos[$motivo])) {
                    continue;
                }
                if ($monto <= 0) {
                    continue;
                }

                if ($esPagoInmediato && empty($idBanco)) {
                    $respuesta = [
                        "Titulo" => "Debe seleccionar banco",
                        "Mensaje" => "Ha seleccionado pago inmediato. Por favor, indique el banco desde el cual se realizará el retiro.",
                        "TMensaje" => "warning"
                    ];
                    continue;
                }

                $contenedor = DocumCotizacion::where('num_contenedor', $numContenedor)
                    ->where('id_empresa', $idEmpresa)
                    ->first();

                if (!$contenedor) {
                    continue;
                }

                $asignacion = Asignaciones::where('id_contenedor', $contenedor->id)->first();
                if (!$asignacion) {
                    continue;
                }

                $tipoGasto = $descripcionGastosPermitidos[$motivo];

                // 🔹 Evitar duplicados
                $gastoExistente = GastosOperadores::where('id_cotizacion', $contenedor->id_cotizacion)
                    ->where('tipo', $tipoGasto)
                    ->first();

                if ($gastoExistente) {
                    if ($gastoExistente->cantidad != $monto) {
                        $gastoExistente->update(["cantidad" => $monto]);
                    }
                    continue;
                }

                // 🔹 Registrar gasto operador
                $datosGasto[] = [
                    "id_cotizacion" => $contenedor->id_cotizacion,
                    "id_banco" => $esPagoInmediato ? $idBanco : null,
                    "id_asignacion" => $asignacion->id,
                    "id_operador" => $asignacion->id_operador,
                    "cantidad" => $monto,
                    "tipo" => $tipoGasto,
                    "estatus" => $esPagoInmediato ? 'Pagado' : 'Pago Pendiente',
                    "fecha_pago" => $esPagoInmediato ? Carbon::now() : null,
                    "pago_inmediato" => $esPagoInmediato,
                    "created_at" => Carbon::now()
                ];

                // 🔹 Si es pago inmediato, validar y descontar saldo
                if ($esPagoInmediato && $idBanco) {
                    $banco = Bancos::where('id_empresa', $idEmpresa)->where('id', $idBanco)->first();

                    if (!$banco) {
                        $respuesta = [
                            "Titulo" => "Banco no encontrado",
                            "Mensaje" => "El banco seleccionado no existe o no pertenece a la empresa.",
                            "TMensaje" => "warning"
                        ];
                        continue;
                    }

                    if ($banco->saldo < $monto) {
                        $respuesta = [
                            "Titulo" => "Saldo insuficiente",
                            "Mensaje" => "El banco {$banco->nombre} no cuenta con saldo suficiente.",
                            "TMensaje" => "warning"
                        ];
                        continue;
                    }

                    // 🔹 Registrar movimiento bancario
                    $contenedoresAbonosJson = json_encode([
                        ['num_contenedor' => $numContenedor, 'abono' => $monto]
                    ]);

                    $bancoDinero[] = [
                        "monto1" => $monto,
                        "metodo_pago1" => 'Transferencia',
                        "descripcion" => "{$tipoGasto} {$numContenedor}",
                        "id_banco1" => $idBanco,
                        "contenedores" => $contenedoresAbonosJson,
                        "tipo" => 'Salida',
                        "fecha_pago" => date('Y-m-d'),
                    ];

                    // 🔹 Descontar saldo de inmediato
                    Bancos::where('id', $idBanco)->update([
                        "saldo" => DB::raw("saldo - {$monto}")
                    ]);
                }
            }

            // 🔹 Guardar registros
            if (!empty($datosGasto)) {
                Log::info('Insertando gastos operadores:', $datosGasto);
                GastosOperadores::insert($datosGasto);
            }

            if (!empty($bancoDinero)) {
                Log::info('Insertando movimientos bancarios:', $bancoDinero);
                BancoDinero::insert($bancoDinero);
            }

            DB::commit();

            return $respuesta ?? [
                "Titulo" => "Gasto agregado",
                "Mensaje" => "Se agregaron los gastos correctamente.",
                "TMensaje" => "success"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en guardarOtrosGastos: ' . $e->getMessage());

            return [
                "Titulo" => "Error interno",
                "Mensaje" => "Ocurrió un error al guardar los gastos.",
                "TMensaje" => "error"
            ];
        }
    }


    public function edit_fecha(Request $request)
    {
        $id = $request->get('urlId');
        $urlId = $request->get('urlId');

        $cotizaciones = Cotizaciones::find($urlId);

        if ($request->get('finzalizar_vieje') != null) {

            $cotizaciones->estatus = $request->get('finzalizar_vieje');

        }
        $cotizaciones->update();

        $asignaciones = Asignaciones::where('id_contenedor', '=', $cotizaciones->id)->first();

        if ($request->get('finzalizar_vieje') == 'Finalizado') {
            $asignaciones->fecha_inicio = null;
            $asignaciones->fecha_fin = null;
        } else {
            $asignaciones->fecha_inicio = $request->get('nuevaFechaInicio');
            $asignaciones->fecha_fin = $request->get('nuevaFechaFin');
        }

        $asignaciones->nombreOperadorSub = $request->get('nombreOperadorSub');
        $asignaciones->telefonoOperadorSub = $request->get('telefonoOperadorSub');
        $asignaciones->placasOperadorSub = $request->get('placasOperadorSub');

        $asignaciones->update();

        // Devuelve una respuesta, por ejemplo:
        return response()->json(['message' => 'Cambios aplicados correctamente','TMensaje' => 'success']);
    }


    public function advance_planeaciones(Request $request)
    {
        $cotizaciones = Cotizaciones::where('id_empresa', '=', auth()->user()->id_empresa)->where('estatus', '=', 'Aprobada')->where('estatus_planeacion', '=', null)->get();
        $numCotizaciones = $cotizaciones->count();
        $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)
        ->where(function ($query) {
            $query->where('tipo', '=', 'servicio de burreo')
                  ->orwhere('tipo', '>=', 'servicio de viaje');
        })
        ->get();

        $equipos = Equipo::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $operadores = Operador::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $events = [];

        $appointments = Asignaciones::where('id_empresa', '=', auth()->user()->id_empresa)->get();


        foreach ($appointments as $appointment) {
            if ($appointment->id_operador == null) {
                $description = 'Proveedor: ' . $appointment->Proveedor->nombre . ' - ' . $appointment->Proveedor->telefono . '<br>' . 'Costo viaje: ' . $appointment->precio;
                $tipo = 'S';
            } else {
                if ($appointment->Contenedor->Cotizacion->tipo_viaje == 'Sencillo') {
                    $description = 'Tipo viaje: ' . $appointment->Contenedor->Cotizacion->tipo_viaje . '<br> <br>' .
                    'Operador: ' . $appointment->Operador->nombre . ' - ' . $appointment->Operador->telefono . '<br>' .
                    'Camion: ' . ' #' . $appointment->Camion->id_equipo . ' - ' . $appointment->Camion->num_serie . ' - ' . $appointment->Camion->modelo . '<br>' .
                    'Chasis: ' . $appointment->Chasis->num_serie . ' - ' . $appointment->Chasis->modelo . '<br>';
                } elseif ($appointment->Contenedor->Cotizacion->tipo_viaje == 'Full') {
                    $description = 'Tipo viaje: ' . $appointment->Contenedor->Cotizacion->tipo_viaje . '<br> <br>' .
                    'Operador: ' . $appointment->Operador->nombre . ' - ' . $appointment->Operador->telefono . '<br>' .
                    'Camion: ' . ' #' . $appointment->Camion->id_equipo . ' - ' . $appointment->Camion->num_serie . ' - ' . $appointment->Camion->modelo . '<br>' .
                    'Chasis: ' . $appointment->Chasis->num_serie . ' - ' . $appointment->Chasis->modelo . '<br>' .
                    'Chasis 2: ' . $appointment->Chasis2->num_serie . ' - ' . $appointment->Chasis2->modelo . '<br>' .
                    'Doly: ' . $appointment->Doly->num_serie . ' - ' . $appointment->Doly->modelo . '<br>';
                }
                $tipo = 'P';
            }

            $coordenadas = Coordenadas::where('id_asignacion', '=', $appointment->id)->first();

            $description = str_replace('<br>', "\n", $description);

            $isOperadorNull = $appointment->id_operador === null;

            $event = [
                'title' => $tipo .' / '. $appointment->Contenedor->Cotizacion->Cliente->nombre . ' / #' . $appointment->Contenedor->Cotizacion->DocCotizacion->num_contenedor,
                'description' => $description,
                'start' => $appointment->fecha_inicio,
                'end' => $appointment->fecha_fin,
                'urlId' => $appointment->id,
                'idCotizacion' => $appointment->Contenedor->id_cotizacion,
                'isOperadorNull' => $isOperadorNull,
                'nombreOperadorSub' => $appointment->nombreOperadorSub ?? '',
                'telefonoOperadorSub' => $appointment->telefonoOperadorSub ?? '',
                'placasOperadorSub' => $appointment->placasOperadorSub ?? '',
            ];


            // Verificar si $coordenadas no es null antes de acceder a su propiedad id
            if ($coordenadas !== null) {
                $event['idCoordenda'] = $appointment->id;
            }

            if ($appointment->Operador !== null) {
                $event['telOperador'] = $appointment->Operador->telefono;
            }
            // else{
            //     $event['telOperador'] = '5585314498';
            // }

            $events[] = $event;

        }

        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
        ->where('asignaciones.fecha_inicio', '!=', null)->where('asignaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->select('asignaciones.*', 'docum_cotizacion.num_contenedor')
        ->get();

        $asignaciones = Asignaciones::where('id_empresa', '=', auth()->user()->id_empresa);

        if ($request->contenedor !== null) {
            $asignaciones = $asignaciones->where('id', $request->contenedor);
        }

        $asignaciones = $asignaciones->first();

        return view('planeacion.index', compact('equipos', 'operadores', 'events', 'cotizaciones', 'proveedores', 'numCotizaciones', 'asignaciones', 'planeaciones'));
    }

    public function advance_planeaciones_faltantes(Request $request)
    {
        $cotizaciones = Cotizaciones::where('id_empresa', '=', auth()->user()->id_empresa)->where('estatus', '=', 'Aprobada')->where('estatus_planeacion', '=', null)->get();
        $numCotizaciones = $cotizaciones->count();
        $proveedores = Proveedor::where('id_empresa', '=', auth()->user()->id_empresa)
        ->where(function ($query) {
            $query->where('tipo', '=', 'servicio de burreo')
                  ->orwhere('tipo', '>=', 'servicio de viaje');
        })
        ->get();

        $equipos = Equipo::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $operadores = Operador::where('id_empresa', '=', auth()->user()->id_empresa)->get();
        $events = [];

        $appointments = Asignaciones::where('id_empresa', '=', auth()->user()->id_empresa)->get();


        foreach ($appointments as $appointment) {
            if ($appointment->id_operador == null) {
                $description = 'Proveedor: ' . $appointment->Proveedor->nombre . ' - ' . $appointment->Proveedor->telefono . '<br>' . 'Costo viaje: ' . $appointment->precio;
                $tipo = 'S';
            } else {
                if ($appointment->Contenedor->Cotizacion->tipo_viaje == 'Sencillo') {
                    $description = 'Tipo viaje: ' . $appointment->Contenedor->Cotizacion->tipo_viaje . '<br> <br>' .
                    'Operador: ' . $appointment->Operador->nombre . ' - ' . $appointment->Operador->telefono . '<br>' .
                    'Camion: ' . ' #' . $appointment->Camion->id_equipo . ' - ' . $appointment->Camion->num_serie . ' - ' . $appointment->Camion->modelo . '<br>' .
                    'Chasis: ' . $appointment->Chasis->num_serie . ' - ' . $appointment->Chasis->modelo . '<br>';
                } elseif ($appointment->Contenedor->Cotizacion->tipo_viaje == 'Full') {
                    $description = 'Tipo viaje: ' . $appointment->Contenedor->Cotizacion->tipo_viaje . '<br> <br>' .
                    'Operador: ' . $appointment->Operador->nombre . ' - ' . $appointment->Operador->telefono . '<br>' .
                    'Camion: ' . ' #' . $appointment->Camion->id_equipo . ' - ' . $appointment->Camion->num_serie . ' - ' . $appointment->Camion->modelo . '<br>' .
                    'Chasis: ' . $appointment->Chasis->num_serie . ' - ' . $appointment->Chasis->modelo . '<br>' .
                    'Chasis 2: ' . $appointment->Chasis2->num_serie . ' - ' . $appointment->Chasis2->modelo . '<br>' .
                    'Doly: ' . $appointment->Doly->num_serie . ' - ' . $appointment->Doly->modelo . '<br>';
                }
                $tipo = 'P';
            }

            $coordenadas = Coordenadas::where('id_asignacion', '=', $appointment->id)->first();

            $description = str_replace('<br>', "\n", $description);

            $isOperadorNull = $appointment->id_operador === null;

            $event = [
                'title' => $tipo .' / '. $appointment->Contenedor->Cotizacion->Cliente->nombre . ' / #' . $appointment->Contenedor->Cotizacion->DocCotizacion->num_contenedor,
                'description' => $description,
                'start' => $appointment->fecha_inicio,
                'end' => $appointment->fecha_fin,
                'urlId' => $appointment->id,
                'idCotizacion' => $appointment->Contenedor->id_cotizacion,
                'isOperadorNull' => $isOperadorNull,
                'nombreOperadorSub' => $appointment->nombreOperadorSub ?? '',
                'telefonoOperadorSub' => $appointment->telefonoOperadorSub ?? '',
                'placasOperadorSub' => $appointment->placasOperadorSub ?? '',
            ];


            // Verificar si $coordenadas no es null antes de acceder a su propiedad id
            if ($coordenadas !== null) {
                $event['idCoordenda'] = $appointment->id;
            }

            if ($appointment->Operador !== null) {
                $event['telOperador'] = $appointment->Operador->telefono;
            }
            // else{
            //     $event['telOperador'] = '5585314498';
            // }

            $events[] = $event;

        }

        $planeaciones = Asignaciones::join('docum_cotizacion', 'asignaciones.id_contenedor', '=', 'docum_cotizacion.id')
        ->where('asignaciones.fecha_inicio', '!=', null)->where('asignaciones.id_empresa', '=', auth()->user()->id_empresa)
        ->select('asignaciones.*', 'docum_cotizacion.num_contenedor')
        ->get();

        $cotizaciones_faltantes = Cotizaciones::where('id_empresa', '=', auth()->user()->id_empresa)->where('estatus', '=', 'Aprobada')->where('estatus_planeacion', '=', null);

        if ($request->contenedor_faltantes !== null) {
            $cotizaciones_faltantes = $cotizaciones_faltantes->where('id', $request->contenedor_faltantes);
        }

        $cotizaciones_faltantes = $cotizaciones_faltantes->first();

        return view('planeacion.index', compact('equipos', 'operadores', 'events', 'cotizaciones', 'proveedores', 'numCotizaciones', 'cotizaciones_faltantes', 'planeaciones'));
    }

}
