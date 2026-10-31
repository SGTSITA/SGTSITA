<div class="modal fade" id="modalMovimiento" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Registrar movimiento bancario</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <form method="POST" id="formcrearmovimientom"
                action="{{ route('bancos.movimientos.store', $cuenta->id) }}">
                @csrf

                <input type="hidden" name="cuenta_bancaria_id" value="{{ $cuenta->id }}">

                <div class="modal-body">
                    <div class="row g-3">

                        {{-- Fecha --}}
                        <div class="col-md-4">
                            <label class="form-label">Fecha de aplicación</label>
                            <input type="date" name="fecha_movimiento" class="form-control"
                                value="{{ date('Y-m-d') }}" required>
                        </div>

                        {{-- Tipo --}}
                        <div class="col-md-4">
                            <label class="form-label">Tipo</label>
                            <select name="tipo" class="form-control" required>
                                <option value="abono">Ingreso</option>
                                <option value="cargo">Egreso</option>
                            </select>
                        </div>

                        {{-- Referencia --}}
                        <div class="col-md-4">
                            <label class="form-label">Referencia (opcional)</label>
                            <input type="text" name="referencia" class="form-control"
                                placeholder="Folio, SPEI, ticket...">
                        </div>

                        {{-- Concepto (TEXTO LIBRE) --}}
                        <div class="col-md-12">
                            <label class="form-label">Concepto</label>
                            <textarea name="concepto" class="form-control" rows="2"
                                placeholder="Ej. Depósito en efectivo del día, Ajuste por diferencia, Comisión bancaria... " required></textarea>
                        </div>

                        {{-- Monto --}}
                        <div class="col-md-4">
                            <label class="form-label">Monto</label>
                            <input type="number" step="0.01" name="monto" class="form-control" required>
                        </div>

                        {{-- Concepto Contable / Clasificación --}}
                        <div class="col-md-4">
                            <label class="form-label">Clasificación Contable</label>
                            <select name="tipo_concepto" id="selectTipoConceptoMov" class="form-control" onchange="toggleInfoInteres(this.value)">
                                <option value="operativo" selected>Operativo (General)</option>
                                <option value="interes">Interés / Rendimiento Bancario</option>
                                <option value="traspaso">Traspaso entre cuentas</option>
                                <option value="comision">Comisión bancaria</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label">Origen del movimiento</label>
                            <select name="origen" class="form-control" required>
                                <option value="manual">Registro manual</option>
                                <option value="banco">Movimiento bancario</option>
                                <option value="ajuste">Ajuste</option>
                                <option value="importacion">Importación</option>
                            </select>
                        </div>

                        <div class="col-12 d-none" id="alertInfoInteres">
                            <div class="alert alert-info py-2 px-3 mb-0 d-flex align-items-center gap-2" style="font-size: 13px;">
                                <i class="fa fa-info-circle text-info fs-5"></i>
                                <div>
                                    <strong>Tratamiento Contable CONAC:</strong> Este movimiento generará automáticamente una Póliza de Ingreso con abono a <em>4.3.1.1 Intereses Ganados / Rendimientos Financieros</em>, separándolo de los fletes operativos en el Balance y Estado de Resultados.
                                </div>
                            </div>
                        </div>



                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button class="btn btn-success" type="button" class="btn btn-success" id="btnGuardarMovimiento">
                        Guardar movimiento
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>

<script>
    function toggleInfoInteres(val) {
        const alertEl = document.getElementById('alertInfoInteres');
        const tipoSelect = document.querySelector('#formcrearmovimientom select[name="tipo"]');
        if (val === 'interes') {
            if (alertEl) alertEl.classList.remove('d-none');
            if (tipoSelect) tipoSelect.value = 'abono';
        } else {
            if (alertEl) alertEl.classList.add('d-none');
        }
    }
</script>
