@extends('layouts.app')

@section('template_title')
    Liquidados CXC
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/5.0.1/css/fixedColumns.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Reporte liquidados CXC</h5>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-sm-12">
                                    <div class="card">
                                        <form action="{{ route('advance_liquidados.buscador') }}" method="GET">
                                            <div class="card-body" style="padding-left: 1.5rem; padding-top: 1rem">
                                                <h5>Filtros</h5>
                                                <div class="row align-items-end">
                                                    <div class="col-md-2 mb-2">
                                                        <label for="id_client" class="form-label text-sm mb-1">Buscar cliente:</label>
                                                        <select
                                                            class="form-control cliente"
                                                            name="id_client"
                                                            id="id_client"
                                                        >
                                                            <option value="">Todos los clientes</option>
                                                            @foreach ($clientes as $client)
                                                                <option value="{{ $client->id }}" {{ request('id_client') == $client->id ? 'selected' : '' }}>
                                                                    {{ $client->nombre }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 mb-2">
                                                        <label for="id_subcliente" class="form-label text-sm mb-1">Buscar subcliente:</label>
                                                        <select
                                                            class="form-control subcliente"
                                                            name="id_subcliente"
                                                            id="id_subcliente"
                                                        >
                                                            <option value="">Todos los subclientes</option>
                                                            @foreach ($subclientes as $subclient)
                                                                <option value="{{ $subclient->id }}" {{ request('id_subcliente') == $subclient->id ? 'selected' : '' }}>
                                                                    {{ $subclient->nombre }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-2 mb-2">
                                                        <label for="id_unidad" class="form-label text-sm mb-1">Buscar unidad (equipo):</label>
                                                        <select
                                                            class="form-control unidad"
                                                            name="id_unidad"
                                                            id="id_unidad"
                                                        >
                                                            <option value="">Todas las unidades</option>
                                                            @foreach ($equipos as $equipo)
                                                                <option value="{{ $equipo->id }}" {{ request('id_unidad') == $equipo->id ? 'selected' : '' }}>
                                                                    {{ $equipo->id_equipo }} - {{ $equipo->marca }} {{ $equipo->modelo }} ({{ $equipo->placas }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                    <div class="col-md-3 mb-2">
                                                        <label class="form-label text-sm mb-1">Periodo (Rango de fechas):</label>
                                                        <input type="text" id="daterange" readonly class="form-control form-control-sm" style="background-color: #fff;" />
                                                        <input type="hidden" name="fecha_inicio" id="fecha_inicio" value="{{ request('fecha_inicio') }}">
                                                        <input type="hidden" name="fecha_fin" id="fecha_fin" value="{{ request('fecha_fin') }}">
                                                    </div>
                                                    <div class="col-md-3 mb-2 d-flex gap-2">
                                                        <button
                                                            class="btn btn-sm mb-0 p-2"
                                                            type="submit"
                                                            style="background-color: #f82018; color: #ffffff"
                                                        >
                                                            Buscar
                                                        </button>
                                                        <a href="{{ route('index_liquidados_cxc.reporteria') }}" class="btn btn-sm btn-outline-secondary mb-0 p-2">
                                                            Limpiar
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <div class="mb-3"></div>
                            <div class="mb-3">
                                <button type="button" id="selectAllButton" class="btn btn-primary">
                                    Seleccionar todo
                                </button>
                            </div>
                            <form id="exportForm" action="{{ route('liquidados_cxc.export') }}" method="POST">
                                @csrf
                                <table class="table table-flush" id="datatable-search">
                                    <thead class="thead">
                                        <tr>
                                            <th></th>
                                            <th>#</th>
                                            <th>
                                                <img
                                                    src="{{ asset('img/icon/user_predeterminado.webp') }}"
                                                    alt=""
                                                    width="25px"
                                                />
                                                Cliente
                                            </th>
                                            <th>
                                                <img
                                                    src="{{ asset('img/icon/user_predeterminado.webp') }}"
                                                    alt=""
                                                    width="25px"
                                                />
                                                Subcliente
                                            </th>
                                            <th>
                                                <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px" />
                                                Origen
                                            </th>
                                            <th>
                                                <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px" />
                                                Destino
                                            </th>
                                            <th>
                                                <img
                                                    src="{{ asset('img/icon/contenedor.png') }}"
                                                    alt=""
                                                    width="25px"
                                                />
                                                # Contenedor
                                            </th>
                                            <th>
                                                <img
                                                    src="{{ asset('img/icon/semaforos.webp') }}"
                                                    alt=""
                                                    width="25px"
                                                />
                                                Estatus
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (Route::currentRouteName() != 'index_liquidados_cxc.reporteria')
                                            @foreach ($cotizaciones as $cotizacion)
                                                <tr>
                                                    <td>
                                                        <input
                                                            type="checkbox"
                                                            name="cotizacion_ids[]"
                                                            value="{{ $cotizacion->id }}"
                                                            class="select-checkbox visually-hidden"
                                                        />
                                                    </td>
                                                    <td>{{ $cotizacion->id }}</td>
                                                    <td>{{ $cotizacion->Cliente->nombre ?? '-' }}</td>
                                                    <td>{{ $cotizacion->Subcliente->nombre ?? '-' }}</td>
                                                    <td>{{ $cotizacion->origen }}</td>
                                                    <td>{{ $cotizacion->destino }}</td>
                                                    <td>{{ $cotizacion->DocCotizacion->num_contenedor ?? '-' }}</td>
                                                    <td>
                                                        @can('cotizaciones-estatus')
                                                            <button
                                                                type="button"
                                                                class="btn btn-outline-{{ $cotizacion->estatus == 'Aprobada' ? 'info' : 'success' }} btn-xs"
                                                            >
                                                                {{ $cotizacion->estatus }}
                                                            </button>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                                @if (isset($cotizaciones) && $cotizaciones->count() > 0)
                                    <input
                                        type="hidden"
                                        id="txtDataGenericExcel"
                                        value="{{ json_encode($cotizaciones) }}"
                                    />
                                    <button
                                        type="button"
                                        id="exportButtonExcel"
                                        data-filetype="xlsx"
                                        class="btn btn-success exportButton"
                                    >
                                        Exportar a Excel
                                    </button>
                                    <button
                                        type="button"
                                        id="exportButton"
                                        data-filetype="pdf"
                                        class="btn btn-primary exportButton"
                                    >
                                        Exportar a PDF
                                    </button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('datatable')
    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js') }}"></script>

    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/dataTables.fixedColumns.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/fixedColumns.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.min.js"></script>

    <!-- Moment & DateRangePicker JS -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        $(document).ready(function () {
            $('.cliente, .subcliente, .unidad').select2();

            // Date Range Picker
            const startVal = $('#fecha_inicio').val() ? moment($('#fecha_inicio').val()) : moment().startOf('month');
            const endVal = $('#fecha_fin').val() ? moment($('#fecha_fin').val()) : moment().endOf('month');

            $('#daterange').daterangepicker({
                startDate: startVal,
                endDate: endVal,
                opens: 'right',
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' AL ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: [
                        'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                        'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre',
                    ],
                    firstDay: 1,
                },
                ranges: {
                    'Hoy': [moment(), moment()],
                    'Últimos 7 días': [moment().subtract(6, 'days'), moment()],
                    'Últimos 30 días': [moment().subtract(29, 'days'), moment()],
                    'Este mes': [moment().startOf('month'), moment().endOf('month')],
                    'Mes anterior': [
                        moment().subtract(1, 'month').startOf('month'),
                        moment().subtract(1, 'month').endOf('month')
                    ]
                }
            }, function (start, end) {
                $('#fecha_inicio').val(start.format('YYYY-MM-DD'));
                $('#fecha_fin').val(end.format('YYYY-MM-DD'));
            });

            if (!$('#fecha_inicio').val()) {
                $('#fecha_inicio').val(startVal.format('YYYY-MM-DD'));
                $('#fecha_fin').val(endVal.format('YYYY-MM-DD'));
            }

            const table = $('#datatable-search').DataTable({
                columnDefs: [
                    {
                        orderable: false,
                        className: 'select-checkbox',
                        targets: 0,
                    },
                ],
                fixedColumns: {
                    start: 2,
                },
                order: [[1, 'asc']],
                paging: true,
                pageLength: 30,

                select: {
                    style: 'multi',
                    selector: 'td:first-child',
                },
            });

            // Función para manejar el botón "Seleccionar todo"
            $('#selectAllButton').on('click', function () {
                if (table.rows({ selected: true }).count() === table.rows().count()) {
                    table.rows().deselect();
                    $(this).text('Seleccionar todo');
                } else {
                    table.rows().select();
                    $(this).text('Deseleccionar todo');
                }
            });

            // Detectar cuando las filas cambian de estado
            table.on('select deselect', function () {
                if (table.rows({ selected: true }).count() === table.rows().count()) {
                    $('#selectAllButton').text('Deseleccionar todo');
                } else {
                    $('#selectAllButton').text('Seleccionar todo');
                }
            });

            // Función para la exportación de datos seleccionados
            $('.exportButton').on('click', function (event) {
                event.preventDefault();
                const selectedIds = table
                    .rows('.selected')
                    .data()
                    .toArray()
                    .map((row) => row[1]);

                var fileType = $('#' + event.target.id).data('filetype');

                $.ajax({
                    url: '{{ route('liquidados_cxc.export') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        selected_ids: selectedIds,
                        fileType: fileType,
                    },
                    xhrFields: {
                        responseType: 'blob',
                    },
                    success: function (response, status, xhr) {
                        var blob = new Blob([response], { type: 'application/' + fileType });
                        var url = URL.createObjectURL(blob);

                        var downloadName = 'cxc_' + fileType;
                        var disposition = xhr.getResponseHeader('Content-Disposition');
                        if (disposition && disposition.indexOf('filename=') !== -1) {
                            var matches = /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/.exec(disposition);
                            if (matches != null && matches[1]) {
                                downloadName = matches[1].replace(/['"]/g, '');
                            }
                        }

                        var a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = downloadName;
                        document.body.appendChild(a);

                        a.click();
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);
                    },
                    error: function (xhr, status, error) {
                        console.error(error);
                        alert('Ocurrió un error al exportar los datos.');
                    },
                });
            });
        });

        // Función para actualizar los subclientes en función del cliente seleccionado
        $(document).ready(function () {
            $('#id_client').on('change', function () {
                var clientId = $(this).val();
                if (clientId) {
                    $.ajax({
                        url: '/subclientes/' + clientId,
                        type: 'GET',
                        dataType: 'json',
                        success: function (data) {
                            $('#id_subcliente').empty();
                            $('#id_subcliente').append('<option value="">Todos los subclientes</option>');
                            $.each(data, function (key, subcliente) {
                                $('#id_subcliente').append(
                                    '<option value="' + subcliente.id + '">' + subcliente.nombre + '</option>',
                                );
                            });
                        },
                    });
                } else {
                    $('#id_subcliente').empty();
                    $('#id_subcliente').append('<option value="">Todos los subclientes</option>');
                }
            });
        });
    </script>
@endsection

@push('custom-javascript')
    <script src="{{ asset('js/reporteria/genericExcel.js') }}"></script>
@endpush

