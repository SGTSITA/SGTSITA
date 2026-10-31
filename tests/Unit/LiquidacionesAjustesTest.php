<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\LiquidacionesAjustesController;
use App\Models\Liquidaciones;
use App\Models\LiquidacionAjuste;
use App\Models\LiquidacionAjusteDetalle;
use App\Services\BancosService;
use Mockery;

class LiquidacionesAjustesTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function el_controlador_puede_instanciarse_con_bancos_service()
    {
        $bancosService = Mockery::mock(BancosService::class);
        $controller = new LiquidacionesAjustesController($bancosService);

        $this->assertInstanceOf(LiquidacionesAjustesController::class, $controller);
    }

    /** @test */
    public function el_modelo_liquidacion_ajuste_calcula_relaciones_y_casts()
    {
        $ajuste = new LiquidacionAjuste([
            'id_empresa' => 28,
            'id_liquidacion' => 10,
            'id_operador' => 5,
            'fecha' => '2026-10-26',
            'motivo' => 'Bono de maniobra foranea',
            'total_percepciones' => 500.00,
            'total_deducciones' => 100.00,
            'total_neto' => 400.00,
            'estatus' => 'aplicado',
        ]);

        $this->assertEquals(500.00, $ajuste->total_percepciones);
        $this->assertEquals(100.00, $ajuste->total_deducciones);
        $this->assertEquals(400.00, $ajuste->total_neto);
        $this->assertEquals('aplicado', $ajuste->estatus);
    }

    /** @test */
    public function el_modelo_liquidacion_ajuste_detalle_devuelve_el_nombre_formateado_del_concepto()
    {
        $detalleSueldo = new LiquidacionAjusteDetalle([
            'tipo' => 'percepcion',
            'categoria_concepto' => 'sueldo_operador',
            'monto' => 1200.00,
        ]);
        $this->assertEquals('Sueldo Operador', $detalleSueldo->nombre_concepto);

        $detallePrestamo = new LiquidacionAjusteDetalle([
            'tipo' => 'deduccion',
            'categoria_concepto' => 'abono_prestamo',
            'monto' => 300.00,
        ]);
        $this->assertEquals('Abono a Préstamo', $detallePrestamo->nombre_concepto);

        $detalleViatico = new LiquidacionAjusteDetalle([
            'tipo' => 'percepcion',
            'categoria_concepto' => 'dinero_viaje',
            'monto' => 250.00,
        ]);
        $this->assertEquals('Dinero de Viaje / Viáticos', $detalleViatico->nombre_concepto);
    }

    /** @test */
    public function el_modelo_liquidaciones_tiene_la_relacion_ajustes()
    {
        $liquidacion = new Liquidaciones();
        $relacion = $liquidacion->Ajustes();

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Relations\HasMany::class, $relacion);
        $this->assertEquals('id_liquidacion', $relacion->getForeignKeyName());
    }
}
