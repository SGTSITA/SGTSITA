<style>
    body {
        font-family: DejaVu Sans, sans-serif;
        font-size: 12px;
        color: #333;
    }

    .header {
        width: 100%;
        margin-bottom: 20px;
    }

    .header-table {
        width: 100%;
    }

    .logo {
        width: 160px;
    }

    .titulo {
        font-size: 18px;
        font-weight: bold;
    }

    .subinfo {
        font-size: 11px;
        color: #666;
    }

    .resumen-card {
        border: 1px solid #ddd;
        padding: 12px;
        margin-bottom: 20px;
    }

    .resumen-table {
        width: 100%;
        text-align: center;
    }

    .resumen-table td {
        padding: 8px;
    }

    .saldo-inicial {
        color: #0d6efd;
        font-weight: bold;
    }

    .abonos {
        color: #198754;
        font-weight: bold;
    }

    .cargos {
        color: #dc3545;
        font-weight: bold;
    }

    .saldo-final {
        color: #000;
        font-weight: bold;
    }

    table.movimientos {
        width: 100%;
        border-collapse: collapse;
        font-size: 9px;
    }

    table.movimientos th {
        background: #f2f2f2;
        border-bottom: 1px solid #ccc;
        padding: 6px;
        text-align: left;
    }

    table.movimientos td {
        padding: 6px;
        border-bottom: 1px solid #eee;
    }

    .text-end {
        text-align: right;
    }

    .cargo {
        color: #dc3545;
        font-weight: bold;
    }

    .abono {
        color: #198754;
        font-weight: bold;
    }

    .footer {
        margin-top: 20px;
        font-size: 10px;
        text-align: center;
        color: #888;
    }
    .negativo {
        color: #ea580c;
        font-weight: bold;
    }
</style>
@php
    $logo = optional($cuenta->catBanco)->logo;
    $bancoTipoText = $cuenta->banco_1 == 1 ? 'Banco 1 (Base 1 / Oficial)' : 'Banco 2 (Base 2 / Secundaria)';
@endphp
<div
    style="
    width:100%;
    height:6px;
    background-color: {{ $cuenta->catBanco->color ?? '#000' }};
    margin-bottom:15px;
">
</div>

<div class="header">
    <table class="header-table">
        <tr>

            <td width="30%" style="vertical-align: top;">
                <strong>SGT-Software</strong><br>
                @if ($logo && file_exists(public_path($logo)))
                    <img src="{{ public_path($logo) }}" style="width:220px;">
                @else
                    <div style="font-size:18px; font-weight:bold;">
                        {{ $cuenta->catBanco->nombre }}
                    </div>
                @endif
            </td>

            <td width="70%" class="text-end">
                <div class="titulo">Estado de Cuenta</div>
                <div class="subinfo">
                    <strong style="color: #2563eb;">{{ $bancoTipoText }}</strong><br>
                    {{ $cuenta->nombre_beneficiario }}<br>
                    Cuenta: {{ $cuenta->cuenta_bancaria }} ({{ $cuenta->tipo }})<br>
                    Moneda: {{ $cuenta->moneda }}<br>
                    Periodo: {{ $fecha_inicio }} al {{ $fecha_fin }}<br>
                    Generado: {{ now()->format('d/m/Y H:i') }}
                </div>
            </td>
        </tr>
    </table>
</div>

<div class="resumen-card">
    <table class="resumen-table">
        <tr>
            <td>
                <small>Saldo inicial</small><br>
                <span class="{{ $saldoAnterior < 0 ? 'negativo' : 'saldo-inicial' }}">
                    {{ $saldoAnterior < 0 ? '-$' . number_format(abs($saldoAnterior), 2) : '$' . number_format($saldoAnterior, 2) }}
                </span>
            </td>

            <td>
                <small>Abonos ({{ $conteo_depositos }})</small><br>
                <span class="abonos">
                    + ${{ number_format($total_depositos, 2) }}
                </span>
            </td>

            <td>
                <small>Cargos ({{ $conteo_cargos }})</small><br>
                <span class="cargos">
                    - ${{ number_format($total_cargos, 2) }}
                </span>
            </td>

            <td>
                <small>Saldo final</small><br>
                <span class="{{ $saldoActual < 0 ? 'negativo' : 'saldo-final' }}">
                    {{ $saldoActual < 0 ? '-$' . number_format(abs($saldoActual), 2) : '$' . number_format($saldoActual, 2) }}
                </span>
            </td>
        </tr>
    </table>
</div>
<div
    style="
    width:100%;
    height:2px;
    background-color: {{ $cuenta->catBanco->color ?? '#000' }};
    margin:10px 0;
">
</div>
<table class="movimientos">
    <thead>
        <tr>
            <th>Fecha</th>
            <th>Concepto</th>
            <th>Referencia</th>
            <th class="text-end">Cargo</th>
            <th class="text-end">Abono</th>
            <th class="text-end">Saldo</th>
            <th>Origen</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($movimientos as $mov)
            @php
                $montoNum = (float) $mov->monto;
                $esNeg = $montoNum < 0;
                $saldoResNum = (float) $mov->saldo_resultante;
            @endphp
            <tr>
                <td>{{ \Carbon\Carbon::parse($mov->fecha_movimiento)->format('d/m/Y') }}</td>
                <td>{{ $mov->concepto }}</td>
                <td>{{ $mov->referencia }}</td>

                <td class="text-end {{ $esNeg ? 'negativo' : 'cargo' }}">
                    @if ($mov->tipo == 'cargo')
                        {{ $esNeg ? '-$' . number_format(abs($montoNum), 2) : '- $' . number_format($montoNum, 2) }}
                    @endif
                </td>

                <td class="text-end {{ $esNeg ? 'negativo' : 'abono' }}">
                    @if ($mov->tipo == 'abono')
                        {{ $esNeg ? '-$' . number_format(abs($montoNum), 2) : '+ $' . number_format($montoNum, 2) }}
                    @endif
                </td>

                <td class="text-end {{ $saldoResNum < 0 ? 'negativo' : '' }}">
                    {{ $saldoResNum < 0 ? '-$' . number_format(abs($saldoResNum), 2) : '$' . number_format($saldoResNum, 2) }}
                </td>

                <td>{{ strtoupper(substr($mov->origen, 0, 3)) }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
<div class="footer">
    <div
        style="
    width:100%;
    height:4px;
    background-color: {{ $cuenta->catBanco->color ?? '#000' }};
    margin-top:20px;
">
    </div>
    Documento generado automáticamente por SITA software.<br>
    Este documento no requiere firma.
</div>
