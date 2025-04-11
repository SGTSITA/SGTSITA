document.addEventListener("DOMContentLoaded", function () {
    let gridApi;
    let currentTab = "planeadas";

    const tabs = document.querySelectorAll('#cotTabs .nav-link');
    tabs.forEach(tab => {
        tab.addEventListener("click", function () {
            tabs.forEach(t => t.classList.remove("active"));
            this.classList.add("active");
            currentTab = this.getAttribute("data-status");
            getCotizacionesList();
        });
    });

    const columnDefs = [
        { headerCheckboxSelection: true, checkboxSelection: true, width: 50 },
        { headerName: "No", field: "id", sortable: true, filter: true },
        { headerName: "Cliente", field: "cliente", sortable: true, filter: true },
        { headerName: "Origen", field: "origen", sortable: true, filter: true },
        { headerName: "Destino", field: "destino", sortable: true, filter: true },
        { headerName: "# Contenedor", field: "contenedor", sortable: true, filter: true },
        {
            headerName: "Estatus",
            field: "estatus",
            minWidth: 180,
            cellRenderer: function (params) {
                let color = "secondary";
                if (params.data.estatus === "Aprobada") color = "success";
                else if (params.data.estatus === "Cancelada") color = "danger";
                else if (params.data.estatus === "Pendiente") color = "warning";
        
                return `
                    <button class="btn btn-sm btn-outline-${color}" onclick="abrirCambioEstatus(${params.data.id})" title="Cambiar estatus">
                        <i class="fa fa-sync-alt me-1"></i> ${params.data.estatus}
                    </button>
                `;
            }
        },        
        {
            headerName: "Coordenadas",
            field: "coordenadas",
            minWidth: 180,
            sortable: false,
            filter: false,
            cellRenderer: function (params) {
                if (params.data.id_asignacion) {
                    return `
                        <a href="/coordenadas/${params.data.id_asignacion}" class="btn btn-sm btn-outline-info" title="Ver coordenadas">
                            <i class="fa fa-map-marker-alt"></i> Coord.
                        </a>
                    `;
                }
                return `<span class="text-muted">N/A</span>`;
            }
        },
        {
            headerName: "Acciones",
            field: "acciones",
            minWidth: 500,
            cellRenderer: function (params) {
                let acciones = "";

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
                        `}`;
                } else if (currentTab === "finalizadas") {
                    acciones = `
                    <a href="${params.data.edit_url}" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-primary" onclick="descargarPDF(${params.data.id})" title="Descargar PDF">
                            <i class="fa fa-file-pdf"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                    `;
                } else if (currentTab === "en_espera") {
                    acciones = `
                        <a href="${params.data.edit_url}" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-info" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                     
                    `;
                } else if (currentTab === "aprobadas") {
                    acciones = `
                     <a href="${params.data.edit_url}" class="btn btn-sm btn-outline-secondary" title="Editar">
                            <i class="fa fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-outline-primary" onclick="abrirCambioEmpresa(${params.data.id})" title="Cambiar Empresa">
                            <i class="fa fa-exchange-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-outline-info" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                       
                    `;
                } else if (currentTab === "canceladas") {
                    acciones = `
<button class="btn btn-sm btn-outline-warning" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
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
    };

    const myGridElement = document.querySelector("#myGrid");
    gridApi = agGrid.createGrid(myGridElement, gridOptions);

    getCotizacionesList();

    function getCotizacionesList() {
        const overlay = document.getElementById("gridLoadingOverlay");
        overlay.style.display = "flex";
    
        let url = "/cotizaciones/list";
        if (currentTab === "finalizadas") url = "/cotizaciones/finalizadas";
        if (currentTab === "en_espera") url = "/cotizaciones/espera";
        if (currentTab === "aprobadas") url = "/cotizaciones/aprobadas";
        if (currentTab === "canceladas") url = "/cotizaciones/canceladas";
    
        gridApi.setGridOption("rowData", []); 
    
        fetch(url)
            .then(response => response.json())
            .then(data => {
                gridApi.setGridOption("rowData", data.list);
            })
            .catch(error => {
                console.error("❌ Error al obtener la lista de cotizaciones:", error);
            })
            .finally(() => {
                overlay.style.display = "none"; 
            });
    }
});      


function abrirDocumentos(idCotizacion) {
    $(`#estatusDoc${idCotizacion}`).modal("show");
}

function descargarPDF(idCotizacion) {
    const fecha = new Date().toISOString().slice(0, 10); // formato: YYYY-MM-DD
    const link = document.createElement('a');
    link.href = `/cotizaciones/pdf/${idCotizacion}`;
    link.download = `cotizacion_${idCotizacion}_${fecha}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}


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
function abrirCambioEmpresa(idCotizacion) {
    const form = document.getElementById("formCambioEmpresa");
    const route = `/cotizaciones/cambiar/empresa/${idCotizacion}`;
    form.setAttribute("action", route);

    const modal = new bootstrap.Modal(document.getElementById("modalCambioEmpresa"));
    modal.show();
}


function abrirCambioEstatus(idCotizacion) {
    const form = document.getElementById("formCambioEstatus");

    if (!form) {
        console.error("❌ No se encontró el formulario #formCambioEstatus");
        return;
    }

    // Setear la acción del formulario
    form.action = `/cotizaciones/update/estatus/${idCotizacion}`;

    // Mostrar el modal
    const modal = new bootstrap.Modal(document.getElementById("modalCambioEstatus"));
    modal.show();
}
function abrirDocumentos(idCotizacion) {
    fetch(`/cotizaciones/documentos/${idCotizacion}`)
        .then(response => response.json())
        .then(data => {
            const modal = new bootstrap.Modal(document.getElementById("modalEstatusDocumentos"));
            const titulo = document.getElementById("tituloContenedor");
            const cuerpo = document.getElementById("estatusDocumentosBody");

            titulo.innerText = `#${data.num_contenedor ?? 'N/A'}`;
            cuerpo.innerHTML = '';

            const campos = [
                { label: 'Num contenedor', valor: data.num_contenedor },
                { label: 'Documento CCP', valor: data.doc_ccp },
                { label: 'Boleta de Liberación', valor: data.boleta_liberacion },
                { label: 'Doda', valor: data.doda },
                { label: 'Carta Porte', valor: data.carta_porte },
                { label: 'Boleta Vacio', valor: data.boleta_vacio === 'si' },
                { label: 'EIR', valor: data.doc_eir },
            ];

            campos.forEach(item => {
                const col = document.createElement('div');
                col.className = 'col-6';
                col.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid ${item.valor ? 'fa-check-circle text-success' : 'fa-times-circle text-muted'}"></i>
                        <span class="fw-semibold">${item.label}</span>
                    </div>
                `;
                cuerpo.appendChild(col);
            });
        
            modal.show();
        })
        .catch(error => {
            console.error('Error al obtener documentos:', error);
            Swal.fire('Error', 'No se pudieron obtener los documentos', 'error');
        });
}




