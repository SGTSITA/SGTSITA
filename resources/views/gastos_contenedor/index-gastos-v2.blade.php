@extends('layouts.app')

@section('template_title')
    Gastos Viajes
@endsection

@section('content')
<div class="card">
   <div class="card-header">
     <h5 class="card-title">Registro de gastos por viaje</h5>
     <div class="card-toolbar"></div>
   </div>
   <div class="card-body">
     <div class="row">
     <div id="pagosPendientes"></div>
     </div>
   </div>
   <div class="card-footer text-end">  
     <div class="col-md-4 offset-8">
       <button class="btn btn-sm bg-gradient-success" onclick="asignarContenedores()">
         <i class="fas fa-check" aria-hidden="true"></i>&nbsp;&nbsp; Guardar gastos </button>
     </div>
   </div>
 </div>
 
@endsection

@push('custom-javascript')
<link href="/assets/handsontable/handsontable.full.min.css" rel="stylesheet" media="screen">
<script src="/assets/handsontable/handsontable.full.min.js"></script>
<script src="/assets/handsontable/all.js"></script>
<!--script src="/js/sgt/cxp/cxp.js"></script-->
<script src="{{ asset('js/sgt/gastos/gastosViajes.js') }}?v={{ filemtime(public_path('js/sgt/gastos/gastosViajes.js')) }}"></script>

<script>
   $(document).ready(()=>{
    getViajes();
   });
</script>
@endpush