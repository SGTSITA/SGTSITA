<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Gasto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('store.gastos_generales') }}" id="" enctype="multipart/form-data" role="form">
            @csrf
            <div class="modal-body">
                <div class="row">

                    <div class="col-12 form-group">
                        <label for="name">Motivo*</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                            </span>
                            <input name="motivo" id="motivo" type="text" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Monto *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px">
                            </span>
                            <input name="monto1" id="monto1" type="text" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Metodo pago *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/pago-movil.webp') }}" alt="" width="25px">
                            </span>
                            <select class="form-select d-inline-block" id="metodo_pago1" name="metodo_pago1" required>
                                <option value="Tarjeta C/D">Tarjeta C/D</option>
                                <option value="Transferencia">Transferencia</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Banco *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/t debito.webp') }}" alt="" width="25px">
                            </span>
                            <select class="form-select d-inline-block" id="id_banco1" name="id_banco1" required>
                                <option value="">Selecciona</option>
                                @foreach ($bancos as $item)
                                    <option value="{{$item->id}}">{{$item->nombre_banco}} - ${{ number_format($item->saldo, 2, '.', ',') }}</option>
                                @endforeach
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
