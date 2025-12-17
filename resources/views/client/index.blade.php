@extends('layouts.app')

@section('template_title')
    Clientes
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center">
                            <h5 id="card_title">Clientes</h5>

                            @can('clientes-create')
                                <div class="float-right">
                                    <!--button type="button" class="btn btn-sm bg-gradient-info" data-bs-toggle="modal" data-bs-target="#exampleModal" >
                                    <i class="fa fa-fw fa-plus"></i>  Nuevo cliente
                                </button-->
                                    <div class="ms-auto my-auto">
                                        <a
                                            href="{{ route('create.clients') }}"
                                            class="btn bg-gradient-info btn-xs mb-2"
                                        >
                                            +&nbsp; Nuevo cliente
                                        </a>
                                        <button
                                            type="button"
                                            class="btn btn-outline-info btn-xs mb-2"
                                            onclick="goToClientEdit()"
                                        >
                                            Editar Cliente
                                        </button>
                                        <button
                                            class="btn btn-outline-info btn-xs mb-2 mt-sm-0 mt-1"
                                            type="button"
                                            name="button"
                                            onclick="goToSubClients()"
                                        >
                                            Sub Clientes
                                        </button>
                                    </div>
                                </div>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body mt-3" style="padding: 0rem 1.5rem 1.5rem 1.5rem">
                        <div class="row">
                            <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 500px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/clientes/clientes_list.js') }}?v={{ filemtime(public_path('js/sgt/clientes/clientes_list.js')) }}"></script>

    <script>
        $(document).ready(() => {
            getClientesList();
        });
    </script>
@endpush
