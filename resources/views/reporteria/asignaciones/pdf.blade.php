@php 
 use Carbon\Carbon; 
 use App\Http\Controllers\ReporteriaController;
@endphp

@if (!isset($isExcel))
    <!DOCTYPE html>
    <html>

    <head>
        <title>Reporte de Viajes</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                color: #000;
                font-size: 12px;
                margin: 20px;
            }

            .titulo {
                text-align: center;
                margin-bottom: 20px;
            }

            .datos-empresa {
                display: flex;
                justify-content: space-between;
                margin-bottom: 10px;
            }

            .tabla-viajes {
                width: 100%;
                border-collapse: collapse;
                margin-top: 20px;
            }

            .tabla-viajes th,
            .tabla-viajes td {
                border: 1px solid #000;
                padding: 8px;
                text-align: center;
            }

            .tabla-viajes th {
                background-color: #f2f2f2;
                font-weight: bold;
            }
        </style>
    </head>

    <body>
@endif

@php
    $empresaActual = $user?->Empresa ?? ($cotizacion?->Empresa ?? null);
    $debeSepararUnidades = !empty($separarUnidades);
@endphp

{{-- Empresa y fecha (siempre visibles) --}}
<div style="margin-bottom: 10px;">
    <h4 style="margin: 0;">Empresa: {{ optional($user?->Empresa)->nombre ?? 'SGT' }}</h4>
    <div style="text-align: right;"><strong>Fecha:</strong> {{ date('d-m-Y') }}</div>
    <h4 style="margin-top: 5px;">Viajes</h4>
</div>

