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

let originalHistorialData = [];
let expandedRows = new Set();

function buildGridDataWithDetails() {
    const flatData = [];
    originalHistorialData.forEach((item) => {
        flatData.push(item);
        if (expandedRows.has(item.IdPago)) {
            flatData.push({
                isDetailRow: true,
                IdPago: item.IdPago,
                detailData: item.ContenedoresDetalle || [],
            });
        }
    });
    return flatData;
}

function updateGridData() {
    const data = buildGridDataWithDetails();
    apiGrid.setGridOption("rowData", data);
}

const gridOptions = {
    pagination: true,
    paginationPageSize: 10,
    paginationPageSizeSelector: [10, 20, 50, 100],
    defaultColDef: {
        resizable: true,
        sortable: true,
        filter: true,
    },
    rowSelection: {
        mode: "multiRow",
        headerCheckbox: false,
    },
    isFullWidthRow: (params) => {
        return params.rowNode.data.isDetailRow === true;
    },
    fullWidthCellRenderer: (params) => {
        const detailData = params.data.detailData || [];
        if (detailData.length === 0) {
            return `<div class="p-3 text-muted">No hay detalles de contenedores disponibles.</div>`;
        }
        let html = `
        <div style="padding: 12px 20px; background-color: #f8f9fa; border-left: 4px solid #0d6efd; margin: 4px 0; border-radius: 6px; box-shadow: inset 0 0 5px rgba(0,0,0,0.05);">
            <div class="d-flex align-items-center mb-2">
                <i class="fa fa-boxes text-primary me-2"></i>
                <strong class="text-dark" style="font-size: 13px;">Desglose por Contenedor (${detailData.length})</strong>
            </div>
            <table class="table table-sm table-bordered bg-white mb-0" style="font-size: 12px;">
                <thead class="bg-light">
                    <tr>
                        <th>No. Contenedor</th>
                        <th class="text-right">Sueldo Operador</th>
                        <th class="text-right">Dinero Viaje</th>
                        <th class="text-right">Gastos Justificados</th>
                        <th class="text-right">Total Pagado</th>
                    </tr>
                </thead>
                <tbody>`;

        detailData.forEach((c) => {
            html += `
                <tr>
                    <td><strong class="text-primary">${c.num_contenedor}</strong></td>
                    <td class="text-right">${currencyFormatter(c.sueldo_operador)}</td>
                    <td class="text-right">${currencyFormatter(c.dinero_viaje)}</td>
                    <td class="text-right">${currencyFormatter(c.dinero_justificado)}</td>
                    <td class="text-right font-weight-bold text-success">${currencyFormatter(c.total_pagado)}</td>
                </tr>`;
        });

        html += `
                </tbody>
            </table>
        </div>`;
        return html;
    },
    getRowHeight: (params) => {
        if (params.node.data.isDetailRow) {
            const count = params.node.data.detailData ? params.node.data.detailData.length : 1;
            return 85 + (count * 36);
        }
        return 42;
    },
    rowData: [],
    columnDefs: [
        {
            headerName: "",
            field: "expand",
            width: 50,
            sortable: false,
            filter: false,
            cellRenderer: (params) => {
                if (params.data.isDetailRow) return "";
                const isExpanded = expandedRows.has(params.data.IdPago);
                const count = params.data.ContenedoresDetalle ? params.data.ContenedoresDetalle.length : 0;
                if (count === 0) return "";
                return `
                    <button type="button" class="btn btn-sm btn-link text-primary p-0 btn-toggle-detail" data-id="${params.data.IdPago}" style="font-size: 14px; text-decoration: none;">
                        <i class="fa ${isExpanded ? "fa-minus-square text-danger" : "fa-plus-square text-primary"}"></i>
                    </button>
                `;
            },
        },
        { field: "IdPago", hide: true },
        { field: "IdOperador", hide: true },
        { field: "IdBanco", hide: true },
        { field: "Operador" },
        { field: "Fecha" },
        { field: "ViajesRealizados" },
        {
            field: "SueldoOperador",
            width: 140,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            field: "DineroViaje",
            width: 140,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            field: "DineroJustificado",
            width: 140,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            field: "TotalPagado",
            width: 140,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: "right" },
        },
        {
            headerName: "Acciones",
            field: "acciones",
            width: 100,
            cellRenderer: (params) => {
                if (params.data.isDetailRow) return "";
                return `
            <button class="btn btn-danger btn-sm btnEliminarLiquidacion"
                data-id="${params.data.IdPago}">
                <i class="fa fa-trash"></i>
            </button>
        `;
            },
        },
    ],

    localeText: localeText,
};

