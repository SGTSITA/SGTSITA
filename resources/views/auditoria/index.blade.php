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
                        <div class="col-md-3">
                            <label class="form-label">Empresa</label>
                            <select id="filtroEmpresa" class="form-select">
                                <option value="">Todas</option>
                                @foreach ($empresas as $empresa)
                                    <option value="{{ $empresa->id }}">{{ $empresa->id }}-{{ $empresa->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Referencia</label>
                            <input type="text" id="contenedor" class="form-control" placeholder="Ej: ABC123">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Rango de fechas</label>
                            <input type="text" id="auditoriaRange" class="form-control"
                                placeholder="Selecciona un rango">
                        </div>

                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-primary w-100" id="btnBuscar">
                                Buscar
                            </button>
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

        const hoy = moment();
        const hace15Dias = moment().subtract(15, 'days');

        $('#auditoriaRange').daterangepicker({
            startDate: hace15Dias,
            endDate: hoy,
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

        document.getElementById('btnBuscar').addEventListener('click', function() {
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
            const contenedor = document.getElementById('contenedor').value;

            if (contenedor) {
                params.append('referencia', contenedor);
            }



            const empresaId = document.getElementById('filtroEmpresa').value;
            if (empresaId) {
                params.append('empresa_id', empresaId);
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
                headerName: 'Empresa',
                valueGetter: p => p.data.empresa?.nombre ?? 'Sistema'
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
                headerName: 'Referencia',
                field: 'referencia'
            },
            {
                headerName: 'Campos modificados',
                field: 'campos_modificados'
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

        function getColor(action) {
            return {
                created: 'success',
                updated: 'warning',
                deleted: 'danger'
            } [action] || 'secondary';
        }

        function formatValue(val) {
            if (val === null || val === undefined || val === '') return '<i class="text-muted">—</i>';

            if (typeof val === 'object') {
                return `<pre>${JSON.stringify(val, null, 2)}</pre>`;
            }

            return val;
        }

        function formatField(field) {
            return field
                .replace(/_/g, ' ')
                .replace(/\b\w/g, l => l.toUpperCase());
        }

        function togglePayload() {
            const el = document.getElementById('payloadBox');
            el.style.display = el.style.display === 'none' ? 'block' : 'none';
        }



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
        <div class="mb-3">
            <p><b>Acción:</b>
                <span class="badge bg-${getColor(data.accion)}">
                    ${data.accion.toUpperCase()}
                </span>
            </p>
            <p><b>Modelo:</b> ${data.modelo} #${data.modelo_id}</p>
            <p><b>Usuario:</b> ${data.usuario}</p>
            <p><b>Empresa:</b> ${data.empresa ?? 'N/A'}</p>
            <p><b>Referencia:</b> ${data.referencia ?? 'N/A'}</p>
            <p><b>Fecha:</b> ${data.fecha}</p>
        </div>

        <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-light">
                <tr>
                    <th style="width:25%">Campo</th>
                    <th style="width:35%">Antes</th>
                    <th style="width:35%">Después</th>
                </tr>
            </thead>
            <tbody>
    `;

            const keys = new Set([
                ...Object.keys(data.old || {}),
                ...Object.keys(data.new || {})
            ]);

            keys.forEach(key => {

                const oldVal = formatValue(data.old?.[key]);
                const newVal = formatValue(data.new?.[key]);

                const changed = oldVal !== newVal;

                html += `
            <tr class="${changed ? 'table-warning' : ''}">
                <td><b>${formatField(key)}</b></td>
                <td class="text-danger">${oldVal}</td>
                <td class="text-success">${newVal}</td>
            </tr>
        `;
            });

            html += '</tbody></table></div>';


            if (data.request_payload) {
                html += `
            <hr>
            <button class="btn btn-sm btn-outline-secondary mb-2" onclick="togglePayload()">
                Ver request
            </button>
            <pre id="payloadBox" style="display:none;max-height:300px;overflow:auto;background:#111;color:#0f0;padding:10px;">
${JSON.stringify(data.request_payload, null, 2)}
            </pre>
        `;
            }


            html += `
<a href="/admin/auditoria/${data.id}/pdf" target="_blank"
   class="btn btn-danger btn-sm">
   Descargar PDF
</a>

<a href="/admin/auditoria/${data.id}/pdf?payload=1" target="_blank"
   class="btn btn-secondary btn-sm">
   PDF con payload
</a>
`;

            document.getElementById('auditoriaDetalle').innerHTML = html;
        }
    </script>
@endpush
