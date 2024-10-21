<!DOCTYPE html>
<html>
    @php
        use Carbon\Carbon;
    @endphp
    @if(!isset($isExcel)) 
    <style>
        .registro-contenedor {
            border: 2px solid #000; /* Cambia el color y grosor del borde según tus necesidades */
            margin-bottom: 20px; /* Espacio entre cada registro */
            padding: 15px; /* Espacio interno alrededor de las tablas */
            border-radius: 5px; /* Bordes redondeados, opcional */
        }

        .registro-contenedor table {
            margin-bottom: 10px; /* Espacio entre tablas dentro del mismo contenedor */
        }

        .totales {
            margin-top: 20px;
        }

        .totales h3 {
            font-weight: bold;
        }

        .totales p {
            font-size: 1.2em;
            color: #000;
        }
    </style>
    @endif
    <head>
        <title>Utilidades</title>
    </head>

    <body>

            <div class="contianer" style="position: relative">
                <h4>Empresa: {{ $user->Empresa->nombre }}</h4>
                <h4>Utilidades</h4>
            </div>
            <div class="contianer" style="position: relative">
                <h5 style="position: absolute;left:75%;">Utilidades: {{ date("d-m-Y") }}</h5><br>
            </div>

            <table class="table text-white tabla-completa" style="color: #000;width: 100%;padding: 30px; font-size: 12px">
                <thead>
                    <tr>
                        <th>Contenedor</th>
                        <th>Cliente</th>
                        <th>Subcliente</th>
                        <th>Origen</th>
                        <th>Destino</th>
                        <th>Utilidad</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;font-size: 100%;">
                    @foreach ($cotizaciones as $cotizacion)
                        <tr>
                            <td>{{$cotizacion->Contenedor->num_contenedor}}</td>
                            <td>{{$cotizacion->Contenedor->Cotizacion->Cliente->nombre}}</td>
                            <td>
                                @if ($cotizacion->Contenedor->Cotizacion->id_subcliente != NULL)
                                    {{$cotizacion->Contenedor->Cotizacion->Subcliente->nombre}} / {{$cotizacion->Contenedor->Cotizacion->Subcliente->telefono}}
                                @else
                                    -
                                @endif
                            </td>
                            <td>{{$cotizacion->Contenedor->Cotizacion->origen}}</td>
                            <td>{{$cotizacion->Contenedor->Cotizacion->destino}}</td>
                            <td>{{$cotizacion->Contenedor->Cotizacion->estatus}}</td>
                            <td>
                                @php
                                    if($cotizacion->total_proveedor == NULL){
                                        $utilidad = $cotizacion->Contenedor->Cotizacion->total - $cotizacion->pago_operador;
                                    }elseif($cotizacion->total_proveedor != NULL){
                                        $utilidad = $cotizacion->Contenedor->Cotizacion->total - $cotizacion->total_proveedor;
                                    }else{
                                        $utilidad = 0;
                                    }
                                @endphp
                            <b> ${{ number_format($utilidad, 2, '.', ',') }}</b>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <h4>Gatos generales</h4>
            <table class="table text-white tabla-completa" style="color: #000;width: 35%;padding: 30px; font-size: 12px">
                <thead>
                    <tr>
                        <th>Motivo</th>
                        <th>Monto</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;font-size: 100%;">
                    @foreach ($gastos as $gasto)
                        <tr>
                            <td>{{$gasto->motivo}}</td>
                            <td><b> ${{ number_format($gasto->monto1, 2, '.', ',') }} </b></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <table class="table text-white tabla-completa" style="color: #000;width: 35%;padding: 30px; font-size: 12px">
                <thead>
                    <tr>
                        <th>Total <br> Utilidades</th>
                        <th>Total <br> Gastos<</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;font-size: 100%;">
                    @php
                        $utilidades = 0;
                        foreach ($cotizaciones as $cotizacion) {
                            if($cotizacion->total_proveedor == NULL){
                                $utilidad = $cotizacion->Contenedor->Cotizacion->total - $cotizacion->pago_operador;
                            }elseif($cotizacion->total_proveedor != NULL){
                                $utilidad = $cotizacion->Contenedor->Cotizacion->total - $cotizacion->total_proveedor;
                            }else{
                                $utilidad = 0;
                            }

                            $utilidades += $utilidad;
                        }

                        $suma_gastos = 0;
                        foreach ($gastos as $gasto) {
                            $suma_gastos += $gasto->monto1;
                        }

                        $resta = 0;
                        $resta = $utilidades - $suma_gastos;
                    @endphp
                        <tr>
                            <td>${{ number_format($utilidades, 2, '.', ',') }}</td>
                            <td>${{ number_format($suma_gastos, 2, '.', ',') }}</td>
                            <td>${{ number_format($resta, 2, '.', ',') }}</td>
                        </tr>
                </tbody>
            </table>
    </body>
</html>
