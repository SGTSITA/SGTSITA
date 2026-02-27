let grid;
document.addEventListener('DOMContentLoaded', function () {
    const gridDiv = document.querySelector('#tablaPendientesMEP');

    // Función para marcar celdas modificadas
    function cellHighlightRules(fieldName) {
        return {
            'highlight-cell': (params) => {
                return params.data?.highlight?.[fieldName] === true;
            },
        };
    }

    function money(params) {
        return `$${parseFloat(params.value || 0).toFixed(2)}`;
    }

    const columnDefs = [
        {
            headerName: '#',
            valueGetter: 'node.rowIndex + 1',
            width: 60,
            cellClass: 'text-center',
        },
        { headerName: 'Contenedor', field: 'contenedor', width: 130, floatingFilter: true },
        { headerName: 'Proveedor', field: 'nombre_proveedor', width: 140, floatingFilter: true },
        { headerName: 'Destino', field: 'destino', width: 120, floatingFilter: true },
        { headerName: 'Estatus', field: 'estatus', width: 120, floatingFilter: true },
        {
            headerName: 'Costo del viaje',
            field: 'precio_viaje',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('precio_viaje'),
        },
        {
            headerName: 'Burreo',
            field: 'burreo',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('burreo'),
        },
        {
            headerName: 'Maniobra',
            field: 'maniobra',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('maniobra'),
        },
        {
            headerName: 'Estadía',
            field: 'estadia',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('estadia'),
        },
        {
            headerName: 'Otros',
            field: 'otro',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('otro'),
        },
        {
            headerName: 'Peso Contenedor',
            field: 'peso_contenedor',
            floatingFilter: true,
            width: 100,
            cellClassRules: cellHighlightRules('peso_contenedor'),
        },
        {
            headerName: 'Sobrepeso',
            field: 'sobrepeso',
            floatingFilter: true,
            width: 100,
            cellClassRules: cellHighlightRules('sobrepeso'),
        },
        {
            headerName: 'Precio sobrepeso',
            field: 'precio_sobrepeso',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('precio_sobrepeso'),
        },
        {
            headerName: 'Base 1',
            field: 'base1',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('base1'),
        },
        {
            headerName: 'Base 2',
            field: 'base2',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('base2'),
        },
        {
            headerName: 'IVA',
            field: 'iva',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('iva'),
        },
        {
            headerName: 'Retención',
            field: 'retencion',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('retencion'),
        },
        {
            headerName: 'Total',
            field: 'total',
            floatingFilter: true,
            valueFormatter: money,
            width: 100,
            cellClassRules: cellHighlightRules('total'),
        },
        {
            headerName: 'Acciones',
            field: 'acciones',
            minWidth: 50,
            cellRenderer: (params) => {
                return `
                <button class="btn btn-sm btn-outline-primary" onclick="mostrarComparacion(${params.data.id})" title="Comparar Cambios">
                    <i class="fas fa-balance-scale"></i>
                </button>
            `;
            },
        },
    ];

    grid = agGrid.createGrid(gridDiv, {
        columnDefs: columnDefs,
        rowData: [],
        pagination: true,
        defaultColDef: {
            resizable: true,
            sortable: true,
            filter: true,
        },
        suppressSizeToFit: true,
        domLayout: 'normal',
    });

    fetch('/costos/mep/pendientes')
        .then((res) => res.json())
        .then((data) => grid.setGridOption('rowData', data));
});

function recargarTablaPendientes() {
    fetch('/costos/mep/pendientes')
        .then((res) => res.json())
        .then((data) => {
            grid.setGridOption('rowData', data);
        });
}

