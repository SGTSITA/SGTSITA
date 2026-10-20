<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>Reporte de Resultados</title>
    </head>

    <body>
        <!-- ENCABEZADO -->
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px" border="1">
            <tr>
                <td>
                    <h2>Reporte de Resultados</h2>
                    <p>
                        <strong>Empresa:</strong>
                        {{ $user->Empresa->nombre }}
                    </p>
                    <p>
                        <strong>Periodo:</strong>
                        {{ date('d-m-Y', strtotime($fechaInicio)) }} al {{ date('d-m-Y', strtotime($fechaFin)) }}
                    </p>
                    <p>
                        <strong>Contenedores seleecionados:</strong>
                        {{ $selectedRows }} de {{ $totalRows }}
                    </p>
                </td>
                <td>
                    <p>
                        <strong>Utilidad Bruta:</strong>
                        ${{ number_format($utilidad, 2) }}
                    </p>
                    <p>
                        <strong>Otros Gastos:</strong>
                        ${{ number_format($gastos, 2) }}
                    </p>
                    <p>
                        <strong>Utilidad Neta:</strong>
                        ${{ number_format($utilidad - $gastos, 2) }}
                    </p>
                </td>
            </tr>
        </table>

        @php
            $empresaActual = $user?->Empresa ?? null;
            $esEmpresaGlobal = !empty($separarUnidades);
        @endphp

        <!-- TABLA PRINCIPAL -->
        @if (!$esEmpresaGlobal)
            <table style="width: 100%; border-collapse: collapse" border="1">
                <thead>
                    <tr style="background-color: #f0f0f0">
                        <th>Núm Contenedor</th>
                        <th>Cliente</th>
                        <th>Precio Viaje</th>
                        <th>Pago Operación</th>
                        <th>Gastos Extra</th>
                        <th>Dinero Viaje S/Justificar</th>
                        <th>Gastos Viaje</th>
                        <th>Gastos Diferidos</th>
                        <th>Utilidad</th>
                        <th>Transportado Por</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($cotizaciones as $cotizacion)
                        <tr>
                            <td>{{ $cotizacion['numContenedor'] }}</td>
                            <td>{{ $cotizacion['cliente'] }}</td>
                            <td>{{ number_format(floatval($cotizacion['precioViaje'] ?? 0), 2) }}</td>
                            <td>{{ number_format(floatval($cotizacion['pagoOperacion'] ?? 0), 2) }}</td>
                            <td>{{ number_format(floatval($cotizacion['gastosExtra'] ?? 0), 2) }}</td>
                            <td>{{ number_format(floatval($cotizacion['dineroViajeSinJustificar'] ?? 0), 2) }}</td>
                            <td>{{ number_format(floatval($cotizacion['gastosViaje'] ?? 0), 2) }}</td>
                            <td>{{ number_format(floatval($cotizacion['gastosDiferidos'] ?? 0), 2) }}</td>
                            <td @if (($cotizacion['utilidad'] ?? 0) < 0) style="background-color:#ffc107;" @endif>
                                {{ number_format(floatval($cotizacion['utilidad'] ?? 0), 2) }}
                            </td>
                            <td>{{ $cotizacion['transportadoPor'] }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            @php
                $cotizacionesPorUnidad = $cotizaciones->groupBy(function($item) {
                    return $item['unidad'] ?? 'SIN UNIDAD ASIGNADA';
                });
                $gastosPorUnidad = $gastosGenerales->groupBy(function($gasto) {
                    return $gasto->unidad_texto ?? 'SIN UNIDAD ASIGNADA';
                });

                $todasLasUnidades = $cotizacionesPorUnidad->keys()
                    ->merge($gastosPorUnidad->keys())
                    ->unique()
                    ->filter(function($u) {
                        return $u !== 'SIN UNIDAD ASIGNADA';
                    })
                    ->values();
            @endphp

            @foreach ($todasLasUnidades as $unidadTexto)
                @php
                    $itemsUnidad = $cotizacionesPorUnidad->get($unidadTexto, collect([]));
                    $gastosUnidadItem = $gastosPorUnidad->get($unidadTexto, collect([]));
                    $brutoUnidad = floatval(collect($itemsUnidad)->sum('utilidad'));
                    $otrosGastosUnidad = floatval(collect($gastosUnidadItem)->sum('monto_aplicado'));
                    $netoUnidad = $brutoUnidad - $otrosGastosUnidad;
                @endphp

                <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;" border="1">
                    <thead>
                        <tr>
                            <th colspan="10" style="background-color: #2c3e50; color: #ffffff; text-align: left; font-weight: bold;">
                                🚛 UNIDAD / TRACTOCAMIÓN: {{ $unidadTexto }} ({{ count($itemsUnidad) }} viajes | {{ count($gastosUnidadItem) }} otros gastos)
                            </th>
                        </tr>
                        @if (count($itemsUnidad) > 0)
                            <tr style="background-color: #f0f0f0">
                                <th>Núm Contenedor</th>
                                <th>Cliente</th>
                                <th>Precio Viaje</th>
                                <th>Pago Operación</th>
                                <th>Gastos Extra</th>
                                <th>Dinero Viaje S/Justificar</th>
                                <th>Gastos Viaje</th>
                                <th>Gastos Diferidos</th>
                                <th>Utilidad</th>
                                <th>Transportado Por</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @foreach ($itemsUnidad as $cotizacion)
                            <tr>
                                <td>{{ $cotizacion['numContenedor'] }}</td>
                                <td>{{ $cotizacion['cliente'] }}</td>
                                <td>{{ number_format(floatval($cotizacion['precioViaje'] ?? 0), 2) }}</td>
                                <td>{{ number_format(floatval($cotizacion['pagoOperacion'] ?? 0), 2) }}</td>
                                <td>{{ number_format(floatval($cotizacion['gastosExtra'] ?? 0), 2) }}</td>
                                <td>{{ number_format(floatval($cotizacion['dineroViajeSinJustificar'] ?? 0), 2) }}</td>
                                <td>{{ number_format(floatval($cotizacion['gastosViaje'] ?? 0), 2) }}</td>
                                <td>{{ number_format(floatval($cotizacion['gastosDiferidos'] ?? 0), 2) }}</td>
                                <td @if (($cotizacion['utilidad'] ?? 0) < 0) style="background-color:#ffc107;" @endif>
                                    {{ number_format(floatval($cotizacion['utilidad'] ?? 0), 2) }}
                                </td>
                                <td>{{ $cotizacion['transportadoPor'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        @if (count($itemsUnidad) > 0)
                            <tr style="background-color: #e9ecef; font-weight: bold;">
                                <td colspan="2">SUBTOTAL VIAJES UNIDAD ({{ $unidadTexto }})</td>
                                <td>{{ number_format(floatval(collect($itemsUnidad)->sum('precioViaje')), 2) }}</td>
                                <td>{{ number_format(floatval(collect($itemsUnidad)->sum('pagoOperacion')), 2) }}</td>
                                <td>{{ number_format(floatval(collect($itemsUnidad)->sum('gastosExtra')), 2) }}</td>
                                <td>{{ number_format(floatval(collect($itemsUnidad)->sum('dineroViajeSinJustificar')), 2) }}</td>
                                <td>{{ number_format(floatval(collect($itemsUnidad)->sum('gastosViaje')), 2) }}</td>
                                <td>{{ number_format(floatval(collect($itemsUnidad)->sum('gastosDiferidos')), 2) }}</td>
                                <td>{{ number_format($brutoUnidad, 2) }}</td>
                                <td></td>
                            </tr>
                        @endif
                    </tfoot>
                </table>

                @if (count($gastosUnidadItem) > 0)
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 5px;" border="1">
                        <thead>
                            <tr style="background-color: #f7f9fa">
                                <th colspan="5" style="font-weight: bold; text-align: left;">
                                    Otros Gastos Asociados a la Unidad ({{ count($gastosUnidadItem) }})
                                </th>
                            </tr>
                            <tr style="background-color: #f0f0f0">
                                <th>Fecha</th>
                                <th>Motivo / Concepto</th>
                                <th>Categoría</th>
                                <th>Método de Pago</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gastosUnidadItem as $gastoU)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($gastoU->fecha_aplicada)->format('d-m-Y') }}</td>
                                    <td>{{ $gastoU->concepto }}</td>
                                    <td>{{ $gastoU->categoria->categoria ?? 'N/A' }}</td>
                                    <td>{{ $gastoU->pagos->first()->metodo_pago ?? 'N/A' }}</td>
                                    <td>{{ number_format($gastoU->monto_aplicado, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr style="font-weight: bold; background-color: #f1f3f5;">
                                <td colspan="4" style="text-align: right">Subtotal Otros Gastos Unidad:</td>
                                <td style="color: #dc3545;">{{ number_format($otrosGastosUnidad, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif

                {{-- RESUMEN UNIDAD --}}
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 15px;" border="1">
                    <tr style="font-weight: bold; text-align: center; background-color: #fdfdfe;">
                        <td colspan="3">Utilidad Bruta Unidad: ${{ number_format($brutoUnidad, 2) }}</td>
                        <td colspan="3">Otros Gastos Unidad: ${{ number_format($otrosGastosUnidad, 2) }}</td>
                        <td colspan="4" style="background-color: {{ $netoUnidad < 0 ? '#fff3cd' : '#e8f5e9' }};">
                            Utilidad Neta Unidad: ${{ number_format($netoUnidad, 2) }}
                        </td>
                    </tr>
                </table>
            @endforeach

            {{-- SECCIÓN GASTOS SIN UNIDAD --}}
            @php
                $gastosHuerfanos = $gastosPorUnidad->get('SIN UNIDAD ASIGNADA', collect([]));
                $viajesHuerfanos = $cotizacionesPorUnidad->get('SIN UNIDAD ASIGNADA', collect([]));
            @endphp

            @if ($viajesHuerfanos->isNotEmpty() || $gastosHuerfanos->isNotEmpty())
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;" border="1">
                    <thead>
                        <tr>
                            <th colspan="10" style="background-color: #e74c3c; color: #ffffff; text-align: left; font-weight: bold;">
                                ⚠️ MOVIMIENTOS / GASTOS SIN UNIDAD ASIGNADA
                            </th>
                        </tr>
                    </thead>
                </table>

                @if ($gastosHuerfanos->isNotEmpty())
                    <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;" border="1">
                        <thead>
                            <tr style="background-color: #f0f0f0">
                                <th>Fecha</th>
                                <th>Motivo / Concepto</th>
                                <th>Categoría</th>
                                <th>Método de Pago</th>
                                <th>Monto</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($gastosHuerfanos as $gastoH)
                                <tr>
                                    <td>{{ \Carbon\Carbon::parse($gastoH->fecha_aplicada)->format('d-m-Y') }}</td>
                                    <td>{{ $gastoH->concepto }}</td>
                                    <td>{{ $gastoH->categoria->categoria ?? 'N/A' }}</td>
                                    <td>{{ $gastoH->pagos->first()->metodo_pago ?? 'N/A' }}</td>
                                    <td>{{ number_format($gastoH->monto_aplicado, 2) }}</td>
                                </tr>
                            @endforeach
                            <tr style="font-weight: bold; background-color: #f1f3f5;">
                                <td colspan="4" style="text-align: right">Total Gastos Sin Unidad Asignada:</td>
                                <td style="color: #dc3545;">{{ number_format(floatval(collect($gastosHuerfanos)->sum('monto_aplicado')), 2) }}</td>
                            </tr>
                        </tbody>
                    </table>
                @endif
            @endif

            {{-- GRAN TOTAL CONSOLIDADO --}}
            @php
                $granTotalBruto = floatval($cotizaciones->sum('utilidad'));
                $granTotalGastos = floatval($gastosGenerales->sum('monto_aplicado'));
                $granTotalNeto = $granTotalBruto - $granTotalGastos;
            @endphp
            <table style="width: 100%; border-collapse: collapse; margin-top: 10px;" border="1">
                <tfoot>
                    <tr style="background-color: #344767; color: #ffffff; font-weight: bold;">
                        <td colspan="4" style="color: #ffffff;">GRAN TOTAL CONSOLIDADO (TODAS LAS UNIDADES)</td>
                        <td colspan="2" style="color: #ffffff;">Utilidad Bruta: ${{ number_format($granTotalBruto, 2) }}</td>
                        <td colspan="2" style="color: #ffffff;">Otros Gastos: ${{ number_format($granTotalGastos, 2) }}</td>
                        <td colspan="2" style="color: #ffffff;">Utilidad Neta: ${{ number_format($granTotalNeto, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        @endif

        {{-- TABLA DE GASTOS TRADICIONAL (SÓLO CUANDO NO SE SEPARA POR UNIDAD) --}}
        @if (!$esEmpresaGlobal)
            <h4 style="margin-top: 20px">Desglose de Otros Gastos</h4>
            <table style="width: 100%; border-collapse: collapse" border="1">
                <thead style="background-color: #f0f0f0">
                    <tr>
                        <th>Fecha</th>
                        <th>Motivo</th>
                        <th>Categoría</th>
                        <th>Método de Pago</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($gastosGenerales as $gasto)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($gasto->fecha_aplicada)->format('d-m-Y') }}</td>
                            <td>{{ $gasto->concepto }}</td>
                            <td>{{ $gasto->categoria->categoria ?? 'N/A' }}</td>
                            <td>{{ $gasto->pagos->first()->metodo_pago ?? 'N/A' }}</td>
                            <td>{{ number_format($gasto->monto_aplicado, 2) }}</td>
                        </tr>
                    @endforeach

                    <tr style="font-weight: bold">
                        <td colspan="4" style="text-align: right">Total:</td>
                        <td>{{ number_format($gastos, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif
    </body>
</html>
