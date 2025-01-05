@extends('layouts.app')

@section('template_title')
    Client
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            
                            <span id="card_title">
                                Proveedores
                            </span>

                             <div class="float-right">
                                @can('proovedores-create')
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#proveedores" style="background: {{$configuracion->color_boton_add}}; color: #ffff">
                                    <i class="fa fa-fw fa-plus"></i>  Crear
                                  </button>
                                  @endcan
                              </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-flush" id="datatable-search">
                                <thead class="thead">
                                    <tr>
                                        <th>No</th>
                                        <th>Nombre</th>
                                        <th>Telefono</th>
                                        <th>Tipo</th>
                                        <th>RFC</th>
                                        <th>Cuentas Bancarias</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>

                                    <tbody>
                                        @foreach ($proveedores as $proveedor)
                                            <tr>
                                                <td>{{$proveedor->id}}</td>
                                                <td>{{$proveedor->nombre}}</td>
                                                <td>{{$proveedor->telefono}}</td>
                                                <td>{{$proveedor->tipo}}</td>
                                                <td>{{$proveedor->rfc}}</td>
                                                <td>
                                                    @can('proovedores-cuentas')
                                                    <button type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cuentasModal{{$proveedor->id}}">
                                                        Ver cuentas registradas <img src="{{ asset('img/icon/business-card-design.webp') }}" alt="" width="20px">
                                                    </button>
                                                     @endcan
                                                </td>
                                                <td>
                                                    @can('proovedores-edit')
                                                        <button type="button" class="btn btn-xs btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#editModal{{$proveedor->id}}">
                                                            <img src="{{ asset('img/icon/editar.webp') }}" alt="" width="20px">
                                                        </button>
                                                    @endcan

                                                    @can('proovedores-cuentas-create')
                                                        <button type="button" class="btn btn-xs btn-outline-success" data-bs-toggle="modal" data-bs-target="#editarModal{{$proveedor->id}}">
                                                            <img src="{{ asset('img/icon/t credito.png.webp') }}" alt="" width="20px">
                                                        </button>
                                                    @endcan

                                                </td>
                                            </tr>
                                            @include('proveedores.modal_edit')
                                            @include('proveedores.modal_crear_cuenta')
                                            @include('proveedores.modal_cuentas')
                                        @endforeach
                                    </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@include('proveedores.modal_create')

@endsection

@section('datatable')

<script>
    const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
      searchable: true,
      fixedHeight: false
    });
</script>

@endsection
