<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <title>Gastos por Pagar</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
        }

        h2 {
            text-align: center;
            background-color: #f2f2f2;
            padding: 10px;
            margin-bottom: 15px;
            font-size: 16px;
            border: 1px solid #ccc;
        }

        .header-info {
            font-size: 12px;
            margin-bottom: 15px;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 6px;
            border: 1px solid #000;
            text-align: center;
            font-size: 11px;
        }

        td {
            border: 1px solid #000;
            padding: 5px 6px;
            text-align: center;
            font-size: 10px;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }
    </style>
</head>

<body>
    <h2>Reporte de Gastos {{ $status === 'por_pagar' ? 'por Pagar' : 'Pendientes/Pagados' }}</h2>

    <div class="header-info">
        <strong>Empresa:</strong>
        {{ $empresa ?? '---' }}
    </div>

    @php
        $empresaActual = auth()->user()?->Empresa ?? null;
        $esEmpresaGlobal = $empresaActual ? $empresaActual->esGlobalMultiEquipo() : false;
    @endphp

    @if ($status === 'por_pagar')
        @if (!$esEmpresaGlobal)
            {{-- MODO TRADICIONAL (Configuración Global = 0): Diseño 100% original e intacto --}}
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Operador</th>
                        <th>Cliente</th>
                        <th>Subcliente</th>
                        <th>Contenedor</th>
                        <th>Monto</th>
                        <th>Motivo</th>
                        <th>Fecha Movimiento</th>
                    </tr>
                </thead>
                <tbody>
                    @php $totalGeneral = 0; @endphp
                    @foreach ($gastos as $gasto)
                        @php $totalGeneral += is_numeric($gasto['monto']) ? $gasto['monto'] : 0; @endphp
                        <tr>
                            <td>{{ $gasto['id'] ?? '-' }}</td>
                            <td>{{ $gasto['operador'] ?? '-' }}</td>
                            <td>{{ $gasto['cliente'] ?? '-' }}</td>
                            <td>{{ $gasto['subcliente'] ?? '-' }}</td>
                            <td>{{ $gasto['num_contenedor'] ?? '-' }}</td>
                            <td>${{ is_numeric($gasto['monto']) ? number_format(floatval($gasto['monto']), 2) : '0.00' }}</td>
                            <td>{{ $gasto['motivo'] ?? '-' }}</td>
                            <td>{{ $gasto['fecha_movimiento'] ?? '-' }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="5"
                            style="text-align: right; font-weight: bold; font-size: 11px; background-color: #e5e7eb;">Total
                            General:</td>
                        <td style="font-weight: bold; font-size: 11px; background-color: #e5e7eb;">
                            ${{ number_format($totalGeneral, 2) }}</td>
                        <td colspan="2" style="background-color: #e5e7eb;"></td>
                    </tr>
                </tfoot>
            </table>
        @else
            {{-- MODO EMPRESA GLOBAL (H00): Agrupación separada por Unidad / Tractocamión con subtotales y gran total --}}
            @php
                $gastosPorUnidad = collect($gastos)->groupBy(function($item) {
                    return $item['unidad'] ?? 'SIN UNIDAD ASIGNADA';
                });
                $totalConsolidado = 0;
            @endphp

            @foreach ($gastosPorUnidad as $unidadTexto => $itemsUnidad)
                @php
                    $subtotalUnidad = collect($itemsUnidad)->sum(function($item) {
                        return is_numeric($item['monto']) ? $item['monto'] : 0;
                    });
                    $totalConsolidado += $subtotalUnidad;
                @endphp

                <div style="background-color: #2c3e50; color: #ffffff; padding: 5px 10px; font-size: 11px; font-weight: bold; margin-top: {{ $loop->first ? '0px' : '15px' }}; margin-bottom: 5px; border-radius: 3px;">
                    🚛 UNIDAD / TRACTOCAMIÓN: {{ $unidadTexto }} ({{ count($itemsUnidad) }} gastos pendientes)
                </div>

                <table style="margin-top: 5px; margin-bottom: 10px;">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th>ID</th>
                            <th>Operador</th>
                            <th>Cliente</th>
                            <th>Subcliente</th>
                            <th>Contenedor</th>
                            <th>Monto</th>
                            <th>Motivo</th>
                            <th>Fecha Movimiento</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($itemsUnidad as $gasto)
                            <tr>
                                <td>{{ $gasto['id'] ?? '-' }}</td>
                                <td>{{ $gasto['operador'] ?? '-' }}</td>
                                <td>{{ $gasto['cliente'] ?? '-' }}</td>
                                <td>{{ $gasto['subcliente'] ?? '-' }}</td>
                                <td>{{ $gasto['num_contenedor'] ?? '-' }}</td>
                                <td>${{ is_numeric($gasto['monto']) ? number_format(floatval($gasto['monto']), 2) : '0.00' }}</td>
                                <td>{{ $gasto['motivo'] ?? '-' }}</td>
                                <td>{{ $gasto['fecha_movimiento'] ?? '-' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr style="background-color: #e9ecef; font-weight: bold;">
                            <td colspan="5" style="text-align: right; padding: 6px;">SUBTOTAL UNIDAD ({{ $unidadTexto }}):</td>
                            <td style="padding: 6px;">${{ number_format($subtotalUnidad, 2) }}</td>
                            <td colspan="2"></td>
                        </tr>
                    </tfoot>
                </table>
            @endforeach

            @if ($gastosPorUnidad->count() > 1)
                <table style="margin-top: 15px; border-collapse: collapse;">
                    <tfoot>
                        <tr style="background-color: #344767; color: #ffffff; font-weight: bold; font-size: 11px;">
                            <td colspan="5" style="text-align: right; color: #ffffff; padding: 8px;">GRAN TOTAL CONSOLIDADO GASTOS (TODAS LAS UNIDADES):</td>
                            <td style="color: #ffffff; padding: 8px;">${{ number_format($totalConsolidado, 2) }}</td>
                            <td colspan="2" style="color: #ffffff;"></td>
                        </tr>
                    </tfoot>
                </table>
            @endif
        @endif
    @else
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Motivo o Concepto</th>
                    <th>Importe</th>
                    <th>Fecha</th>
                    <th>Fecha Aplicación</th>
                    <th>Cuenta/Banco</th>
                    <th>Vínculos</th>
                </tr>
            </thead>
            <tbody>
                @php $totalGeneral = 0; @endphp
                @foreach ($gastos as $categoria => $subcategorias)
                    <tr>
                        <td colspan="7"
                            style="background-color: #d1d5db; font-weight: bold; text-align: left; padding: 6px;">
                            {{ $categoria }}
                        </td>
                    </tr>
                    @php $subtotalCategoria = 0; @endphp

                    @foreach ($subcategorias as $subcategoria => $items)
                        <tr>
                            <td colspan="7"
                                style="background-color: #e5e7eb; font-weight: bold; text-align: left; padding: 6px; padding-left: 15px;">
                                Subcategoría: {{ $subcategoria }}
                            </td>
                        </tr>

                        @php $subtotalSubcategoria = 0; @endphp
                        @foreach ($items as $gasto)
                            @php
                                $importe = is_numeric($gasto['importe']) ? $gasto['importe'] : 0;
                                $subtotalSubcategoria += $importe;
                            @endphp
                            <tr>
                                <td>{{ $gasto['id'] }}</td>
                                <td>{{ $gasto['motivo'] ?? '-' }}</td>
                                <td>${{ number_format($importe, 2) }}</td>
                                <td>{{ $gasto['fecha'] ?? '-' }}</td>
                                <td>{{ $gasto['fecha_aplicacion'] ?? '-' }}</td>
                                <td>{{ !empty($gasto['cuenta_banco']) && strlen($gasto['cuenta_banco']) >= 4 ? '********' . substr($gasto['cuenta_banco'], -4) : '-' }}
                                </td>
                                <td>{{ $gasto['vinculos'] ?? '-' }}</td>
                            </tr>
                        @endforeach

                        <tr>
                            <td colspan="2" style="text-align: right; font-weight: bold; background-color: #f3f4f6;">
                                Subtotal {{ $subcategoria }}:</td>
                            <td style="font-weight: bold; background-color: #f3f4f6;">
                                ${{ number_format($subtotalSubcategoria, 2) }}</td>
                            <td colspan="4" style="background-color: #f3f4f6;"></td>
                        </tr>
                        @php $subtotalCategoria += $subtotalSubcategoria; @endphp
                    @endforeach

                    <tr>
                        <td colspan="2" style="text-align: right; font-weight: bold; background-color: #e5e7eb;">
                            Total {{ $categoria }}:</td>
                        <td style="font-weight: bold; background-color: #e5e7eb;">
                            ${{ number_format($subtotalCategoria, 2) }}</td>
                        <td colspan="4" style="background-color: #e5e7eb;"></td>
                    </tr>
                    @php $totalGeneral += $subtotalCategoria; @endphp
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2"
                        style="text-align: right; font-weight: bold; font-size: 11px; background-color: #d1d5db;">Total
                        General:</td>
                    <td style="font-weight: bold; font-size: 11px; background-color: #d1d5db;">
                        ${{ number_format($totalGeneral, 2) }}</td>
                    <td colspan="4" style="background-color: #d1d5db;"></td>
                </tr>
            </tfoot>
        </table>
    @endif
</body>

</html>
