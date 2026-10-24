<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $tituloReporte }} - {{ $nombresEmpresas }}</title>
    <style>
        @page {
            margin: 12mm 10mm 15mm 10mm;
            size: {{ in_array($tipoReporte, ['balanza_comprobacion', 'auxiliar_contable', 'libro_diario']) ? 'letter landscape' : 'letter portrait' }};
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            color: #222;
            font-size: 9pt;
            margin: 0;
            padding: 0;
            line-height: 1.3;
        }
        .header-container {
            width: 100%;
            border-bottom: 2px solid #1a365d;
            padding-bottom: 8px;
            margin-bottom: 12px;
            text-align: center;
        }
        .institution-tag {
            font-size: 7.5pt;
            font-weight: bold;
            color: #4a5568;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin-bottom: 2px;
        }
        .company-name {
            font-size: 13pt;
            font-weight: 800;
            color: #1a365d;
            text-transform: uppercase;
            margin: 2px 0;
        }
        .report-title {
            font-size: 11pt;
            font-weight: bold;
            color: #2b6cb0;
            margin: 2px 0;
            text-transform: uppercase;
        }
        .meta-info {
            font-size: 8pt;
            color: #4a5568;
            margin-top: 3px;
        }
        .meta-info strong {
            color: #1a202c;
        }
        table.conac-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        table.conac-table th, table.conac-table td {
            border: 0.5px solid #cbd5e0;
            padding: 4px 6px;
            font-size: 8pt;
        }
        table.conac-table th {
            background-color: #2b6cb0;
            color: #ffffff;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 7.5pt;
            text-align: center;
        }
        table.conac-table .sub-th {
            background-color: #3182ce;
            color: #ffffff;
            font-size: 7pt;
        }
        table.conac-table tr.section-row td {
            background-color: #edf2f7;
            font-weight: bold;
            color: #2d3748;
            text-transform: uppercase;
            font-size: 8pt;
        }
        table.conac-table tr.total-row td {
            background-color: #e2e8f0;
            font-weight: bold;
            border-top: 1.5px solid #4a5568;
            border-bottom: 1.5px solid #4a5568;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .text-left {
            text-align: left;
        }
        .text-danger {
            color: #c53030;
        }
        .text-success {
            color: #22543d;
        }
        .signatures-table {
            width: 100%;
            margin-top: 25px;
            border-collapse: collapse;
            page-break-inside: avoid;
        }
        .signatures-table td {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 15px;
        }
        .signature-line {
            border-top: 1px solid #4a5568;
            margin-top: 40px;
            padding-top: 5px;
            font-size: 7.5pt;
            font-weight: bold;
            color: #2d3748;
            text-transform: uppercase;
        }
        .signature-sub {
            font-size: 6.5pt;
            color: #718096;
            margin-top: 2px;
        }
        .protest-clause {
            font-size: 6.5pt;
            color: #718096;
            text-align: center;
            font-style: italic;
            margin-top: 20px;
            border-top: 0.5px solid #e2e8f0;
            padding-top: 5px;
        }
        .cuadre-badge {
            display: inline-block;
            padding: 2px 8px;
            font-size: 7.5pt;
            font-weight: bold;
            border-radius: 3px;
        }
        .cuadre-ok {
            background-color: #c6f6d5;
            color: #22543d;
            border: 1px solid #9ae6b4;
        }
        .cuadre-diff {
            background-color: #fed7d7;
            color: #9b2c2c;
            border: 1px solid #feb2b2;
        }
    </style>
