@extends('layouts.app')

@section('template_title')
    Balance General y Estado de Resultados
@endsection

@section('content')
<div class="container-fluid py-3">

    {{-- BARRA DE FILTROS Y CONTROL --}}
    {{-- PANEL SUPERIOR DE CONTROL Y REPORTES CONTABLES --}}
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('contabilidad.balance-general.index') }}" id="formFiltroBalance">
                {{-- FILA 1: FILTROS PRINCIPALES Y TIPO DE REPORTE --}}
                <div class="row g-3 align-items-end mb-3">

                    {{-- Selector de Tipo de Reporte Contable --}}
                    <div class="col-xl-3 col-lg-4 col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">
                            <i class="fas fa-file-invoice text-primary me-1"></i> Tipo de Reporte Contable:
                        </label>
                        <select name="tipo_reporte" id="selectTipoReporte" class="form-select form-select-sm fw-semibold" onchange="this.form.submit()">
                            @foreach ($catalogoReportes as $key => $rep)
                                <option value="{{ $key }}" {{ $tipoReporte === $key ? 'selected' : '' }}>
                                    [{{ $rep['codigo_conac'] }}] {{ $rep['nombre'] }} {{ !$rep['disponible'] ? '(Próximamente)' : '' }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted text-xxs d-block mt-1">
                            <i class="fas fa-info-circle me-1"></i>{{ $catalogoReportes[$tipoReporte]['descripcion'] ?? '' }}
                        </small>
                    </div>

                    {{-- Filtro de Empresas (solo si es_admin == 1) --}}
                    @if ($esAdmin)
                        <div class="col-xl-3 col-lg-3 col-md-6">
                            <label class="form-label fw-bold text-dark text-xs mb-1">
                                <i class="fas fa-building text-primary me-1"></i> Empresa(s):
                            </label>
                            <select name="empresas[]" id="selectEmpresas" class="form-select form-select-sm" multiple style="min-height: 38px;">
                                <option value="todas" {{ in_array('todas', $seleccionEmpresas) ? 'selected' : '' }}>-- Todas las Empresas (Consolidado) --</option>
                                @foreach ($todasEmpresas as $emp)
                                    <option value="{{ $emp->id }}" {{ in_array($emp->id, $seleccionEmpresas) && !in_array('todas', $seleccionEmpresas) ? 'selected' : '' }}>
                                        {{ $emp->nombre }}
                                    </option>
                                @endforeach
                            </select>
                            <small class="text-muted text-xxs">Mantén Ctrl presionado para multi-selección.</small>
                        </div>
                    @endif

                    {{-- Filtro de Unidad / Camión (id_equipo - marca) --}}
                    <div class="col-xl-{{ $esAdmin ? '2' : '3' }} col-lg-{{ $esAdmin ? '2' : '3' }} col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">
                            <i class="fas fa-truck text-primary me-1"></i> Unidad / Camión:
                        </label>
                        <select name="id_equipo" class="form-select form-select-sm">
                            <option value="">-- Todos los Camiones (Consolidado) --</option>
                            @foreach ($equiposDisponibles as $eq)
                                <option value="{{ $eq->id }}" {{ (string)$idEquipo === (string)$eq->id ? 'selected' : '' }}>
                                    {{ $eq->texto_select }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted text-xxs">Filtra la operatividad por unidad vehicular.</small>
                    </div>

                    {{-- Selector de Rango de Fechas / Ejercicio (SGT DateRangePicker) --}}
                    <div class="col-xl-4 col-lg-4 col-md-6 col-12">
                        <label class="form-label fw-bold text-dark text-xs mb-1">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Periodo / Ejercicio:
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white text-primary border-end-0"><i class="fas fa-calendar-week"></i></span>
                            <input type="text" id="balanceDaterange" class="form-control form-control-sm border-start-0 ps-1 fw-semibold text-dark" style="background-color: #fff; cursor: pointer;" readonly />
                            <input type="hidden" name="fecha_inicio" id="fecha_inicio" value="{{ $fechaInicio }}">
                            <input type="hidden" name="fecha_corte" id="fecha_corte" value="{{ $fechaCorte }}">
                        </div>
                        <small class="text-muted text-xxs">Rangos rápidos: Ejercicio 2025, Ejercicio 2026, Este mes o personalizado.</small>
                    </div>

                </div>

                {{-- FILA 2: BARRA DE ACCIONES Y BOTONES CON WRAP Y VISIBILIDAD GARANTIZADA --}}
                <div class="d-flex flex-wrap align-items-center justify-content-between pt-2 border-top gap-2">
                    
                    {{-- Botón de Aplicar Filtro --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary btn-sm px-3 shadow-sm mb-0">
                            <i class="fas fa-sync-alt me-1"></i> Actualizar Reporte
                        </button>
                    </div>

                    {{-- Accesos Directos Contables --}}
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('contabilidad.poliza-apertura.index') }}" class="btn btn-outline-info btn-sm shadow-sm mb-0" title="Póliza de Apertura / Saldos Iniciales">
                            <i class="fas fa-file-invoice-dollar me-1"></i> Póliza de Apertura
                        </a>
                        <button type="button" class="btn btn-dark btn-sm shadow-sm mb-0" data-bs-toggle="modal" data-bs-target="#modalCierreContable" title="Gestionar y aplicar Cierre Contable de Ejercicios">
                            <i class="fas fa-lock me-1 text-warning"></i> Cierre Contable
                        </button>

                        {{-- Exportación Oficial CONAC (PDF y Excel) --}}
                        @php
                            $queryParams = array_merge(request()->all(), ['tipo_reporte' => $tipoReporte]);
                        @endphp
                        <a href="{{ route('contabilidad.balance-general.exportar', array_merge($queryParams, ['formato' => 'pdf'])) }}" target="_blank" class="btn btn-danger btn-sm shadow-sm mb-0" title="Ver y descargar formato oficial CONAC en PDF">
                            <i class="fas fa-file-pdf me-1"></i> Exportar PDF
                        </a>
                        <a href="{{ route('contabilidad.balance-general.exportar', array_merge($queryParams, ['formato' => 'excel'])) }}" class="btn btn-success btn-sm shadow-sm mb-0" title="Descargar formato oficial CONAC en Excel (XLSX)">
                            <i class="fas fa-file-excel me-1"></i> Exportar Excel
                        </a>
                    </div>

                </div>
            </form>
        </div>
    </div>

    {{-- HOJA DE BALANCE GENERAL (FORMATO MODELO TP 002) --}}
    <div class="card shadow border-0" id="hojaBalance">
        <div class="card-body p-4 p-md-5">

            @if ($tipoReporte === 'balanza_comprobacion')
                {{-- VISTA PREVIA: BALANZA DE COMPROBACIÓN [BC-03] --}}
                @include('contabilidad.partials.vista-balanza-comprobacion')
            @elseif ($tipoReporte === 'auxiliar_contable')
                {{-- VISTA PREVIA: AUXILIAR CONTABLE / LIBRO MAYOR [AC-07] --}}
                @include('contabilidad.partials.vista-auxiliar-contable')
            @elseif ($tipoReporte === 'libro_diario')
                {{-- VISTA PREVIA: LIBRO DIARIO DE PÓLIZAS [LD-06] --}}
                @include('contabilidad.partials.vista-libro-diario')
            @elseif ($tipoReporte === 'estado_resultados')
                {{-- VISTA PREVIA: ESTADO DE ACTIVIDADES / RESULTADOS [ERA-02] --}}
                @include('contabilidad.partials.vista-estado-resultados')
            @else
                {{-- VISTA PREVIA: BALANCE GENERAL [ESF-01] --}}
                @include('contabilidad.partials.vista-balance-general')
            @endif

            {{-- FIRMAS INSTITUCIONALES CONAC Y LEYENDA LEGAL --}}
            <div class="row text-center mt-5 pt-4 text-xs text-muted">
                <div class="col-4">
                    <div class="border-top pt-2 mx-4">
                        ELABORÓ<br>
                        <strong class="text-dark">DEPARTAMENTO CONTABLE</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top pt-2 mx-4">
                        REVISÓ<br>
                        <strong class="text-dark">DIRECCIÓN FINANCIERA</strong>
                    </div>
                </div>
                <div class="col-4">
                    <div class="border-top pt-2 mx-4">
                        AUTORIZÓ<br>
                        <strong class="text-dark">DIRECCIÓN GENERAL</strong>
                    </div>
                </div>
            </div>

            <div class="text-center mt-3 pt-2 text-xxs text-muted fst-italic border-top">
                "Bajo protesta de decir verdad declaramos que los estados financieros y sus notas, son razonablemente correctos y son responsabilidad del emisor."
            </div>

        </div>
    </div>

</div>

{{-- MODAL DE AUXILIAR Y DESGLOSE DE MOVIMIENTOS CONTABLES --}}
<div class="modal fade" id="modalDetalleConcepto" tabindex="-1" aria-labelledby="modalDetalleConceptoLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white py-3">
                <div>
                    <h5 class="modal-title fw-bold text-white mb-0" id="modalDetalleConceptoLabel">
                        <i class="fas fa-book-open text-primary me-2"></i>Auxiliar Contable: <span id="detalleTituloConcepto" class="text-primary">---</span>
                    </h5>
                    <small class="opacity-75 text-white" id="detalleSubtitulo">Fecha de Corte: {{ $fechaCorte }}</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body p-4">
                {{-- KPI Cards --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-4">
                        <div class="card border border-success bg-success-light p-3 rounded text-center shadow-sm">
                            <span class="text-xs text-uppercase text-success fw-bold"><i class="fas fa-arrow-down me-1"></i>Total Cargos (Debe)</span>
                            <h4 class="fw-bolder text-success mb-0 mt-1" id="kpiTotalDebe">$ 0.00</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border border-danger bg-danger-light p-3 rounded text-center shadow-sm">
                            <span class="text-xs text-uppercase text-danger fw-bold"><i class="fas fa-arrow-up me-1"></i>Total Abonos (Haber)</span>
                            <h4 class="fw-bolder text-danger mb-0 mt-1" id="kpiTotalHaber">$ 0.00</h4>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card border border-primary bg-primary-light p-3 rounded text-center shadow-sm">
                            <span class="text-xs text-uppercase text-primary fw-bold"><i class="fas fa-balance-scale me-1"></i>Saldo Neto Resultante</span>
                            <h4 class="fw-bolder text-primary mb-0 mt-1" id="kpiSaldoNeto">$ 0.00</h4>
                        </div>
                    </div>
                </div>

                {{-- Barra de búsqueda y conteo --}}
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                    <div class="input-group input-group-sm" style="max-width: 420px;">
                        <span class="input-group-text bg-light"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" id="inputBuscarMovimiento" class="form-control" placeholder="Filtrar por contenedor, póliza, cuenta, concepto...">
                    </div>
                    <div>
                        <span class="badge bg-secondary text-white px-3 py-2 text-xs" id="badgeTotalMovimientos">0 movimientos</span>
                    </div>
                </div>

                {{-- Spinner de carga --}}
                <div id="spinnerDetalle" class="text-center py-5">
                    <div class="spinner-border text-primary" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <p class="text-muted mt-2 text-sm fw-semibold">Consultando auxiliares contables y movimientos de pólizas...</p>
                </div>

                {{-- Tabla de movimientos con scroll --}}
                <div class="table-responsive" id="contenedorTablaDetalle" style="max-height: 480px; display: none;">
                    <table class="table table-sm table-hover align-middle border mb-0 text-xs" id="tablaDetalleMovimientos">
                        <thead class="table-dark sticky-top">
                            <tr>
                                <th style="width: 85px;">Fecha</th>
                                <th style="width: 130px;">Póliza</th>
                                <th style="width: 160px;">Cuenta Contable</th>
                                <th>Concepto</th>
                                <th style="width: 170px;">Referencia / Contenedor</th>
                                <th class="text-end" style="width: 120px;">Cargos (Debe)</th>
                                <th class="text-end" style="width: 120px;">Abonos (Haber)</th>
                                <th class="text-end" style="width: 130px;">Saldo Acumulado</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDetalleMovimientos">
                        </tbody>
                    </table>
                </div>

                <div id="alertaSinMovimientos" class="alert alert-info text-center py-4 text-xs mb-0 shadow-sm" style="display: none;">
                    <i class="fas fa-info-circle me-1"></i> No se encontraron movimientos contables registrados para este concepto al corte especificado.
                </div>
            </div>
            <div class="modal-footer py-2 bg-light d-flex justify-content-between">
                <small class="text-muted text-xxs">
                    <i class="fas fa-shield-alt text-success me-1"></i>Información generada por el motor de pólizas contables CONAC
                </small>
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL DE CIERRE CONTABLE DE EJERCICIOS --}}
<div class="modal fade" id="modalCierreContable" tabindex="-1" aria-labelledby="modalCierreContableLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-dark text-white py-3">
                <div class="d-flex align-items-center">
                    <div class="bg-warning text-dark p-2 rounded me-2">
                        <i class="fas fa-lock fa-lg"></i>
                    </div>
                    <div>
                        <h5 class="modal-title text-white fw-bold mb-0" id="modalCierreContableLabel">Cierre Contable de Ejercicios</h5>
                        <small class="text-white-50 text-xs">Cancelación de cuentas de resultados y traslado formal a subcuentas de 3.2.2</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <div class="modal-body p-4">
                {{-- Pestañas de Navegación del Modal --}}
                <ul class="nav nav-tabs nav-tabs-custom mb-3" id="tabCierreContable" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-xs" id="tab-nuevo-cierre" data-bs-toggle="tab" data-bs-target="#panel-nuevo-cierre" type="button" role="tab">
                            <i class="fas fa-plus-circle me-1 text-primary"></i> Ejecutar Nuevo Cierre
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-xs" id="tab-historial-cierres" data-bs-toggle="tab" data-bs-target="#panel-historial-cierres" type="button" role="tab" onclick="cargarHistorialCierres()">
                            <i class="fas fa-history me-1 text-secondary"></i> Historial de Cierres Aplicados
                        </button>
                    </li>
                </ul>

                <div class="tab-content" id="tabContentCierreContable">
                    {{-- PANEL 1: NUEVO CIERRE --}}
                    <div class="tab-pane fade show active" id="panel-nuevo-cierre" role="tabpanel">
                        <form id="formEjecutarCierre">
                            <div class="row g-3 align-items-end mb-3 p-3 bg-light rounded border">
                                @if($esAdmin)
                                <div class="col-md-3">
                                    <label class="form-label text-xs fw-bold text-dark mb-1">Empresa:</label>
                                    <select class="form-select form-select-sm fw-semibold" id="cierre_id_empresa" name="id_empresa" required>
                                        @foreach($todasEmpresas as $emp)
                                            <option value="{{ $emp->id }}" {{ (in_array($emp->id, $seleccionEmpresas) && !in_array('todas', $seleccionEmpresas)) ? 'selected' : ($emp->id == 8 ? 'selected' : '') }}>
                                                {{ $emp->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                @else
                                <input type="hidden" id="cierre_id_empresa" name="id_empresa" value="{{ auth()->user()?->id_empresa }}">
                                @endif

                                <div class="col-md-3">
                                    <label class="form-label text-xs fw-bold text-dark mb-1">Fecha Inicio del Periodo:</label>
                                    <input type="date" class="form-control form-control-sm fw-semibold" id="cierre_fecha_inicio" name="fecha_inicio" value="2025-08-01" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label text-xs fw-bold text-dark mb-1">Fecha Fin (Corte del Cierre):</label>
                                    <input type="date" class="form-control form-control-sm fw-semibold" id="cierre_fecha_fin" name="fecha_fin" value="2025-12-31" required>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label text-xs fw-bold text-dark mb-1">Nombre / Periodo Ejercicio:</label>
                                    <input type="text" class="form-control form-control-sm fw-semibold" id="cierre_nombre_ejercicio" name="nombre_ejercicio" placeholder="Ej. Resultados Ejercicios Anteriores 2025">
                                </div>

                                <div class="col-12 d-flex justify-content-end gap-2 mt-2">
                                    <button type="button" class="btn btn-primary btn-sm px-3 shadow-sm mb-0" id="btnPrevisualizarCierre" onclick="previsualizarCierre()">
                                        <i class="fas fa-search me-1"></i> Previsualizar Cierre
                                    </button>
                                </div>
                            </div>
                        </form>

                        {{-- SPINNER DE CARGA --}}
                        <div id="cierreSpinner" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-primary" role="status"></div>
                            <p class="text-xs text-muted mt-2">Calculando saldos y partidas de cierre contable...</p>
                        </div>

                        {{-- CONTENEDOR DE RESULTADO DE PREVISUALIZACIÓN --}}
                        <div id="cierreResultadoContenedor" style="display: none;">
                            {{-- TARJETAS KPI RESUMEN --}}
                            <div class="row g-2 mb-3">
                                <div class="col-md-3">
                                    <div class="card border border-success bg-success bg-opacity-10 shadow-none p-2 text-center">
                                        <small class="text-success text-xxs fw-bold text-uppercase">Ingresos a Cancelar (Debe)</small>
                                        <h5 class="fw-bold text-success mb-0" id="kpiCierreIngresos">$ 0.00</h5>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border border-danger bg-danger bg-opacity-10 shadow-none p-2 text-center">
                                        <small class="text-danger text-xxs fw-bold text-uppercase">Gastos a Cancelar (Haber)</small>
                                        <h5 class="fw-bold text-danger mb-0" id="kpiCierreGastos">$ 0.00</h5>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border border-primary bg-primary bg-opacity-10 shadow-none p-2 text-center">
                                        <small class="text-primary text-xxs fw-bold text-uppercase" id="kpiCierreTipoLabel">Resultado Neto</small>
                                        <h5 class="fw-bold text-primary mb-0" id="kpiCierreNeto">$ 0.00</h5>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="card border border-dark bg-dark bg-opacity-10 shadow-none p-2 text-center">
                                        <small class="text-dark text-xxs fw-bold text-uppercase">Subcuenta Destino</small>
                                        <h6 class="fw-bold text-dark mb-0 text-truncate" id="kpiCierreSubcuenta" title="">3.2.2.01</h6>
                                    </div>
                                </div>
                            </div>

                            {{-- ALERTA SI YA EXISTE CIERRE --}}
                            <div class="alert alert-warning text-xs mb-3" id="cierreAlertaExistente" style="display: none;">
                                <i class="fas fa-exclamation-triangle me-1"></i> <span id="cierreAlertaTexto"></span>
                            </div>

                            {{-- TABLA DE PARTIDAS DE LA PÓLIZA DE CIERRE --}}
                            <div class="table-responsive border rounded mb-3" style="max-height: 280px; overflow-y: auto;">
                                <table class="table table-sm table-hover align-middle mb-0 text-xs">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th>Cuenta</th>
                                            <th>Nombre de la Cuenta</th>
                                            <th>Acción de Cierre</th>
                                            <th class="text-end">Debe (Cargo)</th>
                                            <th class="text-end">Haber (Abono)</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyPartidasCierre"></tbody>
                                </table>
                            </div>

                            {{-- BOTÓN DE CONFIRMACIÓN --}}
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="text-muted text-xxs">
                                    <i class="fas fa-info-circle me-1"></i>Al confirmar, se creará la póliza de diario con origen <code>cierre_ejercicio</code> y fecha de corte seleccionada.
                                </span>
                                <button type="button" class="btn btn-success btn-sm px-4 shadow-sm mb-0 fw-bold" id="btnConfirmarCierre" onclick="ejecutarCierre()">
                                    <i class="fas fa-check-circle me-1"></i> Confirmar y Aplicar Cierre Contable
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- PANEL 2: HISTORIAL DE CIERRES --}}
                    <div class="tab-pane fade" id="panel-historial-cierres" role="tabpanel">
                        <div id="historialSpinner" class="text-center py-4" style="display: none;">
                            <div class="spinner-border text-secondary" role="status"></div>
                            <p class="text-xs text-muted mt-2">Cargando historial de cierres...</p>
                        </div>
                        <div id="historialVacio" class="text-center py-4 text-muted text-xs" style="display: none;">
                            <i class="fas fa-folder-open fa-2x mb-2 text-muted"></i>
                            <p class="mb-0">No se han registrado pólizas de cierre contable para esta empresa.</p>
                        </div>
                        <div class="table-responsive border rounded" id="historialTablaContenedor" style="display: none;">
                            <table class="table table-sm table-hover align-middle mb-0 text-xs">
                                <thead class="table-light">
                                    <tr>
                                        <th>Fecha Corte</th>
                                        <th>Folio Póliza</th>
                                        <th>Concepto</th>
                                        <th>Subcuenta Destino</th>
                                        <th class="text-end">Resultado Trasladado</th>
                                        <th class="text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody id="tbodyHistorialCierres"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer py-2 bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalEl = document.getElementById('modalDetalleConcepto');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);

    document.querySelectorAll('.fila-concepto').forEach(row => {
        row.addEventListener('click', function () {
            const conceptoId = this.dataset.conceptoId;
            const conceptoNombre = this.dataset.conceptoNombre || 'Detalle';
            if (!conceptoId) return;

            document.getElementById('detalleTituloConcepto').textContent = conceptoNombre;
            document.getElementById('spinnerDetalle').style.display = 'block';
            document.getElementById('contenedorTablaDetalle').style.display = 'none';
            document.getElementById('alertaSinMovimientos').style.display = 'none';
            document.getElementById('kpiTotalDebe').textContent = '$ 0.00';
            document.getElementById('kpiTotalHaber').textContent = '$ 0.00';
            document.getElementById('kpiSaldoNeto').textContent = '$ 0.00';
            document.getElementById('badgeTotalMovimientos').textContent = 'Consultando...';
            document.getElementById('tbodyDetalleMovimientos').innerHTML = '';
            document.getElementById('inputBuscarMovimiento').value = '';

            modal.show();

            const form = document.getElementById('formFiltroBalance');
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);
            params.set('concepto_id', conceptoId);

            fetch(`{{ route('contabilidad.balance-general.detalle-concepto') }}?${params.toString()}`)
                .then(res => res.json())
                .then(res => {
                    document.getElementById('spinnerDetalle').style.display = 'none';
                    if (!res.success || !res.data) {
                        alert(res.message || 'Error al cargar el detalle contable.');
                        return;
                    }

                    const data = res.data;
                    document.getElementById('kpiTotalDebe').textContent = '$ ' + Number(data.total_debe).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    document.getElementById('kpiTotalHaber').textContent = '$ ' + Number(data.total_haber).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    document.getElementById('kpiSaldoNeto').textContent = '$ ' + Number(data.saldo_neto).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                    document.getElementById('badgeTotalMovimientos').textContent = data.conteo + ' movimientos registrados';

                    if (!data.movimientos || data.movimientos.length === 0) {
                        document.getElementById('alertaSinMovimientos').style.display = 'block';
                        return;
                    }

                    const tbody = document.getElementById('tbodyDetalleMovimientos');
                    let html = '';
                    data.movimientos.forEach(m => {
                        const tipoBadge = m.poliza_tipo === 'ingreso' ? 'bg-success' : (m.poliza_tipo === 'egreso' ? 'bg-danger' : 'bg-primary');
                        const badgeContenedor = m.contenedor 
                            ? `<span class="badge bg-primary text-white d-inline-block text-truncate mb-1" style="font-size: 10px; max-width: 160px;" title="Contenedor: ${m.contenedor}"><i class="fas fa-box me-1"></i>${m.contenedor}</span><br>` 
                            : '';
                        const refHtml = m.referencia ? `<small class="text-muted text-break"><i class="fas fa-tag me-1"></i>${m.referencia}</small>` : '<small class="text-muted">-</small>';

                        html += `
                            <tr>
                                <td class="text-nowrap text-secondary">${m.fecha_formato}</td>
                                <td>
                                    <span class="badge ${tipoBadge} me-1" style="font-size: 9px;">${(m.poliza_tipo || '').toUpperCase()}</span>
                                    <strong class="text-dark">${m.poliza_numero || ('#' + m.poliza_id)}</strong>
                                </td>
                                <td>
                                    <code class="text-primary fw-bold">${m.cuenta_codigo}</code><br>
                                    <span class="text-dark text-xxs">${m.cuenta_nombre}</span>
                                </td>
                                <td>
                                    <span class="text-dark fw-semibold">${m.concepto}</span>
                                </td>
                                <td>
                                    ${badgeContenedor}
                                    ${refHtml}
                                </td>
                                <td class="text-end fw-semibold ${m.debe > 0 ? 'text-success' : 'text-muted'}">${m.debe > 0 ? '$ ' + Number(m.debe).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}</td>
                                <td class="text-end fw-semibold ${m.haber > 0 ? 'text-danger' : 'text-muted'}">${m.haber > 0 ? '$ ' + Number(m.haber).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '-'}</td>
                                <td class="text-end fw-bold text-dark">$ ${Number(m.saldo_acumulado).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                            </tr>
                        `;
                    });

                    tbody.innerHTML = html;
                    document.getElementById('contenedorTablaDetalle').style.display = 'block';
                })
                .catch(err => {
                    document.getElementById('spinnerDetalle').style.display = 'none';
                    alert('Error de conexión al obtener el detalle: ' + err.message);
                });
        });
    });

    // Filtro en tiempo real dentro del modal
    const inputBuscar = document.getElementById('inputBuscarMovimiento');
    if (inputBuscar) {
        inputBuscar.addEventListener('keyup', function () {
            const query = this.value.toLowerCase().trim();
            document.querySelectorAll('#tbodyDetalleMovimientos tr').forEach(tr => {
                const text = tr.innerText.toLowerCase();
                tr.style.display = text.includes(query) ? '' : 'none';
            });
        });
    }

    // Inicialización de SGT DateRangePicker para Balance General
    if (typeof initSgtDateRange === 'function') {
        const fechaIniVal = $('#fecha_inicio').val() || '2025-08-01';
        const fechaCorteVal = $('#fecha_corte').val() || moment().format('YYYY-MM-DD');

        const customRanges = {
            'Ejercicio 2025': [moment('2025-01-01'), moment('2025-12-31')],
            'Ejercicio 2026': [moment('2026-01-01'), moment('2026-12-31')],
            'Este mes': [moment().startOf('month'), moment().endOf('month')],
            'Mes anterior': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')],
            'Todo el periodo histórico': [moment('2025-08-01'), moment()]
        };

        initSgtDateRange('#balanceDaterange', {
            startDate: moment(fechaIniVal),
            endDate: moment(fechaCorteVal),
            ranges: customRanges
        }, function (start, end, label) {
            $('#fecha_inicio').val(start.format('YYYY-MM-DD'));
            $('#fecha_corte').val(end.format('YYYY-MM-DD'));
            $('#balanceDaterange').val(start.format('YYYY-MM-DD') + ' AL ' + end.format('YYYY-MM-DD'));
        });

        $('#balanceDaterange').val(fechaIniVal + ' AL ' + fechaCorteVal);
    }
});

