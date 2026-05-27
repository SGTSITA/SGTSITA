document.addEventListener("DOMContentLoaded", function () {
    iniciarGridTiposNotificacion();
    iniciarGridReglasNotificacion();
    iniciarGridUsuariosReglas();
});

/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function tienePermiso(permiso) {
    return permisosNotificaciones && permisosNotificaciones[permiso] === true;
}

function badgeActivo(activo) {
    if (activo === true || activo === 1 || activo === "1") {
        return `<span class="badge bg-gradient-success">Activo</span>`;
    }

    return `<span class="badge bg-gradient-secondary">Inactivo</span>`;
}

function badgeSiNo(valor) {
    if (valor === true || valor === 1 || valor === "1") {
        return `<span class="badge bg-gradient-success">Sí</span>`;
    }

    return `<span class="badge bg-gradient-secondary">No</span>`;
}

function textoSeguro(valor, fallback = "S/N") {
    if (valor === null || valor === undefined || valor === "") {
        return fallback;
    }

    return valor;
}

function getCsrfToken() {
    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? meta.getAttribute("content") : "";
}

function formatearFecha(fecha) {
    if (!fecha) {
        return "S/N";
    }

    const date = new Date(fecha);

    if (isNaN(date.getTime())) {
        return fecha;
    }

    return date.toLocaleDateString("es-MX", {
        year: "numeric",
        month: "2-digit",
        day: "2-digit",
    });
}

function crearBotonAccion({ clase, icono, titulo, onclick }) {
    return `
        <button type="button"
            class="${clase}"
            title="${titulo}"
            onclick="${onclick}">
            <i class="${icono}"></i>
        </button>
    `;
}

/*
|--------------------------------------------------------------------------
| Grid: Tipos de notificación
|--------------------------------------------------------------------------
*/

function iniciarGridTiposNotificacion() {
    const gridDiv = document.querySelector("#tiposNotificacionGrid");

    if (!gridDiv) {
        return;
    }

    const columnDefs = [
        {
            headerName: "#",
            field: "id",
            width: 80,
            pinned: "left",
        },
        {
            headerName: "Clave",
            field: "clave",
            minWidth: 180,
            filter: true,
            cellRenderer: (params) => {
                return `<span class="badge bg-gradient-dark">${textoSeguro(params.value)}</span>`;
            },
        },
        {
            headerName: "Nombre",
            field: "nombre",
            minWidth: 220,
            filter: true,
        },
        {
            headerName: "Descripción",
            field: "descripcion",
            flex: 1,
            minWidth: 260,
            filter: true,
            valueFormatter: (params) => textoSeguro(params.value, ""),
        },
        {
            headerName: "Reglas",
            field: "reglas_count",
            width: 110,
            cellRenderer: (params) => {
                return `<span class="badge bg-gradient-info">${params.value ?? 0}</span>`;
            },
        },
        {
            headerName: "Estatus",
            field: "activo",
            width: 120,
            cellRenderer: (params) => badgeActivo(params.value),
        },
        {
            headerName: "Creado",
            field: "created_at",
            width: 140,
            valueFormatter: (params) => formatearFecha(params.value),
        },
        {
            headerName: "Acciones",
            field: "acciones",
            pinned: "right",
            width: 180,
            suppressMenu: true,
            sortable: false,
            filter: false,
            cellRenderer: (params) => accionesTipo(params.data),
        },
    ];

    const gridOptions = {
        rowData: tiposNotificacionData || [],
        columnDefs,
        defaultColDef: {
            sortable: true,
            resizable: true,
            filter: true,
        },
        animateRows: true,
        pagination: true,
        paginationPageSize: 10,
        domLayout: "normal",
        localeText: AG_GRID_LOCALE_ES,
    };

    agGrid.createGrid(gridDiv, gridOptions);
}

