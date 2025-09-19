<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <title>Reporte de Resultados</title>
</head>

<body>
    <!-- ENCABEZADO -->
    <table style="width:100%; border-collapse: collapse; margin-bottom:10px;" border="1">
        <tr>
            <td>
                <h2>Reporte de Resultados</h2>
                <p><strong>Empresa:</strong> {{ $user->Empresa->nombre }}</p>
                <p><strong>Periodo:</strong> {{ date('d-m-Y', strtotime($fechaInicio)) }} al
                    {{ date('d-m-Y', strtotime($fechaFin)) }}</p>
                <p><strong>Contenedores seleecionados:</strong> {{ $selectedRows }} de {{ $totalRows }}</p>
            </td>
            <td>
                <p><strong>Utilidad Bruta:</strong> ${{ number_format($utilidad, 2) }}</p>
                <p><strong>Otros Gastos:</strong> ${{ number_format($gastos, 2) }}</p>
                <p><strong>Utilidad Neta:</strong> ${{ number_format($utilidad - $gastos, 2) }}</p>
            </td>
        </tr>
    </table>

    <!-- TABLA PRINCIPAL -->
    <table style="width:100%; border-collapse: collapse;" border="1">
        <thead>
            <tr style="background-color:#f0f0f0;">
                <th>Núm Contenedor</th>
                <th>Cliente</th>
                <th>Precio Viaje</th>
                <th>Pago Operación</th>
                <th>Gastos Extra</th>
                <th>Gastos Viaje</th>
                <th>Gastos Diferidos</th>
                <th>Utilidad</th>
                <th>Transportado Por</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cotizaciones as $cotizacion)
                <tr>
                    <td>{{ $cotizacion['numContenedor'] }}</td>
                    <td>{{ $cotizacion['cliente'] }}</td>
                    <td>{{ number_format($cotizacion['precioViaje'], 2) }}</td>
                    <td>{{ number_format($cotizacion['pagoOperacion'], 2) }}</td>
                    <td>{{ number_format($cotizacion['gastosExtra'], 2) }}</td>
                    <td>{{ number_format($cotizacion['gastosViaje'], 2) }}</td>
                    <td>{{ number_format($cotizacion['gastosDiferidos'], 2) }}</td>
                    <td @if ($cotizacion['utilidad'] < 0) style="background-color:#ffc107;" @endif>
                        {{ number_format($cotizacion['utilidad'], 2) }}
                    </td>
                    <td>{{ $cotizacion['transportadoPor'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- TABLA DE GASTOS -->
    <h4 style="margin-top:20px;">Desglose de Otros Gastos</h4>
    <table style="width:100%; border-collapse: collapse;" border="1">
        <thead style="background-color:#f0f0f0;">
            <tr>
                <th>Fecha</th>
                <th>Motivo</th>
                <th>Categoría</th>
                <th>Método de Pago</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($gastosGenerales as $gasto)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($gasto->fecha)->format('d-m-Y') }}</td>
                    <td>{{ $gasto->motivo }}</td>
                    <td>{{ $gasto->Categoria->categoria ?? 'N/A' }}</td>
                    <td>{{ $gasto->metodo_pago1 }}</td>
                    <td>{{ number_format($gasto->monto1, 2) }}</td>
                </tr>
            @endforeach
            <tr style="font-weight:bold;">
                <td colspan="4" style="text-align:right;">Total:</td>
                <td>{{ number_format($gastos, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>

</html>
