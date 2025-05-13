<div class="row text-start mt-3">
            <h6 class="mb-0">Información de la unidad</h6>
            <p class="text-sm">Proporcione los datos de la unidad donde se realizará el envío.</p>
            <div class="col-12 col-md-4  mt-2 text-start">
              <label>Tipo de unidad</label>
              <select class="form-control" name="cmbTipoUnidad" id="cmbTipoUnidad" disabled>
                <option value="Sencillo">Sencillo</option>
                <option value="Full">Full</option>
              </select>
            </div>
            <div class="col-12 col-md-4  mt-2 text-start">
              <label>Unidad</label>
              <select class="form-control" name="cmbCamion" id="cmbCamion">
                @foreach ($equipos as $item)
                  @if($item->tipo == "Tractos / Camiones")
                      <option value="{{$item->id}}">{{$item->id_equipo}}</option>
                  @endif
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-4  mt-2 text-start">
              <label>Chasis</label>
              <select class="form-control" name="cmbChasis" id="cmbChasis">
              @foreach ($equipos as $item)
                  @if($item->tipo == "Chasis / Plataforma")
                      <option value="{{$item->id}}">{{$item->id_equipo}}</option>
                  @endif
                @endforeach
              </select>
            </div>
            <div class="col-12 col-md-4  mt-2 text-start">
              <label>Chasis 2</label>
              <select class="form-control" name="cmbChasis2" id="cmbChasis2" disabled>
              @foreach ($equipos as $item)
                  @if($item->tipo == "Chasis / Plataforma")
                      <option value="{{$item->id}}">{{$item->id_equipo}}</option>
                  @endif
              @endforeach
              </select>
            </div>
            <div class="col-12 col-md-4  mt-2 text-start">
              <label>Doly</label>
              <select class="form-control" name="cmbDoly" id="cmbDoly" disabled>
                @foreach ($equipos as $item)
                  @if($item->tipo == "Chasis / Plataforma")
                      <option value="{{$item->id}}">{{$item->id_equipo}}</option>
                  @endif
                @endforeach
              </select>
            </div>
          
          </div>
          <div class="row mt-4">
            <div class="col-lg-5 col-12">
              <h6 class="mb-0">Operador</h6>
              <p class="text-sm">Seleccione operador que transportará el contenedor.</p>
              <div class="border-dashed border-1 border-secondary border-radius-md p-3">
                <p class="text-xs mb-2">
                  <span class="font-weight-bolder">Operador</span>
                </p>
                
                <div class="d-flex align-items-center">
                <select class="form-control" name="cmbOperador" id="cmbOperador">
                  <option value="">Seleccione operador</option>
                  @foreach ($operadores as $item)
                    <option value="{{$item->id}}">{{$item->nombre}}</option>
                  @endforeach
                </select>
                  <!--<div class="form-group w-70">
                    <div class="input-group bg-gray-200 is-filled">
                      <input class="form-control form-control-sm" value="argon-dashboard-vmsk392" type="text" disabled="" onfocus="focused(this)" onfocusout="defocused(this)">
                      <span class="input-group-text bg-transparent" data-bs-toggle="tooltip" data-bs-placement="top" aria-label="Referral code expires in 24 hours" data-bs-original-title="Referral code expires in 24 hours">
                        <i class="ni ni-key-25"></i>
                      </span>
                    </div>
                  </div>
                  <a href="javascript:;" class="btn btn-sm btn-outline-secondary ms-2 px-3">Copy</a>-->
                </div>
                <!--<p class="text-xs mb-1">You cannot generate codes.</p>
                <p class="text-xs mb-0">
                  <a href="javascript:;">Contact us</a> to generate more referrals link.
                </p>-->
              </div>
            </div>
            <div class="col-lg-7 col-12 mt-4 mt-lg-0">
              <h6 class="mb-0">Información de pago</h6>
              <p class="text-sm">Proporcione la información de pago.</p>
              <div class="row">
                <div class="col-md-6">
                  <label>Sueldo operador</label>
                  <div class="form-group">
                    <div class="input-group ">
                      <span class="input-group-text">
                        <div class="icon icon-shape bg-gradient-success text-center border-radius-md mb-2">
                          <i class="ni ni-money-coins opacity-10" aria-hidden="true"></i>
                        </div>
                      </span>
                      <input class="form-control moneyformat" name="txtSueldoOperador" id="txtSueldoOperador" autocomplete="off" placeholder="Sueldo Operador" oninput="allowOnlyDecimals(event)" type="text">
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <label>Dinero viaje</label>
                  <div class="form-group">
                    <div class="input-group ">
                      <span class="input-group-text">
                        <div class="icon icon-shape bg-gradient-success text-center border-radius-md mb-2">
                          <i class="ni ni-money-coins opacity-10" aria-hidden="true"></i>
                        </div>
                      </span>
                      <input class="form-control moneyformat" name="txtDineroViaje" id="txtDineroViaje" autocomplete="off" placeholder="Dinero viaje" oninput="allowOnlyDecimals(event)" type="text">
                    </div>
                  </div>
                </div>
                <div class="col-12 col-md-12 text-start">
                  <label>Banco</label>
                  <select class="form-control" name="cmbBanco" id="cmbBanco">
                  <option value="">Seleccione banco</option>
                  @foreach ($bancos as $item)
                        <option value="{{$item->id}}">{{$item->nombre_banco}} / {{$item->nombre_beneficiario}}</option>
                  @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>