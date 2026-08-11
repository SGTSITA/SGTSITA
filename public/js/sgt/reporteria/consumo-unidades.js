document.addEventListener("DOMContentLoaded", function () {
    const unidad = document.getElementById("unidad_id");
    const tipoConsumo = document.getElementById("tipo_consumo");
    const fechaInicio = document.getElementById("fecha_inicio");
    const fechaFin = document.getElementById("fecha_fin");
    const btnConsultar = document.getElementById("btnConsultarConsumo");
    const btnExportarPdf = document.getElementById("btnExportarPdfConsumo");
    const btnExportarExcel = document.getElementById("btnExportarExcelConsumo");
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
            headerName: "Mapa",
            field: "mapa",
            width: 80,
            minWidth: 80,
            cellClass: "text-center",
            cellRenderer: (params) => {
                const row = params.data || {};
                const hasRuta = (row.coordenadas_ruta && row.coordenadas_ruta.length > 0) || (row.diesel_lat && row.diesel_lng);
                if (!hasRuta) {
                    return `<button type="button" class="btn btn-sm btn-icon-only btn-outline-secondary m-0 p-1" title="Sin datos de ruta" disabled style="opacity: 0.5;"><i class="fas fa-map-marked-alt"></i></button>`;
                }
                return `<button type="button" class="btn btn-sm btn-icon-only btn-outline-info m-0 p-1 btn-ver-mapa" title="Ver Ruta en Mapa"><i class="fas fa-map-marked-alt"></i></button>`;
            }
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
            headerName: "Origen KM",
            field: "origen_km",
            filter: "agTextColumnFilter",
            floatingFilter: true,
            width: 140,
            minWidth: 140,
            cellRenderer: (params) => {
                const val = params.value || "Sin KM";
                let badgeClass = "bg-secondary";
                if (val === "Diferencia Odómetros") {
                    badgeClass = "bg-success";
                } else if (val === "Estimación Coordenadas") {
                    badgeClass = "bg-info text-dark";
                } else if (val === "Captura Manual") {
                    badgeClass = "bg-primary";
                } else if (val === "Coordenadas Diésel") {
                    badgeClass = "bg-warning text-dark";
                }
                return `<span class="badge ${badgeClass} text-xxs p-1">${val}</span>`;
            }
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
        onCellClicked: (params) => {
            if (params.event.target.closest('.btn-ver-mapa')) {
                const rowData = params.data;
                abrirMapaRuta(rowData);
            }
        },
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

    if (btnConsultar) {
        btnConsultar.addEventListener("click", consultarConsumo);
    }

    if (btnExportarPdf) {
        btnExportarPdf.addEventListener("click", function () {
            exportarConsumo("pdf");
        });
    }

    if (btnExportarExcel) {
        btnExportarExcel.addEventListener("click", function () {
            exportarConsumo("excel");
        });
    }

    async function consultarConsumo() {
        if (!validarFiltros()) return;

        pintarLoading();
        updateGridHeaders();

        const params = new URLSearchParams({
            unidad_id: unidad.value,
            tipo_consumo: tipoConsumo.value,
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
    function exportarConsumo(tipo) {
        if (!validarFiltros()) return;

        const params = new URLSearchParams({
            unidad_id: unidad.value,
            tipo_consumo: tipoConsumo.value,
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
        });

        const url = URL_CONSUMO_UNIDADES_EXPORTAR.replace("__TIPO__", tipo);

        window.open(`${url}?${params.toString()}`, "_blank");
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

        const fuelLabel = tipoConsumo.value === "urea" ? "Urea" : "Diésel";
        const lblTitleTotalLitros = document.getElementById("lblTitleTotalLitros");
        const lblTitleRendimientoPromedio = document.getElementById("lblTitleRendimientoPromedio");
        
        if (lblTitleTotalLitros) {
            lblTitleTotalLitros.textContent = `Total litros ${fuelLabel.toLowerCase()}`;
        }
        if (lblTitleRendimientoPromedio) {
            lblTitleRendimientoPromedio.textContent = `KM / Litro ${fuelLabel.toLowerCase()}`;
        }

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

    let googleMapInstance = null;
    let mapMarkers = [];
    let mapPathPolyline = null;

    function abrirMapaRuta(rowData) {
        const modalEl = document.getElementById('modalMapaRuta');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        if (mapMarkers) {
            mapMarkers.forEach(m => m.setMap(null));
        }
        mapMarkers = [];
        if (mapPathPolyline) {
            mapPathPolyline.setMap(null);
            mapPathPolyline = null;
        }

        setTimeout(() => {
            const mapDiv = document.getElementById('mapaRutaConsumo');
            if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                mapDiv.innerHTML = '<div class="alert alert-danger m-3">Google Maps API no está cargado correctamente.</div>';
                return;
            }

            const defaultCenter = { lat: 19.4326, lng: -99.1332 };
            const mapOptions = {
                zoom: 8,
                center: defaultCenter,
                mapTypeId: google.maps.MapTypeId.ROADMAP
            };

            googleMapInstance = new google.maps.Map(mapDiv, mapOptions);
            const bounds = new google.maps.LatLngBounds();

            if (rowData.diesel_lat && rowData.diesel_lng) {
                const dieselPos = { lat: parseFloat(rowData.diesel_lat), lng: parseFloat(rowData.diesel_lng) };
                const marker = new google.maps.Marker({
                    position: dieselPos,
                    map: googleMapInstance,
                    title: "Carga Diésel actual / Origen",
                    icon: 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
                });
                mapMarkers.push(marker);
                bounds.extend(dieselPos);
            }

            if (rowData.diesel_siguiente_lat && rowData.diesel_siguiente_lng) {
                const dieselSigPos = { lat: parseFloat(rowData.diesel_siguiente_lat), lng: parseFloat(rowData.diesel_siguiente_lng) };
                const marker = new google.maps.Marker({
                    position: dieselSigPos,
                    map: googleMapInstance,
                    title: "Siguiente carga Diésel / Destino",
                    icon: 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
                });
                mapMarkers.push(marker);
                bounds.extend(dieselSigPos);
            }

            const pathCoordinates = [];
            if (rowData.coordenadas_ruta && rowData.coordenadas_ruta.length > 0) {
                rowData.coordenadas_ruta.forEach((coord) => {
                    const pos = { lat: parseFloat(coord.latitud), lng: parseFloat(coord.longitud) };
                    pathCoordinates.push(pos);
                    bounds.extend(pos);

                    const dotMarker = new google.maps.Marker({
                        position: pos,
                        map: googleMapInstance,
                        title: `Punto rastreo: ${coord.registrado_en || ''}`,
                        icon: {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 4,
                            fillColor: "#007bff",
                            fillOpacity: 0.8,
                            strokeColor: "#ffffff",
                            strokeWeight: 1,
                        }
                    });
                    mapMarkers.push(dotMarker);
                });
            }

            if (pathCoordinates.length > 0) {
                mapPathPolyline = new google.maps.Polyline({
                    path: pathCoordinates,
                    geodesic: true,
                    strokeColor: '#007bff',
                    strokeOpacity: 0.8,
                    strokeWeight: 4,
                    map: googleMapInstance
                });
            } else if (rowData.diesel_lat && rowData.diesel_lng && rowData.diesel_siguiente_lat && rowData.diesel_siguiente_lng) {
                mapPathPolyline = new google.maps.Polyline({
                    path: [
                        { lat: parseFloat(rowData.diesel_lat), lng: parseFloat(rowData.diesel_lng) },
                        { lat: parseFloat(rowData.diesel_siguiente_lat), lng: parseFloat(rowData.diesel_siguiente_lng) }
                    ],
                    geodesic: true,
                    strokeColor: '#dc3545',
                    strokeOpacity: 0.6,
                    strokeWeight: 3,
                    map: googleMapInstance
                });
            }

            if (!bounds.isEmpty()) {
                googleMapInstance.fitBounds(bounds);
            } else {
                googleMapInstance.setCenter(defaultCenter);
                googleMapInstance.setZoom(6);
            }
        }, 300);
    }

    function updateGridHeaders() {
        if (!gridConsumoApi) return;
        const fuelLabel = tipoConsumo.value === "urea" ? "Urea" : "Diésel";
        
        let colDefs = [];
        if (typeof gridConsumoApi.getColumnDefs === "function") {
            colDefs = gridConsumoApi.getColumnDefs();
        } else if (gridOptionsConsumo.columnDefs) {
            colDefs = gridOptionsConsumo.columnDefs;
        }

        if (colDefs && colDefs.length > 0) {
            colDefs.forEach(col => {
                if (col.field === "litros_capturados_viaje") {
                    col.headerName = `Litros ${col.field === "litros_capturados_viaje" ? fuelLabel.toLowerCase() : ""} capturados`;
                    // Actually let's use exact match
                    col.headerName = `Litros ${fuelLabel.toLowerCase()} capturados`;
                }
                if (col.field === "litros_calculo_consumo") {
                    col.headerName = `Litros ${fuelLabel.toLowerCase()} cálculo`;
                }
            });

            if (typeof gridConsumoApi.setGridOption === "function") {
                gridConsumoApi.setGridOption("columnDefs", colDefs);
            } else if (typeof gridConsumoApi.setColumnDefs === "function") {
                gridConsumoApi.setColumnDefs(colDefs);
            }
        }
    }
});
