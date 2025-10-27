<div class="timeline-item">


  <!--begin::Timeline content-->
  <div class="timeline-content mb-10 mt-n1">

    <!--begin::Timeline details-->
    <div class="overflow-auto pb-5">
      <!--begin::Record-->
      <div class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-750px px-7 py-3 mb-5">
        <div class="flex-grow-1">
          <div class="mb-2"></div>
          <div class="text-muted fs-7">Agregue la información para elaborar Carta Porte</div>
          <div class="mb-2"></div>
          <div class="fv-row row mb-3">
            <div class="col-6 d-none">
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
                      <input @if ($action == 'editar') value="." @endif type="text"
                          class="form-control" id="destino" autocomplete="off" placeholder="" />
                      <label for="destino" class="text-gray-700">Razón Social</label>
                  </div>
              </div>
            </div>
            <div class="col-3 d-none">
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
                      <input @if ($action == 'editar') value="." @endif type="text"
                          class="form-control" id="destino" autocomplete="off" placeholder="" />
                      <label for="destino" class="text-gray-700">RFC</label>
                  </div>
              </div>
            </div>
            
          </div>
          <div class="fv-row row mb-3">
          <div class="col-12 d-none">
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
                      <input @if ($action == 'editar') value="." @endif type="text"
                          class="form-control" id="destino" autocomplete="off" placeholder="" />
                      <label for="destino" class="text-gray-700">Domicilio Fiscal</label>
                  </div>
              </div>
            </div>
            
            <div class="col-3">
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
                      <input @if ($action == 'editar') value="" @endif type="text"
                          class="form-control" id="cp_fraccion" autocomplete="off" placeholder="" />
                      <label for="cp_fraccion" class="text-gray-700">Fracción</label>
                  </div>
              </div>
            </div>

            <div class="col-3">
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
                      <input @if ($action == 'editar') value="" @endif type="text"
                          class="form-control" id="cp_pedimento" autocomplete="off" placeholder="" />
                      <label for="cp_pedimento" class="text-gray-700">Pedimento</label>
                  </div>
              </div>
            </div>

                                    <label for="cp_fraccion" class="text-gray-700">Fracción</label>
                                </div>
                            </div>
                        </div>
       
                        <div class="col-3">
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
                                    <input
                                        @if ($action == 'editar') value="{{ $cotizacion->cp_pedimento ?? '' }}" @endif
                                        type="text" class="form-control" id="cp_pedimento" name="cp_pedimento" autocomplete="off"
                                        placeholder="" />
                                    <label for="cp_pedimento" class="text-gray-700">Pedimento</label>
                                </div>
                            </div>
                        </div>
                        

            <div class="col-3">
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
                      <input @if ($action == 'editar') value="" @endif type="text"
                          class="form-control" id="cp_clave_sat" autocomplete="off" placeholder="" />
                      <label for="cp_clave_sat" class="text-gray-700">Clave SAT</label>
                  </div>
              </div>
            </div>

            <div class="col-3">
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
                      <input @if ($action == 'editar') value="" @endif type="text"
                          class="form-control" id="cp_cantidad" autocomplete="off" placeholder="" />
                      <label for="cp_cantidad" class="text-gray-700">Cantidad</label>
                  </div>
              </div>
            </div>

            <div class="col-3">
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
                      <input @if ($action == 'editar') value="" @endif type="text"
                          class="form-control" id="cp_valor" autocomplete="off" oninput="allowOnlyDecimals(event)" placeholder="" />
                      <label for="cp_valor" class="text-gray-700">Valor</label>
                  </div>
              </div>
            </div>

            <div class="col-3">
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
                      <input @if ($action == 'editar') value="" @endif type="text"
                          class="form-control" id="cp_moneda_valor" autocomplete="off" placeholder="" />
                      <label for="cp_moneda_valor" class="text-gray-700">Moneda</label>
                  </div>
              </div>
            </div>

            <div class="col-3">
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
                      <input @if ($action == 'editar') value="" @endif type="text"
                          class="form-control" id="cp_contacto_entrega" autocomplete="off" placeholder="" />
                      <label for="cp_contacto_entrega" class="text-gray-700">Tel. Contacto entrega</label>
                  </div>
              </div>
            </div>

            <div class="col-3">
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
                      <input @if ($action == 'editar') value="" @endif type="text"
                          class="form-control fechas" id="cp_fecha_tentativa_entrega" autocomplete="off" placeholder="" />
                      <label for="cp_fecha_tentativa_entrega" class="text-gray-700">Fecha tentativa entrega</label>
                  </div>
              </div>
            </div>

            <div class="col-3">
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
                      <input @if ($action == 'editar') value="" @endif  type="time"
                          class="form-control" id="cp_hora_tentativa_entrega" autocomplete="off" placeholder="" />
                      <label for="cp_hora_tentativa_entrega" class="text-gray-700">Hora tentativa entrega</label>
                  </div>
              </div>
            </div>

            <div class="col-12">
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
                      <input @if ($action == 'editar') value="" @endif type="text"
                          class="form-control" id="cp_comentarios" autocomplete="off" placeholder="" />
                      <label for="cp_comentarios" class="text-gray-700">Comentarios Carta Porte</label>
                  </div>
              </div>
            </div>

          </div>
        </div>
      </div>
      <!--end::Record-->
    </div>
    <!--end::Timeline details-->
  </div>
    <!--begin::Timeline content-->
    <div class="timeline-content mb-10 mt-n1">

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
            <div class="fs-4 fw-bold" id="id_uso_cfdi_label">Uso CFDI:</div>
          </div>
          <select class="form-select id_uso_cfdi d-inline-block" id="id_uso_cfdi" name="id_uso_cfdi">
            <option value="">Seleccionar Uso del CFDI</option> @foreach($usoCfdi as $u) <option value="{{$u->id}}" @if($action=="editar" ) @if($u->id == $cotizacion->sat_uso_cfdi_id) selected @endif @endif>{{$u->sat_code}} - {{$u->uso_cfdi}}</option> @endforeach
          </select>
        </div>
        <div class="col-6">
          <div class="d-flex align-items-center">
            <div class="fs-4 fw-bold" id="proveedorName">Forma de pago:</div>
          </div>
          <select class="form-select subcliente d-inline-block" id="id_forma_pago" name="id_forma_pago">
            <option value="">Seleccionar forma de pago</option> @foreach($formasPago as $f) <option value="{{$f->id}}" @if($action=="editar" ) @if($f->id == $cotizacion->sat_forma_pago_id) selected @endif @endif>{{$f->sat_code}} - {{$f->forma_pago}}</option> @endforeach
          </select>
        </div>
      </div>
      <div class="fv-row row mb-3">
        <div class="col-6">
          <div class="d-flex align-items-center">
            <div class="fs-4 fw-bold" id="proveedorName">Metodo de pago:</div>
          </div>
          <select class="form-select subcliente d-inline-block" id="id_metodo_pago" name="id_metodo_pago">
            <option value="">Seleccionar metodo de pago</option> @foreach($metodosPago as $m) <option value="{{$m->id}}" @if($action=="editar" ) @if($m->id == $cotizacion->sat_metodo_pago_id) selected @endif @endif>{{$m->sat_code}} - {{$m->metodo_pago}}</option> @endforeach
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