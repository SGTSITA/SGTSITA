<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">

    <style>
        body {
            font-family: DejaVu Sans, sans-serif;
            font-size: 12px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }

        th,
        td {
            border: 1px solid #ccc;
            padding: 6px;
        }

        th {
            background: #f2f2f2;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .title {
            background-color: #ecf0f1;
            /* fondo gris claro */
            border-radius: 8px;
            /* bordes redondeados */
            padding: 15px 10px;
            /* espacio interno */
            font-size: 26px;
            font-weight: 700;
            text-align: center;
            color: #2c3e50;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
            /* sombra ligera */
        }

        .section {
            margin-top: 20px;
        }
    </style>

</head>

<body>

    <div class="title">
        Liquidación Operador # {{ $liquidacionViajes->id }}
    </div>

    <table>
        <tr>
            <td><b>Operador</b></td>
            <td>{{ $liquidacionViajes->Operadores->nombre ?? '' }}</td>

            <td><b>Fecha Pago</b></td>
            <td>{{ \Carbon\Carbon::parse($liquidacionViajes->fecha)->format('d/m/Y') }}</td>
        </tr>

        <tr>
            <td><b>Banco</b></td>
            <td>
                {{ $liquidacionViajes->Banco->catBanco->nombre ?? '' }} -
                ****{{ substr($liquidacionViajes->Banco->cuenta_bancaria ?? '', -4) }}
            </td>

            <td><b>Usuario</b></td>
            <td>{{ $user->name }}</td>
        </tr>
    </table>

    {{-- RESUMEN --}}
    <div class="section">
        <b>Resumen</b>

        <table>
            <tr>
                <th>Sueldo</th>
                <th>Dinero Viaje</th>
                <th>Justificado</th>
                <th>Deuda</th>
                <th>Pagado</th>
            </tr>

            <tr>
                <td class="text-end">${{ number_format($totales['sueldo'], 2) }}</td>
                <td class="text-end">${{ number_format($totales['dinero_viaje'], 2) }}</td>
                <td class="text-end">${{ number_format($totales['justificado'], 2) }}</td>
                <td class="text-end">
                    ${{ number_format($totales['deudas'], 2) }}
                </td>
                <td class="text-end">${{ number_format($totales['pagado'], 2) }}</td>
            </tr>
        </table>
    </div>

    {{-- VIAJES --}}
    <div class="section">
        <b>Detalle Viajes</b>

        <table>
            <thead>
                <tr>
                    <th>Contenedores</th>
                    <th class="text-end">Sueldo</th>
                    <th class="text-end">Dinero</th>
                    <th class="text-end">Justificado</th>
                    <th class="text-end">Monto Pago</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($liquidacionViajes->viajes as $viaje)
                    <tr>

                        <td>


                            {{ $viaje->Contenedores->num_contenedor ?? '' }}<br>


                        </td>

                        <td class="text-end">
                            ${{ number_format($viaje->sueldo_operador, 2) }}
                        </td>

                        <td class="text-end">
                            ${{ number_format($viaje->dinero_viaje, 2) }}
                        </td>

                        <td class="text-end">
                            ${{ number_format($viaje->dinero_justificado, 2) }}
                        </td>

                        <td class="text-end">
                            ${{ number_format($viaje->total_pagado, 2) }}
                        </td>

                    </tr>
                @endforeach




            </tbody>
        </table>
    </div>

    {{-- JUSTIFICACIONES --}}
    <div class="section">

        <b>Desglose Gastos Justificados</b>

        <table>

            <thead>
                <tr>
                    <th>Contenedor</th>
                    <th>Concepto</th>
                    <th class="text-end">Importe</th>
                </tr>
            </thead>

            <tbody>

                @foreach ($viaticos as $v)
                    <tr>

                        <td>
                            {{ $v->contenedor }}
                        </td>

                        <td>
                            {{ $v->descripcion_gasto }}
                        </td>

                        <td class="text-end">
                            ${{ number_format($v->monto, 2) }}
                        </td>

                    </tr>
                @endforeach

            </tbody>
        </table>

    </div>

    {{-- DINERO VIAJE --}}
    <div class="section">

        <b>Dinero Viaje</b>

        <table>

            <thead>
                <tr>
                    <th>Contenedor</th>
                    <th>Fecha</th>
                    <th>Concepto</th>
                    <th class="text-end">Importe</th>

                </tr>
            </thead>

            <tbody>

                @foreach ($dineroViaje as $d)
                    <tr>

                        <td>
                            {{ $d->DocCotizacion->num_contenedor ?? '' }}
                        </td>
                        <td>
                            {{ $d->fecha_entrega_monto ? \Carbon\Carbon::parse($d->fecha_entrega_monto)->format('d/m/Y') : '' }}
                        </td>
                        <td>
                            {{ $d->motivo }}
                        </td>

                        <td class="text-end">
                            ${{ number_format($d->monto, 2) }}
                        </td>



                    </tr>
                @endforeach

            </tbody>
        </table>

    </div>

    @if ($prestamosPagados->count() > 0 && $abonoprestamosLiquidacion->count() > 0)
        {{-- PRESTAMOS solo si hubo abonos en esta liquidacion --}}
        <div class="section">

            <b>Deudas (Préstamos / Adelantos)</b>

            <table>

                <thead>
                    <tr>
                        <th>Tipo</th>
                        <th>Fecha</th>
                        <th class="text-end">Cantidad</th>
                        <th class="text-end">Pagado</th>
                        <th class="text-end">Abono</th>
                        <th class="text-end">Saldo Final</th>
                    </tr>
                </thead>

                <tbody>

                    @foreach ($prestamosPagados as $p)
                        <tr>

                            <td>
                                {{ $p->tipo ?? '' }}
                            </td>

                            <td>
                                {{ \Carbon\Carbon::parse($p->fecha_prestamo)->format('d/m/Y') ?? '' }}
                            </td>

                            <td class="text-end">
                                ${{ number_format($p->cantidad ?? 0, 2) }}
                            </td>

                            <td class="text-end">
                                ${{ number_format($p->abonos, 2) }}
                            </td>

                            <td class="text-end">

                                ${{ number_format($abonoprestamosLiquidacion->where('id_prestamo', $p->id)->sum('monto_pago') ?? 0, 2) }}
                            </td>
                            <td class="text-end">
                                ${{ number_format(($p->cantidad ?? 0) - (($p->abonos ?? 0) + ($abonoprestamosLiquidacion->where('id_prestamo', $p->id)->sum('monto_pago') ?? 0)), 2) }}
                            </td>

                        </tr>
                    @endforeach

                </tbody>
            </table>

        </div>
    @endif

</body>

</html>
