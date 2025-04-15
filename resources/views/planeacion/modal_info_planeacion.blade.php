<div class="modal fade" id="viajeModal" tabindex="-1" role="dialog" aria-labelledby="viajeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
    <div class="modal-content">
      <div class="modal-header d-flex justify-content-between align-items-center">
        <h5 class="modal-title" id="viajeModalLabel">
            Detalle planeación</h5>
            <p class="text-sm"><span class="badge badge-sm" id="tipoViajeSpan">Viaje Subcontratado</span></p>
       
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h6>Núm Contenedor: <span id="numContenedorSpan"></span></h6>
                <p class="text-sm mb-0">
                Fecha salida. <b id="fechaSalida"></b>
                </p>
                <p class="text-sm">
                Fecha entrega: <b id="fechaEntrega"></b>
                </p>
            </div>
            
        </div>
        <div class="">
           <h6 class="mb-0">Información del trasportista</h6>
           <div>
                <h6>Nombre: <span id="nombreTransportista"></span></h6>
               
            </div>
           
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm bg-gradient-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-sm bg-gradient-danger" data-bs-dismiss="modal">Deshacer planeación</button>
        <button type="button" class="btn btn-sm bg-gradient-success">Finalizar viaje</button>
      </div>
    </div>
  </div>
</div>