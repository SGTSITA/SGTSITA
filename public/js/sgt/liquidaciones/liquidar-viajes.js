const gastosFormFields = [
    { field: 'motivo', id: 'motivo', label: 'Descripción', required: true, type: 'text' },
    { field: 'monto1', id: 'monto1', label: 'Monto', required: true, type: 'money' },
    { field: 'categoria_movimiento', id: 'categoria_movimiento', label: 'Categoría', required: true, type: 'text' },
    { field: 'fecha_movimiento', id: 'fecha_movimiento', label: 'Fecha movimiento', required: true, type: 'text' },
    { field: 'fecha_aplicacion', id: 'fecha_aplicacion', label: 'Fecha aplicación', required: true, type: 'text' },
    { field: 'id_banco1', id: 'id_banco1', label: 'Fecha aplicación', required: true, type: 'text' },
];

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
            '<span class="svg-icon svg-icon-muted svg-icon-2hx"><svg width="23" height="24" viewBox="0 0 23 24" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M21 13V13.5C21 16 19 18 16.5 18H5.6V16H16.5C17.9 16 19 14.9 19 13.5V13C19 12.4 19.4 12 20 12C20.6 12 21 12.4 21 13ZM18.4 6H7.5C5 6 3 8 3 10.5V11C3 11.6 3.4 12 4 12C4.6 12 5 11.6 5 11V10.5C5 9.1 6.1 8 7.5 8H18.4V6Z" fill="currentColor"/><path opacity="0.3" d="M21.7 6.29999C22.1 6.69999 22.1 7.30001 21.7 7.70001L18.4 11V3L21.7 6.29999ZM2.3 16.3C1.9 16.7 1.9 17.3 2.3 17.7L5.6 21V13L2.3 16.3Z" fill="currentColor"/></svg></span></span>';
        button.className = 'btn btn-sm bg-gradient-success';
        button.style.fontSize = '10px';
        button.style.padding = '2px 6px';
        button.style.lineHeight = '1';

        const NumContenedorValue = params.data.NumContenedor;

        this.eventListener = () => assignEmpresa(NumContenedorValue);
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

const currencyFormatter = (value) => {
    return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(value);
};

const formatFecha = (params) => {
    if (!params) return '';
    const [year, month, day] = params.split('-'); // Divide YYYY-MM-DD
    return `${day}/${month}/${year}`; // Retorna en formato d/m/Y
};

const gridOptions = {
    pagination: true,
    paginationPageSize: 10,
    paginationPageSizeSelector: [10, 20, 50, 100],
    rowSelection: {
        mode: 'multiRow',
        headerCheckbox: true,
    },
    rowData: [],

    columnDefs: [
        { field: 'IdAsignacion', hide: true },
        { field: 'IdOperador', hide: true },
        { field: 'IdContenedor', hide: true },
        { field: 'ContenedorPrincipal', hide: true },
        { field: 'Contenedores' },
        {
            field: 'SueldoViaje',
            width: 150,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: 'right' },
        },
        {
            field: 'DineroViaje',
            width: 150,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: 'right' },
        },
        {
            field: 'GastosJustificados',
            width: 150,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: 'right' },
        },
        {
            field: 'MontoPago',
            width: 150,
            valueFormatter: (params) => currencyFormatter(params.value),
            cellStyle: { textAlign: 'right' },
        },
        { field: 'FechaInicia', width: 150 },
        { field: 'FechaTermina', width: 150 },
    ],

    localeText: localeText,
};

const myGridElement = document.querySelector('#myGrid');
let apiGrid = agGrid.createGrid(myGridElement, gridOptions);
// const gridInstance = new agGrid.Grid(myGridElement, gridOptions);

var paginationTitle = document.querySelector('#ag-32-label');
paginationTitle.textContent = 'Registros por página';

