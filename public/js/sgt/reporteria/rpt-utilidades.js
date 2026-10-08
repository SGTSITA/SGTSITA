class MissionResultRenderer {
    eGui;

    // Optional: Params for rendering. The same params that are passed to the cellRenderer function.
    init(params) {
        let icon = document.createElement("img");
        icon.src = `https://www.ag-grid.com/example-assets/icons/${params.value ? "tick-in-circle" : "cross-in-circle"}.png`;
        icon.setAttribute("style", "width: auto; height: auto;");

        this.eGui = document.createElement("span");
        this.eGui.setAttribute(
            "style",
            "display: flex; justify-content: center; height: 100%; align-items: center",
        );
        this.eGui.appendChild(icon);
    }

    // Required: Return the DOM element of the component, this is what the grid puts into the cell
    getGui() {
        return this.eGui;
    }

    // Required: Get the cell to refresh.
    refresh(params) {
        return false;
    }
}

class CustomButtonComponent {
    eGui;
    eButton;
    eventListener;

    init(params) {
        this.eGui = document.createElement("div");
        let button = document.createElement("button");
        button.innerHTML =
            '<span class="svg-icon svg-icon-muted svg-icon-2hx"><svg width="23" height="24" viewBox="0 0 23 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 13V13.5C21 16 19 18 16.5 18H5.6V16H16.5C17.9 16 19 14.9 19 13.5V13C19 12.4 19.4 12 20 12C20.6 12 21 12.4 21 13ZM18.4 6H7.5C5 6 3 8 3 10.5V11C3 11.6 3.4 12 4 12C4.6 12 5 11.6 5 11V10.5C5 9.1 6.1 8 7.5 8H18.4V6Z" fill="currentColor"/><path opacity="0.3" d="M21.7 6.29999C22.1 6.69999 22.1 7.30001 21.7 7.70001L18.4 11V3L21.7 6.29999ZM2.3 16.3C1.9 16.7 1.9 17.3 2.3 17.7L5.6 21V13L2.3 16.3Z" fill="currentColor"/></svg></span></span>';
        button.className = "btn btn-sm bg-gradient-success";
        button.style.fontSize = "10px";
        button.style.padding = "2px 6px";
        button.style.lineHeight = "1";

        const NumContenedorValue = params.data.NumContenedor;

        this.eventListener = () => assignEmpresa(NumContenedorValue);
        button.addEventListener("click", this.eventListener);
        this.eGui.appendChild(button);
    }

    getGui() {
        return this.eGui;
    }

    refresh(params) {
        return true;
    }

    destroy() {
        if (button) {
            button.removeEventListener("click", this.eventListener);
        }
    }
}

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

const currencyFormatter = (value) => {
    return new Intl.NumberFormat("es-MX", {
        style: "currency",
        currency: "MXN",
    }).format(value);
};

const formatFecha = (params) => {
    if (!params) return "";
    const [year, month, day] = params.split("-"); // Divide YYYY-MM-DD
    return `${day}/${month}/${year}`; // Retorna en formato d/m/Y
};

const gridOptions = {
    pagination: true,
    paginationPageSize: 10,
    paginationPageSizeSelector: [10, 20, 50, 100],
    rowSelection: {
        mode: "multiRow",
        headerCheckbox: true,
    },
    rowClassRules: {
        "bg-gradient-danger": (params) => params.data.utilidad < 0,
    },
    rowData: [],

    columnDefs: [
        { field: "numContenedor", filter: true, floatingFilter: true },
        { field: "cliente", filter: true, floatingFilter: true },
        {
            field: "precioViaje",
            width: 150,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            field: "pagoOperacion",
            width: 150,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            field: "gastosExtra",
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            field: "dineroViajeSinJustificar",
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            field: "gastosViaje",
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            field: "gastosDiferidos",
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            field: "utilidad",
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            field: "transportadoPor",
            width: 150,
            filter: true,
            floatingFilter: true,
        },
        { field: "operadorOrProveedor", filter: true, floatingFilter: true },
        { field: "estatusViaje", filter: true, floatingFilter: true },
        { field: "estatusPago", filter: true, floatingFilter: true },
        { field: "detalleGastos", hide: true },
    ],

    localeText: localeText,
    onSelectionChanged: () => {
        const selectedRows = apiGrid ? apiGrid.getSelectedRows() : [];
        const btn = document.getElementById("btnVistaPreliminar");
        if (btn) {
            if (selectedRows.length > 0) {
                btn.classList.remove("d-none");
            } else {
                btn.classList.add("d-none");
                // Hide the preview panel if no rows are selected
                document.getElementById("panelVistaPreliminar").classList.add("d-none");
            }
        }
    }
};

