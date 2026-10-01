<?php

namespace App\Console\Commands;

use App\Models\Cotizaciones;
use App\Models\Viajes;
use App\Models\ViajesCostos;
use Illuminate\Support\Facades\DB;
use Illuminate\Console\Command;

class MigrarViajes extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'viajes:migrar {tipo=all}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrar cotizaciones a viajes (FULL y SENCILLOS)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🚀 Iniciando migración de viajes...');

        $tipo = $this->argument('tipo');

        if ($tipo === 'full' || $tipo === 'all') {
            $this->migrarFull();
        }

        if ($tipo === 'sencillo' || $tipo === 'all') {
            $this->migrarSencillos();
        }

        $this->info('✅ Migración completada');
    }


    private function migrarFull()
    {
        $this->info('🔵 Procesando FULL...');

        $mapaCostos = config('CatAuxiliares.costosViajes');

        $refs = Cotizaciones::whereNotNull('referencia_full')
            ->distinct()
            ->pluck('referencia_full');

        foreach ($refs as $ref) {

            $items = Cotizaciones::where('referencia_full', $ref)->get();

            if ($items->count() < 2) {
                continue;
            }

            $principal = $items->firstWhere('jerarquia', 'Principal');

            if (!$principal) {
                $this->error("❌ FULL sin principal: {$ref}");
                continue;
            }

            DB::transaction(function () use ($items, $principal, $mapaCostos) {

                $viaje = Viajes::create([
                    'tipo' => 'full',
                    'estado' => 'activo',
                ]);

                foreach ($items as $cotizacion) {
                    DB::table('viajes_cotizacion')->insert([
                        'viaje_id' => $viaje->id,
                        'cotizacion_id' => $cotizacion->id
                    ]);
                }

                foreach ($mapaCostos as $campo => $configCosto) {

                    if (!empty($configCosto['usa_meta'])) {
                        $this->procesarSobrepeso($viaje, $principal);
                        continue;
                    }

                    $monto = floatval($principal->$campo ?? 0);

                    if ($monto <= 0) {
                        continue;
                    }

                    ViajesCostos::create([
                        'viaje_id' => $viaje->id,
                        'concepto' => $configCosto['concepto'],
                        'monto' => $monto,
                        'tipo_operacion' => $configCosto['tipo_operacion'],
                    ]);
                }

            });

        }
    }

    private function migrarSencillos()
    {
        $this->info('🟢 Procesando SENCILLOS...');

        $mapaCostos = config('CatAuxiliares.costosViajes');

        Cotizaciones::whereNull('referencia_full')
            ->chunkById(100, function ($sencillos) use ($mapaCostos) {

                DB::transaction(function () use ($sencillos, $mapaCostos) {

                    foreach ($sencillos as $cotizacion) {

                        $viaje = Viajes::create([
                            'tipo' => 'sencillo',
                            'estado' => 'activo',
                        ]);

                        DB::table('viajes_cotizacion')->insert([
                            'viaje_id' => $viaje->id,
                            'cotizacion_id' => $cotizacion->id
                        ]);

                        foreach ($mapaCostos as $campo => $configCosto) {

                            if (!empty($configCosto['usa_meta'])) {
                                //  dd($configCosto['usa_meta']);
                                $this->procesarSobrepeso($viaje, $cotizacion);
                                continue;
                            }

                            $monto = floatval($cotizacion->$campo ?? 0);

                            if ($monto <= 0) {
                                continue;
                            }

                            ViajesCostos::create([
                                'viaje_id' => $viaje->id,
                                'concepto' => $configCosto['concepto'],
                                'monto' => $monto,
                                'tipo_operacion' => $configCosto['tipo_operacion'],
                            ]);
                        }

                    }

                });

            });
    }

    private function procesarSobrepeso($viaje, $request)
    {

        $precio = floatval($request->precio_sobre_peso ?? 0);
        $peso = floatval($request->sobrepeso ?? 0);


        $monto = $precio * $peso;
        if ($precio > 0 && $peso > 0) {
            $this->info('Procesando sobrepeso: Precio=' . $precio . ' Peso=' . $peso . ' Monto=' . $monto);
        }




        if ($monto > 0) {

            ViajesCostos::updateOrCreate(
                [
                'viaje_id' => $viaje->id,
                'concepto' => 'sobrepeso'
                ],
                [
                'monto' => $monto,
                'tipo_operacion' => 'cargo',
                'meta' => [
                    'precio_sobre_peso' => $request->precio_sobre_peso,
                    'peso' => $peso,
                    'precio_tonelada' => $monto,
                ]
                ]
            );

        } else {

            ViajesCostos::where('viaje_id', $viaje->id)
                ->where('concepto', 'sobrepeso')
                ->delete();
        }
    }
}
