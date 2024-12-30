<!DOCTYPE html>
<html>
@if(!isset($isExcel))    
    <style>
       body {
    font-family: Arial, sans-serif; /* Fuente Arial para todo */
    font-size: 16px; /* Tamaño de fuente 12 */
    margin-top: 50px; 
    margin-left: 40px;
}

/* Estilo general para eliminar negritas en los elementos */
h3, p, th, td {
    font-weight: normal; /* Quitar negrita */
    
}

.registro-contenedor {
    border: 2px solid #000; /* Borde negro */
    margin-bottom: 20px; /* Espacio entre registros */
    padding: 15px; /* Espacio interno */
    border-radius: 5px; /* Bordes redondeados */
}

.registro-contenedor table {
    margin-bottom: 10px; /* Espacio entre tablas dentro del contenedor */
    font-family: Arial, sans-serif; /* Consistencia de fuente */
    font-size: 12px; /* Tamaño de fuente */
    width: 100%;
    border-collapse: collapse; /* Sin bordes dobles */
}

table th, table td {
    border: 1px solid #000; /* Bordes de celdas */
    padding: 8px; /* Espaciado interno */
}

.totales {
    margin-top: 20px; /* Separación superior */
    font-size: 16px;
}

.totales h3 {
    font-weight: bold; /* Negrita */
    font-size: 16px;
}

.totales p {
    font-size: 1.2em; /* Tamaño más grande */
    color: #000; /* Color negro */
    font-size: 16px;
}

.margin_cero, .sin_espacios, .sin_margem {
    margin: 0; /* Sin márgenes */
    padding: 0; /* Sin relleno */
    font-size: 16px;
}

.margin_cero {
    font-size: 16px; /* Tamaño de fuente específico */

}

.sin_espacios {
    font-size: 16px; /* Tamaño de fuente */
}

.sin_espacios2 {
    margin: 2px; /* Márgenes pequeños */
    padding: 0; /* Sin relleno */
    font-size: 10px; /* Fuente más pequeña */
    font-size: 12px;
}

.contianer {
    padding: 0; /* Sin relleno */
    margin: -40px; /* Margen negativo */
    font-size: 16px;
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
        @endphp

            <div class="contianer sin_margem" style="margin: -40px;">
                <h4 class="sin_espacios2">Empresa: {{ $user->Empresa->nombre }}</h4>
                <h4 class="sin_espacios2">Estado de cuenta</h4>
                <h4 class="sin_espacios2">Proveedor: {{ $cotizacion->Proveedor->nombre }}</h4>
            </div>

            <div class="contianer sin_margem" style="position: relative">
                <h5 style="position: absolute;left:70%;top:-5%;">Estado de cuenta por pagar : {{ date("d-m-Y") }}</h5><br>
            </div>

            <table class="table text-white tabla-completa" style="color: #000;width: 100%;padding: 5px;margin-top: 50px; font-size: 12px">
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
    @if ($cotizacion->Proveedor)
        @if ($cotizacion->Proveedor->CuentasBancarias->isNotEmpty())
            @php
                $contador = 1;
            @endphp
            @foreach ($cotizacion->Proveedor->CuentasBancarias as $cuentas)
                <td style="padding: 15px; margin: 0; border: 1px solid #ccc; text-align: left; vertical-align: top; width: 200px; min-width: 200px;">
                    <h4 style="margin-bottom: 10px; font-size: 16px;">Cuenta #{{ $contador }}</h4>
                    <p style="margin: 5px 0; font-size: 14px;">Beneficiario: <b>{{ $cuentas->nombre_beneficiario }}</b></p>
                    <p style="margin: 5px 0; font-size: 14px;">Banco: <b>{{ $cuentas->nombre_banco }}</b></p>
                    <p style="margin: 5px 0; font-size: 14px;">Cuenta: <b>{{ $cuentas->cuenta_bancaria }}</b></p>
                    <p style="margin: 5px 0; font-size: 14px;">Clave: <b>{{ $cuentas->cuenta_clabe }}</b></p>
                    @if ($contador == 1)
                        <p style="margin: 5px 0; font-size: 14px;"><b>A pagar:</b> ${{ number_format($pagar1, 2, '.', ',') }}</p>
                    @elseif ($contador == 2)
                        <p style="margin: 5px 0; font-size: 14px;"><b>A pagar:</b> ${{ number_format($pagar2, 2, '.', ',') }}</p>
                    @else
                        <p style="margin: 5px 0; font-size: 14px;"><b>A pagar:</b> $00.00</p>
                    @endif
                    @php
                        $contador++;
                    @endphp
                </td>
            @endforeach
        @else
            <td colspan="3" style="padding: 20px; border: 1px solid #ccc; text-align: center; font-size: 16px; width: 100%;">
                No se encontraron cuentas bancarias para el proveedor: <b>{{ $cotizacion->Proveedor->nombre }}</b>.
            </td>
        @endif
    @else
        <td colspan="3" style="padding: 20px; border: 1px solid #ccc; text-align: center; font-size: 16px; width: 100%;">
            No se encontró un proveedor asociado a esta cotización.
        </td>
    @endif
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
