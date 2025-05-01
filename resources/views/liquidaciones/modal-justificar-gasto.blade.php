<div class="modal fade" id="modal-justificar" tabindex="-1" role="dialog" aria-labelledby="modal-justificar" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
        <div class="modal-body p-0">
            <div class="card card-plain">
            <div class="card-header pb-0 text-left">
                <h3 class="font-weight-bolder text-info text-gradient">Justificar Gasto</h3>
                <p class="mb-0">Por favor introduza la Información solicitada a continuación:</p>
            </div>
            <div class="card-body">
               
                <label>Descripción</label>
                <div class="input-group mb-3">
                    <input type="text" autocomplete="off" class="form-control" id="txtDescripcion" placeholder="Descripción del gasto" aria-label="Descripción del gasto" />
                </div>
                <label>Monto</label>
                <div class="input-group mb-3">
                    <input type="text" autocomplete="off" class="form-control moneyformat" id="txtMonto" placeholder="$ 0.00" aria-label="$ 0.00" oninput="allowOnlyDecimals(event)" />
                </div>
               
                <div class="text-center">
                    <button type="button" id="btnJustificar" onclick ="justificarGasto()" class="btn btn-round bg-gradient-info btn-lg w-100 mt-4 mb-0">Agregar</button>
                </div>
               
            </div>
            <div class="card-footer text-center pt-0 px-lg-2 px-1">
             
            </div>
            </div>
        </div>
        </div>
    </div>
    </div>
</div>