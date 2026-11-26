@extends('layouts.app')

@section('template_title')
    Relación de Gastos Unificada
@endsection

@section('content')
    <div class="container-fluid py-2">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 mb-4">
                    <!-- Header Estandarizado Metronic -->
                    <div class="card-header bg-gradient-dark py-3 d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <div>
                            <h5 class="text-white mb-0">
                                <i class="fa fa-table me-2"></i> Relación de Gastos (Matriz Unificada)
                            </h5>
                            <p class="text-xs text-white opacity-8 mb-0" id="subtituloReporte">
                                Resumen consolidado por Categorías y Subcategorías
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-success mb-0" id="btnExportarExcel">
                                <i class="fa fa-file-excel me-1"></i> Descargar Excel (.xlsx)
                            </button>
                            <a href="{{ route('gastos.index') }}" class="btn btn-sm btn-outline-light mb-0">
                                <i class="fa fa-arrow-left me-1"></i> Volver a Gastos
                            </a>
                        </div>
                    </div>

                    <div class="card-body">
                        <!-- Filtros Estandarizados Metronic -->
                        <div class="card shadow-none border p-3 mb-4 bg-light">
                            <div class="row g-3 align-items-end">
                                <!-- Periodo -->
                                <div class="col-lg-3 col-md-5 col-sm-12">
                                    <label class="form-label text-xs font-weight-bold text-uppercase text-muted mb-1">
                                        <i class="fa fa-calendar-alt me-1"></i> Periodo de Gastos
                                    </label>
                                    <input type="text" id="daterangeReporte" readonly
                                        class="form-control form-control-sm bg-white cursor-pointer shadow-none">
                                </div>

                                <!-- Agrupación de Filas -->
                                <div class="col-lg-3 col-md-4 col-sm-6">
                                    <label class="form-label text-xs font-weight-bold text-uppercase text-muted mb-1">
                                        <i class="fa fa-layer-group me-1"></i> Agrupar Filas Por
                                    </label>
                                    <select id="selectAgrupacion" class="form-select form-select-sm bg-white shadow-none">
                                        <option value="subcategoria" {{ ($agrupacion ?? 'subcategoria') === 'subcategoria' ? 'selected' : '' }}>
                                            Subcategorías (Catálogo)
                                        </option>
                                        <option value="concepto" {{ ($agrupacion ?? '') === 'concepto' ? 'selected' : '' }}>
                                            Concepto / Motivo Libre
                                        </option>
                                        <option value="categoria" {{ ($agrupacion ?? '') === 'categoria' ? 'selected' : '' }}>
                                            Solo Categorías
                                        </option>
                                    </select>
                                </div>

                                <!-- Switch Omitir Vacías -->
                                <div class="col-lg-3 col-md-3 col-sm-6">
                                    <div class="form-check form-switch mb-1">
                                        <input class="form-check-input cursor-pointer" type="checkbox" id="checkOmitirVacias" {{ !empty($omitirVacias) ? 'checked' : '' }}>
                                        <label class="form-check-label text-xs font-weight-bold text-muted cursor-pointer" for="checkOmitirVacias">
                                            Omitir columnas sin gastos ($0)
                                        </label>
                                    </div>
                                    <small class="text-xxs text-muted d-block">Solo muestra categorías con movimientos</small>
                                </div>

                                <!-- Botón Actualizar -->
                                <div class="col-lg-3 col-md-12 col-sm-12 d-flex justify-content-lg-end justify-content-between align-items-center gap-2 flex-wrap">
                                    <button type="button" class="btn btn-sm btn-dark mb-0" id="btnActualizarReporte">
                                        <i class="fa fa-sync-alt me-1"></i> Actualizar
                                    </button>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary px-3 py-2" id="badgeTotalRegistros">0 gastos</span>
                                        <span class="badge bg-gradient-success px-3 py-2 fs-6 fw-bold" id="badgeGranTotal">Total: $ 0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Contenedor de la Matriz Pivote -->
                        <div class="card border shadow-none bg-white">
                            <div class="table-responsive" style="max-height: calc(100vh - 330px);">
                                <table class="table table-bordered table-sm table-hover align-middle mb-0" id="tablaMatriz" style="font-size: 13px;">
                                    <thead class="sticky-top bg-white" style="z-index: 3;">
                                        <tr id="trSumaMonto" style="background-color: #f8f9fa;">
                                            <th class="fw-bold text-dark py-2" style="min-width: 240px; border-bottom: 2px solid #dee2e6;">
                                                Suma de Monto
                                            </th>
                                            <th id="thEtiquetasColumna" class="fw-bold text-center text-dark py-2" colspan="1" style="border-bottom: 2px solid #dee2e6;">
                                                Etiquetas de columna
                                            </th>
                                        </tr>
                                        <tr id="trEncabezadosCategorias" style="background-color: #1B365D; color: #FFFFFF;">
                                            <th style="background-color: #1B365D; color: #FFFFFF; min-width: 240px;" class="fw-bold py-2">
                                                Etiquetas de fila
                                            </th>
                                            <th style="background-color: #1B365D; color: #FFFFFF; min-width: 150px;" class="text-end fw-bold py-2">
                                                Total general
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody id="tbodyMatriz">
                                        <tr>
                                            <td colspan="2" class="text-center py-5 text-muted">
                                                <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                                                Calculando relación de gastos...
                                            </td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="sticky-bottom" style="z-index: 2;" id="tfootMatriz">
                                        <!-- Totales calculados dinámicamente -->
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('js_custom')
    <script>
        const initialFrom = @json($from);
        const initialTo = @json($to);

        let filtroInicio = initialFrom;
        let filtroFin = initialTo;

        function formatMoneda(valor) {
            const num = parseFloat(valor) || 0;
            return num.toLocaleString("es-MX", { minimumFractionDigits: 2, maximumFractionDigits: 2 });
        }

        function setRangoRapido(tipo) {
            let start, end;

            if (tipo === "ultimos_7") {
                start = moment().subtract(6, "days").startOf("day");
                end = moment().endOf("day");
            } else if (tipo === "ultimos_30") {
                start = moment().subtract(29, "days").startOf("day");
                end = moment().endOf("day");
            } else if (tipo === "este_mes") {
                start = moment().startOf("month");
                end = moment().endOf("month");
            } else if (tipo === "mes_anterior") {
                start = moment().subtract(1, "month").startOf("month");
                end = moment().subtract(1, "month").endOf("month");
            } else if (tipo === "todos") {
                start = moment("2024-01-01").startOf("day");
                end = moment().endOf("day");
            } else {
                start = moment().startOf("year");
                end = moment().endOf("year");
            }

            const picker = $("#daterangeReporte").data("daterangepicker");
            if (picker) {
                picker.setStartDate(start);
                picker.setEndDate(end);
            }

            filtroInicio = start.format("YYYY-MM-DD");
            filtroFin = end.format("YYYY-MM-DD");
            $("#daterangeReporte").val(`${filtroInicio} - ${filtroFin}`);
            cargarDatosReporte();
        }

        function cargarDatosReporte() {
            const tbody = document.getElementById("tbodyMatriz");
            const omitirVacias = document.getElementById("checkOmitirVacias").checked ? 1 : 0;
            const agrupacion = document.getElementById("selectAgrupacion").value || "subcategoria";

            tbody.innerHTML = `
                <tr>
                    <td colspan="20" class="text-center py-5 text-muted">
                        <div class="spinner-border text-primary spinner-border-sm me-2" role="status"></div>
                        Calculando relación de gastos del periodo...
                    </td>
                </tr>
            `;

            const url = `/gastos/reporte-unificado/data?from=${encodeURIComponent(filtroInicio)}&to=${encodeURIComponent(filtroFin)}&omitir_vacias=${omitirVacias}&agrupacion=${encodeURIComponent(agrupacion)}`;

            fetch(url)
                .then(res => res.json())
                .then(res => {
                    if (res.TMensaje !== "success" || !res.data) {
                        tbody.innerHTML = `<tr><td colspan="20" class="text-center text-danger py-4">Error al cargar datos del reporte.</td></tr>`;
                        return;
                    }

                    const d = res.data;
                    const categorias = d.categorias || [];
                    const filas = d.filas || [];
                    const totalesCols = d.totales_columnas || {};
                    const granTotal = d.gran_total || 0;

                    // Títulos y badges
                    document.getElementById("subtituloReporte").innerText = `Relación de Gastos del ${d.from} al ${d.to}`;
                    document.getElementById("badgeTotalRegistros").innerText = `${d.total_registros || 0} gastos procesados`;
                    document.getElementById("badgeGranTotal").innerText = `Total: $ ${formatMoneda(granTotal)}`;

                    const esSoloCategorias = (d.agrupacion === "categoria");
                    const trSumaMonto = document.getElementById("trSumaMonto");
                    const trEncabezados = document.getElementById("trEncabezadosCategorias");
                    const tfoot = document.getElementById("tfootMatriz");

                    if (esSoloCategorias) {
                        // En modo Solo Categorías: ocultar fila de etiquetas de columna y dejar tabla en 2 columnas
                        if (trSumaMonto) trSumaMonto.style.display = "none";

                        trEncabezados.innerHTML = `
                            <th style="background-color: #1B365D; color: #FFFFFF; min-width: 320px;" class="fw-bold py-2">
                                Categoría
                            </th>
                            <th style="background-color: #1B365D; color: #FFFFFF; min-width: 180px;" class="text-end fw-bold py-2">
                                Total general
                            </th>
                        `;

                        if (filas.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="2" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="fa fa-folder-open text-muted fa-3x mb-3 opacity-6"></i>
                                            <h6 class="text-secondary fw-bold mb-1">No se encontraron gastos en este periodo</h6>
                                            <p class="text-muted text-xs mb-3">
                                                No hay movimientos registrados del <strong>${d.from}</strong> al <strong>${d.to}</strong>.
                                            </p>
                                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                <button type="button" class="btn btn-sm btn-outline-primary mb-0" onclick="setRangoRapido('este_mes')">
                                                    <i class="fa fa-calendar-alt me-1"></i> Este Mes
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary mb-0" onclick="setRangoRapido('ultimos_30')">
                                                    <i class="fa fa-history me-1"></i> Últimos 30 Días
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-dark mb-0" onclick="setRangoRapido('mes_anterior')">
                                                    <i class="fa fa-arrow-left me-1"></i> Mes Anterior
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary mb-0" onclick="setRangoRapido('todos')">
                                                    <i class="fa fa-globe me-1"></i> Ver Todo el Histórico
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        } else {
                            let htmlFilas = "";
                            filas.forEach(fila => {
                                htmlFilas += `
                                    <tr>
                                        <td class="fw-semibold text-dark py-2">${fila.nombre}</td>
                                        <td class="text-end font-monospace fw-bold bg-light py-2">$ ${formatMoneda(fila.total)}</td>
                                    </tr>
                                `;
                            });
                            tbody.innerHTML = htmlFilas;
                        }

                        tfoot.innerHTML = `
                            <tr style="font-weight: bold; background-color: #F8F9FA;">
                                <td class="fw-bold py-2">Total general</td>
                                <td class="text-end font-monospace fw-bold py-2" style="background-color: #E2EFDA; color: #1e4620; font-size: 14px;">$ ${formatMoneda(granTotal)}</td>
                            </tr>
                        `;
                    } else {
                        // En modo Subcategoría o Concepto: mostrar fila superior y todas las columnas de categorías
                        if (trSumaMonto) trSumaMonto.style.display = "";

                        const colSpanTotal = Math.max(categorias.length, 1) + 1;
                        document.getElementById("thEtiquetasColumna").setAttribute("colspan", colSpanTotal);

                        let htmlTh = `
                            <th style="background-color: #1B365D; color: #FFFFFF; min-width: 240px;" class="fw-bold py-2">
                                Etiquetas de fila
                            </th>
                        `;

                        categorias.forEach(cat => {
                            htmlTh += `
                                <th style="background-color: #1B365D; color: #FFFFFF; min-width: 140px;" class="text-end fw-bold py-2">
                                    ${cat.categoria}
                                </th>
                            `;
                        });

                        htmlTh += `
                            <th style="background-color: #1B365D; color: #FFFFFF; min-width: 150px;" class="text-end fw-bold py-2">
                                Total general
                            </th>
                        `;
                        trEncabezados.innerHTML = htmlTh;

                        // Filas y Empty State
                        if (filas.length === 0) {
                            tbody.innerHTML = `
                                <tr>
                                    <td colspan="${categorias.length + 2}" class="text-center py-5">
                                        <div class="py-4">
                                            <i class="fa fa-folder-open text-muted fa-3x mb-3 opacity-6"></i>
                                            <h6 class="text-secondary fw-bold mb-1">No se encontraron gastos en este periodo</h6>
                                            <p class="text-muted text-xs mb-3">
                                                No hay movimientos registrados del <strong>${d.from}</strong> al <strong>${d.to}</strong>.
                                            </p>
                                            <div class="d-flex justify-content-center gap-2 flex-wrap">
                                                <button type="button" class="btn btn-sm btn-outline-primary mb-0" onclick="setRangoRapido('este_mes')">
                                                    <i class="fa fa-calendar-alt me-1"></i> Este Mes
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary mb-0" onclick="setRangoRapido('ultimos_30')">
                                                    <i class="fa fa-history me-1"></i> Últimos 30 Días
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-dark mb-0" onclick="setRangoRapido('mes_anterior')">
                                                    <i class="fa fa-arrow-left me-1"></i> Mes Anterior
                                                </button>
                                                <button type="button" class="btn btn-sm btn-primary mb-0" onclick="setRangoRapido('todos')">
                                                    <i class="fa fa-globe me-1"></i> Ver Todo el Histórico
                                                </button>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            `;
                        } else {
                            let htmlFilas = "";
                            filas.forEach(fila => {
                                htmlFilas += `<tr><td class="fw-semibold text-dark text-nowrap py-2">${fila.nombre}</td>`;
                                categorias.forEach(cat => {
                                    const val = (fila.valores && fila.valores[cat.id]) ? fila.valores[cat.id] : 0;
                                    htmlFilas += `<td class="text-end font-monospace py-2">${val > 0 ? '$ ' + formatMoneda(val) : ''}</td>`;
                                });
                                htmlFilas += `<td class="text-end font-monospace fw-bold bg-light py-2">$ ${formatMoneda(fila.total)}</td></tr>`;
                            });
                            tbody.innerHTML = htmlFilas;
                        }

                        // Totales de columna en Tfoot
                        let htmlFoot = `<tr style="font-weight: bold; background-color: #F8F9FA;"><td class="fw-bold py-2">Total general</td>`;

                        categorias.forEach(cat => {
                            const colVal = totalesCols[cat.id] || 0;
                            htmlFoot += `<td class="text-end font-monospace fw-bold py-2">${colVal > 0 ? '$ ' + formatMoneda(colVal) : ''}</td>`;
                        });

                        htmlFoot += `<td class="text-end font-monospace fw-bold py-2" style="background-color: #E2EFDA; color: #1e4620; font-size: 14px;">$ ${formatMoneda(granTotal)}</td></tr>`;
                        tfoot.innerHTML = htmlFoot;
                    }
                })
                .catch(err => {
                    console.error("Error al cargar reporte:", err);
                    tbody.innerHTML = `<tr><td colspan="20" class="text-center text-danger py-4">Error de comunicación con el servidor.</td></tr>`;
                });
        }

        $(document).ready(function() {
            const startM = moment(initialFrom);
            const endM = moment(initialTo);

            // Inicializar con la misma función y opciones centralizadas de SGT (/gastos)
            initSgtDateRange(
                "#daterangeReporte",
                {
                    startDate: startM,
                    endDate: endM,
                    locale: {
                        separator: " - ",
                    },
                },
                function(start, end) {
                    filtroInicio = moment(start).format("YYYY-MM-DD");
                    filtroFin = moment(end).format("YYYY-MM-DD");
                    $("#daterangeReporte").val(`${filtroInicio} - ${filtroFin}`);
                    cargarDatosReporte();
                }
            );

            $("#daterangeReporte").val(
                `${startM.format("YYYY-MM-DD")} - ${endM.format("YYYY-MM-DD")}`
            );

            $("#btnActualizarReporte").on("click", function() {
                cargarDatosReporte();
            });

            $("#checkOmitirVacias").on("change", function() {
                cargarDatosReporte();
            });

            $("#selectAgrupacion").on("change", function() {
                cargarDatosReporte();
            });

            $("#btnExportarExcel").on("click", function() {
                const omitirVacias = document.getElementById("checkOmitirVacias").checked ? 1 : 0;
                const agrupacion = document.getElementById("selectAgrupacion").value || "subcategoria";
                const url = `/gastos/reporte-unificado/export?from=${encodeURIComponent(filtroInicio)}&to=${encodeURIComponent(filtroFin)}&omitir_vacias=${omitirVacias}&agrupacion=${encodeURIComponent(agrupacion)}`;
                window.location.href = url;
            });

            // Carga inicial inmediata con los parámetros recibidos
            cargarDatosReporte();
        });
    </script>
@endsection
