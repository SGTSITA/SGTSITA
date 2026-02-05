<!DOCTYPE html>
<html>
@if (!isset($isExcel))
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 10px;
            /* 🔽 Tamaño general reducido */
            margin-top: 50px;
            margin-left: 40px;
        }

        h3,
        h4,
        h5,
        p,
        th,
        td {
            font-weight: normal;
            font-size: 10px;
            /* 🔽 Aplica a títulos y párrafos */
            margin: 0;
            padding: 0;
        }

        .registro-contenedor {
            border: 2px solid #000;
            margin-bottom: 20px;
            padding: 10px;
            border-radius: 5px;
        }

        .registro-contenedor table {
            margin-bottom: 10px;
            font-family: Arial, sans-serif;
            font-size: 9px;
            width: 100%;
            border-collapse: collapse;
        }

        table th,
        table td {
            border: 1px solid #000;
            padding: 4px;
            font-size: 9px;
        }

        .tabla-completa thead th {
            background-color: #007bff;
            color: white;
            font-weight: bold;
            font-size: 9px;
            text-align: center;
            padding: 4px;
        }

        .tabla-completa td {
            font-size: 8.5px;
            padding: 4px;
        }

        .tabla-completa {
            font-size: 8.5px;
            border-collapse: collapse;
        }

        .totales {
            margin-top: 15px;
            font-size: 9.5px;
        }

        .totales h3 {
            font-weight: bold;
            font-size: 10px;
            margin-bottom: 5px;
        }

        .totales h4 {
            font-size: 9.5px;
            margin: 2px 0;
        }

        .sin_espacios2 {
            margin: 2px;
            font-size: 9.5px;
        }

        .contianer {
            padding: 0;
            margin: -40px;
        }
    </style>
    <style>
        .tabla-completa thead th {
            background-color: #007bff;
            /* Azul claro */
            color: white;
            font-weight: bold;
            font-size: 9px;
            /* 🔽 Tamaño reducido */
            text-align: center;
            padding: 4px;
            /* 🔽 Menos espacio para más compacidad */
        }

        .tabla-completa td {
            font-size: 8.5px;
            /* 🔽 Letra más pequeña */
            padding: 4px;
        }

        .tabla-completa {
            font-size: 8.5px;
            border-collapse: collapse;
        }
    </style>
@endif

<head>
    <meta charset="UTF-8" />
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
    <title>Cuentas por pagar</title>
</head>

