<div class="modal fade" id="modal-gestionar-viaticos" tabindex="-1" aria-labelledby="modalGestionarViaticosLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-gradient-info text-white">
                <h5 class="modal-title text-white" id="modalGestionarViaticosLabel">
                    <i class="fa fa-receipt me-2"></i> Viáticos y Justificaciones Registradas
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border py-2 px-3 mb-3 text-sm">
                    <i class="fa fa-info-circle text-info me-1"></i>
                    Al eliminar un viático erróneo, se descontará del saldo del operador y se cancelará el gasto unificado correspondiente sin afectar bancos.
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" id="tablaViaticosGuardados">
                        <thead class="table-light">
                            <tr>
                                <th>Contenedor</th>
                                <th>Descripción / Concepto</th>
                                <th class="text-end">Monto</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyViaticosGuardados">
                            <tr>
                                <td colspan="4" class="text-center text-muted py-3">Cargando viáticos...</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
