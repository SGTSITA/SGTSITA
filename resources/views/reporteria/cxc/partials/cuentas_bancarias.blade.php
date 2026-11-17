    @php
        $empresaActual = $empresaCuentaId ?? auth()->user()?->id_empresa ?? ($cotizaciones->first()?->id_empresa ?? null);
        $empresaActualModel = \App\Models\Empresas::find($empresaActual);
        $esEmpresaGlobal = $esEmpresaGlobal ?? $empresaActualModel?->esGlobalMultiEquipo() ?? false;

        $totalBancarioOficial = 0;
        $totalBancarioNoOficial = 0;
        $totalBancarioVenta = 0;
        foreach ($cotizaciones as $cotizacionBancaria) {
            $baseFactura = (float) ($cotizacionBancaria->base_factura ?? 0);
            $iva = (float) ($cotizacionBancaria->iva ?? 0);
            $retencion = (float) ($cotizacionBancaria->retencion ?? 0);
            $totalOficial = $baseFactura + $iva - $retencion;
            $totalNoOficial = (float) ($cotizacionBancaria->total ?? 0) - $baseFactura - $iva + $retencion;

            $totalBancarioOficial += $totalOficial;
            $totalBancarioNoOficial += $totalNoOficial;
            $totalBancarioVenta += $totalOficial + $totalNoOficial;
        }

        // Recopila los IDs de los proveedores únicos de las cotizaciones, excluyendo NULL
        $proveedoresIds = $cotizaciones->pluck('DocCotizacion.Asignaciones.id_proveedor')->filter()->unique();

        // Carga los proveedores con sus cuentas bancarias usando los IDs recopilados
        $proveedoresConCuentas = App\Models\Proveedor::whereIn('id', $proveedoresIds)->with('CuentasBancarias')->get();

        $cotizacionesPorProveedor = $cotizaciones->groupBy('DocCotizacion.Asignaciones.id_proveedor');
    @endphp

    @php
        // Sacamos el orden de proveedores a partir de las cotizaciones
        $ordenProveedores = $cotizaciones
            ->map(fn($c) => optional(optional($c->DocCotizacion)->Asignaciones)->id_proveedor)
            ->filter()
            ->unique()
            ->values(); // colección con IDs en el orden en que aparecen
    @endphp

    <h3 class="sin_margem"
        style="color: #fff; background: rgb(24, 192, 141); margin-top: 0px; padding: 0px; font-size: 10px">
        {{ $esEmpresaGlobal ? 'Cuentas Bancarias y Desglose' : 'Cuentas Bancarias Proveedores' }}
    </h3>

    <div style="display: flex; justify-content: space-between; align-items: flex-start; width: 100%">
        <!-- Contenedor de la Tabla de Totales Oficiales -->
        @php
            $empresa = $empresaActualModel;
            $esEmpresaPropia = $empresa?->empresa_propia ?? false;

            $bancoBase1 = null;
            if ($esEmpresaPropia) {
                $bancoBase1 = \App\Models\Bancos::withTrashed()
                    ->where('banco_1', true)
                    ->where('id_empresa', $empresaActual)
                    ->first();
            }
        @endphp

        <div style="width: 48%; padding: 0; box-sizing: border-box">
            <h3
                style="
                        text-align: center;
                        font-size: 8px;
                        color: white;
                        background-color: #6289b1;
                        margin: 2px 0;
                        padding: 2px;
                    ">
                Base 1
            </h3>
            <table class="table text-white"
                style="color: #000; width: 100%; padding: 2px; font-size: 6px; border-collapse: collapse">
                @if ($esEmpresaPropia && $bancoBase1)
                    @php
                        $beneficiarioCuenta1 = $bancoBase1->nombre_beneficiario ?? 'No disponible';
                        $totalFacturaProveedor = 0;
                        $facturadosPorProveedor = [];

                        foreach ($cotizaciones as $cotizacion) {
                            $cuenta1 = floatval($cotizacion->base_factura ?? 0) + floatval($cotizacion->iva ?? 0) - floatval($cotizacion->retencion ?? 0);
                            $totalFacturaProveedor += $cuenta1;

                            $facturadoA = optional($cotizacion->Subcliente)->nombre ?? 'N/A';
                            if (!isset($facturadosPorProveedor[$facturadoA])) {
                                $facturadosPorProveedor[$facturadoA] = 0;
                            }
                            $facturadosPorProveedor[$facturadoA] += $cuenta1;
                        }
                    @endphp

                    <thead>
                        <tr style="font-size: 7px; border: 1px solid #000; background-color: #2c3e50; color: white">
                            <th @if (isset($isExcel)) colspan="3" @endif style="padding: 2px; border: 1px solid #000">Cuenta Oficial Empresa</th>
                            <th style="padding: 2px; border: 1px solid #000">Total</th>
                            @foreach ($facturadosPorProveedor as $facturadoA => $total)
                                <th style="padding: 2px; border: 1px solid #000">{{ $facturadoA }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody style="text-align: center; font-size: 6px">
                        <tr style="background-color: #f1f1f1">

                            <td @if (isset($isExcel)) colspan="3" @endif style="padding: 2px; border: 1px solid #ccc">
                                @if (isset($isExcel))
                                    ##ROW_ALTO##
                                @endif
                                {{ $beneficiarioCuenta1 }}
                                <br />
                                {{ $bancoBase1->nombre_banco ?? '-' }}
                                <br />
                                No. {{ $bancoBase1->clabe ?? '-' }}
                                <br />
                            </td>
                            <td style="padding: 2px; border: 1px solid #ccc">
                                ${{ number_format($totalFacturaProveedor, 2, '.', ',') }}
                            </td>
                            @foreach ($facturadosPorProveedor as $total)
                                <td style="padding: 2px; border: 1px solid #ccc">
                                    ${{ number_format($total, 2, '.', ',') }}
                                </td>
                            @endforeach
                        </tr>
                    </tbody>
                @else
                    @php
                        $proveedoresIds = $cotizaciones
                            ->pluck('DocCotizacion.Asignaciones.id_proveedor')
                            ->filter()
                            ->unique();
                        $proveedoresConCuentas = App\Models\Proveedor::whereIn('id', $proveedoresIds)
                            ->with('CuentasBancarias')
                            ->get();
                        $cotizacionesPorProveedor = $cotizaciones->groupBy('DocCotizacion.Asignaciones.id_proveedor');
                    @endphp

                    @foreach ($ordenProveedores as $index => $proveedorId)
                        @php
                            $proveedor = $proveedoresConCuentas->firstWhere('id', $proveedorId);

                            $totalFacturaProveedor = 0;
                            $facturadosPorProveedor = [];
                            $beneficiarioCuenta1 = '';
                            $cuentaCLABE = null;

                            if ($proveedor && isset($cotizacionesPorProveedor[$proveedor->id])) {
                                $cotizacionesProveedor = $cotizacionesPorProveedor[$proveedor->id];
                                foreach ($cotizacionesProveedor as $cotizacion) {
                                    $cuenta1 = floatval($cotizacion->base_factura ?? 0) + floatval($cotizacion->iva ?? 0) - floatval($cotizacion->retencion ?? 0);
                                    $totalFacturaProveedor += $cuenta1;

                                    $facturadoA = optional($cotizacion->Subcliente)->nombre ?? 'N/A';
                                    if (!isset($facturadosPorProveedor[$facturadoA])) {
                                        $facturadosPorProveedor[$facturadoA] = 0;
                                    }
                                    $facturadosPorProveedor[$facturadoA] += $cuenta1;
                                }
                            }

                            if ($proveedor && !$proveedor->CuentasBancarias->isEmpty()) {
                                $cuentaCLABE = $proveedor->CuentasBancarias->firstWhere('cuenta_1', true);

                                $beneficiarioCuenta1 = $cuentaCLABE->nombre_beneficiario ?? 'No disponible';
                            }
                        @endphp

                        @if ($proveedor)
                            <thead>
                                <tr
                                    style="
                                            font-size: 7px;
                                            border: 1px solid #000;
                                            background-color: #2c3e50;
                                            color: white;
                                        ">
                                    <th @if (isset($isExcel)) colspan="3" @endif style="padding: 2px; border: 1px solid #000">
                                        Proveedor - {{ $proveedor->nombre }}
                                    </th>
                                    <th style="padding: 2px; border: 1px solid #000">Total</th>
                                    @foreach ($facturadosPorProveedor as $facturadoA => $total)
                                        <th style="padding: 2px; border: 1px solid #000">{{ $facturadoA }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody style="text-align: center; font-size: 6px">
                                <tr style="background-color: {{ $index % 2 == 0 ? '#f1f1f1' : '#e0e0e0' }}">
                                    <td @if (isset($isExcel)) colspan="3" @endif style="padding: 2px; border: 1px solid #ccc">
                                        @if (isset($isExcel))
                                            ##ROW_ALTO##
                                        @endif
                                        {{ $beneficiarioCuenta1 }}
                                        <br />
                                        {{ $cuentaCLABE->nombre_banco ?? '-' }}
                                        <br />
                                        No. {{ $cuentaCLABE->cuenta_clabe ?? '-' }}
                                    </td>
                                    <td style="padding: 2px; border: 1px solid #ccc">
                                        ${{ number_format($totalFacturaProveedor, 2, '.', ',') }}
                                    </td>
                                    @foreach ($facturadosPorProveedor as $total)
                                        <td style="padding: 2px; border: 1px solid #ccc">
                                            ${{ number_format($total, 2, '.', ',') }}
                                        </td>
                                    @endforeach
                                </tr>
                            </tbody>
                        @endif
                    @endforeach
                @endif
            </table>
        </div>

        <!-- Contenedor de la Tabla de Totales No Oficiales -->
        <div style="width: 48%; padding: 0; box-sizing: border-box">
            <h3
                style="
                        text-align: center;
                        font-size: 8px;
                        color: white;
                        background-color: #0080ff;
                        margin: 2px 0;
                        padding: 2px;
                    ">
                Base 2
            </h3>

            @php
                $bancoGlobal = \App\Models\Bancos::where('cuenta_global', 1)->where('id_empresa', $empresaActual)->first();

                $cuentaGlobal = $bancoGlobal
                    ? [
                        'beneficiario' => $bancoGlobal->nombre_beneficiario ?? '---',
                        'banco' => $bancoGlobal->nombre_banco ?? '---',
                        'clabe' => $bancoGlobal->clabe ?? '---',
                    ]
                    : [
                        'beneficiario' => '---',
                        'banco' => '---',
                        'clabe' => '---',
                    ];

                $subclientesUnicos = [];
                $totalesPorProveedor = [];

                foreach ($cotizaciones as $cotizacion) {
                    $proveedorId = optional(optional($cotizacion->DocCotizacion)->Asignaciones)->id_proveedor;
                    $baseTaref =
                        floatval($cotizacion->total ?? 0) -
                        floatval($cotizacion->base_factura ?? 0) -
                        floatval($cotizacion->iva ?? 0) +
                        floatval($cotizacion->retencion ?? 0);
                    $subcliente = optional($cotizacion->Subcliente)->nombre ?? 'N/A';

                    if (!isset($totalesPorProveedor[$proveedorId])) {
                        $totalesPorProveedor[$proveedorId] = [
                            'nombre' =>
                                optional(optional($cotizacion->DocCotizacion)->Asignaciones)->Proveedor->nombre ??
                                'Proveedor desconocido',
                            'total' => 0,
                            'subclientes' => [],
                        ];
                    }

                    $totalesPorProveedor[$proveedorId]['total'] += $baseTaref;
                    $totalesPorProveedor[$proveedorId]['subclientes'][$subcliente] =
                        ($totalesPorProveedor[$proveedorId]['subclientes'][$subcliente] ?? 0) + $baseTaref;
                    $subclientesUnicos[$subcliente] = true;
                }

                $subclientesLista = array_keys($subclientesUnicos);
            @endphp

            <table class="table text-white"
                style="color: #000; width: 100%; padding: 0; font-size: 7px; border-collapse: collapse">
                <thead>
                    <tr style="font-size: 7px; background-color: #2c3e50; color: white">
                        <th @if (isset($isExcel)) colspan="3" @endif style="padding: 2px; border: 1px solid #000">
                            {{-- si hay cuenta global mostramos ese título, si no ponemos "Cuenta Bancaria Proveedor" o el nombre que quieras --}}
                            {{ $bancoGlobal ? 'Cuenta Global' : 'Cuenta Bancaria Proveedor' }}
                        </th>
                        <th style="padding: 2px; border: 1px solid #000">{{ $esEmpresaGlobal ? "Unidad / Detalle" : "Proveedor" }}</th>
                        <th style="padding: 2px; border: 1px solid #000">Total</th>
                        @foreach ($subclientesLista as $subcliente)
                            <th style="padding: 2px; border: 1px solid #000">{{ $subcliente }}</th>
                        @endforeach
                    </tr>
                </thead>

                <tbody>
                    @foreach ($totalesPorProveedor as $proveedorId => $prov)
                        @php
                            $proveedorModel = $proveedoresConCuentas->firstWhere('id', $proveedorId);
                            $cuenta2 = $proveedorModel?->CuentasBancarias->where('cuenta_2', true)->first();
                        @endphp

                        <tr style="background-color: {{ $loop->odd ? '#f1f1f1' : '#e0e0e0' }}">
                            {{-- Columna de cuenta bancaria --}}

                            @if ($loop->first && $bancoGlobal)
                                <td rowspan="{{ count($totalesPorProveedor) }}" @if (isset($isExcel)) colspan="3" @endif
                                    style="
                                            padding: 2px;
                                            border: 1px solid #ccc;
                                            text-align: center;
                                            vertical-align: middle;
                                        ">
                                    @if (isset($isExcel))
                                        ##ROW_ALTO##
                                    @endif
                                    {{ $cuentaGlobal['beneficiario'] }}
                                    <br />
                                    {{ $cuentaGlobal['banco'] }}
                                    <br />
                                    No. {{ $cuentaGlobal['clabe'] }}
                                </td>
                            @elseif (!$bancoGlobal)
                                <td @if (isset($isExcel)) colspan="3" @endif style="padding: 2px; border: 1px solid #ccc; text-align: center">
                                    @if (isset($isExcel))
                                        ##ROW_ALTO##
                                    @endif
                                    {{ $cuenta2?->nombre_beneficiario ?? 'No disponible' }}
                                    <br />
                                    {{ $cuenta2?->nombre_banco ?? '-' }}
                                    <br />
                                    No. {{ $cuenta2?->cuenta_clabe ?? '-' }}
                                </td>
                            @endif

                            {{-- Nombre proveedor --}}
                            <td style="border: 1px solid #ccc">{{ $prov['nombre'] }}</td>

                            {{-- Total --}}
                            <td style="border: 1px solid #ccc">
                                ${{ number_format($prov['total'], 2, '.', ',') }}
                            </td>

                            {{-- Columnas por subcliente --}}
                            @foreach ($subclientesLista as $subcliente)
                                <td style="padding: 2px; border: 1px solid #ccc">
                                    @php
                                        $monto = $prov['subclientes'][$subcliente] ?? 0;
                                    @endphp

                                    {{ $monto > 0 ? '$' . number_format($monto, 2, '.', ',') : '-' }}
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="totales">
        <h3 class="margin_cero" style="color: #000000; background: rgb(0, 174, 255); margin-top: 5px">Totales</h3>
        <p class="margin_cero">
            Total oficial:
            <b class="margin_cero;margin-top: 5px;">${{ number_format($totalBancarioOficial, 2, '.', ',') }}</b>
        </p>
        <p class="margin_cero">
            Total no oficial:
            <b class="margin_cero;margin-top: 5px;">${{ number_format($totalBancarioNoOficial, 2, '.', ',') }}</b>
        </p>
        <p class="margin_cero">
            Importe vta:
            <b class="margin_cero;margin-top: 5px;">${{ number_format($totalBancarioVenta, 2, '.', ',') }}</b>
        </p>
    </div>
