class MissionResultRenderer {
    eGui;

    // Optional: Params for rendering. The same params that are passed to the cellRenderer function.
    init(params) {
        let icon = document.createElement('img');
        icon.src = `https://www.ag-grid.com/example-assets/icons/${params.value ? 'tick-in-circle' : 'cross-in-circle'}.png`;
        icon.setAttribute('style', 'width: auto; height: auto;');

        this.eGui = document.createElement('span');
        this.eGui.setAttribute('style', 'display: flex; justify-content: center; height: 100%; align-items: center');
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
        this.eGui = document.createElement('div');
        let button = document.createElement('button');
        button.innerHTML =
            '<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path opacity="0.3" d="M5 16C3.3 16 2 14.7 2 13C2 11.3 3.3 10 5 10H5.1C5 9.7 5 9.3 5 9C5 6.2 7.2 4 10 4C11.9 4 13.5 5 14.3 6.5C14.8 6.2 15.4 6 16 6C17.7 6 19 7.3 19 9C19 9.4 18.9 9.7 18.8 10C18.9 10 18.9 10 19 10C20.7 10 22 11.3 22 13C22 14.7 20.7 16 19 16H5ZM8 13.6H16L12.7 10.3C12.3 9.89999 11.7 9.89999 11.3 10.3L8 13.6Z" fill="currentColor" /><path d="M11 13.6V19C11 19.6 11.4 20 12 20C12.6 20 13 19.6 13 19V13.6H11Z" fill="currentColor" /></svg>';
        button.className = 'btn btn-sm btn-primary';
        button.style.fontSize = '10px';
        button.style.padding = '2px 6px';
        button.style.lineHeight = '1';

        const NumContenedorValue = params.data.NumContenedor;

        this.eventListener = () => goToUploadDocuments(NumContenedorValue);
        button.addEventListener('click', this.eventListener);
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
            button.removeEventListener('click', this.eventListener);
        }
    }
}

function verHistorialEstatus(maniobraId, contenedor) {
    fetch(`/viajes/maniobras/${maniobraId}/historial-estatus`)
        .then((res) => res.json())
        .then((data) => {
            let html = '';
            let title = `Historial de estatus - ${contenedor || ''}`;

            if (data.length === 0) {
                html = '<p class="text-muted text-center">Sin historial</p>';
            }

            data.forEach((item) => {
                html += `
                    <div class="mb-3 border-start ps-3">
                        <strong>${item.estatus}</strong><br>
                        <small class="text-muted">
                          Fecha Creado:  ${item.created_at}
                        </small>
                        <p class="mb-0">${item.nota ?? ''}</p>
                    </div>
                `;
            });

            document.getElementById('historialEstatusContenido').innerHTML = html;
            document.getElementById('modalHistorialEstatusTitle').innerText = title;

            new bootstrap.Modal(document.getElementById('modalHistorialEstatus')).show();
        });
}

const localeText = {
    page: 'Página',
    more: 'Más',
    to: 'a',
    of: 'de',
    next: 'Siguiente',
    last: 'Último',
    first: 'Primero',
    previous: 'Anterior',
    loadingOoo: 'Cargando...',
    selectAll: 'Seleccionar todo',
    searchOoo: 'Buscar...',
    blanks: 'Vacíos',
    filterOoo: 'Filtrar...',
    applyFilter: 'Aplicar filtro...',
    equals: 'Igual',
    notEqual: 'Distinto',
    lessThan: 'Menor que',
    greaterThan: 'Mayor que',
    contains: 'Contiene',
    notContains: 'No contiene',
    startsWith: 'Empieza con',
    endsWith: 'Termina con',
    andCondition: 'Y',
    orCondition: 'O',
    group: 'Grupo',
    columns: 'Columnas',
    filters: 'Filtros',
    pivotMode: 'Modo Pivote',
    groups: 'Grupos',
    values: 'Valores',
    noRowsToShow: 'Sin filas para mostrar',
    pinColumn: 'Fijar columna',
    autosizeThiscolumn: 'Ajustar columna',
    copy: 'Copiar',
    resetColumns: 'Restablecer columnas',
    blank: 'Vacíos',
    notBlank: 'No Vacíos',
    paginationPageSize: 'Registros por página',
};

