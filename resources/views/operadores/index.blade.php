@extends('layouts.app')

@section('template_title')
    Operadores
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
                                Operadores
                            </span>

                             <div class="float-right">
                                @can('operadores-create')
                                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#operadoresModal" style="background: {{$configuracion->color_boton_add}}; color: #ffff">
                                    <i class="fa fa-fw fa-plus"></i> Crear
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
                                        <th class="text-center">No</th>
                                        <th class="text-center">Nombre <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px"></th>
                                        <th class="text-center">Telefono <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px"></th>
                                        <th class="text-center">Acciones <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px"></th>
                                    </tr>
                                </thead>

                                    <tbody class="text-center">
                                        @foreach ($operadores as $operador)
                                            <?php
                                                $registrosPendientes = $pagos_pendientes->where('id_operador', $operador->id)->count();
                                            ?>
                                            <tr>
                                                <td>{{$operador->id}}</td>
                                                <td>{{$operador->nombre}}</td>
                                                <td>{{$operador->telefono}}</td>
                                                <td>
                                                    @can('operadores-edit')
                                                    <a type="button" class="btn btn-xs btn-outline-primary" data-bs-toggle="modal" data-bs-target="#operadoresModal_Edit{{$operador->id}}">
                                                        <img src="{{ asset('img/icon/editar.webp') }}" alt="" width="25px">
                                                    </a>
                                                    @endcan
                                                </td>
                                            </tr>

                                                @include('operadores.modal_edit')
                                        @endforeach
                                    </tbody>

                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@include('operadores.modal_create')

@endsection

@section('datatable')
    <script type="text/javascript">
        const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
        searchable: true,
        fixedHeight: false
        });

    </script>
@endsection