const myGridElement = document.querySelector("#myGrid");
let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
// const gridInstance = new agGrid.Grid(myGridElement, gridOptions);

const paginationTitle = document.querySelector("#ag-32-label");

if (paginationTitle) {
    paginationTitle.textContent = "Registros por página";
}

let IdContenedor = null;
let btnVerDetalle = document.querySelector("#btnVerDetalle");

function exportUtilidades() {
    Swal.fire({
        title: "Exportar Reporte",
        text: "¿Cómo deseas exportar el reporte?",
        icon: "question",
        showDenyButton: true,
        showCancelButton: true,
        confirmButtonText: "Excel",
        denyButtonText: "PDF",
        cancelButtonText: "Cancelar",
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            // Usuario eligió Excel
            ejecutarExportacion("xlsx");
        } else if (result.isDenied) {
            // Usuario eligió PDF
            ejecutarExportacion("pdf");
        }
    });
}

function ejecutarExportacion(fileType) {
    var _token = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    const rowData = JSON.stringify(apiGrid.getSelectedRows());
    const totalRows = apiGrid.paginationGetRowCount();
    let fechaInicio = $("#daterange").attr("data-start");
    let idProveedor = $("#selProveedorUtilidad").val();
    let idEquipo = $("#selEquipoUtilidad").val();

    $.ajax({
        url: "/reporteria/utilidad/export",
        method: "POST",
        data: {
            _token: _token,
            rowData: rowData,
            totalRows: totalRows,
            fechaInicio: fechaInicio,
            fechaFin: fechaFin,
            fileType: fileType,
            id_proveedor: idProveedor,
            id_equipo: idEquipo,
        },
        xhrFields: {
            responseType: "blob",
        },
        beforeSend: () => {
            mostrarLoading("Preparando reporte... espere un momento");
        },
        success: function (response) {
            ocultarLoading();
            if (response instanceof Blob) {
                // Crear Blob dependiendo del tipo de archivo
                var blob = new Blob([response], {
                    type:
                        fileType === "xlsx"
                            ? "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet"
                            : "application/pdf",
                });
                var url = URL.createObjectURL(blob);

                // Generar nombre dinámico con fecha y hora
                var timestamp = moment().format("YYYY-MM-DD_HH-mm-ss");
                var fileName =
                    "Resultados_" +
                    timestamp +
                    (fileType === "xlsx" ? ".xlsx" : ".pdf");

                // Crear enlace de descarga
                var a = document.createElement("a");
                a.style.display = "none";
                a.href = url;
                a.download = fileName;
                document.body.appendChild(a);

                // Ejecutar descarga
                a.click();

                // Limpiar
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            } else {
                console.error("La respuesta no es un Blob válido:", response);
            }
        },
        error: function (xhr, status, error) {
            ocultarLoading();
            alert("Ocurrió un error al exportar los datos.");
        },
    });
}

function getUtilidadesViajes(startDate, endDate) {
    var _token = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    let idProveedor = $("#selProveedorUtilidad").val();
   let idEquipo = $("#selEquipoUtilidad").val();
    return  $.ajax({
        url: "/reporteria/utilidad/ver-utilidad",
        type: "post",
        data: { _token, startDate, endDate, id_proveedor: idProveedor, id_equipo: idEquipo },
        beforeSend: () => {
            mostrarLoading("Consultando viajes...");
        },
        success: (response) => {
            ocultarLoading();
            let data = JSON.parse(response);
            apiGrid.setGridOption("rowData", data.Info);
            
            // Guardar gastos generales
            window.latestGastosGenerales = data.GastosGenerales || [];
            
            // Actualizar vista preliminar si está abierta
            if (!document.getElementById("panelVistaPreliminar").classList.contains("d-none")) {
                calcularVistaPreliminar();
            }
        },
        error: () => {
            ocultarLoading();
        },
    });
}

$("#selProveedorUtilidad, #selEquipoUtilidad").on("change", function () {
    let fechaInicio = $("#daterange").attr("data-start");
    let fechaFin = $("#daterange").attr("data-end");
    if (fechaInicio && fechaFin) {
        getUtilidadesViajes(fechaInicio, fechaFin);
    }
});

