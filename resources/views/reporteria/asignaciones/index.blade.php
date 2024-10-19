
@extends('layouts.app')

@section('template_title')
    Viajes
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
                                            <form action="{{ route('advance_viajes.buscador') }}" method="GET" >

                                                <div class="card-body" style="padding-left: 1.5rem; padding-top: 1rem;">
                                                    <h5>Filtro</h5>
                                                        <div class="row">
                                                            <div class="col-4 mb-3">
                                                                <label for="user_id">Rango de fecha DE:</label>
                                                                <input class="form-control" type="date" id="fecha_de" name="fecha_de">
                                                            </div>
                                                            <div class="col-4 mb-3">
                                                                <label for="user_id">Rango de fecha Hasta:</label>
                                                                <input class="form-control" type="date" id="fecha_hasta" name="fecha_hasta">
                                                            </div>
                                                            <div class="col-4 mb-3">
                                                                <label for="user_id">Estatus:</label>
                                                                <select class="form-control" name="estatus" id="estatus">
                                                                    <option value="">seleccionar estatus</option>
                                                                    <option value="Finalizado">Finalizado</option>
                                                                    <option value="Aprobada">Aprobada</option>
                                                                </select>
                                                            </div>
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
                                                            <div class="col-3">
                                                                <label for="user_id">Buscar proveedor:</label>
                                                                <select class="form-control proveedor" name="id_proveedor" id="id_proveedor">
                                                                    <option selected value="">seleccionar proveedor</option>
                                                                    @foreach($proveedores as $proveedor)
                                                                        <option value="{{ $proveedor->id }}">{{ $proveedor->nombre }}</option>
                                                                    @endforeach
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
                                <form id="exportForm" action="{{ route('export_viajes.viajes') }}" method="POST">
                                    @csrf
                                    <table class="table table-flush" id="datatable-search">
                                        <thead class="thead">
                                            <tr>
                                                <th></th>
                                                <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Cliente</th>
                                                <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Subcliente</th>
                                                <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen</th>
                                                <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino</th>
                                                <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                                <th><img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">Fecha salida</th>
                                                <th><img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">Fecha llegada</th>
                                                <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="25px">Estatus</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(Route::currentRouteName() != 'index_viajes.reporteria')
                                                @foreach ($asignaciones as $cotizacion)
                                                    <tr>
                                                        <td>
                                                            <input type="checkbox" name="cotizacion_ids[]" value="{{ $cotizacion->id }}" class="select-box" data-row-id="{{ $cotizacion->id }}">
                                                        </td>
                                                        <td>{{$cotizacion->Contenedor->Cotizacion->Cliente->nombre}}</td>
                                                        <td>
                                                            @if ($cotizacion->Contenedor->Cotizacion->id_subcliente != NULL)
                                                                {{$cotizacion->Contenedor->Cotizacion->Subcliente->nombre}} / {{$cotizacion->Contenedor->Cotizacion->Subcliente->telefono}}
                                                            @else
                                                                -
                                                            @endif
                                                        </td>
                                                        <td>{{$cotizacion->Contenedor->Cotizacion->origen}}</td>
                                                        <td>{{$cotizacion->Contenedor->Cotizacion->destino}}</td>
                                                        <td>{{$cotizacion->Contenedor->num_contenedor}}</td>
                                                        <td>{{ Carbon\Carbon::parse($cotizacion->fehca_inicio_guard)->format('d-m-Y') }}</td>
                                                        <td>{{ Carbon\Carbon::parse($cotizacion->fehca_fin_guard)->format('d-m-Y') }}</td>
                                                        <td>
                                                            @can('cotizaciones-estatus')
                                                                @if ($cotizacion->Contenedor->Cotizacion->estatus == 'Aprobada')
                                                                    <button type="button" class="btn btn-outline-info btn-xs">
                                                                @else
                                                                    <button type="button" class="btn btn-outline-success btn-xs">
                                                                @endif
                                                                    {{$cotizacion->Contenedor->Cotizacion->estatus}}
                                                                </button>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                    
                                    <!--button type="button" id="exportButtonGenericExcel" data-report="1" class="btn btn-success">Exportar a Excel</button>
                                    <input type="hidden" id="txtDataGenericExcel" value="{{json_encode($asignaciones)}}"-->
                                   

                                    @if(isset($asignaciones) && $asignaciones != null)
                                    <div class="dropdown">
                                        <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                          Exportar
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" id="exportButtonGenericExcel" data-report="1"  href="javascript:$('#exportButtonGenericExcel').click();">Exportar Tablero</a></li>
                                            <li><button type="submit" class="dropdown-item" data-filetype="pdf" id="exportButton" value="pdf" name="btnExport">PDF Cuentas por Pagar</button></li>
                                            <li><button type="submit" class="dropdown-item exportButton" data-filetype="xlsx" id="exportButtonXlsx" value="xlsx" name="btnExport">Excel Cuentas por Pagar</button></li>
                                        </ul>
                                    </div>
                                    <input type="hidden" id="txtDataGenericExcel" value="{{json_encode($asignaciones)}}">
                                    @endif
                                    <!--button type="submit" id="exportButton" class="btn btn-primary">Exportar a PDFa</button-->
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

    <script>
        $(document).ready(function() {
            $('.cliente').select2();
            $('.proveedor').select2();
        });

        const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
        searchable: true,
        fixedHeight: false
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

        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('select-all');
            const exportButton = document.getElementById('exportButton');
            const selectedRows = new Set();

            const table = $('#datatable-search').DataTable();

            // Manejar el evento de cambio en el checkbox "select all"
            selectAllCheckbox.addEventListener('change', function() {
                const checkboxes = document.querySelectorAll('.select-box');
                const allChecked = selectAllCheckbox.checked;

                checkboxes.forEach(checkbox => {
                    checkbox.checked = allChecked;
                    const rowId = checkbox.value;
                    if (allChecked) {
                        selectedRows.add(rowId);
                    } else {
                        selectedRows.delete(rowId);
                    }
                });
                toggleExportButton();
            });

            // Manejar el evento de cambio en cada checkbox individual
            $('#datatable-search tbody').on('change', '.select-box', function() {
                const rowId = this.value;
                if (this.checked) {
                    selectedRows.add(rowId);
                } else {
                    selectedRows.delete(rowId);
                }
                toggleExportButton();
            });

            function toggleExportButton() {
                exportButton.disabled = selectedRows.size === 0;
            }

            // Exportar los datos seleccionados a PDF
            exportButton.addEventListener('click', function(event) {
                const selectedData = Array.from(selectedRows);
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids';
                input.value = JSON.stringify(selectedData);
                document.getElementById('exportForm').appendChild(input);
            });
        });

    </script>
@endsection

@push('custom-javascript')
<script src="{{asset('js/reporteria/genericExcel.js')}}"></script>
@endpush