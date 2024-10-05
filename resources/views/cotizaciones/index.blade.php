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
                            <a href="{{ route('dashboard') }}" class="btn btn-xs" style="background: {{$configuracion->color_boton_close}}; color: #ffff; margin-right: 3rem;">
                                Regresar
                            </a>
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
                        <button class="nav-link custom-tab active" id="nav-planeadas-tab" data-bs-toggle="tab" data-bs-target="#nav-planeadas" type="button" role="tab" aria-controls="nav-planeadas" aria-selected="false">
                            <img src="{{ asset('img/icon/resultado.webp') }}" alt="" width="40px">  Planeadas
                        </button>

                        <button class="nav-link custom-tab " id="nav-finalizadas-tab" data-bs-toggle="tab" data-bs-target="#nav-finalizadas" type="button" role="tab" aria-controls="nav-finalizadas" aria-selected="false">
                            <img src="{{ asset('img/icon/pdf.webp') }}" alt="" width="40px">  Finalizadas
                        </button>

                          <button class="nav-link custom-tab" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">
                            <img src="{{ asset('img/icon/pausa.png') }}" alt="" width="40px">  En espera
                          </button>

                          <button class="nav-link custom-tab" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">
                            <img src="{{ asset('img/icon/cheque.png') }}" alt="" width="40px">  Aprobada
                          </button>

                          <button class="nav-link custom-tab" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">
                            <img src="{{ asset('img/icon/cerrar.png') }}" alt="" width="40px">  Canceladas
                          </button>
                        </div>
                    </nav>


                      <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-planeadas" role="tabpanel" aria-labelledby="nav-planeadas-tab" tabindex="0">
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

                        <div class="tab-pane fade " id="nav-finalizadas" role="tabpanel" aria-labelledby="nav-finalizadas-tab" tabindex="0">
                            <div class="table-responsive">
                                    <table class="table table-flush" id="datatable-finalizadas">
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
                                                @foreach ($cotizaciones_finalizadas as $cotizacion)
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

                                                            @can('cotizaciones-pdf')
                                                            <a type="button" class="btn btn-xs" href="{{ route('pdf.cotizaciones', $cotizacion->id) }}">
                                                                <img src="{{ asset('img/icon/pdf.webp') }}" alt="" width="25px">
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
                                                                    Subcontratado
                                                                </button>
                                                                @endcan
                                                                @endif
                                                            @endif

                                                            @if ($cotizacion->DocCotizacion)
                                                                <button type="button" class="btn btn-outline-warning btn-xs" data-bs-toggle="modal" data-bs-target="#esatusDoc{{ $cotizacion->DocCotizacion->id }}">
                                                                    <img src="{{ asset('img/icon/catalogo.webp') }}" alt="" width="25px">
                                                                </button>
                                                            @endif

                                                        </td>
                                                    </tr>
                                                    @include('cotizaciones.modal_estatus_doc')
                                                    @include('cotizaciones.modal_estatus')
                                                    @include('cotizaciones.modal_cambio')
                                                @endforeach
                                            </tbody>

                                    </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                            <div class="table-responsive">
                                    <table class="table table-flush" id="datatable-search">
                                        <thead class="thead">
                                            <tr>
                                                <th>No</th>
                                                <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Cliente</th>
                                                <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen</th>
                                                <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino</th>
                                                <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                                <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="25px">Estatus</th>
                                                <th><img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">Acciones</th>
                                            </tr>
                                        </thead>
                                            <tbody>
                                                @foreach ($cotizaciones as $cotizacion)
                                                    <tr>
                                                        <td>{{$cotizacion->id}}</td>
                                                        <td>{{$cotizacion->Cliente->nombre}}</td>
                                                        <td>{{$cotizacion->origen}}</td>
                                                        <td>{{$cotizacion->destino}}</td>
                                                        <td>{{$cotizacion->DocCotizacion->num_contenedor}}</td>
                                                        <td>
                                                            @if ($cotizacion->estatus == 'Pendiente')
                                                            @can('cotizaciones-estatus')
                                                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#estatusModal{{$cotizacion->id}}">
                                                                    {{$cotizacion->estatus}}
                                                                </button>
                                                                @endcan
                                                            @elseif ($cotizacion->estatus == 'Cancelada')
                                                            @can('cotizaciones-estatus')
                                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#estatusModal{{$cotizacion->id}}">
                                                                    {{$cotizacion->estatus}}
                                                                </button>
                                                                @endcan
                                                            @elseif ($cotizacion->estatus == 'Aprobada')
                                                            @can('cotizaciones-estatus')
                                                                <button type="button" class="btn btn-outline-success btn-xs" data-bs-toggle="modal" data-bs-target="#estatusModal{{$cotizacion->id}}">
                                                                    {{$cotizacion->estatus}}
                                                                </button>
                                                                @endcan
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($cotizacion->estatus == 'Aprobada')
                                                                @can('cotizaciones-edit')
                                                                <a type="button" class="btn btn-xs" href="{{ route('edit.cotizaciones', $cotizacion->id) }}">
                                                                    <img src="{{ asset('img/icon/quotes.webp') }}" alt="" width="25px">
                                                                </a>
                                                                @endcan
                                                            @endif
                                                            @if ($cotizacion->DocCotizacion)
                                                                <button type="button" class="btn btn-outline-warning btn-xs" data-bs-toggle="modal" data-bs-target="#esatusDoc{{ $cotizacion->DocCotizacion->id }}">
                                                                    <img src="{{ asset('img/icon/catalogo.webp') }}" alt="" width="25px">
                                                                </button>
                                                            @endif

                                                        </td>
                                                    </tr>
                                                    @include('cotizaciones.modal_estatus_doc')
                                                    @include('cotizaciones.modal_estatus')
                                                @endforeach
                                            </tbody>

                                    </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-flush" id="datatable_aprovadas">
                                    <thead class="thead">
                                        <tr>
                                            <th>No</th>
                                            <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Cliente</th>
                                            <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen</th>
                                            <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino</th>
                                            <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px">Contenedor</th>
                                            <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="25px">Estatus</th>
                                            <th><img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">Acciones</th>
                                        </tr>
                                    </thead>

                                        <tbody>
                                            @foreach ($cotizaciones_aprovadas as $cotizacion)
                                                <tr>
                                                    <td>{{$cotizacion->id}}</td>
                                                    <td>{{$cotizacion->Cliente->nombre}}</td>
                                                    <td>{{$cotizacion->origen}}</td>
                                                    <td>{{$cotizacion->destino}}</td>
                                                    <td>#{{$cotizacion->DocCotizacion->num_contenedor}}</td>
                                                    <td>
                                                        @can('cotizaciones-estatus')
                                                        @if ($cotizacion->estatus == 'Pendiente')
                                                            <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#estatusModal{{$cotizacion->id}}">
                                                                {{$cotizacion->estatus}}
                                                            </button>
                                                        @elseif ($cotizacion->estatus == 'Cancelada')
                                                            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#estatusModal{{$cotizacion->id}}">
                                                                {{$cotizacion->estatus}}
                                                            </button>
                                                        @elseif ($cotizacion->estatus == 'Aprobada')
                                                            <button type="button" class="btn btn-outline-success btn-xs" data-bs-toggle="modal" data-bs-target="#estatusModal{{$cotizacion->id}}">
                                                                {{$cotizacion->estatus}}
                                                            </button>
                                                        @endif
                                                        @endcan
                                                    </td>
                                                    <td>
                                                        @if ($cotizacion->estatus == 'edit')
                                                            @can('cotizaciones-estatus')
                                                            <a type="button" class="btn btn-xs" href="{{ route('edit.cotizaciones', $cotizacion->id) }}">
                                                                <img src="{{ asset('img/icon/quotes.webp') }}" alt="" width="25px">
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
                                            @endforeach
                                        </tbody>

                                </table>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-flush" id="datatable_canceladas">
                                    <thead class="thead">
                                        <tr>
                                            <th>No</th>
                                            <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Cliente</th>
                                            <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen</th>
                                            <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino</th>
                                            <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                            <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="25px">Estatus</th>
                                            <th><img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">Acciones</th>
                                        </tr>
                                    </thead>

                                        <tbody>
                                            @foreach ($cotizaciones_canceladas as $cotizacion)
                                                <tr>
                                                    <td>{{$cotizacion->id}}</td>
                                                    <td>{{$cotizacion->Cliente->nombre}}</td>
                                                    <td>{{$cotizacion->origen}}</td>
                                                    <td>{{$cotizacion->destino}}</td>
                                                    <td>{{$cotizacion->DocCotizacion->num_contenedor}}</td>
                                                    <td>
                                                        @can('cotizaciones-estatus')
                                                            @if ($cotizacion->estatus == 'Pendiente')
                                                                <button type="button" class="btn btn-outline-warning" data-bs-toggle="modal" data-bs-target="#estatusModal{{$cotizacion->id}}">
                                                                    {{$cotizacion->estatus}}
                                                                </button>
                                                            @elseif ($cotizacion->estatus == 'Cancelada')
                                                                <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#estatusModal{{$cotizacion->id}}">
                                                                    {{$cotizacion->estatus}}
                                                                </button>
                                                            @elseif ($cotizacion->estatus == 'Aprobada')
                                                                <button type="button" class="btn btn-outline-success btn-xs" data-bs-toggle="modal" data-bs-target="#estatusModal{{$cotizacion->id}}">
                                                                    {{$cotizacion->estatus}}
                                                                </button>
                                                            @endif
                                                        @endcan
                                                    </td>
                                                    <td>
                                                        @if ($cotizacion->estatus == 'Aprobada')
                                                            @can('cotizaciones-edit')
                                                            <a type="button" class="btn btn-xs" href="{{ route('edit.cotizaciones', $cotizacion->id) }}">
                                                                <img src="{{ asset('img/icon/quotes.webp') }}" alt="" width="25px">
                                                            </a>
                                                            @endcan
                                                        @endif
                                                        @if ($cotizacion->DocCotizacion)
                                                            <button type="button" class="btn btn-outline-warning btn-xs" data-bs-toggle="modal" data-bs-target="#esatusDoc{{ $cotizacion->DocCotizacion->id }}">
                                                                <img src="{{ asset('img/icon/catalogo.webp') }}" alt="" width="25px">
                                                            </button>
                                                        @endif
                                                    </td>
                                                </tr>
                                                @include('cotizaciones.modal_estatus_doc')
                                                @include('cotizaciones.modal_estatus')
                                            @endforeach
                                        </tbody>

                                </table>
                            </div>
                        </div>
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
