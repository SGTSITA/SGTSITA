<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Detalle de Gastos</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            margin: 20px;
            color: #333;
        }

        h2 {
            text-align: center;
            margin-bottom: 5px;
            color: #222;
        }

        .total-general {
            text-align: center;
            font-size: 14px;
            font-weight: bold;
            margin-bottom: 20px;
            color: #1c1c1c;
        }

        .contenedor-title {
            background-color: #f2f2f2;
            font-weight: bold;
            padding: 6px 10px;
            border: 1px solid #ccc;
            margin-top: 25px;
            margin-bottom: 5px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        th {
            background-color: #dddddd;
            padding: 6px;
            text-align: center;
            border: 1px solid #ccc;
        }

        td {
            padding: 6px;
            border: 1px solid #ccc;
            text-align: center;
        }

        .text-left {
            text-align: left;
        }

        .text-right {
            text-align: right;
        }

        .total-row {
            font-weight: bold;
            background-color: #acb1b6;
        }

        .page-break {
            page-break-after: always;
        }
    </style>
</head>

<body>

    <h2>Detalle de los Gastos</h2>

    @php
        $agrupado = $datos->groupBy('Contenedor');
        $totalGeneral = $datos->sum('Monto');
    @endphp

    <div class="total-general">
        Total de gastos: ${{ number_format($totalGeneral, 2, '.', ',') }}
    </div>

    @foreach ($agrupado as $contenedor => $gastos)
        <div class="contenedor-title">Contenedor: {{ $contenedor }}</div>

        <table>
            <thead>
                <tr>
                    <th class="text-left">Motivo</th>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                @php $subtotal = 0; @endphp
                @foreach ($gastos as $gasto)
                    @php $subtotal += $gasto['Monto']; @endphp
                    <tr>
                        <td class="text-left">{{ $gasto['Motivo'] }}</td>
                        <td>{{ $gasto['Fecha'] }}</td>
                        <td>{{ $gasto['Tipo'] }}</td>
                        <td class="text-right">${{ number_format($gasto['Monto'], 2, '.', ',') }}</td>

                    </tr>
                @endforeach
                <tr class="total-row">
                    <td colspan="3" class="text-right">Total del contenedor:</td>
                    <td>${{ number_format($subtotal, 2, '.', ',') }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

</body>

</html>