function accionesTipo(tipo) {
    if (!tipo) {
        return "";
    }

    let html = `<div class="d-flex gap-1 justify-content-center">`;

    if (tienePermiso("puedeEditar")) {
        html += crearBotonAccion({
            clase: "btn btn-xs bg-gradient-info",
            icono: "fas fa-edit",
            titulo: "Editar tipo",
            onclick: `abrirModalEditarTipo(${tipo.id})`,
        });

        html += crearBotonAccion({
            clase: tipo.activo
                ? "btn btn-xs bg-gradient-warning"
                : "btn btn-xs bg-gradient-success",
            icono: tipo.activo ? "fas fa-toggle-off" : "fas fa-toggle-on",
            titulo: tipo.activo ? "Desactivar tipo" : "Activar tipo",
            onclick: `confirmarToggleTipo(${tipo.id}, ${tipo.activo ? 1 : 0})`,
        });
    }

    if (tienePermiso("puedeEliminar")) {
        html += crearBotonAccion({
            clase: "btn btn-xs bg-gradient-danger",
            icono: "fas fa-trash",
            titulo: "Eliminar tipo",
            onclick: `confirmarEliminarTipo(${tipo.id})`,
        });
    }

    html += `</div>`;

    return html;
}

function abrirModalEditarTipo(id) {
    const modalElement = document.getElementById(
        `modalEditarTipoNotificacion${id}`,
    );

    if (!modalElement) {
        Swal.fire({
            icon: "error",
            title: "Modal no encontrado",
            text: "No se encontró el modal para editar este tipo.",
        });
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function confirmarEliminarTipo(id) {
    Swal.fire({
        icon: "warning",
        title: "¿Eliminar tipo?",
        text: "Si este tipo tiene reglas o notificaciones relacionadas, puede fallar la eliminación.",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById("form-eliminar-tipo");
            form.action = `/notificaciones/tipos/${id}`;
            form.submit();
        }
    });
}

function confirmarToggleTipo(id, activo) {
    const accion = activo ? "desactivar" : "activar";

    Swal.fire({
        icon: "question",
        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} tipo?`,
        text: `Se va a ${accion} este tipo de notificación.`,
        showCancelButton: true,
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#6c757d",
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById("form-toggle-tipo");
            form.action = `/notificaciones/tipos/${id}/toggle`;
            form.submit();
        }
    });
}

/*
|--------------------------------------------------------------------------
| Grid: Reglas de notificación
|--------------------------------------------------------------------------
*/

function iniciarGridReglasNotificacion() {
    const gridDiv = document.querySelector("#reglasNotificacionGrid");

    if (!gridDiv) {
        return;
    }

    const columnDefs = [
        {
            headerName: "#",
            field: "id",
            width: 80,
            pinned: "left",
        },
        {
            headerName: "Tipo",
            field: "tipo.nombre",
            minWidth: 220,
            filter: true,
            valueGetter: (params) => {
                return params.data?.tipo?.nombre ?? "Sin tipo";
            },
        },
        {
            headerName: "Clave",
            field: "tipo.clave",
            minWidth: 170,
            filter: true,
            valueGetter: (params) => {
                return params.data?.tipo?.clave ?? "S/N";
            },
            cellRenderer: (params) => {
                return `<span class="badge bg-gradient-dark">${textoSeguro(params.value)}</span>`;
            },
        },
        {
            headerName: "Empresa",
            field: "empresa.nombre",
            minWidth: 220,
            filter: true,
            valueGetter: (params) => {
                const empresa = params.data?.empresa;

                if (!empresa) {
                    return "Global / Todas";
                }

                return (
                    empresa.nombre ??
                    empresa.razon_social ??
                    empresa.empresa ??
                    `Empresa #${empresa.id}`
                );
            },
        },
        {
            headerName: "Empresa",
            field: "notificar_empresa",
            width: 120,
            cellRenderer: (params) => badgeSiNo(params.value),
        },
        {
            headerName: "Cliente",
            field: "notificar_cliente",
            width: 120,
            cellRenderer: (params) => badgeSiNo(params.value),
        },
        {
            headerName: "Proveedor",
            field: "notificar_proveedor",
            width: 130,
            cellRenderer: (params) => badgeSiNo(params.value),
        },
        {
            headerName: "Usuarios",
            field: "usuarios_count",
            width: 120,
            cellRenderer: (params) => {
                return `<span class="badge bg-gradient-info">${params.value ?? 0}</span>`;
            },
        },
        {
            headerName: "Estatus",
            field: "activo",
            width: 120,
            cellRenderer: (params) => badgeActivo(params.value),
        },
        {
            headerName: "Creada",
            field: "created_at",
            width: 140,
            valueFormatter: (params) => formatearFecha(params.value),
        },
        {
            headerName: "Acciones",
            field: "acciones",
            pinned: "right",
            width: 190,
            suppressMenu: true,
            sortable: false,
            filter: false,
            cellRenderer: (params) => accionesRegla(params.data),
        },
    ];

    const gridOptions = {
        rowData: reglasNotificacionData || [],
        columnDefs,
        defaultColDef: {
            sortable: true,
            resizable: true,
            filter: true,
        },
        animateRows: true,
        pagination: true,
        paginationPageSize: 10,
        localeText: AG_GRID_LOCALE_ES,
    };

    agGrid.createGrid(gridDiv, gridOptions);
}

