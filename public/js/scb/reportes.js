document.addEventListener("DOMContentLoaded", function () {
    const cuenta = document.getElementById("cuenta_id");
    const unidad = document.getElementById("unidad_id");
    const fechaInicio = document.getElementById("fecha_inicio");
    const fechaFin = document.getElementById("fecha_fin");
    const tipoReporte = document.getElementById("tipo_reporte");

    const btnConsultar = document.getElementById("btnConsultarReporte");
    const btnPdf = document.getElementById("btnReportePdf");
    const btnExcel = document.getElementById("btnReporteExcel");
    const ordenReporteTabla = document.getElementById("ordenReporteTabla");
    const buscarTablaReporte = document.getElementById("buscarTablaReporte");
    const btnLimpiarBusquedaReporte = document.getElementById(
        "btnLimpiarBusquedaReporte",
    );

    let reporteActual = null;
    let rowsReporteActuales = [];
    let terminoBusquedaReporteActual = "";
    let debounceBusquedaReporte = null;

    inicializarFechas();

    btnConsultar.addEventListener("click", consultarReporte);

    btnPdf.addEventListener("click", function () {
        abrirExportacion(SCB_REPORTE_URLS.pdf);
    });

    btnExcel.addEventListener("click", function () {
        abrirExportacion(SCB_REPORTE_URLS.excel);
    });

    if (buscarTablaReporte) {
        buscarTablaReporte.addEventListener("input", function () {
            clearTimeout(debounceBusquedaReporte);

            debounceBusquedaReporte = setTimeout(() => {
                terminoBusquedaReporteActual = buscarTablaReporte.value.trim();

                refrescarTablaReporte();

                if (btnLimpiarBusquedaReporte) {
                    btnLimpiarBusquedaReporte.classList.toggle(
                        "d-none",
                        !terminoBusquedaReporteActual,
                    );
                }
            }, 250);
        });
    }

    if (btnLimpiarBusquedaReporte) {
        btnLimpiarBusquedaReporte.addEventListener("click", function () {
            terminoBusquedaReporteActual = "";

            if (buscarTablaReporte) {
                buscarTablaReporte.value = "";
                buscarTablaReporte.focus();
            }

            btnLimpiarBusquedaReporte.classList.add("d-none");

            refrescarTablaReporte();
        });
    }

    if (ordenReporteTabla) {
        ordenReporteTabla.addEventListener("change", function () {
            refrescarTablaReporte();
        });
    }

    document.addEventListener("click", function (e) {
        const rowReporte = e.target.closest(".reporte-master-row");

        if (
            rowReporte &&
            !e.target.closest("button, a, input, select, textarea")
        ) {
            toggleDetalleReporte(rowReporte);
        }
    });

    async function consultarReporte() {
        if (!validarFiltros()) return;

        pintarLoading();

        try {
            const response = await fetch(
                `${SCB_REPORTE_URLS.consultar}?${obtenerQueryString()}`,
                {
                    headers: {
                        Accept: "application/json",
                    },
                },
            );

            const data = await response.json();

            if (!response.ok || !data.success) {
                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.message || "No se pudo consultar el reporte.",
                });

                return;
            }

            pintarReporte(data.data);
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo consultar el reporte.",
            });
        }
    }

    function abrirExportacion(url) {
        if (!validarFiltros()) return;

        window.open(`${url}?${obtenerQueryString()}`, "_blank");
    }

    function toggleDetalleReporte(row) {
        const detalleId = row.dataset.detalleId;

        if (!detalleId) return;

        const detalleRow = document.getElementById(detalleId);

        if (!detalleRow) return;

        detalleRow.classList.toggle("d-none");
        row.classList.toggle("detalle-abierto");

        const icon = row.querySelector(".reporte-chevron");

        if (icon) {
            icon.classList.toggle("fa-chevron-right");
            icon.classList.toggle("fa-chevron-down");
        }
    }

    function validarFiltros() {
        if (
            !cuenta.value ||
            !fechaInicio.value ||
            !fechaFin.value ||
            !tipoReporte.value
        ) {
            Swal.fire({
                icon: "warning",
                title: "Filtros requeridos",
                text: "Selecciona cuenta, fecha inicio, fecha fin y tipo de reporte.",
            });

            return false;
        }

        if (fechaFin.value < fechaInicio.value) {
            Swal.fire({
                icon: "warning",
                title: "Rango inválido",
                text: "La fecha fin no puede ser menor que la fecha inicio.",
            });

            return false;
        }

        return true;
    }

    function obtenerQueryString() {
        const params = new URLSearchParams({
            cuenta_id: cuenta.value,
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
            tipo_reporte: tipoReporte.value,
        });

        if (unidad && unidad.value) {
            params.append("unidad_id", unidad.value);
        }

        return params.toString();
    }

    function pintarReporte(reporte) {
        document.getElementById("resumenReporte").classList.remove("d-none");

        setText("lblSaldoInicial", money(reporte.saldo_inicial));
        setText("lblTotalCargos", `+${money(reporte.total_cargos)}`);
        setText("lblTotalAbonos", `-${money(reporte.total_abonos)}`);
        setText("lblSaldoFinal", money(reporte.saldo_final));

        setText(
            "tituloReporteTabla",
            reporte.titulo || "Resultado del reporte",
        );

        const textoUnidad = reporte.unidad?.nombre
            ? ` | Unidad: ${reporte.unidad.nombre}`
            : " | Todas las unidades";

        setText(
            "subtituloReporteTabla",
            `${reporte.cuenta.banco} - ${reporte.cuenta.beneficiario} - ${reporte.cuenta.numero_cuenta} | ${reporte.fecha_inicio} al ${reporte.fecha_fin}${textoUnidad}`,
        );

        reporteActual = reporte;
        rowsReporteActuales = reporte.rows || [];
        terminoBusquedaReporteActual = "";

        if (buscarTablaReporte) {
            buscarTablaReporte.value = "";
        }

        if (btnLimpiarBusquedaReporte) {
            btnLimpiarBusquedaReporte.classList.add("d-none");
        }

        refrescarTablaReporte();
    }
    function refrescarTablaReporte() {
        if (!reporteActual) return;

        const rowsFiltrados = filtrarRowsReporte(rowsReporteActuales);
        const rowsOrdenados = ordenarRowsReporte(rowsFiltrados);

        const termino = normalizarTexto(terminoBusquedaReporteActual);

        setText(
            "lblConteoReporte",
            termino
                ? `${rowsOrdenados.length} resultado(s) encontrados.`
                : `${rowsOrdenados.length} movimiento(s) cargados.`,
        );

        if (reporteActual.tipo_reporte === "detallado") {
            pintarDetallado(rowsOrdenados);
            return;
        }

        pintarEstadoCuenta(rowsOrdenados);
    }

    function filtrarRowsReporte(rows) {
        const termino = normalizarTexto(terminoBusquedaReporteActual);

        if (!termino) {
            return rows;
        }

        return rows.filter((row) => {
            const textoMovimiento = [
                row.fecha,
                row.concepto,
                row.referencia,
                row.referencia_bancaria,
                row.cargo,
                row.abono,
                row.saldo,
                row.id,
            ].join(" ");

            const textoDetalles = (row.detalles || [])
                .map((detalle) => {
                    return [
                        obtenerTextoUnidadDetalle(detalle),
                        detalle.descripcion,
                        detalle.referencia,
                        detalle.monto,
                    ].join(" ");
                })
                .join(" ");

            const textoCompleto = normalizarTexto(
                `${textoMovimiento} ${textoDetalles}`,
            );

            return textoCompleto.includes(termino);
        });
    }

    function ordenarRowsReporte(rows) {
        const orden = ordenReporteTabla?.value || "fecha_asc";
        const [campo, direccion] = orden.split("_");

        const lista = [...rows];

        const getValor = (row) => {
            switch (campo) {
                case "fecha":
                    return Date.parse(row.fecha || "") || 0;

                case "cargo":
                    return Math.abs(Number(row.cargo || 0));

                case "abono":
                    return Math.abs(Number(row.abono || 0));

                case "saldo":
                    return Number(row.saldo || 0);

                case "concepto":
                    return String(row.concepto || "").toLowerCase();

                case "referencia":
                    return String(
                        row.referencia || row.referencia_bancaria || "",
                    ).toLowerCase();

                default:
                    return Date.parse(row.fecha || "") || 0;
            }
        };

        lista.sort((a, b) => {
            const valorA = getValor(a);
            const valorB = getValor(b);

            let resultado = 0;

            if (typeof valorA === "string" || typeof valorB === "string") {
                resultado = String(valorA).localeCompare(String(valorB), "es", {
                    sensitivity: "base",
                });
            } else {
                resultado = valorA - valorB;
            }

            if (resultado === 0) {
                resultado = Number(a.id || 0) - Number(b.id || 0);
            }

            return direccion === "desc" ? resultado * -1 : resultado;
        });

        return lista;
    }

    function obtenerTextoUnidadDetalle(detalle) {
        if (!detalle) return "";

        if (typeof detalle.unidad === "string") {
            return detalle.unidad;
        }

        return [
            detalle.unidad?.descripcion,
            detalle.unidad?.placas,
            detalle.unidad?.nombre,
        ].join(" ");
    }
    function pintarEstadoCuenta(rows) {
        document.getElementById("theadReporte").innerHTML = `
            <tr>
                <th>Fecha</th>
                <th>Concepto</th>
                <th>Referencia</th>
                <th class="text-end">Cargo</th>
                <th class="text-end">Abono</th>
                <th class="text-end">Saldo</th>
            </tr>
        `;

        const tbody = document.getElementById("tbodyReporte");
        tbody.innerHTML = "";

        if (!rows.length) {
            tbody.innerHTML = emptyRow(6);
            return;
        }

        rows.forEach((row) => {
            const cargo = Math.abs(Number(row.cargo || 0));
            const abono = Math.abs(Number(row.abono || 0));
            const saldo = Number(row.saldo || 0);
            const detalles = Array.isArray(row.detalles) ? row.detalles : [];
            const detalleId = `detalle-reporte-estado-${row.id}`;

            tbody.insertAdjacentHTML(
                "beforeend",
                `
                <tr class="reporte-master-row" data-detalle-id="${detalleId}">
                    <td class="fw-bold">
                        <i class="fas fa-chevron-right reporte-chevron me-2"></i>
                        ${escapeHtml(row.fecha || "S/N")}
                    </td>

                    <td>
                        <div class="fw-bold">${escapeHtml(row.concepto || "")}</div>
                        ${
                            detalles.length > 0
                                ? `<small class="text-muted">${detalles.length} detalle(s). Click para ver unidades.</small>`
                                : `<small class="text-muted">Sin detalles.</small>`
                        }
                    </td>

                    <td>${escapeHtml(row.referencia || "S/N")}</td>

                    <td class="text-end">${cargoHtml(cargo)}</td>

                    <td class="text-end">${abonoHtml(abono)}</td>

                    <td class="text-end ${saldoClass(saldo)}">
                        ${money(saldo)}
                    </td>
                </tr>

                <tr id="${detalleId}" class="reporte-detalle-row d-none">
                    <td colspan="6" class="p-0 border-0">
                        ${renderDetalleEstadoCuentaCollapse(detalles, cargo > 0)}
                    </td>
                </tr>
                `,
            );
        });
    }

    function pintarDetallado(rows) {
        document.getElementById("theadReporte").innerHTML = `
            <tr>
                <th>Fecha</th>
                <th>Movimiento / Detalle</th>
                <th>Referencia</th>
                <th class="text-end">Cargo</th>
                <th class="text-end">Abono</th>
                <th class="text-end">Saldo</th>
            </tr>
        `;

        const tbody = document.getElementById("tbodyReporte");
        tbody.innerHTML = "";

        if (!rows.length) {
            tbody.innerHTML = emptyRow(6);
            return;
        }

        rows.forEach((mov) => {
            const cargo = Math.abs(Number(mov.cargo || 0));
            const abono = Math.abs(Number(mov.abono || 0));
            const saldo = Number(mov.saldo || 0);
            const detalles = Array.isArray(mov.detalles) ? mov.detalles : [];

            tbody.insertAdjacentHTML(
                "beforeend",
                `
                <tr class="table-light">
                    <td class="fw-bold">
                        ${escapeHtml(mov.fecha || "S/N")}
                    </td>

                    <td>
                        <div class="fw-bold text-dark">
                            ${escapeHtml(mov.concepto || "")}
                        </div>
                        <small class="text-muted">
                            Movimiento #${mov.id} · ${detalles.length} detalle(s)
                        </small>
                    </td>

                    <td>${escapeHtml(mov.referencia_bancaria || "S/N")}</td>

                    <td class="text-end">${cargoHtml(cargo)}</td>

                    <td class="text-end">${abonoHtml(abono)}</td>

                    <td class="text-end ${saldoClass(saldo)}">
                        ${money(saldo)}
                    </td>
                </tr>
                `,
            );

            if (!detalles.length) {
                tbody.insertAdjacentHTML(
                    "beforeend",
                    `
                    <tr>
                        <td></td>
                        <td colspan="5" class="text-muted ps-4">
                            Sin detalles registrados.
                        </td>
                    </tr>
                    `,
                );

                return;
            }

            detalles.forEach((detalle) => {
                const monto = Math.abs(Number(detalle.monto || 0));

                tbody.insertAdjacentHTML(
                    "beforeend",
                    `
                    <tr>
                        <td></td>

                        <td class="ps-4">
                            <div class="small">
                                <i class="fas fa-level-up-alt fa-rotate-90 text-muted me-1"></i>
                                <strong>Unidad:</strong> ${escapeHtml(detalle.unidad || "S/N")}
                            </div>
                            <div class="small text-muted">
                                ${escapeHtml(detalle.descripcion || "")}
                            </div>
                        </td>

                        <td class="small">
                            ${escapeHtml(detalle.referencia || "S/N")}
                        </td>

                        <td class="text-end small">
                            ${
                                cargo > 0
                                    ? `<span class="monto-cargo">+${money(monto)}</span>`
                                    : `<span class="text-muted">-</span>`
                            }
                        </td>

                        <td class="text-end small">
                            ${
                                abono > 0
                                    ? `<span class="monto-abono">-${money(monto)}</span>`
                                    : `<span class="text-muted">-</span>`
                            }
                        </td>

                        <td class="text-end small text-muted">
                            -
                        </td>
                    </tr>
                    `,
                );
            });

            tbody.insertAdjacentHTML(
                "beforeend",
                `
                <tr>
                    <td></td>
                    <td colspan="2" class="text-end small fw-bold text-muted">
                        Total detalles
                    </td>

                    <td class="text-end small fw-bold">
                        ${
                            cargo > 0
                                ? `<span class="monto-cargo">+${money(mov.total_detalles || 0)}</span>`
                                : `<span class="text-muted">-</span>`
                        }
                    </td>

                    <td class="text-end small fw-bold">
                        ${
                            abono > 0
                                ? `<span class="monto-abono">-${money(mov.total_detalles || 0)}</span>`
                                : `<span class="text-muted">-</span>`
                        }
                    </td>

                    <td></td>
                </tr>
                `,
            );
        });
    }

    function renderDetalleEstadoCuentaCollapse(detalles, esCargo) {
        if (!detalles.length) {
            return `
                <div class="reporte-collapse-box">
                    <div class="text-center text-muted py-2 small">
                        Este movimiento no tiene detalles registrados.
                    </div>
                </div>
            `;
        }

        const filas = detalles
            .map((detalle) => {
                const monto = Math.abs(Number(detalle.monto || 0));

                return `
                    <tr>
                        <td>
                            <div class="fw-bold small">
                                ${escapeHtml(detalle.unidad || "S/N")}
                            </div>
                        </td>

                        <td class="small">
                            ${escapeHtml(detalle.descripcion || "")}
                        </td>

                        <td class="small">
                            ${escapeHtml(detalle.referencia || "S/N")}
                        </td>

                        <td class="text-end small fw-bold">
                            ${
                                esCargo
                                    ? `<span class="monto-cargo">+${money(monto)}</span>`
                                    : `<span class="monto-abono">-${money(monto)}</span>`
                            }
                        </td>
                    </tr>
                `;
            })
            .join("");

        const totalDetalles = detalles.reduce((acc, detalle) => {
            return acc + Math.abs(Number(detalle.monto || 0));
        }, 0);

        return `
            <div class="reporte-collapse-box">
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-2">
                    <div>
                        <div class="reporte-collapse-title">
                            Detalle por unidad
                        </div>
                        <small class="text-muted">
                            Desglose capturado del movimiento.
                        </small>
                    </div>

                    <div class="text-end">
                        <small class="text-muted d-block">Total detalle</small>
                        <span class="reporte-detalle-total">
                            ${
                                esCargo
                                    ? `+${money(totalDetalles)}`
                                    : `-${money(totalDetalles)}`
                            }
                        </span>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-sm mb-0 reporte-detalle-table">
                        <thead>
                            <tr>
                                <th>Unidad</th>
                                <th>Descripción</th>
                                <th>Referencia</th>
                                <th class="text-end">Importe</th>
                            </tr>
                        </thead>

                        <tbody>
                            ${filas}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    }

    function pintarLoading() {
        document.getElementById("theadReporte").innerHTML = `
            <tr>
                <th>Resultado</th>
            </tr>
        `;

        document.getElementById("tbodyReporte").innerHTML = `
            <tr>
                <td class="text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm me-2"></div>
                    Consultando reporte...
                </td>
            </tr>
        `;
    }

    function emptyRow(colspan) {
        return `
            <tr>
                <td colspan="${colspan}" class="text-center text-muted py-4">
                    No hay información para los filtros seleccionados.
                </td>
            </tr>
        `;
    }

    function inicializarFechas() {
        const hoy = new Date();
        const primerDia = new Date(hoy.getFullYear(), hoy.getMonth(), 1);
        const ultimoDia = new Date(hoy.getFullYear(), hoy.getMonth() + 1, 0);

        fechaInicio.value = toInputDate(primerDia);
        fechaFin.value = toInputDate(ultimoDia);
    }

    function toInputDate(date) {
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, "0");
        const day = String(date.getDate()).padStart(2, "0");

        return `${year}-${month}-${day}`;
    }

    function money(value) {
        return Number(value || 0).toLocaleString("es-MX", {
            style: "currency",
            currency: "MXN",
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        });
    }

    function cargoHtml(value) {
        return Number(value || 0) > 0
            ? `<span class="monto-cargo">+${money(value)}</span>`
            : `<span class="text-muted">-</span>`;
    }

    function abonoHtml(value) {
        return Number(value || 0) > 0
            ? `<span class="monto-abono">-${money(value)}</span>`
            : `<span class="text-muted">-</span>`;
    }

    function saldoClass(value) {
        return Number(value || 0) >= 0
            ? "monto-saldo-positivo"
            : "monto-saldo-negativo";
    }

    function setText(id, value) {
        const element = document.getElementById(id);

        if (element) {
            element.textContent = value;
        }
    }

    function escapeHtml(value) {
        return String(value ?? "")
            .replaceAll("&", "&amp;")
            .replaceAll("<", "&lt;")
            .replaceAll(">", "&gt;")
            .replaceAll('"', "&quot;")
            .replaceAll("'", "&#039;");
    }
    function normalizarTexto(value) {
        return String(value ?? "")
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .trim();
    }
});
