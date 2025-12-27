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
                        <div style="display: flex; justify-content: space-between; align-items: center">
                            <a
                                id="backButton"
                                class="btn"
                                style="
                                    background: {{ $configuracion->color_boton_close }};
                                    color: #ffff;
                                    margin-right: 3rem;
                                "
                            >
                                Regresar
                            </a>
                            <span id="card_title">Tenats</span>

                            <div class="float-right">
                                <button
                                    type="button"
                                    class="btn btn-primary"
                                    data-bs-toggle="modal"
                                    data-bs-target="#operadoresModal"
                                    style="background: {{ $configuracion->color_boton_add }}; color: #ffff"
                                >
                                    <i class="fa fa-fw fa-plus"></i>
                                    Crear
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-flush" id="datatable-search">
                                <thead class="thead">
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th class="text-center">
                                            Dominios
                                            <img
                                                src="{{ asset('img/icon/user_predeterminado.webp') }}"
                                                alt=""
                                                width="25px"
                                            />
                                        </th>
                                        <th class="text-center">Editar</th>
                                    </tr>
                                </thead>

                                <tbody class="text-center">
                                    @foreach ($tenants as $item)
                                        <tr>
                                            <th>{{ $item->id }}</th>
                                            <th>{{ $item->domains->first()->domain ?? '' }}</th>
                                            <th>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#equipoEditModal-{{ $item->id }}"
                                                >
                                                    <img
                                                        src="{{ asset('img/icon/editar.webp') }}"
                                                        alt=""
                                                        width="25px"
                                                    />
                                                </button>
                                                <form action="{{ route('eliminar.tenants', $item) }}" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button class="btn btn-danger">Eliminar</button>
                                                </form>
                                            </th>
                                        </tr>

                                        @include('tenants.modal_edit')
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('tenants.modal_create')
@endsection

@section('datatable')
    <script type="text/javascript">
        const dataTableSearch = new simpleDatatables.DataTable('#datatable-search', {
            searchable: true,
            fixedHeight: false,
        });
    </script>
@endsection
