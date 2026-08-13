@extends('layouts.app')

@section('template_title')
    Socios (Socios de Negocios)
@endsection

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card mb-4">
                <div class="card-header pb-0 d-flex justify-content-between align-items-center">
                    <div>
                        <h5 class="mb-0">Control de Socios de Negocios</h5>
                        <p class="text-sm text-muted mb-0">Gestión de socios, asignación de utilidades por unidad y reportes
                            financieros.</p>
                    </div>
                </div>

                <div class="card-body">
                    <ul class="nav nav-tabs mb-3" id="sociosTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="config-tab" data-bs-toggle="tab"
                                data-bs-target="#config-content" type="button" role="tab"
                                aria-controls="config-content" aria-selected="true">
                                <i class="fas fa-cogs me-1"></i> 1. Catálogo y Acuerdos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="calculo-tab" data-bs-toggle="tab" data-bs-target="#calculo-content"
                                type="button" role="tab" aria-controls="calculo-content" aria-selected="false">
                                <i class="fas fa-chart-line me-1"></i> 2. Cálculo de Periodo
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pagos-tab" data-bs-toggle="tab" data-bs-target="#pagos-content"
                                type="button" role="tab" aria-controls="pagos-content" aria-selected="false">
                                <i class="fas fa-wallet me-1"></i> 3. Historial de Pagos
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="cortes-tab" data-bs-toggle="tab" data-bs-target="#cortes-content"
                                type="button" role="tab" aria-controls="cortes-content" aria-selected="false">
                                <i class="fas fa-history me-1"></i> 4. Historial de Cortes
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content" id="sociosTabsContent">
                        <!-- PESTAÑA 1: Configuración de Socios y Acuerdos -->
                        <div class="tab-pane fade show active" id="config-content" role="tabpanel"
                            aria-labelledby="config-tab">
                            <div class="row">
                                <div class="col-lg-6 col-12 mb-4">
                                    <div class="card shadow-none border p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Catálogo de Socios</h6>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                onclick="openSocioModal()">
                                                <i class="fas fa-plus me-1"></i> Agregar Socio
                                            </button>
                                        </div>
                                        <div id="gridSocios" class="ag-theme-alpine" style="height: 400px; width: 100%;">
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 col-12 mb-4">
                                    <div class="card shadow-none border p-3">
                                        <div class="d-flex justify-content-between align-items-center mb-3">
                                            <h6 class="mb-0">Configuraciones de Distribución por Unidad</h6>
                                            <button type="button" class="btn btn-sm btn-primary"
                                                onclick="openConfigModal()">
                                                <i class="fas fa-plus me-1"></i> Nueva Asignación
                                            </button>
                                        </div>
                                        <div class="row g-2 align-items-center mb-3">
                                            <div class="col-7">
                                                <select class="form-select form-select-sm" id="filtro_equipo_config"
                                                    onchange="filtrarConfigsPorEquipo()">
                                                    <option value="todos">-- Todas las Unidades --</option>
                                                    @foreach ($equipos as $e)
                                                        <option value="{{ $e->id }}">
                                                            {{ $e->id_equipo ?: $e->placas }} ({{ $e->marca }})</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-5">
                                                <span id="label_porcentaje_unidad_config" class="badge bg-secondary d-none"
                                                    style="font-size: 11px; white-space: normal;">Suma Porcentajes:
                                                    0.00%</span>
                                            </div>
                                        </div>
                                        <div id="gridConfigs" class="ag-theme-alpine" style="height: 400px; width: 100%;">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- PESTAÑA 2: Cálculo y Pagos -->
                        <div class="tab-pane fade" id="calculo-content" role="tabpanel" aria-labelledby="calculo-tab">
                            <div class="row g-3 align-items-end mb-3">
                                <div class="col-md-3">
                                    <label class="form-label text-sm mb-1">Rango de Viajes</label>
                                    <input type="text" id="utilidadDaterange" readonly
                                        class="form-control form-control-sm">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label text-sm mb-1">Unidad (Equipo)</label>
                                    <select id="utilidad_equipo_id" class="form-select form-select-sm">
                                        <option value="">Todas las unidades</option>
                                        @foreach ($equipos as $eq)
                                            <option value="{{ $eq->id }}">{{ ($eq->id_equipo ? $eq->id_equipo . ' - ' : '') . $eq->placas . ' (' . $eq->marca . ')' }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-sm btn-info mb-0 w-100"
                                        onclick="cargarReporteUtilidad()">
                                        <i class="fas fa-sync me-1"></i> Calcular Utilidades
                                    </button>
                                </div>
                                <div class="col-md-3 d-none" id="divAccionesCorte">
                                    <button type="button" class="btn btn-sm btn-success mb-0 w-100"
                                        onclick="guardarCorteHistorico()">
                                        <i class="fas fa-save me-1"></i> Cerrar Corte
                                    </button>
                                </div>
                            </div>

                            <div class="row mb-4 d-none" id="seccionResumenPeriodo">
                                <div class="col-lg-3 col-md-6 col-12 mb-3">
                                    <div class="card bg-gradient-light shadow-sm">
                                        <div class="card-body p-3">
                                            <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Utilidad
                                                Bruta Viajes</p>
                                            <h4 class="font-weight-bolder mb-0" id="resumenBruto">$ 0.00</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-12 mb-3">
                                    <div class="card bg-gradient-light shadow-sm">
                                        <div class="card-body p-3">
                                            <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Gastos
                                                Indirectos Mes</p>
                                            <h4 class="font-weight-bolder mb-0 text-danger" id="resumenGastos">$ 0.00</h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-12 mb-3">
                                    <div class="card bg-gradient-light shadow-sm">
                                        <div class="card-body p-3">
                                            <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Utilidad a
                                                repartir</p>
                                            <h4 class="font-weight-bolder mb-0 text-warning" id="resumenComisiones">$ 0.00
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-3 col-md-6 col-12 mb-3">
                                    <div class="card bg-gradient-light shadow-sm">
                                        <div class="card-body p-3">
                                            <p class="text-xs mb-0 text-uppercase font-weight-bold text-muted">Total pago
                                            </p>
                                            <h4 class="font-weight-bolder mb-0 text-success" id="resumenNeta">$ 0.00</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Alerta de Comparativa Histórica -->
                            <div class="alert alert-warning text-white d-none mb-3" id="alertaComparativa"
                                role="alert">
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <span id="textoAlertaComparativa">Se detectaron diferencias con el corte guardado
                                        previamente para este periodo.</span>
                                    <button class="btn btn-xs btn-light ms-auto my-0"
                                        onclick="verDetalleDiferencias()">Ver Diferencias</button>
                                </div>
                            </div>

                            <!-- Grids de Utilidad y Viajes -->
                            <div class="row mb-4">
                                <div class="col-12 mb-4">
                                    <h6>Distribución Agrupada por Socio (Seleccione un socio para ver desglose y habilitar
                                        formulario de pago)</h6>
                                    <div id="gridUtilidad" class="ag-theme-alpine" style="height: 280px; width: 100%;">
                                    </div>
                                </div>
                                <div class="col-12 mb-4">
                                    <h6>Desglose Individual de Viajes</h6>
                                    <div id="gridViajesDesglose" class="ag-theme-alpine"
                                        style="height: 250px; width: 100%;"></div>
                                </div>
                            </div>

                        </div>

                        <!-- PESTAÑA 3: Historial de Pagos -->
                        <div class="tab-pane fade" id="pagos-content" role="tabpanel" aria-labelledby="pagos-tab">
                            <div class="card shadow-none border p-3 mb-4">
                                <h6 class="mb-3">Filtrar Historial de Pagos</h6>
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-3">
                                        <label class="form-label text-xs mb-1">Rango de Fecha</label>
                                        <input type="text" id="historialDaterange"
                                            class="form-control form-control-sm" placeholder="Seleccione rango...">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-xs mb-1">Socio</label>
                                        <select class="form-select form-select-sm" id="historial_socio_id">
                                            <option value="">Todos los Socios</option>
                                            @foreach ($socios as $socio)
                                                <option value="{{ $socio->id }}">{{ $socio->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <label class="form-label text-xs mb-1">Camión / Unidad</label>
                                        <select class="form-select form-select-sm" id="historial_equipo_id">
                                            <option value="">Todas las Unidades</option>
                                            @foreach ($equipos as $eq)
                                                <option value="{{ $eq->id }}">
                                                    {{ ($eq->id_equipo ? $eq->id_equipo . ' ' : '') . $eq->placas }}
                                                    ({{ $eq->marca }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-3 d-flex gap-2">
                                        <button type="button" class="btn btn-sm btn-primary mb-0 w-50"
                                            onclick="cargarHistorialPagosFiltrado()">
                                            <i class="fas fa-search me-1"></i> Buscar
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success mb-0 w-50"
                                            onclick="abrirModalPagoGeneral()">
                                            <i class="fas fa-hand-holding-usd me-1"></i> Adelanto
                                        </button>
                                    </div>
                                </div>
                            </div>

                            <div class="card shadow-none border p-3">
                                <h6>Registro de Pagos y Abonos</h6>
                                <div id="gridHistorialPagos" class="ag-theme-alpine" style="height: 450px; width: 100%;">
                                </div>
                            </div>
                        </div>

                        <!-- PESTAÑA 4: Historial de Cortes -->
                        <div class="tab-pane fade" id="cortes-content" role="tabpanel" aria-labelledby="cortes-tab">
                            <div class="card shadow-none border p-3">
                                <h6 class="mb-3">Cortes Financieros Cerrados</h6>
                                <div id="gridHistorialCortes" class="ag-theme-alpine"
                                    style="height: 450px; width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="modal fade" id="modalSocio" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <form class="modal-content" id="formSocio" onsubmit="saveSocio(event)">
                @csrf
                <input type="hidden" id="socio_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSocioTitle">Registrar Socio</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" name="nombre" id="socio_nombre" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label">RFC</label>
                            <input type="text" class="form-control" name="rfc" id="socio_rfc"
                                placeholder="Opcional">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" class="form-control" name="telefono" id="socio_telefono">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Correo Electrónico</label>
                            <input type="email" class="form-control" name="email" id="socio_email">
                        </div>
                        <div class="col-12" id="divSocioActivo" style="display: none;">
                            <label class="form-label">Estatus</label>
                            <select class="form-select" name="activo" id="socio_activo">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>


    <div class="modal fade" id="modalConfig" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <form class="modal-content" id="formConfig" onsubmit="saveConfig(event)">
                @csrf
                <input type="hidden" id="config_id" name="id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfigTitle">Configurar Utilidad por Unidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label">Socio de Negocio *</label>
                            <select class="form-select" name="socio_id" id="config_socio_id" required>
                                <option value="">-- Seleccione Socio --</option>
                                @foreach ($socios as $s)
                                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Unidad (Tracto / Camión) *</label>
                            <select class="form-select" name="equipo_id" id="config_equipo_id" required>
                                <option value="">-- Seleccione Camión --</option>
                                @foreach ($equipos as $e)
                                    <option value="{{ $e->id }}">{{ $e->id_equipo ?: $e->placas }}
                                        ({{ $e->marca }})
                                    </option>
                                @endforeach
                            </select>
                            <span id="config_porcentaje_acumulado" class="badge bg-secondary mt-2 d-none"
                                style="font-size: 11px; display: inline-block;">Porcentaje Acumulado de la Unidad:
                                0.00%</span>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tipo de Pago *</label>
                            <select class="form-select" name="tipo_pago" id="config_tipo_pago" required>
                                <option value="porcentaje">Porcentaje</option>
                                <option value="cuota_fija">Cuota Fija</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Valor (Monto / %) *</label>
                            <input type="number" step="0.01" min="0" class="form-control" name="valor"
                                id="config_valor" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Inicio Vigencia</label>
                            <input type="date" class="form-control" name="fecha_inicio" id="config_fecha_inicio">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha Fin Vigencia</label>
                            <input type="date" class="form-control" name="fecha_fin" id="config_fecha_fin">
                        </div>
                        <div class="col-12" id="divConfigActivo" style="display: none;">
                            <label class="form-label">Estatus</label>
                            <select class="form-select" name="activo" id="config_activo">
                                <option value="1">Activa</option>
                                <option value="0">Inactiva</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>


    <div class="modal fade" id="modalDiferencias" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Comparativa de Desviaciones de Utilidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0 text-sm">
                            <thead>
                                <tr>
                                    <th>Tipo de Cambio</th>
                                    <th>Contenedor / Concepto</th>
                                    <th class="text-end">Importe Guardado</th>
                                    <th class="text-end">Importe Actual</th>
                                    <th class="text-end text-danger">Variación</th>
                                </tr>
                            </thead>
                            <tbody id="tbodyDiferencias">
                                <!-- Dynamic lines -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-sm btn-warning" onclick="recalcularYGuardarCorte()">Recalcular
                        y Re-guardar</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para Registrar Pago / Abono -->
    <div class="modal fade" id="modalRegistrarPago" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog">
            <form class="modal-content" id="formRegistrarPago" onsubmit="aplicarPagoSocio(event)">
                @csrf
                <input type="hidden" name="socio_id" id="pago_socio_id">
                <div class="modal-header">
                    <h5 class="modal-title"><i class="fas fa-wallet me-2 text-success"></i>Registrar Pago / Abono</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12" id="grupo_pago_socio_select" style="display:none;">
                            <label class="form-label text-xs mb-1">Seleccionar Socio *</label>
                            <select class="form-select" id="pago_socio_id_select" onchange="onModalSocioSelectChange()">
                                <option value="">Seleccione un socio...</option>
                                @foreach ($socios as $socio)
                                    <option value="{{ $socio->id }}">{{ $socio->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12" id="grupo_pago_socio_read">
                            <label class="form-label text-xs mb-1">Socio Seleccionado</label>
                            <input type="text" class="form-control font-weight-bold" id="pago_socio_nombre" readonly>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-xs mb-1">Cortes/Periodos Pendientes *</label>
                            <div id="seccionCortesCheckbox" class="border rounded p-2 text-xs"
                                style="max-height: 150px; overflow-y: auto; background-color: #fafafa;">
                                <span class="text-xs text-muted">Cargando periodos...</span>
                            </div>
                        </div>
                        <div class="col-12" id="grupo_pago_concepto_div">
                            <label class="form-label text-xs mb-1">Concepto / Referencia (Adelanto)</label>
                            <input type="text" class="form-control" name="concepto" id="pago_concepto" placeholder="Ej. Adelanto de utilidades">
                        </div>
                        <div class="col-12">
                            <label class="form-label text-xs mb-1">Monto a Pagar *</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="number" step="0.01" class="form-control font-weight-bold"
                                    name="monto" id="pago_monto" required>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-xs mb-1">Banco de Origen *</label>
                            <select class="form-select" name="banco_id" id="pago_banco_id" required>
                                <option value="">Seleccione un banco...</option>
                                @foreach ($bancos as $banco)
                                    <option value="{{ $banco->id }}">{{ $banco->nombre_banco }}
                                        ({{ $banco->cuenta_bancaria }})
                                        - Saldo: ${{ number_format($banco->saldo, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-xs mb-1">Fecha de Aplicación *</label>
                            <input type="date" class="form-control" name="fecha_aplicacion"
                                id="pago_fecha_aplicacion" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success">
                        <i class="fas fa-check me-1"></i> Aplicar Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('js_custom')
    <script type="text/javascript" src="https://cdn.jsdelivr.net/momentjs/latest/moment.min.js"></script>
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />


    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <script>
        let gridSociosOptions, gridConfigsOptions, gridUtilidadOptions, gridViajesOptions, gridHistorialPagosOptions,
            gridHistorialCortesOptions;
        let gridSociosApi, gridConfigsApi, gridUtilidadApi, gridViajesApi, gridHistorialPagosApi, gridHistorialCortesApi;
        let comparativaData = null;
        let corteGuardadoGlobal = false;
        let configsList = [];

        function formatCurrency(val) {
            return `$ ${parseFloat(val || 0).toLocaleString('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
        }

        function formatDate(val) {
            if (!val) return 'S/N';
            return moment(val).format('DD-MM-YYYY');
        }

        function socioActionRenderer(params) {
            const socio = params.data;
            return `
                <button class="btn btn-xs btn-outline-info me-1 py-1" onclick='editSocio(${JSON.stringify(socio)})'>Editar</button>
                <button class="btn btn-xs btn-outline-danger py-1" onclick='deleteSocio(${socio.id})'>Eliminar</button>
            `;
        }

        function configActionRenderer(params) {
            const config = params.data;
            return `
                <button class="btn btn-xs btn-outline-info me-1 py-1" onclick='editConfig(${JSON.stringify(config)})'>Editar</button>
                <button class="btn btn-xs btn-outline-danger py-1" onclick='deleteConfig(${config.id})'>Eliminar</button>
            `;
        }

        document.addEventListener('DOMContentLoaded', function() {
            const start = moment().startOf('month');
            const end = moment().endOf('month');

            $('#utilidadDaterange').daterangepicker({
                startDate: start,
                endDate: end,
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                        'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                    ],
                    firstDay: 1
                }
            });

            // Listen to unit config field changes to update cumulative percentage badge
            document.getElementById('config_equipo_id').addEventListener('change', actualizarPorcentajeAcumulado);
            document.getElementById('config_tipo_pago').addEventListener('change', actualizarPorcentajeAcumulado);
            document.getElementById('config_valor').addEventListener('input', actualizarPorcentajeAcumulado);

            gridSociosOptions = {
                columnDefs: [{
                        headerName: 'ID',
                        field: 'id',
                        width: 80,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Nombre del Socio',
                        field: 'nombre',
                        width: 250,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'RFC',
                        field: 'rfc',
                        width: 150,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Teléfono',
                        field: 'telefono',
                        width: 150,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Correo Electrónico',
                        field: 'email',
                        width: 220,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Estatus',
                        field: 'activo',
                        width: 120,
                        cellRenderer: params => params.value ?
                            '<span class="badge bg-success">Activo</span>' :
                            '<span class="badge bg-secondary">Inactivo</span>'
                    },
                    {
                        headerName: 'Acciones',
                        cellRenderer: socioActionRenderer,
                        width: 200,
                        sortable: false,
                        filter: false
                    }
                ],
                rowData: []
            };
            gridSociosApi = agGrid.createGrid(document.querySelector('#gridSocios'), gridSociosOptions);

            gridConfigsOptions = {
                columnDefs: [{
                        headerName: 'Socio de Negocios',
                        field: 'socio.nombre',
                        width: 220,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Unidad',
                        field: 'equipo',
                        width: 180,
                        sortable: true,
                        filter: true,
                        cellRenderer: params => params.value ?
                            `${params.value.id_equipo || params.value.placas} (${params.value.marca})` :
                            '--'
                    },
                    {
                        headerName: 'Tipo Distribución',
                        field: 'tipo_pago',
                        width: 150,
                        cellRenderer: params => params.value === 'porcentaje' ? 'Porcentaje' : 'Cuota Fija'
                    },
                    {
                        headerName: 'Valor Pactado',
                        field: 'valor',
                        width: 130,
                        sortable: true,
                        cellRenderer: params => params.data.tipo_pago === 'porcentaje' ?
                            `${params.value}%` : formatCurrency(params.value)
                    },
                    {
                        headerName: 'Vigencia Inicio',
                        field: 'fecha_inicio',
                        width: 140,
                        sortable: true,
                        filter: true,
                        cellRenderer: params => formatDate(params.value)
                    },
                    {
                        headerName: 'Vigencia Fin',
                        field: 'fecha_fin',
                        width: 140,
                        sortable: true,
                        filter: true,
                        cellRenderer: params => formatDate(params.value)
                    },
                    {
                        headerName: 'Estatus',
                        field: 'activo',
                        width: 120,
                        cellRenderer: params => params.value ?
                            '<span class="badge bg-success">Vigente</span>' :
                            '<span class="badge bg-secondary">Inactiva</span>'
                    },
                    {
                        headerName: 'Acciones',
                        cellRenderer: configActionRenderer,
                        width: 180,
                        sortable: false,
                        filter: false
                    }
                ],
                rowData: []
            };
            gridConfigsApi = agGrid.createGrid(document.querySelector('#gridConfigs'), gridConfigsOptions);


            gridUtilidadOptions = {
                columnDefs: [{
                        headerName: 'Socio',
                        field: 'socio',
                        width: 150,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Unidad Pactada',
                        field: 'unidad',
                        width: 140,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Regla de Pago',
                        field: 'factor',
                        width: 100
                    },
                    {
                        headerName: 'Viajes',
                        field: 'viajes_realizados',
                        width: 80,
                        sortable: true
                    },
                    {
                        headerName: 'Util. Bruta',
                        field: 'utilidad_bruta',
                        width: 110,
                        sortable: true,
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Gastos Camión',
                        field: 'gastos_camion',
                        width: 120,
                        sortable: true,
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Util. Neta',
                        field: 'utilidad_neta',
                        width: 110,
                        sortable: true,
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Acumulado (Cortes)',
                        field: 'saldo_acumulado',
                        width: 140,
                        sortable: true,
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Total Pagado',
                        field: 'total_pagado',
                        width: 120,
                        sortable: true,
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Saldo Pendiente',
                        field: 'saldo_pendiente',
                        width: 130,
                        sortable: true,
                        cellRenderer: params => formatCurrency(params.value),
                        cellStyle: params => {
                            if (params.value < 0) {
                                return {
                                    color: 'white',
                                    backgroundColor: '#dc3545',
                                    fontWeight: 'bold'
                                };
                            }
                            return null;
                        }
                    },
                    {
                        headerName: 'Deuda / Adelanto',
                        field: 'deuda_pendiente',
                        width: 130,
                        sortable: true,
                        cellRenderer: params => formatCurrency(params.value),
                        cellStyle: params => {
                            if (params.value > 0) {
                                return {
                                    color: 'white',
                                    backgroundColor: '#fd7e14',
                                    fontWeight: 'bold'
                                };
                            }
                            return null;
                        }
                    },
                    {
                        headerName: 'Observaciones',
                        field: 'observacion',
                        width: 220,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Acciones',
                        field: 'acciones',
                        width: 120,
                        cellRenderer: params => {
                            if (!corteGuardadoGlobal) {
                                return `<button class="btn btn-xs btn-secondary" disabled title="Debe cerrar el corte para pagar"><i class="fas fa-lock me-1"></i> Pagar</button>`;
                            }
                            return `<button class="btn btn-xs btn-success" onclick="abrirModalPago(${params.data.socio_id}, '${params.data.socio.replace(/'/g, "\\'")}', ${params.data.saldo_pendiente})" title="Registrar Pago"><i class="fas fa-money-bill-wave me-1"></i> Pagar</button>`;
                        }
                    }
                ],
                rowData: [],
                rowSelection: 'single',
                onSelectionChanged: onSocioSelectionChanged
            };
            gridUtilidadApi = agGrid.createGrid(document.querySelector('#gridUtilidad'), gridUtilidadOptions);


            gridViajesOptions = {
                columnDefs: [{
                        headerName: 'Fecha Viaje',
                        field: 'fecha_viaje',
                        width: 120,
                        sortable: true,
                        cellRenderer: params => formatDate(params.value)
                    },
                    {
                        headerName: 'Contenedor',
                        field: 'contenedor',
                        width: 170,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Cliente',
                        field: 'cliente',
                        width: 160,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Unidad',
                        field: 'unidad',
                        width: 110,
                        sortable: true
                    },
                    {
                        headerName: 'Estatus',
                        field: 'estatus_viaje',
                        width: 110,
                        sortable: true,
                        cellRenderer: params =>
                            `<span class="badge ${params.value === 'Planeada' ? 'bg-info' : 'bg-success'}">${params.value}</span>`
                    },
                    {
                        headerName: 'Utilidad Viaje',
                        field: 'utilidad_viaje',
                        width: 130,
                        sortable: true,
                        cellRenderer: params => formatCurrency(params.value)
                    }
                ],
                rowData: []
            };
            gridViajesApi = agGrid.createGrid(document.querySelector('#gridViajesDesglose'), gridViajesOptions);

            // Initialize Historial de Pagos Grid
            gridHistorialPagosOptions = {
                columnDefs: [{
                        headerName: 'Fecha',
                        field: 'fecha',
                        width: 120,
                        sortable: true,
                        cellRenderer: params => formatDate(params.value)
                    },
                    {
                        headerName: 'Socio',
                        field: 'socio_nombre',
                        width: 160,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Concepto / Periodo',
                        field: 'concepto',
                        width: 220,
                        sortable: true,
                        filter: true
                    },
                    {
                        headerName: 'Banco',
                        field: 'banco',
                        width: 180,
                        sortable: true
                    },
                    {
                        headerName: 'Cargo (Utilidad)',
                        field: 'cargo',
                        width: 130,
                        sortable: true,
                        cellClass: 'text-danger',
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Abono (Pago)',
                        field: 'abono',
                        width: 130,
                        sortable: true,
                        cellClass: 'text-success',
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Saldo Final',
                        field: 'saldo',
                        width: 130,
                        sortable: true,
                        cellClass: 'fw-bold',
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Registrado Por',
                        field: 'registrado_por',
                        width: 130,
                        sortable: true
                    },
                    {
                        headerName: 'Acciones',
                        width: 100,
                        cellRenderer: params => {
                            const idStr = params.data.id;
                            if (idStr && idStr.startsWith('pago_')) {
                                const dbId = idStr.replace('pago_', '');
                                return `<button class="btn btn-xs btn-outline-danger py-1" onclick="eliminarPago(${dbId})">Eliminar</button>`;
                            }
                            return '';
                        }
                    }
                ],
                rowData: []
            };
            gridHistorialPagosApi = agGrid.createGrid(document.querySelector('#gridHistorialPagos'),
                gridHistorialPagosOptions);

            // Initialize Historial de Cortes Grid
            gridHistorialCortesOptions = {
                columnDefs: [{
                        headerName: 'ID',
                        field: 'id',
                        width: 80,
                        sortable: true
                    },
                    {
                        headerName: 'Periodo Desde',
                        field: 'fecha_desde',
                        width: 140,
                        sortable: true,
                        cellRenderer: params => formatDate(params.value)
                    },
                    {
                        headerName: 'Periodo Hasta',
                        field: 'fecha_hasta',
                        width: 140,
                        sortable: true,
                        cellRenderer: params => formatDate(params.value)
                    },
                    {
                        headerName: 'Unidad',
                        field: 'equipo',
                        width: 140,
                        cellRenderer: params => {
                            const eq = params.value;
                            if (!eq) return '<span class="badge bg-secondary text-xs">Global</span>';
                            return (eq.id_equipo ? eq.id_equipo + ' - ' : '') + eq.placas;
                        }
                    },
                    {
                        headerName: 'Util. Bruta Viajes',
                        field: 'total_utilidad_bruta_viajes',
                        width: 160,
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Gastos Periodo',
                        field: 'total_gastos_periodo',
                        width: 150,
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Util. Neta Distribuible',
                        field: 'utilidad_neta_distribuible',
                        width: 180,
                        cellRenderer: params => formatCurrency(params.value)
                    },
                    {
                        headerName: 'Cerrado Por',
                        field: 'user.name',
                        width: 150
                    },
                    {
                        headerName: 'Fecha Cierre',
                        field: 'created_at',
                        width: 160,
                        cellRenderer: params => moment(params.value).format('DD-MM-YYYY HH:mm')
                    },
                    {
                        headerName: 'Acciones',
                        width: 120,
                        cellRenderer: params => {
                            const id = params.data.id;
                            return `<button class="btn btn-xs btn-outline-danger py-1" onclick="eliminarCorte(${id})">Eliminar</button>`;
                        }
                    }
                ],
                rowData: []
            };
            gridHistorialCortesApi = agGrid.createGrid(document.querySelector('#gridHistorialCortes'),
                gridHistorialCortesOptions);

            // Date picker for payments history filter
            $('#historialDaterange').daterangepicker({
                startDate: moment().startOf('year'),
                endDate: end,
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' - ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                        'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                    ],
                    firstDay: 1
                }
            });

            cargarSocios();
            cargarConfigs();
            cargarHistorialPagosFiltrado();
            cargarHistorialCortes();
        });

        async function cargarSocios() {
            try {
                const res = await fetch("{{ route('socios.data') }}");
                const json = await res.json();
                if (gridSociosApi) {
                    gridSociosApi.setGridOption('rowData', json.socios);
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function cargarConfigs() {
            try {
                const res = await fetch("{{ route('socios.configs.data') }}");
                const json = await res.json();
                configsList = json.configs || [];
                filtrarConfigsPorEquipo();
            } catch (e) {
                console.error(e);
            }
        }

        function filtrarConfigsPorEquipo() {
            const selectedEquipoId = document.getElementById('filtro_equipo_config').value;
            const badge = document.getElementById('label_porcentaje_unidad_config');

            if (gridConfigsApi) {
                if (selectedEquipoId === 'todos') {
                    gridConfigsApi.setGridOption('rowData', configsList);
                    badge.classList.add('d-none');
                } else {
                    const filtered = configsList.filter(c => c.equipo_id == selectedEquipoId);
                    gridConfigsApi.setGridOption('rowData', filtered);

                    let sum = 0;
                    filtered.forEach(c => {
                        if (c.tipo_pago === 'porcentaje' && c.activo) {
                            sum += parseFloat(c.valor) || 0;
                        }
                    });

                    badge.classList.remove('d-none');
                    badge.textContent = `Suma Porcentajes: ${sum.toFixed(2)}%`;

                    badge.classList.remove('bg-secondary', 'bg-success', 'bg-danger');
                    if (sum > 100) {
                        badge.classList.add('bg-danger');
                    } else if (sum === 100) {
                        badge.classList.add('bg-success');
                    } else {
                        badge.classList.add('bg-secondary');
                    }
                }
            }
        }

        async function cargarReporteUtilidad() {
            const dates = $('#utilidadDaterange').val().split(' - ');
            const from = dates[0];
            const to = dates[1];
            const equipoId = document.getElementById('utilidad_equipo_id').value;

            try {
                Swal.fire({
                    title: 'Calculando utilidad...',
                    text: 'Cruzando viajes, gastos diferidos y acuerdos por socio...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                const res = await fetch(`{{ route('socios.reporte.utilidad') }}?from=${from}&to=${to}&equipo_id=${equipoId}`);
                const json = await res.json();


                document.getElementById('resumenBruto').textContent = formatCurrency(json.total_utilidad_bruta_viajes);
                document.getElementById('resumenGastos').textContent = formatCurrency(json.total_gastos_periodo);
                document.getElementById('resumenComisiones').textContent = formatCurrency(json
                    .total_distribuido_socios);
                document.getElementById('resumenNeta').textContent = formatCurrency(json.total_pagado_periodo);

                document.getElementById('seccionResumenPeriodo').classList.remove('d-none');
                document.getElementById('divAccionesCorte').classList.remove('d-none');

                if (gridUtilidadApi) {
                    gridUtilidadApi.setGridOption('rowData', json.socios_desglose);
                }
                if (gridViajesApi) {
                    gridViajesApi.setGridOption('rowData', json.viajes_desglose);
                }

                const compRes = await fetch(`{{ route('socios.comparativa') }}?from=${from}&to=${to}&equipo_id=${equipoId}`);
                const compJson = await compRes.json();
                comparativaData = compJson;
                corteGuardadoGlobal = compJson.has_saved;

                if (gridUtilidadApi) {
                    gridUtilidadApi.redrawRows();
                }

                if (compJson.has_overlap) {
                    document.getElementById('divAccionesCorte').classList.add('d-none');
                    Swal.fire('Atención', compJson.overlap_message, 'warning');
                } else {
                    document.getElementById('divAccionesCorte').classList.remove('d-none');
                    Swal.close();
                }

                const alertComp = document.getElementById('alertaComparativa');
                if (compJson.has_saved && compJson.diferencias && compJson.diferencias.length > 0) {
                    alertComp.classList.remove('d-none');
                    document.getElementById('textoAlertaComparativa').innerHTML =
                        `<b>¡Desviación detectada!</b> Se detectaron diferencias con el corte guardado previamente para este periodo (${compJson.fecha_guardado}).`;
                } else {
                    alertComp.classList.add('d-none');
                }
            } catch (e) {
                console.error(e);
                Swal.fire('Error', 'No se pudieron calcular los rendimientos.', 'error');
            }
        }

        function exportarReporte(fileType) {
            const dates = $('#utilidadDaterange').val().split(' - ');
            const from = dates[0];
            const to = dates[1];
            window.open(`/socios/exportar?from=${from}&to=${to}&fileType=${fileType}`, '_blank');
        }

        function verDetalleDiferencias() {
            if (!comparativaData || !comparativaData.diferencias) return;

            const tbody = document.getElementById('tbodyDiferencias');
            tbody.innerHTML = '';

            comparativaData.diferencias.forEach(d => {
                const tr = document.createElement('tr');
                tr.innerHTML = `
                    <td><b>${d.tipo}</b></td>
                    <td>${d.referencia}</td>
                    <td class="text-end">$ ${parseFloat(d.guardado).toFixed(2)}</td>
                    <td class="text-end">$ ${parseFloat(d.actual).toFixed(2)}</td>
                    <td class="text-end text-danger font-weight-bold">$ ${parseFloat(d.diferencia).toFixed(2)}</td>
                `;
                tbody.appendChild(tr);
            });

            new bootstrap.Modal(document.getElementById('modalDiferencias')).show();
        }

        async function guardarCorteHistorico() {
            const dates = $('#utilidadDaterange').val().split(' - ');
            const from = dates[0];
            const to = dates[1];
            const equipoId = document.getElementById('utilidad_equipo_id').value;

            const confirm = await Swal.fire({
                title: '¿Guardar Corte Financiero?',
                text: "Se guardará una instantánea histórica de este periodo. Si ya existe un corte para estas fechas, se sobrescribirá.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, guardar',
                cancelButtonText: 'Cancelar'
            });

            if (confirm.isConfirmed) {
                Swal.fire({
                    title: 'Guardando snapshot...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const res = await fetch("{{ route('socios.guardar.corte') }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            from,
                            to,
                            equipo_id: equipoId
                        })
                    });
                    const json = await res.json();
                    if (json.success) {
                        Swal.fire('¡Guardado!', json.Mensaje, 'success');
                        document.getElementById('alertaComparativa').classList.add('d-none');
                        await cargarReporteUtilidad();
                        cargarHistorialCortes();
                    } else {
                        Swal.fire('Atención', json.Mensaje || 'No se pudo guardar el corte.', 'warning');
                    }
                } catch (e) {
                    Swal.fire('Error', 'No se pudo guardar el corte del periodo.', 'error');
                }
            }
        }

        async function recalcularYGuardarCorte() {
            bootstrap.Modal.getInstance(document.getElementById('modalDiferencias')).hide();
            await guardarCorteHistorico();
            await cargarReporteUtilidad();
        }

        function openSocioModal() {
            document.getElementById('formSocio').reset();
            document.getElementById('socio_id').value = '';
            document.getElementById('modalSocioTitle').textContent = 'Registrar Socio';
            document.getElementById('divSocioActivo').style.display = 'none';
            new bootstrap.Modal(document.getElementById('modalSocio')).show();
        }

        function editSocio(socio) {
            openSocioModal();
            document.getElementById('socio_id').value = socio.id;
            document.getElementById('socio_nombre').value = socio.nombre;
            document.getElementById('socio_rfc').value = socio.rfc || '';
            document.getElementById('socio_telefono').value = socio.telefono || '';
            document.getElementById('socio_email').value = socio.email || '';
            document.getElementById('socio_activo').value = socio.activo ? '1' : '0';
            document.getElementById('divSocioActivo').style.display = 'block';
            document.getElementById('modalSocioTitle').textContent = 'Editar Socio';
        }

        async function saveSocio(e) {
            e.preventDefault();
            const id = document.getElementById('socio_id').value;
            const url = id ? `/socios/${id}` : '/socios/store';
            const method = id ? 'PUT' : 'POST';

            Swal.fire({
                title: 'Guardando...',
                text: 'Registrando la información del socio, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const payload = {
                nombre: document.getElementById('socio_nombre').value,
                rfc: document.getElementById('socio_rfc').value,
                telefono: document.getElementById('socio_telefono').value,
                email: document.getElementById('socio_email').value
            };
            if (id) {
                payload.activo = document.getElementById('socio_activo').value;
            }

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json.success) {
                    Swal.fire('¡Éxito!', json.Mensaje, 'success').then(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalSocio')).hide();
                        cargarSocios();
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', json.Mensaje || 'Error procesando solicitud.', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Error de red al guardar socio.', 'error');
            }
        }

        async function deleteSocio(id) {
            const confirm = await Swal.fire({
                title: '¿Está seguro?',
                text: "Se eliminarán también sus configuraciones asociadas.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (confirm.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                try {
                    const res = await fetch(`/socios/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const json = await res.json();
                    if (json.success) {
                        Swal.fire('Eliminado', json.Mensaje, 'success');
                        cargarSocios();
                        cargarConfigs();
                    }
                } catch (e) {
                    Swal.fire('Error', 'No se pudo eliminar el socio.', 'error');
                }
            }
        }

        function actualizarPorcentajeAcumulado() {
            const equipoId = document.getElementById('config_equipo_id').value;
            const configId = document.getElementById('config_id').value;
            const tipoPago = document.getElementById('config_tipo_pago').value;
            const currentVal = parseFloat(document.getElementById('config_valor').value) || 0;

            const badge = document.getElementById('config_porcentaje_acumulado');

            if (!equipoId) {
                badge.classList.add('d-none');
                return;
            }

            let sumPercentage = 0;
            configsList.forEach(c => {
                // Sum active percentage configs for this unit, excluding the one currently editing
                if (c.equipo_id == equipoId && c.id != configId && c.tipo_pago === 'porcentaje' && c.activo) {
                    sumPercentage += parseFloat(c.valor) || 0;
                }
            });

            let totalPercentage = sumPercentage;
            if (tipoPago === 'porcentaje') {
                totalPercentage += currentVal;
            }

            badge.classList.remove('d-none');
            badge.textContent = `Porcentaje Acumulado de la Unidad: ${totalPercentage.toFixed(2)}%`;

            badge.classList.remove('bg-secondary', 'bg-success', 'bg-danger');
            if (totalPercentage > 100) {
                badge.classList.add('bg-danger');
            } else if (totalPercentage === 100) {
                badge.classList.add('bg-success');
            } else {
                badge.classList.add('bg-secondary');
            }
        }

        function openConfigModal() {
            document.getElementById('formConfig').reset();
            document.getElementById('config_id').value = '';
            document.getElementById('modalConfigTitle').textContent = 'Configurar Utilidad por Unidad';
            document.getElementById('divConfigActivo').style.display = 'none';
            new bootstrap.Modal(document.getElementById('modalConfig')).show();
            actualizarPorcentajeAcumulado();
        }

        function editConfig(config) {
            openConfigModal();
            document.getElementById('config_id').value = config.id;
            document.getElementById('config_socio_id').value = config.socio_id;
            document.getElementById('config_equipo_id').value = config.equipo_id;
            document.getElementById('config_tipo_pago').value = config.tipo_pago;
            document.getElementById('config_valor').value = config.valor;
            document.getElementById('config_fecha_inicio').value = config.fecha_inicio || '';
            document.getElementById('config_fecha_fin').value = config.fecha_fin || '';
            document.getElementById('config_activo').value = config.activo ? '1' : '0';
            document.getElementById('divConfigActivo').style.display = 'block';
            document.getElementById('modalConfigTitle').textContent = 'Editar Asignación de Utilidad';
            actualizarPorcentajeAcumulado();
        }

        async function saveConfig(e) {
            e.preventDefault();
            const id = document.getElementById('config_id').value;
            const equipoId = document.getElementById('config_equipo_id').value;
            const tipoPago = document.getElementById('config_tipo_pago').value;
            const currentVal = parseFloat(document.getElementById('config_valor').value) || 0;

            if (tipoPago === 'porcentaje') {
                let sumPercentage = 0;
                configsList.forEach(c => {
                    if (c.equipo_id == equipoId && c.id != id && c.tipo_pago === 'porcentaje' && c.activo) {
                        sumPercentage += parseFloat(c.valor) || 0;
                    }
                });

                if (sumPercentage + currentVal > 100) {
                    Swal.fire('Porcentaje excedido',
                        `El porcentaje total acumulado para esta unidad superará el 100% (Total: ${(sumPercentage + currentVal).toFixed(2)}%). Por favor ajuste los valores.`,
                        'warning');
                    return;
                }
            }

            const url = id ? `/socios/configs/${id}` : '/socios/configs/store';
            const method = id ? 'PUT' : 'POST';

            Swal.fire({
                title: 'Guardando...',
                text: 'Registrando configuración, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const payload = {
                socio_id: document.getElementById('config_socio_id').value,
                equipo_id: document.getElementById('config_equipo_id').value,
                tipo_pago: document.getElementById('config_tipo_pago').value,
                valor: document.getElementById('config_valor').value,
                fecha_inicio: document.getElementById('config_fecha_inicio').value || null,
                fecha_fin: document.getElementById('config_fecha_fin').value || null
            };
            if (id) {
                payload.activo = document.getElementById('config_activo').value;
            }

            try {
                const res = await fetch(url, {
                    method: method,
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify(payload)
                });
                const json = await res.json();
                if (json.success) {
                    Swal.fire('¡Éxito!', json.Mensaje, 'success').then(() => {
                        bootstrap.Modal.getInstance(document.getElementById('modalConfig')).hide();
                        cargarConfigs();
                    });
                } else {
                    Swal.fire('Error', json.Mensaje || 'Error procesando solicitud.', 'error');
                }
            } catch (err) {
                Swal.fire('Error', 'Error de red al guardar configuración.', 'error');
            }
        }

        async function deleteConfig(id) {
            const confirm = await Swal.fire({
                title: '¿Está seguro?',
                text: "Se cancelará el acuerdo de distribución de esta unidad.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (confirm.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                try {
                    const res = await fetch(`/socios/configs/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const json = await res.json();
                    if (json.success) {
                        Swal.fire('Eliminado', json.Mensaje, 'success');
                        cargarConfigs();
                    }
                } catch (e) {
                    Swal.fire('Error', 'No se pudo eliminar la configuración.', 'error');
                }
            }
        }

        let socioCortesData = [];

        function onSocioSelectionChanged() {
            const selectedRows = gridUtilidadApi.getSelectedRows();
            if (selectedRows && selectedRows.length > 0) {
                const selected = selectedRows[0];
                if (gridViajesApi) {
                    gridViajesApi.setFilterModel({
                        cliente: null,
                        contenedor: null,
                        socio: {
                            filterType: 'text',
                            type: 'contains',
                            filter: selected.socio
                        }
                    });
                }
            } else {
                if (gridViajesApi) {
                    gridViajesApi.setFilterModel(null);
                }
            }
        }

        function abrirModalPago(socioId, socioNombre, saldoPendiente) {
            document.getElementById('formRegistrarPago').reset();
            document.getElementById('pago_socio_id').value = socioId;
            document.getElementById('pago_socio_nombre').value = socioNombre;
            document.getElementById('pago_fecha_aplicacion').value = new Date().toISOString().split('T')[0];

            document.getElementById('grupo_pago_socio_select').style.display = 'none';
            document.getElementById('grupo_pago_socio_read').style.display = 'block';

            cargarCortesSocio(socioId, true);

            const modal = new bootstrap.Modal(document.getElementById('modalRegistrarPago'));
            modal.show();
        }

        function abrirModalPagoGeneral() {
            document.getElementById('formRegistrarPago').reset();
            document.getElementById('pago_socio_id').value = '';
            document.getElementById('pago_socio_nombre').value = '';
            document.getElementById('pago_fecha_aplicacion').value = new Date().toISOString().split('T')[0];

            document.getElementById('grupo_pago_socio_select').style.display = 'block';
            document.getElementById('grupo_pago_socio_read').style.display = 'none';
            document.getElementById('pago_socio_id_select').value = '';

            document.getElementById('seccionCortesCheckbox').innerHTML =
                '<span class="text-xs text-muted">Seleccione un socio para ver periodos...</span>';

            const modal = new bootstrap.Modal(document.getElementById('modalRegistrarPago'));
            modal.show();
        }

        function onModalSocioSelectChange() {
            const selectEl = document.getElementById('pago_socio_id_select');
            const socioId = selectEl.value;
            document.getElementById('pago_socio_id').value = socioId;
            document.getElementById('pago_socio_nombre').value = selectEl.options[selectEl.selectedIndex].text;

            if (socioId) {
                // When selecting general advance, keep checkboxes unchecked by default
                cargarCortesSocio(socioId, false);
            } else {
                document.getElementById('seccionCortesCheckbox').innerHTML =
                    '<span class="text-xs text-muted">Seleccione un socio para ver periodos...</span>';
            }
        }

        async function cargarCortesSocio(socioId, checkAll = true) {
            try {
                const res = await fetch(`{{ route('socios.socio.cortes') }}?socio_id=${socioId}`);
                const json = await res.json();

                const container = document.getElementById('seccionCortesCheckbox');
                container.innerHTML = '';
                socioCortesData = [];

                if (json.cortes && json.cortes.length > 0) {
                    socioCortesData = json.cortes;
                    json.cortes.forEach((c, idx) => {
                        const labelText =
                            `Corte #${c.id} [${c.periodo}]: Asignado: $${parseFloat(c.monto_distribuido).toFixed(2)} - Pendiente: $${parseFloat(c.saldo_pendiente).toFixed(2)}`;
                        const div = document.createElement('div');
                        div.className = 'form-check mb-1';
                        div.innerHTML = `
                            <input class="form-check-input corte-check-item" type="checkbox" value="${c.id}" id="corte_check_${c.id}" data-idx="${idx}" ${c.saldo_pendiente > 0 && checkAll ? 'checked' : ''} ${c.saldo_pendiente > 0 ? '' : 'disabled'}>
                            <label class="form-check-label text-xs mb-0 ${c.saldo_pendiente > 0 ? 'text-dark font-weight-bold' : 'text-muted'}" for="corte_check_${c.id}">
                                ${labelText}
                            </label>
                        `;
                        container.appendChild(div);
                    });

                    document.querySelectorAll('.corte-check-item').forEach(el => {
                        el.addEventListener('change', actualizarMontoPagoAutomatico);
                    });

                    actualizarMontoPagoAutomatico();
                } else {
                    container.innerHTML =
                        `<span class="text-xs text-muted">Este socio no tiene periodos cerrados registrados. El pago se registrará como abono general.</span>`;
                    document.getElementById('pago_monto').value = '';
                }
            } catch (e) {
                console.error(e);
            }
        }

        function actualizarMontoPagoAutomatico() {
            let total = 0;
            const checkedCount = document.querySelectorAll('.corte-check-item:checked').length;

            document.querySelectorAll('.corte-check-item:checked').forEach(el => {
                const idx = el.getAttribute('data-idx');
                const corte = socioCortesData[idx];
                if (corte) {
                    total += parseFloat(corte.saldo_pendiente);
                }
            });
            document.getElementById('pago_monto').value = total > 0 ? total.toFixed(2) : '';

            const conceptoDiv = document.getElementById('grupo_pago_concepto_div');
            const conceptoInput = document.getElementById('pago_concepto');
            if (checkedCount > 0) {
                conceptoDiv.style.display = 'none';
                conceptoInput.required = false;
                conceptoInput.value = '';
            } else {
                conceptoDiv.style.display = 'block';
                conceptoInput.required = true;
            }
        }

        async function cargarHistorialPagosFiltrado() {
            const dates = $('#historialDaterange').val().split(' - ');
            const from = dates[0] || '';
            const to = dates[1] || '';
            const socioId = document.getElementById('historial_socio_id').value;
            const equipoId = document.getElementById('historial_equipo_id').value;

            try {
                const res = await fetch(
                    `{{ route('socios.pagos.historial') }}?fecha_desde=${from}&fecha_hasta=${to}&socio_id=${socioId}&equipo_id=${equipoId}`
                );
                const json = await res.json();
                if (gridHistorialPagosApi) {
                    gridHistorialPagosApi.setGridOption('rowData', json.pagos);
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function cargarHistorialCortes() {
            try {
                const res = await fetch(`{{ route('socios.cortes-historial') }}`);
                const json = await res.json();
                if (gridHistorialCortesApi) {
                    gridHistorialCortesApi.setGridOption('rowData', json.cortes);
                }
            } catch (e) {
                console.error(e);
            }
        }

        async function aplicarPagoSocio(e) {
            e.preventDefault();

            const socioId = document.getElementById('pago_socio_id').value;
            const montoTotal = parseFloat(document.getElementById('pago_monto').value);
            const bancoId = document.getElementById('pago_banco_id').value;
            const fechaAplicacion = document.getElementById('pago_fecha_aplicacion').value;
            const concepto = document.getElementById('pago_concepto').value;

            // Distribute payment among checked periods
            let periodosPayload = [];
            const checkedItems = document.querySelectorAll('.corte-check-item:checked');

            if (checkedItems.length > 0) {
                if (montoTotal < 0) {
                    // For negative values/deductions, we associate the entire negative amount to the first checked period
                    const firstIdx = checkedItems[0].getAttribute('data-idx');
                    const corte = socioCortesData[firstIdx];
                    if (corte) {
                        periodosPayload.push({
                            id: corte.id,
                            monto: montoTotal
                        });
                    }
                } else {
                    let montoRestante = montoTotal;
                    checkedItems.forEach(el => {
                        const idx = el.getAttribute('data-idx');
                        const corte = socioCortesData[idx];
                        if (corte && montoRestante > 0) {
                            const pagarParaEste = Math.min(montoRestante, corte.saldo_pendiente);
                            if (pagarParaEste > 0) {
                                periodosPayload.push({
                                    id: corte.id,
                                    monto: pagarParaEste
                                });
                                montoRestante -= pagarParaEste;
                            }
                        }
                    });

                    // If there's leftover money, add it to the first period
                    if (montoRestante > 0 && periodosPayload.length > 0) {
                        periodosPayload[0].monto += montoRestante;
                    }
                }
            }

            Swal.fire({
                title: 'Aplicando Pago...',
                text: 'Registrando el abono en el historial y afectando saldo de bancos...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const res = await fetch("{{ route('socios.pagar') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        socio_id: socioId,
                        monto: montoTotal,
                        banco_id: bancoId,
                        fecha_aplicacion: fechaAplicacion,
                        concepto: concepto,
                        periodos: periodosPayload
                    })
                });

                const json = await res.json();

                if (json.success) {
                    Swal.fire('¡Pago Aplicado!', json.Mensaje, 'success');

                    // Reset form and default date
                    document.getElementById('formRegistrarPago').reset();
                    document.getElementById('pago_fecha_aplicacion').value = new Date().toISOString().split('T')[0];

                    // Hide Modal
                    const modalEl = document.getElementById('modalRegistrarPago');
                    const modalInst = bootstrap.Modal.getInstance(modalEl);
                    if (modalInst) {
                        modalInst.hide();
                    }

                    // Reload utility calculations and histories
                    await cargarReporteUtilidad();
                    cargarHistorialPagosFiltrado();
                    cargarHistorialCortes();
                } else {
                    Swal.fire('Error', json.Mensaje || 'No se pudo aplicar el pago.', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Error de red al aplicar el pago.', 'error');
            }
        }

        async function eliminarCorte(id) {
            const confirm = await Swal.fire({
                title: '¿Está seguro de eliminar este corte?',
                text: "Esta acción eliminará el cálculo del periodo de manera permanente y no se podrá deshacer.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (confirm.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando corte...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const res = await fetch(`/socios/cortes/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const json = await res.json();
                    if (res.ok) {
                        Swal.fire('¡Eliminado!', json.Mensaje, 'success');
                        await cargarReporteUtilidad();
                        cargarHistorialCortes();
                    } else {
                        Swal.fire('Atención', json.message || 'No se pudo eliminar el corte.', 'warning');
                    }
                } catch (e) {
                    console.error(e);
                    Swal.fire('Error', 'Error de red al eliminar el corte.', 'error');
                }
            }
        }

        async function eliminarPago(id) {
            const confirm = await Swal.fire({
                title: '¿Está seguro de eliminar este pago?',
                text: "Esta acción anulará el pago en el historial y revertirá el movimiento en el saldo bancario.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            });

            if (confirm.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando pago...',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                try {
                    const res = await fetch(`/socios/pagos/${id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    });
                    const json = await res.json();
                    if (res.ok) {
                        Swal.fire('¡Eliminado!', json.Mensaje, 'success');
                        await cargarReporteUtilidad();
                        cargarHistorialPagosFiltrado();
                        cargarHistorialCortes();
                    } else {
                        Swal.fire('Atención', json.message || 'No se pudo eliminar el pago.', 'warning');
                    }
                } catch (e) {
                    console.error(e);
                    Swal.fire('Error', 'Error de red al eliminar el pago.', 'error');
                }
            }
        }
    </script>
@endsection
