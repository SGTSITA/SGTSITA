<!DOCTYPE html>
<html>
    <style>
        table {
        font-family: arial, sans-serif;
        border-collapse: collapse;
        width: 100%;
        }

        td, th {
        border: 1px solid #dddddd;
        text-align: left;
        padding: 8px;
        }

        tr:nth-child(even) {
        background-color: #dddddd;
        }
    </style>
    <head>
        <title>Reporte de bancos</title>
    </head>

    <body>
        <h2>Reporte de banco</h2>
        <h4>{{$banco->nombre_banco}}</h4>
        <table>
        <thead>
            <tr>
            <th>Fecha</th>
            <th>Contenedor</th>
            <th>Cobros</th>
            <th>Pagos</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($combined as $item)
                @if(isset($item->fecha_pago))
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->fecha_pago)->translatedFormat('j \d\e F') }}</td>
                        <td>
                            @if(!isset($item->id_operador))
                                @if(isset($item->DocCotizacion))
                                        {{ $item->DocCotizacion->num_contenedor }} <br> {{ $item->Cliente->nombre }}
                                @elseif(isset($item->Cliente))
                                    {{-- Provisional --}}
                                    @if($item->tipo == 'Salida')
                                        Varios <br> {{ $item->Cliente2->nombre }} <br>
                                        @if ($item->contenedores != null)
                                                Contenedores y Abonos
                                            <ul>
                                                @php
                                                    $contenedoresAbonos = json_decode($item->contenedores, true);
                                                @endphp
                                                @foreach ($contenedoresAbonos as $contenedorAbono)
                                                    <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @else
                                        Varios <br> {{ $item->Cliente->nombre }} <br>
                                        @if ($item->contenedores != null)
                                                Contenedores y Abonos
                                            <ul>
                                                @php
                                                    $contenedoresAbonos = json_decode($item->contenedores, true);
                                                @endphp
                                                @foreach ($contenedoresAbonos as $contenedorAbono)
                                                    <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                                @endforeach
                                            </ul>
                                        @endif
                                    @endif
                                @elseif(isset($item->Proveedor))
                                    Varios <br> {{ $item->Proveedor->nombre }} <br>
                                    @if ($item->contenedores != null)
                                            Contenedores y Abonos
                                        <ul>
                                            @php
                                                $contenedoresAbonos = json_decode($item->contenedores, true);
                                            @endphp
                                            @foreach ($contenedoresAbonos as $contenedorAbono)
                                                <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                @endif
                            @else
                                @if(isset($item->contenedores))
                                    Liquidación Varios <br> {{ $item->Operador->nombre }}
                                    Contenedores y Abonos
                                    <ul>
                                        @php
                                            $contenedoresAbonos = json_decode($item->contenedores, true);
                                        @endphp
                                        @foreach ($contenedoresAbonos as $contenedorAbono)
                                            <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                        @endforeach
                                    </ul>
                                @elseif(isset($item->id_cotizacion))
                                    {{ $item->Asignacion->Contenedor->num_contenedor }}<br>{{ $item->Operador->nombre }}
                                @endif
                            @endif
                        </td>
                        <td class="penultima-columna">
                            @if(!isset($item->id_operador))
                                @if(!isset($item->tipo))
                                    @if (isset($item->id_banco1) && $item->id_banco1 == $banco->id)
                                        $ {{ number_format($item->monto1, 0, '.', ',') }}
                                    @else
                                        $ {{ number_format($item->monto2, 0, '.', ',') }}
                                    @endif
                                @else
                                    @if ($item->tipo == 'Entrada')
                                        @if (isset($item->id_banco1) && $item->id_banco1 == $banco->id)
                                            $ {{ number_format($item->monto1, 0, '.', ',') }}
                                        @else
                                            $ {{ number_format($item->monto2, 0, '.', ',') }}
                                        @endif
                                    @endif
                                @endif
                            @endif
                        </td>
                        <td class="ultima-columna">
                            @if(isset($item->id_operador))
                                @if (isset($item->id_banco1) && $item->id_banco1 == $banco->id)
                                    $ {{ number_format($item->monto1, 0, '.', ',') }}
                                @else
                                    $ {{ number_format($item->monto2, 0, '.', ',') }}
                                @endif
                            @else
                                @if ($item->tipo == 'Salida')
                                    @if (isset($item->id_banco1) && $item->id_banco1 == $banco->id)
                                        $ {{ number_format($item->monto1, 0, '.', ',') }}
                                    @else
                                        $ {{ number_format($item->monto2, 0, '.', ',') }}
                                    @endif
                                @endif
                            @endif
                        </td>
                    </tr>
                @elseif(isset($item->fecha_pago_proveedor))
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->fecha_pago_proveedor)->translatedFormat('j \d\e F') }}</td>
                        <td>{{ $item->DocCotizacion->num_contenedor }} <br> {{ $item->DocCotizacion->Asignaciones->Proveedor->nombre }}</td>
                        <td></td>
                        <td class="ultima-columna">
                            @if (isset($item->id_prove_banco1) && $item->id_prove_banco1 == $banco->id)
                                $ {{ number_format($item->prove_monto1, 0, '.', ',') }}
                            @else
                                $ {{ number_format($item->prove_monto2, 0, '.', ',') }}
                            @endif
                        </td>
                    </tr>
                @elseif(isset($item->fecha))
                    <tr>
                        <td>{{ \Carbon\Carbon::parse($item->fecha)->translatedFormat('j \d\e F') }}</td>
                        <td>{{ $item->motivo }}</td>
                        <td></td>
                        <td class="ultima-columna"> $ {{ number_format($item->monto1, 0, '.', ',') }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
        <tfoot>
            <tr>
            <th></th>
            <th>SubTotal</th>
            <td>$ {{ number_format($penultimaTotal, 0, '.', ',') }}</td>
            <td>$ {{ number_format($ultimaTotal, 0, '.', ',') }}</td>
            </tr>
            <tr>
                <th></th>
                <th></th>
                <th>Total</th>
                <td>$ {{ number_format($diferencia, 0, '.', ',') }}</td>
            </tr>
        </tfoot>
        </table>

    </body>
</html>
