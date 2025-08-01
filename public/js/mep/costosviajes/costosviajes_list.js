let gridApi = null;
let filaSeleccionada = null;

const columnDefs = [
    { headerName: "Contenedor", field: "contenedor", flex: 1 },
    { headerName: "Destino", field: "destino", flex: 1 },
    { headerName: "Estatus", field: "estatus", flex: 1 },
    { headerName: "Costo del viaje", field: "precio_viaje", valueFormatter: money, flex: 1 },
    { headerName: "Burreo", field: "burreo", valueFormatter: money, flex: 1 },
    { headerName: "Maniobra", field: "maniobra", valueFormatter: money, flex: 1 },
    { headerName: "Estadía", field: "estadia", valueFormatter: money, flex: 1 },
    { headerName: "Otros", field: "otro", valueFormatter: money, flex: 1 },
    { headerName: "IVA", field: "iva", valueFormatter: money, flex: 1 },
    { headerName: "Retención", field: "retencion", valueFormatter: money, flex: 1 },
    { headerName: "Base 1", field: "base1", valueFormatter: money, flex: 1 },
    { headerName: "Base 2", field: "base2", valueFormatter: money, flex: 1 },
    { headerName: "Total", field: "total", valueFormatter: money, flex: 1 },
    { headerName: "Sobrepeso", field: "sobrepeso", valueFormatter: money, flex: 1 },
    { headerName: "Precio sobrepeso", field: "precio_sobrepeso", valueFormatter: money, flex: 1 },
    {
        headerName: "Acciones", field: "acciones", flex: 0.6,
        cellRenderer: (params) => {
            const row = JSON.stringify(params.data).replace(/"/g, '&quot;');
            return `<button class="btn btn-sm btn-warning" onclick="abrirModal('${row}')">
                <i class="fas fa-edit"></i>
            </button>`;
        }
    }
];


function money(params) {
    return `$${parseFloat(params.value || 0).toFixed(4)}`;
}
function reverseMoneyFormat(value) {
    return parseFloat(value.replace(/[$,]/g, '')) || 0;
}

function moneyFormat(value) {
    return `$${parseFloat(value).toFixed(2)}`;
}

document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('#precio_viaje, #burreo, #maniobra, #estadia, #otro, #sobrepeso, #precio_sobrepeso, #base1')
        .forEach(input => input.addEventListener('input', calcularTotales));

    const gridDiv = document.querySelector('#tablaCostosMEP');

    gridApi = agGrid.createGrid(gridDiv, {
        columnDefs: columnDefs,
        rowData: [],
        pagination: true,
        defaultColDef: {
            resizable: true,
            sortable: true,
            filter: true
        }
    });

    cargarDatos();

    document.getElementById('formEditarCostos').addEventListener('submit', enviarFormulario);
});

function cargarDatos() {
    fetch('/costos/mep/data')
        .then(res => res.json())
        .then(data => gridApi.setGridOption('rowData', data));
}

function abrirModal(rowJson) {
    const row = JSON.parse(rowJson.replace(/&quot;/g, '"')); // Revertir comillas escapadas
    filaSeleccionada = row;

    document.getElementById('id_asignacion').value = row.id;

    [
        'precio_viaje', 'burreo', 'maniobra', 'estadia',
        'otro', 'iva', 'retencion', 'base1', 'base2',
        'sobrepeso', 'precio_sobrepeso', 'total'
    ].forEach(campo => {
        document.getElementById(campo).value = row[campo] ?? 0;
    });
    document.getElementById('total').value = row.total ?? 0;
    document.getElementById('motivo_cambio').value = '';
    document.getElementById('contenedor').value = row.contenedor ?? '-';
    document.getElementById('destino').value = row.destino ?? '-';
    document.getElementById('estatus').value = row.estatus ?? '-';

    new bootstrap.Modal(document.getElementById('modalEditarCostos')).show();
}


function enviarFormulario(e) {
    e.preventDefault();
    const form = e.target;
    const datos = new FormData(form);

    Swal.fire({
        title: '¿Enviar cambios para revisión?',
        text: 'Este cambio se enviará como pendiente para verificar.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, enviar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch('/costos/mep/guardar-cambio', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: datos
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Éxito', data.message, 'success');
                        bootstrap.Modal.getInstance(document.getElementById('modalEditarCostos')).hide();
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                })
                .catch(err => {
                    console.error(err);
                    Swal.fire('Error', 'No se pudo guardar el cambio', 'error');
                });
        }
    });
}

function mostrarPendientes() {
    window.location.href = '/costos/mep/pendientes'; // o mostrar en modal otra tabla si lo deseas
}


const tasa_iva = 0.16;
const tasa_retencion = 0.04;

function calcularTotales() {
    const precio = parseFloat(reverseMoneyFormat($('#precio_viaje').val())) || 0;
    const burreo = parseFloat(reverseMoneyFormat($('#burreo').val())) || 0;
    const maniobra = parseFloat(reverseMoneyFormat($('#maniobra').val())) || 0;
    const estadia = parseFloat(reverseMoneyFormat($('#estadia').val())) || 0;
    const otro = parseFloat(reverseMoneyFormat($('#otro').val())) || 0;
    const sobrepeso = parseFloat(reverseMoneyFormat($('#sobrepeso').val())) || 0;
    const precio_sobrepeso = parseFloat(reverseMoneyFormat($('#precio_sobrepeso').val())) || 0;

    const baseFactura = parseFloat(reverseMoneyFormat($('#base1').val())) || 0;

    const subtotal = precio + burreo + maniobra + estadia + otro;
    const sobre = sobrepeso * precio_sobrepeso;
    const iva = baseFactura * tasa_iva;
    const retencion = baseFactura * tasa_retencion;
    const total = subtotal + sobre + iva - retencion;
    const baseTaref = (total - baseFactura - iva) + retencion;

    $('#iva').val(total > 0 ? iva.toFixed(4) : '');
    $('#retencion').val(total > 0 ? retencion.toFixed(4) : '');
    $('#base2').val(total > 0 ? baseTaref.toFixed(4) : '');
    $('#total').val(total > 0 ? total.toFixed(4) : '');

}
