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
            <th>Total Cobrado/Pagado</th>
            <th>Observaciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach($pagos as $pago)
            <tr>
                <td>{{ $tipo === 'cxc' ? ($pago->cliente->nombre ?? 'N/A') : ($pago->proveedor->nombre ?? 'N/A') }}</td>
                <td>{{ optional($pago->fechaAplicacion1)->format('d-m-Y') ?? '-' }}</td>
                <td>{{ $pago->bancoA->nombre_banco ?? '-' }}</td>
                <td>{{ $pago->monto_A }}</td>
                <td>{{ optional($pago->fechaAplicacion2)->format('d-m-Y') ?? '-' }}</td>
                <td>{{ $pago->bancoB->nombre_banco ?? '-' }}</td>
                <td>{{ $pago->monto_B }}</td>
                <td>{{ $pago->monto_A + $pago->monto_B }}</td>
                <td>{{ $pago->observaciones }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
