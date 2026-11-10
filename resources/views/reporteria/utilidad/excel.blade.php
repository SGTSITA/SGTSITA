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
            $esEmpresaGlobal = $empresaActual ? $empresaActual->esGlobalMultiEquipo() : false;
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
            @endphp

            @foreach ($cotizacionesPorUnidad as $unidadTexto => $itemsUnidad)
                <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;" border="1">
                    <thead>
                        <tr>
                            <th colspan="10" style="background-color: #2c3e50; color: #ffffff; text-align: left; font-weight: bold;">
                                🚛 UNIDAD / TRACTOCAMIÓN: {{ $unidadTexto }} ({{ count($itemsUnidad) }} viajes)
                            </th>
                        </tr>
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
                        <tr style="background-color: #e9ecef; font-weight: bold;">
                            <td colspan="2">SUBTOTAL UNIDAD ({{ $unidadTexto }})</td>
                            <td>{{ number_format(floatval(collect($itemsUnidad)->sum('precioViaje')), 2) }}</td>
                            <td>{{ number_format(floatval(collect($itemsUnidad)->sum('pagoOperacion')), 2) }}</td>
                            <td>{{ number_format(floatval(collect($itemsUnidad)->sum('gastosExtra')), 2) }}</td>
                            <td>{{ number_format(floatval(collect($itemsUnidad)->sum('dineroViajeSinJustificar')), 2) }}</td>
                            <td>{{ number_format(floatval(collect($itemsUnidad)->sum('gastosViaje')), 2) }}</td>
                            <td>{{ number_format(floatval(collect($itemsUnidad)->sum('gastosDiferidos')), 2) }}</td>
                            <td>{{ number_format(floatval(collect($itemsUnidad)->sum('utilidad')), 2) }}</td>
                            <td></td>
                        </tr>
                    </tfoot>
                </table>
            @endforeach

            @if ($cotizacionesPorUnidad->count() > 1)
                <table style="width: 100%; border-collapse: collapse; margin-top: 10px;" border="1">
                    <tfoot>
                        <tr style="background-color: #344767; color: #ffffff; font-weight: bold;">
                            <td colspan="2" style="color: #ffffff;">GRAN TOTAL CONSOLIDADO (TODAS LAS UNIDADES)</td>
                            <td style="color: #ffffff;">{{ number_format(floatval($cotizaciones->sum('precioViaje')), 2) }}</td>
                            <td style="color: #ffffff;">{{ number_format(floatval($cotizaciones->sum('pagoOperacion')), 2) }}</td>
                            <td style="color: #ffffff;">{{ number_format(floatval($cotizaciones->sum('gastosExtra')), 2) }}</td>
                            <td style="color: #ffffff;">{{ number_format(floatval($cotizaciones->sum('dineroViajeSinJustificar')), 2) }}</td>
                            <td style="color: #ffffff;">{{ number_format(floatval($cotizaciones->sum('gastosViaje')), 2) }}</td>
                            <td style="color: #ffffff;">{{ number_format(floatval($cotizaciones->sum('gastosDiferidos')), 2) }}</td>
                            <td style="color: #ffffff;">{{ number_format(floatval($cotizaciones->sum('utilidad')), 2) }}</td>
                            <td style="color: #ffffff;"></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endif

        <!-- TABLA DE GASTOS -->
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
    </body>
</html>
