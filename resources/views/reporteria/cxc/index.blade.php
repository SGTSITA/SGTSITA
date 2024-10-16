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
                <div class="card-body">
                    <a href="{{ route('dashboard') }}" class="btn" style="background: {{$configuracion->color_boton_close}}; color: #ffff; margin-right: 3rem;">
                        Regresar
                    </a>
                    <div class="container-fluid">
                        <div class="row">
                            <div class="col-sm-12">
                                <div class="card">
                                    <form action="{{ route('advance_search.buscador') }}" method="GET">
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
                                                    <label for="user_id">Buscar proveedor:</label>
                                                    <select class="form-control proveedor" name="id_proveedor" id="id_proveedor">
                                                        <option selected value="">seleccionar proveedor</option>
                                                        @foreach($proveedores as $proveedor)
                                                        <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
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
                        <form id="exportForm" action="{{ route('cotizaciones.export') }}" method="POST">
                            @csrf
                            <table class="table table-flush" id="datatable-search">
                                <thead class="thead">
                                    <tr>
                                        <th></th>
                                        <th>#</th>
                                        <th>Fecha inicio</th>
                                        <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Cliente</th>
                                        <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Subcliente</th>
                                        <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen</th>
                                        <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino</th>
                                        <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                        <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="25px">Estatus</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if(Route::currentRouteName() != 'index.reporteria')

                                    @foreach ($cotizaciones as $cotizacion)
                                    <tr>
                                        <td><input type="checkbox" name="cotizacion_ids[]" value="{{ $cotizacion->id }}" class="select-checkbox visually-hidden"></td>
                                        <td>{{$cotizacion->id}}</td>
                                        <td>
                                            {{ optional($cotizacion->DocCotizacion->Asignaciones)->fehca_inicio_guard ? Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fehca_inicio_guard)->format('d-m-Y') : 'Sin fecha' }}
                                        </td>

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
                            <button type="button" id="exportButtonExcel" class="btn btn-success">Exportar a Excel</button>
                            <input type="hidden" id="txtDataCotizaciones" value="{{json_encode($cotizaciones)}}">
                            @endif
                            <button type="button" id="exportButton" class="btn btn-primary">Exportar a PDF</button>
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
{{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script> --}}
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/dataTables.fixedColumns.min.js"></script>
<script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/fixedColumns.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/select/2.0.3/js/dataTables.select.min.js"></script>
<script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('.cliente').select2();
        $('.proveedor').select2();


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

        $("#exportButtonExcel").on('click',()=>{
            var dataExport = $("#txtDataCotizaciones").val();
            $.ajax({
                    url:"{{route('cotizaciones.export-excel')}}",
                    type:'post',
                    data:{ _token: '{{ csrf_token() }}',dataExport:dataExport},
                    xhrFields: {
                        responseType: 'blob' 
                    },
                    beforeSend:()=>{},
                    success:(response)=>{
                    var blob = new Blob([response], { type: 'application/xlsx' });
                    var url = URL.createObjectURL(blob);
                    var a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'Cuentas_por_coborar_{{  date('d-m-Y'); }}.xlsx';
                    document.body.appendChild(a);
                    a.click();
                    window.URL.revokeObjectURL(url);
                    document.body.removeChild(a);
                    },
                    error:()=>{}
                });
        });

        $('#exportButton').on('click', function() {
            const selectedIds = table.rows('.selected').data().toArray().map(row => row[1]); // Obtener los IDs seleccionados

            console.log(selectedIds); // Verificar en la consola del navegador

            // Enviar los IDs seleccionados al controlador por Ajax
            $.ajax({
                url: '{{ route('cotizaciones.export') }}',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    selected_ids: selectedIds
                },
                xhrFields: {
                        responseType: 'blob' // Indicar que esperamos una respuesta tipo blob (archivo)
                    },
                success: function(response) {
                    // Crear un objeto URL del blob recibido
                    var blob = new Blob([response], { type: 'application/pdf' });
                    var url = URL.createObjectURL(blob);

                    // Crear un elemento <a> para simular el clic de descarga
                    var a = document.createElement('a');
                    a.style.display = 'none';
                    a.href = url;
                    a.download = 'Cuentas_por_coborar_{{  date('d-m-Y'); }}.pdf';
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
