<div class="modal fade" id="modalCambiarEstatus" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Cambiar estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <input type="hidden" id="maniobra_id">

                <div class="mb-3">
                    <label class="form-label fw-bold">Estatus</label>
                    <select class="form-select" id="estatus_id" onchange="mostrarDescripcionEstatus(this)">
                        <option value="">Seleccione un estatus</option>
                        @foreach ($estatusManiobras as $estatus)
                            <option value="{{ $estatus->id }}" data-descripcion="{{ $estatus->descripcion }}">
                                {{ $estatus->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mt-2">
                    <small class="text-muted" id="descripcionEstatus">
                        Seleccione un estatus para ver la descripción
                    </small>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nota / Comentario</label>
                    <textarea class="form-control" id="nota_estatus" rows="3"
                        placeholder="Escriba el motivo o comentario del cambio de estatus"></textarea>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="guardarCambioEstatus()">
                    Guardar
                </button>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="modalHistorialEstatus" tabindex="-1">
    <div class="modal-dialog modal-md">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="modalHistorialEstatusTitle">Historial de estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="historialEstatusContenido">
                <div class="text-center text-muted">
                    Cargando historial...
                </div>
            </div>

        </div>
    </div>
</div>
