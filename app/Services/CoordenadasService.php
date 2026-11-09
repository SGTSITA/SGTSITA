<?php

namespace App\Services;

use App\Models\Coordenadas;
use App\Models\Cotizaciones;
use App\Models\Equipo;
use App\Models\DocumCotizacion;
use App\Models\Conboys;
use App\Models\conboysContenedores;
use App\Models\CoordenadasHistorial;
use App\Models\User;
use App\Services\AuditoriaCifrado;
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
            ->join('empresas as emc', 'emc.id', '=', 'c.id_empresa')
            ->leftJoin('viajes_cotizacion', 'viajes_cotizacion.cotizacion_id', '=', 'c.id')
            ->leftJoin('viajes', 'viajes.id', '=', 'viajes_cotizacion.viaje_id')


            ->leftJoin('equipos as eq', 'eq.id', '=', 'a.id_camion')

            ->leftJoin('equipos as eq_chasis', 'eq_chasis.id', '=', 'a.id_chasis')
            ->leftJoin('equipos as eq_chasis2', 'eq_chasis2.id', '=', 'a.id_chasis2')

            ->leftJoin('gps_company as gps', 'gps.id', '=', 'eq.gps_company_id')
            ->leftJoin('gps_company as gpsChasis', 'gpsChasis.id', '=', 'eq_chasis.gps_company_id')
            ->leftJoin('gps_company as gpsChasis2', 'gpsChasis2.id', '=', 'eq_chasis2.gps_company_id')
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
                'viajes.id as id_viaje',
                'viajes.tipo as tipo_viaje',
                'c.referencia_full',
                   DB::raw("
        CASE
            WHEN c.referencia_full IS NOT NULL
            AND c.referencia_full <> ''
            AND EXISTS (
                SELECT 1
                FROM cotizaciones c2
                WHERE c2.referencia_full = c.referencia_full
                AND c2.id <> c.id
                AND c2.referencia_full IS NOT NULL
                AND c2.referencia_full <> ''
            )
            THEN 1
            ELSE 0
        END as es_full
    "),
                'dc.num_contenedor as contenedor',
                'c.estatus',
                'eq.id as id_equipo_unico',
                'eq.imei',
                'eq.id_equipo',
                'eq.placas',
                'gps.url_conexion as tipoGps',
                'gps.nombre as nombre_gps',
                'gps.icono as icono_gps',
                'a.id_contenedor',
                'a.tipo_contrato',
                'a.fecha_inicio',
                'a.fecha_fin',

                'eq_chasis.imei as imei_chasis',
                'eq_chasis.id_equipo as id_equipo_chasis',
                'gpsChasis.url_conexion as tipoGpsChasis',
                  'gpsChasis.nombre as nombre_gpschasis',
                'eq_chasis.placas as placasChasis',
                'gpsChasis.icono as icono_gpschasis',

                'eq_chasis2.imei as imei_chasis2',
                'eq_chasis2.id_equipo as id_equipo_chasis2',
                'gpsChasis2.url_conexion as tipoGpsChasis2',
                  'gpsChasis2.nombre as nombre_gpschasis2',
                'eq_chasis2.placas as placasChasis2',
                'gpsChasis2.icono as icono_gpschasis2',
                'c.id_empresa',
                'a.id_empresa as id_empresa_Asig',
                'c.latitud',
                'c.longitud',
                'c.cp_contacto_entrega',
                DB::raw("COALESCE(NULLIF(em.nombre, ''), emc.nombre) as empresa"),
                'operadores.nombre as operador',
                'prov.nombre as transportista_nombre',
                DB::raw('COALESCE(operadores.telefono,prov.telefono) as beneficiario_telefono'),
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
            ->when($filters['id_contenedores'] ?? null, function ($q, $id_contenedores) {
                $q->whereIn('dc.id', $id_contenedores);
            })


            // Si se especifica idCliente, filtrar por el cliente e ignorar id_empresa
            ->when(!empty($filters['idCliente']), function ($q) use ($filters) {
                $q->where('cli.id', $filters['idCliente']);
            })

            ->when(empty($filters['idCliente']) && !($filters['isAdmin'] ?? false), function ($q) use ($filters) {
                $q->when($filters['id_empresa'] ?? null, function ($q, $id_empresa) {
                    $q->where('c.id_empresa', $id_empresa);
                });
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
                    } else if (count($list) == 1) {
                        $q->where('dc.num_contenedor', $list[0]);
                    }
                })


          ->when($filters['fecha'] ?? null, function ($q, $fecha) {

              $q->whereDate('a.fecha_inicio', '<=', $fecha)
                ->whereDate('a.fecha_fin', '>=', $fecha);

          })
           ->when($filters['fecha_salida'] ?? null, function ($q, $fecha_salida) {

             $inicioDia = Carbon::parse($fecha_salida)->startOfDay();
                $finDia = Carbon::parse($fecha_salida)->endOfDay();

                $q->whereBetween('a.fecha_inicio', [$inicioDia, $finDia]);

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
        ->when($filters['id_convoys'] ?? null, function ($q, $ids) {
            $q->whereIn('conboys.id', $ids);
        })->when($filters['fecha_salida'] ?? null, function ($q, $fecha_salida) {

              $q->whereDate('conboys.fecha_inicio', '>=', $fecha_salida);

          })


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
            ->when($filters['id_convoys'] ?? null, function ($q, $ids) {
                $q->whereIn('conboys.id', $ids);
            })

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

            })->when($filters['fecha_salida'] ?? null, function ($q, $fecha_salida) {

                $q->whereDate('conboys.fecha_inicio', '>=', $fecha_salida);

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


    $fecha = $filters['fecha']
        ?? $filters['fecha']
        ?? Carbon::now()->toDateString();

  $fechaSalida = $filters['fecha_salida']
        ?? $filters['fecha_salida']
        ?? null;


        return $query

         ->when(empty($filters['idCliente']) && !($filters['isAdmin'] ?? false), function ($q) use ($filters) {
             $empresa = $filters['id_empresa'] ?? 0;
             if ($empresa != 0) {
                 $q->where('equipos.id_empresa', $empresa);
             }
         })

         ->where(function ($queryOuter) use ($filters, $fecha, $fechaSalida) {
             $queryOuter->whereExists(function ($sub) use ($filters, $fecha, $fechaSalida) {
                 $sub->select(DB::raw(1))
                     ->from('asignaciones as a')
                     ->join('docum_cotizacion as dc', 'dc.id', '=', 'a.id_contenedor')
                     ->join('cotizaciones as c', 'c.id', '=', 'dc.id_cotizacion')

                     // El equipo puede estar como tracto, chasis A o chasis B
                     ->where(function ($q) {
                         $q->whereColumn('a.id_camion', 'equipos.id')
                             ->orWhereColumn('a.id_chasis', 'equipos.id')
                             ->orWhereColumn('a.id_chasis2', 'equipos.id');
                     })

                     ->when($fecha ?? null, function ($q) use ($fecha) {
                         $q->whereDate('fecha_inicio', '<=', $fecha)
                           ->whereDate('fecha_fin', '>=', $fecha);
                     })
                     ->when($fechaSalida ?? null, function ($q) use ($fechaSalida) {
                         $q->whereDate('fecha_inicio', '>=', $fechaSalida);
                     })

                     // Si no es admin, también valida empresa en asignaciones
                     ->when(empty($filters['idCliente']) && !($filters['isAdmin'] ?? false), function ($q) use ($filters) {
                         $empresa = $filters['id_empresa'] ?? 0;
                         if ($empresa != 0) {
                             $q->where('a.id_empresa', $empresa);
                         }
                     })

                     // Si es cliente externo o usuario con cliente asignado
                     ->when(!empty($filters['idCliente']), function ($q) use ($filters) {
                         $q->where('c.id_cliente', $filters['idCliente']);
                     });
             });

             if (empty($filters['idCliente'])) {
                 $queryOuter->orWhereNotExists(function ($sub) {
                     $sub->select(DB::raw(1))
                         ->from('asignaciones as a')
                         ->where(function ($q) {
                             $q->whereColumn('a.id_camion', 'equipos.id')
                                 ->orWhereColumn('a.id_chasis', 'equipos.id')
                                 ->orWhereColumn('a.id_chasis2', 'equipos.id');
                         });
                 });
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


    /**
     * Consulta convoys finalizados con historial geográfico.
     */
    public function getConboysFinalizados(int $idEmpresa, string $fechaInicio, string $fechaFin)
    {
        $infoSubquery = "
        (
        select cotizaciones.id as id_union,
               asig.id as id_asignacion,
               clients.nombre as cliente,
               cotizaciones.origen,
               cotizaciones.destino,
               asig.num_contenedor as contenedor,
               cotizaciones.estatus,
               asig.tipo_contrato,
               asig.fecha_inicio,
               asig.fecha_fin,
               id_equipo,
               marca,
               placas,
               cotizaciones.id_empresa,
               'Cotizaciones' as tipoConsulta
        from cotizaciones
        inner join clients on cotizaciones.id_cliente = clients.id
        inner join (
            select docum_cotizacion.id as id_contenedor,
                   asignaciones.id,
                   asignaciones.id_camion,
                   docum_cotizacion.num_contenedor,
                   asignaciones.fecha_inicio,
                   asignaciones.fecha_fin,
                   equipos.id_equipo,
                   equipos.marca,
                   equipos.placas,
                   CASE WHEN asignaciones.id_proveedor IS NULL
                        THEN asignaciones.id_operador
                        ELSE asignaciones.id_proveedor END as beneficiario_id,
                   CASE WHEN asignaciones.id_proveedor IS NULL
                        THEN 'Propio'
                        ELSE 'Subcontratado' END as tipo_contrato
            from asignaciones
            inner join docum_cotizacion on docum_cotizacion.id = asignaciones.id_contenedor
            inner join equipos on equipos.id = asignaciones.id_camion
            inner join gps_company on gps_company.id = equipos.gps_company_id
            left join equipos as eq_chasis on eq_chasis.id = asignaciones.id_chasis
        ) as asig on asig.id_contenedor = cotizaciones.id
        inner join (
            select * from (
                (select id, nombre, telefono, 'Propio' as tipo_contrato, id_empresa from operadores)
                union
                (select id, nombre, telefono, 'Subcontratado' as tipo_contrato, id_empresa from proveedores)
            ) as beneficiarios
        ) as beneficiarios
        on asig.beneficiario_id = beneficiarios.id
        and asig.tipo_contrato = beneficiarios.tipo_contrato
        where cotizaciones.id_empresa = ?
        union
        select equipos.id as id_union,
               0 as id_asignacion,
               CONCAT(equipos.id_equipo, ' ', COALESCE(equipos.marca,''), ' ', COALESCE(placas, 'SIN PLACA')) as cliente,
               'NA' as origen,
               'NA' as destino,
               equipos.id_equipo as contenedor,
               case when equipos.activo = 1 then 'Activo' else 'Inactivo' end as estatus,
               '' as tipo_contrato,
               NOW() as fecha_inicio,
               NOW() as fecha_fin,
               equipos.id_equipo,
               equipos.marca,
               equipos.placas,
               equipos.id_empresa,
               'Equipos' as tipoConsulta
        from equipos
        where equipos.id_empresa = ?
        ) as info
        ";

        return DB::table('coordenadas_historial')
            ->distinct()
            ->select([
                'coordenadas_historial.id_convoy',
                'coordenadas_historial.ubicacionable_type',
                'coordenadas_historial.ubicacionable_id',
                'coordenadas_historial.tipo',
                'coordenadas_historial.user_id',
                DB::raw("COALESCE(conboys.id,0) as id"),
                DB::raw("COALESCE(conboys.nombre,'NA') as nombre_convoy"),
                DB::raw("COALESCE(conboys.no_conboy,'NA') as no_conboy"),
                DB::raw("COALESCE(conboys.tipo_disolucion,'NA') as tipo_disolucion"),
                DB::raw("COALESCE(conboys.geocerca_lat,'NA') as geocerca_lat"),
                DB::raw("COALESCE(conboys.geocerca_lng,'NA') as geocerca_lng"),
                DB::raw("COALESCE(conboys.geocerca_radio,'NA') as geocerca_radio"),
                DB::raw("COALESCE(conboys.fecha_disolucion,'NA') as fecha_disolucion"),
                'info.id_union',
                'info.cliente',
                'info.origen',
                'info.destino',
                'info.contenedor',
                'info.fecha_inicio as fecha_inicio_viaje',
                'info.fecha_fin as fecha_fin_viaje',
            ])
            ->leftJoin('conboys', 'conboys.id', '=', 'coordenadas_historial.id_convoy')
            ->join(DB::raw($infoSubquery), function ($join) use ($idEmpresa) {
                $join->on('info.id_union', '=', 'coordenadas_historial.ubicacionable_id');
            })
            ->setBindings([$idEmpresa, $idEmpresa])
            ->whereBetween('coordenadas_historial.registrado_en', [$fechaInicio, $fechaFin])
            ->get();
    }

    /**
     * Consulta convoys activos con sus contenedores asignados.
     */
    public function getConboysList(int $idEmpresa, bool $esAdmin)
    {
        $conboys = DB::table('conboys')
            ->select(
                'conboys.id',
                'conboys.no_conboy',
                'conboys.nombre',
                'conboys.fecha_inicio',
                'conboys.fecha_fin',
                'conboys.user_id',
                'tipo_disolucion',
                'estatus',
                'fecha_disolucion',
                'geocerca_lat',
                'geocerca_lng',
                'geocerca_radio'
            )->where('estatus', '=', 'activo')
            ->when(!$esAdmin, function ($query) use ($idEmpresa) {
                $query->whereIn('conboys.id', function ($q) use ($idEmpresa) {
                    $q->select('conboys_contenedores.conboy_id')
                        ->from('conboys_contenedores')
                        ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')
                        ->where('docum_cotizacion.id_empresa', $idEmpresa);
                });
            })
            ->get();

        $conboyIds = $conboys->pluck('id');

        $contenedores = DB::table('conboys_contenedores')
            ->join('docum_cotizacion', 'docum_cotizacion.id', '=', 'conboys_contenedores.id_contenedor')
            ->whereIn('conboys_contenedores.conboy_id', $conboyIds)
            ->when(!$esAdmin, function ($query) use ($idEmpresa) {
                $query->where('docum_cotizacion.id_empresa', $idEmpresa);
            })
            ->select(
                'conboys_contenedores.conboy_id',
                'conboys_contenedores.id_contenedor',
                'conboys_contenedores.es_primero',
                'docum_cotizacion.num_contenedor'
            )
            ->get();

        $contenedoresAgrupados = $contenedores->groupBy('conboy_id');

        return $conboys->map(function ($conboy) use ($contenedoresAgrupados) {
            return [
                'id' => $conboy->id,
                'no_conboy' => $conboy->no_conboy,
                'nombre' => $conboy->nombre,
                'fecha_inicio' => $conboy->fecha_inicio,
                'fecha_fin' => $conboy->fecha_fin,
                'user_id' => $conboy->user_id,
                'tipo_disolucion' => $conboy->tipo_disolucion,
                'estatus' => $conboy->estatus,
                'fecha_disolucion' => $conboy->fecha_disolucion,
                'geocerca_lat' => $conboy->geocerca_lat,
                'geocerca_lng' => $conboy->geocerca_lng,
                'geocerca_radio' => $conboy->geocerca_radio,
                'contenedores' => $contenedoresAgrupados->get($conboy->id, [])
            ];
        });
    }

    /**
     * Crea un convoy y asocia sus contenedores dentro de transacción.
     */
    public function storeConvoy(array $data, array $items, $user): array
    {
        return DB::transaction(function () use ($data, $items, $user) {
            $userEmp = User::findOrFail($user->id);
            $userEmp->consecutivo_conboy += 1;
            $userEmp->save();

            $iniciales = self::obtenerIniciales($userEmp->name);
            $fecha = now()->format('dmy');
            $consecutivo_formateado = str_pad($userEmp->consecutivo_conboy, 5, '0', STR_PAD_LEFT);
            $no_conboy = $iniciales . $fecha . $consecutivo_formateado;

            $conboy = Conboys::create([
                'nombre' => $data['nombre'],
                'fecha_inicio' => $data['fecha_inicio'] ?? null,
                'fecha_fin' => $data['fecha_fin'] ?? null,
                'user_id' => $user->id,
                'no_conboy' => $no_conboy,
                'tipo_disolucion' => $data['tipo_disolucion'],
                'estatus' => 'Activo',
                'fecha_disolucion' => $data['fecha_disolucion'] ?? null,
                'geocerca_lat' => $data['geocerca_lat'] ?? null,
                'geocerca_lng' => $data['geocerca_lng'] ?? null,
                'geocerca_radio' => $data['geocerca_radio'] ?? null
            ]);

            if ($conboy && !empty($items)) {
                foreach ($items as $item) {
                    $esPrimero = !conboysContenedores::where('conboy_id', $conboy->id)->exists();
                    $parts = explode('|', $item);
                    $id_contenedor = $parts[1] ?? null;
                    $imei = $parts[2] ?? null;

                    conboysContenedores::create([
                        'conboy_id' => $conboy->id,
                        'id_contenedor' => $id_contenedor,
                        'es_primero' => $esPrimero,
                        'usuario' => $user->id,
                        'imei' => $imei
                    ]);
                }
            }

            return [
                'success' => true,
                'message' => 'Conboy creado exitosamente.',
                'no_conboy' => $no_conboy,
            ];
        });
    }

    /**
     * Registra o actualiza la coordenada de seguimiento evitando duplicados.
     */
    public function guardarCoordenadasSeguimientos(array $data, $user): array
    {
        $latitud = (float) $data['latitud'];
        $longitud = (float) $data['longitud'];
        $ubicacionableType = $data['tipoRastreo'] ?? null;
        $ubicacionableId = (int) $data['ubicacionable_id'];
        $tipo = $data['tipo'];
        $idConvoy = $data['idProceso'] ?? null;
        $valorSolicitado = $data['valorSolicitado'] ?? null;
        $idCompaniaGps = $data['new_id'] ?? null;

        $metrosMinimosCambio = 15;
        $minutosMaximosSinGuardar = 30;

        $ultimoRegistro = CoordenadasHistorial::query()
            ->where('ubicacionable_id', $ubicacionableId)
            ->where('ubicacionable_type', $ubicacionableType)
            ->where('tipo', $tipo)
            ->when($idConvoy, fn($q) => $q->where('id_convoy', $idConvoy))
            ->when($valorSolicitado, fn($q) => $q->where('valorSolicitado', $valorSolicitado))
            ->when($idCompaniaGps, fn($q) => $q->where('id_compania_gps', $idCompaniaGps))
            ->latest('registrado_en')
            ->first();

        if ($ultimoRegistro) {
            $distanciaMetros = self::calcularDistanciaMetros(
                (float) $ultimoRegistro->latitud,
                (float) $ultimoRegistro->longitud,
                $latitud,
                $longitud
            );

            $mismoStatus = (string) ($ultimoRegistro->status_api ?? 0) === (string) ($data['status_api'] ?? 0);
            $mismoError = (string) ($ultimoRegistro->error_message ?? '') === (string) ($data['messageAp'] ?? '');
            $minutosDesdeUltimo = $ultimoRegistro->registrado_en
                ? now()->diffInMinutes($ultimoRegistro->registrado_en)
                : $minutosMaximosSinGuardar + 1;

            $esDuplicado =
                $distanciaMetros < $metrosMinimosCambio &&
                $mismoStatus &&
                $mismoError &&
                $minutosDesdeUltimo < $minutosMaximosSinGuardar;

            if ($esDuplicado) {
                return [
                    'success' => true,
                    'skipped' => true,
                    'message' => 'Coordenada duplicada, no se guardó nuevamente.',
                    'distancia_metros' => round($distanciaMetros, 2),
                    'ultimo_registro_id' => $ultimoRegistro->id,
                    'data' => $ultimoRegistro,
                ];
            }
        }

        $statusApi = $data['status_api'] ?? 0;
        $responseEncriptado = ($statusApi != 1 && isset($data['data']))
            ? AuditoriaCifrado::encrypt($data['data'])
            : null;

        $coordenada = CoordenadasHistorial::create([
            'latitud' => $latitud,
            'longitud' => $longitud,
            'registrado_en' => now(),
            'user_id' => $user->id,
            'ubicacionable_id' => $ubicacionableId,
            'ubicacionable_type' => $ubicacionableType,
            'tipo' => $tipo,
            'id_convoy' => $idConvoy,
            'status_api' => $statusApi,
            'id_compania_gps' => $idCompaniaGps,
            'tiempo_respuesta_ms' => $data['tiempo_respuesta_ms'] ?? null,
            'valorSolicitado' => $valorSolicitado,
            'response_json' => $responseEncriptado,
            'error_message' => $data['messageAp'] ?? null,
        ]);

        return [
            'success' => true,
            'skipped' => false,
            'message' => 'Coordenada registrada correctamente.',
            'data' => $coordenada,
        ];
    }

    public static function calcularDistanciaMetros(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $radioTierra = 6371000;
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
            cos($lat1Rad) * cos($lat2Rad) *
            sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
        return $radioTierra * $c;
    }

    public static function obtenerIniciales(string $nombreUser): string
    {
        $palabras = explode(' ', trim($nombreUser));
        $iniciales = '';
        foreach ($palabras as $p) {
            if (!empty($p)) {
                $iniciales .= strtoupper(substr($p, 0, 1));
            }
        }
        return substr($iniciales, 0, 3);
    }
}
