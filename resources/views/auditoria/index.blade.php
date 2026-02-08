@extends('layouts.app')

@section('template_title', 'Auditoría del sistema')

@section('content')
    <div class="row mt-4">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h5 class="mb-0">Auditoría del sistema</h5>
                    <p class="text-sm mb-0">Registro de altas, cambios y eliminaciones</p>

                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Rango de fechas</label>
                            <input type="text" id="auditoriaRange" class="form-control" placeholder="Selecciona un rango">
                        </div>
                    </div>
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
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>

    {{-- Moment --}}
    <script src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>

    {{-- DateRangePicker --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <script>
        let gridApi;
        let fechaInicio = null;
        let fechaFin = null;

        $('#auditoriaRange').daterangepicker({
            autoUpdateInput: false,
            opens: 'left',
            locale: {
                format: 'YYYY-MM-DD',
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Desde',
                toLabel: 'Hasta',
                customRangeLabel: 'Personalizado',
                daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                monthNames: [
                    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                    'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                ],
                firstDay: 1
            }
        }, function(start, end) {
            fechaInicio = start.format('YYYY-MM-DD');
            fechaFin = end.format('YYYY-MM-DD');

            $('#auditoriaRange').val(
                `${fechaInicio} AL ${fechaFin}`
            );

            cargarDatos();
        });

        function cargarDatos(page = 1) {
            const params = new URLSearchParams({
                page
            });

            if (fechaInicio && fechaFin) {
                params.append('fecha_inicio', fechaInicio);
                params.append('fecha_fin', fechaFin);
            }
            fetch(`/admin/auditoria-data/inicial?${params}`)
                .then(r => r.json())
                .then(resp => {
                    gridApi.setGridOption('rowData', resp);
                })
                .catch(err => {
                    console.error(err);
                    alert('Error al cargar auditoría');
                });
        }



        const columnDefs = [{
                headerName: 'Fecha',
                field: 'created_at'
            },
            {
                headerName: 'Usuario',
                valueGetter: p => p.data.user?.name ?? 'Sistema'
            },
            {
                headerName: 'Modelo',
                field: 'model'
            },
            {
                headerName: 'ID Modelo',
                field: 'model_id'
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
                }
            },
            {
                headerName: 'IP',
                field: 'ip'
            },
            {
                headerName: 'Detalle',
                cellRenderer: p => `
            <button class="btn btn-sm btn-primary" onclick="verAuditoria(${p.data.id})">
                Ver
            </button>
        `
            }
        ];



        const gridOptions = {
            columnDefs,
            pagination: true,
            paginationPageSize: 100,
            defaultColDef: {
                resizable: true,
                sortable: true,
                filter: true
            },
            onGridReady: params => {
                gridApi = params.api;
                cargarDatos(); // carga inicial
            }
        };

        document.addEventListener('DOMContentLoaded', () => {
            agGrid.createGrid(document.querySelector('#auditoriaGrid'), gridOptions);
        });




        // 🔍 DETALLE
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
                html += `
            <tr>
                <td>${key}</td>
                <td>${data.old?.[key] ?? ''}</td>
                <td>${data.new?.[key] ?? ''}</td>
            </tr>
        `;
            });

            html += '</tbody></table>';
            document.getElementById('auditoriaDetalle').innerHTML = html;
        }
    </script>
@endpush
