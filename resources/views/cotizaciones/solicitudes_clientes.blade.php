@extends('layouts.app')

@section('template_title')
    Solicitudes entrantes
@endsection

@section('content')
<div class="card">
   <div class="card-header">
     <h5 class="card-title">Solicitudes por asignar</h5>
     <div class="card-toolbar"></div>
   </div>
   <div class="card-body">
     <div class="row">
       <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 500px"></div>
     </div>
   </div>
   <div class="card-footer text-end">
     <div class="col "></div>
     <div class="col-md-3 offset-9">
       <div class="form-group">
         <label for="example-text-input" class="form-control-label">Empresa</label>
         <select name="cmbEmpresa" id="cmbEmpresa" class="form-control">
           <option value="">Seleccione empresa</option> @foreach ($empresas as $empresa) <option value="{{$empresa->id}}">{{$empresa->nombre}}</option> @endforeach
         </select>
       </div>
     </div>
     <div class="col-md-4 offset-8">
       <button class="btn btn-sm bg-gradient-success" onclick="asignarContenedores()">
         <i class="fas fa-check" aria-hidden="true"></i>&nbsp;&nbsp; Asignar empresa </button>
     </div>
   </div>
 </div>
 
@endsection

@push('custom-javascript')
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

<script src="{{ asset('js/sgt/cotizaciones/cotizaciones-para-asignar.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones-para-asignar.js')) }}"></script>
<script>
    $(document).ready(()=>{
        getContenedoresPorAsignar();
        
    });
</script>
@endpush