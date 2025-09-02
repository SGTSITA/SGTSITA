@extends('layouts.app')

@section('template_title')
    Usuarios
@endsection


@push('custom-css')
    <style>
        .table-wrapper {
            padding-top: 0.5rem;
            padding-bottom: 1rem;
            padding-left: 1rem;
            padding-right: 1rem;
        }
    </style>
@endpush



@section('content')
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col">
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header">
                        <div class="d-flex justify-content-between align-items-center">

                            <h3 class="mb-0">Usuarios</h3>

                            <a class="btn btn-ios d-flex align-items-center" href="{{ route('users.create') }}">
                                <i class="bi bi-plus-lg me-1"></i> Nuevo Usuario
                            </a>
                        </div>
                    </div>


                    <div class="table-responsive py-4" style="">
                        <div class="table-wrapper px-3 pb-3">
                            <div id="usersGrid" class="ag-theme-alpine rounded" style="height: 600px; width: 100%; "></div>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>

    {!! $data->render() !!}
@endsection

@push('custom-javascript')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let gridApi = null;

            const columnDefs = [{
                    headerName: 'No',
                    valueGetter: 'node.rowIndex + 1',
                    width: 80
                },
                {
                    field: 'name',
                    headerName: 'Nombre',
                    flex: 1
                },
                {
                    field: 'email',
                    headerName: 'Email',
                    flex: 1.5
                },
                {
                    field: 'empresa',
                    headerName: 'Empresa',
                    flex: 1
                },
                {
                    field: 'roles',
                    headerName: 'Roles',
                    flex: 1.5,
                    cellRenderer: (params) => {
                        if (!params.value || params.value.length === 0) {
                            return '<span class="badge badge-info">Sin rol</span>';
                        }
                        return params.value.map(r => `<span class="badge badge-success me-1">${r}</span>`)
                            .join('');
                    }
                },
                {
                    headerName: 'Acciones',
                    width: 250,
                    cellRenderer: (params) => {
                        const id = params.data.id;

                        return `
        <div class="d-flex justify-content-end gap-2 align-items-center">
            <a href="/users/${id}/edit"
               class="btn bg-white border border-light-subtle rounded-pill px-3 py-1 d-flex align-items-center gap-2"
               title="Editar"
               style="box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);">
                <i class="bi bi-pencil-square text-primary fs-5"></i>
                <span class="d-none d-md-inline text-primary fw-semibold">Editar</span>
            </a>

            <form method="POST" action="/users/${id}"
                  onsubmit="return confirm('¿Eliminar este usuario?')"
                  class="m-0 p-0">
                <input type="hidden" name="_token" value="${document.querySelector('meta[name=csrf-token]').getAttribute('content')}">
                <input type="hidden" name="_method" value="DELETE">
                <button type="submit"
                        class="btn bg-white border border-light-subtle rounded-pill px-3 py-1 d-flex align-items-center gap-2"
                        title="Eliminar"
                        style="box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);">
                    <i class="bi bi-trash text-danger fs-5"></i>
                    <span class="d-none d-md-inline text-danger fw-semibold">Eliminar</span>
                </button>
            </form>
        </div>
        `;
                    }
                }


            ];

            const gridOptions = {
                columnDefs: columnDefs,
                defaultColDef: {
                    sortable: true,
                    filter: true,
                    resizable: true
                },
                rowData: [],
                pagination: true,
                paginationPageSize: 10,
                domLayout: 'autoHeight',
                onGridReady: function(params) {
                    gridApi = params.api; // ✅ Guardar referencia para usar luego

                    // Cargar datos
                    fetch('{{ route('users.index') }}?json=1')
                        .then(response => response.json())
                        .then(users => {
                            const formatted = users.map(user => ({
                                id: user.id,
                                name: user.name,
                                email: user.email,
                                empresa: user.empresa?.nombre ?? '—',
                                roles: user.roles
                            }));
                            gridApi.setGridOption('rowData', formatted);
                        })
                        .catch(err => {
                            console.error("Error al cargar los usuarios:", err);
                        });
                }
            };

            const gridDiv = document.querySelector('#usersGrid');
            agGrid.createGrid(gridDiv, gridOptions); // ✅ Crea el grid correctamente
        });
    </script>
@endpush
