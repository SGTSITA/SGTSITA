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
      @if(!isset($subCliente))
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
  <form method="POST" 
        @if(!isset($subCliente))
        action="{{ route('store.clients') }}" 
        @else
        action="{{ route('upadate.subcliente') }}" 
        @endif
        id="clienteCreate" enctype="multipart/form-data" 
        role="form">
            @csrf
    <input type="hidden" value="{{Auth::User()->id_cliente}}" name="id_cliente" id="id_cliente"> 
    @if(isset($subCliente))
    <input type="hidden" value="{{$subCliente->id}}" name="id_subcliente" id="id_subcliente"> 
    @endif
    <div class="row mb-3">
      <label class="col-lg-2 fw-semibold text-muted">Nombre ó Razón Social</label>
      <div class="col-lg-8">
        <input type="text" class="form-control" name="txtNombre" id="nombre" placeholder="Nombre completo" autocomplete="off" @if(isset($subCliente)) value="{{$subCliente->nombre}}" @endif>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-2 fw-semibold text-muted"> RFC 
      </label>
      <div class="col-lg-8">
        <input type="text" class="form-control" id="rfc" placeholder="Proporcione RFC" autocomplete="off"  @if(isset($subCliente)) value="{{$subCliente->rfc}}" @endif>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-2 fw-semibold text-muted">Régimen Fiscal</label>
      <div class="col-lg-8">
        <input type="text" class="form-control" id="regimen_fiscal" placeholder="Régimen Fiscal" autocomplete="off"  @if(isset($subCliente)) value="{{$subCliente->regimen_fiscal}}" @endif>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-2 fw-semibold text-muted">Nombre Comercial</label>
      <div class="col-lg-8 fv-row">
        <input type="text" class="form-control" id="nombre_empresa" placeholder="Nombre con el que se conoce a la empresa" autocomplete="off"   @if(isset($subCliente)) value="{{$subCliente->nombre_empresa}}" @endif>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-2 fw-semibold text-muted"> Correo Electrónico 

      </label>
      <div class="col-lg-8">
        <span class="badge bg-gradient-info badge-sm mb-2">Cuenta de acceso al sistema</span>
        <input type="text" class="form-control mb-2" id="correo" placeholder="Correo Electrónico para acceder al sistema" autocomplete="off"   @if(isset($subCliente)) value="{{$subCliente->correo}}" @endif>
        
      </div>
      
    </div>
    <div class="row mb-3">
      <label class="col-lg-2 fw-semibold text-muted"> Teléfono 
      </label>
      <div class="col-lg-8 d-flex align-items-center">
        <input type="text" class="form-control" id="telefono" placeholder="Teléfono" autocomplete="off"   @if(isset($subCliente)) value="{{$subCliente->telefono}}" @endif>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-2 fw-semibold text-muted">Dirección</label>
      <div class="col-lg-8">
        <input type="text" class="form-control" id="direccion" placeholder="Dirección" autocomplete="off"   @if(isset($subCliente)) value="{{$subCliente->direccion}}" @endif>
      </div>
    </div>
    @if(isset($subCliente))
    <div class="notice d-flex bg-light-warning rounded border-warning border border-dashed  p-6">
      <i class="ki-outline ki-information fs-2tx text-warning me-4"></i>
      <div class="d-flex flex-stack flex-grow-1 ">
        <div class=" fw-semibold">
          <h4 class="text-gray-900 fs-6 fw-bold">¡Atención!</h4>
          <div class="fs-7 text-gray-700 ">Usted está editando los datos de un subcliente existente; si desea crear uno nuevo utilice la opción <a class="fw-bold" href="{{ route('subcliente.index') }}">Crear SubCliente</a>. </div>
        </div>
      </div>
    </div>
    @endif
  </div>
  <div class="card-footer text-end">
    <button type="submit" class="btn btn-sm bg-gradient-success align-self-center">
    @if(!isset($subCliente))
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
<script src="{{ asset('js/sgt/clientes/clientes.js') }}?v={{ filemtime(public_path('js/sgt/clientes/clientes.js')) }}"></script>
@endpush