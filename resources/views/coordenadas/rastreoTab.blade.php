{{-- resources/views/coordenadas/dashboard.blade.php --}}
@extends('layouts.app')
@section('template_title')
    Rastreo
@endsection

@section('content')
    <style>
        html,
        body {
            overflow-x: hidden;
        }

        /* Contenedor principal */
        .container-fluid.bg-white {
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Tabs */
        #rastreoTabsContent {
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Pestaña de rastreo */
        #rastreo {
            max-width: 100%;
            overflow-x: hidden;
        }

        /*
                                                               Layout principal del mapa y panel.
                                                               g-0 en el Blade elimina gutters.
                                                            */
        .rastreo-layout {
            height: calc(100vh - 190px);
            min-height: 600px;
            width: 100%;
            max-width: 100%;
            margin-left: 0 !important;
            margin-right: 0 !important;
            overflow-x: hidden;
        }

        /* Permite que las columnas no empujen el ancho */
        .rastreo-layout>[class*="col-"] {
            min-width: 0;
        }

        /* Columna del mapa */
        .rastreo-mapa {
            height: 100%;
            min-width: 0;
            overflow: hidden;
        }

        /* Mapa */
        #map {
            width: 100%;
            height: 100% !important;
            min-height: 500px;
        }

        /* Columna derecha */
        .rastreo-panel {
            height: 100%;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-width: 0;
            max-width: 100%;
        }

        /* Todos los hijos del panel pueden encogerse */
        .rastreo-panel * {
            min-width: 0;
        }

        /* Filtros / inputs */
        .rastreo-panel .form-control,
        .rastreo-panel .form-select,
        .rastreo-panel .input-group {
            max-width: 100%;
        }

        /* Botón de configuración del mapa */
        #btnOpcionesVistaMapa.btnConfigVistaMapa {
            width: 52px;
            height: 48px;
            min-height: 48px;
            flex: 0 0 52px;
            padding: 0;
            margin: 0;

            display: flex;
            align-items: center;
            justify-content: center;

            background: linear-gradient(135deg, #eef5ff, #ffffff);
            border: 1px solid #ced4da;
            border-left: 0;
            color: #0d6efd;

            border-top-right-radius: .375rem;
            border-bottom-right-radius: .375rem;
        }

        #btnOpcionesVistaMapa.btnConfigVistaMapa i {
            font-size: 15px;
            line-height: 1;
        }

        #btnOpcionesVistaMapa.btnConfigVistaMapa:hover {
            background: linear-gradient(135deg, #dbeafe, #ffffff);
            color: #0a58ca;
            border-color: #86b7fe;
        }

        #btnOpcionesVistaMapa.btnConfigVistaMapa:focus,
        #btnOpcionesVistaMapa.btnConfigVistaMapa.show {
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, .18);
            border-color: #86b7fe;
        }

        /* Select tipo */
        #filtroTipo {
            height: 48px;
        }

        /* Buscador */
        #buscadorGeneral {
            width: 100%;
            max-width: 100%;
        }

        /* Chips */
        #chipsBusqueda {
            max-width: 100%;
            overflow-x: hidden;
        }

        /* Resultados buscador */
        #resultadosBusqueda {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
        }

        /* Panel de dispositivos: aquí debe aparecer el scroll */
        .panelDispositivos {
            flex: 1;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
            padding-right: 2px;
        }

        /* Header de dispositivos */
        .panelDispositivos .dispositivos-header {
            position: sticky;
            top: 0;
            z-index: 2;
        }

        /* Lista interna */
        #listaDispositivos {
            padding-bottom: 20px !important;
            margin-bottom: 0;
        }

        /* Evita que textos largos revienten el panel */
        #listaDispositivos,
        #listaDispositivos * {
            max-width: 100%;
            overflow-wrap: anywhere;
        }

        /* Responsive */
        @media (max-width: 767.98px) {
            .rastreo-layout {
                height: auto;
                min-height: auto;
            }

            .rastreo-mapa {
                height: 450px;
                margin-bottom: 1rem;
            }

            #map {
                height: 450px !important;
                min-height: 450px;
            }

            .rastreo-panel {
                height: 600px;
            }
        }
    </style>
    <div class="bg-white w-100 overflow-hidden px-3 py-3 rounded">
        <h3 class="mb-3 text-center">📍 Módulo de Rastreo y Gestión</h3>

        {{-- Tabs --}}
        <ul class="nav nav-tabs" id="rastreoTabs" role="tablist">
            @can('Coordenadas-Rastreo-vivo')
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-rastreo" data-bs-toggle="tab" data-bs-target="#rastreo" type="button"
                        role="tab">
                        Rastreo en Vivo
                    </button>
                </li>
            @endcan

            @can('Coordenadas-Gest-Convoys')
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-convoys" data-bs-toggle="tab" data-bs-target="#convoys" type="button"
                        role="tab">
                        Gestión de Convoys
                    </button>
                </li>
            @endcan

            @can('Coordenadas-Historial-Reportes')
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-historial" data-bs-toggle="tab" data-bs-target="#historial" type="button"
                        role="tab">
                        Historial / Reportes
                    </button>
                </li>
            @endcan

            @can('Coordenadas-Configurar-interval')
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-config" data-bs-toggle="tab" data-bs-target="#config" type="button"
                        role="tab">
                        Configuraciones
                    </button>
                </li>
            @endcan
        </ul>

        <div class="tab-content p-3 border border-top-0" id="rastreoTabsContent">
            {{-- Pestaña Rastreo --}}

            <div class="tab-pane fade show active" id="rastreo" role="tabpanel">
                <div class="row rastreo-layout g-0">

                    {{-- MAPA --}}
                    <div class="col-md-9 rastreo-mapa pe-md-2">
                        <div id="map"></div>
                    </div>

                    {{-- PANEL DERECHO --}}
                    <div class="col-md-3 bg-white p-3 rounded shadow-sm rastreo-panel">

                        {{-- FILTROS COLAPSABLES --}}
                        <div class="mb-2">
                            <button
                                class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-between"
                                type="button" data-bs-toggle="collapse" data-bs-target="#filtrosRastreoPanel"
                                aria-expanded="false" aria-controls="filtrosRastreoPanel">
                                <span>
                                    <i class="fas fa-filter me-1"></i>
                                    Filtros
                                </span>
                                <i class="fas fa-chevron-down small"></i>
                            </button>

                            <div class="collapse mt-2" id="filtrosRastreoPanel">
                                <div class="border rounded bg-light p-2">
                                    <div class="mb-2">
                                        <label class="form-label mb-1">Linea Transporte</label>
                                        <select id="filtroLineaT" class="form-select">
                                            <option value="">Todos</option>
                                        </select>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label mb-1">Cliente</label>
                                        <select id="filtrocliente" class="form-select">
                                            <option value="">Todos</option>
                                        </select>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label mb-1">Fecha salida</label>

                                        <div class="input-group">
                                            <input type="date" id="filtroFechaSalida" class="form-control">

                                            <button type="button" class="btn btn-outline-secondary"
                                                id="btnLimpiarFechaSalida">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- TIPO + CONFIG --}}
                        <div class="mb-2">
                            <label class="form-label">Tipo</label>

                            <div class="input-group">
                                <select id="filtroTipo" class="form-select">
                                    <option value="">Todos</option>
                                    <option value="Equipo">Equipos</option>
                                    <option value="Convoy">Convoys</option>
                                    <option value="Contenedor">Contenedores</option>
                                </select>

                                <button class="btn btnConfigVistaMapa" type="button" id="btnOpcionesVistaMapa"
                                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                                    title="Configurar vista del mapa">
                                    <i class="fas fa-sliders-h"></i>
                                </button>

                                <div class="dropdown-menu dropdown-menu-end p-3 shadow" style="width: 230px;z-index:9855">
                                    <div class="fw-bold small text-dark mb-2">
                                        <i class="fas fa-eye me-1"></i>
                                        Mostrar en mapa
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input filtroVistaMapaCheck" type="checkbox"
                                            value="todos" id="vistaCheckTodos" checked>
                                        <label class="form-check-label small" for="vistaCheckTodos">
                                            Todos
                                        </label>

                                    </div>



                                    <div class="form-check mb-2">
                                        <input class="form-check-input filtroVistaMapaCheck filtroVistaMapaTipo"
                                            type="checkbox" value="Camion" id="vistaCheckCamion" checked>
                                        <label class="form-check-label small" for="vistaCheckCamion">
                                            Tracto
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input filtroVistaMapaCheck filtroVistaMapaTipo"
                                            type="checkbox" value="ChasisA" id="vistaCheckChasisA" checked>
                                        <label class="form-check-label small" for="vistaCheckChasisA">
                                            Chasis A
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input filtroVistaMapaCheck filtroVistaMapaTipo"
                                            type="checkbox" value="ChasisB" id="vistaCheckChasisB" checked>
                                        <label class="form-check-label small" for="vistaCheckChasisB">
                                            Chasis B
                                        </label>
                                    </div>

                                    <div class="pt-2 border-top">
                                        <span class="badge bg-light text-dark border" id="lblFiltroVistaMapa">
                                            Todos
                                        </span>
                                    </div>

                                    <hr class="my-2">

                                    <div class="fw-bold small text-dark mb-2">
                                        <i class="fas fa-map-marker-alt me-1"></i>
                                        Estilo marcador
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input radioVistaMarker" type="radio"
                                            name="vistaMarker" id="vistaMarkerDefault" value="default" checked>
                                        <label class="form-check-label small" for="vistaMarkerDefault">
                                            Default
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input radioVistaMarker" type="radio"
                                            name="vistaMarker" id="vistaMarkertransparente" value="transparente">
                                        <label class="form-check-label small" for="vistaMarkertransparente">
                                            Transparente
                                        </label>
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input radioVistaMarker" type="radio"
                                            name="vistaMarker" id="vistaMarkerlive" value="live">
                                        <label class="form-check-label small" for="vistaMarkerlive">
                                            Clasico
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BUSCADOR --}}
                        <div class="mb-3">
                            <div class="w-100">
                                <div class="position-relative">
                                    <input type="text" id="buscadorGeneral"
                                        placeholder="Buscar convoy, contenedor o equipo..."
                                        class="form-control bg-light shadow-sm w-100" />

                                    <div id="chipsBusqueda" class="d-flex flex-wrap gap-2 mt-2"></div>

                                    <div id="resultadosBusqueda" class="dropdown-menu show mt-1">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- DISPOSITIVOS CON SCROLL --}}
                        <div class="panelDispositivos">
                            <div
                                class="dispositivos-header d-flex justify-content-between align-items-center mb-2 bg-light px-2 py-2 rounded shadow-sm">
                                <h6 class="mb-0 fw-bold">
                                    Dispositivos (<span id="totalDispositivos">0</span>)
                                </h6>

                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input" type="checkbox" id="toggleTodos">
                                    <label class="form-check-label small ms-1" for="toggleTodos" id="labelToggle">
                                        Mostrar Todos
                                    </label>
                                </div>
                            </div>

                            <ul class="list-group" id="listaDispositivos"></ul>
                        </div>

                    </div>
                </div>
            </div>
            {{-- Pestaña Convoys --}}
            <div class="tab-pane fade" id="convoys" role="tabpanel">
                <div class="container-fluid py-4 px-3 bg-gray-100 min-h-screen">
                    <div class="row justify-content-center">
                        <div class="col-sm-12">
                            <div class="card shadow-lg border-0 bg-white rounded-4 p-4">
                                <div class="mb-4 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-primary" data-bs-toggle="modal"
                                            id="btnNuevoconboy">
                                            <i class="bi bi-plus-circle me-1"></i>
                                            Nuevo Convoy
                                        </button>

                                        <button type="button" class="btn btn-info" data-bs-toggle="modal"
                                            id="btnBuscarconboy">
                                            <i class="bi bi-search me-1"></i>
                                            Buscar Convoy
                                        </button>
                                    </div>

                                    <button type="button" class="btn btn-primary d-none" id="btnRastrearconboysSelec">
                                        <i class="bi bi-map-fill me-1"></i>
                                        Rastrear seleccionados
                                    </button>

                                    <li class="nav-item">
                                        <i class="fas fa-route fa-3x me-2 text-primary"></i>
                                        <span class="sidenav-normal">Convoys Virtuales</span>
                                    </li>
                                </div>

                                <div id="myGrid" class="ag-theme-alpine position-relative" style="height: 500px">
                                    <div id="gridLoadingOverlay" class="loading-overlay" style="display: none">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Cargando...</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="modalCambiarEstatus" tabindex="-1" aria-labelledby="estatusModalLabel"
                    aria-hidden="true" data-id="">
                    <div class="modal-dialog">
                        <form id="formCambiarEstatus">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h5 class="modal-title" id="estatusModalLabel">Cambiar Estatus</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Cerrar"></button>
                                </div>
                                <div class="modal-body">
                                    <label for="nuevoEstatus" class="form-label">Selecciona nuevo estatus:</label>
                                    <select class="form-select" id="nuevoEstatus" name="nuevoEstatus" required>
                                        <option value="" selected>-- Selecciona --</option>
                                        <option value="Activo" selected>Activo</option>
                                        <option value="Disuelto">Disuelto</option>
                                    </select>
                                    <input type="hidden" id="idItem" name="idItem" value="" />
                                    <!-- Puedes llenar este input dinámicamente -->
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                                        Cerrar
                                    </button>
                                    <button type="button" class="btn btn-primary" id="btnGuardarCambios">
                                        Guardar Cambios
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="modal fade" id="CreateModal" tabindex="-1" aria-labelledby="filtroModalLabel"
                    aria-hidden="true">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="filtroModalLabel">Crear Convoy Virtual</h5>
                                <!-- Botón de cierre del modal -->
                                <button type="button" class="btn-close" id="btnCerrarModal" data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                            </div>
                            <div class="modal-body">
                                <form id="formFiltros" data-edit-id="0">
                                    <input type="hidden" class="form-control" name="id_convoy" id="id_convoy" />
                                    <div class="mb-3">
                                        <label for="no_convoy" class="form-label">No. Convoy</label>
                                        <input type="text" class="form-control" name="no_convoy" id="no_convoy"
                                            readonly />
                                    </div>
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label for="tipo_disolucion" class="form-label">Tipo de disolución</label>
                                            <select name="tipo_disolucion" id="tipo_disolucion" class="form-select"
                                                required>
                                                <option value="">Seleccione una opción</option>
                                                <option value="geocerca">Geocerca</option>
                                                <option value="tiempo">Tiempo</option>
                                            </select>
                                        </div>
                                        <div class="col-md-4">
                                            <label for="fecha_inicio" class="form-label">Inicio</label>
                                            <input type="datetime-local" name="fecha_inicio" id="fecha_inicio"
                                                class="form-control input-alto" />
                                        </div>
                                        <div class="col-md-4">
                                            <label for="fecha_fin" class="form-label">Fin</label>
                                            <input type="datetime-local" name="fecha_fin" id="fecha_fin"
                                                class="form-control input-alto" />
                                        </div>
                                    </div>

                                    <div id="geocercaConfig" class="mb-3" style="display: none">
                                        <button type="button" class="btn btn-primary" onclick="abrirGeocerca()">
                                            Configurar geocerca
                                        </button>

                                        <!-- Campos ocultos para guardar lat/lng/radio -->
                                        <input type="hidden" name="geocerca_lat" id="geocerca_lat" />
                                        <input type="hidden" name="geocerca_lng" id="geocerca_lng" />
                                        <input type="hidden" name="geocerca_radio" id="geocerca_radio" />
                                    </div>
                                    <div class="col-md-12">
                                        <label for="nombre" class="form-label">Descripción</label>
                                        <input type="text" class="form-control" name="nombre" id="nombre" />
                                    </div>

                                    <div class="mb-3 position-relative">
                                        <label for="contenedor-input" class="form-label">Contenedores</label>
                                        <input type="text" class="form-control" id="contenedor-input"
                                            oninput="mostrarSugerencias()" placeholder="Buscar contenedor..." />
                                        <div id="sugerencias"
                                            style="
                                                border: 1px solid #ccc;
                                                max-height: 150px;
                                                overflow-y: auto;
                                                display: none;
                                                position: absolute;
                                                background: white;
                                                z-index: 1050;
                                                width: 100%;
                                            ">
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                            onclick="agregarContenedor()">
                                            Agregar
                                        </button>
                                        <div id="contenedores-seleccionados" class="mt-2"></div>
                                        <input type="hidden" name="contenedores" id="contenedores" />
                                        <input type="hidden" id="ItemsSelects" name="ItemsSelects" />
                                    </div>
                                    <table class="table table-sm table-bordered align-middle text-center"
                                        style="display: block" id="tablaContenedores">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Contenedor</th>
                                                <th style="width: 20%">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaContenedoresBody">
                                            <!-- Se llenará dinámicamente -->
                                        </tbody>
                                    </table>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-danger text-white" data-bs-dismiss="modal">
                                            <i class="fas fa-times"></i>
                                            Cancelar
                                        </button>
                                        <button type="submit" class="btn btn-primary text-white"
                                            id="btnActualizarEditar">
                                            <i class="fas fa-sync-alt"></i>
                                            Guardar
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="idCotizacionCompartir" value="" />
                <input type="hidden" id="idAsignacionCompartir" value="" />
                <div class="modal" id="modalCoordenadas" tabindex="-1" style="display: none">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content p-3">
                            <h5>Compartir conboys</h5>
                            <div class="form-group"></div>
                            <!-- Tabs -->
                            <ul class="nav nav-tabs mb-3">
                                <li class="nav-item">
                                    <a class="nav-link active" href="#"
                                        onclick="mostrarTab('mail', event, this)">📧
                                        Mail</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" onclick="mostrarTab('whatsapp', event, this)">📲
                                        WhatsApp</a>
                                </li>
                            </ul>

                            <!-- Tab contenido: MAIL -->
                            <div id="tab-mail" class="tab-content">
                                @include('emails.email-conboys')
                            </div>

                            <!-- Tab contenido: WHATSAPP -->
                            <div id="tab-whatsapp" class="tab-content" style="display: none">
                                <label>Se comparte el siguiente no. de Convoy:</label>
                                <div id="wmensajeText" class="mb-2"></div>

                                <a href="#" id="whatsappLink" class="btn btn-success" target="_blank">Abrir
                                    WhatsApp</a>
                            </div>

                            <button class="btn btn-secondary mt-2" onclick="cerrarModal()">Cerrar</button>
                        </div>
                    </div>
                </div>
                <div class="modal fade" id="modalBuscarConvoy" tabindex="-1" aria-labelledby="modalBuscarConvoyLabel"
                    aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
                    <div class="modal-dialog modal-lg modal-dialog-centered">
                        <div class="modal-content rounded-4">
                            <div class="modal-header bg-primary text-white">
                                <h5 class="modal-title" id="modalBuscarConvoyLabel">Buscar Convoy</h5>
                            </div>
                            <div class="modal-body">
                                <form id="formBuscarConvoy" class="mb-3">
                                    <div class="mb-3">
                                        <input type="hidden" class="form-control" name="id_convoy" id="id_convoy" />
                                        <label for="numero_convoy" class="form-label">Número de convoy</label>
                                        <input type="text" class="form-control" id="numero_convoy"
                                            name="numero_convoy" required />
                                    </div>
                                    <button type="submit" class="btn btn-primary">Buscar</button>
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                        onclick="limpiarFormularioConvoy2()">
                                        Cerrar
                                    </button>
                                </form>

                                <div id="resultadoConvoy" style="display: none">
                                    <hr />
                                    <div class="border rounded p-3 mb-4">
                                        <h5 class="text-center text-uppercase border-bottom pb-2 mb-3">
                                            Información del convoy
                                        </h5>

                                        <p>
                                            <strong>Descripción:</strong>
                                            <span id="descripcionConvoy"></span>
                                        </p>

                                        <div class="d-flex flex-wrap">
                                            <p class="me-4">
                                                <strong>Fecha inicio:</strong>
                                                <span id="fechaInicioConvoy"></span>
                                            </p>
                                            <p>
                                                <strong>Fecha fin:</strong>
                                                <span id="fechaFinConvoy"></span>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="mb-3 position-relative">
                                        <label for="contenedor-input" class="form-label">Agregar Contenedores</label>
                                        <input type="text" class="form-control" id="contenedor-input2"
                                            oninput="mostrarSugerencias2()" placeholder="Buscar contenedor..." />
                                        <div id="sugerencias2"
                                            style="
                                                border: 1px solid #ccc;
                                                max-height: 150px;
                                                overflow-y: auto;
                                                display: none;
                                                position: absolute;
                                                background: white;
                                                z-index: 1050;
                                                width: 100%;
                                            ">
                                        </div>
                                        <button type="button" class="btn btn-sm btn-outline-secondary mt-2"
                                            onclick="agregarContenedor2()">
                                            Agregar
                                        </button>
                                        <div id="contenedores-seleccionados2" class="mt-2"></div>
                                        <input type="hidden" name="contenedores" id="contenedores" />
                                        <input type="hidden" id="ItemsSelects" name="ItemsSelects" />
                                    </div>
                                    <table class="table table-sm table-bordered align-middle text-center"
                                        id="tablaContenedoresBuscar">
                                        <thead class="table-light">
                                            <tr>
                                                <th>Contenedor</th>
                                                <th style="width: 20%">Acción</th>
                                            </tr>
                                        </thead>
                                        <tbody id="tablaContenedoresBodyBuscar">
                                            <!-- Se llenará dinámicamente -->
                                        </tbody>
                                    </table>

                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"
                                            onclick="limpiarFormularioConvoy2()">
                                            Cerrar
                                        </button>
                                        <button type="button" class="btn btn-success" id="btnGuardarContenedores">
                                            Guardar
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pestaña Historial --}}
            <div class="tab-pane fade" id="historial" role="tabpanel">
                <h5>
                    <div class="d-flex align-items-center gap-2 px-4 pt-3">
                        <label class="mb-0 fw-semibold text-sm">Periodo:</label>
                        <input type="text" id="daterange" readonly class="form-control form-control-sm"
                            style="width: auto; min-width: 200px; box-shadow: none" />
                    </div>
                </h5>
                <div id="myGridConvoyFinalizados" class="ag-theme-alpine position-relative" style="height: 500px">
                    <div id="gridLoadingOverlay" class="loading-overlay" style="display: none">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="config" role="tabpanel">
                <div class="text-center mb-4">
                    <h3 class="fw-bold text-primary">Configurar Intervalo de Rastreo Automático</h3>
                </div>
                <div class="card shadow-sm border-0">
                    <div class="card-body">
                        <form action="{{ route('scheduler.update') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="interval" class="form-label">Intervalo</label>
                                <select name="interval" id="interval" class="form-select">
                                    <option value="everyMinute"
                                        {{ $intervals->interval == 'everyMinute' ? 'selected' : '' }}>
                                        Cada minuto
                                    </option>
                                    <option value="everyFiveMinutes"
                                        {{ $intervals->interval == 'everyFiveMinutes' ? 'selected' : '' }}>
                                        Cada 5 minutos
                                    </option>
                                    <option value="hourly" {{ $intervals->interval == 'hourly' ? 'selected' : '' }}>
                                        Cada hora
                                    </option>
                                    <option value="daily" {{ $intervals->interval == 'daily' ? 'selected' : '' }}>
                                        Diario
                                    </option>
                                    <option value="weekly" {{ $intervals->interval == 'weekly' ? 'selected' : '' }}>
                                        Semanal
                                    </option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="modalInfoViaje" tabindex="-1" aria-labelledby="modalInfoViajeLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title" id="modalInfoViajeLabel">
                        <i class="bi bi-truck-front-fill me-2"></i>
                        Información del Viaje
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Nav tabs (se generan dinámicamente con los contenedores) -->
                    <ul class="nav nav-tabs" id="contenedorTabs" role="tablist">
                        <!-- Aquí se insertan las pestañas por contenedor -->
                    </ul>

                    <!-- Contenido de cada tab -->
                    <div class="tab-content mt-3" id="contenedorTabsContent">
                        <!-- Aquí se insertan los divs de cada contenedor -->
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script>
        const proveedores = @json($proveedores);
        const clientes = @json($clientes);
    </script>
    <script
        src="{{ asset('js/sgt/coordenadas/coordenadasRastreo.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/coordenadasRastreo.js')) }}">
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googleMapsApi.apikey') }}" async defer
        onload="googleMapsReady()"></script>
    <!-- jQuery -->
    <script src="https://cdn.jsdelivr.net/npm/jquery@3.7.1/dist/jquery.min.js"></script>

    <!-- Moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

    <script>
        window.escliente = 0;
    </script>
@endpush
