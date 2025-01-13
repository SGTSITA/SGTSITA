@extends('layouts.app')

@section('template_title')
    Cotizaciones
@endsection

@section('content')
<style>

    .custom-tabs .custom-tab {
        background-color: #f8f9fa; /* Color por defecto */
        border-color: #dee2e6; /* Color del borde por defecto */
        color: #495057; /* Color del texto por defecto */
    }

    .custom-tabs .custom-tab.active {
        background-color: #47a0cd; /* Color de fondo del tab activo */
        border-color: #47a0cd; /* Color del borde del tab activo */
        color: #ffffff; /* Color del texto del tab activo */
    }

</style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            
                            <span id="card_title">
                                Cotizaciones
                            </span>

                             <div class="float-right">
                                @can('cotizaciones-create')
                                <a type="button" class="btn btn-primary" href="{{ route('create.cotizaciones') }}" style="background: {{$configuracion->color_boton_add}}; color: #ffff">
                                    Crear
                                  </a>
                                  @endcan
                              </div>
                        </div>
                    </div>

                    <nav class="mx-auto">
                        <div class="nav nav-tabs custom-tabs" id="nav-tab" role="tablist">
                        <a class="nav-link custom-tab active" id="nav-planeadas-tab" href="{{ route('index.cotizaciones') }}">
                            <img src="{{ asset('img/icon/resultado.webp') }}" alt="" width="40px">  Planeadas
                        </a>

                        <a class="nav-link custom-tab " id="nav-finalizadas-tab" href="{{ route('index_finzaliadas.cotizaciones') }}">
                            <img src="{{ asset('img/icon/pdf.webp') }}" alt="" width="40px">  Finalizadas
                        </a>

                          <a class="nav-link custom-tab" id="nav-home-tab" href="{{ route('index_espera.cotizaciones') }}">
                            <img src="{{ asset('img/icon/pausa.png') }}" alt="" width="40px">  En espera
                          </a>

                          <a class="nav-link custom-tab" id="nav-profile-tab" href="{{ route('index_aprobadas.cotizaciones') }}">
                            <img src="{{ asset('img/icon/cheque.png') }}" alt="" width="40px">  Aprobada
                          </a>

                          <a class="nav-link custom-tab" id="nav-contact-tab" href="{{ route('index_canceladas.cotizaciones') }}">
                            <img src="{{ asset('img/icon/cerrar.png') }}" alt="" width="40px">  Canceladas
                          </a>
                        </div>
                    </nav>

                    <div class="table-responsive">
                            <table class="table table-flush" id="datatable-planeadas">
                                        <thead class="thead">
                                            <tr>
                                                <th>No</th>
                                                <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Cliente</th>
                                                <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen</th>
                                                <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino</th>
                                                <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                                <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="25px">Estatus</th>
                                                <th><img src="{{ asset('img/icon/coordenadas.png') }}" alt="" width="25px">Coordeneadas</th>
                                                <th><img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">Acciones</th>
                                            </tr>
                                        </thead>
                                            <tbody>
                                                @foreach ($cotizaciones_planeadas as $cotizacion)
                                                    <tr>
                                                        <td>{{$cotizacion->id}}</td>
                                                        <td>{{$cotizacion->Cliente->nombre}}</td>
                                                        <td>{{$cotizacion->origen}}</td>
                                                        <td>{{$cotizacion->destino}}</td>
                                                        <td>{{$cotizacion->DocCotizacion->num_contenedor}}</td>

                                                        <td>
                                                            @can('cotizaciones-estatus')
                                                                <button type="button" class="btn btn-outline-success btn-xs" data-bs-toggle="modal" data-bs-target="#estatusModal{{$cotizacion->id}}">
                                                                    {{$cotizacion->estatus}}
                                                                </button>
                                                            @endcan
                                                        </td>
                                                        <td>
                                                            @if ($cotizacion->DocCotizacion && $cotizacion->DocCotizacion->Asignaciones)
                                                                @can('cotizaciones-cordeenadas')
                                                                <a type="button" class="btn btn-xs" href="{{ route('index.cooredenadas', $cotizacion->DocCotizacion->Asignaciones->id) }}">
                                                                    <img src="{{ asset('img/icon/coordenadas.png') }}" alt="" width="25px"> Coordenadas
                                                                </a>
                                                                @endcan
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @can('cotizaciones-edit')
                                                            <a type="button" class="btn btn-xs" href="{{ route('edit.cotizaciones', $cotizacion->id) }}">
                                                                <img src="{{ asset('img/icon/quotes.webp') }}" alt="" width="25px">
                                                            </a>
                                                            @endcan

                                                            @if ($cotizacion->DocCotizacion->Asignaciones)
                                                                @if ($cotizacion->DocCotizacion->Asignaciones->id_proveedor == NULL)
                                                                    @can('cotizaciones-cambio-tipo')
                                                                        <button type="button" class="btn btn-outline-success btn-xs" data-bs-toggle="modal" data-bs-target="#cambioModal{{ $cotizacion->DocCotizacion->Asignaciones->id }}">
                                                                                Propio
                                                                        </button>
                                                                    @endcan
                                                                @else

                                                                    @can('cotizaciones-cambio-tipo')
                                                                        <button type="button" class="btn btn-outline-dark btn-xs" data-bs-toggle="modal" data-bs-target="#cambioModal{{ $cotizacion->DocCotizacion->Asignaciones->id }}">
                                                                            Sub.
                                                                        </button>
                                                                    @endcan
                                                                @endif
                                                            @endif

                                                            @can('cotizaciones-cambio-empresa')
                                                                <button type="button" class="btn btn-outline-info btn-xs" data-bs-toggle="modal" data-bs-target="#cambioEmpresa{{ $cotizacion->id }}">
                                                                    <img src="{{ asset('img/icon/documento.png') }}" alt="" width="25px">
                                                                </button>
                                                            @endcan

                                                            @if ($cotizacion->DocCotizacion)
                                                                <button type="button" class="btn btn-outline-warning btn-xs" data-bs-toggle="modal" data-bs-target="#esatusDoc{{ $cotizacion->DocCotizacion->id }}">
                                                                    <img src="{{ asset('img/icon/catalogo.webp') }}" alt="" width="25px">
                                                                </button>
                                                            @endif

                                                        </td>
                                                    </tr>
                                                    @include('cotizaciones.modal_estatus_doc')
                                                    @include('cotizaciones.modal_cambio_empresa')

                                                    @include('cotizaciones.modal_estatus')
                                                    @include('cotizaciones.modal_cambio')

                                                @endforeach
                                            </tbody>

                            </table>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection

