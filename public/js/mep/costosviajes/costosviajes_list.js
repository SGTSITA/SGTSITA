let hot = null;
let rowData = [];
const tasa_iva = 0.16;
const tasa_retencion = 0.04;

function recalcularTotales(row) {
    const precio = parseFloat(row.precio_viaje) || 0;
    const burreo = parseFloat(row.burreo) || 0;
    const maniobra = parseFloat(row.maniobra) || 0;
    const estadia = parseFloat(row.estadia) || 0;
    const otro = parseFloat(row.otro) || 0;
    const sobrepeso = parseFloat(row.sobrepeso) || 0;
    const precio_sobrepeso = parseFloat(row.precio_sobrepeso) || 0;
    const base1 = parseFloat(row.base1) || 0;

    const subtotal = precio + burreo + maniobra + estadia + otro;
    const sobre = sobrepeso * precio_sobrepeso;
    const iva = base1 * tasa_iva;
    const retencion = base1 * tasa_retencion;
    const total = subtotal + sobre + iva - retencion;
    const base2 = total - base1 - iva + retencion;

    row.iva = parseFloat(iva.toFixed(4));
    row.retencion = parseFloat(retencion.toFixed(4));
    row.total = parseFloat(total.toFixed(4));
    row.base2 = parseFloat(base2.toFixed(4));
}

document.addEventListener('DOMContentLoaded', function () {
    $('#daterange').daterangepicker(
        {
            opens: 'right',
            locale: {
                format: 'YYYY-MM-DD',
                separator: ' - ',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                weekLabel: 'S',
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
            },
            startDate: moment().subtract(7, 'days'),
            endDate: moment(),
        },
        function (start, end) {
            cargarDatos(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
        },
    );

    const container = document.getElementById('tablaCostosMEP');
    hot = new Handsontable(container, {
        columns: [
            { data: 'contenedor', title: 'Contenedor' },
            { data: 'destino', title: 'Destino' },
            { data: 'estatus', title: 'Estatus' },
            { data: 'precio_viaje', title: 'Costo del viaje', type: 'numeric', numericFormat: { pattern: '$0,0.00' } },
            { data: 'burreo', title: 'Burreo', type: 'numeric', numericFormat: { pattern: '$0,0.00' } },
            { data: 'maniobra', title: 'Maniobra', type: 'numeric', numericFormat: { pattern: '$0,0.00' } },
            { data: 'estadia', title: 'Estadía', type: 'numeric', numericFormat: { pattern: '$0,0.00' } },
            { data: 'otro', title: 'Otros', type: 'numeric', numericFormat: { pattern: '$0,0.00' } },
            { data: 'peso_contenedor', title: 'Peso Contenedor', type: 'numeric', readOnly: true },
            { data: 'sobrepeso', title: 'Sobrepeso', type: 'numeric', readOnly: true },
            {
                data: 'precio_sobrepeso',
                title: 'Precio sobrepeso',
                type: 'numeric',
                numericFormat: { pattern: '$0,0.00' },
            },
            { data: 'base1', title: 'Base 1', type: 'numeric', numericFormat: { pattern: '$0,0.00' } },
            { data: 'base2', title: 'Base 2', readOnly: true, type: 'numeric', numericFormat: { pattern: '$0,0.00' } },
            { data: 'iva', title: 'IVA', readOnly: true, type: 'numeric', numericFormat: { pattern: '$0,0.00' } },
            {
                data: 'retencion',
                title: 'Retención',
                readOnly: true,
                type: 'numeric',
                numericFormat: { pattern: '$0,0.00' },
            },
            { data: 'total', title: 'Total', readOnly: true, type: 'numeric', numericFormat: { pattern: '$0,0.00' } },
        ],
        data: rowData,
        stretchH: 'all',
        height: 600,
        licenseKey: 'non-commercial-and-evaluation',
        afterChange: function (changes, source) {
            if (source !== 'edit' || !changes) return;

            changes.forEach(([rowIndex, prop, oldValue, newValue]) => {
                const camposTrigger = [
                    'precio_viaje',
                    'burreo',
                    'maniobra',
                    'estadia',
                    'otro',
                    'base1',
                    'sobrepeso',
                    'precio_sobrepeso',
                ];

                if (camposTrigger.includes(prop)) {
                    const row = hot.getSourceDataAtRow(rowIndex);
                    recalcularTotales(row);
                    hot.setDataAtRowProp(rowIndex, 'iva', row.iva);
                    hot.setDataAtRowProp(rowIndex, 'retencion', row.retencion);
                    hot.setDataAtRowProp(rowIndex, 'total', row.total);
                    hot.setDataAtRowProp(rowIndex, 'base2', row.base2);
                }
            });
        },
    });

    const range = $('#daterange').data('daterangepicker');
    cargarDatos(range.startDate.format('YYYY-MM-DD'), range.endDate.format('YYYY-MM-DD'));
});

function cargarDatos(start, end) {
    fetch(`/costos/mep/data?fecha_inicio=${start}&fecha_fin=${end}`)
        .then((res) => res.json())
        .then((data) => {
            rowData = data;
            hot.loadData(rowData);

            if (rowData.length === 0) {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin resultados',
                    text: 'No se encontraron viajes pendientes de registro en este periodo.',
                    confirmButtonText: 'Aceptar',
                });
            }
        });
}

