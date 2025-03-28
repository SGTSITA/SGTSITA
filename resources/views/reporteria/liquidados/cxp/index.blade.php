
@extends('layouts.app')

@section('template_title')
    Liquidaciones CXP
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
                        <div class="card-header  d-flex justify-content-between align-items-center">
                        
                        <h5>Reporte Liquidados CXP</h5>
                        
                        </div>
                        <div class="card-body">
                        
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="card">
                                            <form action="{{ route('advance_liquidados_cxp.buscador') }}" method="GET" >
                                                <div class="card-body" style="padding-left: 1.5rem; padding-top: 1rem;">
                                                    <div class="row">
                                                        <div class="col-3">
                                                            <label for="user_id">Buscar proveedor:</label>
                                                            <select class="form-control cliente" name="id_proveedor" id="id_proveedor">
                                                                <option selected value="">seleccionar proveedor</option>
                                                                @foreach($proveedores as $proveedor)
                                                                    <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }} {{ $proveedor->telefono }}</option>
                                                                @endforeach
                                                            </select>
                                                        </div>
                                                        <div class="col-3">
                                                            <br>
                                                            <button class="btn btn-sm mb-0 mt-sm-0 mt-1" type="submit" style="background-color: #F82018; color: #ffffff;">Buscar</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                            <div class="mb-3">
                            </div>
                            <div class="mb-3">
                            <button type="button" id="selectAllButton" class="btn btn-primary">Seleccionar todo</button>
                            </div>
                                <form id="exportForm" action="{{ route('liquidados_cxp.export') }}" method="POST">
                                    @csrf
                                    @if(Route::currentRouteName() != 'index_liquidados_cxp.reporteria')
                                        <h3> {{$proveedor->nombre}} </h3>
                                    @endif
                                    <table class="table table-flush" id="datatable-search">
                                        <thead class="thead">
                                            <tr>
                                                <th></th>
                                                <th>#</th>
                                                <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen</th>
                                                <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino</th>
                                                <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                                <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="25px">Estatus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(Route::currentRouteName() != 'index_liquidados_cxp.reporteria')
                                                @foreach ($cotizaciones as $cotizacion)
                                                    <tr>
                                                        <td><input type="checkbox" name="cotizacion_ids[]" value="{{ $cotizacion->id }}" class="select-checkbox visually-hidden"></td>
                                                        <td>{{$cotizacion->id}}</td>
                                                        <td>{{$cotizacion->origen}}</td>
                                                        <td>{{$cotizacion->destino}}</td>
                                                        <td>{{$cotizacion->num_contenedor}}</td>

                                                        <td>
                                                            @can('cotizaciones-estatus')
                                                                @if ($cotizacion->estatus == 'Aprobada')
                                                                    <button type="button" class="btn btn-outline-info btn-xs">
                                                                @else
                                                                    <button type="button" class="btn btn-outline-success btn-xs">
                                                                @endif
                                                                    {{$cotizacion->estatus}}
                                                                </button>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    @if(isset($cotizaciones) && $cotizaciones != null)
                                        <!--button type="button" id="exportButtonGenericExcel" data-report="4" class="btn btn-success exportButton">Exportar a Excel</button-->
                                        <input type="hidden" id="txtDataGenericExcel" value="{{json_encode($cotizaciones)}}">
                                        <button type="button" id="exportButtonExcel" data-filetype="xlsx" class="btn btn-success exportButton">Exportar a Excel</button>
                                        <button type="button" id="exportButton" data-filetype="pdf" class="btn btn-primary exportButton">Exportar a PDF</button>
                                    @endif  
                                    <!--button type="submit" id="exportButton" class="btn btn-primary">Exportar a PDF</button-->
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
@endsection

@section('datatable')
<script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
<script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js')}}"></script>

<!-- JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/dataTables.fixedColumns.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/fixedColumns.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/select/2.0.3/js/dataTables.select.min.js"></script>
<script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.min.js"></script>

<script>
    $(document).ready(function() {
        $('.cliente').select2();

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
            // Verificar si todas las filas están seleccionadas (no solo las visibles)
            if (table.rows({ selected: true }).count() === table.rows().count()) {
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
            if (table.rows({ selected: true }).count() === table.rows().count()) {
                $('#selectAllButton').text('Deseleccionar todo');
            } else {
                // Si no todas las filas están seleccionadas, cambiar el texto a "Seleccionar todo"
                $('#selectAllButton').text('Seleccionar todo');
            }
        });

        // Función para la exportación de datos seleccionados
        $('.exportButton').on('click', function(event) {
            event.preventDefault(); // Evita el comportamiento predeterminado del formulario

            // Obtener los IDs de las filas seleccionadas
            const selectedIds = table.rows('.selected').data().toArray().map(row => row[1]); // Asumiendo que el ID está en la segunda columna

            console.log(selectedIds); // Verificar en la consola del navegador

            var fileType = $("#"+event.target.id).data('filetype'); // Obtener el tipo de archivo (PDF, Excel, etc.)

            // Enviar los IDs seleccionados al controlador por Ajax
            $.ajax({
                url: '{{ route('liquidados_cxp.export') }}', // Ruta para la exportación
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    selected_ids: selectedIds,
                    fileType: fileType
                },
                xhrFields: {
                    responseType: 'blob' // Indicar que esperamos una respuesta tipo blob (archivo)
                },
                success: function(response) {
                    // Crear un objeto URL del blob recibido
                    var blob = new Blob([response], { type: 'application/' + fileType });
                    var url = URL.createObjectURL(blob);

                    // Crear un elemento <a> para simular el clic de descarga
                    var a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'Cuentas_por_pagar_{{ date('d-m-Y') }}.' + fileType;
                    document.body.appendChild(a);

                    // Simular el clic en el enlace para iniciar la descarga
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
<script src="{{asset('js/reporteria/genericExcel.js')}}"></script>
@endpush