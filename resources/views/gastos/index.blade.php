@extends('layouts.app')

@section('template_title')
    Gastos
@endsection

@section('content')
    <div class="card">
        <div class="card-header bg-transparent d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Gastos</h5>
                <p class="text-sm text-muted mb-0">Modulo unificado para validar registros nuevos y espejos legacy.</p>
            </div>
            <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#modalGastoNew">
                <i class="fa fa-plus"></i> Registrar gasto
            </button>
        </div>

        <div class="card-body">
            <div class="row g-2 mb-3 align-items-end">

                <div class="col-md-4">
                    <label class="form-label text-sm mb-1">Periodo</label>
                    <input type="text" id="daterange" readonly class="form-control form-control-sm">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-sm mb-1">Concepto / Folio</label>
                    <input type="text" id="gastosNewSearch" class="form-control form-control-sm"
                        placeholder="Buscar concepto, folio...">
                </div>

                <div class="col-md-3">
                    <label class="form-label text-sm mb-1">Tipo de Gasto</label>
                    <select id="gastosNewTipo" class="form-control form-control-sm">
                        <option value="todos">Todos los tipos</option>
                        <option value="general">General</option>
                        <option value="periodo">Periodo</option>
                        <option value="unidad">Unidad</option>
                        <option value="viaje">Viaje</option>
                        <option value="cotizacion">Cotización</option>
                        <option value="contenedor">Contenedor</option>
                        <option value="operador">Operador</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <button type="button" class="btn btn-sm btn-outline-primary w-100 mb-0" id="btnGastosNewBuscar">
                        Buscar
                    </button>
                </div>

            </div>
            <div class="row">
                <div id="myGridNew" class="col-12 ag-theme-quartz" style="height: 550px"></div>
            </div>
        </div>
    </div>

    <!-- Modal para Aplicar Pago (Pagar Gasto Pendiente) -->
    <div class="modal fade" id="modalPagarGasto" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog">
            <form class="modal-content" id="formPagarGasto">
                @csrf
                <input type="hidden" name="gasto_id" id="pagoGastoId">
                <div class="modal-header">
                    <h5 class="modal-title">Aplicar Pago a Gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label text-muted mb-0">Concepto del Gasto</label>
                            <div class="font-weight-bold" id="pagoGastoConcepto">-</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label text-muted mb-0">Saldo Pendiente</label>
                            <div class="font-weight-bold text-danger text-lg" id="pagoGastoSaldo">$ 0.00</div>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Cuenta de retiro (Banco) *</label>
                            <select class="form-select" name="cuenta_bancaria_id" id="pagoCuentaBancaria" required>
                                <option value="">-- Seleccionar Cuenta --</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['display'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Monto a Pagar *</label>
                            <input type="number" step="0.01" min="0.01" name="monto" id="pagoMonto"
                                class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Fecha de Pago *</label>
                            <input type="date" name="fecha_pago" id="pagoFecha" class="form-control"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label">Referencia del Pago</label>
                            <input type="text" name="referencia" class="form-control"
                                placeholder="Ej. Transferencia 12345">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-success">Aplicar Pago</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalGastoNew" tabindex="-1" aria-hidden="true" data-bs-backdrop="static"
        data-bs-keyboard="false">
        <div class="modal-dialog modal-lg">
            <form class="modal-content" id="formGastoNew">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title">Registrar gasto</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="row g-3">
                        <input type="hidden" name="id" id="gastoIdNew" value="">
                        <input type="hidden" name="tipo_gasto" id="tipo_gasto" value="periodo">
                        <input type="hidden" name="metodo_imputacion" id="metodo_imputacion" value="directo">

                        <!-- Aplicar Gasto A: Radios visuales -->
                        <div class="col-12">
                            <h6 class="mb-3">Aplicar gasto a:</h6>
                            <div class="option-group">
                                <label class="custom-option selected">
                                    <input type="radio" checked name="formasAplicar" value="Periodo"
                                        onchange="handleSelectionNew(this)" />
                                    <i class="fas fa-clock icon"></i>
                                    <div class="text-group">
                                        <div class="text">Periodo</div>
                                        <div class="text text-xs text-muted" id="periodoGastoNewInfo"
                                            style="font-size: 11px;">
                                            -
                                        </div>
                                    </div>
                                    <i class="fas fa-check check-icon"></i>
                                </label>
                                <label class="custom-option">
                                    <input type="radio" name="formasAplicar" value="Equipo"
                                        onchange="handleSelectionNew(this)" />
                                    <i class="fas fa-truck-moving icon"></i>
                                    <span class="text">Unidad (Equipo)</span>
                                    <i class="fas fa-check check-icon"></i>
                                </label>
                                <label class="custom-option">
                                    <input type="radio" name="formasAplicar" value="Viaje"
                                        onchange="handleSelectionNew(this)" />
                                    <i class="fas fa-compass icon"></i>
                                    <span class="text">Contenedor (Viaje)</span>
                                    <i class="fas fa-check check-icon"></i>
                                </label>
                            </div>
                        </div>

                        <!-- Sección de Selección Múltiple para Unidades -->
                        <div class="col-12 d-none aplicacion-gastos-new" id="aplicacion-equipoNew">
                            <label class="form-label font-weight-bold text-info">Seleccione Unidades (Equipo)</label>
                            <select class="form-control" name="unidades[]" id="selectUnidadesNew" multiple>
                                @foreach ($equipos as $e)
                                    <option value="{{ $e->id }}">{{ $e->marca }} -
                                        {{ $e->id_equipo ?: $e->placas }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sección de Selección Múltiple para Viajes -->
                        <div class="col-12 d-none aplicacion-gastos-new" id="aplicacion-viajeNew">
                            <label class="form-label font-weight-bold text-info">Seleccione Viajes (Contenedor)</label>
                            <select class="form-control" name="viajes[]" id="selectViajesNew" multiple>
                                @foreach ($viajes as $v)
                                    <option value="{{ $v->id }}">
                                        Contenedor: {{ $v->Contenedor?->num_contenedor ?: 'S/N' }} (Inicio:
                                        {{ $v->fecha_inicio }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label font-weight-bold text-info">¿Dónde debe impactar este gasto?</label>
                            <select name="impacto" id="impacto" class="form-select" required>
                                <option value="">Seleccione</option>
                                <option value="periodo">Gasto Administrativo / Periodo</option>
                                <option value="viaje">Gastos Operativo del Viaje</option>
                                <option value="cotizacion">Gastos + Costo de Cotización</option>
                            </select>

                        </div>

                        <!-- Concepto / Descripción breve -->
                        <div class="col-md-12">
                            <label class="form-label">Concepto / Motivo *</label>
                            <input type="text" name="concepto" id="conceptoNew" class="form-control"
                                placeholder="Escriba el concepto del gasto" required>
                        </div>

                        <!-- Monto y Categoría -->
                        <div class="col-md-6">
                            <label class="form-label">Monto total *</label>
                            <input type="number" step="0.01" min="0.01" name="monto_total" id="monto_totalNew"
                                class="form-control" placeholder="0.00" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Categoría *</label>
                            <select class="form-select" name="categoria_gasto_id" id="categoria_gasto_idNew" required>
                                <option value="">-- Seleccionar Categoría --</option>
                                @foreach ($categorias as $categoria)
                                    <option value="{{ $categoria->id }}">{{ $categoria->categoria }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Condición de Pago y Fecha de Gasto -->
                        <div class="col-md-6">
                            <label class="form-label">Condición de pago</label>
                            <select class="form-select" id="tipoPagoNew" name="tipoPago" required>
                                <option value="0">Contado</option>
                                <option value="1">Diferido</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Fecha de aplicación *</label>
                            <input type="date" name="fecha_gasto" id="fecha_gastoNew" class="form-control"
                                value="{{ now()->format('Y-m-d') }}" required>
                        </div>

                        <!-- Configuración de Pago Diferido (Colapsable) -->
                        <div class="col-12 collapse" id="seccionDiferidoNew">
                            <div class="p-3 mb-2 bg-gray-100 border border-secondary border-radius-md"
                                style="background-color: #f8f9fa; border-radius: 8px;">
                                <h6 class="mb-1 text-sm font-weight-bold">Configuración de pago diferido</h6>
                                <small class="text-muted mb-3 d-block" style="font-size: 11px;">
                                    Determine el rango de fechas para distribuir el pago en modalidad diferida mensualmente.
                                </small>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <div class="mb-2">
                                            <label class="form-label text-xs">Fecha de inicio</label>
                                            <input name="txtDiferirFechaInicia" id="txtDiferirFechaIniciaNew"
                                                type="date" class="form-control form-control-sm fechasDiferirNew" />
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label text-xs">Fecha de finalización</label>
                                            <input name="txtDiferirFechaTermina" id="txtDiferirFechaTerminaNew"
                                                type="date" class="form-control form-control-sm fechasDiferirNew" />
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-xs">Resumen del pago diferido</label>
                                        <div class="table-responsive">
                                            <table class="table table-sm table-bordered mb-0">
                                                <tbody>
                                                    <tr>
                                                        <th scope="row" class="text-xs py-1" style="font-size: 11px;">
                                                            Número de periodos</th>
                                                        <td class="text-end py-1" style="font-size: 11px;"><strong
                                                                id="labelDiasPeriodoNew">0</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row" class="text-xs py-1" style="font-size: 11px;">
                                                            Monto por periodo</th>
                                                        <td class="text-end py-1" style="font-size: 11px;"><strong
                                                                id="labelGastoDiarioNew">$ 0.00</strong></td>
                                                    </tr>
                                                    <tr>
                                                        <th scope="row" class="text-xs py-1" style="font-size: 11px;">
                                                            Total del gasto</th>
                                                        <td class="text-end py-1" style="font-size: 11px;"><strong
                                                                id="labelMontoGastoNew">$ 0.00</strong></td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Cuenta Retiro (Banco) -->
                        <div class="col-12" id="divCuentaRetiroNew">
                            <label class="form-label">Cuenta de retiro (Banco) *</label>
                            <select class="form-select" id="id_banco1New" name="id_banco1" required>
                                <option value="">-- Seleccionar Cuenta --</option>
                                @foreach ($bancos as $b)
                                    <option value="{{ $b['id'] }}">
                                        {{ $b['display'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-sm btn-info">Guardar</button>
                </div>
            </form>
        </div>
    </div>

    <div class="modal fade" id="modalHistorialPagos" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        Historial de pagos
                    </h5>

                    <button type="button" class="btn-close" data-bs-dismiss="modal">
                    </button>
                </div>

                <div class="modal-body">

                    <div id="historialPagosBody">

                    </div>

                </div>

            </div>
        </div>
    </div>
@endsection

@section('js_custom')
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>


    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script>
        console.log('jquery', typeof $);
        console.log('daterangepicker', typeof $.fn.daterangepicker);
    </script>


    <!-- AG Grid Community y estilos -->
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <!-- Cargar Choices.js y Flatpickr -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/choices.js/public/assets/scripts/choices.min.js"></script>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>

    <script>
        window.mesinicio = null;
        window.mesfin = null;
        const gastosRoutes = {
            data: @json(route('gastos.data')),
            store: @json(route('gastos.store')),

            historial: '/gastos',
            cancelarPago: '/gastos/pagos',
        };




        function currencyFormatter(value) {
            if (value === null || value === undefined) return '';
            return new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN'
            }).format(value);
        }

        function formatFecha(params) {
            if (!params.value) return '';
            const parts = params.value.split('-');
            if (parts.length === 3) {
                return `${parts[2]}/${parts[1]}/${parts[0]}`;
            }
            return params.value;
        }

        class EstatusRenderer {
            init(params) {
                const val = params.value;
                this.eGui = document.createElement('span');
                if (val === 'pagado') {
                    this.eGui.className = 'badge bg-success';
                    this.eGui.textContent = 'Pagado';
                } else if (val === 'pagado_parcial') {
                    this.eGui.className = 'badge bg-warning text-dark';
                    this.eGui.textContent = 'Parcial';
                } else if (val === 'pendiente_pago') {
                    this.eGui.className = 'badge bg-danger';
                    this.eGui.textContent = 'Pendiente';
                } else if (val === 'cancelado') {
                    this.eGui.className = 'badge bg-secondary';
                    this.eGui.textContent = 'Cancelado';
                } else {
                    this.eGui.className = 'badge bg-info';
                    this.eGui.textContent = val || '';
                }
            }
            getGui() {
                return this.eGui;
            }
        }

        class VinculosRenderer {
            init(params) {
                this.eGui = document.createElement('div');
                const vinculos = params.value || [];
                if (vinculos.length === 0) {
                    this.eGui.innerHTML = '<span class="text-muted text-xs">-</span>';
                    return;
                }
                this.eGui.innerHTML = vinculos.map(v => `
                    <div style="line-height: 1.2; margin-bottom: 2px;">
                        <span class="text-xs" style="font-size: 11px;">
                            <i class="fa fa-link text-muted" style="font-size: 9px;"></i>
                            <strong>${v.tipo.toUpperCase()}:</strong> ${v.detalle}
                        </span>
                    </div>
                `).join('');
            }
            getGui() {
                return this.eGui;
            }
        }

        class ActionButtonRenderer {
            init(params) {

                this.eGui = document.createElement('div');
                this.eGui.style.display = 'flex';
                this.eGui.style.gap = '3px';
                this.eGui.style.alignItems = 'center';
                this.eGui.style.height = '100%';

                const saldo = parseFloat(params.data.saldo_pendiente) || 0;
                const estatus = params.data.estatus;

                const totalPagos = params.data.total_pagos || 0;
                const ultimoPagoId = params.data.ultimo_pago_id;

                // PAGAR
                if (saldo > 0 && estatus !== 'cancelado') {

                    const btnPagar = document.createElement('button');

                    btnPagar.className =
                        'btn btn-xs btn-success my-0 py-1 px-2';

                    btnPagar.innerHTML =
                        '<i class="fa fa-money-bill"></i>';

                    btnPagar.title = 'Aplicar pago';

                    btnPagar.addEventListener('click', () => {
                        abrirModalPago(params.data);
                    });

                    this.eGui.appendChild(btnPagar);
                }

                // EDITAR
                if (estatus !== 'cancelado') {
                    const btnEditar = document.createElement('button');
                    btnEditar.className = 'btn btn-xs btn-warning my-0 py-1 px-2';
                    btnEditar.innerHTML = '<i class="fa fa-edit"></i>';
                    btnEditar.title = 'Editar gasto';
                    btnEditar.addEventListener('click', () => {
                        abrirModalEditar(params.data);
                    });
                    this.eGui.appendChild(btnEditar);
                }

                // HISTORIAL
                if (totalPagos > 0) {

                    const btnHistorial = document.createElement('button');

                    btnHistorial.className =
                        'btn btn-xs btn-info my-0 py-1 px-2';

                    btnHistorial.innerHTML =
                        '<i class="fa fa-list"></i>';

                    btnHistorial.title = 'Historial de pagos';

                    btnHistorial.addEventListener('click', () => {
                        abrirHistorialPagos(params.data.id);
                    });

                    this.eGui.appendChild(btnHistorial);
                }

                // CANCELACION RAPIDA
                if (totalPagos === 1 && ultimoPagoId) {

                    const btnCancelar = document.createElement('button');

                    btnCancelar.className =
                        'btn btn-xs btn-danger my-0 py-1 px-2';

                    btnCancelar.innerHTML =
                        '<i class="fa fa-times"></i>';

                    btnCancelar.title = 'Cancelar pago';

                    btnCancelar.addEventListener('click', () => {
                        cancelarPagoDirecto(
                            ultimoPagoId,
                            params.data.ultima_fecha_pago
                        );
                    });

                    this.eGui.appendChild(btnCancelar);
                }

                if (this.eGui.children.length === 0) {
                    this.eGui.innerHTML =
                        '<span class="text-muted text-xs">-</span>';
                }
            }

            getGui() {
                return this.eGui;
            }
        }

        const localeText = {
            page: "Página",
            more: "Más",
            to: "a",
            of: "de",
            next: "Siguiente",
            last: "Último",
            first: "Primero",
            previous: "Anterior",
            loadingOoo: "Cargando...",
            selectAll: "Seleccionar todo",
            searchOoo: "Buscar...",
            blanks: "Vacíos",
            filterOoo: "Filtrar...",
            applyFilter: "Aplicar filtro...",
            equals: "Igual",
            notEqual: "Distinto",
            lessThan: "Menor que",
            greaterThan: "Mayor que",
            contains: "Contiene",
            notContains: "No contiene",
            startsWith: "Empieza con",
            endsWith: "Termina con",
            andCondition: "Y",
            orCondition: "O",
            group: "Grupo",
            columns: "Columnas",
            filters: "Filtros",
            pivotMode: "Modo Pivote",
            groups: "Grupos",
            values: "Valores",
            noRowsToShow: "Sin filas para mostrar",
            pinColumn: "Fijar columna",
            autosizeThiscolumn: "Ajustar columna",
            copy: "Copiar",
            resetColumns: "Restablecer columnas",
            blank: "Vacíos",
            notBlank: "No Vacíos",
            paginationPageSize: "Registros por página",
        };

        const gridOptions = {
            pagination: true,
            paginationPageSize: 15,
            paginationPageSizeSelector: [10, 15, 30, 50, 100],
            rowData: [],
            columnDefs: [{
                    field: "id",
                    headerName: "ID",
                    width: 75,
                    filter: 'agNumberColumnFilter',
                    floatingFilter: true,
                    hide: true
                },
                {
                    field: "fecha_gasto",
                    headerName: "Fecha",
                    width: 110,
                    valueFormatter: formatFecha,
                    filter: 'agDateColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "concepto",
                    headerName: "Concepto",
                    filter: 'agTextColumnFilter',
                    floatingFilter: true,
                    flex: 1
                },
                {
                    field: "tipo_gasto",
                    headerName: "Tipo",
                    width: 100,
                    filter: 'agTextColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "categoria",
                    headerName: "Categoría",
                    width: 130,
                    filter: 'agTextColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "vinculos",
                    headerName: "Vínculos",
                    width: 230,
                    cellRenderer: VinculosRenderer
                },
                {
                    field: "monto_total",
                    headerName: "Total",
                    width: 110,
                    valueFormatter: (params) => currencyFormatter(params.value),
                    cellStyle: {
                        textAlign: 'right'
                    },
                    filter: 'agNumberColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "monto_pagado",
                    headerName: "Pagado",
                    width: 110,
                    valueFormatter: (params) => currencyFormatter(params.value),
                    cellStyle: {
                        textAlign: 'right',
                        color: '#2dce89'
                    },
                    filter: 'agNumberColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "saldo_pendiente",
                    headerName: "Saldo",
                    width: 110,
                    valueFormatter: (params) => currencyFormatter(params.value),
                    cellStyle: {
                        textAlign: 'right',
                        color: '#f5365c'
                    },
                    filter: 'agNumberColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "estatus",
                    headerName: "Estatus",
                    width: 110,
                    cellRenderer: EstatusRenderer,
                    filter: 'agTextColumnFilter',
                    floatingFilter: true
                },
                {
                    field: "origen_legacy",
                    headerName: "Origen",
                    width: 110,
                    valueGetter: (params) => params.data.origen_legacy || params.data.origen_modulo || '',
                    filter: 'agTextColumnFilter',
                    floatingFilter: true
                },
                {
                    headerName: "Acciones",
                    width: 100,
                    cellRenderer: ActionButtonRenderer
                }
            ],
            localeText: localeText
        };

        let apiGrid = null;

        async function cargarGastosNew() {

            const from = window.mesinicio;
            const to = window.mesfin;
            const search = document.getElementById('gastosNewSearch').value;
            const tipo_gasto = document.getElementById('gastosNewTipo').value;

            const url =
                `${gastosRoutes.data}?from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&search=${encodeURIComponent(search)}&tipo_gasto=${encodeURIComponent(tipo_gasto)}`;

            try {
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json'
                    }
                });
                const json = await response.json();

                if (apiGrid) {
                    apiGrid.setGridOption('rowData', json.gastos || []);
                }
            } catch (err) {
                console.error(err);
            }
        }

        document.getElementById('btnGastosNewBuscar').addEventListener('click', cargarGastosNew);

        document.getElementById('gastosNewSearch').addEventListener('keyup', (e) => {
            if (e.key === 'Enter') {
                cargarGastosNew();
            }
        });

        document.getElementById('gastosNewTipo').addEventListener('change', cargarGastosNew);

        const selectTipoGasto = document.querySelector('select[name="tipo_gasto"]');
        const modalForm = document.getElementById('formGastoNew');

        let choicesUnidades = null;
        let choicesViajes = null;

        document.addEventListener('DOMContentLoaded', () => {


            // Inicializar AG Grid
            const myGridElement = document.querySelector("#myGridNew");
            if (myGridElement) {
                apiGrid = agGrid.createGrid(myGridElement, gridOptions);
            }

            // Inicializar Choices.js
            if (document.getElementById('selectUnidadesNew')) {
                choicesUnidades = new Choices(document.getElementById('selectUnidadesNew'), {
                    removeItemButton: true,
                    noResultsText: 'No se encontraron unidades',
                    noChoicesText: 'No hay opciones disponibles',
                    itemSelectText: 'Seleccionar'
                });
            }

            if (document.getElementById('selectViajesNew')) {
                choicesViajes = new Choices(document.getElementById('selectViajesNew'), {
                    removeItemButton: true,
                    noResultsText: 'No se encontraron viajes',
                    noChoicesText: 'No hay opciones disponibles',
                    itemSelectText: 'Seleccionar'
                });
            }

            // Inicializar Flatpickr


            flatpickr('#txtDiferirFechaIniciaNew', {
                locale: 'es',
                dateFormat: 'Y-m-d',
                allowInput: false,
                onChange: calcDaysNew
            });

            flatpickr('#txtDiferirFechaTerminaNew', {
                locale: 'es',
                dateFormat: 'Y-m-d',
                allowInput: false,
                onChange: calcDaysNew
            });





        });

        function actualizarTextoPeriodo() {
            const from = window.mesinicio;
            const to = window.mesfin;


            const labelPeriodo = document.getElementById('periodoGastoNewInfo');
            if (labelPeriodo) {
                labelPeriodo.textContent = `${from} AL ${to}`;
            }

        }



        function handleSelectionNew(input) {
            input.closest('.option-group').querySelectorAll('.custom-option').forEach(opt => {
                opt.classList.remove('selected');
            });

            input.parentElement.classList.add('selected');

            document.querySelectorAll('.aplicacion-gastos-new').forEach(div => {
                div.classList.add('d-none');
            });

            if (choicesUnidades) choicesUnidades.removeActiveItems();
            if (choicesViajes) choicesViajes.removeActiveItems();

            const tipoGastoInput = document.getElementById('tipo_gasto');

            if (input.value === 'Equipo') {
                document.getElementById('aplicacion-equipoNew').classList.remove('d-none');
                tipoGastoInput.value = 'unidad';
                document.getElementById('impacto').value = 'viaje';
            } else if (input.value === 'Viaje') {
                document.getElementById('aplicacion-viajeNew').classList.remove('d-none');
                tipoGastoInput.value = 'viaje';
                document.getElementById('impacto').value = 'viaje';
            } else {
                tipoGastoInput.value = 'periodo';
                document.getElementById('impacto').value = 'periodo';
            }
        }

        document.getElementById('tipoPagoNew').addEventListener('change', function() {
            const seccionDiferido = document.getElementById('seccionDiferidoNew');
            const divCuentaRetiro = document.getElementById('divCuentaRetiroNew');
            const selectBanco = document.getElementById('id_banco1New');
            const metodoImputacionInput = document.getElementById('metodo_imputacion');

            if (this.value === '1') {
                const bsCollapse = new bootstrap.Collapse(seccionDiferido, {
                    show: true
                });
                metodoImputacionInput.value = 'diferido';
                divCuentaRetiro.style.display = 'none';
                selectBanco.required = false;
                selectBanco.value = '';
            } else {
                const bsCollapse = bootstrap.Collapse.getInstance(seccionDiferido);
                if (bsCollapse) bsCollapse.hide();
                metodoImputacionInput.value = 'directo';
                divCuentaRetiro.style.display = 'block';
                selectBanco.required = true;
            }
        });

        document.getElementById('monto_totalNew').addEventListener('input', calcDaysNew);

        function diferenciaEnMeses(fecha1, fecha2) {
            let inicio = new Date(fecha1 + "T00:00:00");
            let fin = new Date(fecha2 + "T00:00:00");
            let periodos = 1;

            if (inicio.getFullYear() === fin.getFullYear() && inicio.getMonth() === fin.getMonth()) {
                return periodos;
            }

            while (inicio.getFullYear() < fin.getFullYear() || inicio.getMonth() < fin.getMonth()) {
                periodos++;
                inicio.setMonth(inicio.getMonth() + 1);
            }
            return periodos;
        }

        function moneyFormat(val) {
            return '$ ' + Number(val || 0).toLocaleString('es-MX', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });
        }

        function calcDaysNew() {
            const fechaI = document.getElementById('txtDiferirFechaIniciaNew').value;
            const fechaF = document.getElementById('txtDiferirFechaTerminaNew').value;
            const labelDias = document.getElementById('labelDiasPeriodoNew');
            const labelGastoDiario = document.getElementById('labelGastoDiarioNew');
            const labelMontoGasto = document.getElementById('labelMontoGastoNew');
            const montoVal = parseFloat(document.getElementById('monto_totalNew').value) || 0;

            labelMontoGasto.textContent = moneyFormat(montoVal);

            if (fechaI && fechaF) {
                const diasContados = diferenciaEnMeses(fechaI, fechaF);
                labelDias.textContent = diasContados;

                const dailyAmount = montoVal / diasContados;
                labelGastoDiario.textContent = moneyFormat(dailyAmount);
            } else {
                labelDias.textContent = '0';
                labelGastoDiario.textContent = '$ 0.00';
            }
        }

        // Evento Submit de Crear Gasto
        modalForm.addEventListener('submit', async (event) => {
            event.preventDefault();

            const formasAplicar = document.querySelector('input[name="formasAplicar"]:checked').value;
            if (formasAplicar === 'Equipo') {
                const selectUnidades = document.getElementById('selectUnidadesNew');
                if (selectUnidades.selectedOptions.length === 0) {
                    Swal.fire('Selección Requerida',
                        'Por favor, seleccione al menos una Unidad para aplicar el gasto.', 'warning');
                    return;
                }
            } else if (formasAplicar === 'Viaje') {
                const selectViajes = document.getElementById('selectViajesNew');
                if (selectViajes.selectedOptions.length === 0) {
                    Swal.fire('Selección Requerida',
                        'Por favor, seleccione al menos un Viaje para aplicar el gasto.', 'warning');
                    return;
                }
            }

            const tipoPago = document.getElementById('tipoPagoNew').value;
            if (tipoPago === '1') {
                const fechaI = document.getElementById('txtDiferirFechaIniciaNew').value;
                const fechaF = document.getElementById('txtDiferirFechaTerminaNew').value;
                if (!fechaI || !fechaF) {
                    Swal.fire('Campos Requeridos',
                        'Para pago diferido es obligatorio indicar las fechas de inicio y fin.', 'warning');
                    return;
                }
            }
            const fechaAplicacion =
                document.getElementById('fecha_gastoNew').value;

            if (
                fechaAplicacion < window.mesinicio ||
                fechaAplicacion > window.mesfin
            ) {
                Swal.fire(
                    'Fecha inválida',
                    `La fecha de aplicación (${fechaAplicacion}) debe estar dentro del periodo seleccionado (${window.mesinicio} al ${window.mesfin}).`,
                    'warning'
                );

                return;
            }

            Swal.fire({
                title: 'Procesando...',
                text: 'Registrando el gasto, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const formData = new FormData(modalForm);
            const gastoId = document.getElementById('gastoIdNew').value;
            const isEdit = !!gastoId;
            const urlSubmit = isEdit ? `/gastos/${gastoId}` : gastosRoutes.store;
            
            // For Laravel PUT requests with FormData, we can append _method = PUT
            if (isEdit) {
                formData.append('_method', 'PUT');
            }

            try {
                const response = await fetch(urlSubmit, {
                    method: 'POST', // Use POST method to allow file/FormData submission + method spoofing
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': formData.get('_token')
                    },
                    body: formData
                });
                const json = await response.json();

                if (json.TMensaje === 'success') {
                    Swal.fire({
                        title: json.Titulo || 'Éxito',
                        text: json.Mensaje || 'El gasto se guardó correctamente.',
                        icon: 'success'
                    }).then(() => {
                        modalForm.reset();

                        const seccionDiferido = document.getElementById('seccionDiferidoNew');
                        const bsCollapse = bootstrap.Collapse.getInstance(seccionDiferido);
                        if (bsCollapse) bsCollapse.hide();

                        document.getElementById('divCuentaRetiroNew').style.display = 'block';
                        document.getElementById('id_banco1New').required = true;

                        if (choicesUnidades) choicesUnidades.removeActiveItems();
                        if (choicesViajes) choicesViajes.removeActiveItems();

                        document.querySelectorAll('.custom-option').forEach(opt => {
                            opt.classList.remove('selected');
                        });
                        document.querySelector('input[name="formasAplicar"][value="Periodo"]')
                            .parentElement.classList.add('selected');

                        document.querySelectorAll('.aplicacion-gastos-new').forEach(div => {
                            div.classList.add('d-none');
                        });

                        document.getElementById('tipo_gasto').value = 'periodo';
                        document.getElementById('metodo_imputacion').value = 'directo';

                        bootstrap.Modal.getInstance(document.getElementById('modalGastoNew')).hide();
                        cargarGastosNew();
                    });
                } else {
                    Swal.fire(json.Titulo || 'Error', json.Mensaje || 'No se pudo guardar el gasto.', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Ocurrió un error al enviar el formulario.', 'error');
            }
        });

        // Lógica de Edición (abrir modal con datos cargados)
        function abrirModalEditar(gasto) {
            // Reset modal first
            modalForm.reset();
            if (choicesUnidades) choicesUnidades.removeActiveItems();
            if (choicesViajes) choicesViajes.removeActiveItems();

            document.getElementById('gastoIdNew').value = gasto.id;
            document.getElementById('conceptoNew').value = gasto.concepto;
            document.getElementById('monto_totalNew').value = gasto.monto_total;
            document.getElementById('categoria_gasto_idNew').value = gasto.categoria_gasto_id || '';
            document.getElementById('fecha_gastoNew').value = gasto.fecha_gasto;

            // Determinar tipo de imputación y configurar radios
            let formasAplicarVal = 'Periodo';
            if (gasto.tipo_gasto === 'unidad') {
                formasAplicarVal = 'Equipo';
            } else if (gasto.tipo_gasto === 'viaje') {
                formasAplicarVal = 'Viaje';
            }

            const inputRadio = document.querySelector(`input[name="formasAplicar"][value="${formasAplicarVal}"]`);
            if (inputRadio) {
                inputRadio.checked = true;
                handleSelectionNew(inputRadio);
            }

            // Populate selected units or trips
            const vinculos = gasto.vinculos || [];
            if (formasAplicarVal === 'Equipo' && choicesUnidades) {
                const mappedUnidades = vinculos.filter(v => v.tipo === 'unidad').map(v => {
                    // Try to match value from option list
                    const select = document.getElementById('selectUnidadesNew');
                    const opt = Array.from(select.options).find(o => o.text.includes(v.detalle.replace('Unidad: ', '')));
                    return opt ? opt.value : null;
                }).filter(val => val !== null);
                
                choicesUnidades.setChoiceByValue(mappedUnidades);
            } else if (formasAplicarVal === 'Viaje' && choicesViajes) {
                const mappedViajes = vinculos.filter(v => v.tipo === 'asignacion' || v.tipo === 'contenedor').map(v => {
                    const select = document.getElementById('selectViajesNew');
                    const opt = Array.from(select.options).find(o => o.text.includes(v.detalle.replace('Contenedor: ', '').replace('Viaje (Contenedor): ', '')));
                    return opt ? opt.value : null;
                }).filter(val => val !== null);

                choicesViajes.setChoiceByValue(mappedViajes);
            }

            // Set impact select
            const imputaciones = gasto.imputaciones || [];
            if (imputaciones.length > 0) {
                document.getElementById('impacto').value = imputaciones[0].tipo_imputacion || 'periodo';
            }

            // Condition/tipo de pago
            const metodoPagoVal = gasto.metodo_imputacion === 'diferido' ? '1' : '0';
            document.getElementById('tipoPagoNew').value = metodoPagoVal;
            // trigger change event to toggle defer colapsable if necessary
            document.getElementById('tipoPagoNew').dispatchEvent(new Event('change'));

            // Populate Bank Account if it is Contado and has payments
            const pagos = gasto.pagos || [];
            if (metodoPagoVal === '0' && pagos.length > 0 && pagos[0].cuenta_bancaria) {
                document.getElementById('id_banco1New').value = pagos[0].cuenta_bancaria.id;
            } else {
                document.getElementById('id_banco1New').value = '';
            }

            // Show modal
            const modal = new bootstrap.Modal(document.getElementById('modalGastoNew'));
            modal.show();
        }

        // Clean ID when creating a new Gasto
        document.querySelector('[data-bs-target="#modalGastoNew"]').addEventListener('click', () => {
            document.getElementById('gastoIdNew').value = '';
            modalForm.reset();
            if (choicesUnidades) choicesUnidades.removeActiveItems();
            if (choicesViajes) choicesViajes.removeActiveItems();
            document.getElementById('tipoPagoNew').value = '0';
            document.getElementById('tipoPagoNew').dispatchEvent(new Event('change'));
        });

        // Lógica de Pago Individual
        function abrirModalPago(gasto) {
            document.getElementById('pagoGastoId').value = gasto.id;
            document.getElementById('pagoGastoConcepto').textContent = gasto.concepto;
            document.getElementById('pagoGastoSaldo').textContent = currencyFormatter(gasto.saldo_pendiente);
            document.getElementById('pagoMonto').value = gasto.saldo_pendiente;
            document.getElementById('pagoMonto').max = gasto.saldo_pendiente;

            const modal = new bootstrap.Modal(document.getElementById('modalPagarGasto'));
            modal.show();
        }

        const formPagar = document.getElementById('formPagarGasto');
        formPagar.addEventListener('submit', async (event) => {
            event.preventDefault();

            const gastoId = document.getElementById('pagoGastoId').value;
            const formData = new FormData(formPagar);

            Swal.fire({
                title: 'Procesando...',
                text: 'Registrando el pago en bancos y gastos, por favor espere.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const urlPay = `/gastos/${gastoId}/pagar`;

                const response = await fetch(urlPay, {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': formData.get('_token')
                    },
                    body: formData
                });
                const json = await response.json();

                if (json.TMensaje === 'success') {
                    Swal.fire('Éxito', json.Mensaje || 'El pago se registró correctamente.', 'success').then(
                        () => {
                            formPagar.reset();
                            bootstrap.Modal.getInstance(document.getElementById('modalPagarGasto')).hide();
                            cargarGastosNew();
                        });
                } else {
                    Swal.fire(json.Titulo || 'Error', json.Mensaje || 'No se pudo aplicar el pago.', 'error');
                }
            } catch (err) {
                console.error(err);
                Swal.fire('Error', 'Ocurrió un error al procesar el pago.', 'error');
            }
        });

        async function abrirHistorialPagos(gastoId) {

            try {

                const response = await fetch(
                    `${gastosRoutes.historial}/${gastoId}/historial-pagos`
                );

                const json = await response.json();

                if (json.TMensaje !== 'success') {

                    Swal.fire(
                        'Error',
                        json.Mensaje,
                        'error'
                    );

                    return;
                }

                renderHistorialPagos(json.pagos);

                new bootstrap.Modal(
                    document.getElementById('modalHistorialPagos')
                ).show();

            } catch (e) {

                console.error(e);

                Swal.fire(
                    'Error',
                    'No fue posible cargar el historial',
                    'error'
                );
            }
        }

        function renderHistorialPagos(pagos) {

            let html = `
        <table class="table table-sm table-bordered">

            <thead>

                <tr>
                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Monto</th>
                    <th>Referencia</th>
                    <th>Estatus</th>
                    <th></th>
                </tr>

            </thead>

            <tbody>
    `;

            pagos.forEach(p => {

                html += `
            <tr>

                <td>${p.id}</td>

                <td>${p.fecha_pago}</td>

                <td>${currencyFormatter(p.monto)}</td>

                <td>${p.referencia ?? ''}</td>

                <td>

                    ${
                        p.estatus === 'cancelado'
                        ?
                        '<span class="badge bg-secondary">Cancelado</span>'
                        :
                        '<span class="badge bg-success">Aplicado</span>'
                    }

                </td>

                <td>
        `;

                if (p.estatus !== 'cancelado') {

                    html += `
                <button
                    class="btn btn-sm btn-danger"
                    onclick="cancelarPagoHistorial(${p.id}, '${p.fecha_pago}')">

                    Cancelar

                </button>
            `;
                }

                html += `
                </td>

            </tr>
        `;
            });

            html += `
            </tbody>

        </table>
    `;

            document.getElementById(
                'historialPagosBody'
            ).innerHTML = html;
        }

        async function cancelarPagoDirecto(pagoId, fechaPago) {
            cancelarPagoHistorial(pagoId, fechaPago);
        }

        async function cancelarPagoHistorial(
            pagoId,
            fechaPago
        ) {

            const result = await Swal.fire({

                title: 'Cancelar pago',

                html: `
            <label class="form-label">
                Fecha cancelación
            </label>

            <input
                id="fechaCancelacion"
                type="date"
                class="swal2-input"
                value="${fechaPago}">
        `,

                showCancelButton: true,

                preConfirm: () => {

                    return document.getElementById(
                        'fechaCancelacion'
                    ).value;
                }
            });

            if (!result.isConfirmed) {
                return;
            }

            try {

                Swal.fire({
                    title: 'Cancelando...',
                    text: 'Cancelando movimiento de pago.',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });


                const response = await fetch(
                    `${gastosRoutes.cancelarPago}/${pagoId}/cancelar`, {
                        method: 'POST',

                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector(
                                'meta[name="csrf-token"]'
                            ).content
                        },

                        body: JSON.stringify({
                            fecha_cancelacion: result.value
                        })
                    }
                );

                const json = await response.json();

                if (json.TMensaje === 'success') {

                    Swal.fire(
                        'Correcto',
                        json.Mensaje,
                        'success'
                    );

                    cargarGastosNew();

                    document
                        .querySelector('.modal.show')
                        ?.querySelector('.btn-close')
                        ?.click();

                    const modalHistorial = bootstrap.Modal.getInstance(
                        document.getElementById('modalHistorialPagos')
                    );

                    if (modalHistorial) {
                        modalHistorial.hide();
                    }


                } else {

                    Swal.fire(
                        'Error',
                        json.Mensaje,
                        'error'
                    );
                }

            } catch (e) {

                console.error(e);

                Swal.fire(
                    'Error',
                    'No fue posible cancelar el pago',
                    'error'
                );
            }
        }

        $(function() {
            const hoy = moment().endOf("day"); // hoy hasta 23:59
            const hace7Dias = moment().subtract(6, "days").startOf(
                "day"); // desde hace 6 días (7 en total)

            // Inicializar daterangepicker
            $("#daterange").daterangepicker({
                    startDate: hace7Dias,
                    endDate: hoy,
                    //  maxDate: hoy, //  bloquear fechas futuras
                    locale: {
                        format: "YYYY-MM-DD",
                        separator: " - ",
                        applyLabel: "Aplicar",
                        cancelLabel: "Cancelar",
                        fromLabel: "Desde",
                        toLabel: "Hasta",
                        customRangeLabel: "Personalizado",
                        weekLabel: "S",
                        daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                        monthNames: [
                            "Enero",
                            "Febrero",
                            "Marzo",
                            "Abril",
                            "Mayo",
                            "Junio",
                            "Julio",
                            "Agosto",
                            "Septiembre",
                            "Octubre",
                            "Noviembre",
                            "Diciembre",
                        ],
                        firstDay: 1,
                    },
                    ranges: {
                        Hoy: [moment(), moment()],
                        "Últimos 7 días": [moment().subtract(6, "days"), moment()],
                        "Últimos 30 días": [moment().subtract(29, "days"), moment()],
                        "Este mes": [
                            moment().startOf("month"),
                            moment().endOf("month"),
                        ],
                        "Mes anterior": [
                            moment().subtract(1, "month").startOf("month"),
                            moment().subtract(1, "month").endOf("month"),
                        ],
                    },
                },
                function(start, end) {



                    window.mesinicio = moment(start).format("YYYY-MM-DD");
                    window.mesfin = moment(end).format("YYYY-MM-DD");

                    actualizarTextoPeriodo();
                    cargarGastosNew();

                },
            );


            $("#daterange").val(
                `${hace7Dias.format("YYYY-MM-DD")} - ${hoy.format("YYYY-MM-DD")}`,
            );

            window.mesinicio = moment(hace7Dias).format("YYYY-MM-DD");
            window.mesfin = moment(hoy).format("YYYY-MM-DD");


            // Cargar datos iniciales
            cargarGastosNew();
            actualizarTextoPeriodo();


        });
    </script>

    <style>
        .option-group {
            display: flex;
            flex-wrap: wrap;
            gap: 16px;
            max-width: 100%;
            margin-bottom: 15px;
        }

        .custom-option {
            position: relative;
            display: flex;
            align-items: center;
            border: 1px dashed #ccc;
            border-radius: 8px;
            padding: 12px 16px;
            min-height: 70px;
            flex: 1 1 200px;
            cursor: pointer;
            transition: background-color 0.2s, border-color 0.2s;
        }

        .custom-option input[type="radio"] {
            display: none;
        }

        .custom-option .icon {
            margin-right: 12px;
            font-size: 20px;
            color: #ccc;
            flex-shrink: 0;
            transition: color 0.2s;
        }

        .custom-option .text {
            font-size: 0.9rem;
            color: #333;
        }

        .custom-option.selected {
            background-color: #e6f4ff;
            border-color: #007bff;
        }

        .custom-option.selected .icon {
            color: #007bff;
        }

        .check-icon {
            position: absolute;
            top: 50%;
            right: 8px;
            transform: translateY(-50%);
            background-color: #a5dc86;
            border-radius: 50%;
            padding: 4px;
            font-size: 11px;
            color: white;
            display: none;
        }

        .custom-option.selected .check-icon {
            display: inline-block;
        }

        /* Ajustes para AG Grid en este modulo */
        .ag-theme-quartz {
            --ag-header-background-color: #f8f9fa;
            --ag-header-foreground-color: #495057;
            --ag-border-color: #e9ecef;
            --ag-row-hover-color: #f1f3f5;
        }
    </style>
@endsection
