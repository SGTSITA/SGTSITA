@extends('layouts.usuario_externo')

@section('WorkSpace')
    <style>
        .header-center .ag-header-cell-label {
            justify-content: center;
            text-align: center;
        }

        .header-center .ag-header-cell-text {
            white-space: normal;
            line-height: 1.3;
        }

        .status-tabs-container {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 20px;
            padding: 8px;
            background-color: #f8f9fa;
            border-radius: 10px;
            border: 1px solid #e9ecef;
            width: 100%;
        }

        .status-tab-btn {
            flex: 1 1 0;
            min-width: 150px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 10px 14px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            border: 1px solid #dee2e6;
            background-color: #ffffff;
            color: #495057;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            user-select: none;
            text-align: center;
        }

        .status-tab-btn:hover {
            background-color: #f1f3f5;
        }

        /* Planeadas (Naranja suave) */
        .status-tab-btn[data-status="planeadas"].active {
            background-color: #fff7ed !important;
            border-color: #fdba74 !important;
            color: #c2410c !important;
            box-shadow: 0 2px 6px rgba(249, 115, 22, 0.15);
        }
        .status-tab-btn[data-status="planeadas"] .badge-count {
            background-color: #ffedd5;
            color: #c2410c;
        }

        /* Viajes Solicitados (Pendientes) (Amarillo/Ámbar suave) */
        .status-tab-btn[data-status="pendientes"].active {
            background-color: #fefce8 !important;
            border-color: #fde047 !important;
            color: #a16207 !important;
            box-shadow: 0 2px 6px rgba(234, 179, 8, 0.15);
        }
        .status-tab-btn[data-status="pendientes"] .badge-count {
            background-color: #fef9c3;
            color: #a16207;
        }

        /* Aprobadas (Azul suave) */
        .status-tab-btn[data-status="aprobadas"].active {
            background-color: #eff6ff !important;
            border-color: #93c5fd !important;
            color: #1d4ed8 !important;
            box-shadow: 0 2px 6px rgba(59, 130, 246, 0.15);
        }
        .status-tab-btn[data-status="aprobadas"] .badge-count {
            background-color: #dbeafe;
            color: #1d4ed8;
        }

        /* Finalizadas (Verde suave) */
        .status-tab-btn[data-status="finalizadas"].active {
            background-color: #f0fdf4 !important;
            border-color: #86efac !important;
            color: #15803d !important;
            box-shadow: 0 2px 6px rgba(34, 197, 94, 0.15);
        }
        .status-tab-btn[data-status="finalizadas"] .badge-count {
            background-color: #dcfce7;
            color: #15803d;
        }

        /* Por Asignar (Morado suave) */
        .status-tab-btn[data-status="por_asignar"].active {
            background-color: #f5f3ff !important;
            border-color: #c4b5fd !important;
            color: #6d28d9 !important;
            box-shadow: 0 2px 6px rgba(124, 58, 237, 0.15);
        }
        .status-tab-btn[data-status="por_asignar"] .badge-count {
            background-color: #ede9fe;
            color: #6d28d9;
        }

        /* Canceladas (Rojo suave) */
        .status-tab-btn[data-status="canceladas"].active {
            background-color: #fef2f2 !important;
            border-color: #fca5a5 !important;
            color: #b91c1c !important;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.15);
        }
        .status-tab-btn[data-status="canceladas"] .badge-count {
            background-color: #fee2e2;
            color: #b91c1c;
        }

        .badge-count {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 700;
        }
    </style>
    <div class="row gx-5 gx-xl-10">
        <div class="col-sm-12 mb-5 mb-xl-10">
            <div class="card card-flush h-lg-100">
                <div class="card-header border-0 pt-5 pb-2">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 w-100">

                        <!-- Título y Filtro Periodo (A la izquierda) -->
                        <div class="d-flex flex-wrap align-items-center gap-4">
                            <div>
                                <h3 class="card-title mb-1 fw-bold text-gray-900">
                                    Mis Viajes
                                </h3>
                                <div class="text-gray-500 fw-semibold fs-7">
                                    Gestión y estatus de viajes
                                </div>
                            </div>

                            <!-- Periodo -->
                            <div class="d-flex align-items-center gap-2 ms-sm-2">
                                <label class="fw-semibold text-gray-600 mb-0 fs-7">
                                    Periodo:
                                </label>

                                <div class="position-relative">
                                    <input type="text" id="rangoFechasViajes"
                                        class="form-control form-control-sm ps-10 w-230px" placeholder="Seleccionar rango" />

                                    <i
                                        class="ki-outline ki-calendar fs-2 position-absolute top-50 start-0 translate-middle-y ms-3"></i>
                                </div>
                            </div>
                        </div>

                        <!-- Buscador General Ampliado + Botón Opciones (A la derecha) -->
                        <div class="d-flex flex-wrap align-items-center gap-3 ms-auto">

                            <!-- Buscador General ampliado -->
                            <div class="position-relative w-280px">
                                <input type="text" id="inputSearchGeneral"
                                    class="form-control form-control-sm ps-9" placeholder="Buscar..." />
                                <i class="ki-outline ki-magnifier fs-3 position-absolute top-50 start-0 translate-middle-y ms-3 text-gray-500"></i>
                            </div>

                            <!-- Botón Opciones (Al lado del buscador) -->
                            <div class="card-toolbar m-0">
                                <button class="btn btn-primary btn-sm px-4" data-kt-menu-trigger="click"
                                    data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">
                                    Opciones
                                    <i class="ki-outline ki-plus fs-1 text-white me-n1 ms-1"></i>
                                </button>
                                <!--begin::Menu 2-->
                                <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px"
                                    data-kt-menu="true">
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <div class="menu-content fs-6 text-gray-900 fw-bold px-3 py-4">
                                            Acciones rápidas
                                        </div>
                                    </div>
                                    <!--end::Menu item-->
                                    <!--begin::Menu separator-->
                                    <div class="separator mb-3 opacity-75"></div>
                                    <!--end::Menu separator-->
                                    <!--begin::Menu item-->
                                    <div class="menu-item px-3">
                                        <a class="menu-link px-3" onclick="getFilesCFDI()">Obtener CFDI Carta Porte</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a class="menu-link px-3" onclick="cancelarViajeQuestion()">Cancelar Viaje</a>
                                    </div>

                                    <div class="menu-item px-3">
                                        <a class="menu-link px-3" onclick="fileManager()">Ver Documentos</a>
                                    </div>
                                    @can('Complemento de pagos')
                                        <div class="menu-item px-3">
                                            <a class="menu-link px-3"
                                                href="{{ route('viajes.complementos_pago_view') }}">Complemento de pagos</a>
                                        </div>
                                    @endcan
                                    @can('cotizaciones-edit')
                                        <div class="menu-item px-3">
                                            <a class="menu-link px-3" onclick="editarViaje()">Editar Viaje</a>
                                        </div>
                                    @endcan

                                    <div class="separator mt-3 opacity-75"></div>
                                    <div class="menu-item px-3">
                                        <a class="menu-link px-3" onclick="viajeFull()">Viajar en Full</a>
                                    </div>
                                    <div class="menu-item px-3">
                                        <a class="menu-link px-3" onclick="cancelarFull()"> Cancelar viaje Full</a>
                                    </div>
                                    @can('regresar-Contendorlocal')
                                        <div class="menu-item px-3 disabled" id="btnRegresarLocal">
                                            <a class="menu-link px-3" onclick="RegresarForaneoLocal()"> Regresar a local</a>
                                        </div>
                                    @endcan

                                    @can('addDocscliente-acceso')
                                        <div class="menu-item px-3">
                                            <div class="menu-content px-3 py-3">
                                                <button class="btn btn-primary btn-sm px-4" name="btnDocs" id="btnDocs">
                                                    Agregar documentos
                                                </button>
                                            </div>
                                        </div>
                                    @endcan

                                    <!-- Rastreo disponible solo en Planeadas -->
                                    <div class="menu-item px-3" id="menuItemRastreo">
                                        <div class="menu-content px-3 py-3">
                                            <button type="button" class="btn btn-sm btn-success w-100" title="Rastrear contenedor"
                                                id="btnRastreo">
                                                <i class="fa fa-shipping-fast"></i>
                                                Rastreo
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <!--end::Menu 2-->
                            </div>

                        </div>

                    </div>
                </div>
                <div class="card-body">
                    <div class="status-tabs-container" id="statusTabsViajes">
                        <button type="button" class="status-tab-btn active" data-status="planeadas">
                            <i class="fa-solid fa-folder text-warning fs-5"></i>
                            <span>Planeadas</span>
                            <span class="badge-count" id="count-planeadas">0</span>
                        </button>

                        <button type="button" class="status-tab-btn" data-status="pendientes">
                            <i class="fa-solid fa-folder text-warning fs-5"></i>
                            <span>Viajes Solicitados (Pendientes)</span>
                            <span class="badge-count" id="count-pendientes">0</span>
                        </button>

                        <button type="button" class="status-tab-btn" data-status="por_asignar">
                            <i class="fa-solid fa-folder fs-5" style="color: #7c3aed;"></i>
                            <span>Por Asignar</span>
                            <span class="badge-count" id="count-por-asignar">0</span>
                        </button>

                        <button type="button" class="status-tab-btn" data-status="aprobadas">
                            <i class="fa-solid fa-folder text-primary fs-5"></i>
                            <span>Aprobadas</span>
                            <span class="badge-count" id="count-aprobadas">0</span>
                        </button>

                        <button type="button" class="status-tab-btn" data-status="finalizadas">
                            <i class="fa-solid fa-folder text-success fs-5"></i>
                            <span>Finalizadas</span>
                            <span class="badge-count" id="count-finalizadas">0</span>
                        </button>

                        <button type="button" class="status-tab-btn" data-status="canceladas">
                            <i class="fa-solid fa-folder text-danger fs-5"></i>
                            <span>Canceladas</span>
                            <span class="badge-count" id="count-canceladas">0</span>
                        </button>
                    </div>

                    <div class="row">
                        <div id="myGrid" class="col-12 ag-theme-quartz mb-6" style="height: 610px"></div>

                        <div class="modal fade" id="kt_modal_top_up_wallet" tabindex="-1" aria-hidden="true">
                            <!--begin::Modal dialog-->
                            <div class="modal-dialog modal-fullscreen p-9">
                                <!--begin::Modal content-->
                                <div class="modal-content modal-rounded">
                                    <!--begin::Modal header-->
                                    <div class="modal-header py-7 d-flex justify-content-between">
                                        <!--begin::Modal title-->
                                        <div class="mb-3">
                                            <!--begin::Title-->
                                            <h3 class="mb-3">Boleta de liberación</h3>
                                            <!--end::Title-->

                                            <!--begin::Description-->
                                            <div class="text-muted fw-semibold fs-5">
                                                Contenedor
                                                <span class="fw-bold link-primary">PPPP0009991</span>
                                                .
                                            </div>
                                            <!--end::Description-->
                                        </div>

                                        <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                                            <i class="ki-duotone ki-cross fs-1">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                    </div>
                                    <div class="modal-body scroll-y m-5"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalSeleccionContenedor" tabindex="-1" aria-labelledby="seleccionContenedorLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Selecciona un contenedor</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <p class="mb-3">Este viaje es Full. Por favor selecciona cuál deseas editar:</p>
                    <div id="contenedorOpciones" class="list-group">
                        <!-- Aquí se agregan dinámicamente los botones -->
                    </div>
                </div>
            </div>
        </div>
    </div>


    @include('cotizaciones.externos.modal_fileuploader')
