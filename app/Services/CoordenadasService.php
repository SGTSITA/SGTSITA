<?php

namespace App\Services;

use App\Models\Coordenadas;
use App\Models\Cotizaciones;
use App\Models\Equipo;
use App\Models\DocumCotizacion;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class CoordenadasService
{
    public function guardarRespuesta($request)
    {
        $coordenada = Coordenadas::find($request->id_coordenada);

        if (!$coordenada) {
            return [
                'success' => false,
                'message' => 'Coordenada no encontrada'
            ];
        }

        $fecha = Carbon::now();

        $coordenada->update([
            $request->columna => $request->coordenadas,
            $request->columna_datetime => $fecha
        ]);

        $message = 'Coordenada guardada.';

        if ($request->columna === 'recepcion_doc_firmados') {

            $cotizacion = Cotizaciones::find($coordenada->id_cotizacion);

            if ($cotizacion) {
                $cotizacion->update([
                    'estatus' => 'Finalizado'
                ]);

                $message = 'Coordenada guardada. Viaje finalizado';
            }
        }

        return [
            'success' => true,
            'message' => $message
        ];
    }
    //archivo foto patio preguntas

    public function subirFotoPatio($file, $cotizacionId, $idCoordenada)
    {
        $path = public_path('/cotizaciones/cotizacion' . $cotizacionId);

        if (!file_exists($path)) {
            mkdir($path, 0755, true);
        }

        $fileName = uniqid() . '_' . $file->getClientOriginalName();

        $file->move($path, $fileName);

        $doc = DocumCotizacion::firstOrNew([
            'id_cotizacion' => $cotizacionId
        ]);

        $doc->foto_patio = $fileName;
        $doc->save();

        $coordenada = Coordenadas::find($idCoordenada);

        if ($coordenada) {

            $coordenada->update([
                'toma_foto_patio' => 1,
                'toma_foto_patio_datetime' => Carbon::now()
            ]);

        }

        return $fileName;
    }

    //end archivo foto patio preguntas



    //coordenadas
    public function guardarCoordenada($data)
    {
        $coordenada = Coordenadas::firstOrCreate(
            [
                'id_asignacion' => $data['id_asignacion'],
                'id_cotizacion' => $data['id_cotizacion']
            ],
            [
                'tipo_flujo' => $data['tipo_flujo'] ?? null,
                'registro_puerto' => $data['registro_puerto'] ?? null,
                'dentro_puerto' => $data['dentro_puerto'] ?? null,
                'descarga_vacio' => $data['descarga_vacio'] ?? null,
                'cargado_contenedor' => $data['cargado_contenedor'] ?? null,
                'fila_fiscal' => $data['fila_fiscal'] ?? null,
                'modulado_tipo' => $data['modulado_tipo'] ?? null,
                'modulado_coordenada' => $data['modulado_coordenada'] ?? null,
                'en_destino' => $data['en_destino'] ?? null,
                'inicio_descarga' => $data['inicio_descarga'] ?? null,
                'fin_descarga' => $data['fin_descarga'] ?? null,
                'recepcion_doc_firmados' => $data['recepcion_doc_firmados'] ?? null,

                'tipo_flujo_datatime' => $data['tipo_flujo_datatime'] ?? null,
                'registro_puerto_datatime' => $data['registro_puerto_datatime'] ?? null,
                'dentro_puerto_datatime' => $data['dentro_puerto_datatime'] ?? null,
                'descarga_vacio_datatime' => $data['descarga_vacio_datatime'] ?? null,
                'cargado_contenedor_datatime' => $data['cargado_contenedor_datatime'] ?? null,
                'fila_fiscal_datatime' => $data['fila_fiscal_datatime'] ?? null,
                'modulado_tipo_datatime' => $data['modulado_tipo_datatime'] ?? null,
                'modulado_coordenada_datatime' => $data['modulado_coordenada_datatime'] ?? null,
                'en_destino_datatime' => $data['en_destino_datatime'] ?? null,
                'inicio_descarga_datatime' => $data['inicio_descarga_datatime'] ?? null,
                'fin_descarga_datatime' => $data['fin_descarga_datatime'] ?? null,
                'recepcion_doc_firmados_datatime' => $data['recepcion_doc_firmados_datatime'] ?? null,

                'tipo_c_estado' => $data['tipo_c_estado'] ?? null,
                'tipo_b_estado' => $data['tipo_b_estado'] ?? null,
                'tipo_f_estado' => $data['tipo_f_estado'] ?? null,
            ]
        );

        return $coordenada;
    }


    public function getDetalleCoordenadas($idCotizacion)
    {

        $query = $this->baseQuery()

            ->addSelect(
                'coordenadas.registro_puerto',
                'coordenadas.registro_puerto_datatime',
                'coordenadas.dentro_puerto',
                'coordenadas.dentro_puerto_datatime',
                'coordenadas.descarga_vacio',
                'coordenadas.descarga_vacio_datatime',
                'coordenadas.cargado_contenedor',
                'coordenadas.cargado_contenedor_datatime',
                'coordenadas.fila_fiscal',
                'coordenadas.fila_fiscal_datatime',
                'coordenadas.modulado_tipo',
                'coordenadas.modulado_tipo_datatime',
                'coordenadas.modulado_coordenada',
                'coordenadas.modulado_coordenada_datatime',
                'coordenadas.en_destino',
                'coordenadas.en_destino_datatime',
                'coordenadas.inicio_descarga',
                'coordenadas.inicio_descarga_datatime',
                'coordenadas.fin_descarga',
                'coordenadas.fin_descarga_datatime',
                'coordenadas.recepcion_doc_firmados',
                'coordenadas.recepcion_doc_firmados_datatime',
                'coordenadas.descarga_patio',
                'coordenadas.descarga_patio_datetime',
                'coordenadas.cargado_patio',
                'coordenadas.cargado_patio_datetime',
                'coordenadas.tipo_c_estado',
                'coordenadas.tipo_b_estado',
                'coordenadas.tipo_f_estado',
                'coordenadas.toma_foto_patio',
                'coordenadas.toma_foto_patio_datetime'
            )

            ->where('c.id', $idCotizacion)

            ->first();


        return $query;
    }


    public function getReporteCoordenadas($params)
    {
        $query = $this->baseQuery();

        $query->addSelect(
            'coordenadas.registro_puerto',
            'coordenadas.registro_puerto_datatime',
            'coordenadas.dentro_puerto',
            'coordenadas.dentro_puerto_datatime',
            'coordenadas.descarga_vacio',
            'coordenadas.descarga_vacio_datatime',
            'coordenadas.cargado_contenedor',
            'coordenadas.cargado_contenedor_datatime',
            'coordenadas.fila_fiscal',
            'coordenadas.fila_fiscal_datatime',
            'coordenadas.modulado_tipo',
            'coordenadas.modulado_tipo_datatime',
            'coordenadas.modulado_coordenada',
            'coordenadas.modulado_coordenada_datatime',
            'coordenadas.en_destino',
            'coordenadas.en_destino_datatime',
            'coordenadas.inicio_descarga',
            'coordenadas.inicio_descarga_datatime',
            'coordenadas.fin_descarga',
            'coordenadas.fin_descarga_datatime',
            'coordenadas.recepcion_doc_firmados',
            'coordenadas.recepcion_doc_firmados_datatime',
            'coordenadas.descarga_patio',
            'coordenadas.descarga_patio_datetime',
            'coordenadas.cargado_patio',
            'coordenadas.cargado_patio_datetime',
            'coordenadas.toma_foto_patio',
            'coordenadas.tipo_b_estado',
            'coordenadas.tipo_f_estado',
            'coordenadas.tipo_c_estado'
        );

        $query

    ->when($params['proveedor'] ?? null, function ($q, $proveedor) {
        $q->where('proveedor_id', $proveedor);
    })

    ->when($params['cliente'] ?? null, function ($q, $cliente) {
        $q->where('cli.id', $cliente);
    })

    ->when($params['idCliente'] ?? null, function ($q, $idCliente) {
        $q->where('cli.id', $idCliente);
    })

    ->when(
        ($params['fecha_inicio'] ?? null) && ($params['fecha_fin'] ?? null),
        function ($q) use ($params) {

            $q->whereBetween('a.fecha_inicio', [
                $params['fecha_inicio'],
                $params['fecha_fin']
            ]);

        }
    )

    ->when($params['contenedor'] ?? null, function ($q, $contenedor) {
        $q->where('dc.num_contenedor', $contenedor);
    });


        if (!empty($params['contenedores'])) {

            $contenedores = array_filter(
                array_map('trim', explode(';', $params['contenedores']))
            );

            $query->whereIn('dc.num_contenedor', $contenedores);
        }

        $query->where(function ($q) {

            $q->whereNotNull('coordenadas.tipo_flujo')
              ->orWhereNotNull('coordenadas.registro_puerto')
              ->orWhereNotNull('coordenadas.dentro_puerto')
              ->orWhereNotNull('coordenadas.descarga_vacio')
              ->orWhereNotNull('coordenadas.cargado_contenedor')
              ->orWhereNotNull('coordenadas.fila_fiscal')
              ->orWhereNotNull('coordenadas.modulado_tipo')
              ->orWhereNotNull('coordenadas.modulado_coordenada')
              ->orWhereNotNull('coordenadas.en_destino')
              ->orWhereNotNull('coordenadas.inicio_descarga')
              ->orWhereNotNull('coordenadas.fin_descarga')
              ->orWhereNotNull('coordenadas.recepcion_doc_firmados')
              ->orWhereNotNull('coordenadas.descarga_patio')
              ->orWhereNotNull('coordenadas.cargado_patio');

        });

        return $query->get();

    }

    //end coordenadas




    public function baseQuery()
    {

        return DB::table('asignaciones as a')

            ->join('docum_cotizacion as dc', 'dc.id', '=', 'a.id_contenedor')

            ->join('cotizaciones as c', 'c.id', '=', 'dc.id_cotizacion')

            ->join('clients as cli', 'cli.id', '=', 'c.id_cliente')
            ->join('empresas as em', 'em.id', '=', 'a.id_empresa')

            ->leftJoin('equipos as eq', 'eq.id', '=', 'a.id_camion')

            ->leftJoin('equipos as eq_chasis', 'eq_chasis.id', '=', 'a.id_chasis')

            ->leftJoin('gps_company as gps', 'gps.id', '=', 'eq.gps_company_id')
            ->leftJoin('gps_company as gpsChasis', 'gpsChasis.id', '=', 'eq_chasis.gps_company_id')
            ->LeftJoin('coordenadas', 'coordenadas.id_asignacion', '=', 'a.id')
            ->LeftJoin('proveedores as prov', 'prov.id', '=', 'c.id_proveedor')
             ->leftjoin('operadores', 'operadores.id', '=', 'a.id_operador')


            ->select(
                'c.id as id_cotizacion',
                'a.id as id_asignacion',
                'coordenadas.id as id_coordenada',
                'cli.id as id_cliente',
                'cli.nombre as cliente',
                'c.origen',
                'c.destino',
                'dc.num_contenedor as contenedor',
                'c.estatus',
                'eq.id as id_equipo_unico',
                'eq.imei',
                'eq.id_equipo',
                'gps.url_conexion as tipoGps',
                'a.id_contenedor',
                'a.tipo_contrato',
                'a.fecha_inicio',
                'a.fecha_fin',
                'eq_chasis.imei as imei_chasis',
                'eq_chasis.id_equipo as id_equipo_chasis',
                'gpsChasis.url_conexion as tipoGpsChasis',
                'c.id_empresa',
                'c.latitud',
                'c.longitud',
                'c.cp_contacto_entrega',
                'em.nombre as empresa',
                'operadores.nombre as operador',
                'prov.nombre as transportista_nombre',
                DB::raw('COALESCE(prov.telefono, operadores.telefono) as beneficiario_telefono'),
                DB::raw("
                    COALESCE(a.id_proveedor, c.id_proveedor)
                    as proveedor_id
                "),
            )    ;



    }

    public function applyFilters($query, $filters)
    {

        return $query
          ->whereNotNull('eq.imei')

            ->where('c.estatus', 'Aprobada')


            ->when(!($filters['isAdmin'] ?? false), function ($q) use ($filters) {

                $q->when($filters['id_empresa'] ?? null, function ($q, $id_empresa) {
                    $q->where('c.id_empresa', $id_empresa);
                })

                ->when($filters['idCliente'] ?? null, function ($q, $idCliente) {
                    $q->where('cli.id', $idCliente);
                })

                ->when($filters['proveedor'] ?? null, function ($q, $proveedor) {
                    $q->whereRaw(
                        'COALESCE(a.id_proveedor, c.id_proveedor) = ?',
                        [$proveedor]
                    );
                })

                ->when($filters['contenedor'] ?? null, function ($q, $contenedor) {
                    $q->where('dc.num_contenedor', $contenedor);
                })

                ->when($filters['contenedores'] ?? null, function ($q, $contenedores) {

                    $list = array_filter(array_map('trim', explode(';', $contenedores)));

                    if (count($list) > 1) {
                        $q->whereIn('dc.num_contenedor', $list);
                    } else {
                        $q->where('dc.num_contenedor', $list[0]);
                    }
                });

            })


          ->when($filters['fecha'] ?? null, function ($q, $fecha) {  // por si despues pongo boton en vista para filtrar con rango de fecha o algo asi.

              $q->whereDate('a.fecha_inicio', '<=', $fecha)
                ->whereDate('a.fecha_fin', '>=', $fecha);

          });
    }


    public function getContenedoresRastreo($query, $filters = [])
    {
        $query = $this->applyFilters($query, $filters);

        return $query->get();
    }

    public function getContenedoresBase($filters = [])
    {

        $query = $this->baseQuery();

        $query = $this->applyFilters($query, $filters);

        return $query;
    }


    //end contenedores rastreo


    //conboys
    public function baseConboysQuery()
    {

        return DB::table('conboys')

    ->join('conboys_contenedores', 'conboys.id', '=', 'conboys_contenedores.conboy_id')

    ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')

    ->join('cotizaciones', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')

    ->select(
        'conboys.id',
        'conboys.no_conboy',
        'conboys.nombre',
        'conboys.fecha_inicio',
        'conboys.fecha_fin',
        'conboys.user_id',
        'conboys.tipo_disolucion',
        'conboys.estatus',
        'conboys.fecha_disolucion',
        'conboys.geocerca_lat',
        'conboys.geocerca_lng',
        'conboys.geocerca_radio',
        DB::raw('GROUP_CONCAT(DISTINCT cotizaciones.id_empresa) as empresas'),
        DB::raw('GROUP_CONCAT(DISTINCT cotizaciones.id_cliente) as clientes'),
        DB::raw('GROUP_CONCAT(DISTINCT cotizaciones.id_proveedor) as lineas')
    )

    ->where('conboys.estatus', 'Activo')
    ->groupBy(
        'conboys.id',
        'conboys.no_conboy',
        'conboys.nombre',
        'conboys.fecha_inicio',
        'conboys.fecha_fin',
        'conboys.user_id',
        'conboys.tipo_disolucion',
        'conboys.estatus',
        'conboys.fecha_disolucion',
        'conboys.geocerca_lat',
        'conboys.geocerca_lng',
        'conboys.geocerca_radio'
    );
    }

    public function applyConboysFilters($query, $filters)
    {
        $fecha = $filters['fecha'] ?? Carbon::today()->toDateString();
        return $query

           ->whereDate('conboys.fecha_inicio', '<=', $fecha)
        ->whereDate('conboys.fecha_fin', '>=', $fecha)

         ->when(
             !($filters['isAdmin'] ?? false),
             function ($q) use ($filters) {
                 $q->when($filters['id_empresa'] ?? null, function ($q, $id_empresa) {
                     $q->where('cotizaciones.id_empresa', $id_empresa);
                 })

                 ->when($filters['idCliente'] ?? null, function ($q, $idCliente) {
                     $q->where('cotizaciones.id_cliente', $idCliente);
                 });
             }
         );



    }
    public function getConboys($filters = [])
    {

        $query = $this->baseConboysQuery();

        $query = $this->applyConboysFilters($query, $filters);

        return $query->get();

    }


    public function baseConboysDetalleQuery()
    {
        return DB::table('conboys_contenedores')

            ->join('conboys', 'conboys.id', '=', 'conboys_contenedores.conboy_id')

            ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')

            ->leftJoin('asignaciones', 'asignaciones.id_contenedor', '=', 'conboys_contenedores.id_contenedor')

            ->join('cotizaciones', 'cotizaciones.id', '=', 'docum_cotizacion.id_cotizacion')

            ->join('equipos', 'equipos.id', '=', 'asignaciones.id_camion')

            ->join('gps_company', 'gps_company.id', '=', 'equipos.gps_company_id')

            ->leftJoin('equipos as eq_chasis', 'eq_chasis.id', '=', 'asignaciones.id_chasis')

            ->leftJoin('gps_company as gps_companyChasis', 'gps_companyChasis.id', '=', 'eq_chasis.gps_company_id')

            ->select(
                'conboys_contenedores.conboy_id',
                'conboys.no_conboy',
                'conboys_contenedores.id_contenedor',
                'docum_cotizacion.num_contenedor',
                'equipos.imei',
                'equipos.id_equipo',
                'gps_company.url_conexion as tipoGps',
                'conboys_contenedores.es_primero',
                'eq_chasis.imei as imei_chasis',
                'gps_companyChasis.url_conexion as tipoGpsChasis',
                'eq_chasis.id_equipo as id_equipo_chasis'
            );
    }

    public function applyFiltersConboysDetalle($query, $filters)
    {

        $fecha = $filters['fecha'] ?? Carbon::today()->toDateString();

        return $query

            ->where('conboys.estatus', 'Activo')

            ->when(
                !($filters['isAdmin'] ?? false),
                function ($q) use ($filters) {
                    $q->when($filters['id_empresa'] ?? null, function ($q, $id_empresa) {
                        $q->where('cotizaciones.id_empresa', $id_empresa);
                    })

                    ->when($filters['idCliente'] ?? null, function ($q, $idCliente) {
                        $q->where('cotizaciones.id_cliente', $idCliente);
                    });
                }
            )

            ->when($filters['fecha'] ?? null, function ($q) use ($fecha) {

                $q->whereDate('conboys.fecha_inicio', '<=', $fecha)
                  ->whereDate('conboys.fecha_fin', '>=', $fecha);

            });

    }

    public function getConboysDetalle($filters = [])
    {

        $query = $this->baseConboysDetalleQuery();

        $query = $this->applyFiltersConboysDetalle($query, $filters);

        return $query->get();

    }

    //end conboys



    //equipos con gps
    public function buildEquiposQuery()
    {
        return Equipo::select(
            'equipos.id',
            'equipos.imei',
            'equipos.tipo',
            'equipos.marca',
            'equipos.id_equipo',
            'equipos.placas',
            'gps_company.url_conexion as tipoGps',
            'equipos.id_empresa',
            'equipos.user_id'
        )
        ->join('gps_company', 'gps_company.id', '=', 'equipos.gps_company_id')
        ->whereNotNull('equipos.imei');
    }


    public function applyFiltersEquipos($query, $filters)
    {
        return $query

         ->when(!($filters['isAdmin'] ?? false), function ($q) use ($filters) {

             $empresa = $filters['id_empresa'] ?? 0;

             if ($empresa != 0) {
                 $q->where('equipos.id_empresa', $empresa);
             }
         });

    }

    public function getEquiposbase($filters = [])
    {
        $query = $this->buildEquiposQuery();

        $query = $this->applyFiltersEquipos($query, $filters);

        return $query;
    }


    public function getEquiposRastreo($equipos, $filters = [])
    {
        $query = $this->applyFiltersEquipos($equipos, $filters);

        return $query->get();
    }
    //end equipos con gps

}
