<?php

namespace App\Services;

use App\Models\Bancos;
use App\Models\CatCuentaContable;
use App\Models\CategoriasGastos;
use App\Models\ContabilidadEntidadCuenta;
use App\Models\EmpresaCuentaConfig;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use InvalidArgumentException;

class CuentaContableResolverService
{
    // Roles Contables Estándar del ERP
    public const ROL_CAJA_OPERATIVA           = 'CAJA_OPERATIVA';
    public const ROL_BANCOS_DEFAULT           = 'BANCOS_DEFAULT';
    public const ROL_CLIENTES_CXC             = 'CLIENTES_CXC';
    public const ROL_PROVEEDORES_CXP          = 'PROVEEDORES_CXP';
    public const ROL_PRESTAMOS_OPERADORES     = 'PRESTAMOS_OPERADORES';
    public const ROL_ANTICIPOS_SOCIOS         = 'ANTICIPOS_SOCIOS';
    public const ROL_DEUDORES_DIVERSOS        = 'DEUDORES_DIVERSOS';
    public const ROL_PASIVO_SOCIOS            = 'PASIVO_SOCIOS';
    public const ROL_CAPITAL_PATRIMONIO       = 'CAPITAL_PATRIMONIO';
    public const ROL_RESULTADO_EJERCICIO      = 'RESULTADO_EJERCICIO';
    public const ROL_RESULTADO_ANTERIORES     = 'RESULTADO_ANTERIORES';
    public const ROL_INGRESOS_OPERATIVOS      = 'INGRESOS_OPERATIVOS';
    public const ROL_INGRESOS_INTERESES       = 'INGRESOS_INTERESES';
    public const ROL_GASTOS_ADMIN             = 'GASTOS_ADMIN';
    public const ROL_GASTOS_COMBUSTIBLE       = 'GASTOS_COMBUSTIBLE';
    public const ROL_GASTOS_CASETAS           = 'GASTOS_CASETAS';
    public const ROL_GASTOS_MANIOBRAS         = 'GASTOS_MANIOBRAS';
    public const ROL_GASTOS_MANTENIMIENTO     = 'GASTOS_MANTENIMIENTO';

    /**
     * Mapeo de fallbacks estándar CONAC en caso de ausencia de configuración en BD.
     */
    protected const CODIGOS_FALLBACK = [
        self::ROL_CAJA_OPERATIVA           => '1.1.1.1.001',
        self::ROL_BANCOS_DEFAULT           => '1.1.1.2.001',
        self::ROL_CLIENTES_CXC             => '1.1.2.1.001',
        self::ROL_PROVEEDORES_CXP          => '2.1.1.2.001',
        self::ROL_PRESTAMOS_OPERADORES     => '1.1.2.3.001',
        self::ROL_ANTICIPOS_SOCIOS         => '1.1.2.3.003',
        self::ROL_DEUDORES_DIVERSOS        => '1.1.2.3.004',
        self::ROL_PASIVO_SOCIOS            => '2.1.9.1.002',
        self::ROL_CAPITAL_PATRIMONIO       => '3.1.1.1.001',
        self::ROL_RESULTADO_EJERCICIO      => '3.2.1.1',
        self::ROL_RESULTADO_ANTERIORES     => '3.2.2.1',
        self::ROL_INGRESOS_OPERATIVOS      => '4.1.1.1',
        self::ROL_INGRESOS_INTERESES       => '4.3.1.1',
        self::ROL_GASTOS_ADMIN             => '5.1.3.1',
        self::ROL_GASTOS_COMBUSTIBLE       => '5.1.1.1',
        self::ROL_GASTOS_CASETAS           => '5.1.1.2',
        self::ROL_GASTOS_MANIOBRAS         => '5.1.1.3',
        self::ROL_GASTOS_MANTENIMIENTO     => '5.1.2.1',
    ];

    /**
     * Caché de resolución en memoria por ciclo de request.
     * key: "{idEmpresa}_{rolClave}" => CatCuentaContable
     */
    protected array $cacheRoles = [];

    /**
     * Caché de bancos resueltos.
     * key: "{idEmpresa}_{bancoId}" => int (cuenta_contable_id)
     */
    protected array $cacheBancos = [];

