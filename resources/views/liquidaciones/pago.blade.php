<div class="modal fade" id="cobrarModal{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Liquidar viaje - Contenedor: {{ $item->Contenedor->num_contenedor }}</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

            <form method="POST" action="{{ route('update.liquidacion', $item->id) }}" enctype="multipart/form-data" role="form">
                @csrf
                <input type="hidden" name="_method" value="PATCH">
                <div class="modal-body">
                    <div class="row">

                        <div class="col-6 form-group">
                            <label for="name">Casetas</label>
                            <ul>
                                @foreach ($gastos_ope as $gasto_ope)
                                    @if ($gasto_ope->id_asignacion == $item->id)
                                        @if ($gasto_ope->tipo == 'Casetas')
                                            <li>{{$gasto_ope->cantidad}}</li>
                                        @endif
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Otros</label>
                            <ul>
                                @foreach ($gastos_ope as $gasto_ope)
                                    @if ($gasto_ope->id_asignacion == $item->id)
                                        @if ($gasto_ope->tipo == 'Otros')
                                            <li>{{$gasto_ope->cantidad}}</li>
                                        @endif
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Gasolina</label>
                            <ul>
                                @foreach ($gastos_ope as $gasto_ope)
                                    @if ($gasto_ope->id_asignacion == $item->id)
                                        @if ($gasto_ope->tipo == 'Gasolina')
                                            <li>{{$gasto_ope->cantidad}}</li>
                                        @endif
                                    @endif
                                @endforeach
                            </ul>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Sueldo Viaje</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" value=" ${{ number_format($item->sueldo_viaje, 2, '.', ',') }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Dinero para Viaje</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" value=" ${{ number_format($item->dinero_viaje, 2, '.', ',') }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Dinero a pagar</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" id="pago_operador" name="pago_operador" value=" ${{ number_format($item->pago_operador, 2, '.', ',') }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Restante</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" value=" ${{ number_format($item->restante_pago_operador, 2, '.', ',') }}" readonly>
                                </div>
                            </div>
                        </div>

                        <h5 class="modal-title mt-3">Metodo de pago 1</h5>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Monto de pago 1 *</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="float" id="monto1" name="monto1" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Metodo de pago 1 *</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                    </span>
                                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="metodo_pago1" name="metodo_pago1">
                                        <option value="">Seleccionar Metodo</option>
                                        <option value="Tarjeta C/D">Tarjeta C/D</option>
                                        <option value="Transferencia">Transferencia</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Banco</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                    </span>
                                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="id_banco1" name="id_banco1">
                                        <option value="">Selecciona</option>
                                        @foreach ($bancos as $item)
                                            <option value="{{$item->id}}">{{$item->nombre_banco}} - ${{$item->saldo}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Comprobante de pago 1 *</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/validando-billete.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="file" id="comprobante1" name="comprobante1" class="form-control">
                                </div>
                            </div>
                        </div>

                        <h5 class="modal-title mt-3">Metodo de pago 2</h5>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Monto de pago 2</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="float" id="monto2" name="monto2" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Metodo de pago 2</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                    </span>
                                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="metodo_pago2" name="metodo_pago2">
                                        <option value="">Seleccionar Metodo</option>
                                        <option value="Tarjeta C/D">Tarjeta C/D</option>
                                        <option value="Transferencia">Transferencia</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Banco 2</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                    </span>
                                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="id_banco2" name="id_banco2">
                                            <option value="">Selecciona</option>
                                        @foreach ($bancos as $item)
                                            <option value="{{$item->id}}">{{$item->nombre_banco}} - ${{$item->saldo}}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Comprobante de pago 2</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/validando-billete.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="file" id="comprobante2" name="comprobante2" class="form-control">
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
  @section('select2')
  <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
  <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js')}}"></script>

    <script>

    </script>
@endsection
