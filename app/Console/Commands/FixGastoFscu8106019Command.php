<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\Gasto;
use App\Models\GastoPago;
use App\Models\CatBancoCuentasMovimientos;
use App\Models\Bancos;
use App\Models\BitacoraViajeOperador;
use App\Models\GastosOperadores;

class FixGastoFscu8106019Command extends Command
{
    protected $signature = 'fix:gasto-fscu8106019 {--dry-run : Ejecutar sin guardar cambios en BD}';
    protected $description = 'Corrige el cargo bancario y pago desincronizado de diésel para el contenedor FSCU8106019-ZZH86';

    public function handle()
    {
        $this->info("=== Iniciando regularización de diésel FSCU8106019-ZZH86 ===");
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn("MODO DRY-RUN ACTIVADO: No se aplicarán cambios a la base de datos.");
        }

        $montoReal = 17529.09;

        // 1. Bitacora 59
        $bitacora = BitacoraViajeOperador::find(59);
        $this->line("Bitacora #59 costo actual: " . ($bitacora ? $bitacora->costo : 'No encontrada'));

        // 2. GastosOperadores 1682
        $gastoOp = GastosOperadores::find(1682);
        $this->line("GastosOperadores #1682 cantidad actual: " . ($gastoOp ? $gastoOp->cantidad : 'No encontrado'));

        // 3. Gasto 2532
        $gasto = Gasto::find(2532);
        if (!$gasto) {
            $this->error("Gasto #2532 no encontrado.");
            return 1;
        }
        $this->line("Gasto #2532 monto_total: {$gasto->monto_total}, fecha_gasto: {$gasto->fecha_gasto}, empresa: {$gasto->id_empresa}");

        // 4. GastoPago 2317
        $pago = GastoPago::find(2317);
        if (!$pago) {
            $this->error("GastoPago #2317 no encontrado.");
            return 1;
        }
        $this->line("GastoPago #2317 monto actual: {$pago->monto}");

        // 5. Movimiento Bancario 2258
        $movimiento = CatBancoCuentasMovimientos::find(2258);
        if (!$movimiento) {
            $this->error("Movimiento bancario #2258 no encontrado.");
            return 1;
        }
        $this->line("Movimiento #2258 monto actual: {$movimiento->monto}");

        // 6. Cuenta Bancaria 6
        $cuenta = Bancos::find(6);
        $this->line("Cuenta #6 saldo registrado actual: " . ($cuenta ? $cuenta->saldo : 'No encontrada'));

        if (!$dryRun) {
            DB::transaction(function () use ($bitacora, $gastoOp, $gasto, $pago, $movimiento, $cuenta, $montoReal) {
                // Actualizar Bitácora
                if ($bitacora && (float)$bitacora->costo !== $montoReal) {
                    $bitacora->costo = $montoReal;
                    $bitacora->save();
                }

                // Actualizar GastosOperadores
                if ($gastoOp && (float)$gastoOp->cantidad !== $montoReal) {
                    $gastoOp->cantidad = $montoReal;
                    $gastoOp->save();
                }

                // Actualizar Gasto
                $gasto->monto_total = $montoReal;
                $gasto->estatus = 'pagado';
                $gasto->save();

                // Actualizar Imputaciones
                foreach ($gasto->imputaciones as $imp) {
                    $imp->monto_imputado = $montoReal;
                    $imp->save();
                }

                // Actualizar GastoPago
                $pago->monto = $montoReal;
                $pago->save();

                // Actualizar detalles JSON en Movimiento
                $detalles = $movimiento->detalles;
                if (is_array($detalles)) {
                    foreach ($detalles as &$item) {
                        $item['monto'] = $montoReal;
                        $item['concepto'] = 'GDI02 - Diesel';
                    }
                    unset($item);
                }

                // Actualizar Movimiento
                $movimiento->monto = $montoReal;
                $movimiento->detalles = $detalles;
                $movimiento->save();

                // Recalcular saldo de la Cuenta 6
                if ($cuenta) {
                    $abonos = CatBancoCuentasMovimientos::where('cuenta_bancaria_id', 6)->where('tipo', 'abono')->where('cancelado', false)->sum('monto');
                    $cargos = CatBancoCuentasMovimientos::where('cuenta_bancaria_id', 6)->where('tipo', 'cargo')->where('cancelado', false)->sum('monto');
                    $nuevoSaldo = (float)($cuenta->inicial_saldo ?? 0) + $abonos - $cargos;
                    $cuenta->saldo = $nuevoSaldo;
                    $cuenta->save();
                    $this->info("Cuenta #6 saldo recalculado: {$nuevoSaldo}");
                }
            });

            $this->info("REGULARIZACIÓN EXITOSA: Todos los registros sincronizados a $montoReal.");
        } else {
            $this->info("DRY-RUN completado. Sin cambios efectuados.");
        }

        return 0;
    }
}