document.addEventListener("click", function (e) {
    const toggleBtn = e.target.closest(".btn-toggle-detail");
    if (toggleBtn) {
        const id = parseInt(toggleBtn.dataset.id);
        if (expandedRows.has(id)) {
            expandedRows.delete(id);
        } else {
            expandedRows.add(id);
        }
        updateGridData();
        return;
    }

    if (e.target.closest(".btnEliminarLiquidacion")) {
        const btn = e.target.closest(".btnEliminarLiquidacion");
        const id = btn.dataset.id;

        Swal.fire({
            title: "Eliminar liquidación",
            text: "¿Seguro que deseas eliminar esta liquidación?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Eliminar",
            cancelButtonText: "Cancelar",
            confirmButtonColor: "#d33",
        }).then((result) => {
            if (result.isConfirmed) {
                eliminarLiquidacion(id);
            }
        });
    }
});

function eliminarLiquidacion(id) {
    let _token = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    $.ajax({
        url: "/liquidaciones/historial/delete/" + id,
        type: "POST",
        data: {
            _token: _token,
        },
        success: function (response) {
            Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);

            if (response.TMensaje === "success") {
                location.reload();
            }
        },
        error: function () {
            Swal.fire("Error", "No se pudo eliminar", "error");
        },
    });
}

const myGridElement = document.querySelector("#gridHistorial");
let apiGrid = agGrid.createGrid(myGridElement, gridOptions);

const paginationTitle = document.querySelector("#ag-32-label");

if (paginationTitle) {
    paginationTitle.textContent = "Registros por página";
}

let numContenedorSearchTimeout;
$(document).on("keyup input", "#num_contenedor_search", function () {
    clearTimeout(numContenedorSearchTimeout);
    numContenedorSearchTimeout = setTimeout(() => {
        let picker = $("#daterange").data("daterangepicker");
        if (picker) {
            getHistorial(
                picker.startDate.format("YYYY-MM-DD"),
                picker.endDate.format("YYYY-MM-DD")
            );
        } else {
            const today = new Date();
            const sevenDaysAgo = new Date();
            sevenDaysAgo.setDate(today.getDate() - 7);
            const formatDate = (date) => date.toISOString().split("T")[0];
            getHistorial(formatDate(sevenDaysAgo), formatDate(today));
        }
    }, 400);
});

function getHistorial(startDate, endDate) {
    let _token = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    let num_contenedor = $("#num_contenedor_search").val() || "";

    $.ajax({
        url: "/liquidaciones/historial/data",
        type: "post",
        data: { _token, startDate, endDate, num_contenedor },
        beforeSend: () => {},
        success: (response) => {
            originalHistorialData = response.data || [];
            updateGridData();
        },
        error: () => {},
    });
}

function getComprobantePago() {
    let _token = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");
    let liquidados = apiGrid.getSelectedRows().filter((r) => !r.isDetailRow);
    if (liquidados.length <= 0) {
        Swal.fire(
            "Seleccione un pago",
            "Debe seleccionar solo un pago de la lista",
            "warning",
        );
        return;
    }

    let IdOperacion = liquidados[0].IdPago;

    $.ajax({
        url: "/liquidaciones/historial/pagos/comprobante",
        method: "POST",
        data: {
            _token: _token,
            IdOperacion: IdOperacion,
            fileType: "pdf",
        },
        xhrFields: {
            responseType: "blob",
        },
        success: function (response) {
            if (response instanceof Blob) {
                var blob = new Blob([response], { type: "application/pdf" });
                var url = URL.createObjectURL(blob);

                var a = document.createElement("a");
                a.style.display = "none";
                a.href = url;
                a.target = "_blank";
                document.body.appendChild(a);

                a.click();

                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);
            } else {
                console.error("La respuesta no me un Blob válido:", response);
            }
        },
        error: function (xhr, status, error) {
            console.error(error);
            alert("Ocurrió un error al exportar los datos.");
        },
    });
}
