<!DOCTYPE html>
<html>
    @if (! isset($isExcel))
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

            .sin_margem {
                margin: 0;
                padding: 0;
            }

            .sin_espacios {
                margin: 0;
                padding: 0;
                font-size: 15px;
            }

            .sin_espacios2 {
                margin: 2px;
                padding: 0;
                font-size: 10px;
            }

            .margin_cero {
                padding: 0;
                margin: 0;
                font-size: 15px;
            }
            .contianer {
                padding: 0;
                margin: -40px;
            }
        </style>
    @endif

    <head>
        <title>Cuentas por pagar</title>
    </head>

    <body>
        @php
            $importeCT = 0;
            $pagar1 = 0;
            $pagar2 = 0;
        @endphp

        <div class="contianer sin_margem" style="margin: -40px">
            <h4 class="sin_espacios2">Empresa: {{ $user->Empresa->nombre }}</h4>
            <h4 class="sin_espacios2">Estado de cuenta</h4>
            <h4 class="sin_espacios2">Proveedor: {{ $cotizacion->Proveedor->nombre }}</h4>
        </div>

        <div class="contianer sin_margem" style="position: relative">
            <h5 style="position: absolute; left: 75%; top: -5%">Estado de cuenta por pagar : {{ date('d-m-Y') }}</h5>
            <br />
        </div>

        <table class="table text-white tabla-completa" style="color: #000; width: 100%; padding: 5px; font-size: 12px">
            <thead>
                <tr>
                    <th>Contratista</th>
                    <th>Contenedor</th>
                    <th>Importe CT</th>
                    <th>A pagar 1</th>
                    <th>A pagar 2</th>
                    <th>Forma de Pago</th>
                    <th>Abono</th>
                    <th>Fecha de planeación</th>
                    <th>Fecha de pago</th>
                </tr>
            </thead>
            <tbody style="text-align: center; font-size: 100%">
                @php
                    $totalBaseFactura = 0;
                    $totalImporteVTA = 0;
                    $base_factura = 0;
                @endphp

                @foreach ($cotizaciones as $item)
                    @php
                        $total_oficial = $item->base1_proveedor + $item->iva - $item->retencion;
                        $base_factura = $item->total_proveedor - $item->base1_proveedor - $item->iva + $item->retencion;

                        $importe_vta = $base_factura - $total_oficial;
                        $suma_importeCT = $base_factura + $total_oficial;

                        $importeCT += $suma_importeCT;
                        $pagar1 += $total_oficial;
                        $pagar2 += $base_factura;

                        $totalBaseFactura += $base_factura;
                        $totalImporteVTA += $importe_vta;
                    @endphp

                    <tr>
                        <td>{{ $cotizacion->Proveedor->nombre }}</td>
                        <td>{{ $item->Contenedor->num_contenedor }}</td>
                        <td>${{ number_format($suma_importeCT, 2, '.', ',') }}</td>
                        <td>${{ number_format($total_oficial, 2, '.', ',') }}</td>
                        <td>${{ number_format($base_factura, 2, '.', ',') }}</td>
                        <td>
                            @foreach ($registrosBanco as $registro)
                                @php
                                    $contenedores = json_decode($registro->contenedores, true);
                                    $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $item->Contenedor->num_contenedor);
                                @endphp

                                @if ($contenedorEncontrado)
                                    {{ $registro->metodo_pago1 }}
                                    <br />
                                @endif
                            @endforeach

                            {{ $item->Contenedor->Cotizacion->prove_metodo_pago1 }}
                            <br />
                            {{ $item->Contenedor->Cotizacion->prove_metodo_pago2 }}
                        </td>
                        <td>
                            @foreach ($registrosBanco as $registro)
                                @php
                                    $contenedores = json_decode($registro->contenedores, true);
                                    $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $item->Contenedor->num_contenedor);
                                @endphp

                                @if ($contenedorEncontrado)
                                    {{ $contenedorEncontrado['abono'] }}
                                    <br />
                                @endif
                            @endforeach

                            {{ $item->Contenedor->Cotizacion->prove_monto1 }}
                            <br />
                            {{ $item->Contenedor->Cotizacion->prove_monto2 }}
                        </td>
                        <td>
                            {{ \Carbon\Carbon::parse($item->fehca_inicio_guard)->translatedFormat('d F Y') }}
                            <br />
                            {{ \Carbon\Carbon::parse($item->fehca_fin_guard)->translatedFormat('d F Y') }}
                        </td>
                        <td>
                            @foreach ($registrosBanco as $registro)
                                @php
                                    $contenedores = json_decode($registro->contenedores, true);
                                    $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $item->Contenedor->num_contenedor);
                                @endphp

                                @if ($contenedorEncontrado)
                                    {{ $registro->fecha_pago }}
                                    <br />
                                @endif
                            @endforeach

                            {{ $item->Contenedor->Cotizacion->fecha_pago_proveedor }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