const estatusColorMap = {
    1: 'badge-light-info', // Local solicitado
    2: 'badge-light-success', // En patio
    3: 'badge-light-info', // En revisión
    4: 'badge-light-warning', // En proceso doc
    5: 'badge-light-success', // Docs liberados
    6: 'badge-light-warning', // Para liberación
    7: 'badge-light-success', // Liberado
    8: 'badge-light-warning', // Listo para salir
    9: 'badge-light-success', // Fuera de patio
    10: 'badge-light-info', // En espera
    11: 'badge-light-danger', // Incidencia
    12: 'badge-light-danger', // Cancelado
};

const agCellClassRules = {
    'badge fs-base': () => true,
    ...Object.fromEntries(
        Object.entries(estatusColorMap).map(([id, clase]) => [clase, (p) => p.data?.estatus_maniobra_id == id]),
    ),
};
const gridOptions = {
    pagination: true,
    paginationPageSize: 500,
    paginationPageSizeSelector: [200, 500, 1000],

    rowSelection: {
        mode: 'multiRow',
        headerCheckbox: true,
    },

    defaultColDef: {
        sortable: true,
        filter: true,
        resizable: true,
    },

    columnDefs: [
        // ===== OCULTOS / CONTROL =====
        { field: 'id', hide: true },
        { field: 'estatus_maniobra_id', hide: true },
        { field: 'tipo', hide: true },
        { field: 'NUM_CONTENEDOR_REFER', hide: true },

        // ===== DOCUMENTOS =====
        {
            field: 'BoletaLiberacion',
            headerName: 'Boleta Liberación',
            width: 120,
            cellRenderer: MissionResultRenderer,
            valueFormatter: (p) => (p.value ? 'SI' : 'NO'),
        },
        {
            field: 'DODA',
            width: 90,
            cellRenderer: MissionResultRenderer,
            valueFormatter: (p) => (p.value ? 'SI' : 'NO'),
        },
        // {
        //     field: 'FormatoCartaPorte',
        //     headerName: 'Carta Porte',
        //     width: 110,
        //     cellRenderer: MissionResultRenderer,
        // },
        // {
        //     field: 'PreAlta',
        //     headerName: 'Pre-Alta',
        //     width: 90,
        //     cellRenderer: MissionResultRenderer,
        // },
        // {
        //     field: 'foto_patio',
        //     headerName: 'Foto Patio',
        //     width: 100,
        //     cellRenderer: MissionResultRenderer,
        // },
        {
            field: 'BoletaPatio',
            headerName: 'Boleta Patio',
            width: 110,
            cellRenderer: MissionResultRenderer,
            valueFormatter: (p) => (p.value ? 'SI' : 'NO'),
        },

        {
            field: 'NumContenedor',
            headerName: 'Contenedor',
            minWidth: 180,
            autoHeight: true,
            cellStyle: (params) => {
                const styles = {
                    whiteSpace: 'normal',
                    lineHeight: '1.5',
                };

                if (params.data.tipo === 'Full') {
                    styles.backgroundColor = '#ffe5b4';
                }

                return styles;
            },
        },
        { field: 'Referencia', headerName: 'Referencia', minWidth: 150, autoHeight: true },
        {
            field: 'estado_contenedor',
            headerName: 'Estado Contenedor',
            width: 130,
            cellRenderer: (p) => {
                if (p.value === 'Ninguno') {
                    return `<span class="text-muted">Ninguno</span>`;
                }

                const colors = {
                    VERDE: '#198754',
                    AMARILLO: '#ffc107',
                    ROJO: '#dc3545',
                    OVT: '#0d6efd',
                };

                return `
        <span style="font-weight:bold; color:${colors[p.value] || '#000'}">
            ${p.value}
        </span>
    `;
            },
        },

        {
            field: 'EstatusManiobra',
            headerName: 'Estatus',

            cellClassRules: agCellClassRules,
            minWidth: 180,
            cellRenderer: (p) => {
                if (!p.value) return '';

                return `
            <span
                style="cursor:pointer; text-decoration:underline;"
                onclick="verHistorialEstatus(${p.data.id}, '${p.data.NumContenedor}')"
                title="Ver historial de estatus"
            >
                ${p.value}
            </span>
        `;
            },
        },

        { field: 'Origen', minWidth: 140 },
        { field: 'Destino', minWidth: 140 },

        { field: 'Peso', width: 100 },
        { field: 'Terminal', width: 120 },
        { field: 'Puerto', width: 100 },
        { field: 'NAutorizacion', headerName: 'N° Autorización', width: 150 },
        { field: 'FechaSolicitud', headerName: 'Fecha Solicitud', width: 140 },

        { field: 'cp_pedimento', headerName: 'Pedimento', width: 120 },
        { field: 'cp_clase_ped', headerName: 'Clase Pedimento', width: 120 },
        { field: 'dias_estadia', headerName: 'Días Estadía', width: 120 },
        { field: 'dias_pernocta', headerName: 'Días Pernocta', width: 120 },
        { field: 'tarifa_estadia', headerName: 'Tarifa Estadía', width: 120 },
        { field: 'tarifa_pernocta', headerName: 'Tarifa Pernocta', width: 120 },
        { field: 'total_estadia', headerName: 'Total Estadía', width: 120 },
        { field: 'total_pernocta', headerName: 'Total Pernocta', width: 120 },
        { field: 'costo_maniobra_local', headerName: 'Maniobra', width: 120 },
        { field: 'total_general', headerName: 'Total General', width: 120 },
        { field: 'agente_aduanal', minWidth: 180 },

        { field: 'Empresa', minWidth: 180 },
        { field: 'Proveedor', minWidth: 180 },
        { field: 'Cliente', minWidth: 180 },
        { field: 'Subcliente', minWidth: 180 },

        {
            field: 'Observaciones',
            minWidth: 220,
            wrapText: true,
            autoHeight: true,
        },
    ],

    localeText: localeText,
};