function accionesRegla(regla) {
    if (!regla) {
        return "";
    }

    let html = `<div class="d-flex gap-1 justify-content-center">`;

    if (tienePermiso("puedeEditar")) {
        html += crearBotonAccion({
            clase: "btn btn-xs bg-gradient-info",
            icono: "fas fa-edit",
            titulo: "Editar regla",
            onclick: `abrirModalEditarRegla(${regla.id})`,
        });

        html += crearBotonAccion({
            clase: regla.activo
                ? "btn btn-xs bg-gradient-warning"
                : "btn btn-xs bg-gradient-success",
            icono: regla.activo ? "fas fa-toggle-off" : "fas fa-toggle-on",
            titulo: regla.activo ? "Desactivar regla" : "Activar regla",
            onclick: `confirmarToggleRegla(${regla.id}, ${regla.activo ? 1 : 0})`,
        });
    }

    if (tienePermiso("puedeEliminar")) {
        html += crearBotonAccion({
            clase: "btn btn-xs bg-gradient-danger",
            icono: "fas fa-trash",
            titulo: "Eliminar regla",
            onclick: `confirmarEliminarRegla(${regla.id})`,
        });
    }

    html += `</div>`;

    return html;
}

function abrirModalEditarRegla(id) {
    const modalElement = document.getElementById(
        `modalEditarReglaNotificacion${id}`,
    );

    if (!modalElement) {
        Swal.fire({
            icon: "error",
            title: "Modal no encontrado",
            text: "No se encontró el modal para editar esta regla.",
        });
        return;
    }

    const modal = new bootstrap.Modal(modalElement);
    modal.show();
}

function confirmarEliminarRegla(id) {
    Swal.fire({
        icon: "warning",
        title: "¿Eliminar regla?",
        text: "También se quitarán los usuarios asignados a esta regla.",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById("form-eliminar-regla");
            form.action = `/notificaciones/reglas/${id}`;
            form.submit();
        }
    });
}

function confirmarToggleRegla(id, activo) {
    const accion = activo ? "desactivar" : "activar";

    Swal.fire({
        icon: "question",
        title: `¿${accion.charAt(0).toUpperCase() + accion.slice(1)} regla?`,
        text: `Se va a ${accion} esta regla de notificación.`,
        showCancelButton: true,
        confirmButtonText: `Sí, ${accion}`,
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#3085d6",
        cancelButtonColor: "#6c757d",
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById("form-toggle-regla");
            form.action = `/notificaciones/reglas/${id}/toggle`;
            form.submit();
        }
    });
}

/*
|--------------------------------------------------------------------------
| Grid: Usuarios por regla
|--------------------------------------------------------------------------
*/

