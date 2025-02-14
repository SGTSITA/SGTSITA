<!DOCTYPE html>
<html>
@php
    use Carbon\Carbon;
@endphp
@if (!isset($isExcel))
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 8px;
            /* Tamaño de fuente reducido */
            margin: 0;
            padding: 0;
        }

        .registro-contenedor {
            border: 2px solid #000;
            /* Cambia el color y grosor del borde según tus necesidades */
            margin-bottom: 20px;
            /* Espacio entre cada registro */
            padding: 15px;
            /* Espacio interno alrededor de las tablas */
            border-radius: 5px;
            /* Bordes redondeados, opcional */
        }

        .registro-contenedor table {
            margin-bottom: 10px;
            /* Espacio entre tablas dentro del mismo contenedor */
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
        <h5 style="position: absolute;left:75%;">Documentos: {{ date('d-m-Y') }}</h5><br>
    </div>

    <table class="table text-white tabla-completa" style="color: #000;width: 100%;padding: 30px; font-size: 12px">
        <thead>
            <tr>
                <th># Contenedor</th>
                <th>Formato CCP</th>
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
                    <td>{{ $cotizacion->num_contenedor }}</td>
                    <td>
                        <div class="form-check">
                            @if ($cotizacion->doc_ccp == null)
                                <input class="form-check-input" type="checkbox" disabled>
                            @else
                                <input class="form-check-input" type="checkbox" checked disabled>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="form-check">
                            @if ($cotizacion->boleta_liberacion == null)
                                <input class="form-check-input" type="checkbox" disabled>
                            @else
                                <input class="form-check-input" type="checkbox" checked disabled>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="form-check">
                            @if ($cotizacion->doda == null)
                                <input class="form-check-input" type="checkbox" disabled>
                            @else
                                <input class="form-check-input" type="checkbox" checked disabled>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="form-check">
                            @if ($cotizacion->carta_porte == null)
                                <input class="form-check-input" type="checkbox" disabled>
                            @else
                                <input class="form-check-input" type="checkbox" checked disabled>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="form-check">
                            @if ($cotizacion->boleta_vacio == null)
                                <input class="form-check-input" type="checkbox" disabled>
                            @else
                                <input class="form-check-input" type="checkbox" checked disabled>
                            @endif
                        </div>
                    </td>
                    <td>
                        <div class="form-check">
                            @if ($cotizacion->doc_eir == null)
                                <input class="form-check-input" type="checkbox" disabled>
                            @else
                                <input class="form-check-input" type="checkbox" checked disabled>
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>

</html>
