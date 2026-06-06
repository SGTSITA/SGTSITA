document.addEventListener("DOMContentLoaded", function () {
    const unidad = document.getElementById("unidad_id");
    const fechaInicio = document.getElementById("fecha_inicio");
    const fechaFin = document.getElementById("fecha_fin");
    const btnConsultar = document.getElementById("btnConsultarConsumo");
    const tbody = document.getElementById("tbodyConsumoUnidad");

    inicializarFechas();

    btnConsultar.addEventListener("click", consultarConsumo);

    async function consultarConsumo() {
        if (!validarFiltros()) return;

        pintarLoading();

        const params = new URLSearchParams({
            unidad_id: unidad.value,
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
        });

        try {
            const response = await fetch(
                `${URL_CONSUMO_UNIDADES}?${params.toString()}`,
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

                pintarVacio("No se pudo consultar el reporte.");
                return;
            }

            pintarResumen(data.resumen);
            pintarTabla(data.rows);
        } catch (error) {
            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error inesperado",
                text: "No se pudo consultar el reporte.",
            });

            pintarVacio("No se pudo consultar el reporte.");
        }
    }

    function validarFiltros() {
        if (!unidad.value || !fechaInicio.value || !fechaFin.value) {
            Swal.fire({
                icon: "warning",
                title: "Filtros requeridos",
                text: "Selecciona unidad, fecha inicio y fecha fin.",
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

    function pintarResumen(resumen) {
        document
            .getElementById("resumenConsumoUnidad")
            .classList.remove("d-none");

        setText("lblTotalViajes", resumen.total_viajes || 0);
        setText("lblViajesConDatos", resumen.viajes_con_datos || 0);
        setText("lblViajesSinDatos", resumen.viajes_sin_datos || 0);
        setText("lblTotalKm", numberFormat(resumen.total_km, 2));
        setText("lblTotalLitros", numberFormat(resumen.total_litros, 3));
        setText(
            "lblRendimientoPromedio",
            resumen.rendimiento_promedio !== null
                ? numberFormat(resumen.rendimiento_promedio, 3)
                : "S/N",
        );
    }

    function pintarTabla(rows) {
        tbody.innerHTML = "";

        if (!rows || !rows.length) {
            pintarVacio("No hay viajes para la unidad y periodo seleccionado.");
            return;
        }

        rows.forEach((row) => {
            const rendimiento = row.rendimiento_km_litro;
            const rendimientoHtml =
                rendimiento !== null
                    ? `<span class="${claseRendimiento(rendimiento)}">${numberFormat(rendimiento, 3)}</span>`
                    : `<span class="text-muted">S/N</span>`;

            //  console.log(row);

            const origen = row.origen || "S/N";
            const destino = row.destino || "S/N";

            tbody.insertAdjacentHTML(
                "beforeend",
                `
                <tr>
                    <td>${escapeHtml(row.fecha_inicio || "S/N")}</td>

                    <td>
                        <div class="fw-bold">${escapeHtml(row.contenedor || "S/N")}</div>

                    </td>

                    <td>${escapeHtml(row.operador || "S/N")}</td>
                      <td class="ruta-cell">
    <div class="ruta-box">
        <div class="ruta-item">
            <span class="ruta-label">Origen</span>
            <span class="ruta-text">${escapeHtml(origen)}</span>
        </div>

        <div class="ruta-divider"></div>

        <div class="ruta-item">
            <span class="ruta-label">Destino</span>
            <span class="ruta-text">${escapeHtml(destino)}</span>
        </div>
    </div>
</td>

                    <td class="text-end fw-bold">
                        ${numberFormat(row.km_recorridos, 2)}
                    </td>

                    <td class="text-end fw-bold">
                        ${numberFormat(row.litros_diesel, 3)}
                    </td>

                    <td class="text-end">
                        ${rendimientoHtml}

                    </td>

                    <td>
                        ${
                            row.observacion
                                ? `<span class="badge bg-warning text-dark">${escapeHtml(row.observacion)}</span>`
                                : `<span class="badge bg-success">Completo</span>`
                        }
                    </td>
                </tr>
            `,
            );
        });
    }

    function pintarLoading() {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    <div class="spinner-border spinner-border-sm me-2"></div>
                    Consultando consumo...
                </td>
            </tr>
        `;
    }

    function pintarVacio(message) {
        tbody.innerHTML = `
            <tr>
                <td colspan="7" class="text-center text-muted py-4">
                    ${escapeHtml(message)}
                </td>
            </tr>
        `;
    }

    function claseRendimiento(value) {
        const rendimiento = Number(value || 0);

        if (rendimiento >= 3) return "rendimiento-bueno";
        if (rendimiento >= 2) return "rendimiento-medio";

        return "rendimiento-bajo";
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

    function numberFormat(value, decimals = 2) {
        return Number(value || 0).toLocaleString("es-MX", {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        });
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
