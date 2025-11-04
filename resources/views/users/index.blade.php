@extends('layouts.app')

@section('template_title')
    Usuarios
@endsection

@push('custom-css')
<style>
    .table-wrapper {
        padding: 1rem;
        position: relative;
    }

    .ag-theme-alpine {
        border-radius: 0.75rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
        overflow: visible !important;
    }

    .pagination-container {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.6rem;
        margin-top: 1.25rem;
        flex-wrap: wrap;
    }

    .pagination-container button {
        border: none;
        background-color: #f8f9fa;
        color: #333;
        padding: 0.45rem 1rem;
        border-radius: 0.5rem;
        cursor: pointer;
        transition: all 0.2s ease;
        font-size: 0.9rem;
    }

    .pagination-container button:hover {
        background-color: #0d6efd;
        color: white;
    }

    .pagination-container button:disabled {
        background-color: #e9ecef;
        color: #aaa;
        cursor: not-allowed;
    }

    .pagination-info {
        font-size: 0.9rem;
        color: #555;
    }

    /* Hace que la tabla crezca sin cortar la paginación */
    #usersGrid {
        min-height: 600px;
        width: 100%;
    }
    .ag-theme-alpine .ag-cell {
    overflow: visible !important; /* permite que los botones no se corten */
}

.btn-sm {
    font-size: 0.8rem;
    padding: 0.25rem 0.5rem;
}

.ag-cell div.d-flex.gap-2 {
    flex-wrap: wrap; /* permite que los botones salten de línea si no caben */
    justify-content: center;
}

.ag-theme-alpine .btn-outline-primary,
.ag-theme-alpine .btn-outline-warning,
.ag-theme-alpine .btn-outline-danger {
    white-space: nowrap; /* evita que el texto se parta */
}
</style>
@endpush

