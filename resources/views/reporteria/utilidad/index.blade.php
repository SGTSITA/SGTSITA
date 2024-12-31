
@extends('layouts.app')

@section('template_title')
    Buscador
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                        
                        <h5>Reporte de Utilidad</h5>
                        
                       
                        </div>
                        <div class="card-body">
                           
                            <div class="container-fluid">
                                <div class="row">
                                    <div class="col-sm-12">
                                        <div class="card">
                                        <form action="{{ route('advance_utilidad.buscador') }}" method="GET">
    <div class="card-body" style="padding-left: 1.5rem; padding-top: 1rem;">
        <h5>Filtro</h5>
        <div class="row">
            <div class="col-4 mb-3">
                <label for="fecha_de">Rango de fecha DE:</label>
                <input 
                    class="form-control" 
                    type="date" 
                    id="fecha_de" 
                    name="fecha_de" 
                    value="{{ request('fecha_de') }}"
                >
            </div>
            <div class="col-4 mb-3">
                <label for="fecha_hasta">Rango de fecha Hasta:</label>
                <input 
                    class="form-control" 
                    type="date" 
                    id="fecha_hasta" 
                    name="fecha_hasta" 
                    value="{{ request('fecha_hasta') }}"
                >
            </div>
            <div class="col-3 mb-5">
                <br>
                <button 
                    class="btn btn-sm mb-0 mt-sm-0 mt-1" 
                    type="submit" 
                    style="background-color: #F82018; color: #ffffff;"
                >
                    Buscar
                </button>
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
                                <form id="exportForm" action="{{ route('export_utilidad.export') }}" method="POST">
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
                                                <th><img src="{{ asset('img/icon/coins.webp') }}" alt="" width="25px">Utilidad</th>
                                                <th><img src="{{ asset('img/icon/coins.webp') }}" alt="" width="25px">Detalles</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if(Route::currentRouteName() != 'index_utilidad.reporteria')
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
                                                        <td>
                                                            @php
                                                                if($cotizacion->total_proveedor == NULL){
                                                                    $utilidad = $cotizacion->total - $cotizacion->pago_operador;
                                                                }elseif($cotizacion->total_proveedor != NULL){
                                                                    $utilidad = $cotizacion->total - $cotizacion->total_proveedor;
                                                                }else{
                                                                    $utilidad = 0;
                                                                }
                                                            @endphp
                                                           <b> ${{ number_format($utilidad, 2, '.', ',') }}</b>
                                                        </td>
                                                        <td>
                                                            <a class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#detalles{{ $cotizacion->id }}">
                                                                <i class="fa fa-fw fa-edit"></i> Ver
                                                            </a>
                                                        </td>
                                                    </tr>
                                                    @include('reporteria.utilidad.detalles')
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                                                  
                                    @if(isset($asignaciones) && $asignaciones != null)
                                    <div class="dropdown">
                                        <button class="btn btn-success dropdown-toggle" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
                                          Exportar
                                        </button>
                                        <ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
                                            <li><a class="dropdown-item" id="exportButtonGenericExcel" data-report="2"  href="javascript:$('#exportButtonGenericExcel').click();">Exportar Tablero</a></li>
                                            <li><button type="submit" class="dropdown-item" data-filetype="pdf" id="exportButton" value="pdf" name="btnExport">PDF Cuentas por Pagar</button></li>
                                            <li><button type="submit" class="dropdown-item exportButton" data-filetype="xlsx" id="exportButtonXlsx" value="xlsx" name="btnExport">Excel Cuentas por Pagar</button></li>
                                        </ul>
                                    </div>
                                    <input type="hidden" id="txtDataGenericExcel" value="{{json_encode($asignaciones)}}">
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

    <script>
        $(document).ready(function() {
            // Inicializa Select2 para los selects
            $('.cliente').select2();
            $('.contenedor').select2();

            // Inicializa DataTable
            const table = new simpleDatatables.DataTable("#datatable-search", {
                searchable: true,
                fixedHeight: false
            });

            // Funcionalidad de "Seleccionar todo"
            $('#selectAllButton').on('click', function() {
                const checkboxes = $('.select-box');
                const allChecked = checkboxes.filter(':checked').length === checkboxes.length;

                // Marcar o desmarcar todos los checkboxes
                checkboxes.prop('checked', !allChecked);

                // Cambiar el texto del botón
                $(this).text(allChecked ? 'Seleccionar todo' : 'Deseleccionar todo');
                updateSelectedRows();
            });

            // Manejo de la selección/deselección individual
            $('#datatable-search tbody').on('change', '.select-box', function() {
                updateSelectedRows();
                const allChecked = $('.select-box:checked').length === $('.select-box').length;
                $('#selectAllButton').text(allChecked ? 'Deseleccionar todo' : 'Seleccionar todo');
            });

            // Función para actualizar las filas seleccionadas
            function updateSelectedRows() {
                const selectedRows = $('.select-box:checked').map(function() {
                    return this.value;
                }).get();

                // Habilitar el botón de exportación si hay filas seleccionadas
                $('#exportButton').prop('disabled', selectedRows.length === 0);
            }

            // Exportación de los datos seleccionados
            $('#exportButton').on('click', function() {
                const selectedRows = $('.select-box:checked').map(function() {
                    return this.value;
                }).get();

                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'selected_ids';
                input.value = JSON.stringify(selectedRows);
                document.getElementById('exportForm').appendChild(input);
            });
        });
    </script>
@endsection

@push('custom-javascript')
<script src="{{asset('js/reporteria/genericExcel.js')}}"></script>
@endpush