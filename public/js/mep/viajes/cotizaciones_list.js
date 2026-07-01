const localeText = {
    page: "Página",
    more: "Más",
    to: "a",
    of: "de",
    next: "Siguiente",
    last: "Último",
    first: "Primero",
    previous: "Anterior",
    loadingOoo: "Cargando...",
    selectAll: "Seleccionar todo",
    searchOoo: "Buscar...",
    blanks: "Vacíos",
    filterOoo: "Filtrar...",
    applyFilter: "Aplicar filtro...",
    equals: "Igual",
    notEqual: "Distinto",
    lessThan: "Menor que",
    greaterThan: "Mayor que",
    contains: "Contiene",
    notContains: "No contiene",
    startsWith: "Empieza con",
    endsWith: "Termina con",
    andCondition: "Y",
    orCondition: "O",
    group: "Grupo",
    columns: "Columnas",
    filters: "Filtros",
    pivotMode: "Modo Pivote",
    groups: "Grupos",
    values: "Valores",
    noRowsToShow: "Sin filas para mostrar",
    pinColumn: "Fijar columna",
    autosizeThiscolumn: "Ajustar columna",
    copy: "Copiar",
    resetColumns: "Restablecer columnas",
    blank: "Vacíos",
    notBlank: "No Vacíos",
    paginationPageSize: "Registros por página",
};

let operadores = [];
let unidades = [];

const formFieldsMep = [
    {
        field: "txtOperador",
        id: "txtOperador",
        label: "Nombre operador",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtTelefono",
        id: "txtTelefono",
        label: "Teléfono",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtNumUnidad",
        id: "txtNumUnidad",
        label: "Núm Eco/ Núm Unidad / Identificador",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtPlacas",
        id: "txtPlacas",
        label: "Placas",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtSerie",
        id: "txtSerie",
        label: "Núm Serie / VIN",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "selectGPS",
        id: "selectGPS",
        label: "Compañia GPS",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtImei",
        id: "txtImei",
        label: "IMEI",
        required: true,
        type: "text",
        trigger: "none",
    },
];

const btnFull = document.querySelector("#btnFull");
const btnCancelarFull = document.querySelector("#btnCancelarFull");

