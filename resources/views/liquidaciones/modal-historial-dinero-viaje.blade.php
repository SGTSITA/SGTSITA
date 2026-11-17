<div class="modal fade" id="modal-historial-dinero" tabindex="-1" aria-labelledby="modalHistorialDineroLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl">
        <div class="modal-content">
            <div class="modal-header bg-gradient-success text-white">
                <h5 class="modal-title text-white" id="modalHistorialDineroLabel">
                    <i class="fa fa-money-bill me-2"></i> Control de Dinero para Viaje
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-light border py-2 px-3 mb-3 text-sm">
                    <i class="fa fa-info-circle text-info me-1"></i>
                    <strong>Control de entregas:</strong> Aquí puedes visualizar todas las entregas de dinero para viaje (inicial de planeación y adicionales). Al cancelar cualquier entrega, el sistema realizará la <strong>devolución bancaria</strong> a la cuenta y neteará automáticamente el saldo a favor del operador.
                </div>

                <div class="table-responsive">
                    <table class="table table-sm table-hover align-middle mb-0" id="tablaDinerosViaje">
                        <thead class="table-light">
                            <tr>
                                <th>Contenedor</th>
                                <th>Fecha</th>
                                <th>Motivo</th>
                                <th>Banco</th>
                                <th class="text-end">Monto</th>
                                <th class="text-center">Tipo</th>
                                <th class="text-center">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="tbodyDinerosViaje">
                            <tr>
                                <td colspan="7" class="text-center text-muted py-3">Cargando registros...</td>
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
