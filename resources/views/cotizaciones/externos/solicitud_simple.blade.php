@extends('layouts.usuario_externo')

@section('WorkSpace')
<div class="row gx-5 gx-xl-10">
  <div class="col-sm-12 mb-5 mb-xl-10">
    <div class="card card-flush h-lg-100">
      <div class="card-header ">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-900">Servicio de transporte</span>
          <span class="text-gray-500 mt-1 fw-semibold fs-6">Solicitud de servicio de gestion de transporte</span>
        </h3>
        <div class="card-toolbar">
          
        </div>
      </div>
      <div class="card-body">
        <div class="timeline timeline-border-dashed">
          <form method="POST" action="{{ route('store.cotizaciones') }}" id="cotizacionCreate" enctype="multipart/form-data" role="form">
            @csrf
            <input type="hidden" value="{{Auth::User()->id_cliente}}" name="id_cliente" id="id_cliente"> 
            @include('cotizaciones.externos.datos_generales') 
            @include('cotizaciones.externos.datos_facturacion')
            @include('cotizaciones.externos.datos_bloque')
        </div>
      </div>
      <div class="separator separator-dashed mb-8"></div>
      <button type="submit" class="btn btn-primary">Solicitar servicio</button>
      </form>
    </div>
  </div>
</div>
</div>
@endsection

@push('javascript')
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/cotizaciones/cotizaciones.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones.js')) }}"></script>
<script src="{{ asset('assets/metronic/fileuploader/cotizacion-cliente-externo.js')}}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones.js')) }}" type="text/javascript"></script>

<script>
  $(document).ready(() =>{
     getClientes({{Auth::User()->id_cliente}})

     var genericUUID = localStorage.getItem('uuid');
     if(genericUUID == null){
      genericUUID = generateUUID();
      localStorage.setItem('uuid',genericUUID);
     }

     let condicionRecinto = document.querySelectorAll('.recinto');
     let inputRecinto = document.querySelector('#input-recinto');
     let textRecinto = document.querySelector('#text_recinto');

     condicionRecinto.forEach(function(elemento) {
      elemento.addEventListener('click', function() {
        inputRecinto.classList.toggle('d-none',elemento.attributes['data-kt-plan'].value != 'recinto-si') 
        textRecinto.value = (elemento.attributes['data-kt-plan'].value != 'recinto-si') ? '' : 'recinto-si';
      });
    });

    $(".fechas").daterangepicker({
      singleDatePicker: true,
      locale: {
            format: 'YYYY-MM-DD', // Formato día/mes/año
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "Desde",
            toLabel: "Hasta",
            customRangeLabel: "Rango personalizado",
            weekLabel: "S",
            daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
            monthNames: [
                "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", 
                "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
            ],
            firstDay: 1 
      }
    });
  })


</script>
@endpush
