<div class="modal fade" id="modal-justificar-multiple" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content" id="content-justificar">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Justificar Gastos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>

      <div class="modal-body">
        <div id="gridJustificar"></div>
      </div>

      <div class="modal-footer">
       <div class="d-flex justify-content-end gap-2 mt-3">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
            Cancelar
          </button>

          <button type="button" id="btnAddRow" class="btn btn-outline-primary">
            + Agregar fila
          </button>

          <button type="button" id="btnLimpiarTabla" class="btn btn-outline-danger">
            Limpiar tabla
          </button>

          <button type="button" id="btnGuardarJustificacion" class="btn btn-success" onclick ="justificarGastoMultiple()">
            Guardar
          </button>
        </div>
      </div>
    </div>
  </div>
</div>