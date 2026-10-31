<div class="modal fade" id="modalEditarTipoNotificacion{{ $tipo->id }}" tabindex="-1"
    aria-labelledby="modalEditarTipoNotificacionLabel{{ $tipo->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('notificaciones.tipos.update', $tipo->id) }}" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarTipoNotificacionLabel{{ $tipo->id }}">
                    <i class="fas fa-tags me-1"></i>
                    Editar tipo de notificación
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Clave</label>
                        <input type="text" name="clave" class="form-control"
                            value="{{ old('clave', $tipo->clave) }}" required>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control"
                            value="{{ old('nombre', $tipo->nombre) }}" required>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Estatus</label>
                        <select name="activo" class="form-control">
                            <option value="1" @selected(old('activo', $tipo->activo) == 1)>Activo</option>
                            <option value="0" @selected(old('activo', $tipo->activo) == 0)>Inactivo</option>
                        </select>
                    </div>

                    <div class="col-md-12 form-group mt-3">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3">{{ old('descripcion', $tipo->descripcion) }}</textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="submit" class="btn bg-gradient-info btn-sm">
                    <i class="fas fa-save"></i>
                    Actualizar tipo
                </button>
            </div>
        </form>
    </div>
</div>
