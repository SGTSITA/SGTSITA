<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Http\Controllers\LiquidacionesController;
use App\Services\BancosService;
use App\Services\EquipoService;
use App\Models\Liquidaciones;
use Mockery;

class LiquidacionesHistorialFiltroTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    /** @test */
    public function el_controlador_puede_inyectar_equiposervice()
    {
        $bancosService = Mockery::mock(BancosService::class);
        $equipoService = Mockery::mock(EquipoService::class);

        $controller = new LiquidacionesController($bancosService, $equipoService);

        $this->assertInstanceOf(LiquidacionesController::class, $controller);
    }

    /** @test */
    public function la_subconsulta_where_exists_se_genera_correctamente_cuando_se_filtra_por_equipo()
    {
        $idEquipo = 42;
        $idOperadores = [1, 2, 3];

        $query = Liquidaciones::query()->whereIn('id_operador', $idOperadores);

        $query->whereExists(function ($sub) use ($idEquipo) {
            $sub->select(\Illuminate\Support\Facades\DB::raw(1))
                ->from('liquidacion_contenedor')
                ->join('asignaciones', 'asignaciones.id_contenedor', '=', 'liquidacion_contenedor.id_contenedor')
                ->whereColumn('liquidacion_contenedor.id_liquidacion', 'liquidaciones.id')
                ->where('asignaciones.id_camion', $idEquipo);
        });

        $sql = $query->toSql();

        $this->assertStringContainsString('exists (select 1 from `liquidacion_contenedor`', $sql);
        $this->assertStringContainsString('inner join `asignaciones` on `asignaciones`.`id_contenedor` = `liquidacion_contenedor`.`id_contenedor`', $sql);
        $this->assertStringContainsString('`liquidacion_contenedor`.`id_liquidacion` = `liquidaciones`.`id`', $sql);
        $this->assertStringContainsString('`asignaciones`.`id_camion` = ?', $sql);
        $this->assertContains($idEquipo, $query->getBindings());
    }
}
