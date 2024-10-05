@extends('layouts.app')

@section('template_title')
Empresas
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

                            <h2 id="card_title">
                               Empresas
                            </h2>

                             <div class="float-right">
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal" style="background: {{$configuracion->color_boton_add}}; color: #ffff">
                                    <i class="fa fa-fw fa-plus"></i>  Crear
                                </button>
                              </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table_id" id="datatable-search">
                                <thead class="thead">
                                    <tr>
                                        <th>No</th>
										<th>Nombre</th>
										<th>Telefono</th>
										<th>Correo</th>
                                        <th>Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($empresas as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
											<td>{{ $item->nombre }}</td>
											<td>{{ $item->telefono }}</td>
											<td>{{ $item->correo }}</td>
                                            <td>
                                            <td>
                                                <a class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#editModal-{{ $item->id }}">
                                                    <i class="fa fa-fw fa-edit"></i> Editar</a>
                                            </td>
                                        </tr>
                                        @include('empresa.editar')
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('empresa.modal_create')
@endsection

@section('datatable')

<script>
    const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
      searchable: true,
      fixedHeight: false
    });

</script>

@endsection