// ==========================================
// FUNCIONES DE MOTOR DE CIERRE CONTABLE
// ==========================================
window.previsualizarCierre = function () {
    const idEmpresa = document.getElementById('cierre_id_empresa').value;
    const fechaInicio = document.getElementById('cierre_fecha_inicio').value;
    const fechaFin = document.getElementById('cierre_fecha_fin').value;
    const nombre = document.getElementById('cierre_nombre_ejercicio').value;

    if (!idEmpresa || !fechaInicio || !fechaFin) {
        alert('Por favor complete la empresa y el rango de fechas.');
        return;
    }

    document.getElementById('cierreSpinner').style.display = 'block';
    document.getElementById('cierreResultadoContenedor').style.display = 'none';
    document.getElementById('cierreAlertaExistente').style.display = 'none';

    const params = new URLSearchParams({
        id_empresa: idEmpresa,
        fecha_inicio: fechaInicio,
        fecha_fin: fechaFin,
        nombre_ejercicio: nombre
    });

    fetch(`{{ route('contabilidad.cierre.previsualizar') }}?${params.toString()}`)
        .then(res => res.json())
        .then(res => {
            document.getElementById('cierreSpinner').style.display = 'none';
            if (!res.success) {
                alert(res.message || 'Error al previsualizar el cierre.');
                return;
            }

            const data = res.data;
            document.getElementById('kpiCierreIngresos').textContent = '$ ' + Number(data.total_ingresos).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('kpiCierreGastos').textContent = '$ ' + Number(data.total_gastos).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            
            const neto = Number(data.resultado_neto);
            document.getElementById('kpiCierreNeto').textContent = '$ ' + Math.abs(neto).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            document.getElementById('kpiCierreTipoLabel').textContent = data.tipo_resultado === 'utilidad' ? 'Utilidad Neta (Haber)' : 'Pérdida Neta (Debe)';
            
            document.getElementById('kpiCierreSubcuenta').textContent = data.codigo_subcuenta + ' - ' + data.nombre_subcuenta;
            document.getElementById('kpiCierreSubcuenta').title = data.nombre_subcuenta;

            if (data.cierre_existente) {
                document.getElementById('cierreAlertaTexto').textContent = `Atención: Ya existe la póliza de cierre ${data.cierre_existente.numero} con fecha ${data.cierre_existente.fecha}. Debe revertirla si desea regenerar este cierre.`;
                document.getElementById('cierreAlertaExistente').style.display = 'block';
                document.getElementById('btnConfirmarCierre').disabled = true;
            } else {
                document.getElementById('btnConfirmarCierre').disabled = false;
            }

            // Llenar tabla de partidas
            let html = '';
            data.partidas_ingresos.forEach(item => {
                html += `
                    <tr>
                        <td><code>${item.codigo}</code></td>
                        <td>${item.nombre}</td>
                        <td><span class="badge bg-success text-xxs">Ingresos (Cancelación)</span></td>
                        <td class="text-end fw-semibold text-success">$ ${Number(item.debe).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="text-end text-muted">-</td>
                    </tr>
                `;
            });
            data.partidas_gastos.forEach(item => {
                html += `
                    <tr>
                        <td><code>${item.codigo}</code></td>
                        <td>${item.nombre}</td>
                        <td><span class="badge bg-danger text-xxs">Gastos (Cancelación)</span></td>
                        <td class="text-end text-muted">-</td>
                        <td class="text-end fw-semibold text-danger">$ ${Number(item.haber).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    </tr>
                `;
            });

            // Fila de resultado a subcuenta 3.2.2.xx
            if (data.tipo_resultado === 'utilidad') {
                html += `
                    <tr class="table-primary fw-bold">
                        <td><code>${data.codigo_subcuenta}</code></td>
                        <td>${data.nombre_subcuenta}</td>
                        <td><span class="badge bg-primary text-xxs">Traslado Utilidad Neta</span></td>
                        <td class="text-end text-muted">-</td>
                        <td class="text-end text-primary">$ ${Math.abs(neto).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                    </tr>
                `;
            } else {
                html += `
                    <tr class="table-warning fw-bold">
                        <td><code>${data.codigo_subcuenta}</code></td>
                        <td>${data.nombre_subcuenta}</td>
                        <td><span class="badge bg-warning text-dark text-xxs">Traslado Pérdida Neta</span></td>
                        <td class="text-end text-danger">$ ${Math.abs(neto).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="text-end text-muted">-</td>
                    </tr>
                `;
            }

            document.getElementById('tbodyPartidasCierre').innerHTML = html;
            document.getElementById('cierreResultadoContenedor').style.display = 'block';
        })
        .catch(err => {
            document.getElementById('cierreSpinner').style.display = 'none';
            alert('Error de conexión al previsualizar cierre: ' + err.message);
        });
};

