<table>
    <thead>
        <tr>
            <th colspan="{{ $reporte['tipo_reporte'] === 'detallado' ? 8 : 6 }}">
                {{ $reporte['titulo'] }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ $reporte['tipo_reporte'] === 'detallado' ? 8 : 6 }}">
                {{ $reporte['cuenta']['banco'] }} -
                {{ $reporte['cuenta']['beneficiario'] }} -
                {{ $reporte['cuenta']['numero_cuenta'] }}
            </th>
        </tr>
        <tr>
            <th colspan="{{ $reporte['tipo_reporte'] === 'detallado' ? 8 : 6 }}">
                Periodo: {{ $reporte['fecha_inicio'] }} al {{ $reporte['fecha_fin'] }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th>Saldo inicial</th>
            <th>Total cargos</th>
            <th>Total abonos</th>
            <th>Saldo final</th>
        </tr>
        <tr>
            <td>{{ $reporte['saldo_inicial'] }}</td>
            <td>{{ $reporte['total_cargos'] }}</td>
            <td>{{ $reporte['total_abonos'] }}</td>
            <td>{{ $reporte['saldo_final'] }}</td>
        </tr>
        <tr></tr>

        @if ($reporte['tipo_reporte'] === 'detallado')
            <tr>
                <th>Fecha</th>
                <th>Movimiento</th>
                <th>Unidad</th>
                <th>Descripción detalle</th>
                <th>Referencia</th>
                <th>Cargo</th>
                <th>Abono</th>
                <th>Saldo</th>
            </tr>
        @else
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Referencia</th>
                <th>Cargo</th>
                <th>Abono</th>
                <th>Saldo</th>
            </tr>
        @endif
    </thead>

    <tbody>
        @if ($reporte['tipo_reporte'] === 'detallado')
            @foreach ($reporte['rows'] as $row)
                <tr>
                    <td>{{ $row['fecha'] }}</td>
                    <td>{{ $row['concepto'] }}</td>
                    <td>{{ $row['unidad'] }}</td>
                    <td>{{ $row['descripcion'] }}</td>
                    <td>{{ $row['referencia_detalle'] ?? 'S/N' }}</td>
                    <td>{{ $row['cargo'] }}</td>
                    <td>{{ $row['abono'] }}</td>
                    <td>{{ $row['saldo'] }}</td>
                </tr>
            @endforeach
        @else
            @foreach ($reporte['rows'] as $row)
                <tr>
                    <td>{{ $row['fecha'] }}</td>
                    <td>{{ $row['concepto'] }}</td>
                    <td>{{ $row['referencia'] ?? 'S/N' }}</td>
                    <td>{{ $row['cargo'] }}</td>
                    <td>{{ $row['abono'] }}</td>
                    <td>{{ $row['saldo'] }}</td>
                </tr>
            @endforeach
        @endif
    </tbody>
</table>
