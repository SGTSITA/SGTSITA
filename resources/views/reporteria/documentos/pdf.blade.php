<!DOCTYPE html>
<html>
@php
    use Carbon\Carbon;
@endphp
@if (!isset($isExcel))
    <style>
        body {
            font-family: Arial, sans-serif, 'Segoe UI Emoji', 'Noto Color Emoji';
            font-size: 10px;
            margin: 0;
            padding: 0px;
        }

        h4,
        h5 {
            color: #333;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
            font-size: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 5px;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        .status-check {
            display: inline-block;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            margin: auto;
        }

        .checked {
            background-color: green;
        }

        .unchecked {
            background-color: red;
        }

        .cima-badge {
            display: inline-block;
            background-color: green;
            color: white;
            border-radius: 10px;
            padding: 2px 8px;
            font-weight: bold;
            font-size: 9px;
        }
    </style>
@endif

<head>
    <title>Estatus Documentos</title>
</head>

<body>

    <h4>Empresa: {{ $user->Empresa->nombre }}</h4>
    <h4>Estatus Documentos</h4>
    <h5 style="text-align: right;">Fecha: {{ date('d-m-Y') }}</h5>

    <table>
        <thead>
            <tr>
                <th># Contenedor</th>
                <th>Formato CCP</th>
                <th>Boleta Liberación</th>
                <th>Doda</th>
                <th>Carta Porte</th>
                <th>Boleta Vacío</th>
                <th>EIR</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cotizaciones as $cotizacion)
                <tr>
                    <td>{{ $cotizacion['num_contenedor'] }}</td>
                    <td><span class="status-check {{ $cotizacion['doc_ccp'] ? 'checked' : 'unchecked' }}"></span></td>
                    <td><span
                            class="status-check {{ $cotizacion['boleta_liberacion'] ? 'checked' : 'unchecked' }}"></span>
                    </td>
                    <td><span class="status-check {{ $cotizacion['doda'] ? 'checked' : 'unchecked' }}"></span></td>
                    <td><span class="status-check {{ $cotizacion['carta_porte'] ? 'checked' : 'unchecked' }}"></span>
                    </td>
                    <td><span class="status-check {{ $cotizacion['boleta_vacio'] ? 'checked' : 'unchecked' }}"></span>
                    </td>
                    <td>
                        @if (isset($cotizacion['cima']) && $cotizacion['cima'] == 1)
                            <div
                                style="background-color: green; color: white; border-radius: 5px; padding: 2px 6px; font-weight: bold; font-size: 9px; display: inline-block; width: 40px;">
                                CIMA
                            </div>
                        @else
                            <span class="status-check {{ $cotizacion['doc_eir'] ? 'checked' : 'unchecked' }}"></span>
                        @endif
                    </td>


                </tr>
            @endforeach
        </tbody>
    </table>

</body>

</html>