@section('datatable')
    <script type="text/javascript">
        const dataTableSearch4 = new simpleDatatables.DataTable("#datatable-planeadas", {
        searchable: true,
        fixedHeight: false
        });

        const dataTableSearch5 = new simpleDatatables.DataTable("#datatable-finalizadas", {
        searchable: true,
        fixedHeight: false
        });


        const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
        searchable: true,
        fixedHeight: false
        });

        const dataTableSearch2 = new simpleDatatables.DataTable("#datatable_aprovadas", {
        searchable: true,
        fixedHeight: false
        });

        const dataTableSearch3 = new simpleDatatables.DataTable("#datatable_canceladas", {
        searchable: true,
        fixedHeight: false
        });

        document.addEventListener("DOMContentLoaded", function() {
            // Inicializar los formularios y eventos para cada cotización
            @foreach($cotizaciones_planeadas as $cotizacion)
                @if ($cotizacion->DocCotizacion->Asignaciones)
                    (function() {
                        var modalId = "{{ $cotizacion->DocCotizacion->Asignaciones->id }}";
                        var formPropio = document.getElementById("formPropio" + modalId);
                        var formSubcontratado = document.getElementById("formSubcontratado" + modalId);

                        var radioPropio = document.getElementById("propio" + modalId);
                        var radioSubcontratado = document.getElementById("subcontratado" + modalId);

                        function inicializarFormulario() {
                            var idProveedor = "{{ $cotizacion->DocCotizacion->Asignaciones->id_proveedor }}";

                            if (idProveedor === "") {
                                formPropio.style.display = "block";
                                formSubcontratado.style.display = "none";
                                radioPropio.checked = true;
                            } else {
                                formPropio.style.display = "none";
                                formSubcontratado.style.display = "block";
                                radioSubcontratado.checked = true;
                            }
                        }

                        document.querySelectorAll('input[name="formType' + modalId + '"]').forEach(function(radio) {
                            radio.addEventListener("change", function() {
                                if (radioPropio.checked) {
                                    formPropio.style.display = "block";
                                    formSubcontratado.style.display = "none";
                                } else {
                                    formPropio.style.display = "none";
                                    formSubcontratado.style.display = "block";
                                }
                            });
                        });

                        // Inicializar el formulario cuando se muestra el modal
                        $('#cambioModal' + modalId).on('show.bs.modal', function () {
                            inicializarFormulario();
                        });
                    })();
                @endif
            @endforeach
        });

        $(document).ready(function() {
            $('[id^="btn_clientes_search"]').click(function() {
                var cotizacionId = $(this).data('cotizacion-id'); // Obtener el ID de la cotización del atributo data
                buscar_clientes(cotizacionId);
            });

            function buscar_clientes(cotizacionId) {
                $('#loadingSpinner').show();

                var fecha_inicio = $('#fecha_inicio_' + cotizacionId).val();
                var fecha_fin = $('#fecha_fin_' + cotizacionId).val();

                $.ajax({
                    url: '{{ route('equipos.planeaciones') }}',
                    type: 'get',
                    data: {
                        'fecha_inicio': fecha_inicio,
                        'fecha_fin': fecha_fin,
                        '_token': '{{ csrf_token() }}' // Agregar el token CSRF a los datos enviados
                    },
                    success: function(data) {
                        $('#resultado_equipos' + cotizacionId).html(data); // Actualiza la sección con los datos del servicio
                    },
                    error: function(error) {
                        console.log(error);
                    },
                    complete: function() {
                        // Ocultar el spinner cuando la búsqueda esté completa
                        $('#loadingSpinner').hide();
                    }
                });
            }
        });


        document.addEventListener("DOMContentLoaded", function() {
            @foreach($cotizaciones_planeadas as $cotizacion)
                @if ($cotizacion->DocCotizacion->Asignaciones)
                    (function() {
                        var asignacionId = "{{ $cotizacion->DocCotizacion->Asignaciones->id }}";

                        function calcularTotal(asignacionId) {
                            var precio = parseFloat($('#cot_precio_' + asignacionId).val()) || 0;
                            var burreo = parseFloat($('#cot_burreo_' + asignacionId).val()) || 0;
                            var maniobra = parseFloat($('#cot_maniobra_' + asignacionId).val()) || 0;
                            var estadia = parseFloat($('#cot_estadia_' + asignacionId).val()) || 0;
                            var otro = parseFloat($('#cot_otro_' + asignacionId).val()) || 0;
                            var retencion = parseFloat($('#cot_retencion_' + asignacionId).val()) || 0;
                            var iva = parseFloat($('#cot_iva_' + asignacionId).val()) || 0;

                            var total = precio + burreo + maniobra + estadia + otro + iva - retencion;

                            $('#total_proveedor_' + asignacionId).val(total.toFixed(2));
                        }

                        $('#cot_precio_' + asignacionId + ', #cot_burreo_' + asignacionId + ', #cot_maniobra_' + asignacionId + ', #cot_estadia_' + asignacionId + ', #cot_otro_' + asignacionId + ', #cot_retencion_' + asignacionId + ', #cot_iva_' + asignacionId).on('input', function() {
                            calcularTotal(asignacionId);
                        });

                        // Inicializa el total cuando se muestra el modal
                        $('#cambioModal' + asignacionId).on('show.bs.modal', function () {
                            calcularTotal(asignacionId);
                        });
                    })();
                @endif
            @endforeach
        });
    </script>
@endsection
