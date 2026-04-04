<!DOCTYPE html>
<html>
    @php
        use Carbon\Carbon;
    @endphp

    @if (! isset($isExcel))
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
                font-size: 12px;
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

            .container1 {
                display: flex;
                justify-content: space-between;
                /* Distribuye los elementos entre los extremos */
                align-items: center;
                /* Centra los elementos verticalmente */
                height: 100%;
                padding: 0 10px;
                /* Agrega algo de espacio a los lados */
            }

            .left-element,
            .right-element {
                padding: 10px;
                background-color: lightgray;
                border-radius: 5px;
            }

            table {
                font-family: Arial, sans-serif;
                font-size: 9px;
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

            .my-table {
                border: none !important;
                /* Quita el borde de la tabla */
                border-collapse: collapse !important;
                /* Elimina los bordes entre celdas */
                font-size: 14px !important;
                text-align: left;
                background-color: lightgray;
                border-radius: 10px;
            }

            .my-table td {
                border: none !important;
                /* Quita el borde de las celdas */
            }
        </style>
    @endif

    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Resultados</title>
    </head>

    <body>
        <table class="my-table">
            <tr>
                <td align="left">
                    <div class="left-element">
                        <h2>Reporte de Resultados</h2>
                        <h4>Empresa: {{ $user->Empresa->nombre }}</h4>

                        <h5>
                            Periodo: {{ date('d-m-Y', strtotime($fechaInicio)) }} al
                            {{ date('d-m-Y', strtotime($fechaFin)) }}
                        </h5>
                        <h5>Contenedores seleccionados: {{ $selectedRows }} de {{ $totalRows }}</h5>
                    </div>
                </td>
                <td align="right">
                    <div class="right-element">
                        <h3 style="font-weight: bold !important">
                            Utilidad Bruta: ${{ number_format($utilidad, 2) }}
                        </h3>
                        <h3 style="font-weight: bold !important">Otros Gastos: ${{ number_format($gastos, 2) }}</h3>
                        <h3 style="font-weight: bold !important">
                            Utilidad Neta: ${{ number_format($utilidad - $gastos, 2) }}
                        </h3>
                    </div>
                </td>
            </tr>
        </table>

        <table
            class="table text-white tabla-completa"
            style="
                color: #000;
                width: 100%;
                padding: 5px;
                margin: 0px;
                border-collapse: collapse;
                border: 1px solid #000;
            "
        >
            <thead>
                <tr>
                    <th>Núm Contenedor</th>
                    <th>Cliente</th>
                    <th>Precio Viaje</th>
                    <th>Pago Operación</th>
                    <th>Gastos Extra</th>
                    <th>Dinero Viaje S/Justificar</th>
                    <th>Gastos Viaje</th>
                    <th>Gastos Diferidos</th>
                    <th>Utilidad</th>
                    <th>Transportado Por</th>
                </tr>
            </thead>
            <tbody style="text-align: center; font-size: 100%">
                @foreach ($cotizaciones as $cotizacion)
                    <tr>
                        <td>{{ $cotizacion['numContenedor'] }}</td>
                        <td>{{ $cotizacion['cliente'] }}</td>
                        <td>$ {{ number_format($cotizacion['precioViaje'], 2) }}</td>
                        <td>$ {{ number_format($cotizacion['pagoOperacion'], 2) }}</td>
                        <td>$ {{ number_format($cotizacion['gastosExtra'], 2) }}</td>
                        <td>$ {{ number_format($cotizacion['dineroViajeSinJustificar'], 2) }}</td>
                        <td>$ {{ number_format($cotizacion['gastosViaje'], 2) }}</td>
                        <td>$ {{ number_format($cotizacion['gastosDiferidos'], 2) }}</td>
                        <td @if ($cotizacion['utilidad'] < 0) class="bg-warning" @endif>
                            $ {{ number_format($cotizacion['utilidad'], 2) }}
                        </td>
                        <td>{{ $cotizacion['transportadoPor'] }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot style="text-align: center; font-size: 14px; font-weight: bold">
                <tr style="text-align: center; font-size: 14px; font-weight: bold">
                    <td colspan="2">TOTAL</td>

                    <td>$ {{ number_format($cotizaciones->sum('precioViaje'), 2) }}</td>
                    <td>$ {{ number_format($cotizaciones->sum('pagoOperacion'), 2) }}</td>
                    <td>$ {{ number_format($cotizaciones->sum('gastosExtra'), 2) }}</td>
                    <td>$ {{ number_format($cotizaciones->sum('dineroViajeSinJustificar'), 2) }}</td>
                    <td>$ {{ number_format($cotizaciones->sum('gastosViaje'), 2) }}</td>
                    <td>$ {{ number_format($cotizaciones->sum('gastosDiferidos'), 2) }}</td>
                    <td>${{ number_format($cotizaciones->sum('utilidad'), 2) }}</td>
                    <td></td>
                </tr>
            </tfoot>
        </table>

        <h4 style="margin-top: 20px">Desglose de Otros Gastos</h4>
        <table style="width: 100%; font-size: 10px; border-collapse: collapse" border="1">
            <thead style="background-color: #f0f0f0">
                <tr>
                    <th style="padding: 5px">Fecha</th>
                    <th style="padding: 5px">Motivo</th>
                    <th style="padding: 5px">Categoría</th>
                    <th style="padding: 5px">Método de Pago</th>
                    <th style="padding: 5px">Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($gastosGenerales as $gasto)
                    <tr>
                        <td style="padding: 5px">{{ \Carbon\Carbon::parse($gasto->fecha)->format('d-m-Y') }}</td>
                        <td style="padding: 5px">{{ $gasto->motivo }}</td>
                        <td style="padding: 5px">{{ $gasto->Categoria->categoria ?? 'N/A' }}</td>
                        <td style="padding: 5px">{{ $gasto->metodo_pago1 }}</td>
                        <td style="padding: 5px">$ {{ number_format($gasto->monto1, 2) }}</td>
                    </tr>
                @endforeach

                <tr style="font-weight: bold">
                    <td colspan="4" style="text-align: right; padding: 5px">Total:</td>
                    <td style="padding: 5px">$ {{ number_format($gastos, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </body>
</html>