let IdContenedor = null;
let IdOperador = document.querySelector('#IdOperador');
let dTotalPago = document.querySelector('#totalPago');
let dNumViajes = document.querySelector('#numViajes');
let btnSummaryPayment = document.querySelector('#btnSummaryPayment');
let sumaSalario = document.querySelector('#sumaSalario');
let sumaDineroViaje = document.querySelector('#sumaDineroViaje');
let sumaJustificados = document.querySelector('#sumaJustificados');
let totalPrestamo = document.querySelector('#totalPrestamo');
let sumaPago = document.querySelector('#sumaPago');
const sumaPrestamos = document.querySelector('#sumaPrestamos');
let contadorContenedores = document.querySelector('#contadorContenedores');
let btnConfirmaPago = document.querySelector('#btnConfirmaPago');
let btnJustificar = document.querySelector('#btnJustificar');
let btnDineroViaje = document.querySelector('#btnDineroViaje');
let montoPagoPrestamo = document.querySelector('#montoPagoPrestamo');

let totalMontoPago = 0;
let saldoActual = 0;
let totalPagoPrestamo = 0;

const validFeedBack = document.querySelector('#valid-feedback');
const invalidFeedBack = document.querySelector('#invalid-feedback');

function mostrarViajesOperador(operador) {
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    $.ajax({
        url: '/liquidaciones/viajes/operador',
        type: 'post',
        data: { _token, operador },
        beforeSend: () => {
            mostrarLoading('Obteniendo viajes');
        },
        success: (response) => {
            apiGrid.setGridOption('rowData', response.viajes);
            dTotalPago.textContent = moneyFormat(response.totalPago);
            dNumViajes.textContent = response.numViajes;
            saldoActual = response.prestamos.reduce((suma, p) => suma + parseFloat(p.saldo_actual), 0);
            totalPrestamo.textContent = moneyFormat(saldoActual);

            let dataCContenedores = response.data;

            if (dataCContenedores.length > 0) {
                let saved = JSON.parse(localStorage.getItem('justificaciones')) || {};

                saved = [];

                dataCContenedores.forEach((contenedor) => {
                    if (contenedor.justificacion && contenedor.justificacion.length > 0) {
                        contenedor.justificacion.forEach((c) => {
                            saved.push({
                                [`id_registro|${contenedor.id_contenedor}`]: c.id,
                                [`motivo|${contenedor.id_contenedor}`]: c.descripcion_gasto,
                                [`monto|${contenedor.id_contenedor}`]: parseFloat(c.monto).toFixed(2),
                            });
                        });
                    } else {
                        saved.push({
                            [`id_registro|${contenedor.id_contenedor}`]: null,
                            [`motivo|${contenedor.id_contenedor}`]: '',
                            [`monto|${contenedor.id_contenedor}`]: '',
                        });
                    }
                });

                console.log('Justificaciones cargadas desde backend:', saved);
                localStorage.setItem('justificaciones', JSON.stringify(saved));
            }

            if (saldoActual <= 0) {
                montoPagoPrestamo.disabled = true;
                montoPagoPrestamo.classList.add('is-invalid');
                montoPagoPrestamo.value = 0;
            } else {
            }

            ocultarLoading();
        },
        error: () => {
            ocultarLoading();
        },
    });
}

montoPagoPrestamo.addEventListener('input', (e) => {
    let montoPago = parseFloat(e.target.value) || 0;
    if (montoPago > 0 && montoPago <= saldoActual) {
        montoPagoPrestamo.classList.add('is-valid');
        montoPagoPrestamo.classList.remove('is-invalid');
        validFeedBack.textContent = `${moneyFormat(saldoActual - montoPago)} pendiente despues de la operación.`;
    } else if (montoPago > saldoActual) {
        montoPagoPrestamo.classList.add('is-invalid');
        montoPagoPrestamo.classList.remove('is-valid');
        invalidFeedBack.textContent = `El monto de pago es mayor al saldo actual del prestamo`;
    } else if (montoPago == 0) {
        montoPagoPrestamo.classList.remove('is-invalid');
        montoPagoPrestamo.classList.remove('is-valid');
    }
    totalPagoPrestamo = montoPago;
    sumaPago.textContent = moneyFormat(totalMontoPago - montoPago);
    sumaPrestamos.textContent = `- ${moneyFormat(montoPago)}`;
});

