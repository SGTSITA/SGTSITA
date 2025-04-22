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
                    <button class="btn btn-sm btn-outline-info" 
                    onclick="abrirModalCoordenadas(${params.data.id_asignacion})" 
                     title="Compartir coordenadas">
                     <i class="fa fa-map-marker-alt"></i> Compartir
                     </button>
                         <input type="hidden" id="idCotizacionCompartir" value="${params.data.id}">
                         <input type="hidden" id="idAsignacionCompartir" value="${params.data.id_asignacion}">
                         
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
document.addEventListener('DOMContentLoaded', function () {
    // Abrir el modal
    window.abrirModalCoordenadas = function(id_asignacion) {
        const modal = document.getElementById('modalCoordenadas');
        if (modal) {
            modal.style.display = 'block';
        
            // Backdrop
            if (!document.querySelector('.modal-backdrop')) {
                const backdrop = document.createElement('div');
                backdrop.classList.add('modal-backdrop');
                backdrop.style.position = 'fixed';
                backdrop.style.top = '0';
                backdrop.style.left = '0';
                backdrop.style.width = '100%';
                backdrop.style.height = '100%';
                backdrop.style.backgroundColor = 'rgba(0,0,0,0.5)';
                backdrop.style.zIndex = '1040';
                document.body.appendChild(backdrop);
            }
        
            document.body.classList.add('modal-open');
            document.body.style.overflow = 'hidden';
        
            // Activar tab Mail por defecto
            cambiarTab('mail');
        }

        // Asegúrate de que el select para tipo de cuestionario exista
        const tipoSelect = document.getElementById('optipoCuestionario');
        if (tipoSelect) {
            tipoSelect.addEventListener('change', function() {
                // Verifica si se seleccionó una opción válida
                const tipoSeleccionado = tipoSelect.value;

                if (tipoSeleccionado) {
                    // Aquí haces el fetch solo si hay una opción seleccionada
                    fetchCotizacion(id_asignacion, tipoSeleccionado);
                } else {
                    // Si no hay selección válida, limpiamos los datos
                    limpiarDatos();
                }
            });
        }
    };

    // Función para buscar los datos de la cotización
    function fetchCotizacion(id_asignacion, tipoCuestionario) {
        const link = `${window.location.origin}/coordenadas/${id_asignacion}/${tipoCuestionario}`;
        let _url = `/cotizaciones/get/${id_asignacion}`;

        fetch(_url)
            .then(response => response.json())
            .then(data => {
                if (!data.list || data.list.length === 0) {
                    let messageNoData = "No se encontró información para esta cotización.";
                    // Limpiar la información cuando no se encuentra
                    limpiarDatos(messageNoData);
                    return;
                }

                const item = data.list[0];
                const mensaje = ` ${item.contenedor}`;
                // mail
                document.getElementById('linkMail').innerText = link;
                document.getElementById('mensajeText').innerText = mensaje;
                // whatsapp
                document.getElementById("wmensajeText").innerText = mensaje;
                document.getElementById("linkWhatsapp").value = link;

                // 🟢 Armamos el link para WhatsApp
                const textoWhatsapp = `Contenedor: ${mensaje}\n\n${link}`;
                document.getElementById("whatsappLink").href = `https://wa.me/?text=${encodeURIComponent(textoWhatsapp)}`;
            })
            .catch(error => {
                console.error("❌ Error al obtener info de cotizaciones:", error);
            });
    }

    // Función para limpiar los datos
    function limpiarDatos(message = "") {
        // Limpiar los valores cuando no se selecciona una opción válida
        document.getElementById('linkMail').innerText = message;
        document.getElementById('asuntoText').innerText = "";
        document.getElementById('mensajeText').innerText = "";
        document.getElementById("wasuntoText").innerText = message;
        document.getElementById("wmensajeText").innerText = "";
        document.getElementById("linkWhatsapp").value = "";
        document.getElementById("whatsappLink").href = "#";
        document.getElementById("idAsignacionCompartir").value = "";
    }
});
function cambiarTab(tabId) {
    // Ocultamos todos los divs con clase 'tab-content'
    const tabs = document.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.style.display = 'none';
    });

    // Mostramos solo el que corresponde
    const tabToShow = document.getElementById('tab-' + tabId);
    if (tabToShow) {
        tabToShow.style.display = 'block';
    } else {
        console.error(`No se encontró el tab: tab-${tabId}`);
    }
}

function mostrarTab(tab) {
    // Ocultar ambos
    document.getElementById('tab-mail').style.display = 'none';
    document.getElementById('tab-whatsapp').style.display = 'none';

    // Quitar clase activa
    const tabs = document.querySelectorAll('.nav-link');
    tabs.forEach(el => el.classList.remove('active'));

    // Mostrar el tab seleccionado
    document.getElementById(`tab-${tab}`).style.display = 'block';

    // Activar tab
    document.querySelector(`.nav-link[href="#"][onclick*="${tab}"]`).classList.add('active');
}

