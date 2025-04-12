@extends('layouts.app')

@section('template_title')
    Planeacion
@endsection

@section('content')
<div class="row">
  <div class="col-12 col-lg-8 mx-auto my-4">
    <div class="card">
      <div class="card-body">
        <div class="multisteps-form__progress">
          <button class="multisteps-form__progress-btn js-active" type="button" title="User Info">
            <span>Contenedor</span>
          </button>
          <button class="multisteps-form__progress-btn" type="button" title="User Info">
            <span>Tipo de servicio</span>
          </button>
          <button class="multisteps-form__progress-btn" type="button" title="Address">Datos del transporte</button>
          <button class="multisteps-form__progress-btn" type="button" title="Socials">Fechas del viaje</button>
        </div>
      </div>
    </div>
  </div>
</div>
<!--form panels-->
<div class="row">
  <div class="col-12 col-lg-12 m-auto">
    <form class="multisteps-form__form">
      <!--single form panel-->
      <div class="card multisteps-form__panel p-3 border-radius-xl bg-white js-active" data-animation="FadeIn">
        <div class="row">
          <div class="col-7 mt-3 ">
            <h5 class="font-weight-normal text-left">¡Empecemos!</h5>
            <p class="text-left">Seleccione un contenedor para iniciar la planeación</p>
          </div>
          <div class="col-sm-5 text-end my-3 mt-3">
            <div class="h-100">
              <h5 class="mb-1 font-weight-bolder numContenedorLabel" id="numContenedor"></h5>
              <p class="mb-0 font-weight-bold text-sm nombreClienteLabel"></p>
            </div>
          </div>
        </div>
        <div class="row">
          <div id="gridAprobadas" class="ag-theme-alpine position-relative" style="height: 500px;">
            <div id="gridLoadingOverlay" class="loading-overlay" style="display: none;">
              <div class="spinner-border text-primary" role="status">
                <span class="visually-hidden">Cargando...</span>
              </div>
            </div>
          </div>
        </div>
        <div class="multisteps-form__content">
          <div class="button-row d-flex mt-4">
            <button class="btn bg-gradient-info btn-sm ms-auto mb-0 js-btn-next" id="nextOne" disabled="true" type="button" title="Siguiente"> Siguiente <i class="fa fa-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
      <!--single form panel-->
      <div class="card multisteps-form__panel p-3 border-radius-xl bg-white" data-animation="FadeIn">
        <div class="row">
          <div class="col-7 mt-3 text-left">
            <h5 class="font-weight-normal">¿Cúal medio utilizará para el envío contenedor?</h5>
            <p>Indique como se realizará el viaje</p>
          </div>
          <div class="col-sm-5 text-end mt-3">
            <div class="h-100">
              <h5 class="mb-1 font-weight-bolder numContenedorLabel"></h5>
              <p class="mb-0 font-weight-bold text-sm nombreClienteLabel"></p>
            </div>
          </div>
        </div>
        <div class="multisteps-form__content">
          <div class="row mt-4">
            <div class="row mt-4">
              <div class="custom-radio-group">
                <label class="custom-radio">
                  <input type="radio" name="option" value="propio" onclick="setTipoViaje('propio')">
                  <div class="content">
                    <i class="fas fa-truck-moving"></i>
                    <span>Propio</span>
                  </div>
                </label>
                <label class="custom-radio">
                  <input type="radio" name="option" value="proveedor" onclick="setTipoViaje('proveedor')">
                  <div class="content">
                    <i class="fas fa-trailer"></i>
                    <span>Sub Contratado</span>
                  </div>
                </label>
              </div>
            </div>
          </div>
          <div class="button-row d-flex mt-4">
            <button class="btn bg-gradient-info btn-sm mb-0 js-btn-prev" type="button" title="Anterior">
              <i class="fa fa-arrow-left"></i> Anterior </button>
            <button class="btn bg-gradient-info btn-sm ms-auto mb-0 js-btn-next" id="nextTwo" disabled="true" type="button" title="Siguiente"> Siguiente <i class="fa fa-arrow-right"></i>
            </button>
          </div>
        </div>
      </div>
      <!--single form panel-->
      <div class="card multisteps-form__panel p-3 border-radius-xl bg-white" data-animation="FadeIn">
        <div class="row">
          <div class="col-7 mt-3 text-left">
          <h5 class="font-weight-normal">Información general del viaje</h5>
            <p>Necesitamos algo de información para programar el viaje</p>
          </div>
          <div class="col-sm-5 my-4 mt-3 text-end">
            <div class="h-100">
              <h5 class="mb-1 font-weight-bolder text-right numContenedorLabel"></h5>
              <p class="mb-0 font-weight-bold text-sm text-right nombreClienteLabel"></p>
            </div>
          </div>
        </div>
        <div class="multisteps-form__content">
          <div class="row mt-2">
          <div class="col-lg-7 col-12 mt-4 mt-lg-0">
              <h6 class="mb-0">Fecha de viaje</h6>
              <p class="text-sm">Seleccione rango de fechas para el viaje.</p>
              <div class="row">
                <div class="col-md-6">
                  <label>Fecha salida</label>
                  <div class="form-group">
                    <div class="input-group ">
                      <span class="input-group-text">
                        <div class="icon icon-shape bg-gradient-danger text-center border-radius-md mb-2">
                          <i class="fa fa-calendar opacity-10" aria-hidden="true"></i>
                        </div>
                      </span>
                      <input class="form-control dateInput" name="txtFechaInicio" id="txtFechaInicio" placeholder="Fecha inicio" type="text">
                    </div>
                  </div>
                </div>
                <div class="col-md-6">
                  <label>Fecha entrega</label>
                  <div class="form-group">
                    <div class="input-group ">
                      <span class="input-group-text">
                        <div class="icon icon-shape bg-gradient-danger text-center border-radius-md mb-2">
                          <i class="fa fa-calendar opacity-10" aria-hidden="true"></i>
                        </div>
                      </span>
                      <input class="form-control dateInput" name="txtFechaFinal" id="txtFechaFinal" placeholder="Fecha fin" type="text">
                    </div>
                  </div>
                </div>
            
              </div>
            </div>
          </div>
          
          <div class="row text-start mt-3">
            <h6 class="mb-0">Información de la unidad</h6>
            <p class="text-sm">Proporcione los datos de la unidad donde se realizará el envío.</p>
            <div class="col-12 col-md-4  mt-2 text-start">
              <label>Tipo de unidad</label>
              <select class="form-control" name="cmbTipoUnidad" id="cmbTipoUnidad">
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
                  @if($item->tipo == "Chasis / Plataforma")
                      <option value="{{$item->id}}">{{$item->id_equipo}}</option>
                  @endif
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
                  @foreach ($bancos as $item)
                        <option value="{{$item->id}}">{{$item->nombre_banco}} / {{$item->nombre_beneficiario}}</option>
                  @endforeach
                  </select>
                </div>
              </div>
            </div>
          </div>
          <div class="row mt-3">
            <div class="button-row d-flex mt-4 col-12">
              <button class="btn bg-gradient-info btn-sm mb-0 js-btn-prev" type="button" title="Anterior">
              <i class="fa fa-arrow-left"></i> Anterior </button>
              <button class="btn bg-gradient-success btn-sm ms-auto mb-0" type="button" id="btnProgramar" title="Send">Programar viaje</button>
            </div>
          </div>
        </div>
      </div>
    </form>
  </div>
