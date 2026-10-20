@php
    $idIngresos = collect($balance['estado_resultados']['items'])->firstWhere('concepto', 'INGRESOS')['id'] ?? null;
    $idIntereses = collect($balance['estado_resultados']['items'])->firstWhere('concepto', 'intereses')['id'] ?? null;
    $idGastos = collect($balance['estado_resultados']['items'])->firstWhere('concepto', 'GASTOS')['id'] ?? null;
    $totalIngresos = round($balance['estado_resultados']['ingresos_fletes'] + $balance['estado_resultados']['intereses_ganados'], 2);
    $totalGastos = $balance['estado_resultados']['gastos_totales'];
    $utilidadNeta = $balance['estado_resultados']['utilidad_neta'];
    $margenUtilidad = $totalIngresos > 0 ? round(($utilidadNeta / $totalIngresos) * 100, 1) : 0;
@endphp

{{-- ENCABEZADO FORMAL ESTADO DE ACTIVIDADES / RESULTADOS --}}
<div class="text-center border-bottom pb-4 mb-4">
    <span class="badge bg-light text-primary border px-3 py-1 mb-2 fw-semibold" style="letter-spacing: 1px;">
        SISTEMA GESTIÓN TRANSPORTE CONTABLE • FORMATO ERA-02 • CLASIFICADOR POR OBJETO DEL GASTO
    </span>
    <h3 class="fw-bolder text-dark mb-1 text-uppercase" style="letter-spacing: 1.5px;">ESTADO DE ACTIVIDADES / RESULTADOS</h3>
    
    <h5 class="text-secondary fw-normal mb-1">
        @if ($equipoSeleccionado)
            UNIDAD: <strong class="text-dark">{{ $equipoSeleccionado->texto_select }}</strong>
        @else
            CONSOLIDADO GLOBAL DE UNIDADES
        @endif
    </h5>

    <p class="text-muted text-sm mb-0">
        PERIODO: DEL <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($balance['fecha_inicio_ejercicio'])->isoFormat('D [DE] MMMM [DE] YYYY') }}</span>
        AL <span class="fw-bold text-dark">{{ \Carbon\Carbon::parse($fechaCorte)->isoFormat('D [DE] MMMM [DE] YYYY') }}</span>
        @if ($esAdmin && in_array('todas', $seleccionEmpresas))
            <span class="badge bg-secondary text-white ms-2">Consolidado Multi-Empresa</span>
        @endif
    </p>

    <div class="mt-2">
        <span class="badge bg-light text-dark border px-3 py-1 text-xs">
            <i class="fas fa-coins text-warning me-1"></i> Cifras expresadas en Pesos Mexicanos (MXN)
        </span>
    </div>
</div>

