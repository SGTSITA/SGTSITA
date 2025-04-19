<table>
    <thead>
        <tr>
            <th>Contenedor</th>
            <th>Cliente</th>
            <th>Subcliente</th>
            <th>Origen</th>
            <th>Destino</th>
            <th>Estatus</th>
            <th>Fecha salida</th>
            <th>Fecha llegada</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($cotizaciones as $cotizacion)
            <tr>
                <td>{{ $cotizacion->Contenedor->num_contenedor }}</td>
                <td>{{ $cotizacion->Contenedor->Cotizacion->Cliente->nombre }}</td>
                <td>
                    @if ($cotizacion->Contenedor->Cotizacion->id_subcliente)
                        {{ $cotizacion->Contenedor->Cotizacion->Subcliente->nombre }} /
                        {{ $cotizacion->Contenedor->Cotizacion->Subcliente->telefono }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $cotizacion->Contenedor->Cotizacion->origen }}</td>
                <td>{{ $cotizacion->Contenedor->Cotizacion->destino }}</td>
                <td>{{ $cotizacion->Contenedor->Cotizacion->estatus }}</td>
                <td>{{ \Carbon\Carbon::parse($cotizacion->fehca_inicio_guard)->format('d-m-Y') }}</td>
                <td>{{ \Carbon\Carbon::parse($cotizacion->fehca_fin_guard)->format('d-m-Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>
