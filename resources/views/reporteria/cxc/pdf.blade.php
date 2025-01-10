<!DOCTYPE html>
<html>
@if(!isset($isExcel))
    <style>
        /* Configuración de la página para tamaño carta en horizontal con márgenes generales */
        @page {
            size: letter landscape; /* Tamaño carta en orientación horizontal */
            margin: 10mm; /* Márgenes generales alrededor de la página */
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 8px; /* Tamaño de fuente reducido */
            margin: 0; 
            padding: 0;
        }

        /* Eliminar el bold en todos los elementos */
        h3, p, th, td {
            font-weight: normal; /* Quitar negrita */
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
            font-weight: normal; /* Quitar negrita */
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
            font-size: 7px; /* Tamaño de fuente reducido */
            width: 100%;
            border-collapse: collapse;
            margin-top: 0px;
        }

        th, td {
            border: 1px solid #000;
            padding: 4px; /* Reducir el espacio de las celdas */
            text-align: center; /* Centrar el texto */
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
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <title>Cuentas por cobrar</title>
</head>

<body>
    @php
        $totalOficialSum = 0;
        $totalnoofi = 0;
        $importeVtaSum = 0;
        $total_no_ofi = 0;
    @endphp

    <div class="container" style="position: relative; padding: 0px;">
        <h4 class="margin_cero" style="margin: 2px;">Empresa: {{ $user->Empresa->nombre }}</h4>
        <h4 class="margin_cero" style="margin: 2px;">Estado de cuenta</h4>
        <h4 class="margin_cero" style="margin: 2px;">Cliente: {{ $cotizacion->Cliente->nombre }}</h4><br>
    </div>

    <div class="container" style="position: relative;">
        <h5 style="position: absolute;left:80%;top:-10%;font-size: 10px; margin-top: 5px;">Cuentas por Cobrar : {{ date("d-m-Y") }}</h5><br>
    </div>

    <table class="table text-white tabla-completa" style="color: #000; width: 100%; padding: 5px; margin: 0px; border-collapse: collapse; border: 1px solid #000;">
        <thead>
            <tr style="font-size: 7px; border: 1px solid #000;">
                <th style="padding: 2px; border: 1px solid #000;">Fecha inicio</th>
                <th style="padding: 2px; border: 1px solid #000;">Contratista</th>
                <th style="padding: 2px; border: 1px solid #000;">Contenedor</th>
                <th style="padding: 2px; border: 1px solid #000;">Facturado a</th>
                <th style="padding: 2px; border: 1px solid #000;">Destino</th>
                <th style="padding: 2px; border: 1px solid #000;">Peso</th>
                <th style="padding: 2px; border: 1px solid #000;">Tam. Cont.</th>
                <th style="padding: 2px; border: 1px solid #000;">Burreo</th>
                <th style="padding: 2px; border: 1px solid #000;">Estadia</th>
                <th style="padding: 2px; border: 1px solid #000;">Sobre peso</th>
                <th style="padding: 2px; border: 1px solid #000;">Otro</th>
                <th style="padding: 2px; border: 1px solid #000;">Precio venta</th>
                <th style="padding: 2px; border: 1px solid #000;">Precio viaje</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7;">Base factura</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7;">IVA</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7;">Retención</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #2dce89;">Base 2</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: yellow;">Total oficial</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #fb6340;">Total no oficial</th>
                <th style="padding: 2px; border: 1px solid #000;">Importe VTA</th>
            </tr>
        </thead>
        <tbody style="text-align: center; font-size: 8px;">
            @foreach ($cotizaciones as $cotizacion)
                @php
                    $base_factura = floatval($cotizacion->base_factura ?? 0);
                    $iva = floatval($cotizacion->iva ?? 0);
                    $retencion = floatval($cotizacion->retencion ?? 0);
                    $total = floatval($cotizacion->total ?? 0);
                    $total_oficial = ($base_factura + $iva) - $retencion;
                    $base_taref = $total - $base_factura - $iva + $retencion;
                    $importe_vta = $base_taref + $total_oficial;
                    $totalOficialSum += $total_oficial;
                    $totalnoofi += $base_taref;
                    $importeVtaSum += $importe_vta;
                @endphp
                <tr style="font-size: 7px; border: 1px solid #000;">
                    <td style="padding: 2px; border: 1px solid #000;">{{ Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fehca_inicio_guard)->format('d-m-Y') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">{{ optional($cotizacion->DocCotizacion->Asignaciones->Proveedor)->nombre ?? '-' }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">{{ $cotizacion->DocCotizacion->num_contenedor }}</td>
                    <td style="padding: 2px; border: 1px solid #000; color: #020202; background: yellow;">{{ $cotizacion->id_subcliente ? $cotizacion->Subcliente->nombre : '' }}</td>
                    <td style="padding: 2px; border: 1px solid #000; color: #ffffff; background: #2778c4;">{{ $cotizacion->destino }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">{{ $cotizacion->peso_contenedor }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">{{ $cotizacion->tamano }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($cotizacion->burreo, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($cotizacion->maniobra, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($cotizacion->precio_tonelada, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($cotizacion->otro, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($cotizacion->precio, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($cotizacion->precio_viaje, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($base_factura, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($iva, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($retencion, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($base_taref, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($total_oficial, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($base_taref, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($importe_vta, 2, '.', ',') }}</td>
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

<h3 class="sin_margem" style="color: #fff; background: rgb(24, 192, 141);margin-top: 0px;padding: 0px;">Cuentas Bancarias Proveedores</h3>
<table class="table text-white tabla-completa sin_margem" style="color: #000; width: 100%; padding: 0px; font-size: 8px; border-collapse: collapse;">
    <thead style="background-color: #f5f5f5; font-weight: bold;">
        <tr>
            <th style="padding: 1px; border: 1px solid #ccc;">Cuenta 1</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Beneficiario</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Banco</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Cuenta</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Clabe</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Monto a Pagar</th>
        </tr>
    </thead>
    <tbody>
        <!-- Tabla para las Cuentas 1 -->
        @foreach ($proveedoresConCuentas as $proveedor)
            @php
                $totalCuenta1 = 0;
                if (isset($cotizacionesPorProveedor[$proveedor->id])) {
                    $cotizacionesProveedor = $cotizacionesPorProveedor[$proveedor->id];
                    foreach ($cotizacionesProveedor as $cotizacion) {
                        $cuenta1 = $cotizacion->base_factura + $cotizacion->iva - $cotizacion->retencion;
                        $totalCuenta1 += $cuenta1;
                    }
                }
            @endphp

            @if (!$proveedor->CuentasBancarias->isEmpty() && isset($proveedor->CuentasBancarias[0]))
                <tr>
                    <td style="padding: 1px; border: 1px solid #ccc;">{{ $proveedor->nombre }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">{{ $proveedor->CuentasBancarias[0]->nombre_beneficiario }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">{{ $proveedor->CuentasBancarias[0]->nombre_banco }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">No.{{ $proveedor->CuentasBancarias[0]->cuenta_bancaria }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">No.{{ $proveedor->CuentasBancarias[0]->cuenta_clabe }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">${{ number_format($totalCuenta1, 2, '.', ',') }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>

<br>

<table class="table text-white tabla-completa sin_margem" style="color: #000; width: 100%; padding: 0px; font-size: 8px; border-collapse: collapse;">
    <thead style="background-color: #f5f5f5; font-weight: bold;">
        <tr>
            <th style="padding: 1px; border: 1px solid #ccc;">Cuenta 2</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Beneficiario</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Banco</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Cuenta</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Clabe</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Monto a Pagar</th>
        </tr>
    </thead>
    <tbody>
        <!-- Tabla para las Cuentas 2 -->
        @foreach ($proveedoresConCuentas as $proveedor)
            @php
                $totalCuenta2 = 0;
                if (isset($cotizacionesPorProveedor[$proveedor->id])) {
                    $cotizacionesProveedor = $cotizacionesPorProveedor[$proveedor->id];
                    foreach ($cotizacionesProveedor as $cotizacion) {
                        $cuenta2 = $cotizacion->total - $cotizacion->base_factura - $cotizacion->iva + $cotizacion->retencion;
                        $totalCuenta2 += $cuenta2;
                    }
                }
            @endphp

            @if (isset($proveedor->CuentasBancarias[1]))
                <tr>
                    <td style="padding: 1px; border: 1px solid #ccc;">{{ $proveedor->nombre }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">{{ $proveedor->CuentasBancarias[1]->nombre_beneficiario }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">{{ $proveedor->CuentasBancarias[1]->nombre_banco }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">No.{{ $proveedor->CuentasBancarias[1]->cuenta_bancaria }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">No.{{ $proveedor->CuentasBancarias[1]->cuenta_clabe }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">${{ number_format($totalCuenta2, 2, '.', ',') }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>

<br>

<table class="table text-white tabla-completa sin_margem" style="color: #000; width: 100%; padding: 0px; font-size: 8px; border-collapse: collapse;">
    <thead style="background-color: #f5f5f5; font-weight: bold;">
        <tr>
            <th style="padding: 1px; border: 1px solid #ccc;">Cuenta 3</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Beneficiario</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Banco</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Cuenta</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Clabe</th>
            <th style="padding: 1px; border: 1px solid #ccc;">Monto a Pagar</th>
        </tr>
    </thead>
    <tbody>
        <!-- Tabla para las Cuentas 3 -->
        @foreach ($proveedoresConCuentas as $proveedor)
            @php
                $totalCuenta3 = 0;
                if (isset($cotizacionesPorProveedor[$proveedor->id])) {
                    $cotizacionesProveedor = $cotizacionesPorProveedor[$proveedor->id];
                    foreach ($cotizacionesProveedor as $cotizacion) {
                        $totalCuenta3 += 0; // Si aplica un valor para cuenta 3, reemplazar aquí
                    }
                }
            @endphp

            @if (isset($proveedor->CuentasBancarias[2]))
                <tr>
                    <td style="padding: 1px; border: 1px solid #ccc;">{{ $proveedor->nombre }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">{{ $proveedor->CuentasBancarias[2]->nombre_beneficiario }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">{{ $proveedor->CuentasBancarias[2]->nombre_banco }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">No.{{ $proveedor->CuentasBancarias[2]->cuenta_bancaria }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">No.{{ $proveedor->CuentasBancarias[2]->cuenta_clabe }}</td>
                    <td style="padding: 1px; border: 1px solid #ccc;">${{ number_format($totalCuenta3, 2, '.', ',') }}</td>
                </tr>
            @endif
        @endforeach
    </tbody>
</table>

<table class="table text-white" style="color: #000; width: 50%; padding: 5px; font-size: 8px; border-collapse: collapse">
@php
    // Recopilamos los proveedores y sus datos
    $proveedoresIds = $cotizaciones->pluck('DocCotizacion.Asignaciones.id_proveedor')->filter()->unique();
    $proveedoresConCuentas = App\Models\Proveedor::whereIn('id', $proveedoresIds)
                                            ->with('CuentasBancarias')
                                            ->get();

    $cotizacionesPorProveedor = $cotizaciones->groupBy('DocCotizacion.Asignaciones.id_proveedor');
@endphp

@foreach ($proveedoresConCuentas as $index => $proveedor)
    @php
        $totalFacturaProveedor = 0;
        $facturadosPorProveedor = [];
        $beneficiarioCuenta1 = ''; // Variable para el beneficiario de la cuenta CLABE 1

        if (isset($cotizacionesPorProveedor[$proveedor->id])) {
            $cotizacionesProveedor = $cotizacionesPorProveedor[$proveedor->id];
            foreach ($cotizacionesProveedor as $cotizacion) {
                $cuenta1 = $cotizacion->base_factura + $cotizacion->iva - $cotizacion->retencion;
                $totalFacturaProveedor += $cuenta1;

                // Agrupamos los "facturados a" por proveedor
                $facturadoA = $cotizacion->Subcliente->nombre ?? 'Sin facturado';
                if (!isset($facturadosPorProveedor[$facturadoA])) {
                    $facturadosPorProveedor[$facturadoA] = 0;
                }
                $facturadosPorProveedor[$facturadoA] += $cuenta1;
            }
        }

        // Obtenemos el beneficiario de la primera cuenta CLABE (si existe)
        if (!$proveedor->CuentasBancarias->isEmpty()) {
            $cuentaCLABE = $proveedor->CuentasBancarias->first(); // Asumimos que la cuenta 1 es la primera
            $beneficiarioCuenta1 = $cuentaCLABE->nombre_beneficiario ?? 'No disponible';
        }
    @endphp

    <thead>
        <tr style="font-size: 7px; border: 1px solid #000; background-color: #2c3e50; color: white;">
            <th style="padding: 1px; border: 1px solid #000;">Proveedor-{{ $proveedor->nombre }}</th>
            <th style="padding: 1px; border: 1px solid #000;">Total</th>
            <!-- Columnas dinámicas para los "Facturados A" -->
            @foreach($facturadosPorProveedor as $facturadoA => $total)
                <th style="padding: 1px; border: 1px solid #000;">{{ $facturadoA }}</th>     
            @endforeach
        </tr>
    </thead>
    <tbody style="text-align: center; font-size: 8px;">
        @if (!$proveedor->CuentasBancarias->isEmpty())
            <tr style="background-color: {{ $index % 2 == 0 ? '#f1f1f1' : '#e0e0e0' }};">
                <td style="padding: 1px; border: 1px solid #ccc;">
                    <!-- Proveedor, Beneficiario, Banco, CLABE -->
                   
                    {{ $beneficiarioCuenta1 }}<br>
                    {{ $proveedor->CuentasBancarias[0]->nombre_banco }}<br>
                    No.{{ $cuentaCLABE->cuenta_clabe }}<br>
                </td>
                <td style="padding: 1px; border: 1px solid #ccc;">
                    ${{ number_format($totalFacturaProveedor, 2, '.', ',') }}<br>
                </td>
                <!-- Facturado A -->
                @foreach($facturadosPorProveedor as $facturadoA => $total)
                    <td style="padding: 1px; border: 1px solid #ccc;">
                        ${{ number_format($total, 2, '.', ',') }}<br>
                    </td>
                @endforeach
            </tr>
        @endif
    </tbody>
@endforeach
</table>

    <div class="totales">
        <h3 class="margin_cero" style="color: #000000; background: rgb(0, 174, 255);margin-top: 5px;">Totales</h3>
        <p class="margin_cero">Total oficial: <b class="margin_cero;margin-top: 5px;"> ${{ number_format($totalOficialSum, 2, '.', ',') }} </b></p>
        <p class="margin_cero">Total no oficial: <b class="margin_cero;margin-top: 5px;"> ${{ number_format($totalnoofi, 2, '.', ',') }} </b></p>
        <p class="margin_cero">Importe vta: <b class="margin_cero;margin-top: 5px;"> ${{ number_format($importeVtaSum, 2, '.', ',') }} </b></p>
    </div>

</body>
</html>