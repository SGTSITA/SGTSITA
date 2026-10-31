<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Reporte de Viáticos - {{ $operadorNombre }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #2b2b2b;
            font-size: 11px;
            line-height: 1.4;
            margin: 15px;
        }
        .header {
            border-bottom: 2px solid #5e72e4;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header-table {
            width: 100%;
        }
        .header-title {
            font-size: 18px;
            font-weight: bold;
            color: #5e72e4;
            margin: 0;
            text-transform: uppercase;
        }
        .header-sub {
            font-size: 11px;
            color: #6c757d;
        }
        .info-box {
            background-color: #f8f9fe;
            border: 1px solid #e9ecef;
            border-left: 4px solid #5e72e4;
            padding: 8px 12px;
            margin-bottom: 15px;
            border-radius: 3px;
        }
        .info-table {
            width: 100%;
        }
        .info-table td {
            padding: 2px 0;
            font-size: 11px;
        }
        .label {
            font-weight: bold;
            color: #32325d;
        }
        .contenedor-card {
            border: 1px solid #dee2e6;
            border-radius: 4px;
            margin-bottom: 15px;
            background-color: #ffffff;
        }
        .contenedor-header {
            background-color: #f6f9fc;
            border-bottom: 1px solid #dee2e6;
            padding: 8px 12px;
        }
        .contenedor-title {
            font-size: 12px;
            font-weight: bold;
            color: #32325d;
        }
        .contenedor-fecha {
            font-size: 11px;
            font-weight: bold;
            color: #2dce89;
            float: right;
        }
        .gastos-table {
            width: 100%;
            border-collapse: collapse;
        }
        .gastos-table th {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e9ecef;
            padding: 6px 12px;
            font-size: 10px;
            text-transform: uppercase;
            color: #8898aa;
            text-align: left;
        }
        .gastos-table td {
            padding: 6px 12px;
            border-bottom: 1px solid #f1f3f5;
            font-size: 11px;
        }
        .text-right {
            text-align: right;
        }
        .no-gastos {
            padding: 10px 12px;
            font-style: italic;
            color: #8898aa;
            font-size: 10px;
        }
        .total-box {
            margin-top: 20px;
            padding: 10px 15px;
            background-color: #f6f9fc;
            border: 1px solid #e9ecef;
            border-radius: 4px;
        }
        .total-title {
            font-size: 14px;
            font-weight: bold;
            color: #32325d;
        }
        .total-amount {
            font-size: 16px;
            font-weight: bold;
            color: #fb6340;
            float: right;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 9px;
            color: #8898aa;
            border-top: 1px solid #e9ecef;
            padding-top: 8px;
        }
    </style>
</head>
<body>

    <!-- ENCABEZADO SUPERIOR -->
    <div class="header">
        <table class="header-table">
            <tr>
                <td>
                    <div class="header-title">Reporte de Viáticos</div>
                    <div class="header-sub">SGT Logistics — Módulo de Administración</div>
                </td>
                <td class="text-right">
                    <div class="header-sub">Fecha Emisión</div>
                    <div class="label">{{ $fechaGeneracion }}</div>
                </td>
            </tr>
        </table>
    </div>

    <!-- INFORMACIÓN DE ENCABEZADO: OPERADOR Y EMPRESA -->
    <div class="info-box">
        <table class="info-table">
            <tr>
                <td style="width: 50%;">
                    <span class="label">Operador:</span> {{ $operadorNombre }}
                </td>
                <td style="width: 50%;">
                    <span class="label">Empresa:</span> {{ $empresaNombre }}
                </td>
            </tr>
        </table>
    </div>

    <!-- LISTADO DE VIAJES / CONTENEDORES -->
    @foreach ($reporteData as $data)
        <div class="contenedor-card">
            <div class="contenedor-header">
                <span class="contenedor-title">Contenedor: {{ $data['num_contenedor'] }}</span>
                <span class="contenedor-fecha">Fecha Inicio Viaje: {{ $data['fecha_inicio'] }}</span>
                @if (!empty($data['referencia']))
                    <div style="font-size: 10px; color: #6c757d; margin-top: 2px;">
                        Referencia: {{ $data['referencia'] }}
                    </div>
                @endif
            </div>

            @if (count($data['gastos']) > 0)
                <table class="gastos-table">
                    <thead>
                        <tr>
                            <th>Concepto</th>
                            <th class="text-right">Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($data['gastos'] as $gasto)
                            <tr>
                                <td>{{ $gasto['concepto'] }}</td>
                                <td class="text-right">${{ number_format($gasto['monto'], 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @else
                <div class="no-gastos">
                    * No se registraron gastos de viáticos para este viaje.
                </div>
            @endif
        </div>
    @endforeach

    <!-- TOTAL GENERAL -->
    <div class="total-box">
        <span class="total-title">TOTAL GENERAL DE VIÁTICOS:</span>
        <span class="total-amount">${{ number_format($totalGeneral, 2) }}</span>
        <div style="clear: both;"></div>
    </div>

    <div class="footer">
        Este documento es un reporte informativo generado desde la administración del sistema SGT Logistics.
    </div>

</body>
</html>