window.mostrarComparacion = function (idPendiente) {
    fetch(`/costos/mep/pendientes/${idPendiente}/comparacion`)
        .then((res) => res.json())
        .then((data) => {
            const tbody = document.getElementById('tablaComparacionCostos');
            tbody.innerHTML = '';

            const labels = {
                precio_viaje: 'Costo del viaje',
                burreo: 'Burreo',
                maniobra: 'Maniobra',
                estadia: 'Estadía',
                otro: 'Otros',
                iva: 'IVA',
                retencion: 'Retención',
                base1: 'Base 1',
                base2: 'Base 2',
                sobrepeso: 'Sobrepeso',
                precio_sobrepeso: 'Precio sobrepeso',
                total: 'Total',
            };

            const moneyFields = new Set([
                'precio_viaje',
                'burreo',
                'maniobra',
                'estadia',
                'otro',
                'iva',
                'retencion',
                'base1',
                'base2',
                'precio_sobrepeso',
                'total',
            ]);

            const fmtMoney = (v) => {
                const n = parseFloat(v);
                return isNaN(n) ? (v ?? '-') : `$${n.toFixed(2)}`;
            };

            const campos = Object.keys(labels);

            tbody.innerHTML = campos
                .map((campo) => {
                    document.getElementById('infoContenedor').textContent = data?.asignacion?.num_contenedor || '-';
                    document.getElementById('infoProveedor').textContent = data?.asignacion?.proveedor || '-';
                    document.getElementById('infoFechaInicio').textContent = data?.asignacion?.fecha_inicio || '-';
                    document.getElementById('infoFechaSolicitud').textContent = data?.fecha_solicitud || '-';

                    const originalRaw = data.original?.[campo] ?? '-';
                    const nuevoRaw = data.nuevo?.[campo] ?? '-';

                    const original = moneyFields.has(campo) ? fmtMoney(originalRaw) : originalRaw;
                    const nuevo = moneyFields.has(campo) ? fmtMoney(nuevoRaw) : nuevoRaw;

                    const diferente = String(originalRaw) !== String(nuevoRaw);

                    return `
<tr>
    <td><strong>${labels[campo] ?? campo}</strong></td>
    <td>${original}</td>
    <td class="${diferente ? 'diff-cell' : ''}">${nuevo}</td>
    <td>
        ${diferente ? `<input type="checkbox" name="campo_observado" value="${campo}" class="campo-checkbox" title="Observar este campo">` : ''}
    </td>
</tr>`;
                })
                .join('');

            document.getElementById('btnAceptarCambio').dataset.id = idPendiente;
            document.getElementById('btnRechazarCambio').dataset.id = idPendiente;

            new bootstrap.Modal(document.getElementById('modalCompararCostos')).show();
        })
        .catch(() => {
            Swal.fire('Error', 'No se pudo cargar la comparación.', 'error');
        });
};

document.getElementById('btnRechazarCambio').addEventListener('click', function () {
    const id = this.dataset.id;

    bootstrap.Modal.getInstance(document.getElementById('modalCompararCostos')).hide();

    Swal.fire({
        title: '¿Estás seguro?',
        text: 'Escribe una razón para rechazar este cambio.',
        input: 'text',
        inputLabel: 'Motivo del rechazo',
        inputPlaceholder: 'Ej. No se justifica el ajuste',
        inputAttributes: {
            maxlength: 500,
            autocapitalize: 'off',
            autocorrect: 'off',
            style: 'color: #000; background-color: #fff;', // Forzar visibilidad del input
        },
        showCancelButton: true,
        confirmButtonText: 'Rechazar',
        cancelButtonText: 'Cancelar',
        preConfirm: (motivo) => motivo || null,
    }).then((result) => {
        if (result.isConfirmed) {
            const motivo = result.value;
            const camposSeleccionados = Array.from(
                document.querySelectorAll('input[name="campo_observado"]:checked'),
            ).map((input) => input.value);

            fetch(`/costos/mep/pendientes/${id}/rechazar`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                },
                body: JSON.stringify({
                    motivo: motivo,
                    campo_observado: camposSeleccionados,
                }),
            })
                .then((res) => res.json())
                .then((res) => {
                    Swal.fire('Aplicado', res.message, 'success').then(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalCompararCostos')).hide();
                        recargarTablaPendientes();
                    });
                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo rechazar el cambio.', 'error');
                });
        }
    });
});

document.getElementById('btnAceptarCambio').addEventListener('click', function () {
    const id = this.dataset.id;

    Swal.fire({
        title: '¿Aceptar cambio?',
        text: 'Este cambio se aplicará a la asignación.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`/costos/mep/pendientes/${id}/aceptar`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrf,
                },
            })
                .then((res) => res.json())
                .then((res) => {
                    Swal.fire('Aplicado', res.message, 'success').then(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalCompararCostos')).hide();
                        recargarTablaPendientes();
                    });
                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo aplicar el cambio.', 'error');
                });
        }
    });
});
