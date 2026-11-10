<?php

namespace App\Services;

use App\Models\Equipo;
use App\Models\GpsCompany;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Http\UploadedFile;

class EquipoService
{
    /**
     * Obtiene los tractocamiones / camiones activos de una empresa para reportería,
     * asignaciones y catálogo de imputación de gastos.
     */
    public function getTractocamiones(?int $idEmpresa = null): Collection
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        if (!$idEmpresa) {
            return new Collection();
        }

        return Equipo::where('id_empresa', $idEmpresa)
            ->where('tipo', 'Tractos / Camiones')
            ->where('activo', 1)
            ->orderBy('id_equipo', 'asc')
            ->get();
    }

    /**
     * Obtiene todos los equipos de una empresa por tipo opcional.
     */
    public function getEquiposPorEmpresa(?int $idEmpresa = null, ?string $tipo = null): Collection
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        if (!$idEmpresa) {
            return new Collection();
        }

        $query = Equipo::where('id_empresa', $idEmpresa);

        if ($tipo) {
            $query->where('tipo', $tipo);
        }
        $query->where('activo', 1);

        return $query->orderBy('id_equipo', 'asc')->get();
    }

    /**
     * Obtiene los datos agrupados para la vista principal de equipos (dolys, chasis, camiones).
     */
    public function getEquiposCatalogosIndex(?int $idEmpresa = null): array
    {
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;

        $equipos_dolys = Equipo::where('id_empresa', $idEmpresa)
            ->where('tipo', 'Dolys')
            ->where('activo', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $equipos_chasis = Equipo::where('id_empresa', $idEmpresa)
            ->where('tipo', 'Chasis / Plataforma')
            ->where('activo', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $equipos_camiones = Equipo::where('id_empresa', $idEmpresa)
            ->where('tipo', 'Tractos / Camiones')
            ->where('activo', true)
            ->orderBy('created_at', 'desc')
            ->get();

        $gps_companies = GpsCompany::all();

        return [
            'equipos_dolys' => $equipos_dolys,
            'equipos_chasis' => $equipos_chasis,
            'equipos_camiones' => $equipos_camiones,
            'gps_companies' => $gps_companies,
            'fechaActual' => date('Y-m-d')
        ];
    }

    /**
     * Registra un equipo (tracto, chasis o dolly) con sus archivos.
     */
    public function storeEquipo(array $data, array $files = [], ?int $userId = null, ?int $idEmpresa = null): ?Equipo
    {
        $userId = $userId ?? auth()->id();
        $idEmpresa = $idEmpresa ?? auth()->user()?->id_empresa;
        $equipo = new Equipo();

        if (!empty($data['marca'])) {
            $equipo->tipo = 'Tractos / Camiones';
            $equipo->folio = $data['folio'] ?? null;
            $equipo->id_equipo = $data['id_equipo'] ?? null;
            $equipo->marca = $data['marca'];
            $equipo->motor = $data['motor'] ?? null;
            $equipo->placas = $data['placas'] ?? null;
            $equipo->year = $data['year'] ?? null;
            $equipo->num_serie = $data['num_serie'] ?? null;
            $equipo->modelo = $data['modelo'] ?? null;
            $equipo->acceso = $data['acceso'] ?? null;
            $equipo->fecha = $data['fecha'] ?? null;
            $equipo->carga_diesel_al_final = $data['carga_diesel_al_final'] ?? 0;
            $equipo->activo = true;
            $equipo->id_empresa = $idEmpresa;
            $equipo->user_id = $userId;

            $this->procesarArchivosEquipo($equipo, $files, 'tarjeta_circulacion', 'poliza_seguro');
            $equipo->save();
            return $equipo;

        } elseif (!empty($data['marca_chasis'])) {
            $equipo->tipo = 'Chasis / Plataforma';
            $equipo->folio = $data['folio'] ?? null;
            $equipo->id_equipo = $data['id_equipo_chasis'] ?? null;
            $equipo->marca = $data['marca_chasis'];
            $equipo->motor = $data['motor_chasis'] ?? null;
            $equipo->placas = $data['placas_chasis'] ?? null;
            $equipo->year = $data['year_chasis'] ?? null;
            $equipo->num_serie = $data['num_serie_chasis'] ?? null;
            $equipo->modelo = $data['modelo_chasis'] ?? null;
            $equipo->acceso = $data['acceso_chasis'] ?? null;
            $equipo->fecha = $data['fecha_chasis'] ?? null;
            $equipo->activo = true;
            $equipo->id_empresa = $idEmpresa;
            $equipo->user_id = $userId;

            $this->procesarArchivosEquipo($equipo, $files, 'tarjeta_circulacion_chasis', 'poliza_seguro_chasis');
            $equipo->save();
            return $equipo;

        } elseif (!empty($data['marca_doly'])) {
            $equipo->tipo = 'Dolys';
            $equipo->folio = $data['folio'] ?? null;
            $equipo->id_equipo = $data['id_equipo_doly'] ?? null;
            $equipo->year = $data['year_doly'] ?? null;
            $equipo->marca = $data['marca_doly'];
            $equipo->placas = $data['placas_doly'] ?? null;
            $equipo->num_serie = $data['num_serie_doly'] ?? null;
            $equipo->modelo = $data['modelo_doly'] ?? null;
            $equipo->acceso = $data['acceso_doly'] ?? null;
            $equipo->fecha = $data['fecha_doly'] ?? null;
            $equipo->activo = true;
            $equipo->id_empresa = $idEmpresa;
            $equipo->user_id = $userId;

            $this->procesarArchivosEquipo($equipo, $files, 'tarjeta_circulacion_doly', 'poliza_seguro_doly');
            $equipo->save();
            return $equipo;
        }

        return null;
    }

    /**
     * Actualiza un equipo existente y sube nuevos archivos si fueron enviados.
     */
    public function updateEquipo(int $id, string $tipo, array $data, array $files = []): array
    {
        $equipo = Equipo::findOrFail($id);
        $updateData = [];

        if ($tipo === 'Tractos / Camiones') {
            $updateData = array_intersect_key($data, array_flip([
                'id_equipo', 'fecha', 'year', 'marca', 'modelo', 'placas',
                'num_serie', 'motor', 'acceso', 'carga_diesel_al_final'
            ]));
        } elseif ($tipo === 'Chasis / Plataforma') {
            $updateData = array_intersect_key($data, array_flip([
                'id_equipo', 'fecha', 'year', 'marca', 'modelo', 'placas',
                'num_serie', 'motor', 'acceso', 'folio'
            ]));
        } elseif ($tipo === 'Dolys') {
            $updateData = array_intersect_key($data, array_flip([
                'id_equipo', 'fecha', 'year', 'marca', 'modelo', 'placas',
                'num_serie', 'acceso'
            ]));
        } else {
            return [
                'success' => false,
                'message' => 'Tipo de equipo no reconocido.'
            ];
        }

        if (isset($files['tarjeta_circulacion']) && $files['tarjeta_circulacion'] instanceof UploadedFile) {
            $file = $files['tarjeta_circulacion'];
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move(public_path('/equipos'), $fileName);
            $updateData['tarjeta_circulacion'] = $fileName;
        }

        if (isset($files['poliza_seguro']) && $files['poliza_seguro'] instanceof UploadedFile) {
            $file = $files['poliza_seguro'];
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move(public_path('/equipos'), $fileName);
            $updateData['poliza_seguro'] = $fileName;
        }

        $equipo->update($updateData);

        return [
            'success' => true,
            'message' => 'Equipo actualizado con éxito'
        ];
    }

    /**
     * Activa o desactiva un equipo.
     */
    public function toggleActivo(int $id, string $tipoAccion): array
    {
        $equipo = Equipo::findOrFail($id);

        if ($tipoAccion === 'desactivado') {
            $equipo->activo = false;
        } elseif ($tipoAccion === 'activado') {
            $equipo->activo = true;
        }

        $equipo->save();

        return [
            'success' => true,
            'message' => $tipoAccion === 'desactivado' ? 'Equipo desactivado' : 'Equipo reactivado'
        ];
    }

    /**
     * Asigna compañía GPS e IMEI a un equipo.
     */
    public function asignarGps(int $id, ?int $gpsCompanyId, ?string $imei): void
    {
        $equipo = Equipo::findOrFail($id);
        $equipo->gps_company_id = $gpsCompanyId ?: null;
        $equipo->imei = $imei ?: null;
        $equipo->save();
    }

    /**
     * Obtiene los equipos con GPS y la lista de compañías GPS para la vista index-gps.
     */
    public function getEquiposGpsData($user): array
    {
        $userProveedores = User::find($user->id);
        $proveedorIds = $userProveedores ? $userProveedores->proveedores()->pluck('proveedor_id') : collect();

        $usuariosRelacionados = User::whereHas('proveedores', function ($q) use ($proveedorIds) {
            $q->whereIn('proveedor_id', $proveedorIds);
        })->pluck('id');

        $equipos = Equipo::whereIn('user_id', $usuariosRelacionados)
            ->leftJoin('gps_company', 'gps_company.id', '=', 'equipos.gps_company_id')
            ->where('equipos.activo', true)
            ->orderBy('equipos.created_at', 'desc')
            ->select([
                'equipos.*',
                'gps_company.nombre as gps_nombre',
                'gps_company.id as gps_company_id_join'
            ])
            ->get();

        $duplicatedImeis = Equipo::where('activo', true)
            ->whereNotNull('imei')
            ->where('imei', '!=', '')
            ->select('imei')
            ->groupBy('imei')
            ->havingRaw('count(*) > 1')
            ->pluck('imei')
            ->toArray();

        $equipos = $equipos->map(function ($e) use ($duplicatedImeis) {
            $e->is_duplicated_imei = !empty($e->imei) && in_array($e->imei, $duplicatedImeis);

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

        $gps_companies = GpsCompany::all();

        return [
            'equipos' => $equipos,
            'gps_companies' => $gps_companies
        ];
    }

    /**
     * Registra o actualiza un equipo para proveedores MEP con validación de IMEI duplicado.
     */
    public function updateMepEquipo(array $data, ?int $userId = null): array
    {
        $userId = $userId ?? auth()->id();
        $imei = $data['imei'] ?? null;
        $equipoId = $data['equipo_id'] ?? null;

        if (!empty($imei)) {
            $query = Equipo::where('imei', $imei)->where('activo', true);
            if ($equipoId) {
                $query->where('id', '!=', $equipoId);
            }
            if ($query->exists()) {
                return [
                    'success' => false,
                    'status' => 422,
                    'message' => 'El IMEI ya se encuentra registrado en otro equipo activo.'
                ];
            }
        }

        if ($equipoId) {
            $equipo = Equipo::findOrFail($equipoId);
            $equipo->update([
                'id_equipo' => $data['numero_equipo'] ?? $equipo->id_equipo,
                'imei' => $imei,
                'marca' => $data['marca'] ?? $equipo->marca,
                'placas' => $data['placas'] ?? $equipo->placas,
                'tipo' => $data['tipo_equipo'] ?? $equipo->tipo,
                'num_serie' => $data['num_serie'] ?? $equipo->num_serie,
            ]);

            return [
                'success' => true,
                'status' => 200,
                'message' => 'Equipo Actualizado con exito'
            ];
        }

        Equipo::create([
            'id_equipo' => $data['numero_equipo'] ?? null,
            'imei' => $imei,
            'marca' => $data['marca'] ?? null,
            'placas' => $data['placas'] ?? null,
            'num_serie' => $data['num_serie'] ?? null,
            'tipo' => $data['tipo_equipo'] ?? null,
            'user_id' => $userId,
            'activo' => true
        ]);

        return [
            'success' => true,
            'status' => 200,
            'message' => 'Equipo Guardado con exito'
        ];
    }

    /**
     * Procesa y sube tarjeta de circulación y póliza de seguro.
     */
    protected function procesarArchivosEquipo(Equipo $equipo, array $files, string $tarjetaKey, string $polizaKey): void
    {
        if (isset($files[$tarjetaKey]) && $files[$tarjetaKey] instanceof UploadedFile) {
            $file = $files[$tarjetaKey];
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move(public_path('/equipos'), $fileName);
            $equipo->tarjeta_circulacion = $fileName;
        }

        if (isset($files[$polizaKey]) && $files[$polizaKey] instanceof UploadedFile) {
            $file = $files[$polizaKey];
            $fileName = uniqid() . $file->getClientOriginalName();
            $file->move(public_path('/equipos'), $fileName);
            $equipo->poliza_seguro = $fileName;
        }
    }

    /**
     * Devuelve el texto formateado estándar para selects (id_equipo - marca).
     */
    public function formatTextoSelect(Equipo $equipo): string
    {
        return $equipo->texto_select;
    }
}
