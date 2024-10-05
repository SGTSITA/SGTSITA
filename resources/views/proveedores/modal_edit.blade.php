<div class="modal fade" id="editModal{{$proveedor->id}}" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Proveedor</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('update.proveedores',$proveedor->id) }}" id="" enctype="multipart/form-data" role="form">
            <input type="hidden" name="_method" value="PATCH">

            @csrf

            <div class="modal-body">
                <div class="row">

                    <div class="col-12 form-group">
                        <label for="name">Nombre Completo*</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">
                            </span>
                            <input name="nombre" id="nombre" type="text" class="form-control" value="{{$proveedor->nombre}}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">correo *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/sobre.png.webp') }}" alt="" width="25px">
                            </span>
                            <input name="correo" id="correo" type="email" class="form-control" value="{{$proveedor->correo}}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Telefono *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/telefono.png.webp') }}" alt="" width="25px">
                            </span>
                            <input name="telefono" id="telefono" type="number" class="form-control" value="{{$proveedor->telefono}}">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Direccion *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/mapa-de-la-ciudad.webp') }}" alt="" width="25px">
                            </span>
                            <input name="direccion" id="direccion" type="text" class="form-control" value="{{$proveedor->direccion}}">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Regimen Fiscal*</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px">
                            </span>
                            <input name="regimen_fiscal" id="regimen_fiscal" type="text" class="form-control" value="{{$proveedor->regimen_fiscal}}">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">RFC *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px">
                            </span>
                            <input name="rfc" id="rfc" type="text" class="form-control"  value="{{$proveedor->rfc}}">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Tipo *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px">
                            </span>
                            <select class="form-select d-inline-block" id="tipo" name="tipo" >
                                <option value="{{$proveedor->tipo}}" selected>{{$proveedor->tipo}}</option>
                                <option value="servicio mecánico">1) servicio mecánico</option>
                                <option value="servicio de burreo">2) servicio de burreo</option>
                                <option value="servicio de viaje">3) servicio de viaje</option>
                                <option value="servicio de patio">4) servicio d
                                <option value="Otro">Otro</option>
                            </select>
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
