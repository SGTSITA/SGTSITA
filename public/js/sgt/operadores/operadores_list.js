document.addEventListener('DOMContentLoaded', function () {
    const gridDiv = document.querySelector('#operadoresGrid');

    const rowData = operadoresData.map(op => {
        const pendientes = pagosPendientes.filter(p => p.id_operador === op.id).length;
        return {
            id: op.id,
            nombre: op.nombre,
            telefono: op.telefono,
            curp: op.curp,
            estatus: op.deleted_at ? 'Inactivo' : 'Activo',
            deleted_at: op.deleted_at,
            acciones: op.id,
        };
    });

    const columnDefs = [
        { headerName: "No", field: "id", width: 80 },
        { headerName: "Nombre", field: "nombre", flex: 1, floatingFilter: true, },
        { headerName: "Teléfono", field: "telefono", flex: 1,  floatingFilter: true, },
        { headerName: "Curp", field: "curp", flex: 1,  floatingFilter: true, },
        {
            headerName: "Estatus",
            field: "estatus",
            width: 130,
            cellRenderer: (params) => {
                return params.value === "Activo"
                    ? `<span class="badge bg-success"><i class="fas fa-circle-check me-1"></i> Activo</span>`
                    : `<span class="badge bg-danger"><i class="fas fa-circle-xmark me-1"></i> Inactivo</span>`;
            }
        },
        {
            headerName: "Acciones",
            field: "acciones",
            cellRenderer: (params) => {
                const id = params.value;
                const isInactive = !!rowData.find(r => r.id === id).deleted_at;
                const editBtn = `<button class="btn btn-sm btn-outline-primary me-1" onclick="abrirModalEditar(${id})"><i class='fas fa-edit'></i></button>`;
                const actionBtn = isInactive
                    ? `<button class="btn btn-sm btn-outline-success" onclick="restaurarOperador(${id})" title="Reactivar"><i class='fas fa-rotate-left'></i></button>`
                    : `<button class="btn btn-sm btn-outline-danger" onclick="eliminarOperador(${id})" title="Dar de Baja"><i class='fas fa-user-slash'></i></button>`;
                return editBtn + actionBtn;
            },
        }
    ];

    agGrid.createGrid(gridDiv, {
        columnDefs,
        rowData,
        defaultColDef: {
            resizable: true,
            sortable: true,
            filter: true,
        },
        pagination: true,
        paginationPageSize: 20,
    });
});    

function abrirModalEditar(id) {
    const modal = new bootstrap.Modal(document.getElementById(`operadoresModal_Edit${id}`));
    modal.show();
}

function eliminarOperador(id) {
    Swal.fire({
        title: '¿Estás seguro?',
        text: "El operador será dado de baja.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, dar de baja',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('form-eliminar');
            form.action = `/operadores/${id}`;
            form.submit();
        }
    });
}

function restaurarOperador(id) {
    Swal.fire({
        title: '¿Reactivar operador?',
        text: "El operador será reactivado.",
        icon: 'info',
        showCancelButton: true,
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, reactivar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const form = document.getElementById('form-restaurar');
            form.action = `/operadores/${id}/restaurar`;
            form.submit();
        }
    });
}
