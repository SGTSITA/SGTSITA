<div class="modal fade" id="modalPagar" tabindex="-1" role="dialog" aria-labelledby="modal-form" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
            <div class="modal-body p-0">
                <div class="card card-plain">
                    <div class="card-header pb-0 text-left">
                        <h3 class="font-weight-bolder text-warning text-gradient">Pagar Gastos</h3>
                        <p class="mb-0">Se realizará un pago por:</p>
                    </div>
                    <div class="card-body">

                        <div class="row">
                            <div class="col-6 offset-3 text-center">
                                <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                                    <h6 class="text-primary mb-0">Total a Pagar</h6>
                                    <h4 class="font-weight-bolder"><span class="small totalPago"
                                            id="totalPagoGastosOperador">$ 0.00</span></h4>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 offset-3 text-center">
                            <label for="name">Fecha Aplicación</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt=""
                                        width="25px" />
                                </span>
                                <input name="fecha_aplicacion" id="fecha_aplicacion" autocomplete="off" type="text"
                                    class="form-control fechas" required />
                            </div>


                        </div>

                        <label>Banco</label>
                        <select class="form-control" name="bancosPagoGastos" id="bancosPagoGastos">
                            @foreach ($bancos as $b)
                                <option value="{{ $b['id'] }}">
                                    {{ $b['display'] }}
                                </option>
                            @endforeach

                        </select>



                        <div class="text-center">
                            <button type="button" id="btnPagar" onclick ="applyPaymentGastos()"
                                class="btn btn-round bg-gradient-success btn-lg w-100 mt-4 mb-0">
                                <i class="fa fa-fw fa-coins"></i>
                                Realizar Pago
                            </button>
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
