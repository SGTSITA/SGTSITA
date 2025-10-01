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
            background-color: #007BFF;
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
            background-color: #007BFF;
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
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
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
    <div class="contianer sin_margem ">
        <h4 class="sin_espacios2 "style="position: top: -10%;">Empresa: {{ $user->Empresa->nombre }}</h4>
        <h4 class="sin_espacios2" style="position: top: -10%;">Estado de cuenta</h4>
        <h4 class="sin_espacios2" style="position: top: -10%;">Proveedor: {{ $cotizacion->Proveedor->nombre }}</h4>
    </div>

    <div class="contianer sin_margem" style="position: relative">
        <h5 style="position: absolute; left: 70%; top: -5%;">Estado de cuenta por pagar: {{ date('d-m-Y') }}</h5>
    </div>


    <!-- Tabla principal de cuentas por pagar -->
    <table class="table tabla-completa"
        style="color: #000; width: 100%; margin-top: 55px; font-size: 10px; border-collapse: collapse;">
        <thead>
            <tr>
                <th>Facturado a</th>
                <th>Contenedor</th>
                <th>Importe CT</th>
                <th>A pagar 1</th>
                <th>A pagar 2</th>
                <th>Retención</th>
                <th>IVA</th>
                <th>Base factura</th>
                <th>Precio viaje</th>
                <th>Otro</th>
                <th>Estadía</th>
                <th>Burreo</th>
            </tr>
        </thead>
        <tbody style="text-align: center; font-size: 10px; line-height: 1;">
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
                    <td>{{ optional($item->Contenedor->Cotizacion->Subcliente)->nombre ?? 'N/A' }}
                    </td>
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
        style="color: #000; width: 100%; font-size: 10px; margin-top: 5px; border-collapse: collapse;">
        <tbody style="text-align: left; font-size: 10px; line-height: 1;">
            @if ($cotizacion->Proveedor && $cotizacion->Proveedor->CuentasBancarias->isNotEmpty())
                @php $contador = 1; @endphp
                <tr>
                    @foreach ($cotizacion->Proveedor->CuentasBancarias as $cuentas)
                        <td
                            style="padding: 5px; border: 1px solid #ccc; text-align: left; vertical-align: top; width: 150px; min-width: 150px;">
                            <h4 style="margin: 0; font-size: 10px;">Cuenta #{{ $contador }}</h4>
                            <p>Beneficiario: {{ $cuentas->nombre_beneficiario }}</p>
                            <p>Banco: {{ $cuentas->nombre_banco }}</p>
                            <p>Cuenta: {{ $cuentas->cuenta_bancaria }}</p>
                            <p>Clabe: <b>{{ $cuentas->cuenta_clabe }}</b></p>
                            <p>A pagar:
                                <b>${{ number_format($contador == 1 ? $pagar1 : ($contador == 2 ? $pagar2 : 0), 2, '.', ',') }}</b>
                            </p>
                            @php $contador++; @endphp
                        </td>
                    @endforeach
                </tr>
            @else
                <tr>
                    <td colspan="3"
                        style="padding: 5px; border: 1px solid #ccc; text-align: center; font-size: 10px;">No se
                        encontraron cuentas bancarias para el proveedor:
                        <b>{{ $cotizacion->Proveedor->nombre ?? 'N/A' }}</b>.
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    <!-- Totales finales -->
    <div class="totales">
        <h3 style="background: rgb(0, 174, 255);">Totales</h3>
        <h4>A pagar oficial: ${{ number_format($pagar1, 2, '.', ',') }}</h4>
        <h4>A pagar no oficial: ${{ number_format($pagar2, 2, '.', ',') }}</h4>
        <h4>Importe CT: ${{ number_format($importeCT, 2, '.', ',') }}</h4>
    </div>
</body>

</html>
