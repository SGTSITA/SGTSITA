@extends('layouts.usuario_externo')

@section('WorkSpace')
<div class="card mb-5 mb-xl-10" id="kt_profile_details_view">
  <div class="card-header">
    <div class="card-title m-0">
      @if(!isset($subCliente))
      <h3 class="fw-bold m-0">Crear Usuario externo</h3>
      @else
      <h3 class="fw-bold m-0">Editar Usuario externo</h3>
      @endif
    </div>
  </div>
  <div class="card-body p-9">
  <form method="POST" 
        @if(!isset($subCliente))
        action="{{ route('usuario.store') }}" 
        @else
        action="{{ route('upadate.subcliente') }}" 
        @endif
        id="usuarioCreate" enctype="multipart/form-data" 
        role="form">
            @csrf
    
    @if(isset($subCliente))
    <input type="hidden" value="{{$subCliente->id}}" name="id_subcliente" id="id_subcliente"> 
    @endif
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted">Nombre</label>
      <div class="col-lg-8">
        <input type="text" class="form-control" autocomplete="off" name="name" id="name" @if(isset($subCliente)) value="{{$subCliente->nombre}}" @endif>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted"> Correo Electrónico 

      </label>
      <div class="col-lg-8 d-flex align-items-center">
        <input type="text" class="form-control" autocomplete="off" id="email" name="email" @if(isset($subCliente)) value="{{$subCliente->correo}}" @endif>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted"> Contraseña 
      </label>
      <div class="col-lg-8">
        <input type="password" class="form-control" autocomplete="off" id="password" name="password" @if(isset($subCliente)) value="{{$subCliente->rfc}}" @endif>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted">Confirmar Contraseña</label>
      <div class="col-lg-8">
        <input type="password" class="form-control" autocomplete="off" id="confirm-password" name="confirm-password" @if(isset($subCliente)) value="{{$subCliente->regimen_fiscal}}" @endif>
      </div>
    </div>
    <div class="row mb-3">
      <label class="col-lg-3 fw-semibold text-muted">Usuario pertenece a:</label>
      <div class="col-lg-8 fv-row">
      <select class="form-select subcliente d-inline-block" id="id_cliente" name="id_cliente">
      <option value="">Seleccionar Cliente</option>

        @foreach($clientes as $cl)
            <option value="{{$cl->id}}">{{$cl->nombre}}</option>
        @endforeach
        </select>
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
    <button type="submit" class="btn btn-sm btn-primary align-self-center">
    @if(!isset($subCliente))
      Crear usuario
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
<script src="{{ asset('js/sgt/usuarios/usuarios.js') }}?v={{ filemtime(public_path('js/sgt/usuarios/usuarios.js')) }}"></script>
@endpush