@section('content')
<div class="container-fluid mt-3">
    <div class="card shadow-sm border-0">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
            <h3 class="mb-0 fw-semibold">Usuarios</h3>
            <a class="btn btn-primary d-flex align-items-center" href="{{ route('users.create') }}">
                <i class="bi bi-plus-lg me-2"></i> Nuevo Usuario
            </a>
        </div>

        <div class="card-body">
            <div class="table-wrapper">
                <div class="modal-body" style="max-height: 700px; overflow-y: auto;">
                <div id="usersGrid" class="ag-theme-alpine"></div>

                           </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-javascript')
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    let gridApi = null;

    const columnDefs = [
        { headerName: '#', valueGetter: 'node.rowIndex + 1', width: 80 },
        { field: 'name', headerName: 'Nombre', flex: 1 },
        { field: 'email', headerName: 'Email', flex: 1.5 },
        { field: 'empresa', headerName: 'Empresa', flex: 1 },
        {
            field: 'roles',
            headerName: 'Roles',
            flex: 1.5,
            cellRenderer: (params) => {
                if (!params.value?.length) return '<span class="badge bg-secondary">Sin rol</span>';
                return params.value.map(r => `<span class="badge bg-success me-1">${r}</span>`).join('');
            }
        },
        {
            headerName: 'Acciones',
          minWidth: 220,
flex: 1.2,
            cellRenderer: (params) => {

              const id = params.data.id;
        const email = params.data.email;
        const name = params.data.name;

        return `
  <div class="d-flex justify-content-center align-items-center gap-2">

    <a href="/users/${id}/edit"
       class="btn btn-sm btn-outline-primary p-1"
       data-bs-toggle="tooltip"
       title="Editar usuario">
       <i class="bi bi-pencil-square fs-5"></i>
    </a>

    <button type="button"
        class="btn btn-sm btn-outline-warning p-1"
        onclick="resetPassword(${id}, '${name}', '${email}')"
        data-bs-toggle="tooltip"
        title="Restablecer contraseña">
        <i class="bi bi-key fs-5"></i>
    </button>

    <form method="POST" action="/users/${id}"
          onsubmit="return confirm('¿Eliminar este usuario?')"
          class="m-0 p-0">
        <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').content}">
        <input type="hidden" name="_method" value="DELETE">
        <button type="submit"
            class="btn btn-sm btn-outline-danger p-1"
            data-bs-toggle="tooltip"
            title="Eliminar usuario">
            <i class="bi bi-trash fs-5"></i>
        </button>
    </form>

  </div>
`;
    }
        }
    ];

    const gridOptions = {
        columnDefs,
        defaultColDef: { sortable: true, filter: true, resizable: true },
        rowData: [],
        pagination: true,
        paginationPageSize: 15,
    paginationPageSizeSelector: [15, 20, 50, 100],
        domLayout: 'autoHeight',
        animateRows: true,
        onGridReady: (params) => {
            gridApi = params.api;
            cargarUsuarios();
        }
    };

    const gridDiv = document.querySelector('#usersGrid');
    agGrid.createGrid(gridDiv, gridOptions);

   



    function cargarUsuarios() {
        fetch('{{ route('users.index') }}?json=1')
            .then(r => r.json())
            .then(users => {
                const formatted = users.map(u => ({
                    id: u.id,
                    name: u.name,
                    email: u.email,
                    empresa: u.empresa?.nombre ?? '—',
                    roles: u.roles
                }));
                gridApi.setGridOption('rowData', formatted);
               
            })
            .catch(err => console.error("Error al cargar usuarios:", err));
    }


  
});

 function resetPassword(id, name, email) {
    Swal.fire({
        title: `¿Restablecer contraseña de ${name}?`,
        text: "Se generará una contraseña temporal nueva.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, continuar",
        cancelButtonText: "Cancelar"
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: "Generando contraseña temporal...",
                didOpen: () => {
                    Swal.showLoading();
                    fetch(`/users/${id}/reset-password`, {
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                            "Accept": "application/json"
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (!data.success) throw new Error(data.message);
                        
                        Swal.fire({
                            title: "Contraseña generada",
                            html: `
                                <p><strong>Usuario:</strong> ${data.name}</p>
                                <p><strong>Correo:</strong> ${data.email}</p>
                                <p><strong>Contraseña temporal:</strong></p>
                                <div class="input-group mb-3">
                                    <input id="tempPass" type="text" class="form-control" readonly value="${data.temp_password}">
                                    <button class="btn btn-outline-secondary" onclick="copyPassword()">📋</button>
                                </div>
                                <div class="d-flex justify-content-center gap-2 mt-2">
                                    <button class="btn btn-primary" onclick="sendByEmail('${data.email}', '${data.temp_password}')">Enviar por Email</button>
                                    <button class="btn btn-success" onclick="sendByWhatsapp('${data.temp_password}')">Enviar por WhatsApp</button>
                                </div>
                            `,
                            icon: "success",
                            showConfirmButton: false,
                            width: 500
                        });
                    })
                    .catch(err => {
                        Swal.fire("Error", err.message, "error");
                    });
                }
            });
        }
    });
}

function copyPassword() {
    const input = document.getElementById('tempPass');
    input.select();
    document.execCommand('copy');
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: 'success',
        title: 'Contraseña copiada',
        showConfirmButton: false,
        timer: 1500
    });
}

function sendByEmail(email, password) {
    Swal.fire({
        icon: 'info',
        title: '📧 Enviando correo...',
        showConfirmButton: false,
        didOpen: () => {
            Swal.showLoading();
            // Aquí puedes hacer un fetch a una ruta backend que envíe el correo
            setTimeout(() => {
                Swal.fire('Correo enviado', `Se envió la contraseña temporal a ${email}`, 'success');
            }, 1500);
        }
    });
}

function sendByWhatsapp(password) {
    const msg = encodeURIComponent(`Tu contraseña temporal es: ${password}`);
    const url = `https://wa.me/?text=${msg}`;
    window.open(url, '_blank');
}


</script>
@endpush