const myGridElement = document.querySelector('#myGrid');
let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
// const gridInstance = createGrid(myGridElement, gridOptions)//new agGrid.Grid(myGridElement, gridOptions);

var paginationTitle = document.querySelector('#ag-32-label');
paginationTitle.textContent = 'Registros por página';

const btnDocumets = document.querySelectorAll('.btnDocs');
//const api = createGrid(gridDiv, gridOptions)
let columnsCache = [];

function toggleColumns() {
    const container = document.getElementById('columnsCheckboxes');
    container.innerHTML = '';
    columnsCache = [];

    gridOptions.columnDefs.forEach((col) => {
        if (!col.field) return;

        columnsCache.push(col);

        const checked = !col.hide;

        container.innerHTML += `
            <div class="col-md-6 column-item" data-name="${(col.headerName || col.field).toLowerCase()}">
                <div class="card shadow-sm">
                    <div class="card-body py-2 px-3 d-flex justify-content-between align-items-center">
                        <span class="fw-semibold">
                            ${col.headerName || col.field}
                        </span>

                        <div class="form-check form-switch m-0">
                            <input
                                class="form-check-input"
                                type="checkbox"
                                ${checked ? 'checked' : ''}
                                onchange="toggleColumn('${col.field}', this.checked)"
                            >
                        </div>
                    </div>
                </div>
            </div>
        `;
    });

    new bootstrap.Modal(document.getElementById('columnsModal')).show();
}

function toggleColumn(field, visible) {
    apiGrid.applyColumnState({
        state: [{ colId: field, hide: !visible }],
    });

    saveColumnsState();
}
function filterColumns(text) {
    text = text.toLowerCase();
    document.querySelectorAll('.column-item').forEach((el) => {
        el.style.display = el.dataset.name.includes(text) ? '' : 'none';
    });
}
function formatBool($value) {
    return $value ? 'SI' : 'NO';
}
function exportExcel() {
    apiGrid.exportDataAsCsv({
        fileName: 'maniobras.csv',
    });
}
function updateColumnSwitches(visible) {
    document.querySelectorAll('#columnsCheckboxes input[type="checkbox"]').forEach((checkbox) => {
        checkbox.checked = visible;
    });

    saveColumnsState();
}

function showAllColumns() {
    const state = columnsCache.map((col) => ({
        colId: col.field,
        hide: false,
    }));

    apiGrid.applyColumnState({ state });
    updateColumnSwitches(true);
}

