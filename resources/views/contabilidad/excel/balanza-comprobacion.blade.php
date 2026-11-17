<table>
    <thead>
        <tr>
            <th colspan="9" style="font-size: 14pt; font-weight: bold; text-align: center; color: #1f497d;">
                {{ $nombresEmpresas }}
            </th>
        </tr>
        <tr>
            <th colspan="9" style="font-size: 12pt; font-weight: bold; text-align: center;">
                BALANZA DE COMPROBACIÓN CONAC • CÓDIGO BC-03
            </th>
        </tr>
        <tr>
            <th colspan="9" style="font-size: 10pt; font-style: italic; text-align: center; color: #595959;">
                @if($equipoNombre) UNIDAD: {{ $equipoNombre }} | @else CONSOLIDADO GLOBAL | @endif
                DEL {{ \Carbon\Carbon::parse($data['fecha_inicio'] ?? $fechaCorte)->format('d/m/Y') }} AL {{ \Carbon\Carbon::parse($fechaCorte)->format('d/m/Y') }} • CIFRAS EN PESOS MEXICANOS (MXN)
            </th>
        </tr>
        <tr>
            <th colspan="9"></th>
        </tr>
        <tr style="background-color: #1F497D; color: #FFFFFF; font-weight: bold; text-align: center;">
            <th rowspan="2" style="width: 100px; vertical-align: middle;">CÓDIGO CONAC</th>
            <th rowspan="2" style="width: 320px; vertical-align: middle; text-align: left;">NOMBRE DE LA CUENTA</th>
            <th rowspan="2" style="width: 80px; vertical-align: middle;">NATURALEZA</th>
            <th colspan="2" style="width: 220px; text-align: center; background-color: #2F5597;">SALDO INICIAL</th>
            <th colspan="2" style="width: 220px; text-align: center; background-color: #385723;">MOVIMIENTOS DEL PERIODO</th>
            <th colspan="2" style="width: 220px; text-align: center; background-color: #1F497D;">SALDO FINAL</th>
        </tr>
        <tr style="background-color: #2F5597; color: #FFFFFF; font-weight: bold; text-align: right;">
            <th style="width: 110px; text-align: right;">DEUDOR</th>
            <th style="width: 110px; text-align: right;">ACREEDOR</th>
            <th style="width: 110px; text-align: right;">DEBE (CARGOS)</th>
            <th style="width: 110px; text-align: right;">HABER (ABONOS)</th>
            <th style="width: 110px; text-align: right;">DEUDOR</th>
            <th style="width: 110px; text-align: right;">ACREEDOR</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['cuentas'] as $c)
            <tr>
                <td style="text-align: center; font-family: monospace;">{{ $c['codigo'] }}</td>
                <td>{{ $c['nombre'] }}</td>
                <td style="text-align: center; text-transform: uppercase;">{{ substr($c['naturaleza'], 0, 1) }}</td>
                <td style="text-align: right;">{{ $c['saldo_ini_deudor'] > 0 ? $c['saldo_ini_deudor'] : '' }}</td>
                <td style="text-align: right;">{{ $c['saldo_ini_acreedor'] > 0 ? $c['saldo_ini_acreedor'] : '' }}</td>
                <td style="text-align: right; color: #385723;">{{ $c['debe'] > 0 ? $c['debe'] : '' }}</td>
                <td style="text-align: right; color: #C00000;">{{ $c['haber'] > 0 ? $c['haber'] : '' }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $c['saldo_fin_deudor'] > 0 ? $c['saldo_fin_deudor'] : '' }}</td>
                <td style="text-align: right; font-weight: bold;">{{ $c['saldo_fin_acreedor'] > 0 ? $c['saldo_fin_acreedor'] : '' }}</td>
            </tr>
        @endforeach
        <tr style="background-color: #D9E1F2; font-weight: bold; font-size: 11pt; border-top: 2px solid #1F497D;">
            <td colspan="3" style="text-align: right;">SUMAS IGUALES:</td>
            <td style="text-align: right;">{{ $data['totales']['saldo_ini_deudor'] }}</td>
            <td style="text-align: right;">{{ $data['totales']['saldo_ini_acreedor'] }}</td>
            <td style="text-align: right;">{{ $data['totales']['debe'] }}</td>
            <td style="text-align: right;">{{ $data['totales']['haber'] }}</td>
            <td style="text-align: right;">{{ $data['totales']['saldo_fin_deudor'] }}</td>
            <td style="text-align: right;">{{ $data['totales']['saldo_fin_acreedor'] }}</td>
        </tr>
    </tbody>
</table>
