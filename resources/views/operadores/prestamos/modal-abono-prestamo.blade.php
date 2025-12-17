<!-- Modal para registrar abono -->
<div class="modal fade" id="modalAbono" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Registrar Abono</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <form id="formAbono">
                    <input type="hidden" id="id_prestamo_abono" name="id_prestamo" />

                    <!-- Banco de abono -->
                    <div class="mb-3">
                        <label for="id_banco_abono" class="form-label fw-semibold">Banco donde se abona</label>
                        <select id="id_banco_abono" name="id_banco_abono" class="form-select" required>
                            <option value="">Seleccione un banco</option>
                            @foreach ($bancos as $b)
                                <option value="{{ $b->id }}">{{ $b->nombre_banco }}</option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">Debe seleccionar un banco para el abono.</div>
                    </div>

                    <!-- Monto -->
                    <div class="mb-3">
                        <label for="monto_abono" class="form-label fw-semibold">Monto a abonar</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0">$</span>
                            <input
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="form-control border-start-0"
                                id="monto_abono"
                                required
                            />
                        </div>
                        <div class="invalid-feedback">Ingrese un monto válido mayor a 0.</div>
                    </div>
                    <!-- Referencia -->
                    <div class="mb-3">
                        <label for="referencia" class="form-label fw-semibold">Referencia (opcional)</label>
                        <input
                            type="text"
                            class="form-control"
                            id="referencia"
                            name="referencia"
                            maxlength="150"
                            placeholder="Ingrese una referencia para el abono"
                        />
                        <div class="invalid-feedback">La referencia no debe exceder los 150 caracteres.</div>
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-success px-4">
                            <i class="bi bi-cash-coin me-1"></i>
                            Guardar abono
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