function cerrarModal() {
    const modal = document.getElementById('modalCoordenadas');

    if (modal) {
        modal.style.display = 'none';
    }

    // Eliminar el fondo oscuro si existe
    const backdrop = document.querySelector('.modal-backdrop');
    if (backdrop) {
        backdrop.remove();
    }

    // Quitar la clase modal-open del body
    document.body.classList.remove('modal-open');
    document.body.style.overflow = ''; // restaurar scroll
}

function copiarDesdeInput(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("¡Enlace copiado!");
}


function enviarMailCoordenadas() {

    const mensaje = document.getElementById('mensajeText').innerText;
    const asunto = 'No. contenedor: ' + mensaje;

    const link = document.getElementById('linkMail').innerText;
    const correo = document.getElementById('correoDestino').value;
 
    fetch('/cotizaciones/mail-coordenadas', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
        body: JSON.stringify({
            correo: correo,
            asunto: asunto,
            mensaje: mensaje,
            link:  link
        })
    })
    .then(res => res.json())
    .then(data => alert('Correo enviado ✅'))
    .catch(err => console.error('Error:', err));
    saveCoordenadas();
    
}
function guardarYAbrirWhatsApp(event) {
    event.preventDefault(); // Evita que el enlace se abra inmediatamente
    saveCoordenadas();
    window.open(document.getElementById('whatsappLink').href, '_blank');
       
}

function saveCoordenadas() {

    let idAsignacionSave= document.getElementById("idAsignacionCompartir").value;
    let idCotizacionSave= document.getElementById("idCotizacionCompartir").value;
    if (idAsignacionSave != null)
    {

        let tipoFlujo = null;
        let registroPuerto = null;
        let dentroPuerto = null;
        let descargaVacio = null;
        let cargadoContenedor = null;
        let filaFiscal = null;
        let moduladoTipo = null;
        let moduladoCoordenada = null;
        let enDestino = null;
        let inicioDescarga = null;
        let finDescarga = null;
        let recepcionDocFirmados = null;
        
        let tipoFlujoDatetime = null;
        let registroPuertoDatetime = null;
        let dentroPuertoDatetime = null;
        let descargaVacioDatetime = null;
        let cargadoContenedorDatetime = null;
        let filaFiscalDatetime = null;
        let moduladoTipoDatetime = null;
        let moduladoCoordenadaDatetime = null;
        let enDestinoDatetime = null;
        let inicioDescargaDatetime = null;
        let finDescargaDatetime = null;
        let recepcionDocFirmadosDatetime = null;
     
     
        const data = {
         idAsig: idAsignacionSave,
         idCotSave: idCotizacionSave,
     
         tipo_flujo: tipoFlujo ?? null,
         registro_puerto: registroPuerto ?? null,
         dentro_puerto: dentroPuerto ?? null,
         descarga_vacio: descargaVacio ?? null,
         cargado_contenedor: cargadoContenedor ?? null,
         fila_fiscal: filaFiscal ?? null,
         modulado_tipo: moduladoTipo ?? null,
         modulado_coordenada: moduladoCoordenada ?? null,
         en_destino: enDestino ?? null,
         inicio_descarga: inicioDescarga ?? null,
         fin_descarga: finDescarga ?? null,
         recepcion_doc_firmados: recepcionDocFirmados ?? null,
     
         tipo_flujo_datatime: tipoFlujoDatetime ?? null,
         registro_puerto_datatime: registroPuertoDatetime ?? null,
         dentro_puerto_datatime: dentroPuertoDatetime ?? null,
         descarga_vacio_datatime: descargaVacioDatetime ?? null,
         cargado_contenedor_datatime: cargadoContenedorDatetime ?? null,
         fila_fiscal_datatime: filaFiscalDatetime ?? null,
         modulado_tipo_datatime: moduladoTipoDatetime ?? null,
         modulado_coordenada_datatime: moduladoCoordenadaDatetime ?? null,
         en_destino_datatime: enDestinoDatetime ?? null,
         inicio_descarga_datatime: inicioDescargaDatetime ?? null,
         fin_descarga_datatime: finDescargaDatetime ?? null,
         recepcion_doc_firmados_datatime: recepcionDocFirmadosDatetime ?? null,
        }  ;
     
         fetch('/coordenadas/save', {
             method: 'POST',
             headers: {
                 'Content-Type': 'application/json',
                 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
             },
             body: JSON.stringify(data)
         })
         .then(res => {
            if (res.status === 204) {
                console.log('Coordenadas Actualizadas ✅');
            } else {
                return res.json().then(data => {
                    console.log('Coordenadas Actualizadas ✅');
                });
            }
        })
        .catch(err => console.error('Error:', err));
    
    }
    
}