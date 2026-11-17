<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <title>Comprobante de Ajuste de Liquidación - {{ $ajuste->folio }}</title>
    <style>
        @page {
            margin: 25px 30px;
        }
        body {
            font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
            font-size: 11px;
            color: #333;
            line-height: 1.3;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            border-bottom: 2px solid #2c3e50;
            padding-bottom: 10px;
        }
        .header-table td {
            border: none;
            padding: 4px;
            vertical-align: middle;
        }
        .title {
            font-size: 16px;
            font-weight: bold;
            color: #2c3e50;
            text-transform: uppercase;
            margin: 0;
        }
        .subtitle {
            font-size: 10px;
            color: #7f8c8d;
        }
        .folio-box {
            text-align: right;
        }
        .folio-number {
            font-size: 14px;
            font-weight: bold;
            color: #c0392b;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            border-radius: 4px;
            background-color: #27ae60;
            color: #fff;
        }
        .status-cancelado {
            background-color: #e74c3c !important;
        }
        .info-card {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            background-color: #fcfcfc;
        }
        .info-card td {
            border: 1px solid #e1e8ed;
            padding: 6px 8px;
            font-size: 10.5px;
        }
        .info-label {
            font-weight: bold;
            color: #495057;
            background-color: #f1f3f5;
            width: 18%;
        }
        .info-value {
            color: #212529;
            width: 32%;
        }
        .section-title {
            font-size: 12px;
            font-weight: bold;
            color: #2c3e50;
            margin-top: 15px;
            margin-bottom: 6px;
            border-bottom: 1px solid #bdc3c7;
            padding-bottom: 3px;
        }
        .table-data {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .table-data th {
            background-color: #f4f6f7;
            color: #2c3e50;
            border: 1px solid #dcdcdc;
            padding: 6px 8px;
            font-size: 10px;
            text-align: left;
        }
        .table-data td {
            border: 1px solid #e5e5e5;
            padding: 5px 8px;
            font-size: 10px;
        }
        .text-end {
            text-align: right !important;
        }
        .text-center {
            text-align: center !important;
        }
        .totals-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 25px;
        }
        .totals-table td {
            padding: 5px 8px;
            font-size: 11px;
            border: 1px solid #ddd;
        }
        .totals-label {
            font-weight: bold;
            color: #495057;
            background-color: #f8f9fa;
        }
        .grand-total {
            font-size: 13px !important;
            font-weight: bold;
            background-color: #e8f4f8 !important;
            color: #1a5276 !important;
        }
        .signatures-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 40px;
        }
        .signatures-table td {
            border: none;
            text-align: center;
            vertical-align: top;
            width: 33.33%;
            padding: 0 15px;
        }
        .signature-line {
            border-top: 1px solid #2c3e50;
            margin-top: 45px;
            padding-top: 5px;
            font-size: 10px;
            font-weight: bold;
            color: #2c3e50;
        }
        .signature-role {
            font-size: 9px;
            color: #7f8c8d;
        }
    </style>