<body>
    @php
        $importeCT = 0;
        $pagar1 = 0;
        $pagar2 = 0;
        $totalBaseFactura = 0;
        $totalImporteVTA = 0;
    @endphp



    <!-- Encabezado principal -->
    {{-- <div class="contianer sin_margem">
        <h4 class="sin_espacios2" style="position: top: -10%;">Empresa: {{ $user->Empresa->nombre }}</h4>
        <h4 class="sin_espacios2" style="position: top: -10%;">Estado de cuenta</h4>
        <h4 class="sin_espacios2" style="position: top: -10%;">Proveedor: {{ $cotizacion->Proveedor->nombre }}</h4>
    </div> --}}
    @if (!isset($isExcel))
        <div class="contianer sin_margem">
            <h4 style="margin: 2px">Empresa: {{ $user->Empresa->nombre }}</h4>

            <div style="display:flex; align-items:center; position:relative;">
                <h4 style="margin: 2px">Estado de cuenta</h4>

                <h2
                    style="
                position:absolute;
                left:50%;
                top:-5px;
                transform:translateX(-50%);
                margin:0;
                font-weight:bold;">
                    {{ $cotizacion->Contenedor->Cotizacion->estadoCuenta->numero }}
                </h2>
            </div>

            <h4 style="margin: 2px">
                Proveedor: {{ $cotizacion->Proveedor->nombre }}
            </h4>
            <br>
        </div>

        <div class="contianer sin_margem" style="position: relative">
            <h5 style="position:absolute; left:80%; top:-5%">
                Estado de cuenta por pagar: {{ date('d-m-Y') }}
            </h5>
        </div>
    @endif
    @if (isset($isExcel))
        <table width="100%" style="border-collapse:collapse;">
            <tr>
                <td colspan="4">
                    <b>Empresa:</b> {{ $user->Empresa->nombre }}
                </td>
            </tr>
            <tr>
                <td colspan="3">

                    <b> Estado de cuenta</b>

                </td>

                <td colspan="3" align="center" style="font-size:18pt; font-weight:bold;">
                    <b> {{ $cotizacion->Contenedor->Cotizacion->estadoCuenta->numero }} </b>
                </td>

                <td colspan="6" align="right">
                    Estado de cuenta por pagar: {{ date('d-m-Y') }}
                </td>

            </tr>
            <tr>
                <td colspan="4">
                    <b>Proveedor:</b> {{ $cotizacion->Proveedor->nombre }}
                </td>
            </tr>
            {{-- <tr>
                <td colspan="4">
                    <b>Empresa:</b> {{ $user->Empresa->nombre }}<br>
                    Estado de cuenta<br>
                    <b>Proveedor:</b> {{ $cotizacion->Proveedor->nombre }}
                </td>
                <td colspan="4" align="center">
                    {{ $cotizacion->Contenedor->Cotizacion->estadoCuenta->numero }}
                </td>
                <td colspan="4" align="right">
                    {{ date('d-m-Y') }}
                </td>
            </tr> --}}
        </table>
    @endif

    <!-- Tabla principal de cuentas por pagar -->
    <table class="table tabla-completa"
        style="color: #000; width: 100%; margin-top: 55px; font-size: 10px; border-collapse: collapse">
        <thead>
            <tr>
                <th style="background-color:#007bff; color:#ffffff;">Facturado a</th>
                <th style="background-color:#007bff; color:#ffffff;">Contenedor</th>
                <th style="background-color:#007bff; color:#ffffff;">Importe CT</th>
                <th style="background-color:#007bff; color:#ffffff;">A pagar 1</th>
                <th style="background-color:#007bff; color:#ffffff;">A pagar 2</th>
                <th style="background-color:#007bff; color:#ffffff;">Retención</th>
                <th style="background-color:#007bff; color:#ffffff;">IVA</th>
                <th style="background-color:#007bff; color:#ffffff;">Base factura</th>
                <th style="background-color:#007bff; color:#ffffff;">Precio viaje</th>
                <th style="background-color:#007bff; color:#ffffff;">Otro</th>
                <th style="background-color:#007bff; color:#ffffff;">Estadía</th>
                <th style="background-color:#007bff; color:#ffffff;">Burreo</th>
            </tr>
        </thead>
        <tbody style="text-align: center; font-size: 10px; line-height: 1">
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
                    <td>{{ optional($item->Contenedor->Cotizacion->Subcliente)->nombre ?? 'N/A' }}</td>
                    @php
                        $numContenedor = optional($item->Contenedor)->num_contenedor ?? '';
                        $cot = optional($item->Contenedor)->Cotizacion;

                        if ($cot && $cot->jerarquia === 'Principal' && $cot->referencia_full) {
                            $sec = \App\Models\Cotizaciones::where('referencia_full', $cot->referencia_full)
                                ->where('jerarquia', 'Secundario')
                                ->where('id', '!=', $cot->id)
                                ->with('DocCotizacion')
                                ->first();

                            $numContSec = optional($sec?->DocCotizacion)->num_contenedor;

                            if ($numContSec) {
                                $numContenedor .= ' / ' . $numContSec;
                            }
                        }

                        $arr = explode(' / ', $numContenedor);
                        $arr = array_map(fn($c) => mb_substr($c, 0, 11), $arr);
                        $numContenedor = implode(' / ', $arr);
                    @endphp

                    <td>{{ $numContenedor }}</td>
                    <td>${{ number_format($suma_importeCT, 2, '.', ',') }}</td>
                    <td>${{ number_format($total_oficial, 2, '.', ',') }}</td>
                    <td>${{ number_format($base_factura, 2, '.', ',') }}</td>
                    <td>${{ number_format($item->retencion, 2, '.', ',') }}</td>
                    <td>${{ number_format($item->iva, 2, '.', ',') }}</td>
                    <td>${{ number_format($item->base1_proveedor, 2, '.', ',') }}</td>
                    <td>${{ number_format($item->precio, 2, '.', ',') }}</td>
                    <td>${{ number_format($item->otro, 2, '.', ',') }}</td>
                    <td>${{ number_format($item->estadia, 2, '.', ',') }}</td>
                    <td>${{ number_format($item->burreo, 2, '.', ',') }}</td>
                </tr>
            @endforeach

        </tbody>
    </table>

    <table class="table tabla-completa sin_margem"
        style="color: #000; width: 100%; font-size: 10px; margin-top: 5px; border-collapse: collapse">
        <tbody style="text-align: left; font-size: 10px; line-height: 1">
            @if ($cotizacion->Proveedor && $cotizacion->Proveedor->CuentasBancarias->isNotEmpty())
                @php
                    $contador = 1;
                    $colspan = 0;
                @endphp

                <table width="100%" cellspacing="0" cellpadding="0" style="margin-top:5px;">
                    <tr>

                        @foreach ($cotizacion->Proveedor->CuentasBancarias as $cuentas)
                            @php
                                $colspan = $contador == 1 ? 4 : 8;
                            @endphp
                            <td colspan="{{ $colspan }}"
                                style="
                    padding: 8px;
                    border: 1px solid #ccc;
                    text-align: left;
                    vertical-align: top;
                    font-size: 10px;
                ">
                                <strong>Cuenta #{{ $contador }}</strong><br>

                                Beneficiario: {{ $cuentas->nombre_beneficiario }}<br>
                                Banco: {{ $cuentas->nombre_banco }}<br>
                                Cuenta: {{ $cuentas->cuenta_bancaria }}<br>
                                Clabe: <strong>{{ $cuentas->cuenta_clabe }}</strong><br>
                                A pagar:
                                <strong>
                                    ${{ number_format($contador == 1 ? $pagar1 : ($contador == 2 ? $pagar2 : 0), 2, '.', ',') }}
                                </strong>
                                <br><br><br><br><br>
                            </td>

                            @php $contador++; @endphp
                        @endforeach

                    </tr>
                </table>
            @else
                <tr>
                    <td colspan="3"
                        style="padding: 5px; border: 1px solid #ccc; text-align: center; font-size: 10px">
                        No se encontraron cuentas bancarias para el proveedor:
                        <b>{{ $cotizacion->Proveedor->nombre ?? 'N/A' }}</b>
                        .
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Totales finales -->
    <table width="100%" style="margin-top:8px; border-collapse:collapse; border:none;">
        <tr>
            <td colspan="12" style="background-color:#00aaff; color:#000; font-weight:bold; border:none;">
                Totales
            </td>
        </tr>
    </table>

    <table style="border-collapse:collapse; border:none;">
        <tr>
            <td style="border:none;">A pagar oficial: ${{ number_format($pagar1, 2) }}</td>
        </tr>
        <tr>
            <td style="border:none;">A pagar no oficial: ${{ number_format($pagar2, 2) }}</td>
        </tr>
        <tr>
            <td style="border:none;">Importe CT: ${{ number_format($importeCT, 2) }}</td>
        </tr>
    </table>
</body>

</html>
