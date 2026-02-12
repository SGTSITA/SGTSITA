let fechaInicioViajes;
let fechaFinViajes;

$(document).ready(function () {
    let start = moment().subtract(1, 'month');
    let end = moment();

    function setFechas(start, end) {
        fechaInicioViajes = start.format('YYYY-MM-DD');
        fechaFinViajes = end.format('YYYY-MM-DD');

        $('#rangoFechasViajes').val(start.format('DD/MM/YYYY') + ' - ' + end.format('DD/MM/YYYY'));

        getContenedoresPendientes('all');
    }

    $('#rangoFechasViajes').daterangepicker(
        {
            startDate: start,
            endDate: end,
            autoApply: true,
            locale: {
                format: 'DD/MM/YYYY',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: [
                    'Enero',
                    'Febrero',
                    'Marzo',
                    'Abril',
                    'Mayo',
                    'Junio',
                    'Julio',
                    'Agosto',
                    'Septiembre',
                    'Octubre',
                    'Noviembre',
                    'Diciembre',
                ],
                firstDay: 1,
            },
            ranges: {
                Hoy: [moment(), moment()],
                'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                'Este mes': [moment().startOf('month'), moment().endOf('month')],
                'Mes anterior': [
                    moment().subtract(1, 'month').startOf('month'),
                    moment().subtract(1, 'month').endOf('month'),
                ],
            },
        },
        setFechas,
    );

    // Ejecuta al cargar
    setFechas(start, end);
});

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

const ragCellClassRules = {
    'badge badge-light-info fs-base': (params) => params.value === 'Viaje solicitado',
    'badge badge-light-warning fs-base': (params) => params.value === 'Documentos Faltantes',
    'badge badge-light-success fs-base': (params) => params.value === 'Aprobada',
    'badge badge-light-primary fs-base': (params) => params.value === 'Planeado',
    'badge badge-light-danger fs-base': (params) => params.value === 'Cancelada',
};

const gridOptions = {
    pagination: true,
    paginationPageSize: 500,
    paginationPageSizeSelector: [200, 500, 1000],
    rowSelection: {
        mode: 'multiRow',
        headerCheckbox: true,
    },
    rowData: [],

    columnDefs: [
        { field: 'id', hide: true },
        { field: 'tipo', hide: true },
        {
            field: 'BoletaLiberacion',
            wrapHeaderText: true,
            autoHeaderHeight: true,
            width: 110,
            headerClass: 'header-center',
            cellRenderer: MissionResultRenderer,
        },
        { field: 'DODA', width: 80, cellRenderer: MissionResultRenderer },
        {
            field: 'FormatoCartaPorte',
            wrapHeaderText: true,
            autoHeaderHeight: true,
            width: 100,
            headerClass: 'header-center',
            cellRenderer: MissionResultRenderer,
        },
        {
            field: 'cartaPortepdf',
            headerName: 'Carta porte pdf',
            wrapHeaderText: true,
            autoHeaderHeight: true,
            width: 100,
            headerClass: 'header-center',
            cellRenderer: MissionResultRenderer,
        },
        {
            field: 'carta_porte_xml',
            headerName: 'Carta porte xml',
            wrapHeaderText: true,
            autoHeaderHeight: true,
            width: 100,
            headerClass: 'header-center',
            cellRenderer: MissionResultRenderer,
        },
        {
            field: 'FormatoCartaPorte',
            wrapHeaderText: true,
            autoHeaderHeight: true,
            width: 100,
            headerClass: 'header-center',
            cellRenderer: MissionResultRenderer,
        },
        { field: 'PreAlta', width: 100, cellRenderer: MissionResultRenderer },
        { field: 'foto_patio', headerName: 'Foto Patio', width: 100, cellRenderer: MissionResultRenderer },
        { field: 'docEir', headerName: 'EIR', width: 70, cellRenderer: MissionResultRenderer },
        {
            field: 'NumContenedor',
            sortable: true,
            filter: true,
            minWidth: 150,
            autoHeight: true, // Permite que la fila se ajuste en altura
            cellStyle: (params) => {
                const styles = {
                    'white-space': 'normal',
                    'line-height': '1.5',
                };

                // Si la cotización es tipo "Full", aplicar fondo
                if (params.data.tipo === 'Full') {
                    styles['background-color'] = '#ffe5b4';
                }

                return styles;
            },
        },
        { field: 'transportista', width: 200, filter: true, floatingFilter: true },
        { field: 'Estatus', width: 100, filter: true, floatingFilter: true, cellClassRules: ragCellClassRules },
        { field: 'Origen', width: 100, filter: true, floatingFilter: true },
        { field: 'Destino', width: 100 },
        { field: 'Peso', width: 100 },
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

function getContenedoresPendientes(estatus = 'Documentos Faltantes') {
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/viajes/documents/pending',
        type: 'post',
        data: { _token, estatus, fechaInicioViajes, fechaFinViajes },
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
                        getContenedoresPendientes('all');
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

function cancelarFull() {
    let seleccion = apiGrid.getSelectedRows();

    if (seleccion.length != 1) {
        Swal.fire('Seleccione un contenedor', 'Debe seleccionar un contenedor que sea Full', 'warning');
        return false;
    }
    if (!seleccion[0].tipo || seleccion[0].tipo != 'Full') {
        Swal.fire('Contenedor no es Full', 'El contenedor seleccionado no es un viaje Full', 'warning');
        return false;
    }

    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Quiere separar los contenedores del viaje Full.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, continuar',
        cancelButtonText: 'No, cancelar',
        reverseButtons: true,
    }).then((result) => {
        if (result.isConfirmed) {
            let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            $.ajax({
                url: '/cotizaciones/transformar/cancelar-full',
                type: 'post',
                data: { _token, seleccion },
                beforeSend: () => {},
                success: (response) => {
                    Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);
                    if (response.TMensaje == 'success') {
                        getContenedoresPendientes('all');
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
    var url = '/viajes/file-manager';

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
    const url = '/viajes/editar';

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
    const contenedores = viaje.NumContenedor?.split(' ')
        .map((c) => c.trim())
        .filter(Boolean);

    if (contenedores.length > 1) {
        const contenedorOpciones = document.getElementById('contenedorOpciones');
        contenedorOpciones.innerHTML = ''; // Limpia opciones anteriores

        contenedores.forEach((numContenedor) => {
            const btn = document.createElement('button');
            btn.classList.add('btn', 'btn-outline-primary', 'mb-2', 'w-100');
            btn.textContent = `Editar contenedor: ${numContenedor}`;
            btn.onclick = () => {
                const form = $(
                    '<form action="' +
                        url +
                        '" method="post">' +
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
    seleccionados.forEach((c) => (numContenedor = c.NumContenedor));

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

document.querySelector('#btnDocs').addEventListener('click', goToUploadDocuments);
