@extends('layouts.app')

@section('template_title', 'Cotizaciones')

@section('content')
    <style>
        #myGrid {
            height: 500px;
            width: 100%;
        }

        #cotTabs .nav-link {
            cursor: pointer;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            z-index: 10; /* asegúrate que esté por encima del grid */
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.6); /* opcional para desenfoque */
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .tab-chasis .nav-link {
            width: 50%;
            text-align: center;
            font-weight: bold;
            padding: 12px;
            border-radius: 0;
            border: 1px solid #dee2e6;
            color: #0d6efd;
        }

        .tab-chasis .nav-link.active {
            background-color: #0d6efd;
            color: white !important;
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span id="card_title">Cotizaciones</span>
                        @can('cotizaciones-create')
                            <div>
                                <a
                                    type="button"
                                    class="btn bg-gradient-info btn-xs mb-2"
                                    href="{{ route('create.cotizaciones') }}"
                                >
                                    +&nbsp; Crear Cotizacion
                                </a>
                                <button type="button" class="btn bg-gradient-success btn-xs mb-2" id="btnFull" disabled>
                                    <i class="fas fa-truck-moving"></i>
                                    Convertir a Full
                                </button>
                            </div>
                        @endcan

                        @can('mep-asignacion-unidad')
                            <div>
                                <button type="button" class="btn bg-gradient-info btn-xs mb-2" id="abrirModalBtn">
                                    +&nbsp; Planear viaje
                                </button>
                            </div>
                        @endcan
                    </div>

                    <!-- Pestañas sin recargar página -->
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1 flex-row" id="cotTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-status="planeadas">
                                    <i class="fa-solid fa-clipboard-list" style="font-size: 18px"></i>
                                    <span class="ms-2">Planeadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="finalizadas">
                                    <i class="fa-solid fa-check-circle" style="font-size: 18px"></i>
                                    <span class="ms-2">Finalizadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="en_espera">
                                    <i class="fa-solid fa-clock" style="font-size: 18px"></i>
                                    <span class="ms-2">En Espera</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="aprobadas">
                                    <i class="fa-solid fa-thumbs-up" style="font-size: 18px"></i>
                                    <span class="ms-2">Aprobadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="canceladas">
                                    <i class="fa-solid fa-ban" style="font-size: 18px"></i>
                                    <span class="ms-2">Canceladas</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Contenedor de AG Grid -->
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
    <div id="estadoCuestionarios" style="display: none">
        <input type="hidden" id="estadoC" name="estadoC" value="0" />
        <input type="hidden" id="estadoB" name="estadoB" value="0" />
        <input type="hidden" id="estadoF" name="estadoF" value="0" />
    </div>
    <input type="hidden" id="idCotizacionCompartir" value="" />
    <input type="hidden" id="idAsignacionCompartir" value="" />
    <!-- Modal Coordenadas con Tabs -->
    <div class="modal" id="modalCoordenadas" tabindex="-1" style="display: none">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content p-3">
                <h5>Compartir coordenadas</h5>
                <div class="form-group">
                    <label for="optipoCuestionario">Seleccione tipo de cuestionario</label>
                    <select id="optipoCuestionario" name="tipoCuestionario" class="form-control">
                        <option value="" disabled selected>Seleccione tipo</option>
                        <option value="b">Burrero</option>
                        <option value="f">Foráneo</option>
                        <option value="c">Completo</option>
                    </select>
                </div>
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
                    @include('emails.email-coordenadas')
                </div>

                <!-- Tab contenido: WHATSAPP -->
                <div id="tab-whatsapp" class="tab-content" style="display: none">
                    <label>Contenedor:</label>
                    <div id="wmensajeText" class="mb-2"></div>

                    <label>Enlace para compartir por WhatsApp:</label>
                    <input type="text" id="linkWhatsapp" class="form-control mb-2" readonly />

                    <button class="btn btn-primary mb-2" onclick="copiarDesdeInput('linkWhatsapp')">
                        📋 Copiar enlace
                    </button>
                    <a
                        href="#"
                        id="whatsappLink"
                        class="btn btn-success"
                        target="_blank"
                        onclick="guardarYAbrirWhatsApp(event)"
                    >
                        Abrir WhatsApp
                    </a>
                </div>

                <button class="btn btn-secondary mt-2" onclick="cerrarModal()">Cerrar</button>
            </div>
        </div>
    </div>
    <!-- Modal: Cambio de Empresa -->
    <div
        class="modal fade"
        id="modalCambioEmpresa"
        tabindex="-1"
        aria-labelledby="modalCambioEmpresaLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <form method="POST" id="formCambioEmpresa" action="" enctype="multipart/form-data" class="p-3">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH" />

                    <div class="modal-header text-white rounded-top-4">
                        <h5 class="modal-title">
                            <i class="fa-solid fa-building-circle-arrow-right me-2"></i>
                            Cambio de Empresa
                        </h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>
                    </div>

                    <div class="modal-body pt-4">
                        <div class="mb-3">
                            <label for="id_empresa" class="form-label fw-semibold">Seleccione la nueva empresa *</label>
                            <div class="input-group">
                                <select class="form-select border-start-0" id="id_empresa" name="id_empresa" required>
                                    <option value="">Seleccione empresa</option>
                                    @foreach ($empresas as $empresa)
                                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer border-top-0 pt-3">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-success">
                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <!-- Modal: Estatus de Documentos -->
    <div
        class="modal fade"
        id="modalEstatusDocumentos"
        tabindex="-1"
        aria-labelledby="modalEstatusDocumentosLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header text-white rounded-top-4">
                    <h5 class="modal-title d-flex align-items-center" id="modalEstatusDocumentosLabel">
                        <i class="fa-solid fa-folder-open me-2"></i>
                        Estatus de Documentos
                        <span id="tituloContenedor" class="ms-2 t fw-bold"></span>
                    </h5>
                    <button
                        type="button"
                        class="btn-close btn-close-white"
                        data-bs-dismiss="modal"
                        aria-label="Cerrar"
                    ></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3" id="estatusDocumentosBody">
                        {{-- Aquí se insertan dinámicamente los checkboxes --}}
                    </div>
                </div>

                <div class="modal-footer border-top-0 d-flex justify-content-end px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-1"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal: Cambio de Estatus -->
    <div
        class="modal fade"
        id="modalCambioEstatus"
        tabindex="-1"
        aria-labelledby="modalCambioEstatusLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <form id="formCambioEstatus" method="POST" action="" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH" />

                    <div class="modal-header text-white py-3">
                        <h5 class="modal-title d-flex align-items-center" id="modalCambioEstatusLabel">
                            <i class="fa-solid fa-sync-alt me-2"></i>
                            Cambio de Estatus
                        </h5>
                        <button
                            type="button"
                            class="btn-close btn-close-white"
                            data-bs-dismiss="modal"
                            aria-label="Cerrar"
                        ></button>
                    </div>

                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label for="estatus" class="form-label fw-bold text-dark">
                                Seleccione el nuevo estatus
                                <span class="text-danger">*</span>
                            </label>
                            <div class="input-group rounded-3 shadow-sm">
                                <select class="form-select border-start-0" name="estatus" id="estatus" required>
                                    <option value="">Seleccionar Estatus</option>
                                    <option value="Pendiente">Pendiente</option>
                                    <option value="Aprobada">Aprobada</option>
                                    <option value="Cancelada">Cancelada</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top-0 px-4 py-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-1"></i>
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i>
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @can('mep-asignacion-unidad')
        @include('mep.viajes.modal-asignar')
        @include('mep.viajes.modal-alert')
    @endcan
@endsection

@push('custom-javascript')
    <!-- AG Grid -->
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <!-- Nuestro JavaScript unificado -->
    <script src="{{ asset('js/sgt/cotizaciones/cotizaciones_list.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones_list.js')) }}"></script>
    <script src="{{ asset('js/mep/viajes/viajes_list.js') }}?v={{ filemtime(public_path('js/mep/viajes/viajes_list.js')) }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
                     flatpickr(".dateInput", {
              dateFormat: "d/m/Y",
              locale: "es"
            });

                    @can('mep-asignacion-unidad')
                     getCatalogoOperadorUnidad()
                    @endcan
                    getCatalogoOperadorUnidad()
                    @if (session('success'))
                        Swal.fire({
                            icon: 'success',
                            title: '¡Éxito!',
                            text: "{{ session('success') }}",
                            confirmButtonText: 'Aceptar'
                        });
                    @endif

                    @if (session('error'))
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: "{{ session('error') }}",
                            confirmButtonText: 'Cerrar'
                        });
                    @endif
                });
    </script>
@endpush
