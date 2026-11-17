<table>
    <thead>
        <tr>
            <th colspan="5" style="font-size: 14pt; font-weight: bold; text-align: center; color: #1f497d;">
                {{ $nombresEmpresas }}
            </th>
        </tr>
        <tr>
            <th colspan="5" style="font-size: 12pt; font-weight: bold; text-align: center;">
                BALANCE GENERAL (ESTADO DE SITUACIÓN FINANCIERA) • CONAC ESF-01
            </th>
        </tr>
        <tr>
            <th colspan="5" style="font-size: 10pt; font-style: italic; text-align: center; color: #595959;">
                @if($equipoNombre) UNIDAD: {{ $equipoNombre }} | @else CONSOLIDADO GLOBAL | @endif
                AL {{ \Carbon\Carbon::parse($fechaCorte)->format('d/m/Y') }} • CIFRAS EN PESOS MEXICANOS (MXN)
            </th>
        </tr>
        <tr>
            <th colspan="5"></th>
        </tr>
        <tr style="background-color: #2F5597; color: #FFFFFF; font-weight: bold;">
            <th style="width: 35px; background-color: #1F497D; color: #FFFFFF;">SECCIÓN</th>
            <th style="width: 45px; background-color: #1F497D; color: #FFFFFF;">CÓDIGO / REF</th>
            <th style="width: 280px; background-color: #1F497D; color: #FFFFFF;">CONCEPTO</th>
            <th style="width: 130px; background-color: #1F497D; color: #FFFFFF; text-align: right;">MONTO (MXN)</th>
            <th style="width: 80px; background-color: #1F497D; color: #FFFFFF; text-align: center;">TIPO</th>
        </tr>
    </thead>
    <tbody>
        {{-- ACTIVO --}}
        <tr style="background-color: #D9E1F2; font-weight: bold;">
            <td colspan="5">ACTIVO</td>
        </tr>
        @foreach ($data['activo']['items'] as $item)
            <tr>
                <td>ACTIVO</td>
                <td>{{ $item['prefijo'] ?? '' }}</td>
                <td style="{{ $item['es_calculado'] ? 'font-weight: bold;' : 'padding-left: 15px;' }}">
                    {{ $item['concepto'] }}
                </td>
                <td style="text-align: right; {{ $item['monto'] < 0 ? 'color: #C00000;' : '' }}">
                    {{ $item['monto'] }}
                </td>
                <td style="text-align: center;">{{ $item['es_calculado'] ? 'Calculado' : 'Rubro' }}</td>
            </tr>
        @endforeach
        <tr style="background-color: #B4C6E7; font-weight: bold;">
            <td colspan="3" style="text-align: right;">TOTAL ACTIVO:</td>
            <td style="text-align: right;">{{ $data['activo']['total'] }}</td>
            <td></td>
        </tr>

        <tr><td colspan="5"></td></tr>

        {{-- PASIVO --}}
        <tr style="background-color: #FCE4D6; font-weight: bold;">
            <td colspan="5">PASIVO</td>
        </tr>
        @foreach ($data['pasivo']['items'] as $item)
            <tr>
                <td>PASIVO</td>
                <td>{{ $item['prefijo'] ?? '' }}</td>
                <td style="{{ $item['es_calculado'] ? 'font-weight: bold;' : 'padding-left: 15px;' }}">
                    {{ $item['concepto'] }}
                </td>
                <td style="text-align: right; {{ $item['monto'] < 0 ? 'color: #C00000;' : '' }}">
                    {{ $item['monto'] }}
                </td>
                <td style="text-align: center;">{{ $item['es_calculado'] ? 'Calculado' : 'Rubro' }}</td>
            </tr>
        @endforeach
        <tr style="background-color: #F8CBAD; font-weight: bold;">
            <td colspan="3" style="text-align: right;">TOTAL PASIVO:</td>
            <td style="text-align: right;">{{ $data['pasivo']['total'] }}</td>
            <td></td>
        </tr>

        <tr><td colspan="5"></td></tr>

        {{-- PATRIMONIO --}}
        <tr style="background-color: #E2EFDA; font-weight: bold;">
            <td colspan="5">HACIENDA PÚBLICA / PATRIMONIO</td>
        </tr>
        @foreach ($data['patrimonio']['items'] as $item)
            <tr>
                <td>PATRIMONIO</td>
                <td>{{ $item['prefijo'] ?? '' }}</td>
                <td style="{{ $item['es_calculado'] ? 'font-weight: bold;' : 'padding-left: 15px;' }}">
                    {{ $item['concepto'] }}
                </td>
                <td style="text-align: right; {{ $item['monto'] < 0 ? 'color: #C00000;' : '' }}">
                    {{ $item['monto'] }}
                </td>
                <td style="text-align: center;">{{ $item['es_calculado'] ? 'Calculado' : 'Rubro' }}</td>
            </tr>
        @endforeach
        <tr style="background-color: #C6E0B4; font-weight: bold;">
            <td colspan="3" style="text-align: right;">TOTAL PATRIMONIO:</td>
            <td style="text-align: right;">{{ $data['patrimonio']['total'] }}</td>
            <td></td>
        </tr>

        <tr><td colspan="5"></td></tr>

        {{-- TOTAL PASIVO + PATRIMONIO --}}
        <tr style="background-color: #FFF2CC; font-weight: bold; font-size: 11pt;">
            <td colspan="3" style="text-align: right;">TOTAL PASIVO Y PATRIMONIO:</td>
            <td style="text-align: right;">{{ $data['total_pasivo_patrimonio'] }}</td>
            <td></td>
        </tr>
        <tr style="background-color: {{ $data['cuadrado'] ? '#E2EFDA' : '#FCE4D6' }}; font-weight: bold;">
            <td colspan="3" style="text-align: right;">ESTADO DE CUADRE (ACTIVO - PASIVO Y PATRIMONIO):</td>
            <td style="text-align: right; color: {{ $data['cuadrado'] ? '#385723' : '#C00000' }};">
                {{ $data['diferencia'] }}
            </td>
            <td style="text-align: center; color: {{ $data['cuadrado'] ? '#385723' : '#C00000' }};">
                {{ $data['cuadrado'] ? 'CUADRADO' : 'DESCUADRADO' }}
            </td>
        </tr>
    </tbody>
</table>
