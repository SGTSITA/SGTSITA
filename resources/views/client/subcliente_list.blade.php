@extends('layouts.usuario_externo')

@section('WorkSpace')
<div class="row gx-5 gx-xl-10">
  <div class="col-sm-12 mb-5 mb-xl-10">
    <div class="card card-flush h-lg-100">
      <div class="card-header ">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-900">Sub Clientes</span>
          <span class="text-gray-500 mt-1 fw-semibold fs-6">Lista de <span class="text-gray-600 fw-bold">Sub Clientes</span> </span>
        </h3>
        <div class="card-toolbar">
        <a href="{{route('subcliente.index')}}" class="btn btn-sm btn-primary me-3">Agregar SubCliente</a>
        </div>
        
      </div>
      <div class="card-body">
         
            <div class="row">
                <div id="agGridSubClientes" class="col-12 ag-theme-quartz" style="height: 450px">
                   
                </div>
            </div>
      </div>
    </div>
  </div>
</div>
@endsection

@push('javascript')
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script src="{{ asset('js/sgt/clientes/subclientes_list.js') }}?v={{ filemtime(public_path('js/sgt/clientes/subclientes_list.js')) }}"></script>
<script src="{{ asset('js/sgt/clientes/subclientes.js') }}?v={{ filemtime(public_path('js/sgt/clientes/subclientes.js')) }}"></script>

<script>
    $(document).ready( e => getSubClientes());
</script>
@endpush