function hideAllColumns() {
    const state = columnsCache.map((col) => ({
        colId: col.field,
        hide: true,
    }));

    apiGrid.applyColumnState({ state });
    updateColumnSwitches(false);
}
function exportPDF() {
    const { jsPDF } = window.jspdf;
    const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'mm',
        format: [216, 356],
    });

    const booleanFields = ['BoletaLiberacion', 'DODA', 'BoletaPatio', 'FormatoCartaPorte', 'PreAlta', 'foto_patio'];

    const columns = apiGrid
        .getColumnDefs()
        .filter((c) => !c.hide)
        .map((c) => ({
            header: c.headerName || c.field,
            dataKey: c.field,
        }));

    const data = [];
    apiGrid.forEachNodeAfterFilterAndSort((node) => {
        data.push({ ...node.data });
    });

    doc.autoTable({
        columns,
        body: data,
        styles: { fontSize: 8 },

        didParseCell: function (hookData) {
            if (hookData.section !== 'body') return;

            const { cell, column, row } = hookData;
            const field = column.dataKey;

            if (booleanFields.includes(field)) {
                const value = row.raw[field];

                if (value) {
                    cell.text = ['SI'];
                    cell.styles.fillColor = [212, 237, 218]; // verde
                    cell.styles.textColor = [40, 167, 69];
                } else {
                    cell.text = ['NO'];
                    cell.styles.fillColor = [248, 215, 218]; // rojo
                    cell.styles.textColor = [220, 53, 69];
                }

                cell.styles.fontStyle = 'bold';
                cell.styles.halign = 'center';
            }
        },
    });

    doc.save('maniobras.pdf');
}

function saveColumnsState() {
    localStorage.setItem('maniobras_columns', JSON.stringify(apiGrid.getColumnState()));
}

function getContenedoresPendientes(estatus = 'Local') {
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/viajes/documents/pending-local',
        type: 'post',
        data: { _token, estatus },
        beforeSend: () => {},
        success: (response) => {
            if (response.length > 0) {
                btnDocumets.forEach((btn) => (btn.disabled = false));
            }
            apiGrid.setGridOption('rowData', response);
        },
        error: () => {},
    });
}
function getContenedoresPendientesPatio(estatus = 'Local') {
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/viajes/documents/pending-local-patio',
        type: 'post',
        data: { _token, estatus },
        beforeSend: () => {},
        success: (response) => {
            if (response.length > 0) {
                btnDocumets.forEach((btn) => (btn.disabled = false));
            }
            apiGrid.setGridOption('rowData', response);
        },
        error: () => {},
    });
}

function goToUploadDocuments() {
    let contenedor = apiGrid.getSelectedRows();
    if (contenedor.length != 1) {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: false,
            progressBar: true,
            positionClass: 'toastr-top-center',
            preventDuplicates: false,
            onclick: null,
            showDuration: '1500',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
        };

        toastr.error(`Debe seleccionar unicamente un contenedor para esta opción`);
        return false;
    }
    let numContenedor = null;
    contenedor.forEach((c) => (numContenedor = c.NumContenedor));

    let titleFileUploader = document.querySelector('#titleFileUploader');
    titleFileUploader.textContent = numContenedor.toUpperCase();
    localStorage.setItem('numContenedor', numContenedor);
    const modalElement = document.getElementById('kt_modal_fileuploader');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
}

function cancelarViajeQuestion() {
    let contenedor = apiGrid.getSelectedRows();
    if (contenedor.length != 1) {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: false,
            progressBar: true,
            positionClass: 'toastr-top-center',
            preventDuplicates: false,
            onclick: null,
            showDuration: '1500',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
        };

        toastr.error(`Debe seleccionar unicamente un contenedor para esta opción`);
        return false;
    }
    Swal.fire({
        title: '¿Desea cancelar el viaje seleccionado?',
        text: 'Está a punto de cancelar el viaje, si está seguro haga click en "Si, Cancelar"',
        icon: 'question',
        confirmButtonText: 'Si, Cancelar',
        cancelButtonText: 'No quiero cancelarlo',
        showCancelButton: true,
    }).then((respuesta) => {
        if (respuesta.isConfirmed) {
            cancelarViajeConfirm();
        }
    });
}

function cancelarViajeConfirm() {
    let contenedor = apiGrid.getSelectedRows();
    if (contenedor.length <= 0) return;

    let numContenedor = null;
    contenedor.forEach((c) => (numContenedor = c.NumContenedor));

    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    $.ajax({
        url: '/viajes/cancelar',
        type: 'post',
        data: { _token, numContenedor },
        beforeSend: () => {},
        success: (response) => {
            Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);
        },
        error: (err) => {
            Swal.fire('Ocurrio un error', 'No se pudo procesar la solicitud', 'error');
        },
    });
}