window.ejecutarCierre = function () {
    const idEmpresa = document.getElementById('cierre_id_empresa').value;
    const fechaInicio = document.getElementById('cierre_fecha_inicio').value;
    const fechaFin = document.getElementById('cierre_fecha_fin').value;
    const nombre = document.getElementById('cierre_nombre_ejercicio').value;

    const ejecutar = () => {
        const btn = document.getElementById('btnConfirmarCierre');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Aplicando...';

        fetch(`{{ route('contabilidad.cierre.store') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                id_empresa: idEmpresa,
                fecha_inicio: fechaInicio,
                fecha_fin: fechaFin,
                nombre_ejercicio: nombre
            })
        })
        .then(res => res.json())
        .then(res => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Confirmar y Aplicar Cierre Contable';

            if (!res.success) {
                alert(res.message || 'Error al ejecutar el cierre contable.');
                return;
            }

            alert('¡Cierre Contable aplicado exitosamente! Folio: ' + (res.data.poliza ? res.data.poliza.numero : ''));
            window.location.reload();
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Confirmar y Aplicar Cierre Contable';
            alert('Error de comunicación: ' + err.message);
        });
    };

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Confirmar Cierre Contable?',
            text: `Se cancelarán los ingresos y gastos del periodo ${fechaInicio} al ${fechaFin} y se trasladará el resultado neto a la subcuenta 3.2.2.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#28a745',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, aplicar cierre',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                ejecutar();
            }
        });
    } else {
        if (confirm(`¿Confirmar Cierre Contable del periodo ${fechaInicio} al ${fechaFin}?`)) {
            ejecutar();
        }
    }
};

