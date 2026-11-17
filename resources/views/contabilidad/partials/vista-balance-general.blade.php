{{-- ENCABEZADO FORMAL DEL BALANCE --}}
<div class="text-center border-bottom pb-4 mb-4">
    <span class="badge bg-light text-primary border px-3 py-1 mb-2 fw-semibold" style="letter-spacing: 1px;">
        SISTEMA GESTIÓN TRANSPORTE CONTABLE • FORMATO ESF-01 • PARTIDA DOBLE
    </span>
    <h3 class="fw-bolder text-dark mb-1 text-uppercase" style="letter-spacing: 1.5px;">ESTADO DE SITUACIÓN FINANCIERA / BALANCE GENERAL</h3>
    
    <h5 class="text-secondary fw-normal mb-1">
        @if ($equipoSeleccionado)
            UNIDAD: <strong class="text-dark">{{ $equipoSeleccionado->texto_select }}</strong>
        @else
            CONSOLIDADO GLOBAL DE UNIDADES
        @endif
    </h5>

    <p class="text-muted text-sm mb-0">
        AL <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($fechaCorte)->isoFormat('D [DE] MMMM [DE] YYYY') }}</span>
        @if ($esAdmin && in_array('todas', $seleccionEmpresas))
            <span class="badge bg-secondary text-white ms-2">Consolidado Multi-Empresa</span>
        @endif
    </p>

    {{-- Status de Cuadre Contable --}}
    <div class="mt-2">
        @if ($balance['cuadrado'])
            <span class="badge bg-success-light text-success border border-success px-3 py-1 text-xs">
                <i class="fas fa-check-circle me-1"></i> Balance Cuadrado: Activo = Pasivo + Capital ($ {{ number_format($balance['activo']['total'], 2) }})
            </span>
        @else
            <span class="badge bg-danger-light text-danger border border-danger px-3 py-1 text-xs">
                <i class="fas fa-exclamation-triangle me-1"></i> Diferencia Contable: $ {{ number_format($balance['diferencia'], 2) }}
            </span>
        @endif
    </div>
</div>