function cargarPdfVistaPreliminar() {
    const panel = document.getElementById("panelVistaPreliminar");
    panel.classList.remove("d-none");

    const _token = document.querySelector('meta[name="csrf-token"]').getAttribute("content");
    const rowData = JSON.stringify(apiGrid.getSelectedRows());
    const totalRows = apiGrid.paginationGetRowCount();
    let fechaInicio = $("#daterange").attr("data-start");
    let fechaFin = $("#daterange").attr("data-end");
    let idProveedor = $("#selProveedorUtilidad").val();
    let idEquipo = $("#selEquipoUtilidad").val();

    let iframeContainer = document.getElementById("iframePreviewContainer");
    iframeContainer.innerHTML = ''; // Clear previous

    const isMobileOrTablet = window.innerWidth < 992 || ('ontouchstart' in window) || (navigator.maxTouchPoints > 0);

    if (isMobileOrTablet) {
        let mobileContainer = document.createElement("div");
        mobileContainer.className = "text-center p-5 bg-white rounded border shadow-sm w-100";
        mobileContainer.innerHTML = `
            <div class="mb-3">
                <i class="fas fa-file-pdf text-danger" style="font-size: 56px;"></i>
            </div>
            <h5 class="text-dark font-weight-bold">Vista previa no disponible en este dispositivo</h5>
            <p class="text-muted small px-3">Los navegadores móviles no permiten incrustar la vista previa interactiva del PDF. Puedes descargar o abrir el reporte completo directamente pulsando el siguiente botón.</p>
            <button type="button" class="btn btn-danger btn-sm text-white px-4 py-2 mt-2 font-weight-bold" id="btnDescargarPdfDirecto">
                <i class="fas fa-download me-1"></i> Descargar / Abrir PDF
            </button>
        `;
        iframeContainer.appendChild(mobileContainer);

        document.getElementById("btnDescargarPdfDirecto").addEventListener("click", function() {
            ejecutarExportacion("pdf");
        });
    } else {
        let iframe = document.createElement("iframe");
        iframe.name = "pdf_preview_iframe";
        iframe.style.width = "100%";
        iframe.style.height = "700px";
        iframe.style.border = "none";
        iframe.style.borderRadius = "8px";
        iframeContainer.appendChild(iframe);

        let form = document.createElement("form");
        form.method = "POST";
        form.action = "/reporteria/utilidad/export";
        form.target = "pdf_preview_iframe";

        const inputs = {
            _token: _token,
            rowData: rowData,
            totalRows: totalRows,
            fechaInicio: fechaInicio,
            fechaFin: fechaFin,
            fileType: 'pdf',
            id_proveedor: idProveedor,
            id_equipo: idEquipo
        };

        for (const [key, value] of Object.entries(inputs)) {
            let input = document.createElement("input");
            input.type = "hidden";
            input.name = key;
            input.value = value;
            form.appendChild(input);
        }

        document.body.appendChild(form);
        form.submit();
        document.body.removeChild(form);
    }

    panel.scrollIntoView({ behavior: 'smooth' });
}

document.getElementById("btnVistaPreliminar").addEventListener("click", function() {
    cargarPdfVistaPreliminar();
});


let currentGastosModalTab = 'contenedor';

function cambiarTabGastos(tipo) {
    currentGastosModalTab = tipo;
    const btnContenedor = document.getElementById("tabBtnContenedor");
    const btnIndirectos = document.getElementById("tabBtnIndirectos");

    if (tipo === 'contenedor') {
        if (btnContenedor) {
            btnContenedor.classList.add("active", "bg-white", "shadow-sm", "text-primary");
            btnContenedor.classList.remove("text-muted");
        }
        if (btnIndirectos) {
            btnIndirectos.classList.remove("active", "bg-white", "shadow-sm", "text-primary");
            btnIndirectos.classList.add("text-muted");
        }
        renderizarGastosContenedor();
    } else {
        if (btnIndirectos) {
            btnIndirectos.classList.add("active", "bg-white", "shadow-sm", "text-primary");
            btnIndirectos.classList.remove("text-muted");
        }
        if (btnContenedor) {
            btnContenedor.classList.remove("active", "bg-white", "shadow-sm", "text-primary");
            btnContenedor.classList.add("text-muted");
        }
        renderizarGastosIndirectos();
    }
}

