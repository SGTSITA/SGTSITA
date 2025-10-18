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
           <h6 class="mt-3">Información del Proveedor y Operador</h6>
           <div>
               
                <p class="text-sm">
                Proveedor: <b id="nombreProveedor"></b>
                </p>

                {{-- <p class="text-sm">
                Transportista: <b id="nombreTransportista"></b>
                </p> --}}

                 <p class="text-sm">
                Contacto Entrega: <b id="ContactoEntrega"></b>
                </p>

                <p class="text-sm">
                Operador: <b id="nombreOperador"></b>
                </p>

                <p class="text-sm">
                Telefono: <b id="telefonoOperador"></b>
                </p>
            </div>
           
        </div>
          <div class="">
           <h6 class="mt-3">Información Unidad (camion y chasis)</h6>
           <div>
               
                <p class="text-sm">
                Camion: <b id="id_equipo_camion"></b>

                </p>
                 <p class="text-sm">
                Placas: <b id="placas_camion"></b>

                </p>
                 <p class="text-sm">
                Marca: <b id="marca_camion"></b>

                </p>
                     <p class="text-sm">
                IMEI: <b id="imei_camion"></b>

                </p>

                <p class="text-sm">
                Chasis: <b id="id_equipo_chasis"></b>

                </p>

                <p class="text-sm">
                IMEI Chasis: <b id="imei_chasis"></b>

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
                  <th>Formato CCP</th>
                  <th>DODA</th>
                  <th>Boleta Vacío</th>
                  
                  <th>Carta Porte XML</th>
                  <th>Carta Porte PDF</th>
                  <th>EIR</th>
                </tr>
              </thead>
              <tbody>
                <tr>
                  <td class="documentos" id="boleta_liberacion"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                  <td class="documentos" id="doc_ccp"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                  <td class="documentos" id="doda"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                  <td class="documentos" id="boleta_vacio"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                  <td class="documentos" id="carta_porte_xml"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                  <td class="documentos" id="carta_porte"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                  <td class="documentos" id="doc_eir"><i class="fas fa-circle-xmark text-secondary fa-lg"></i></td>
                </tr>
               
              </tbody>
              
            </table>
               
            </div>
           
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-sm bg-gradient-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-sm btn-success"  title="Rastrear contenedor" id="btnRastreo"><i class="fa fa-shipping-fast"></i> Rastreo</button>
        <button type="button" class="btn btn-sm bg-gradient-danger" id="btnDeshacer">Deshacer planeación</button>
        <button type="button" class="btn btn-sm bg-gradient-success" id="btnFinalizar">Finalizar viaje</button>
      </div>
    </div>
  </div>
</div>