@endsection

@push('javascript')
    <style>
        .disabled-link {
            pointer-events: none;
            /* Desactiva los clics */
            color: gray;
            /* Cambia el estilo visual */
            cursor: default;
        }
    </style>
    <link href="{{ asset('assets/metronic/fileuploader/font/font-fileuploader.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/metronic/fileuploader/jquery.fileuploader.min.css') }}" media="all"
        rel="stylesheet" />
    <link href="{{ asset('assets/metronic/fileuploader/jquery.fileuploader-theme-dragdrop.css') }}" media="all"
        rel="stylesheet" />
    <script src="{{ asset('assets/metronic/fileuploader/jquery.fileuploader.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/metronic/fileuploader/cotizacion-cliente-externo.js') }}" type="text/javascript"></script>

    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <script
        src="{{ asset('js/sgt/cotizaciones/cotizacion-documentacion.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizacion-documentacion.js')) }}">
    </script>
    <script>
        let estatusSearch = 'all';
        $(document).ready(() => {
            getContenedoresPendientes(estatusSearch);
            adjuntarDocumentos();
        });

        function ComplementoPago() {
            var myModal = new bootstrap.Modal(document.getElementById('modalComplementoPagos'));
            myModal.show();

            $('#loadingComplemento').removeClass('d-none');
            $('#complementoContent').html('');
            $('#noComplementoMessage').addClass('d-none');

            $.ajax({
                url: '/viajes/file-manager/get-complementos-pago',
                type: 'GET',
                success: function(response) {
                    $('#loadingComplemento').addClass('d-none');
                    if (response.success && response.data.length > 0) {
                        let accordionHtml = '';
                        response.data.forEach((group, index) => {
                            let headingId = `heading-${index}`;
                            let collapseId = `collapse-${index}`;

                            accordionHtml += `
                                <div class="accordion-item">
                                    <h2 class="accordion-header" id="${headingId}">
                                        <button class="accordion-button collapsed fw-bold text-dark fs-6" type="button" data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
                                            Estado de Cuenta: ${group.grupo}
                                        </button>
                                    </h2>
                                    <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}" data-bs-parent="#modalComplementoPagos">
                                        <div class="accordion-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered align-middle fs-7">
                                                    <thead>
                                                        <tr class="fw-bold text-gray-800">
                                                            <th>Contenedor</th>
                                                            <th>Archivos Disponibles</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>`;

                            group.contenedores.forEach(container => {
                                accordionHtml += `
                                    <tr>
                                        <td class="fw-bold text-dark">${container.num_contenedor}</td>
                                        <td>`;

                                container.files.forEach(file => {
                                    let btnColor = file.name === 'PDF' ? 'btn-danger' :
                                        'btn-primary';
                                    let icon = file.name === 'PDF' ? 'fa-file-pdf' :
                                        'fa-file-code';
                                    accordionHtml += `
                                        <a href="${file.url}" target="_blank" class="btn btn-sm ${btnColor} me-1 my-1">
                                            <i class="fa ${icon} me-1"></i> Descargar ${file.name}
                                        </a>`;
                                });

                                accordionHtml += `
                                        </td>
                                    </tr>`;
                            });

                            accordionHtml += `
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>`;
                        });
                        $('#complementoContent').html(accordionHtml);
                    } else {
                        $('#noComplementoMessage').removeClass('d-none');
                    }
                },
                error: function() {
                    $('#loadingComplemento').addClass('d-none');
                    $('#noComplementoMessage').removeClass('d-none').text(
                        'Ocurrió un error al obtener los documentos.');
                }
            });
        }
    </script>
    <style>
        .rag-red {
            background-color: #cc222244;
        }

        .rag-green {
            background-color: #198754;
        }
    </style>
@endpush
