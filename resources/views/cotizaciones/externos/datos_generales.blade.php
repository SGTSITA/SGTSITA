<div class="timeline-item">
      <!--begin::Timeline line-->
      <div class="timeline-line"></div>
      <!--end::Timeline line-->

      <!--begin::Timeline icon-->
      <div class="timeline-icon">
          <i class="ki-duotone ki-message-text-2 fs-2 text-gray-500"><span class="path1"></span><span class="path2"></span><span class="path3"></span></i>    </div>
      <!--end::Timeline icon-->

      <!--begin::Timeline content-->
      <div class="timeline-content mb-10 mt-n1">
          <!--begin::Timeline heading-->
          <div class="pe-3 mb-5">
              <!--begin::Title-->
              <div class="fs-5 fw-semibold mb-2">Datos Generales:</div>
              <!--end::Title-->

              <!--begin::Description-->
              <div class="d-flex align-items-center mt-1 fs-6">
                  <!--begin::Info-->
                  <div class="text-muted me-2 fs-7">Información del contenedor</div>
                  <!--end::Info-->

              </div>
              <!--end::Description-->
          </div>
          <!--end::Timeline heading-->

          <!--begin::Timeline details-->
          <div class="overflow-auto pb-5">
              <!--begin::Record-->
              <div class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-750px px-7 py-3 mb-5">  
      
        
                  
