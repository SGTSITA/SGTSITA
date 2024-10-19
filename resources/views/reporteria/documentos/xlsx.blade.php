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
            padding: 15px; /* Espacio inter- alrededor de las tablas */
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
        <title>Estatus Documentos</title>
    </head>

    <body>

            <div class="contianer" style="position: relative">
                <h4>Empresa: {{ $user->Empresa->nombre }}</h4>
                <h4>Estatus Documentos</h4>
            </div>
            <div class="contianer" style="position: relative">
                <h5 style="position: absolute;left:75%;">Documentos: {{ date("d-m-Y") }}</h5><br>
            </div>

            <table class="table text-white tabla-completa" style="color: #000;width: 100%;padding: 30px; font-size: 12px">
                <thead>
                    <tr>
                        <th># Contenedor</th>
                        <th>CCP</th>
                        <th>Boleta liberacion</th>
                        <th>Doda</th>
                        <th>Carta porte</th>
                        <th>Boleta vacio</th>
                        <th>EIR</th>
                    </tr>
                </thead>
                <tbody style="text-align: center;font-size: 100%;">
                    @foreach ($cotizaciones as $cotizacion)
                        <tr>
                            <td>{{$cotizacion->num_contenedor}}</td>
                            <td>
                                <div class="form-check">
                                    @if ($cotizacion->doc_ccp == NULL)
                                        -
                                    @else
                                        SI
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    @if ($cotizacion->boleta_liberacion == NULL)
                                        -
                                    @else
                                        SI
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    @if ($cotizacion->doda == NULL)
                                        -
                                    @else
                                        SI
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    @if ($cotizacion->carta_porte == NULL)
                                        -
                                    @else
                                        SI
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    @if ($cotizacion->boleta_vacio == NULL)
                                        -
                                    @else
                                        SI
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="form-check">
                                    @if ($cotizacion->doc_eir == NULL)
                                        -
                                    @else
                                        SI
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
    </body>
</html>
