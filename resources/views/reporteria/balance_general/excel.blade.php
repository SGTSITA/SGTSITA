<table>
    <thead>
        <tr>
            <th colspan="6" style="text-align: center; font-size: 14pt; font-weight: bold;">BALANCE GENERAL</th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: center; font-size: 10pt; font-style: italic;">Fecha de Corte: {{ \Carbon\Carbon::parse($fechaCorte)->format('d/m/Y') }}</th>
        </tr>
        <tr>
            <th colspan="6"></th>
        </tr>
        <tr>
            <th style="font-weight: bold; background-color: #dbe5f1; text-align: left;">ACTIVOS</th>
            <th style="font-weight: bold; background-color: #dbe5f1; text-align: right;">SALDO</th>
            <th></th>
            <th style="font-weight: bold; background-color: #fce4d6; text-align: left;">PASIVOS</th>
            <th style="font-weight: bold; background-color: #fce4d6; text-align: right;">SALDO</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        @php
            $activos = collect($balance['rows'])->where('grupo', 'activo')->values();
            $pasivos = collect($balance['rows'])->where('grupo', 'pasivo')->values();
            $capital = collect($balance['rows'])->where('grupo', 'capital')->values();

            $maxRows = max($activos->count() + 1, $pasivos->count() + $capital->count() + 3);
        @endphp

        @for($i = 0; $i < $maxRows; $i++)
            <tr>
                <!-- Activos Section -->
                @if($i < $activos->count())
                    <td>{{ $activos[$i]['concepto'] }}</td>
                    <td style="text-align: right;">{{ number_format($activos[$i]['valor'], 2) }}</td>
                @elseif($i === $activos->count())
                    <td style="font-weight: bold; background-color: #e2efda;">SUMA ACTIVOS</td>
                    <td style="font-weight: bold; background-color: #e2efda; text-align: right;">{{ number_format($balance['totales']['activo'], 2) }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif

                <td></td>

                <!-- Pasivos & Capital Section -->
                @if($i < $pasivos->count())
                    <td>{{ $pasivos[$i]['concepto'] }}</td>
                    <td style="text-align: right;">{{ number_format($pasivos[$i]['valor'], 2) }}</td>
                @elseif($i === $pasivos->count())
                    <td style="font-weight: bold; background-color: #fff2cc;">SUMA PASIVOS</td>
                    <td style="font-weight: bold; background-color: #fff2cc; text-align: right;">{{ number_format($balance['totales']['pasivo'], 2) }}</td>
                @elseif($i === ($pasivos->count() + 1))
                    <td style="font-weight: bold; background-color: #d9e1f2; text-align: left;">CAPITAL</td>
                    <td style="background-color: #d9e1f2;"></td>
                @elseif($i > ($pasivos->count() + 1) && ($i - ($pasivos->count() + 2)) < $capital->count())
                    @php $capIndex = $i - ($pasivos->count() + 2); @endphp
                    <td>{{ $capital[$capIndex]['concepto'] }}</td>
                    <td style="text-align: right;">{{ number_format($capital[$capIndex]['valor'], 2) }}</td>
                @elseif($i === ($pasivos->count() + $capital->count() + 2))
                    <td style="font-weight: bold; background-color: #e2efda;">SUMA CAPITAL</td>
                    <td style="font-weight: bold; background-color: #e2efda; text-align: right;">{{ number_format($balance['totales']['capital'], 2) }}</td>
                @else
                    <td></td>
                    <td></td>
                @endif
                <td></td>
            </tr>
        @endfor
        <tr>
            <td colspan="6"></td>
        </tr>
        <tr>
            <td style="font-weight: bold; text-transform: uppercase;">TOTAL ACTIVOS</td>
            <td style="font-weight: bold; text-align: right;">{{ number_format($balance['totales']['activo'], 2) }}</td>
            <td></td>
            <td style="font-weight: bold; text-transform: uppercase;">TOTAL PASIVO + CAPITAL</td>
            <td style="font-weight: bold; text-align: right;">{{ number_format($balance['totales']['pasivo'] + $balance['totales']['capital'], 2) }}</td>
            <td></td>
        </tr>
    </tbody>
</table>
