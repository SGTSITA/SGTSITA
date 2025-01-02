@extends('layouts.usuario_externo')

@section('WorkSpace')
<div class="row gx-5 gx-xl-10">
  <div class="col-sm-12 mb-5 mb-xl-10">
    <div class="card card-flush h-lg-100">
      <div class="card-header ">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-900">Mis Viajes</span>
          <span class="text-gray-500 mt-1 fw-semibold fs-6">Lista de viajes <span class="text-gray-600 fw-bold">Aprobados</span> </span>
        </h3>
        <div class="card-toolbar">
        <button name="btnDocs" id="btnDocs" disabled="true" class="btn btn-sm btn-primary me-3">Cargar Documentos </button>
        </div>
        
      </div>
      <div class="card-body">
         
            <div class="row">
                <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 500px">
                   
                </div>
            </div>
      </div>
    </div>
  </div>
</div>

@include('cotizaciones.externos.modal_fileuploader')
@endsection

@push('javascript')
<link href="{{asset('assets/metronic/fileuploader/font/font-fileuploader.css')}}" rel="stylesheet">
<link href="{{asset('assets/metronic/fileuploader/jquery.fileuploader.min.css')}}" media="all" rel="stylesheet">
<link href="{{asset('assets/metronic/fileuploader/jquery.fileuploader-theme-dragdrop.css')}}" media="all" rel="stylesheet">
<script src="{{asset('assets/metronic/fileuploader/jquery.fileuploader.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/metronic/fileuploader/cotizacion-cliente-externo.js')}}" type="text/javascript"></script>

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

<script src="{{ asset('js/sgt/cotizaciones/cotizacion-documentacion.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizacion-documentacion.js')) }}"></script>
<script>
    $(document).ready(()=>{
        getContenedoresPendientes('all');
        adjuntarDocumentos();
    });
</script>
@endpush