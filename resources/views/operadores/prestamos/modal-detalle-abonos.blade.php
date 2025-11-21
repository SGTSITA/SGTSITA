<!-- Modal -->
<div class="modal fade" id="detallePrestamoModal" tabindex="-1" aria-labelledby="detallePrestamoLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="detallePrestamoLabel">Detalle del Préstamo</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
       <p><strong>Operador:</strong> <span id="nombreOperador"></span></p>
        <div class="mb-3">
          <strong>Total del Préstamo: </strong> <span id="totalPrestamo">$0.00</span>
        </div>

        <div class="table-responsive">
          <table class="table table-bordered" id="movimientosPrestamoTable">
            <thead>
              <tr>
                <th>Tipo</th>
                <th>Fecha</th>
                <th>Monto</th>
                <th>Referencia</th>
              </tr>
            </thead>
            <tbody></tbody>
            <tfoot>
              <tr>
                <th colspan="2">Deuda Actual</th>
                <th colspan="2" id="deudaActual">$0.00</th>
              </tr>
            </tfoot>
          </table>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" id="exportarPDF">Generar PDF</button>
      </div>
    </div>
  </div>
</div>
