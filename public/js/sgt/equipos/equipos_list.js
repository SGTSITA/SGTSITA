// Archivo: public/js/sgt/equipos/equipos_list.js

let equiposData = [];

async function fetchEquiposData() {
    const response = await fetch("/equipos/data");
    equiposData = await response.json();
}

function checkboxRenderer(params) {
    const valor = params.value;
    return `
        <div class="text-center">
            <i class="fas ${valor
            ? "fa-circle-check text-success"
            : "fa-circle-xmark text-secondary"
        } fa-lg"></i>
        </div>
    `;
}

function accionesRenderer(params) {
    const id = params.value;
    const token = document.querySelector('meta[name="csrf-token"]').content;

    return `
    <div class="d-flex gap-2 align-items-center justify-content-center">
        <button type="button" class="btn btn-sm btn-outline-secondary btn-editar-equipo"
            data-id="${id}" data-bs-toggle="modal" data-bs-target="#equipoEditModal-${id}" title="Editar">
            <i class="fas fa-edit"></i>
        </button>

        <button type="button" class="btn btn-sm btn-outline-secondary"
            data-bs-toggle="modal" data-bs-target="#documenotsdigitales-${id}" title="Ver documentos">
            <i class="fas fa-folder-open"></i>
        </button>

        <button type="button" class="btn btn-sm btn-outline-secondary"
            data-id="${id}" data-bs-toggle="modal" data-bs-target="#asignarGpsModal-${id}" title="Asignar GPS">
            <i class="fas fa-satellite-dish"></i>
        </button>

        ${params.data.activo
            ? `<form method="POST" action="/equipos/desactivar/${id}" 
        class="form-desactivar-equipo" data-id="${id}" data-tipo="desactivado"
        style="margin: 0; padding: 0; display: inline-block;">
        <input type="hidden" name="_token" value="${token}">
        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="tipo" value="desactivado">
        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Desactivar">
            <i class="fas fa-trash"></i>
        </button>
    </form>`
            : `<form method="POST" action="/equipos/desactivar/${id}" 
        class="form-desactivar-equipo" data-id="${id}" data-tipo="activado"
        style="margin: 0; padding: 0; display: inline-block;">
        <input type="hidden" name="_token" value="${token}">
        <input type="hidden" name="_method" value="PATCH">
        <input type="hidden" name="tipo" value="activado">
        <button type="submit" class="btn btn-sm btn-outline-success" title="Reactivar">
            <i class="fas fa-arrow-rotate-right"></i>
        </button>
    </form>`
        }

    </div>`;
}

function crearGrid(id, tipo) {
    const rowData = equiposData.filter((item) => item.tipo === tipo);

    let columnDefs = [
        { headerName: "Equipo", field: "id_equipo" },
        { headerName: "Año", field: "year" },
        { headerName: "Marca", field: "marca" },
        { headerName: "Modelo", field: "modelo" },
        { headerName: "Placas", field: "placas" },
        { headerName: "Número de Serie", field: "num_serie" },
        { headerName: "Acceso", field: "acceso" },
        {
            headerName: "Tarjeta",
            field: "tarjeta_circulacion",
            cellRenderer: checkboxRenderer,
            width: 110,
            cellClass: "text-center",
        },
        {
            headerName: "Póliza",
            field: "poliza_seguro",
            cellRenderer: checkboxRenderer,
            width: 110,
            cellClass: "text-center",
        },
        {
            headerName: "Estatus",
            field: "activo",
            width: 120,
            cellRenderer: (params) => {
                return `
            <div class="text-center">
                <span class="badge ${params.value ? "bg-success" : "bg-danger"
                    }">
                    ${params.value ? "Activo" : "Inactivo"}
                </span>
            </div>
        `;
            },
            cellClass: "text-center",
        },
    ];

    if (tipo === "Tractos / Camiones" || tipo === "Chasis / Plataforma") {
        columnDefs.splice(4, 0, { headerName: "Motor", field: "motor" });
    }

    if (tipo === "Chasis / Plataforma") {
        columnDefs.splice(8, 0, { headerName: "Tipo", field: "folio" });
    }

    columnDefs.push({
        headerName: "Acciones",
        field: "id",
        cellRenderer: accionesRenderer,
        width: 350,
        cellClass: "text-center",
        suppressSizeToFit: true,
        sortable: false,
        filter: false,
        cellStyle: {
            display: "flex",
            alignItems: "center",
            justifyContent: "center",
            height: "100%",
        },
    });

    const gridDiv = document.querySelector(`#${id} `);
    gridDiv.innerHTML = "";
    agGrid.createGrid(gridDiv, {
        columnDefs,
        rowData,
        pagination: true,
        rowHeight: 56,
        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true,
            floatingFilter: true,
        },
        domLayout: "normal",
        suppressCellSelection: true,
        components: { checkboxRenderer },
    });
}

