<table>
    <thead>
        <tr>
            <th colspan="4" style="font-size: 14pt; font-weight: bold; text-align: center; color: #1f497d;">
                {{ $nombresEmpresas }}
            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 12pt; font-weight: bold; text-align: center;">
                ESTADO DE ACTIVIDADES / RESULTADOS • CONAC ERA-02
            </th>
        </tr>
        <tr>
            <th colspan="4" style="font-size: 10pt; font-style: italic; text-align: center; color: #595959;">
                @if($equipoNombre) UNIDAD: {{ $equipoNombre }} | @else CONSOLIDADO GLOBAL | @endif
                DEL {{ \Carbon\Carbon::parse($data['fecha_inicio_ejercicio'] ?? $fechaCorte)->format('d/m/Y') }} AL {{ \Carbon\Carbon::parse($fechaCorte)->format('d/m/Y') }} • CIFRAS EN MXN
            </th>
        </tr>
        <tr>
            <th colspan="4"></th>
        </tr>
        <tr style="background-color: #1F497D; color: #FFFFFF; font-weight: bold;">
            <th style="width: 50px; text-align: center;">TIPO</th>
            <th style="width: 320px;">CONCEPTO DE OPERACIÓN</th>
            <th style="width: 140px; text-align: right;">IMPORTE (MXN)</th>
            <th style="width: 100px; text-align: center;">CLASIFICACIÓN</th>
        </tr>
    </thead>
    <tbody>
        {{-- INGRESOS --}}
        <tr style="background-color: #D9E1F2; font-weight: bold;">
            <td colspan="4">INGRESOS DE GESTIÓN Y FINANCIEROS</td>
        </tr>
        <tr>
            <td style="text-align: center; color: #385723; font-weight: bold;">(+)</td>
            <td>Ingresos por Servicios de Fletes y Logística (4.1.1.1)</td>
            <td style="text-align: right;">{{ $data['estado_resultados']['ingresos_fletes'] }}</td>
            <td style="text-align: center;">Operativo</td>
        </tr>
        <tr>
            <td style="text-align: center; color: #2F5597; font-weight: bold;">(+)</td>
            <td>Intereses y Rendimientos Bancarios (4.3.1.1)</td>
            <td style="text-align: right;">{{ $data['estado_resultados']['intereses_ganados'] }}</td>
            <td style="text-align: center;">Financiero</td>
        </tr>
        <tr style="background-color: #B4C6E7; font-weight: bold;">
            <td colspan="2" style="text-align: right;">TOTAL INGRESOS:</td>
            <td style="text-align: right;">{{ round($data['estado_resultados']['ingresos_fletes'] + $data['estado_resultados']['intereses_ganados'], 2) }}</td>
            <td></td>
        </tr>

        <tr><td colspan="4"></td></tr>

        {{-- GASTOS --}}
        <tr style="background-color: #FCE4D6; font-weight: bold;">
            <td colspan="4">GASTOS Y OTRAS PÉRDIDAS</td>
        </tr>
        <tr>
            <td style="text-align: center; color: #C00000; font-weight: bold;">(-)</td>
            <td>Gastos Operativos, Generales y de Funcionamiento (5.1.*)</td>
            <td style="text-align: right;">{{ $data['estado_resultados']['gastos_totales'] }}</td>
            <td style="text-align: center;">Operativo</td>
        </tr>
        <tr style="background-color: #F8CBAD; font-weight: bold;">
            <td colspan="2" style="text-align: right;">TOTAL GASTOS:</td>
            <td style="text-align: right;">{{ $data['estado_resultados']['gastos_totales'] }}</td>
            <td></td>
        </tr>

        <tr><td colspan="4"></td></tr>

        {{-- UTILIDAD NETA --}}
        <tr style="background-color: {{ $data['estado_resultados']['utilidad_neta'] >= 0 ? '#C6E0B4' : '#F8CBAD' }}; font-weight: bold; font-size: 11pt;">
            <td colspan="2" style="text-align: right;">RESULTADO DEL EJERCICIO (AHORRO / DESAHORRO NETO):</td>
            <td style="text-align: right; color: {{ $data['estado_resultados']['utilidad_neta'] >= 0 ? '#385723' : '#C00000' }};">
                {{ $data['estado_resultados']['utilidad_neta'] }}
            </td>
            <td style="text-align: center;">
                {{ $data['estado_resultados']['utilidad_neta'] >= 0 ? 'UTILIDAD' : 'DÉFICIT' }}
            </td>
        </tr>
    </tbody>
</table>
