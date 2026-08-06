<!DOCTYPE html>
<html>
    @php
        use Carbon\Carbon;
    @endphp

    @if (! isset($isExcel))
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 14px; /* Tamaño de fuente reducido */
                margin: 0;
                padding: 0;
            }

            /* Eliminar el bold en todos los elementos */
            h3,
            p,
            th,
            td {
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

            .container1 {
                display: flex;
                justify-content: space-between; /* Distribuye los elementos entre los extremos */
                align-items: center; /* Centra los elementos verticalmente */
                height: 100%;
                padding: 0 10px; /* Agrega algo de espacio a los lados */
            }

            .left-element,
            .right-element {
                padding: 10px;
                background-color: lightgray;
                border-radius: 5px;
            }

            table {
                font-family: Arial, sans-serif;
                font-size: 9px; /* Tamaño de fuente reducido */
                width: 100%;
                border-collapse: collapse;
                margin-top: 0px;
            }

            th,
            td {
                border: 1px solid #000;
                padding: 4px; /* Reducir el espacio de las celdas */
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
                border: none !important; /* Quita el borde de la tabla */
                border-collapse: collapse !important; /* Elimina los bordes entre celdas */
                font-size: 14px !important;
                text-align: left;
                background-color: lightgray;
                border-radius: 10px;
            }

            .my-table td {
                border: none !important; /* Quita el borde de las celdas */
            }
        </style>
    @endif

    <head>
        <title>Pago Operador</title>
    </head>

    <body>
        <table class="my-table">
            <tr>
                <td align="left">
                    <div class="left-element">
                        <h2>COMPROBANTE DE PAGO</h2>
                        <h4>Empresa: {{ $user->Empresa->nombre }}</h4>
                        <h4>Operador: {{ $liquidacion->operadores->nombre }}</h4>
                        <h5>Fecha Pago: {{ date('d/m/Y', strtotime($liquidacion->fecha)) }}</h5>
                    </div>
                </td>
                <td align="right">
                    <div class="right-element">
                        <table width="200">
                            <tbody>
                                <tr>
                                    <td align="right">Viajes Realizados</td>
                                    <td align="right" width="70">{{ $liquidacion->viajes_realizados }}</td>
                                </tr>
                                <tr>
                                    <td align="right">Sueldo Viajes</td>
                                    <td align="right">$ {{ number_format($liquidacion->sueldo_operador, 2) }}</td>
                                </tr>
                                <tr>
                                    <td align="right">Dinero Viajes</td>
                                    <td align="right">- $ {{ number_format($liquidacion->dinero_viaje, 2) }}</td>
                                </tr>
                                <tr>
                                    <td align="right">Dinero Justificado</td>
                                    <td align="right">+ $ {{ number_format($liquidacion->dinero_justificado, 2) }}</td>
                                </tr>
                                <tr>
                                    <td align="right">Prestamos</td>
                                    <td align="right">- $ {{ number_format($liquidacion->pago_prestamos, 2) }}</td>
                                </tr>
                                <tr>
                                    <td align="right"><h2>Total a Pagar</h2></td>
                                    <td align="right"><h2>$ {{ number_format($liquidacion->total_pago, 2) }}</h2></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>
        </table>

        <h3>DETALLE DE VIAJES PAGADOS</h3>

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
                    <th>Sueldo Operador</th>
                    <th>Dinero Viaje</th>
                    <th>Dinero Justificado</th>
                    <th>Total Pago</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($liquidacion->viajes as $v)
                    <tr>
                        <td>{{ $v->contenedores->num_contenedor }}</td>
                        <td align="right">$ {{ number_format($v->sueldo_operador, 2) }}</td>
                        <td align="right">$ {{ number_format($v->dinero_viaje, 2) }}</td>
                        <td align="right">$ {{ number_format($v->dinero_justificado, 2) }}</td>
                        <td align="right">$ {{ number_format($v->total_pagado, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>DINERO PARA VIAJE</h3>
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
                    <th>Contenedor</th>
                    <th>Descripción Gasto</th>
                    <th>Monto</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dineroViaje as $v)
                    <tr>
                        <td>{{ $v->DocCotizacion->num_contenedor ?? 0 }}</td>
                        <td>{{ $v->motivo }}</td>
                        <td align="right">$ {{ number_format($v->monto, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <h3>DINERO VIAJE JUSTIFICADO</h3>
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
                    <th>Contenedor</th>
                    <th>Descripción Gasto</th>
                    <th>Sueldo Operador</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($viaticos as $v)
                    <tr>
                        <td>{{ $v->contenedor }}</td>
                        <td>{{ $v->descripcion_gasto }}</td>
                        <td align="right">$ {{ number_format($v->monto, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </body>
</html>
