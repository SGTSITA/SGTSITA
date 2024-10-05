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

        .sin_margem{
            margin: 0;
            padding: 0;
        }

        .sin_espacios{
            margin: 0;
            padding: 0;
            font-size: 15px;
        }

        .sin_espacios2{
            margin: 2px;
            padding: 0;
            font-size: 10px;
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
        <title>Cuentas por pagar</title>
    </head>

    <body>
        @php
            $importeCT = 0;
            $pagar1 = 0;
            $pagar2 = 0;
        @endphp

            <div class="contianer sin_margem" style="margin: -40px;">
                <h4 class="sin_espacios2">Empresa: {{ $user->Empresa->nombre }}</h4>
                <h4 class="sin_espacios2">Estado de cuenta</h4>
                <h4 class="sin_espacios2">Proveedor: {{ $cotizacion->Proveedor->nombre }}</h4>
            </div>

            <div class="contianer sin_margem" style="position: relative">
                <h5 style="position: absolute;left:75%;top:-5%;">Estado de cuenta por pagar : {{ date("d-m-Y") }}</h5><br>
            </div>

            <table class="table text-white tabla-completa" style="color: #000;width: 100%;padding: 5px; font-size: 12px">
                <thead>
                    <tr>
                        <th>Contratista</th>
                        <th>Contenedor</th>
                        <th>Importe CT</th>
                        <th>A pagar 1</th>
                        <th>A pagar 2</th>
                        <th>Retención</th>
                        <th>IVA</th>
                        <th>Base factura</th>
                        <th>Precio viaje</th>
                        <th>Otro</th>
                        {{-- <th>Sobre peso</th> --}}
                        <th>Estadia</th>
                        <th>Burreo</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;font-size: 100%;">
                        @php
                            $totalBaseFactura = 0;
                            $totalImporteVTA = 0;
                            $base_factura = 0;
                        @endphp

                    @foreach ($cotizaciones as $item)
                        @php
                            $total_oficial = ($item->base1_proveedor + $item->iva) - $item->retencion;
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
                            <td>${{ number_format($item->retencion, 2, '.', ',') }}</td>
                            <td>${{ number_format($item->iva, 2, '.', ',') }}</td>
                            <td>${{ number_format($item->base1_proveedor, 2, '.', ',') }}</td>
                            <td>${{ number_format($item->precio, 2, '.', ',') }}</td>
                            <td>${{ number_format($item->otro, 2, '.', ',') }}</td>
                            {{-- <td>${{ number_format($item->Contenedor->item->precio_tonelada, 2, '.', ',') }}</td> --}}
                            <td>${{ number_format($item->estadia, 2, '.', ',') }}</td>
                            <td>${{ number_format($item->burreo, 2, '.', ',') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h3 class="sin_margem" style="color: #fff; background: rgb(24, 192, 141);">Contratista</h3>
            <table class="table text-white tabla-completa sin_margem" style="color: #000;width: 100%;padding: 5px; font-size: 12px;margin-top:5px">
                <tbody style="text-align: left;font-size: 100%;">
                    @php
                        $contador = 1;
                    @endphp
                    <tr>
                        @foreach ($cotizacion->Proveedor->CuentasBancarias as $cuentas)
                            <td style="padding: 0; margin: 0; border: none;display:inline-block;">
                                Cuenta #{{ $contador }}
                                Beneficiario: <br> <b> {{ $cuentas->nombre_beneficiario }} </b><br>
                                Banco: <b> {{ $cuentas->nombre_banco }} </b><br>
                                Cuenta: <b> {{ $cuentas->cuenta_bancaria }}</b><br>
                                <p>Clave: <b> {{ $cuentas->cuenta_clabe }}</b></p>
                                @if ($contador == 1)
                                    <h4 class="sin_espacios2">A pagar: ${{ number_format($pagar1, 2, '.', ',') }}<b></b></h4>
                                @endif
                                @if ($contador == 2)
                                    <h4 class="sin_espacios2">A pagar: ${{ number_format($pagar2, 2, '.', ',') }}<b></b></h4>
                                @endif
                                @php
                                    $contador++;
                                @endphp
                            </td>
                        @endforeach
                    </tr>
                </tbody>
            </table>

        <div class="totales">
            <h3 class="sin_margem" style="color: #000000; background: rgb(0, 174, 255);">Totales</h3>
            <h4 class="sin_espacios2">A pagar oficial: ${{ number_format($pagar1, 2, '.', ',') }} </h4>
            <h4 class="sin_espacios2">A pagar no oficial: ${{ number_format($pagar2, 2, '.', ',') }}</h4>
            <h4 class="sin_espacios2">Importe CT:  ${{ number_format($importeCT, 2, '.', ',') }}</h4>
        </div>
    </body>
</html>
