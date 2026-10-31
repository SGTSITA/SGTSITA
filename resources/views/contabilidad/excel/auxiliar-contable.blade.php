<table>
    <thead>
        <tr>
            <th colspan="8" style="font-size: 14pt; font-weight: bold; text-align: center; color: #1F497D;">
                {{ $nombresEmpresas }}
            </th>
        </tr>
        <tr>
            <th colspan="8" style="font-size: 12pt; font-weight: bold; text-align: center;">
                AUXILIAR CONTABLE (LIBRO MAYOR POR CUENTAS) • CÓDIGO CONAC AC-07
            </th>
        </tr>
        <tr>
            <th colspan="8" style="font-size: 10pt; font-style: italic; text-align: center; color: #595959;">
                @if($equipoNombre) UNIDAD: {{ $equipoNombre }} | @else CONSOLIDADO GLOBAL | @endif
                DEL {{ \Carbon\Carbon::parse($data['fecha_inicio'] ?? $fechaCorte)->format('d/m/Y') }} AL {{ \Carbon\Carbon::parse($fechaCorte)->format('d/m/Y') }} • CIFRAS EN PESOS MEXICANOS (MXN)
            </th>
        </tr>
        <tr>
            <th colspan="8"></th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['cuentas'] as $c)
            @if (!$c['es_afectable'])
                {{-- TÍTULO / GRUPO DE MAYOR --}}
                <tr style="background-color: #2F5597; color: #FFFFFF; font-weight: bold; font-size: 10pt;">
                    <td colspan="8" style="padding: 6px;">
                        {{ $c['codigo'] }} - {{ $c['nombre'] }}
                    </td>
                </tr>
            @else
                {{-- CUENTA AFECTABLE: ENCABEZADO DE CUENTA --}}
                <tr style="background-color: #D9E1F2; font-weight: bold; font-size: 9.5pt; border-top: 1px solid #1F497D;">
                    <td colspan="4" style="color: #1F497D;">
                        CUENTA: {{ $c['codigo'] }} {{ $c['nombre'] }} ({{ ucfirst($c['naturaleza']) }})
                    </td>
                    <td colspan="2" style="text-align: right; color: #595959;">
                        SALDO INICIAL:
                    </td>
                    <td colspan="2" style="text-align: right; font-weight: bold;">
                        {{ number_format($c['saldo_inicial'], 2) }}
                    </td>
                </tr>

                @if (!empty($c['movimientos']))
                    <tr style="background-color: #F2F2F2; font-weight: bold; font-size: 8.5pt; color: #333333; text-align: center;">
                        <th style="width: 85px;">FECHA</th>
                        <th style="width: 75px;">TIPO</th>
                        <th style="width: 130px;">PÓLIZA #</th>
                        <th style="width: 280px; text-align: left;">CONCEPTO / DETALLE</th>
                        <th style="width: 160px; text-align: left;">REFERENCIA / CONTENEDOR</th>
                        <th style="width: 110px; text-align: right;">CARGOS (DEBE)</th>
                        <th style="width: 110px; text-align: right;">ABONOS (HABER)</th>
                        <th style="width: 120px; text-align: right;">SALDO ACUMULADO</th>
                    </tr>

                    @foreach ($c['movimientos'] as $m)
                        <tr style="font-size: 8.5pt;">
                            <td style="text-align: center;">{{ $m['fecha_formato'] }}</td>
                            <td style="text-align: center; text-transform: uppercase;">{{ $m['poliza_tipo'] }}</td>
                            <td style="text-align: center; font-family: monospace;">{{ $m['poliza_numero'] }}</td>
                            <td>{{ $m['concepto'] }}</td>
                            <td>{{ $m['referencia'] }}</td>
                            <td style="text-align: right; color: #385723;">{{ $m['debe'] > 0 ? $m['debe'] : '' }}</td>
                            <td style="text-align: right; color: #C00000;">{{ $m['haber'] > 0 ? $m['haber'] : '' }}</td>
                            <td style="text-align: right; font-weight: bold;">{{ $m['saldo_acumulado'] }}</td>
                        </tr>
                    @endforeach
                @else
                    <tr style="font-size: 8.5pt; color: #888888; font-style: italic;">
                        <td colspan="8" style="text-align: center; padding: 4px;">
                            (Sin movimientos en el periodo seleccionado)
                        </td>
                    </tr>
                @endif

                {{-- SUB-TOTAL DE LA CUENTA --}}
                <tr style="background-color: #E2EFDA; font-weight: bold; font-size: 9pt; border-bottom: 1.5px solid #2F5597;">
                    <td colspan="5" style="text-align: right;">TOTAL CUENTA {{ $c['codigo'] }}:</td>
                    <td style="text-align: right; color: #385723;">{{ $c['total_debe'] }}</td>
                    <td style="text-align: right; color: #C00000;">{{ $c['total_haber'] }}</td>
                    <td style="text-align: right; font-weight: bold; color: #1F497D;">{{ $c['saldo_final'] }}</td>
                </tr>
                <tr>
                    <td colspan="8" style="height: 6px;"></td>
                </tr>
            @endif
        @endforeach

        {{-- RESUMEN GLOBAL FINAL --}}
        <tr style="background-color: #1F497D; color: #FFFFFF; font-weight: bold; font-size: 11pt; border-top: 2px solid #000;">
            <td colspan="5" style="text-align: right;">SUMAS TOTALES AUXILIARES:</td>
            <td style="text-align: right;">{{ $data['total_debe'] }}</td>
            <td style="text-align: right;">{{ $data['total_haber'] }}</td>
            <td style="text-align: right;">
                {{ $data['cuadrado'] ? 'CUADRADO' : 'DIF: ' . $data['diferencia'] }}
            </td>
        </tr>
    </tbody>
</table>
