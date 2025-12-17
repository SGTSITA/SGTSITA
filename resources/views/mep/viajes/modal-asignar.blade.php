<!-- Modal Detalles del Viaje -->
<div class="modal fade" id="viajeModal" tabindex="-1" aria-labelledby="viajeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content shadow rounded-4 border-0">
            <!-- Encabezado -->
            <div class="modal-header bg-light border-bottom">
                <h5 class="modal-title fw-semibold" id="viajeModalLabel">Resumen del Viaje Seleccionado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
            </div>
            <!-- Cuerpo -->
            <div class="modal-body">
                <form>
                    <div class="row">
                        <h6>
                            Datos del Operador
                            <span class="form-text text-muted text-xs d-block ms-1">
                                La información del operador quedará registrada para que puedas utilizarlo en el futuro.
                                Si deseas asignarlo nuevamente, solo haz clic en el ícono de búsqueda.
                            </span>
                        </h6>

                        <div class="col-md-6">
                            <label for="operadorSelect" class="form-label">Nombre</label>
                            <div class="position-relative w-100">
                                <input
                                    type="text"
                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill"
                                    placeholder="Nombre completo del operador..."
                                    id="txtOperador"
                                    data-mep-operador="0"
                                />
                                <!-- Icono convertido en botón -->
                                <button
                                    type="button"
                                    class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2 p-1 rounded-circle"
                                    onclick="buscarOperador(txtOperador.value)"
                                >
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="operadorSelect" class="form-label">Teléfono</label>
                            <div class="position-relative w-100" style="max-width: 300px">
                                <input
                                    type="text"
                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill"
                                    placeholder="Teléfono del operador..."
                                    id="txtTelefono"
                                />
                                <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                    <i class="fas fa-phone"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <hr class="horizontal dark mt-4 mb-4" />
                    <div class="row mt-3">
                        <h6>
                            Datos de la Unidad
                            <span class="form-text text-muted text-xs d-block ms-1">
                                La información de la unidad asignada se almacenará para que puedas seleccionarlo
                                fácilmente en futuros viajes.
                            </span>
                        </h6>

                        <div class="col-md-4">
                            <label for="operadorSelect" class="form-label">Núm Eco/ Núm Unidad / Identificador</label>
                            <div class="position-relative w-100">
                                <input
                                    type="text"
                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                    placeholder="Ej. SF001..."
                                    id="txtNumUnidad"
                                    data-mep-unidad="0"
                                />
                                <!-- Icono convertido en botón -->
                                <button
                                    type="button"
                                    class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2 p-1 rounded-circle"
                                    onclick="buscarUnidad(txtNumUnidad.value, 'Unidad')"
                                >
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label for="operadorSelect" class="form-label">Placas</label>
                            <div class="position-relative w-100" style="max-width: 300px">
                                <input
                                    type="text"
                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                    id="txtPlacas"
                                    placeholder="Placas..."
                                />
                                <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                    <i class="fas fa-barcode"></i>
                                </span>
                            </div>
                        </div>

                        <div class="col-md-3">
                            <label for="operadorSelect" class="form-label">Núm Serie / VIN</label>
                            <div class="position-relative w-100" style="max-width: 300px">
                                <input
                                    type="text"
                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                    id="txtSerie"
                                    placeholder="Serie de la unidad"
                                />
                                <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                    <i class="fas fa-qrcode"></i>
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <label for="selectGPS" class="form-label">Compañia GPS</label>
                            <div class="position-relative w-100" style="max-width: 300px">
                                <select
                                    id="selectGPS"
                                    class="form-select form-select-sm ps-3 pe-5 rounded-pill text-uppercase"
                                >
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
                            <label for="operadorSelect" class="form-label">IMEI</label>
                            <div class="position-relative w-100" style="max-width: 300px">
                                <input
                                    type="text"
                                    class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                    id="txtImei"
                                    placeholder="Imei GPS..."
                                />
                                <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                    <i class="fas fa-microchip"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                    <hr class="horizontal dark mt-4 mb-4" />

                    <h6>
                        Datos de Chasis
                        <span class="form-text text-muted text-xs d-block ms-1">
                            Selecciona qué chasis deseas consultar.
                        </span>
                    </h6>

                    <ul class="nav nav-tabs mb-3" id="chasisTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link active"
                                id="chasisA-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#chasisA"
                                type="button"
                                role="tab"
                            >
                                Chasis A
                            </button>
                        </li>

                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link"
                                id="chasisB-tab"
                                data-bs-toggle="tab"
                                data-bs-target="#chasisB"
                                type="button"
                                role="tab"
                            >
                                Chasis B
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="chasisTabsContent">
                        <!-- ================= CHASIS A ================= -->
                        <div class="tab-pane fade show active" id="chasisA" role="tabpanel">
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label class="form-label">Núm Eco / Núm Chasis / Identificador</label>
                                    <div class="position-relative w-100">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                            placeholder="Ej. CH001..."
                                            id="txtNumChasisA"
                                            data-mep-unidad="0"
                                        />
                                        <button
                                            type="button"
                                            class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2 p-1 rounded-circle"
                                            onclick="buscarUnidad(txtNumChasisA.value, 'ChasisA')"
                                        >
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Placas</label>
                                    <div class="position-relative w-100">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                            id="txtPlacasA"
                                            placeholder="Placas..."
                                        />
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                            <i class="fas fa-barcode"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <label class="form-label">Compañía GPS</label>
                                    <div class="position-relative w-100">
                                        <select
                                            id="selectChasisAGPS"
                                            class="form-select form-select-sm ps-3 pe-5 rounded-pill text-uppercase"
                                        >
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
                                    <label class="form-label">IMEI</label>
                                    <div class="position-relative w-100">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                            id="txtImeiChasisA"
                                            placeholder="Imei GPS..."
                                        />
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                            <i class="fas fa-microchip"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- ================= CHASIS B ================= -->
                        <div class="tab-pane fade" id="chasisB" role="tabpanel">
                            <div class="row mt-3">
                                <div class="col-md-4">
                                    <label class="form-label">Núm Eco / Núm Chasis B / Identificador</label>
                                    <div class="position-relative w-100">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                            placeholder="Ej. CH002..."
                                            id="txtNumChasisB"
                                            data-mep-unidad="0"
                                        />
                                        <button
                                            type="button"
                                            class="btn btn-sm position-absolute end-0 top-50 translate-middle-y me-2 p-1 rounded-circle"
                                            onclick="buscarUnidad(txtNumChasisB.value, 'ChasisB')"
                                        >
                                            <i class="fas fa-search"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">Placas</label>
                                    <div class="position-relative w-100">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                            id="txtPlacasB"
                                            placeholder="Placas..."
                                        />
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                            <i class="fas fa-barcode"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <div class="row mt-2">
                                <div class="col-md-4">
                                    <label class="form-label">Compañía GPS</label>
                                    <div class="position-relative w-100">
                                        <select
                                            id="selectChasisBGPS"
                                            class="form-select form-select-sm ps-3 pe-5 rounded-pill text-uppercase"
                                        >
                                            <option value="" disabled selected>Selecciona compañia GPS...</option>
                                            @foreach ($gpsCompanies as $gps)
                                                <option value="{{ $gps->id }}">{{ $gps->nombre }}</option>
                                            @endforeach
                                        </select>
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                            <i class="fas fa-satellite-dish"></i>
                                        </span>
                                    </div>
                                    <input type="hidden" name="txtTipoViaje" id="txtTipoViaje" />
                                </div>

                                <div class="col-md-3">
                                    <label class="form-label">IMEI</label>
                                    <div class="position-relative w-100">
                                        <input
                                            type="text"
                                            class="form-control form-control-sm ps-3 pe-5 rounded-pill text-uppercase"
                                            id="txtImeiChasisB"
                                            placeholder="Imei GPS..."
                                        />
                                        <span class="position-absolute end-0 top-50 translate-middle-y pe-3 text-muted">
                                            <i class="fas fa-microchip"></i>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="horizontal dark mt-4 mb-4" />

                    <div class="mb-4" id="seccionProgramarViaje">
                        <div class="row mt-2">
                            <div class="col-lg-7 col-12 mt-4 mt-lg-0">
                                <h6 class="mb-0">Fecha de viaje</h6>
                                <p class="text-sm">Seleccione rango de fechas para el viaje.</p>

                                <div class="row">
                                    <div class="col-md-6">
                                        <label>Fecha salida</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <div
                                                        class="icon icon-shape bg-gradient-danger text-center border-radius-md mb-2"
                                                    >
                                                        <i class="fa fa-calendar opacity-10"></i>
                                                    </div>
                                                </span>
                                                <input
                                                    class="form-control dateInput"
                                                    name="txtFechaInicio"
                                                    id="txtFechaInicio"
                                                    placeholder="Fecha inicio"
                                                    type="text"
                                                />
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label>Fecha entrega</label>
                                        <div class="form-group">
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <div
                                                        class="icon icon-shape bg-gradient-danger text-center border-radius-md mb-2"
                                                    >
                                                        <i class="fa fa-calendar opacity-10"></i>
                                                    </div>
                                                </span>
                                                <input
                                                    class="form-control dateInput"
                                                    name="txtFechaFinal"
                                                    id="txtFechaFinal"
                                                    placeholder="Fecha fin"
                                                    type="text"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr />

                    <div class="row mt-3">
                        <h6>
                            Datos del viaje
                            <span class="form-text text-muted text-xs d-block ms-1">
                                A continuación, encontrará los datos oficiales asociados al viaje.
                            </span>
                        </h6>
                        <div class="col-md-6">
                            <label class="form-label">Número de Contenedor</label>
                            <p id="numeroContenedor" class="form-control-plaintext text-dark"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha del Viaje</label>
                            <p id="fechaViaje" class="form-control-plaintext text-dark"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Origen</label>
                            <p id="origenViaje" class="form-control-plaintext text-dark"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Destino</label>
                            <p id="destinoViaje" class="form-control-plaintext text-dark"></p>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Estatus</label>
                            <p id="estatusViaje" class="form-control-plaintext text-dark"></p>
                        </div>
                    </div>
                </form>
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
