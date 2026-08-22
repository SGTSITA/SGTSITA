<div class="modal fade" id="modalEditarReglaNotificacion{{ $regla->id }}" tabindex="-1"
    aria-labelledby="modalEditarReglaNotificacionLabel{{ $regla->id }}" aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="{{ route('notificaciones.reglas.update', $regla->id) }}" class="modal-content">
            @csrf
            @method('PUT')

            <div class="modal-header">
                <h5 class="modal-title" id="modalEditarReglaNotificacionLabel{{ $regla->id }}">
                    <i class="fas fa-sliders-h me-1"></i>
                    Editar regla de notificación
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Tipo de notificación</label>
                        <select name="notificacion_tipo_id" class="form-control" required>
                            <option value="">Seleccione un tipo</option>

                            @foreach ($tiposSelect as $tipo)
                                <option value="{{ $tipo->id }}" @selected(old('notificacion_tipo_id', $regla->notificacion_tipo_id) == $tipo->id)>
                                    {{ $tipo->nombre }} - {{ $tipo->clave }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Empresa</label>
                        <select name="empresa_id" class="form-control">
                            <option value="">Global / Todas las empresas</option>

                            @foreach ($empresasSelect as $empresa)
                                <option value="{{ $empresa->id }}" @selected(old('empresa_id', $regla->empresa_id) == $empresa->id)>
                                    {{ $empresa->nombre ?? ($empresa->razon_social ?? ($empresa->empresa ?? 'Empresa #' . $empresa->id)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Estatus</label>
                        <select name="activo" class="form-control">
                            <option value="1" @selected(old('activo', $regla->activo) == 1)>Activo</option>
                            <option value="0" @selected(old('activo', $regla->activo) == 0)>Inactivo</option>
                        </select>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notificar_empresa" value="1"
                                    id="notificar_empresa_edit_{{ $regla->id }}" @checked(old('notificar_empresa', $regla->notificar_empresa))>
                                <label class="form-check-label fw-bold"
                                    for="notificar_empresa_edit_{{ $regla->id }}">
                                    Notificar empresa
                                </label>
                            </div>
                            <small class="text-muted">
                                Usuarios internos o responsables de empresa.
                            </small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notificar_cliente" value="1"
                                    id="notificar_cliente_edit_{{ $regla->id }}" @checked(old('notificar_cliente', $regla->notificar_cliente))>
                                <label class="form-check-label fw-bold"
                                    for="notificar_cliente_edit_{{ $regla->id }}">
                                    Notificar cliente
                                </label>
                            </div>
                            <small class="text-muted">
                                Usuarios relacionados con cliente.
                            </small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notificar_proveedor"
                                    value="1" id="notificar_proveedor_edit_{{ $regla->id }}"
                                    @checked(old('notificar_proveedor', $regla->notificar_proveedor))>
                                <label class="form-check-label fw-bold"
                                    for="notificar_proveedor_edit_{{ $regla->id }}">
                                    Notificar proveedor
                                </label>
                            </div>
                            <small class="text-muted">
                                Usuarios relacionados con proveedor.
                            </small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="incluir_url_documento"
                                    value="1" id="incluir_url_documento_edit_{{ $regla->id }}"
                                    @checked(old('incluir_url_documento', $regla->incluir_url_documento))>
                                <label class="form-check-label fw-bold"
                                    for="incluir_url_documento_edit_{{ $regla->id }}">
                                    Incluir url documento al enviar notificacion
                                </label>
                            </div>
                            <small class="text-muted">
                                En las notificaciones se adjunta la url del docuemento si esta habilitado, si no solo
                                llega la notificacion.
                            </small>
                        </div>
                    </div>
                </div>

                @if ($regla->usuarios && $regla->usuarios->count() > 0)
                    <hr>

                    <label>Usuarios asignados actualmente</label>

                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($regla->usuarios as $usuario)
                            <span class="badge bg-gradient-secondary">
                                {{ $usuario->name }}
                            </span>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="submit" class="btn bg-gradient-primary btn-sm">
                    <i class="fas fa-save"></i>
                    Actualizar regla
                </button>
            </div>
        </form>
    </div>
</div>
