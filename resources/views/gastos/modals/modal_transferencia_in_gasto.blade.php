<div class="modal fade" id="modalTransferenciaInGasto" tabindex="-1" aria-labelledby="modalTransferenciaInGastoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalTransferenciaInGastoLabel">
                    <i class="fa fa-exchange-alt text-success fs-4"></i>
                    <span>Transferencia entre cuentas bancarias</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formTransferenciaInGasto">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-success d-flex align-items-center py-2 px-3 mb-3 fs-7" role="alert">
                        <i class="fa fa-info-circle me-2 fs-5"></i>
                        <div>
                            Realiza un traspaso entre cuentas de la empresa para habilitar saldo en la cuenta de retiro elegida.
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Cuenta origen --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-7">
                                Cuenta de origen (Retiro) <span class="text-danger">*</span>
                            </label>
                            <select name="cuenta_origen" id="transCuentaOrigen" class="form-select select-cuentas-bancarias" required>
                                <option value="">-- Seleccionar Cuenta Origen --</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['display'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Cuenta destino --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-7">
                                Cuenta de destino (Depósito) <span class="text-danger">*</span>
                            </label>
                            <select name="cuenta_destino" id="transCuentaDestino" class="form-select select-cuentas-bancarias" required>
                                <option value="">-- Seleccionar Cuenta Destino --</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['display'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Monto --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-7">
                                Monto a transferir <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01" name="monto" id="transMonto" class="form-control"
                                    placeholder="0.00" required>
                            </div>
                        </div>

                        {{-- Fecha de aplicación --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-7">
                                Fecha de aplicación <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="fecha_aplicacion" id="transFechaAplicacion" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                        {{-- Concepto --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark fs-7">
                                Concepto / Motivo de la transferencia <span class="text-danger">*</span>
                            </label>
                            <input type="text" name="concepto" id="transConcepto" class="form-control"
                                placeholder="Ej. Traspaso para cubrir pago de gasto..." required>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-success btn-sm d-flex align-items-center gap-1" id="btnSubmitTransferenciaInGasto">
                        <i class="fa fa-check-circle"></i>
                        <span>Aplicar transferencia</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
