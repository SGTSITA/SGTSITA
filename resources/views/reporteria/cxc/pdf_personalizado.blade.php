<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estado de Cuenta - Transporte</title>
    <style>
        @page {
            size: letter landscape;
            margin: 8mm 6mm 8mm 6mm;
        }

        body {
            font-family: Arial, sans-serif;
            font-size: 7px;
            color: #1a1a1a;
            margin: 0;
            padding: 0;
        }

        .header-title {
            font-size: 13px;
            font-weight: bold;
            color: #0b2545;
            margin-bottom: 2px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .header-note {
            font-size: 6.5px;
            color: #555555;
            font-style: italic;
            margin-bottom: 6px;
            line-height: 1.2;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 7.5px;
        }

        .meta-table td {
            padding: 3px 6px;
            border: 1px solid #c0c0c0;
        }

        .meta-label {
            font-weight: bold;
            background-color: #f1f3f5;
            color: #2b2b2b;
            width: 14%;
        }

        .meta-value {
            background-color: #ffffff;
            color: #000000;
            width: 19%;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 6.5px;
            table-layout: auto;
        }

        .data-table th {
            background-color: #1e3a5f;
            color: #ffffff;
            font-weight: bold;
            padding: 4px 2px;
            border: 1px solid #0f243d;
            text-align: center;
            vertical-align: middle;
            line-height: 1.1;
        }

        .data-table th.th-calc {
            background-color: #3b597d;
        }

        .data-table td {
            padding: 3px 2px;
            border: 1px solid #d0d0d0;
            vertical-align: middle;
            text-align: center;
        }

        .data-table td.text-start {
            text-align: left;
            padding-left: 3px;
        }

        .data-table td.text-end {
            text-align: right;
            padding-right: 3px;
        }

        .data-table td.col-calc {
            background-color: #f2f4f7;
            font-weight: 600;
        }

        .data-table tr.total-row td {
            background-color: #e9ecef;
            font-weight: bold;
            border-top: 2px solid #0f243d;
            border-bottom: 2px solid #0f243d;
        }

        .nowrap {
            white-space: nowrap;
        }
    </style>
</head>
<body>
    @php
        // Extraer fechas mínima y máxima
        $fechas = $cotizaciones->map(function ($c) {
            $asignacion = optional(optional($c->DocCotizacion)->Asignaciones);
            return $asignacion->fehca_inicio_guard ?? $c->fecha_entrega ?? ($c->created_at ? $c->created_at->format('Y-m-d') : null);
        })->filter()->sort()->values();

        $periodoDel = $fechas->first() ? \Carbon\Carbon::parse($fechas->first())->format('Y-m-d') : date('Y-m-d');
        $periodoAl = $fechas->last() ? \Carbon\Carbon::parse($fechas->last())->format('Y-m-d') : date('Y-m-d');

        $primera = $cotizaciones->first();
        $proveedorHeader = $primera?->Empresa?->nombre 
            ?? $primera?->Proveedor?->nombre 
            ?? 'GC / YOALPE';

        $folioEdoCuenta = $primera?->estadoCuenta?->numero 
            ?? (request('numero_edo_cuenta') ? optional(\App\Models\Estado_Cuenta::find(request('numero_edo_cuenta')))->numero : 'S/F');

        $totalContenedores = $cotizaciones->count();
        $fechaEnvio = date('Y-m-d');

        // Totales acumulados
        $sumPrecioViaje = 0;
        $sumManiobra = 0;
        $sumBurreo = 0;
        $sumEstadia = 0;
        $sumSobrepeso = 0;
        $sumOtros = 0;
        $sumTotalServicio = 0;
        $sumBaseFacturada = 0;
        $sumIva = 0;
        $sumRetencion = 0;
        $sumTotalFactura = 0;
        $sumRestoSinFactura = 0;
        $sumTotalAPagar = 0;
        $sumPeso = 0;

        foreach ($cotizaciones as $c) {
            $pv = floatval($c->precio_viaje ?? 0);
            $man = floatval($c->maniobra ?? 0);
            $bur = floatval($c->burreo ?? 0);
            $est = floatval($c->estadia ?? 0);
            $sp = floatval($c->precio_sobre_peso ?? $c->sobrepeso ?? 0);
            $ot = floatval($c->otro ?? 0);
            $ts = $pv + $man + $bur + $est + $sp + $ot;

            $bf = floatval($c->base_factura ?? 0);
            $iv = floatval($c->iva ?? 0);
            $ret = floatval($c->retencion ?? 0);
            $tf = $bf + $iv - $ret;
            $rs = $ts - $bf;
            $tap = $tf + $rs;
            $pesoVal = floatval($c->peso_contenedor ?? 0);

            $sumPrecioViaje += $pv;
            $sumManiobra += $man;
            $sumBurreo += $bur;
            $sumEstadia += $est;
            $sumSobrepeso += $sp;
            $sumOtros += $ot;
            $sumTotalServicio += $ts;
            $sumBaseFacturada += $bf;
            $sumIva += $iv;
            $sumRetencion += $ret;
            $sumTotalFactura += $tf;
            $sumRestoSinFactura += $rs;
            $sumTotalAPagar += $tap;
            $sumPeso += $pesoVal;
        }
    @endphp

    <div class="header-title">ESTADO DE CUENTA · TRANSPORTE</div>

    <!-- Metadata del estado de cuenta -->
    <table class="meta-table">
        <tr>
            <td class="meta-label">Proveedor</td>
            <td class="meta-value">{{ $proveedorHeader }}</td>
            <td class="meta-label">Folio del estado de cuenta / corte</td>
            <td class="meta-value" style="font-weight: bold; color: #0b2545;">{{ $folioEdoCuenta }}</td>
            <td class="meta-label">Contenedores en este envío</td>
            <td class="meta-value" style="font-weight: bold;">{{ $totalContenedores }}</td>
        </tr>
        <tr>
            <td class="meta-label">Periodo del</td>
            <td class="meta-value">{{ $periodoDel }}</td>
            <td class="meta-label">Periodo al</td>
            <td class="meta-value">{{ $periodoAl }}</td>
            <td class="meta-label">TOTAL DEL PERIODO</td>
            <td class="meta-value" style="font-weight: bold; color: #0f5132; background-color: #d1e7dd;">
                ${{ number_format($sumTotalAPagar, 2, '.', ',') }}
            </td>
        </tr>
        <tr>
            <td class="meta-label">Fecha de envío</td>
            <td class="meta-value">{{ $fechaEnvio }}</td>
            <td class="meta-value" colspan="4" style="background-color: #ffffff; border-color: #ffffff;"></td>
        </tr>
    </table>

    <!-- Tabla Detallada de 20 Columnas -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">Fecha del viaje</th>
                <th style="width: 7%;">Contenedor</th>
                <th style="width: 6%;">Importadora</th>
                <th style="width: 9%;">Transportista</th>
                <th style="width: 6%;">Destino</th>
                <th style="width: 4%;">Peso (ton)</th>
                <th style="width: 5%;">Precio del viaje</th>
                <th style="width: 4%;">Maniobra</th>
                <th style="width: 4%;">Burreo</th>
                <th style="width: 4%;">Estadía</th>
                <th style="width: 4%;">Sobrepeso</th>
                <th style="width: 4%;">Otros</th>
                <th style="width: 6%;" class="th-calc">TOTAL SERVICIO</th>
                <th style="width: 5%;">Base facturada</th>
                <th style="width: 4%;">IVA</th>
                <th style="width: 4%;">Retención</th>
                <th style="width: 5%;" class="th-calc">TOTAL FACTURA</th>
                <th style="width: 5%;" class="th-calc">Resto sin factura</th>
                <th style="width: 6%;" class="th-calc">TOTAL A PAGAR</th>
                <th style="width: 9%;">Observaciones</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cotizaciones as $c)
                @php
                    $asignacion = optional(optional($c->DocCotizacion)->Asignaciones);
                    $fechaV = $asignacion->fehca_inicio_guard ?? $c->fecha_entrega ?? ($c->created_at ? $c->created_at->format('Y-m-d') : '');
                    $fechaVStr = !empty($fechaV) ? \Carbon\Carbon::parse($fechaV)->format('Y-m-d') : '-';

                    $numCont = optional($c->DocCotizacion)->num_contenedor ?? $c->num_contenedor ?? 'S/N';
                    $importadora = optional($c->Subcliente)->nombre ?? optional($c->Cliente)->nombre ?? '-';
                    $transp = optional($asignacion->Proveedor)->nombre 
                        ?? optional($asignacion->Camion)->texto_select 
                        ?? optional($asignacion->Camion)->marca 
                        ?? optional($c->Proveedor)->nombre 
                        ?? 'PROPIO';

                    $dest = $c->destino ?? '-';
                    $pesoV = floatval($c->peso_contenedor ?? 0);
                    $pv = floatval($c->precio_viaje ?? 0);
                    $man = floatval($c->maniobra ?? 0);
                    $bur = floatval($c->burreo ?? 0);
                    $est = floatval($c->estadia ?? 0);
                    $sp = floatval($c->precio_sobre_peso ?? $c->sobrepeso ?? 0);
                    $ot = floatval($c->otro ?? 0);
                    $ts = $pv + $man + $bur + $est + $sp + $ot;

                    $bf = floatval($c->base_factura ?? 0);
                    $iv = floatval($c->iva ?? 0);
                    $ret = floatval($c->retencion ?? 0);
                    $tf = $bf + $iv - $ret;
                    $rs = $ts - $bf;
                    $tap = $tf + $rs;

                    $obs = !empty($c->observaciones_edo_cuenta) 
                        ? $c->observaciones_edo_cuenta 
                        : ($c->observaciones ?? $c->comentarios ?? '');
                @endphp
                <tr>
                    <td class="nowrap">{{ $fechaVStr }}</td>
                    <td class="nowrap" style="font-weight: bold;">{{ $numCont }}</td>
                    <td class="text-start">{{ $importadora }}</td>
                    <td class="text-start">{{ $transp }}</td>
                    <td class="text-start">{{ $dest }}</td>
                    <td>{{ $pesoV > 0 ? number_format($pesoV, 3, '.', '') : '' }}</td>
                    <td class="text-end">{{ $pv > 0 ? number_format($pv, 2, '.', ',') : '' }}</td>
                    <td class="text-end">{{ $man > 0 ? number_format($man, 2, '.', ',') : '' }}</td>
                    <td class="text-end">{{ $bur > 0 ? number_format($bur, 2, '.', ',') : '' }}</td>
                    <td class="text-end">{{ $est > 0 ? number_format($est, 2, '.', ',') : '' }}</td>
                    <td class="text-end">{{ $sp > 0 ? number_format($sp, 2, '.', ',') : '' }}</td>
                    <td class="text-end">{{ $ot > 0 ? number_format($ot, 2, '.', ',') : '' }}</td>
                    <td class="text-end col-calc">{{ number_format($ts, 2, '.', ',') }}</td>
                    <td class="text-end">{{ $bf > 0 ? number_format($bf, 2, '.', ',') : '' }}</td>
                    <td class="text-end">{{ $iv > 0 ? number_format($iv, 2, '.', ',') : '' }}</td>
                    <td class="text-end">{{ $ret > 0 ? number_format($ret, 2, '.', ',') : '' }}</td>
                    <td class="text-end col-calc">{{ number_format($tf, 2, '.', ',') }}</td>
                    <td class="text-end col-calc">{{ number_format($rs, 2, '.', ',') }}</td>
                    <td class="text-end col-calc" style="color: #0f5132;">{{ number_format($tap, 2, '.', ',') }}</td>
                    <td class="text-start">{{ $obs }}</td>
                </tr>
            @endforeach
            <!-- Fila de Totales Generales -->
            <tr class="total-row">
                <td colspan="5" style="text-align: right; padding-right: 6px;">TOTALES ({{ $totalContenedores }} cont.):</td>
                <td>{{ number_format($sumPeso, 3, '.', '') }}</td>
                <td class="text-end">{{ number_format($sumPrecioViaje, 2, '.', ',') }}</td>
                <td class="text-end">{{ number_format($sumManiobra, 2, '.', ',') }}</td>
                <td class="text-end">{{ number_format($sumBurreo, 2, '.', ',') }}</td>
                <td class="text-end">{{ number_format($sumEstadia, 2, '.', ',') }}</td>
                <td class="text-end">{{ number_format($sumSobrepeso, 2, '.', ',') }}</td>
                <td class="text-end">{{ number_format($sumOtros, 2, '.', ',') }}</td>
                <td class="text-end col-calc">{{ number_format($sumTotalServicio, 2, '.', ',') }}</td>
                <td class="text-end">{{ number_format($sumBaseFacturada, 2, '.', ',') }}</td>
                <td class="text-end">{{ number_format($sumIva, 2, '.', ',') }}</td>
                <td class="text-end">{{ number_format($sumRetencion, 2, '.', ',') }}</td>
                <td class="text-end col-calc">{{ number_format($sumTotalFactura, 2, '.', ',') }}</td>
                <td class="text-end col-calc">{{ number_format($sumRestoSinFactura, 2, '.', ',') }}</td>
                <td class="text-end col-calc" style="color: #0f5132;">{{ number_format($sumTotalAPagar, 2, '.', ',') }}</td>
                <td></td>
            </tr>
        </tbody>
    </table>
</body>
</html>
