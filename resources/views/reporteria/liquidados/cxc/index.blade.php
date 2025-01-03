@extends('layouts.app')

@section('template_title')
    Liquidados CXC
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
                
                <h5>Reporte liquidados CXC</h5>
                
                </div>
                <div class="card-body">
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <form action="{{ route('advance_liquidados.buscador') }}" method="GET">
                                        <div class="card-body" style="padding-left: 1.5rem; padding-top: 1rem;">
                                            <h5>Filtro</h5>
                                            <div class="row">
                                                <div class="col-3">
                                                    <label for="user_id">Buscar cliente:</label>
                                                    <select class="form-control cliente" name="id_client" id="id_client">
                                                        <option selected value="">seleccionar cliente</option>
                                                        @foreach($clientes as $client)
                                                        <option value="{{ $client->id }}">{{ $client->nombre }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div class="col-3">
                                                    <label for="user_id">Buscar subcliente:</label>
                                                    <select class="form-control subcliente" name="id_subcliente" id="id_subcliente">
                                                        <option selected value="">seleccionar cliente</option>
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
                        <form id="exportForm" action="{{ route('liquidados_cxc.export') }}" method="POST">
                            @csrf
                            <table class="table table-flush" id="datatable-search">
                                <thead class="thead">
                                    <tr>
                                        <th></th>
                                        <th>#</th>
                                        <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Cliente</th>
                                        <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Subcliente</th>
                                        <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen</th>
                                        <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino</th>
                                        <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                        <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="25px">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(Route::currentRouteName() != 'index_liquidados_cxc.reporteria')

                                    @foreach ($cotizaciones as $cotizacion)
                                    <tr>
                                        <td><input type="checkbox" name="cotizacion_ids[]" value="{{ $cotizacion->id }}" class="select-checkbox visually-hidden"></td>
                                        <td>{{$cotizacion->id}}</td>
                                        <td>{{$cotizacion->Cliente->nombre}}</td>
                                        <td>{{$cotizacion->Subcliente->nombre ?? '-'}}</td>
                                        <td>{{$cotizacion->origen}}</td>
                                        <td>{{$cotizacion->destino}}</td>
                                        <td>{{$cotizacion->DocCotizacion->num_contenedor}}</td>
                                        <td>
                                            @can('cotizaciones-estatus')
                                            <button type="button" class="btn btn-outline-{{ $cotizacion->estatus == 'Aprobada' ? 'info' : 'success' }} btn-xs">
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
                            <!--button type="button" id="exportButtonGenericExcel" data-report="3" class="btn btn-success exportButton">Exportar a Excel</button-->
                            <input type="hidden" id="txtDataGenericExcel" value="{{json_encode($cotizaciones)}}">
                            <button type="button" id="exportButtonExcel" data-filetype="xlsx" class="btn btn-success exportButton">Exportar a Excel</button>
                            <button type="button" id="exportButton" data-filetype="pdf" class="btn btn-primary exportButton">Exportar a PDF</button>
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
        $('.exportButton').on('click', function() {
            const selectedIds = table.rows('.selected').data().toArray().map(row => row[1]); // Obtener los IDs seleccionados

            console.log(selectedIds); // Verificar en la consola del navegador
            var fileType = $("#"+event.target.id).data('filetype');
            
            // Enviar los IDs seleccionados al controlador por Ajax
            $.ajax({
                url: '{{ route('liquidados_cxc.export') }}',
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
                    a.download = 'Liquidados_cxc_{{ date('d-m-Y') }}.' + fileType;
                    document.body.appendChild(a);

                    // Simular el clic en el enlace para iniciar la descarga
                    a.click();

                    // Limpiar después de la descarga
                    window.URL.revokeObjectURL(url);

                    // Alerta opcional para indicar que se ha descargado correctamente
                    alert('El archivo se ha descargado correctamente.');

                    // Opcional: eliminar el elemento <a> después de la descarga
                    document.body.removeChild(a);
                },
                error: function(xhr, status, error) {
                    console.error(error);
                    alert('Ocurrió un error al exportar los datos.');
                }
            });
        });

    });

    // Función para actualizar los subclientes en función del cliente seleccionado
    $(document).ready(function() {
        $('#id_client').on('change', function() {
            var clientId = $(this).val();
            if(clientId) {
                $.ajax({
                    url: '/subclientes/' + clientId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        $('#id_subcliente').empty();
                        $('#id_subcliente').append('<option selected value="">Seleccionar subcliente</option>');
                        $.each(data, function(key, subcliente) {
                            $('#id_subcliente').append('<option value="'+ subcliente.id +'">'+ subcliente.nombre +'</option>');
                        });
                    }
                });
            } else {
                $('#id_subcliente').empty();
                $('#id_subcliente').append('<option selected value="">Seleccionar subcliente</option>');
            }
        });
    });
</script>

@endsection

@push('custom-javascript')
<script src="{{asset('js/reporteria/genericExcel.js')}}"></script>
@endpush