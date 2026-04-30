@extends('layouts.usuario_externo')

@section('WorkSpace')
    <style>
        input[type='date'],
        select.form-select {
            height: 35px; /* Ajusta el valor según necesites */
            padding: 0.375rem 0.75rem; /* Igualar el padding de otros campos */
        }
    </style>
    <script>
        const idCliente = @json($idCliente);
    </script>

    <div class="container-fluid py-4 px-3 bg-gray-100 min-h-screen">
        <div class="row justify-content-center">
            <div class="col-sm-12">
                <div class="card shadow-lg border-0 bg-white rounded-4 p-4">
                    <div class="mb-4 d-flex align-items-center justify-content-between">
                        <h3 class="text-xl font-semibold text-center mb-0">Busqueda de Coordenadas Lista</h3>
                    </div>

                    <div class="card mb-3">
                        <div class="card-body py-3">
                            <form id="formFiltros">
                                <div class="row g-2">
                                    <div class="col-md-2">
                                        <label for="contenedor-input" class="form-label">Contenedores</label>
                                        <input
                                            type="text"
                                            class="form-control"
                                            id="contenedor-input"
                                            oninput="mostrarSugerencias()"
                                            placeholder="Buscar contenedor..."
                                        />
                                        <div
                                            id="sugerencias"
                                            style="
                                                border: 1px solid #ccc;
                                                max-height: 150px;
                                                overflow-y: auto;
                                                display: none;
                                                position: absolute;
                                                background: white;
                                                z-index: 1050;
                                                width: 100%;
                                            "
                                        ></div>
                                        <button
                                            type="button"
                                            class="btn btn-sm btn-outline-secondary mt-2"
                                            onclick="agregarContenedor()"
                                        >
                                            Agregar
                                        </button>
                                        <div id="contenedores-seleccionados" class="mt-2"></div>
                                        <input type="hidden" name="contenedores" id="contenedores" />
                                    </div>
                                    <!-- <div class="col-md-4">
                            <label for="proveedor" class="form-label small">Proveedor</label>
                            <select class="form-select form-select-sm" name="proveedor" id="proveedor">
                                <option value="">Todos</option>
                            </select>
                            </div> -->
                                    <div class="col-md-2">
                                        <label for="fecha_inicio" class="form-label small">Fecha Inicio</label>
                                        <input
                                            type="date"
                                            class="form-control form-control-sm"
                                            name="fecha_inicio"
                                            id="fecha_inicio"
                                        />
                                    </div>

                                    <div class="col-md-2">
                                        <label for="fecha_fin" class="form-label small">Fecha Fin</label>
                                        <input
                                            type="date"
                                            class="form-control form-control-sm"
                                            name="fecha_fin"
                                            id="fecha_fin"
                                        />
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-3">
                                        <label for="cliente" class="form-label small">Cliente</label>
                                        <select class="form-select form-select-sm" name="cliente" id="cliente">
                                            <option value="">Todos</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="subcliente" class="form-label small">Subcliente</label>
                                        <select class="form-select form-select-sm" name="subcliente" id="subcliente">
                                            <option value="">Todos</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-center gap-2 mt-3">
                                    <button
                                        type="button"
                                        onclick="limpiarFiltros()"
                                        class="btn btn-outline-secondary btn-sm"
                                    >
                                        Limpiar
                                    </button>
                                    <button type="submit" class="btn btn-primary btn-sm">Buscar</button>
                                </div>
                            </form>
                        </div>
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

    <div
        id="myModal"
        class="modal"
        style="
            display: none;
            width: 100%;
            height: 100%;
            position: fixed;
            top: 0;
            left: 0;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1050;
        "
    >
        <div
            class="modal-content"
            style="
                background: #fff;
                margin: 5% auto;
                padding: 20px;
                border-radius: 8px;
                width: 90%;
                max-width: 600px;
                box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
            "
        >
            <span
                class="close"
                onclick="closeModal()"
                style="float: right; font-size: 24px; cursor: pointer; top: -5px"
            >
                Cerrar
            </span>
            <h3 id="numeroContenedor" style="text-align: center"></h3>
            <div class="modal-body" id="modal-body-cuestionario">
                <!-- Aquí se llenará desde JS -->
            </div>
        </div>
    </div>
    <div
        id="modalMapa"
        class="modal"
        style="
            position: fixed;
            display: none;
            z-index: 1060;
            top: 20%;
            left: 43%;
            width: 500px;
            height: 50%;
            background-color: white;
            border: 1px solid #ccc;
            padding: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.3);
        "
    >
        <div class="modal-header" style="cursor: move">
            <button onclick="cerrarModalMapa()" style="position: absolute; top: 10px; right: 15px">Cerrar</button>
            <h5>Ubicación</h5>
        </div>
        <div id="contenedorMapa" style="width: 100%; height: 80%">
            <iframe
                id="iframeMapa"
                width="100%"
                height="100%"
                style="border: 0"
                allowfullscreen=""
                loading="lazy"
            ></iframe>
        </div>
    </div>

    <!-- Estilos -->
    <style>
        .modal {
            display: none;
            position: fixed;
            z-index: 999;
            padding-top: 60px;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
        }
        .modal-content {
            background: #fff;
            margin: auto;
            padding: 20px;
            width: 60%;
            border-radius: 10px;
            position: relative;
        }
        .close {
            position: absolute;
            top: 10px;
            right: 20px;
            font-size: 24px;
            cursor: pointer;
        }

        #btnVerMapa {
            font-size: 11px; /* texto más pequeño */
            padding: 2px 6px; /* menos relleno */
        }

        #btnCerrar {
            margin-top: -8px; /* Sube el botón */
        }
    </style>
@endsection

@push('javascript')
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <!-- Nuestro JavaScript unificado -->
    <script src="{{ asset('js/sgt/coordenadas/extcoordenadassearch.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/extcoordenadassearch.js')) }}"></script>

    <!-- SweetAlert para mostrar mensajes -->
    <script></script>
@endpush