function renderizarGastosContenedor() {
    let seleccionados = (window.apiGrid && typeof apiGrid.getSelectedRows === 'function') 
        ? apiGrid.getSelectedRows() 
        : [];
    const ul = document.getElementById("infoGastos");
    if (!ul) return;
    ul.innerHTML = "";

    if (seleccionados.length === 0) {
        ul.innerHTML = `<li class="list-group-item border-0 text-center py-4 text-muted"><i class="fas fa-box-open fa-2x mb-2 text-warning"></i><br>No hay contenedor seleccionado. Seleccione un contenedor de la tabla para ver sus gastos de viaje.</li>`;
        const badge = document.getElementById("badgeTotalGastosModal");
        if (badge) badge.textContent = moneyFormat(0);
        const footer = document.getElementById("resumenConteoGastos");
        if (footer) footer.textContent = "0 gastos";
        return;
    }

    const cont = seleccionados[0];
    const elementos = cont.detalleGastos || [];
    let total = 0;

    if (elementos.length === 0) {
        ul.innerHTML = `<li class="list-group-item border-0 text-center py-4 text-muted"><i class="fas fa-check-circle fa-2x mb-2 text-success"></i><br>Este contenedor no tiene gastos de viaje registrados.</li>`;
    } else {
        elementos.forEach((item) => {
            const monto = parseFloat(item.monto_gasto || 0);
            total += monto;
            const fechaStr = item.fecha_gasto || '';
            const fechaLetra = fechaStr ? obtenerFechaFormateada(fechaStr) : 'Sin fecha';
            const liTemplate = `
                <li class="list-group-item border-0 d-flex justify-content-between align-items-center ps-0 mb-2 border-radius-lg p-2 bg-light">
                    <div class="d-flex flex-column">
                        <h6 class="mb-1 text-dark font-weight-bold text-sm" style="color:#333335 !important">${item.motivo_gasto || 'Gasto de Viaje'}</h6>
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-xs text-muted"><i class="far fa-calendar-alt me-1"></i>${fechaLetra}</span>
                            <span class="badge bg-purple-transparent text-xs">${item.tipo_gasto || 'Directo'}</span>
                        </div>
                    </div>
                    <div class="d-flex fw-semibold align-items-right text-dark" style="font-size:15px;">
                        ${moneyFormat(monto)}
                    </div>
                </li>`;
            ul.innerHTML += liTemplate;
        });
    }

    const sub = document.getElementById("subtituloSeccionGastos");
    if (sub) sub.textContent = `Gastos de Viaje: ${cont.numContenedor || ''} (${elementos.length})`;
    const badge = document.getElementById("badgeTotalGastosModal");
    if (badge) badge.textContent = moneyFormat(total);
    const footer = document.getElementById("resumenConteoGastos");
    if (footer) footer.textContent = `${elementos.length} gasto(s) de viaje - Total: ${moneyFormat(total)}`;
}

function renderizarGastosIndirectos() {
    const ul = document.getElementById("infoGastos");
    if (!ul) return;
    ul.innerHTML = "";
    const elementos = window.latestGastosGenerales || [];
    let total = 0;

    if (elementos.length === 0) {
        ul.innerHTML = `<li class="list-group-item border-0 text-center py-4 text-muted"><i class="fas fa-info-circle fa-2x mb-2 text-info"></i><br>No se encontraron gastos indirectos/generales para el periodo seleccionado.</li>`;
    } else {
        elementos.forEach((item) => {
            const monto = parseFloat(item.monto_aplicado || item.monto_total || item.monto_gasto || 0);
            total += monto;
            const concepto = item.concepto || item.motivo_gasto || (item.categoria ? item.categoria.categoria : 'Gasto General');
            const catNombre = (item.categoria && item.categoria.categoria) ? item.categoria.categoria : (item.tipo_gasto || 'Indirecto');
            const fechaStr = item.fecha_aplicada || item.fecha_gasto || '';
            const fechaLetra = fechaStr ? obtenerFechaFormateada(fechaStr) : 'Sin fecha';
            const metodo = item.metodo_imputacion ? `<span class="badge bg-light text-secondary border text-xxs ms-1">${item.metodo_imputacion}</span>` : '';
            const estatus = item.estatus ? `<span class="badge bg-success-transparent text-success text-xxs text-uppercase ms-1">${item.estatus}</span>` : '';

            const liTemplate = `
                <li class="list-group-item border-0 d-flex justify-content-between align-items-center ps-0 mb-2 border-radius-lg p-2 bg-light">
                    <div class="d-flex flex-column" style="max-width: 68%;">
                        <h6 class="mb-1 text-dark font-weight-bold text-sm" style="color:#333335 !important">${concepto}</h6>
                        <div class="d-flex align-items-center gap-1 flex-wrap">
                            <span class="text-xs text-muted"><i class="far fa-calendar-alt me-1"></i>${fechaLetra}</span>
                            <span class="badge bg-purple-transparent text-xs">${catNombre}</span>
                            ${metodo}
                            ${estatus}
                        </div>
                    </div>
                    <div class="d-flex flex-column text-end align-items-end">
                        <span class="fw-bold text-danger" style="font-size:15px;">${moneyFormat(monto)}</span>
                    </div>
                </li>`;
            ul.innerHTML += liTemplate;
        });
    }

    const sub = document.getElementById("subtituloSeccionGastos");
    if (sub) sub.textContent = `Gastos Indirectos / Generales (${elementos.length})`;
    const badge = document.getElementById("badgeTotalGastosModal");
    if (badge) badge.textContent = moneyFormat(total);
    const footer = document.getElementById("resumenConteoGastos");
    if (footer) footer.textContent = `${elementos.length} gasto(s) indirecto(s) - Total: ${moneyFormat(total)}`;
}

