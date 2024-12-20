@extends('layouts.usuario_externo')

@section('WorkSpace')
<div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
  <div class="card-header">
    <div class="card-title m-0">
      <h3 class="fw-bold m-0">Crear Sub-Cliente</h3>
    </div>
  </div>
  <div class="card-body p-9">
  <form method="POST" action="{{ route('store_subclientes.clients') }}" id="sublienteCreate" enctype="multipart/form-data" role="form">
  
            @csrf
            <input type="hidden" value="{{Auth::User()->id_cliente}}" name="id_cliente" id="id_cliente"> 
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted">Nombre ó Razón Social</label>
      <div class="col-lg-8">
        <input type="text" class="form-control" name="txtNombre" id="nombre">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted"> RFC <span class="ms-1" data-bs-toggle="tooltip" aria-label="Country of origination" data-bs-original-title="Country of origination" data-kt-initialized="1">
          <i class="ki-outline ki-information fs-7"></i>
        </span>
      </label>
      <div class="col-lg-8">
        <input type="text" class="form-control" id="rfc">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted">Régimen Fiscal</label>
      <div class="col-lg-8">
        <input type="text" class="form-control" id="regimen_fiscal">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted">Nombre Comercial</label>
      <div class="col-lg-8 fv-row">
        <input type="text" class="form-control" id="nombre_empresa">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted"> Correo Electrónico 
        <span class="ms-1" data-bs-toggle="tooltip" aria-label="Phone number must be active" data-bs-original-title="Phone number must be active" data-kt-initialized="1">
          <i class="ki-outline ki-information fs-7"></i>
        </span>
      </label>
      <div class="col-lg-8 d-flex align-items-center">
        <input type="text" class="form-control" id="correo">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted"> Teléfono 
        <span class="ms-1" data-bs-toggle="tooltip" aria-label="Phone number must be active" data-bs-original-title="Phone number must be active" data-kt-initialized="1">
          <i class="ki-outline ki-information fs-7"></i>
        </span>
      </label>
      <div class="col-lg-8 d-flex align-items-center">
        <input type="text" class="form-control" id="telefono">
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted">Dirección</label>
      <div class="col-lg-8">
        <input type="text" class="form-control" id="direccion">
      </div>
    </div>
    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed  p-6 d-none">
      <i class="ki-outline ki-information fs-2tx text-warning me-4"></i>
      <div class="d-flex flex-stack flex-grow-1 ">
        <div class=" fw-semibold">
          <h4 class="text-gray-900 fw-bold">We need your attention!</h4>
          <div class="fs-6 text-gray-700 ">Your payment was declined. To start using tools, please <a class="fw-bold" href="/metronic8/demo55/account/billing.html">Add Payment Method</a>. </div>
        </div>
      </div>
    </div>
  </div>
  <div class="card-footer text-end">
    <button type="submit" class="btn btn-sm btn-primary align-self-center">Guardar</button>
  </form>
  </div>
</div>
@endsection

@push('javascript')
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/clientes/subclientes.js') }}?v={{ filemtime(public_path('js/sgt/clientes/subclientes.js')) }}"></script>
@endpush