{{-- CUERPO DEL BALANCE: DOS COLUMNAS BALANCEADAS --}}
<div class="row g-4 mb-4">

    {{-- COLUMNA IZQUIERDA: ACTIVO --}}
    <div class="col-md-6 border-end pe-md-4">
        <div class="d-flex justify-content-between align-items-center bg-light p-2 px-3 rounded mb-3 border">
            <h6 class="mb-0 fw-bold text-primary text-uppercase" style="letter-spacing: 1px;">
                <i class="fas fa-wallet me-2"></i>ACTIVO
            </h6>
            <span class="text-xs text-muted">Bancos, Clientes y Equipos</span>
        </div>

        <table class="table table-sm table-hover align-middle mb-0">
            <tbody>
                @forelse ($balance['activo']['items'] as $item)
                    <tr class="fila-concepto" style="cursor: pointer;" data-concepto-id="{{ $item['id'] ?? '' }}" data-concepto-nombre="{{ $item['concepto'] }}" data-monto="{{ $item['monto'] }}" title="Clic para ver el desglose contable de {{ $item['concepto'] }}">
                        <td class="text-sm py-2 {{ $item['es_calculado'] ? 'fw-bold text-dark' : 'text-secondary ps-3' }}">
                            @if (!$item['es_calculado'])
                                <i class="fas fa-angle-right text-muted me-1" style="font-size: 10px;"></i>
                            @endif
                            {{ $item['concepto'] }}
                            @if(!empty($item['id']))
                                <span class="badge bg-light text-primary border ms-2 px-1 py-0 opacity-75 d-none d-sm-inline-block" style="font-size: 10px;">
                                    <i class="fas fa-search-dollar me-1"></i>Ver detalle
                                </span>
                            @endif
                        </td>
                        <td class="text-end text-sm py-2 fw-semibold {{ $item['monto'] < 0 ? 'text-danger' : 'text-dark' }}" style="width: 140px;">
                            $ {{ number_format($item['monto'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted py-3 text-xs">Sin registros de activo en este corte.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="table-primary border-top border-2 border-primary">
                    <th class="text-uppercase fw-bolder py-3 text-dark">SUMA DEL ACTIVO</th>
                    <th class="text-end fw-bolder py-3 text-primary fs-6">
                        $ {{ number_format($balance['activo']['total'], 2) }}
                    </th>
                </tr>
            </tfoot>
        </table>
    </div>

    {{-- COLUMNA DERECHA: PASIVO Y PATRIMONIO --}}
    <div class="col-md-6 ps-md-4">

        {{-- SECCIÓN PASIVO --}}
        <div class="d-flex justify-content-between align-items-center bg-light p-2 px-3 rounded mb-3 border">
            <h6 class="mb-0 fw-bold text-danger text-uppercase" style="letter-spacing: 1px;">
                <i class="fas fa-file-invoice-dollar me-2"></i>PASIVO
            </h6>
            <span class="text-xs text-muted">Proveedores y Acreedores</span>
        </div>

        <table class="table table-sm table-hover align-middle mb-4">
            <tbody>
                @forelse ($balance['pasivo']['items'] as $item)
                    <tr class="fila-concepto" style="cursor: pointer;" data-concepto-id="{{ $item['id'] ?? '' }}" data-concepto-nombre="{{ $item['concepto'] }}" data-monto="{{ $item['monto'] }}" title="Clic para ver el desglose contable de {{ $item['concepto'] }}">
                        <td class="text-sm py-2 {{ $item['es_calculado'] ? 'fw-bold text-dark' : 'text-secondary ps-3' }}">
                            @if (!$item['es_calculado'])
                                <i class="fas fa-angle-right text-muted me-1" style="font-size: 10px;"></i>
                            @endif
                            {{ $item['concepto'] }}
                            @if(!empty($item['id']))
                                <span class="badge bg-light text-primary border ms-2 px-1 py-0 opacity-75 d-none d-sm-inline-block" style="font-size: 10px;">
                                    <i class="fas fa-search-dollar me-1"></i>Ver detalle
                                </span>
                            @endif
                        </td>
                        <td class="text-end text-sm py-2 fw-semibold {{ $item['monto'] < 0 ? 'text-danger' : 'text-dark' }}" style="width: 140px;">
                            $ {{ number_format($item['monto'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted py-2 text-xs">Sin pasivos registrados.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="table-light border-top">
                    <th class="text-uppercase fw-bold py-2 text-dark text-xs">TOTAL PASIVO</th>
                    <th class="text-end fw-bold py-2 text-dark text-sm">
                        $ {{ number_format($balance['pasivo']['total'], 2) }}
                    </th>
                </tr>
            </tfoot>
        </table>

        {{-- SECCIÓN PATRIMONIO / CAPITAL --}}
        <div class="d-flex justify-content-between align-items-center bg-light p-2 px-3 rounded mb-3 border">
            <h6 class="mb-0 fw-bold text-success text-uppercase" style="letter-spacing: 1px;">
                <i class="fas fa-chart-pie me-2"></i>CAPITAL CONTABLE / PATRIMONIO
            </h6>
            <span class="text-xs text-muted">Utilidades y Resultados</span>
        </div>

        <table class="table table-sm table-hover align-middle mb-0">
            <tbody>
                @forelse ($balance['patrimonio']['items'] as $item)
                    <tr class="fila-concepto" style="cursor: pointer;" data-concepto-id="{{ $item['id'] ?? '' }}" data-concepto-nombre="{{ $item['concepto'] }}" data-monto="{{ $item['monto'] }}" title="Clic para ver el desglose contable de {{ $item['concepto'] }}">
                        <td class="text-sm py-2 {{ $item['es_calculado'] ? 'fw-bold text-dark' : 'text-secondary ps-3' }}">
                            @if (!$item['es_calculado'])
                                <i class="fas fa-angle-right text-muted me-1" style="font-size: 10px;"></i>
                            @endif
                            {{ $item['concepto'] }}
                            @if(!empty($item['id']))
                                <span class="badge bg-light text-primary border ms-2 px-1 py-0 opacity-75 d-none d-sm-inline-block" style="font-size: 10px;">
                                    <i class="fas fa-search-dollar me-1"></i>Ver detalle
                                </span>
                            @endif
                        </td>
                        <td class="text-end text-sm py-2 fw-semibold {{ $item['monto'] < 0 ? 'text-danger' : 'text-dark' }}" style="width: 140px;">
                            $ {{ number_format($item['monto'], 2) }}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="2" class="text-center text-muted py-2 text-xs">Sin movimientos de capital.</td>
                    </tr>
                @endforelse
            </tbody>
            <tfoot>
                <tr class="table-light border-top">
                    <th class="text-uppercase fw-bold py-2 text-dark text-xs">TOTAL CAPITAL CONTABLE</th>
                    <th class="text-end fw-bold py-2 text-dark text-sm">
                        $ {{ number_format($balance['patrimonio']['total'], 2) }}
                    </th>
                </tr>
                <tr class="table-primary border-top border-2 border-primary">
                    <th class="text-uppercase fw-bolder py-3 text-dark">SUMA DEL PASIVO Y CAPITAL</th>
                    <th class="text-end fw-bolder py-3 text-primary fs-6">
                        $ {{ number_format($balance['total_pasivo_patrimonio'], 2) }}
                    </th>
                </tr>
            </tfoot>
        </table>

    </div>

</div>

{{-- SECCIÓN INFERIOR: RESUMEN DE ESTADO DE RESULTADOS CON COLAPSO INTERACTIVO Y CONCILIACIÓN --}}
<div class="mt-4 pt-4 border-top">
    <div class="card bg-light border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary text-white p-2 rounded-circle">
                    <i class="fas fa-chart-line"></i>
                </span>
                <div>
                    <h6 class="mb-0 fw-bold text-dark">ESTADO DE RESULTADOS (DEL EJERCICIO AL CORTE)</h6>
                    <small class="text-muted">Desglose Operativo por Ejercicio y Conciliación con Situación Patrimonial</small>
                </div>
            </div>
            <div>
                <span class="text-xs text-muted">Periodo: </span>
                <span class="badge bg-light text-dark border">{{ $balance['fecha_inicio_ejercicio'] }} al {{ $balance['fecha_corte'] }}</span>
            </div>
        </div>

        <div class="card-body p-4">
            <div class="row g-4 align-items-start">

                @php
                    $idIngresos = collect($balance['estado_resultados']['items'])->firstWhere('concepto', 'INGRESOS')['id'] ?? null;
                    $idIntereses = collect($balance['estado_resultados']['items'])->firstWhere('concepto', 'intereses')['id'] ?? null;
                    $idGastos = collect($balance['estado_resultados']['items'])->firstWhere('concepto', 'GASTOS')['id'] ?? null;
                @endphp
                {{-- Desglose Numérico con Colapso Interactivo por Ejercicio --}}
                <div class="col-lg-8">
                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0 bg-white rounded shadow-sm border">
                            <tbody>
                                {{-- 1. Fila INGRESOS POR FLETES --}}
                                <tr class="fila-concepto" data-concepto-id="{{ $idIngresos }}" data-concepto-nombre="INGRESOS POR FLETES Y SERVICIOS" title="Clic en la cantidad para desplegar por ejercicio, o clic aquí para ver pólizas">
                                    <td class="py-2 px-3 fw-semibold text-dark">
                                        <i class="fas fa-plus text-success me-2"></i> Ingresos por Fletes y Servicios
                                        @if ($idIngresos)
                                            <span class="badge bg-light text-primary border ms-2 px-1 py-0 opacity-75 d-none d-sm-inline-block" style="font-size: 10px;">
                                                <i class="fas fa-search-dollar me-1"></i>Ver pólizas
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-end fw-bold text-dark td-toggle-collapse" data-bs-toggle="collapse" data-bs-target="#collapseIngresosEjercicios" style="width: 220px; cursor: pointer;" title="Clic para desplegar desglose por ejercicio">
                                        <span>$ {{ number_format($balance['estado_resultados']['ingresos_fletes'], 2) }}</span>
                                        <i class="fas fa-chevron-down text-primary ms-2" style="font-size: 11px;"></i>
                                    </td>
                                </tr>
                                {{-- Sub-filas colapsables de Ingresos por Ejercicio --}}
                                <tr class="collapse bg-light border-0" id="collapseIngresosEjercicios">
                                    <td colspan="2" class="p-0 border-0">
                                        <div class="p-2 ps-4 bg-light border-start border-3 border-success">
                                            <div class="text-xs text-muted mb-1 fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                                <i class="fas fa-calendar-alt text-success me-1"></i> Desglose de Ingresos por Ejercicio:
                                            </div>
                                            <div class="row g-2">
                                                @forelse ($balance['estado_resultados']['desglose_ejercicios'] ?? [] as $anio => $ej)
                                                    <div class="col-sm-6">
                                                        <div class="d-flex justify-content-between p-2 bg-white rounded border">
                                                            <span class="text-xs fw-semibold text-dark">Ingresos {{ $anio }}:</span>
                                                            <span class="text-xs fw-bold text-success">$ {{ number_format($ej['ingresos'], 2) }}</span>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 text-xs text-muted">Sin desglose disponible para el periodo seleccionado.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- 2. Fila INTERESES BANCARIOS --}}
                                <tr class="table-info fila-concepto" data-concepto-id="{{ $idIntereses }}" data-concepto-nombre="INTERESES BANCARIOS / RENDIMIENTOS" title="Clic en la cantidad para desplegar por ejercicio, o clic aquí para ver pólizas">
                                    <td class="py-2 px-3 fw-bold text-info">
                                        <i class="fas fa-percent text-info me-2"></i> Intereses Bancarios / Rendimientos Financieros (4.3.1.1)
                                        <span class="badge bg-info text-dark ms-2" style="font-size: 10px;">Separado de Fletes</span>
                                        @if ($idIntereses)
                                            <span class="badge bg-light text-info border ms-2 px-1 py-0 opacity-75 d-none d-sm-inline-block" style="font-size: 10px;">
                                                <i class="fas fa-search-dollar me-1"></i>Ver pólizas
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-end fw-bold text-info td-toggle-collapse" data-bs-toggle="collapse" data-bs-target="#collapseInteresesEjercicios" style="width: 220px; cursor: pointer;" title="Clic para desplegar desglose por ejercicio">
                                        <span>$ {{ number_format($balance['estado_resultados']['intereses_ganados'], 2) }}</span>
                                        <i class="fas fa-chevron-down text-info ms-2" style="font-size: 11px;"></i>
                                    </td>
                                </tr>
                                {{-- Sub-filas colapsables de Intereses por Ejercicio --}}
                                <tr class="collapse bg-light border-0" id="collapseInteresesEjercicios">
                                    <td colspan="2" class="p-0 border-0">
                                        <div class="p-2 ps-4 bg-light border-start border-3 border-info">
                                            <div class="text-xs text-muted mb-1 fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                                <i class="fas fa-calendar-alt text-info me-1"></i> Desglose de Intereses por Ejercicio:
                                            </div>
                                            <div class="row g-2">
                                                @forelse ($balance['estado_resultados']['desglose_ejercicios'] ?? [] as $anio => $ej)
                                                    <div class="col-sm-6">
                                                        <div class="d-flex justify-content-between p-2 bg-white rounded border">
                                                            <span class="text-xs fw-semibold text-dark">Intereses {{ $anio }}:</span>
                                                            <span class="text-xs fw-bold text-info">$ {{ number_format($ej['intereses'], 2) }}</span>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 text-xs text-muted">Sin intereses registrados en el periodo.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>

                                {{-- 3. Fila GASTOS OPERATIVOS --}}
                                <tr class="fila-concepto" data-concepto-id="{{ $idGastos }}" data-concepto-nombre="GASTOS OPERATIVOS Y GENERALES" title="Clic en la cantidad para desplegar por ejercicio, o clic aquí para ver pólizas">
                                    <td class="py-2 px-3 fw-semibold text-danger">
                                        <i class="fas fa-minus text-danger me-2"></i> Gastos Operativos y Generales (5.1.*)
                                        @if ($idGastos)
                                            <span class="badge bg-light text-danger border ms-2 px-1 py-0 opacity-75 d-none d-sm-inline-block" style="font-size: 10px;">
                                                <i class="fas fa-search-dollar me-1"></i>Ver pólizas
                                            </span>
                                        @endif
                                    </td>
                                    <td class="py-2 px-3 text-end fw-bold text-danger td-toggle-collapse" data-bs-toggle="collapse" data-bs-target="#collapseGastosEjercicios" style="width: 220px; cursor: pointer;" title="Clic para desplegar desglose por ejercicio">
                                        <span>- $ {{ number_format($balance['estado_resultados']['gastos_totales'], 2) }}</span>
                                        <i class="fas fa-chevron-down text-danger ms-2" style="font-size: 11px;"></i>
                                    </td>
                                </tr>
                                {{-- Sub-filas colapsables de Gastos por Ejercicio --}}
                                <tr class="collapse bg-light border-0" id="collapseGastosEjercicios">
                                    <td colspan="2" class="p-0 border-0">
                                        <div class="p-2 ps-4 bg-light border-start border-3 border-danger">
                                            <div class="text-xs text-muted mb-1 fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                                <i class="fas fa-calendar-alt text-danger me-1"></i> Desglose de Gastos por Ejercicio:
                                            </div>
                                            <div class="row g-2">
                                                @forelse ($balance['estado_resultados']['desglose_ejercicios'] ?? [] as $anio => $ej)
                                                    <div class="col-sm-6">
                                                        <div class="d-flex justify-content-between p-2 bg-white rounded border">
                                                            <span class="text-xs fw-semibold text-dark">Gastos {{ $anio }}:</span>
                                                            <span class="text-xs fw-bold text-danger">- $ {{ number_format($ej['gastos'], 2) }}</span>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 text-xs text-muted">Sin gastos registrados en el periodo.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot>
                                {{-- 4. Fila UTILIDAD NETA OPERATIVA --}}
                                <tr class="border-top border-2 border-dark">
                                    <th class="py-3 px-3 text-uppercase fw-bolder text-dark">
                                        <i class="fas fa-equals text-primary me-2"></i> UTILIDAD NETA DEL EJERCICIO (RESULTADO OPERATIVO)
                                    </th>
                                    <th class="py-3 px-3 text-end fw-bolder {{ $balance['estado_resultados']['utilidad_neta'] < 0 ? 'text-danger' : 'text-success' }} fs-6 td-toggle-collapse" data-bs-toggle="collapse" data-bs-target="#collapseUtilidadEjercicios" style="cursor: pointer;" title="Clic para ver desglose de utilidad por ejercicio">
                                        <span>$ {{ number_format($balance['estado_resultados']['utilidad_neta'], 2) }}</span>
                                        <i class="fas fa-chevron-down text-dark ms-2" style="font-size: 11px;"></i>
                                    </th>
                                </tr>
                                {{-- Sub-filas colapsables de Utilidad por Ejercicio --}}
                                <tr class="collapse bg-light border-0" id="collapseUtilidadEjercicios">
                                    <td colspan="2" class="p-0 border-0">
                                        <div class="p-2 ps-4 bg-light border-start border-3 border-primary">
                                            <div class="text-xs text-muted mb-1 fw-bold text-uppercase" style="letter-spacing: 0.5px;">
                                                <i class="fas fa-calendar-check text-primary me-1"></i> Utilidad Neta Operativa por Ejercicio:
                                            </div>
                                            <div class="row g-2">
                                                @forelse ($balance['estado_resultados']['desglose_ejercicios'] ?? [] as $anio => $ej)
                                                    <div class="col-sm-6">
                                                        <div class="d-flex justify-content-between p-2 bg-white rounded border">
                                                            <span class="text-xs fw-semibold text-dark">Utilidad {{ $anio }}:</span>
                                                            <span class="text-xs fw-bold {{ $ej['utilidad'] < 0 ? 'text-danger' : 'text-success' }}">
                                                                $ {{ number_format($ej['utilidad'], 2) }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                @empty
                                                    <div class="col-12 text-xs text-muted">Sin desglose disponible.</div>
                                                @endforelse
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    {{-- Bloque de Conciliación con Balance General --}}
                    @php
                        $ajusteHist = abs($balance['estado_resultados']['ajuste_patrimonio_historico'] ?? 0);
                        $estaConciliadoExacto = $ajusteHist < 0.01;
                    @endphp
                    <div class="mt-3 p-3 bg-white rounded border {{ $estaConciliadoExacto ? 'border-success' : 'border-warning' }} shadow-sm">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-xs fw-bold text-uppercase text-secondary" style="letter-spacing: 0.5px;">
                                <i class="fas {{ $estaConciliadoExacto ? 'fa-check-circle text-success' : 'fa-balance-scale text-warning' }} me-1"></i> Conciliación: Resultado Operativo vs. Patrimonio en Balance
                            </span>
                            <span class="badge {{ $estaConciliadoExacto ? 'bg-success-light text-success border border-success' : 'bg-warning-light text-dark border border-warning' }} text-xs">
                                {{ $estaConciliadoExacto ? 'Conciliación Exacta (Diferencia $0.00)' : 'Auditoría Contable' }}
                            </span>
                        </div>
                        <div class="row g-2 text-xs">
                            <div class="col-sm-4">
                                <span class="text-muted d-block">Utilidad Operativa (E.R.):</span>
                                <strong class="text-success fs-7">$ {{ number_format($balance['estado_resultados']['utilidad_neta'], 2) }}</strong>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted d-block">Ejercicios Anteriores (3.2.2):</span>
                                <strong class="{{ $estaConciliadoExacto ? 'text-dark' : 'text-danger' }} fs-7">
                                    $ {{ number_format($balance['estado_resultados']['ajuste_patrimonio_historico'] ?? 0, 2) }}
                                </strong>
                            </div>
                            <div class="col-sm-4">
                                <span class="text-muted d-block">Patrimonio Neto en Balance General:</span>
                                <strong class="text-primary fs-7">$ {{ number_format($balance['patrimonio']['total'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="mt-2 text-muted" style="font-size: 11px;">
                            @if ($estaConciliadoExacto)
                                <i class="fas fa-check-double text-success me-1"></i>
                                <strong>Sincronización Total:</strong> Al no existir cierre contable previo en este periodo integral, la Utilidad Neta Operativa del Estado de Resultados ($ {{ number_format($balance['estado_resultados']['utilidad_neta'], 2) }}) cuadra con exactitud al centavo con el Patrimonio Neto del Balance General, y la cuenta 3.2.2 se mantiene limpia en $0.00.
                            @else
                                <i class="fas fa-info-circle text-info me-1"></i>
                                <strong>Diferencia de $ {{ number_format($ajusteHist, 2) }}:</strong>
                                El Estado de Resultados refleja únicamente los ingresos facturados menos los gastos devengados. El Balance General incorpora los movimientos históricos de apertura pre-bancos.
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Tarjeta de Resumen Visual con Desglose Anual --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm text-center p-3 {{ $balance['estado_resultados']['utilidad_neta'] >= 0 ? 'bg-success text-white' : 'bg-danger text-white' }} mb-3">
                        <small class="text-uppercase opacity-75 fw-semibold" style="letter-spacing: 1px;">Resultado Operativo Total</small>
                        <h2 class="fw-bolder my-2 text-white">
                            $ {{ number_format($balance['estado_resultados']['utilidad_neta'], 2) }}
                        </h2>
                        <small class="opacity-75">
                            {{ $balance['estado_resultados']['utilidad_neta'] >= 0 ? 'Utilidad acumulada en el periodo' : 'Déficit acumulado en el periodo' }}
                        </small>
                    </div>

                    @if (!empty($balance['estado_resultados']['desglose_ejercicios']))
                        <div class="card border bg-white shadow-sm p-3">
                            <h6 class="text-xs fw-bold text-uppercase text-muted mb-2">
                                <i class="fas fa-layer-group text-primary me-1"></i> Comparativo por Ejercicio
                            </h6>
                            @foreach ($balance['estado_resultados']['desglose_ejercicios'] as $anio => $ej)
                                <div class="d-flex justify-content-between align-items-center py-2 border-bottom text-xs">
                                    <div>
                                        <span class="fw-bold text-dark d-block">Ejercicio {{ $anio }}</span>
                                        <span class="text-muted" style="font-size: 10px;">Ing: ${{ number_format($ej['ingresos'], 0) }} | Gas: ${{ number_format($ej['gastos'], 0) }}</span>
                                    </div>
                                    <span class="fw-bold {{ $ej['utilidad'] < 0 ? 'text-danger' : 'text-success' }} fs-7">
                                        $ {{ number_format($ej['utilidad'], 2) }}
                                    </span>
                                </div>
                            @endforeach
                            <div class="d-flex justify-content-between align-items-center pt-2 text-xs">
                                <span class="fw-bold text-dark">Utilidad Operativa Total:</span>
                                <span class="fw-bold text-primary fs-7">
                                    $ {{ number_format($balance['estado_resultados']['utilidad_neta'], 2) }}
                                </span>
                            </div>
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>
</div>
