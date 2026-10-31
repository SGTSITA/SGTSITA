<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Empresas;
use App\Models\User;
use App\Models\Bancos;
use App\Models\Poliza;
use App\Models\CatCuentaContable;
use App\Services\PolizaService;
use App\Services\ContabilidadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;

class ContabilidadAperturaAnticiposTest extends TestCase
{
    use DatabaseTransactions;

    protected int $idEmpresa;
    protected PolizaService $polizaService;
    protected ContabilidadService $contabilidadService;

    protected function setUp(): void
    {
        parent::setUp();

        $empresa = Empresas::first();
        $this->idEmpresa = $empresa ? $empresa->id : 1;

        $this->polizaService = app(PolizaService::class);
        $this->contabilidadService = app(ContabilidadService::class);
    }

    public function test_guardar_poliza_apertura_valida_partida_doble()
    {
        $cuentaBanco = CatCuentaContable::where('codigo', '1.1.1.2.001')->first();
        $cuentaCapital = CatCuentaContable::where('codigo', '3.2.1.1')->first();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Póliza descuadrada');

        // Intento con Debe != Haber
        $this->polizaService->guardarPolizaApertura([
            'id_empresa' => $this->idEmpresa,
            'fecha' => '2024-01-01',
            'movimientos' => [
                ['cuenta_contable_id' => $cuentaBanco->id, 'debe' => 10000.00, 'haber' => 0.00],
                ['cuenta_contable_id' => $cuentaCapital->id, 'debe' => 0.00, 'haber' => 9500.00],
            ]
        ]);
    }

    public function test_guardar_poliza_apertura_exitosa_y_reemplazo()
    {
        $cuentaBanco = CatCuentaContable::where('codigo', '1.1.1.2.001')->first();
        $cuentaCapital = CatCuentaContable::where('codigo', '3.2.1.1')->first();

        // 1. Guardar póliza de apertura cuadrada
        $poliza = $this->polizaService->guardarPolizaApertura([
            'id_empresa' => $this->idEmpresa,
            'fecha' => '2024-01-01',
            'concepto' => 'Apertura Ejercicio 2024 Test',
            'movimientos' => [
                ['cuenta_contable_id' => $cuentaBanco->id, 'debe' => 50000.00, 'haber' => 0.00],
                ['cuenta_contable_id' => $cuentaCapital->id, 'debe' => 0.00, 'haber' => 50000.00],
            ]
        ]);

        $this->assertTrue($poliza->es_apertura);
        $this->assertStringStartsWith('APE-', $poliza->numero);
        $this->assertEquals(50000.00, (float)$poliza->total_debe);
        $this->assertEquals(50000.00, (float)$poliza->total_haber);
        $this->assertEquals(2, $poliza->movimientos()->count());

        // 2. Si se vuelve a guardar para la misma empresa y año, actualiza sin duplicar
        $polizaActualizada = $this->polizaService->guardarPolizaApertura([
            'id_empresa' => $this->idEmpresa,
            'fecha' => '2024-01-01',
            'concepto' => 'Apertura Ejercicio 2024 Reajustada',
            'movimientos' => [
                ['cuenta_contable_id' => $cuentaBanco->id, 'debe' => 60000.00, 'haber' => 0.00],
                ['cuenta_contable_id' => $cuentaCapital->id, 'debe' => 0.00, 'haber' => 60000.00],
            ]
        ]);

        $this->assertEquals($poliza->id, $polizaActualizada->id);
        $this->assertEquals(60000.00, (float)$polizaActualizada->total_debe);
    }

