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

            .tabla-completa {
                width: 100%;
                border-collapse: collapse;
                margin: 0px;
                font-size: 12px;
                color: #000;
            }
            .tabla-completa th, .tabla-completa td {
                border: 1px solid #000;
                padding: 5px;
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
            .proveedor-block {
                page-break-inside: avoid;
                margin-bottom: 25px;
                clear: both;
            }
        </style>
    @endif

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Cuentas por pagar</title>
    </head>

    <body>
        @php
            $cotizacionesGrouped = $cotizaciones->groupBy(function($item) use ($cotizacion) {
                return $item->Proveedor?->nombre ?? ($cotizacion?->Proveedor?->nombre ?? 'Sin Proveedor');
            });

            $grandImporteCT = 0;
            $grandPagar1 = 0;
            $grandPagar2 = 0;
            $grandContratistas = [];
            $grandTotalContenedores = 0;
        @endphp

        @foreach($cotizacionesGrouped as $nombreProveedor => $items)
            @php
                $subImporteCT = 0;
                $subPagar1 = 0;
                $subPagar2 = 0;
            @endphp

            <div class="proveedor-block">
                <div class="sin_margem" style="margin-bottom: 12px; position: relative; clear: both;">
                    <h4 class="sin_espacios2">Empresa: {{ $user?->Empresa?->nombre ?? '-' }}</h4>
                    <h4 class="sin_espacios2">Estado de cuenta</h4>
                    <h4 class="sin_espacios2">Proveedor: {{ $nombreProveedor }}</h4>
                    <h5 style="position: absolute; right: 0; top: 0; margin: 0;">Estado de cuenta por pagar : {{ date('d-m-Y') }}</h5>
                </div>

                <table class="table text-white tabla-completa" style="color: #000; width: 100%; font-size: 11px; border-collapse: collapse;" border="1">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="border: 1px solid #000; padding: 5px;">Contratista</th>
                            <th style="border: 1px solid #000; padding: 5px;">Contenedor</th>
                            <th style="border: 1px solid #000; padding: 5px;">Importe CT</th>
                            <th style="border: 1px solid #000; padding: 5px; color: #000000; background: yellow;">A pagar 1</th>
                            <th style="border: 1px solid #000; padding: 5px; color: #000000; background: #fb6340;">A pagar 2</th>
                            <th style="border: 1px solid #000; padding: 5px;">Forma de Pago</th>
                            <th style="border: 1px solid #000; padding: 5px;">Abono</th>
                            <th style="border: 1px solid #000; padding: 5px;">Fecha de planeación</th>
                            <th style="border: 1px solid #000; padding: 5px;">Fecha de pago</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center; font-size: 100%">
                        @foreach ($items as $item)
                            @php
                                $total_oficial = $item->base1_proveedor + $item->iva - $item->retencion;
                                $base_factura = $item->total_proveedor - $item->base1_proveedor - $item->iva + $item->retencion;

                                $importe_vta = $base_factura - $total_oficial;
                                $suma_importeCT = $base_factura + $total_oficial;

                                $subImporteCT += $suma_importeCT;
                                $subPagar1 += $total_oficial;
                                $subPagar2 += $base_factura;

                                $grandImporteCT += $suma_importeCT;
                                $grandPagar1 += $total_oficial;
                                $grandPagar2 += $base_factura;
                                $grandTotalContenedores++;

                                if (!empty($nombreProveedor) && trim($nombreProveedor) !== '-') {
                                    $grandContratistas[] = trim($nombreProveedor);
                                }
                            @endphp

                            <tr>
                                <td style="border: 1px solid #000;">{{ $item->Proveedor?->nombre ?? $nombreProveedor }}</td>
                                <td style="border: 1px solid #000;">{{ $item->Contenedor?->num_contenedor ?? '-' }}</td>
                                <td style="border: 1px solid #000;">${{ number_format($suma_importeCT, 2, '.', ',') }}</td>
                                <td style="border: 1px solid #000;">${{ number_format($total_oficial, 2, '.', ',') }}</td>
                                <td style="border: 1px solid #000;">${{ number_format($base_factura, 2, '.', ',') }}</td>
                                <td style="border: 1px solid #000;">
                                    @php
                                        $cotiPadre = $item->Contenedor?->Cotizacion;
                                    @endphp
                                    @if(isset($cotiPadre->pagos) && $cotiPadre->pagos->count() > 0)
                                        @foreach($cotiPadre->pagos as $pagoDetalle)
                                            @php
                                                $bancoNombre = $pagoDetalle->origen == 'B'
                                                    ? ($pagoDetalle->cobroPago?->bancoB?->nombre ?? $pagoDetalle->cobroPago?->bancoProveedorB?->nombre)
                                                    : ($pagoDetalle->cobroPago?->bancoA?->nombre ?? $pagoDetalle->cobroPago?->bancoProveedorA?->nombre);
                                            @endphp
                                            Transferencia {{ $bancoNombre ? '('.$bancoNombre.')' : '' }} <br />
                                        @endforeach
                                    @else
                                        @php $foundMetodo = false; @endphp
                                        @foreach ($registrosBanco as $registro)
                                            @php
                                                $contenedores = json_decode($registro->contenedores, true);
                                                $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $item->Contenedor?->num_contenedor);
                                            @endphp

                                            @if ($contenedorEncontrado)
                                                @php $foundMetodo = true; @endphp
                                                {{ $registro->metodo_pago1 ?: 'Transferencia' }}
                                                <br />
                                            @endif
                                        @endforeach

                                        @if(!$foundMetodo)
                                            {{ $item->Contenedor?->Cotizacion?->prove_metodo_pago1 ?: 'Transferencia' }}
                                            @if($item->Contenedor?->Cotizacion?->prove_metodo_pago2)
                                                <br />{{ $item->Contenedor?->Cotizacion?->prove_metodo_pago2 }}
                                            @endif
                                        @endif
                                    @endif
                                </td>
                                <td style="border: 1px solid #000;">
                                    @if(isset($cotiPadre->pagos) && $cotiPadre->pagos->count() > 0)
                                        @foreach($cotiPadre->pagos as $pagoDetalle)
                                            $ {{ number_format($pagoDetalle->monto, 2, '.', ',') }} <br />
                                        @endforeach
                                    @else
                                        @foreach ($registrosBanco as $registro)
                                            @php
                                                $contenedores = json_decode($registro->contenedores, true);
                                                $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $item->Contenedor?->num_contenedor);
                                            @endphp

                                            @if ($contenedorEncontrado)
                                                $ {{ number_format($contenedorEncontrado['abono'], 2, '.', ',') }}
                                                <br />
                                            @endif
                                        @endforeach

                                        {{ $item->Contenedor?->Cotizacion?->prove_monto1 }}
                                        <br />
                                        {{ $item->Contenedor?->Cotizacion?->prove_monto2 }}
                                    @endif
                                </td>
                                <td style="border: 1px solid #000;">
                                    @php
                                        $fIni = $item->fecha_inicio ?? $item->fehca_inicio_guard;
                                        $fFin = $item->fecha_fin ?? $item->fehca_fin_guard;
                                    @endphp
                                    @if($fIni)
                                        {{ \Carbon\Carbon::parse($fIni)->format('d/m/Y') }}
                                        @if($fFin && $fFin != $fIni)
                                            <br />{{ \Carbon\Carbon::parse($fFin)->format('d/m/Y') }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="border: 1px solid #000;">
                                    @if(isset($cotiPadre->pagos) && $cotiPadre->pagos->count() > 0)
                                        @foreach($cotiPadre->pagos as $pagoDetalle)
                                            @php
                                                $fechaApp = $pagoDetalle->origen == 'B'
                                                    ? ($pagoDetalle->cobroPago?->fechaAplicacion2 ?? $pagoDetalle->cobroPago?->created_at)
                                                    : ($pagoDetalle->cobroPago?->fechaAplicacion1 ?? $pagoDetalle->cobroPago?->created_at);
                                            @endphp
                                            {{ $fechaApp ? \Carbon\Carbon::parse($fechaApp)->format('d/m/Y') : '-' }} <br />
                                        @endforeach
                                    @else
                                        @foreach ($registrosBanco as $registro)
                                            @php
                                                $contenedores = json_decode($registro->contenedores, true);
                                                $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $item->Contenedor?->num_contenedor);
                                            @endphp

                                            @if ($contenedorEncontrado)
                                                {{ $registro->fecha_pago }}
                                                <br />
                                            @endif
                                        @endforeach

                                        {{ $item->Contenedor?->Cotizacion?->fecha_pago_proveedor }}
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="text-align: center; font-size: 11px; font-weight: bold;">
                        <tr style="background-color: #f2f2f2;">
                            <td style="border: 1px solid #000; padding: 5px;">
                                Subtotal: {{ $nombreProveedor }}
                            </td>
                            <td style="border: 1px solid #000; padding: 5px;">
                                Contenedores: {{ $items->count() }}
                            </td>
                            <td style="border: 1px solid #000; padding: 5px;">
                                $ {{ number_format($subImporteCT, 2, '.', ',') }}
                            </td>
                            <td style="border: 1px solid #000; background-color: yellow; color: #000; padding: 5px;">
                                $ {{ number_format($subPagar1, 2, '.', ',') }}
                            </td>
                            <td style="border: 1px solid #000; background-color: #fb6340; color: #000; padding: 5px;">
                                $ {{ number_format($subPagar2, 2, '.', ',') }}
                            </td>
                            <td style="border: 1px solid #000;" colspan="4"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach

        @if($cotizacionesGrouped->count() > 0)
            <div style="margin-top: 25px; page-break-inside: avoid;">
                <h4 style="margin-bottom: 8px; font-size: 13px; font-weight: bold; text-align: center;">RESUMEN GRAN TOTAL GENERAL (TODOS LOS PROVEEDORES)</h4>
                <table class="table tabla-completa" style="color: #000; width: 100%; padding: 5px; font-size: 12px; border-collapse: collapse; text-align: center; font-weight: bold;" border="1">
                    <thead>
                        <tr style="background-color: #344767; color: #ffffff;">
                            <th style="border: 1px solid #000; padding: 6px; color: #ffffff;">Proveedores Distintos</th>
                            <th style="border: 1px solid #000; padding: 6px; color: #ffffff;">Total Contenedores</th>
                            <th style="border: 1px solid #000; padding: 6px; color: #ffffff;">Total Importe CT</th>
                            <th style="border: 1px solid #000; padding: 6px; background: yellow; color: #000;">Total A pagar 1 (Oficial)</th>
                            <th style="border: 1px solid #000; padding: 6px; background: #fb6340; color: #000;">Total A pagar 2 (No Oficial)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #e9ecef;">
                            <td style="border: 1px solid #000; padding: 8px;">{{ count(array_unique($grandContratistas)) }}</td>
                            <td style="border: 1px solid #000; padding: 8px;">{{ $grandTotalContenedores }}</td>
                            <td style="border: 1px solid #000; padding: 8px;">$ {{ number_format($grandImporteCT, 2, '.', ',') }}</td>
                            <td style="border: 1px solid #000; padding: 8px; background-color: yellow; color: #000;">$ {{ number_format($grandPagar1, 2, '.', ',') }}</td>
                            <td style="border: 1px solid #000; padding: 8px; background-color: #fb6340; color: #000;">$ {{ number_format($grandPagar2, 2, '.', ',') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </body>
</html>
