<div class="modal fade" id="exampleModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"  aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Realizar pago</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('store.gastos_generales') }}" id="frmCrearGasto" enctype="multipart/form-data" role="form">
            @csrf
            <div class="modal-body">
                
                <div class="row">
                    <div class="col-6">
                  
                      <div class="form-group">
                        <label for="example-text-input" class="form-control-label">Cuenta de retiro</label>
                        <select name="cmbBankOne" id="cmbBankOne" class="form-control">
                         <option value="null">Seleccione banco</option>
                          @foreach ($bancos as $item)
                              <option value="{{$item->id}}">{{$item->nombre_banco}}: ${{number_format($item->saldo,2)}}</option>
                          @endforeach
                        </select>
                      </div>

                      <div class="col-md-9">
                        <label for="cantidad" class="form-label">Pago de préstamo</label>
                        <input type="number" name="montoPagoPrestamo" id="montoPagoPrestamo" class="form-control" placeholder="Ingrese la cantidad" required min="0.01" step="0.01">
                        <div class="invalid-feedback" id="invalid-feedback">No hay prestamos pendientes.</div>
                        <div class="valid-feedback text-sm" style="color:#6c757d !important" id="valid-feedback">
                          $0.00 pendiente despues de esta operación.
                        </div>

                      </div>
                    
                    </div>
                <div class="col-lg-6 col-12 ms-auto">
                  <h6 class="mb-3">Resumen</h6>
                  <div class="d-flex justify-content-between">
                    <span class="mb-2 text-sm">
                      Viajes a pagar:
                    </span>
                    <span class="text-dark font-weight-bold ms-2" id="contadorContenedores">0 de 3</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="mb-2 text-sm">
                      Total sueldo:
                    </span>
                    <span class="text-dark font-weight-bold ms-2" id="sumaSalario">$ 0</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="mb-2 text-sm">
                      Total Dinero Viaje:
                    </span>
                    <span class="text-dark ms-2 font-weight-bold" id ="sumaDineroViaje">$0</span>
                  </div>

                  <div class="d-flex justify-content-between">
                    <span class="mb-2 text-sm">
                      Total Justificado:
                    </span>
                    <span class="text-dark ms-2 font-weight-bold" id ="sumaJustificados">$0</span>
                  </div>
                  <div class="d-flex justify-content-between">
                    <span class="mb-2 text-sm">
                      Pago Prestamos:
                    </span>
                    <span class="text-dark ms-2 font-weight-bold" id ="sumaPrestamos">$0</span>
                  </div>
     
                  <div class="d-flex justify-content-between mt-4">
                    <span class="mb-2 text-lg">
                      Total a pagar:
                    </span>
                    <span class="text-dark text-lg ms-2 font-weight-bold" id="sumaPago">$0</span>
                  </div>
                </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-link text-muted" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-sm bg-gradient-success" id="btnConfirmaPago" > Confirmar pago</button>
              </div>
        </form>
      </div>
    </div>
  </div>
