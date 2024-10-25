<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Crear Usuario</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('update.bancos',$banco->id) }}" id="" enctype="multipart/form-data" role="form">
            <input type="hidden" name="_method" value="PATCH">
            @csrf

            <div class="modal-body">
                <div class="row">

                    <div class="col-12 form-group">
                        <label for="name">Nombre Beneficiario*</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">
                            </span>
                            <input name="nombre_beneficiario" id="nombre_beneficiario" type="text" class="form-control" value="{{$banco->nombre_beneficiario}}">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Nombre Banco *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/sobre.png.webp') }}" alt="" width="25px">
                            </span>
                            <input name="nombre_banco" id="nombre_banco" type="text" class="form-control" value="{{$banco->nombre_banco}}">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Cuenta Bancaria *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/business-card-design.webp') }}" alt="" width="25px">
                            </span>
                            <input name="cuenta_bancaria" id="cuenta_bancaria" type="text" class="form-control" value="{{$banco->cuenta_bancaria}}">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Saldo inicial *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="25px">
                            </span>
                            <input name="saldo_inicial" id="saldo_inicial" type="number" class="form-control" value="{{$banco->saldo_inicial}}">
                        </div>
                    </div>

                    {{-- <div class="col-12 form-group">
                        <label for="name">Saldo actual *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="25px">
                            </span>
                            <input name="saldo" id="saldo" type="number" class="form-control" value="{{$banco->saldo}}">
                        </div>
                    </div> --}}
                    <div class="col-12 form-group">
                        <label for="name">Clabe *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/mapa-de-la-ciudad.webp') }}" alt="" width="25px">
                            </span>
                            <input name="clabe" id="clabe" type="text" class="form-control"  value="{{$banco->clabe}}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
        </form>
      </div>
    </div>
  </div>
