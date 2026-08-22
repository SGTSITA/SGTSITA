<table>
    <thead>
        <tr>
            <th colspan="6" style="font-size: 18px; font-weight: bold; text-align: center;">
                Reporte de Préstamos / Adelantos
            </th>
        </tr>
        <tr>
            <th colspan="6" style="font-size: 12px; text-align: center;">
                Estado de cuenta del operador
            </th>
        </tr>
        <tr>
            <th colspan="6" style="text-align: right;">
                Generado: {{ now()->format('d/m/Y H:i') }}
            </th>
        </tr>

        <tr>
            <th colspan="6"></th>
        </tr>

        <tr>
            <th colspan="3" style="font-weight: bold;">Operador:</th>
            <th colspan="3">{{ $operador->nombre ?? 'S/N' }}</th>
        </tr>
        <tr>
            <th colspan="3" style="font-weight: bold;">CURP:</th>
            <th colspan="3">{{ $operador->curp ?? 'S/N' }}</th>
        </tr>
        <tr>
            <th colspan="3" style="font-weight: bold;">Teléfono:</th>
            <th colspan="3">{{ $operador->telefono ?? 'S/N' }}</th>
        </tr>
        <tr>
            <th colspan="3" style="font-weight: bold;">Correo:</th>
            <th colspan="3">{{ $operador->correo ?? 'S/N' }}</th>
        </tr>

        <tr>
            <th colspan="6"></th>
        </tr>

        <tr>
            <th style="font-weight: bold; background-color: #1f2937; color: #ffffff;">Total préstamos</th>
            <th style="font-weight: bold; background-color: #1f2937; color: #ffffff;">Total adelantos</th>
            <th style="font-weight: bold; background-color: #1f2937; color: #ffffff;">Total deuda</th>
            <th style="font-weight: bold; background-color: #1f2937; color: #ffffff;">Total abonos</th>
            <th colspan="2" style="font-weight: bold; background-color: #1f2937; color: #ffffff;">Saldo final</th>
        </tr>
        <tr>
            <td>${{ number_format($totalPrestamos ?? 0, 2) }}</td>
            <td>${{ number_format($totalAdelantos ?? 0, 2) }}</td>
            <td>${{ number_format($totalDeuda ?? 0, 2) }}</td>
            <td>${{ number_format($totalAbonos ?? 0, 2) }}</td>
            <td colspan="2">${{ number_format($saldoFinal ?? 0, 2) }}</td>
        </tr>

        <tr>
            <th colspan="6"></th>
        </tr>
    </thead>

    <tbody>
        @forelse ($prestamos as $index => $prestamo)
            @php
                $abonado = $prestamo->pagoprestamos->sum('monto_pago');
                $saldo = ($prestamo->cantidad ?? 0) - $abonado;
                $estatus = $saldo <= 0 ? 'Pagado' : 'Pendiente';
            @endphp

            <tr>
                <td colspan="6" style="font-weight: bold; background-color: #f1f5f9;">
                    #{{ $index + 1 }} - {{ $prestamo->tipo ?? 'Movimiento' }}
                    | Estatus: {{ $estatus }}
                </td>
            </tr>

            <tr>
                <td style="font-weight: bold;">Fecha creación</td>
                <td>{{ optional($prestamo->created_at)->format('d/m/Y') }}</td>

                <td style="font-weight: bold;">Fecha aplicación</td>
                <td>{{ optional($prestamo->fecha_prestamo ?: $prestamo->created_at)->format('d/m/Y') }}</td>

                <td style="font-weight: bold;">Banco salida</td>
                <td>{{ $prestamo->banco->nombre ?? ($prestamo->banco->nombre_banco ?? 'S/N') }}</td>
            </tr>

            <tr>
                <td style="font-weight: bold;">Cantidad</td>
                <td>${{ number_format($prestamo->cantidad ?? 0, 2) }}</td>

                <td style="font-weight: bold;">Abonado</td>
                <td>${{ number_format($abonado ?? 0, 2) }}</td>

                <td style="font-weight: bold;">Saldo</td>
                <td>${{ number_format($saldo ?? 0, 2) }}</td>
            </tr>

            <tr>
                <td colspan="6" style="font-weight: bold;">
                    Historial de abonos
                </td>
            </tr>

            @if ($prestamo->pagoprestamos->count() > 0)
                <tr>
                    <th style="font-weight: bold; background-color: #e5e7eb;">#</th>
                    <th style="font-weight: bold; background-color: #e5e7eb;">Fecha</th>
                    <th style="font-weight: bold; background-color: #e5e7eb;">Monto abonado</th>
                    <th style="font-weight: bold; background-color: #e5e7eb;">Saldo después</th>
                    <th colspan="2" style="font-weight: bold; background-color: #e5e7eb;">Referencia</th>
                </tr>

                @php
                    $saldoTemporal = $prestamo->cantidad ?? 0;
                @endphp

                @foreach ($prestamo->pagoprestamos as $i => $pago)
                    @php
                        $saldoTemporal -= $pago->monto_pago ?? 0;

                        $referenciaPago = $pago->referencia;

                        if (!$referenciaPago && $pago->liquidacion) {
                            $contenedores = optional($pago->liquidacion->Viajes)
                                ->map(function ($viaje) {
                                    return optional($viaje->Contenedores)->num_contenedor;
                                })
                                ->filter()
                                ->implode(' / ');

                            $referenciaPago = $contenedores ?: 'Liquidación #' . $pago->liquidacion->id;
                        }
                    @endphp

                    <tr>
                        <td>{{ $i + 1 }}</td>
                        <td>{{ optional($pago->fecha_pago ?: $pago->created_at)->format('d/m/Y') }}</td>
                        <td>${{ number_format($pago->monto_pago ?? 0, 2) }}</td>
                        <td>${{ number_format($saldoTemporal, 2) }}</td>
                        <td colspan="2">{{ $referenciaPago ?? ' ' }}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="6" style="background-color: #fff7ed; color: #9a3412;">
                        Este préstamo/adelanto aún no tiene abonos registrados.
                    </td>
                </tr>
            @endif

            <tr>
                <td colspan="6"></td>
            </tr>
        @empty
            <tr>
                <td colspan="6" style="background-color: #fff7ed; color: #9a3412;">
                    No hay préstamos o adelantos registrados para este operador.
                </td>
            </tr>
        @endforelse

        <tr>
            <td colspan="6" style="text-align: center;">
                Reporte generado automáticamente
            </td>
        </tr>
    </tbody>
</table>
