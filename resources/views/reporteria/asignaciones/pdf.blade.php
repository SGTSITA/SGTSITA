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

{{-- Empresa y fecha (siempre visibles) --}}
<div style="margin-bottom: 10px;">
    <h4 style="margin: 0;">Empresa: {{ $user->Empresa->nombre }}</h4>
    <div style="text-align: right;"><strong>Fecha:</strong> {{ date('d-m-Y') }}</div>
    <h4 style="margin-top: 5px;">Viajes</h4>
</div>

{{-- Tabla --}}
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
        @foreach ($cotizaciones as $cotizacion)
          @php
            $numContenedor = $cotizacion->Contenedor->num_contenedor;
            $contenedor2 = ReporteriaController::getContenedorSecundario($cotizacion->Contenedor->Cotizacion->referencia_full);
            $numContenedor .= $contenedor2;
          @endphp
          
            <tr>
                <td>{{ $numContenedor ?? '-' }}</td>
                <td>{{ $cotizacion->Contenedor->Cotizacion->Cliente->nombre ?? '-' }}</td>
                <td>
                    @if ($cotizacion->Contenedor->Cotizacion->id_subcliente)
                        {{ $cotizacion->Contenedor->Cotizacion->Subcliente->nombre ?? '-' }} /
                        {{ $cotizacion->Contenedor->Cotizacion->Subcliente->telefono ?? '-' }}
                    @else
                        -
                    @endif
                </td>
                <td>{{ $cotizacion->Contenedor->Cotizacion->origen ?? '-' }}</td>
                <td>{{ $cotizacion->Contenedor->Cotizacion->destino ?? '-' }}</td>
                <td>{{ $cotizacion->Contenedor->Cotizacion->estatus ?? '-' }}</td>
                <td>{{ Carbon::parse($cotizacion->fehca_inicio_guard)->format('d-m-Y') }}</td>
                <td>{{ Carbon::parse($cotizacion->fehca_fin_guard)->format('d-m-Y') }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

@if (!isset($isExcel))
    </body>

    </html>
@endif
