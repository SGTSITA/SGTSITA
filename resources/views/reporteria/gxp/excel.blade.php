<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            margin: 25px;
        }

        h2 {
            font-size: 18px;
            text-align: center;
            margin-top: 10px;
            margin-bottom: 40px;
        }

        .header-info {
            font-size: 14px;
            margin-bottom: 25px;
            text-align: left;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
            font-size: 12px;
        }

        th {
            background-color: #2c3e50;
            color: #ffffff;
            padding: 12px;
            border: 1px solid #2c3e50;
            text-align: center;
            font-size: 13px;
        }

        td {
            padding: 12px;
            border: 1px solid #ccc;
            text-align: center;
            background-color: #ffffff;
        }

        tr:nth-child(even) td {
            background-color: #f9f9f9;
        }

        /* Columnas más anchas */
        td:nth-child(1),
        th:nth-child(1) {
            width: 60px;
        }

        td:nth-child(2),
        th:nth-child(2),
        td:nth-child(3),
        th:nth-child(3),
        td:nth-child(4),
        th:nth-child(4),
        td:nth-child(5),
        th:nth-child(5),
        td:nth-child(6),
        th:nth-child(6),
        td:nth-child(7),
        th:nth-child(7),
        td:nth-child(8),
        th:nth-child(8),
        td:nth-child(9),
        th:nth-child(9) {
            width: 180px;
        }
    </style>
</head>

<body>
    <h2>Reporte de Gastos {{ $status === 'por_pagar' ? 'por Pagar' : 'Pendientes/Pagados' }}</h2>

    @if (isset($empresa))
        <div class="header-info">
            <strong>Empresa:</strong>
            {{ $empresa }}
        </div>
    @endif

    <br />
    <br />

    @if ($status === 'por_pagar')
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
                        <td>{{ $gasto['id'] }}</td>
                        <td>{{ $gasto['operador'] ?? '-' }}</td>
                        <td>{{ $gasto['cliente'] ?? '-' }}</td>
                        <td>{{ $gasto['subcliente'] ?? '-' }}</td>
                        <td>{{ $gasto['num_contenedor'] ?? '-' }}</td>
                        <td>${{ is_numeric($gasto['monto']) ? number_format($gasto['monto'], 2) : '0.00' }}</td>
                        <td>{{ $gasto['motivo'] ?? '-' }}</td>
                        <td>{{ $gasto['fecha_movimiento'] ?? '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="5"
                        style="text-align: right; font-weight: bold; font-size: 13px; background-color: #e5e7eb;">Total
                        General:</td>
                    <td style="font-weight: bold; font-size: 13px; background-color: #e5e7eb;">
                        ${{ number_format($totalGeneral, 2) }}</td>
                    <td colspan="2" style="background-color: #e5e7eb;"></td>
                </tr>
            </tfoot>
        </table>
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
                            style="background-color: #d1d5db; font-weight: bold; text-align: left; padding: 10px; font-size: 14px;">
                            {{ $categoria }}
                        </td>
                    </tr>
                    @php $subtotalCategoria = 0; @endphp

                    @foreach ($subcategorias as $subcategoria => $items)
                        <tr>
                            <td colspan="7"
                                style="background-color: #e5e7eb; font-weight: bold; text-align: left; padding: 10px; padding-left: 20px;">
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
                        style="text-align: right; font-weight: bold; font-size: 13px; background-color: #d1d5db;">Total
                        General:</td>
                    <td style="font-weight: bold; font-size: 13px; background-color: #d1d5db;">
                        ${{ number_format($totalGeneral, 2) }}</td>
                    <td colspan="4" style="background-color: #d1d5db;"></td>
                </tr>
            </tfoot>
        </table>
    @endif
</body>

</html>
