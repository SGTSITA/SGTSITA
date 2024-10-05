<div class="modal fade" id="equipoEditModal-{{$item->id}}" tabindex="-1" aria-labelledby="equipoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Equipo #{{$item->id}} </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('update.equipos', $item->id) }}" id="" enctype="multipart/form-data" role="form">
            <input type="hidden" name="_method" value="PATCH">

            @csrf

            <div class="modal-body">
                <div class="row">

                    <div class="col-12">
                        <h6>Datos del Vehiculo</h6>
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
                            <input name="fecha" id="fecha" type="date" class="form-control" value="{{$item->fecha}}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Año *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                            </span>
                            <input name="year" id="year" type="number" class="form-control" value="{{$item->year}}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Marca *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/marca.webp') }}" alt="" width="25px">
                            </span>
                            <input name="marca" id="marca" type="text" class="form-control" value="{{$item->marca}}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Modelo *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/coche.png') }}" alt="" width="25px">
                            </span>
                            <input name="modelo" id="modelo" type="text" class="form-control" value="{{$item->modelo}}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Placas *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/placa.png') }}" alt="" width="25px">
                            </span>
                            <input name="placas" id="placas" type="text" class="form-control" value="{{$item->placas}}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Numero de Serie *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/sku.webp') }}" alt="" width="25px">
                            </span>
                            <input name="num_serie" id="num_serie" type="text" class="form-control" value="{{$item->num_serie}}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Acceso *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/iniciar-sesion.png') }}" alt="" width="25px">
                            </span>
                            <input name="acceso" id="acceso" type="text" class="form-control" value="{{$item->acceso}}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Tarjeta de Circulacion *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/business-card-design.webp') }}" alt="" width="25px">
                            </span>
                            <input name="tarjeta_circulacion" id="tarjeta_circulacion" type="file" class="form-control" value="{{$item->acceso}}">
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

                    @if($item->folio != NULL)

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

                    @endif


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
