@extends('layouts.app')

@section('template_title')
    Equipos
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <a href="{{ route('dashboard') }}" class="btn" style="background: {{$configuracion->color_boton_close}}; color: #ffff; margin-right: 3rem;">
                                Regresar
                            </a>
                            <span id="card_title">
                                Equipos
                            </span>

                            @can('equipos-create')
                             <div class="float-right">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#equipoModal" style="background: {{$configuracion->color_boton_add}}; color: #ffff">
                                    <i class="fa fa-fw fa-plus"></i> Crear
                                  </button>
                              </div>
                            @endcan
                        </div>
                    </div>

                    <nav class="mx-auto">
                        <div class="nav nav-tabs" id="nav-tab" role="tablist">
                          <button class="nav-link active" id="nav-home-tab" data-bs-toggle="tab" data-bs-target="#nav-home" type="button" role="tab" aria-controls="nav-home" aria-selected="true">
                            <img src="{{ asset('img/icon/chasis.png') }}" alt="" width="40px">  Dollys
                          </button>

                          <button class="nav-link" id="nav-profile-tab" data-bs-toggle="tab" data-bs-target="#nav-profile" type="button" role="tab" aria-controls="nav-profile" aria-selected="false">
                            <img src="{{ asset('img/icon/troca.png') }}" alt="" width="40px">  Chasis Plataforma
                          </button>

                          <button class="nav-link" id="nav-contact-tab" data-bs-toggle="tab" data-bs-target="#nav-contact" type="button" role="tab" aria-controls="nav-contact" aria-selected="false">
                            <img src="{{ asset('img/icon/camion.png') }}" alt="" width="40px">  Tractos / Camiones
                          </button>
                        </div>
                      </nav>

                      <div class="tab-content" id="nav-tabContent">
                        <div class="tab-pane fade show active" id="nav-home" role="tabpanel" aria-labelledby="nav-home-tab" tabindex="0">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-flush" id="datatable-search2">
                                        <thead class="thead">
                                            <tr>
                                                <th>No</th>
                                                <th>Folio  <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px"></th>
                                                <th>Vehiculo <img src="{{ asset('img/icon/coche.png') }}" alt="" width="25px"></th>
                                                <th>Acceso <img src="{{ asset('img/icon/iniciar-sesion.png') }}" alt="" width="25px"></th>
                                                <th>ID Interno <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px"></th>
                                                <th>Fecha Alta <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px"></th>
                                                <th>Acciones <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px"></th>
                                            </tr>
                                        </thead>

                                            <tbody>
                                                @foreach ($equipos_dolys as $item)
                                                    <tr>
                                                        <td>{{$item->id}}</td>
                                                        <td>{{$item->id_equipo}}</td>
                                                        <td>
                                                            <ul>
                                                                <li>Marca : {{$item->marca}}</li>
                                                                <li>Año : {{$item->year}}</li>
                                                                <li>Modelo : {{$item->modelo}}</li>
                                                                <li>Num Serie : {{$item->num_serie}}</li>
                                                                <li>Placas : {{$item->placas}}</li>                                                            </ul>
                                                        </td>
                                                        <td>{{$item->acceso}}</td>
                                                        <td>{{$item->id_equipo}}</td>
                                                        <td>{{$item->fecha}}</td>
                                                        <td>
                                                            @can('equipos-edit')
                                                            <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#equipoEditModal-{{$item->id}}">
                                                                <img src="{{ asset('img/icon/editar.webp') }}" alt="" width="25px">
                                                            </button>
                                                            @endcan

                                                            @can('equipos-documentos')
                                                            <button type="button" class="btn btn-xs btn-outline-success" data-bs-toggle="modal" data-bs-target="#documenotsdigitales-{{$item->id}}">
                                                                <img src="{{ asset('img/icon/galeria-de-imagenes.webp') }}" alt="" width="25px">
                                                            </button>
                                                            @endcan

                                                            @can('equipos-delete')
                                                            <form method="POST" class="d-inline" action="{{ route('desactivar.equipos', $item->id) }}" id="" enctype="multipart/form-data" role="form">
                                                               <input type="hidden" name="_method" value="PATCH">
                                                               @csrf

                                                               <input type="hidden" name="tipo" value="desactivado">

                                                               <button type="submit" class="btn btn-xs btn-outline-warning" >
                                                                   <img src="{{ asset('img/icon/borrar.webp') }}" alt="" width="25px">
                                                               </button>
                                                            </form>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                    @include('equipos.modal_edit')
                                                    @include('equipos.modal_docs')

                                                @endforeach
                                            </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="nav-profile" role="tabpanel" aria-labelledby="nav-profile-tab" tabindex="0">
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-flush" id="datatable-search3">
                                        <thead class="thead">
                                            <tr>
                                                <th>No</th>
                                                <th>Folio  <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px"></th>
                                                <th>Vehiculo <img src="{{ asset('img/icon/coche.png') }}" alt="" width="25px"></th>
                                                <th>Acceso <img src="{{ asset('img/icon/iniciar-sesion.png') }}" alt="" width="25px"></th>
                                                <th>ID Interno <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px"></th>
                                                <th>Fecha Alta <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px"></th>
                                                <th>Acciones <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px"></th>
                                            </tr>
                                        </thead>

                                            <tbody>
                                                @foreach ($equipos_chasis as $item)
                                                    <tr>
                                                        <td>{{$item->id}}</td>
                                                        <td>{{$item->id_equipo}}</td>
                                                        <td>
                                                            <ul>
                                                                <li>Marca : {{$item->marca}}</li>
                                                                <li>Año : {{$item->year}}</li>
                                                                <li>Modelo : {{$item->modelo}}</li>
                                                                <li>Motor : {{$item->motor}}</li>
                                                                <li>Num Serie : {{$item->num_serie}}</li>
                                                                <li>Placas : {{$item->placas}}</li>                                                            </ul>
                                                        </td>
                                                        <td>{{$item->acceso}}</td>
                                                        <td>{{$item->id_equipo}}</td>
                                                        <td>{{$item->fecha}}</td>
                                                        <td>
                                                            @can('equipos-edit')
                                                            <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#equipoEditModal-{{$item->id}}">
                                                                <img src="{{ asset('img/icon/editar.webp') }}" alt="" width="25px">
                                                            </button>
                                                             @endcan
                                                            @can('equipos-documentos')
                                                            <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#documenotsdigitales-{{$item->id}}">
                                                                <img src="{{ asset('img/icon/galeria-de-imagenes.webp') }}" alt="" width="25px">
                                                            </button>
                                                             @endcan

                                                             @can('equipos-delete')
                                                             <form method="POST" class="d-inline" action="{{ route('desactivar.equipos', $item->id) }}" id="" enctype="multipart/form-data" role="form">
                                                                <input type="hidden" name="_method" value="PATCH">
                                                                @csrf

                                                                <input type="hidden" name="tipo" value="desactivado">

                                                                <button type="submit" class="btn btn-xs btn-outline-warning" >
                                                                    <img src="{{ asset('img/icon/borrar.webp') }}" alt="" width="25px">
                                                                </button>
                                                             </form>
                                                             @endcan
                                                        </td>
                                                    </tr>
                                                    @include('equipos.modal_edit')
                                                    @include('equipos.modal_docs')

                                                @endforeach
                                            </tbody>

                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="tab-pane fade" id="nav-contact" role="tabpanel" aria-labelledby="nav-contact-tab" tabindex="0">
                            <div class="table-responsive">
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-flush" id="datatable-search">
                                            <thead class="thead">
                                                <tr>
                                                    <th>No</th>
                                                    <th>Folio  <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px"></th>
                                                    <th>Vehiculo <img src="{{ asset('img/icon/coche.png') }}" alt="" width="25px"></th>
                                                    <th>Acceso <img src="{{ asset('img/icon/iniciar-sesion.png') }}" alt="" width="25px"></th>
                                                    <th>Fecha Alta <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px"></th>
                                                    <th>Acciones <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px"></th>
                                                </tr>
                                            </thead>

                                                <tbody>
                                                    @foreach ($equipos_camiones as $item)
                                                        <tr>
                                                            <td>{{$item->id}}</td>
                                                            <td>{{$item->id_equipo}}</td>
                                                            <td>
                                                                <ul>
                                                                    <li>Marca : {{$item->marca}}</li>
                                                                    <li>Año : {{$item->year}}</li>
                                                                    <li>Modelo : {{$item->modelo}}</li>
                                                                    <li>Motor : {{$item->motor}}</li>
                                                                    <li>Num Serie : {{$item->num_serie}}</li>
                                                                    <li>Placas : {{$item->placas}}</li>
                                                                </ul>
                                                            </td>
                                                            <td>{{$item->acceso}}</td>
                                                            <td>{{$item->fecha}}</td>
                                                            <td>
                                                                @can('equipos-edit')
                                                                <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#equipoEditModal-{{$item->id}}">
                                                                    <img src="{{ asset('img/icon/editar.webp') }}" alt="" width="25px">
                                                                </button>
                                                                 @endcan

                                                                @can('equipos-documentos')
                                                                <button type="button" class="btn btn-xs btn-outline-success" data-bs-toggle="modal" data-bs-target="#documenotsdigitales-{{$item->id}}">
                                                                    <img src="{{ asset('img/icon/galeria-de-imagenes.webp') }}" alt="" width="25px">
                                                                </button>
                                                                @endcan

                                                                 @can('equipos-delete')
                                                                    <form method="POST" class="d-inline" action="{{ route('desactivar.equipos', $item->id) }}" id="" enctype="multipart/form-data" role="form">
                                                                        <input type="hidden" name="_method" value="PATCH">
                                                                        @csrf

                                                                        <input type="hidden" name="tipo" value="desactivado">

                                                                        <button type="submit" class="btn btn-xs btn-outline-warning" >
                                                                            <img src="{{ asset('img/icon/borrar.webp') }}" alt="" width="25px">
                                                                        </button>
                                                                    </form>
                                                                 @endcan

                                                            </td>
                                                        </tr>
                                                        @include('equipos.modal_edit')
                                                        @include('equipos.modal_docs')

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
    </div>

@include('equipos.modal_create')

@endsection

@section('datatable')
    <script type="text/javascript">
        const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
        searchable: true,
        fixedHeight: false
        });

        const dataTableSearch = new simpleDatatables.DataTable("#datatable-search2", {
        searchable: true,
        fixedHeight: false
        });

        const dataTableSearch = new simpleDatatables.DataTable("#datatable-search3", {
        searchable: true,
        fixedHeight: false
        });

    </script>
@endsection
