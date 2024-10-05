<div class="modal fade" id="cobrarModal{{$item->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-dialog-centered">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Comprobante de cobro</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

            <form method="POST" action="{{ route('update.cobrar', $item->id) }}" enctype="multipart/form-data" role="form">
                @csrf
                <input type="hidden" name="_method" value="PATCH">
                <div class="modal-body">
                    <div class="row">
                        @if ($item->id_subcliente != NULL)
                           <h5> {{$item->Subcliente->nombre}} / {{$item->Subcliente->telefono}}</h5>
                        @else
                            -
                        @endif

                        @if ($item->precio_viaje != NULL)
                            <div class="col-6 form-group">
                                <label for="name">Precio Viaje</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" value="${{ number_format($item->precio_viaje, 2, '.', ',')}}" readonly>
                                </div>
                            </div>
                        @endif

                        @if ($item->burreo != NULL)
                            <div class="col-6 form-group">
                                <label for="name">Burreo</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/burro.png') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" value="${{ number_format($item->burreo, 2, '.', ',')}}" readonly>
                                </div>
                            </div>
                        @endif

                        @if ($item->maniobra != NULL)
                            <div class="col-6 form-group">
                                <label for="name">Maniobra</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/logistica.png') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" value="${{ number_format($item->maniobra, 2, '.', ',')}}" readonly>
                                </div>
                            </div>
                        @endif

                        @if ($item->estadia != NULL)
                            <div class="col-6 form-group">
                                <label for="name">Estadia</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/servidor-en-la-nube.png') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" value="${{ number_format($item->estadia, 2, '.', ',')}}" readonly>
                                </div>
                            </div>
                        @endif

                        @if ($item->otro != NULL)
                            <div class="col-6 form-group">
                                <label for="name">Otros</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control"  value="${{ number_format($item->otro, 2, '.', ',')}}" readonly>
                                </div>
                            </div>
                        @endif

                        @if ($item->iva != NULL)
                            <div class="col-6 form-group">
                                <label for="name">IVA</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/impuesto.png') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" value="${{ number_format($item->iva, 2, '.', ',')}}" readonly>
                                </div>
                            </div>
                        @endif

                        @if ($item->retencion != NULL)
                            <div class="col-6 form-group">
                                <label for="name">Retención</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" value="${{ number_format($item->retencion, 2, '.', ',')}}" readonly>
                                </div>
                            </div>
                        @endif
                        <div class="col-6"></div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Total a cobrar *</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px">
                                    </span>
                                    <input type="text" class="form-control" value=" ${{ number_format($item->total, 2, '.', ',') }}" readonly>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-group">
                                <label for="name">Restante</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/depositar.png') }}" alt="" width="25px">
                                    </span>
                                    <input type="float" class="form-control" name="restante" value=" ${{ number_format($item->restante, 2, '.', ',') }}" readonly>
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
                                    <input type="float" id="monto1_{{ $item->id }}" name="monto1" class="form-control" value="{{ $item->monto1 }}" oninput="calcularRestante({{ $item->id }})">
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
                                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="metodo_pago1" name="metodo_pago1" value="{{ old('metodo_pago1') }}">
                                        @if ($item->metodo_pago1 == NULL)
                                            <option value="">Seleccionar Metodo</option>
                                        @else
                                            <option value="{{$item->metodo_pago1}}">{{$item->metodo_pago1}}</option>
                                        @endif
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
                                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="id_banco1" name="id_banco1" value="{{ old('id_banco1') }}">
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
                                    <input type="float" id="monto2_{{ $item->id }}" name="monto2" class="form-control" value="{{ $item->monto2 }}" oninput="calcularRestante({{ $item->id }})">
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
                                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="metodo_pago2" name="metodo_pago2" value="{{ old('metodo_pago2') }}">
                                        @if ($item->metodo_pago2 == NULL)
                                            <option value="">Seleccionar Metodo</option>
                                        @else
                                            <option value="{{$item->metodo_pago2}}">{{$item->metodo_pago2}}</option>
                                        @endif
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
                                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="id_banco2" name="id_banco2" value="{{ old('id_banco2') }}">

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
    function calcularRestante(id) {
        // Obtener los montos
        const monto1 = parseFloat(document.getElementById(`monto1_${id}`).value) || 0;
        const monto2 = parseFloat(document.getElementById(`monto2_${id}`).value) || 0;
        const total = parseFloat(document.getElementById(`total_${id}`).value.replace(/[^0-9.-]+/g,""));

        // Calcular el restante
        let restante = total - monto1 - monto2;

        // Mostrar el restante
        document.getElementById(`restante_${id}`).value = restante.toFixed(2);
    }
</script>
@endsection
