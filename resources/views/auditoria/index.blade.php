@extends('layouts.app')

@section('template_title', 'Auditoría del sistema')

@section('breadcrumb')
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb bg-transparent mb-0 pb-0 pt-1 px-0">
            <li class="breadcrumb-item text-sm">
                <a class="opacity-5 text-white" href="#">Admin</a>
            </li>
            <li class="breadcrumb-item text-sm text-white active" aria-current="page">
                Auditoría
            </li>
        </ol>
    </nav>
@endsection


@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Auditoría del sistema</h5>
                    <p class="text-sm mb-0">
                        Registro de altas, cambios y eliminaciones
                    </p>
                </div>

                <div class="card-body">
                    <div id="auditoriaGrid" class="ag-theme-quartz" style="height: 600px;"></div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalAuditoria" tabindex="-1">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Detalle de Auditoría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div id="auditoriaDetalle"></div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <script>
        const rowData = @json($logs);

        const columnDefs = [{
                headerName: 'Fecha',
                field: 'created_at',
                sortable: true,
                filter: true
            },
            {
                headerName: 'Usuario',
                valueGetter: p => p.data.user?.name ?? 'Sistema',
                filter: true
            },
            {
                headerName: 'Modelo',
                field: 'model',
                filter: true
            },
            {
                headerName: 'ID Modelo',
                field: 'model_id',
                filter: 'agNumberColumnFilter'
            },
            {
                headerName: 'Acción',
                field: 'action',
                cellRenderer: p => {
                    const map = {
                        created: 'success',
                        updated: 'warning',
                        deleted: 'danger'
                    };
                    const color = map[p.value] ?? 'info';
                    return `<span class="badge bg-${color}">${p.value.toUpperCase()}</span>`;
                },
                filter: true
            },
            {
                headerName: 'IP',
                field: 'ip',
                filter: true
            },
            {
                headerName: 'Detalle',
                cellRenderer: params => {
                    return `
            <button class="btn btn-sm btn-primary"
                onclick="verAuditoria(${params.data.id})">
                Ver
            </button>
        `;
                }
            }
        ];

        const gridOptions = {
            columnDefs,
            rowData,
            pagination: true,
            paginationPageSize: 20,
            animateRows: true,
            defaultColDef: {
                resizable: true,
                sortable: true,
                filter: true
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            const gridDiv = document.querySelector('#auditoriaGrid');
            agGrid.createGrid(gridDiv, gridOptions);
        });


        function verAuditoria(id) {
            fetch(`/admin/auditoria/${id}`)
                .then(r => r.json())
                .then(data => {
                    renderDetalle(data);
                    new bootstrap.Modal('#modalAuditoria').show();
                });
        }

        function renderDetalle(data) {
            let html = `
        <p><b>Acción:</b> ${data.accion}</p>
        <p><b>Modelo:</b> ${data.modelo} #${data.modelo_id}</p>
        <p><b>Usuario:</b> ${data.usuario}</p>
        <p><b>Fecha:</b> ${data.fecha}</p>

        <table class="table table-bordered table-sm">
            <thead>
                <tr>
                    <th>Campo</th>
                    <th>Antes</th>
                    <th>Después</th>
                </tr>
            </thead>
            <tbody>
    `;

            const keys = new Set([
                ...Object.keys(data.old || {}),
                ...Object.keys(data.new || {})
            ]);

            keys.forEach(key => {
                const oldVal = data.old?.[key] ?? '';
                const newVal = data.new?.[key] ?? '';
                const changed = oldVal != newVal;

                html += `
            <tr class="${changed ? 'table-warning' : ''}">
                <td>${key}</td>
                <td>${oldVal}</td>
                <td>${newVal}</td>
            </tr>
        `;
            });

            html += `</tbody></table>`;

            document.getElementById('auditoriaDetalle').innerHTML = html;
        }
    </script>
@endpush