@if (!$debeSepararUnidades)
    {{-- MODO TRADICIONAL (Configuración Global = 0): Diseño 100% original e intacto --}}
    <table class="{{ !isset($isExcel) ? 'tabla-viajes' : '' }}">
        <thead>
            <tr>
                <th>Contenedor</th>
                <th>Cliente</th>
                <th>Subcliente</th>
                <th>Origen</th>
                <th>Destino</th>
                <th>Estatus</th>
                <th>Fecha salida</th>
                <th>Fecha llegada</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cotizaciones as $cotizacionItem)
              @php
                $numContenedor = optional($cotizacionItem->Contenedor)->num_contenedor ?? '-';
                $refFull = optional(optional($cotizacionItem->Contenedor)->Cotizacion)->referencia_full;
                $contenedor2 = $refFull ? ReporteriaController::getContenedorSecundario($refFull) : '';
                $numContenedor .= $contenedor2;
                $cotRef = optional(optional($cotizacionItem->Contenedor)->Cotizacion);
              @endphp
              
                <tr>
                    <td>{{ $numContenedor ?? '-' }}</td>
                    <td>{{ optional($cotRef->Cliente)->nombre ?? '-' }}</td>
                    <td>
                        @if ($cotRef->id_subcliente)
                            {{ optional($cotRef->Subcliente)->nombre ?? '-' }} /
                            {{ optional($cotRef->Subcliente)->telefono ?? '-' }}
                        @else
                            -
                        @endif
                    </td>
                    <td>{{ $cotRef->origen ?? '-' }}</td>
                    <td>{{ $cotRef->destino ?? '-' }}</td>
                    <td>{{ $cotRef->estatus ?? '-' }}</td>
                    <td>{{ $cotizacionItem->fehca_inicio_guard ? Carbon::parse($cotizacionItem->fehca_inicio_guard)->format('d-m-Y') : '-' }}</td>
                    <td>{{ $cotizacionItem->fehca_fin_guard ? Carbon::parse($cotizacionItem->fehca_fin_guard)->format('d-m-Y') : '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@else
    {{-- MODO EMPRESA GLOBAL (H00): Agrupación separada por Unidad / Tractocamión --}}
    @php
        $viajesPorUnidad = $cotizaciones->groupBy(function($item) {
            $camion = $item->Camion;
            if (!$camion) {
                return 'SIN UNIDAD ASIGNADA';
            }
            return trim(($camion->id_equipo ?: $camion->placas ?: 'UNIDAD #' . $camion->id) . ' - ' . ($camion->marca ?: 'TRACTO') . ' [' . ($camion->placas ?: 'S/P') . ']');
        });
        $grandTotalViajes = 0;
    @endphp

    @foreach ($viajesPorUnidad as $unidadTexto => $itemsUnidad)
        @php
            $grandTotalViajes += $itemsUnidad->count();
        @endphp

        @if (!isset($isExcel))
            <div style="background-color: #2c3e50; color: #ffffff; padding: 5px 10px; font-size: 11px; font-weight: bold; margin-top: {{ $loop->first ? '0px' : '15px' }}; margin-bottom: 5px; border-radius: 3px;">
                UNIDAD / TRACTOCAMION: {{ $unidadTexto }} (Total viajes: {{ $itemsUnidad->count() }})
            </div>
        @endif

        <table class="{{ !isset($isExcel) ? 'tabla-viajes' : '' }}" style="{{ isset($isExcel) ? 'border-collapse: collapse; margin-bottom: 10px;' : 'margin-top: 5px; margin-bottom: 10px;' }}" border="{{ isset($isExcel) ? '1' : '0' }}">
            @if (isset($isExcel))
                <thead>
                    <tr>
                        <th colspan="8" style="background-color: #2c3e50; color: #ffffff; text-align: left; font-size: 11pt; font-weight: bold;">
                            UNIDAD / TRACTOCAMION: {{ $unidadTexto }} (Total viajes: {{ $itemsUnidad->count() }})
                        </th>
                    </tr>
                </thead>
            @endif
            <thead>
                <tr style="{{ isset($isExcel) ? 'background-color: #f2f2f2;' : '' }}">
                    <th>Contenedor</th>
                    <th>Cliente</th>
                    <th>Subcliente</th>
                    <th>Origen</th>
                    <th>Destino</th>
                    <th>Estatus</th>
                    <th>Fecha salida</th>
                    <th>Fecha llegada</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($itemsUnidad as $cotizacionItem)
                    @php
                        $numContenedor = optional($cotizacionItem->Contenedor)->num_contenedor ?? '-';
                        $refFull = optional(optional($cotizacionItem->Contenedor)->Cotizacion)->referencia_full;
                        $contenedor2 = $refFull ? ReporteriaController::getContenedorSecundario($refFull) : '';
                        $numContenedor .= $contenedor2;
                        $cotRef = optional(optional($cotizacionItem->Contenedor)->Cotizacion);
                    @endphp
                    <tr>
                        <td>{{ $numContenedor ?? '-' }}</td>
                        <td>{{ optional($cotRef->Cliente)->nombre ?? '-' }}</td>
                        <td>
                            @if ($cotRef->id_subcliente)
                                {{ optional($cotRef->Subcliente)->nombre ?? '-' }} /
                                {{ optional($cotRef->Subcliente)->telefono ?? '-' }}
                            @else
                                -
                            @endif
                        </td>
                        <td>{{ $cotRef->origen ?? '-' }}</td>
                        <td>{{ $cotRef->destino ?? '-' }}</td>
                        <td>{{ $cotRef->estatus ?? '-' }}</td>
                        <td>{{ $cotizacionItem->fehca_inicio_guard ? Carbon::parse($cotizacionItem->fehca_inicio_guard)->format('d-m-Y') : '-' }}</td>
                        <td>{{ $cotizacionItem->fehca_fin_guard ? Carbon::parse($cotizacionItem->fehca_fin_guard)->format('d-m-Y') : '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr style="background-color: #e9ecef; font-weight: bold;">
                    <td colspan="7" style="text-align: right; padding: 6px;">SUBTOTAL VIAJES UNIDAD ({{ $unidadTexto }}):</td>
                    <td style="text-align: center; padding: 6px;">{{ $itemsUnidad->count() }}</td>
                </tr>
            </tfoot>
        </table>
    @endforeach

    @if ($viajesPorUnidad->count() > 1)
        <table class="{{ !isset($isExcel) ? 'tabla-viajes' : '' }}" style="margin-top: 15px; border-collapse: collapse;" border="{{ isset($isExcel) ? '1' : '0' }}">
            <tfoot>
                <tr style="background-color: #344767; color: #ffffff; font-weight: bold; font-size: 11px;">
                    <td colspan="7" style="text-align: right; color: #ffffff; padding: 8px;">GRAN TOTAL CONSOLIDADO VIAJES (TODAS LAS UNIDADES):</td>
                    <td style="text-align: center; color: #ffffff; padding: 8px;">{{ $grandTotalViajes }}</td>
                </tr>
            </tfoot>
        </table>
    @endif
@endif

@if (!isset($isExcel))
    </body>

    </html>
@endif
