let permisosPersonalizados = [];
let gridApi = null;

const gridOptions = {
    columnDefs: [
        {
            headerName: '',
            checkboxSelection: true,
            headerCheckboxSelection: true,
            width: 40,
        },
        { headerName: 'ID', field: 'id', width: 80 },
        { headerName: 'Nombre', field: 'description', width: 300 },
        { headerName: 'Módulo', field: 'modulo', width: 180 },
        {
            headerName: 'Sistema',
            field: 'sistema',
            width: 120,
            cellRenderer: (params) => `
        <span class="badge bg-secondary">${params.value || '-'}</span>
    `,
        },
        { headerName: 'Permiso', field: 'name', width: 180 },
        {
            headerName: 'Acciones',
            width: 240,
            cellRenderer: (params) => `
                <div class="d-flex justify-content-center gap-2">
                    <button type="button" class="btn btn-sm btn-primary" onclick="event.stopPropagation(); abrirModalEditarPermiso(${params.data.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                </div>`,
            cellStyle: { textAlign: 'center' },
        },
    ],
    rowData: [],
    pagination: true,
    paginationPageSize: 20,
    rowSelection: 'multiple',
    suppressRowClickSelection: true, //  ESTA ES LA CLAVE
    domLayout: 'autoHeight',
    defaultColDef: {
        sortable: true,
        resizable: true,
        filter: true,
    },
    onGridReady: function (params) {
        gridApi = params.api;

        gridApi.setGridOption('rowData', permisosPersonalizados);

        setTimeout(() => {
            const seleccionados = (window.PERMISOS_SELECCIONADOS || []).map(Number);

            gridApi.forEachNode((node) => {
                if (seleccionados.includes(node.data.id)) {
                    node.setSelected(true);
                }
            });

            actualizarHiddenInput();
        }, 0);
    },
    onSelectionChanged: actualizarHiddenInput,
};

function actualizarHiddenInput() {
    if (!gridApi) return;

    const selectedNodes = gridApi.getSelectedNodes();
    const selectedData = selectedNodes.map((n) => ({
        id: n.data.id,
        modulo: n.data.modulo || '',
        descripcion: n.data.description || '',
    }));

    // Input para JSON completo
    const hiddenJson = document.getElementById('custom_permissions_json');
    if (hiddenJson) {
        hiddenJson.value = JSON.stringify(selectedData);
    }

    // Crear inputs tipo permission[]
    const container = document.getElementById('permisosContainer');
    if (container) {
        container.innerHTML = '';
        selectedData.forEach((item) => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'permission[]';
            input.value = item.id;
            container.appendChild(input);
        });
    }

    const badge = document.getElementById('contadorSeleccionados');
    if (badge) {
        badge.innerText = `${selectedData.length} seleccionados`;
    }
}

function abrirModalEditarPermiso(id) {
    if (!gridApi) {
        Swal.fire('Error', 'La tabla aún no está lista', 'error');
        return;
    }

    const permiso = permisosPersonalizados.find((p) => p.id === id);

    if (!permiso) {
        Swal.fire('Error', 'No se encontró el permiso a editar.', 'error');
        return;
    }

    document.getElementById('inputModulo').value = permiso.modulo || '';
    document.getElementById('inputNombrePermiso').value = permiso.name || '';
    document.getElementById('inputDescripcionPermiso').value = permiso.description || '';
    document.getElementById('selectSistema').value = permiso.sistema || '';
    document.getElementById('inputEditId').value = permiso.id;

    const modal = new bootstrap.Modal(document.getElementById('editarPermisoModal'));
    modal.show();
}

window.abrirModalEditarPermiso = abrirModalEditarPermiso;

document.addEventListener('DOMContentLoaded', function () {
    const gridDiv = document.querySelector('#tablaPermisosAGGrid');
    if (gridDiv) {
        const permisos = window.PERMISOS_EXISTENTES || [];
        const seleccionados = (window.PERMISOS_SELECCIONADOS || []).map(Number);

        permisosPersonalizados = permisos.map((p) => ({
            id: p.id,
            name: p.name,
            modulo: p.modulo || '',
            description: p.description || '',
            sistema: p.sistema || '',
            selected: seleccionados.includes(p.id),
        }));

        gridOptions.rowData = permisosPersonalizados;
        agGrid.createGrid(gridDiv, gridOptions);
    }

    document.getElementById('btnGuardarEdicionPermiso')?.addEventListener('click', async function () {
        const id = parseInt(document.getElementById('inputEditId').value);
        const modulo = document.getElementById('inputModulo').value.trim();
        const descripcion = document.getElementById('inputDescripcionPermiso').value.trim();
        const sistema = document.getElementById('selectSistema').value;

        const index = permisosPersonalizados.findIndex((p) => p.id === id);
        if (index === -1) {
            Swal.fire('Error', 'Permiso no encontrado para actualizar.', 'error');
            return;
        }

        try {
            const response = await fetch(`/permisos/${id}/editar-json`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                },
                body: JSON.stringify({
                    modulo,
                    description: descripcion,
                    sistema,
                }),
            });

            if (!response.ok) throw new Error('Error al actualizar');

            const result = await response.json();

            // ⚠️ Restaurar selección
            const selectedIds = gridApi.getSelectedNodes().map((n) => n.data.id);

            // Actualizar local
            permisosPersonalizados[index].modulo = modulo;
            permisosPersonalizados[index].description = descripcion;
            permisosPersonalizados[index].sistema = sistema;

            // Actualiza solo esa fila
            gridApi.applyTransaction({
                update: [permisosPersonalizados[index]],
            });

            // Volver a seleccionar
            gridApi.forEachNode((node) => {
                if (selectedIds.includes(node.data.id)) {
                    node.setSelected(true);
                }
            });

            actualizarHiddenInput();

            const modal = bootstrap.Modal.getInstance(document.getElementById('editarPermisoModal'));
            modal.hide();

            Swal.fire('Actualizado', result.message, 'success');
        } catch (error) {
            Swal.fire('Error', 'No se pudo actualizar el permiso.', 'error');
            console.error(error);
        }
    });
});
