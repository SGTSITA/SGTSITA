{{-- resources/views/reporteria/vxc/xlsx.blade.php --}}
<table>
    <thead>
        <tr>
            <th># Contenedor</th>
            <th>Subcliente</th>
            <th>Importe pendiente</th>
            <th>Tipo de viaje</th>
            <th>Estatus</th>
            <th>Carta Porte</th>
            <th>XML CP</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cotizaciones as $c)
            <tr>
                <td>{{ $c['num_contenedor'] ?? '-' }}</td>
                <td>{{ $c['subcliente'] ?? '-' }}</td>
                <td>{{ number_format(floatval($c['restante'] ?? 0), 2) }}</td>
                <td>{{ $c['tipo_viaje'] ?? '-' }}</td>
                <td>{{ $c['estatus'] ?? '-' }}</td>
                <td>{{ ! empty($c['carta_porte']) ? '✅' : '❌' }}</td>
                <td>{{ ! empty($c['carta_porte_xml']) ? '✅' : '❌' }}</td>
            </tr>
        @endforeach

        <tr>
            <td colspan="7">
                <strong>Total generado:</strong>
                ${{ number_format(floatval($totalGenerado), 2) }}
            </td>
        </tr>
        <tr>
            <td colspan="7">
                <strong>Retenido:</strong>
                ${{ number_format(floatval($retenido), 2) }}
            </td>
        </tr>
        <tr>
            <td colspan="7">
                <strong>Pago neto:</strong>
                ${{ number_format(floatval($pagoNeto), 2) }}
            </td>
        </tr>
    </tbody>
</table>