    /**
     * Resuelve el modelo CatCuentaContable configurado para el rol en la empresa.
     *
     * Cascada de resolución:
     * 1. Mapeo explícito de la empresa en cat_empresa_cuentas_config.
     * 2. Mapeo global del ERP (id_empresa IS NULL).
     * 3. Fallback CONAC por código base seguro.
     */
    public function resolverCuentaPorRol(string $rolClave, int $idEmpresa): CatCuentaContable
    {
        $cacheKey = "{$idEmpresa}_{$rolClave}";
        if (isset($this->cacheRoles[$cacheKey])) {
            return $this->cacheRoles[$cacheKey];
        }

        // 1. Buscar configuración específica de empresa o global
        $config = EmpresaCuentaConfig::with('cuentaContable')
            ->paraEmpresaORoot($idEmpresa)
            ->where('rol_clave', $rolClave)
            ->first();

        if ($config && $config->cuentaContable && $config->cuentaContable->activo) {
            $this->cacheRoles[$cacheKey] = $config->cuentaContable;
            return $config->cuentaContable;
        }

        // 2. Fallback estándar CONAC por código seguro
        $codigoFallback = self::CODIGOS_FALLBACK[$rolClave] ?? null;
        if ($codigoFallback) {
            $cuenta = CatCuentaContable::where('codigo', $codigoFallback)->first();

            // Si no existe la subcuenta exacta, buscar la primera cuenta afectable del rubro o padre
            if (!$cuenta) {
                $cuenta = CatCuentaContable::where('codigo', 'like', $codigoFallback . '%')
                    ->where('es_afectable', true)
                    ->first();
            }

            if ($cuenta) {
                $this->cacheRoles[$cacheKey] = $cuenta;
                return $cuenta;
            }
        }

        Log::error("No se pudo resolver la cuenta contable para el rol '{$rolClave}' en empresa ID {$idEmpresa}.");
        throw new InvalidArgumentException("No se encontró una cuenta contable configurada para el rol contable '{$rolClave}' (Empresa: {$idEmpresa}).");
    }

    /**
     * Resuelve únicamente el ID de la cuenta contable para un rol dado.
     */
    public function resolverIdPorRol(string $rolClave, int $idEmpresa): int
    {
        return $this->resolverCuentaPorRol($rolClave, $idEmpresa)->id;
    }

    /**
     * Resuelve la cuenta contable analítica de un Banco.
     *
     * Cascada:
     * 1. Cuenta contable vinculada directamente al banco (bancos.cuenta_contable_id).
     * 2. Cuenta vinculada en el catálogo maestro del banco (cat_bancos.cuenta_contable_id si existe).
     * 3. Mapeo por rol BANCOS_DEFAULT de la empresa.
     */
    public function resolverCuentaBanco(?int $bancoId, int $idEmpresa): int
    {
        $cacheKey = "{$idEmpresa}_" . ($bancoId ?? 'default');
        if (isset($this->cacheBancos[$cacheKey])) {
            return $this->cacheBancos[$cacheKey];
        }

        if ($bancoId) {
            // 0. Mapeo analítico asignado por el contador en el Centro de Mapeo
            $mapeoBanco = ContabilidadEntidadCuenta::paraEntidad($idEmpresa, 'bancos', $bancoId)->first();
            if ($mapeoBanco && !empty($mapeoBanco->cuenta_contable_id)) {
                $this->cacheBancos[$cacheKey] = (int) $mapeoBanco->cuenta_contable_id;
                return (int) $mapeoBanco->cuenta_contable_id;
            }

            $banco = Bancos::with('catBanco')->find($bancoId);
            if ($banco) {
                // 1. Directa del banco
                if (!empty($banco->cuenta_contable_id)) {
                    $this->cacheBancos[$cacheKey] = (int) $banco->cuenta_contable_id;
                    return (int) $banco->cuenta_contable_id;
                }

                // 2. Coincidencia por subcuenta con código CONAC si existe en catálogo
                $nombreBanco = strtoupper(($banco->catBanco?->nombre ?? '') . ' ' . ($banco->nombre_banco ?? '') . ' ' . ($banco->nombre ?? '') . ' ' . ($banco->banco ?? ''));
                $codigoSugerido = null;
                if (str_contains($nombreBanco, 'AZTECA')) {
                    $codigoSugerido = '1.1.1.2.002';
                } elseif (str_contains($nombreBanco, 'BANORTE')) {
                    $codigoSugerido = '1.1.1.2.005';
                } elseif (str_contains($nombreBanco, 'BANAMEX') || str_contains($nombreBanco, 'CITI')) {
                    $codigoSugerido = '1.1.1.2.003';
                } elseif (str_contains($nombreBanco, 'SANTANDER')) {
                    $codigoSugerido = '1.1.1.2.004';
                }

                if ($codigoSugerido) {
                    $cta = CatCuentaContable::where('codigo', $codigoSugerido)->first();
                    if ($cta) {
                        $this->cacheBancos[$cacheKey] = (int) $cta->id;
                        return (int) $cta->id;
                    }
                }
            }
        }

        // 3. Fallback al rol general de bancos de la empresa
        $cuentaDefault = $this->resolverCuentaPorRol(self::ROL_BANCOS_DEFAULT, $idEmpresa);
        $this->cacheBancos[$cacheKey] = (int) $cuentaDefault->id;
        return (int) $cuentaDefault->id;
    }

