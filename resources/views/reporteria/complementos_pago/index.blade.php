@extends('layouts.app')

@section('template_title')
    Reportería — Complemento pago
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3 mb-5">
                    <div class="card-header bg-white border-0 pt-6">
                        <div class="d-flex justify-content-between align-items-center">
                            <h3 class="fw-bold text-dark mb-0">Complementos de Pago</h3>
                        </div>
                    </div>
                    <div class="card-body">
                        <!-- Filtros / Buscadores -->
                        <div class="row mb-5 g-3 align-items-end">
                            <div class="col-md-5">
                                <label class="form-label fw-bold text-gray-700">Estado de Cuenta</label>
                                <div class="position-relative">
                                    <input type="text" id="searchEstadoCuenta" class="form-control" 
                                        placeholder="Buscar por número de estado de cuenta..." />
                                </div>
                            </div>
                            <div class="col-md-5">
                                <label class="form-label fw-bold text-gray-700">Número de Contenedor</label>
                                <div class="position-relative">
                                    <input type="text" id="searchContenedor" class="form-control" 
                                        placeholder="Buscar por número de contenedor..." />
                                </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" id="btnBuscar" class="btn btn-primary w-100 fw-bold">
                                    <i class="fa fa-search me-1"></i> Buscar
                                </button>
                            </div>
                        </div>

                        <!-- Loader -->
                        <div id="loadingIndex" class="text-center my-10 d-none">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                            <p class="mt-2 text-gray-600">Obteniendo complementos de pago...</p>
                        </div>

                        <!-- Acordeón para estados de cuenta -->
                        <style>
                            .accordion-button {
                                padding: 0.4rem 0.8rem !important;
                                font-size: 0.8rem !important;
                            }
                            .accordion-button::after {
                                width: 0.85rem !important;
                                height: 0.85rem !important;
                                background-size: 0.85rem !important;
                            }
                            .accordion-button:not(.collapsed) {
                                background-color: #f8f9fa !important;
                                color: #344767 !important;
                                box-shadow: inset 0 -1px 0 rgba(0,0,0,.125) !important;
                            }
                            .custom-pagination {
                                display: flex !important;
                                align-items: center !important;
                                list-style: none !important;
                                padding-left: 0 !important;
                                margin-bottom: 0 !important;
                                gap: 6px !important;
                            }
                            .custom-pagination .page-item {
                                margin: 0 !important;
                            }
                            .custom-pagination .page-link {
                                display: inline-flex !important;
                                align-items: center !important;
                                justify-content: center !important;
                                padding: 0.35rem 0.75rem !important;
                                font-size: 0.8rem !important;
                                border: 1px solid #dee2e6 !important;
                                background-color: #fff !important;
                                color: #5e72e4 !important;
                                border-radius: 4px !important;
                                cursor: pointer !important;
                                text-decoration: none !important;
                                line-height: 1.25 !important;
                                height: auto !important;
                                width: auto !important;
                                box-shadow: none !important;
                            }
                            .custom-pagination .page-item.active .page-link {
                                background-color: #5e72e4 !important;
                                color: #fff !important;
                                border-color: #5e72e4 !important;
                            }
                            .custom-pagination .page-item.disabled .page-link {
                                color: #8898aa !important;
                                pointer-events: none !important;
                                background-color: #fff !important;
                                border-color: #dee2e6 !important;
                                opacity: 0.6 !important;
                            }
                        </style>

                        <div id="complementoAccordion" class="accordion mb-5">
                            <!-- Generado dinámicamente -->
                        </div>

                        <!-- Alerta Sin Resultados / Estado Inicial -->
                        <div id="emptyState" class="alert alert-info text-center">
                            <i class="fa fa-info-circle me-2"></i> Ingrese los filtros y haga clic en Buscar para consultar la información.
                        </div>

                        <!-- Paginación -->
                        <div id="paginationContainer" class="d-flex justify-content-between align-items-center flex-wrap pt-4 d-none">
                            <div id="paginationInfo" class="fs-6 fw-bold text-gray-700">
                                Mostrando 0 de 0 estados de cuenta
                            </div>
                            <ul class="custom-pagination">
                                <li class="page-item" id="btnPrevPage">
                                    <button class="page-link" type="button">Anterior</button>
                                </li>
                                <li class="page-item active" id="liCurrentPage">
                                    <span class="page-link" id="lblCurrentPage">1</span>
                                </li>
                                <li class="page-item" id="btnNextPage">
                                    <button class="page-link" type="button">Siguiente</button>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('datatable')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let allData = [];
        let currentPage = 1;
        const itemsPerPage = 8; // Número de estados de cuenta por página

        $(document).ready(() => {
            // Buscar al hacer clic en el botón
            $('#btnBuscar').on('click', () => {
                currentPage = 1;
                fetchComplementos();
            });

            // Buscar al presionar Enter en los inputs
            $('#searchEstadoCuenta, #searchContenedor').on('keypress', function(e) {
                if (e.which === 13) {
                    currentPage = 1;
                    fetchComplementos();
                }
            });

            // Botones de paginación
            $('#btnPrevPage').on('click', function(e) {
                e.preventDefault();
                if (currentPage > 1) {
                    currentPage--;
                    renderPage();
                }
            });

            $('#btnNextPage').on('click', function(e) {
                e.preventDefault();
                const totalPages = Math.ceil(allData.length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage();
                }
            });
        });

        function fetchComplementos() {
            const num_estado_cuenta = $('#searchEstadoCuenta').val().trim();
            const num_contenedor = $('#searchContenedor').val().trim();

            $('#loadingIndex').removeClass('d-none');
            $('#complementoAccordion').html('');
            $('#paginationContainer').addClass('d-none');
            $('#emptyState').addClass('d-none');

            $.ajax({
                url: '{{ route("reporteria.complementos_pago.data") }}',
                type: 'GET',
                data: {
                    num_estado_cuenta: num_estado_cuenta,
                    num_contenedor: num_contenedor
                },
                success: function(response) {
                    $('#loadingIndex').addClass('d-none');
                    if (response.success && response.data.length > 0) {
                        allData = response.data;
                        $('#paginationContainer').removeClass('d-none');
                        renderPage();
                    } else {
                        allData = [];
                        $('#emptyState').removeClass('d-none').html('<i class="fa fa-info-circle me-2"></i> No se encontraron complementos de pago bajo los criterios de búsqueda.');
                    }
                },
                error: function() {
                    $('#loadingIndex').addClass('d-none');
                    allData = [];
                    $('#emptyState').removeClass('d-none').html('<i class="fa fa-exclamation-circle me-2"></i> Ocurrió un error al obtener la información.');
                }
            });
        }

        function renderPage() {
            const totalItems = allData.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);

            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
            const pageData = allData.slice(startIndex, endIndex);

            let html = '';
            pageData.forEach((group, index) => {
                const uniqueIdx = startIndex + index;
                const headingId = `heading-${uniqueIdx}`;
                const collapseId = `collapse-${uniqueIdx}`;

                html += `
                    <div class="accordion-item shadow-sm mb-3 border rounded">
                        <h2 class="accordion-header" id="${headingId}">
                            <button class="accordion-button collapsed fw-bold text-dark bg-light" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
                                <i class="fa fa-file-invoice-dollar me-2 text-primary fs-6"></i>
                                Estado de Cuenta: ${group.grupo}
                            </button>
                        </h2>
                        <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}">
                            <div class="accordion-body">
                                <div class="d-flex justify-content-end mb-3">
                                    <button class="btn btn-sm btn-success fw-bold text-white" onclick="downloadZip('${group.grupo}')">
                                        <i class="fa fa-file-archive me-1"></i> Descargar Todo (.ZIP)
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered align-middle fs-7 mb-0">
                                        <thead>
                                            <tr class="fw-bold text-white bg-dark">
                                                <th>Contenedor</th>
                                                <th>Archivos Disponibles</th>
                                            </tr>
                                        </thead>
                                        <tbody>`;

                group.contenedores.forEach(container => {
                    html += `
                        <tr>
                            <td class="fw-bold text-dark">${container.num_contenedor}</td>
                            <td>`;

                    container.files.forEach(file => {
                        const btnColor = file.name === 'PDF' ? 'btn-danger' : 'btn-primary';
                        const icon = file.name === 'PDF' ? 'fa-file-pdf' : 'fa-file-code';
                        html += `
                            <a href="${file.url}" download="${file.filename}" target="_blank" class="btn btn-sm ${btnColor} me-1 my-1 text-white">
                                <i class="fa ${icon} me-1"></i> Descargar ${file.name}
                            </a>`;
                    });

                    html += `
                            </td>
                        </tr>`;
                });

                html += `
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>`;
            });

            $('#complementoAccordion').html(html);

            // Actualizar controles de paginación
            $('#lblCurrentPage').text(currentPage);
            $('#paginationInfo').text(`Mostrando ${startIndex + 1} - ${endIndex} de ${totalItems} estados de cuenta`);

            if (currentPage === 1) {
                $('#btnPrevPage').addClass('disabled');
            } else {
                $('#btnPrevPage').removeClass('disabled');
            }

            if (currentPage === totalPages || totalPages === 0) {
                $('#btnNextPage').addClass('disabled');
            } else {
                $('#btnNextPage').removeClass('disabled');
            }
        }

        function downloadZip(grupo) {
            let parts = grupo.split(' - ');
            let num_estado_cuenta = parts[0].trim();
            let nombre_empresa = parts[1].trim();

            Swal.fire({
                title: 'Generando archivo ZIP...',
                text: 'Por favor espere un momento.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            $.ajax({
                url: '{{ route("reporteria.complementos_pago.download_zip") }}',
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    num_estado_cuenta: num_estado_cuenta,
                    nombre_empresa: nombre_empresa
                },
                success: function(response) {
                    Swal.close();
                    if (response.success && response.zipUrl) {
                        window.location.href = response.zipUrl;
                    } else {
                        Swal.fire('Error', response.message || 'No se pudo generar el archivo ZIP.', 'error');
                    }
                },
                error: function() {
                    Swal.close();
                    Swal.fire('Error', 'Ocurrió un error inesperado al procesar la solicitud.', 'error');
                }
            });
        }
    </script>
@endsection
