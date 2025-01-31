@extends('layouts.app')

@section('template_title')
    Nuevo Cliente
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
                    <div class="card-header">
                        <div class="card-title m-0">
                            @if (!isset($cliente))
                                <h5 class="fw-bold mt-5">Crear Cliente</h5>
                            @else
                                <h5 class="fw-bold mt-5">Editar Cliente</h5>
                            @endif
                            <p class="text-muted mb-2">
                                Por favor proporcione los datos a continuación
                            </p>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Navegación de pestañas -->
                        <ul class="nav nav-tabs" id="clienteTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="datos-generales-tab" data-bs-toggle="tab"
                                    data-bs-target="#datos-generales" type="button" role="tab"
                                    aria-controls="datos-generales" aria-selected="true">Datos Generales</button>
                            </li>
                            @if (isset($cliente))
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="empresas-vinculadas-tab" data-bs-toggle="tab"
                                        data-bs-target="#empresas-vinculadas" type="button" role="tab"
                                        aria-controls="empresas-vinculadas" aria-selected="false">Empresas
                                        Vinculadas</button>
                                </li>
                            @endif
                        </ul>
                        <div class="tab-content mt-4" id="clienteTabsContent">
                            <!-- Datos Generales -->
                            <div class="tab-pane fade show active" id="datos-generales" role="tabpanel"
                                aria-labelledby="datos-generales-tab">
                                <form method="POST"
                                    @if (!isset($cliente)) action="{{ route('store.clients') }}" 
                            @else
                            action="{{ route('update.client') }}" @endif
                                    id="clienteCreate" enctype="multipart/form-data" role="form">
                                    @csrf
                                    <div class="row mb-3">
                                        <label class="col-lg-2 fw-semibold text-muted">Nombre ó Razón Social</label>
                                        <div class="col-lg-8">
                                            @if (isset($cliente))
                                                <input type="hidden" id="idClient" value="{{ $cliente->id }}">
                                            @endif
                                            <input type="text" class="form-control" name="txtNombre" id="nombre"
                                                placeholder="Nombre completo" autocomplete="off"
                                                @if (isset($cliente)) value="{{ $cliente->nombre }}" @endif>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 fw-semibold text-muted">RFC</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="rfc"
                                                placeholder="Proporcione RFC" autocomplete="off"
                                                @if (isset($cliente)) value="{{ $cliente->rfc }}" @endif>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 fw-semibold text-muted">Régimen Fiscal</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="regimen_fiscal"
                                                placeholder="Régimen Fiscal" autocomplete="off"
                                                @if (isset($cliente)) value="{{ $cliente->regimen_fiscal }}" @endif>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 fw-semibold text-muted">Nombre Comercial</label>
                                        <div class="col-lg-8 fv-row">
                                            <input type="text" class="form-control" id="nombre_empresa"
                                                placeholder="Nombre con el que se conoce a la empresa" autocomplete="off"
                                                @if (isset($cliente)) value="{{ $cliente->nombre_empresa }}" @endif>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 fw-semibold text-muted">Correo Electrónico</label>
                                        <div class="col-lg-8">
                                            <span class="badge bg-gradient-info badge-sm mb-2">Cuenta de acceso al
                                                sistema</span>
                                            <input type="text" class="form-control mb-2" id="correo"
                                                placeholder="Correo Electrónico para acceder al sistema" autocomplete="off"
                                                @if (isset($cliente)) value="{{ $cliente->correo }}" @endif>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 fw-semibold text-muted">Teléfono</label>
                                        <div class="col-lg-8 d-flex align-items-center">
                                            <input type="text" class="form-control" id="telefono" placeholder="Teléfono"
                                                autocomplete="off"
                                                @if (isset($cliente)) value="{{ $cliente->telefono }}" @endif>
                                        </div>
                                    </div>
                                    <div class="row mb-3">
                                        <label class="col-lg-2 fw-semibold text-muted">Dirección</label>
                                        <div class="col-lg-8">
                                            <input type="text" class="form-control" id="direccion"
                                                placeholder="Dirección" autocomplete="off"
                                                @if (isset($cliente)) value="{{ $cliente->direccion }}" @endif>
                                        </div>
                                    </div>
                                    @if (isset($cliente))
                                        <div
                                            class="notice d-flex bg-light-warning rounded border-warning border border-dashed p-6">
                                            <i class="ki-outline ki-information fs-2tx text-warning me-4"></i>
                                            <div class="d-flex flex-stack flex-grow-1">
                                                <div class="fw-semibold">
                                                    <h4 class="text-gray-900 fs-6 fw-bold">¡Atención!</h4>
                                                    <div class="fs-7 text-gray-700">
                                                        Usted está editando los datos de un cliente existente; si desea
                                                        crear uno nuevo utilice la opción
                                                        <a class="fw-bold" href="{{ route('create.clients') }}">Nuevo
                                                            Cliente</a>.
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                            </div>
                            <!-- Empresas Vinculadas -->
                            @if (isset($cliente))
                                <div class="tab-pane fade" id="empresas-vinculadas" role="tabpanel"
                                    aria-labelledby="empresas-vinculadas-tab">
                                    <div class="row">
                                        <h3>Empresas Vinculadas</h3>
                                        @foreach ($empresas as $empresa)
                                            <div class="col-lg-6">
                                                <ul class="list-group">
                                                    <li
                                                        class="list-group-item d-flex justify-content-between align-items-center">
                                                        {{ $empresa->nombre }}
                                                        <div class="form-check form-switch">
                                                            <input class="form-check-input toggle-switch" type="checkbox"
                                                                id="empresa_{{ $empresa->id }}"
                                                                data-id="{{ $empresa->id }}"
                                                                data-client-id="{{ $cliente->id }}"
                                                                @if (in_array($empresa->id, $vinculadas)) checked @endif>
                                                            <label class="form-check-label"
                                                                for="empresa_{{ $empresa->id }}"></label>
                                                        </div>
                                                    </li>
                                                </ul>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-sm bg-gradient-success align-self-center">
                            @if (!isset($cliente))
                                Guardar
                            @else
                                Actualizar
                            @endif
                        </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
    <script
        src="{{ asset('js/sgt/clientes/clientes.js') }}?v={{ filemtime(public_path('js/sgt/clientes/clientes.js')) }}">
    </script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const switches = document.querySelectorAll('.toggle-switch');

            switches.forEach(function(toggle) {
                toggle.addEventListener('change', function() {
                    const idEmpresa = this.getAttribute('data-id');
                    const idCliente = this.getAttribute('data-client-id');
                    const checked = this.checked;

                    // Validar que la empresa 1 nunca se pueda desactivar
                    if (idEmpresa === '1' && !checked) {
                        alert('La Empresa 1 no puede ser desactivada.');
                        this.checked = true; // Restablecer el estado a "activado"
                        return;
                    }

                    fetch('/clients/edit', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector(
                                    'meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({
                                toggle_empresa: idEmpresa,
                                id_client: idCliente,
                                checked: checked
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                console.log('Cambio guardado con éxito.');
                            } else {
                                console.error('Error al guardar el cambio.');
                            }
                        })
                        .catch(error => console.error('Error:', error));
                });
            });
        });
    </script>
@endpush
