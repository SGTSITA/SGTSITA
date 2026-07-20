@php
    $resumen = $reporte['resumen'] ?? [];
    $rows = $reporte['rows'] ?? [];
@endphp

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 9px;
            color: #222;
        }

        .titulo {
            text-align: center;
            font-size: 17px;
            font-weight: bold;
            margin-bottom: 4px;
        }

        .subtitulo {
            text-align: center;
            font-size: 10px;
            color: #555;
            margin-bottom: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        .filtros {
            margin-bottom: 10px;
        }

        .filtros td {
            border: 1px solid #ddd;
            padding: 5px;
        }

        .label {
            background: #f1f3f5;
            font-weight: bold;
        }

        .resumen {
            margin-bottom: 12px;
        }

        .resumen th {
            background: #344767;
            color: #fff;
            border: 1px solid #344767;
            padding: 5px;
            text-align: center;
        }

        .resumen td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
            font-weight: bold;
        }

        .tabla th {
            background: #344767;
            color: #fff;
            border: 1px solid #344767;
            padding: 4px;
            text-align: center;
            font-size: 8px;
        }

        .tabla td {
            border: 1px solid #ddd;
            padding: 4px;
            vertical-align: top;
            font-size: 8px;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="titulo">Reporte de consumo por unidad</div>
    <div class="subtitulo">
        Consulta kilómetros, litros diesel y rendimiento por viaje
    </div>

    <table class="filtros">
        <tr>
            <td class="label">Unidad</td>
            <td>{{ $filtros['unidad'] ?? 'S/N' }}</td>
            <td class="label">Fecha inicio</td>
            <td>{{ $filtros['fecha_inicio'] ?? '' }}</td>
            <td class="label">Fecha fin</td>
            <td>{{ $filtros['fecha_fin'] ?? '' }}</td>
        </tr>
    </table>

    <table class="resumen">
        <thead>
            <tr>
                <th>Viajes</th>
                <th>Con datos</th>
                <th>Sin datos</th>
                <th>Total KM</th>
                <th>Litros cálculo</th>
                <th>Litros capturados</th>
                <th>KM / Litro</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ number_format($resumen['total_viajes'] ?? 0, 0) }}</td>
                <td>{{ number_format($resumen['viajes_con_datos'] ?? 0, 0) }}</td>
                <td>{{ number_format($resumen['viajes_sin_datos'] ?? 0, 0) }}</td>
                <td>{{ number_format($resumen['total_km'] ?? 0, 2) }}</td>
                <td>{{ number_format($resumen['total_litros_calculo'] ?? 0, 3) }}</td>
                <td>{{ number_format($resumen['total_litros_capturados'] ?? 0, 3) }}</td>
                <td>
                    @if (($resumen['rendimiento_promedio'] ?? null) !== null)
                        {{ number_format($resumen['rendimiento_promedio'], 3) }}
                    @else
                        S/N
                    @endif
                </td>
            </tr>
        </tbody>
    </table>

    <table class="tabla">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Contenedor</th>
                <th>Peso</th>
                <th>Operador</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>KM</th>
                <th>Litros cap.</th>
                <th>Litros calc.</th>
                <th>KM/L</th>
                <th>Tomado de</th>
                <th>Estado</th>
            </tr>
        </thead>

        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td class="text-center">{{ $row['fecha_inicio'] ?? 'S/N' }}</td>
                    <td>{{ $row['contenedor'] ?? 'S/N' }}</td>
                    <td class="text-end">{{ number_format((float) ($row['peso_contenedor'] ?? 0), 2) }}</td>
                    <td>{{ $row['operador'] ?? 'S/N' }}</td>
                    <td>{{ $row['origen'] ?? 'S/N' }}</td>
                    <td>{{ $row['destino'] ?? 'S/N' }}</td>
                    <td class="text-end">{{ number_format((float) ($row['km_recorridos'] ?? 0), 2) }}</td>
                    <td class="text-end">{{ number_format((float) ($row['litros_capturados_viaje'] ?? 0), 3) }}</td>
                    <td class="text-end">{{ number_format((float) ($row['litros_calculo_consumo'] ?? 0), 3) }}</td>
                    <td class="text-end">
                        @if (($row['rendimiento_km_litro'] ?? null) !== null)
                            {{ number_format((float) $row['rendimiento_km_litro'], 3) }}
                        @else
                            S/N
                        @endif
                    </td>
                    <td>{{ $row['litros_tomados_de_contenedor'] ?? 'S/N' }}</td>
                    <td>{{ $row['observacion'] ?? 'Completo' }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="12" class="text-center">
                        No se encontraron registros.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>

</html>
