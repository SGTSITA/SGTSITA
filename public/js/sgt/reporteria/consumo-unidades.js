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
                window.open(`/reporteria/consumo-unidades/mapa/${rowData.asignacion_id}`, '_blank');
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

        // Show SweetAlert2 loader
        Swal.fire({
            title: 'Consultando información...',
            text: 'Por favor espera mientras recopilamos el historial del GPS.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const isRefresh = document.getElementById("chkRecargarCoordenadas")?.checked ? 1 : 0;

        const params = new URLSearchParams({
            unidad_id: unidad.value,
            tipo_consumo: tipoConsumo.value,
            fecha_inicio: fechaInicio.value,
            fecha_fin: fechaFin.value,
            refresh: isRefresh,
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

            Swal.close(); // Close loader
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
    let directionsRenderers = [];
    let currentActiveRowData = null;

    document.getElementById('selectSegmentoMapa').addEventListener('change', function() {
        if (currentActiveRowData) {
            renderizarMapaRuta(currentActiveRowData, this.value);
        }
    });

    document.getElementById('btnGuardarHistorial').addEventListener('click', async function() {
        if (!currentActiveRowData || !currentActiveRowData.coordenadas_ruta || currentActiveRowData.coordenadas_ruta.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'Sin datos',
                text: 'No hay coordenadas en el historial de este viaje para guardar.'
            });
            return;
        }

        Swal.fire({
            title: '¿Guardar historial?',
            text: 'Se registrarán de forma permanente estas coordenadas en la asignación para consultas rápidas posteriores.',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, guardar',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                try {
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                    const response = await fetch(URL_GUARDAR_COORDENADAS, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrfToken
                        },
                        body: JSON.stringify({
                            asignacion_id: currentActiveRowData.asignacion_id,
                            coordenadas: currentActiveRowData.coordenadas_ruta
                        })
                    });
                    const data = await response.json();
                    if (!response.ok || !data.success) {
                        throw new Error(data.message || 'Error al guardar coordenadas.');
                    }
                    return data;
                } catch (error) {
                    Swal.showValidationMessage(`Request failed: ${error}`);
                }
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: 'El historial de coordenadas ha sido guardado exitosamente.'
                });
            }
        });
    });

    function abrirMapaRuta(rowData) {
        currentActiveRowData = rowData;
        document.getElementById('selectSegmentoMapa').value = 'todos';

        // Set container and dates info in modal header
        document.getElementById('badgeContenedorModal').innerText = `Contenedor: ${rowData.contenedor || 'S/N'}`;
        document.getElementById('badgeFechasModal').innerText = `Ruta: ${rowData.fecha_inicio || 'S/N'} al ${rowData.fecha_fin || 'S/N'}`;

        const modalEl = document.getElementById('modalMapaRuta');
        const modal = new bootstrap.Modal(modalEl);
        modal.show();

        setTimeout(() => {
            renderizarMapaRuta(rowData, 'todos');
        }, 300);
    }

    function renderizarMapaRuta(rowData, tramo) {
        if (mapMarkers) {
            mapMarkers.forEach(m => m.setMap(null));
        }
        mapMarkers = [];
        if (mapPathPolyline) {
            mapPathPolyline.setMap(null);
            mapPathPolyline = null;
        }
        if (directionsRenderers) {
            directionsRenderers.forEach(r => r.setMap(null));
        }
        directionsRenderers = [];

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
        const infoWindow = new google.maps.InfoWindow();

        const timelineDiv = document.getElementById('timelineRutaConsumo');
        timelineDiv.innerHTML = '';
        let timelineItems = [];

        function getDistanceKm(lat1, lon1, lat2, lon2) {
            const R = 6371;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
        }

        let accumulatedDistances = [];
        let totalAccumulatedDist = 0;
        let deliveryIdx = -1;

        if (rowData.coordenadas_ruta && rowData.coordenadas_ruta.length > 0) {
            accumulatedDistances.push(0);
            for (let i = 1; i < rowData.coordenadas_ruta.length; i++) {
                const prev = rowData.coordenadas_ruta[i-1];
                const curr = rowData.coordenadas_ruta[i];
                totalAccumulatedDist += getDistanceKm(
                    parseFloat(prev.latitud), parseFloat(prev.longitud),
                    parseFloat(curr.latitud), parseFloat(curr.longitud)
                );
                accumulatedDistances.push(totalAccumulatedDist);
            }

            if (rowData.destino_lat && rowData.destino_lng) {
                const destLat = parseFloat(rowData.destino_lat);
                const destLng = parseFloat(rowData.destino_lng);
                let minCotiDist = Infinity;
                rowData.coordenadas_ruta.forEach((coord, idx) => {
                    const dist = getDistanceKm(destLat, destLng, parseFloat(coord.latitud), parseFloat(coord.longitud));
                    if (dist < minCotiDist) {
                        minCotiDist = dist;
                        deliveryIdx = idx;
                    }
                });
            }
        }

        let fullRuta = rowData.coordenadas_ruta || [];
        let filteredRuta = [];
        let startIdx = 0;
        let endIdx = fullRuta.length - 1;

        if (deliveryIdx !== -1) {
            if (tramo === 'ida') {
                endIdx = deliveryIdx;
            } else if (tramo === 'regreso') {
                startIdx = deliveryIdx;
            }
        }
        filteredRuta = fullRuta.slice(startIdx, endIdx + 1);

        let originLatLng = null;
        let destLatLng = null;

        if (filteredRuta.length > 0) {
            originLatLng = new google.maps.LatLng(parseFloat(filteredRuta[0].latitud), parseFloat(filteredRuta[0].longitud));
            destLatLng = new google.maps.LatLng(parseFloat(filteredRuta[filteredRuta.length - 1].latitud), parseFloat(filteredRuta[filteredRuta.length - 1].longitud));
        }

        // Draw start marker
        if (originLatLng) {
            let startTitle = tramo === 'regreso' ? "Punto de Entrega (Inicio Regreso)" : "Inicio del viaje (Origen)";
            let startTimelineTitle = tramo === 'regreso' ? "Punto de Entrega (Destino)" : "Inicio (Origen)";
            const marker = new google.maps.Marker({
                position: originLatLng,
                map: googleMapInstance,
                title: startTitle,
                icon: tramo === 'regreso' ? 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png' : 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
            });
            mapMarkers.push(marker);
            bounds.extend(originLatLng);

            const startInfoContent = `
                <div style="font-family: sans-serif; font-size: 13px; line-height: 1.4; padding: 5px;">
                    <h6 style="margin: 0 0 5px 0; color: #198754; font-weight: bold;"><i class="fas fa-play-circle me-1"></i>${startTitle}</h6>
                    <strong>Fecha/Hora:</strong> ${rowData.fecha_inicio || 'S/N'}<br>
                    <strong>Lat/Lng:</strong> ${originLatLng.lat().toFixed(6)}, ${originLatLng.lng().toFixed(6)}
                </div>
            `;
            marker.addListener('click', () => {
                infoWindow.setContent(startInfoContent);
                infoWindow.open(googleMapInstance, marker);
            });

            timelineItems.push({
                title: startTimelineTitle,
                time: rowData.fecha_inicio || 'S/N',
                lat: originLatLng.lat(),
                lng: originLatLng.lng(),
                badgeClass: tramo === 'regreso' ? 'bg-primary' : 'bg-success',
                iconClass: tramo === 'regreso' ? 'fa-map-marker-alt' : 'fa-play'
            });
        }

        // Find closest point to diesel loading in this filtered subset
        let dieselInsertIdx = -1;
        if (rowData.diesel_lat && rowData.diesel_lng) {
            const dLat = parseFloat(rowData.diesel_lat);
            const dLng = parseFloat(rowData.diesel_lng);
            let minTrackDist = Infinity;
            filteredRuta.forEach((coord, idx) => {
                const dist = getDistanceKm(dLat, dLng, parseFloat(coord.latitud), parseFloat(coord.longitud));
                if (dist < minTrackDist) {
                    minTrackDist = dist;
                    dieselInsertIdx = idx;
                }
            });
        }

        // Render intermediate tracking markers
        let lastMarkerPos = null;
        filteredRuta.forEach((coord, idx) => {
            const globalIdx = startIdx + idx;
            const pos = { lat: parseFloat(coord.latitud), lng: parseFloat(coord.longitud) };
            bounds.extend(pos);

            let labelType = "Ida";
            let labelClass = "badge bg-info text-white ms-2";
            if (deliveryIdx !== -1) {
                if (globalIdx < deliveryIdx) {
                    labelType = "Ida";
                    labelClass = "badge bg-info text-white ms-2";
                } else if (globalIdx === deliveryIdx) {
                    labelType = "Entrega";
                    labelClass = "badge bg-dark text-white ms-2";
                } else {
                    labelType = "Vuelta";
                    labelClass = "badge bg-secondary text-white ms-2";
                }
            }

            // Downsample markers to avoid clustering (threshold: 4 km)
            let shouldDrawMarker = idx === 0 || 
                                   idx === filteredRuta.length - 1 || 
                                   globalIdx === deliveryIdx ||
                                   globalIdx === (deliveryIdx - 1) ||
                                   globalIdx === (deliveryIdx + 1);
            if (!shouldDrawMarker && lastMarkerPos) {
                const dist = getDistanceKm(lastMarkerPos.lat, lastMarkerPos.lng, pos.lat, pos.lng);
                if (dist >= 4.0) {
                    shouldDrawMarker = true;
                }
            }

            if (shouldDrawMarker) {
                lastMarkerPos = pos;
                let markerTitle = `Punto de rastreo (${labelType})`;
                let markerIcon = {
                    path: google.maps.SymbolPath.CIRCLE,
                    scale: 5,
                    fillColor: globalIdx === deliveryIdx ? "#343a40" : (globalIdx < deliveryIdx ? "#007bff" : "#6c757d"),
                    fillOpacity: 0.9,
                    strokeColor: "#ffffff",
                    strokeWeight: 1,
                };

                let dotMarker;
                if (globalIdx === deliveryIdx && tramo === 'todos') {
                    dotMarker = new google.maps.Marker({
                        position: pos,
                        map: googleMapInstance,
                        title: "Punto de Entrega (Destino)",
                        icon: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                    });
                } else {
                    dotMarker = new google.maps.Marker({
                        position: pos,
                        map: googleMapInstance,
                        title: `${markerTitle}: ${coord.registrado_en || ''} (A ${accumulatedDistances[globalIdx].toFixed(1)} km)`,
                        icon: markerIcon
                    });
                }
                mapMarkers.push(dotMarker);

                const infoContent = `
                    <div style="font-family: sans-serif; font-size: 13px; line-height: 1.4; padding: 5px;">
                        <h6 style="margin: 0 0 5px 0; color: #007bff; font-weight: bold;"><i class="fas fa-map-marker-alt me-1"></i>Punto de Rastreo <span class="${labelClass}">${labelType}</span></h6>
                        <strong>Fecha/Hora:</strong> ${coord.registrado_en || 'S/N'}<br>
                        <strong>Distancia recorrida:</strong> ${accumulatedDistances[globalIdx].toFixed(1)} km<br>
                        <strong>Lat/Lng:</strong> ${parseFloat(coord.latitud).toFixed(5)}, ${parseFloat(coord.longitud).toFixed(5)}
                    </div>
                `;
                dotMarker.addListener('click', () => {
                    infoWindow.setContent(infoContent);
                    infoWindow.open(googleMapInstance, dotMarker);
                });

                timelineItems.push({
                    title: `Ruta (${accumulatedDistances[globalIdx].toFixed(1)} km) <span class="${labelClass}" style="font-size:10px;">${labelType}</span>`,
                    time: coord.registrado_en || 'S/N',
                    lat: pos.lat,
                    lng: pos.lng,
                    badgeClass: globalIdx === deliveryIdx ? 'bg-dark' : (globalIdx < deliveryIdx ? 'bg-info' : 'bg-secondary'),
                    iconClass: 'fa-map-pin'
                });
            }

            // Diesel refueling event marker
            if (idx === dieselInsertIdx && rowData.diesel_lat && rowData.diesel_lng) {
                const dieselLatLng = new google.maps.LatLng(parseFloat(rowData.diesel_lat), parseFloat(rowData.diesel_lng));
                const dieselMarker = new google.maps.Marker({
                    position: dieselLatLng,
                    map: googleMapInstance,
                    title: `Carga de Diésel (A ${accumulatedDistances[globalIdx].toFixed(1)} km del inicio)`,
                    icon: 'https://maps.google.com/mapfiles/ms/icons/orange-dot.png'
                });
                mapMarkers.push(dieselMarker);

                const dieselInfoContent = `
                    <div style="font-family: sans-serif; font-size: 13px; line-height: 1.4; padding: 5px;">
                        <h6 style="margin: 0 0 5px 0; color: #fd7e14; font-weight: bold;"><i class="fas fa-gas-pump me-1"></i>Carga de Diésel <span class="${labelClass}">${labelType}</span></h6>
                        <strong>Aprox. Recorrido:</strong> ${accumulatedDistances[globalIdx].toFixed(1)} km<br>
                        <strong>Lat/Lng:</strong> ${dieselLatLng.lat().toFixed(5)}, ${dieselLatLng.lng().toFixed(5)}
                    </div>
                `;
                dieselMarker.addListener('click', () => {
                    infoWindow.setContent(dieselInfoContent);
                    infoWindow.open(googleMapInstance, dieselMarker);
                });

                timelineItems.push({
                    title: `Carga de Diésel (${accumulatedDistances[globalIdx].toFixed(1)} km) <span class="${labelClass}" style="font-size:10px;">${labelType}</span>`,
                    time: coord.registrado_en || 'S/N',
                    lat: dieselLatLng.lat(),
                    lng: dieselLatLng.lng(),
                    badgeClass: 'bg-warning text-dark',
                    iconClass: 'fa-gas-pump'
                });
            }
        });

        // Destination marker
        if (destLatLng) {
            let destTitle = tramo === 'ida' ? "Punto de Entrega (Destino)" : "Fin del viaje (Retorno Lázaro)";
            let destTimelineTitle = tramo === 'ida' ? "Punto de Entrega (Destino)" : "Fin (Retorno Lázaro)";
            const marker = new google.maps.Marker({
                position: destLatLng,
                map: googleMapInstance,
                title: destTitle,
                icon: tramo === 'ida' ? 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png' : 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
            });
            mapMarkers.push(marker);
            bounds.extend(destLatLng);

            let fechaFinDisplay = 'S/N';
            if (tramo === 'ida' && deliveryIdx !== -1) {
                fechaFinDisplay = fullRuta[deliveryIdx].registrado_en || 'S/N';
            } else if (rowData.viaje_finalizado_lat && rowData.viaje_finalizado_lng) {
                fechaFinDisplay = rowData.viaje_finalizado_fecha || 'S/N';
            } else if (filteredRuta.length > 0) {
                fechaFinDisplay = filteredRuta[filteredRuta.length - 1].registrado_en || 'S/N';
            }

            const destInfoContent = `
                <div style="font-family: sans-serif; font-size: 13px; line-height: 1.4; padding: 5px;">
                    <h6 style="margin: 0 0 5px 0; color: #dc3545; font-weight: bold;"><i class="fas fa-flag-checkered me-1"></i>${destTitle}</h6>
                    <strong>Fecha/Hora:</strong> ${fechaFinDisplay}<br>
                    <strong>Lat/Lng:</strong> ${destLatLng.lat().toFixed(6)}, ${destLatLng.lng().toFixed(6)}
                </div>
            `;
            marker.addListener('click', () => {
                infoWindow.setContent(destInfoContent);
                infoWindow.open(googleMapInstance, marker);
            });

            timelineItems.push({
                title: destTimelineTitle,
                time: fechaFinDisplay,
                lat: destLatLng.lat(),
                lng: destLatLng.lng(),
                badgeClass: tramo === 'ida' ? 'bg-primary' : 'bg-danger',
                iconClass: 'fa-flag-checkered'
            });
        }

        // Draw path using Directions Service (Route by road)
        function trazarRutaCarretera(origin, dest, pts, color) {
            let waypoints = [];
            if (pts && pts.length > 2) {
                let rawIntermediates = pts.slice(1, -1);
                let step = 1;
                if (rawIntermediates.length > 21) {
                    step = rawIntermediates.length / 21;
                }
                for (let i = 0; i < 21 && i * step < rawIntermediates.length; i++) {
                    let idx = Math.floor(i * step);
                    let pt = rawIntermediates[idx];
                    waypoints.push({
                        location: new google.maps.LatLng(parseFloat(pt.latitud), parseFloat(pt.longitud)),
                        stopover: false
                    });
                }
            }

            const directionsService = new google.maps.DirectionsService();
            const directionsRenderer = new google.maps.DirectionsRenderer({
                map: googleMapInstance,
                suppressMarkers: true,
                polylineOptions: {
                    strokeColor: color,
                    strokeOpacity: 0.8,
                    strokeWeight: 5
                }
            });
            directionsRenderers.push(directionsRenderer);

            directionsService.route({
                origin: origin,
                destination: dest,
                waypoints: waypoints,
                optimizeWaypoints: false,
                travelMode: google.maps.TravelMode.DRIVING
            }, function(response, status) {
                if (status === 'OK') {
                    directionsRenderer.setDirections(response);
                } else {
                    console.warn('Directions API failed (' + status + '). Fallback to polyline.');
                    trazarPolylineDirecta(origin, dest, pts, color);
                }
            });
        }

        function trazarPolylineDirecta(origin, dest, pts, color) {
            const pathCoordinates = [];
            if (origin) pathCoordinates.push(origin);
            if (pts && pts.length > 0) {
                pts.forEach(c => {
                    pathCoordinates.push({ lat: parseFloat(c.latitud), lng: parseFloat(c.longitud) });
                });
            }
            if (dest) pathCoordinates.push(dest);

            if (pathCoordinates.length > 0) {
                const poly = new google.maps.Polyline({
                    path: pathCoordinates,
                    geodesic: true,
                    strokeColor: color,
                    strokeOpacity: 0.8,
                    strokeWeight: 4,
                    map: googleMapInstance
                });
            }
        }

        // Trigger road-based routing depending on segment selection
        if (tramo === 'todos' && deliveryIdx !== -1) {
            let rutaIda = fullRuta.slice(0, deliveryIdx + 1);
            let rutaRegreso = fullRuta.slice(deliveryIdx);
            
            let oIda = new google.maps.LatLng(parseFloat(rutaIda[0].latitud), parseFloat(rutaIda[0].longitud));
            let dIda = new google.maps.LatLng(parseFloat(rutaIda[rutaIda.length - 1].latitud), parseFloat(rutaIda[rutaIda.length - 1].longitud));
            
            let oRegreso = new google.maps.LatLng(parseFloat(rutaRegreso[0].latitud), parseFloat(rutaRegreso[0].longitud));
            let dRegreso = new google.maps.LatLng(parseFloat(rutaRegreso[rutaRegreso.length - 1].latitud), parseFloat(rutaRegreso[rutaRegreso.length - 1].longitud));

            trazarRutaCarretera(oIda, dIda, rutaIda, '#007bff'); // Blue for Ida
            trazarRutaCarretera(oRegreso, dRegreso, rutaRegreso, '#dc3545'); // Red/Orange for Regreso
        } else {
            if (originLatLng && destLatLng) {
                trazarRutaCarretera(originLatLng, destLatLng, filteredRuta, tramo === 'ida' ? '#007bff' : '#dc3545');
            }
        }

        // Render Timeline
        if (timelineItems.length > 0) {
            let html = '<div class="list-group list-group-flush">';
            timelineItems.forEach((item, index) => {
                html += `
                    <a href="javascript:void(0);" class="list-group-item list-group-item-action py-2 lh-sm item-timeline-map" data-lat="${item.lat}" data-lng="${item.lng}" data-index="${index}" style="font-size: 11.5px;">
                        <div class="d-flex w-100 flex-column mb-1">
                            <div class="d-flex justify-content-between align-items-center">
                                <strong class="mb-0 text-dark" style="font-size: 11.5px;"><span class="badge ${item.badgeClass} me-2"><i class="fas ${item.iconClass}"></i></span>${item.title}</strong>
                                <small class="text-muted font-weight-bold" style="font-size: 10px;">${item.time}</small>
                            </div>
                        </div>
                        <div class="text-muted ps-4" style="font-size: 10.5px; margin-top: 2px;">
                            <i class="fas fa-map-marker-alt me-1"></i>Coord: <strong>${item.lat.toFixed(6)}, ${item.lng.toFixed(6)}</strong>
                        </div>
                    </a>
                `;
            });
            html += '</div>';
            timelineDiv.innerHTML = html;

            document.querySelectorAll('.item-timeline-map').forEach(el => {
                el.addEventListener('click', function() {
                    const lat = parseFloat(this.getAttribute('data-lat'));
                    const lng = parseFloat(this.getAttribute('data-lng'));
                    if (googleMapInstance) {
                        googleMapInstance.setCenter({ lat, lng });
                        googleMapInstance.setZoom(14);
                    }
                });
            });
        }

        if (!bounds.isEmpty()) {
            googleMapInstance.fitBounds(bounds);
        } else {
            googleMapInstance.setCenter(defaultCenter);
            googleMapInstance.setZoom(6);
        }
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