async function recargarEquipos(tipo) {
    await fetchEquiposData();
    if (tipo === "Tractos / Camiones") {
        crearGrid("gridCamiones", "Tractos / Camiones");
    } else if (tipo === "Chasis / Plataforma") {
        crearGrid("gridChasis", "Chasis / Plataforma");
    } else if (tipo === "Dollys") {
        crearGrid("gridDolys", "Dollys");
    }
}

document.addEventListener("DOMContentLoaded", async () => {
    await fetchEquiposData();
    crearGrid("gridDolys", "Dollys");
    crearGrid("gridChasis", "Chasis / Plataforma");
    crearGrid("gridCamiones", "Tractos / Camiones");

    document.querySelectorAll(".form-editar-equipo").forEach((form) => {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();

            const id = this.getAttribute("data-id");
            const tipo = this.getAttribute("data-tipo");
            const formData = new FormData(this);

            try {
                const response = await fetch(this.action, {
                    method: "POST",
                    headers: { "X-Requested-With": "XMLHttpRequest" },
                    body: formData,
                });

                const result = await response.json();

                if (result.success) {
                    const modal = document.getElementById(
                        `equipoEditModal-${id}`
                    );
                    const modalInstance = bootstrap.Modal.getInstance(modal);
                    if (modalInstance) modalInstance.hide();

                    await Swal.fire({
                        icon: "success",
                        title: "¡Actualizado!",
                        text: result.message,
                        confirmButtonText: "Aceptar",
                    });

                    await recargarEquipos(tipo);
                } else {
                    Swal.fire({
                        icon: "error",
                        title: "Error",
                        text:
                            result.message ||
                            "No se pudo actualizar el equipo.",
                    });
                }
            } catch (error) {
                console.error(error);
                Swal.fire({
                    icon: "error",
                    title: "Error de conexión",
                    text: "Ocurrió un error al actualizar el equipo.",
                });
            }
        });
    });
});

document.addEventListener("click", function (e) {
    const btn = e.target.closest(".btn-editar-equipo");
    if (!btn) return;

    const id = btn.getAttribute("data-id");
    const equipo = equiposData.find((item) => item.id == id);
    if (!equipo) return;

    const modal = document.querySelector(`#equipoEditModal-${id}`);
    if (!modal) return;

    modal.querySelector('[name="id_equipo"]').value = equipo.id_equipo || "";
    modal.querySelector('[name="fecha"]').value = equipo.fecha || "";
    modal.querySelector('[name="year"]').value = equipo.year || "";
    modal.querySelector('[name="marca"]').value = equipo.marca || "";
    modal.querySelector('[name="modelo"]').value = equipo.modelo || "";
    modal.querySelector('[name="placas"]').value = equipo.placas || "";
    modal.querySelector('[name="num_serie"]').value = equipo.num_serie || "";
    modal.querySelector('[name="acceso"]').value = equipo.acceso || "";

    const selectFolio = modal.querySelector('[name="folio"]');
    if (selectFolio && equipo.folio) {
        selectFolio.value = equipo.folio;
    }
});

document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".form-documentos-equipo").forEach((form) => {
        form.addEventListener("submit", async function (e) {
            e.preventDefault();

            const formData = new FormData(this);

            try {
                const response = await fetch(this.action, {
                    method: "POST",
                    headers: {
                        "X-Requested-With": "XMLHttpRequest",
                    },
                    body: formData,
                });

                const result = await response.json();

                if (result.success) {
                    // Cierra el modal automáticamente
                    const modal = bootstrap.Modal.getInstance(
                        this.closest(".modal")
                    );
                    if (modal) modal.hide();

                    // Muestra SweetAlert
                    await Swal.fire({
                        icon: "success",
                        title: "¡Éxito!",
                        text: result.message,
                        confirmButtonText: "Aceptar",
                    });

                    // Aquí puedes refrescar la tabla o los datos si lo necesitas
                    // gridApi.refreshInfiniteCache(); o location.reload();
                } else {
                    Swal.fire(
                        "Error",
                        result.message || "Ocurrió un error al guardar.",
                        "error"
                    );
                }
            } catch (error) {
                console.error(error);
                Swal.fire("Error", "Error de red o del servidor.", "error");
            }
        });
    });
});

