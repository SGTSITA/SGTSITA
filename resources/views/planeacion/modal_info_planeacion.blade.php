<div class="modal modal-lg fade" id="viajeModal" tabindex="-1" role="dialog" aria-labelledby="viajeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-between align-items-center">
        <h5 class="modal-title" id="viajeModalLabel">
            Detalle planeación</h5>
            <p class="text-sm">
              <span class="badge badge-sm" id="tipoViajeSpan">
                Viaje Subcontratado
              </span>
              
            </p>
       
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center">
          
            <div>
                <h6>Núm Contenedor: <span id="numContenedorSpan"></span></h6>
                <p class="text-sm mb-0">
                Fecha salida. <b id="fechaSalida"></b>
                </p>
                <p class="text-sm mb-0">
                Fecha entrega: <b id="fechaEntrega"></b>
                </p>
                <p class="text-sm mb-0">
                Origen: <b id="origen"></b>
                </p>
                <p class="text-sm mb-0">
                Destino: <b id="destino"></b>
                </p>
            </div>

            

            
            
        </div>
        <div class="">
           <h6 class="mt-3">Información del Cliente</h6>
           <div>
               
                <p class="text-sm mb-0">
                Nombre Cliente: <b id="nombreCliente"></b>
                </p>
                <p class="text-sm mb-0">
                Sub Cliente: <b id="nombreSubcliente"></b>
                </p>
            </div>
           
        </div>
        <div class="">
           <h6 class="mt-3">Información del transportista</h6>
           <div>
               
                <p class="text-sm">
                Nombre: <b id="nombreTransportista"></b>
                </p>
            </div>
           
        </div>
        <div class="">
           <h6 class="mt-3">
              Documentos
            </h6>
            <div class="text-center d-none" id="cima-label">
              <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                <h6 class="text-primary mb-0">Entregar vacío:</h6>
                <h4 class="font-weight-bolder"><span class="small" id="currentBalance">CIMA</span></h4>
              </div>
            </div>
           <div>
            <table width="100%" style="width: 100%; table-layout: fixed; text-align: center; border-collapse: collapse;">
              <thead>
                <tr>
                  <th>Boleta de Liberación</th>
                  <th>Carta Porte</th>
                  <th>DODA</th>
                  <th>Boleta Vacío</th>
                  <th>EIR</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="documentos" id="boleta_liberacion"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                  <td class="documentos" id="doc_ccp"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                  <td class="documentos" id="doda"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                  <td class="documentos" id="boleta_vacio"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                  <td class="documentos" id="doc_eir"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                </tr>
               
              </tbody>
              
            </table>
               
            </div>
           
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm bg-gradient-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-sm bg-gradient-danger" id="btnDeshacer">Deshacer planeación</button>
        <button type="button" class="btn btn-sm bg-gradient-success" id="btnFinalizar">Finalizar viaje</button>
      </div>
    </div>
  </div>
</div>