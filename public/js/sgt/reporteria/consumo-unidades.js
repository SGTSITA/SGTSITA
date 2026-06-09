document.addEventListener("DOMContentLoaded", function () {
    const unidad = document.getElementById("unidad_id");
    const fechaInicio = document.getElementById("fecha_inicio");
    const fechaFin = document.getElementById("fecha_fin");
    const btnConsultar = document.getElementById("btnConsultarConsumo");
    const gridDiv = document.getElementById("gridConsumoUnidad");

    let gridConsumoApi = null;

    const columnDefsConsumo = [
        {
            headerName: "Fecha",
            field: "fecha_inicio",
            filter: "agTextColumnFilter",
            floatingFilter: true,
            width: 105,
            minWidth: 105,
            valueFormatter: (params) => params.value || "S/N",
        },
        {
            headerName: "Contenedor",
            field: "contenedor",
            filter: "agTextColumnFilter",
            floatingFilter: true,
            width: 190,
            minWidth: 190,
            valueGetter: (params) => {
                const row = params.data || {};
                return `${row.contenedor || ""} ${row.peso_contenedor || ""}`;
            },
            cellRenderer: (params) => {
                const row = params.data || {};

                return `
                    <div class="consumo-contenedor">
                        <div class="fw-bold">${escapeHtml(row.contenedor || "S/N")}</div>
                        <div class="small text-muted">
                            <strong>Peso:</strong> ${escapeHtml(row.peso_contenedor || "S/N")}
                        </div>
                    </div>
                `;
            },
        },
        {
            headerName: "Operador",
            field: "operador",
            filter: "agTextColumnFilter",
            floatingFilter: true,
            width: 200,
            valueFormatter: (params) => params.value || "S/N",
        },
        {
            headerName: "Ruta",
            field: "ruta",
            filter: "agTextColumnFilter",
            floatingFilter: true,
            width: 350,
            minWidth: 350,
            flex: 1,
            valueGetter: (params) => {
                const row = params.data || {};
                return `${row.origen || ""} ${row.destino || ""}`;
            },
            cellRenderer: (params) => {
                const row = params.data || {};
                const origen = row.origen || "S/N";
                const destino = row.destino || "S/N";

                return `
                    <div class="ruta-box">
                        <div class="ruta-item">
                            <span class="ruta-label">Origen:</span>
                            <span class="ruta-text">${escapeHtml(origen)}</span>
                        </div>

                        <div class="ruta-divider"></div>

                        <div class="ruta-item">
                            <span class="ruta-label">Destino:</span>
                            <span class="ruta-text">${escapeHtml(destino)}</span>
                        </div>
                    </div>
                `;
            },
        },
        {
            headerName: "KM",
            field: "km_recorridos",
            filter: "agNumberColumnFilter",
            floatingFilter: true,
            width: 110,
            minWidth: 110,
            type: "numericColumn",
            cellClass: "text-end fw-bold",
            valueGetter: (params) => Number(params.data?.km_recorridos || 0),
            valueFormatter: (params) => numberFormat(params.value, 2),
        },
        {
            headerName: "Litros capturados",
            field: "litros_capturados_viaje",
            filter: "agNumberColumnFilter",
            floatingFilter: true,
            width: 160,
            minWidth: 160,
            type: "numericColumn",
            cellClass: "text-end",
            valueGetter: (params) =>
                Number(params.data?.litros_capturados_viaje || 0),
            cellRenderer: (params) => {
                const litrosCapturados = Number(params.value || 0);

                return `
                    <div>
                        <div class="fw-bold">${numberFormat(litrosCapturados, 3)}</div>
                        <div class="small text-muted">Guardado en este viaje</div>
                    </div>
                `;
            },
        },
        {
            headerName: "Litros cálculo",
            field: "litros_calculo_consumo",
            filter: "agNumberColumnFilter",
            floatingFilter: true,
            width: 160,
            minWidth: 160,
            type: "numericColumn",
            cellClass: "text-end",
            valueGetter: (params) =>
                Number(params.data?.litros_calculo_consumo || 0),
            cellRenderer: (params) => {
                const row = params.data || {};
                const litrosCalculo = Number(row.litros_calculo_consumo || 0);

                if (litrosCalculo > 0) {
                    return `
                        <div>
                            <div class="fw-bold">${numberFormat(litrosCalculo, 3)}</div>
                            <div class="small text-muted">
                                <strong>Tomado de:</strong> ${escapeHtml(row.litros_tomados_de_contenedor || "S/N")}
                            </div>
                        </div>
                    `;
                }

                return `
                    <div>
                        <div class="text-muted fw-bold">0.000</div>
                        <div class="small text-muted">
                            Pendiente de siguiente carga
                        </div>
                    </div>
                `;
            },
        },
        {
            headerName: "Rendimiento",
            field: "rendimiento_km_litro",
            filter: "agNumberColumnFilter",
            floatingFilter: true,
            width: 135,
            minWidth: 135,
            type: "numericColumn",
            cellClass: "text-end",
            valueGetter: (params) => {
                const value = params.data?.rendimiento_km_litro;

                return value !== null && value !== undefined
                    ? Number(value)
                    : null;
            },
            cellRenderer: (params) => {
                const rendimiento = params.value;

                if (rendimiento !== null && rendimiento !== undefined) {
                    return `
                        <span class="${claseRendimiento(rendimiento)}">
                            ${numberFormat(rendimiento, 3)}
                            KM/L
                        </span>
                    `;
                }

                return `<span class="text-muted">S/N</span>`;
            },
        },
        {
            headerName: "Estado",
            field: "observacion",
            filter: "agTextColumnFilter",
            floatingFilter: true,
            minWidth: 160,
            valueGetter: (params) => params.data?.observacion || "Completo",
            cellRenderer: (params) => {
                const row = params.data || {};

                if (row.observacion) {
                    return `
            <span class="badge bg-warning text-dark badge-observacion-grid">
                <strong>Observación:</strong><br>
                ${escapeHtml(row.observacion)}
            </span>
        `;
                }

                return `<span class="badge bg-success">Completo</span>`;
            },
        },
    ];

    const gridOptionsConsumo = {
        columnDefs: columnDefsConsumo,
        rowData: [],
        animateRows: true,
        pagination: true,
        paginationPageSize: 20,
        rowHeight: 82,
        headerHeight: 42,
        floatingFiltersHeight: 38,

        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true,
            floatingFilter: true,
            suppressHeaderMenuButton: false,
        },

        overlayLoadingTemplate: `
            <div class="text-center text-muted py-4">
                <div class="spinner-border spinner-border-sm me-2"></div>
                Consultando consumo...
            </div>
        `,

        overlayNoRowsTemplate: `
            <div class="text-center text-muted py-4">
                No hay viajes para la unidad y periodo seleccionado.
            </div>
        `,
    };

    if (gridDiv) {
        if (typeof agGrid.createGrid === "function") {
            gridConsumoApi = agGrid.createGrid(gridDiv, gridOptionsConsumo);
        } else {
            new agGrid.Grid(gridDiv, gridOptionsConsumo);
            gridConsumoApi = gridOptionsConsumo.api;
        }
    }

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

                pintarVacio();
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

            pintarVacio();
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
        resumen = resumen || {};

        document
            .getElementById("resumenConsumoUnidad")
            .classList.remove("d-none");

        setText("lblTotalViajes", resumen.total_viajes || 0);
        setText("lblViajesConDatos", resumen.viajes_con_datos || 0);
        setText("lblViajesSinDatos", resumen.viajes_sin_datos || 0);
        setText("lblTotalKm", numberFormat(resumen.total_km, 2));

        setText(
            "lblTotalLitros",
            numberFormat(resumen.total_litros_calculo, 3),
        );

        setText(
            "lblTotalLitrosCapturados",
            numberFormat(resumen.total_litros_capturados, 3),
        );

        setText(
            "lblRendimientoPromedio",
            resumen.rendimiento_promedio !== null &&
                resumen.rendimiento_promedio !== undefined
                ? numberFormat(resumen.rendimiento_promedio, 3)
                : "S/N",
        );
    }

    function pintarTabla(rows) {
        const data = rows || [];

        setGridRows(data);

        if (!data.length) {
            pintarVacio();
            return;
        }

        if (gridConsumoApi?.hideOverlay) {
            gridConsumoApi.hideOverlay();
        }
    }

    function pintarLoading() {
        setGridRows([]);

        if (gridConsumoApi?.showLoadingOverlay) {
            gridConsumoApi.showLoadingOverlay();
        }
    }

    function pintarVacio() {
        setGridRows([]);

        if (gridConsumoApi?.showNoRowsOverlay) {
            gridConsumoApi.showNoRowsOverlay();
        }
    }

    function setGridRows(rows) {
        if (!gridConsumoApi) return;

        if (typeof gridConsumoApi.setGridOption === "function") {
            gridConsumoApi.setGridOption("rowData", rows);
            return;
        }

        if (typeof gridConsumoApi.setRowData === "function") {
            gridConsumoApi.setRowData(rows);
        }
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
