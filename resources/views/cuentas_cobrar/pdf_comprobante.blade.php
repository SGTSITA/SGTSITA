<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <title>Comprobante de {{ $pago->tipo === 'cxc' ? 'Cobro' : 'Pago' }} #{{ $pago->id }}</title>
    <style>
        body {
            font-family: sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.4;
        }

        .header-table {
            width: 100%;
            border: none;
            margin-bottom: 20px;
        }

        .logo-cell {
            width: 30%;
            text-align: left;
        }

        .info-cell {
            width: 70%;
            text-align: right;
        }

        .title-banner {
            background-color: #f2f2f2;
            padding: 10px;
            text-align: center;
            font-size: 15px;
            font-weight: bold;
            margin-bottom: 20px;
            text-transform: uppercase;
            border: 1px solid #ddd;
        }

        .details-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        .details-table td {
            padding: 8px;
            border: 1px solid #ddd;
            vertical-align: top;
        }

        .details-table td.label {
            font-weight: bold;
            background-color: #fafafa;
            width: 25%;
        }

        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        .items-table th,
        .items-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        .items-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }

        .totals {
            margin-top: 20px;
            text-align: right;
            font-size: 13px;
        }

        .signature-section {
            margin-top: 50px;
            text-align: center;
        }

        .signature-line {
            width: 200px;
            border-bottom: 1px solid #000;
            margin: 0 auto 5px auto;
        }
    </style>
</head>

<body>
    <table class="header-table">
        <tr>
            <td class="logo-cell">
                @if ($configuracion && $configuracion->logo_img)
                    <img src="{{ public_path('favicon/' . $configuracion->logo_img) }}" alt="Logo"
                        style="max-height: 70px;">
                @else
                    <h2>{{ $configuracion->nombre_sistema ?? 'SGTSITA' }}</h2>
                @endif
            </td>
            <td class="info-cell">
                <strong>{{ $configuracion->nombre_empresa ?? 'SITA' }}</strong><br>

            </td>
        </tr>
    </table>

    <div class="title-banner">
        Comprobante de {{ $pago->tipo === 'cxc' ? 'Cobro (CxC)' : 'Pago (CxP)' }}
    </div>

    <table class="details-table">
        <tr>
            <td class="label">Folio:</td>
            <td>#{{ $pago->id }}</td>
            <td class="label">Fecha Emisión:</td>
            <td>{{ date('d-m-Y H:i') }}</td>
        </tr>
        <tr>
            <td class="label">{{ $pago->tipo === 'cxc' ? 'Cliente:' : 'Proveedor:' }}</td>
            <td colspan="3">
                <strong>
                    {{ $pago->tipo === 'cxc' ? $pago->cliente->nombre ?? 'N/A' : $pago->proveedor->nombre ?? 'N/A' }}
                </strong>
            </td>
        </tr>
        @if ($pago->monto_A > 0)
            <tr>
                <td class="label">Banco A / Cuenta:</td>
                <td>{{ $pago->bancoA->nombre_banco ?? '-' }}</td>
                <td class="label">Monto A / Fecha:</td>
                <td>${{ number_format($pago->monto_A, 2) }}
                    ({{ optional($pago->fechaAplicacion1)->format('d-m-Y') ?? '-' }})</td>
            </tr>
        @endif
        @if ($pago->monto_B > 0)
            <tr>
                <td class="label">Banco B / Cuenta:</td>
                <td>{{ $pago->bancoB->nombre_banco ?? '-' }}</td>
                <td class="label">Monto B / Fecha:</td>
                <td>${{ number_format($pago->monto_B, 2) }}
                    ({{ optional($pago->fechaAplicacion2)->format('d-m-Y') ?? '-' }})</td>
            </tr>
        @endif
        @if ($pago->observaciones)
            <tr>
                <td class="label">Observaciones:</td>
                <td colspan="3">{{ $pago->observaciones }}</td>
            </tr>
        @endif
    </table>

    <h4>Viajes / Cotizaciones Asociadas</h4>
    <table class="items-table">
        <thead>
            <tr>
                <th>Cotización ID</th>
                <th>Contenedor</th>
                <th>Origen</th>
                <th>Destino</th>
                <th style="text-align: right;">Monto Aplicado</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($pago->detalles as $detalle)
                <tr>
                    <td>{{ $detalle->cotizacion_id }}</td>
                    <td><strong>{{ $detalle->cotizacion->DocCotizacion->num_contenedor ?? 'N/A' }}</strong></td>
                    <td>{{ $detalle->cotizacion->origen ?? '-' }}</td>
                    <td>{{ $detalle->cotizacion->destino ?? '-' }}</td>
                    <td style="text-align: right;">${{ number_format($detalle->monto, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <strong>Total Aplicado: ${{ number_format($pago->monto_A + $pago->monto_B, 2) }}</strong>
    </div>

    <div class="signature-section">
        <div class="signature-line"></div>
        <strong>Reporte generado por: {{ auth()->user()->name }} , SITA-Software</strong>
    </div>
</body>

</html>