function obtenerFechaFormateada(fecha) {
    if (!fecha) return '';
    try {
        const clean = String(fecha).split('T')[0].replace(/-/g, '/');
        return obtenerFechaEnLetra(clean);
    } catch(e) {
        return String(fecha).split('T')[0];
    }
}

async function verDetalleGastos() {
    const startDate = $("#daterange").attr("data-start");
    const endDate = $("#daterange").attr("data-end");

    // Si los gastos indirectos aún no se han consultado en memoria, consultarlos
    if (window.latestGastosGenerales === undefined && startDate && endDate) {
        mostrarLoading("Consultando gastos...");
        await getUtilidadesViajes(startDate, endDate);
        ocultarLoading();
    }

    let seleccionados = (window.apiGrid && typeof apiGrid.getSelectedRows === 'function') 
        ? apiGrid.getSelectedRows() 
        : [];
    
    const gastosIndirectos = window.latestGastosGenerales || [];
    const countIndirectos = gastosIndirectos.length;
    const tabCountInd = document.getElementById("tabCountIndirectos");
    if (tabCountInd) tabCountInd.textContent = countIndirectos;

    const tabsBar = document.getElementById("gastosModalTabs");
    if (seleccionados.length > 0) {
        // Caso 1: Hay un contenedor seleccionado
        const cont = seleccionados[0];
        if (tabsBar) tabsBar.style.display = "flex";
        const tabItemContenedor = document.getElementById("tabItemContenedor");
        if (tabItemContenedor) tabItemContenedor.style.display = "block";
        const gastosContenedor = cont.detalleGastos || [];
        const tabCountCont = document.getElementById("tabCountContenedor");
        if (tabCountCont) tabCountCont.textContent = gastosContenedor.length;
        const lblContenedor = document.getElementById("labelContenedor");
        if (lblContenedor) lblContenedor.textContent = `Contenedor ${cont.numContenedor || ''}`;
        cambiarTabGastos('contenedor');
    } else {
        // Caso 2: No hay contenedor seleccionado (o no hay viajes en el periodo)
        // Se pasa automáticamente a los gastos indirectos del mes/periodo y se oculta la barra de tabs
        if (tabsBar) tabsBar.style.display = "none";
        const tabItemContenedor = document.getElementById("tabItemContenedor");
        if (tabItemContenedor) tabItemContenedor.style.display = "none";
        const lblContenedor = document.getElementById("labelContenedor");
        if (lblContenedor) lblContenedor.textContent = `Periodo General: ${startDate || ''} AL ${endDate || ''}`;
        cambiarTabGastos('indirectos');
    }

    mostrarModal();
}

const btnVerDetalleEl = document.getElementById("btnVerDetalle");
if (btnVerDetalleEl) {
    btnVerDetalleEl.addEventListener("click", () => {
        verDetalleGastos();
    });
}

$(".moneyformat").on("focus", (e) => {
    var val = e.target.value;
    e.target.value = reverseMoneyFormat(val);
});

$(".moneyformat").on("blur", (e) => {
    var val = e.target.value;
    e.target.value = moneyFormat(val);
});
