<div class="modal fade" id="modal-pago">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Pagar Gastos</h5>
            </div>

            <div class="modal-body">

                <div class="mb-2">
                    <label>Total a pagar</label>
                    <input type="text" id="txtTotalPagar" class="form-control" readonly>
                </div>

                <div class="mb-2">
                    <label>Fecha aplicación</label>
                    <div class="input-group b-3">
                        <span class="input-group-text" id="basic-addon1">
                            <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px" />
                        </span>
                        <input name="txtFechaAplicacion" id="txtFechaAplicacion" type="date" class="form-control"
                            value="{{ now()->format('Y-m-d') }}" />
                    </div>


                </div>

                <div class="mb-2">
                    <label>Banco</label>
                    <select id="cmbBanco" class="form-control">
                        @foreach ($bancos as $item)
                            <option value="{{ $item['id'] }}">
                                {{ $item['display'] }} :
                                ${{ number_format($item['saldo_actual'], 2) }}
                            </option>
                        @endforeach
                    </select>
                </div>

            </div>

            <div class="modal-footer">
                <button class="btn btn-success" id="btnConfirmarPago">Confirmar Pago</button>
            </div>

        </div>
    </div>
</div>
