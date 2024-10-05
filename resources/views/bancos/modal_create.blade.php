<div class="modal fade" id="bancoModal" tabindex="-1" aria-labelledby="bancoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Crear Banco</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('store.bancos') }}" id="" enctype="multipart/form-data" role="form">
            @csrf

            <div class="modal-body">
                <div class="row">

                    <div class="col-12 form-group">
                        <label for="name">Nombre Beneficiario*</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">
                            </span>
                            <input name="nombre_beneficiario" id="nombre_beneficiario" type="text" class="form-control">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Nombre Banco *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/sobre.png.webp') }}" alt="" width="25px">
                            </span>
                            <input name="nombre_banco" id="nombre_banco" type="text" class="form-control">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Cuenta Bancaria *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/telefono.png.webp') }}" alt="" width="25px">
                            </span>
                            <input name="cuenta_bancaria" id="cuenta_bancaria" type="text" class="form-control">
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Saldo inicial *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/billetera.png') }}" alt="" width="25px">
                            </span>
                            <input name="saldo_inicial" id="saldo_inicial" type="number" class="form-control">
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Clabe *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/mapa-de-la-ciudad.webp') }}" alt="" width="25px">
                            </span>
                            <input name="clabe" id="clabe" type="text" class="form-control">
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
