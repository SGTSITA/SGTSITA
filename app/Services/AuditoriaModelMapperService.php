<?php

namespace App\Services;

class AuditoriaModelMapperService
{
    /**
     * Definición centralizada de módulos y su mapeo a modelos Eloquent en activity_log.
     */
    protected static array $catalogoModulos = [
        'planeacion' => [
            'etiqueta'   => 'Planeación de Viajes (Asignaciones)',
            'categoria'  => 'Operación y Tráfico',
            'modelos_bd' => ['Asignaciones'],
            'activo'     => true,
        ],
        'cotizaciones' => [
            'etiqueta'   => 'Cotizaciones y Viajes (Cotizaciones)',
            'categoria'  => 'Operación y Tráfico',
            'modelos_bd' => ['Cotizaciones'],
            'activo'     => true,
        ],
        'documentos' => [
            'etiqueta'   => 'Documentación de Viajes (DocumCotizacion)',
            'categoria'  => 'Operación y Tráfico',
            'modelos_bd' => ['DocumCotizacion', 'DocumCotizacionAcceso'],
            'activo'     => true,
        ],
        'costos' => [
            'etiqueta'   => 'Costos de Viajes (ViajesCostos)',
            'categoria'  => 'Operación y Tráfico',
            'modelos_bd' => ['ViajesCostos'],
            'activo'     => true,
        ],
        'bancos_v2' => [
            'etiqueta'   => 'Bancos V2 (Movimientos Bancarios)',
            'categoria'  => 'Finanzas y Tesorería',
            'modelos_bd' => ['CatBancoCuentasMovimientos'],
            'activo'     => true,
        ],
        'cxc_cxp' => [
            'etiqueta'   => 'Cobros y Pagos de Viajes (CXC / CXP)',
            'categoria'  => 'Finanzas y Tesorería',
            'modelos_bd' => ['CobroPago', 'CobroPagoCotizacion'],
            'activo'     => true,
        ],
        'gastos' => [
            'etiqueta'   => 'Gastos Unificados (Gastos, Imputaciones y Pagos)',
            'categoria'  => 'Finanzas y Tesorería',
            'modelos_bd' => ['Gasto', 'GastoImputacion', 'GastoPago', 'GastoPartida'],
            'activo'     => true,
        ],
        'liquidaciones' => [
            'etiqueta'   => 'Liquidaciones y Ajustes (Operadores)',
            'categoria'  => 'Finanzas y Tesorería',
            'modelos_bd' => ['Liquidaciones', 'LiquidacionAjuste', 'LiquidacionAjusteDetalle'],
            'activo'     => true,
        ],
        'catalogos' => [
            'etiqueta'   => 'Catálogos (Clientes, Proveedores, Operadores, Equipos)',
            'categoria'  => 'Catálogos Base',
            'modelos_bd' => ['Client', 'ClientEmpresa', 'Subclientes', 'Proveedor', 'Operador', 'Equipo', 'User'],
            'activo'     => true,
        ],
        'historicos' => [
            'etiqueta'   => 'Históricos / Descontinuados (Bancos V1, Gastos V1, Planeación V1)',
            'categoria'  => 'Histórico / Descontinuado',
            'modelos_bd' => ['Bancos', 'BancoDinero', 'BancoDineroOpe', 'GastosOperadores', 'GastosGenerales', 'GastosExtras', 'Planeacion'],
            'activo'     => false,
        ],
    ];

    /**
     * Mapeo inverso de nombre de modelo a etiqueta amigable.
     */
    protected static array $etiquetasModelos = [
        'Asignaciones'               => 'Planeación de Viaje',
        'Cotizaciones'               => 'Cotización / Viaje',
        'DocumCotizacion'            => 'Documentos de Viaje',
        'DocumCotizacionAcceso'      => 'Acceso a Documentos',
        'ViajesCostos'               => 'Costos de Viaje',
        'CatBancoCuentasMovimientos' => 'Movimiento Bancario (V2)',
        'CobroPago'                  => 'Cobro / Pago (CXC / CXP)',
        'CobroPagoCotizacion'        => 'Partida Cobro / Pago',
        'Gasto'                      => 'Gasto Unificado',
        'GastoImputacion'            => 'Imputación de Gasto',
        'GastoPago'                  => 'Pago de Gasto (CXP)',
        'GastoPartida'               => 'Partida de Gasto',
        'Liquidaciones'              => 'Liquidación de Operador',
        'LiquidacionAjuste'          => 'Ajuste de Liquidación',
        'LiquidacionAjusteDetalle'   => 'Detalle Ajuste Liquidación',
        'Client'                     => 'Cliente',
        'ClientEmpresa'              => 'Cliente - Empresa',
        'Subclientes'                => 'Subcliente',
        'Proveedor'                  => 'Proveedor / Transportista',
        'Operador'                   => 'Operador',
        'Equipo'                     => 'Unidad / Tracto',
        'User'                       => 'Usuario del Sistema',
        'Configuracion'              => 'Configuración',
        'Bancos'                     => 'Bancos (V1 Histórico)',
        'BancoDinero'                => 'Dinero Banco (V1 Histórico)',
        'BancoDineroOpe'             => 'Dinero Operador (V1 Histórico)',
        'GastosOperadores'           => 'Gastos Operador (V1 Histórico)',
        'GastosGenerales'            => 'Gastos Generales (V1 Histórico)',
        'GastosExtras'               => 'Gastos Extras (V1 Histórico)',
        'Planeacion'                 => 'Planeación (V1 Histórico)',
    ];

    /**
     * Devuelve los módulos agrupados por categoría para renderizar en un <select> con <optgroup>.
     */
    public static function getModulosAgrupados(): array
    {
        $agrupados = [];

        foreach (self::$catalogoModulos as $clave => $item) {
            $agrupados[$item['categoria']][$clave] = $item['etiqueta'];
        }

        return $agrupados;
    }

    /**
     * Resuelve los nombres de modelos de BD a partir de una o varias claves de módulo seleccionadas.
     * Si no se especifica ninguna o se incluye 'todos', retorna un array vacío (sin filtro).
     *
     * @param string|array|null $claves
     * @return array
     */
    public static function getModelosPorClaves($claves): array
    {
        if (empty($claves)) {
            return [];
        }

        if (is_string($claves)) {
            $claves = explode(',', $claves);
        }

        $claves = array_map('trim', (array) $claves);

        if (in_array('todos', $claves, true) || in_array('', $claves, true)) {
            return [];
        }

        $modelos = [];

        foreach ($claves as $clave) {
            if (isset(self::$catalogoModulos[$clave])) {
                $modelos = array_merge($modelos, self::$catalogoModulos[$clave]['modelos_bd']);
            } elseif (in_array($clave, array_keys(self::$etiquetasModelos), true)) {
                // Soporte por si se pasa directamente el nombre de un modelo específico
                $modelos[] = $clave;
            }
        }

        return array_unique($modelos);
    }

    /**
     * Obtiene la etiqueta funcional y amigable para un modelo de base de datos.
     * Ejemplo: 'Asignaciones' => 'Planeación de Viaje (Asignaciones)'
     */
    public static function getEtiquetaAmigable(?string $modelo): string
    {
        if (empty($modelo)) {
            return 'General';
        }

        $nombre = self::$etiquetasModelos[$modelo] ?? $modelo;

        return "{$nombre} ({$modelo})";
    }
}
