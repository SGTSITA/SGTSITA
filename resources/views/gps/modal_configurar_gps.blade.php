<div class="modal fade" id="modal-gps-form" tabindex="-1" role="dialog" aria-labelledby="modal-gps-form" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md" role="document">
        <div class="modal-content">
        <div class="modal-body p-0">
            <div class="card card-plain">
            <div class="card-header pb-0 text-left">
                <h3 class="font-weight-bolder text-info text-gradient" data-gps-company="0" id="gpsCompany">...</h3>
                <p class="mb-0">Configuración del servicio:</p>
            </div>
            <div class="card-body">
               
                <label>App Id / Usuario</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control" id="txtUserName" placeholder="" aria-label="" />
                </div>
                <label>Access Key / Password</label>
                <div class="input-group mb-3">
                    <input type="text" class="form-control moneyformat" id="txtPassword" placeholder="" />
                </div>
                <div class="text-center">
                    <button type="button" id="btnConfigurar" onclick ="guardarConfigGps()" class="btn btn-sm btn-round bg-gradient-info  w-100 mt-4 mb-0">Guardar</button>
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