 <div class="modal fade" id="modalPreview" tabindex="-1">
     <div class="modal-dialog modal-xl modal-dialog-scrollable">
         <div class="modal-content">
             <div class="modal-header">
                 <h5 class="modal-title" id="modalPreviewLabel"> Vista Previa Liquidación </h5> <button type="button"
                     class="btn-close" data-bs-dismiss="modal"></button>
             </div>
             <div class="modal-body"> <!-- RESUMEN -->
                 <div id="previewResumen" class="mb-4"></div>
                 <!-- DETALLE VIAJES -->
                 <div id="sectionViajes" class="mb-4">
                     <h5 id="sectionViajesLabel">Detalle Viajes</h5>
                     <table class="table table-bordered" id="tablaViajes">
                         <thead>
                             <tr>
                                 <th>Contenedores</th>
                                 <th class="text-end">Sueldo</th>
                                 <th class="text-end">Dinero</th>
                                 <th class="text-end">Justificado</th>
                                 <th class="text-end">Monto Pago</th>
                             </tr>
                         </thead>
                         <tbody></tbody>
                     </table>
                 </div> <!-- JUSTIFICADOS -->
                 <div id="sectionJustificados" class="mb-4">
                     <h5>Desglose Justificaciones</h5>
                     <table class="table table-bordered" id="tablaJustificados">
                         <thead>
                             <tr>
                                 <th>Contenedor</th>
                                 <th>Concepto</th>
                                 <th class="text-end">Importe</th>
                             </tr>
                         </thead>
                         <tbody></tbody>
                     </table>
                 </div> <!-- DINERO VIAJE -->
                 <div id="sectionDineroViaje" class="mb-4">
                     <h5>Dinero Viaje</h5>
                     <table class="table table-bordered" id="tablaDineroViaje">
                         <thead>
                             <tr>
                                 <th>Contenedor</th>
                                 <th>Concepto</th>
                                 <th class="text-end">Importe</th>
                                 <th>Fecha</th>
                             </tr>
                         </thead>
                         <tbody></tbody>
                     </table>
                 </div> <!-- DEUDAS -->
                 <div id="sectionDeudas">
                     <h5>Deudas (Préstamos / Adelantos)</h5>
                     <table class="table table-bordered" id="tablaDeudas">
                         <thead>
                             <tr>
                                 <th>Tipo</th>
                                 <th>Fecha</th>
                                 <th class="text-end">Cantidad</th>
                                 <th class="text-end">Pagado</th>
                                 <th class="text-end">Saldo</th>
                             </tr>
                         </thead>
                         <tbody></tbody>
                     </table>
                 </div>
             </div>
             <div class="row">

                 <!-- BANCO -->
                 <div class="col-md-8">
                     <div class="form-group">
                         <label class="form-control-label">Cuenta de retiro</label>
                         <select name="cmbBankOne" id="cmbBankOne" class="form-control">
                             <option value="null">Seleccione banco</option>
                             @foreach ($bancos as $item)
                                 <option value="{{ $item['id'] }}">
                                     {{ $item['display'] }} : ${{ number_format($item['saldo_actual'], 2) }}
                                 </option>
                             @endforeach
                         </select>
                     </div>
                 </div>

                 <!-- FECHA -->
                 <div class="col-md-4">
                     <div class="form-group">
                         <label for="FechaAplicacionPago">Fecha Aplicación</label>
                         <div class="input-group">
                             <span class="input-group-text">
                                 <i class="fa fa-calendar text-danger"></i>
                             </span>
                             <input class="form-control dateInput" name="FechaAplicacionPago" id="FechaAplicacionPago"
                                 placeholder="Fecha Aplicación" type="text" />
                         </div>
                     </div>
                 </div>

             </div>
             <div class="modal-footer">
                 <button id="btnDescargar" class="btn btn-danger"> Descargar PDF </button>
                 <button class="btn btn-success" id="btnConfirmaPagov2"> Confirmar Pago </button> <button
                     class="btn btn-secondary" data-bs-dismiss="modal"> Cerrar </button>
             </div>
         </div>
     </div>
 </div>