    /**
     * Resuelve la cuenta contable de Gasto Operativo o Administrativo.
     *
     * Cascada:
     * 1. Cuenta configurada directamente en la categoría del gasto (categorias_gastos.cuenta_contable_id).
     * 2. Rol por concepto operativo específico (Combustible, Casetas, Maniobras, Taller).
     * 3. Rol de Gastos Administrativos / Generales (ROL_GASTOS_ADMIN) de la empresa.
     */
    public function resolverCuentaGasto(?int $categoriaGastoId, int $idEmpresa, ?string $concepto = null): int
    {
        $conceptoStr = strtoupper($concepto ?? '');

        if ($categoriaGastoId) {
            $cat = CategoriasGastos::find($categoriaGastoId);
            if ($cat) {
                if (!empty($cat->cuenta_contable_id)) {
                    return (int) $cat->cuenta_contable_id;
                }
                $conceptoStr .= ' ' . strtoupper($cat->categoria ?? '');
            }
        }

        // Detección por concepto operativo a rol estándar
        if (str_contains($conceptoStr, 'DIESEL') || str_contains($conceptoStr, 'COMBUSTIBLE') || str_contains($conceptoStr, 'GASOLINA')) {
            return $this->resolverIdPorRol(self::ROL_GASTOS_COMBUSTIBLE, $idEmpresa);
        }

        if (str_contains($conceptoStr, 'CASETA') || str_contains($conceptoStr, 'PEAJE') || str_contains($conceptoStr, 'TAG')) {
            return $this->resolverIdPorRol(self::ROL_GASTOS_CASETAS, $idEmpresa);
        }

        if (str_contains($conceptoStr, 'MANIOBRA') || str_contains($conceptoStr, 'BURREO') || str_contains($conceptoStr, 'CARGA') || str_contains($conceptoStr, 'DESCARGA')) {
            return $this->resolverIdPorRol(self::ROL_GASTOS_MANIOBRAS, $idEmpresa);
        }

        if (str_contains($conceptoStr, 'TALLER') || str_contains($conceptoStr, 'MANTENIMIENTO') || str_contains($conceptoStr, 'LLANTA') || str_contains($conceptoStr, 'REFACCION')) {
            return $this->resolverIdPorRol(self::ROL_GASTOS_MANTENIMIENTO, $idEmpresa);
        }

        // Default: Gastos Administrativos y Generales
        return $this->resolverIdPorRol(self::ROL_GASTOS_ADMIN, $idEmpresa);
    }