function abrirModal(index) {
    const row = rowData[index];
    if (!row) return;

    filaSeleccionada = row;

    [
        'id_asignacion',
        'precio_viaje',
        'burreo',
        'maniobra',
        'estadia',
        'otro',
        'iva',
        'retencion',
        'base1',
        'base2',
        'sobrepeso',
        'precio_sobrepeso',
        'total',
    ].forEach((campo) => {
        $(`#${campo}`).val(row[campo] ?? 0);
    });

    $('#contenedor').val(row.contenedor);
    $('#destino').val(row.destino);
    $('#estatus').val(row.estatus);
    $('#motivo_cambio').val('');

    new bootstrap.Modal(document.getElementById('modalEditarCostos')).show();
}

function mostrarPendientes() {
    window.location.href = '/costos/mep/pendientes';
}

document.getElementById('guardarCambios').addEventListener('click', async () => {
    const datos = hot.getSourceData(); // Obtener todos los datos de la tabla

    // Validamos si hay cambios y generamos las peticiones
    const formularios = datos.map((row) => {
        return {
            id_asignacion: row.id,
            precio_viaje: parseFloat(row.precio_viaje || 0),
            burreo: parseFloat(row.burreo || 0),
            maniobra: parseFloat(row.maniobra || 0),
            estadia: parseFloat(row.estadia || 0),
            otro: parseFloat(row.otro || 0),
            iva: parseFloat(row.iva || 0),
            retencion: parseFloat(row.retencion || 0),
            base1: parseFloat(row.base1 || 0),
            base2: parseFloat(row.base2 || 0),
            sobrepeso: parseFloat(row.sobrepeso || 0),
            precio_sobrepeso: parseFloat(row.precio_sobrepeso || 0),
            total: parseFloat(row.total || 0),
            motivo_cambio: 'Modificación por revisión masiva',
        };
    });

    // Confirmación con SweetAlert
    const confirmacion = await Swal.fire({
        title: '¿Guardar todos los cambios?',
        text: 'Se enviarán los datos modificados para revisión.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, guardar',
        cancelButtonText: 'Cancelar',
    });

    if (!confirmacion.isConfirmed) return;

    // Enviar cada cambio
    for (const form of formularios) {
        try {
            const res = await fetch('/costos/mep/guardar-cambio', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(form),
            });

            const data = await res.json();
            if (!data.success) throw new Error(data.message);
        } catch (error) {
            console.error(error);
            Swal.fire('Error', `No se pudo guardar el cambio para ID ${form.id_asignacion}`, 'error');
        }
    }

    Swal.fire('Éxito', 'Todos los cambios han sido enviados correctamente.', 'success');
    window.location.href = '/costos/mep/dashboard';
});