function viajeFull() {
    let seleccion = apiGrid.getSelectedRows();

    if (seleccion.length > 2) {
        Swal.fire(
            'Maximo 2 contenedores',
            'Lo sentimos, solo puede seleccionar maximo 2 contenedores, estos deben ser de un mismo cliente',
            'warning',
        );
        return false;
    }

    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Quiere unir los contenedores seleccionados en un viaje Full.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'No, cancelar',
        reverseButtons: true, // Opcional: invierte el orden de los botones
    }).then((result) => {
        if (result.isConfirmed) {
            let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            $.ajax({
                url: '/cotizaciones/transformar/full',
                type: 'post',
                data: { _token, seleccion },
                beforeSend: () => {},
                success: (response) => {
                    Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);
                    if (response.TMensaje == 'success') {
                        getCotizacionesList();
                    }
                },
                error: () => {},
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Acción si el usuario canceló
            console.log('El usuario canceló');
        }
    });
}

function convertirForaneo() {
    let seleccion = apiGrid.getSelectedRows();

    //   if (seleccion.length > 2) {
    //     Swal.fire('Maximo 2 contenedores', 'Lo sentimos, solo puede seleccionar maximo 2 contenedores, estos deben ser de un mismo cliente', 'warning')
    //     return false
    //   }

    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Esta accion convertirá los contenedores seleccionados a viaje foráneo.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'No, cancelar',
        reverseButtons: true, // Opcional: invierte el orden de los botones
    }).then((result) => {
        if (result.isConfirmed) {
            let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            $.ajax({
                url: '/cotizaciones/transformar/foraneo',
                type: 'post',
                data: { _token, seleccion },
                beforeSend: () => {},
                success: (response) => {
                    Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);
                    if (response.TMensaje == 'success') {
                        getContenedoresPendientesPatio();
                    }
                },
                error: () => {},
            });
        } else if (result.dismiss === Swal.DismissReason.cancel) {
            // Acción si el usuario canceló
            console.log('El usuario canceló');
        }
    });
}

function fileManager() {
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var url = '/viajes/file-manager-local';

    let contenedor = apiGrid.getSelectedRows();

    if (contenedor.length != 1) {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: false,
            progressBar: true,
            positionClass: 'toastr-top-center',
            preventDuplicates: false,
            onclick: null,
            showDuration: '1500',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
        };

        toastr.error(`Debe seleccionar unicamente un contenedor para esta opción`);
        return false;
    }

    let numContenedor = null;
    contenedor.forEach((c) => (numContenedor = c.NUM_CONTENEDOR_REFER));

    var form = $(
        '<form action="' +
            url +
            '" method="post" >' +
            '<input type="hidden" name="numContenedor" value="' +
            numContenedor +
            '" />' +
            '<input type="hidden" name="_token" value="' +
            _token +
            '" />' +
            '</form>',
    );

    $('body').append(form);
    form.submit();

    setTimeout(() => {
        if (form) {
            form.remove();
        }
    }, 1000);
}

function editarViaje() {
    const _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const url = '/viajes/editar-local';

    let seleccionados = apiGrid.getSelectedRows();

    if (seleccionados.length !== 1) {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: false,
            progressBar: true,
            positionClass: 'toastr-top-center',
            preventDuplicates: false,
            onclick: null,
            showDuration: '1500',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
        };

        toastr.error(`Debe seleccionar unicamente un contenedor para esta opción`);
        return false;
    }

    const viaje = seleccionados[0];

    // Si es FULL y tiene más de un viaje asociado, mostrar modal
    if (viaje.tipo === 'Full' && Array.isArray(viaje.viajesFull) && viaje.viajesFull.length > 1) {
        const contenedorOpciones = document.getElementById('contenedorOpciones');
        contenedorOpciones.innerHTML = ''; // Limpia opciones anteriores

        viaje.viajesFull.forEach((v) => {
            const btn = document.createElement('button');
            btn.classList.add('btn', 'btn-outline-primary', 'mb-2', 'w-100');
            btn.textContent = `Editar contenedor: ${v.NumContenedor}`;
            btn.onclick = () => {
                const form = $(
                    '<form action="' +
                        url +
                        '" method="post">' +
                        '<input type="hidden" name="numContenedor" value="' +
                        v.NumContenedor +
                        '" />' +
                        '<input type="hidden" name="_token" value="' +
                        _token +
                        '" />' +
                        '</form>',
                );
                $('body').append(form);
                form.submit();

                setTimeout(() => {
                    if (form) form.remove();
                }, 1000);
            };
            contenedorOpciones.appendChild(btn);
        });

        const modal = new bootstrap.Modal(document.getElementById('modalSeleccionContenedor'));
        modal.show();
        return;
    }

    //  Comportamiento original si NO es FULL o solo hay un contenedor
    let numContenedor = null;
    seleccionados.forEach((c) => (numContenedor = c.NUM_CONTENEDOR_REFER));

    const form = $(
        '<form action="' +
            url +
            '" method="post" >' +
            '<input type="hidden" name="numContenedor" value="' +
            numContenedor +
            '" />' +
            '<input type="hidden" name="_token" value="' +
            _token +
            '" />' +
            '</form>',
    );

    $('body').append(form);
    form.submit();

    setTimeout(() => {
        if (form) {
            form.remove();
        }
    }, 1000);
}

