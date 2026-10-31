<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Historial de Pagos</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { margin-bottom: 20px; border-bottom: 2px solid #ddd; padding-bottom: 10px; }
        .title { font-size: 16px; font-weight: bold; text-align: center; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px; text-align: left; }
        th { background-color: #f2f2f2; font-weight: bold; }
        .text-right { text-align: right; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Historial de {{ $tipo === 'cxc' ? 'Cobros (CxC)' : 'Pagos (CxP)' }}</div>
        <div style="margin-top: 5px; text-align: right;">Generado el: {{ date('d-m-Y H:i') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ $tipo === 'cxc' ? 'Cliente' : 'Proveedor' }}</th>
                <th>Fecha Pago A</th>
                <th>Banco A</th>
                <th>Monto A</th>
                <th>Fecha Pago B</th>
                <th>Banco B</th>
                <th>Monto B</th>
                <th>Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pagos as $pago)
                <tr>
                    <td>{{ $tipo === 'cxc' ? ($pago->cliente->nombre ?? 'N/A') : ($pago->proveedor->nombre ?? 'N/A') }}</td>
                    <td>{{ optional($pago->fechaAplicacion1)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $pago->bancoA->nombre_banco ?? '-' }}</td>
                    <td>${{ number_format($pago->monto_A, 2) }}</td>
                    <td>{{ optional($pago->fechaAplicacion2)->format('d-m-Y') ?? '-' }}</td>
                    <td>{{ $pago->bancoB->nombre_banco ?? '-' }}</td>
                    <td>${{ number_format($pago->monto_B, 2) }}</td>
                    <td><strong>${{ number_format($pago->monto_A + $pago->monto_B, 2) }}</strong></td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
