<table>
    <thead>
        <tr>
            <th colspan="10" style="font-size: 14pt; font-weight: bold; text-align: center; color: #1F497D;">
                {{ $nombresEmpresas }}
            </th>
        </tr>
        <tr>
            <th colspan="10" style="font-size: 12pt; font-weight: bold; text-align: center;">
                LIBRO DIARIO DE PÓLIZAS CONTABLES • CÓDIGO CONAC LD-06
            </th>
        </tr>
        <tr>
            <th colspan="10" style="font-size: 10pt; font-style: italic; text-align: center; color: #595959;">
                @if($equipoNombre) UNIDAD: {{ $equipoNombre }} | @else CONSOLIDADO GLOBAL | @endif
                DEL {{ \Carbon\Carbon::parse($data['fecha_inicio'] ?? $fechaCorte)->format('d/m/Y') }} AL {{ \Carbon\Carbon::parse($fechaCorte)->format('d/m/Y') }} • TOTAL PÓLIZAS: {{ $data['total_polizas'] }}
            </th>
        </tr>
        <tr>
            <th colspan="10"></th>
        </tr>
        <tr style="background-color: #1F497D; color: #FFFFFF; font-weight: bold; font-size: 9pt; text-align: center;">
            <th style="width: 85px;">FECHA</th>
            <th style="width: 75px;">TIPO</th>
            <th style="width: 130px;">PÓLIZA #</th>
            <th style="width: 250px; text-align: left;">CONCEPTO GENERAL</th>
            <th style="width: 110px;">CÓDIGO CUENTA</th>
            <th style="width: 250px; text-align: left;">NOMBRE DE LA CUENTA</th>
            <th style="width: 220px; text-align: left;">CONCEPTO DE LA PARTIDA</th>
            <th style="width: 160px; text-align: left;">REFERENCIA / CONTENEDOR</th>
            <th style="width: 110px; text-align: right;">CARGOS (DEBE)</th>
            <th style="width: 110px; text-align: right;">ABONOS (HABER)</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($data['polizas'] as $p)
            @foreach ($p['partidas'] as $idx => $part)
                <tr style="font-size: 8.5pt; {{ $loop->parent->odd ? 'background-color: #FFFFFF;' : 'background-color: #F9FAFB;' }}">
                    @if ($idx === 0)
                        <td rowspan="{{ count($p['partidas']) }}" style="text-align: center; vertical-align: top; font-weight: bold;">
                            {{ $p['fecha_formato'] }}
                        </td>
                        <td rowspan="{{ count($p['partidas']) }}" style="text-align: center; vertical-align: top; text-transform: uppercase;">
                            {{ $p['tipo'] }}
                        </td>
                        <td rowspan="{{ count($p['partidas']) }}" style="text-align: center; vertical-align: top; font-family: monospace; font-weight: bold; color: #1F497D;">
                            {{ $p['numero'] }}
                        </td>
                        <td rowspan="{{ count($p['partidas']) }}" style="vertical-align: top;">
                            {{ $p['concepto'] }}
                        </td>
                    @endif
                    <td style="text-align: center; font-family: monospace;">{{ $part['cuenta_codigo'] }}</td>
                    <td>{{ $part['cuenta_nombre'] }}</td>
                    <td>{{ $part['concepto_detalle'] }}</td>
                    <td>{{ $part['referencia'] }}</td>
                    <td style="text-align: right; color: #385723;">{{ $part['debe'] > 0 ? $part['debe'] : '' }}</td>
                    <td style="text-align: right; color: #C00000;">{{ $part['haber'] > 0 ? $part['haber'] : '' }}</td>
                </tr>
            @endforeach
            {{-- Línea de separación sutil entre pólizas --}}
            <tr style="border-bottom: 1px solid #D9D9D9;">
                <td colspan="10" style="height: 2px; padding: 0;"></td>
            </tr>
        @endforeach

        {{-- RESUMEN GLOBAL FINAL --}}
        <tr style="background-color: #1F497D; color: #FFFFFF; font-weight: bold; font-size: 11pt; border-top: 2px solid #000;">
            <td colspan="8" style="text-align: right;">SUMAS IGUALES DEL DIARIO:</td>
            <td style="text-align: right;">{{ $data['total_debe'] }}</td>
            <td style="text-align: right;">{{ $data['total_haber'] }}</td>
        </tr>
    </tbody>
</table>