</div>
@endsection

@push('custom-javascript')
<style>
    .custom-radio-group {
  display: flex;
  justify-content: center;
  gap: 2rem;
  margin-top: 2rem;
}

.custom-radio {
  cursor: pointer;
  text-align: center;
  width: 160px;
  height: 160px; 
  position: relative;
}

.custom-radio input[type="radio"] {
  display: none;
}

.custom-radio .content {
  border: 1px dashed #ccc;
  border-radius: 15px;
  width: 100%;
  height: 100%;
  padding: 1rem;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  transition: all 0.3s ease;
}

.custom-radio .content i {
  font-size: 3rem;
  margin-bottom: 1rem;
  color: #555;
}

.custom-radio .content span {
  font-size: 1.2rem;
  color: #333;
}

/* Cuando está seleccionado */
.custom-radio input[type="radio"]:checked + .content {
  border: 1px solid #007bff; /* Borde sólido azul */
}

.custom-radio input[type="radio"]:checked + .content i,
.custom-radio input[type="radio"]:checked + .content span {
  color: #007bff;
}

/* Hover efecto */
.custom-radio:hover .content {
  border-color: #007bff;
}

input.flatpickr-input[readonly] {
  background-color: #fff !important; /* Fondo blanco */
  cursor: pointer; /* Opcional: para que el mouse cambie a "manita" */
}

.flatpickr-day .today {
  background: #28a745 !important; /* verde */
  border-color: #28a745 !important; 
  color: #fff; /* texto blanco */
}
</style>
   <!-- AG Grid -->
   <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>

   <!-- Nuestro JavaScript unificado -->
   <script src="/js/sgt/cotizaciones/aprobadas_list.js?v=1744206575"></script>
   <script src="/js/sgt/common.js?v=1744206575"></script>

<script src="/assets/js/plugins/multistep-form.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    flatpickr(".dateInput", {
      dateFormat: "d/m/Y",
      locale: "es"
    });

    let moneyformatInput = document.querySelectorAll('.moneyformat');

    moneyformatInput.forEach((r) => r.value = moneyFormat(r.value))
});
    </script>
@endpush