window.cargarHistorialCierres = function () {
    const idEmpresa = document.getElementById('cierre_id_empresa').value;
    document.getElementById('historialSpinner').style.display = 'block';
    document.getElementById('historialTablaContenedor').style.display = 'none';
    document.getElementById('historialVacio').style.display = 'none';

    fetch(`{{ route('contabilidad.cierre.historial') }}?id_empresa=${idEmpresa}`)
        .then(res => res.json())
        .then(res => {
            document.getElementById('historialSpinner').style.display = 'none';
            if (!res.success || !res.data || res.data.length === 0) {
                document.getElementById('historialVacio').style.display = 'block';
                return;
            }

            let html = '';
            res.data.forEach(c => {
                const tipoBadge = c.tipo === 'Utilidad' ? 'bg-success' : 'bg-danger';
                html += `
                    <tr>
                        <td class="fw-semibold text-dark">${c.fecha}</td>
                        <td><span class="badge bg-dark">${c.numero}</span></td>
                        <td><span class="text-truncate d-inline-block" style="max-width: 250px;">${c.concepto}</span></td>
                        <td><code>${c.subcuenta}</code></td>
                        <td class="text-end fw-bold ${c.tipo === 'Utilidad' ? 'text-success' : 'text-danger'}">$ ${Number(Math.abs(c.resultado_neto)).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}</td>
                        <td class="text-center">
                            <button class="btn btn-outline-danger btn-xs mb-0 py-1 px-2" title="Revertir este cierre" onclick="revertirCierre(${c.id})">
                                <i class="fas fa-undo me-1"></i> Revertir
                            </button>
                        </td>
                    </tr>
                `;
            });

            document.getElementById('tbodyHistorialCierres').innerHTML = html;
            document.getElementById('historialTablaContenedor').style.display = 'block';
        })
        .catch(err => {
            document.getElementById('historialSpinner').style.display = 'none';
            alert('Error al consultar historial: ' + err.message);
        });
};

window.revertirCierre = function (id) {
    if (!confirm('¿Está seguro de revertir este cierre contable? Se eliminará la póliza de cierre y las cuentas de resultados volverán a reflejar su saldo original.')) {
        return;
    }

    fetch(`{{ url('contabilidad/cierre-ejercicio') }}/${id}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        }
    })
    .then(res => res.json())
    .then(res => {
        if (!res.success) {
            alert(res.message || 'Error al revertir el cierre.');
            return;
        }
        alert('Cierre revertido correctamente.');
        window.location.reload();
    })
    .catch(err => alert('Error al revertir: ' + err.message));
};
});
</script>
@endsection
