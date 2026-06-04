@extends('layouts.usuario_externo')

@section('WorkSpace')
    <style>
        /* ==============================
                                           SWITCH UBICACIÓN
                                           ============================== */

        .switch {
            position: relative;
            display: inline-block;
            width: 200px;
            height: 30px;
        }

        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .slider {
            position: absolute;
            cursor: pointer;
            inset: 0;
            background-color: #ccc;
            transition: 0.4s;
            border-radius: 34px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
        }

        .slider span {
            position: absolute;
            transition: 0.4s;
            font-size: 14px;
        }

        .slider:before {
            position: absolute;
            content: '';
            height: 22px;
            width: 22px;
            left: 4px;
            border-radius: 50%;
            background-color: white;
            transition: 0.4s;
        }

        input:checked+.slider {
            background-color: #4caf50;
        }

        input:checked+.slider:before {
            transform: translateX(170px);
        }

        input:checked+.slider #ubicacion-texto {
            transform: translateX(80px);
        }

        input:not(:checked)+.slider #ubicacion-texto {
            transform: translateX(-80px);
        }

        /* ==============================
                                           GENERALES
                                           ============================== */

        .btn-close {
            filter: invert(1);
        }

        .input-alto {
            height: 38px;
        }

        .loading-overlay {
            position: absolute;
            inset: 0;
            z-index: 10;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.6);
            display: flex;
            justify-content: center;
            align-items: center;
        }

        /* ==============================
                                           TABLA CONTENEDORES EDITAR
                                           ============================== */

        #contenedoreseditar {
            font-size: 0.85rem;
        }

        #contenedoreseditar th,
        #contenedoreseditar td {
            padding: 0.3rem 0.5rem;
            vertical-align: middle;
        }

        #contenedoreseditar thead {
            background-color: #f0f0f0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }

        /* ==============================
                                           TABS
                                           ============================== */

        .nav-tabs .nav-link.active {
            background-color: #0d6efd;
            color: #fff !important;
            font-weight: bold;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        .nav-tabs .nav-link:hover {
            background-color: #e9ecef;
            border-radius: 0.5rem 0.5rem 0 0;
        }

        /* ==============================
                                           CONTENEDOR GENERAL
                                           ============================== */

        .rastreo-page {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        #rastreoTabsContent {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        #rastreo {
            width: 100%;
            max-width: 100%;
            overflow: hidden;
        }

        /* ==============================
                                           MAPA + PANEL
                                           ============================== */

        .rastreo-auto-layout {
            width: 100%;
            max-width: 100%;
            height: 600px;
            max-height: 600px;
            overflow: hidden;
        }

        .rastreo-auto-mapa,
        .rastreo-auto-panel {
            height: 100%;
            min-width: 0;
        }

        .rastreo-auto-mapa {
            overflow: hidden;
        }

        #map {
            width: 100%;
            height: 100% !important;
            min-height: 300px;
        }

        .rastreo-auto-panel {
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
        }

        .rastreo-auto-panel * {
            min-width: 0;
        }

        .rastreo-auto-panel .form-control,
        .rastreo-auto-panel .form-select,
        .rastreo-auto-panel .input-group {
            max-width: 100%;
        }

        /* ==============================
                                           FILTRO TIPO + BOTÓN CONFIG
                                           ============================== */

        #filtroTipo {
            height: 48px;
        }

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

        /* ==============================
                                           BUSCADOR
                                           ============================== */

        #buscadorGeneral {
            width: 100%;
            max-width: 100%;
        }

        #chipsBusqueda {
            max-width: 100%;
            overflow-x: hidden;
        }

        #resultadosBusqueda {
            max-height: 200px;
            overflow-y: auto;
            overflow-x: hidden;
            width: 100%;
            max-width: 100%;
        }

        /* ==============================
                                           PANEL DISPOSITIVOS
                                           ESTE ES EL ÚNICO QUE SCROLLEA
                                           ============================== */

        .panelDispositivosAuto {
            flex: 1 1 auto;
            min-height: 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .panelDispositivosAuto .dispositivos-header {
            position: sticky;
            top: 0;
            z-index: 3;
        }

        #listaDispositivos {
            margin-bottom: 0;
            padding-bottom: 20px;
        }

        #listaDispositivos,
        #listaDispositivos * {
            max-width: 100%;
            overflow-wrap: anywhere;
        }

        /* ==============================
                                           RESPONSIVE
                                           ============================== */

        @media (max-width: 767.98px) {
            .rastreo-auto-layout {
                height: auto !important;
                max-height: none !important;
                overflow: visible;
            }

            .rastreo-auto-mapa {
                height: 420px;
                margin-bottom: 1rem;
            }

            #map {
                height: 420px !important;
            }

            .rastreo-auto-panel {
                height: 600px;
            }
        }
    </style>

    <div class="rastreo-page bg-white w-100 overflow-hidden px-2 py-2 rounded">
        <h3 class="mb-3 text-center">📍 Módulo de Rastreo y Gestión</h3>

        {{-- Tabs --}}
        <ul class="nav nav-tabs" id="rastreoTabs" role="tablist">
            @can('Coordenadas-MEC-Rastreo-vivo')
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-rastreo" data-bs-toggle="tab" data-bs-target="#rastreo" type="button"
                        role="tab">
                        Rastreo en Vivo
                    </button>
                </li>
            @endcan

            @can('Coordenadas-MEC-Gest-Convoys')
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-convoys" data-bs-toggle="tab" data-bs-target="#convoys" type="button"
                        role="tab">
                        Gestión de Convoys
                    </button>
                </li>
            @endcan

            @can('Coordenadas-MEC-Historial-Reportes')
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-historial" data-bs-toggle="tab" data-bs-target="#historial" type="button"
                        role="tab">
                        Historial / Reportes
                    </button>
                </li>
            @endcan
        </ul>

        <div class="tab-content p-2 border border-top-0" id="rastreoTabsContent">

            {{-- Pestaña Rastreo --}}
            <div class="tab-pane fade show active" id="rastreo" role="tabpanel">

                <div class="row rastreo-auto-layout g-0">

                    {{-- MAPA --}}
                    <div class="col-md-9 rastreo-auto-mapa pe-md-2">
                        <div id="map"></div>
                    </div>

                    {{-- PANEL DERECHO --}}
                    <div class="col-md-3 bg-white p-3 rounded shadow-sm rastreo-auto-panel">

                        {{-- FILTROS --}}
                        <div class="mb-2">
                            <button
                                class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-between"
                                type="button" data-bs-toggle="collapse" data-bs-target="#filtrosRastreoMecPanel"
                                aria-expanded="false" aria-controls="filtrosRastreoMecPanel">
                                <span>
                                    <i class="fas fa-filter me-1"></i>
                                    Filtros
                                </span>
                                <i class="fas fa-chevron-down small"></i>
                            </button>

                            <div class="collapse mt-2" id="filtrosRastreoMecPanel">
                                <div class="border rounded bg-light p-2">
                                    <label class="form-label mb-1">Linea Transporte</label>
                                    <select id="filtroLineaT" class="form-select">
                                        <option value="">Todos</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- TIPO --}}
                        <div class="mb-2">
                            <label class="form-label">Tipo</label>

                            <div class="input-group">
                                <select id="filtroTipo" class="form-select">
                                    <option value="">Todos</option>
                                    {{-- <option value="Equipo">Equipos</option> --}}
                                    <option value="Convoy">Convoys</option>
                                    <option value="Contenedor">Contenedores</option>
                                </select>

                                <button class="btn btnConfigVistaMapa" type="button" id="btnOpcionesVistaMapa"
                                    data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false"
                                    title="Configurar vista del mapa">
                                    <i class="fas fa-sliders-h"></i>
                                </button>

                                <div class="dropdown-menu dropdown-menu-end p-3 shadow" style="width: 230px;">
                                    <div class="fw-bold small text-dark mb-2">
                                        <i class="fas fa-eye me-1"></i>
                                        Mostrar en mapa
                                    </div>

                                    <div class="form-check mb-2">
                                        <input class="form-check-input filtroVistaMapaCheck" type="checkbox" value="todos"
                                            id="vistaCheckTodos" checked>
                                        <label class="form-check-label small" for="vistaCheckTodos">
                                            Todos
                                        </label>
                                    </div>

                                    <hr class="my-2">

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
                                            live prueba
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- BUSCADOR --}}
                        <div class="mb-3">
                            <div class="position-relative">
                                <input type="text" id="buscadorGeneral"
                                    placeholder="Buscar convoy, contenedor o equipo..."
                                    class="form-control bg-light shadow-sm w-100" />

                                <div id="chipsBusqueda" class="d-flex flex-wrap gap-2 mt-2"></div>

                                <div id="resultadosBusqueda" class="dropdown-menu show mt-1"></div>
                            </div>
                        </div>

                        {{-- PANEL DISPOSITIVOS --}}
                        <div class="panelDispositivosAuto border rounded p-2">

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
                                    <a class="nav-link active" href="#" onclick="mostrarTab('mail')">📧 Mail</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="#" onclick="mostrarTab('whatsapp')">📲 WhatsApp</a>
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

