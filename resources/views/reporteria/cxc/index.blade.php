@extends('layouts.app')

@section('template_title')
    Buscador
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/5.0.1/css/fixedColumns.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.min.css" />
@endsection

<style>
    /* Centrado de la vista previa del PDF */
    #pdf-preview-container {
        text-align: center;
        margin-top: 20px;
        margin-bottom: 20px;
    }

    #pdf-canvas {
        border: 1px solid #ddd;
        margin: 0 auto;
    }
</style>

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Reporte de Cuentas por Cobrar</h5>
                    </div>
                    <div class="card-body">
                        <form method="GET" action="{{ route('reporteria.advance') }}">
                            <div class="row">
                                <div class="col-md-3">
                                    <label for="id_client">Cliente</label>
                                    <select name="id_client" id="id_client" class="form-control">
                                        <option value="">Seleccionar Cliente</option>
                                        @foreach ($clientes as $cliente)
                                            <option value="{{ $cliente->id }}"
                                                {{ request('id_client') == $cliente->id ? 'selected' : '' }}>
                                                {{ $cliente->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="id_subcliente">Subcliente</label>
                                    <select name="id_subcliente" id="id_subcliente" class="form-control">
                                        <option value="">Seleccionar Subcliente</option>
                                        @foreach ($subclientes as $subcliente)
                                            <option value="{{ $subcliente->id }}"
                                                {{ request('id_subcliente') == $subcliente->id ? 'selected' : '' }}>
                                                {{ $subcliente->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-3">
                                    <label for="id_proveedor">Proveedor</label>
                                    <select name="id_proveedor" id="id_proveedor" class="form-control">
                                        <option value="">Seleccionar Proveedor</option>
                                        @foreach ($proveedores as $proveedor)
                                            <option value="{{ $proveedor->id }}"
                                                {{ request('id_proveedor') == $proveedor->id ? 'selected' : '' }}>
                                                {{ $proveedor->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm mt-3">Buscar</button>
                                </div>
                            </div>
                        </form>

                        <div class="d-flex justify-content-end my-2">
                            <button type="button" id="btnAsignarEdoCuenta" class="btn btn-primary d-none"
                                data-bs-toggle="modal" data-bs-target="#modalAsignarEdoCuenta">
                                Asignar No Edo cuenta
                            </button>
                        </div>

                        <div class="table-responsive">
                            <div class="mb-3"></div>
                            <div class="mb-3">
                                <button type="button" id="selectAllButton" class="btn btn-outline-secondary btn-sm">
                                    Seleccionar todo
                                </button>
                            </div>
                            <form id="exportForm" action="{{ route('cotizaciones.export') }}" method="POST">
                                @csrf
                                <table class="table table-flush" id="datatable-search">
                                    <thead class="thead">
                                        <tr>
                                            <th></th>
                                            <th>#</th>
                                            <th>Edo. Cuenta</th>
                                            <th>Fecha inicio</th>
                                            <th>
                                                <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt=""
                                                    width="25px" />
                                                Cliente
                                            </th>
                                            <th>
                                                <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt=""
                                                    width="25px" />
                                                Subcliente
                                            </th>
                                            <th>
                                                <img src="{{ asset('img/icon/gps.webp') }}" alt=""
                                                    width="25px" />
                                                Origen
                                            </th>
                                            <th>
                                                <img src="{{ asset('img/icon/origen.png') }}" alt=""
                                                    width="25px" />
                                                Destino
                                            </th>
                                            <th>
                                                <img src="{{ asset('img/icon/contenedor.png') }}" alt=""
                                                    width="25px" />
                                                # Contenedor
                                            </th>
                                            <th>Tipo</th>
                                            <th>
                                                <img src="{{ asset('img/icon/semaforos.webp') }}" alt=""
                                                    width="25px" />
                                                Estatus
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (Route::currentRouteName() != 'index.reporteria')
                                            @foreach ($cotizaciones as $cotizacion)
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" name="cotizacion_ids[]"
                                                            value="{{ $cotizacion->id }}"
                                                            class="select-checkbox visually-hidden" />
                                                    </td>
                                                    <td>{{ $cotizacion->id }}</td>
                                                    <td>{{ $cotizacion->edo_cuenta ?? 'NA' }}</td>
                                                    <td>
                                                        {{ optional($cotizacion->DocCotizacion->Asignaciones)->fehca_inicio_guard ? Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fehca_inicio_guard)->format('d-m-Y') : 'Sin fecha' }}
                                                    </td>

                                                    <td>{{ $cotizacion->Cliente->nombre }}</td>
                                                    <td>{{ $cotizacion->Subcliente->nombre ?? '-' }}</td>
                                                    <td>{{ $cotizacion->origen }}</td>
                                                    <td>{{ $cotizacion->destino }}</td>
                                                    @php
                                                        $docPrincipal = optional($cotizacion->DocCotizacion);
                                                        $numContenedor = $docPrincipal->num_contenedor ?? '';

                                                        if (
                                                            $cotizacion->jerarquia === 'Principal' &&
                                                            $cotizacion->referencia_full
                                                        ) {
                                                            $cotSecundaria = \App\Models\Cotizaciones::where(
                                                                'referencia_full',
                                                                $cotizacion->referencia_full,
                                                            )
                                                                ->where('jerarquia', 'Secundario')
                                                                ->with('DocCotizacion')
                                                                ->first();

                                                            $docSecundaria = optional($cotSecundaria)->DocCotizacion;
                                                            if ($docSecundaria && $docSecundaria->num_contenedor) {
                                                                $numContenedor .=
                                                                    ' / ' . $docSecundaria->num_contenedor;
                                                            }
                                                        }
                                                    @endphp

                                                    <td>{{ $numContenedor }}</td>

                                                    <td>
                                                        {{ $cotizacion->jerarquia === 'Principal' && $cotizacion->referencia_full ? 'Full' : 'Sencillo' }}
                                                    </td>

                                                    <td>
                                                        @can('cotizaciones-estatus')
                                                            <button type="button"
                                                                class="btn btn-outline-{{ $cotizacion->estatus == 'Aprobada' ? 'info' : 'success' }} btn-xs">
                                                                {{ $cotizacion->estatus }}
                                                            </button>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                                <button type="button" id="exportButton" data-filetype="pdf"
                                    class="btn btn-outline-secondary btn-sm exportButton">
                                    Vista previa
                                </button>
                                <div id="warningMessage" class="alert alert-warning d-none" role="alert">
                                    <strong>Advertencia!</strong>
                                    Debes seleccionar al menos una casilla para visualizar el reporte.
                                </div>
                                <div id="pdf-preview-container" class="d-none">
                                    <canvas id="pdf-canvas"></canvas>
                                    <div class="button-container">
                                        @if (isset($cotizaciones) && $cotizaciones != null)
                                            <button type="button" id="exportButtonExcel1" data-filetype="xlsx"
                                                class="btn btn-outline-secondary btn-sm exportButton">
                                                Exportar a Excel
                                            </button>
                                            <input type="hidden" id="txtDataCotizaciones"
                                                value="{{ json_encode($cotizaciones) }}" />
                                        @endif

                                        <button type="button" id="downloadPdfButton"
                                            class="btn btn-outline-secondary btn-sm d-none">
                                            Exportar a PDF
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalAsignarEdoCuenta" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-md modal-dialog-centered">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Asignar No Edo cuenta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <input type="hidden" id="registroSeleccionado">

                    <div class="mb-3">
                        <label class="form-label">Número de estado de cuenta</label>
                        <input type="text" class="form-control" id="noEdoCuenta">
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-primary" id="btnGuardarEdoCuenta">
                        Guardar
                    </button>
                </div>

            </div>
        </div>
    </div>

@endsection

@section('datatable')
    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js') }}"></script>

    <!-- JS -->
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> --}}
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/dataTables.fixedColumns.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/fixedColumns.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.10.377/pdf.min.js"></script>
    <script>
        $(document).ready(function() {
            $('.cliente').select2();
            $('.proveedor').select2();

            const table = $('#datatable-search').DataTable({
                columnDefs: [{
                    orderable: false,
                    className: 'select-checkbox',
                    targets: 0,
                }, ],
                fixedColumns: {
                    start: 2,
                },
                order: [
                    [1, 'asc']
                ],
                paging: true,
                pageLength: 30,
                select: {
                    style: 'multi',
                    selector: 'td:first-child',
                },
            });

            // Actualización del botón "Seleccionar todo" al cambiar la selección de filas
            table.on('select deselect', function() {

                const selectedCount = table.rows({
                    selected: true
                }).count();

                // Botón seleccionar todo (tu lógica actual)
                if (selectedCount === table.rows().count()) {
                    $('#selectAllButton').text('Deseleccionar todo');
                } else {
                    $('#selectAllButton').text('Seleccionar todo');
                }

                // 🔹 Mostrar / ocultar botón Asignar
                if (selectedCount > 0) {
                    $('#btnAsignarEdoCuenta').removeClass('d-none');
                } else {
                    $('#btnAsignarEdoCuenta').addClass('d-none');
                }
            });

            // Botón "Seleccionar todo" para seleccionar/desmarcar todas las filas
            $('#selectAllButton').on('click', function() {
                if (
                    table
                    .rows({
                        selected: true,
                    })
                    .count() === table.rows().count()
                ) {
                    // Si todas las filas están seleccionadas, deseleccionarlas
                    table.rows().deselect();
                    $(this).text('Seleccionar todo'); // Cambiar el texto del botón
                } else {
                    // Si no todas las filas están seleccionadas, seleccionarlas
                    table.rows().select();
                    $(this).text('Deseleccionar todo'); // Cambiar el texto del botón
                }
            });

            $('.exportButton').on('click', function(event) {
                const selectedIds = table
                    .rows('.selected')
                    .data()
                    .toArray()
                    .map((row) => row[1]); // Obtener los IDs seleccionados

                if (selectedIds.length === 0) {
                    $('#warningMessage').removeClass('d-none');
                    return;
                }

                $('#warningMessage').addClass('d-none'); // Ocultar advertencia

                var fileType = $('#' + event.target.id).data('filetype');

                // Enviar los IDs seleccionados al controlador por Ajax
                $.ajax({
                    url: '{{ route('cotizaciones.export') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        selected_ids: selectedIds,
                        fileType: fileType,
                    },
                    xhrFields: {
                        responseType: 'blob', // Indicar que esperamos una respuesta tipo blob (archivo)
                    },
                    success: function(response) {
                        var blob = new Blob([response], {
                            type: 'application/' + fileType,
                        });
                        var url = URL.createObjectURL(blob);

                        if (fileType === 'pdf') {
                            // Mostrar el contenedor del PDF para vista previa
                            $('#pdf-preview-container').removeClass('d-none');

                            // Cargar el PDF en el canvas
                            var canvas = document.getElementById('pdf-canvas');
                            var context = canvas.getContext('2d');
                            var pdfUrl = url;

                            // Usar PDF.js para renderizar el PDF
                            pdfjsLib.getDocument(pdfUrl).promise.then(function(pdf) {
                                pdf.getPage(1).then(function(page) {
                                    var scale = 1.5;
                                    var viewport = page.getViewport({
                                        scale: scale,
                                    });

                                    canvas.height = viewport.height;
                                    canvas.width = viewport.width;

                                    page.render({
                                        canvasContext: context,
                                        viewport: viewport,
                                    });
                                });
                            });

                            // Mostrar el botón de descarga del PDF
                            $('#downloadPdfButton').removeClass('d-none');

                            // Agregar un evento para descargar el PDF
                            $('#downloadPdfButton').on('click', function() {
                                var a = document.createElement('a');
                                a.href = url;
                                a.download =
                                    'cxc_' +
                                    new Date().toLocaleDateString('es-ES').replaceAll(
                                        '/', '-') +
                                    '_' +
                                    new Date()
                                    .toLocaleTimeString('es-ES', {
                                        hour12: false,
                                    })
                                    .replaceAll(':', '_') +
                                    '.pdf';

                                document.body.appendChild(a);
                                a.click();
                                document.body.removeChild(a);
                                // No ocultar la vista previa después de la descarga
                                window.URL.revokeObjectURL(url);
                            });
                        } else if (fileType === 'xlsx') {
                            // Lógica para descargar el archivo Excel (si es necesario)
                            var a = document.createElement('a');
                            a.href = url;
                            a.download =
                                'cxc_' +
                                new Date().toLocaleDateString('es-ES').replaceAll('/', '-') +
                                '_' +
                                new Date()
                                .toLocaleTimeString('es-ES', {
                                    hour12: false,
                                })
                                .replaceAll(':', '_') +
                                '.xlsx';

                            document.body.appendChild(a);
                            a.click();
                            document.body.removeChild(a);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        alert('Ocurrió un error al exportar los datos.');
                    },
                });
            });

            $('#id_client').on('change', function() {
                var clientId = $(this).val();
                if (clientId) {
                    $.ajax({
                        url: '/subclientes/' + clientId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#id_subcliente').empty();
                            $('#id_subcliente').append(
                                '<option selected value="">Seleccionar subcliente</option>');
                            $.each(data, function(key, subcliente) {
                                $('#id_subcliente').append(
                                    '<option value="' + subcliente.id + '">' +
                                    subcliente.nombre + '</option>',
                                );
                            });
                        },
                    });
                } else {
                    $('#id_subcliente').empty();
                    $('#id_subcliente').append('<option selected value="">Seleccionar subcliente</option>');
                }
            });
        });
    </script>
@endsection
