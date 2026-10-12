<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Bancos;
use App\Models\CatCuentaContable;
use App\Models\CobroPagoCotizacion;
use App\Models\Cotizaciones;
use App\Models\Empresas;
use App\Models\Gasto;
use App\Models\GastoPago;
use App\Models\Prestamo;
use App\Models\Poliza;
use App\Services\CotizacionesService;
use App\Services\PolizaService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ContabilidadSyncHistoricoCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contabilidad:sync-historico 
                            {--empresa= : ID de la empresa a procesar (opcional, por defecto todas)}
                            {--force : Forzar regeneración de pólizas que ya existen}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza y genera pólizas contables históricas de bancos, cotizaciones aprobadas, gastos y préstamos';

    public const EMPRESAS_PERMITIDAS = [7, 8, 6, 9, 27, 2]; // GC, H10-OLIVA, H6-ELPE, H11-YOJO, H20-TERA, H7-YOALPE

    public function __construct(
        protected PolizaService $polizaService,
        protected CotizacionesService $cotizacionesService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info("================================================================================");
        $this->info("   SGTSITA - SINCRONIZADOR HISTÓRICO DE CONTABILIDAD Y PÓLIZAS AUTOMÁTICAS      ");
        $this->info("================================================================================");

        $empresaFiltro = $this->option('empresa');
        $force = (bool) $this->option('force');

        $empresasQuery = Empresas::query();
        if ($empresaFiltro) {
            $empresasQuery->where('id', $empresaFiltro);
        } else {
            $empresasQuery->whereIn('id', self::EMPRESAS_PERMITIDAS);
        }
        $empresas = $empresasQuery->get();

        if ($empresas->isEmpty()) {
            $this->error("No se encontraron empresas para procesar.");
            return Command::FAILURE;
        }

        $resumen = [
            'aperturas'        => 0,
            'cotizaciones'     => 0,
            'cobros_cxc'       => 0,
            'cxp_provisiones'  => 0,
            'cxp_pagos'        => 0,
            'gastos_deveng'    => 0,
            'gastos_pagos'     => 0,
            'prestamos'        => 0,
            'omitidos'         => 0,
            'errores'          => 0,
        ];

        foreach ($empresas as $empresa) {
            $this->line("");
            $this->info("Procesando Empresa: [ID: {$empresa->id}] {$empresa->nombre}");

            // 1. Pólizas de Apertura por Saldos Iniciales de Bancos
            $this->syncAperturaBancos($empresa->id, $force, $resumen);

            // 2. Cotizaciones (Provisión de Viajes CxC)
            $this->syncCotizaciones($empresa->id, $force, $resumen);

            // 3. Cobros de Clientes (CxC Recaudado a Bancos)
            $this->syncCobrosClientes($empresa->id, $force, $resumen);

            // 4. CxP Proveedores Subcontratados (Únicamente GC - ID 7)
            if ((int)$empresa->id === 7) {
                $this->syncProvisionesCxpSubcontratados($empresa->id, $force, $resumen);
                $this->syncPagosCxpProveedores($empresa->id, $force, $resumen);
            }

            // 5. Gastos Devengados
            $this->syncGastosDevengados($empresa->id, $force, $resumen);

            // 6. Gastos Pagados
            $this->syncGastosPagados($empresa->id, $force, $resumen);

            // 7. Préstamos a Operadores
            $this->syncPrestamosOperadores($empresa->id, $force, $resumen);
        }

        $this->line("");
        $this->info("================================================================================");
        $this->info("                       RESUMEN DE SINCRONIZACIÓN CONTABLE                      ");
        $this->info("================================================================================");
        $this->table(
            ['Concepto Operativo / Contable', 'Pólizas Generadas / Actualizadas'],
            [
                ['Pólizas de Apertura (Saldos Iniciales Bancos)', $resumen['aperturas']],
                ['Provisiones de Viajes CxC (Cotizaciones)', $resumen['cotizaciones']],
                ['Cobros Recaudados de Clientes (Bancos vs CxC)', $resumen['cobros_cxc']],
                ['Provisiones CXP Fletes Subcontratados (GC ID: 7)', $resumen['cxp_provisiones']],
                ['Pagos CXP Fletes Proveedores (GC ID: 7)', $resumen['cxp_pagos']],
                ['Gastos Devengados (Gasto vs Proveedores CXP)', $resumen['gastos_deveng']],
                ['Pagos de Gastos a Proveedores (CXP vs Bancos)', $resumen['gastos_pagos']],
                ['Préstamos / Adelantos Otorgados a Operadores', $resumen['prestamos']],
                ['Registros Previamente Pólizados (Omitidos)', $resumen['omitidos']],
                ['Errores o Advertencias', $resumen['errores']],
            ]
        );

        $this->info("Sincronización histórica finalizada exitosamente.");
        return Command::SUCCESS;
    }

    /**
     * Sincroniza saldos iniciales de bancos en una Póliza de Apertura.
     */
    protected function syncAperturaBancos(int $idEmpresa, bool $force, array &$resumen): void
    {
        $bancos = Bancos::where('id_empresa', $idEmpresa)
            ->where('estado', 1)
            ->where('inicial_saldo', '>', 0)
            ->get();

        if ($bancos->isEmpty()) {
            return;
        }

        $anio = now()->year;
        $polizaExistente = Poliza::where('id_empresa', $idEmpresa)
            ->where('es_apertura', true)
            ->whereYear('fecha', $anio)
            ->first();

        if ($polizaExistente && !$force) {
            $resumen['omitidos']++;
            return;
        }

        // Cuenta de Capital / Patrimonio para equilibrar la apertura
        $cuentaCapital = CatCuentaContable::where('codigo', '3.2.2.1')->first()
            ?: CatCuentaContable::where('codigo', '3.1.1.1.001')->first()
            ?: CatCuentaContable::where('codigo', '3.2.1.1')->first()
            ?: CatCuentaContable::where('codigo', 'like', '3%')->where('es_afectable', true)->first();

        if (!$cuentaCapital) {
            $this->warn("  [Apertura] No se encontró cuenta afectable de Patrimonio/Capital para empresa {$idEmpresa}.");
            $resumen['errores']++;
            return;
        }

        $movimientos = [];
        $totalSaldos = 0.00;

        foreach ($bancos as $banco) {
            $monto = round((float) $banco->inicial_saldo, 2);
            if ($monto <= 0) continue;

            $cuentaBancoId = $this->polizaService->resolverCuentaBanco($banco->id, $idEmpresa);
            $movimientos[] = [
                'cuenta_contable_id' => $cuentaBancoId,
                'debe'               => $monto,
                'haber'              => 0.00,
                'referencia'         => 'SALDO_INI',
                'concepto_detalle'   => 'Saldo Inicial Banco ' . ($banco->nombre_banco ?? 'Bancos'),
            ];
            $totalSaldos += $monto;
        }

        if ($totalSaldos <= 0 || empty($movimientos)) {
            return;
        }

        // Contrapartida al Haber en Capital/Patrimonio
        $movimientos[] = [
            'cuenta_contable_id' => $cuentaCapital->id,
            'debe'               => 0.00,
            'haber'              => $totalSaldos,
            'referencia'         => 'CAPITAL_INI',
            'concepto_detalle'   => 'Patrimonio / Saldos Iniciales Bancarios Apertura ' . $anio,
        ];

        try {
            $this->polizaService->guardarPolizaApertura([
                'id_empresa'  => $idEmpresa,
                'fecha'       => "{$anio}-01-01",
                'concepto'    => "Póliza de Apertura / Saldos Iniciales Bancos {$anio}",
                'movimientos' => $movimientos,
            ]);
            $resumen['aperturas']++;
            $this->line("  ✓ Póliza de Apertura generada por \$" . number_format($totalSaldos, 2));
        } catch (\Throwable $e) {
            $this->warn("  ✗ Error en apertura empresa {$idEmpresa}: " . $e->getMessage());
            $resumen['errores']++;
        }
    }

    /**
     * Sincroniza cotizaciones aprobadas con importe.
     */
    protected function syncCotizaciones(int $idEmpresa, bool $force, array &$resumen): void
    {
        $cotizaciones = Cotizaciones::where('id_empresa', $idEmpresa)
            ->whereIn('estatus', ['Aprobada', 'Finalizado'])
            ->where(function ($q) {
                $q->where('total', '>', 0)->orWhere('precio_viaje', '>', 0);
            })
            ->get();

        $count = 0;
        foreach ($cotizaciones as $cot) {
            if (!$force) {
                $existe = Poliza::where('origen_tipo', 'cotizacion')->where('origen_id', $cot->id)->exists();
                if ($existe) {
                    $resumen['omitidos']++;
                    continue;
                }
            }

            try {
                $pol = $this->cotizacionesService->contabilizarProvisionViaje($cot);
                if ($pol) {
                    $count++;
                    $resumen['cotizaciones']++;
                }
            } catch (\Throwable $e) {
                $resumen['errores']++;
            }
        }

        if ($count > 0) {
            $this->line("  ✓ {$count} provisiones de viajes contabilizadas (CxC vs Ingresos).");
        }
    }

    /**
     * Sincroniza cobros de clientes.
     */
    protected function syncCobrosClientes(int $idEmpresa, bool $force, array &$resumen): void
    {
        $cobros = CobroPagoCotizacion::whereHas('cotizacion', function ($q) use ($idEmpresa) {
                $q->where('id_empresa', $idEmpresa);
            })
            ->whereHas('cobroPago', function ($q) {
                $q->where('tipo', 'cxc');
            })
            ->where('monto', '>', 0)
            ->get();

        $count = 0;
        foreach ($cobros as $cpc) {
            if (!$force) {
                $existe = Poliza::where('origen_tipo', 'cobro_cxc')->where('origen_id', $cpc->id)->exists();
                if ($existe) {
                    $resumen['omitidos']++;
                    continue;
                }
            }

            try {
                $pol = $this->polizaService->contabilizarCobroCxc($cpc);
                if ($pol) {
                    $count++;
                    $resumen['cobros_cxc']++;
                }
            } catch (\Throwable $e) {
                $resumen['errores']++;
            }
        }

        if ($count > 0) {
            $this->line("  ✓ {$count} cobros a clientes recaudados contabilizados.");
        }
    }

    /**
     * Sincroniza gastos devengados.
     */
    protected function syncGastosDevengados(int $idEmpresa, bool $force, array &$resumen): void
    {
        $gastos = Gasto::where('id_empresa', $idEmpresa)
            ->where('monto_total', '>', 0)
            ->where('estatus', '!=', 'cancelado')
            ->get();

        $count = 0;
        foreach ($gastos as $gasto) {
            if (!$force) {
                $existe = Poliza::where('origen_tipo', 'gasto_devengado')->where('origen_id', $gasto->id)->exists();
                if ($existe) {
                    $resumen['omitidos']++;
                    continue;
                }
            }

            try {
                $pol = $this->polizaService->contabilizarGastoDevengado($gasto);
                if ($pol) {
                    $count++;
                    $resumen['gastos_deveng']++;
                }
            } catch (\Throwable $e) {
                $resumen['errores']++;
            }
        }

        if ($count > 0) {
            $this->line("  ✓ {$count} gastos devengados contabilizados (Gasto vs CXP Proveedor).");
        }
    }

    /**
     * Sincroniza pagos de gastos.
     */
    protected function syncGastosPagados(int $idEmpresa, bool $force, array &$resumen): void
    {
        $pagos = GastoPago::whereHas('gasto', function ($q) use ($idEmpresa) {
                $q->where('id_empresa', $idEmpresa);
            })
            ->where('monto', '>', 0)
            ->get();

        $count = 0;
        foreach ($pagos as $pago) {
            if (!$force) {
                $existe = Poliza::where('origen_tipo', 'gasto_pago')->where('origen_id', $pago->id)->exists();
                if ($existe) {
                    $resumen['omitidos']++;
                    continue;
                }
            }

            try {
                $pol = $this->polizaService->contabilizarGastoPago($pago);
                if ($pol) {
                    $count++;
                    $resumen['gastos_pagos']++;
                }
            } catch (\Throwable $e) {
                $resumen['errores']++;
            }
        }

        if ($count > 0) {
            $this->line("  ✓ {$count} pagos de gastos a proveedores contabilizados (CXP vs Bancos).");
        }
    }

    /**
     * Sincroniza préstamos a operadores.
     */
    protected function syncPrestamosOperadores(int $idEmpresa, bool $force, array &$resumen): void
    {
        $prestamos = Prestamo::whereHas('operador', function ($q) use ($idEmpresa) {
                $q->where('id_empresa', $idEmpresa);
            })
            ->where('cantidad', '>', 0)
            ->get();

        $count = 0;
        foreach ($prestamos as $prestamo) {
            if (!$force) {
                $existe = Poliza::where('origen_tipo', 'prestamo_operador')->where('origen_id', $prestamo->id)->exists();
                if ($existe) {
                    $resumen['omitidos']++;
                    continue;
                }
            }

            try {
                $pol = $this->polizaService->contabilizarPrestamoOperador($prestamo);
                if ($pol) {
                    $count++;
                    $resumen['prestamos']++;
                }
            } catch (\Throwable $e) {
                $resumen['errores']++;
            }
        }

        if ($count > 0) {
            $this->line("  ✓ {$count} préstamos/adelantos a operadores contabilizados.");
        }
    }

    /**
     * Sincroniza provisiones de CXP a proveedores de flete subcontratado (Exclusivo GC - ID 7).
     */
    protected function syncProvisionesCxpSubcontratados(int $idEmpresa, bool $force, array &$resumen): void
    {
        $cotizaciones = Cotizaciones::where('id_empresa', $idEmpresa)
            ->whereIn('estatus', ['Aprobada', 'Finalizado'])
            ->whereHas('DocCotizacion.Asignaciones', function ($q) {
                $q->where('tipo_contrato', 'Subcontratado')
                  ->whereNotNull('id_proveedor');
            })
            ->with(['DocCotizacion.Asignaciones.Proveedor'])
            ->get();

        $count = 0;
        foreach ($cotizaciones as $cot) {
            if (!$force) {
                $existe = Poliza::where('origen_tipo', 'cotizacion_cxp')->where('origen_id', $cot->id)->exists();
                if ($existe) {
                    $resumen['omitidos']++;
                    continue;
                }
            }

            try {
                $pol = $this->polizaService->contabilizarProvisionCxpProveedor($cot);
                if ($pol) {
                    $count++;
                    $resumen['cxp_provisiones']++;
                }
            } catch (\Throwable $e) {
                $resumen['errores']++;
            }
        }

        if ($count > 0) {
            $this->line("  ✓ {$count} provisiones de CXP a proveedores de flete contabilizadas.");
        }
    }

    /**
     * Sincroniza pagos de CXP a proveedores de flete subcontratado (Exclusivo GC - ID 7).
     */
    protected function syncPagosCxpProveedores(int $idEmpresa, bool $force, array &$resumen): void
    {
        $pagos = CobroPagoCotizacion::whereHas('cotizacion', function ($q) use ($idEmpresa) {
                $q->where('id_empresa', $idEmpresa);
            })
            ->whereHas('cobroPago', function ($q) {
                $q->where('tipo', 'cxp');
            })
            ->where('monto', '>', 0)
            ->with(['cotizacion', 'cobroPago'])
            ->get();

        $count = 0;
        foreach ($pagos as $cpc) {
            if (!$force) {
                $existe = Poliza::where('origen_tipo', 'cxp_proveedor_pago')->where('origen_id', $cpc->id)->exists();
                if ($existe) {
                    $resumen['omitidos']++;
                    continue;
                }
            }

            try {
                $pol = $this->polizaService->contabilizarPagoCxpProveedor($cpc);
                if ($pol) {
                    $count++;
                    $resumen['cxp_pagos']++;
                }
            } catch (\Throwable $e) {
                $resumen['errores']++;
            }
        }

        if ($count > 0) {
            $this->line("  ✓ {$count} pagos de CXP a proveedores de flete contabilizados.");
        }
    }
}