@push('javascript')
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>
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
        const proveedores = @json($proveedores);

        function ajustarAltoRastreoAuto() {
            const layout = document.querySelector(".rastreo-auto-layout");

            if (!layout) return;

            const rect = layout.getBoundingClientRect();


            const margenInferior = 16;

            let altoDisponible = window.innerHeight - rect.top - margenInferior;


            if (altoDisponible < 420) {
                altoDisponible = 420;
            }

            layout.style.height = altoDisponible + "px";
            layout.style.maxHeight = altoDisponible + "px";

            const mapa = document.getElementById("map");

            if (mapa) {
                mapa.style.height = "100%";
            }


            if (window.google && window.google.maps && window.map) {
                google.maps.event.trigger(window.map, "resize");
            }
        }

        window.addEventListener("load", ajustarAltoRastreoAuto);
        window.addEventListener("resize", ajustarAltoRastreoAuto);

        document.addEventListener("shown.bs.tab", function() {
            setTimeout(ajustarAltoRastreoAuto, 100);
        });

        /*
         * Recalcula cuando se abre/cierra el collapse de filtros,
         * porque cambia la altura disponible para la lista.
         */
        document.addEventListener("shown.bs.collapse", function() {
            setTimeout(ajustarAltoRastreoAuto, 100);
        });

        document.addEventListener("hidden.bs.collapse", function() {
            setTimeout(ajustarAltoRastreoAuto, 100);
        });

        window.escliente = 1;
    </script>
@endpush
