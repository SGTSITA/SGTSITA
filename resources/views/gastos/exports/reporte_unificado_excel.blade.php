@if (($agrupacion ?? '') === 'categoria' || count($categorias) === 0)
<table>
    <thead>
        <tr>
            <th colspan="2" style="font-weight: bold; font-size: 14pt; text-align: center;">
                Relación de Gastos por Categoría del {{ $from }} al {{ $to }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold;">Suma de Monto</th>
            <th></th>
        </tr>
        <tr style="background-color: #1B365D; color: #FFFFFF; font-weight: bold;">
            <th style="background-color: #1B365D; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; width: 280px;">
                Categoría
            </th>
            <th style="background-color: #1B365D; color: #FFFFFF; font-weight: bold; text-align: right; border: 1px solid #000000; width: 160px;">
                Total general
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($filas as $fila)
            <tr>
                <td style="border: 1px solid #D9D9D9;">{{ $fila['nombre'] }}</td>
                <td style="text-align: right; font-weight: bold; border: 1px solid #D9D9D9;">
                    {{ number_format($fila['total'], 2, '.', '') }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight: bold; background-color: #F2F2F2;">
            <td style="font-weight: bold; border: 1px solid #000000;">Total general</td>
            <td style="text-align: right; font-weight: bold; border: 1px solid #000000; background-color: #E2EFDA;">
                {{ number_format($gran_total, 2, '.', '') }}
            </td>
        </tr>
    </tfoot>
</table>
@else
<table>
    <thead>
        <tr>
            <th colspan="{{ count($categorias) + 2 }}" style="font-weight: bold; font-size: 14pt; text-align: center;">
                Relación de Gastos del {{ $from }} al {{ $to }}
            </th>
        </tr>
        <tr></tr>
        <tr>
            <th style="font-weight: bold;">Suma de Monto</th>
            <th colspan="{{ count($categorias) + 1 }}" style="font-weight: bold;">Etiquetas de columna</th>
        </tr>
        <tr style="background-color: #1B365D; color: #FFFFFF; font-weight: bold;">
            <th style="background-color: #1B365D; color: #FFFFFF; font-weight: bold; border: 1px solid #000000;">
                Etiquetas de fila
            </th>
            @foreach ($categorias as $cat)
                <th style="background-color: #1B365D; color: #FFFFFF; font-weight: bold; text-align: right; border: 1px solid #000000;">
                    {{ $cat->categoria }}
                </th>
            @endforeach
            <th style="background-color: #1B365D; color: #FFFFFF; font-weight: bold; text-align: right; border: 1px solid #000000;">
                Total general
            </th>
        </tr>
    </thead>
    <tbody>
        @foreach ($filas as $fila)
            <tr>
                <td style="border: 1px solid #D9D9D9;">{{ $fila['nombre'] }}</td>
                @foreach ($categorias as $cat)
                    @php
                        $val = $fila['valores'][$cat->id] ?? 0.0;
                    @endphp
                    <td style="text-align: right; border: 1px solid #D9D9D9;">
                        {{ $val > 0 ? number_format($val, 2, '.', '') : '' }}
                    </td>
                @endforeach
                <td style="text-align: right; font-weight: bold; border: 1px solid #D9D9D9;">
                    {{ number_format($fila['total'], 2, '.', '') }}
                </td>
            </tr>
        @endforeach
    </tbody>
    <tfoot>
        <tr style="font-weight: bold; background-color: #F2F2F2;">
            <td style="font-weight: bold; border: 1px solid #000000;">Total general</td>
            @foreach ($categorias as $cat)
                @php
                    $colVal = $totales_columnas[$cat->id] ?? 0.0;
                @endphp
                <td style="text-align: right; font-weight: bold; border: 1px solid #000000;">
                    {{ $colVal > 0 ? number_format($colVal, 2, '.', '') : '' }}
                </td>
            @endforeach
            <td style="text-align: right; font-weight: bold; border: 1px solid #000000; background-color: #E2EFDA;">
                {{ number_format($gran_total, 2, '.', '') }}
            </td>
        </tr>
    </tfoot>
</table>
@endif