function summaryPay() {
    let pagoContenedores = apiGrid.getSelectedRows();
    if (pagoContenedores.length <= 0) {
        Swal.fire('Seleccione contenedores', 'Debe seleccionar al menos un contenedor de la lista', 'warning');
        return;
    }

    let suma = 0;
    let sumSalario = 0;
    let sumJustificado = 0;
    let sumDineroViaje = 0;

    pagoContenedores.forEach((c) => {
        suma = suma + parseFloat(c.MontoPago);
        sumSalario = sumSalario + parseFloat(c.SueldoViaje);
        sumDineroViaje = parseFloat(sumDineroViaje ?? 0) + parseFloat(c.DineroViaje ?? 0);
        sumJustificado = sumJustificado + parseFloat(c.GastosJustificados);
    });

    totalMontoPago = suma;

    sumaPago.textContent = moneyFormat(suma - totalPagoPrestamo);
    sumaSalario.textContent = moneyFormat(sumSalario);
    sumaDineroViaje.textContent = `- ${moneyFormat(sumDineroViaje)}`;
    sumaJustificados.textContent = `+ ${moneyFormat(sumJustificado)}`;
    contadorContenedores.textContent = `${pagoContenedores.length} de ${dNumViajes.textContent}`;

    const modalElement = document.getElementById('exampleModal');
    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
}

function aplicarPago() {
    let banco = document.getElementById('cmbBankOne');
    let FechaAplicacionPago = document.getElementById('FechaAplicacionPago').value;
    if (banco.value == 'null') {
        Swal.fire('Seleccione cuenta de retiro', 'Por favor seleccione la cuenta de retiro', 'warning');
        return;
    }
    if (!FechaAplicacionPago) {
        Swal.fire('Seleccione Fecha aplicacion', 'Por favor seleccione fecha aplicacion para el pago', 'warning');
        return;
    }

    let pagoContenedores = apiGrid.getSelectedRows();
    let bancoId = banco.value;
    var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let _IdOperador = IdOperador.value;

    $.ajax({
        url: '/liquidaciones/viajes/aplicar-pago',
        type: 'post',
        data: {
            _token,
            _IdOperador,
            pagoContenedores,
            bancoId,
            totalMontoPago,
            totalPagoPrestamo,
            FechaAplicacionPago,
        },
        beforeSend: () => {},
        success: (response) => {
            Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);
            if (response.TMensaje == 'success') {
                location.reload();
            }
        },
        error: () => {
            Swal.fire('Error', 'Ha ocurrido un error', 'danger');
        },
    });
}

btnSummaryPayment.addEventListener('click', () => {
    summaryPay();
});

btnConfirmaPago.addEventListener('click', () => {
    aplicarPago();
});

btnJustificar.addEventListener('click', () => {
    openModalJustificar('justificar-multiple');
});

btnDineroViaje.addEventListener('click', () => {
    openModalJustificar('dinero_viaje');
});

function openModalJustificar(accion = 'justificar') {
    let justificaContenedores = apiGrid.getSelectedRows();
    let modalElement = null;

    if (justificaContenedores.length != 1 && accion != 'justificar-multiple') {
        Swal.fire('Seleccione un contenedor', 'Debe seleccionar solo un contenedor de la lista', 'warning');
        return false;
    } else if (justificaContenedores.length < 1 && accion == 'justificar-multiple') {
        Swal.fire('Seleccione contenedores', 'Debe seleccionar al menos un contenedor de la lista', 'warning');
        return false;
    }

    if (accion == 'justificar-multiple') {
        modalElement = document.getElementById('modal-justificar-multiple');

        // Ajuste del ancho del modal-dialog, no del grid
        const modalDialog = modalElement.querySelector('.modal-dialog');
        modalDialog.style.maxWidth = `${Math.min(200 + justificaContenedores.length * 250, 1200) + 20}px`;

        // Crear el grid, solo ajusta altura, no ancho
        crearPivotTable(justificaContenedores);
    } else {
        modalElement = document.getElementById('modal-justificar');
    }

    // Ajuste de títulos y campos
    document.querySelector('#actionTitle').textContent =
        accion == 'justificar' ? 'Justificar gastos' : 'Registro dinero viaje';
    const bancoRetiro = document.querySelector('#bancoRetiro');
    accion == 'justificar' ? bancoRetiro.classList.add('d-none') : bancoRetiro.classList.remove('d-none');
    document.getElementById('btnJustificar').setAttribute('data-sgt-action', accion);

    const bootstrapModal = new bootstrap.Modal(modalElement);
    bootstrapModal.show();
}

