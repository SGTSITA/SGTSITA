/* global agGrid */
(function () {
    const gridDiv = document.querySelector('#gridCambios');
    const urlData = '/costos/mep/cambios/data';

    const columnDefs = [
        { headerName: '#', valueGetter: 'node.rowIndex + 1', width: 70, pinned: 'left' },
        { headerName: 'Estatus', field: 'estatus_cambio', filter: true, width: 120 },
        { headerName: 'Contenedor', field: 'contenedor', filter: true, width: 140 },
        { headerName: 'Destino', field: 'destino', filter: true, width: 150 },
        { headerName: 'Precio viaje', field: 'precio_viaje', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'Burreo', field: 'burreo', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'Maniobra', field: 'maniobra', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'Estadía', field: 'estadia', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'Otro', field: 'otro', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'IVA', field: 'iva', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'Retención', field: 'retencion', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'Base 1', field: 'base1', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'Base 2', field: 'base2', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'Sobrepeso', field: 'sobrepeso', width: 100 },
        { headerName: 'Precio sobrepeso', field: 'precio_sobrepeso', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'Total', field: 'total', valueFormatter: fmtMoney, width: 100 },
        { headerName: 'Fecha de solicitud', field: 'created_at', width: 160 },
        {
            headerName: '',
            field: 'id',
            pinned: 'right',
            width: 90,
            suppressMenu: true,
            sortable: false,
            filter: false,
            cellRenderer: (p) => {
                const status = p.data?.estatus_cambio;
                const id = p.data?.id;

                if (status === 'rechazado') {
                    return `
       
          <button class="btn  btn-warning" data-action="observacion" data-id="${id}" title="Ver observaciones">
            <i class="fas fa-exclamation-triangle me-1"></i> 
          </button>

      `;
                }

                if (status === 'aprobado') {
                    return `
        <span class="badge bg-success" title="Sin observaciones">
          <i class="fas fa-check me-1"></i> 
        </span>
      `;
                }

                // pendiente u otros
                return `<span class="text-muted">—</span>`;
            },
        }

    ];

    function fmtMoney(params) {
        const v = Number(params.value ?? 0);
        return v ? v.toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) : '0.00';
    }

    const gridOptions = {
        columnDefs,
        defaultColDef: { sortable: true, filter: true, resizable: true },
        animateRows: true,
        pagination: true,
        paginationPageSize: 50,

        // Colorea la fila según estatus
        getRowStyle: (params) => {
            const s = params.data?.estatus_cambio;
            if (s === 'aprobado') return { backgroundColor: '#d1fae5' }; // verde suave
            if (s === 'rechazado') return { backgroundColor: '#fff3cd' }; // amarillo suave
            return null; // pendiente = normal
        },
    };


    const gridApi = agGrid.createGrid(gridDiv, gridOptions);

    fetch(urlData, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
        .then(r => r.json())
        .then(data => {
            gridApi.setGridOption('rowData', data);
            aplicarFiltroInicialPorQuery();
        });

    document.getElementById('quickSearch')
        .addEventListener('input', (e) => gridApi.setGridOption('quickFilterText', e.target.value));

    document.getElementById('btnAll').addEventListener('click', () => setStatusFilter(null));
    document.getElementById('btnPendientes').addEventListener('click', () => setStatusFilter('pendiente'));
    document.getElementById('btnAprobados').addEventListener('click', () => setStatusFilter('aprobado'));
    document.getElementById('btnRechazados').addEventListener('click', () => setStatusFilter('rechazado'));

    function setStatusFilter(status) {
        const model = status ? { estatus_cambio: { filterType: 'text', type: 'equals', filter: status } } : null;
        gridApi.setFilterModel(model);
    }

    function aplicarFiltroInicialPorQuery() {
        const url = new URL(window.location.href);
        const status = url.searchParams.get('status'); // pendiente | aprobado | rechazado | all
        if (status && status !== 'all') setStatusFilter(status);
    }
})();

