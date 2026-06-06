document.addEventListener("DOMContentLoaded", function () {
    const cuenta = document.getElementById("cuenta_id");
    const fechaInicio = document.getElementById("fecha_inicio");
    const fechaFin = document.getElementById("fecha_fin");
    const tipoReporte = document.getElementById("tipo_reporte");

    const btnConsultar = document.getElementById("btnConsultarReporte");
    const btnPdf = document.getElementById("btnReportePdf");
    const btnExcel = document.getElementById("btnReporteExcel");

    inicializarFechas();

    btnConsultar.addEventListener("click", consultarReporte);

    btnPdf.addEventListener("click", function () {
        abrirExportacion(SCB_REPORTE_URLS.pdf);
    });

    btnExcel.addEventListener("click", function () {
        abrirExportacion(SCB_REPORTE_URLS.excel);
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
        return new URLSearchParams({
            cuenta_id: cuenta.value,
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
            tipo_reporte: tipoReporte.value,
        }).toString();
    }

    function pintarReporte(reporte) {
        document.getElementById("resumenReporte").classList.remove("d-none");

        setText("lblSaldoInicial", money(reporte.saldo_inicial));
        setText("lblTotalCargos", money(reporte.total_cargos));
        setText("lblTotalAbonos", money(reporte.total_abonos));
        setText("lblSaldoFinal", money(reporte.saldo_final));

        setText(
            "tituloReporteTabla",
            reporte.titulo || "Resultado del reporte",
        );

        setText(
            "subtituloReporteTabla",
            `${reporte.cuenta.banco} - ${reporte.cuenta.beneficiario} - ${reporte.cuenta.numero_cuenta} | ${reporte.fecha_inicio} al ${reporte.fecha_fin}`,
        );

        if (reporte.tipo_reporte === "detallado") {
            pintarDetallado(reporte.rows || []);
            return;
        }

        pintarEstadoCuenta(reporte.rows || []);
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
            tbody.insertAdjacentHTML(
                "beforeend",
                `
                <tr>
                    <td>${escapeHtml(row.fecha)}</td>
                    <td>
                        <div class="fw-bold">${escapeHtml(row.concepto || "")}</div>
                        <small class="text-muted">${row.detalles_count || 0} detalle(s)</small>
                    </td>
                    <td>${escapeHtml(row.referencia || "S/N")}</td>
                    <td class="text-end">${cargoHtml(row.cargo)}</td>
                    <td class="text-end">${abonoHtml(row.abono)}</td>
                    <td class="text-end ${saldoClass(row.saldo)}">${money(row.saldo)}</td>
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
            tbody.insertAdjacentHTML(
                "beforeend",
                `
            <tr class="table-light">
                <td class="fw-bold">${escapeHtml(mov.fecha)}</td>

                <td>
                    <div class="fw-bold text-dark">
                        ${escapeHtml(mov.concepto || "")}
                    </div>
                    <small class="text-muted">
                        Movimiento #${mov.id} · ${mov.detalles_count || 0} detalle(s)
                    </small>
                </td>

                <td>${escapeHtml(mov.referencia_bancaria || "S/N")}</td>

                <td class="text-end">${cargoHtml(mov.cargo)}</td>

                <td class="text-end">${abonoHtml(mov.abono)}</td>

                <td class="text-end ${saldoClass(mov.saldo)}">
                    ${money(mov.saldo)}
                </td>
            </tr>
        `,
            );

            const detalles = mov.detalles || [];

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

                    <td class="text-end small text-muted">
                        ${Number(mov.cargo || 0) > 0 ? money(detalle.monto) : "-"}
                    </td>

                    <td class="text-end small text-muted">
                        ${Number(mov.abono || 0) > 0 ? money(detalle.monto) : "-"}
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
                    ${Number(mov.cargo || 0) > 0 ? money(mov.total_detalles) : "-"}
                </td>

                <td class="text-end small fw-bold">
                    ${Number(mov.abono || 0) > 0 ? money(mov.total_detalles) : "-"}
                </td>

                <td></td>
            </tr>
        `,
            );
        });
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
            ? `<span class="monto-cargo">-${money(value)}</span>`
            : `<span class="text-muted">-</span>`;
    }

    function abonoHtml(value) {
        return Number(value || 0) > 0
            ? `<span class="monto-abono">+${money(value)}</span>`
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
});