function crearPivotTable(gridselectedrows) {
    const container = document.getElementById('gridJustificar');

    // Leer justificaciones guardadas
    let justificaciones = [];
    const saved = localStorage.getItem('justificaciones');
    if (saved) {
        try {
            const parsed = JSON.parse(saved);
            if (Array.isArray(parsed)) justificaciones = parsed;
        } catch (e) {
            console.error('Error al leer localStorage:', e);
        }
    }

    const contenedoresSeleccionados = gridselectedrows.map((c) => c.IdContenedor);
    let dataParaJustificar = [];

    // Agrupar justificaciones por contenedor
    const agrupadoPorContenedor = {};
    contenedoresSeleccionados.forEach((id) => {
        agrupadoPorContenedor[id] = justificaciones.filter((item) =>
            Object.keys(item).some((k) => k.includes(`|${id}`)),
        );
    });

    // Calcular el número máximo de filas necesarias
    const maxFilas = Math.max(...contenedoresSeleccionados.map((id) => agrupadoPorContenedor[id].length), 10);

    // Crear estructura inicial de datos
    for (let i = 0; i < maxFilas; i++) {
        let fila = {};
        contenedoresSeleccionados.forEach((id) => {
            const registro = agrupadoPorContenedor[id][i] || {};
            fila[`id_registro|${id}`] = registro[`id_registro|${id}`] ?? '';
            fila[`motivo|${id}`] = registro[`motivo|${id}`] ?? '';
            fila[`monto|${id}`] = registro[`monto|${id}`] ?? '';
        });
        dataParaJustificar.push(fila);
    }

    // Calcular totales iniciales
    const totales = {};
    contenedoresSeleccionados.forEach((id) => {
        let total = 0;
        dataParaJustificar.forEach((fila) => {
            const monto = parseFloat(fila[`monto|${id}`]);
            if (!isNaN(monto)) total += monto;
        });
        totales[id] = total;
    });

    const columns = [];
    const nestedHeadersLevel1 = [];
    const nestedHeadersLevel2 = [];

    gridselectedrows.forEach((c) => {
        const totalFormateado = new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2,
        }).format(totales[c.IdContenedor] || 0);

        nestedHeadersLevel1.push({
            label: `${c.Contenedores}<br><span style="font-size: 14px; color: #008000;">${totalFormateado}</span>`,
            colspan: 2,
        });

        nestedHeadersLevel2.push('Concepto', 'Monto');

        columns.push(
            { data: `motivo|${c.IdContenedor}`, editor: 'text', className: 'htCenter htMiddle', width: 150 },
            {
                data: `monto|${c.IdContenedor}`,
                type: 'numeric',
                className: 'htRight htMiddle',
                width: 100,
                numericFormat: { pattern: '0,0.00', culture: 'es-MX' },
            },
        );
    });

    let anchoGrid = columns.reduce((sum, col) => sum + (col.width || 100), 0) + 50;
    if (gridselectedrows.length === 1) anchoGrid += 100;

    container.style.width = `${anchoGrid}px`;
    const alturaTabla = Math.min(260 + gridselectedrows.length * 30, 800);
    container.style.height = `${alturaTabla + 60}px`;

    function recalcularTotales() {
        contenedoresSeleccionados.forEach((id) => {
            let total = 0;
            dataParaJustificar.forEach((fila) => {
                const monto = parseFloat(fila[`monto|${id}`]);
                if (!isNaN(monto)) total += monto;
            });
            totales[id] = total;
        });

        window.hotInstance.updateSettings({
            nestedHeaders: [
                [
                    ...gridselectedrows.map((c) => ({
                        label: `${c.Contenedores}<br><span style="font-size: 14px; color: #008000;">${totales[c.IdContenedor].toLocaleString('es-MX', { style: 'currency', currency: 'MXN', minimumFractionDigits: 2 })}</span>`,
                        colspan: 2,
                    })),
                ],
                nestedHeadersLevel2,
            ],
        });
    }

    if (window.hotInstance) {
        window.hotInstance.updateSettings({
            data: dataParaJustificar,
            columns,
            nestedHeaders: [nestedHeadersLevel1, nestedHeadersLevel2],
            stretchH: 'none',
            height: alturaTabla,
            viewportRowRenderingOffset: 0,
        });
        window.hotInstance.render();
        window.hotInstance.refreshDimensions();
    } else {
        window.hotInstance = new Handsontable(container, {
            data: dataParaJustificar,
            columns,
            nestedHeaders: [nestedHeadersLevel1, nestedHeadersLevel2],
            rowHeaders: true,
            stretchH: 'none',
            manualColumnResize: true,
            manualRowResize: true,
            contextMenu: true,
            height: alturaTabla,
            viewportRowRenderingOffset: 0,
            licenseKey: 'non-commercial-and-evaluation',
            afterChange: function (changes, source) {
                if (source === 'loadData') return;
                guardarJustificacionesEnLocalStorage();
                recalcularTotales();
            },
        });
    }

    recalcularTotales();

    const modal = document.getElementById('modal-justificar-multiple');
    const modalDialog = modal.querySelector('.modal-dialog');
    modalDialog.style.maxWidth = `${anchoGrid + 60}px`;

    modal.addEventListener(
        'shown.bs.modal',
        () => {
            window.hotInstance.render();
            window.hotInstance.refreshDimensions();
        },
        { once: true },
    );
}
function guardarJustificacionesEnLocalStorage() {
    const data = window.hotInstance.getSourceData();
    localStorage.setItem('justificaciones', JSON.stringify(data));
    console.log('Datos guardados en localStorage');
}
document.getElementById('btnLimpiarTabla').addEventListener('click', function () {
    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Se eliminarán todos los datos capturados en la tabla.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#3085d6',
        confirmButtonText: 'Sí, limpiar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            let dataParaJustificar = window.hotInstance.getSourceData();
            dataParaJustificar = dataParaJustificar.map((fila) => {
                const nuevaFila = {};
                Object.keys(fila).forEach((k) => (nuevaFila[k] = ''));
                return nuevaFila;
            });

            window.hotInstance.loadData(dataParaJustificar);
            limpiarJustificacionesLocalStorage();
        }
    });
});

