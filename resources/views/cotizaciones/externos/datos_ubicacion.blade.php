<div class="col-12">
  <div class="position-relative mb-6">
    <textarea name="direccion_mapa" id="direccion_mapa" data-bs-toggle="modal" data-bs-target="#mapModal" readonly class="form-control border-0 p-0 pe-10 resize-none min-h-25px" data-kt-autosize="true" rows="1" placeholder="Seleccione ubicación GSP..." data-kt-initialized="1" style="overflow: hidden; overflow-wrap: break-word; text-align: start; height: 25px;">@if($action == "editar") {{trim($cotizacion->direccion_mapa)}} @endif</textarea>
    <div class="position-absolute top-0 end-0 me-n5">
      <button type="button" class="btn btn-sm btn-light-primary btn-flex btn-center" data-bs-toggle="modal" data-bs-target="#mapModal">
        <i class="ki-duotone ki-geolocation fs-3">
          <span class="path1"></span>
          <span class="path2"></span>
        </i>
        <span class="indicator-label"> Ubicación GPS </span>
      </button>
    </div>
  </div>
  <h5 class="fw-bold mb-2 mt-3">Dirección entrega</h5>
  <textarea name="direccion_entrega" id="direccion_entrega" class="form-control" placeholder="Dirección entrega">@if($action == "editar") {{trim($cotizacion->direccion_entrega)}} @endif</textarea>
  <input @if($action=="editar" ) value="{{$cotizacion->latitud}}" @endif class="form-control" type="hidden" name="latitud" id="latitud" value="0">
  <input @if($action=="editar" ) value="{{$cotizacion->longitud}}" @endif class="form-control" type="hidden" name="longitud" id="longitud" value="0">
  <input @if($action=="editar" ) value="{{$cotizacion->direccion_mapa}}" @endif class="form-control" type="hidden" name="direccion_mapa" id="direccion_mapa" value="NINGUNA SELECCIONADA">
  <input @if($action=="editar" ) value="{{$cotizacion->fecha_seleccion_ubicacion}}" @endif class="form-control" type="hidden" name="fecha_seleccion" id="fecha_seleccion" value="">
</div>
<div class="col-12">
  <br>
  <h4 class="fw-bold mb-4">¿Su contenedor va a recinto?</h4>
  <!--begin::Nav group-->
  <div class="nav bg-light rounded-pill px-3 py-2 ms-9 mb-5 w-225px" data-kt-buttons="true">
    <!--begin::Nav link-->
    <div class="recinto nav-link btn btn-active btn-active-primary fw-bold btn-color-gray-600 @if($action == 'editar') @if(intval($cotizacion->uso_recinto) == 0) active @endif @else active @endif py-3 px-5 m-1 rounded-pill" data-kt-plan="recinto-no"> No </div>
    <!--end::Nav link-->
    <!--begin::Nav link-->
    <div class="recinto nav-link  btn btn-active btn-active-primary fw-bold btn-color-gray-600 @if($action == 'editar') @if(intval($cotizacion->uso_recinto) == 1) active @endif @endif py-3 px-5 m-1 rounded-pill" data-kt-plan="recinto-si"> Si va a recinto </div>
    <!--end::Nav link-->
  </div>
  <input type="text" name="text_recinto" id="text_recinto" class="d-none">
  <div class="input-group @if($action == 'editar') @if(intval($cotizacion->uso_recinto) == 0) d-none @endif @else d-none @endif" id="input-recinto">
    <span class="input-group-text">Dirección recinto</span>
    <textarea class="form-control" name="direccion_recinto" id="direccion_recinto" aria-label="Dirección recinto">
      @if($action == "editar")
      {{$cotizacion->direccion_recinto}}
      @endif
    </textarea>
  </div>
</div>          