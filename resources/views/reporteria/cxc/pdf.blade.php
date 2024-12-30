<!DOCTYPE html>
<html>
@if(!isset($isExcel))
    <style>
        body {
            font-family: Arial, sans-serif; /* Fuente Arial para todo */
            font-size: 12px; /* Tamaño de fuente 12 */
            margin-top: 50px; 
            margin-left: 40px;
        }

        /* Eliminar el bold en todos los elementos */
        h3, p, th, td {
            font-weight: normal; /* Quitar negrita */
        }

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
            font-weight: normal; /* Quitar negrita */
        }

        .totales p {
            font-size: 1.2em;
            color: #000;
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

        table {
            font-family: Arial, sans-serif;
            font-size: 12px;
            width: 100%;
            border-collapse: collapse;
        }

        th, td {
            border: 1px solid #000;
            padding: 8px;
           
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
        
                
                <div class="contianer" style="position: relative">
                    <h4 class="margin_cero">Empresa: {{ $user->Empresa->nombre }}</h4>
                    <h4 class="margin_cero">Estado de cuenta</h4>
                    <h4 class="margin_cero">Cliente: {{ $cotizacion->Cliente->nombre }}</h4><br>
                </div>
                

                <div class="contianer" style="position: relative">
                    <h5 style="position: absolute;left:80%;top:-5%;">Cuentas por Cobrar : {{ date("d-m-Y") }}</h5><br>
                    
                </div>
                

                
                <table class="table text-white tabla-completa" style="color: #000; width: 100%; padding: 5px; margin: 5px; margin-top: 50px; font-size: 10px; border-collapse: collapse; border: 1px solid #000;">
    <thead>
        <tr style="font-size: 10px; border: 1px solid #000;">
            <th style="padding: 3px; border: 1px solid #000;">Fecha inicio</th>
            <th style="padding: 3px; border: 1px solid #000;">Contratista</th>
            <th style="padding: 3px; border: 1px solid #000;">Contenedor</th>
            <th style="padding: 3px; border: 1px solid #000;">Facturado a</th>
            <th style="padding: 3px; border: 1px solid #000;">Destino</th>
            <th style="padding: 3px; border: 1px solid #000;">Peso</th>
            <th style="padding: 3px; border: 1px solid #000;">Tam. Cont.</th>
            <th style="padding: 3px; border: 1px solid #000;">Burreo</th>
            <th style="padding: 3px; border: 1px solid #000;">Estadia</th>
            <th style="padding: 3px; border: 1px solid #000;">Sobre peso</th>
            <th style="padding: 3px; border: 1px solid #000;">Otro</th>
            <th style="padding: 3px; border: 1px solid #000;">Precio venta</th>
            <th style="padding: 3px; border: 1px solid #000;">Precio viaje</th>
            <th style="padding: 3px; border: 1px solid #000; color: #000000; background: #56d1f7;">Base factura</th>
            <th style="padding: 3px; border: 1px solid #000; color: #000000; background: #56d1f7;">IVA</th>
            <th style="padding: 3px; border: 1px solid #000; color: #000000; background: #56d1f7;">Retención</th>
            <th style="padding: 3px; border: 1px solid #000; color: #000000; background: #2dce89;">Base 2</th>
            <th style="padding: 3px; border: 1px solid #000; color: #000000; background: yellow;">Total oficial</th>
            <th style="padding: 3px; border: 1px solid #000; color: #000000; background: #fb6340;">Total no oficial</th>
            <th style="padding: 3px; border: 1px solid #000;">Importe VTA</th>
        </tr>
    </thead>
    <tbody style="text-align: center; font-size: 9px;">
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
            <tr style="font-size: 9px; border: 1px solid #000;">
                <td style="padding: 3px; border: 1px solid #000;">{{ Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fehca_inicio_guard)->format('d-m-Y') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">{{ optional($cotizacion->DocCotizacion->Asignaciones->Proveedor)->nombre ?? '-' }}</td>
                <td style="padding: 3px; border: 1px solid #000;">{{ $cotizacion->DocCotizacion->num_contenedor }}</td>
                <td style="padding: 3px; border: 1px solid #000; color: #020202; background: yellow;">{{ $cotizacion->id_subcliente ? $cotizacion->Subcliente->nombre : '' }}</td>
                <td style="padding: 3px; border: 1px solid #000; color: #ffffff; background: #2778c4;">{{ $cotizacion->destino }}</td>
                <td style="padding: 3px; border: 1px solid #000;">{{ $cotizacion->peso_contenedor }}</td>
                <td style="padding: 3px; border: 1px solid #000;">{{ $cotizacion->tamano }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($cotizacion->burreo, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($cotizacion->maniobra, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($cotizacion->precio_tonelada, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($cotizacion->otro, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($cotizacion->precio, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($cotizacion->precio_viaje, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($base_factura, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($iva, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($retencion, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($base_taref, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($total_oficial, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($base_taref, 2, '.', ',') }}</td>
                <td style="padding: 3px; border: 1px solid #000;">$ {{ number_format($importe_vta, 2, '.', ',') }}</td>
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
                <h3 class="sin_margem" style="color: #fff; background: rgb(24, 192, 141);">Cuentas Bancarias Proveedores</h3>
                <table class="table text-white tabla-completa sin_margem" style="color: #000; width: 100%; padding: 0px; font-size: 12px; border-collapse: collapse;">
    <tbody style="text-align: left; font-size: 100%;">
        @foreach ($proveedoresConCuentas as $proveedor)
            @php
                $totalCuenta1 = 0;
                $totalCuenta2 = 0;
                $totalCuenta3 = 0;

                // Cálculo de montos a pagar por cuenta
                if (isset($cotizacionesPorProveedor[$proveedor->id])) {
                    $cotizacionesProveedor = $cotizacionesPorProveedor[$proveedor->id];
                    foreach ($cotizacionesProveedor as $cotizacion) {
                        $cuenta1 = $cotizacion->base_factura + $cotizacion->iva - $cotizacion->retencion;
                        $cuenta2 = $cotizacion->total - $cotizacion->base_factura - $cotizacion->iva + $cotizacion->retencion;
                        $totalCuenta1 += $cuenta1;
                        $totalCuenta2 += $cuenta2;
                        $totalCuenta3 += 0; // Si aplica
                    }
                }
            @endphp

            <!-- Fila para el nombre del proveedor -->
            <tr>
                <td colspan="3" style="padding: 4px; text-align: left; background-color: #f5f5f5; font-weight: bold;font-size: 8px;">
                    Proveedor: {{ $proveedor->nombre }}
                </td>
            </tr>

            <!-- Fila con las cuentas bancarias -->
            @if ($proveedor->CuentasBancarias->isEmpty())
                <!-- Si no hay cuentas -->
                <tr>
                    <td colspan="3" style="padding: 8px; text-align: left;font-size: 10px; border-bottom: 1px solid #ccc;">
                        No hay cuentas bancarias registradas.
                    </td>
                </tr>
            @else
                @php
                    $contador = 1;
                @endphp
                <tr>
                    @foreach ($proveedor->CuentasBancarias as $cuenta)
                        <td style="padding: 8px; text-align: left; border: 1px solid #ccc; font-size: 8px;" width="25">
                            <b>Cuenta #{{ $contador }}</b><br>
                            Beneficiario: {{ $cuenta->nombre_beneficiario }}<br>
                            Banco: {{ $cuenta->nombre_banco }}<br>
                            Cuenta: {{ $cuenta->cuenta_bancaria }}<br>
                            Clabe: <b>{{ $cuenta->cuenta_clabe }}</b><br>
                            @if ($contador == 1)
                                A pagar: <b>${{ number_format($totalCuenta1, 2, '.', ',') }}</b>
                            @elseif ($contador == 2)
                                A pagar: <b>${{ number_format($totalCuenta2, 2, '.', ',') }}</b>
                            @elseif ($contador == 3)
                                A pagar: <b>${{ number_format($totalCuenta3, 2, '.', ',') }}</b>
                            @endif
                        </td>
                        @php
                            $contador++;
                        @endphp
                    @endforeach
                </tr>
            @endif
        @endforeach
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