function limpiarJustificacionesLocalStorage() {
    localStorage.removeItem('justificaciones');
}
document.getElementById('btnAddRow').addEventListener('click', () => {
    if (window.hotInstance) {
        hotInstance.alter('insert_row', hotInstance.countRows());
    } else {
        console.log('La tabla no está inicializada', 'error');
    }
});

function justificarGasto() {
    let monto = document.getElementById('txtMonto').value;
    if (monto.length == 0) {
        Swal.fire('Ingrese Monto', 'Por favor introduzca el monto del gasto que está justificando', 'warning');
        return false;
    }

    let txtDescripcion = document.getElementById('txtDescripcion').value;

    if (txtDescripcion.length == 0) {
        Swal.fire(
            'Ingrese descripción',
            'Por favor introduzca la descripción del gasto que está justificando',
            'warning',
        );
        return false;
    }

    let justificaContenedores = apiGrid.getSelectedRows();
    let DineroViaje = 0;
    let GastosJustificados = 0;
    let numContenedor;
    let IdOperador = null;
    justificaContenedores.forEach((cn) => {
        DineroViaje = cn.DineroViaje;
        GastosJustificados = cn.GastosJustificados || 0;
        numContenedor = cn.ContenedorPrincipal;
        IdOperador = cn.IdOperador;
    });

    let sinJustificar = DineroViaje - GastosJustificados;
    let montoJustificacion = reverseMoneyFormat(document.getElementById('txtMonto').value);

    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    if (txtDescripcion.length == 0) {
        Swal.fire(
            'Ingrese descripcion',
            'Por favor introduzca la descripción del gasto que está justificando',
            'warning',
        );
        return false;
    }

    let payload = { _token, montoJustificacion, numContenedor, txtDescripcion, sinJustificar };

    let accion = document.getElementById('btnJustificar').dataset.sgtAction;
    let url = null;
    if (accion == 'dinero_viaje') {
        let cmbBancoRetiro = document.querySelector('#cmbBancoRetiro');
        let FechaAplicacionDinero = document.getElementById('FechaAplicacionDinero').value;
        if (cmbBancoRetiro.value == 'null')
            return Swal.fire(
                'Seleccione cuenta retiro',
                'Por favor seleccione banco de donde se retira el recurso',
                'warning',
            );
        if (!FechaAplicacionDinero)
            return Swal.fire(
                'Fecha aplicacion',
                'Por favor seleccione fecha aplicacion para movimeinto en banco',
                'warning',
            );

        payload = { ...payload, bank: cmbBancoRetiro.value, FechaAplicacionDinero: FechaAplicacionDinero };
        url = '/liquidaciones/viajes/dinero_para_viaje';
    } else {
        url = '/liquidaciones/viajes/gastos/justificar';
    }

    $.ajax({
        url: url,
        type: 'post',
        data: payload,
        beforeSend: () => {},
        success: (response) => {
            Swal.fire(response.Titulo, response.Mensaje, response.TMensaje);
            $('#exampleModal').modal('hide');
            if (response.TMensaje == 'success') {
                document.getElementById('txtMonto').value = '';
                document.getElementById('txtDescripcion').value = '';
                mostrarViajesOperador(IdOperador);
            }
        },
        error: (error) => {
            Swal.fire('Error', 'Ocurre un problema', 'error');
        },
    });
}