    public function test_resolver_asiento_anticipo_socio()
    {
        $banco = Bancos::first();
        $cuentaAnticipo = CatCuentaContable::where('codigo', '1.1.2.3.003')->first();
        $this->assertNotNull($cuentaAnticipo, 'La cuenta 1.1.2.3.003 debe existir en catálogo');

        $asiento = $this->polizaService->resolverAsientoPorEvento('ANTICIPO_SOCIO', null, $this->idEmpresa, [
            'monto' => 15000.00,
            'cuenta_bancaria_id' => $banco?->id,
            'concepto' => 'Anticipo quincenal socio Juan Pérez'
        ]);

        $this->assertEquals('egreso', $asiento['tipo']);
        $this->assertCount(2, $asiento['movimientos']);

        // Movimiento Cargo: Anticipos a Socios (Activo)
        $cargo = $asiento['movimientos'][0];
        $this->assertEquals($cuentaAnticipo->id, $cargo['cuenta_contable_id']);
        $this->assertEquals(15000.00, $cargo['debe']);
        $this->assertEquals(0.00, $cargo['haber']);

        // Movimiento Abono: Bancos
        $abono = $asiento['movimientos'][1];
        $this->assertEquals(0.00, $abono['debe']);
        $this->assertEquals(15000.00, $abono['haber']);
    }

    public function test_resolver_asiento_liquidacion_socio_con_amortizacion_anticipo()
    {
        $banco = Bancos::first();
        $cuentaAnticipo = CatCuentaContable::where('codigo', '1.1.2.3.003')->first();
        $cuentaPasivoSocios = CatCuentaContable::where('codigo', '2.1.9.1.002')->first();

        // Utilidad total: $25,000. Anticipo previo: $10,000. Pago banco: $15,000.
        $asiento = $this->polizaService->resolverAsientoPorEvento('LIQUIDACION_SOCIOS_CON_ANTICIPO', null, $this->idEmpresa, [
            'monto' => 25000.00,
            'monto_total_utilidad' => 25000.00,
            'monto_anticipo_amortizado' => 10000.00,
            'monto_pago_banco' => 15000.00,
            'cuenta_bancaria_id' => $banco?->id,
            'concepto' => 'Liquidación de periodo con compensación de anticipo'
        ]);

        $this->assertEquals('egreso', $asiento['tipo']);
        $this->assertCount(3, $asiento['movimientos']);

        // Cargo 1: Pasivo Socios ($25,000)
        $this->assertEquals($cuentaPasivoSocios->id, $asiento['movimientos'][0]['cuenta_contable_id']);
        $this->assertEquals(25000.00, $asiento['movimientos'][0]['debe']);

        // Abono 1: Amortiza Anticipo ($10,000)
        $this->assertEquals($cuentaAnticipo->id, $asiento['movimientos'][1]['cuenta_contable_id']);
        $this->assertEquals(10000.00, $asiento['movimientos'][1]['haber']);

        // Abono 2: Salida Banco ($15,000)
        $this->assertEquals(15000.00, $asiento['movimientos'][2]['haber']);

        // Verificamos que crear la póliza con este asiento cuadre perfectamente
        $poliza = $this->polizaService->crearPoliza($asiento);
        $this->assertEquals(25000.00, (float)$poliza->total_debe);
        $this->assertEquals(25000.00, (float)$poliza->total_haber);
    }

    public function test_socio_balance_desglosa_anticipos_correctamente()
    {
        $socio = \App\Models\Socio::first() ?: \App\Models\Socio::create([
            'id_empresa' => $this->idEmpresa,
            'nombre' => 'Socio Test Anticipos',
            'activo' => 1
        ]);

        $banco = Bancos::first();

        // 1. Registrar un anticipo sin periodo ($8,000)
        \App\Models\SocioPago::create([
            'id_empresa' => $this->idEmpresa,
            'socio_id' => $socio->id,
            'monto' => 8000.00,
            'banco_id' => $banco?->id,
            'fecha_aplicacion' => now()->format('Y-m-d'),
            'calculo_periodo_id' => null,
            'user_id' => 1
        ]);

        $sociosService = app(\App\Services\SociosService::class);
        $balance = $sociosService->getSocioBalance($socio->id);

        $this->assertArrayHasKey('total_anticipos', $balance);
        $this->assertArrayHasKey('total_liquidado_cortes', $balance);
        $this->assertGreaterThanOrEqual(8000.00, $balance['total_anticipos']);
    }
}

