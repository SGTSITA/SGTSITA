<div class="modal fade" id="modalNuevoPrestamo" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Registrar Préstamo / Adelanto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="formPrestamoModal" novalidate>
                    <div class="row g-3">

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Operador</label>
                            <select id="modal_id_operador" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($operadores as $o)
                                    <option value="{{ $o->id }}">{{ $o->nombre }}</option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Seleccione operador.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Tipo</label>
                            <select id="modal_tipo" class="form-select" required>
                                <option value="prestamo">Préstamo</option>
                                <option value="adelanto">Adelanto</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Cantidad</label>
                            <input type="number" id="modal_cantidad" class="form-control" min="0.01" step="0.01"
                                required>
                            <div class="invalid-feedback">Ingrese cantidad válida.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Banco</label>
                            <select id="modal_id_banco" class="form-select" required>
                                <option value="">Seleccione</option>
                                @foreach ($bancos as $item)
                                    <option value="{{ $item['id'] }}">
                                        {{ $item['display'] }} :
                                        ${{ number_format($item['saldo_actual'], 2) }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="invalid-feedback">Seleccione banco.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Fecha Aplicación</label>
                            <input type="text" id="modal_FechaAplicacion" class="form-control dateInput">
                        </div>

                    </div>
                </form>

            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button class="btn btn-success" id="btnGuardarPrestamoModal">
                    Guardar
                </button>
            </div>

        </div>
    </div>
</div>
