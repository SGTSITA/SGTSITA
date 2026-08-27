@extends('layouts.usuario_externo')

@section('WorkSpace')
    <div class="card shadow-sm mb-5">
        <div class="card-header border-0 pt-6">
            <div class="card-title">
                <div class="d-flex align-items-center position-relative my-1">
                    <h3 class="fw-bold text-dark">Complementos de Pago</h3>
                </div>
            </div>
        </div>
        <div class="card-body pt-0">
            <!-- Buscadores -->
            <div class="row mb-5 g-3">
                <div class="col-md-6">
                    <label class="form-label fw-bold text-gray-700">Estado de Cuenta</label>
                    <div class="position-relative">
                        <i class="fa fa-search position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                        <input type="text" id="searchEstadoCuenta" class="form-control ps-10" 
                            placeholder="Buscar por número de estado de cuenta (ej. 150)..." />
                    </div>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-bold text-gray-700">Número de Contenedor</label>
                    <div class="position-relative">
                        <i class="fa fa-box position-absolute top-50 translate-middle-y ms-4 text-gray-500"></i>
                        <input type="text" id="searchContenedor" class="form-control ps-10" 
                            placeholder="Buscar por número de contenedor (ej. MSDU)..." />
                    </div>
                </div>
            </div>

            <!-- Loader -->
            <div id="loadingIndex" class="text-center my-10">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Cargando...</span>
                </div>
                <p class="mt-2 text-gray-600">Obteniendo complementos de pago...</p>
            </div>

            <!-- Acordeón para estados de cuenta -->
            <div id="complementoAccordion" class="accordion mb-5">
                <!-- Generado dinámicamente -->
            </div>

            <!-- Alerta Sin Resultados -->
            <div id="emptyState" class="alert alert-info text-center d-none">
                <i class="fa fa-info-circle me-2"></i> No se encontraron complementos de pago bajo los criterios de búsqueda.
            </div>

            <!-- Paginación -->
            <div id="paginationContainer" class="d-flex flex-stack flex-wrap pt-5 d-none">
                <div id="paginationInfo" class="fs-6 fw-bold text-gray-700">
                    Mostrando 0 de 0 estados de cuenta
                </div>
                <ul class="pagination">
                    <li class="page-item previous" id="btnPrevPage">
                        <button class="page-link" type="button"><i class="next"></i>Anterior</button>
                    </li>
                    <li class="page-item active">
                        <span class="page-link" id="lblCurrentPage">1</span>
                    </li>
                    <li class="page-item next" id="btnNextPage">
                        <button class="page-link" type="button">Siguiente<i class="next"></i></button>
                    </li>
                </ul>
            </div>
        </div>
    </div>
@endsection

@push('javascript')
    <script>
        let allData = [];
        let filteredData = [];
        let currentPage = 1;
        const itemsPerPage = 8; // Número de estados de cuenta por página

        $(document).ready(() => {
            fetchComplementos();

            // Escuchar cambios en los inputs de búsqueda
            $('#searchEstadoCuenta, #searchContenedor').on('keyup input', () => {
                currentPage = 1;
                filterAndRender();
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
                const totalPages = Math.ceil(filteredData.length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    renderPage();
                }
            });
        });

        function fetchComplementos() {
            $('#loadingIndex').removeClass('d-none');
            $('#complementoAccordion').html('');
            $('#paginationContainer').addClass('d-none');
            $('#emptyState').addClass('d-none');

            $.ajax({
                url: '/viajes/file-manager/get-complementos-pago',
                type: 'GET',
                success: function(response) {
                    $('#loadingIndex').addClass('d-none');
                    if (response.success && response.data.length > 0) {
                        allData = response.data;
                        filterAndRender();
                    } else {
                        $('#emptyState').removeClass('d-none');
                    }
                },
                error: function() {
                    $('#loadingIndex').addClass('d-none');
                    $('#emptyState').removeClass('d-none').text('Ocurrió un error al obtener la información.');
                }
            });
        }

        function filterAndRender() {
            const searchEc = $('#searchEstadoCuenta').val().toLowerCase().trim();
            const searchCont = $('#searchContenedor').val().toLowerCase().trim();

            // Filtrado lógico
            filteredData = allData.filter(group => {
                // Filtro por Estado de Cuenta (grupo es: "numero - empresa")
                const matchesEc = group.grupo.toLowerCase().includes(searchEc);

                // Filtro por Contenedor
                let matchesCont = true;
                if (searchCont !== '') {
                    matchesCont = group.contenedores.some(c => c.num_contenedor.toLowerCase().includes(searchCont));
                }

                return matchesEc && matchesCont;
            });

            // Si hay filtro por contenedor, debemos filtrar los contenedores internos también en la vista filtrada
            if (searchCont !== '') {
                filteredData = filteredData.map(group => {
                    return {
                        ...group,
                        contenedores: group.contenedores.filter(c => c.num_contenedor.toLowerCase().includes(searchCont))
                    };
                });
            }

            if (filteredData.length === 0) {
                $('#complementoAccordion').html('');
                $('#paginationContainer').addClass('d-none');
                $('#emptyState').removeClass('d-none');
            } else {
                $('#emptyState').addClass('d-none');
                $('#paginationContainer').removeClass('d-none');
                renderPage();
            }
        }

        function renderPage() {
            const totalItems = filteredData.length;
            const totalPages = Math.ceil(totalItems / itemsPerPage);

            // Asegurar que la página actual esté en rango
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIndex = (currentPage - 1) * itemsPerPage;
            const endIndex = Math.min(startIndex + itemsPerPage, totalItems);
            const pageData = filteredData.slice(startIndex, endIndex);

            // Renderizado del acordeón
            let html = '';
            pageData.forEach((group, index) => {
                const uniqueIdx = startIndex + index;
                const headingId = `heading-${uniqueIdx}`;
                const collapseId = `collapse-${uniqueIdx}`;

                html += `
                    <div class="accordion-item shadow-xs mb-3 border">
                        <h2 class="accordion-header" id="${headingId}">
                            <button class="accordion-button collapsed fw-bold text-dark fs-6 bg-light" type="button" 
                                data-bs-toggle="collapse" data-bs-target="#${collapseId}" aria-expanded="false" aria-controls="${collapseId}">
                                <i class="fa fa-file-invoice-dollar me-2 text-primary fs-5"></i>
                                Estado de Cuenta: ${group.grupo}
                            </button>
                        </h2>
                        <div id="${collapseId}" class="accordion-collapse collapse" aria-labelledby="${headingId}" data-bs-parent="#complementoAccordion">
                            <div class="accordion-body">
                                <div class="d-flex justify-content-end mb-3">
                                    <button class="btn btn-sm btn-success fw-bold" onclick="downloadZip('${group.grupo}')">
                                        <i class="fa fa-file-archive me-1"></i> Descargar Todo (.ZIP)
                                    </button>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-striped table-bordered align-middle fs-7 mb-0">
                                        <thead>
                                            <tr class="fw-bold text-gray-800 bg-secondary">
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
                            <a href="${file.url}" download="${file.filename}" target="_blank" class="btn btn-sm ${btnColor} me-1 my-1">
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

            // Habilitar/Deshabilitar botones
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
                url: '/viajes/file-manager/download-zip-complementos',
                type: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
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
@endpush
