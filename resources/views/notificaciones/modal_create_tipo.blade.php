<div class="modal fade" id="modalCrearTipoNotificacion" tabindex="-1" aria-labelledby="modalCrearTipoNotificacionLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('notificaciones.tipos.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearTipoNotificacionLabel">
                    <i class="fas fa-tags me-1"></i>
                    Nuevo tipo de notificación
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-light border">
                    <i class="fas fa-info-circle me-1"></i>
                    El tipo define el evento que podrá generar notificaciones, por ejemplo:
                    <strong>gps_separacion</strong>, <strong>cotizacion_creada</strong> o
                    <strong>pago_registrado</strong>.
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Clave</label>
                        <input type="text" name="clave" class="form-control" placeholder="gps_separacion"
                            value="{{ old('clave') }}" required>
                        <small class="text-muted">
                            Usa minúsculas y guion bajo.
                        </small>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" placeholder="Separación GPS"
                            value="{{ old('nombre') }}" required>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Estatus</label>
                        <select name="activo" class="form-control">
                            <option value="1" selected>Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>

                    <div class="col-md-12 form-group mt-3">
                        <label>Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="3"
                            placeholder="Describe cuándo se usará esta notificación">{{ old('descripcion') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="submit" class="btn bg-gradient-info btn-sm">
                    <i class="fas fa-save"></i>
                    Guardar tipo
                </button>
            </div>
        </form>
    </div>
</div>
