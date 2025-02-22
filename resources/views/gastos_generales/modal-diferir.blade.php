<div class="modal fade" id="modalDiferir" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"  aria-labelledby="modalDiferirLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Diferir Gasto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        
            
            <div class="modal-body">
                
                <div class="row">
                    <div class="col-6">
                  
                      <div class="form-group">
                        <h6 for="example-text-input" class="form-control-label" id="labelDescripcionGasto">...</h6>
                      </div>

                      <div class="col-12">
                           <div class="col-12 form-group">
                              <label for="name">Inicio periodo</label>
                              <div class="input-group mb-3">
                                  <span class="input-group-text" id="basic-addon1">
                                      <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                  </span>
                                  <input name="txtDiferirFechaInicia" id="txtDiferirFechaInicia" autocomplete="off" type="text" class="form-control fechas fechasDiferir" required>
                              </div>
                          </div>

                          <div class="col-12 form-group">
                              <label for="name">Final Periodo</label>
                              <div class="input-group mb-3">
                                  <span class="input-group-text" id="basic-addon1">
                                      <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                  </span>
                                  <input name="txtDiferirFechaTermina" id="txtDiferirFechaTermina" autocomplete="off" type="text" class="form-control fechas fechasDiferir" required>
                              </div>
                          </div>
                      </div>
                    
                    </div>
                    
                <div class="col-lg-4 col-12 ms-auto">
                  <h6 class="mb-3">Resumen</h6>
                  <div class="d-flex justify-content-between">
                    <span class="mb-2 text-sm">
                      Días periodo:
                    </span>
                    <span class="text-dark font-weight-bold ms-2" id="labelDiasPeriodo">0</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="mb-2 text-sm">
                      Gasto por día:
                    </span>
                    <span class="text-dark font-weight-bold ms-2" id="labelGastoDiario">$ 0</span>
                  </div>
                  <div class="d-flex justify-content-between mt-4">
                    <span class="mb-2 text-lg">
                      Monto Gasto:
                    </span>
                    <span class="text-dark text-lg ms-2 font-weight-bold" id="labelMontoGasto">$0</span>
                  </div>
                </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-link text-muted" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm bg-gradient-success" id="btnConfirmacion" > 
                  Diferir pago
                </button>
              </div>
        
      </div>
    </div>
  </div>