</head>
<body>

    {{-- ENCABEZADO CONAC --}}
    <div class="header-container">
        <div class="institution-tag">SISTEMA GESTIÓN TRANSPORTE CONTABLE</div>
        <div class="company-name">{{ $nombresEmpresas }}</div>
        <div class="report-title">{{ $tituloReporte }}</div>
        <div class="meta-info">
            @if($equipoNombre)
                UNIDAD VEHICULAR: <strong>{{ $equipoNombre }}</strong> &nbsp;|&nbsp;
            @else
                <strong>CONSOLIDADO GLOBAL DE UNIDADES</strong> &nbsp;|&nbsp;
            @endif
            PERIODO: <strong>{{ \Carbon\Carbon::parse($fechaInicio ?? $fechaCorte)->format('d/m/Y') }} AL {{ \Carbon\Carbon::parse($fechaCorte)->format('d/m/Y') }}</strong> &nbsp;|&nbsp;
            MONEDA: <strong>PESOS MEXICANOS (MXN)</strong>
        </div>
    </div>

    {{-- CUERPO SEGÚN TIPO DE REPORTE --}}

    @if ($tipoReporte === 'balance_general')
        {{-- ==================== 1. BALANCE GENERAL ==================== --}}
        <table style="width: 100%; border-collapse: collapse; margin-bottom: 10px;">
            <tr>
                {{-- COLUMNA ACTIVO --}}
                <td style="width: 50%; vertical-align: top; padding-right: 8px;">
                    <table class="conac-table">
                        <thead>
                            <tr>
                                <th class="text-left" style="width: 70%;">ACTIVO</th>
                                <th class="text-right" style="width: 30%;">MONTO (MXN)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['activo']['items'] as $item)
                                <tr>
                                    <td class="{{ $item['es_calculado'] ? 'fw-bold' : '' }}" style="{{ !$item['es_calculado'] ? 'padding-left: 12px; color: #4a5568;' : 'font-weight: bold;' }}">
                                        {{ $item['concepto'] }}
                                    </td>
                                    <td class="text-right {{ $item['monto'] < 0 ? 'text-danger' : '' }}" style="{{ $item['es_calculado'] ? 'font-weight: bold;' : '' }}">
                                        $ {{ number_format($item['monto'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td class="text-left">TOTAL ACTIVO</td>
                                <td class="text-right">$ {{ number_format($data['activo']['total'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </td>

                {{-- COLUMNA PASIVO Y PATRIMONIO --}}
                <td style="width: 50%; vertical-align: top; padding-left: 8px;">
                    <table class="conac-table" style="margin-bottom: 8px;">
                        <thead>
                            <tr>
                                <th class="text-left" style="width: 70%;">PASIVO</th>
                                <th class="text-right" style="width: 30%;">MONTO (MXN)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['pasivo']['items'] as $item)
                                <tr>
                                    <td class="{{ $item['es_calculado'] ? 'fw-bold' : '' }}" style="{{ !$item['es_calculado'] ? 'padding-left: 12px; color: #4a5568;' : 'font-weight: bold;' }}">
                                        {{ $item['concepto'] }}
                                    </td>
                                    <td class="text-right {{ $item['monto'] < 0 ? 'text-danger' : '' }}" style="{{ $item['es_calculado'] ? 'font-weight: bold;' : '' }}">
                                        $ {{ number_format($item['monto'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td class="text-left">TOTAL PASIVO</td>
                                <td class="text-right">$ {{ number_format($data['pasivo']['total'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>

                    <table class="conac-table">
                        <thead>
                            <tr>
                                <th class="text-left" style="width: 70%;">HACIENDA PÚBLICA / PATRIMONIO</th>
                                <th class="text-right" style="width: 30%;">MONTO (MXN)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($data['patrimonio']['items'] as $item)
                                <tr>
                                    <td class="{{ $item['es_calculado'] ? 'fw-bold' : '' }}" style="{{ !$item['es_calculado'] ? 'padding-left: 12px; color: #4a5568;' : 'font-weight: bold;' }}">
                                        {{ $item['concepto'] }}
                                    </td>
                                    <td class="text-right {{ $item['monto'] < 0 ? 'text-danger' : '' }}" style="{{ $item['es_calculado'] ? 'font-weight: bold;' : '' }}">
                                        $ {{ number_format($item['monto'], 2) }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="total-row">
                                <td class="text-left">TOTAL PATRIMONIO</td>
                                <td class="text-right">$ {{ number_format($data['patrimonio']['total'], 2) }}</td>
                            </tr>
                            <tr style="background-color: #edf2f7; font-weight: bold;">
                                <td class="text-left">TOTAL PASIVO Y PATRIMONIO</td>
                                <td class="text-right">$ {{ number_format($data['total_pasivo_patrimonio'], 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </td>
            </tr>
        </table>

        {{-- BARRA DE CUADRE --}}
        <div class="text-center" style="margin-top: 5px;">
            @if ($data['cuadrado'])
                <span class="cuadre-badge cuadre-ok">
                    ✓ BALANCE GENERAL CUADRADO AL CORTE: ACTIVO ($ {{ number_format($data['activo']['total'], 2) }}) = PASIVO Y PATRIMONIO ($ {{ number_format($data['total_pasivo_patrimonio'], 2) }})
                </span>
            @else
                <span class="cuadre-badge cuadre-diff">
                    ⚠ DIFERENCIA CONTABLE: $ {{ number_format($data['diferencia'], 2) }}
                </span>
            @endif
        </div>

    @elseif ($tipoReporte === 'estado_resultados')
        {{-- ==================== 2. ESTADO DE RESULTADOS ==================== --}}
        <table class="conac-table">
            <thead>
                <tr>
                    <th class="text-left" style="width: 70%;">CONCEPTO</th>
                    <th class="text-right" style="width: 30%;">MONTO DEL EJERCICIO (MXN)</th>
                </tr>
            </thead>
            <tbody>
                <tr class="section-row">
                    <td colspan="2">INGRESOS DE GESTIÓN Y FINANCIEROS</td>
                </tr>
                <tr>
                    <td style="padding-left: 15px;">(+) Ingresos por Servicios de Fletes y Logística (4.1.1.1)</td>
                    <td class="text-right fw-bold">$ {{ number_format($data['estado_resultados']['ingresos_fletes'], 2) }}</td>
                </tr>
                <tr>
                    <td style="padding-left: 15px;">(+) Intereses y Rendimientos Financieros Bancarios (4.3.1.1)</td>
                    <td class="text-right fw-bold">$ {{ number_format($data['estado_resultados']['intereses_ganados'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="text-left">TOTAL DE INGRESOS</td>
                    <td class="text-right">$ {{ number_format($data['estado_resultados']['ingresos_fletes'] + $data['estado_resultados']['intereses_ganados'], 2) }}</td>
                </tr>

                <tr class="section-row">
                    <td colspan="2">GASTOS Y OTRAS PÉRDIDAS</td>
                </tr>
                <tr>
                    <td style="padding-left: 15px;">(-) Gastos Operativos, de Transporte y Generales (5.1.*)</td>
                    <td class="text-right fw-bold text-danger">- $ {{ number_format($data['estado_resultados']['gastos_totales'], 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td class="text-left">TOTAL DE GASTOS</td>
                    <td class="text-right text-danger">$ {{ number_format($data['estado_resultados']['gastos_totales'], 2) }}</td>
                </tr>

                <tr style="background-color: {{ $data['estado_resultados']['utilidad_neta'] >= 0 ? '#c6f6d5' : '#fed7d7' }}; font-weight: bold; font-size: 10pt;">
                    <td class="text-left" style="padding: 8px;">
                        RESULTADO DEL EJERCICIO (AHORRO / DESAHORRO NETO)
                    </td>
                    <td class="text-right {{ $data['estado_resultados']['utilidad_neta'] >= 0 ? 'text-success' : 'text-danger' }}" style="padding: 8px;">
                        $ {{ number_format($data['estado_resultados']['utilidad_neta'], 2) }}
                    </td>
                </tr>
            </tbody>
        </table>

    @elseif ($tipoReporte === 'balanza_comprobacion')
        {{-- ==================== 3. BALANZA DE COMPROBACIÓN ==================== --}}
        <table class="conac-table">
            <thead>
                <tr>
                    <th rowspan="2" style="width: 10%;">CÓDIGO</th>
                    <th rowspan="2" class="text-left" style="width: 30%;">NOMBRE DE LA CUENTA</th>
                    <th rowspan="2" style="width: 5%;">NAT</th>
                    <th colspan="2" class="sub-th" style="width: 18%;">SALDO INICIAL</th>
                    <th colspan="2" class="sub-th" style="width: 18%;">MOVIMIENTOS DEL PERIODO</th>
                    <th colspan="2" class="sub-th" style="width: 19%;">SALDO FINAL</th>
                </tr>
                <tr>
                    <th class="sub-th text-right" style="width: 9%;">DEUDOR</th>
                    <th class="sub-th text-right" style="width: 9%;">ACREEDOR</th>
                    <th class="sub-th text-right" style="width: 9%;">DEBE</th>
                    <th class="sub-th text-right" style="width: 9%;">HABER</th>
                    <th class="sub-th text-right" style="width: 9.5%;">DEUDOR</th>
                    <th class="sub-th text-right" style="width: 9.5%;">ACREEDOR</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['cuentas'] as $c)
                    <tr>
                        <td class="text-center" style="font-family: monospace;">{{ $c['codigo'] }}</td>
                        <td class="text-left">{{ $c['nombre'] }}</td>
                        <td class="text-center" style="text-transform: uppercase;">{{ substr($c['naturaleza'], 0, 1) }}</td>
                        <td class="text-right">{{ $c['saldo_ini_deudor'] > 0 ? '$ ' . number_format($c['saldo_ini_deudor'], 2) : '-' }}</td>
                        <td class="text-right">{{ $c['saldo_ini_acreedor'] > 0 ? '$ ' . number_format($c['saldo_ini_acreedor'], 2) : '-' }}</td>
                        <td class="text-right text-success">{{ $c['debe'] > 0 ? '$ ' . number_format($c['debe'], 2) : '-' }}</td>
                        <td class="text-right text-danger">{{ $c['haber'] > 0 ? '$ ' . number_format($c['haber'], 2) : '-' }}</td>
                        <td class="text-right fw-bold">{{ $c['saldo_fin_deudor'] > 0 ? '$ ' . number_format($c['saldo_fin_deudor'], 2) : '-' }}</td>
                        <td class="text-right fw-bold">{{ $c['saldo_fin_acreedor'] > 0 ? '$ ' . number_format($c['saldo_fin_acreedor'], 2) : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row">
                    <td colspan="3" class="text-right">SUMAS IGUALES:</td>
                    <td class="text-right">$ {{ number_format($data['totales']['saldo_ini_deudor'], 2) }}</td>
                    <td class="text-right">$ {{ number_format($data['totales']['saldo_ini_acreedor'], 2) }}</td>
                    <td class="text-right text-success">$ {{ number_format($data['totales']['debe'], 2) }}</td>
                    <td class="text-right text-danger">$ {{ number_format($data['totales']['haber'], 2) }}</td>
                    <td class="text-right">$ {{ number_format($data['totales']['saldo_fin_deudor'], 2) }}</td>
                    <td class="text-right">$ {{ number_format($data['totales']['saldo_fin_acreedor'], 2) }}</td>
                </tr>
            </tfoot>
        </table>

        {{-- Estado de cuadre balanza --}}
        <div class="text-center" style="margin-top: 5px;">
            @if ($data['cuadrado'])
                <span class="cuadre-badge cuadre-ok">
                    ✓ BALANZA CUADRADA: MOVIMIENTOS DEL PERIODO DEBE = HABER ($ {{ number_format($data['totales']['debe'], 2) }})
                </span>
            @else
                <span class="cuadre-badge cuadre-diff">
                    ⚠ DIFERENCIA EN MOVIMIENTOS: $ {{ number_format($data['diferencia_movimientos'], 2) }}
                </span>
            @endif
        </div>

    @elseif ($tipoReporte === 'auxiliar_contable')
        {{-- ==================== 4. AUXILIAR CONTABLE (LIBRO MAYOR) ==================== --}}
        @foreach ($data['cuentas'] as $c)
            @if (!$c['es_afectable'])
                <div style="background-color: #2b6cb0; color: #ffffff; padding: 4px 6px; font-weight: bold; font-size: 8pt; margin-top: 8px; margin-bottom: 2px;">
                    {{ $c['codigo'] }} - {{ $c['nombre'] }}
                </div>
            @else
                <table class="conac-table" style="margin-bottom: 8px; page-break-inside: avoid;">
                    <thead>
                        <tr style="background-color: #edf2f7; color: #2d3748;">
                            <th colspan="5" class="text-left" style="background-color: #e2e8f0; color: #1a202c; font-size: 7.5pt;">
                                CUENTA: {{ $c['codigo'] }} {{ $c['nombre'] }} ({{ ucfirst($c['naturaleza']) }})
                            </th>
                            <th colspan="2" class="text-right" style="background-color: #e2e8f0; color: #1a202c; font-size: 7.5pt;">
                                SALDO INICIAL: $ {{ number_format($c['saldo_inicial'], 2) }}
                            </th>
                        </tr>
                        @if(!empty($c['movimientos']))
                            <tr>
                                <th style="width: 10%;">FECHA</th>
                                <th style="width: 8%;">TIPO</th>
                                <th style="width: 15%;">PÓLIZA #</th>
                                <th class="text-left" style="width: 32%;">CONCEPTO / DETALLE</th>
                                <th class="text-left" style="width: 15%;">REF / CONT</th>
                                <th class="text-right" style="width: 10%;">DEBE</th>
                                <th class="text-right" style="width: 10%;">HABER</th>
                            </tr>
                        @endif
                    </thead>
                    <tbody>
                        @forelse ($c['movimientos'] as $m)
                            <tr>
                                <td class="text-center">{{ $m['fecha_formato'] }}</td>
                                <td class="text-center">{{ $m['poliza_tipo'] }}</td>
                                <td class="text-center" style="font-family: monospace;">{{ $m['poliza_numero'] }}</td>
                                <td class="text-left">{{ $m['concepto'] }}</td>
                                <td class="text-left">{{ $m['referencia'] }}</td>
                                <td class="text-right text-success">{{ $m['debe'] > 0 ? '$ ' . number_format($m['debe'], 2) : '-' }}</td>
                                <td class="text-right text-danger">{{ $m['haber'] > 0 ? '$ ' . number_format($m['haber'], 2) : '-' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center" style="color: #718096; font-style: italic; font-size: 7pt; padding: 3px;">
                                    (Sin movimientos en el periodo seleccionado)
                                </td>
                            </tr>
                        @endforelse
                        <tr class="total-row">
                            <td colspan="5" class="text-right" style="font-size: 7.5pt;">TOTAL CUENTA {{ $c['codigo'] }}:</td>
                            <td class="text-right text-success" style="font-size: 7.5pt;">$ {{ number_format($c['total_debe'], 2) }}</td>
                            <td class="text-right text-danger" style="font-size: 7.5pt;">$ {{ number_format($c['total_haber'], 2) }}</td>
                        </tr>
                        <tr style="background-color: #f7fafc; font-weight: bold;">
                            <td colspan="5" class="text-right" style="font-size: 7.5pt;">SALDO FINAL ({{ strtoupper($c['naturaleza']) }}):</td>
                            <td colspan="2" class="text-right" style="font-size: 8pt; color: #2b6cb0;">
                                $ {{ number_format($c['saldo_final'], 2) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            @endif
        @endforeach

        <table class="conac-table" style="margin-top: 10px;">
            <tr class="total-row" style="background-color: #1a365d; color: #ffffff;">
                <td colspan="5" class="text-right" style="color: #ffffff; font-weight: bold; font-size: 8.5pt;">SUMAS TOTALES AUXILIARES:</td>
                <td class="text-right text-success" style="color: #68d391; font-weight: bold; font-size: 8.5pt;">$ {{ number_format($data['total_debe'], 2) }}</td>
                <td class="text-right text-danger" style="color: #fc8181; font-weight: bold; font-size: 8.5pt;">$ {{ number_format($data['total_haber'], 2) }}</td>
            </tr>
        </table>

    @elseif ($tipoReporte === 'libro_diario')
        {{-- ==================== 5. LIBRO DIARIO DE PÓLIZAS ==================== --}}
        <table class="conac-table">
            <thead>
                <tr>
                    <th style="width: 9%;">FECHA</th>
                    <th style="width: 8%;">TIPO</th>
                    <th style="width: 14%;">PÓLIZA #</th>
                    <th style="width: 12%;">CUENTA</th>
                    <th class="text-left" style="width: 25%;">NOMBRE CUENTA / CONCEPTO</th>
                    <th class="text-left" style="width: 14%;">REF / CONT</th>
                    <th class="text-right" style="width: 9%;">DEBE</th>
                    <th class="text-right" style="width: 9%;">HABER</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($data['polizas'] as $p)
                    <tr style="background-color: #edf2f7; font-weight: bold; border-top: 1px solid #2b6cb0;">
                        <td class="text-center">{{ $p['fecha_formato'] }}</td>
                        <td class="text-center">{{ $p['tipo'] }}</td>
                        <td class="text-center" style="font-family: monospace; color: #2b6cb0;">{{ $p['numero'] }}</td>
                        <td colspan="3" class="text-left" style="color: #2d3748;">
                            {{ $p['concepto'] }}
                        </td>
                        <td class="text-right text-success">$ {{ number_format($p['total_debe'], 2) }}</td>
                        <td class="text-right text-danger">$ {{ number_format($p['total_haber'], 2) }}</td>
                    </tr>
                    @foreach ($p['partidas'] as $part)
                        <tr>
                            <td colspan="3" class="text-right" style="color: #a0aec0; font-size: 6.5pt;">
                                <em>Línea {{ $part['linea'] }}</em>
                            </td>
                            <td class="text-center" style="font-family: monospace;">{{ $part['cuenta_codigo'] }}</td>
                            <td class="text-left">
                                <strong>{{ $part['cuenta_nombre'] }}</strong>
                                @if($part['concepto_detalle'] && $part['concepto_detalle'] !== $p['concepto'])
                                    <div style="color: #718096; font-size: 6.5pt;">{{ $part['concepto_detalle'] }}</div>
                                @endif
                            </td>
                            <td class="text-left">{{ $part['referencia'] }}</td>
                            <td class="text-right text-success">{{ $part['debe'] > 0 ? '$ ' . number_format($part['debe'], 2) : '-' }}</td>
                            <td class="text-right text-danger">{{ $part['haber'] > 0 ? '$ ' . number_format($part['haber'], 2) : '-' }}</td>
                        </tr>
                    @endforeach
                @endforeach
            </tbody>
            <tfoot>
                <tr class="total-row" style="background-color: #1a365d; color: #ffffff;">
                    <td colspan="6" class="text-right" style="color: #ffffff; font-weight: bold; font-size: 8.5pt;">SUMAS IGUALES DEL DIARIO:</td>
                    <td class="text-right" style="color: #68d391; font-weight: bold; font-size: 8.5pt;">$ {{ number_format($data['total_debe'], 2) }}</td>
                    <td class="text-right" style="color: #fc8181; font-weight: bold; font-size: 8.5pt;">$ {{ number_format($data['total_haber'], 2) }}</td>
                </tr>
            </tfoot>
        </table>
    @endif

    {{-- LEYENDA LEGAL CONAC --}}
    <div class="protest-clause">
        "Bajo protesta de decir verdad declaramos que los Estados Financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor."
    </div>

    {{-- FIRMAS INSTITUCIONALES CONAC --}}
    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-line">ELABORÓ</div>
                <div class="signature-sub">DEPARTAMENTO CONTABLE</div>
            </td>
            <td>
                <div class="signature-line">REVISÓ</div>
                <div class="signature-sub">DIRECCIÓN FINANCIERA</div>
            </td>
            <td>
                <div class="signature-line">AUTORIZÓ</div>
                <div class="signature-sub">DIRECCIÓN GENERAL</div>
            </td>
        </tr>
    </table>

</body>
</html>