// Agregar escucha para botones "Ver observaciones"
document.addEventListener('click', function (e) {
    const btn = e.target.closest('[data-action="observacion"]');
    if (!btn) return;

    const id = btn.dataset.id;

    fetch(`/costos/mep/cambios/${id}/detalle`, {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
        .then(res => {
            if (!res.ok) throw new Error('Error del servidor');
            return res.json();
        })
        .then(data => {
            if (!data || Object.keys(data).length === 0) {
                throw new Error('Sin datos');
            }
            mostrarModalObservaciones(data); // Solo si los datos son válidos
        })
        .catch((err) => {
            Swal.fire('Error', 'No se pudo cargar la información del cambio rechazado.', 'error');
        });
});


function mostrarModalObservaciones(data) {
    const modal = new bootstrap.Modal(document.getElementById('modalObservaciones'));

    document.getElementById('motivoRechazo').textContent = data.motivo_cambio || 'No especificado';

    const tbody = document.getElementById('tablaCamposObservados');
    tbody.innerHTML = '';

    const campos = {
        precio_viaje: 'Precio del viaje',
        burreo: 'Burreo',
        maniobra: 'Maniobra',
        estadia: 'Estadía',
        otro: 'Otros',
        base1: 'Base 1',
        base2: 'Base 2',
        iva: 'IVA',
        retencion: 'Retención',
        sobrepeso: 'Sobrepeso',
        precio_sobrepeso: 'Precio sobrepeso',
        total: 'Total'
    };

    const camposCalculados = ['iva', 'retencion', 'base2', 'total'];
    const camposObservados = data.campo_observado || [];

    const row = {};

    Object.entries(campos).forEach(([campo, label]) => {
        const esCalculado = camposCalculados.includes(campo);
        const esObservado = camposObservados.includes(campo);
        const valor = Number(data[campo] || 0).toFixed(2);

        row[campo] = Number(valor); // Para uso posterior

        const asterisco = esObservado ? '<span class="text-danger">*</span>' : '';
        const inputAttrs = esCalculado
            ? 'readonly class="form-control form-control-sm bg-light border-0 text-muted"'
            : 'class="form-control form-control-sm campo-editable"';

        tbody.innerHTML += `
            <tr>
                <td class="text-start fw-semibold">${label} ${asterisco}</td>
                <td>
                    <input type="number" step="0.01" min="0" name="${campo}" ${inputAttrs} value="${valor}">
                </td>
            </tr>
        `;
    });

    // Guardamos la data para envío posterior
    document.getElementById('btnReenviarCambios').dataset.id = data.id;
    document.getElementById('btnReenviarCambios').dataset.row = JSON.stringify(row);
    document.getElementById('infoContenedor').textContent = data?.contenedor || '-';
    document.getElementById('infoFechaViaje').textContent = data?.fecha_viaje || '-';
    document.getElementById('infoFechaCambio').textContent = data?.fecha_cambio || '-';

    modal.show();
    const inputs = document.querySelectorAll('.campo-editable');

    inputs.forEach(input => {
        input.addEventListener('input', () => {
            // Actualizar row con los nuevos valores del input
            const name = input.name;
            const value = parseFloat(input.value) || 0;
            row[name] = value;

            // Recalcular
            const tasa_iva = 0.16;
            const tasa_retencion = 0.04;

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
            const base2 = (total - base1 - iva) + retencion;

            // Guardar los nuevos valores
            row.iva = parseFloat(iva.toFixed(4));
            row.retencion = parseFloat(retencion.toFixed(4));
            row.total = parseFloat(total.toFixed(4));
            row.base2 = parseFloat(base2.toFixed(4));

            // Refrescar campos visibles en el DOM
            document.querySelector('input[name="iva"]').value = row.iva;
            document.querySelector('input[name="retencion"]').value = row.retencion;
            document.querySelector('input[name="total"]').value = row.total;
            document.querySelector('input[name="base2"]').value = row.base2;
        });
    });

}

const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
// Reenviar cambios con recálculo
document.getElementById('btnReenviarCambios').addEventListener('click', () => {
    const id = document.getElementById('btnReenviarCambios').dataset.id;
    const row = JSON.parse(document.getElementById('btnReenviarCambios').dataset.row);

    const inputs = document.querySelectorAll('#tablaCamposObservados .campo-editable');
    inputs.forEach(input => {
        row[input.name] = parseFloat(input.value || 0);
    });

    // Recalcular
    const tasa_iva = 0.16;
    const tasa_retencion = 0.04;

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
    const base2 = (total - base1 - iva) + retencion;

    row.iva = parseFloat(iva.toFixed(4));
    row.retencion = parseFloat(retencion.toFixed(4));
    row.total = parseFloat(total.toFixed(4));
    row.base2 = parseFloat(base2.toFixed(4));

    fetch(`/costos/mep/cambios/${id}/reenviar`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf
        },
        body: JSON.stringify(row)
    })
        .then(res => res.json())
        .then(res => {
            if (res.success) {
                Swal.fire('Enviado', res.message, 'success').then(() => location.reload());
            } else {
                Swal.fire('Error', res.message || 'No se pudo reenviar el cambio.', 'error');
            }
        })
        .catch(() => {
            Swal.fire('Error', 'Fallo al reenviar el cambio.', 'error');
        });
});
