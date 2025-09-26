@php
    $agrupado = collect($datos)->groupBy('Contenedor');
@endphp

<tr>
    <td colspan="5"><strong>Detalle de los Gastos </strong></td>
</tr>
<tr>
    <td colspan="5"><strong>Total general: ${{ number_format($datos->sum('Monto'), 2, '.', ',') }}</strong></td>
</tr>

@foreach ($agrupado as $contenedor => $gastos)
    <tr>
        <td colspan="5"><strong>Contenedor: {{ $contenedor }}</strong></td>
    </tr>
    <tr>
        <td>Motivo</td>
        <td>Fecha</td>
        <td>Tipo</td>
        <td>Monto</td>
        <td></td>
    </tr>
    @php $subtotal = 0; @endphp
    @foreach ($gastos as $gasto)
        <tr>
            <td>{{ $gasto['Motivo'] }}</td>
            <td>{{ $gasto['Fecha'] }}</td>
            <td>{{ $gasto['Tipo'] }}</td>
            <td>{{ number_format($gasto['Monto'], 2, '.', ',') }}</td>
            <td></td>
        </tr>
        @php $subtotal += $gasto['Monto']; @endphp
    @endforeach
    <tr>
        <td colspan="3" style="text-align:right"><strong>Total del contenedor:</strong></td>
        <td><strong>{{ number_format($subtotal, 2, '.', ',') }}</strong></td>
        <td></td>
    </tr>
@endforeach
