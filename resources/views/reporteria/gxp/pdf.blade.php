<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <title>Gastos por Pagar</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 12px;
            }

            h2 {
                text-align: center;
                background-color: #f2f2f2;
                padding: 12px;
                margin-bottom: 20px;
                font-size: 18px;
                border: 1px solid #ccc;
            }

            .header-info {
                font-size: 14px;
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
                /* color azul oscuro */
                color: #ffffff;
                /* texto blanco */
                padding: 8px;
                border: 1px solid #000;
                text-align: center;
            }

            td {
                border: 1px solid #000;
                padding: 6px 8px;
                text-align: center;
            }

            tr:nth-child(even) td {
                background-color: #f9f9f9;
            }
        </style>
    </head>

    <body>
        <h2>Reporte de Gastos por Pagar</h2>

        <div class="header-info">
            <strong>Empresa:</strong>
            {{ $empresa ?? '---' }}
        </div>

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
                @foreach ($gastos as $gasto)
                    <tr>
                        <td>{{ $gasto['id'] ?? '-' }}</td>
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
        </table>
    </body>
</html>