function justificarGastoMultiple() {
    const allData = window.hotInstance.getSourceData();
    const gridselectedrows = apiGrid.getSelectedRows();

    const payload = [];
    const errores = [];

    allData.forEach((fila, rowIndex) => {
        gridselectedrows.forEach((c) => {
            const motivoKey = `motivo|${c.IdContenedor}`;
            const montoKey = `monto|${c.IdContenedor}`;

            const motivo = fila[motivoKey]?.trim() || '';
            const monto = fila[montoKey];

            if (!c.id_contenedor) {
                if (motivo && (!monto || isNaN(monto) || monto <= 0)) {
                    errores.push(`Contenedor: ${c.Contenedores}, Fila: ${rowIndex + 1}`);

                    const colIndex = window.hotInstance.propToCol(montoKey);
                    resaltarCelda(rowIndex, colIndex, 8);
                }
            }

            if (c.IdContenedor && motivo && monto > 0) {
                payload.push({
                    idviatico: fila[`id_registro|${c.IdContenedor}`] || null,
                    IdContenedor: c.IdContenedor,
                    motivo,
                    monto: monto || 0,
                });
            }
        });
    });

    if (errores.length > 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Faltan montos válidos',
            html: 'Revisa las siguientes filas:<br>' + errores.join('<br>'),
        });
        return;
    }
    let token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/liquidaciones/viajes/gastos/justificar-multiple', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': token,
        },
        body: JSON.stringify({ filas: payload }),
    })
        .then((res) => res.json())
        .then((res) => {
            Swal.fire({
                icon: 'success',
                title: 'Guardado exitoso',
                text: 'Las justificaciones se enviaron correctamente.',
            });
            limpiarJustificacionesLocalStorage();
            $('#modal-justificar-multiple').modal('hide');
            const IdOperador = gridselectedrows.length > 0 ? gridselectedrows[0].IdOperador : null;
            if (IdOperador) {
                mostrarViajesOperador(IdOperador);
            }
        })
        .catch((err) => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error al guardar',
                text: 'Ocurrió un problema al enviar los datos al servidor.',
            });
        });
}

function resaltarCelda(row, col, veces = 3, intervalo = 300) {
    let count = 0;
    const celda = window.hotInstance.getCell(row, col);

    if (!celda) return;

    const originalBg = celda.style.backgroundColor;

    const blink = setInterval(() => {
        celda.style.backgroundColor = count % 2 === 0 ? '#ffcccc' : originalBg;
        count++;
        if (count > veces * 2) {
            celda.style.backgroundColor = originalBg;
            clearInterval(blink);
        }
    }, intervalo);
}

$('.moneyformat').on('focus', (e) => {
    var val = e.target.value;
    e.target.value = reverseMoneyFormat(val);
});

$('.moneyformat').on('blur', (e) => {
    var val = e.target.value;
    e.target.value = moneyFormat(val);
});
