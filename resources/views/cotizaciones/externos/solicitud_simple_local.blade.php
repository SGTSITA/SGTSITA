@extends('layouts.usuario_externo')

@section('WorkSpace')
<style>
.switch-lg {
  width: 3.2em !important;
  height: 1.8em !important;
  cursor: pointer;
}
.switch-lg:checked {
  background-color: #16a34a !important; /* verde tailwind */
}
.switch-lg:focus {
  box-shadow: 0 0 0 0.25rem rgba(34,197,94,0.25);
}

.total-general-floating {
    position: absolute;
    right: 25px;
    bottom: 40px; /* 👈 antes era top, ahora controlamos posición vertical desde abajo */
    z-index: 10;
}

.total-general-content {
    background: #fff;
    border: 2px solid #28a745;
    border-radius: 12px;
    padding: 10px 16px;
    width: 220px;
    text-align: center;
    box-shadow: 0 3px 8px rgba(0, 0, 0, 0.05);
    transition: all 0.3s ease;
}

.total-general-content:hover {
    transform: scale(1.03);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
}

.total-general-content .label {
    font-size: 0.9rem;
    color: #28a745;
    font-weight: 600;
}


.input-error {
    border: 2px solid #dc3545 !important;
    background: #fff6f6 !important;
}


.color-select {
    transition: background-color 0.3s ease;
    color: #000;
}

