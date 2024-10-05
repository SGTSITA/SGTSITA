<!DOCTYPE html>
<html>
    <style>
        .registro-contenedor {
            border: 2px solid #000; /* Cambia el color y grosor del borde según tus necesidades */
            margin-bottom: 20px; /* Espacio entre cada registro */
            padding: 15px; /* Espacio interno alrededor de las tablas */
            border-radius: 5px; /* Bordes redondeados, opcional */
        }

        .registro-contenedor table {
            margin-bottom: 10px; /* Espacio entre tablas dentro del mismo contenedor */
        }

        .totales {
            margin-top: 20px;
        }

        .totales h3 {
            font-weight: bold;
        }

        .totales p {
            font-size: 1.2em;
            color: #000;
        }

        .margin_cero{
            padding: 0;
            margin: 0;
            font-size: 15px;
        }
        .contianer{
            padding: 0;
            margin: -40px;
        }
    </style>
    <head>
        <title>Liquidados CxC</title>
    </head>

    <body>
        @php
            $totalOficialSum = 0;
            $totalnoofi = 0;
            $importeVtaSum = 0;
            $total_no_ofi = 0;
        @endphp

                <div class="contianer" style="position: relative">
                    <h4 class="margin_cero">Empresa: {{ $user->Empresa->nombre }}</h4>
                    <h4 class="margin_cero">Liquidados CxC</h4>
                    <h4 class="margin_cero">Cliente: {{ $cotizacion_first->Cliente->nombre }}</h4>
                </div>

                <div class="contianer" style="position: relative">
                    <h5 style="position: absolute;left:80%;top:-5%;">Liquidados Cuentas por Cobrar : {{ date("d-m-Y") }}</h5><br>
                </div>

                <table class="table text-white tabla-completa"  style="color: #000;width: 100%;padding: 10px; margin: 0px; font-size: 12px">
                    <thead>
                        <tr>
                            <th>Contratista</th>
                            <th>Contenedor</th>
                            <th style="color: #000000; border-radius:3px; background: yellow;">Total oficial</th>
                            <th style="color: #000000; border-radius:3px; background: #fb6340;">Total no oficial</th>
                            <th>Importe VTA</th>
                            <th>Forma de Pago</th>
                            <th>Abono</th>
                            <th>Fecha de planeación</th>
                            <th>Fecha de pago</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center;font-size: 100%;">
                        @foreach ($cotizaciones as $cotizacion)
                            @php
                                $total_oficial = ($cotizacion->base_factura + $cotizacion->iva) - $cotizacion->retencion;
                                $base_taref = $cotizacion->total - $cotizacion->base_factura - $cotizacion->iva + $cotizacion->retencion;

                                $importe_vta = $base_taref + $total_oficial;

                                $totalOficialSum += $total_oficial;
                                $totalnoofi += $base_taref;
                                $importeVtaSum += $importe_vta;
                            @endphp
                            <tr>
                            @if (optional($cotizacion->DocCotizacion->Asignaciones)->id_proveedor == NULL)
                                <td>-</td>
                            @else
                                <td>{{ optional($cotizacion->DocCotizacion->Asignaciones->Proveedor)->nombre }}</td>
                            @endif
                                <td>{{ $cotizacion->DocCotizacion->num_contenedor }}</td>
                                <td>
                                    @php
                                        $total_oficial = ($cotizacion->base_factura + $cotizacion->iva) - $cotizacion->retencion;
                                    @endphp
                                    $ {{ number_format($total_oficial, 2, '.', ',')}}
                                </td>
                                <td>
                                    @php
                                        $total_no_ofi = $cotizacion->total - $cotizacion->base_factura - $cotizacion->iva + $cotizacion->retencion;
                                    @endphp
                                    $ {{ number_format($total_no_ofi, 2, '.', ',')}}</td>
                                <td>
                                    @php
                                        $importe_vta2 = $total_oficial + $total_no_ofi;
                                    @endphp
                                    $ {{ number_format($importe_vta2, 2, '.', ',')}}
                                </td>
                                <td>
                                    @foreach ($registrosBanco as $registro)
                                        @php
                                            $contenedores = json_decode($registro->contenedores, true);
                                            $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $cotizacion->DocCotizacion->num_contenedor);
                                        @endphp

                                        @if ($contenedorEncontrado)
                                            {{ $registro->metodo_pago1 }} <br>
                                        @endif
                                    @endforeach
                                    {{$cotizacion->metodo_pago1}}<br>
                                    {{$cotizacion->metodo_pago2}}
                                </td>
                                <td>
                                    @foreach ($registrosBanco as $registro)
                                        @php
                                            $contenedores = json_decode($registro->contenedores, true);
                                            $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $cotizacion->DocCotizacion->num_contenedor);
                                        @endphp

                                        @if ($contenedorEncontrado)
                                        $ {{ number_format($contenedorEncontrado['abono'], 2, '.', ',')}} <br>
                                        @endif
                                    @endforeach
                                    {{$cotizacion->monto1}}<br>
                                    {{$cotizacion->monto2}}
                                </td>
                                <td>{{ \Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fehca_inicio_guard)->translatedFormat('d F Y') }} <br>
                                    {{ \Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fehca_fin_guard)->translatedFormat('d F Y') }}
                                </td>
                                <td>
                                    @foreach ($registrosBanco as $registro)
                                        @php
                                            $contenedores = json_decode($registro->contenedores, true);
                                            $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $cotizacion->DocCotizacion->num_contenedor);
                                        @endphp

                                        @if ($contenedorEncontrado)
                                            {{ $registro->fecha_pago }} <br>
                                        @endif
                                    @endforeach
                                    {{$cotizacion->fecha_pago}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
    </body>
</html>
