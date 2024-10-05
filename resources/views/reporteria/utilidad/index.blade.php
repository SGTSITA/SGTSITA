
@extends('layouts.app')

@section('template_title')
    Buscador
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
                                            <form action="{{ route('advance_utilidad.buscador') }}" method="GET" >

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
                                                            {{-- <div class="col-4 mb-3">
                                                                <label for="user_id">Contenedor:</label>
                                                                <select class="form-control contenedor" name="contenedor" id="contenedor">
                                                                    <option value="">seleccionar contenedor</option>
                                                                    @foreach ($contenedores as $contenedor)
                                                                        <option value="{{ $contenedor->num_contenedor }}">{{ $contenedor->num_contenedor }}</option>
                                                                    @endforeach
                                                                </select>
                                                            </div> --}}
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
                                <form id="exportForm" action="{{ route('export_utilidad.export') }}" method="POST">
                                    @csrf
                                    <input type="date" id="fecha_de" name="fecha_de" value="{{$fechaDe}}">
                                    <input type="date" id="fecha_hasta" name="fecha_hasta" value="{{$fechaHasta}}">
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
                                    <button type="submit" id="exportButton" class="btn btn-primary">Exportar a PDF</button>
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
            $('.contenedor').select2();
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
