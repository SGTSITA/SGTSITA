<div class="modal fade" id="editProveedorModal" tabindex="-1" aria-labelledby="editProveedorLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header  text-white">
                <h5 class="modal-title">Editar Proveedor</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formEditarProveedor">
                @csrf
                @method('PATCH')

                <input type="hidden" id="edit_id" name="id">

                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_nombre" name="nombre" value required>
                                <label for="edit_nombre">Nombre Completo *</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="email" class="form-control" id="edit_correo" name="correo" required>
                                <label for="edit_correo">Correo Electrónico *</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="tel" class="form-control" id="edit_telefono" name="telefono" required>
                                <label for="edit_telefono">Teléfono *</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_direccion" name="direccion">
                                <label for="edit_direccion">Dirección</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_regimen_fiscal"
                                    name="regimen_fiscal">
                                <label for="edit_regimen_fiscal">Régimen Fiscal</label>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="form-floating">
                                <input type="text" class="form-control" id="edit_rfc" name="rfc">
                                <label for="edit_rfc">RFC</label>
                            </div>
                        </div>

                        <div class="col-6">
                            <div class="form-floating">
                                <select class="form-select" id="edit_tipo" name="tipo">
                                    <option value="servicio mecánico">Servicio Mecánico</option>
                                    <option value="servicio de burreo">Servicio de Burreo</option>
                                    <option value="servicio de viaje">Servicio de Viaje</option>
                                    <option value="servicio de patio">Servicio de Patio</option>
                                    <option value="Otro">Otro</option>
                                </select>
                                <label for="edit_tipo">Tipo de Servicio *</label>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="form-floating">
                                <select class="form-select" id="edit_tipo_viaje" name="tipo_viaje">
                                        @foreach($tipoViaje as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                </select>
                                <label for="edit_tipo_viaje">Tipo de Viaje *</label>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger btn-xs"
                        data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-outline-info btn-xs">Actualizar</button>

                </div>
            </form>
        </div>
    </div>
</div>
