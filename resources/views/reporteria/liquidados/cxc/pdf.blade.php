<!DOCTYPE html>
<html>
    @if (! isset($isExcel))
        <style>
            .registro-contenedor {
                border: 2px solid #000;
                margin-bottom: 20px;
                padding: 15px;
                border-radius: 5px;
            }

            .registro-contenedor table {
                margin-bottom: 10px;
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
            .margin_cero {
                padding: 0;
                margin: 0;
                font-size: 15px;
            }
            .sin_espacios2 {
                margin: 2px;
                padding: 0;
                font-size: 10px;
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
        <title>Liquidados CxC</title>
    </head>

    <body>
        @php
            $cotizacionesGrouped = $cotizaciones->groupBy(function($cotizacion) {
                return $cotizacion->estadoCuenta?->numero ?? '-';
            });

            $grandOficialSum = 0;
            $grandNoOfiSum = 0;
            $grandImporteVtaSum = 0;
            $grandAbonoSum = 0;
            $grandContratistas = [];
            $grandTotalContenedores = 0;
        @endphp

        @foreach($cotizacionesGrouped as $numEdoCuenta => $items)
            @php
                $subTotalOficial = 0;
                $subTotalNoOfi = 0;
                $subImporteVta = 0;
                $subAbono = 0;
                $subContratistas = [];
            @endphp

            <div class="proveedor-block">
                <div class="sin_margem" style="margin-bottom: 12px; position: relative; clear: both;">
                    <h4 class="sin_espacios2">Empresa: {{ $user?->Empresa?->nombre ?? '-' }}</h4>
                    <h4 class="sin_espacios2">Liquidados CxC</h4>
                    <h4 class="sin_espacios2">Estado de cuenta: {{ $numEdoCuenta }}</h4>
                    <h4 class="sin_espacios2">Cliente: {{ $cotizacion_first?->Cliente?->nombre ?? 'Todos' }}</h4>
                    <h5 style="position: absolute; right: 0; top: 0; margin: 0;">Liquidados Cuentas por Cobrar : {{ date('d-m-Y') }}</h5>
                </div>

                <table class="table text-white tabla-completa" style="color: #000; width: 100%; font-size: 11px; border-collapse: collapse;" border="1">
                    <thead>
                        <tr style="background-color: #f2f2f2;">
                            <th style="border: 1px solid #000; padding: 5px;">Contratista</th>
                            <th style="border: 1px solid #000; padding: 5px;">Contenedor</th>
                            <th style="border: 1px solid #000; padding: 5px;">Importe VTA</th>
                            <th style="border: 1px solid #000; padding: 5px; color: #000000; background: yellow;">Total oficial</th>
                            <th style="border: 1px solid #000; padding: 5px; color: #000000; background: #fb6340;">Total no oficial</th>
                            <th style="border: 1px solid #000; padding: 5px;">Fecha de planeación</th>
                            <th style="border: 1px solid #000; padding: 5px;">Forma de Pago</th>
                            <th style="border: 1px solid #000; padding: 5px;">Abono</th>
                            <th style="border: 1px solid #000; padding: 5px;">Fecha de pago</th>
                        </tr>
                    </thead>
                    <tbody style="text-align: center; font-size: 100%">
                        @foreach ($items as $cotizacionItem)
                            @php
                                $total_oficial = (float)$cotizacionItem->base_factura + (float)$cotizacionItem->iva - (float)$cotizacionItem->retencion;
                                $total_no_ofi = (float)$cotizacionItem->total - (float)$cotizacionItem->base_factura - (float)$cotizacionItem->iva + (float)$cotizacionItem->retencion;
                                $importe_vta = $total_oficial + $total_no_ofi;

                                $abonoItem = 0;
                                if(isset($cotizacionItem->cobros) && $cotizacionItem->cobros->count() > 0) {
                                    $abonoItem = (float)$cotizacionItem->cobros->sum('monto');
                                } else {
                                    $foundReg = false;
                                    foreach ($registrosBanco as $registro) {
                                        $contenedores = json_decode($registro->contenedores, true);
                                        $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $cotizacionItem->DocCotizacion?->num_contenedor);
                                        if ($contenedorEncontrado && isset($contenedorEncontrado['abono'])) {
                                            $abonoItem += (float)$contenedorEncontrado['abono'];
                                            $foundReg = true;
                                        }
                                    }
                                    if (!$foundReg) {
                                        $abonoItem = (float)($cotizacionItem->monto1 ?? 0) + (float)($cotizacionItem->monto2 ?? 0);
                                    }
                                }

                                $subTotalOficial += $total_oficial;
                                $subTotalNoOfi += $total_no_ofi;
                                $subImporteVta += $importe_vta;
                                $subAbono += $abonoItem;

                                $grandOficialSum += $total_oficial;
                                $grandNoOfiSum += $total_no_ofi;
                                $grandImporteVtaSum += $importe_vta;
                                $grandAbonoSum += $abonoItem;
                                $grandTotalContenedores++;

                                $contratistaNombre = optional($cotizacionItem->DocCotizacion?->Asignaciones?->Proveedor)->nombre;
                                if (!empty($contratistaNombre) && trim($contratistaNombre) !== '-') {
                                    $subContratistas[] = trim($contratistaNombre);
                                    $grandContratistas[] = trim($contratistaNombre);
                                }
                            @endphp

                            <tr>
                                <td style="border: 1px solid #000;">
                                    {{ optional($cotizacionItem->DocCotizacion?->Asignaciones?->Proveedor)->nombre ?? '-' }}
                                </td>
                                <td style="border: 1px solid #000;">{{ $cotizacionItem->DocCotizacion?->num_contenedor ?? '-' }}</td>
                                <td style="border: 1px solid #000;">
                                    $ {{ number_format($importe_vta, 2, '.', ',') }}
                                </td>
                                <td style="border: 1px solid #000;">
                                    $ {{ number_format($total_oficial, 2, '.', ',') }}
                                </td>
                                <td style="border: 1px solid #000;">
                                    $ {{ number_format($total_no_ofi, 2, '.', ',') }}
                                </td>
                                <td style="border: 1px solid #000;">
                                    @php
                                        $asig = $cotizacionItem->DocCotizacion?->Asignaciones;
                                        $fIni = $asig?->fecha_inicio ?? $asig?->fehca_inicio_guard;
                                        $fFin = $asig?->fecha_fin ?? $asig?->fehca_fin_guard;
                                    @endphp
                                    @if($fIni)
                                        {{ \Carbon\Carbon::parse($fIni)->format('d/m/Y') }}
                                        @if($fFin && $fFin != $fIni)
                                            a {{ \Carbon\Carbon::parse($fFin)->format('d/m/Y') }}
                                        @endif
                                    @else
                                        -
                                    @endif
                                </td>
                                <td style="border: 1px solid #000;">
                                    @if(isset($cotizacionItem->cobros) && $cotizacionItem->cobros->count() > 0)
                                        @foreach($cotizacionItem->cobros as $cobroDetalle)
                                            @php
                                                $bancoNombre = $cobroDetalle->origen == 'B'
                                                    ? $cobroDetalle->cobroPago?->bancoB?->nombre
                                                    : $cobroDetalle->cobroPago?->bancoA?->nombre;
                                            @endphp
                                            Transferencia {{ $bancoNombre ? '('.$bancoNombre.')' : '' }} <br />
                                        @endforeach
                                    @else
                                        @php $foundMetodo = false; @endphp
                                        @foreach ($registrosBanco as $registro)
                                            @php
                                                $contenedores = json_decode($registro->contenedores, true);
                                                $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $cotizacionItem->DocCotizacion?->num_contenedor);
                                            @endphp

                                            @if ($contenedorEncontrado)
                                                @php $foundMetodo = true; @endphp
                                                {{ $registro->metodo_pago1 ?: 'Transferencia' }}
                                                <br />
                                            @endif
                                        @endforeach

                                        @if(!$foundMetodo)
                                            {{ $cotizacionItem->metodo_pago1 ?: 'Transferencia' }}
                                            @if($cotizacionItem->metodo_pago2)
                                                <br />{{ $cotizacionItem->metodo_pago2 }}
                                            @endif
                                        @endif
                                    @endif
                                </td>
                                <td style="border: 1px solid #000;">
                                    @if(isset($cotizacionItem->cobros) && $cotizacionItem->cobros->count() > 0)
                                        @foreach($cotizacionItem->cobros as $cobroDetalle)
                                            $ {{ number_format($cobroDetalle->monto, 2, '.', ',') }} <br />
                                        @endforeach
                                    @else
                                        @php $hasAbonoPrint = false; @endphp
                                        @foreach ($registrosBanco as $registro)
                                            @php
                                                $contenedores = json_decode($registro->contenedores, true);
                                                $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $cotizacionItem->DocCotizacion?->num_contenedor);
                                            @endphp

                                            @if ($contenedorEncontrado)
                                                @php $hasAbonoPrint = true; @endphp
                                                $ {{ number_format($contenedorEncontrado['abono'], 2, '.', ',') }}
                                                <br />
                                            @endif
                                        @endforeach

                                        @if(!$hasAbonoPrint)
                                            @if($cotizacionItem->monto1)
                                                $ {{ number_format($cotizacionItem->monto1, 2, '.', ',') }} <br />
                                            @endif
                                            @if($cotizacionItem->monto2)
                                                $ {{ number_format($cotizacionItem->monto2, 2, '.', ',') }}
                                            @endif
                                            @if(!$cotizacionItem->monto1 && !$cotizacionItem->monto2)
                                                $ 0.00
                                            @endif
                                        @endif
                                    @endif
                                </td>
                                <td style="border: 1px solid #000;">
                                    @if(isset($cotizacionItem->cobros) && $cotizacionItem->cobros->count() > 0)
                                        @foreach($cotizacionItem->cobros as $cobroDetalle)
                                            @php
                                                $fechaApp = $cobroDetalle->origen == 'B'
                                                    ? ($cobroDetalle->cobroPago?->fechaAplicacion2 ?? $cobroDetalle->cobroPago?->created_at)
                                                    : ($cobroDetalle->cobroPago?->fechaAplicacion1 ?? $cobroDetalle->cobroPago?->created_at);
                                            @endphp
                                            {{ $fechaApp ? \Carbon\Carbon::parse($fechaApp)->format('d/m/Y') : '-' }} <br />
                                        @endforeach
                                    @else
                                        @foreach ($registrosBanco as $registro)
                                            @php
                                                $contenedores = json_decode($registro->contenedores, true);
                                                $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $cotizacionItem->DocCotizacion?->num_contenedor);
                                            @endphp

                                            @if ($contenedorEncontrado)
                                                {{ $registro->fecha_pago }}
                                                <br />
                                            @endif
                                        @endforeach

                                        {{ $cotizacionItem->fecha_pago }}
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot style="text-align: center; font-size: 11px; font-weight: bold;">
                        <tr style="background-color: #f2f2f2;">
                            <td style="border: 1px solid #000; padding: 5px;">
                                Contratistas: {{ count(array_unique($subContratistas)) }}
                            </td>
                            <td style="border: 1px solid #000; padding: 5px;">
                                Contenedores: {{ $items->count() }}
                            </td>
                            <td style="border: 1px solid #000; padding: 5px;">
                                $ {{ number_format($subImporteVta, 2, '.', ',') }}
                            </td>
                            <td style="border: 1px solid #000; background-color: yellow; color: #000; padding: 5px;">
                                $ {{ number_format($subTotalOficial, 2, '.', ',') }}
                            </td>
                            <td style="border: 1px solid #000; background-color: #fb6340; color: #000; padding: 5px;">
                                $ {{ number_format($subTotalNoOfi, 2, '.', ',') }}
                            </td>
                            <td style="border: 1px solid #000;"></td>
                            <td style="border: 1px solid #000;"></td>
                            <td style="border: 1px solid #000; padding: 5px;">
                                $ {{ number_format($subAbono, 2, '.', ',') }}
                            </td>
                            <td style="border: 1px solid #000;"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        @endforeach

        @if($cotizacionesGrouped->count() > 0)
            <div style="margin-top: 25px; page-break-inside: avoid;">
                <h4 style="margin-bottom: 8px; font-size: 13px; font-weight: bold; text-align: center;">RESUMEN GRAN TOTAL GENERAL</h4>
                <table class="table tabla-completa" style="color: #000; width: 100%; padding: 5px; font-size: 12px; border-collapse: collapse; text-align: center; font-weight: bold;" border="1">
                    <thead>
                        <tr style="background-color: #344767; color: #ffffff;">
                            <th style="border: 1px solid #000; padding: 6px; color: #ffffff;">Contratistas Distintos</th>
                            <th style="border: 1px solid #000; padding: 6px; color: #ffffff;">Total Contenedores</th>
                            <th style="border: 1px solid #000; padding: 6px; color: #ffffff;">Total Importe VTA</th>
                            <th style="border: 1px solid #000; padding: 6px; background: yellow; color: #000;">Total Oficial</th>
                            <th style="border: 1px solid #000; padding: 6px; background: #fb6340; color: #000;">Total No Oficial</th>
                            <th style="border: 1px solid #000; padding: 6px; color: #ffffff;">Total Abonos</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr style="background-color: #e9ecef;">
                            <td style="border: 1px solid #000; padding: 8px;">{{ count(array_unique($grandContratistas)) }}</td>
                            <td style="border: 1px solid #000; padding: 8px;">{{ $grandTotalContenedores }}</td>
                            <td style="border: 1px solid #000; padding: 8px;">$ {{ number_format($grandImporteVtaSum, 2, '.', ',') }}</td>
                            <td style="border: 1px solid #000; padding: 8px; background-color: yellow; color: #000;">$ {{ number_format($grandOficialSum, 2, '.', ',') }}</td>
                            <td style="border: 1px solid #000; padding: 8px; background-color: #fb6340; color: #000;">$ {{ number_format($grandNoOfiSum, 2, '.', ',') }}</td>
                            <td style="border: 1px solid #000; padding: 8px;">$ {{ number_format($grandAbonoSum, 2, '.', ',') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif
    </body>
</html>
