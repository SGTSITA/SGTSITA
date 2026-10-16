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
                    <div class="col-xl-{{ $esAdmin ? '3' : '4' }} col-lg-{{ $esAdmin ? '3' : '4' }} col-md-6">
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

                    {{-- Fecha de Corte --}}
                    <div class="col-xl-{{ $esAdmin ? '3' : '4' }} col-lg-{{ $esAdmin ? '2' : '4' }} col-md-6">
                        <label class="form-label fw-bold text-dark text-xs mb-1">
                            <i class="fas fa-calendar-alt text-primary me-1"></i> Fecha de Corte:
                        </label>
                        <input type="date" name="fecha_corte" class="form-control form-control-sm" value="{{ $fechaCorte }}" required>
                        <small class="text-muted text-xxs">Calcula saldos al día especificado.</small>
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
                        <a href="{{ route('contabilidad.configuracion.index') }}" class="btn btn-outline-secondary btn-sm shadow-sm mb-0" title="Configurar cuentas CONAC y matriz">
                            <i class="fas fa-sliders-h me-1"></i> Matriz y Cuentas CONAC
                        </a>
                        <a href="{{ route('contabilidad.manual') }}" class="btn btn-outline-warning btn-sm shadow-sm mb-0" title="Manual Contable y Guía CONAC">
                            <i class="fas fa-book me-1"></i> Guía Contabilizadora
                        </a>

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
});
</script>
@endsection
