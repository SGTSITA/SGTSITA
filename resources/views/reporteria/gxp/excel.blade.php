<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
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

        /* ⬅️ Columnas más anchas */
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
            /* el doble de lo anterior */
        }
    </style>
</head>

<body>
    <h2>Reporte de Gastos por Pagar</h2>

    @if (isset($empresa))
        <div class="header-info">
            <strong>Empresa:</strong> {{ $empresa }}
        </div>
    @endif

    <br><br>

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
                <th>Fecha Aplicación</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gastos as $gasto)
                <tr>
                    <td>{{ $gasto['id'] }}</td>
                    <td>{{ $gasto['operador'] ?? '-' }}</td>
                    <td>{{ $gasto['cliente'] ?? '-' }}</td>
                    <td>{{ $gasto['subcliente'] ?? '-' }}</td>
                    <td>{{ $gasto['num_contenedor'] ?? '-' }}</td>
                    <td>${{ is_numeric($gasto['monto']) ? number_format($gasto['monto'], 2) : '0.00' }}</td>
                    <td>{{ $gasto['motivo'] ?? '-' }}</td>
                    <td>{{ $gasto['fecha_movimiento'] ?? '-' }}</td>
                    <td>{{ $gasto['fecha_aplicacion'] ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
