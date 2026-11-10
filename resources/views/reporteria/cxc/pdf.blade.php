@php
    $user = $user ?? auth()->user();
    $cotizacion = $cotizacion ?? null;
    $cotizaciones = $cotizaciones ?? collect();
@endphp
<!DOCTYPE html>
<html>
@if (!isset($isExcel))
    <style>
        /* Configuración de la página para tamaño carta en horizontal con márgenes generales */
        @page {
            size: letter landscape;
            /* Tamaño carta en orientación horizontal */
            margin: 10mm;
            /* Márgenes generales alrededor de la página */
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            /* Tamaño de fuente reducido */
            margin: 0;
            padding: 0;
        }

        /* Eliminar el bold en todos los elementos */
        h3,
        p,
        th,
        td {
            font-weight: normal;
            /* Quitar negrita */
        }

        .registro-contenedor {
            border: 2px solid #000;
            margin-bottom: 0px;
            padding: 15px;
            border-radius: 5px;
        }

        .registro-contenedor table {
            margin-bottom: 0px;
        }

        .totales {
            margin-top: 0px;
        }

        .totales h3 {
            font-weight: normal;
            /* Quitar negrita */
        }

        .totales p {
            font-size: 1.2em;
            color: #000;
        }

        .margin_cero {
            padding: 0;
            margin: 0;
            font-size: 12px;
        }

        .contianer {
            padding: 0;
            margin: 0;
        }

        table {
            font-family: Arial, sans-serif;
            font-size: 7px;
            /* Tamaño de fuente reducido */
            width: 100%;
            border-collapse: collapse;
            margin-top: 0px;
        }

        th,
        td {
            border: 1px solid #000;
            padding: 4px;
            /* Reducir el espacio de las celdas */
            text-align: center;
            /* Centrar el texto */
        }

        .bg-primary {
            background-color: #17a2b8;
            color: white;
        }

        .bg-success {
            background-color: #28a745;
            color: white;
        }

        .bg-warning {
            background-color: #ffc107;
            color: black;
        }

        .bg-danger {
            background-color: #dc3545;
            color: white;
        }

        .tabla-completa {
            margin-top: 0px;
        }
    </style>
@endif

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Cuentas por cobrar</title>
</head>

