@extends('layouts.app')

@section('template_title')
    CXP
@endsection

@section('css')
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/fixedcolumns/5.0.1/css/fixedColumns.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/select/2.0.3/css/select.bootstrap5.min.css">
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5>Reporte de Cuentas por Pagar</h5>

                    </div>
                    <div class="card-body">
                        <!-- Mostrar advertencia si no se ha seleccionado proveedor -->
                        @if (isset($showWarning) && $showWarning)
                            <div class="alert alert-warning">
                                <strong>Advertencia!</strong> No se ha seleccionado un proveedor. Por favor, elija un
                                proveedor para realizar la búsqueda.
                            </div>
                        @endif
                        <form action="{{ route('ruta_advance_cxp') }}" method="GET">
                            <div class="card-body" style="padding-left: 1.5rem; padding-top: 1rem;">
                                <div class="row">
                                    <!-- Campo para proveedor -->
                                    <div class="col-3">
                                        <label for="id_proveedor">Buscar proveedor:</label>
                                        <select class="form-control cliente" name="id_proveedor" id="id_proveedor">
                                            <option selected value="">Seleccionar proveedor</option>
                                            @foreach ($proveedores as $proveedor)
                                                <option value="{{ $proveedor->id }}"
                                                    {{ request('id_proveedor') == $proveedor->id ? 'selected' : '' }}>
                                                    {{ $proveedor->nombre }} {{ $proveedor->telefono }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Campo para cliente -->
                                    <div class="col-3">
                                        <label for="id_cliente">Buscar cliente:</label>
                                        <select class="form-control cliente" name="id_cliente" id="id_cliente">
                                            <option selected value="">Seleccionar cliente</option>
                                            @foreach ($clientes as $cliente)
                                                <option value="{{ $cliente->id }}"
                                                    {{ request('id_cliente') == $cliente->id ? 'selected' : '' }}>
                                                    {{ $cliente->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Campo para subcliente -->
                                    <div class="col-3">
                                        <label for="id_subcliente">Buscar subcliente:</label>
                                        <select class="form-control cliente" name="id_subcliente" id="id_subcliente">
                                            <option selected value="">Seleccionar subcliente</option>
                                            @foreach ($subclientes as $subcliente)
                                                <option value="{{ $subcliente->id }}"
                                                    {{ request('id_subcliente') == $subcliente->id ? 'selected' : '' }}>
                                                    {{ $subcliente->nombre }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-3">
                                        <br>
                                        <button class="btn btn-sm mb-0 mt-sm-0 mt-1" type="submit"
                                            style="background-color: #F82018; color: #ffffff;">Buscar</button>
                                    </div>
                                </div>
                            </div>
                        </form>


                        <div class="table-responsive">
                            <div class="mb-3">
                            </div>
                            <div class="mb-3">
                                <button type="button" id="selectAllButton" class="btn btn-primary">Seleccionar
                                    todo</button>
                            </div>
                            <form id="exportForm" action="{{ route('cotizaciones_cxp.export') }}" method="POST">
                                @csrf
                                @if (Route::currentRouteName() != 'index_cxp.reporteria' && isset($proveedor_cxp))
                                    <h3>{{ $proveedor_cxp->nombre }}</h3>
                                @endif
                                <table class="table table-flush" id="datatable-search">
                                    <thead class="thead">
                                        <tr>
                                            <th></th>
                                            <th>#</th>
                                            <th><img src="{{ asset('img/icon/gps.webp') }}" alt=""
                                                    width="25px">Origen</th>
                                            <th><img src="{{ asset('img/icon/origen.png') }}" alt=""
                                                    width="25px">Destino</th>
                                            <th><img src="{{ asset('img/icon/contenedor.png') }}" alt=""
                                                    width="25px"># Contenedor</th>
                                            <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt=""
                                                    width="25px">Estatus</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (Route::currentRouteName() != 'index_cxp.reporteria')
                                            @foreach ($cotizaciones as $cotizacion)
                                                <tr>
                                                    <td><input type="checkbox" name="cotizacion_ids[]"
                                                            value="{{ $cotizacion->id }}"
                                                            class="select-checkbox visually-hidden"></td>
                                                    <td>{{ $cotizacion->id }}</td>
                                                    <td>{{ $cotizacion->origen }}</td>
                                                    <td>{{ $cotizacion->destino }}</td>
                                                    @php
                                                        $cotizacionOriginal = \App\Models\Cotizaciones::find(
                                                            $cotizacion->id_cotizacion,
                                                        );
                                                        $numContenedor = $cotizacion->num_contenedor ?? '';

                                                        if (
                                                            $cotizacionOriginal &&
                                                            $cotizacionOriginal->jerarquia === 'Principal' &&
                                                            $cotizacionOriginal->referencia_full
                                                        ) {
                                                            $cotSecundaria = \App\Models\Cotizaciones::where(
                                                                'referencia_full',
                                                                $cotizacionOriginal->referencia_full,
                                                            )
                                                                ->where('jerarquia', 'Secundario')
                                                                ->where('id', '!=', $cotizacionOriginal->id)
                                                                ->with('DocCotizacion')
                                                                ->first();

                                                            $contenedorSec = optional($cotSecundaria?->DocCotizacion)
                                                                ->num_contenedor;

                                                            if ($contenedorSec) {
                                                                $numContenedor .= ' / ' . $contenedorSec;
                                                            }
                                                        }

                                                    @endphp
                                                    <td>{{ $numContenedor }}</td>
                                                    <td>
                                                        @can('cotizaciones-estatus')
                                                            @if ($cotizacion->estatus == 'Aprobada')
                                                                <button type="button" class="btn btn-outline-info btn-xs">
                                                                @else
                                                                    <button type="button"
                                                                        class="btn btn-outline-success btn-xs">
                                                            @endif
                                                            {{ $cotizacion->estatus }}
                                                            </button>
                                                        @endcan
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>

                                @if (isset($cotizaciones) && $cotizaciones != null)
                                    <div class="dropdown">
                                        <button class="btn btn-success dropdown-toggle" type="button"
                                            id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                            Exportar
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" id="exportButtonGenericExcel" data-report="0"
                                                    href="#">Exportar Tablero</a></li>
                                            <li><a class="dropdown-item exportButton" data-filetype="pdf" id="exportButton"
                                                    href="#">PDF Cuentas por Pagar</a></li>
                                            <li><a class="dropdown-item exportButton" data-filetype="xlsx"
                                                    id="exportButtonXlsx" href="#">Excel Cuentas por Pagar</a></li>
                                        </ul>
                                    </div>
                                    <!--button type="button" id="exportButtonGenericExcel" data-report="0" class="btn btn-success">Exportar a Excel</button-->
                                    <input type="hidden" id="txtDataGenericExcel"
                                        value="{{ json_encode($cotizaciones) }}">
                                @endif
                                <!--button type="submit" id="exportButton" class="btn btn-primary">Exportar a PDF</button-->
                            </form>
                            <!-- Advertencia oculta inicialmente -->
                            <div id="warningMessage" class="alert alert-warning d-none" role="alert">
                                <strong>Advertencia!</strong> Debes seleccionar al menos una casilla para visualizar el
                                reporte.
                            </div>
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
    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> --}}
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/dataTables.fixedColumns.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/fixedColumns.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar select2 para el campo de proveedor
            $('.cliente').select2();

            // Inicializar la tabla con DataTable
            const table = $('#datatable-search').DataTable({
                columnDefs: [{
                    orderable: false,
                    className: 'select-checkbox',
                    targets: 0
                }],
                fixedColumns: {
                    start: 2
                },
                order: [
                    [1, 'asc']
                ],
                paging: true,
                pageLength: 30,
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                }
            });

            // Función para manejar el botón "Seleccionar todo"
            $('#selectAllButton').on('click', function() {
                // Verificar si todas las filas están seleccionadas
                if (table.rows({
                        selected: true
                    }).count() === table.rows().count()) {
                    // Si todas están seleccionadas, deseleccionarlas
                    table.rows().deselect();
                    $(this).text('Seleccionar todo');
                } else {
                    // Si no todas están seleccionadas, seleccionarlas todas
                    table.rows().select();
                    $(this).text('Deseleccionar todo');
                }
            });

            // Detectar cuando las filas cambian de estado (seleccionadas o desmarcadas)
            table.on('select deselect', function() {
                // Si todas las filas están seleccionadas, cambiar el texto a "Deseleccionar todo"
                if (table.rows({
                        selected: true
                    }).count() === table.rows().count()) {
                    $('#selectAllButton').text('Deseleccionar todo');
                } else {
                    // Si no todas están seleccionadas, cambiar el texto a "Seleccionar todo"
                    $('#selectAllButton').text('Seleccionar todo');
                }
            });

            // Función para la exportación de datos seleccionados
            $('.exportButton').on('click', function(event) {
                event.preventDefault(); // Evitar comportamiento predeterminado del formulario

                const selectedIds = table.rows('.selected').data().toArray().map(row => row[
                    1]); // Obtener los IDs seleccionados

                // Verificar si no se seleccionó ninguna fila
                if (selectedIds.length === 0) {
                    // Mostrar el mensaje de advertencia si no se seleccionó ninguna fila
                    $('#warningMessage').removeClass('d-none');
                    return; // Detener la ejecución del código
                }

                // Si se seleccionó al menos una fila, ocultar el mensaje de advertencia
                $('#warningMessage').addClass('d-none');

                var fileType = $(this).data('filetype'); // Obtener el tipo de archivo (PDF o Excel)

                // Enviar los IDs seleccionados al controlador por AJAX
                $.ajax({
                    url: '{{ route('cotizaciones_cxp.export') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        selected_ids: selectedIds,
                        fileType: fileType
                    },
                    xhrFields: {
                        responseType: 'blob' // Esperamos una respuesta tipo blob (archivo)
                    },
                    success: function(response) {
                        var blob = new Blob([response], {
                            type: 'application/' + fileType
                        });
                        var url = URL.createObjectURL(blob);

                        var a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = 'cxp_' +
                            new Date().toLocaleDateString('es-ES').replaceAll('/', '-') + '_' +
                            new Date().toLocaleTimeString('es-ES', {
                                hour12: false
                            }).replaceAll(':', '_') +
                            '.' + fileType;


                        // Inicia la descarga
                        a.click();

                        // Limpiar después de la descarga
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);

                        alert('El archivo se ha descargado correctamente.');
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        alert('Ocurrió un error al exportar los datos.');
                    }
                });
            });
        });
    </script>
@endsection

@push('custom-javascript')
    <script src="{{ asset('js/reporteria/genericExcel.js') }}"></script>
@endpush
