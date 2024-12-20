@extends('layouts.app')

@section('template_title')
    Solicitudes entrantes
@endsection

@section('content')
 <div class="card">
    <div class="card-header">
        <h5 class="card-title">Solicitudes por asignar</h5>
        <div class="card-toolbar">
      
      </div>
    </div>
    <div class="card-body">
        <div class="row">
            <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 500px">
                
            </div>
        </div>
    </div>
 </div>
 @include('cotizaciones.modal_asignar_empresa')
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