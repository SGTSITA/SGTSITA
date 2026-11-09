<?php

namespace App\Services;

use App\Models\Asignaciones;
use App\Models\Cotizaciones;
use App\Models\DocumCotizacion;
use App\Models\Empresas;
use App\Models\Equipo;
use App\Models\Operador;
use App\Models\Proveedor;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MepService
{
    /**
     * Obtiene catálogos de unidades y operadores vinculados a los proveedores del usuario MEP.
     */
    public function getCatalogosMep($user): array
    {
        $userProveedores = User::find($user->id);
        $proveedorIds = $userProveedores ? $userProveedores->proveedores()->pluck('proveedor_id')->toArray() : [];

        $usuariosRelacionados = [$user->id];
        if (!empty($proveedorIds)) {
            $relacionados = User::whereHas('proveedores', function ($q) use ($proveedorIds) {
                $q->whereIn('proveedor_id', $proveedorIds);
            })->pluck('id')->toArray();
            $usuariosRelacionados = array_unique(array_merge($usuariosRelacionados, $relacionados));
        }

        $unidades = Equipo::whereIn('user_id', $usuariosRelacionados)
            ->where('equipos.activo', true)
            ->orderBy('equipos.created_at', 'desc')
            ->get();

        $operadores = Operador::whereHas('proveedores', function ($q) use ($proveedorIds) {
            $q->whereIn('proveedor_id', $proveedorIds)
              ->where('proveedor_operador.estado', 1);
        })->get();

        return [
            'TMensaje' => 'success',
            'unidades' => $unidades,
            'operadores' => $operadores
        ];
    }

    /**
     * Obtiene listado de viajes aprobados asignados al proveedor MEP.
     */
    public function getCotizacionesList(int $idEmpresa)
    {
        $empresa = Empresas::find($idEmpresa);
        if (!$empresa) {
            return collect();
        }

        $proveedor = Proveedor::catalogoPrincipal()->where('rfc', $empresa->rfc)->pluck('id');
        $contenedoresAsignados = Asignaciones::whereIn('id_proveedor', $proveedor)->pluck('id_contenedor');

        return Cotizaciones::whereIn('id', $contenedoresAsignados)
            ->where('estatus', '=', 'Aprobada')
            ->where('estatus_planeacion', '=', 1)
            ->where('jerarquia', '!=', 'Secundario')
            ->orderBy('created_at', 'desc')
            ->with(['cliente', 'DocCotizacion.Asignaciones'])
            ->get()
            ->map(function ($cotizacion) {
                $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';

                if (!is_null($cotizacion->referencia_full)) {
                    $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                        ->where('jerarquia', 'Secundario')
                        ->with('DocCotizacion.Asignaciones')
                        ->first();

                    if ($secundaria && $secundaria->DocCotizacion) {
                        $contenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
                    }
                }

                return [
                    'id' => $cotizacion->id,
                    'cliente' => $cotizacion->cliente ? $cotizacion->cliente->nombre : 'N/A',
                    'origen' => $cotizacion->origen,
                    'destino' => $cotizacion->destino,
                    'contenedor' => $contenedor,
                    'estatus' => $cotizacion->estatus,
                    'coordenadas' => optional($cotizacion->DocCotizacion)->Asignaciones ? 'Ver' : '',
                    'id_asignacion' => optional($cotizacion->DocCotizacion)->Asignaciones->id ?? null,
                    'edit_url' => route('edit.cotizaciones', $cotizacion->id),
                    'tipo' => (!is_null($cotizacion->referencia_full)) ? 'Full' : 'Sencillo'
                ];
            });
    }

    /**
     * Obtiene listado de viajes finalizados asignados al proveedor MEP.
     */
    public function getCotizacionesFinalizadas(int $idEmpresa)
    {
        $empresa = Empresas::find($idEmpresa);
        if (!$empresa) {
            return collect();
        }

        $proveedor = Proveedor::catalogoPrincipal()->where('rfc', $empresa->rfc)->pluck('id');
        $contenedoresAsignados = Asignaciones::whereIn('id_proveedor', $proveedor)->pluck('id_contenedor');

        return Cotizaciones::whereIn('id', $contenedoresAsignados)
            ->where('estatus', 'Finalizado')
            ->where('jerarquia', '!=', 'Secundario')
            ->orderBy('created_at', 'desc')
            ->with(['Cliente', 'DocCotizacion.Asignaciones'])
            ->get()
            ->map(function ($cotizacion) {
                $contenedor = $cotizacion->DocCotizacion ? $cotizacion->DocCotizacion->num_contenedor : 'N/A';

                if (!is_null($cotizacion->referencia_full)) {
                    $secundaria = Cotizaciones::where('referencia_full', $cotizacion->referencia_full)
                        ->where('jerarquia', 'Secundario')
                        ->with('DocCotizacion.Asignaciones')
                        ->first();

                    if ($secundaria && $secundaria->DocCotizacion) {
                        $contenedor .= ' ' . $secundaria->DocCotizacion->num_contenedor;
                    }
                }

                return [
                    'id' => $cotizacion->id,
                    'cliente' => $cotizacion->Cliente ? $cotizacion->Cliente->nombre : 'N/A',
                    'origen' => $cotizacion->origen,
                    'destino' => $cotizacion->destino,
                    'contenedor' => $contenedor,
                    'estatus' => $cotizacion->estatus,
                    'coordenadas' => optional($cotizacion->DocCotizacion)->Asignaciones ? 'Ver' : 'N/A',
                    'id_asignacion' => optional($cotizacion->DocCotizacion)->Asignaciones->id ?? null,
                    'edit_url' => route('edit.cotizaciones', $cotizacion->id),
                    'pdf_url' => route('pdf.cotizaciones', $cotizacion->id),
                    'tipo' => (!is_null($cotizacion->referencia_full)) ? 'Full' : 'Sencillo'
                ];
            });
    }

    /**
     * Valida, crea o actualiza equipos de empresa/proveedor MEP.
     */
    public function validarEquiposEmpresa($numUnidad, $imei, $placas, $serie, $provGps, $tipoEquipo, $idEquipoUnico = null, $user = null)
    {
        if (empty($numUnidad) && empty($idEquipoUnico)) {
            return null;
        }

        $numUnidad = !empty($numUnidad) ? strtoupper(trim($numUnidad)) : null;
        $imei = !empty($imei) ? strtoupper(trim($imei)) : null;
        $placas = !empty($placas) ? strtoupper(trim($placas)) : null;
        $serie = !empty($serie) ? strtoupper(trim($serie)) : null;

        $userId = $user ? $user->id : auth()->id();
        $userActual = User::find($userId);
        $proveedorIds = $userActual ? $userActual->proveedores()->pluck('proveedor_id')->toArray() : [];

        $usuariosRelacionados = [$userId];
        if (!empty($proveedorIds)) {
            $relacionados = User::whereHas('proveedores', function ($q) use ($proveedorIds) {
                $q->whereIn('proveedor_id', $proveedorIds);
            })->pluck('id')->toArray();
            $usuariosRelacionados = array_unique(array_merge($usuariosRelacionados, $relacionados));
        }

        $unidad = null;

        if (!empty($idEquipoUnico)) {
            $unidad = Equipo::find($idEquipoUnico);
        }

        if (!$unidad) {
            $unidadQuery = Equipo::whereIn('user_id', $usuariosRelacionados);

            if (!empty($numUnidad)) {
                $unidad = (clone $unidadQuery)->where('id_equipo', $numUnidad)->first();
            }

            if (!$unidad && !empty($imei)) {
                $unidad = (clone $unidadQuery)->where('imei', $imei)->first();
            }
        }

        if (!$unidad) {
            $unidad = new Equipo();
            $unidad->id_empresa = $user ? $user->id_empresa : auth()->user()->id_empresa;
            $unidad->id_equipo = $numUnidad;
            $unidad->imei = $imei;
            $unidad->placas = $placas;
            $unidad->num_serie = $serie;
            $unidad->gps_company_id = $provGps;
            $unidad->tipo = $tipoEquipo;
            $unidad->user_id = $userId;
            $unidad->save();
        } else {
            if (!empty($imei)) $unidad->imei = $imei;
            if (!empty($placas)) $unidad->placas = $placas;
            if (!empty($serie)) $unidad->num_serie = $serie;
            if (!empty($provGps)) $unidad->gps_company_id = $provGps;
            $unidad->update();
        }

        return $unidad->id;
    }

    /**
     * Asigna operador y unidades a un viaje subcontratado MEP dentro de transacción.
     */
    public function asignarOperador(array $formData, $idCotizacion, $user): array
    {
        return DB::transaction(function () use ($formData, $idCotizacion, $user) {
            $planearViaje = $formData['planear'] ?? 0;
            $idOperador = $formData['mepOperador'] ?? null;
            $userModel = User::find($user->id);
            $proveedorIds = $userModel ? $userModel->proveedores()->pluck('proveedor_id') : collect();
            $idEmpresa = $user->id_empresa;

            if ($idOperador) {
                $operador = Operador::find($idOperador);
                if ($operador) {
                    $operador->nombre = $formData['txtOperador'] ?? $operador->nombre;
                    $operador->telefono = $formData['txtTelefono'] ?? $operador->telefono;
                    $operador->update();
                }
            } else {
                $operador = new Operador();
                $operador->nombre = $formData['txtOperador'];
                $operador->telefono = $formData['txtTelefono'] ?? null;
                $operador->id_empresa = $idEmpresa;
                $operador->save();
            }

            $idOperador = $operador->id;

            foreach ($proveedorIds as $proveedorId) {
                $existeRelacion = DB::table('proveedor_operador')
                    ->where('operador_id', $idOperador)
                    ->where('proveedor_id', $proveedorId)
                    ->exists();

                if (!$existeRelacion) {
                    DB::table('proveedor_operador')->insert([
                        'operador_id' => $idOperador,
                        'proveedor_id' => $proveedorId,
                        'empresa_id' => $idEmpresa,
                        'estado' => 1,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            $idunidad = $this->validarEquiposEmpresa(
                $formData['txtNumUnidad'] ?? null,
                $formData['txtImei'] ?? null,
                $formData['txtPlacas'] ?? null,
                $formData['txtSerie'] ?? null,
                $formData['selectGPS'] ?? null,
                'Tractos / Camiones',
                $formData['id_equipo_unico'] ?? null,
                $user
            );

            $idChasisA = $this->validarEquiposEmpresa(
                $formData['txtNumChasisA'] ?? null,
                $formData['txtImeiChasisA'] ?? null,
                $formData['txtPlacasA'] ?? null,
                '',
                $formData['selectChasisAGPS'] ?? null,
                'Chasis / Plataforma',
                $formData['id_chasis_a_unico'] ?? null,
                $user
            );

            $idChasisB = $this->validarEquiposEmpresa(
                $formData['txtNumChasisB'] ?? null,
                $formData['txtImeiChasisB'] ?? null,
                $formData['txtPlacasB'] ?? null,
                '',
                $formData['selectChasisBGPS'] ?? null,
                'Chasis / Plataforma',
                $formData['id_chasis_b_unico'] ?? null,
                $user
            );

            $documento = DocumCotizacion::where('id_cotizacion', $idCotizacion)->first();
            if (!$documento) {
                throw new \DomainException("No se encontró el documento de cotización con ID: {$idCotizacion}");
            }

            $asignacion = Asignaciones::where('id_contenedor', $documento->id)->first();

            $fechaI = $formData['txtFechaInicio'] ?? null;
            $fechaF = $formData['txtFechaFinal'] ?? null;

            if (empty($fechaI)) {
                $fechaBaseInicio = $asignacion?->fecha_inicio ?? now();
                $fechaInicio = Carbon::parse($fechaBaseInicio)->startOfDay();
            } else {
                $fechaInicio = $this->parseFecha($fechaI)->startOfDay();
            }

            if (empty($fechaF)) {
                $fechaBaseFin = $asignacion?->fecha_fin ?? now();
                $fechaFin = Carbon::parse($fechaBaseFin)->endOfDay();
            } else {
                $fechaFin = $this->parseFecha($fechaF)->endOfDay();
            }

            $proveedorid = $formData['cmbProveedor'] ?? null;

            if ($asignacion) {
                $asignacion->update([
                    "id_operador" => $idOperador,
                    "id_camion" => $idunidad,
                    "id_chasis" => $idChasisA,
                    "id_chasis2" => $idChasisB,
                    "fecha_inicio" => $fechaInicio,
                    "fecha_fin" => $fechaFin,
                    "fehca_inicio_guard" => $fechaInicio,
                    "fehca_fin_guard" => $fechaFin,
                    "id_proveedor" => $proveedorid,
                    'tipo_contrato' => 'Subcontratado',
                ]);
                $titulo = 'Actualizado correctamente';
                $mensaje = 'Los datos fueron modificados con éxito';
            } else {
                $asignacion = new Asignaciones();
                $asignacion->id_empresa = $documento->Cotizacion?->id_empresa ?? $idEmpresa;
                $asignacion->id_contenedor = $documento->id;
                $asignacion->id_camion = $idunidad;
                $asignacion->id_chasis = $idChasisA;
                $asignacion->id_chasis2 = $idChasisB;
                $asignacion->id_operador = $idOperador;
                $asignacion->fecha_inicio = $fechaInicio;
                $asignacion->fecha_fin = $fechaFin;
                $asignacion->fehca_inicio_guard = $fechaInicio;
                $asignacion->fehca_fin_guard = $fechaFin;
                $asignacion->id_proveedor = $proveedorid;
                $asignacion->tipo_contrato = 'Subcontratado';
                $asignacion->save();

                $titulo = 'Se ha realizado la asignación correctamente';
                $mensaje = '';
            }

            if ($planearViaje == 1) {
                Cotizaciones::where('id', $documento->id_cotizacion)->update(['estatus_planeacion' => 1]);
                $titulo = 'Datos guardados correctamente';
                $mensaje = 'Viaje planeado con éxito';
            }

            return [
                "TMensaje" => "success",
                "Titulo" => $titulo,
                "Mensaje" => $mensaje
            ];
        });
    }

    /**
     * Consulta el detalle de asignación para modal MEP.
     */
    public function verAsignacion($idCotizacion)
    {
        $documento = DocumCotizacion::where('id_cotizacion', $idCotizacion)->first();
        if (!$documento) {
            return collect();
        }

        return Asignaciones::with([
            'Camion.gps',
            'Chasis.gps',
            'Chasis2.gps',
            'Operador',
            'Contenedor' => function ($q) {
                $q->select('id', 'id_cotizacion');
            },
            'Contenedor.Cotizacion' => function ($q) {
                $q->select('id', 'estatus', 'origen', 'destino', 'estatus_planeacion');
            }
        ])->where('id_contenedor', $documento->id)->get();
    }

    public function parseFecha($fecha)
    {
        try {
            if (str_contains($fecha, '/')) {
                return Carbon::createFromFormat('d/m/Y', $fecha);
            }
            return Carbon::createFromFormat('Y-m-d', $fecha);
        } catch (\Exception $e) {
            return Carbon::parse($fecha);
        }
    }
}
