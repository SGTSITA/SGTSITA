<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Reporte de Préstamos</title>

    <style>
        @page {
            margin: 25px 25px 35px 25px;
        }

        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 11px;
            color: #1f2937;
        }

        .header {
            border-bottom: 3px solid #1f2937;
            padding-bottom: 8px;
            margin-bottom: 14px;
        }

        .titulo {
            font-size: 20px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 4px;
        }

        .subtitulo {
            font-size: 12px;
            text-align: center;
            color: #555;
        }

        .fecha {
            text-align: right;
            font-size: 10px;
            margin-top: 8px;
            color: #555;
        }

        .info-operador {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
        }

        .info-operador td {
            border: 1px solid #cbd5e1;
            padding: 7px;
        }

        .label {
            font-weight: bold;
            color: #111827;
        }

        .resumen-general {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 18px;
        }

        .resumen-general th {
            background: #1f2937;
            color: white;
            padding: 7px;
            font-size: 11px;
            border: 1px solid #1f2937;
        }

        .resumen-general td {
            border: 1px solid #cbd5e1;
            padding: 8px;
            text-align: right;
            font-weight: bold;
            font-size: 12px;
        }

        .saldo-final {
            background: #fee2e2;
            color: #991b1b;
        }

        .prestamo-card {
            border: 1px solid #94a3b8;
            margin-bottom: 14px;
            page-break-inside: avoid;
        }

        .prestamo-header {
            background: #f1f5f9;
            padding: 8px;
            border-bottom: 1px solid #94a3b8;
        }

        .prestamo-title {
            font-size: 13px;
            font-weight: bold;
            color: #111827;
        }

        .badge {
            display: inline-block;
            padding: 3px 7px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: bold;
            color: white;
        }

        .badge-prestamo {
            background: #2563eb;
        }

        .badge-adelanto {
            background: #7c3aed;
        }

        .badge-pagado {
            background: #16a34a;
        }

        .badge-pendiente {
            background: #dc2626;
        }

        .prestamo-info {
            width: 100%;
            border-collapse: collapse;
        }

        .prestamo-info td {
            border-bottom: 1px solid #e5e7eb;
            padding: 6px 8px;
        }

        .monto {
            text-align: right;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .abonos-title {
            font-weight: bold;
            margin: 8px 8px 5px 8px;
            font-size: 11px;
            color: #374151;
        }

        .tabla-abonos {
            width: calc(100% - 16px);
            margin: 0 8px 8px 8px;
            border-collapse: collapse;
        }

        .tabla-abonos th {
            background: #e5e7eb;
            border: 1px solid #cbd5e1;
            padding: 5px;
            font-size: 10px;
        }

        .tabla-abonos td {
            border: 1px solid #cbd5e1;
            padding: 5px;
            font-size: 10px;
        }

        .sin-abonos {
            margin: 0 8px 8px 8px;
            padding: 7px;
            background: #fff7ed;
            border: 1px solid #fed7aa;
            color: #9a3412;
            font-size: 10px;
        }

        .footer {
            position: fixed;
            bottom: -20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #777;
        }
    </style>
</head>

<body>

    <div class="header">
        <div class="titulo">Reporte de Préstamos / Adelantos</div>
        <div class="subtitulo">Estado de cuenta del operador</div>
        <div class="fecha">
            Generado: {{ now()->format('d/m/Y H:i') }}
        </div>
    </div>

    <table class="info-operador">
        <tr>
            <td>
                <span class="label">Operador:</span>
                {{ $operador->nombre ?? 'S/N' }}
            </td>
            <td>
                <span class="label">CURP:</span>
                {{ $operador->curp ?? 'S/N' }}
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Teléfono:</span>
                {{ $operador->telefono ?? 'S/N' }}
            </td>
            <td>
                <span class="label">Correo:</span>
                {{ $operador->correo ?? 'S/N' }}
            </td>
        </tr>
    </table>

    <table class="resumen-general">
        <thead>
            <tr>
                <th>Total préstamos</th>
                <th>Total adelantos</th>
                <th>Total deuda</th>
                <th>Total abonos</th>
                <th>Saldo final</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>${{ number_format($totalPrestamos ?? 0, 2) }}</td>
                <td>${{ number_format($totalAdelantos ?? 0, 2) }}</td>
                <td>${{ number_format($totalDeuda ?? 0, 2) }}</td>
                <td>${{ number_format($totalAbonos ?? 0, 2) }}</td>
                <td class="saldo-final">${{ number_format($saldoFinal ?? 0, 2) }}</td>
            </tr>
        </tbody>
    </table>

    @forelse ($prestamos as $index => $prestamo)
        @php
            $abonado = $prestamo->pagoprestamos->sum('monto_pago');
            $saldo = ($prestamo->cantidad ?? 0) - $abonado;
            $estatus = $saldo <= 0 ? 'Pagado' : 'Pendiente';

            $badgeTipo = $prestamo->tipo == \App\Models\Prestamo::TIPO_PRESTAMO ? 'badge-prestamo' : 'badge-adelanto';

            $badgeEstatus = $saldo <= 0 ? 'badge-pagado' : 'badge-pendiente';
        @endphp

        <div class="prestamo-card">
            <div class="prestamo-header">
                <span class="prestamo-title">
                    #{{ $index + 1 }} -
                    {{ $prestamo->tipo ?? 'Movimiento' }}
                </span>

                <span class="badge {{ $badgeTipo }}">
                    {{ $prestamo->tipo ?? 'S/N' }}
                </span>

                <span class="badge {{ $badgeEstatus }}">
                    {{ $estatus }}
                </span>
            </div>

            <table class="prestamo-info">
                <tr>
                    <td>
                        <span class="label">Fecha creacion:</span>
                        {{ optional($prestamo->created_at)->format('d/m/Y') }}
                    </td>
                    <td>
                        <span class="label">Fecha Aplicación:</span>
                        {{ optional($prestamo->fecha_prestamo ?: $prestamo->created_at)->format('d/m/Y') }}
                    </td>
                    <td>
                        <span class="label">Banco Salida:</span>
                        {{ $prestamo->banco->nombre ?? ($prestamo->banco->nombre_banco ?? 'S/N') }}
                    </td>
                </tr>
                <tr>
                    <td class="monto">
                        Cantidad:
                        ${{ number_format($prestamo->cantidad ?? 0, 2) }}
                    </td>
                    <td class="monto">
                        Saldo:
                        ${{ number_format($saldo, 2) }}
                    </td>
                </tr>
            </table>

            <div class="abonos-title">
                Historial de abonos
            </div>

            @if ($prestamo->pagoprestamos->count() > 0)
                <table class="tabla-abonos">
                    <thead>
                        <tr>
                            <th style="width: 8%;">#</th>
                            <th style="width: 20%;">Fecha</th>
                            <th style="width: 22%;">Monto abonado</th>
                            <th style="width: 22%;">Saldo después</th>
                            <th style="width: 28%;">Referencia</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $saldoTemporal = $prestamo->cantidad ?? 0;
                        @endphp

                        @foreach ($prestamo->pagoprestamos as $i => $pago)
                            @php
                                $saldoTemporal -= $pago->monto_pago ?? 0;
                            @endphp

                            <tr>
                                <td class="text-center">{{ $i + 1 }}</td>
                                <td class="text-center">
                                    {{ optional($pago->fecha_pago ?: $pago->created_at)->format('d/m/Y') }}
                                </td>
                                <td class="text-right">
                                    ${{ number_format($pago->monto_pago ?? 0, 2) }}
                                </td>
                                <td class="text-right">
                                    ${{ number_format($saldoTemporal, 2) }}
                                </td>
                                <td>

                                    @php
                                        $referenciaPago = $pago->referencia;

                                        if (!$referenciaPago && $pago->liquidacion) {
                                            $contenedores = optional($pago->liquidacion->Viajes)
                                                ->map(function ($viaje) {
                                                    return optional($viaje->Contenedores)->num_contenedor;
                                                })
                                                ->filter()
                                                ->implode(' / ');

                                            $referenciaPago = $contenedores ?: 'Liquidación #' . $pago->liquidacion->id;
                                        }
                                    @endphp

                                    {{ $referenciaPago ?? ' ' }}

                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="sin-abonos">
                    Este préstamo/adelanto aún no tiene abonos registrados.
                </div>
            @endif
        </div>

    @empty
        <div class="sin-abonos">
            No hay préstamos o adelantos registrados para este operador.
        </div>
    @endforelse

    <div class="footer">
        Reporte generado automáticamente
    </div>

</body>

</html>
