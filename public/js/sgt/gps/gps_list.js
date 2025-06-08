document.addEventListener('DOMContentLoaded', function () {
    const gridDiv = document.querySelector('#gpsGrid');
    const modal = new bootstrap.Modal(document.getElementById('modalGps'));
    const form = document.getElementById('formGps');
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const modalTitle = document.getElementById('modalGpsLabel');
    let gridApi = null;

    loadGrid();

    function loadGrid() {
        fetch('/gps/data')
            .then(res => res.json())
            .then(data => {
                if (gridApi) {
                    gridApi.destroy();
                    gridApi = null;
                }
                gridDiv.innerHTML = '';

                gridApi = agGrid.createGrid(gridDiv, {
                    columnDefs: [
                        { field: "nombre", headerName: "Nombre" },
                        { field: "url", headerName: "URL" },
                        { field: "url_conexion", headerName: "URL Conexión" },
                        { field: "telefono", headerName: "Teléfono" },
                        { field: "correo", headerName: "Correo" },
                        { field: "contacto", headerName: "Contacto" },
                        {
                            field: "deleted_at",
                            headerName: "Estatus",
                            width: 110,
                            cellRenderer: params => params.value === null
                                ? '<span class="badge bg-success">Activo</span>'
                                : '<span class="badge bg-secondary">Inactivo</span>',
                            cellClass: 'text-center'
                        },
                        {
                            headerName: "Acciones",
                            width: 250,
                            cellRenderer: params => {
                                const id = params.data.id;
                                if (params.data.deleted_at !== null) {
                                    return `<button class="btn btn-sm btn-outline-success btn-reactivar" data-id="${id}" title="Reactivar"><i class="fas fa-rotate-left"></i></button>`;
                                }
                                return `
                                    <button class="btn btn-sm btn-outline-primary btn-editar me-1" data-id="${id}" title="Editar"><i class="fas fa-pen-to-square"></i></button>
                                    <button class="btn btn-sm btn-outline-danger btn-eliminar" data-id="${id}" title="Eliminar"><i class="fas fa-trash"></i></button>
                                `;
                            },
                            cellStyle: { textAlign: 'center' }
                        }
                    ],
                    rowData: data,
                    defaultColDef: {
                        resizable: true,
                        sortable: true,
                        filter: true
                    }
                });
            });
    }

    // Botón "+ Nuevo Proveedor"
    document.querySelector('[data-bs-target="#modalGps"]').addEventListener('click', () => {
        modalTitle.textContent = 'Nuevo Proveedor GPS';
        form.reset();
        form.gps_id.value = '';
    });

    // Eventos de acciones
    document.addEventListener('click', function (e) {
        const id = e.target.closest('[data-id]')?.dataset.id;

        if (e.target.closest('.btn-eliminar')) {
            Swal.fire({
                title: '¿Eliminar proveedor GPS?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar'
            }).then(result => {
                if (result.isConfirmed) {
                    fetch(`/gps/${id}`, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': csrfToken }
                    }).then(() => {
                        Swal.fire('Eliminado', 'Proveedor desactivado.', 'success');
                        loadGrid();
                    });
                }
            });
        }

        if (e.target.closest('.btn-editar')) {
            fetch('/gps/data')
                .then(res => res.json())
                .then(data => {
                    const p = data.find(x => x.id == id);
                    if (p) {
                        form.gps_id.value = p.id;
                        form.nombre.value = p.nombre;
                        form.url.value = p.url;
                        form.url_conexion.value = p.url_conexion || '';
                        form.telefono.value = p.telefono || '';
                        form.correo.value = p.correo || '';
                        form.contacto.value = p.contacto || '';
                        modalTitle.textContent = 'Editar Proveedor GPS';
                        modal.show();
                    }
                });
        }

        if (e.target.closest('.btn-reactivar')) {
            fetch(`/gps/restore/${id}`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken }
            }).then(() => {
                Swal.fire('Reactivado', 'Proveedor reactivado correctamente.', 'success');
                loadGrid();
            });
        }
    });

    // Guardar (crear o editar)
    form.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(form);
        const id = formData.get('id');
        const url = id ? `/gps/${id}` : '/gps/store';
        const method = id ? 'POST' : 'POST'; // Siempre POST, con _method si es update

        if (id) formData.append('_method', 'PUT');

        fetch(url, {
            method,
            headers: { 'X-CSRF-TOKEN': csrfToken },
            body: formData
        })
            .then(res => {
                if (!res.ok) throw new Error('Error al guardar');
                return res.json();
            })
            .then(() => {
                Swal.fire('Guardado', 'Proveedor guardado correctamente.', 'success');
                form.reset();
                form.gps_id.value = '';
                modalTitle.textContent = 'Nuevo Proveedor GPS';
                modal.hide();
                loadGrid();
            })
            .catch(() => {
                Swal.fire('Error', 'No se pudo guardar.', 'error');
            });
    });
});
