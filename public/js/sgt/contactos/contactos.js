let gridOptions; // ahora es global

document.addEventListener('DOMContentLoaded', async () => {
    const response = await fetch('/contactos/list');
    const rowData = await response.json();

    gridOptions = {
        columnDefs: [
            { field: 'id', headerName: 'ID', width: 70 },
            {
                field: 'nombre',
                headerName: 'Nombre',
                flex: 1,
                cellRenderer: (params) => {
                    const nombre = params.value || 'Sin nombre';
                    const foto = params.data.foto ? params.data.foto : '/assets/images/faces/default-avatar.png';

                    return `
            <div class="d-flex align-items-center">
                <img src="${foto}" class="rounded-circle me-2 border" style="width:32px; height:32px; object-fit:cover;">
                <span>${nombre}</span>
            </div>
        `;
                },
            },

            { field: 'telefono', headerName: 'Teléfono', flex: 1 },
            { field: 'email', headerName: 'Correo', flex: 1 },
            { field: 'empresa', headerName: 'Empresa', flex: 1 },
            {
                headerName: 'Estado',
                field: 'deleted_at',
                width: 120,
                cellRenderer: (params) => {
                    const activo = params.value === null;
                    const badgeClass = activo ? 'bg-success' : 'bg-secondary';
                    const label = activo ? 'ACTIVO' : 'INACTIVO';

                    return `
                        <span class="badge ${badgeClass} text-white text-uppercase d-inline-flex align-items-center justify-content-center w-100">
                            ${label}
                        </span>
                    `;
                },
                cellClass: 'text-center',
            },
            {
                headerName: 'Acciones',
                width: 150,
                cellRenderer: (params) => {
                    const { id, deleted_at, nombre } = params.data;

                    let acciones = `
            <a href="/contactos/editar/${id}" class="btn btn-sm btn-outline-primary me-1" title="Editar">
                <i class="fas fa-edit"></i>
            </a>`;

                    if (deleted_at !== null) {
                        acciones += `
                <button class="btn btn-sm btn-outline-success" onclick="activarContacto(${id}, '${nombre}')" title="Reactivar">
                    <i class="fas fa-rotate-left"></i>
                </button>`;
                    } else {
                        acciones += `
                <button class="btn btn-sm btn-outline-danger" onclick="inactivarContacto(${id}, '${nombre}')" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>`;
                    }

                    return acciones;
                },
                cellStyle: { textAlign: 'center' },
            },
        ],
        rowData,
        pagination: true,
        defaultColDef: {
            sortable: true,
            filter: true,
            resizable: true,
        },
    };

    const eGridDiv = document.querySelector('#myGrid');
    agGrid.createGrid(eGridDiv, gridOptions);
});

// 🔁 Recargar los datos del grid sin refrescar la página
async function recargarTabla() {
    const response = await fetch('/contactos/list');
    const data = await response.json();
    gridOptions.api.setRowData(data);
}

// 🗑 Inactivar
async function inactivarContacto(id, nombre) {
    const confirm = await Swal.fire({
        title: `¿Eliminar a <b>${nombre}</b>?`,
        text: 'El contacto será marcado como inactivo.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#aaa',
    });

    if (confirm.isConfirmed) {
        const response = await fetch(`/contactos/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });

        if (response.ok) {
            Swal.fire('Eliminado', `El contacto <b>${nombre}</b> fue inactivado.`, 'success');
            recargarTabla();
        } else {
            Swal.fire('Error', 'No se pudo eliminar el contacto.', 'error');
        }
    }
}

// 🔄 Reactivar
async function activarContacto(id, nombre) {
    const confirm = await Swal.fire({
        title: `¿Reactivar a <b>${nombre}</b>?`,
        text: 'El contacto volverá a estar activo.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, reactivar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#aaa',
    });

    if (confirm.isConfirmed) {
        const response = await fetch(`/contactos/${id}/restore`, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            },
        });

        if (response.ok) {
            Swal.fire('Reactivado', `El contacto <b>${nombre}</b> fue reactivado.`, 'success');
            recargarTabla();
        } else {
            Swal.fire('Error', 'No se pudo reactivar el contacto.', 'error');
        }
    }
}
