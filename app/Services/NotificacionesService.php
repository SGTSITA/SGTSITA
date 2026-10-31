<?php

namespace App\Services;
use App\Models\Empresas;
use App\Models\Notificacion;
use App\Models\NotificacionRegla;
use App\Models\NotificacionTipo;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class NotificacionesService
{
   public function getDataIndex(): array
    {
        return [
            'tipos' => $this->obtenerTipos(),
            'reglas' => $this->obtenerReglas(),
            'tiposSelect' => $this->obtenerTiposSelect(),
            'empresasSelect' => $this->obtenerEmpresasSelect(),
            'usuariosSelect' => $this->obtenerUsuariosSelect(),
        ];
    }

    public function obtenerTipos()
    {
        return NotificacionTipo::query()
            ->withCount('reglas')
            ->orderBy('nombre')
            ->get();
    }

    public function obtenerReglas()
    {
        return NotificacionRegla::query()
            ->with([
                'tipo:id,clave,nombre',
                'empresa:id,nombre',
                'usuarios:id,name,email',
            ])
            ->withCount('usuarios')
            ->latest()
            ->get();
    }

    public function obtenerTiposSelect()
    {
        return NotificacionTipo::query()
            ->where('activo', true)
            ->orderBy('nombre')
            ->get([
                'id',
                'clave',
                'nombre',
            ]);
    }

    public function obtenerEmpresasSelect()
    {
        return Empresas::query()
            ->orderBy('nombre')
            ->get([
                'id',
                'nombre',
            ]);
    }

    public function obtenerUsuariosSelect()
    {
        return User::query()
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'email',
            ]);
    }


    public function crearTipo(array $data): NotificacionTipo
    {
        return DB::transaction(function () use ($data) {
            return NotificacionTipo::create([
                'clave' => $this->normalizarClave($data['clave']),
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'activo' => $data['activo'] ?? true,
            ]);
        });
    }

    public function actualizarTipo(NotificacionTipo $tipo, array $data): NotificacionTipo
    {
        return DB::transaction(function () use ($tipo, $data) {
            $tipo->update([
                'clave' => $this->normalizarClave($data['clave']),
                'nombre' => $data['nombre'],
                'descripcion' => $data['descripcion'] ?? null,
                'activo' => $data['activo'] ?? true,
            ]);

            return $tipo->fresh();
        });
    }

    public function eliminarTipo(NotificacionTipo $tipo): bool
    {
        return DB::transaction(function () use ($tipo) {
            return (bool) $tipo->delete();
        });
    }

    public function toggleTipo(NotificacionTipo $tipo): NotificacionTipo
    {
        return DB::transaction(function () use ($tipo) {
            $tipo->update([
                'activo' => !$tipo->activo,
            ]);

            return $tipo->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | CRUD reglas
    |--------------------------------------------------------------------------
    */
    public function crearRegla(array $data): NotificacionRegla
    {
        return DB::transaction(function () use ($data) {
            return NotificacionRegla::create([
                'notificacion_tipo_id' => $data['notificacion_tipo_id'],
                'empresa_id' => $data['empresa_id'] ?? null,
                'notificar_empresa' => !empty($data['notificar_empresa']),
                'notificar_cliente' => !empty($data['notificar_cliente']),
                'notificar_proveedor' => !empty($data['notificar_proveedor']),
                 'incluir_url_documento' => !empty($data['incluir_url_documento']),
                'activo' => $data['activo'] ?? true,
            ]);
        });
    }

    public function actualizarRegla(NotificacionRegla $regla, array $data): NotificacionRegla
    {
        return DB::transaction(function () use ($regla, $data) {
            $regla->update([
                'notificacion_tipo_id' => $data['notificacion_tipo_id'],
                'empresa_id' => $data['empresa_id'] ?? null,
                'notificar_empresa' => !empty($data['notificar_empresa']),
                'notificar_cliente' => !empty($data['notificar_cliente']),
                'notificar_proveedor' => !empty($data['notificar_proveedor']),
                'incluir_url_documento' => !empty($data['incluir_url_documento']),
                'activo' => $data['activo'] ?? true,
            ]);

            return $regla->fresh([
                'tipo',
                'empresa',
                'usuarios',
            ]);
        });
    }

    public function eliminarRegla(NotificacionRegla $regla): bool
    {
        return DB::transaction(function () use ($regla) {
            /*
             * Por la FK con cascade, se eliminan también los registros
             * de notificacion_regla_usuarios.
             */
            return (bool) $regla->delete();
        });
    }

    public function toggleRegla(NotificacionRegla $regla): NotificacionRegla
    {
        return DB::transaction(function () use ($regla) {
            $regla->update([
                'activo' => !$regla->activo,
            ]);

            return $regla->fresh();
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Usuarios asignados a reglas
    |--------------------------------------------------------------------------
    */
    public function asignarUsuarioRegla(int $reglaId, int $userId): void
    {
        DB::transaction(function () use ($reglaId, $userId) {
            $regla = NotificacionRegla::query()->findOrFail($reglaId);

            /*
             * syncWithoutDetaching evita duplicados desde Laravel.
             * Además tu DB ya tiene UNIQUE:
             * notif_regla_user_unique
             */
            $regla->usuarios()->syncWithoutDetaching([
                $userId => [
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        });
    }

    public function quitarUsuarioRegla(NotificacionRegla $regla, User $usuario): void
    {
        DB::transaction(function () use ($regla, $usuario) {
            $regla->usuarios()->detach($usuario->id);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Notificaciones reales del usuario
    |--------------------------------------------------------------------------
    */


    public function contarNoLeidas(?int $userId = null): int
    {
        $userId = $userId ?? Auth::id();

        return Notificacion::query()
            ->where('user_id', $userId)
            ->whereNull('leida_at')
            ->count();
    }

    public function marcarComoVista(int $id, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        return Notificacion::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->whereNull('vista_at')
            ->update([
                'vista_at' => now(),
            ]) > 0;
    }

    public function marcarComoLeida(int $id, ?int $userId = null): bool
    {
        $userId = $userId ?? Auth::id();

        return Notificacion::query()
            ->where('id', $id)
            ->where('user_id', $userId)
            ->update([
                'leida_at' => now(),
                'vista_at' => DB::raw('COALESCE(vista_at, NOW())'),
            ]) > 0;
    }

    public function marcarTodasComoLeidas(?int $userId = null): int
    {
        $userId = $userId ?? Auth::id();

        return Notificacion::query()
            ->where('user_id', $userId)
            ->whereNull('leida_at')
            ->update([
                'leida_at' => now(),
                'vista_at' => DB::raw('COALESCE(vista_at, NOW())'),
            ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Crear notificaciones por regla
    |--------------------------------------------------------------------------
    */
    public function notificarPorClave(
        string $clave,
        ?int $empresaId,
        ?int $proveedorId,
        ?int $clienteId,
        string $titulo,
        ?string $mensaje = null,
        ?Model $modelo = null,
        ?string $url = null,
        array $data = [],
        array $usuariosExtra = [],
        bool $evitarDuplicadoPendiente = true
    ): Collection {
        $tipo = NotificacionTipo::query()
            ->where('clave', $clave)
            ->where('activo', true)
            ->first();

            log::info("Buscando tipo de notificación: clave={$clave}, empresaId={$empresaId}, titulo={$titulo}, mensaje={$mensaje}, modelo_id={$modelo?->getKey()}, url={$url}, usuariosExtra=" . implode(',', $usuariosExtra) . ", evitarDuplicadoPendiente={$evitarDuplicadoPendiente}");

        if (!$tipo) {
            return collect();
        }

        $usuarios = $this->resolverUsuariosDestino(
            tipoId: $tipo->id,
            empresaId: $empresaId,
            usuariosExtra: $usuariosExtra,
            proveedorId: $proveedorId ?? null,
            clienteId: $clienteId ?? null,

        );

        log::info("Usuarios destinatarios resueltos: " . $usuarios->pluck('email')->implode(', '));

        if ($usuarios->isEmpty()) {
            return collect();
        }

        //validaremos el nuevo campo de enviar url del doc si aplica



                $reglasUrldoc = NotificacionRegla::where('notificacion_tipo_id', $tipo->id)
            ->where('activo', true)
            ->select("incluir_url_documento")
            ->first();
if(!$reglasUrldoc->incluir_url_documento) {
     $url= null;
}


        return DB::transaction(function () use (
            $usuarios,
            $tipo,
            $empresaId,
            $titulo,
            $mensaje,
            $modelo,
            $url,
            $data,
            $evitarDuplicadoPendiente
        ) {
            $notificaciones = collect();

            foreach ($usuarios as $usuario) {
                if ($evitarDuplicadoPendiente && $modelo) {
                    $existe = Notificacion::query()
                        ->where('user_id', $usuario->id)
                        ->where('notificacion_tipo_id', $tipo->id)
                        ->where('modelo_type', get_class($modelo))
                        ->where('modelo_id', $modelo->getKey())
                        ->whereNull('leida_at')
                        ->exists();

                    if ($existe) {
                        continue;
                    }
                }

                $notificaciones->push(
                    Notificacion::create([
                        'user_id' => $usuario->id,
                        'notificacion_tipo_id' => $tipo->id,
                        'empresa_id' => $empresaId,
                        'titulo' => $titulo,
                        'mensaje' => $mensaje,
                        'modelo_type' => $modelo ? get_class($modelo) : null,
                        'modelo_id' => $modelo?->getKey(),
                        'url' => $url,
                        'data' => $data ?: null,
                    ])
                );
            }

            return $notificaciones;
        });
    }

    private function resolverUsuariosDestino(
        int $tipoId,
        ?int $empresaId,
         array $usuariosExtra = [],
        ?int $proveedorId = null,
        ?int $clienteId = null
    ): Collection {
        $reglas = NotificacionRegla::query()
            ->with('usuarios:id,name,email')
            ->where('notificacion_tipo_id', $tipoId)
            ->where('activo', true)
            ->where(function ($query) use ($empresaId) {
                $query->whereNull('empresa_id');

                if ($empresaId) {
                    $query->orWhere('empresa_id', $empresaId);
                }
            })
            ->get();

            log::info("Reglas encontradas para tipo_id={$tipoId} y empresa_id={$empresaId}: " . $reglas->pluck('id')->implode(', '));

        $usuarios = collect();

        foreach ($reglas as $regla) {
                log::info("Procesando regla id={$regla->id}: notificar_empresa={$regla->notificar_empresa}, notificar_cliente={$regla->notificar_cliente},id_client={$clienteId}, notificar_proveedor={$regla->notificar_proveedor}, usuarios_asignados=" . $regla->usuarios->pluck('email')->implode(', '));
              if ($regla->notificar_empresa && $empresaId) {
                    $usuariosEmpresa = User::query()
                        ->whereHas('Empresa', function ($query) use ($empresaId) {
                            $query->where('empresas.id', $empresaId);
                        })
                        ->whereDoesntHave('proveedores')
                        ->where('users.id_cliente','=',0)
                        ->get([
                            'id',
                            'name',
                            'email',
                        ]);

                    Log::info("Usuarios internos asociados a la empresa_id={$empresaId}: " . $usuariosEmpresa->pluck('email')->implode(', '));

                    $usuarios = $usuarios->merge($usuariosEmpresa);
                }
                if ($regla->notificar_cliente && $clienteId) {
                    $usuariosCliente = User::query()
                        ->where('id_cliente', $clienteId)
                          ->where('users.id_cliente','!=',0)
                        ->get([
                            'id',
                            'name',
                            'email',
                        ]);

                    log::info("Usuarios asociados al cliente_id={$clienteId}: " . $usuariosCliente->pluck('email')->implode(', '));

                    $usuarios = $usuarios->merge($usuariosCliente);
                }
                if ($regla->notificar_proveedor && $proveedorId) {
                    $usuariosProveedor = User::query()
                        ->whereHas('proveedores', function ($query) use ($proveedorId) {
                            $query->where('proveedores.id', $proveedorId);
                        })
                        ->get([
                            'id',
                            'name',
                            'email',
                        ]);

                    Log::info("Usuarios asociados al proveedor_id={$proveedorId}: " . $usuariosProveedor->pluck('email')->implode(', '));

                    $usuarios = $usuarios->merge($usuariosProveedor);
                }


            $usuarios = $usuarios->merge($regla->usuarios);
        }

        if (!empty($usuariosExtra)) {
            $extras = User::query()
                ->whereIn('id', $usuariosExtra)
                ->get([
                    'id',
                    'name',
                    'email',
                ]);

            $usuarios = $usuarios->merge($extras);
        }

        return $usuarios
            ->filter()
            ->unique('id')
            ->values();
    }


    private function normalizarClave(string $clave): string
    {
        return Str::of($clave)
            ->lower()
            ->replace(' ', '_')
            ->replace('-', '_')
            ->replace('__', '_')
            ->trim('_')
            ->toString();
    }
public function notificarDocumentoSubido(
    string $urlRepo,
    ?int $empresaId,
    ?int $id_cliente,
    ?int $id_proveedor,
    string $nombreArchivo,
    ?Model $modelo = null,
    ?string $url = null,
    array $data = []
): Collection {
    $clave = $this->claveDocumentoSubido($urlRepo);
 log::info("inicio : clave={$clave}, empresaId={$empresaId}, nombreArchivo={$nombreArchivo}, urlRepo={$urlRepo}");
    if (!$clave) {
        return collect();
    }

    $nombreDocumento = $this->nombreDocumento($urlRepo);

    $titulo = 'Documento subido: ' . $nombreDocumento;

    $mensaje = 'Se subió el documento ' . $nombreDocumento;

    if (!empty($data['contenedor'])) {
        $mensaje .= ' para el contenedor ' . $data['contenedor'];
    }

    log::info("Notificando documento subido: clave={$clave}, empresaId={$empresaId}, nombreArchivo={$nombreArchivo}, urlRepo={$urlRepo}, titulo={$titulo}, mensaje={$mensaje}");
    return $this->notificarPorClave(
        clave: $clave,
        empresaId: $empresaId,
        clienteId: $id_cliente ?? null,
        proveedorId: $id_proveedor ?? null,
        titulo: $titulo,
        mensaje: $mensaje,
        modelo: $modelo,
        url: $url,
        data: array_merge($data, [
            'urlRepo' => $urlRepo,
            'documento' => $nombreDocumento,
            'archivo' => $nombreArchivo,
        ])
    );
}


private function nombreDocumento(string $urlRepo): string
{
    return match ($urlRepo) {
        'BoletaLib' => 'Boleta de liberación',
        'Doda' => 'DODA',
        'CartaPorte', 'CCP' => 'CCP / Carta Porte',
        'PreAlta' => 'Pre Alta',
        'CartaPortePDF' => 'Carta Porte PDF',
        'CartaPorteXML' => 'Carta Porte XML',
        'EIR' => 'EIR',
        'BoletaPatio' => 'Boleta Patio',
        'EvidenciaDescarga' => 'Evidencia de descarga',
        default => $urlRepo,
    };
}
    public function claveDocumentoSubido(string $urlRepo): ?string
{
    return match ($urlRepo) {
        'BoletaLib' => 'documento_boleta_lib_subida',
        'Doda' => 'documento_doda_subida',
        'CartaPorte', 'CCP' => 'documento_ccp_subida',
        'PreAlta' => 'documento_pre_alta_subida',
        'CartaPortePDF' => 'documento_carta_porte_pdf_subida',
        'CartaPorteXML' => 'documento_carta_porte_xml_subida',
        'EIR' => 'documento_eir_subida',
        'BoletaPatio' => 'documento_boleta_patio_subida',
        'EvidenciaDescarga' => 'documento_evidencia_descarga_subida',
        default => null,
    };
}



public function listarUsuario(?int $userId = null, int $limit = 10)
{
    $userId = $userId ?? auth()->id();

    return Notificacion::query()
        ->with('tipo:id,clave,nombre')
        ->where('user_id', $userId)
        ->latest()
        ->limit($limit)
        ->get()
        ->map(function ($notificacion) {
            return [
                'id' => $notificacion->id,
                'titulo' => $notificacion->titulo,
                'mensaje' => $notificacion->mensaje,
                'url' => $notificacion->url,
                'data' => $notificacion->data,
                'tipo' => $notificacion->tipo?->nombre,
                'tipo_clave' => $notificacion->tipo?->clave,
                'leida' => !is_null($notificacion->leida_at),
                'leida_at' => $notificacion->leida_at,
                'vista_at' => $notificacion->vista_at,
                'created_at' => $notificacion->created_at?->format('Y-m-d H:i:s'),
                'created_at_humano' => $notificacion->created_at?->diffForHumans(),
            ];
        });
}

public function listarUsuarioCompleto(?int $userId = null)
{
    $userId = $userId ?? auth()->id();

    return Notificacion::query()
        ->with('tipo:id,clave,nombre')
        ->where('user_id', $userId)
        ->latest()
        ->paginate(25);
}




public function marcarComoLeidaUsuario(Notificacion $notificacion, int $userId): bool
{
    if ((int) $notificacion->user_id !== (int) $userId) {
        return false;
    }

    return $notificacion->update([
        'leida_at' => $notificacion->leida_at ?? now(),
        'vista_at' => $notificacion->vista_at ?? now(),
    ]);
}

}
