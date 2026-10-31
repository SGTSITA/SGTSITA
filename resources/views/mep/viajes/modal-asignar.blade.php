<style>
    /* Estilos para el Dropdown de Select2 */
    .select2-container--default .select2-results > .select2-results__options {
        max-height: 160px !important;
    }
    .select2-container--default .select2-results__option {
        padding: 4px 8px !important;
        font-size: 12px !important;
    }
    #viajeModal .select2-container--default .select2-selection--single {
        border-radius: 50rem !important; /* rounded-pill */
        height: 31px !important;
        border: 1px solid #d2d6da !important;
        font-size: 12px !important;
    }
    #viajeModal .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 31px !important;
        padding-left: 12px !important;
        text-transform: uppercase !important;
    }
    #viajeModal .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 31px !important;
        right: 8px !important;
    }
    .select2-container--default .select2-search--dropdown .select2-search__field {
        border: 1px solid #d2d6da !important;
        border-radius: 0.25rem !important;
        padding: 4px 8px !important;
        font-size: 12px !important;
    }
</style>

<!-- Modal Detalles del Viaje -->
<div class="modal fade" id="viajeModal" aria-labelledby="viajeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow rounded-4 border-0">
            <!-- Encabezado -->
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-semibold" id="viajeModalLabel">Resumen del Viaje Seleccionado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <!-- Cuerpo -->
            <div class="modal-body">
                <form id="formPlaneacion">
                    <div class="row">
                        <div class="col-12">
                            <h6 class="d-flex justify-content-between align-items-center cursor-pointer mb-2" data-bs-toggle="collapse" data-bs-target="#collapseOperador" aria-expanded="true" style="cursor: pointer;">
                                <span>
                                    Datos del Operador
                                    <span class="form-text text-muted text-xs d-block ms-1">
                                        La información del operador quedará registrada para que puedas utilizarlo en el futuro.
                                    </span>
                                </span>
                                <i class="fas fa-chevron-down text-xs transition-300"></i>
                            </h6>
                        </div>
                        <input type="hidden" id="cmbProveedor" name="cmbProveedor" value="{{ $proveedorId }}" />

                        <div class="collapse show w-100" id="collapseOperador">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="txtOperador" class="form-label">Nombre</label>
                                    <div class="position-relative w-100">
                                        <input type="text" class="form-control form-control-sm ps-3 pe-5 rounded-pill"
                                            placeholder="Nombre completo del operador..." id="txtOperador"
                                            data-mep-operador="0" />

                                        <div id="sugerenciasOperador"
                                            style="
                                                position:absolute;
                                                top:100%;
                                                left:0;
                                                right:0;
                                                background:white;
                                                border-radius:8px;
                                                box-shadow:0 4px 10px rgba(0,0,0,0.15);
                                                z-index:999;
                                                max-height:200px;
                                                overflow-y:auto;
                                                display:none;
                                            ">
                                        </div>
                                        <!-- Icono convertido en botón -->
                                        <button type="button"
                                            class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2 p-1 rounded-circle"
                                            onclick="buscarOperador(txtOperador.value)">
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label for="txtTelefono" class="form-label">Teléfono</label>
                                    <div class="position-relative w-100" style="max-width: 300px">
                                        <input type="text" class="form-control form-control-sm ps-3 pe-5 rounded-pill"
                                            placeholder="Teléfono del operador..." id="txtTelefono" />
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                            <i class="fas fa-phone"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="horizontal dark mt-4 mb-4" />
                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="d-flex justify-content-between align-items-center cursor-pointer mb-2" data-bs-toggle="collapse" data-bs-target="#collapseUnidad" aria-expanded="true" style="cursor: pointer;">
                                <span>
                                    Datos de la Unidad
                                    <span class="form-text text-muted text-xs d-block ms-1">
                                        La información de la unidad asignada se almacenará para que puedas seleccionarla fácilmente en futuros viajes.
                                    </span>
                                </span>
                                <i class="fas fa-chevron-down text-xs transition-300"></i>
                            </h6>
                        </div>

                        <div class="collapse show w-100" id="collapseUnidad">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label for="txtNumUnidad" class="form-label mb-0">Núm Eco/ Núm Unidad / Identificador</label>
                                        <a href="/equipos-gps/index" target="_blank" class="btn btn-xs btn-outline-primary px-2 py-0" style="font-size: 10px; margin-bottom: 2px;">
                                            <i class="fas fa-plus"></i> Anexar Unidad
                                        </a>
                                    </div>
                                    <div class="position-relative w-100">
                                        <select class="form-select form-select-sm rounded-pill text-uppercase" id="txtNumUnidad" data-mep-unidad="0">
                                            <option value="" disabled selected>Selecciona Unidad...</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="txtPlacas" class="form-label">Placas</label>
                                    <div class="position-relative w-100" style="max-width: 300px">
                                        <input type="text"
                                            class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                            id="txtPlacas" placeholder="Placas..." readonly />
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                            <i class="fas fa-barcode"></i>
                                        </span>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label for="txtSerie" class="form-label">Núm Serie / VIN</label>
                                    <div class="position-relative w-100" style="max-width: 300px">
                                        <input type="text"
                                            class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                            id="txtSerie" placeholder="Serie de la unidad" readonly />
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                            <i class="fas fa-qrcode"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row align-items-end mt-2">
                                <div class="col-md-4">
                                    <label for="selectGPS" class="form-label">Compañia GPS</label>
                                    <div class="position-relative w-100" style="max-width: 300px">
                                        <select id="selectGPS"
                                            class="form-select form-select-sm ps-3 pe-5 rounded-pill text-uppercase" style="pointer-events: none; background: #e9ecef; opacity: 0.8;">
                                            <option value="" disabled selected>Selecciona compañia GPS...</option>
                                            @foreach ($gpsCompanies as $gps)
                                                <option value="{{ $gps->id }}">{{ $gps->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                            <i class="fas fa-satellite-dish"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <label for="txtImei" class="form-label">IMEI</label>
                                    <div class="position-relative w-100" style="max-width: 300px">
                                        <input type="text"
                                            class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                            id="txtImei" placeholder="Imei GPS..." readonly />
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                            <i class="fas fa-microchip"></i>
                                        </span>
                                    </div>
                                </div>
                                <div class="col-md-5 mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div id="gpsStatusUnidad" class="small fw-bold text-muted">
                                            Sin asignar GPS
                                        </div>
                                        <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 btn-actualizar-gps shadow-sm" data-gps-tipo="Unidad" style="font-size:10px; display:none;" id="btnActualizarGPSUnidad">
                                            <i class="fas fa-sync-alt"></i> Actualizar GPS
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-12">
                                    <span class="form-text text-muted text-xs ms-1">
                                        <i class="fas fa-info-circle text-info me-1"></i> Para modificar los datos de esta unidad, realiza la edición desde el <a href="/equipos-gps/index" target="_blank" class="text-primary fw-bold text-decoration-underline">Catálogo de Equipos</a>.
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-2">
                        <div class="col-12 col-md-5">
                            <div class="border rounded-4 px-3 py-3 bg-light d-none w-100 shadow-sm" id="cardGpsMapa">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold small text-dark d-flex align-items-center gap-2">
                                            <i class="fas fa-satellite-dish text-primary"></i>
                                            <span>Monitoreo GPS</span>
                                        </div>
                                        <div id="lblDistanciaEquipos" class="small text-muted mt-1">
                                            <i class="fas fa-info-circle me-1"></i>
                                            Sin datos GPS
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-sm"
                                        id="btnMapaUnidad">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class="horizontal dark mt-4 mb-4" />

                    <div class="row">
                        <div class="col-12">
                            <h6 class="d-flex justify-content-between align-items-center cursor-pointer mb-2" data-bs-toggle="collapse" data-bs-target="#collapseChasis" aria-expanded="true" style="cursor: pointer;">
                                <span>
                                    Datos de Chasis
                                    <span class="form-text text-muted text-xs d-block ms-1">
                                        Selecciona qué chasis deseas consultar.
                                    </span>
                                </span>
                                <i class="fas fa-chevron-down text-xs transition-300"></i>
                            </h6>
                        </div>

                        <div class="collapse show w-100" id="collapseChasis">
                            <ul class="nav nav-tabs mb-3" id="chasisTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="chasisA-tab" data-bs-toggle="tab"
                                        data-bs-target="#chasisA" type="button" role="tab">
                                        Chasis A
                                    </button>
                                </li>

                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="chasisB-tab" data-bs-toggle="tab" data-bs-target="#chasisB"
                                        type="button" role="tab">
                                        Chasis B
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="chasisTabsContent">
                                <!-- ================= CHASIS A ================= -->
                                <div class="tab-pane fade show active" id="chasisA" role="tabpanel">
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label for="txtNumChasisA" class="form-label mb-0">Núm Eco / Núm Chasis / Identificador</label>
                                                <a href="/equipos-gps/index" target="_blank" class="btn btn-xs btn-outline-primary px-2 py-0" style="font-size: 10px; margin-bottom: 2px;">
                                                    <i class="fas fa-plus"></i> Anexar Chasis
                                                </a>
                                            </div>
                                            <div class="position-relative w-100">
                                                <select class="form-select form-select-sm rounded-pill text-uppercase" id="txtNumChasisA" data-mep-unidad="0">
                                                    <option value="" disabled selected>Selecciona Chasis A...</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Placas</label>
                                            <div class="position-relative w-100">
                                                <input type="text"
                                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                                    id="txtPlacasA" placeholder="Placas..." readonly />
                                                <span
                                                    class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                                    <i class="fas fa-barcode"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Num Serie / VIN</label>
                                            <div class="position-relative w-100">
                                                <input type="text"
                                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                                    id="txtSerieChasisA" placeholder="Serie del Chasis" readonly />
                                                <span
                                                    class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                                    <i class="fas fa-qrcode"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label">Compañía GPS</label>
                                            <div class="position-relative w-100">
                                                <select id="selectChasisAGPS"
                                                    class="form-select form-select-sm ps-3 pe-5 rounded-pill text-uppercase" style="pointer-events: none; background: #e9ecef; opacity: 0.8;">
                                                    <option value="" disabled selected>Selecciona compañia GPS...
                                                    </option>
                                                    @foreach ($gpsCompanies as $gps)
                                                        <option value="{{ $gps->id }}">{{ $gps->nombre }}</option>
                                                    @endforeach
                                                </select>
                                                <span
                                                    class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                                    <i class="fas fa-satellite-dish"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">IMEI</label>
                                            <div class="position-relative w-100">
                                                <input type="text"
                                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                                    id="txtImeiChasisA" placeholder="Imei GPS..." readonly />
                                                <span
                                                    class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                                    <i class="fas fa-microchip"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-5 mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div id="gpsStatusChasisA" class="small fw-bold text-muted">
                                                    Sin asignar GPS
                                                </div>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 btn-actualizar-gps shadow-sm" data-gps-tipo="ChasisA" style="font-size:10px; display:none;" id="btnActualizarGPSChasisA">
                                                    <i class="fas fa-sync-alt"></i> Actualizar GPS
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <span class="form-text text-muted text-xs ms-1">
                                                <i class="fas fa-info-circle text-info me-1"></i> Para modificar los datos de este chasis, realiza la edición desde el <a href="/equipos-gps/index" target="_blank" class="text-primary fw-bold text-decoration-underline">Catálogo de Equipos</a>.
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <!-- ================= CHASIS B ================= -->
                                <div class="tab-pane fade" id="chasisB" role="tabpanel">
                                    <div class="row mt-3">
                                        <div class="col-md-4">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label for="txtNumChasisB" class="form-label mb-0">Núm Eco / Núm Chasis / Identificador</label>
                                                <a href="/equipos-gps/index" target="_blank" class="btn btn-xs btn-outline-primary px-2 py-0" style="font-size: 10px; margin-bottom: 2px;">
                                                    <i class="fas fa-plus"></i> Anexar Chasis
                                                </a>
                                            </div>
                                            <div class="position-relative w-100">
                                                <select class="form-select form-select-sm rounded-pill text-uppercase" id="txtNumChasisB" data-mep-unidad="0">
                                                    <option value="" disabled selected>Selecciona Chasis B...</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Placas</label>
                                            <div class="position-relative w-100">
                                                <input type="text"
                                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                                    id="txtPlacasB" placeholder="Placas..." readonly />
                                                <span
                                                    class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                                    <i class="fas fa-barcode"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">Num Serie / VIN</label>
                                            <div class="position-relative w-100">
                                                <input type="text"
                                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                                    id="txtSerieChasisB" placeholder="Serie del Chasis" readonly />
                                                <span
                                                    class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                                    <i class="fas fa-qrcode"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mt-2 align-items-end">
                                        <div class="col-md-4">
                                            <label class="form-label">Compañía GPS</label>
                                            <div class="position-relative w-100">
                                                <select id="selectChasisBGPS"
                                                    class="form-select form-select-sm ps-3 pe-5 rounded-pill text-uppercase" style="pointer-events: none; background: #e9ecef; opacity: 0.8;">
                                                    <option value="" disabled selected>Selecciona compañia GPS...
                                                    </option>
                                                    @foreach ($gpsCompanies as $gps)
                                                        <option value="{{ $gps->id }}">{{ $gps->nombre }}</option>
                                                    @endforeach
                                                </select>
                                                <span
                                                    class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                                    <i class="fas fa-satellite-dish"></i>
                                                </span>
                                            </div>
                                            <input type="hidden" name="txtTipoViaje" id="txtTipoViaje" />
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label">IMEI</label>
                                            <div class="position-relative w-100">
                                                <input type="text"
                                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                                    id="txtImeiChasisB" placeholder="Imei GPS..." readonly />
                                                <span
                                                    class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                                    <i class="fas fa-microchip"></i>
                                                </span>
                                            </div>
                                        </div>

                                        <div class="col-md-5 mb-2">
                                            <div class="d-flex align-items-center gap-2">
                                                <div id="gpsStatusChasisB" class="small fw-bold text-muted">
                                                    Sin asignar GPS
                                                </div>
                                                <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 btn-actualizar-gps shadow-sm" data-gps-tipo="ChasisB" style="font-size:10px; display:none;" id="btnActualizarGPSChasisB">
                                                    <i class="fas fa-sync-alt"></i> Actualizar GPS
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row mt-2">
                                        <div class="col-12">
                                            <span class="form-text text-muted text-xs ms-1">
                                                <i class="fas fa-info-circle text-info me-1"></i> Para modificar los datos de este chasis, realiza la edición desde el <a href="/equipos-gps/index" target="_blank" class="text-primary fw-bold text-decoration-underline">Catálogo de Equipos</a>.
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="horizontal dark mt-4 mb-4" />

                    <div class="mb-2" id="seccionProgramarViaje">
                        <h6 class="mb-1" style="font-size: 14px;">Fecha de viaje</h6>
                        <div class="row">
                            <div class="col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px;">Fecha salida</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    <input class="form-control form-control-sm dateInput" name="txtFechaInicio"
                                        id="txtFechaInicio" placeholder="Fecha inicio" type="text" />
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label mb-1" style="font-size: 11px;">Fecha entrega</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text"><i class="fa fa-calendar"></i></span>
                                    <input class="form-control form-control-sm dateInput" name="txtFechaFinal"
                                        id="txtFechaFinal" placeholder="Fecha fin" type="text" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-3" />

                    <div class="row mt-2">
                        <div class="col-md-12">
                            <h6 class="mb-2" style="font-size: 14px;">Datos del viaje</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0" style="font-size: 12px;">
                                    <tbody>
                                        <tr>
                                            <td class="py-1 ps-0" style="width: 15%;"><strong>Contenedor:</strong></td>
                                            <td class="py-1 text-dark" id="numeroContenedor" style="width: 35%;"></td>
                                            <td class="py-1" style="width: 15%;"><strong>Estatus:</strong></td>
                                            <td class="py-1 text-dark" id="estatusViaje" style="width: 35%;"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-1 ps-0"><strong>Origen:</strong></td>
                                            <td class="py-1 text-dark" id="origenViaje" colspan="3"></td>
                                        </tr>
                                        <tr>
                                            <td class="py-1 ps-0"><strong>Destino:</strong></td>
                                            <td class="py-1 text-dark" id="destinoViaje" colspan="3"></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </form>
                <div id="seccionMapa" class="d-none">

                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h6 class="mb-0">Mapa de Equipos</h6>

                        <button type="button" class="btn btn-sm btn-secondary" id="btnRegresarPlaneacion">
                            <i class="fa fa-arrow-left"></i> Regresar
                        </button>
                    </div>

                    <div id="mapaEquipos" style="height:70vh;"></div>

                </div>
            </div>
            <!-- Pie de modal -->
            <div class="modal-footer bg-light border-top">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle me-1"></i>
                    Cerrar
                </button>

                <button type="button" class="btn bg-gradient-primary" id="btnPlanearViaje">
                    <i class="bi bi-flag me-1"></i>
                    Planear
                </button>

                <button type="button" class="btn bg-gradient-success" id="btnAsignaOperador">
                    <i class="bi bi-save me-1"></i>
                    Guardar
                </button>
            </div>
        </div>
    </div>
</div>
