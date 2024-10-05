<div class="modal fade" id="equipoModal" tabindex="-1" aria-labelledby="equipoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Crear Equipo</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('store.equipos') }}" id="" enctype="multipart/form-data" role="form">
            @csrf

            <div class="modal-body">
                <div class="row">

                    <div class="col-12">

                        <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                            <li class="nav-item" role="presentation">
                              <button class="nav-link active" id="pills-home-tab" data-bs-toggle="pill" data-bs-target="#pills-home" type="button" role="tab" aria-controls="pills-home" aria-selected="true">
                                Tractos / Camiones
                            </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-profile-tab" data-bs-toggle="pill" data-bs-target="#pills-profile" type="button" role="tab" aria-controls="pills-profile" aria-selected="false">
                                    Chasis Plataforma
                                </button>
                            </li>

                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="pills-dolys-tab" data-bs-toggle="pill" data-bs-target="#pills-dolys" type="button" role="tab" aria-controls="pills-dolys" aria-selected="false">
                                    Dolys
                                </button>
                            </li>
                          </ul>

                          <div class="tab-content" id="pills-tabContent">
                            <div class="tab-pane fade show  active" id="pills-home" role="tabpanel" aria-labelledby="pills-home-tab" tabindex="0">
                                <div class="row">

                                    <div class="col-12">
                                        <h6>Datos del Tracto / Camion</h6>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Folio *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="id_equipo" id="id_equipo" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Fecha de alta *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="fecha" id="fecha" type="date" class="form-control" value="{{date('Y-m-d')}}">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Año *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="year" id="year" type="number" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Marca *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/marca.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="marca" id="marca" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Modelo *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/coche.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="modelo" id="modelo" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Motor *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="motor" id="motor" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Placas *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/sku.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="placas" id="placas" type="text" class="form-control" >
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Numero de Serie *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="num_serie" id="num_serie" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Acceso *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/iniciar-sesion.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="acceso" id="acceso" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Tarjeta de Circulacion *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/business-card-design.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="tarjeta_circulacion" id="tarjeta_circulacion" type="file" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Poliza de Seguro *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="poliza_seguro" id="poliza_seguro" type="file" class="form-control">
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="tab-pane fade" id="pills-profile" role="tabpanel" aria-labelledby="pills-profile-tab" tabindex="0">
                                <div class="row">
                                    <div class="col-12">
                                        <h6>Datos del Chasis Plataforma</h6>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Folio *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="id_equipo_chasis" id="id_equipo" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Fecha de alta *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="fecha_chasis" id="fecha_chasis" type="date" class="form-control" value="{{date('Y-m-d')}}">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Año *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="year_chasis" id="year_chasis" type="number" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Marca *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/marca.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="marca_chasis" id="marca_chasis" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Modelo *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/coche.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="modelo_chasis" id="modelo_chasis" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Motor *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="motor_chasis" id="motor_chasis" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Placas *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/sku.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="placas_chasis" id="placas" type="text" class="form-control" >
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Numero de Serie *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/sku.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="num_serie_chasis" id="num_serie_chasis" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Acceso *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/iniciar-sesion.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="acceso_chasis" id="acceso_chasis" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Tarjeta de Circulacion *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/business-card-design.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="tarjeta_circulacion_chasis" id="tarjeta_circulacion_chasis" type="file" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Poliza de Seguro *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="poliza_seguro_chasis" id="poliza_seguro_chasis" type="file" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="name">Tipo *</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/tools.png.webp') }}" alt="" width="35px">
                                                </span>
                                                <select name="folio" id="folio" class="form-select d-inline-block" >
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="B9 40P">B9 40P</option>
                                                    <option value="B10 20P">B10 20P</option>
                                                    <option value="B11 20/40P">B11 20/40P</option>
                                                    <option value="B12 Abatible">B12 Abatible</option>
                                                    <option value="B13 Retractil">B13 Retractil</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="tab-pane fade" id="pills-dolys" role="tabpanel" aria-labelledby="pills-dolys-tab" tabindex="0">
                                <div class="row">
                                    <div class="col-12">
                                        <h6>Dolys</h6>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Folio *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="id_equipo_doly" id="id_equipo" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Fecha de alta *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="fecha_doly" id="fecha_doly" type="date" class="form-control" value="{{date('Y-m-d')}}">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Año *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="year_doly" id="year_doly" type="number" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Marca *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/marca.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="marca_doly" id="marca_doly" type="text" class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-6 form-group">
                                        <label for="name">Modelo *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/coche.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="modelo_doly" id="modelo_doly" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Placas *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/placa.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="placas_doly" id="placas" type="text" class="form-control" >
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Numero de Serie *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/sku.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="num_serie_doly" id="num_serie_doly" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Acceso *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/iniciar-sesion.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="acceso_doly" id="acceso_doly" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Tarjeta de Circulacion *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/business-card-design.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="tarjeta_circulacion_doly" id="tarjeta_circulacion_doly" type="file" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Poliza de Seguro *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="poliza_seguro_doly" id="poliza_seguro_doly" type="file" class="form-control">
                                        </div>
                                    </div>

                                </div>
                            </div>
                          </div>

                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
              </div>
        </form>
      </div>
    </div>
  </div>
