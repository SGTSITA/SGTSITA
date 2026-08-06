<!-- 🔹 MODAL PARA AGREGAR CUENTA BANCARIA -->
<div class="modal fade" id="modalCrearCuenta" tabindex="-1" aria-labelledby="modalCrearCuentaLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content shadow-lg">
            <div class="modal-header text-white">
                <h5 class="modal-title" id="modalCrearCuentaLabel">Agregar Cuenta Bancaria</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form id="formAgregarCuenta" method="POST">
                @csrf

                <div class="modal-body">
                    <!-- 🔹 Mostrar Nombre del Proveedor -->
                    <h6>
                        Proveedor:
                        <span id="nombreProveedorCuenta" class="text-info fw-bold"></span>
                    </h6>

                    <input type="hidden" id="idProveedorCuenta" name="id_proveedores" />

                    <div class="mb-3">
                        <label for="nombre_banco" class="form-label">Banco *</label>
                        <input type="text" class="form-control" id="nombre_banco" name="nombre_banco" required />
                    </div>

                    <div class="mb-3">
                        <label for="nombre_beneficiario" class="form-label">Nombre del Beneficiario *</label>
                        <input
                            type="text"
                            class="form-control"
                            id="nombre_beneficiario"
                            name="nombre_beneficiario"
                            required
                        />
                    </div>

                    <div class="mb-3">
                        <label for="cuenta_bancaria" class="form-label">Número de Cuenta *</label>
                        <input type="text" class="form-control" id="cuenta_bancaria" name="cuenta_bancaria" required />
                    </div>

                    <div class="mb-3">
                        <label for="cuenta_clabe" class="form-label">Cuenta CLABE *</label>
                        <input type="text" class="form-control" id="cuenta_clabe" name="cuenta_clabe" required />
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-danger btn-xs" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="submit" class="btn btn-outline-info btn-xs">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
