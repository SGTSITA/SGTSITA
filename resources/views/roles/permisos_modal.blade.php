{{-- Modal de editar permiso --}}
<div
    class="modal fade"
    id="editarPermisoModal"
    tabindex="-1"
    aria-labelledby="editarPermisoModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4">
            <div class="modal-header">
                <h5 class="modal-title fw-bold text-primary" id="editarPermisoModalLabel">
                    <i class="fas fa-edit me-2"></i>
                    Editar Permiso
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarPermiso">
                    <input type="hidden" id="inputEditId" />
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Módulo</label>
                            <input type="text" class="form-control" id="inputModulo" placeholder="Ej. usuarios" />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Permiso</label>
                            <input type="text" class="form-control" id="inputNombrePermiso" readonly />
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Nombre</label>
                            <input
                                type="text"
                                class="form-control"
                                id="inputDescripcionPermiso"
                                placeholder="Opcional"
                            />
                        </div>
                        <div class="mb-3">
                            <label for="selectSistema" class="form-label">Sistema</label>
                            <select id="selectSistema" class="form-select">
                                <option value="">-- Seleccionar --</option>
                                <option value="SGT">SGT</option>
                                <option value="MEC">MEC</option>
                                <option value="MEP">MEP</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary rounded-pill" data-bs-dismiss="modal">Cancelar</button>

                <button class="btn btn-primary rounded-pill" type="button" id="btnGuardarEdicionPermiso">
                    <i class="fas fa-save me-1"></i>
                    Guardar cambios
                </button>
            </div>
        </div>
    </div>
</div>