<div class="flex-grow-1">
   <div class="fv-row row mb-3">
     <div class="col-12 min-w-450px">
      
       <div class="border border-gray-300 border-dashed rounded min-w-450px py-3 px-4 me-6 mb-3">
       <div class="d-flex align-items-center">
           <div class="fs-4 fw-bold" id="proveedorName">Sub Cliente:</div>
         </div>
        <select class="form-select subcliente d-inline-block" id="id_subcliente" name="id_subcliente">
            <option value="">Seleccionar subcliente</option>
        </select>

       </div>
     </div>
   </div>

   <div class="mb-2"></div>
   <div class="text-muted fs-7">Ingrese los datos del contenedor que se solicitan a continuación</div>
   <div class="mb-2"></div>
   <div class="fv-row row mb-3">
      <div class="col-6">
      <div class="input-group  mb-5">
            <span class="input-group-text" id="basic-addon1">
            <i class="ki-duotone ki-map fs-1 text-gray-650 active ">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            </span>
            <div class="form-floating">
            <input @if($action == "editar") value="{{$cotizacion->origen}}" @endif type="text" class="form-control" id="origen" autocomplete="off" placeholder=""/>
            <label for="origen" class="text-gray-700">Origen</label>
            </div>
        </div>
      </div>
      <div class="col-6">
      <div class="input-group  mb-5">
            <span class="input-group-text" id="basic-addon1">
            <i class="ki-duotone ki-route fs-1">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
            </i>
            </span>
            <div class="form-floating">
            <input @if($action == "editar") value="{{$cotizacion->destino}}" @endif type="text" class="form-control" id="destino" autocomplete="off" placeholder=""/>
            <label for="destino" class="text-gray-700">Destino</label>
            </div>
        </div>
      </div>
     
   </div>
   <div class="fv-row row mb-3">
      <div class="col-6">
        <div class="input-group  mb-5">
            <span class="input-group-text" id="basic-addon1">
            <i class="ki-duotone ki-logistic fs-1">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
                <span class="path5"></span>
                <span class="path6"></span>
                <span class="path7"></span>
            </i>
            </span>
            <div class="form-floating">
            <input 
              type="text" 
              class="form-control" 
              autocomplete="off" 
              id="num_contenedor" 
              placeholder="" 
              @if($action == "editar") value="{{$cotizacion->DocCotizacion->num_contenedor}}" @endif
              oninput="changeTag('tagContenedor',this.value)"
            />
            <label for="num_contenedor" class="text-gray-700">Número de Contenedor</label>
            </div>
        </div>
      </div>
      <div class="col-6">
      <div class="input-group  mb-5">
            <span class="input-group-text" id="basic-addon1">
            <i class="ki-duotone ki-delivery-3 fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
            </i>
            </span>
            <div class="form-floating">
            <input @if($action == "editar") value="{{$cotizacion->tamano}}" @endif type="text" class="form-control" autocomplete="off" id="tamano" placeholder="" oninput="allowOnlyDecimals(event)"/>
            <label for="tamano" class="text-gray-700">Tamaño de Contenedor</label>
            </div>
        </div>
      </div>
      <div class="col-6 d-none">
      <div class="input-group mb-5">
            <span class="input-group-text" id="basic-addon1">
            <i class="ki-duotone ki-delivery-2 fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
                <span class="path5"></span>
                <span class="path6"></span>
                <span class="path7"></span>
                <span class="path8"></span>
                <span class="path9"></span>
            </i>
            </span>
            <div class="form-floating">
              <input type="text" class="form-control" autocomplete="off" value="22" id="peso_reglamentario" placeholder="" oninput="allowOnlyDecimals(event)"/>
              <label for="peso_reglamentario" class="text-gray-700">Peso de Reglamentario</label>
              <input type="text" class="form-control" autocomplete="off" id="sobrepeso" placeholder="" value ="0" oninput="allowOnlyDecimals(event)"/>
              <input type="text" class="form-control" autocomplete="off" id="precio_sobre_peso" placeholder="" value ="0" oninput="allowOnlyDecimals(event)"/>
              <input type="text" class="form-control" autocomplete="off" id="precio_tonelada" placeholder="" value ="0" oninput="allowOnlyDecimals(event)"/>
            </div>
        </div>
      </div>
      <div class="col-6">
        <div class="input-group mb-5">
              <span class="input-group-text" id="basic-addon1">
              <i class="ki-duotone ki-delivery-2 fs-2">
                  <span class="path1"></span>
                  <span class="path2"></span>
                  <span class="path3"></span>
                  <span class="path4"></span>
                  <span class="path5"></span>
                  <span class="path6"></span>
                  <span class="path7"></span>
                  <span class="path8"></span>
                  <span class="path9"></span>
              </i>
              </span>
              <div class="form-floating">
                <input type="text" @if($action == "editar") value="{{$cotizacion->peso_contenedor}}" @endif class="form-control" autocomplete="off" id="peso_contenedor" placeholder="" oninput="allowOnlyDecimals(event)"/>
                <label for="peso_contenedor" class="text-gray-700">Peso de Contenedor</label>
              </div>
        </div>
      </div>
      <div class="col-6"></div>
      <div class="col-6">
        <div class="input-group mb-5">
              <span class="input-group-text" id="basic-addon1">
              <i class="ki-duotone ki-calendar-8 fs-1">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
                <span class="path5"></span>
                <span class="path6"></span>
              </i>
              </span>
              <div class="form-floating">
                <input @if($action == "editar") value="{{$cotizacion->fecha_modulacion}}" @endif type="text" class="form-control fechas" autocomplete="off" id="fecha_modulacion" name="fecha_modulacion" placeholder="" oninput="allowOnlyDecimals(event)"/>
                <label for="fecha_modulacion" class="text-gray-700">Fecha Modulación</label>
              </div>
        </div>
      </div>
      <div class="col-6">
        <div class="input-group mb-5">
              <span class="input-group-text" id="basic-addon1">
              <i class="ki-duotone ki-calendar-8 fs-1">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
                <span class="path5"></span>
                <span class="path6"></span>
              </i>
              </span>
              <div class="form-floating">
                <input @if($action == "editar") value="{{$cotizacion->fecha_entrega}}" @endif type="text" class="form-control fechas" autocomplete="off" id="fecha_entrega" name="fecha_entrega" placeholder="" oninput="allowOnlyDecimals(event)"/>
                <label for="fecha_entrega" class="text-gray-700">Fecha Entrega</label>
              </div>
        </div>
      </div>
     {{--  <div class="col-12">
      <h4 class="fw-bold mb-4">Dirección entrega</h4>
        <div class="input-group" >
            <span class="input-group-text">Dirección Entrega</span>
            <textarea class="form-control" value ="d" name="direccion_entrega" id="direccion_entrega" aria-label="Dirección Entrega">
            @if($action == "editar") {{$cotizacion->direccion_entrega}} @endif ssa
            </textarea>
        </div>
      </div> --}}
      <div class="col-12">
                                                <h5 class="fw-bold mb-2 mt-3">Dirección entrega</h5>

                                                <!-- <label class="form-label" for="direccion_entrega">Dirección Entrega</label> -->
                                                <textarea class="form-control" placeholder="Dirección entrega" name="direccion_entrega" id="direccion_entrega" aria-label="Dirección Entrega">
                                                @if($action == "editar") {{trim($cotizacion->direccion_entrega)}} @endif
                                                </textarea>

                                                <div class="mt-2">
                                                    <button type="button"  class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#mapModal">📍 Seleccionar en mapa</button>
                                                </div>

                                                <input @if($action == "editar") value="{{$cotizacion->latitud}}" @endif class="form-control" type="hidden" name="latitud" id="latitud" value="0">
                                                <input @if($action == "editar") value="{{$cotizacion->longitud}}" @endif class="form-control" type="hidden" name="longitud" id="longitud" value="0">
                                                <input @if($action == "editar") value="{{$cotizacion->direccion_mapa}}" @endif class="form-control" type="hidden" name="direccion_mapa" id="direccion_mapa" value="NINGUNA SELECCIONADA">
                                                <input @if($action == "editar") value="{{$cotizacion->fecha_seleccion_ubicacion}}" @endif class="form-control" type="hidden" name="fecha_seleccion" id="fecha_seleccion" value="">
                                            </div>
      <div class="col-12">
      <br>
      <h4 class="fw-bold mb-4">¿Su contenedor va a recinto?</h4>
        <!--begin::Nav group-->                            
        <div class="nav bg-light rounded-pill px-3 py-2 ms-9 mb-5 w-225px" data-kt-buttons="true">
            <!--begin::Nav link-->
            <div class="recinto nav-link btn btn-active btn-active-primary fw-bold btn-color-gray-600 @if($action == 'editar') @if(intval($cotizacion->uso_recinto) == 0) active @endif @else active @endif py-3 px-5 m-1 rounded-pill" data-kt-plan="recinto-no">
                No
            </div>
            <!--end::Nav link-->

            <!--begin::Nav link-->
            <div class="recinto nav-link  btn btn-active btn-active-primary fw-bold btn-color-gray-600 @if($action == 'editar') @if(intval($cotizacion->uso_recinto) == 1) active @endif @endif py-3 px-5 m-1 rounded-pill" data-kt-plan="recinto-si">
                Si va a recinto
            </div>
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
   </div>
 </div>

 </div>
              <!--end::Record-->

</div>
          <!--end::Timeline details-->
      </div>
      <!--end::Timeline content-->    
      </div>
