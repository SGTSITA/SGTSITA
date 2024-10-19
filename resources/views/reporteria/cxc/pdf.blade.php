<!DOCTYPE html>
<html>
    @if(!isset($isExcel))
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
    @endif
    <head>
        <title>Cuentas por coborar</title>
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
                    <h4 class="margin_cero">Estado de cuenta</h4>
                    <h4 class="margin_cero">Cliente: {{ $cotizacion->Cliente->nombre }}</h4>
                </div>

                <div class="contianer" style="position: relative">
                    <h5 style="position: absolute;left:80%;top:-5%;">Cuentas por Cobrar : {{ date("d-m-Y") }}</h5><br>
                </div>

                <table class="table text-white tabla-completa"  style="color: #000;width: 100%;padding: 10px; margin: 0px; font-size: 12px">
                    <thead>
                        <tr>
                            <th>Fecha inicio</th>
                            <th>Contratista</th>
                            <th>Contenedor</th>
                            <th>Facturado a</th>
                            <th>Destino</th>
                            <th>Peso</th>
                            <th>Tamaño contenedor</th>
                            <th>Burreo</th>
                            <th>Estadia</th>
                            <th>Sobre peso</th>
                            <th>Otro</th>
                            <th>Precio venta</th>
                            <th>Precio viaje</th>

                            <th style="color: #000000; border-radius:3px; background: #56d1f7;">Base factura</th>
                            <th style="color: #000000; border-radius:3px; background: #56d1f7;">IVA</th>
                            <th style="color: #000000; border-radius:3px; background: #56d1f7;">Retención</th>
                            <th style="color: #000000; border-radius:3px; background: #2dce89;">Base taref</th>
                            <th style="color: #000000; border-radius:3px; background: yellow;">Total oficial</th>
                            <th style="color: #000000; border-radius:3px; background: #fb6340;">Total no oficial</th>
                            <th>Importe VTA</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center;font-size: 100%;">
                        @foreach ($cotizaciones as $cotizacion)
                            @php
                                // Convertir valores a numéricos para evitar el error
                                $base_factura = floatval($cotizacion->base_factura ?? 0);
                                $iva = floatval($cotizacion->iva ?? 0);
                                $retencion = floatval($cotizacion->retencion ?? 0);
                                $total = floatval($cotizacion->total ?? 0);

                                // Calcular total oficial y base taref
                                $total_oficial = ($base_factura + $iva) - $retencion;
                                $base_taref = $total - $base_factura - $iva + $retencion;

                                // Calcular importe de venta
                                $importe_vta = $base_taref + $total_oficial;

                                // Sumar los valores a las variables acumulativas
                                $totalOficialSum += $total_oficial;
                                $totalnoofi += $base_taref;
                                $importeVtaSum += $importe_vta;
                            @endphp
                            <tr>
                                <td>
                                    {{ Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fehca_inicio_guard)->format('d-m-Y') }}
                                </td>
                                @if (optional($cotizacion->DocCotizacion->Asignaciones)->id_proveedor == NULL)
                                    <td>-</td>
                                @else
                                    <td>{{ optional($cotizacion->DocCotizacion->Asignaciones->Proveedor)->nombre }}</td>
                                @endif
                                <td>{{ $cotizacion->DocCotizacion->num_contenedor }}</td>
                                <td style="color: #020202; background: yellow;">
                                    @if ($cotizacion->id_subcliente != NULL)
                                    {{ $cotizacion->Subcliente->nombre }}
                                    @else

                                    @endif
                                </td>
                                <td style="color: #ffffff; background: #2778c4;">{{$cotizacion->destino}}</td>
                                <td>{{$cotizacion->peso_contenedor}}</td>
                                <td>{{$cotizacion->tamano}}</td>
                                <td>$ {{ number_format($cotizacion->burreo, 2, '.', ',')}}</td>
                                <td>$ {{ number_format($cotizacion->maniobra, 2, '.', ',')}}</td>
                                <td>$ {{ number_format($cotizacion->sobrepeso, 2, '.', ',')}}</td>
                                <td>$ {{ number_format($cotizacion->otro, 2, '.', ',')}}</td>
                                <td>$ {{ number_format($cotizacion->precio, 2, '.', ',')}}</td>
                                <td>$ {{ number_format($cotizacion->precio_viaje, 2, '.', ',')}}</td>

                                <td>$ {{ number_format(floatval($cotizacion->base_factura ?? 0), 2, '.', ',') }}</td>
                                <td>$ {{ number_format($cotizacion->iva, 2, '.', ',')}}</td>
                                <td>$ {{ number_format($cotizacion->retencion, 2, '.', ',')}}</td>
                                <td>$ {{ number_format($cotizacion->base_taref, 2, '.', ',')}}</td>
                                <td>
                                    @php
                                        $total_oficial = ($base_factura + $iva) - $retencion;
                                    @endphp
                                    $ {{ number_format($total_oficial, 2, '.', ',')}}
                                </td>
                                <td>
                                    @php
                                        $total_no_ofi = $cotizacion->total - $base_factura - $iva + $retencion;
                                    @endphp
                                    $ {{ number_format($total_no_ofi, 2, '.', ',')}}</td>
                                <td>
                                    @php
                                        $importe_vta2 = $total_oficial + $total_no_ofi;
                                    @endphp
                                    $ {{ number_format($importe_vta2, 2, '.', ',')}}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                @php
                    // Recopila los IDs de los proveedores únicos de las cotizaciones, excluyendo NULL
                    $proveedoresIds = $cotizaciones->pluck('DocCotizacion.Asignaciones.id_proveedor')->filter()->unique();
                    // Carga los proveedores con sus cuentas bancarias usando los IDs recopilados
                    $proveedoresConCuentas = App\Models\Proveedor::whereIn('id', $proveedoresIds)
                                            ->with('CuentasBancarias')
                                            ->get();

                    $cotizacionesPorProveedor = $cotizaciones->groupBy('DocCotizacion.Asignaciones.id_proveedor');
                @endphp

                <table class="table text-white tabla-completa sin_margem" style="color: #000;width: 100%;padding: 0px; font-size: 12px; border-collapse: collapse;">
                    <tbody style="text-align: left;font-size: 100%;">
                        <tr>
                            @foreach ($proveedoresConCuentas as $proveedor)
                                <td style="padding: 0; margin: 0; border: none;display:inline-block;">
                                    <h3 style="margin: 10px; padding: 10px;">Proveedor: {{ $proveedor->nombre }}</h3>
                                    @if ($proveedor->CuentasBancarias->isEmpty())
                                    <p style="margin: 0; padding: 0;">No hay cuentas bancarias registradas para este proveedor.</p>
                                    @else
                                        <table style="width: 100%; font-size: 12px; margin-bottom: 0; border-collapse: collapse;">
                                            @php
                                                $contador = 1;
                                                $totalCuenta1 = 0;
                                                $totalCuenta2 = 0;
                                                if (isset($cotizacionesPorProveedor[$proveedor->id])) {
                                                    $cotizacionesProveedor = $cotizacionesPorProveedor[$proveedor->id];
                                                    foreach ($cotizacionesProveedor as $cotizacion) {
                                                        $cuenta_1 = $cotizacion->base_factura + $cotizacion->iva - $cotizacion->retencion;
                                                        $cuenta_2 = $cotizacion->total - $cotizacion->base_factura - $cotizacion->iva + $cotizacion->retencion;
                                                        $totalCuenta1 += $cuenta_1;
                                                        $totalCuenta2 += $cuenta_2;
                                                    }
                                                }
                                            @endphp
                                            <tr>
                                                @foreach ($proveedor->CuentasBancarias as $cuenta)
                                                    <td style="padding: 0 5px; margin: 0; border: none;display:inline-block;">
                                                        Cuenta #{{ $contador }}<br>
                                                        Beneficiario: <br> <b>{{ $cuenta->nombre_beneficiario }}</b><br>
                                                        Banco: <b>{{ $cuenta->nombre_banco }}</b><br>
                                                        Cuenta: <b>{{ $cuenta->cuenta_bancaria }}</b><br>
                                                        Clave: <b>{{ $cuenta->cuenta_clabe }}</b><br>
                                                        @if ($contador == 1)
                                                            A pagar: <b>${{ number_format($totalCuenta1, 2, '.', ',') }}</b>
                                                        @elseif ($contador == 2)
                                                            A pagar: <b>${{ number_format($totalCuenta2, 2, '.', ',') }}</b>
                                                        @endif
                                                    </td>
                                                    @php
                                                        $contador++;
                                                    @endphp
                                                @endforeach
                                            </tr>
                                        </table>
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                </table>



        <div class="totales">
            <h3 class="margin_cero" style="color: #000000; background: rgb(0, 174, 255);">Totales</h3>
            <p class="margin_cero">Total oficial: <b class="margin_cero"> ${{ number_format($totalOficialSum, 2, '.', ',') }} </b></p>
            <p class="margin_cero">Total no oficial: <b class="margin_cero"> ${{ number_format($totalnoofi, 2, '.', ',') }} </b></p>
            <p class="margin_cero">Importe vta: <b class="margin_cero"> ${{ number_format($importeVtaSum, 2, '.', ',') }} </b></p>
        </div>
    </body>
</html>