document.addEventListener("submit", async function (e) {
    const form = e.target;
    if (!form.classList.contains("form-desactivar-equipo")) return;

    e.preventDefault();

    const id = form.dataset.id;
    const tipoAccion = form.querySelector('input[name="tipo"]').value;
    const formData = new FormData(form);

    const textoAccion = tipoAccion === 'desactivado'
        ? "Esta acción desactivará el equipo."
        : "Esta acción reactivará el equipo.";
    const textoConfirmar = tipoAccion === 'desactivado'
        ? "Sí, desactivar"
        : "Sí, reactivar";

    const confirmed = await Swal.fire({
        title: "¿Estás seguro?",
        text: textoAccion,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: textoConfirmar,
        cancelButtonText: "Cancelar",
    });

    if (confirmed.isConfirmed) {
        try {
            const response = await fetch(form.action, {
                method: "POST",
                headers: {
                    "X-Requested-With": "XMLHttpRequest",
                },
                body: formData,
            });

            const result = await response.json();

            if (result.success) {
                await Swal.fire({
                    icon: "success",
                    title: tipoAccion === 'desactivado' ? "¡Desactivado!" : "¡Reactivado!",
                    text: result.message,
                });

                if (tipoAccion === 'activado') {
                    // Recargar toda la página si se reactivó
                    location.reload();
                } else {
                    // Solo recargar la tabla del tipo actual si fue desactivado
                    const equipo = equiposData.find(e => e.id == id);
                    const tipo = equipo?.tipo;
                    if (tipo) {
                        await fetchEquiposData();
                        if (tipo === "Dollys") crearGrid("gridDolys", "Dollys");
                        else if (tipo === "Chasis / Plataforma") crearGrid("gridChasis", "Chasis / Plataforma");
                        else if (tipo === "Tractos / Camiones") crearGrid("gridCamiones", "Tractos / Camiones");
                    }
                }

            } else {
                Swal.fire(
                    "Error",
                    result.message || "No se pudo actualizar el equipo.",
                    "error"
                );
            }
        } catch (error) {
            console.error(error);
            Swal.fire("Error", "Error en la solicitud.", "error");
        }
    }
});

document.addEventListener("DOMContentLoaded", () => {
    const btnCrearEquipo = document.querySelector(
        '[data-bs-target="#equipoModal"]'
    );

    if (btnCrearEquipo) {
        btnCrearEquipo.addEventListener("click", () => {
            const activeTab = document.querySelector(
                "#equiposTabs .nav-link.active"
            );
            let tipo = "Tractos / Camiones";

            if (activeTab?.id === "tab-chasis") tipo = "Chasis / Plataforma";
            else if (activeTab?.id === "tab-dollys") tipo = "Dolys";

            document.getElementById("tipoActivo").value = tipo;

            // Activar pestaña interna correctamente
            let tabId = "#pills-home-tab";
            if (tipo === "Chasis / Plataforma") tabId = "#pills-profile-tab";
            if (tipo === "Dolys") tabId = "#pills-dolys-tab";

            // Quitar la clase active de todas las pestañas antes de activar la correcta
            document
                .querySelectorAll("#pills-tab .nav-link")
                .forEach((el) => el.classList.remove("active"));
            document
                .querySelectorAll("#pills-tabContent .tab-pane")
                .forEach((el) => {
                    el.classList.remove("show", "active");
                });

            // Activar pestaña seleccionada
            const targetTab = document.querySelector(tabId);
            const tab = new bootstrap.Tab(targetTab);
            tab.show();
        });
    }
});

document.addEventListener("submit", async function (e) {
    const form = e.target;
    if (!form.classList.contains("form-asignar-gps")) return;

    e.preventDefault(); // ⛔ evita redirección

    const formData = new FormData(form);
    const action = form.action;

    try {
        const response = await fetch(action, {
            method: "POST",
            headers: {
                "X-Requested-With": "XMLHttpRequest", // 👈 muy importante
            },
            body: formData,
        });

        const result = await response.json();

        if (result.success) {
            // ✅ Cierra el modal
            const modal = bootstrap.Modal.getInstance(form.closest(".modal"));
            if (modal) modal.hide();

            // ✅ Muestra mensaje
            await Swal.fire({
                icon: "success",
                title: "¡Asignado!",
                text: result.message,
                timer: 2000,
                showConfirmButton: false,
            });

            // 🔁 Recarga tablas
            await fetchEquiposData();
            crearGrid("gridDolys", "Dollys");
            crearGrid("gridChasis", "Chasis / Plataforma");
            crearGrid("gridCamiones", "Tractos / Camiones");

        } else {
            Swal.fire("Error", result.message || "No se pudo asignar GPS.", "error");
        }
    } catch (error) {
        console.error(error);
        Swal.fire("Error", "Error en la solicitud.", "error");
    }
});