    /**
     * Obtiene el conjunto de cuentas pertenecientes a uno o varios rubros contables.
     * Reemplaza las consultas SQL con LIKE '4%' o LIKE '5%'.
     *
     * @param string|array $rubros ej. 'ingresos' o ['costos', 'gastos']
     */
    public function obtenerCuentasPorRubros(string|array $rubros, ?int $idEmpresa = null): Collection
    {
        $listaRubros = is_array($rubros) ? $rubros : [$rubros];

        $query = CatCuentaContable::whereIn('rubro', $listaRubros)
            ->where('activo', true);

        if ($idEmpresa) {
            $query->where(function ($q) use ($idEmpresa) {
                $q->where('id_empresa', $idEmpresa)->orWhereNull('id_empresa');
            });
        }

        return $query->get();
    }

    /**
     * Resuelve la subcuenta analítica de un Cliente específico si fue desglosada por el contador,
     * o devuelve la cuenta colectiva ROL_CLIENTES_CXC por defecto.
     */
    public function resolverCuentaCliente(?int $clienteId, int $idEmpresa): CatCuentaContable
    {
        if ($clienteId) {
            $mapeo = ContabilidadEntidadCuenta::with('cuentaContable')
                ->paraEntidad($idEmpresa, 'clientes', $clienteId)
                ->first();

            if ($mapeo && $mapeo->cuentaContable && $mapeo->cuentaContable->activo) {
                return $mapeo->cuentaContable;
            }
        }

        return $this->resolverCuentaPorRol(self::ROL_CLIENTES_CXC, $idEmpresa);
    }

    /**
     * Resuelve la subcuenta analítica de un Proveedor específico si fue desglosada por el contador,
     * o devuelve la cuenta colectiva ROL_PROVEEDORES_CXP por defecto.
     */
    public function resolverCuentaProveedor(?int $proveedorId, int $idEmpresa): CatCuentaContable
    {
        if ($proveedorId) {
            $mapeo = ContabilidadEntidadCuenta::with('cuentaContable')
                ->paraEntidad($idEmpresa, 'proveedores', $proveedorId)
                ->first();

            if ($mapeo && $mapeo->cuentaContable && $mapeo->cuentaContable->activo) {
                return $mapeo->cuentaContable;
            }
        }

        return $this->resolverCuentaPorRol(self::ROL_PROVEEDORES_CXP, $idEmpresa);
    }

    /**
     * Crea automáticamente una subcuenta analítica consecutiva bajo una cuenta de mayor CONAC
     * y le asigna la jerarquía, rubro y naturaleza heredada.
     */
    public function crearSubcuentaAnalitica(string $codigoMayor, string $nombre, int $idEmpresa): CatCuentaContable
    {
        $padre = CatCuentaContable::where('codigo', $codigoMayor)->first();
        if (!$padre) {
            throw new InvalidArgumentException("No se encontró la cuenta de mayor con código {$codigoMayor}.");
        }

        // Buscar subcuentas existentes bajo este prefijo
        $subcuentas = CatCuentaContable::where('codigo', 'like', "{$codigoMayor}.%")
            ->where(function ($q) use ($idEmpresa) {
                $q->whereNull('id_empresa')->orWhere('id_empresa', $idEmpresa);
            })
            ->get();

        $maxConsecutivo = 0;
        foreach ($subcuentas as $s) {
            // Extraer el último bloque numérico
            if (preg_match('/\.(\d+)$/', $s->codigo, $matches)) {
                $num = (int) $matches[1];
                if ($num > $maxConsecutivo) {
                    $maxConsecutivo = $num;
                }
            }
        }

        $siguiente = max(1, $maxConsecutivo + 1);
        $siguienteCodigo = $codigoMayor . '.' . str_pad($siguiente, 3, '0', STR_PAD_LEFT);

        return CatCuentaContable::create([
            'id_empresa'       => $idEmpresa,
            'codigo'           => $siguienteCodigo,
            'nombre'           => trim($nombre),
            'nivel'            => 5,
            'cuenta_padre_id'  => $padre->id,
            'rubro'            => $padre->rubro,
            'naturaleza'       => $padre->naturaleza,
            'es_afectable'     => true,
            'activo'           => true,
        ]);
    }

    /**
     * Limpia la memoria interna de la instancia (útil para pruebas o tareas batch).
     */
    public function limpiarCache(): void
    {
        $this->cacheRoles = [];
        $this->cacheBancos = [];
    }
}
