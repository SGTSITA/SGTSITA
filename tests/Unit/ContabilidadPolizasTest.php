<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\CatCuentaContable;
use App\Models\Poliza;
use App\Services\PolizaService;
use App\Services\ContabilidadService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use InvalidArgumentException;

class ContabilidadPolizasTest extends TestCase
{
    use DatabaseTransactions;

    protected PolizaService $polizaService;
    protected ContabilidadService $contabilidadService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->polizaService = app(PolizaService::class);
        $this->contabilidadService = app(ContabilidadService::class);
        Poliza::where('id_empresa', 1)->delete();
    }

    /** @test */
    public function test_poliza_requiere_partida_doble()
    {
        $cuentaBanco = CatCuentaContable::where('codigo', '1.1.1.2.001')->firstOrFail();
        $cuentaInteres = CatCuentaContable::where('codigo', '4.3.1.1')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Póliza descuadrada');

        $this->polizaService->crearPoliza([
            'id_empresa'  => 1,
            'tipo'        => 'ingreso',
            'fecha'       => '2026-10-08',
            'concepto'    => 'Prueba póliza descuadrada',
            'movimientos' => [
                [
                    'cuenta_contable_id' => $cuentaBanco->id,
                    'debe'               => 100.00,
                    'haber'              => 0.00,
                ],
                [
                    'cuenta_contable_id' => $cuentaInteres->id,
                    'debe'               => 0.00,
                    'haber'              => 90.00, // Descuadrada por 10.00
                ],
            ],
        ]);
    }

    /** @test */
    public function test_poliza_rechaza_cuentas_no_afectables()
    {
        // Cuenta nivel 3 de título no afectable
        $cuentaTitulo = CatCuentaContable::where('codigo', '1.1.1')->where('es_afectable', false)->firstOrFail();
        $cuentaInteres = CatCuentaContable::where('codigo', '4.3.1.1')->firstOrFail();

        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('es de título/mayor acumulativa y NO permite movimientos directos');

        $this->polizaService->crearPoliza([
            'id_empresa'  => 1,
            'tipo'        => 'ingreso',
            'fecha'       => '2026-10-08',
            'concepto'    => 'Prueba cuenta de mayor no afectable',
            'movimientos' => [
                [
                    'cuenta_contable_id' => $cuentaTitulo->id,
                    'debe'               => 50.00,
                    'haber'              => 0.00,
                ],
                [
                    'cuenta_contable_id' => $cuentaInteres->id,
                    'debe'               => 0.00,
                    'haber'              => 50.00,
                ],
            ],
        ]);
    }

    /** @test */
    public function test_poliza_se_crea_correctamente_con_cuentas_afectables_y_asientos_cuadrados()
    {
        $cuentaBanco = CatCuentaContable::where('codigo', '1.1.1.2.001')->firstOrFail();
        $cuentaInteres = CatCuentaContable::where('codigo', '4.3.1.1')->firstOrFail();

        $poliza = $this->polizaService->crearPoliza([
            'id_empresa'  => 1,
            'tipo'        => 'ingreso',
            'fecha'       => '2026-10-08',
            'concepto'    => 'Intereses ganados Banco Azteca',
            'movimientos' => [
                [
                    'cuenta_contable_id' => $cuentaBanco->id,
                    'debe'               => 1.72,
                    'haber'              => 0.00,
                ],
                [
                    'cuenta_contable_id' => $cuentaInteres->id,
                    'debe'               => 0.00,
                    'haber'              => 1.72,
                ],
            ],
        ]);

        $this->assertInstanceOf(Poliza::class, $poliza);
        $this->assertEquals(1.72, (float) $poliza->total_debe);
        $this->assertEquals(1.72, (float) $poliza->total_haber);
        $this->assertTrue($poliza->estaCuadrada());
        $this->assertCount(2, $poliza->movimientos);
    }

    /** @test */
    public function test_resolver_asiento_por_evento_interes_bancario_desde_guia_contabilizadora()
    {
        $asiento = $this->polizaService->resolverAsientoPorEvento(
            'INTERES_BANCARIO',
            null,
            1,
            [
                'monto' => 1500.00,
                'fecha' => '2026-10-08',
                'concepto' => 'Rendimientos mensuales',
            ]
        );

        $this->assertEquals('ingreso', $asiento['tipo']);
        $this->assertCount(2, $asiento['movimientos']);
        $this->assertEquals(1500.00, $asiento['movimientos'][0]['debe']);
        $this->assertEquals(1500.00, $asiento['movimientos'][1]['haber']);

        // Crear la póliza a partir de lo resuelto por la guía
        $poliza = $this->polizaService->crearPoliza($asiento);
        $this->assertTrue($poliza->estaCuadrada());
    }

    /** @test */
    public function test_agregacion_recursiva_ascendente_en_arbol_de_cuentas()
    {
        $cuentaBancoBBVA = CatCuentaContable::where('codigo', '1.1.1.2.001')->firstOrFail();
        $cuentaFletes = CatCuentaContable::where('codigo', '4.1.1.1')->firstOrFail();

        // Creamos una póliza con cargo a Bancos BBVA de $5,000.00
        $this->polizaService->crearPoliza([
            'id_empresa'  => 1,
            'tipo'        => 'ingreso',
            'fecha'       => '2026-10-08',
            'concepto'    => 'Prueba suma recursiva',
            'movimientos' => [
                [
                    'cuenta_contable_id' => $cuentaBancoBBVA->id,
                    'debe'               => 5000.00,
                    'haber'              => 0.00,
                ],
                [
                    'cuenta_contable_id' => $cuentaFletes->id,
                    'debe'               => 0.00,
                    'haber'              => 5000.00,
                ],
            ],
        ]);

        $arbol = $this->contabilidadService->obtenerArbolSaldos(1, null, '2026-10-08');

        // Validamos que el saldo se haya propagado recursivamente a todos sus ancestros:
        // Nivel 5: 1.1.1.2.001 BBVA
        $c5 = $arbol->firstWhere('codigo', '1.1.1.2.001');
        $this->assertNotNull($c5);
        $this->assertEquals(5000.00, $c5->saldo_neto);

        // Nivel 4: 1.1.1.2 Bancos / Tesorería (acumula a su hijo)
        $c4 = $arbol->firstWhere('codigo', '1.1.1.2');
        $this->assertNotNull($c4);
        $this->assertEquals(5000.00, $c4->saldo_neto);

        // Nivel 3: 1.1.1 Efectivo y Equivalentes
        $c3 = $arbol->firstWhere('codigo', '1.1.1');
        $this->assertNotNull($c3);
        $this->assertEquals(5000.00, $c3->saldo_neto);

        // Nivel 2: 1.1 ACTIVO CIRCULANTE
        $c2 = $arbol->firstWhere('codigo', '1.1');
        $this->assertNotNull($c2);
        $this->assertEquals(5000.00, $c2->saldo_neto);

        // Nivel 1: 1 ACTIVO
        $c1 = $arbol->firstWhere('codigo', '1');
        $this->assertNotNull($c1);
        $this->assertEquals(5000.00, $c1->saldo_neto);
    }

    /** @test */
    public function test_bancos_service_registrar_movimiento_interes_genera_poliza_automatica()
    {
        $bancosService = app(\App\Services\BancosService::class);

        // Crear una cuenta de banco de prueba
        $cuenta = \App\Models\Bancos::create([
            'id_empresa'          => 1,
            'nombre_banco'        => 'BANCO AZTECA',
            'nombre_beneficiario' => 'EMPRESA TEST SA DE CV',
            'moneda'              => 'MXN',
            'cuenta_bancaria'     => '1234567890',
            'clabe'               => '127180001234567890',
            'inicial_saldo'       => 1000.00,
            'saldo'               => 1000.00,
            'tipo'                => 'Cheques',
            'estado'              => 1,
        ]);

        $mov = $bancosService->registrarMovimiento([
            'cuenta_bancaria_id' => $cuenta->id,
            'tipo'               => 'abono',
            'monto'              => 42.50,
            'concepto'           => 'Intereses ganados periodo',
            'fecha_movimiento'   => '2026-10-08',
            'origen'             => 'banco',
            'tipo_concepto'      => 'interes',
        ]);

        $this->assertNotNull($mov);
        $this->assertEquals('interes', $mov->tipo_concepto);
        $this->assertNotNull($mov->poliza_id);

        $poliza = Poliza::with('movimientos.cuentaContable')->find($mov->poliza_id);
        $this->assertNotNull($poliza);
        $this->assertEquals('ingreso', $poliza->tipo);
        $this->assertEquals(42.50, (float) $poliza->total_debe);
        $this->assertEquals(42.50, (float) $poliza->total_haber);
        $this->assertTrue($poliza->estaCuadrada());

        // Comprobar que el cargo fue a Banco Azteca (1.1.1.2.002) y abono a Intereses ganados (4.3.1.1)
        $cargo = $poliza->movimientos->firstWhere('debe', 42.50);
        $abono = $poliza->movimientos->firstWhere('haber', 42.50);

        $this->assertNotNull($cargo);
        $this->assertNotNull($abono);
        $this->assertEquals('1.1.1.2.002', $cargo->cuentaContable->codigo);
        $this->assertEquals('4.3.1.1', $abono->cuentaContable->codigo);
    }

    /** @test */
    public function test_gastos_service_pagar_genera_poliza_egreso_con_imputacion_a_camion()
    {
        $gastosService = app(\App\Services\GastosService::class);

        // Crear equipo
        $equipo = \App\Models\Equipo::create([
            'id_empresa' => 1,
            'id_equipo'  => 'TP-002',
            'placas'     => '12-ABC-3',
            'marca'      => 'KENWORTH',
            'tipo'       => 'Tractos / Camiones',
        ]);

        // Crear banco con saldo
        $cuenta = \App\Models\Bancos::create([
            'id_empresa'          => 1,
            'nombre_banco'        => 'BBVA BANCOMER',
            'nombre_beneficiario' => 'TEST SA DE CV',
            'moneda'              => 'MXN',
            'cuenta_bancaria'     => '9876543210',
            'inicial_saldo'       => 50000.00,
            'saldo'               => 50000.00,
            'tipo'                => 'Cheques',
            'estado'              => 1,
        ]);

        // Registrar gasto operativo de diesel
        $gasto = $gastosService->registrar([
            'id_empresa'  => 1,
            'id_equipo'   => $equipo->id,
            'concepto'    => 'Carga de Diesel Carretera',
            'monto_total' => 3500.00,
            'fecha_gasto' => '2026-10-08',
            'tipo_gasto'  => 'general',
            'estatus'     => 'pendiente_pago',
        ]);

        // Pagar gasto
        $pago = $gastosService->pagar($gasto, [
            'monto'              => 3500.00,
            'fecha_pago'         => '2026-10-08',
            'cuenta_bancaria_id' => $cuenta->id,
            'metodo_pago'        => 'Transferencia',
        ]);

        $this->assertNotNull($pago);

        // Verificar que se haya emitido la póliza
        $poliza = Poliza::where('origen_tipo', 'gasto')
            ->where('origen_id', $gasto->id)
            ->with('movimientos.cuentaContable')
            ->first();

        $this->assertNotNull($poliza);
        $this->assertEquals('egreso', $poliza->tipo);
        $this->assertEquals(3500.00, (float) $poliza->total_debe);
        $this->assertEquals(3500.00, (float) $poliza->total_haber);
        $this->assertEquals($equipo->id, $poliza->id_equipo);
        $this->assertTrue($poliza->estaCuadrada());

        // Debe ser Cargo a Combustible (5.1.1.1) y Abono a Bancos BBVA (1.1.1.2.001)
        $cargo = $poliza->movimientos->firstWhere('debe', 3500.00);
        $abono = $poliza->movimientos->firstWhere('haber', 3500.00);

        $this->assertNotNull($cargo);
        $this->assertNotNull($abono);
        $this->assertEquals('5.1.1.1', $cargo->cuentaContable->codigo);
        $this->assertEquals('1.1.1.2.001', $abono->cuentaContable->codigo);
    }

    /** @test */
    public function test_cotizaciones_service_provision_viaje_y_cobro_cxc_generan_polizas()
    {
        $cotizacionesService = app(\App\Services\CotizacionesService::class);

        // Crear cotización
        $cotizacion = \App\Models\Cotizaciones::create([
            'id_empresa'   => 1,
            'id_cliente'   => 1,
            'precio_viaje' => 18000.00,
            'burreo'       => 1500.00,
            'maniobra'     => 500.00,
            'estatus'      => 'Aprobada',
            'fecha_entrega'=> '2026-10-08',
        ]);

        // 1. Provisión del flete
        $polizaProvision = $cotizacionesService->contabilizarProvisionViaje($cotizacion);

        $this->assertNotNull($polizaProvision);
        $this->assertEquals('diario', $polizaProvision->tipo);
        $this->assertEquals(20000.00, (float) $polizaProvision->total_debe);
        $this->assertEquals(20000.00, (float) $polizaProvision->total_haber);
        $this->assertTrue($polizaProvision->estaCuadrada());

        // 2. Cobro al cliente (COBRO_CXC)
        $cuenta = \App\Models\Bancos::first();
        $polizaCobro = $cotizacionesService->contabilizarCobroCliente($cotizacion, 20000.00, $cuenta->id, '2026-10-08');

        $this->assertNotNull($polizaCobro);
        $this->assertEquals('ingreso', $polizaCobro->tipo);
        $this->assertEquals(20000.00, (float) $polizaCobro->total_debe);
        $this->assertEquals(20000.00, (float) $polizaCobro->total_haber);
        $this->assertTrue($polizaCobro->estaCuadrada());
    }

    /** @test */
    public function test_socios_service_pago_a_socio_genera_poliza_egreso()
    {
        $sociosService = app(\App\Services\SociosService::class);

        $socio = \App\Models\Socio::create([
            'id_empresa' => 1,
            'nombre'     => 'SOCIO INVERSIONISTA TEST',
            'activo'     => 1,
        ]);

        $cuenta = \App\Models\Bancos::first();

        $pago = $sociosService->registrarPagoSocio([
            'socio_id'         => $socio->id,
            'monto'            => 12500.00,
            'banco_id'         => $cuenta->id,
            'fecha_aplicacion' => '2026-10-08',
        ], 1, 1);

        $this->assertNotNull($pago);

        $poliza = Poliza::where('origen_tipo', 'socio_pago')
            ->where('origen_id', $pago->id)
            ->with('movimientos.cuentaContable')
            ->first();

        $this->assertNotNull($poliza);
        $this->assertEquals('egreso', $poliza->tipo);
        $this->assertEquals(12500.00, (float) $poliza->total_debe);
        $this->assertEquals(12500.00, (float) $poliza->total_haber);
        $this->assertTrue($poliza->estaCuadrada());

        // Cargo a Reparto de Utilidades por Pagar a Socios (2.1.9.1.002) y Abono a Bancos (1.1.1.2.*)
        $cargo = $poliza->movimientos->firstWhere('debe', 12500.00);
        $abono = $poliza->movimientos->firstWhere('haber', 12500.00);

        $this->assertNotNull($cargo);
        $this->assertNotNull($abono);
        $this->assertEquals('2.1.9.1.002', $cargo->cuentaContable->codigo);
        $this->assertTrue(str_starts_with($abono->cuentaContable->codigo, '1.1.1.2'));
    }

    /** @test */
    public function test_contabilidad_controller_balance_general_retorna_estructura_con_intereses_separados()
    {
        $user = \App\Models\User::first() ?: \App\Models\User::create([
            'name'       => 'Admin Contable Test',
            'email'      => 'admin.contable@test.com',
            'password'   => bcrypt('secret'),
            'id_empresa' => 1,
            'es_admin'   => true,
        ]);

        $this->actingAs($user);

        $controller = app(\App\Http\Controllers\ContabilidadController::class);
        $request = \Illuminate\Http\Request::create('/contabilidad/balance-general', 'GET', [
            'fecha_corte' => '2026-10-08',
            'json'        => true,
        ]);

        $response = $controller->balanceGeneral($request);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $response->getData(true);

        $this->assertTrue($data['success']);
        $this->assertArrayHasKey('activo', $data['data']);
        $this->assertArrayHasKey('pasivo', $data['data']);
        $this->assertArrayHasKey('patrimonio', $data['data']);
        $this->assertArrayHasKey('estado_resultados', $data['data']);

        // Verificar que los intereses bancarios están explícitamente separados de los fletes
        $this->assertArrayHasKey('intereses_ganados', $data['data']['estado_resultados']);
        $this->assertArrayHasKey('ingresos_fletes', $data['data']['estado_resultados']);
        $this->assertArrayHasKey('gastos_totales', $data['data']['estado_resultados']);
        $this->assertArrayHasKey('utilidad_neta', $data['data']['estado_resultados']);
    }

    /** @test */
    public function test_vistas_balance_general_renderizan_correctamente()
    {
        $user = \App\Models\User::first();
        $this->actingAs($user);

        $controller = app(\App\Http\Controllers\ContabilidadController::class);

        // 1. Vista principal
        $request1 = \Illuminate\Http\Request::create('/contabilidad/balance-general', 'GET', [
            'fecha_corte' => '2026-10-08',
        ]);
        $response1 = $controller->balanceGeneral($request1);
        $this->assertInstanceOf(\Illuminate\View\View::class, $response1);
        $html1 = $response1->render();
        $this->assertStringContainsString('BALANCE GENERAL', $html1);
        $this->assertStringContainsString('LINEAMIENTOS CONAC', $html1);
        $this->assertStringContainsString('ESTADO DE RESULTADOS', $html1);

        // 2. Vista de impresión
        $request2 = \Illuminate\Http\Request::create('/contabilidad/balance-general/imprimir', 'GET', [
            'fecha_corte' => '2026-10-08',
        ]);
        $response2 = $controller->imprimirBalance($request2);
        $this->assertInstanceOf(\Illuminate\View\View::class, $response2);
        $html2 = $response2->render();
        $this->assertStringContainsString('BALANCE GENERAL', $html2);
        $this->assertStringContainsString('SUMA DEL ACTIVO', $html2);
        $this->assertStringContainsString('SUMA DEL PASIVO Y CAPITAL', $html2);
    }
}
