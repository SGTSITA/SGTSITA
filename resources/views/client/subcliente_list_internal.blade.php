@extends('layouts.app')

@section('meta-tags')
    <meta name="id-cliente" content="{{ $id_client }}" />
    <meta name="is-client" content="no" />
@endsection

@section('template_title')
    Sub Clientes
@endsection

@section('content')
    <div class="row gx-5 gx-xl-10">
        <div class="col-sm-12 mb-3 mb-xl-3">
            <div class="card card-flush h-lg-100">
                <div class="card-header">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <h6 class="mb-0">Sub Clientes</h6>
                        </div>
                        <div class="col-6 text-end">
                            <form action="{{ route('new.subcliente') }}" method="post">
                                @csrf
                                <input type="hidden" name="idClient" id="idClient" value="{{ $id_client }}" />
                                <button type="submit" class="btn btn-sm bg-gradient-info me-3">
                                    Agregar SubCliente
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-md-0 mb-2">
                            <ul class="list-group">
                                <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                                    <div class="d-flex flex-column">
                                        <h6 class="mb-3 text-sm">Cliente Principal</h6>
                                        <span class="mb-2 text-md">
                                            Nombre:
                                            <span class="text-dark font-weight-bold ms-sm-2">
                                                {{ $client->nombre }}
                                            </span>
                                        </span>
                                        <span class="mb-2 text-md">
                                            SubClientes:
                                            <span class="text-dark ms-sm-2 font-weight-bold" id="countSubclientes">
                                                0
                                            </span>
                                        </span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    <div class="row">
                        <div id="agGridSubClientes" class="col-12 ag-theme-quartz" style="height: 450px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/clientes/subclientes_list.js') }}?v={{ filemtime(public_path('js/sgt/clientes/subclientes_list.js')) }}"></script>
    <script src="{{ asset('js/sgt/clientes/subclientes.js') }}?v={{ filemtime(public_path('js/sgt/clientes/subclientes.js')) }}"></script>

    <script>
        $(document).ready((e) => {
            getSubClientes();
            var client = JSON.parse();
        });
    </script>
@endpush
