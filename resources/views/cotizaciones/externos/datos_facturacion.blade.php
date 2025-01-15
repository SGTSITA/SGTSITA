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
              <div class="fs-5 fw-semibold mb-2">Información para Facturación:</div>
              <!--end::Title-->

              <!--begin::Description-->
              <div class="d-flex align-items-center mt-1 fs-6">
                  <!--begin::Info-->
                  <div class="text-muted me-2 fs-7"></div>
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


   <div class="mb-2"></div>
   <div class="text-muted fs-7">Agregue la información con la que se elaborará su factura</div>
   <div class="mb-2"></div>
   <div class="fv-row row mb-3">
      <div class="col-6">
      <div class="d-flex align-items-center">
           <div class="fs-4 fw-bold" id="id_uso_cfdi">Uso CFDI:</div>
         </div>
        <select class="form-select id_uso_cfdi d-inline-block" id="id_uso_cfdi" name="id_uso_cfdi">
            <option value="">Seleccionar Uso del CFDI</option>
            @foreach($usoCfdi as $u)
            <option value="{{$u->id}}">{{$u->sat_code}} - {{$u->uso_cfdi}}</option>
            @endforeach
        </select>
      </div>
      <div class="col-6">
        <div class="d-flex align-items-center">
           <div class="fs-4 fw-bold" id="proveedorName">Forma de pago:</div>
         </div>
        <select class="form-select subcliente d-inline-block" id="id_forma_pago" name="id_forma_pago">
            <option value="">Seleccionar forma de pago</option>
            @foreach($formasPago as $f)
            <option value="{{$f->id}}">{{$f->sat_code}} - {{$f->forma_pago}}</option>
            @endforeach
        </select>
      </div>
     
   </div>
   <div class="fv-row row mb-3">
      <div class="col-6">
        <div class="d-flex align-items-center">
           <div class="fs-4 fw-bold" id="proveedorName">Metodo de pago:</div>
         </div>
        <select class="form-select subcliente d-inline-block" id="id_metodo_pago" name="id_metodo_pago">
            <option value="">Seleccionar metodo de pago</option>
            @foreach($metodosPago as $m)
            <option value="{{$m->id}}">{{$m->sat_code}} - {{$m->metodo_pago}}</option>
            @endforeach
        </select>
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