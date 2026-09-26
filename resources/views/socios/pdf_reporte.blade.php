<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Reporte de Utilidad por Socio</title>
    <style>
        @page {
            margin: 12mm 12mm 15mm 12mm;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #222;
            font-size: 10.5px;
            line-height: 1.35;
        }

        .header {
            margin-bottom: 12px;
            border-bottom: 2px solid #2b354f;
            padding-bottom: 6px;
        }

        .header h2 {
            margin: 0;
            font-size: 16px;
            text-transform: uppercase;
            color: #1e293b;
            letter-spacing: 0.5px;
        }

        .info-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 10px;
        }

        .info-table td {
            padding: 2px 0;
        }

        .section-title {
            font-size: 12px;
            font-weight: bold;
            margin-top: 10px;
            margin-bottom: 6px;
            text-transform: uppercase;
            color: #1e293b;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }

        .summary-box {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-radius: 4px;
        }

        .summary-box table {
            width: 100%;
        }

        .summary-box td {
            padding: 4px;
            font-size: 11px;
        }

        /* Unit Banner */
        .unit-banner {
            background-color: #2b354f;
            color: #ffffff;
            padding: 8px 12px;
            border-radius: 4px;
            margin-top: 8px;
            margin-bottom: 12px;
        }

        .unit-sublabel {
            font-size: 8.5px;
            text-transform: uppercase;
            color: #94a3b8;
            letter-spacing: 0.5px;
        }

        .unit-name {
            font-size: 12.5px;
            font-weight: bold;
            color: #ffffff;
        }

        .unit-value {
            font-size: 12.5px;
            font-weight: bold;
            color: #ffffff;
        }

        /* Socio Card */
        .socio-card {
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            padding: 8px 10px;
            margin-bottom: 14px;
            background-color: #ffffff;
            page-break-inside: avoid;
        }

        .socio-summary-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        .socio-summary-table th {
            background-color: #f1f5f9;
            padding: 5px 6px;
            font-size: 9.5px;
            text-transform: uppercase;
            border-bottom: 1.5px solid #cbd5e1;
            color: #475569;
        }

        .socio-summary-table td {
            padding: 5px 6px;
            border-bottom: 1px solid #e2e8f0;
            font-size: 10.5px;
        }

        .sub-section-title {
            font-size: 9.5px;
            font-weight: bold;
            text-transform: uppercase;
            color: #475569;
            margin-top: 6px;
            margin-bottom: 4px;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 6px;
        }

        table.data-table th {
            background-color: #f8fafc;
            text-align: left;
            padding: 5px 6px;
            font-weight: bold;
            font-size: 9.5px;
            color: #475569;
            border: 1px solid #cbd5e1;
        }

        table.data-table td {
            padding: 4px 6px;
            border: 1px solid #e2e8f0;
            font-size: 10px;
        }

        table.data-table tfoot td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            background-color: #f1f5f9;
            font-size: 10px;
        }

        .alert-info-box {
            background-color: #f8fafc;
            border: 1px dashed #cbd5e1;
            color: #64748b;
            padding: 6px 10px;
            font-size: 9.5px;
            text-align: center;
            border-radius: 4px;
            margin-bottom: 6px;
        }

        .firma-table {
            width: 100%;
            margin-top: 10px;
            padding-top: 4px;
        }

        .badge-rule {
            display: inline-block;
            background-color: #0284c7;
            color: #ffffff;
            padding: 1px 5px;
            border-radius: 3px;
            font-size: 9.5px;
            font-weight: bold;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .font-bold {
            font-weight: bold;
        }

        .text-success {
            color: #16a34a;
        }

        .text-danger {
            color: #dc2626;
        }

        .text-info {
            color: #0284c7;
        }

        .page-break {
            page-break-before: always;
        }
    </style>
</head>

<body>
    <!-- Script de numeración de páginas para DomPDF -->
    <script type="text/php">
    if (isset($pdf)) {
        $text = "Página {PAGE_NUM} de {PAGE_COUNT}";
        $font = $fontMetrics->getFont("Helvetica", "normal");
        $size = 8;
        $color = array(0.45, 0.45, 0.45);
        $y = $pdf->get_height() - 22;
        $x = $pdf->get_width() - 95;
        $pdf->page_text($x, $y, $text, $font, $size, $color);
    }
    </script>

    <div class="header">
        <h2>Reporte de Distribución de Utilidades (Socios)</h2>
    </div>

    <table class="info-table">
        <tr>
            <td width="15%"><strong>Empresa:</strong></td>
            <td width="35%">{{ $empresa }}</td>
            <td width="20%"><strong>Fecha Generación:</strong></td>
            <td width="30%">{{ $fechaGeneracion }}</td>
        </tr>
        <tr>
            <td><strong>Periodo:</strong></td>
            <td>{{ date('d-m-Y', strtotime($data['fecha_desde'])) }} al
                {{ date('d-m-Y', strtotime($data['fecha_hasta'])) }}</td>
            <td><strong>Socios en Reporte:</strong></td>
            <td><strong class="text-info">{{ $data['total_socios_configurados'] ?? count($data['socios_desglose']) }}
                    socio(s)</strong></td>
        </tr>
    </table>

    @if (!isset($tipoReporte) || $tipoReporte === 'completo')
        <!-- FORMATO REPORTE COMPLETO CON VIAJES -->
        <div class="section-title">Resumen Financiero del Periodo</div>
        <div class="summary-box">
            <table>
                <tr>
                    <td width="25%"><strong>Utilidad Bruta Viajes:</strong></td>
                    <td width="25%" class="text-right font-bold">$
                        {{ number_format($data['total_utilidad_bruta_viajes'], 2) }}</td>
                    <td width="25%"><strong>Utilidad a repartir:</strong></td>
                    <td width="25%" class="text-right font-bold text-info">$
                        {{ number_format($data['utilidad_neta_distribuible'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Gastos Indirectos Mes:</strong></td>
                    <td class="text-right font-bold text-danger">$ {{ number_format($data['total_gastos_periodo'], 2) }}
                    </td>
                    <td><strong>Total Pagado / Adelantado:</strong></td>
                    <td class="text-right font-bold text-success">$ {{ number_format($data['total_pagado_socios'], 2) }}
                    </td>
                </tr>
            </table>
        </div>

        <div class="section-title">Distribución Agrupada por Socio</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Socio</th>
                    <th>Unidad Pactada</th>
                    <th style="text-align: center;">Regla</th>
                    <th class="text-right">Viajes</th>
                    <th class="text-right">Utilidad a Repartir</th>
                    <th class="text-right">Utilidad Socio</th>
                    <th class="text-right">Total Pagado</th>
                    <th class="text-right">Saldo Pendiente</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['socios_desglose'] as $soc)
                    <tr>
                        <td>{{ $soc['socio'] }}</td>
                        <td>{{ $soc['unidad'] }}</td>
                        <td style="text-align: center;">{{ $soc['factor'] }}</td>
                        <td class="text-right">{{ $soc['viajes_realizados'] }}</td>
                        <td class="text-right">$ {{ number_format($soc['utilidad_a_repartir'], 2) }}</td>
                        <td class="text-right font-bold text-success">$
                            {{ number_format($soc['monto_distribuido'], 2) }}</td>
                        <td class="text-right">$ {{ number_format($soc['total_pagado'], 2) }}</td>
                        <td class="text-right font-bold">$ {{ number_format($soc['saldo_pendiente'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="section-title">Desglose Individual de Viajes</div>
        <table class="data-table">
            <thead>
                <tr>
                    <th>Fecha Viaje</th>
                    <th>Contenedor</th>
                    <th>Cliente</th>
                    <th>Unidad</th>
                    <th>Estatus</th>
                    <th class="text-right">Utilidad Viaje</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['viajes_desglose'] as $v)
                    <tr>
                        <td>{{ $v['fecha_viaje'] ? date('d-m-Y', strtotime($v['fecha_viaje'])) : 'S/N' }}</td>
                        <td>{{ $v['contenedor'] }}</td>
                        <td>{{ $v['cliente'] ?? '--' }}</td>
                        <td>{{ $v['unidad'] }}</td>
                        <td>{{ $v['estatus_viaje'] ?? 'S/N' }}</td>
                        <td class="text-right font-bold">$ {{ number_format($v['utilidad_viaje'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @else
        <!-- FORMATO POR SOCIO (SIN VIAJES) - AGRUPADO POR UNIDAD Y SOCIO CON CORTE DE PÁGINA Y RÚBRICA -->
        <div class="section-title">Resumen de Utilidad en este Periodo para Socios</div>
        <div class="summary-box">
            <table>
                <tr>
                    <td width="65%">
                        <strong style="font-size: 11.5px;">Utilidad Total Correspondiente a Socios:</strong><br>
                        <span style="font-size: 9.5px; color: #64748b;">Suma total de rendimientos generados en el
                            periodo a repartir entre los socios configurados.</span>
                    </td>
                    <td width="35%" class="text-right font-bold text-success" style="font-size: 15px;">
                        $ {{ number_format($data['total_distribuido_socios'], 2) }}
                    </td>
                </tr>
            </table>
        </div>

        @if (!empty($data['unidades_desglose']))
            @foreach ($data['unidades_desglose'] as $uIndex => $unidad)
                <div class="{{ $uIndex > 0 ? 'page-break' : '' }}">
                    <!-- Header Principal de la Unidad -->
                    <div class="unit-banner">
                        <table style="width: 100%;">
                            <tr>
                                <td style="width: 50%;">
                                    <span class="unit-sublabel">UNIDAD / VEHÍCULO:</span><br>
                                    <span class="unit-name">{{ $unidad['unidad'] }}</span>
                                </td>
                                <td style="width: 20%; text-align: center;">
                                    <span class="unit-sublabel">VIAJES REALIZADOS:</span><br>
                                    <span class="unit-value">{{ $unidad['viajes_realizados'] }}</span>
                                </td>
                                <td style="width: 30%; text-align: right;">
                                    <span class="unit-sublabel">UTILIDAD A REPARTIR UNIDAD:</span><br>
                                    <span class="unit-value text-success" style="color: #4ade80;">$
                                        {{ number_format($unidad['utilidad_a_repartir'], 2) }}</span>
                                </td>
                            </tr>
                        </table>
                    </div>

                    <!-- Socios de esta Unidad -->
                    @foreach ($unidad['socios'] as $soc)
                        <div class="socio-card">
                            <!-- Ficha de Datos del Socio -->
                            <table class="socio-summary-table">
                                <thead>
                                    <tr>
                                        <th style="width: 34%;">Socio</th>
                                        <th style="width: 12%; text-align: center;">Regla</th>
                                        <th style="width: 18%; text-align: right;">Utilidad Socio</th>
                                        <th style="width: 18%; text-align: right;">Total Pagado</th>
                                        <th style="width: 18%; text-align: right;">Saldo Pendiente</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>{{ $soc['socio'] }}</strong></td>
                                        <td style="text-align: center;"><span
                                                class="badge-rule">{{ $soc['factor'] }}</span></td>
                                        <td class="text-right font-bold text-success">$
                                            {{ number_format($soc['monto_distribuido'], 2) }}</td>
                                        <td class="text-right">$ {{ number_format($soc['total_pagado'], 2) }}</td>
                                        <td class="text-right font-bold"
                                            style="color: {{ $soc['saldo_pendiente'] > 0 ? '#dc2626' : '#16a34a' }}; font-size: 11px;">
                                            $ {{ number_format($soc['saldo_pendiente'], 2) }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Desglose de Pagos de este Socio -->
                            <div class="sub-section-title">
                                Pagos y Abonos Registrados en el Periodo
                            </div>

                            @if (!empty($soc['pagos']))
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th style="width: 14%;">Fecha Pago</th>
                                            <th style="width: 40%;">Concepto / Referencia</th>
                                            <th style="width: 28%;">Cuenta / Banco Origen</th>
                                            <th style="width: 18%;" class="text-right">Monto Pagado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($soc['pagos'] as $pago)
                                            <tr>
                                                <td>{{ $pago['fecha_formateada'] }}</td>
                                                <td>{{ $pago['concepto'] }}</td>
                                                <td>{{ $pago['banco'] }}</td>
                                                <td class="text-right font-bold text-success">$
                                                    {{ number_format($pago['monto'], 2) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <td colspan="3" class="text-right font-bold">TOTAL PAGADO EN EL PERIODO:
                                            </td>
                                            <td class="text-right font-bold text-success" style="font-size: 10.5px;">$
                                                {{ number_format($soc['total_pagos_periodo'], 2) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            @else
                                <div class="alert-info-box">
                                    Aún no se han realizado pagos / abonos a este socio en este periodo.
                                </div>
                            @endif

                            <!-- Rúbrica / Firma de Conformidad -->
                            <table class="firma-table">
                                <tr>
                                    <td style="width: 60%; vertical-align: bottom;">
                                        <p style="font-size: 8.5px; color: #94a3b8; margin: 0;">
                                            * Documento emitido con fines de liquidación y control financiero entre
                                            partes.
                                        </p>
                                    </td>
                                    <td style="width: 40%; text-align: center; vertical-align: bottom;">
                                        <div
                                            style="border-bottom: 1px solid #1e293b; width: 85%; margin: 0 auto 4px auto; height: 30px;">
                                        </div>
                                        <strong style="font-size: 9.5px; color: #1e293b;">Firma de
                                            Conformidad</strong><br>
                                        <span style="font-size: 9px; color: #475569;">{{ $soc['socio'] }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    @endif
</body>

</html>