</head>
<body>

    <!-- CABECERA -->
    <table class="header-table">
        <tr>
            <td style="width: 70%;">
                <h1 class="title">SISTEMA ERP DE TRANSPORTE Y LOGÍSTICA</h1>
                <div class="subtitle">Comprobante Oficial de Ajuste y Complemento de Liquidación</div>
            </td>
            <td class="folio-box">
                <div class="folio-number">{{ $ajuste->folio }}</div>
                <div style="margin-top: 3px;">
                    <span class="status-badge {{ $ajuste->estatus === 'cancelado' ? 'status-cancelado' : '' }}">
                        {{ strtoupper($ajuste->estatus) }}
                    </span>
                </div>
                <div style="font-size: 9px; color: #7f8c8d; margin-top: 2px;">
                    Emisión: {{ date('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>

    <!-- INFORMACIÓN GENERAL -->
    <table class="info-card">
        <tr>
            <td class="info-label">Operador:</td>
            <td class="info-value" colspan="3">
                <strong>{{ $ajuste->operador?->nombre ?? 'N/A' }}</strong> (ID: {{ $ajuste->id_operador }})
            </td>
        </tr>
        <tr>
            <td class="info-label">Liquidación Afectada:</td>
            <td class="info-value">
                <strong>LIQ #{{ $ajuste->id_liquidacion }}</strong> 
                ({{ \Carbon\Carbon::parse($ajuste->liquidacion?->fecha)->format('d/m/Y') }})
            </td>
            <td class="info-label">Fecha del Ajuste:</td>
            <td class="info-value">
                <strong>{{ \Carbon\Carbon::parse($ajuste->fecha)->format('d/m/Y') }}</strong>
            </td>
        </tr>
        <tr>
            <td class="info-label">Contenedores Orig.:</td>
            <td class="info-value">
                {{ $ajuste->liquidacion?->Viajes?->map(function($v){ return $v->Contenedores?->num_contenedor; })->filter()->unique()->implode(', ') ?: 'N/A' }}
            </td>
            <td class="info-label">Liq. Original Pagada:</td>
            <td class="info-value">
                ${{ number_format($ajuste->liquidacion?->total_pago ?? 0, 2) }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Cuenta Bancaria:</td>
            <td class="info-value">
                @if($ajuste->banco)
                    {{ $ajuste->banco->nombre_banco }} - Cta: {{ $ajuste->banco->cuenta_bancaria }}
                @else
                    <em>Sin dispersión bancaria directa</em>
                @endif
            </td>
            <td class="info-label">Registrado por:</td>
            <td class="info-value">
                {{ $ajuste->usuario?->name ?? 'Sistema' }}
            </td>
        </tr>
        <tr>
            <td class="info-label">Motivo / Causa:</td>
            <td class="info-value" colspan="3" style="background-color: #fff9e6;">
                <em>{{ $ajuste->motivo }}</em>
            </td>
        </tr>
    </table>

    <!-- PERCEPCIONES / ADICIONALES -->
    <div class="section-title">1. PERCEPCIONES Y CONCEPTOS A FAVOR (+)</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 25%;">Concepto</th>
                <th style="width: 55%;">Descripción / Justificación</th>
                <th class="text-end" style="width: 20%;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @php $percepciones = $ajuste->detalles->where('tipo', 'percepcion'); @endphp
            @forelse($percepciones as $p)
                <tr>
                    <td><strong>{{ $p->nombre_concepto }}</strong></td>
                    <td>{{ $p->descripcion ?: '-' }}</td>
                    <td class="text-end">${{ number_format($p->monto, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="color: #999;">No se registraron percepciones adicionales en este ajuste.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #fcfcfc;">
                <td colspan="2" class="text-end" style="font-weight: bold;">Subtotal Percepciones (+):</td>
                <td class="text-end" style="font-weight: bold; color: #27ae60;">
                    ${{ number_format($ajuste->total_percepciones, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- DEDUCCIONES / RETENCIONES -->
    <div class="section-title">2. DEDUCCIONES Y RETENCIONES (-)</div>
    <table class="table-data">
        <thead>
            <tr>
                <th style="width: 25%;">Concepto</th>
                <th style="width: 55%;">Descripción / Préstamo Vinculado</th>
                <th class="text-end" style="width: 20%;">Monto</th>
            </tr>
        </thead>
        <tbody>
            @php $deducciones = $ajuste->detalles->where('tipo', 'deduccion'); @endphp
            @forelse($deducciones as $d)
                <tr>
                    <td><strong>{{ $d->nombre_concepto }}</strong></td>
                    <td>
                        {{ $d->descripcion ?: '-' }}
                        @if($d->prestamo)
                            <br><small style="color: #666;">(Amortización a {{ ucfirst($d->prestamo->tipo) }} Folio #{{ $d->prestamo->id }})</small>
                        @endif
                    </td>
                    <td class="text-end">${{ number_format($d->monto, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="3" class="text-center" style="color: #999;">No se aplicaron deducciones en este ajuste.</td>
                </tr>
            @endforelse
        </tbody>
        <tfoot>
            <tr style="background-color: #fcfcfc;">
                <td colspan="2" class="text-end" style="font-weight: bold;">Subtotal Deducciones (-):</td>
                <td class="text-end" style="font-weight: bold; color: #c0392b;">
                    ${{ number_format($ajuste->total_deducciones, 2) }}
                </td>
            </tr>
        </tfoot>
    </table>

    <!-- RESUMEN FINAL -->
    <table class="totals-table">
        <tr>
            <td class="totals-label">Total Percepciones:</td>
            <td class="text-end" style="color: #27ae60;">+${{ number_format($ajuste->total_percepciones, 2) }}</td>
        </tr>
        <tr>
            <td class="totals-label">Total Deducciones:</td>
            <td class="text-end" style="color: #c0392b;">-${{ number_format($ajuste->total_deducciones, 2) }}</td>
        </tr>
        <tr>
            <td class="totals-label grand-total">IMPORTE NETO A PAGAR:</td>
            <td class="text-end grand-total">${{ number_format($ajuste->total_neto, 2) }}</td>
        </tr>
    </table>

    <!-- FIRMAS DE CONFORMIDAD -->
    <table class="signatures-table">
        <tr>
            <td>
                <div class="signature-line">{{ $ajuste->operador?->nombre ?? 'OPERADOR' }}</div>
                <div class="signature-role">Operador / Recibí de Conformidad</div>
            </td>
            <td>
                <div class="signature-line">{{ $ajuste->usuario?->name ?? 'USUARIO' }}</div>
                <div class="signature-role">Elaboró / Liquidaciones</div>
            </td>
            <td>
                <div class="signature-line">DIRECCIÓN / FINANZAS</div>
                <div class="signature-role">Autorizó</div>
            </td>
        </tr>
    </table>

</body>
</html>
