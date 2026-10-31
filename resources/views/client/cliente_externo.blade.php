@extends('layouts.usuario_externo')

@section('WorkSpace')
    <div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
        <div class="card-header">
            <div class="card-title m-0">
                @if (!isset($subCliente))
                    <h3 class="fw-bold m-0">Crear Sub-Cliente</h3>
                @else
                    <h3 class="fw-bold m-0">Editar Sub-Cliente</h3>
                @endif
            </div>
        </div>
        <div class="card-body p-9">
            <form method="POST"
                @if (!isset($subCliente)) action="{{ route('store_subclientes.clientes') }}"
        @else
        action="{{ route('upadate.subcliente') }}" @endif
                id="sublienteCreate" enctype="multipart/form-data" role="form">
                @csrf
                <input type="hidden" value="{{ Auth::User()->id_cliente }}" name="id_cliente" id="id_cliente">
                @if (isset($subCliente))
                    <input type="hidden" value="{{ $subCliente->id }}" name="id_subcliente" id="id_subcliente">
                @endif
                <div class="row mb-3">
                    <label class="col-lg-3 fw-semibold text-muted">Nombre ó Razón Social</label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control" name="txtNombre" id="nombre"
                            @if (isset($subCliente)) value="{{ $subCliente->nombre }}" @endif>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-lg-3 fw-semibold text-muted"> RFC
                    </label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control" id="rfc"
                            @if (isset($subCliente)) value="{{ $subCliente->rfc }}" @endif>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-lg-3 fw-semibold text-muted">Régimen Fiscal</label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control" id="regimen_fiscal"
                            @if (isset($subCliente)) value="{{ $subCliente->regimen_fiscal }}" @endif>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-lg-3 fw-semibold text-muted">Nombre Comercial</label>
                    <div class="col-lg-8 fv-row">
                        <input type="text" class="form-control" id="nombre_empresa"
                            @if (isset($subCliente)) value="{{ $subCliente->nombre_empresa }}" @endif>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-lg-3 fw-semibold text-muted"> Correo Electrónico

                    </label>
                    <div class="col-lg-8 d-flex align-items-center">
                        <input type="text" class="form-control" id="correo"
                            @if (isset($subCliente)) value="{{ $subCliente->correo }}" @endif>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-lg-3 fw-semibold text-muted"> Teléfono
                    </label>
                    <div class="col-lg-8 d-flex align-items-center">
                        <input type="text" class="form-control" id="telefono"
                            @if (isset($subCliente)) value="{{ $subCliente->telefono }}" @endif>
                    </div>
                </div>
                <div class="row mb-3">
                    <label class="col-lg-3 fw-semibold text-muted">Dirección</label>
                    <div class="col-lg-8">
                        <input type="text" class="form-control" id="direccion"
                            @if (isset($subCliente)) value="{{ $subCliente->direccion }}" @endif>
                    </div>
                </div>
                @if (isset($subCliente))
                    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed  p-6">
                        <i class="ki-outline ki-information fs-2tx text-warning me-4"></i>
                        <div class="d-flex flex-stack flex-grow-1 ">
                            <div class=" fw-semibold">
                                <h4 class="text-gray-900 fs-6 fw-bold">¡Atención!</h4>
                                <div class="fs-7 text-gray-700 ">Usted está editando los datos de un subcliente existente;
                                    si desea crear uno nuevo utilice la opción <a class="fw-bold"
                                        href="{{ route('subcliente.index') }}">Crear SubCliente</a>. </div>
                            </div>
                        </div>
                    </div>
                @endif
        </div>
        <div class="card-footer text-end">
            <button type="submit" class="btn btn-sm btn-primary align-self-center">
                @if (!isset($subCliente))
                    Guardar
                @else
                    Actualizar
                @endif
            </button>
            </form>
        </div>
    </div>
@endsection

@push('javascript')
    <script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
    <script
        src="{{ asset('js/sgt/clientes/subclientes.js') }}?v={{ filemtime(public_path('js/sgt/clientes/subclientes.js')) }}">
    </script>
@endpush
