<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>{{ $reporte['titulo'] }}</title>

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 10px;
            color: #111827;
        }

        .header {
            border-bottom: 2px solid #1d4ed8;
            padding-bottom: 8px;
            margin-bottom: 12px;
        }

        .title {
            font-size: 18px;
            font-weight: bold;
            color: #1d4ed8;
        }

        .subtitle {
            font-size: 11px;
            color: #4b5563;
        }

        .summary {
            width: 100%;
            margin-bottom: 12px;
            border-collapse: collapse;
        }

        .summary td {
            width: 25%;
            border: 1px solid #e5e7eb;
            padding: 8px;
        }

        .summary .label {
            font-size: 9px;
            text-transform: uppercase;
            color: #6b7280;
            font-weight: bold;
        }

        .summary .value {
            font-size: 13px;
            font-weight: bold;
            margin-top: 3px;
        }

        table.data {
            width: 100%;
            border-collapse: collapse;
        }

        table.data th {
            background: #eff6ff;
            color: #1e3a8a;
            border: 1px solid #bfdbfe;
            padding: 5px;
            text-align: left;
            font-size: 9px;
        }

        table.data td {
            border: 1px solid #e5e7eb;
            padding: 5px;
            vertical-align: top;
        }

        .text-end {
            text-align: right;
        }

        .cargo {
            color: #b91c1c;
            font-weight: bold;
        }

        .abono {
            color: #15803d;
            font-weight: bold;
        }

        .saldo {
            color: #1d4ed8;
            font-weight: bold;
        }

        .muted {
            color: #6b7280;
        }
    </style>
</head>

<body>
    <div class="header">
        <div class="title">{{ $reporte['titulo'] }}</div>
        <div class="subtitle">
            {{ $reporte['cuenta']['banco'] }} -
            {{ $reporte['cuenta']['beneficiario'] }} -
            {{ $reporte['cuenta']['numero_cuenta'] }}
        </div>
        <div class="subtitle">
            Periodo: {{ $reporte['fecha_inicio'] }} al {{ $reporte['fecha_fin'] }}
        </div>
    </div>

    <table class="summary">
        <tr>
            <td>
                <div class="label">Saldo inicial</div>
                <div class="value">${{ number_format($reporte['saldo_inicial'], 2) }}</div>
            </td>
            <td>
                <div class="label">Cargos</div>
                <div class="value cargo">${{ number_format($reporte['total_cargos'], 2) }}</div>
            </td>
            <td>
                <div class="label">Abonos</div>
                <div class="value abono">${{ number_format($reporte['total_abonos'], 2) }}</div>
            </td>
            <td>
                <div class="label">Saldo final</div>
                <div class="value saldo">${{ number_format($reporte['saldo_final'], 2) }}</div>
            </td>
        </tr>
    </table>

    @if ($reporte['tipo_reporte'] === 'detallado')
        <table class="data">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Movimiento / Detalle</th>
                    <th>Referencia</th>
                    <th class="text-end">Cargo</th>
                    <th class="text-end">Abono</th>
                    <th class="text-end">Saldo</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($reporte['rows'] as $mov)
                    <tr style="background:#f8fafc;">
                        <td><strong>{{ $mov['fecha'] }}</strong></td>

                        <td>
                            <strong>{{ $mov['concepto'] }}</strong><br>
                            <span class="muted">
                                Movimiento #{{ $mov['id'] }} · {{ $mov['detalles_count'] }} detalle(s)
                            </span>
                        </td>

                        <td>{{ $mov['referencia_bancaria'] ?? 'S/N' }}</td>

                        <td class="text-end cargo">
                            {{ $mov['cargo'] > 0 ? '$' . number_format($mov['cargo'], 2) : '-' }}
                        </td>

                        <td class="text-end abono">
                            {{ $mov['abono'] > 0 ? '$' . number_format($mov['abono'], 2) : '-' }}
                        </td>

                        <td class="text-end saldo">
                            ${{ number_format($mov['saldo'], 2) }}
                        </td>
                    </tr>

                    @forelse ($mov['detalles'] as $detalle)
                        <tr>
                            <td></td>

                            <td style="padding-left:18px;">
                                <strong>Unidad:</strong> {{ $detalle['unidad'] ?? 'S/N' }}<br>
                                <span class="muted">
                                    {{ $detalle['descripcion'] ?? '' }}
                                </span>
                            </td>

                            <td>{{ $detalle['referencia'] ?? 'S/N' }}</td>

                            <td class="text-end">
                                {{ $mov['cargo'] > 0 ? '$' . number_format($detalle['monto'], 2) : '-' }}
                            </td>

                            <td class="text-end">
                                {{ $mov['abono'] > 0 ? '$' . number_format($detalle['monto'], 2) : '-' }}
                            </td>

                            <td class="text-end muted">-</td>
                        </tr>
                    @empty
                        <tr>
                            <td></td>
                            <td colspan="5" class="muted">
                                Sin detalles registrados.
                            </td>
                        </tr>
                    @endforelse

                    <tr>
                        <td></td>
                        <td colspan="2" class="text-end">
                            <strong>Total detalles</strong>
                        </td>

                        <td class="text-end">
                            <strong>
                                {{ $mov['cargo'] > 0 ? '$' . number_format($mov['total_detalles'], 2) : '-' }}
                            </strong>
                        </td>

                        <td class="text-end">
                            <strong>
                                {{ $mov['abono'] > 0 ? '$' . number_format($mov['total_detalles'], 2) : '-' }}
                            </strong>
                        </td>

                        <td></td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;">Sin información.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @else
        <table class="data">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th>Referencia</th>
                    <th class="text-end">Cargo</th>
                    <th class="text-end">Abono</th>
                    <th class="text-end">Saldo</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($reporte['rows'] as $row)
                    <tr>
                        <td>{{ $row['fecha'] }}</td>
                        <td>
                            <strong>{{ $row['concepto'] }}</strong><br>
                            <span class="muted">{{ $row['detalles_count'] }} detalle(s)</span>
                        </td>
                        <td>{{ $row['referencia'] ?? 'S/N' }}</td>
                        <td class="text-end cargo">
                            {{ $row['cargo'] > 0 ? '$' . number_format($row['cargo'], 2) : '-' }}
                        </td>
                        <td class="text-end abono">
                            {{ $row['abono'] > 0 ? '$' . number_format($row['abono'], 2) : '-' }}
                        </td>
                        <td class="text-end saldo">
                            ${{ number_format($row['saldo'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align:center;">Sin información.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    @endif
</body>

</html>
