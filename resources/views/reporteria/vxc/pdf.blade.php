<!DOCTYPE html>
<html lang="es">
    <head>
        <meta charset="UTF-8" />
        <title>Reporte: Viajes por Cobrar</title>
        <style>
            body {
                font-family:
                    DejaVu Sans,
                    sans-serif;
                font-size: 10px;
            }

            table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
            }

            th,
            td {
                border: 1px solid #ccc;
                padding: 4px;
                text-align: center;
                word-wrap: break-word;
            }

            th {
                background: #eee;
            }

            .totales {
                margin-top: 10px;
                font-size: 11px;
            }

            .totales p {
                margin: 2px 0;
            }
        </style>
    </head>

    <body>
        <h3 style="text-align: center">Reporte: Viajes por Cobrar</h3>

        <table>
            <thead>
                <tr>
                    <th>Contenedor</th>
                    <th>Subcliente</th>
                    <th>Importe</th>
                    <th>Tipo</th>
                    <th>Estatus</th>
                    <th>CP</th>
                    <th>XML</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($cotizaciones as $c)
                    <tr>
                        <td>{{ $c['num_contenedor'] ?? '-' }}</td>
                        <td>{{ $c['subcliente'] ?? '-' }}</td>
                        <td>${{ number_format(floatval($c['restante'] ?? 0), 2) }}</td>
                        <td>{{ $c['tipo_viaje'] ?? '-' }}</td>
                        <td>{{ $c['estatus'] ?? '-' }}</td>
                        <td>{!! ! empty($c['carta_porte']) ? '&#10004;' : '&#10008;' !!}</td>
                        <td>{!! ! empty($c['carta_porte_xml']) ? '&#10004;' : '&#10008;' !!}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="totales">
            <p>
                <strong>Total generado:</strong>
                ${{ number_format(floatval($totalGenerado), 2) }}
            </p>
            <p>
                <strong>Retenido:</strong>
                ${{ number_format(floatval($retenido), 2) }}
            </p>
            <p>
                <strong>Pago neto:</strong>
                ${{ number_format(floatval($pagoNeto), 2) }}
            </p>
        </div>
    </body>
</html>
