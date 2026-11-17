<div class="modal fade" id="modalAsignarUsuarioRegla" tabindex="-1" aria-labelledby="modalAsignarUsuarioReglaLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <form method="POST" action="{{ route('notificaciones.reglas.usuarios.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="modalAsignarUsuarioReglaLabel">
                    <i class="fas fa-user-plus me-1"></i>
                    Asignar usuario a regla
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-light border">
                    <i class="fas fa-info-circle me-1"></i>
                    Selecciona una regla y el usuario que recibirá esa notificación.
                </div>

                <div class="row">
                    <div class="col-md-6 form-group">
                        <label>Regla</label>
                        <select name="notificacion_regla_id" class="form-control" required>
                            <option value="">Seleccione una regla</option>

                            @foreach ($reglas as $regla)
                                <option value="{{ $regla->id }}">
                                    {{ $regla->tipo->nombre ?? 'Sin tipo' }}
                                    /
                                    {{ $regla->empresa->nombre ?? ($regla->empresa->razon_social ?? 'Global') }}
                                    -
                                    Regla #{{ $regla->id }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6 form-group">
                        <label>Usuario</label>
                        <select name="user_id" class="form-control" required>
                            <option value="">Seleccione un usuario</option>

                            @foreach ($usuariosSelect as $usuario)
                                <option value="{{ $usuario->id }}">
                                    {{ $usuario->name }}
                                    @if (!empty($usuario->email))
                                        - {{ $usuario->email }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Si el usuario ya está asignado a la regla, la base de datos evitará duplicados por la llave única.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="submit" class="btn bg-gradient-success btn-sm">
                    <i class="fas fa-save"></i>
                    Asignar usuario
                </button>
            </div>
        </form>
    </div>
</div>
