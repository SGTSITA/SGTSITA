<div class="modal fade" id="editModal-{{ $item->id }}" tabindex="-1" aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar #{{$item->id}}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('update.empresas',$item->id ) }}" id="" enctype="multipart/form-data" role="form">
            <input type="hidden" name="_method" value="PATCH">
            @csrf

            <div class="modal-body">
                <div class="row">

                    <div class="col-12 form-group">
                        <label for="name">Nombre de Empresa  *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">
                            </span>
                            <input name="nombre" id="nombre" type="text" class="form-control" value="{{ $item->nombre }}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">correo *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/sobre.png.webp') }}" alt="" width="25px">
                            </span>
                            <input name="correo" id="correo" type="email" class="form-control" value="{{ $item->correo }}">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Telefono *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/telefono.png.webp') }}" alt="" width="25px">
                            </span>
                            <input name="telefono" id="telefono" type="number" class="form-control" value="{{ $item->telefono }}">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Direccion *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/mapa-de-la-ciudad.webp') }}" alt="" width="25px">
                            </span>
                            <input name="direccion" id="direccion" type="text" class="form-control" value="{{ $item->direccion }}">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Regimen Fiscal*</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px">
                            </span>
                            <input name="regimen_fiscal" id="regimen_fiscal" type="text" class="form-control" value="{{ $item->regimen_fiscal }}">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">RFC*</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px">
                            </span>
                            <input name="rfc" id="rfc" type="text" class="form-control" value="{{ $item->rfc }}">
                        </div>
                    </div>


                    <div class="col-12 form-group">
                        <label for="name">Selecinar configracion *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px">
                            </span>
                            <select class="form-select" name="id_configuracion" id="">

                                <option value="{{ $item->configuracion->nombre_sistema }}">{{ $item->configuracion->nombre_sistema }}</option>

                                @foreach ($configuraciones as $config)
                                    <option value="{{ $config->id }}" >{{ $config->nombre_sistema }}</option>
                                @endforeach

                            </select>
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Actualizar</button>
              </div>
        </form>
      </div>
    </div>
  </div>
