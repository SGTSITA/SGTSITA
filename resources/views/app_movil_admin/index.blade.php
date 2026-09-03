@extends('layouts.app')

@section('template_title')
    App Móvil SGT Logistics — Administración y Configuración
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm border-0">
            <div class="card-header pb-0 bg-white">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <div>
                        <h4 class="mb-0 fw-bold text-dark">
                            <i class="fa fa-mobile-alt text-primary me-2"></i>Administración de App Móvil SGT Logistics
                        </h4>
                        <p class="text-xs text-secondary mb-0">Gestione las bitácoras de viajes y las configuraciones de la aplicación móvil.</p>
                    </div>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('app-movil-admin.create') }}" class="btn btn-sm text-white shadow-xs" style="background: {{ $configuracion->color_boton_add ?? '#5e72e4' }}">
                            <i class="fa fa-plus me-1"></i> Vincular Nueva Bitácora
                        </a>
                        <button type="button" class="btn btn-sm btn-outline-primary shadow-xs" data-bs-toggle="modal" data-bs-target="#modalGlobalConfig" onclick="resetConfigModal()">
                            <i class="fa fa-plus-circle me-1"></i> Nuevo Parámetro Global
                        </button>
                    </div>
                </div>

                <!-- Pestañas de Navegación -->
                @php
                    $activeTab = request('tab', 'bitacoras');
                @endphp
                <ul class="nav nav-tabs card-header-tabs" id="appMovilTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold {{ $activeTab === 'bitacoras' ? 'active' : '' }}" id="bitacoras-tab" data-bs-toggle="tab" data-bs-target="#tab-bitacoras" type="button" role="tab" aria-controls="tab-bitacoras" aria-selected="{{ $activeTab === 'bitacoras' ? 'true' : 'false' }}">
                            <i class="fa fa-route me-1"></i> Bitácoras de Viajes
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold {{ $activeTab === 'config' ? 'active' : '' }}" id="config-tab" data-bs-toggle="tab" data-bs-target="#tab-config" type="button" role="tab" aria-controls="tab-config" aria-selected="{{ $activeTab === 'config' ? 'true' : 'false' }}">
                            <i class="fa fa-cogs me-1 text-primary"></i> Configuración de la App Móvil
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold {{ $activeTab === 'global_table' ? 'active' : '' }}" id="global-tab" data-bs-toggle="tab" data-bs-target="#tab-global-table" type="button" role="tab" aria-controls="tab-global-table" aria-selected="{{ $activeTab === 'global_table' ? 'true' : 'false' }}">
                            <i class="fa fa-database me-1 text-info"></i> Tabla de Parámetros (<code>global_configs</code>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold {{ $activeTab === 'logs_app' ? 'active' : '' }}" id="logs-app-tab" data-bs-toggle="tab" data-bs-target="#tab-logs-app" type="button" role="tab" aria-controls="tab-logs-app" aria-selected="{{ $activeTab === 'logs_app' ? 'true' : 'false' }}">
                            <i class="fa fa-terminal me-1 text-danger"></i> Logs App Móvil
                            @if(isset($totalErrorsDia) && $totalErrorsDia > 0)
                                <span class="badge bg-danger text-white rounded-pill ms-1 text-xxs">{{ $totalErrorsDia }}</span>
                            @endif
                        </button>
                    </li>
                </ul>
            </div>
            
            <div class="card-body">
                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show text-white" role="alert">
                        <i class="fa fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif
                @if (session('edit'))
                    <div class="alert alert-info alert-dismissible fade show text-white" role="alert">
                        <i class="fa fa-info-circle me-2"></i> {{ session('edit') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <div class="tab-content" id="appMovilTabsContent">
                    
                    <!-- ========================================== -->
                    <!-- TAB 1: BITACORAS DE VIAJES                 -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade {{ $activeTab === 'bitacoras' ? 'show active' : '' }}" id="tab-bitacoras" role="tabpanel" aria-labelledby="bitacoras-tab">
                        <form action="{{ route('app-movil-admin.index') }}" method="GET" class="mb-4">
                            <input type="hidden" name="tab" value="bitacoras">
                            <div class="input-group">
                                <input type="text" class="form-control" name="buscar" placeholder="Buscar por operador o contenedor..." value="{{ request('buscar') }}">
                                <button class="btn text-white mb-0" style="background: {{ $configuracion->color_boton_save ?? '#2dce89' }}" type="submit">
                                    <i class="fa fa-search"></i> Buscar
                                </button>
                                @if (request('buscar'))
                                    <a href="{{ route('app-movil-admin.index', ['tab' => 'bitacoras']) }}" class="btn btn-outline-secondary mb-0">Limpiar</a>
                                @endif
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table align-items-center mb-0">
                                <thead>
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Asignación / Operador</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contenedor / Cotización</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Diésel (Litros / Costo)</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Urea (Litros / Costo)</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Fecha Inicio</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Fecha Fin</th>
                                        <th class="text-secondary opacity-7 text-center">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($bitacoras as $bitacora)
                                        <tr>
                                            <td>
                                                <div class="d-flex px-2 py-1">
                                                    <div class="d-flex flex-column justify-content-center">
                                                        <h6 class="mb-0 text-sm">Asignación #{{ $bitacora->id_asignacion }}</h6>
                                                        <p class="text-xs text-secondary mb-0">
                                                            <i class="fa fa-user me-1"></i>{{ $bitacora->Asignacion?->Operador?->nombre ?? 'N/A' }}
                                                        </p>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <h6 class="mb-0 text-sm">{{ $bitacora->Asignacion?->Contenedor?->num_contenedor ?? 'N/A' }}</h6>
                                                <p class="text-xs text-secondary mb-0">
                                                    Cotización: #{{ $bitacora->Asignacion?->Contenedor?->id_cotizacion ?? 'N/A' }}
                                                </p>
                                            </td>
                                            <td>
                                                <span class="text-sm font-weight-bold">
                                                    {{ $bitacora->litros ?? '0' }} L
                                                </span>
                                                <p class="text-xs text-secondary mb-0">
                                                    ${{ number_format($bitacora->costo ?? 0, 2) }}
                                                </p>
                                            </td>
                                            <td>
                                                <span class="text-sm font-weight-bold">
                                                    {{ $bitacora->litros_urea ?? '0' }} L
                                                </span>
                                                <p class="text-xs text-secondary mb-0">
                                                    ${{ number_format($bitacora->costo_urea ?? 0, 2) }}
                                                </p>
                                            </td>
                                            <td>
                                                <span class="text-xs font-weight-bold">
                                                    {{ $bitacora->viaje_iniciado ?? 'No Iniciado' }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="text-xs font-weight-bold">
                                                    {{ $bitacora->viaje_finalizado ?? 'En Progreso' }}
                                                </span>
                                            </td>
                                            <td class="align-middle text-center">
                                                <a href="{{ route('app-movil-admin.edit', $bitacora->id) }}" class="btn btn-xs btn-info text-white me-1">
                                                    <i class="fa fa-edit"></i> Editar / Ver
                                                </a>
                                                <form action="{{ route('app-movil-admin.destroy', $bitacora->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este registro de Bitácora?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger text-white">
                                                        <i class="fa fa-trash"></i> Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center py-4 text-muted">No se encontraron registros de bitácoras de la App Móvil.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3">
                            {{ $bitacoras->appends(request()->all())->links() }}
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 2: CONFIGURACIÓN DE LA APP MÓVIL       -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade {{ $activeTab === 'config' ? 'show active' : '' }}" id="tab-config" role="tabpanel" aria-labelledby="config-tab">
                        <form action="{{ route('app-movil-admin.configs.update') }}" method="POST">
                            @csrf
                            
                            <!-- SECCIÓN 1: DOCUMENTOS DEL OPERADOR -->
                            <div class="card border mb-4 shadow-none">
                                <div class="card-header bg-light py-3">
                                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                                        <div>
                                            <h5 class="mb-0 fw-bold text-dark">
                                                <i class="fa fa-folder-open text-warning me-2"></i>1. Documentos Visibles para el Operador
                                            </h5>
                                            <span class="badge bg-secondary font-monospace mt-1">Clave: documentos_operador</span>
                                        </div>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary" onclick="seleccionarTodosDocs(true)">
                                                <i class="fa fa-check-double me-1"></i> Seleccionar Todos
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary" onclick="seleccionarTodosDocs(false)">
                                                <i class="fa fa-times me-1"></i> Deseleccionar Todos
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body">
                                    <p class="text-sm text-secondary mb-3">
                                        Seleccione los documentos que el operador podrá consultar y descargar en su aplicación móvil al revisar el flujo de su viaje asignado.
                                    </p>

                                    <div class="row g-3">
                                        @foreach ($documentosDisponibles as $keyDoc => $docInfo)
                                            @php
                                                $isSelected = in_array($keyDoc, $documentosSeleccionados) || in_array(strtolower($docInfo['label']), array_map('strtolower', $documentosSeleccionados));
                                            @endphp
                                            <div class="col-md-6 col-lg-4">
                                                <div class="p-3 border rounded h-100 bg-white shadow-xs doc-checkbox-card {{ $isSelected ? 'border-primary' : '' }}" style="cursor: pointer;" onclick="toggleDocCheckbox('doc_{{ $keyDoc }}')">
                                                    <div class="form-check form-switch d-flex align-items-start gap-2">
                                                        <input class="form-check-input mt-1 doc-checkbox" type="checkbox" name="documentos_operador[]" value="{{ $keyDoc }}" id="doc_{{ $keyDoc }}" {{ $isSelected ? 'checked' : '' }} onclick="event.stopPropagation(); updateDocCardStyle(this);">
                                                        <label class="form-check-label w-100 ms-1 cursor-pointer" for="doc_{{ $keyDoc }}">
                                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                                <i class="fa {{ $docInfo['icono'] }} text-primary"></i>
                                                                <span class="fw-bold text-dark text-sm">{{ $docInfo['label'] }}</span>
                                                            </div>
                                                            <p class="text-xs text-muted mb-0">{{ $docInfo['descripcion'] }}</p>
                                                            <span class="badge bg-light text-secondary font-monospace text-xxs mt-1">{{ $keyDoc }}</span>
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>

                                    <div class="mt-3 p-3 bg-light rounded">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                            <span class="text-xs fw-bold text-secondary">Valor JSON actual que se guardará en la base de datos:</span>
                                            <span id="jsonPreviewDocs" class="font-monospace text-xs text-primary bg-white px-2 py-1 rounded border">
                                                {{ json_encode(array_values($documentosSeleccionados)) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- SECCIÓN 2: NOTIFICACIÓN DE CAPTURA DE GASTOS -->
                            <div class="card border mb-4 shadow-none">
                                <div class="card-header bg-light py-3">
                                    <h5 class="mb-0 fw-bold text-dark">
                                        <i class="fa fa-bell text-danger me-2"></i>2. Horario de Recordatorio para Captura de Gastos
                                    </h5>
                                    <span class="badge bg-secondary font-monospace mt-1">Clave: tiempo_notificacion_captura_gastos</span>
                                </div>
                                <div class="card-body">
                                    <p class="text-sm text-secondary mb-3">
                                        Configure el día y la hora en que la aplicación móvil enviará la notificación automática para recordar al operador capturar sus comprobantes o gastos de viaje.
                                    </p>

                                    <div class="row g-3 align-items-center">
                                        <div class="col-md-5">
                                            <label class="form-label fw-bold text-dark text-sm">
                                                <i class="fa fa-calendar-day me-1 text-primary"></i> Día de la Semana
                                            </label>
                                            <select name="notif_dia" id="notif_dia" class="form-select" onchange="updateNotifPreview()">
                                                @foreach ($diasSemana as $numDia => $nombreDia)
                                                    <option value="{{ $numDia }}" {{ (string)$notifDia === (string)$numDia ? 'selected' : '' }}>
                                                        {{ $nombreDia }} (Día {{ $numDia }})
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="col-md-4">
                                            <label class="form-label fw-bold text-dark text-sm">
                                                <i class="fa fa-clock me-1 text-primary"></i> Hora de Notificación (24h)
                                            </label>
                                            <input type="time" name="notif_hora" id="notif_hora" class="form-control" value="{{ $notifHora }}" onchange="updateNotifPreview()" required>
                                        </div>

                                        <div class="col-md-3">
                                            <label class="form-label text-muted text-xs d-block">Formato Almacenado</label>
                                            <div class="p-2 border rounded bg-light text-center font-monospace text-sm fw-bold text-primary" id="previewNotifString">
                                                {{ $notifDia }} {{ $notifHora }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- BOTÓN GUARDAR -->
                            <div class="d-flex justify-content-end gap-2">
                                <button type="submit" class="btn btn-primary px-4 shadow-sm fw-bold">
                                    <i class="fa fa-save me-1"></i> Guardar Configuraciones de App Móvil
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 3: TABLA DE PARÁMETROS GLOBALES        -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade {{ $activeTab === 'global_table' ? 'show active' : '' }}" id="tab-global-table" role="tabpanel" aria-labelledby="global-tab">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
                            <div>
                                <h5 class="mb-0 fw-bold text-dark">Todos los Parámetros Globales (<code>global_configs</code>)</h5>
                                <p class="text-xs text-secondary mb-0">Permite agregar, modificar o inspeccionar cualquier parámetro utilizado por la API y la App Móvil.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-primary shadow-xs" data-bs-toggle="modal" data-bs-target="#modalGlobalConfig" onclick="resetConfigModal()">
                                <i class="fa fa-plus me-1"></i> Agregar Nuevo Parámetro
                            </button>
                        </div>

                        <div class="table-responsive border rounded">
                            <table class="table table-hover align-items-center mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 25%;">Clave (key)</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 35%;">Valor (value)</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 25%;">Descripción</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 15%;">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($todasLasConfigs as $gConfig)
                                        <tr>
                                            <td>
                                                <span class="badge bg-primary text-white font-monospace text-xs">
                                                    {{ $gConfig->key }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-xs font-monospace text-dark text-break bg-light p-2 rounded" style="max-height: 100px; overflow-y: auto;">
                                                    {{ $gConfig->value ?? '—' }}
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-xs text-secondary">
                                                    {{ $gConfig->description ?? 'Sin descripción' }}
                                                </span>
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-xs btn-info text-white me-1" onclick="editConfigModal({{ json_encode($gConfig) }})">
                                                    <i class="fa fa-edit"></i> Editar
                                                </button>
                                                <form action="{{ route('app-movil-admin.configs.destroy', $gConfig->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este parámetro global?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-xs btn-danger text-white">
                                                        <i class="fa fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="text-center py-4 text-muted">No hay parámetros globales registrados.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- ========================================== -->
                    <!-- TAB 4: LOGS DE LA APP MÓVIL                -->
                    <!-- ========================================== -->
                    <div class="tab-pane fade {{ $activeTab === 'logs_app' ? 'show active' : '' }}" id="tab-logs-app" role="tabpanel" aria-labelledby="logs-app-tab">
                        
                        <!-- TARJETAS DE ESTADÍSTICAS RÁPIDAS -->
                        <div class="row g-3 mb-4">
                            <div class="col-md-3">
                                <div class="card border-0 bg-light shadow-xs p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape bg-gradient-primary shadow text-center border-radius-md me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="fa fa-calendar-day text-white"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-secondary mb-0 text-uppercase font-weight-bold">Fecha Activa</p>
                                            <h6 class="font-weight-bolder mb-0 text-dark">{{ $selectedLogDate }}</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 bg-light shadow-xs p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape bg-gradient-info shadow text-center border-radius-md me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="fa fa-list-alt text-white"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-secondary mb-0 text-uppercase font-weight-bold">Total Registros</p>
                                            <h6 class="font-weight-bolder mb-0 text-info">{{ $totalLogsDia }} eventos</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 bg-light shadow-xs p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape bg-gradient-danger shadow text-center border-radius-md me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="fa fa-exclamation-triangle text-white"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-secondary mb-0 text-uppercase font-weight-bold">Errores Detectados</p>
                                            <h6 class="font-weight-bolder mb-0 text-danger">{{ $totalErrorsDia }} errores</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="card border-0 bg-light shadow-xs p-3">
                                    <div class="d-flex align-items-center">
                                        <div class="icon icon-shape bg-gradient-success shadow text-center border-radius-md me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                            <i class="fa fa-network-wired text-white"></i>
                                        </div>
                                        <div>
                                            <p class="text-xs text-secondary mb-0 text-uppercase font-weight-bold">Peticiones Red / HTTP</p>
                                            <h6 class="font-weight-bolder mb-0 text-success">{{ $totalHttpDia }} llamadas</h6>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- BARRA DE FILTROS POR FECHA, NIVEL Y BÚSQUEDA -->
                        <div class="card border shadow-xs mb-4">
                            <div class="card-body p-3">
                                <form action="{{ route('app-movil-admin.index') }}" method="GET" class="row g-2 align-items-end">
                                    <input type="hidden" name="tab" value="logs_app">
                                    
                                    <div class="col-md-3">
                                        <label class="form-label text-xs fw-bold text-dark mb-1">
                                            <i class="fa fa-calendar-alt text-primary me-1"></i> Seleccionar Fecha
                                        </label>
                                        <input type="date" name="log_fecha" class="form-control form-control-sm" value="{{ $selectedLogDate }}" max="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" onchange="this.form.submit()">
                                    </div>

                                    <div class="col-md-2">
                                        <label class="form-label text-xs fw-bold text-dark mb-1">
                                            <i class="fa fa-filter text-info me-1"></i> Nivel
                                        </label>
                                        <select name="log_nivel" class="form-select form-select-sm" onchange="this.form.submit()">
                                            <option value="">-- Todos los Niveles --</option>
                                            <option value="ERROR" {{ $logNivel === 'ERROR' ? 'selected' : '' }}>🔴 Solo Errores (ERROR)</option>
                                            <option value="HTTP" {{ $logNivel === 'HTTP' ? 'selected' : '' }}>🌐 Peticiones HTTP</option>
                                            <option value="WARN" {{ $logNivel === 'WARN' ? 'selected' : '' }}>🟡 Advertencias (WARN)</option>
                                            <option value="INFO" {{ $logNivel === 'INFO' ? 'selected' : '' }}>🔵 Información (INFO)</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label class="form-label text-xs fw-bold text-dark mb-1">
                                            <i class="fa fa-search text-secondary me-1"></i> Buscar en logs
                                        </label>
                                        <input type="text" name="log_buscar" class="form-control form-control-sm" placeholder="Operador, endpoint, timeout..." value="{{ $logBusqueda }}">
                                    </div>

                                    <div class="col-md-4 d-flex flex-wrap gap-1 justify-content-end">
                                        <button type="submit" class="btn btn-sm text-white mb-0" style="background: {{ $configuracion->color_boton_save ?? '#2dce89' }}">
                                            <i class="fa fa-search me-1"></i> Filtrar
                                        </button>
                                        @if ($logBusqueda || $logNivel || $selectedLogDate !== \Carbon\Carbon::now()->format('Y-m-d'))
                                            <a href="{{ route('app-movil-admin.index', ['tab' => 'logs_app']) }}" class="btn btn-sm btn-outline-secondary mb-0">
                                                <i class="fa fa-redo me-1"></i> Hoy
                                            </a>
                                        @endif
                                        @if (count($appLogs) > 0)
                                            <a href="{{ route('app-movil-admin.logs.descargar', ['fecha' => $selectedLogDate]) }}" class="btn btn-sm btn-outline-info mb-0" title="Descargar archivo JSON">
                                                <i class="fa fa-download me-1"></i> Exportar JSON
                                            </a>
                                            <button type="button" class="btn btn-sm btn-outline-danger mb-0" onclick="confirmarLimpiezaLogs('{{ $selectedLogDate }}')">
                                                <i class="fa fa-trash-alt me-1"></i> Limpiar
                                            </button>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- TABLA DETALLADA DE LOGS -->
                        <div class="table-responsive border rounded">
                            <table class="table align-items-center mb-0 table-hover">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7" style="width: 140px;">Hora / Fecha</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="width: 90px;">Nivel</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="width: 180px;">Operador / Usuario</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2" style="width: 140px;">Dispositivo</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Mensaje / Endpoint</th>
                                        <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 text-center" style="width: 110px;">Detalle</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($appLogs as $item)
                                        @php
                                            $isError = ($item['level'] ?? '') === 'ERROR' || !empty($item['error']) || str_contains($item['message'] ?? '', 'ERROR') || str_contains($item['message'] ?? '', 'STATUS: 5') || str_contains($item['message'] ?? '', 'STATUS: 4');
                                            $badgeClass = 'bg-secondary';
                                            if (($item['level'] ?? '') === 'ERROR' || $isError) {
                                                $badgeClass = 'bg-danger';
                                            } elseif (($item['level'] ?? '') === 'HTTP') {
                                                $badgeClass = 'bg-info';
                                            } elseif (($item['level'] ?? '') === 'WARN') {
                                                $badgeClass = 'bg-warning text-dark';
                                            } elseif (($item['level'] ?? '') === 'INFO') {
                                                $badgeClass = 'bg-primary';
                                            }

                                            // Formateo de fecha y hora
                                            $rawTime = $item['timestamp'] ?? $item['server_time'] ?? '';
                                            $formattedTime = $rawTime;
                                            try {
                                                $formattedTime = \Carbon\Carbon::parse($rawTime)->format('H:i:s d/m');
                                            } catch (\Exception $e) {}
                                        @endphp
                                        <tr class="{{ $isError ? 'table-danger-subtle' : '' }}">
                                            <td>
                                                <span class="text-xs font-monospace font-weight-bold text-dark">
                                                    <i class="fa fa-clock text-secondary me-1"></i>{{ $formattedTime }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="badge {{ $badgeClass }} text-xxs px-2 py-1">
                                                    {{ $item['level'] ?? 'LOG' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="d-flex align-items-center">
                                                    <i class="fa fa-user-circle text-primary me-2"></i>
                                                    <span class="text-xs font-weight-bold text-dark text-truncate" style="max-width: 170px;" title="{{ $item['usuario'] ?? 'Desconocido' }}">
                                                        {{ $item['usuario'] ?? 'Desconocido' }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="text-xs text-secondary font-monospace">
                                                    <i class="fa fa-mobile-alt me-1"></i>{{ $item['device'] ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="text-xs font-monospace text-dark text-break" style="max-width: 480px;">
                                                    {{ $item['message'] ?? '—' }}
                                                </div>
                                                @if (!empty($item['error']))
                                                    <div class="text-xxs text-danger font-monospace text-break mt-1 bg-white p-1 rounded border border-danger">
                                                        <i class="fa fa-times-circle me-1"></i>{{ Str::limit($item['error'], 140) }}
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-xs btn-outline-dark mb-0 shadow-none" onclick="verDetalleLogModal({{ json_encode($item) }})" title="Ver Payload Completo">
                                                    <i class="fa fa-eye me-1"></i> Ver
                                                </button>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="6" class="text-center py-5 text-muted">
                                                <i class="fa fa-clipboard-check fa-3x text-secondary mb-3 d-block opacity-5"></i>
                                                <h6 class="text-secondary font-weight-bold">No hay registros de logs para la fecha seleccionada ({{ $selectedLogDate }}).</h6>
                                                <p class="text-xs text-muted mb-0">Los logs se sincronizan automáticamente en segundo plano cuando la app móvil detecta actividad o errores.</p>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-3 text-xs text-muted text-end">
                            <i class="fa fa-folder-open text-primary me-1"></i> Almacenamiento directo en servidor: <code>storage/logs/app_movil/app_logs_{{ $selectedLogDate }}.json</code>
                        </div>

                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- MODAL PARA VER DETALLE TÉCNICO DE UN LOG -->
<div class="modal fade" id="modalDetalleLog" tabindex="-1" aria-labelledby="modalDetalleLogLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title text-white" id="modalDetalleLogLabel">
                    <i class="fa fa-terminal me-2 text-warning"></i>Detalle Técnico del Evento
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="text-xs text-secondary text-uppercase fw-bold mb-0">Usuario / Operador</label>
                        <p class="text-sm font-weight-bold text-dark mb-0" id="log_modal_usuario">—</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-xs text-secondary text-uppercase fw-bold mb-0">Dispositivo y SO</label>
                        <p class="text-sm font-weight-bold text-dark mb-0 font-monospace" id="log_modal_device">—</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-xs text-secondary text-uppercase fw-bold mb-0">Fecha y Hora</label>
                        <p class="text-sm font-weight-bold text-dark mb-0 font-monospace" id="log_modal_time">—</p>
                    </div>
                    <div class="col-md-6">
                        <label class="text-xs text-secondary text-uppercase fw-bold mb-0">Nivel de Gravedad</label>
                        <div id="log_modal_level">—</div>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="text-xs text-secondary text-uppercase fw-bold mb-1">Mensaje / Endpoint Registrado</label>
                    <div class="p-3 bg-light rounded border font-monospace text-xs text-dark" id="log_modal_mensaje" style="max-height: 120px; overflow-y: auto;">
                        —
                    </div>
                </div>

                <div class="mb-3" id="log_modal_error_wrapper">
                    <label class="text-xs text-danger text-uppercase fw-bold mb-1">
                        <i class="fa fa-exclamation-circle me-1"></i> Detalle del Error / Stack Trace
                    </label>
                    <div class="p-3 bg-danger-subtle rounded border border-danger font-monospace text-xs text-danger" id="log_modal_error" style="max-height: 180px; overflow-y: auto; white-space: pre-wrap;">
                        —
                    </div>
                </div>

                <div>
                    <label class="text-xs text-secondary text-uppercase fw-bold mb-1">Objeto JSON Completo</label>
                    <pre class="p-3 bg-dark text-white rounded font-monospace text-xxs mb-0" id="log_modal_json" style="max-height: 200px; overflow-y: auto;"></pre>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<!-- FORM OCULTO PARA LIMPIEZA DE LOGS -->
<form id="formLimpiarLogs" action="{{ route('app-movil-admin.logs.limpiar') }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="fecha" id="limpiar_log_fecha" value="">
</form>

<!-- MODAL PARA AGREGAR / EDITAR PARÁMETRO GLOBAL -->
<div class="modal fade" id="modalGlobalConfig" tabindex="-1" aria-labelledby="modalGlobalConfigLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('app-movil-admin.configs.custom.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title text-white" id="modalGlobalConfigLabel">
                        <i class="fa fa-cog me-2"></i>Parámetro Global
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark text-sm">Clave del Parámetro (Key) <span class="text-danger">*</span></label>
                        <input type="text" name="key" id="modal_config_key" class="form-control font-monospace" placeholder="ej. tiempo_notificacion_captura_gastos" required>
                        <small class="text-muted text-xs">Identificador único del parámetro en <code>global_configs</code>.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark text-sm">Valor (Value)</label>
                        <textarea name="value" id="modal_config_value" class="form-control font-monospace" rows="4" placeholder='ej. ["doda", "boleta_liberacion"] o 6 10:00'></textarea>
                        <small class="text-muted text-xs">Puede ser texto simple, número o formato JSON.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark text-sm">Descripción</label>
                        <input type="text" name="description" id="modal_config_description" class="form-control" placeholder="Breve descripción del propósito de esta configuración">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm fw-bold">
                        <i class="fa fa-save me-1"></i> Guardar Parámetro
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function toggleDocCheckbox(id) {
        const checkbox = document.getElementById(id);
        if (checkbox) {
            checkbox.checked = !checkbox.checked;
            updateDocCardStyle(checkbox);
        }
    }

    function updateDocCardStyle(checkbox) {
        const card = checkbox.closest('.doc-checkbox-card');
        if (card) {
            if (checkbox.checked) {
                card.classList.add('border-primary');
            } else {
                card.classList.remove('border-primary');
            }
        }
        updateDocPreview();
    }

    function seleccionarTodosDocs(status) {
        document.querySelectorAll('.doc-checkbox').forEach(cb => {
            cb.checked = status;
            const card = cb.closest('.doc-checkbox-card');
            if (card) {
                if (status) {
                    card.classList.add('border-primary');
                } else {
                    card.classList.remove('border-primary');
                }
            }
        });
        updateDocPreview();
    }

    function updateDocPreview() {
        const selected = [];
        document.querySelectorAll('.doc-checkbox:checked').forEach(cb => {
            selected.push(cb.value);
        });
        const previewElem = document.getElementById('jsonPreviewDocs');
        if (previewElem) {
            previewElem.textContent = JSON.stringify(selected);
        }
    }

    function updateNotifPreview() {
        const dia = document.getElementById('notif_dia')?.value || '6';
        const hora = document.getElementById('notif_hora')?.value || '10:00';
        const preview = document.getElementById('previewNotifString');
        if (preview) {
            preview.textContent = dia + ' ' + hora;
        }
    }

    function resetConfigModal() {
        document.getElementById('modalGlobalConfigLabel').innerHTML = '<i class="fa fa-plus-circle me-2"></i>Nuevo Parámetro Global';
        document.getElementById('modal_config_key').value = '';
        document.getElementById('modal_config_key').readOnly = false;
        document.getElementById('modal_config_value').value = '';
        document.getElementById('modal_config_description').value = '';
    }

    function editConfigModal(config) {
        document.getElementById('modalGlobalConfigLabel').innerHTML = '<i class="fa fa-edit me-2"></i>Editar Parámetro Global';
        document.getElementById('modal_config_key').value = config.key;
        document.getElementById('modal_config_value').value = config.value || '';
        document.getElementById('modal_config_description').value = config.description || '';
        
        const modal = new bootstrap.Modal(document.getElementById('modalGlobalConfig'));
        modal.show();
    }

    function verDetalleLogModal(logItem) {
        document.getElementById('log_modal_usuario').textContent = logItem.usuario || 'Desconocido';
        document.getElementById('log_modal_device').textContent = logItem.device || 'N/A';
        document.getElementById('log_modal_time').textContent = (logItem.timestamp || logItem.server_time || '—') + (logItem.server_time ? ' (Servidor: ' + logItem.server_time + ')' : '');
        
        const lvl = logItem.level || 'INFO';
        let badgeColor = 'bg-secondary';
        if (lvl === 'ERROR' || logItem.error) badgeColor = 'bg-danger';
        else if (lvl === 'HTTP') badgeColor = 'bg-info';
        else if (lvl === 'WARN') badgeColor = 'bg-warning text-dark';
        else if (lvl === 'INFO') badgeColor = 'bg-primary';

        document.getElementById('log_modal_level').innerHTML = `<span class="badge ${badgeColor}">${lvl}</span>`;
        document.getElementById('log_modal_mensaje').textContent = logItem.message || '—';

        const errorWrapper = document.getElementById('log_modal_error_wrapper');
        const errorContent = document.getElementById('log_modal_error');
        if (logItem.error) {
            errorWrapper.style.display = 'block';
            errorContent.textContent = logItem.error;
        } else {
            errorWrapper.style.display = 'none';
        }

        document.getElementById('log_modal_json').textContent = JSON.stringify(logItem, null, 2);

        const modal = new bootstrap.Modal(document.getElementById('modalDetalleLog'));
        modal.show();
    }

    function confirmarLimpiezaLogs(fecha) {
        if (confirm(`¿Está seguro de eliminar todos los registros de logs del día ${fecha}? Esta acción no se puede deshacer.`)) {
            document.getElementById('limpiar_log_fecha').value = fecha;
            document.getElementById('formLimpiarLogs').submit();
        }
    }
</script>
@endsection
