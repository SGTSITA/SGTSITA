<div class="modal fade" id="modalMovimientoInGasto" tabindex="-1" aria-labelledby="modalMovimientoInGastoLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow-lg border-0">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title d-flex align-items-center gap-2" id="modalMovimientoInGastoLabel">
                    <i class="fa fa-plus-circle text-primary fs-4"></i>
                    <span>Registrar movimiento bancario</span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <form id="formMovimientoInGasto">
                @csrf
                <div class="modal-body p-4">
                    <div class="alert alert-primary d-flex align-items-center py-2 px-3 mb-3 fs-7" role="alert">
                        <i class="fa fa-info-circle me-2 fs-5"></i>
                        <div>
                            Este ajuste aplicará directamente a la cuenta seleccionada. El saldo se actualizará de inmediato para completar tu registro de gasto.
                        </div>
                    </div>

                    <div class="row g-3">
                        {{-- Cuenta bancaria --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark fs-7">
                                Cuenta bancaria a afectar <span class="text-danger">*</span>
                            </label>
                            <select name="cuenta_bancaria_id" id="movCuentaBancariaId" class="form-select select-cuentas-bancarias" required>
                                <option value="">-- Seleccionar Cuenta --</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['display'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tipo de movimiento --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-7">
                                Tipo de movimiento <span class="text-danger">*</span>
                            </label>
                            <select name="tipo" id="movTipo" class="form-select" required>
                                <option value="abono" selected>Depósito / Ingreso (Abono +)</option>
                                <option value="cargo">Retiro / Egreso (Cargo -)</option>
                            </select>
                        </div>

                        {{-- Fecha de aplicación --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-7">
                                Fecha de aplicación <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="fecha_movimiento" id="movFechaMovimiento" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                        {{-- Monto --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-7">
                                Monto <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" min="0.01" name="monto" id="movMonto" class="form-control"
                                    placeholder="0.00" required>
                            </div>
                        </div>

                        {{-- Origen --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold text-dark fs-7">
                                Origen del movimiento <span class="text-danger">*</span>
                            </label>
                            <select name="origen" id="movOrigen" class="form-select" required>
                                <option value="ajuste" selected>Ajuste de saldo</option>
                                <option value="manual">Registro manual</option>
                                <option value="banco">Movimiento bancario</option>
                                <option value="importacion">Importación</option>
                            </select>
                        </div>

                        {{-- Referencia --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark fs-7">
                                Referencia / Folio / SPEI (Opcional)
                            </label>
                            <input type="text" name="referencia" id="movReferencia" class="form-control"
                                placeholder="Folio de transferencia, SPEI, ticket de depósito...">
                        </div>

                        {{-- Concepto --}}
                        <div class="col-md-12">
                            <label class="form-label fw-bold text-dark fs-7">
                                Concepto / Motivo <span class="text-danger">*</span>
                            </label>
                            <textarea name="concepto" id="movConcepto" class="form-control" rows="2"
                                placeholder="Ej. Depósito para cubrir gasto operativo, Ajuste por saldo inicial..." required></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-primary btn-sm d-flex align-items-center gap-1" id="btnSubmitMovimientoInGasto">
                        <i class="fa fa-save"></i>
                        <span>Guardar movimiento</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
