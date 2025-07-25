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
        <h5 style="position: absolute;left:80%;top:-10%;font-size: 10px; margin-top: 5px;">Cuentas por Cobrar :
            {{ date('d-m-Y') }}</h5><br>
    </div>

    <table class="table text-white tabla-completa"
        style="color: #000; width: 100%; padding: 5px; margin: 0px; border-collapse: collapse; border: 1px solid #000;">
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
                <th style="padding: 2px; border: 1px solid #000;">Maniobra</th>
                <th style="padding: 2px; border: 1px solid #000;">Estadia</th>
                <th style="padding: 2px; border: 1px solid #000;">Sobre peso</th>
                <th style="padding: 2px; border: 1px solid #000;">Otro</th>
                <th style="padding: 2px; border: 1px solid #000;">Precio venta</th>
                <th style="padding: 2px; border: 1px solid #000;">Precio viaje</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7;">Base factura</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7;">IVA</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #56d1f7;">Retención</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: yellow;">Base 1</th>
                <th style="padding: 2px; border: 1px solid #000; color: #000000; background: #fb6340;">Base 2
                </th>
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
                    $total_oficial = $base_factura + $iva - $retencion;
                    $base_taref = $total - $base_factura - $iva + $retencion;
                    $importe_vta = $base_taref + $total_oficial;
                    $totalOficialSum += $total_oficial;
                    $totalnoofi += $base_taref;
                    $importeVtaSum += $importe_vta;
                @endphp
                <tr style="font-size: 7px; border: 1px solid #000;">
                    <td style="padding: 2px; border: 1px solid #000;">
                        @php
                            $fechaInicio = optional(optional(optional($cotizacion->DocCotizacion)->Asignaciones))
                                ->fehca_inicio_guard;
                        @endphp
                        {{ $fechaInicio ? \Carbon\Carbon::parse($fechaInicio)->format('d-m-Y') : 'Sin fecha' }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000;">
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
                        @endphp

                    <td style="padding: 2px; border: 1px solid #000;">{{ $numContenedor }}</td>


                    <td style="padding: 2px; border: 1px solid #000; color: #020202; background: yellow;">
                        {{ $cotizacion->id_subcliente && $cotizacion->Subcliente ? $cotizacion->Subcliente->nombre : 'N/A' }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000; color: #ffffff; background: #2778c4;">
                        {{ $cotizacion->destino }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">{{ $cotizacion->peso_contenedor }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">{{ $cotizacion->tamano }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$
                        {{ number_format($cotizacion->burreo, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$
                        {{ number_format($cotizacion->maniobra, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">
                        ${{ number_format($cotizacion->estadia, 2, '.', ',') }}</td>
                    @php
                        $sobrepeso = ($cotizacion->peso_contenedor ?? 0) - ($cotizacion->peso_reglamentario ?? 0);
                        $sobrepeso_calc = $sobrepeso * ($cotizacion->precio_sobre_peso ?? 0);
                    @endphp

                    <td style="padding: 2px; border: 1px solid #000;">$
                        {{ number_format($sobrepeso_calc, 2, '.', ',') }}
                    </td>

                    <td style="padding: 2px; border: 1px solid #000;">$
                        {{ number_format($cotizacion->otro, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$
                        {{ number_format($cotizacion->precio, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$
                        {{ number_format($cotizacion->precio_viaje, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($base_factura, 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($iva, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($retencion, 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000;">$
                        {{ number_format($total_oficial, 2, '.', ',') }}</td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($base_taref, 2, '.', ',') }}
                    </td>
                    <td style="padding: 2px; border: 1px solid #000;">$ {{ number_format($importe_vta, 2, '.', ',') }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>




    @php
        use App\Models\Bancos;
        $empresaActual = auth()->user()->id_empresa;

        // Recopila los IDs de los proveedores únicos de las cotizaciones, excluyendo NULL
        $proveedoresIds = $cotizaciones->pluck('DocCotizacion.Asignaciones.id_proveedor')->filter()->unique();

        // Carga los proveedores con sus cuentas bancarias usando los IDs recopilados
        $proveedoresConCuentas = App\Models\Proveedor::whereIn('id', $proveedoresIds)->with('CuentasBancarias')->get();

        $cotizacionesPorProveedor = $cotizaciones->groupBy('DocCotizacion.Asignaciones.id_proveedor');
    @endphp

    <h3 class="sin_margem"
        style="color: #fff; background: rgb(24, 192, 141); margin-top: 0px; padding: 0px; font-size: 10px;">
        Cuentas Bancarias Proveedores
    </h3>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%;">

        <!-- Contenedor de la Tabla de Totales Oficiales -->
        @php
            $empresaActual = auth()->user()->id_empresa;
        @endphp

        <div style="width: 48%; padding: 0; box-sizing: border-box;">
            <h3
                style="text-align: center; font-size: 8px; color: white; background-color: #6289b1; margin: 2px 0; padding: 2px;">
                Base 1
            </h3>
            <table class="table text-white"
                style="color: #000; width: 100%; padding: 2px; font-size: 6px; border-collapse: collapse;">
                @if ($empresaActual == 6 || $empresaActual == 2)
                    @php
                        $bancoId = $empresaActual == 6 ? 4 : 6;
                        $cuentaCLABE = Bancos::withTrashed()->find($bancoId);
                        $beneficiarioCuenta1 = $cuentaCLABE->nombre_beneficiario ?? 'No disponible';
                        $totalFacturaProveedor = 0;
                        $facturadosPorProveedor = [];

                        foreach ($cotizaciones as $cotizacion) {
                            $cuenta1 = $cotizacion->base_factura + $cotizacion->iva - $cotizacion->retencion;
                            $totalFacturaProveedor += $cuenta1;

                            $facturadoA = $cotizacion->Subcliente->nombre ?? 'N/A';
                            if (!isset($facturadosPorProveedor[$facturadoA])) {
                                $facturadosPorProveedor[$facturadoA] = 0;
                            }
                            $facturadosPorProveedor[$facturadoA] += $cuenta1;
                        }
                    @endphp

                    <thead>
                        <tr style="font-size: 7px; border: 1px solid #000; background-color: #2c3e50; color: white;">
                            <th style="padding: 2px; border: 1px solid #000;">Cuenta Oficial Empresa
                                {{ $empresaActual }}</th>
                            <th style="padding: 2px; border: 1px solid #000;">Total</th>
                            @foreach ($facturadosPorProveedor as $facturadoA => $total)
                                <th style="padding: 2px; border: 1px solid #000;">{{ $facturadoA }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody style="text-align: center; font-size: 6px;">
                        <tr style="background-color: #f1f1f1;">
                            <td style="padding: 2px; border: 1px solid #ccc;">
                                {{ $beneficiarioCuenta1 }}<br>
                                {{ $cuentaCLABE->nombre_banco ?? '-' }}<br>
                                No. {{ $cuentaCLABE->clabe ?? '-' }}<br>
                            </td>
                            <td style="padding: 2px; border: 1px solid #ccc;">
                                ${{ number_format($totalFacturaProveedor, 2, '.', ',') }}
                            </td>
                            @foreach ($facturadosPorProveedor as $total)
                                <td style="padding: 2px; border: 1px solid #ccc;">
                                    ${{ number_format($total, 2, '.', ',') }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                @else
                    @php
                        $proveedoresIds = $cotizaciones
                            ->pluck('DocCotizacion.Asignaciones.id_proveedor')
                            ->filter()
                            ->unique();
                        $proveedoresConCuentas = App\Models\Proveedor::whereIn('id', $proveedoresIds)
                            ->with('CuentasBancarias')
                            ->get();
                        $cotizacionesPorProveedor = $cotizaciones->groupBy('DocCotizacion.Asignaciones.id_proveedor');
                    @endphp

                    @foreach ($proveedoresConCuentas as $index => $proveedor)
                        @php
                            $totalFacturaProveedor = 0;
                            $facturadosPorProveedor = [];
                            $beneficiarioCuenta1 = '';
                            $cuentaCLABE = null;

                            if (isset($cotizacionesPorProveedor[$proveedor->id])) {
                                $cotizacionesProveedor = $cotizacionesPorProveedor[$proveedor->id];
                                foreach ($cotizacionesProveedor as $cotizacion) {
                                    $cuenta1 = $cotizacion->base_factura + $cotizacion->iva - $cotizacion->retencion;
                                    $totalFacturaProveedor += $cuenta1;

                                    $facturadoA = $cotizacion->Subcliente->nombre ?? 'N/A';
                                    if (!isset($facturadosPorProveedor[$facturadoA])) {
                                        $facturadosPorProveedor[$facturadoA] = 0;
                                    }
                                    $facturadosPorProveedor[$facturadoA] += $cuenta1;
                                }
                            }

                            if (!$proveedor->CuentasBancarias->isEmpty()) {
                                $cuentaCLABE = $proveedor->CuentasBancarias->first();
                                $beneficiarioCuenta1 = $cuentaCLABE->nombre_beneficiario ?? 'No disponible';
                            }
                        @endphp

                        <thead>
                            <tr
                                style="font-size: 7px; border: 1px solid #000; background-color: #2c3e50; color: white;">
                                <th style="padding: 2px; border: 1px solid #000;">Proveedor - {{ $proveedor->nombre }}
                                </th>
                                <th style="padding: 2px; border: 1px solid #000;">Total</th>
                                @foreach ($facturadosPorProveedor as $facturadoA => $total)
                                    <th style="padding: 2px; border: 1px solid #000;">{{ $facturadoA }}</th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody style="text-align: center; font-size: 6px;">
                            <tr style="background-color: {{ $index % 2 == 0 ? '#f1f1f1' : '#e0e0e0' }};">
                                <td style="padding: 2px; border: 1px solid #ccc;">
                                    {{ $beneficiarioCuenta1 }}<br>
                                    {{ $cuentaCLABE->nombre_banco ?? '-' }}<br>
                                    No. {{ $cuentaCLABE->cuenta_clabe ?? '-' }}
                                </td>
                                <td style="padding: 2px; border: 1px solid #ccc;">
                                    ${{ number_format($totalFacturaProveedor, 2, '.', ',') }}
                                </td>
                                @foreach ($facturadosPorProveedor as $total)
                                    <td style="padding: 2px; border: 1px solid #ccc;">
                                        ${{ number_format($total, 2, '.', ',') }}
                                    </td>
                                @endforeach
                            </tr>
                        </tbody>
                    @endforeach
                @endif
            </table>
        </div>



        <!-- Contenedor de la Tabla de Totales No Oficiales -->
        <div style="width: 48%; padding: 0; box-sizing: border-box;">
            <h3
                style="text-align: center; font-size: 8px; color: white; background-color: #0080ff; margin: 2px 0; padding: 2px;">
                Base 2
            </h3>

            @php
                $empresaActual = auth()->user()->id_empresa;
                $bancoGlobal = Bancos::where('cuenta_global', 1)->first();

                $cuentaGlobal = $bancoGlobal
                    ? [
                        'beneficiario' => $bancoGlobal->nombre_beneficiario ?? '---',
                        'banco' => $bancoGlobal->nombre_banco ?? '---',
                        'clabe' => $bancoGlobal->clabe ?? '---',
                    ]
                    : [
                        'beneficiario' => '---',
                        'banco' => '---',
                        'clabe' => '---',
                    ];

                $subclientesUnicos = [];
                $totalesPorProveedor = [];

                foreach ($cotizaciones as $cotizacion) {
                    $proveedorId = optional(optional($cotizacion->DocCotizacion)->Asignaciones)->id_proveedor;
                    $baseTaref =
                        floatval($cotizacion->total ?? 0) -
                        floatval($cotizacion->base_factura ?? 0) -
                        floatval($cotizacion->iva ?? 0) +
                        floatval($cotizacion->retencion ?? 0);
                    $subcliente = $cotizacion->Subcliente->nombre ?? 'N/A ';

                    if (!isset($totalesPorProveedor[$proveedorId])) {
                        $totalesPorProveedor[$proveedorId] = [
                            'nombre' =>
                                optional(optional($cotizacion->DocCotizacion)->Asignaciones)->Proveedor->nombre ??
                                'Proveedor desconocido',
                            'total' => 0,
                            'subclientes' => [],
                        ];
                    }

                    $totalesPorProveedor[$proveedorId]['total'] += $baseTaref;
                    $totalesPorProveedor[$proveedorId]['subclientes'][$subcliente] =
                        ($totalesPorProveedor[$proveedorId]['subclientes'][$subcliente] ?? 0) + $baseTaref;
                    $subclientesUnicos[$subcliente] = true;
                }

                $subclientesLista = array_keys($subclientesUnicos);
            @endphp

            <table class="table text-white"
                style="color: #000; width: 100%; padding: 0; font-size: 7px; border-collapse: collapse;">
                <thead>
                    <tr style="font-size: 7px; background-color: #2c3e50; color: white;">
                        <th style="padding: 2px; border: 1px solid #000;">Cuenta Global</th>
                        <th style="padding: 2px; border: 1px solid #000;">Proveedor</th>
                        <th style="padding: 2px; border: 1px solid #000;">Total</th>
                        @foreach ($subclientesLista as $subcliente)
                            <th style="padding: 2px; border: 1px solid #000;">{{ $subcliente }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($totalesPorProveedor as $index => $prov)
                        <tr style="background-color: {{ $loop->odd ? '#f1f1f1' : '#e0e0e0' }};">
                            @if (isset($isExcel))
                                <td style="padding: 2px; border: 1px solid #ccc; text-align: center;">
                                    {{ $cuentaGlobal['beneficiario'] }}<br>
                                    {{ $cuentaGlobal['banco'] }}<br>
                                    No. {{ $cuentaGlobal['clabe'] }}
                                </td>
                            @elseif ($loop->first)
                                <td rowspan="{{ count($totalesPorProveedor) }}"
                                    style="padding: 2px; border: 1px solid #ccc; text-align: center; vertical-align: middle;">
                                    {{ $cuentaGlobal['beneficiario'] }}<br>
                                    {{ $cuentaGlobal['banco'] }}<br>
                                    No. {{ $cuentaGlobal['clabe'] }}
                                </td>
                            @endif

                            <td style="padding: 2px; border: 1px solid #ccc;">{{ $prov['nombre'] }}</td>
                            <td style="padding: 2px; border: 1px solid #ccc;">
                                ${{ number_format($prov['total'], 2, '.', ',') }}
                            </td>
                            @foreach ($subclientesLista as $subcliente)
                                <td style="padding: 2px; border: 1px solid #ccc;">
                                    @php
                                        $monto = $prov['subclientes'][$subcliente] ?? 0;
                                    @endphp
                                    {{ $monto > 0 ? '$' . number_format($monto, 2, '.', ',') : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>


    </div>





    <div class="totales">
        <h3 class="margin_cero" style="color: #000000; background: rgb(0, 174, 255);margin-top: 5px;">Totales</h3>
        <p class="margin_cero">Total oficial: <b class="margin_cero;margin-top: 5px;">
                ${{ number_format($totalOficialSum, 2, '.', ',') }} </b></p>
        <p class="margin_cero">Total no oficial: <b class="margin_cero;margin-top: 5px;">
                ${{ number_format($totalnoofi, 2, '.', ',') }} </b></p>
        <p class="margin_cero">Importe vta: <b class="margin_cero;margin-top: 5px;">
                ${{ number_format($importeVtaSum, 2, '.', ',') }} </b></p>
    </div>

</body>

</html>