.color-select.verde { background-color: #6EE74F !important; }
.color-select.amarillo { background-color: #FFE96B !important; }
.color-select.rojo { background-color: #FF6B6B !important; }
.color-select.ovt { background-color: #a883ff !important; }

</style>
<div class="modal fade" id="mapModal" tabindex="-1" aria-labelledby="mapModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered modal-fullscreen-md-down">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Selecciona una ubicación</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <input type="text" id="searchInput" class="form-control mb-2"
               placeholder="Buscar código postal, dirección o una URL de https://maps.app.goo.gl">
        <div id="map" style="height: 500px;"></div>
      </div>
    </div>
  </div>
</div>

<!--Indica si se ha modificado alguna información en el formulario-->
<input type="hidden" id="modifico_informacion" name="modifico_informacion" value="0">

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
        <li class="nav-item" role="presentation">
          <a id="kt_billing_creditcard_tab" class="nav-link fs-5 fw-bold me-5 active"
             data-bs-toggle="tab" href="#kt_billing_creditcard" role="tab" aria-selected="true">
            Datos Generales
          </a>
        </li>

        {{-- <li class="nav-item" role="presentation">
          <a id="kt_billing_paypal_tab" class="nav-link fs-5 fw-bold"
             data-bs-toggle="tab" href="#kt_billing_paypal" role="tab" aria-selected="false">
            Ubicación GPS
          </a>
        </li> --}}

        <li class="nav-item" role="presentation">
          <a id="kt_bloque_tab" class="nav-link fs-5 fw-bold"
             data-bs-toggle="tab" href="#kt_bloque" role="tab" aria-selected="false">
            Bloque
          </a>
        </li>

        <li class="nav-item" role="presentation">
          <a id="kt_documentos_tab" class="nav-link fs-5 fw-bold"
             data-bs-toggle="tab" href="#kt_documentos" role="tab" aria-selected="false">
            Documentos
          </a>
        </li>
      </ul>
      <!--end::Tab nav-->
    </div>
    <!--end::Toolbar-->
  </div>
  <!--end::Card header-->

  @if ($action == 'editar' &&  ($cotizacion->tipo_viaje ?? '') == 'Full')
  <div class="alert alert-info d-flex align-items-center justify-content-between">
    <div>
      <strong>Este es un viaje tipo Full</strong><br>
      Puedes seleccionar qué contenedor deseas modificar.
    </div>
    <div class="d-flex align-items-center">
      <label class="me-2 fw-semibold">Contenedor:</label>
      <select id="selectContenedorFull" class="form-select form-select-sm w-auto">
        @php
          $referencia = $cotizacion->referencia_full;
          $relacionados = \App\Models\Cotizaciones::where('referencia_full', $referencia)
              ->with('DocCotizacion')
              ->get();
        @endphp
        @foreach ($relacionados as $rel)
        <option value="{{ $rel->DocCotizacion->num_contenedor }}"
          {{ $rel->DocCotizacion->num_contenedor == $cotizacion->DocCotizacion->num_contenedor ? 'selected' : '' }}>
          {{ $rel->DocCotizacion->num_contenedor }}
        </option>
        @endforeach
      </select>
    </div>
  </div>
  @endif

  <form method="POST"
    @if ($action == 'editar') action="{{ route('update.singlelocal', $cotizacion->id) }}"
    @else action="{{ route('store.cotizacioneslocal') }}" @endif
    id="cotizacionCreate" enctype="multipart/form-data" role="form">

    @csrf
    <input type="hidden" value="{{ Auth::User()->id_cliente }}" name="id_cliente" id="id_cliente">

    <div id="kt_billing_payment_tab_content" class="card-body tab-content">

      <div id="kt_billing_creditcard" class="tab-pane fade show active" role="tabpanel"
           aria-labelledby="kt_billing_creditcard_tab">
        <h3 class="mb-5">Datos Generales</h3>
        <div class="row gx-9 gy-6">
          @include('cotizaciones.externos.datos_generaleslocal')
        </div>
      </div>

      {{-- <div id="kt_billing_paypal" class="tab-pane fade" role="tabpanel" aria-labelledby="kt_billing_paypal_tab">
        <h3 class="mb-5">Ubicación GPS</h3>
        <div class="row gx-9 gy-6">
          @include('cotizaciones.externos.datos_ubicacionlocal')
        </div>
      </div> --}}

      <div id="kt_bloque" class="tab-pane fade" role="tabpanel" aria-labelledby="kt_bloque_tab">
        <h3 class="mb-5">Bloque</h3>
        <div class="row gx-9 gy-6">
          @include('cotizaciones.externos.datos_bloquelocal')
        </div>
      </div>

      <div id="kt_documentos" class="tab-pane fade" role="tabpanel" aria-labelledby="kt_documentos_tab">
        <h3 class="mb-5" id="labelDocsViaje">Documentos requeridos</h3>
        <div class="row gx-9 gy-6">
          @include('cotizaciones.externos.datos_fileuploaderlocal')
        </div>
      </div>

    </div>

    <div class="separator separator-dashed mb-8"></div>

    @if ($action == 'editar')
      <button type="button"  id="solicitarservicio" class="btn btn-success">Actualizar local</button>
    @else
      <button type="button" id="solicitarservicio" class="btn btn-primary">Solicitar local</button>
    @endif
  </form>
</div>

@include('cotizaciones.externos.modal_whatsapp')
@endsection

@push('javascript')
 <script>
let COTIZACION_URL = "{{ route('store.cotizacioneslocal') }}";

    @if ($action == 'editar')
       localStorage.setItem('numContenedor', '{{ $cotizacion->DocCotizacion->num_contenedor }}');
        localStorage.setItem('cotizacionId', '{{ $cotizacion->id }}');

    @else
           if(localStorage.getItem('cotizacionId')) {
            localStorage.removeItem('cotizacionId');

        }
    @endif
</script>

</script>
<script src="{{ asset('js/sgt/cotizaciones/cotizaciones-local.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones-local.js')) }}"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>

<link href="/assets/metronic/fileuploader/font/font-fileuploader.css" rel="stylesheet">
<link href="/assets/metronic/fileuploader/jquery.fileuploader.min.css" rel="stylesheet" media="all">
<link href="/assets/metronic/fileuploader/jquery.fileuploader-theme-dragdrop.css" rel="stylesheet" media="all">

<script src="/assets/metronic/fileuploader/jquery.fileuploader.min.js" type="text/javascript"></script>
<script src="{{ asset('js/sgt/cotizaciones/cotizaciones-local-fileuploader-preload.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones-local-fileuploader-preload.js')) }}"></script>
<script src="{{ asset('js/sgt/common/tagify.js') }}?v={{ filemtime(public_path('js/sgt/common/tagify.js')) }}"></script>
<script src="{{ asset('assets/metronic/fileuploader/cotizacion-cliente-externo-local.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones-local.js')) }}"></script>
<script src="{{ asset('js/sgt/cotizaciones/externos.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/externos.js')) }}"></script>

<script>
$(document).ready(() => {
  let subclienteid = {{ $cotizacion?->id_subcliente ?? 'null' }};
  getClientes({{ Auth::User()->id_cliente }}, subclienteid);

  var genericUUID = localStorage.getItem('uuid');
  if (genericUUID == null) {
    genericUUID = generateUUID();
    localStorage.setItem('uuid', genericUUID);
  }







  document.getElementById('num_contenedor').addEventListener('keydown', function(e) {
    if (e.key === '/') e.preventDefault();
  });

  $(".fechas").daterangepicker({
    singleDatePicker: true,
    locale: {
      format: 'YYYY-MM-DD',
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

 const selectEstado = document.getElementById('estado_contenedor');

    function aplicarColor() {
        selectEstado.classList.remove('verde', 'amarillo', 'rojo', 'ovt');

        switch (selectEstado.value) {
            case "VERDE": selectEstado.classList.add('verde'); break;
            case "AMARILLO": selectEstado.classList.add('amarillo'); break;
            case "ROJO": selectEstado.classList.add('rojo'); break;
            case "OVT": selectEstado.classList.add('ovt'); break;
        }
    }

    // Cambia color al seleccionar
    selectEstado.addEventListener('change', aplicarColor);

    // 👌 Aplica color al cargar la vista (modo editar)
    aplicarColor();


});



// Detectar cambios en formularios
$(document).ready(function () {
  $('form').on('change input', 'input, select, textarea', function() {
    console.log('Campo modificado:', $(this).attr('name'));
    $('#modifico_informacion').val('1');
  });
});
</script>
@endpush
