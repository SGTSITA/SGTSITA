<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Utilidad por Socio</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #333;
            font-size: 11px;
            line-height: 1.4;
        }
        .header {
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 8px;
        }
        .header h2 {
            margin: 0;
            font-size: 18px;
            text-transform: uppercase;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 3px 0;
        }
        .section-title {
            font-size: 13px;
            font-weight: bold;
            margin-top: 15px;
            margin-bottom: 8px;
            text-transform: uppercase;
            border-bottom: 1px solid #ddd;
            padding-bottom: 3px;
        }
        .summary-box {
            background-color: #f8f9fa;
            border: 1px solid #e9ecef;
            padding: 10px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .summary-box table {
            width: 100%;
        }
        .summary-box td {
            padding: 5px;
            font-size: 12px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        table.data-table th {
            background-color: #f1f3f5;
            text-align: left;
            padding: 6px;
            font-weight: bold;
            border: 1px solid #dee2e6;
        }
        table.data-table td {
            padding: 6px;
            border: 1px solid #dee2e6;
        }
        .text-right {
            text-align: right;
        }
        .font-bold {
            font-weight: bold;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
    </style>
</head>
<body>
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
            <td colspan="3">{{ date('d-m-Y', strtotime($data['fecha_desde'])) }} al {{ date('d-m-Y', strtotime($data['fecha_hasta'])) }}</td>
        </tr>
    </table>

    @if(!isset($tipoReporte) || $tipoReporte === 'completo')
        <div class="section-title">Resumen Financiero del Periodo</div>
        <div class="summary-box">
            <table>
                <tr>
                    <td><strong>Utilidad Bruta Viajes:</strong></td>
                    <td class="text-right font-bold">$ {{ number_format($data['total_utilidad_bruta_viajes'], 2) }}</td>
                    <td><strong>Utilidad a repartir:</strong></td>
                    <td class="text-right font-bold text-danger">$ {{ number_format($data['total_distribuido_socios'], 2) }}</td>
                </tr>
                <tr>
                    <td><strong>Gastos Indirectos Mes:</strong></td>
                    <td class="text-right font-bold text-danger">$ {{ number_format($data['total_gastos_periodo'], 2) }}</td>
                    <td><strong>Utilidad Neta Empresa:</strong></td>
                    <td class="text-right font-bold text-success">$ {{ number_format($data['utilidad_neta_empresa'], 2) }}</td>
                </tr>
            </table>
        </div>
    @else
        <div class="section-title">Resumen de Liquidación del Periodo</div>
        <div class="summary-box">
            <table>
                <tr>
                    <td width="35%"><strong>Total Asignado a Socio:</strong></td>
                    <td class="text-right font-bold text-success" style="font-size: 14px;">$ {{ number_format($data['total_distribuido_socios'], 2) }}</td>
                </tr>
            </table>
        </div>
    @endif

    <div class="section-title">Distribución Agrupada por Socio</div>
    <table class="data-table">
        <thead>
            <tr>
                <th>Socio</th>
                <th>Unidad Pactada</th>
                <th>Regla de Pago</th>
                <th class="text-right">Viajes Realizados</th>
                <th class="text-right">Monto Distribuido</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data['socios_desglose'] as $soc)
                <tr>
                    <td>{{ $soc['socio'] }}</td>
                    <td>{{ $soc['unidad'] }}</td>
                    <td>{{ $soc['factor'] }}</td>
                    <td class="text-right">{{ $soc['viajes_realizados'] }}</td>
                    <td class="text-right font-bold">$ {{ number_format($soc['monto_distribuido'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @if(!isset($tipoReporte) || $tipoReporte === 'completo')
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
                @foreach($data['viajes_desglose'] as $v)
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
    @endif
</body>
</html>
