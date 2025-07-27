@extends('layouts.usuario_externo')

@section('WorkSpace')
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-md-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Selecciona una ubicación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="searchInput" class="form-control mb-2" placeholder="Buscar código postal, dirección o una url de https://maps.app.goo.gl">
        <div id="map" style="height: 500px;"></div>
      </div>
    </div>
  </div>
</div>

<div class="card mb-5 mb-xl-10">
    <!--begin::Card header-->
    <div class="card-header card-header-stretch pb-0">
        <!--begin::Title-->
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-900">Solicitar Transporte</span>
          <span class="text-gray-500 mt-1 fw-semibold fs-6">Solicitud de servicio de Transporte</span>
        </h3>
        <!--end::Title-->

        <!--begin::Toolbar-->
        <div class="card-toolbar m-0">
            <!--begin::Tab nav-->
            <ul class="nav nav-stretch nav-line-tabs border-transparent" role="tablist">
                <!--begin::Tab item-->
                <li class="nav-item" role="presentation">
                    <a id="kt_billing_creditcard_tab" class="nav-link fs-5 fw-bold me-5 active" data-bs-toggle="tab" role="tab" href="#kt_billing_creditcard" aria-selected="true">
                        Datos Generales
                    </a>
                </li>
                <!--end::Tab item-->
                <!--begin::Tab item-->
                <li class="nav-item" role="presentation">
                    <a id="kt_billing_paypal_tab" class="nav-link fs-5 fw-bold" data-bs-toggle="tab" role="tab" href="#kt_billing_paypal" aria-selected="false" tabindex="-1">
                        Ubicación GPS
                    </a>
                </li>
                <!--end::Tab item-->
                <!--begin::Tab item-->
                <li class="nav-item" role="presentation">
                    <a id="kt_bloque_tab" class="nav-link fs-5 fw-bold" data-bs-toggle="tab" role="tab" href="#kt_bloque" aria-selected="false" tabindex="-1">
                        Bloque
                    </a>
                </li>
                <!--end::Tab item-->
                 <!--begin::Tab item-->
                 <li class="nav-item" role="presentation">
                    <a id="kt_facturacion_tab" class="nav-link fs-5 fw-bold" data-bs-toggle="tab" role="tab" href="#kt_facturacion" aria-selected="false" tabindex="-1">
                        Facturación
                    </a>
                </li>
                <!--end::Tab item-->
                <!--begin::Tab item-->
                <li class="nav-item" role="presentation">
                    <a id="kt_documentos_tab" class="nav-link fs-5 fw-bold" data-bs-toggle="tab" role="tab" href="#kt_documentos" aria-selected="false" tabindex="-1">
                        Documentos
                    </a>
                </li>
                <!--end::Tab item-->
            </ul>
            <!--end::Tab nav-->
        </div>
        <!--end::Toolbar-->
    </div>
    <!--end::Card header-->
    <form method="POST" 
          @if($action == "editar")
            action="{{ route('update.single', $cotizacion->id) }}"
          @else
            action="{{ route('store.cotizaciones') }}" 
          @endif
            sgt-cotizacion-action="{{$action}}"
            id="cotizacionCreate" 
            enctype="multipart/form-data" 
            role="form"
          >
    <!--begin::Tab content-->
    <div id="kt_billing_payment_tab_content" class="card-body tab-content">
        <!--begin::Tab panel-->
        <div id="kt_billing_creditcard" class="tab-pane fade show active" role="tabpanel" "="" aria-labelledby="kt_billing_creditcard_tab">
            <!--begin::Title-->
            <h3 class="mb-5">Datos Generales</h3>
            <!--end::Title-->

            <!--begin::Row-->
            <div class="row gx-9 gy-6">
            @csrf
            <input type="hidden" value="{{Auth::User()->id_cliente}}" name="id_cliente" id="id_cliente"> 
            @include('cotizaciones.externos.datos_generales') 
            </div>
            <!--end::Row-->
        </div>
        <!--end::Tab panel-->

        <!--begin::Tab panel-->
        <div id="kt_billing_paypal" class="tab-pane fade" role="tabpanel" aria-labelledby="kt_billing_paypal_tab">
            <!--begin::Title-->
            <h3 class="mb-5">Ubicacion GPS</h3>
            <!--end::Title-->
            <div class="row gx-9 gy-6">

            @include('cotizaciones.externos.datos_ubicacion') 
            </div>
        </div>
        <!--end::Tab panel-->
        <!--begin::Tab panel-->
        <div id="kt_bloque" class="tab-pane fade" role="tabpanel" aria-labelledby="kt_bloque_tab">
            <!--begin::Title-->
            <h3 class="mb-5">Bloque</h3>
            <!--end::Title-->
            <div class="row gx-9 gy-6">

            @include('cotizaciones.externos.datos_bloque') 
            </div>
        </div>
        <!--end::Tab panel-->
         
        <!--begin::Tab panel-->
        <div id="kt_documentos" class="tab-pane fade" role="tabpanel" aria-labelledby="kt_documentos_tab">
            <!--begin::Title-->
            <h3 class="mb-5" id="labelDocsViaje">Documentos de viaje </h3>
            <!--end::Title-->
            <div class="row gx-9 gy-6">

            @include('cotizaciones.externos.datos_fileuploader') 
            </div>
        </div>
        <!--end::Tab panel-->
        <!--begin::Tab panel-->
        <div id="kt_facturacion" class="tab-pane fade" role="tabpanel" aria-labelledby="kt_facturacion_tab">
            <!--begin::Title-->
            <h3 class="mb-5">Datos para facturación</h3>
            <!--end::Title-->
            <div class="row gx-9 gy-6">

            @include('cotizaciones.externos.datos_facturacion') 
            </div>
        </div>
        <!--end::Tab panel-->
    </div>
    <!--end::Tab content-->
    <div class="separator separator-dashed mb-8"></div>
      @if($action == "editar")
      <button type="submit" class="btn btn-success">
        Actualizar viaje
      </button>
      @else
      <button type="submit" class="btn btn-primary">
        Solicitar servicio
      </button>
      @endif
      </form>
</div>


</div>

@endsection

@push('javascript')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script src="{{ asset('js/sgt/cotizaciones/cotizaciones.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones.js')) }}"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<link href="/assets/metronic/fileuploader/font/font-fileuploader.css" rel="stylesheet">
<link href="/assets/metronic/fileuploader/jquery.fileuploader.min.css" media="all" rel="stylesheet">
<link href="/assets/metronic/fileuploader/jquery.fileuploader-theme-dragdrop.css" media="all" rel="stylesheet">
<script src="/assets/metronic/fileuploader/jquery.fileuploader.min.js" type="text/javascript"></script>
<script src="{{ asset('js/sgt/cotizaciones/cotizacion-fileuploader-preload.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizacion-fileuploader-preload.js')) }}"></script>
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

    @if($action=="editar")
    localStorage.setItem('numContenedor','{{$cotizacion->DocCotizacion->num_contenedor}}'); 
    initFileUploader()
    
    setTimeout(()=>{
        document.getElementById('noticeFileUploader').classList.add('d-none')
        document.getElementById('fileUploaderContainer').classList.remove('d-none')
    },3000);
    @endif

    document.getElementById('num_contenedor').addEventListener('keydown', function(e) {
    if (e.key === '/') {
        e.preventDefault();
    }
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
