@extends('layouts.app')

@section('template_title', 'Proveedores')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card ">
                    <div class="card-header text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Proveedores</h5>
                        <div class="d-flex gap-2">
                            @can('proovedores-create')
                                <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#proveedores">
                                    +&nbsp; Nuevo Proveedor
                                </a>
                            @endcan
                            <button class="btn btn-dark btn-sm" onclick="openCuentaGlobalModal()">
                                <i class="fas fa-coins"></i> Cuenta Global
                            </button>
                        </div>
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

<!-- Modal Cuenta Global -->
<div class="modal fade" id="modalCuentaGlobal" tabindex="-1" aria-labelledby="cuentaGlobalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form id="formCuentaGlobal" class="w-100">
            @csrf
            <div class="modal-content shadow-lg border-0">
                <div class="modal-header  text-white">
                    <h5 class="modal-title" id="cuentaGlobalLabel">
                        <i class="fas fa-university me-2"></i> Cuenta Global
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>

                <div class="modal-body py-4 px-5">
                    <div class="row g-3">
                        <div class="col-md-12">
                            <label for="global_beneficiario" class="form-label fw-semibold">Nombre del
                                Beneficiario</label>
                            <input type="text" class="form-control" id="global_beneficiario"
                                name="nombre_beneficiario" required>
                        </div>

                        <div class="col-md-6">
                            <label for="global_banco" class="form-label fw-semibold">Banco</label>
                            <input type="text" class="form-control" id="global_banco" name="banco" required>
                        </div>

                        <div class="col-md-6">
                            <label for="global_cuenta" class="form-label fw-semibold">Número de Cuenta</label>
                            <input type="text" class="form-control" id="global_cuenta" name="cuenta" required>
                        </div>

                        <div class="col-md-12">
                            <label for="global_clabe" class="form-label fw-semibold">CLABE</label>
                            <input type="text" class="form-control" id="global_clabe" name="clabe" required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer px-4 py-3 bg-light border-top">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-save me-2"></i>Guardar Cuenta Global
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
