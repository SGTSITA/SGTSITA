<div class="modal fade" id="exampleModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"  aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Registrar Gasto</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('store.gastos_generales') }}" id="frmCrearGasto" enctype="multipart/form-data" role="form">
            @csrf
            <div class="modal-body">
                <div class="row">

                    <div class="col-12 form-group">
                        <label for="name">Descripción</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                            </span>
                            <input name="motivo" id="motivo" type="text" autocomplete="off" class="form-control" required>
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Monto *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px">
                            </span>
                            <input name="monto1" id="monto1" type="text" autocomplete="off" class="form-control fechasDiferir moneyformat" oninput="allowOnlyDecimals(event)" required>
                        </div>
                    </div>

                    <div class="col-6 form-group">
                        <label for="name">Categoría</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/pago-movil.webp') }}" alt="" width="25px">
                            </span>
                            <select class="form-select d-inline-block" id="categoria_movimiento" name="categoria_movimiento" required>
                                @foreach($categorias as $c)
                                <option value="{{$c->id}}">{{$c->categoria}}</option>
                                @endforeach
                               
                            </select>
                        </div>
                    </div>

                    
                    <div class="col-6 form-group">
                        <label for="name">Fecha Movimiento *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                            </span>
                            <input name="fecha_movimiento" id="fecha_movimiento" autocomplete="off" type="text" class="form-control fechas" required>
                        </div>
                    </div>
                    
                    <div class="col-6 form-group">
                        <label for="name">Condición de pago</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/pago-movil.webp') }}" alt="" width="25px">
                            </span>
                            <select class="form-select d-inline-block" id="tipoPago" name="tipoPago" required>
                                
                                <option value="0">Contado</option>
                                <option value="1">Diferido</option>
                              
                               
                            </select>
                        </div>
                    </div>

                    <div class="col-12 collapse" id="seccionDiferido" >
                        <div class="p-3 mb-4 bg-gray-100 border-dashed border-1 border-secondary border-radius-md py-3">
                            <h6 class="mb-1">Configuración de pago diferido</h6>
                            <small class="text-muted mb-3 d-block">
                                Determine el rango de fechas para distribuir el pago en modalidad diferida.
                            </small>

                            <div class="row">
                                <!-- Columna izquierda: Fechas -->
                                <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="fechaInicio" class="form-label">Fecha de inicio</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                        </span>
                                        <input name="txtDiferirFechaInicia" id="txtDiferirFechaInicia" autocomplete="off" type="text" class="form-control fechas fechasDiferir" required>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="fechaFin" class="form-label">Fecha de finalización</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                        </span>
                                        <input name="txtDiferirFechaTermina" id="txtDiferirFechaTermina" autocomplete="off" type="text" class="form-control fechas fechasDiferir" required>
                                    </div>
                                </div>
                                </div>

                                <!-- Columna derecha: Resumen -->
                                <div class="col-md-6">
                                <label class="form-label">Resumen del pago diferido</label>
                                <div class="table-responsive">
                                    <table class="table table-sm table-bordered ms-auto">
                                        <tbody>
                                            <tr>
                                            <th scope="row">Número de periodos</th>
                                            <td id="numPeriodos"><span class="text-dark font-weight-bold ms-2" id="labelDiasPeriodo">0</span></td>
                                            </tr>
                                            <tr>
                                            <th scope="row">Monto por periodo</th>
                                            <td id="montoPeriodo"> <span class="text-dark font-weight-bold ms-2" id="labelGastoDiario">$ 0.00</span></td>
                                            </tr>
                                            <tr>
                                            <th scope="row">Total del gasto</th>
                                            <td id="totalGasto"><span class="text-dark text-lg ms-2 font-weight-bold" id="labelMontoGasto">$0.00</span></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                </div>
                            </div>
                            </div>

                    </div>

                    

                    <div class="col-6 form-group d-none">
                        <label for="name">Fecha Aplicación</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                            </span>
                            <input name="fecha_aplicacion" id="fecha_aplicacion" autocomplete="off" type="text" class="form-control fechas" required>
                        </div>
                    </div>

                    <div class="col-12 form-group">
                        <label for="name">Cuenta Retiro</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/t debito.webp') }}" alt="" width="25px">
                            </span>
                            <select class="form-select d-inline-block" id="id_banco1" name="id_banco1" required>
                                <option value="">Selecciona</option>
                                @foreach ($bancos as $item)
                                    <option value="{{$item->id}}">{{$item->nombre_banco}} - ${{ number_format($item->saldo, 2, '.', ',') }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-link" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn bg-gradient-success">Aceptar</button>
              </div>
        </form>
      </div>
    </div>
  </div>