<body>
    @php
        $totalOficialSum = 0;
        $totalnoofi = 0;
        $importeVtaSum = 0;
        $total_no_ofi = 0;
    @endphp

    @if (!isset($isExcel))
        <div class="container" style="padding: 0;">
            <h4 class="margin_cero" style="margin: 2px">
                Empresa: {{ optional($user?->Empresa)->nombre ?? optional($cotizacion?->Empresa)->nombre ?? 'SGT' }}
            </h4>

            <div style="display: flex; align-items: center; position: relative;">
                <h4 class="margin_cero" style="margin: 2px;">
                    Estado de cuenta
                </h4>

                <!-- TÍTULO CENTRADO -->
                <h2
                    style="
            position: absolute;
            left: 50%;
            top:-5px;
            transform: translateX(-50%);
            margin: 0;
            font-weight: bold;
        ">
                    {{ optional($cotizacion?->estadoCuenta)->numero ?? 'S/N' }}
                </h2>

                <div class="container" style="position: relative">
                    <h5 style="position: absolute; left: 80%; top: -5%; font-size: 10px; margin-top: 5px">
                        Cuentas por Cobrar : {{ date('d-m-Y') }}
                    </h5>
                    <br />
                </div>
            </div>

            <h4 class="margin_cero" style="margin: 2px">
                Cliente: {{ optional($cotizacion?->Cliente)->nombre ?? 'S/N' }}
            </h4>
            <br />
        </div>
    @endif
    @if (isset($isExcel))
        <table width="100%" style="border-collapse:collapse;">
            <tr>
                <td colspan="4">

                    <b>Empresa:</b> {{ optional($user?->Empresa)->nombre ?? optional($cotizacion?->Empresa)->nombre ?? 'SGT' }}
                </td>
            </tr>
            <tr>
                <td colspan="7">

                    <b> Estado de cuenta</b>

                </td>

                <td colspan="6" align="center" style="font-size:18pt; font-weight:bold;">
                    <b> {{ optional($cotizacion?->estadoCuenta)->numero ?? 'S/N' }} </b>
                </td>

                <td colspan="7" align="right">
                    <b> Cuentas por Cobrar : {{ date('d-m-Y') }} </b>
                </td>

            </tr>
            <tr>
                <td colspan="20">
                    <b> Cliente: {{ optional($cotizacion?->Cliente)->nombre ?? 'S/N' }} </b>
                </td>
            </tr>
            {{-- <tr>
                <td colspan="4">
                    <b>Empresa:</b> {{ optional($user?->Empresa)->nombre ?? optional($cotizacion?->Empresa)->nombre ?? 'SGT' }}<br>
                    Estado de cuenta<br>
                    <b>Proveedor:</b> {{ $cotizacion->Proveedor->nombre }}
                </td>
                <td colspan="4" align="center">
                    {{ optional(optional(optional($cotizacion?->Contenedor)?->Cotizacion)?->estadoCuenta)->numero ?? 'S/N' }}
                </td>
                <td colspan="4" align="right">
                    {{ date('d-m-Y') }}
                </td>
            </tr> --}}
        </table>
    @endif


    {{-- <div class="container" style="position: relative">
        <h5 style="position: absolute; left: 80%; top: -10%; font-size: 10px; margin-top: 5px">
            Cuentas por Cobrar : {{ date('d-m-Y') }}
        </h5>
        <br />
    </div> --}}

    @php
        $empresaActualModel = $user?->Empresa ?? ($cotizacion?->Empresa ?? null);
        $esEmpresaGlobal = !empty($separarUnidades);
    @endphp

    @if (!$esEmpresaGlobal)
    {{-- MODO TRADICIONAL (Configuración Global = 0): Diseño original 100% intacto --}}
    <table class="table text-white tabla-completa"
        style="
                color: #000;
                width: 100%;
                padding: 5px;
                margin: 0px;
                border-collapse: collapse;
                border: 1px solid #000;
            ">
        <thead>
            <tr style="font-size: 7px; border: 1px solid #000">
                <th style="padding: 2px; border: 1px solid #000">Fecha inicio</th>
                <th style="padding: 2px; border: 1px solid #000">Contratista</th>
                <th style="padding: 2px; border: 1px solid #000">Contenedor</th>
                <th style="padding: 2px; border: 1px solid #000">Facturado a</th>
                <th style="padding: 2px; border: 1px solid #000">Destino</th>
                <th style="padding: 2px; border: 1px solid #000">Peso</th>
                <th style="padding: 2px; border: 1px solid #000">Tam. Cont.</th>
                <th style="padding: 2px; border: 1px solid #000">Burreo</th>
                <th style="padding: 2px; border: 1px solid #000">Maniobra</th>
                <th style="padding: 2px; border: 1px solid #000">Estadia</th>
                <th style="padding: 2px; border: 1px solid #000">Sobre peso</th>
                <th style="padding: 2px; border: 1px solid #000">Otro</th>
                <th style="padding: 2px; border: 1px solid #000">Precio venta</th>
                <th style="padding: 2px; border: 1px solid #000">Precio viaje</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7">
                    Base factura
                </th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7">IVA</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7">Retención</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: yellow">Base 1</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #fb6340">Base 2</th>
                <th style="padding: 2px; border: 1px solid #000">Importe VTA</th>
            </tr>
        </thead>
        <tbody style="text-align: center; font-size: 8px">
            @foreach ($cotizaciones as $cotizacion)
                @php
                    $base_factura = floatval($cotizacion->base_factura ?? 0);
                    $iva = floatval($cotizacion->iva ?? 0);
                    $retencion = floatval($cotizacion->retencion ?? 0);
                    $total = floatval($cotizacion->total ?? 0);
                    $total_oficial = $base_factura + $iva - $retencion;
                    $base_taref = $total - $base_factura - $iva + $retencion;
                    $importe_vta = $base_taref + $total_oficial;
                    $totalOficialSum += $total_oficial;
                    $totalnoofi += $base_taref;
                    $importeVtaSum += $importe_vta;
                @endphp

                <tr style="font-size: 7px; border: 1px solid #000">
                    <td style="padding: 2px; border: 1px solid #000">
                        @php
                            $fechaInicio = optional(optional(optional($cotizacion->DocCotizacion)->Asignaciones))
                                ->fehca_inicio_guard;
                        @endphp

                        {{ $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('d-m-Y') : 'Sin fecha' }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">
                        {{ optional(optional($cotizacion->DocCotizacion)->Asignaciones)->Proveedor->nombre ?? '-' }}

                        @php
                            $numContenedor = optional($cotizacion->DocCotizacion)->num_contenedor ?? '';

                            if ($cotizacion->jerarquia === 'Principal' && $cotizacion->referencia_full) {
                                $cotSecundaria = \App\Models\Cotizaciones::where(
                                    'referencia_full',
                                    $cotizacion->referencia_full,
                                )
                                    ->where('jerarquia', 'Secundario')
                                    ->with('DocCotizacion')
                                    ->first();

                                $contenedorSec = optional($cotSecundaria?->DocCotizacion)->num_contenedor;
                                if ($contenedorSec) {
                                    $numContenedor .= ' / ' . $contenedorSec;
                                }
                            }

                            // 🔹 aquí cortamos cada contenedor a 12 caracteres
                            $arr = explode(' / ', $numContenedor);
                            $arr = array_map(fn($c) => mb_substr($c, 0, 11), $arr);
                            $numContenedor = implode(' / ', $arr);
                        @endphp
                    </td>

                    <td style="padding: 2px; border: 1px solid #000">{{ $numContenedor }}</td>

                    <td style="padding: 2px; border: 1px solid #000; color: #020202; background: yellow">
                        {{ $cotizacion->id_subcliente && $cotizacion->Subcliente ? $cotizacion->Subcliente->nombre : 'N/A' }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000; color: #ffffff; background: #2778c4">
                        {{ $cotizacion->destino }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">{{ $cotizacion->peso_contenedor }}</td>
                    <td style="padding: 2px; border: 1px solid #000">{{ $cotizacion->tamano }}</td>
                    <td style="padding: 2px; border: 1px solid #000">
                        $ {{ number_format(floatval($cotizacion->burreo ?? 0), 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">
                        $ {{ number_format(floatval($cotizacion->maniobra ?? 0), 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">
                        ${{ number_format(floatval($cotizacion->estadia ?? 0), 2, '.', ',') }}
                    </td>
                    @php
                        $sobrepeso = ($cotizacion->peso_contenedor ?? 0) - ($cotizacion->peso_reglamentario ?? 0);
                        $sobrepeso_calc = $sobrepeso * ($cotizacion->precio_sobre_peso ?? 0);
                    @endphp

                    <td style="padding: 2px; border: 1px solid #000">
                        $
                        {{ number_format($sobrepeso_calc, 2, '.', ',') }}
                    </td>

                    <td style="padding: 2px; border: 1px solid #000">
                        $ {{ number_format(floatval($cotizacion->otro ?? 0), 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">
                        $ {{ number_format(floatval($cotizacion->precio ?? 0), 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">
                        $ {{ number_format(floatval($cotizacion->precio_viaje ?? 0), 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">
                        $ {{ number_format($base_factura, 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">$ {{ number_format($iva, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000">
                        $ {{ number_format($retencion, 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">
                        $ {{ number_format($total_oficial, 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">
                        $ {{ number_format($base_taref, 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000">
                        $ {{ number_format($importe_vta, 2, '.', ',') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    @else
    {{-- MODO EMPRESA GLOBAL (Configuración Global = 1): Separación y agrupación por Unidad Tractocamión --}}
    @php
        $cotizacionesPorUnidad = $cotizaciones->groupBy(function($c) {
            $camion = optional(optional(optional($c->DocCotizacion)->Asignaciones)->Camion);
            if ($camion && ($camion->id_equipo || $camion->placas)) {
                return $camion->texto_select;
            }
            return 'Unidad No Asignada';
        });

        $totalPrecioVentaSum = 0;
        $totalPrecioViajeSum = 0;
        $totalBaseFacturaSum = 0;
        $totalIvaSum = 0;
        $totalRetencionSum = 0;
    @endphp

    @foreach ($cotizacionesPorUnidad as $unidadTexto => $itemsUnidad)
        @php
            $subPrecioVenta = 0;
            $subPrecioViaje = 0;
            $subBaseFactura = 0;
            $subIva = 0;
            $subRetencion = 0;
            $subTotalOficial = 0;
            $subBaseTaref = 0;
            $subImporteVta = 0;
        @endphp

        @if (!isset($isExcel))
            <div style="background-color: #2c3e50; color: #ffffff; padding: 4px 8px; font-size: 8.5px; font-weight: bold; margin-top: {{ $loop->first ? '0px' : '10px' }}; margin-bottom: 2px; border-radius: 3px;">
                UNIDAD / TRACTOCAMION: {{ $unidadTexto }}
            </div>
        @endif

        <table class="table text-white tabla-completa"
            style="color: #000; width: 100%; padding: 5px; margin: 0px 0px 6px 0px; border-collapse: collapse; border: 1px solid #000;">
            @if (isset($isExcel))
                <thead>
                    <tr>
                        <th colspan="20" style="background-color: #2c3e50; color: #ffffff; text-align: left; font-size: 10pt; font-weight: bold;">
                            UNIDAD / TRACTOCAMION: {{ $unidadTexto }}
                        </th>
                    </tr>
                </thead>
            @endif
            <thead>
                <tr style="font-size: 7px; border: 1px solid #000">
                    <th style="padding: 2px; border: 1px solid #000">Fecha inicio</th>
                    <th style="padding: 2px; border: 1px solid #000">Unidad</th>
                    <th style="padding: 2px; border: 1px solid #000">Contenedor</th>
                    <th style="padding: 2px; border: 1px solid #000">Facturado a</th>
                    <th style="padding: 2px; border: 1px solid #000">Destino</th>
                    <th style="padding: 2px; border: 1px solid #000">Peso</th>
                    <th style="padding: 2px; border: 1px solid #000">Tam. Cont.</th>
                    <th style="padding: 2px; border: 1px solid #000">Burreo</th>
                    <th style="padding: 2px; border: 1px solid #000">Maniobra</th>
                    <th style="padding: 2px; border: 1px solid #000">Estadia</th>
                    <th style="padding: 2px; border: 1px solid #000">Sobre peso</th>
                    <th style="padding: 2px; border: 1px solid #000">Otro</th>
                    <th style="padding: 2px; border: 1px solid #000">Precio venta</th>
                    <th style="padding: 2px; border: 1px solid #000">Precio viaje</th>
                    <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7">Base factura</th>
                    <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7">IVA</th>
                    <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7">Retención</th>
                    <th style="padding: 2px; border: 1px solid #000; color: #000000; background: yellow">Base 1</th>
                    <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #fb6340">Base 2</th>
                    <th style="padding: 2px; border: 1px solid #000">Importe VTA</th>
                </tr>
            </thead>
            <tbody style="text-align: center; font-size: 8px">
                @foreach ($itemsUnidad as $cotizacion)
                    @php
                        $base_factura = floatval($cotizacion->base_factura ?? 0);
                        $iva = floatval($cotizacion->iva ?? 0);
                        $retencion = floatval($cotizacion->retencion ?? 0);
                        $total = floatval($cotizacion->total ?? 0);
                        $total_oficial = $base_factura + $iva - $retencion;
                        $base_taref = $total - $base_factura - $iva + $retencion;
                        $importe_vta = $base_taref + $total_oficial;

                        $subPrecioVenta += floatval($cotizacion->precio ?? 0);
                        $subPrecioViaje += floatval($cotizacion->precio_viaje ?? 0);
                        $subBaseFactura += $base_factura;
                        $subIva += $iva;
                        $subRetencion += $retencion;
                        $subTotalOficial += $total_oficial;
                        $subBaseTaref += $base_taref;
                        $subImporteVta += $importe_vta;

                        $totalOficialSum += $total_oficial;
                        $totalnoofi += $base_taref;
                        $importeVtaSum += $importe_vta;
                        $totalPrecioVentaSum += floatval($cotizacion->precio ?? 0);
                        $totalPrecioViajeSum += floatval($cotizacion->precio_viaje ?? 0);
                        $totalBaseFacturaSum += $base_factura;
                        $totalIvaSum += $iva;
                        $totalRetencionSum += $retencion;
                    @endphp

                    <tr style="font-size: 7px; border: 1px solid #000">
                        <td style="padding: 2px; border: 1px solid #000">
                            @php
                                $fechaInicio = optional(optional(optional($cotizacion->DocCotizacion)->Asignaciones))->fehca_inicio_guard;
                            @endphp
                            {{ $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('d-m-Y') : 'Sin fecha' }}
                        </td>
                        <td style="padding: 2px; border: 1px solid #000; font-weight: bold; background-color: #f8f9fa;">
                            {{ $unidadTexto }}
                        </td>
                        <td style="padding: 2px; border: 1px solid #000">
                            @php
                                $numContenedor = optional($cotizacion->DocCotizacion)->num_contenedor ?? '';
                                if ($cotizacion->jerarquia === 'Principal' && $cotizacion->referencia_full) {
                                    $cotSecundaria = \App\Models\Cotizaciones::where(
                                        'referencia_full',
                                        $cotizacion->referencia_full,
                                    )
                                        ->where('jerarquia', 'Secundario')
                                        ->with('DocCotizacion')
                                        ->first();

                                    $contenedorSec = optional($cotSecundaria?->DocCotizacion)->num_contenedor;
                                    if ($contenedorSec) {
                                        $numContenedor .= ' / ' . $contenedorSec;
                                    }
                                }
                                $arr = explode(' / ', $numContenedor);
                                $arr = array_map(fn($c) => mb_substr($c, 0, 11), $arr);
                                $numContenedor = implode(' / ', $arr);
                            @endphp
                            {{ $numContenedor }}
                        </td>
                        <td style="padding: 2px; border: 1px solid #000; color: #020202; background: yellow">
                            {{ $cotizacion->id_subcliente && $cotizacion->Subcliente ? $cotizacion->Subcliente->nombre : 'N/A' }}
                        </td>
                        <td style="padding: 2px; border: 1px solid #000; color: #ffffff; background: #2778c4">
                            {{ $cotizacion->destino }}
                        </td>
                        <td style="padding: 2px; border: 1px solid #000">{{ $cotizacion->peso_contenedor }}</td>
                        <td style="padding: 2px; border: 1px solid #000">{{ $cotizacion->tamano }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format(floatval($cotizacion->burreo ?? 0), 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format(floatval($cotizacion->maniobra ?? 0), 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">${{ number_format(floatval($cotizacion->estadia ?? 0), 2, '.', ',') }}</td>
                        @php
                            $sobrepeso = ($cotizacion->peso_contenedor ?? 0) - ($cotizacion->peso_reglamentario ?? 0);
                            $sobrepeso_calc = $sobrepeso * ($cotizacion->precio_sobre_peso ?? 0);
                        @endphp
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format($sobrepeso_calc, 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format(floatval($cotizacion->otro ?? 0), 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format(floatval($cotizacion->precio ?? 0), 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format(floatval($cotizacion->precio_viaje ?? 0), 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format($base_factura, 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format($iva, 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format($retencion, 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format($total_oficial, 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format($base_taref, 2, '.', ',') }}</td>
                        <td style="padding: 2px; border: 1px solid #000">$ {{ number_format($importe_vta, 2, '.', ',') }}</td>
                    </tr>
                @endforeach
                {{-- Fila de Subtotal por Unidad --}}
                <tr style="font-size: 7.5px; font-weight: bold; background-color: #d8e8f5; border: 1.5px solid #000;">
                    <td colspan="12" style="padding: 3px; text-align: right; border: 1px solid #000;">
                        SUBTOTAL {{ $unidadTexto }}:
                    </td>
                    <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($subPrecioVenta, 2, '.', ',') }}</td>
                    <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($subPrecioViaje, 2, '.', ',') }}</td>
                    <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($subBaseFactura, 2, '.', ',') }}</td>
                    <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($subIva, 2, '.', ',') }}</td>
                    <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($subRetencion, 2, '.', ',') }}</td>
                    <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($subTotalOficial, 2, '.', ',') }}</td>
                    <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($subBaseTaref, 2, '.', ',') }}</td>
                    <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($subImporteVta, 2, '.', ',') }}</td>
                </tr>
            </tbody>
        </table>
    @endforeach

    {{-- Tabla de Gran Total Consolidado al finalizar todas las unidades --}}
    <table class="table text-white tabla-completa"
        style="color: #000; width: 100%; margin: 6px 0px 8px 0px; border-collapse: collapse; border: 2px solid #28a745;">
        <tbody>
            <tr style="font-size: 8px; font-weight: bold; background-color: #28a745; color: #ffffff;">
                <td colspan="12" style="padding: 4px; text-align: right; border: 1px solid #000;">
                    GRAN TOTAL CONSOLIDADO ESTADO DE CUENTA:
                </td>
                <td style="padding: 4px; border: 1px solid #000; text-align: center;">$ {{ number_format($totalPrecioVentaSum, 2, '.', ',') }}</td>
                <td style="padding: 4px; border: 1px solid #000; text-align: center;">$ {{ number_format($totalPrecioViajeSum, 2, '.', ',') }}</td>
                <td style="padding: 4px; border: 1px solid #000; text-align: center;">$ {{ number_format($totalBaseFacturaSum, 2, '.', ',') }}</td>
                <td style="padding: 4px; border: 1px solid #000; text-align: center;">$ {{ number_format($totalIvaSum, 2, '.', ',') }}</td>
                <td style="padding: 4px; border: 1px solid #000; text-align: center;">$ {{ number_format($totalRetencionSum, 2, '.', ',') }}</td>
                <td style="padding: 4px; border: 1px solid #000; text-align: center;">$ {{ number_format($totalOficialSum, 2, '.', ',') }}</td>
                <td style="padding: 4px; border: 1px solid #000; text-align: center;">$ {{ number_format($totalnoofi, 2, '.', ',') }}</td>
                <td style="padding: 4px; border: 1px solid #000; text-align: center;">$ {{ number_format($importeVtaSum, 2, '.', ',') }}</td>
            </tr>
        </tbody>
    </table>
    @endif

    @include('reporteria.cxc.partials.cuentas_bancarias')
</body>

</html>