document.addEventListener("DOMContentLoaded", function () {
    let gridApi;
    let currentTab = "planeadas";

    const tabs = document.querySelectorAll("#cotTabs .nav-link");

    tabs.forEach((tab) => {
        tab.addEventListener("click", function () {
            tabs.forEach((t) => t.classList.remove("active"));
            this.classList.add("active");
            currentTab = this.getAttribute("data-status");
            btnFull.disabled =
                currentTab == "en_espera" || currentTab == "aprobadas"
                    ? false
                    : true;

            getCotizacionesList();
        });
    });

    const columnDefs = [
        { headerCheckboxSelection: true, checkboxSelection: true, width: 30 },
        {
            headerName: "No",
            field: "id",
            sortable: true,
            filter: true,
            hide: true,
        },
        {
            headerName: "Tipo Viaje",
            field: "tipo",
            sortable: true,
            filter: true,
            hide: true,
        },
        {
            headerName: "Cliente",
            field: "cliente",
            sortable: true,
            filter: true,
            minWidth: 150,
        },
        {
            headerName: "# Contenedor",
            field: "contenedor",
            sortable: true,
            filter: true,
            minWidth: 150,
            autoHeight: true, // Permite que la fila se ajuste en altura
            cellStyle: (params) => {
                const styles = {
                    "white-space": "normal",
                    "line-height": "1.5",
                };

                // Si la cotización es tipo "Full", aplicar fondo
                if (params.data.tipo === "Full") {
                    styles["background-color"] = "#ffe5b4";
                }

                return styles;
            },
        },
        {
            headerName: "Origen",
            field: "origen",
            sortable: true,
            filter: true,
            minWidth: 150,
        },
        {
            headerName: "Destino",
            field: "destino",
            sortable: true,
            filter: true,
            minWidth: 150,
        },

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
                    <button class="btn btn-sm btn-outline-${color}"  title="Estatus">
                        <i class="fa fa-sync-alt me-1"></i> ${params.data.estatus}
                    </button>
                `;
            },
        },
        {
            headerName: "Coordenadas",
            field: "coordenadas",
            minWidth: 180,
            sortable: false,
            filter: false,
            cellRenderer: function (params) {
                return `
                    <button class="btn btn-sm btn-outline-info"
                    onclick="abrirModalCoordenadas(${params.data.id},${params.data.id_asignacion})"
                     title="Compartir coordenadas">
                     <i class="fa fa-map-marker-alt"></i> Compartir
                     </button>

                    `;
            },
        },
        {
            headerName: "Acciones",
            field: "acciones",
            minWidth: 500,
            cellRenderer: function (params) {
                let acciones = "";

                if (currentTab === "planeadas") {
                    acciones = `

                        <button class="btn btn-sm btn-outline-warning" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                        ${
                            params.data.tipo_asignacion === "Propio"
                                ? `
                            <button class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#cambioModal${params.data.id}" title="Asignación: Propio">
                                Propio
                            </button>
                        `
                                : `
                            <button class="btn btn-sm btn-outline-dark" data-bs-toggle="modal" data-bs-target="#cambioModal${params.data.id}" title="Asignación: Subcontratado">
                                Sub.
                            </button>
                        `
                        }`;
                } else if (currentTab === "finalizadas") {
                    acciones = `

                        <button class="btn btn-sm btn-outline-info" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>
                    `;
                } else if (currentTab === "en_espera") {
                    acciones = `

                        <button class="btn btn-sm btn-outline-info" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>

                    `;
                } else if (currentTab === "aprobadas") {
                    acciones = `

                        <button class="btn btn-sm btn-outline-info" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos">
                            <i class="fa fa-folder"></i>
                        </button>

                    `;
                } else if (currentTab === "canceladas") {
                    acciones = `<button class="btn btn-sm btn-outline-warning" onclick="abrirDocumentos(${params.data.id})" title="Ver Documentos"><i class="fa fa-folder"></i></button>`;
                }

                return acciones;
            },
        },
    ];

    const gridOptions = {
        columnDefs: columnDefs,
        domLayout: "autoHeight",
        pagination: true,
        paginationPageSize: 10,
        paginationPageSizeSelector: [10, 50, 100],
        rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1,
        },
        localeText: localeText,
        onRowSelected: (event) => {
            seleccionarContenedor();
        },
    };

    const myGridElement = document.querySelector("#myGrid");
    gridApi = agGrid.createGrid(myGridElement, gridOptions);

    getCotizacionesList();

    function getCotizacionesList() {
        const overlay = document.getElementById("gridLoadingOverlay");
        overlay.style.display = "flex";

        let url = "/mep/viajes/list";
        if (currentTab === "finalizadas") url = "/mep/viajes/finalizadas";
        if (currentTab === "en_espera") url = "/mep/viajes/espera";
        if (currentTab === "aprobadas") url = "/mep/viajes/aprobadas";
        if (currentTab === "canceladas") url = "/mep/viajes/canceladas";

        gridApi.setGridOption("rowData", []);

        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                gridApi.setGridOption("rowData", data.list);
            })
            .catch((error) => {
                console.error(
                    "❌ Error al obtener la lista de cotizaciones:",
                    error,
                );
            })
            .finally(() => {
                overlay.style.display = "none";
            });
    }

    btnFull.addEventListener("click", () => {
        let seleccion = gridApi.getSelectedRows();
        let validarCliente = seleccion.every(
            (element) => element.cliente === seleccion[0].cliente,
        );

        if (seleccion.length > 2) {
            Swal.fire(
                "Maximo 2 contenedores",
                "Lo sentimos, solo puede seleccionar maximo 2 contenedores, estos deben ser de un mismo cliente",
                "warning",
            );
            return false;
        }

        if (!validarCliente) {
            Swal.fire(
                "Cliente distinto",
                "Lo sentimos, los contenedores deben ser de un mismo cliente",
                "warning",
            );
            return false;
        }

        Swal.fire({
            title: "¿Estás seguro?",
            text: "Quiere unir los contenedores seleccionados en un viaje Full.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, continuar",
            cancelButtonText: "No, cancelar",
            reverseButtons: true, // Opcional: invierte el orden de los botones
        }).then((result) => {
            if (result.isConfirmed) {
                let _token = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");
                $.ajax({
                    url: "/cotizaciones/transformar/full",
                    type: "post",
                    data: { _token, seleccion },
                    beforeSend: () => {
                        mostrarLoading(
                            "Fusionando contenedores... espere un momento",
                        );
                    },
                    success: (response) => {
                        Swal.fire(
                            response.Titulo,
                            response.Mensaje,
                            response.TMensaje,
                        );
                        if (response.TMensaje == "success") {
                            getCotizacionesList();
                        }
                        ocultarLoading();
                    },
                    error: () => {
                        ocultarLoading();
                    },
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                // Acción si el usuario canceló
                console.log("El usuario canceló");
            }
        });
    });

    function cancelarFull() {
        let seleccion = gridApi.getSelectedRows();

        if (seleccion.length != 1) {
            Swal.fire(
                "Seleccione un contenedor",
                "Debe seleccionar un contenedor que sea Full",
                "warning",
            );
            return false;
        }
        if (!seleccion[0].tipo || seleccion[0].tipo != "Full") {
            Swal.fire(
                "Contenedor no es Full",
                "El contenedor seleccionado no es un viaje Full",
                "warning",
            );
            return false;
        }

        Swal.fire({
            title: "¿Estás seguro?",
            text: "Quiere separar los contenedores del viaje Full.",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sí, continuar",
            cancelButtonText: "No, cancelar",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                let _token = document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content");
                $.ajax({
                    url: "/cotizaciones/transformar/cancelar-full",
                    type: "post",
                    data: { _token, seleccion },
                    beforeSend: () => {},
                    success: (response) => {
                        Swal.fire(
                            response.Titulo,
                            response.Mensaje,
                            response.TMensaje,
                        );
                        if (response.TMensaje == "success") {
                            getCotizacionesList();
                        }
                    },
                    error: () => {},
                });
            } else if (result.dismiss === Swal.DismissReason.cancel) {
                console.log("El usuario canceló");
            }
        });
    }
    if (btnCancelarFull) {
        btnCancelarFull.addEventListener("click", cancelarFull);
    }

    const botonAbrirModal = document.getElementById("abrirModalBtn");

    botonAbrirModal.addEventListener("click", () => {
        // llenarModalViaje();
        let seleccion = gridApi.getSelectedRows();

        if (seleccion.length == 1) {
            document.getElementById("numeroContenedor").textContent =
                seleccion[0].contenedor;
            //document.getElementById('fechaViaje').textContent = seleccion[0].;
            document.getElementById("origenViaje").textContent =
                seleccion[0].origen;
            document.getElementById("destinoViaje").textContent =
                seleccion[0].destino;
            document.getElementById("estatusViaje").textContent =
                seleccion[0].estatus;
        }

        let modalElement =
            seleccion.length != 1 ? "noSeleccionModal" : "viajeModal";
        const modal1 = new bootstrap.Modal(
            document.getElementById(modalElement),
        );
        modal1.show();
    });

    const btnAsignaOperador = document.querySelector("#btnAsignaOperador");

    function asignarOperador2() {
        let seleccion = gridApi.getSelectedRows();
        const formData = {};

        //formFieldsMep
        let passValidation = formFieldsMep.every((item) => {
            let field = document.getElementById(item.field);
            if (field) {
                if (item.required === true && field.value.length == 0) {
                    Swal.fire(
                        "El campo " + item.label + " es obligatorio",
                        "Parece que no ha proporcionado información en el campo " +
                            item.label,
                        "warning",
                    );
                    return false;
                }
            }

            if (field.dataset.mepUnidad) {
                formData["mepUnidad"] = field.dataset.mepUnidad;
            }

            if (field.dataset.mepOperador) {
                formData["mepOperador"] = field.dataset.mepOperador;
            }

            formData[item.field] = field.value;
            return true;
        });

        if (!passValidation) return passValidation;

        let data = { contenenedor: seleccion[0], formData: formData };
        fetch("/mep/viajes/operador/asignar", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    ?.getAttribute("content"),
            },
            body: JSON.stringify(data),
        })
            .then((response) => {
                if (!response.ok) {
                    throw new Error("Error en la respuesta del servidor");
                }
                return response.json();
            })
            .then((data) => {
                console.log("Respuesta del backend:", data);

                Swal.fire(data.Titulo, data.Mensaje, data.TMensaje);
            })
            .catch((error) => {
                console.error("Error al enviar los datos:", error);
                alert("Ocurrió un error al asignar el operador.");
            });
    }
    if (btnAsignaOperador) {
        btnAsignaOperador.addEventListener("click", asignarOperador2);
    }

    function seleccionarContenedor() {
        let seleccion = gridApi.getSelectedRows();
        if (seleccion.length === 0) {
            btnCancelarFull.disabled = true;
            return;
        }

        // Obtener el tipo de la primera fila
        const tipoBase = seleccion[0].tipo;

        // Validar que TODAS tengan el mismo tipo
        const mismoTipo = seleccion.every((row) => row.tipo === tipoBase);

        if (mismoTipo && tipoBase === "Full") {
            btnCancelarFull.disabled = false;
        } else {
            btnCancelarFull.disabled = true;
        }

        if (currentTab != "en_espera" && currentTab != "aprobadas")
            return false;

        if (seleccion.length > 2) {
            Swal.fire(
                "Maximo 2 contenedores",
                "Lo sentimos, solo puede seleccionar maximo 2 contenedores, estos deben ser de un mismo cliente",
                "warning",
            );
            return false;
        }

        let validarCliente = seleccion.every(
            (element) => element.cliente === seleccion[0].cliente,
        );

        if (!validarCliente) {
            Swal.fire(
                "Cliente distinto",
                "Lo sentimos, los contenedores deben ser de un mismo cliente",
                "warning",
            );
            return false;
        }

        localStorage.setItem("numContenedor", seleccion[0].contenedor);
    }
});

function buscarOperador(nombre) {
    let operador = operadores.find((op) => {
        return op.nombre === nombre ? op : false;
    });

    let txtTelefono = document.querySelector("#txtTelefono");

    toastr.options.positionClass = "toast-middle-center";
    let txtOperador = document.querySelector("#txtOperador");

    if (operador) {
        txtTelefono.value = operador.telefono;
        txtOperador.dataset.mepOperador = operador.id;
        toastr.success("Operador identificado");
    } else {
        txtTelefono.value = "";
        txtOperador.dataset.mepOperador = 0;
        toastr.warning("Operador no encontrado");
    }
}

function buscarUnidad(numUnidad) {
    let unidad = unidades.find((u) => {
        return u.id_equipo === numUnidad.toUpperCase() ? u : false;
    });

    let txtPlacas = document.querySelector("#txtPlacas");
    let txtSerie = document.querySelector("#txtSerie");
    let txtImei = document.querySelector("#txtImei");
    let selectGPS = document.querySelector("#selectGPS");

    let txtNumUnidad = document.querySelector("#txtNumUnidad");

    toastr.options.positionClass = "toast-middle-center";
    if (unidad) {
        txtPlacas.value = unidad.placas;
        txtSerie.value = unidad.num_serie;
        txtImei.value = unidad.imei;

        txtNumUnidad.dataset.mepUnidad = unidad.id;
        for (let i = 0; i < selectGPS.options.length; i++) {
            if (selectGPS.options[i].value === String(unidad.gps_company_id)) {
                selectGPS.selectedIndex = i;
                break;
            }
        }
        toastr.success("Unidad identificado");
    } else {
        txtPlacas.value = "";
        txtSerie.value = "";
        txtImei.value = "";
        txtNumUnidad.dataset.mepUnidad = 0;
        toastr.warning("No se encontró unidad");
    }
}

function getCatalogoOperadorUnidad() {
    let _token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
    $.ajax({
        url: "/mep/catalogos/operador-unidad",
        type: "post",
        data: { _token },
        beforeSend: () => {},
        success: (response) => {
            operadores = response.operadores;
            unidades = response.unidades;
            populateUnidadesSelects();
        },
        error: () => {
            console.error(
                "No pudimos obtener los datos de operadores y unidades de la empresa.",
            );
        },
    });
}

function abrirDocumentos(idCotizacion) {
    $(`#estatusDoc${idCotizacion}`).modal("show");
}

function descargarPDF(idCotizacion) {
    const fecha = new Date().toISOString().slice(0, 10); // formato: YYYY-MM-DD
    const link = document.createElement("a");
    link.href = `/cotizaciones/pdf/${idCotizacion}`;
    link.download = `cotizacion_${idCotizacion}_${fecha}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function abrirDocumentos(idCotizacion) {
    fetch(`/cotizaciones/documentos/${idCotizacion}`)
        .then((response) => response.json())
        .then((data) => {
            const modal = new bootstrap.Modal(
                document.getElementById("modalEstatusDocumentos"),
            );
            const titulo = document.getElementById("tituloContenedor");
            const cuerpo = document.getElementById("estatusDocumentosBody");

            titulo.innerText = `#${data.num_contenedor ?? "N/A"}`;
            cuerpo.innerHTML = "";

            const campos = [
                { label: "Num contenedor", valor: data.num_contenedor },
                { label: "Documento CCP", valor: data.doc_ccp },
                {
                    label: "Boleta de Liberación",
                    valor: data.boleta_liberacion,
                },
                { label: "Doda", valor: data.doda },
                { label: "Carta Porte", valor: data.carta_porte },
                { label: "Boleta Vacio", valor: data.boleta_vacio === "si" },
                { label: "EIR", valor: data.doc_eir },
                // { label: 'Foto Patio', valor: data.foto_patio },

                { label: "Evidencia Descarga", valor: data.evidencia_descarga },
            ];

            campos.forEach((item) => {
                const col = document.createElement("div");
                col.className = "col-6";
                col.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid ${item.valor ? "fa-check-circle text-success" : "fa-times-circle text-muted"}"></i>
                        <span class="fw-semibold">${item.label}</span>
                    </div>
                `;
                cuerpo.appendChild(col);
            });

            modal.show();
        })
        .catch((error) => {
            console.error("Error al obtener documentos:", error);
            Swal.fire(
                "Error",
                "No se pudieron obtener los documentos",
                "error",
            );
        });
}

function cambiarTab(tabId) {
    // Ocultamos todos los divs con clase 'tab-content'
    const tabs = document.querySelectorAll(".tab-content");
    tabs.forEach((tab) => {
        tab.style.display = "none";
    });

    // Mostramos solo el que corresponde
    const tabToShow = document.getElementById("tab-" + tabId);
    if (tabToShow) {
        tabToShow.style.display = "block";
    } else {
        console.error(`No se encontró el tab: tab-${tabId}`);
    }
}

function populateUnidadesSelects() {
    const selectUnidad = document.getElementById("txtNumUnidad");
    const selectChasisA = document.getElementById("txtNumChasisA");
    const selectChasisB = document.getElementById("txtNumChasisB");

    if (selectUnidad) {
        const valActual = selectUnidad.value;
        const mepUnidadActual = selectUnidad.dataset.mepUnidad;
        selectUnidad.innerHTML = '<option value="" disabled selected>Selecciona Unidad...</option>';
        unidades.filter(u => u.tipo === "Tractos / Camiones").forEach(u => {
            const opt = document.createElement("option");
            opt.value = u.id_equipo;
            opt.textContent = `${u.id_equipo} ${u.placas ? '('+u.placas+')' : ''}`;
            opt.dataset.unitId = u.id;
            selectUnidad.appendChild(opt);
        });
        if (valActual) {
            selectUnidad.value = valActual;
        }
        if (mepUnidadActual) {
            selectUnidad.dataset.mepUnidad = mepUnidadActual;
        }
    }

    if (selectChasisA) {
        const valActual = selectChasisA.value;
        const mepUnidadActual = selectChasisA.dataset.mepUnidad;
        selectChasisA.innerHTML = '<option value="" disabled selected>Selecciona Chasis A...</option>';
        unidades.filter(u => u.tipo === "Chasis / Plataforma").forEach(u => {
            const opt = document.createElement("option");
            opt.value = u.id_equipo;
            opt.textContent = `${u.id_equipo} ${u.placas ? '('+u.placas+')' : ''}`;
            opt.dataset.unitId = u.id;
            selectChasisA.appendChild(opt);
        });
        if (valActual) {
            selectChasisA.value = valActual;
        }
        if (mepUnidadActual) {
            selectChasisA.dataset.mepUnidad = mepUnidadActual;
        }
    }

    if (selectChasisB) {
        const valActual = selectChasisB.value;
        const mepUnidadActual = selectChasisB.dataset.mepUnidad;
        selectChasisB.innerHTML = '<option value="" disabled selected>Selecciona Chasis B...</option>';
        unidades.filter(u => u.tipo === "Chasis / Plataforma").forEach(u => {
            const opt = document.createElement("option");
            opt.value = u.id_equipo;
            opt.textContent = `${u.id_equipo} ${u.placas ? '('+u.placas+')' : ''}`;
            opt.dataset.unitId = u.id;
            selectChasisB.appendChild(opt);
        });
        if (valActual) {
            selectChasisB.value = valActual;
        }
        if (mepUnidadActual) {
            selectChasisB.dataset.mepUnidad = mepUnidadActual;
        }
     }

    // Inicializar o actualizar Select2
    if (typeof jQuery !== 'undefined' && typeof jQuery.fn.select2 !== 'undefined') {
        const select2Options = {
            width: '100%'
        };
        if (selectUnidad) jQuery(selectUnidad).select2(select2Options);
        if (selectChasisA) jQuery(selectChasisA).select2(select2Options);
        if (selectChasisB) jQuery(selectChasisB).select2(select2Options);
    }
}

const mapInputs = {
    Unidad: {
        input: "txtNumUnidad",
        box: "sugerenciasUnidad",
        tipoFiltro: "Tractos / Camiones",
        placas: "txtPlacas",
        serie: "txtSerie",
        imei: "txtImei",
        gps: "selectGPS",
        statusGps: "gpsStatusUnidad",
        latitud: null,
        longitud: null,
    },
    ChasisA: {
        input: "txtNumChasisA",
        box: "sugerenciasChasisA",
        tipoFiltro: "Chasis / Plataforma",
        placas: "txtPlacasA",
        serie: null,
        imei: "txtImeiChasisA",
        gps: "selectChasisAGPS",
        statusGps: "gpsStatusChasisA",
        latitud: null,
        longitud: null,
    },
    ChasisB: {
        input: "txtNumChasisB",
        box: "sugerenciasChasisB",
        tipoFiltro: "Chasis / Plataforma",
        placas: "txtPlacasB",
        serie: null,
        imei: "txtImeiChasisB",
        gps: "selectChasisBGPS",
        statusGps: "gpsStatusChasisB",
        latitud: null,
        longitud: null,
    },
};

function coordenadasValidas(lat, lng) {
    lat = parseFloat(lat);
    lng = parseFloat(lng);
    return !isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0;
}

function esEquipoValido(config) {
    const input = document.getElementById(config.input);
    const imei = document.getElementById(config.imei);
    const gps = document.getElementById(config.gps);

    const lat = config.latitud;
    const lng = config.longitud;

    const tieneInput = input && input.value.trim() !== "";
    const tieneImei = imei && imei.value.trim() !== "";
    const tieneGps = gps && gps.value !== "";
    if (tieneInput && tieneImei && tieneGps) {
        const coordsOk = coordenadasValidas(lat, lng);
        return coordsOk;
    }
    return true;
}

function actualizarEstadoGPS(id, tipo, texto) {
    const clases = {
        success: "text-success",
        danger: "text-danger",
        warning: "text-warning",
        muted: "text-muted",
        secondary: "text-secondary"
    };

    const iconos = {
        success: "fa-circle",
        danger: "fa-triangle-exclamation",
        warning: "fa-spinner fa-spin",
        muted: "fa-minus-circle",
        secondary: "fa-minus-circle"
    };

    const element = $("#" + id);
    if (element.length) {
        element
            .removeClass()
            .addClass(`small fw-bold ${clases[tipo] || "text-secondary"}`).html(`
                <i class="fas ${iconos[tipo] || "fa-minus-circle"}"></i>
                ${texto}
            `);
    }
}

async function validarConexionGPS(tipoKey, imei, gpsCompanyId, equipos = []) {
    const config = mapInputs[tipoKey];
    if (!config) return false;
    const btnActualizar = document.getElementById(`btnActualizarGPS${tipoKey}`);

    if (!imei || !gpsCompanyId) {
        if (btnActualizar) btnActualizar.style.display = "none";
        actualizarEstadoGPS(config.statusGps, "secondary", "Sin GPS");
        config.latitud = null;
        config.longitud = null;
        return false;
    }
    if (btnActualizar) btnActualizar.style.display = "inline-block";
    actualizarEstadoGPS(config.statusGps, "warning", "Conectando GPS...");

    try {
        const response = await fetch("/mep/viajes/ubicaciones", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                equipos: equipos,
            }),
        });

        const data = await response.json();
        const ubi = data[0]?.ubicacion ?? null;

        if (ubi && ubi.lat && ubi.lng) {
            actualizarEstadoGPS(config.statusGps, "success", "Equipo en línea");
            config.latitud = parseFloat(ubi.lat);
            config.longitud = parseFloat(ubi.lng);
            return true;
        } else {
            actualizarEstadoGPS(config.statusGps, "danger", "GPS sin señal");
            config.latitud = null;
            config.longitud = null;
            return false;
        }
    } catch (e) {
        actualizarEstadoGPS(config.statusGps, "danger", "Error conexión GPS");
        config.latitud = null;
        config.longitud = null;
        console.error(e);
        return false;
    }
}

function initSelectsListeners() {
    Object.keys(mapInputs).forEach(tipoKey => {
        const config = mapInputs[tipoKey];
        const select = document.getElementById(config.input);
        if (!select) return;

        jQuery(select).on("change", function () {
            const val = this.value;
            const selectedOpt = this.options[this.selectedIndex];
            const unitId = selectedOpt ? selectedOpt.dataset.unitId : null;
            const btnActualizar = document.getElementById(`btnActualizarGPS${tipoKey}`);

            config.latitud = null;
            config.longitud = null;
            actualizarEstadoGPS(config.statusGps, "secondary", "Sin validar");

            if (config.placas) document.getElementById(config.placas).value = "";
            if (config.serie) document.getElementById(config.serie).value = "";
            if (config.imei) document.getElementById(config.imei).value = "";
            if (config.gps) document.getElementById(config.gps).selectedIndex = 0;

            if (!val || !unitId) {
                this.dataset.mepUnidad = 0;
                if (btnActualizar) btnActualizar.style.display = "none";
                return;
            }

            const u = unidades.find(unit => String(unit.id) === String(unitId));
            if (u) {
                this.dataset.mepUnidad = u.id;

                if (config.placas)
                    document.getElementById(config.placas).value = u.placas ?? "";

                if (config.serie)
                    document.getElementById(config.serie).value = u.num_serie ?? "";

                if (config.imei)
                    document.getElementById(config.imei).value = u.imei ?? "";

                if (config.gps) {
                    let selectGps = document.getElementById(config.gps);
                    for (let i = 0; i < selectGps.options.length; i++) {
                        if (
                            String(selectGps.options[i].value) ===
                            String(u.gps_company_id)
                        ) {
                            selectGps.selectedIndex = i;
                            break;
                        }
                    }
                }
                
                if (u.imei && u.gps_company_id) {
                    if (btnActualizar) btnActualizar.style.display = "inline-block";
                    actualizarEstadoGPS(config.statusGps, "warning", "GPS listo para consultar");
                    validarConexionGPS(tipoKey, u.imei, u.gps_company_id, [u.id]);
                } else {
                    if (btnActualizar) btnActualizar.style.display = "none";
                    actualizarEstadoGPS(config.statusGps, "danger", "Equipo sin IMEI configurado");
                }
                
                toastr.success("Equipo seleccionado");
            } else {
                this.dataset.mepUnidad = 0;
                if (btnActualizar) btnActualizar.style.display = "none";
            }
        });
    });
}

jQuery(document).on("click", ".btn-actualizar-gps", async function() {
    const tipoKey = this.dataset.gpsTipo;
    const config = mapInputs[tipoKey];
    if (!config) return;

    const select = document.getElementById(config.input);
    const unitId = select ? select.options[select.selectedIndex]?.dataset.unitId : null;
    const imei = document.getElementById(config.imei)?.value;
    const gpsCompanyId = document.getElementById(config.gps)?.value;

    if (imei && gpsCompanyId) {
        const btn = jQuery(this);
        const originalHtml = btn.html();
        btn.prop("disabled", true).html('<i class="fas fa-spinner fa-spin"></i> Consultando...');
        try {
            await validarConexionGPS(tipoKey, imei, gpsCompanyId, unitId ? [unitId] : []);
        } catch (e) {
            console.error(e);
        } finally {
            btn.prop("disabled", false).html(originalHtml);
        }
    }
});

initSelectsListeners();
