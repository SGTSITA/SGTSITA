<div class="modal fade" id="modal-gastos-operador" tabindex="-1" role="dialog" aria-labelledby="modal-form"
    aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="card card-plain">
                    <div class="card-header pb-0 text-left">
                        <h3 class="font-weight-bolder text-success text-gradient">Agregar Gasto Operador</h3>
                        <p class="mb-0">Por favor introduza la Información solicitada a continuación:</p>
                    </div>
                    <div class="card-body">

                        <label>Descripción</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control" id="txtDescripcionGastoOperador"
                                placeholder="Descripción del gasto" aria-label="Descripción del gasto" />
                        </div>

                        <label>Monto</label>
                        <div class="input-group mb-3">
                            <input type="text" class="form-control moneyformat" id="txtMontoGastoOperador"
                                placeholder="$ 0.00" aria-label="$ 0.00" oninput="allowOnlyDecimals(event)" />
                        </div>

                        <div class="form-check form-switch ps-0">
                            <input class="form-check-input bd-gradient-success ms-0" type="checkbox"
                                id="checkPagoInmediato" checked="">
                            <label class="form-check-label text-body ms-3 text-truncate w-80 mb-0"
                                for="checkPagoInmediato">Pago Inmediato</label>
                        </div>

                        <label>Fecha aplicación</label>
                        <div class="input-group b-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px" />
                            </span>
                            <input name="txtFechaAplicacion" id="txtFechaAplicacion" type="date" class="form-control"
                                value="{{ now()->format('Y-m-d') }}" />
                        </div>


                        <label>Banco</label>
                        <select class="form-control" name="bancosGastos" id="bancosGastos">
                            @foreach ($bancos as $item)
                                <option value="{{ $item['id'] }}">
                                    {{ $item['display'] }} :
                                    ${{ number_format($item['saldo_actual'], 2) }}
                                </option>
                            @endforeach

                        </select>

                        <div class="text-center">
                            <button type="button" id="btnAgregar" onclick ="putGastosOperador()"
                                class="btn btn-round bg-gradient-success btn-lg w-100 mt-4 mb-0">Agregar</button>
                        </div>

                    </div>
                    <div class="card-footer text-center pt-0 px-lg-2 px-1">

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>
