document.addEventListener("DOMContentLoaded", function () {
    let gridApi;
    let currentTab = "planeadas"; // 🔹 Pestaña por defecto

    // 📌 Detectar la pestaña actual en función de la URL
    function setCurrentTab() {
        const url = window.location.href;
        if (url.includes("index_finzaliadas")) {
            currentTab = "finalizadas";
        } else if (url.includes("index_espera")) {
            currentTab = "en_espera";
        } else if (url.includes("index_aprobadas")) {
            currentTab = "aprobadas";
        } else if (url.includes("index_canceladas")) {
            currentTab = "canceladas";
        } else {
            currentTab = "planeadas";
        }
        console.log(`📌 Pestaña activa: ${currentTab}`);
    }

    // 📌 Definir las columnas de AG Grid con Acciones según la pestaña
    const columnDefs = [
        { headerCheckboxSelection: true, checkboxSelection: true, width: 50 },
        { headerName: "No", field: "id", sortable: true, filter: true },
        { headerName: "Cliente", field: "cliente", sortable: true, filter: true },
        { headerName: "Origen", field: "origen", sortable: true, filter: true },
        { headerName: "Destino", field: "destino", sortable: true, filter: true },
        { headerName: "# Contenedor", field: "contenedor", sortable: true, filter: true },
        { headerName: "Estatus", field: "estatus", sortable: true, filter: true },
        { headerName: "Coordenadas", field: "coordenadas", sortable: true, filter: true },
        {
            headerName: "Acciones",
            field: "acciones",
            minWidth: 500,
            cellRenderer: function (params) {
                let acciones = "";

                // 📌 Acciones para "Planeadas"
                if (currentTab === "planeadas") {
                    acciones = `
                        <a href="${params.data.edit_url}" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-primary" onclick="abrirCambioEmpresa(${params.data.id})" title="Cambiar Empresa">
                            <i class="fa fa-exchange-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-warning" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                        ${params.data.tipo_asignacion === "Propio" ? `
                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#cambioModal${params.data.id}" title="Asignación: Propio">
                                Propio
                            </button>
                        ` : `
                            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#cambioModal${params.data.id}" title="Asignación: Subcontratado">
                                Sub.
                            </button>
                        `}
                    `;
                }

                // 📌 Acciones para "Finalizadas"
                else if (currentTab === "finalizadas") {
                    acciones = `
                        <button class="btn btn-sm btn-outline-primary" onclick="descargarPDF(${params.data.id})" title="Descargar PDF">
                            <i class="fa fa-file-pdf"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#estatusDoc${params.data.id}" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                    `;
                }

                // 📌 Acciones para "En Espera"
                else if (currentTab === "en_espera") {
                    acciones = `
                        <a href="${params.data.edit_url}" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-success" onclick="aprobarCotizacion(${params.data.id})" title="Aprobar">
                            <i class="fa fa-check"></i>
                        </button>
                    `;
                }

                // 📌 Acciones para "Aprobadas"
                else if (currentTab === "aprobadas") {
                    acciones = `
                        <button class="btn btn-sm btn-outline-danger" onclick="cancelarCotizacion(${params.data.id})" title="Cancelar">
                            <i class="fa fa-times"></i>
                        </button>
                    `;
                }

                // 📌 Acciones para "Canceladas"
                else if (currentTab === "canceladas") {
                    acciones = `
                        <button class="btn btn-sm btn-outline-info" data-bs-toggle="modal" data-bs-target="#estatusDoc${params.data.id}" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                    `;
                }

                return acciones;
            }
        }
    ];

    const gridOptions = {
        columnDefs: columnDefs,
        pagination: true,
        paginationPageSize: 100,
        rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1
        },
        onGridReady: function (params) {
            gridApi = params.api;
            setCurrentTab();
            getCotizacionesList();
        }
    };

    const myGridElement = document.querySelector("#myGrid");

    if (agGrid && agGrid.createGrid) {
        gridApi = agGrid.createGrid(myGridElement, gridOptions);
    } else {
        new agGrid.Grid(myGridElement, gridOptions);
    }

    function getCotizacionesList() {
        console.log("📡 Solicitando datos a /cotizaciones/list...");

        fetch('/cotizaciones/list')
            .then(response => response.json())
            .then(data => {
                console.log("📥 Datos recibidos en AG Grid:", data.list);
                if (!gridApi) {
                    console.error("❌ AG Grid API no está disponible aún.");
                    return;
                }
                gridApi.setGridOption("rowData", data.list);
            })
            .catch(error => console.error("❌ Error al obtener la lista de cotizaciones:", error));
    }
});

// 📌 Función para abrir el cambio de empresa
function abrirCambioEmpresa(idCotizacion) {
    $(`#cambioEmpresa${idCotizacion}`).modal("show");
}

// 📌 Función para abrir los documentos
function abrirDocumentos(idCotizacion) {
    $(`#estatusDoc${idCotizacion}`).modal("show");
}

// 📌 Función para descargar el PDF de la cotización
function descargarPDF(idCotizacion) {
    window.open(`/cotizaciones/pdf/${idCotizacion}`, "_blank");
}

// 📌 Función para aprobar cotización en "En Espera"
function aprobarCotizacion(idCotizacion) {
    Swal.fire({
        title: "¿Aprobar cotización?",
        icon: "question",
        showCancelButton: true,
        confirmButtonText: "Sí, aprobar"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/cotizaciones/update/estatus/${idCotizacion}`, {
                method: "PATCH",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
                body: JSON.stringify({ estatus: "Aprobada" })
            })
            .then(() => Swal.fire("Aprobada", "Cotización aprobada", "success"))
            .then(() => getCotizacionesList());
        }
    });
}

// 📌 Función para cancelar cotización en "Aprobadas"
function cancelarCotizacion(idCotizacion) {
    Swal.fire({
        title: "¿Cancelar cotización?",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/cotizaciones/update/estatus/${idCotizacion}`, {
                method: "PATCH",
                headers: {
                    "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').getAttribute("content")
                },
                body: JSON.stringify({ estatus: "Cancelada" })
            })
            .then(() => Swal.fire("Cancelada", "Cotización cancelada", "success"))
            .then(() => getCotizacionesList());
        }
    });
}
