<div class="modal fade" id="modalCrearReglaNotificacion" tabindex="-1" aria-labelledby="modalCrearReglaNotificacionLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl">
        <form method="POST" action="{{ route('notificaciones.reglas.store') }}" class="modal-content">
            @csrf

            <div class="modal-header">
                <h5 class="modal-title" id="modalCrearReglaNotificacionLabel">
                    <i class="fas fa-sliders-h me-1"></i>
                    Nueva regla de notificación
                </h5>

                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-light border">
                    <i class="fas fa-info-circle me-1"></i>
                    La regla define para qué empresa aplica un tipo de notificación y a qué grupos o usuarios se
                    enviará.
                </div>

                <div class="row">
                    <div class="col-md-4 form-group">
                        <label>Tipo de notificación</label>
                        <select name="notificacion_tipo_id" class="form-control" required>
                            <option value="">Seleccione un tipo</option>
                            @foreach ($tiposSelect as $tipo)
                                <option value="{{ $tipo->id }}">
                                    {{ $tipo->id }} - {{ $tipo->nombre }} - {{ $tipo->clave }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Empresa</label>
                        <select name="empresa_id" class="form-control">
                            <option value="">Global / Todas las empresas</option>
                            @foreach ($empresasSelect as $empresa)
                                <option value="{{ $empresa->id }}">
                                    {{ $empresa->nombre ?? ($empresa->razon_social ?? ($empresa->empresa ?? 'Empresa #' . $empresa->id)) }}
                                </option>
                            @endforeach
                        </select>
                        <small class="text-muted">
                            Si queda vacío, la regla aplica de forma global.
                        </small>
                    </div>

                    <div class="col-md-4 form-group">
                        <label>Estatus</label>
                        <select name="activo" class="form-control">
                            <option value="1" selected>Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </div>

                <hr>

                <div class="row">
                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notificar_empresa" value="1"
                                    id="notificar_empresa_create">
                                <label class="form-check-label fw-bold" for="notificar_empresa_create">
                                    Notificar empresa
                                </label>
                            </div>
                            <small class="text-muted">
                                Para usuarios internos o responsables de la empresa.
                            </small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notificar_cliente" value="1"
                                    id="notificar_cliente_create">
                                <label class="form-check-label fw-bold" for="notificar_cliente_create">
                                    Notificar cliente
                                </label>
                            </div>
                            <small class="text-muted">
                                Para usuarios relacionados con el cliente del movimiento.
                            </small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="notificar_proveedor"
                                    value="1" id="notificar_proveedor_create">
                                <label class="form-check-label fw-bold" for="notificar_proveedor_create">
                                    Notificar proveedor
                                </label>
                            </div>
                            <small class="text-muted">
                                Para usuarios relacionados con proveedor, línea o transportista.
                            </small>
                        </div>
                    </div>

                    <div class="col-md-3">
                        <div class="border rounded-4 p-3 bg-light h-100">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="incluir_url_documento"
                                    value="1" id="incluir_url_documento">
                                <label class="form-check-label fw-bold" for="incluir_url_documento_create">
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

                <div class="alert alert-warning mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle me-1"></i>
                    Aunque marques estos grupos, también puedes asignar usuarios específicos a la regla desde la sección
                    de usuarios.
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">
                    Cancelar
                </button>

                <button type="submit" class="btn bg-gradient-primary btn-sm">
                    <i class="fas fa-save"></i>
                    Guardar regla
                </button>
            </div>
        </form>
    </div>
</div>