{{-- TARJETAS KPI DE RESUMEN EJECUTIVO --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card border border-success bg-white shadow-sm p-3 rounded text-center">
            <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Ingresos de Gestión y Fin.</small>
            <h4 class="fw-bold text-success my-1">$ {{ number_format($totalIngresos, 2) }}</h4>
            <span class="text-xxs text-muted">Fletes e Intereses Bancarios</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border border-danger bg-white shadow-sm p-3 rounded text-center">
            <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Gastos y Pérdidas</small>
            <h4 class="fw-bold text-danger my-1">$ {{ number_format($totalGastos, 2) }}</h4>
            <span class="text-xxs text-muted">Operación y Servicios Generales</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border {{ $utilidadNeta >= 0 ? 'border-primary' : 'border-danger' }} bg-white shadow-sm p-3 rounded text-center">
            <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">{{ $utilidadNeta >= 0 ? 'Ahorro / Utilidad Neta' : 'Desahorro / Pérdida' }}</small>
            <h4 class="fw-bold {{ $utilidadNeta >= 0 ? 'text-primary' : 'text-danger' }} my-1">$ {{ number_format($utilidadNeta, 2) }}</h4>
            <span class="text-xxs text-muted">Resultado del Ejercicio al Corte</span>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card border border-info bg-white shadow-sm p-3 rounded text-center">
            <small class="text-muted text-uppercase fw-semibold" style="font-size: 11px;">Rentabilidad Neta</small>
            <h4 class="fw-bold text-info my-1">{{ $margenUtilidad }}%</h4>
            <span class="text-xxs text-muted">Margen sobre Ingresos Totales</span>
        </div>
    </div>
</div>

{{-- TABLA FORMAL CONAC ERA-02 --}}
<div class="table-responsive shadow-sm rounded border bg-white mb-4">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-dark">
            <tr>
                <th style="width: 15%;" class="text-center py-2">CÓDIGO CONAC</th>
                <th style="width: 55%;" class="py-2">CONCEPTO / CLASIFICACIÓN ECONÓMICA</th>
                <th style="width: 15%;" class="text-center py-2">TIPO</th>
                <th style="width: 15%;" class="text-end py-2">IMPORTE (MXN)</th>
            </tr>
        </thead>
        <tbody>
            {{-- SECCIÓN 4.0: INGRESOS Y OTROS BENEFICIOS --}}
            <tr class="table-primary border-top border-2 border-primary">
                <td colspan="4" class="fw-bold py-2 text-dark text-uppercase">
                    <i class="fas fa-plus-circle text-primary me-2"></i> 4.0 INGRESOS Y OTROS BENEFICIOS
                </td>
            </tr>
            <tr class="fila-concepto" style="cursor: pointer;" data-concepto-id="{{ $idIngresos }}" data-concepto-nombre="INGRESOS POR FLETES Y SERVICIOS" title="Clic para ver detalle de viajes y facturas">
                <td class="text-center text-muted"><code>4.1.1.1</code></td>
                <td class="ps-3 fw-semibold text-dark">
                    Ingresos por Servicios de Fletes y Logística
                    <span class="badge bg-light text-primary border ms-2 px-1 py-0 opacity-75" style="font-size: 10px;">
                        <i class="fas fa-search-dollar me-1"></i>Ver detalle
                    </span>
                </td>
                <td class="text-center"><span class="badge bg-light text-secondary border">Gestión</span></td>
                <td class="text-end fw-bold text-dark">$ {{ number_format($balance['estado_resultados']['ingresos_fletes'], 2) }}</td>
            </tr>
            <tr class="table-info fila-concepto" style="cursor: pointer;" data-concepto-id="{{ $idIntereses }}" data-concepto-nombre="INTERESES BANCARIOS / RENDIMIENTOS" title="Clic para ver detalle de rendimientos bancarios">
                <td class="text-center text-info"><code>4.3.1.1</code></td>
                <td class="ps-3 fw-bold text-info">
                    Intereses y Rendimientos Bancarios Ganados
                    <span class="badge bg-info text-dark ms-2" style="font-size: 10px;">Separado de Fletes</span>
                    <span class="badge bg-light text-info border ms-2 px-1 py-0 opacity-75" style="font-size: 10px;">
                        <i class="fas fa-search-dollar me-1"></i>Ver detalle
                    </span>
                </td>
                <td class="text-center"><span class="badge bg-info text-dark">Financiero</span></td>
                <td class="text-end fw-bold text-info">$ {{ number_format($balance['estado_resultados']['intereses_ganados'], 2) }}</td>
            </tr>
            <tr class="bg-light fw-bold border-bottom">
                <td colspan="3" class="text-end text-uppercase text-secondary py-2">TOTAL INGRESOS Y OTROS BENEFICIOS:</td>
                <td class="text-end text-success py-2 fs-6">$ {{ number_format($totalIngresos, 2) }}</td>
            </tr>

            {{-- SECCIÓN 5.0: GASTOS Y OTRAS PÉRDIDAS --}}
            <tr class="table-danger border-top border-2 border-danger">
                <td colspan="4" class="fw-bold py-2 text-dark text-uppercase">
                    <i class="fas fa-minus-circle text-danger me-2"></i> 5.0 GASTOS Y OTRAS PÉRDIDAS
                </td>
            </tr>
            <tr class="fila-concepto" style="cursor: pointer;" data-concepto-id="{{ $idGastos }}" data-concepto-nombre="GASTOS OPERATIVOS Y GENERALES" title="Clic para ver detalle de compras y gastos de operación">
                <td class="text-center text-muted"><code>5.1.*</code></td>
                <td class="ps-3 fw-semibold text-danger">
                    Gastos Operativos, Generales y de Funcionamiento
                    <span class="badge bg-light text-danger border ms-2 px-1 py-0 opacity-75" style="font-size: 10px;">
                        <i class="fas fa-search-dollar me-1"></i>Ver detalle
                    </span>
                </td>
                <td class="text-center"><span class="badge bg-light text-secondary border">Operativo</span></td>
                <td class="text-end fw-bold text-danger">- $ {{ number_format($totalGastos, 2) }}</td>
            </tr>
            <tr class="bg-light fw-bold border-bottom">
                <td colspan="3" class="text-end text-uppercase text-secondary py-2">TOTAL GASTOS Y OTRAS PÉRDIDAS:</td>
                <td class="text-end text-danger py-2 fs-6">- $ {{ number_format($totalGastos, 2) }}</td>
            </tr>
        </tbody>
        <tfoot>
            <tr class="{{ $utilidadNeta >= 0 ? 'table-success border-success' : 'table-danger border-danger' }} border-top border-3">
                <th colspan="3" class="py-3 px-3 text-uppercase fw-bolder fs-6">
                    <i class="fas {{ $utilidadNeta >= 0 ? 'fa-arrow-circle-up text-success' : 'fa-arrow-circle-down text-danger' }} me-2"></i>
                    {{ $utilidadNeta >= 0 ? 'AHORRO NETO DEL EJERCICIO (RESULTADO DEL PERIODO)' : 'DESAHORRO NETO DEL EJERCICIO (PÉRDIDA DEL PERIODO)' }}
                </th>
                <th class="py-3 px-3 text-end fw-bolder {{ $utilidadNeta >= 0 ? 'text-success' : 'text-danger' }} fs-5">
                    $ {{ number_format($utilidadNeta, 2) }}
                </th>
            </tr>
        </tfoot>
    </table>
</div>
