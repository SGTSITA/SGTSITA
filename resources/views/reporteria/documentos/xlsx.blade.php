<!DOCTYPE html>
<html>
    @php
        use Carbon\Carbon;
    @endphp

    @if (! isset($isExcel))
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
                border: 2px solid #000;
            }

            th,
            td {
                border: 1px solid #000;
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
                display: flex;
                justify-content: center;
                align-items: center;
                width: 20px;
                height: 20px;
                border-radius: 50%;
                margin: auto;
                font-size: 14px;
                font-weight: bold;
                color: white;
            }

            .checked {
                background-color: green;
            }

            .unchecked {
                background-color: red;
            }
        </style>
    @endif

    <head>
        <title>Estatus Documentos</title>
    </head>

    <body>
        @php
            $empresaActual = $user?->Empresa ?? null;
            $esEmpresaGlobal = !empty($separarUnidades);
        @endphp

        <h4>Empresa: {{ optional($user?->Empresa)->nombre ?? 'SGT' }}</h4>
        <h4>Estatus Documentos</h4>
        <h5 style="text-align: right">Fecha: {{ date('d-m-Y') }}</h5>

        @php
            $renderEstadoExcel = function ($eir, $cima) {
                if ((int) $cima === 1) {
                    return 'CIMA';
                }
                if ($eir) {
                    return '✔';
                }
                return '✖';
            };
        @endphp

        @if (!$esEmpresaGlobal)
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
                            <td>{{ $cotizacion['doc_ccp'] ? '✔' : '✖' }}</td>
                            <td>{{ $cotizacion['boleta_liberacion'] ? '✔' : '✖' }}</td>
                            <td>{{ $cotizacion['doda'] ? '✔' : '✖' }}</td>
                            <td>{{ $cotizacion['carta_porte'] ? '✔' : '✖' }}</td>
                            <td>{{ $cotizacion['boleta_vacio'] ? '✔' : '✖' }}</td>
                            <td>
                                @php
                                    $isFull = $cotizacion['tipo'] === 'Full';

                                    $eir1 = $isFull ? $cotizacion['eir_primario'] ?? false : $cotizacion['doc_eir'] ?? false;
                                    $eir2 = $isFull ? $cotizacion['eir_secundario'] ?? false : null;

                                    $cima1 = $isFull ? $cotizacion['cima_primario'] ?? 0 : $cotizacion['cima'] ?? 0;
                                    $cima2 = $isFull ? $cotizacion['cima_secundario'] ?? 0 : null;
                                @endphp

                                @if ($isFull)
                                    {{ $renderEstadoExcel($eir1, $cima1) }} / {{ $renderEstadoExcel($eir2, $cima2) }}
                                @else
                                    {{ $renderEstadoExcel($eir1, $cima1) }}
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            @php
                $documentosPorUnidad = collect($cotizaciones)->groupBy(function($item) {
                    return $item['unidad'] ?? 'SIN UNIDAD ASIGNADA';
                });
            @endphp

            @foreach ($documentosPorUnidad as $unidadTexto => $itemsUnidad)
                <table style="margin-bottom: 10px;" border="1">
                    <thead>
                        <tr>
                            <th colspan="7" style="background-color: #2c3e50; color: #ffffff; text-align: left; font-weight: bold;">
                                🚛 UNIDAD / TRACTOCAMIÓN: {{ $unidadTexto }} ({{ count($itemsUnidad) }} contenedores)
                            </th>
                        </tr>
                        <tr style="background-color: #f2f2f2;">
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
                        @foreach ($itemsUnidad as $cotizacion)
                            <tr>
                                <td>{{ $cotizacion['num_contenedor'] }}</td>
                                <td>{{ $cotizacion['doc_ccp'] ? '✔' : '✖' }}</td>
                                <td>{{ $cotizacion['boleta_liberacion'] ? '✔' : '✖' }}</td>
                                <td>{{ $cotizacion['doda'] ? '✔' : '✖' }}</td>
                                <td>{{ $cotizacion['carta_porte'] ? '✔' : '✖' }}</td>
                                <td>{{ $cotizacion['boleta_vacio'] ? '✔' : '✖' }}</td>
                                <td>
                                    @php
                                        $isFull = $cotizacion['tipo'] === 'Full';

                                        $eir1 = $isFull ? $cotizacion['eir_primario'] ?? false : $cotizacion['doc_eir'] ?? false;
                                        $eir2 = $isFull ? $cotizacion['eir_secundario'] ?? false : null;

                                        $cima1 = $isFull ? $cotizacion['cima_primario'] ?? 0 : $cotizacion['cima'] ?? 0;
                                        $cima2 = $isFull ? $cotizacion['cima_secundario'] ?? 0 : null;
                                    @endphp

                                    @if ($isFull)
                                        {{ $renderEstadoExcel($eir1, $cima1) }} / {{ $renderEstadoExcel($eir2, $cima2) }}
                                    @else
                                        {{ $renderEstadoExcel($eir1, $cima1) }}
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endforeach
        @endif
    </body>
</html>
