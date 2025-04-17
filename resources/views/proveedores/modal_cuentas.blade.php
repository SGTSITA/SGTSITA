<div class="modal fade" id="cuentasBancariasModal" tabindex="-1" aria-labelledby="cuentasBancariasLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content shadow-lg">
            <div class="modal-header  text-white">
                <h5 class="modal-title" id="cuentasBancariasLabel">Cuentas Bancarias del Proveedor</h5>
                <button type="button" class="btn-close text-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <h6>Proveedor: <span id="cuentasProveedorNombre" class="text-info"></span></h6>

                <div class="table-responsive mt-3">
                    <table class="table table-striped table-hover">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>#</th>
                                <th>Banco</th>
                                <th>Beneficiario</th>
                                <th>Cuenta</th>
                                <th>CLABE</th>
                                <th>Activo</th>
                                <th>Cuenta 1</th>
                                <th>Cuenta 2</th>
                            </tr>
                        </thead>

                        <tbody id="cuentasBancariasBody" class="text-center">
                            <!-- Se llenará dinámicamente con JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-outline-danger btn-xs" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
