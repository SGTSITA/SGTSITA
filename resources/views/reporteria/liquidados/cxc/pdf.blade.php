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
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <title>Liquidados CxC</title>
    </head>

    <body>
        @php
            $totalOficialSum = 0;
            $totalNoOfiSum = 0;
            $importeVtaSum = 0;
            $contratistas = [];
        @endphp

        <div class="contianer" style="position: relative; margin-bottom: 20px;">
            <h4 class="margin_cero">Empresa: {{ $user?->Empresa?->nombre ?? '-' }}</h4>
            <h4 class="margin_cero">Liquidados CxC</h4>
            <h4 class="margin_cero">Cliente: {{ $cotizacion_first?->Cliente?->nombre ?? 'Todos' }}</h4>
            <br />
        </div>

        <div class="contianer" style="position: relative">
            <h5 style="position: absolute; left: 80%; top: -5%">
                Liquidados Cuentas por Cobrar : {{ date('d-m-Y') }}
            </h5>
            <br />
        </div>
        <br />

        <table
            class="table text-white tabla-completa"
            style="color: #000; width: 100%; padding: 10px; margin: 0px; font-size: 12px; border-collapse: collapse;"
            border="1"
        >
            <thead>
                <tr style="background-color: #f2f2f2;">
                    <th style="border: 1px solid #000; padding: 6px;">Contratista</th>
                    <th style="border: 1px solid #000; padding: 6px;">Contenedor</th>
                    <th style="border: 1px solid #000; padding: 6px; color: #000000; background: yellow;">Total oficial</th>
                    <th style="border: 1px solid #000; padding: 6px; color: #000000; background: #fb6340;">Total no oficial</th>
                    <th style="border: 1px solid #000; padding: 6px;">Importe VTA</th>
                    <th style="border: 1px solid #000; padding: 6px;">Forma de Pago</th>
                    <th style="border: 1px solid #000; padding: 6px;">Abono</th>
                    <th style="border: 1px solid #000; padding: 6px;">Fecha de planeación</th>
                    <th style="border: 1px solid #000; padding: 6px;">Fecha de pago</th>
                </tr>
            </thead>
            <tbody style="text-align: center; font-size: 100%">
                @foreach ($cotizaciones as $cotizacion)
                    @php
                        $total_oficial = (float)$cotizacion->base_factura + (float)$cotizacion->iva - (float)$cotizacion->retencion;
                        $total_no_ofi = (float)$cotizacion->total - (float)$cotizacion->base_factura - (float)$cotizacion->iva + (float)$cotizacion->retencion;
                        $importe_vta = $total_oficial + $total_no_ofi;

                        $totalOficialSum += $total_oficial;
                        $totalNoOfiSum += $total_no_ofi;
                        $importeVtaSum += $importe_vta;

                        $contratistaNombre = optional($cotizacion->DocCotizacion?->Asignaciones?->Proveedor)->nombre;
                        if (!empty($contratistaNombre) && trim($contratistaNombre) !== '-') {
                            $contratistas[] = trim($contratistaNombre);
                        }
                    @endphp

                    <tr>
                        @if (optional($cotizacion->DocCotizacion?->Asignaciones)->id_proveedor == null)
                            <td style="border: 1px solid #000;">-</td>
                        @else
                            <td style="border: 1px solid #000;">{{ optional($cotizacion->DocCotizacion?->Asignaciones?->Proveedor)->nombre }}</td>
                        @endif
                        <td style="border: 1px solid #000;">{{ $cotizacion->DocCotizacion?->num_contenedor ?? '-' }}</td>
                        <td style="border: 1px solid #000;">
                            $ {{ number_format($total_oficial, 2, '.', ',') }}
                        </td>
                        <td style="border: 1px solid #000;">
                            $ {{ number_format($total_no_ofi, 2, '.', ',') }}
                        </td>
                        <td style="border: 1px solid #000;">
                            $ {{ number_format($importe_vta, 2, '.', ',') }}
                        </td>
                        <td style="border: 1px solid #000;">
                            @if(isset($cotizacion->cobros) && $cotizacion->cobros->count() > 0)
                                @foreach($cotizacion->cobros as $cobroDetalle)
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
                                        $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $cotizacion->DocCotizacion?->num_contenedor);
                                    @endphp

                                    @if ($contenedorEncontrado)
                                        @php $foundMetodo = true; @endphp
                                        {{ $registro->metodo_pago1 ?: 'Transferencia' }}
                                        <br />
                                    @endif
                                @endforeach

                                @if(!$foundMetodo)
                                    {{ $cotizacion->metodo_pago1 ?: 'Transferencia' }}
                                    @if($cotizacion->metodo_pago2)
                                        <br />{{ $cotizacion->metodo_pago2 }}
                                    @endif
                                @endif
                            @endif
                        </td>
                        <td style="border: 1px solid #000;">
                            @if(isset($cotizacion->cobros) && $cotizacion->cobros->count() > 0)
                                @foreach($cotizacion->cobros as $cobroDetalle)
                                    $ {{ number_format($cobroDetalle->monto, 2, '.', ',') }} <br />
                                @endforeach
                            @else
                                @foreach ($registrosBanco as $registro)
                                    @php
                                        $contenedores = json_decode($registro->contenedores, true);
                                        $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $cotizacion->DocCotizacion?->num_contenedor);
                                    @endphp

                                    @if ($contenedorEncontrado)
                                        $ {{ number_format($contenedorEncontrado['abono'], 2, '.', ',') }}
                                        <br />
                                    @endif
                                @endforeach

                                {{ $cotizacion->monto1 }}
                                <br />
                                {{ $cotizacion->monto2 }}
                            @endif
                        </td>
                        <td style="border: 1px solid #000;">
                            @php
                                $asig = $cotizacion->DocCotizacion?->Asignaciones;
                                $fIni = $asig?->fecha_inicio ?? $asig?->fehca_inicio_guard;
                                $fFin = $asig?->fecha_fin ?? $asig?->fehca_fin_guard;
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
                            @if(isset($cotizacion->cobros) && $cotizacion->cobros->count() > 0)
                                @foreach($cotizacion->cobros as $cobroDetalle)
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
                                        $contenedorEncontrado = collect($contenedores)->firstWhere('num_contenedor', $cotizacion->DocCotizacion?->num_contenedor);
                                    @endphp

                                    @if ($contenedorEncontrado)
                                        {{ $registro->fecha_pago }}
                                        <br />
                                    @endif
                                @endforeach

                                {{ $cotizacion->fecha_pago }}
                            @endif
                        </td>

                    </tr>
                @endforeach
            </tbody>
            <tfoot style="text-align: center; font-size: 11px; font-weight: bold;">
                <tr style="background-color: #f2f2f2;">
                    <td style="border: 1px solid #000; padding: 6px;">
                        Contratistas: {{ count(array_unique($contratistas)) }}
                    </td>
                    <td style="border: 1px solid #000; padding: 6px;">
                        Contenedores: {{ $cotizaciones->count() }}
                    </td>
                    <td style="border: 1px solid #000; background-color: yellow; color: #000; padding: 6px;">
                        $ {{ number_format($totalOficialSum, 2, '.', ',') }}
                    </td>
                    <td style="border: 1px solid #000; background-color: #fb6340; color: #000; padding: 6px;">
                        $ {{ number_format($totalNoOfiSum, 2, '.', ',') }}
                    </td>
                    <td style="border: 1px solid #000; padding: 6px;">
                        $ {{ number_format($importeVtaSum, 2, '.', ',') }}
                    </td>
                    <td style="border: 1px solid #000;" colspan="4"></td>
                </tr>
            </tfoot>
        </table>
    </body>
</html>
