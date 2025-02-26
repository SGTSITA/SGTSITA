@extends('layouts.app')

@section('template_title', 'Proveedores')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card ">
                    <div class="card-header text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Proveedores</h5>
                        @can('proovedores-create')
                            <a href="#" class="btn bg-gradient-info btn-xs mb-2" data-bs-toggle="modal"
                                data-bs-target="#proveedores">
                                +&nbsp; Nuevo Proveedor
                            </a>
                        @endcan
                    </div>

                    <div class="card-body mt-3">
                        <div class="row">
                            <div id="proveedoresGrid" class="col-12 ag-theme-alpine"
                                style="height: 500px; width: 100%; max-width: 100%;">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <!-- MODALES COMENTADOS -->
    {{-- @include('proveedores.modal_create') --}}
    @include('proveedores.modal_edit')
    @include('proveedores.modal_crear_cuenta')
    @include('proveedores.modal_cuentas')

@endsection


@push('custom-javascript')
    <script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script
        src="{{ asset('js/sgt/proveedores/proveedores_list.js') }}?v={{ filemtime(public_path('js/sgt/proveedores/proveedores_list.js')) }}">
    </script>

    <script>
        $(document).ready(() => {
            getProveedoresList();
        });
    </script>
@endpush
