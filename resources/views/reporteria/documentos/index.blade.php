
@extends('layouts.app')

@section('template_title')
    Buscador
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
                        
                        <h5>Reporte de documentos</h5>
                        
                        </div>
                        <div class="card-body">
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="card">
                                            <form action="{{ route('advance_documentos.buscador') }}" method="GET" >

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
                                                            <div class="col-3 mb-5">
                                                                <label for="user_id">Buscar subcliente:</label>
                                                                <select class="form-control subcliente" name="id_subcliente" id="id_subcliente">
                                                                    <option selected value="">seleccionar cliente</option>
                                                                </select>
                                                            </div>
                                                            <div class="col-3 mb-5">
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
                                <form id="exportForm" action="{{ route('export_documentos.export') }}" method="POST">
                                    @csrf
                                    <table class="table table-flush" id="datatable-search">
                                        <thead class="thead">
                                            <tr>
                                                <th></th>
                                                <th>#</th>
                                                <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                                <th><img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">CCP</th>
                                                <th><img src="{{ asset('img/icon/9.webp') }}" alt="" width="25px">Boleta liberacion</th>
                                                <th><img src="{{ asset('img/icon/documento.png') }}" alt="" width="25px">Doda</th>
                                                <th><img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">Carta porte</th>
                                                <th><img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">Boleta vacio</th>
                                                <th><img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">EIR</th>
                                                <th>Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(Route::currentRouteName() != 'index_documentos.reporteria')
                                                @foreach ($cotizaciones as $cotizacion)
                                                    <tr>
                                                        <td>

                                                            <input type="checkbox" name="cotizacion_ids[]" value="{{ $cotizacion->id }}" class="select-checkbox visually-hidden">
                                                            {{-- <input type="checkbox" name="cotizacion_ids[]" value="{{ $cotizacion->id }}" class="select-box" data-row-id="{{ $cotizacion->id }}"> --}}

                                                        </td>
                                                        <td>{{$cotizacion->id}}</td>
                                                        <td>{{$cotizacion->num_contenedor}}</td>
                                                        <td>
                                                            <div class="form-check">
                                                                @if ($cotizacion->doc_ccp == NULL)
                                                                    <input class="form-check-input" type="checkbox" disabled>
                                                                @else
                                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check">
                                                                @if ($cotizacion->boleta_liberacion == NULL)
                                                                    <input class="form-check-input" type="checkbox" disabled>
                                                                @else
                                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check">
                                                                @if ($cotizacion->doda == NULL)
                                                                    <input class="form-check-input" type="checkbox" disabled>
                                                                @else
                                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check">
                                                                @if ($cotizacion->carta_porte == NULL)
                                                                    <input class="form-check-input" type="checkbox" disabled>
                                                                @else
                                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check">
                                                                @if ($cotizacion->boleta_vacio == NULL)
                                                                    <input class="form-check-input" type="checkbox" disabled>
                                                                @else
                                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <div class="form-check">
                                                                @if ($cotizacion->doc_eir == NULL)
                                                                    <input class="form-check-input" type="checkbox" disabled>
                                                                @else
                                                                    <input class="form-check-input" type="checkbox" checked disabled>
                                                                @endif
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <a type="button" class="btn btn-xs" href="{{ route('edit.cotizaciones', $cotizacion->id) }}">
                                                                <img src="{{ asset('img/icon/quotes.webp') }}" alt="" width="25px">
                                                            </a>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    @if(isset($cotizaciones) && $cotizaciones != null)
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
        // Inicializar Select2 para el cliente y subcliente
        $('.cliente').select2();

        // Inicializar la tabla con DataTables
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

        // Función para actualizar el texto del botón "Seleccionar todo"
        function updateSelectAllButton() {
            const allSelected = table.rows({ selected: true }).count() === table.rows().count();
            const selectAllButton = $('#selectAllButton');
            if (allSelected) {
                selectAllButton.text('Deseleccionar todo');
            } else {
                selectAllButton.text('Seleccionar todo');
            }
        }

        // Lógica para el botón "Seleccionar todo"
        $('#selectAllButton').on('click', function() {
            const allSelected = table.rows({ selected: true }).count() === table.rows().count();
            // Seleccionar/Deseleccionar todas las filas
            if (allSelected) {
                table.rows().deselect();
            } else {
                table.rows().select();
            }
            // Actualizar el texto del botón después de hacer la selección
            updateSelectAllButton();
        });

        // Manejar el evento de cambio de selección de filas
        table.on('select', function() {
            updateSelectAllButton(); // Actualiza el botón cuando se selecciona una fila
        });

        table.on('deselect', function() {
            updateSelectAllButton(); // Actualiza el botón cuando se deselecciona una fila
        });

        // Lógica para la exportación de los datos seleccionados
        $('.exportButton').on('click', function() {
            const selectedIds = table.rows('.selected').data().toArray().map(row => row[1]); // Obtener los IDs seleccionados
            console.log(selectedIds); // Verificar en la consola del navegador

            var fileType = $("#"+event.target.id).data('filetype');

            // Enviar los IDs seleccionados al controlador por Ajax
            $.ajax({
                url: '{{ route('export_documentos.export') }}',
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
                    var blob = new Blob([response], { type: 'application/'+fileType });
                    var url = URL.createObjectURL(blob);

                    // Crear un elemento <a> para simular el clic de descarga
                    var a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'documentos_seleccionados.'+fileType;
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

        // Lógica para manejar la selección de subcliente basada en el cliente seleccionado
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
