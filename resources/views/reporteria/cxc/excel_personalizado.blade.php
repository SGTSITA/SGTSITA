<table>
    @php
        // Extraer fechas mínima y máxima del viaje
        $fechas = $cotizaciones->map(function ($c) {
            $asignacion = optional(optional($c->DocCotizacion)->Asignaciones);
            return $asignacion->fehca_inicio_guard 
                ?? $asignacion->fecha_inicio 
                ?? $c->fecha_entrega 
                ?? ($c->created_at ? $c->created_at->format('Y-m-d') : null);
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

    <!-- Fila 1: Título -->
    <tr>
        <td>ESTADO DE CUENTA · TRANSPORTE</td>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    </tr>

    <!-- Fila 2: Espacio en blanco -->
    <tr>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    </tr>

    <!-- Fila 3: Proveedor & Folio -->
    <tr>
        <td>Proveedor</td>
        <td>{{ $proveedorHeader }}</td>
        <td></td>
        <td></td>
        <td>Folio del estado de cuenta / corte</td>
        <td></td>
        <td>{{ $folioEdoCuenta }}</td>
        <td></td>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    </tr>

    <!-- Fila 4: Periodo del & Contenedores -->
    <tr>
        <td>Periodo del</td>
        <td>{{ $periodoDel }}</td>
        <td></td>
        <td></td>
        <td>Contenedores en este envío</td>
        <td></td>
        <td>{{ $totalContenedores }}</td>
        <td></td>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    </tr>

    <!-- Fila 5: Periodo al & TOTAL DEL PERIODO -->
    <tr>
        <td>Periodo al</td>
        <td>{{ $periodoAl }}</td>
        <td></td>
        <td></td>
        <td>TOTAL DEL PERIODO</td>
        <td></td>
        <td>{{ $sumTotalAPagar }}</td>
        <td></td>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    </tr>

    <!-- Fila 6: Fecha de envío -->
    <tr>
        <td>Fecha de envío</td>
        <td>{{ $fechaEnvio }}</td>
        <td></td>
        <td></td>
        <td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    </tr>

    <!-- Fila 7: Espacio en blanco antes de tabla -->
    <tr>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
        <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
    </tr>

    <!-- Fila 8: Encabezados de la tabla (20 columnas) -->
    <thead>
        <tr>
            <th>Fecha del viaje</th>
            <th>Contenedor</th>
            <th>Importadora</th>
            <th>Transportista</th>
            <th>Destino</th>
            <th>Peso (ton)</th>
            <th>Precio del viaje</th>
            <th>Maniobra</th>
            <th>Burreo</th>
            <th>Estadía</th>
            <th>Sobrepeso</th>
            <th>Otros</th>
            <th>TOTAL SERVICIO</th>
            <th>Base facturada</th>
            <th>IVA</th>
            <th>Retención</th>
            <th>TOTAL FACTURA</th>
            <th>Resto sin factura</th>
            <th>TOTAL A PAGAR</th>
            <th>Observaciones (explicar Estadía / Otros)</th>
        </tr>
    </thead>

    <!-- Filas 9+: Datos -->
    <tbody>
        @foreach ($cotizaciones as $c)
            @php
                $asignacion = optional(optional($c->DocCotizacion)->Asignaciones);
                $fechaViaje = $asignacion->fehca_inicio_guard 
                    ?? $asignacion->fecha_inicio 
                    ?? $c->fecha_entrega 
                    ?? ($c->created_at ? $c->created_at->format('Y-m-d') : '');
                if ($fechaViaje) {
                    $fechaViaje = \Carbon\Carbon::parse($fechaViaje)->format('Y-m-d');
                }

                $contenedor = optional($c->DocCotizacion)->num_contenedor ?? $c->num_contenedor ?? 'S/C';

                $importadora = optional($c->Subcliente)->nombre 
                    ?? optional($c->Cliente)->nombre 
                    ?? optional($c->Cliente)->nombre_empresa 
                    ?? 'S/I';

                $camion = optional($asignacion)->Camion;
                $transportista = optional(optional($asignacion)->Proveedor)->nombre
                    ?? ($camion ? ($camion->id_equipo . ' - ' . $camion->marca) : null)
                    ?? optional($c->Proveedor)->nombre
                    ?? 'S/T';

                $destino = $c->destino ?? $c->localidad ?? $c->direccion ?? '';
                $peso = floatval($c->peso_contenedor ?? 0);

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
                <td>{{ $fechaViaje }}</td>
                <td>{{ $contenedor }}</td>
                <td>{{ $importadora }}</td>
                <td>{{ $transportista }}</td>
                <td>{{ $destino }}</td>
                <td>{{ $peso > 0 ? $peso : '' }}</td>
                <td>{{ $pv > 0 ? $pv : '' }}</td>
                <td>{{ $man > 0 ? $man : '' }}</td>
                <td>{{ $bur > 0 ? $bur : '' }}</td>
                <td>{{ $est > 0 ? $est : '' }}</td>
                <td>{{ $sp > 0 ? $sp : '' }}</td>
                <td>{{ $ot > 0 ? $ot : '' }}</td>
                <td>{{ $ts }}</td>
                <td>{{ $bf > 0 ? $bf : '' }}</td>
                <td>{{ $iv > 0 ? $iv : '' }}</td>
                <td>{{ $ret > 0 ? $ret : '' }}</td>
                <td>{{ $tf }}</td>
                <td>{{ $rs }}</td>
                <td>{{ $tap }}</td>
                <td>{{ $obs }}</td>
            </tr>
        @endforeach

        <!-- Fila de Totales -->
        <tr>
            <td>TOTALES</td>
            <td></td>
            <td></td>
            <td></td>
            <td></td>
            <td>{{ $sumPeso }}</td>
            <td>{{ $sumPrecioViaje }}</td>
            <td>{{ $sumManiobra }}</td>
            <td>{{ $sumBurreo }}</td>
            <td>{{ $sumEstadia }}</td>
            <td>{{ $sumSobrepeso }}</td>
            <td>{{ $sumOtros }}</td>
            <td>{{ $sumTotalServicio }}</td>
            <td>{{ $sumBaseFacturada }}</td>
            <td>{{ $sumIva }}</td>
            <td>{{ $sumRetencion }}</td>
            <td>{{ $sumTotalFactura }}</td>
            <td>{{ $sumRestoSinFactura }}</td>
            <td>{{ $sumTotalAPagar }}</td>
            <td></td>
        </tr>
    </tbody>
</table>
