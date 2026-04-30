<style>
    .modal-lg-custom {
        max-width: 900px;
    }

    .modal-title {
        font-size: 1.25rem;
        font-weight: 600;
    }

    .nav-pills .nav-link {
        font-size: 0.9rem;
        padding: 6px 12px;
        color: #333;
        border-radius: 0.5rem;
    }

    .nav-pills .nav-link.active {
        background-color: #354f8e;
        color: #fff;
    }

    .form-group label {
        font-weight: 600;
        font-size: 0.875rem;
    }

    .form-control {
        font-size: 0.875rem;
    }
</style>
<style>
    .form-section {
        padding: 1rem;
        background-color: #f8f9fa;
        border-radius: 0.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid #e0e0e0;
    }

    .form-section h6 {
        font-weight: 600;
        margin-bottom: 1rem;
        color: #354f8e;
    }

    .form-group label {
        font-weight: 500;
        font-size: 0.875rem;
    }

    .form-control {
        font-size: 0.875rem;
    }
</style>

<div class="modal fade" id="equipoModal" tabindex="-1" aria-labelledby="equipoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-lg-custom">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Equipo</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('store.equipos') }}" enctype="multipart/form-data" role="form">
                @csrf
                <input type="hidden" id="tipoActivo" name="tipoActivo" />
                <div class="modal-body">
                    <div class="row">
                        <div class="col-12">
                            <ul class="nav nav-pills mb-3" id="pills-tab" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link active"
                                        id="pills-home-tab"
                                        data-bs-toggle="pill"
                                        data-bs-target="#pills-home"
                                        type="button"
                                        role="tab"
                                        aria-controls="pills-home"
                                        aria-selected="true"
                                    >
                                        Tractos / Camiones
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link"
                                        id="pills-profile-tab"
                                        data-bs-toggle="pill"
                                        data-bs-target="#pills-profile"
                                        type="button"
                                        role="tab"
                                        aria-controls="pills-profile"
                                        aria-selected="false"
                                    >
                                        Chasis Plataforma
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button
                                        class="nav-link"
                                        id="pills-dolys-tab"
                                        data-bs-toggle="pill"
                                        data-bs-target="#pills-dolys"
                                        type="button"
                                        role="tab"
                                        aria-controls="pills-dolys"
                                        aria-selected="false"
                                    >
                                        Dollys
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="pills-tabContent">
                                <!-- Tracto / Camión -->
                                <div
                                    class="tab-pane show active"
                                    id="pills-home"
                                    role="tabpanel"
                                    aria-labelledby="pills-home-tab"
                                    tabindex="0"
                                >
                                    <div class="form-section">
                                        <h6>Datos del Tracto / Camión</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label>Folio *</label>
                                                <input
                                                    name="id_equipo"
                                                    id="id_equipo"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Fecha de alta *</label>
                                                <input
                                                    name="fecha"
                                                    id="fecha"
                                                    type="date"
                                                    class="form-control"
                                                    value="{{ date('Y-m-d') }}"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Año *</label>
                                                <input name="year" id="year" type="number" class="form-control" />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Marca *</label>
                                                <input name="marca" id="marca" type="text" class="form-control" />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Modelo *</label>
                                                <input name="modelo" id="modelo" type="text" class="form-control" />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Motor *</label>
                                                <input name="motor" id="motor" type="text" class="form-control" />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Placas *</label>
                                                <input name="placas" id="placas" type="text" class="form-control" />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Número de Serie *</label>
                                                <input
                                                    name="num_serie"
                                                    id="num_serie"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Acceso *</label>
                                                <input name="acceso" id="acceso" type="text" class="form-control" />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Tarjeta de Circulación *</label>
                                                <input
                                                    name="tarjeta_circulacion"
                                                    id="tarjeta_circulacion"
                                                    type="file"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Póliza de Seguro *</label>
                                                <input
                                                    name="poliza_seguro"
                                                    id="poliza_seguro"
                                                    type="file"
                                                    class="form-control"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Chasis Plataforma -->
                                <div
                                    class="tab-pane"
                                    id="pills-profile"
                                    role="tabpanel"
                                    aria-labelledby="pills-profile-tab"
                                    tabindex="0"
                                >
                                    <div class="form-section">
                                        <h6>Datos del Chasis Plataforma</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label>Folio *</label>
                                                <input
                                                    name="id_equipo_chasis"
                                                    id="id_equipo"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Fecha de alta *</label>
                                                <input
                                                    name="fecha_chasis"
                                                    id="fecha_chasis"
                                                    type="date"
                                                    class="form-control"
                                                    value="{{ date('Y-m-d') }}"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Año *</label>
                                                <input
                                                    name="year_chasis"
                                                    id="year_chasis"
                                                    type="number"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Marca *</label>
                                                <input
                                                    name="marca_chasis"
                                                    id="marca_chasis"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Modelo *</label>
                                                <input
                                                    name="modelo_chasis"
                                                    id="modelo_chasis"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Motor *</label>
                                                <input
                                                    name="motor_chasis"
                                                    id="motor_chasis"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Placas *</label>
                                                <input
                                                    name="placas_chasis"
                                                    id="placas"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Número de Serie *</label>
                                                <input
                                                    name="num_serie_chasis"
                                                    id="num_serie_chasis"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Acceso *</label>
                                                <input
                                                    name="acceso_chasis"
                                                    id="acceso_chasis"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Tarjeta de Circulación *</label>
                                                <input
                                                    name="tarjeta_circulacion_chasis"
                                                    id="tarjeta_circulacion_chasis"
                                                    type="file"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Póliza de Seguro *</label>
                                                <input
                                                    name="poliza_seguro_chasis"
                                                    id="poliza_seguro_chasis"
                                                    type="file"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-12">
                                                <label>Tipo *</label>
                                                <select name="folio" id="folio" class="form-select">
                                                    <option value="">Seleccione una opción</option>
                                                    <option value="B9 40P">B9 40P</option>
                                                    <option value="B10 20P">B10 20P</option>
                                                    <option value="B11 20/40P">B11 20/40P</option>
                                                    <option value="B12 Abatible">B12 Abatible</option>
                                                    <option value="B13 Retractil">B13 Retractil</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Dolys -->
                                <div
                                    class="tab-pane"
                                    id="pills-dolys"
                                    role="tabpanel"
                                    aria-labelledby="pills-dolys-tab"
                                    tabindex="0"
                                >
                                    <div class="form-section">
                                        <h6>Datos del Doly</h6>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label>Folio *</label>
                                                <input
                                                    name="id_equipo_doly"
                                                    id="id_equipo"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Fecha de alta *</label>
                                                <input
                                                    name="fecha_doly"
                                                    id="fecha_doly"
                                                    type="date"
                                                    class="form-control"
                                                    value="{{ date('Y-m-d') }}"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Año *</label>
                                                <input
                                                    name="year_doly"
                                                    id="year_doly"
                                                    type="number"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Marca *</label>
                                                <input
                                                    name="marca_doly"
                                                    id="marca_doly"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Modelo *</label>
                                                <input
                                                    name="modelo_doly"
                                                    id="modelo_doly"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Placas *</label>
                                                <input
                                                    name="placas_doly"
                                                    id="placas"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Número de Serie *</label>
                                                <input
                                                    name="num_serie_doly"
                                                    id="num_serie_doly"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Acceso *</label>
                                                <input
                                                    name="acceso_doly"
                                                    id="acceso_doly"
                                                    type="text"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Tarjeta de Circulación *</label>
                                                <input
                                                    name="tarjeta_circulacion_doly"
                                                    id="tarjeta_circulacion_doly"
                                                    type="file"
                                                    class="form-control"
                                                />
                                            </div>
                                            <div class="col-md-6">
                                                <label>Póliza de Seguro *</label>
                                                <input
                                                    name="poliza_seguro_doly"
                                                    id="poliza_seguro_doly"
                                                    type="file"
                                                    class="form-control"
                                                />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn bg-gradient-info">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>
