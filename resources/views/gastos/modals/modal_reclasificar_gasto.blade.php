<!-- Modal Reclasificar Gasto(s) -->
<div class="modal fade" id="modalReclasificarGasto" data-bs-focus="false" aria-hidden="true" data-bs-backdrop="static" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow-lg rounded-3" id="formReclasificarGasto">
            @csrf
            <input type="hidden" id="reclasificarGastoId" name="gasto_id" value="">
            <input type="hidden" id="reclasificarEsMultiple" value="0">

            <div class="modal-header bg-gradient-dark py-3 text-white">
                <h5 class="modal-title text-white mb-0">
                    <i class="fa fa-exchange-alt me-2"></i> <span id="reclasificarModalTitulo">Reclasificar Gasto</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body p-3">
                <div class="alert alert-info py-2 px-3 text-xs text-white mb-3" id="reclasificarAlertInfo">
                    Seleccione la nueva categoría y subcategoría para clasificar el gasto.
                </div>

                <div class="mb-3" id="divReclasificarConceptoActual">
                    <label class="form-label text-xs fw-bold text-muted mb-1">Concepto / Motivo Actual</label>
                    <input type="text" id="reclasificarConceptoTxt" class="form-control form-control-sm" placeholder="Concepto del gasto">
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs fw-bold">Nueva Categoría *</label>
                    <select id="reclasificarSelectCategoria" class="form-select form-select-sm" required>
                        <option value="">-- Seleccione Categoría --</option>
                    </select>
                </div>

                <div class="mb-3">
                    <label class="form-label text-xs fw-bold">Nueva Subcategoría (Concepto)</label>
                    <select id="reclasificarSelectSubcategoria" class="form-select form-select-sm">
                        <option value="">-- Sin Subcategoría / General --</option>
                    </select>
                    <small class="text-muted" style="font-size: 11px;">Opcional: Si no aplica una subcategoría específica, se mantiene como General.</small>
                </div>
            </div>

            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-sm btn-info" id="btnGuardarReclasificacion">
                    <i class="fa fa-save me-1"></i> Guardar Reclasificación
                </button>
            </div>
        </form>
    </div>
</div>
