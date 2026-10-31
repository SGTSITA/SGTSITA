  {{-- Modal Crear / Editar --}}
  <div class="modal fade" id="modalBanco" tabindex="-1" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content rounded-4">
              <div class="modal-header">
                  <h5 class="modal-title" id="modalBancoTitulo">Nuevo banco</h5>
                  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
              </div>

              <form id="formBanco">
                  @csrf

                  <input type="hidden" id="banco_id" name="banco_id">

                  <div class="modal-body">
                      <div class="mb-3">
                          <label class="form-label">Nombre del banco</label>
                          <input type="text" name="nombre" id="nombre" class="form-control"
                              placeholder="Ej. BBVA, Santander, Banorte" required>
                          <div class="invalid-feedback" id="error_nombre"></div>
                      </div>

                      <div class="mb-3">
                          <label class="form-label">Clave</label>
                          <input type="text" name="clave" id="clave" class="form-control"
                              placeholder="Ej. 012">
                          <div class="invalid-feedback" id="error_clave"></div>
                      </div>

                      <div class="form-check form-switch">
                          <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1"
                              checked>
                          <label class="form-check-label" for="activo">
                              Activo
                          </label>
                      </div>
                  </div>

                  <div class="modal-footer">
                      <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                          Cancelar
                      </button>

                      <button type="submit" class="btn scb-btn-primary">
                          <i class="fas fa-save me-1"></i>
                          Guardar
                      </button>
                  </div>
              </form>
          </div>
      </div>
  </div>