function getFilesCFDI() {
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var url = '/viajes/file-manager/cfdi-files';

    let contenedores = apiGrid.getSelectedRows();
    if (contenedores.length <= 0) {
        toastr.options = {
            closeButton: true,
            debug: false,
            newestOnTop: false,
            progressBar: true,
            positionClass: 'toastr-top-center',
            preventDuplicates: false,
            onclick: null,
            showDuration: '1500',
            hideDuration: '1000',
            timeOut: '5000',
            extendedTimeOut: '1000',
            showEasing: 'swing',
            hideEasing: 'linear',
            showMethod: 'fadeIn',
            hideMethod: 'fadeOut',
        };

        toastr.info(`Debe seleccionar al menos un contenedor`);
        return false;
    }

    $.ajax({
        url: url,
        type: 'post',
        data: { _token, contenedores },
        beforeSend: () => {},
        success: (response) => {
            if (response.success) {
                const fileURL = `viajes/file-manager/cfdi-files/${response.zipUrl}`;
                const link = document.createElement('a');
                link.href = fileURL;
                document.body.appendChild(link);
                link.click();

                document.body.removeChild(link);
            } else {
                Swal.fire('Ha ocurrido un error', 'No se pudo descargar el archivo', 'warning');
            }
        },
        error: () => {},
    });
}
function mostrarDescripcionEstatus(select) {
    const opcion = select.options[select.selectedIndex];
    const descripcion = opcion.dataset.descripcion;

    document.getElementById('descripcionEstatus').innerText =
        descripcion || 'Seleccione un estatus para ver la descripción';
}
function ModalCambiarEstatus() {
    let contenedores = apiGrid.getSelectedRows();

    if (contenedores.length == 0) {
        Swal.fire('Validacion', 'Seleccione al menos un contenedor de la tabla...', 'warning');
        return false;
    }

    let maniobraId = contenedores[0].id;
    let statusActual = contenedores[0].estatus_maniobra_id;

    document.getElementById('nota_estatus').value = '';

    document.getElementById('estatus_id').value = statusActual;

    document.getElementById('maniobra_id').value = maniobraId;

    const modal = new bootstrap.Modal(document.getElementById('modalCambiarEstatus'));
    modal.show();
}

function guardarCambioEstatus() {
    const maniobraId = document.getElementById('maniobra_id').value;
    const estatusId = document.getElementById('estatus_id').value;
    const notaEstatus = document.getElementById('nota_estatus').value;
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (!estatusId) {
        alert('Seleccione un estatus');
        return;
    }
    try {
        fetch(`viajes/maniobras/cambiar-estatus`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': _token,
            },
            body: JSON.stringify({
                estatus_id: estatusId,
                idCotizacion: maniobraId, //cotizacion id
                notaEstatus: notaEstatus,
            }),
        })
            .then((r) => r.json())
            .then((resp) => {
                if (resp.TMensaje === 'success') {
                    Swal.fire(resp.Titulo, resp.Mensaje, 'success').then(() => {
                        getContenedoresPendientes('all');
                    });

                    //cerramos modal
                    const modalElement = document.getElementById('modalCambiarEstatus');
                    const modal = bootstrap.Modal.getInstance(modalElement);
                    modal.hide();
                }
            });
    } catch (e) {
        console.error('Error al cambiar estatus:', e);
        Swal.fire('Error', e.Mensaje, 'error');
    }
}

//document.querySelector('#btnDocs').addEventListener('click', goToUploadDocuments);
