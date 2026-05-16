<?php

namespace App\Services;

use App\Models\ViajesCostos;

class ViajesCostosService
{
    public function syncDesdeRequest($viaje, $request)
    {
        $this->procesarCostosBase($viaje, $request);
        $this->procesarSobrepeso($viaje, $request);
    }

    public function eliminarCostos($viaje)
    {
        $viaje->costos()->delete();
    }

    public function recalcularTotales($viaje)
    {
        return $viaje->costos->sum(function ($costo) {
            return $costo->tipo_operacion === 'descuento'
                ? -$costo->monto
                : $costo->monto;
        });
    }
    private function procesarCostosBase($viaje, $request)
    {
        $mapaCostos = config('CatAuxiliares.costosViajes');

        foreach ($mapaCostos as $input => $configCosto) {


            if (!empty($configCosto['usa_meta'])) {
                continue;
            }

            $monto = floatval(str_replace(',', '', $request->$input));

            if ($monto > 0) {

                ViajesCostos::updateOrCreate(
                    [
                        'viaje_id' => $viaje->id,
                        'concepto' => $configCosto['concepto'],
                    ],
                    [
                        'monto' => $monto,
                        'tipo_operacion' => $configCosto['tipo_operacion'],
                    ]
                );

            } else {

                ViajesCostos::where('viaje_id', $viaje->id)
                    ->where('concepto', $configCosto['concepto'])
                    ->delete();
            }
        }
    }
    private function procesarSobrepeso($viaje, $request)
    {

        $precio = floatval($request->precio_sobre_peso ?? 0);
        $peso = floatval($request->sobrePeso ?? 0);


        $monto = $precio * $peso;




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