function iniciarGridUsuariosReglas() {
    const gridDiv = document.querySelector("#usuariosReglasGrid");

    if (!gridDiv) {
        return;
    }

    const usuariosReglasData = construirUsuariosReglasData();

    const columnDefs = [
        {
            headerName: "Regla",
            field: "regla_id",
            width: 100,
            pinned: "left",
            cellRenderer: (params) => {
                return `<span class="badge bg-gradient-dark">#${params.value}</span>`;
            },
        },
        {
            headerName: "Tipo",
            field: "tipo_nombre",
            minWidth: 220,
            filter: true,
        },
        {
            headerName: "Clave",
            field: "tipo_clave",
            minWidth: 170,
            filter: true,
            cellRenderer: (params) => {
                return `<span class="badge bg-gradient-dark">${textoSeguro(params.value)}</span>`;
            },
        },
        {
            headerName: "Empresa",
            field: "empresa_nombre",
            minWidth: 220,
            filter: true,
        },
        {
            headerName: "Usuario",
            field: "usuario_nombre",
            minWidth: 220,
            filter: true,
        },
        {
            headerName: "Correo",
            field: "usuario_email",
            minWidth: 240,
            filter: true,
            valueFormatter: (params) => textoSeguro(params.value),
        },
        {
            headerName: "Regla activa",
            field: "regla_activa",
            width: 140,
            cellRenderer: (params) => badgeActivo(params.value),
        },
        {
            headerName: "Acciones",
            field: "acciones",
            pinned: "right",
            width: 120,
            suppressMenu: true,
            sortable: false,
            filter: false,
            cellRenderer: (params) => accionesUsuarioRegla(params.data),
        },
    ];

    const gridOptions = {
        rowData: usuariosReglasData,
        columnDefs,
        defaultColDef: {
            sortable: true,
            resizable: true,
            filter: true,
        },
        animateRows: true,
        pagination: true,
        paginationPageSize: 10,
        localeText: AG_GRID_LOCALE_ES,
    };

    agGrid.createGrid(gridDiv, gridOptions);
}

function construirUsuariosReglasData() {
    const data = [];

    (reglasNotificacionData || []).forEach((regla) => {
        const usuarios = regla.usuarios || [];

        if (usuarios.length === 0) {
            return;
        }

        usuarios.forEach((usuario) => {
            data.push({
                regla_id: regla.id,
                tipo_nombre: regla.tipo?.nombre ?? "Sin tipo",
                tipo_clave: regla.tipo?.clave ?? "S/N",
                empresa_nombre: obtenerNombreEmpresaRegla(regla),
                usuario_id: usuario.id,
                usuario_nombre: usuario.name ?? "S/N",
                usuario_email: usuario.email ?? "S/N",
                regla_activa: regla.activo,
            });
        });
    });

    return data;
}

function obtenerNombreEmpresaRegla(regla) {
    const empresa = regla?.empresa;

    if (!empresa) {
        return "Global / Todas";
    }

    return (
        empresa.nombre ??
        empresa.razon_social ??
        empresa.empresa ??
        `Empresa #${empresa.id}`
    );
}

function accionesUsuarioRegla(row) {
    if (!row) {
        return "";
    }

    let html = `<div class="d-flex gap-1 justify-content-center">`;

    if (tienePermiso("puedeEliminar")) {
        html += crearBotonAccion({
            clase: "btn btn-xs bg-gradient-danger",
            icono: "fas fa-user-times",
            titulo: "Quitar usuario",
            onclick: `confirmarQuitarUsuarioRegla(${row.regla_id}, ${row.usuario_id})`,
        });
    }

    html += `</div>`;

    return html;
}

function confirmarQuitarUsuarioRegla(reglaId, usuarioId) {
    Swal.fire({
        icon: "warning",
        title: "¿Quitar usuario?",
        text: "El usuario dejará de recibir notificaciones de esta regla.",
        showCancelButton: true,
        confirmButtonText: "Sí, quitar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById("form-quitar-usuario-regla");
            form.action = `/notificaciones/reglas/${reglaId}/usuarios/${usuarioId}`;
            form.submit();
        }
    });
}

/*
|--------------------------------------------------------------------------
| Locale AG Grid español básico
|--------------------------------------------------------------------------
*/

const AG_GRID_LOCALE_ES = {
    page: "Página",
    more: "Más",
    to: "a",
    of: "de",
    next: "Siguiente",
    last: "Última",
    first: "Primera",
    previous: "Anterior",
    loadingOoo: "Cargando...",
    selectAll: "Seleccionar todo",
    searchOoo: "Buscar...",
    blanks: "Vacíos",
    filterOoo: "Filtrar...",
    applyFilter: "Aplicar filtro",
    equals: "Igual",
    notEqual: "No igual",
    lessThan: "Menor que",
    greaterThan: "Mayor que",
    lessThanOrEqual: "Menor o igual",
    greaterThanOrEqual: "Mayor o igual",
    inRange: "En rango",
    contains: "Contiene",
    notContains: "No contiene",
    startsWith: "Empieza con",
    endsWith: "Termina con",
    noRowsToShow: "No hay registros para mostrar",
};
