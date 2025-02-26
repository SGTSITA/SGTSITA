@section('template_title')
    Documentos
@endsection
@extends('layouts.app')

@section('content')
    <style>
        .form-check-input[type="checkbox"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            width: 20px;
            height: 20px;
            background: #fff;
            border: 2px solid #000;
            border-radius: 1px;
            position: relative;
            cursor: pointer;
            margin: 0 auto;
            /* Centra la casilla en su contenedor */
        }

        .form-check-input[type="checkbox"]:checked {
            background-color: #0d6efd;
            /* color del fondo del checkbox al hacer check */
            background-image: url('data:image/svg+xml;charset=UTF-8,%3Csvg fill="none" stroke="%23fff" stroke-linecap="round" stroke-linejoin="round" stroke-width="3" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"%3E%3Cpath d="M5 13l4 4L19 7"/%3E%3C/svg%3E');
            background-position: center;
            background-repeat: no-repeat;
            background-size: 70%;
            border-color: #0d6efd;
        }

        .form-check-input[disabled] {
            opacity: 0.5;
            cursor: not-allowed;
        }

        input[type="date"] {
            height: 34px !important;
            /* Ajusta la altura para que coincida con los selects */
            padding: 4px 8px !important;
            /* Ajusta el padding interno */
            font-size: 14px !important;
            /* Mantiene un tamaño de fuente adecuado */
            line-height: normal !important;
        }

        /* Asegurar que todos los filtros tengan la misma altura */
        .form-select,
        .form-control {
            height: 32px !important;
            /* Igualar altura de selects e inputs */
            padding: 4px 8px !important;
            font-size: 14px !important;
        }
    </style>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header d-flex justify-content-between align-items-center"
                        style="background-color: #ffffff;">
                        <h5 class="mb-0 fw-bold">Reporte de documentos</h5>
                    </div>
                    <div class="card-body">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-12">
                                    <form id="searchForm" action="{{ route('advance_documentos.buscador') }}" method="GET"
                                        onsubmit="saveFilters()">
                                        <div class="row gy-3 gx-4 align-items-end">
                                            <div class="col-md-2">
                                                <label for="id_client" class="form-label fw-semibold">Buscar
                                                    Cliente:</label>
                                                <select class="form-select cliente py-2" name="id_client" id="id_client">
                                                    <option selected value="">Seleccionar cliente</option>
                                                    @foreach ($clientes as $client)
                                                        <option value="{{ $client->id }}">{{ $client->nombre }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="id_subcliente" class="form-label fw-semibold">Buscar
                                                    Subcliente:</label>
                                                <select class="form-select subcliente" name="id_subcliente"
                                                    id="id_subcliente">
                                                    <option selected value="">Seleccionar subcliente</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="id_proveedor" class="form-label fw-semibold">Proveedor:</label>
                                                <select class="form-select" name="id_proveedor" id="id_proveedor">
                                                    <option selected value="">Seleccionar proveedor</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2">
                                                <label for="fecha_inicio" class="form-label fw-semibold">Fecha
                                                    Inicio:</label>
                                                <input type="date" class="form-control" name="fecha_inicio"
                                                    id="fecha_inicio">
                                            </div>
                                            <div class="col-md-2">
                                                <label for="fecha_fin" class="form-label fw-semibold">Fecha Fin:</label>
                                                <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
                                            </div>
                                            <div class="col-md-2 text-start">
                                                <button class="btn bg-gradient-info btn-xs py-1"
                                                    type="submit">Buscar</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <div class="d-flex justify-content-between mb-3">
                                <button type="button" id="selectAllButton"
                                    class="btn bg-gradient-info btn-xs mb-2">Seleccionar todo</button>
                            </div>
                            <form id="exportForm" action="{{ route('export_documentos.export') }}" method="POST">
                                @csrf
                                <table class="table table-bordered table-striped align-middle" id="datatable-search"
                                    style="white-space: nowrap;">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="text-center"></th>
                                            <th class="text-center">#</th>
                                            <th class="text-center"># Contenedor</th>
                                            <th class="text-center">Formato CCP</th>
                                            <th class="text-center">Boleta liberacion</th>
                                            <th class="text-center">Doda</th>
                                            <th class="text-center">Carta porte</th>
                                            <th class="text-center">Boleta vacio</th>
                                            <th class="text-center">EIR</th>
                                            <th class="text-center">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @if (Route::currentRouteName() != 'index_documentos.reporteria')
                                            @foreach ($cotizaciones as $cotizacion)
                                                <tr>
                                                    <td class="text-center select-checkbox">
                                                        <input type="checkbox" name="cotizacion_ids[]"
                                                            value="{{ $cotizacion->id }}" class="form-check-input">
                                                    </td>
                                                    <td class="text-center">{{ $cotizacion->id }}</td>
                                                    <td class="text-center">{{ $cotizacion->num_contenedor }}</td>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center">
                                                            <input class="form-check-input" type="checkbox"
                                                                @if ($cotizacion->doc_ccp) checked @endif disabled>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center">
                                                            <input class="form-check-input" type="checkbox"
                                                                @if ($cotizacion->boleta_liberacion) checked @endif disabled>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center">
                                                            <input class="form-check-input" type="checkbox"
                                                                @if ($cotizacion->doda) checked @endif disabled>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center">
                                                            <input class="form-check-input" type="checkbox"
                                                                @if ($cotizacion->carta_porte) checked @endif disabled>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center">
                                                            <input class="form-check-input" type="checkbox"
                                                                @if ($cotizacion->boleta_vacio) checked @endif disabled>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <div class="form-check d-flex justify-content-center">
                                                            <input class="form-check-input" type="checkbox"
                                                                @if ($cotizacion->doc_eir) checked @endif disabled>
                                                        </div>
                                                    </td>
                                                    <td class="text-center">
                                                        <a type="button" class="btn bg-gradient-info btn-xs"
                                                            href="{{ route('edit.cotizaciones', $cotizacion->id) }}">Editar</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                                @if (isset($cotizaciones) && $cotizaciones != null)
                                    <button type="button" id="exportButtonExcel" data-filetype="xlsx"
                                        class="btn btn-outline-info btn-xs mb-2 mt-sm-0 mt-1 exportButton">Exportar a
                                        Excel</button>
                                    <button type="button" id="exportButton" data-filetype="pdf"
                                        class="btn btn-outline-info btn-xs mb-2 mt-sm-0 mt-1 exportButton">Exportar a
                                        PDF</button>
                                @endif
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('datatable')
    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js') }}"></script>
    <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js') }}"></script>

    <!-- JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/dataTables.fixedColumns.min.js"></script>
    <script src="https://cdn.datatables.net/fixedcolumns/5.0.1/js/fixedColumns.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/dataTables.select.min.js"></script>
    <script src="https://cdn.datatables.net/select/2.0.3/js/select.bootstrap5.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        $(document).ready(function() {

            // Inicializar la tabla con DataTables
            const table = $('#datatable-search').DataTable({
                columnDefs: [{
                    orderable: false,
                    className: 'select-checkbox',
                    targets: 0
                }],
                fixedColumns: {
                    start: 2
                },
                order: [
                    [1, 'asc']
                ],
                paging: true,
                pageLength: 30,
                select: {
                    style: 'multi',
                    selector: 'td:first-child'
                }
            });

            // Al hacer click directamente en el checkbox, alternar la selección de la fila
            $('#datatable-search tbody').on('click', 'td.select-checkbox input[type="checkbox"]', function(e) {
                const $row = $(this).closest('tr');
                // Si se chequea manualmente, selecciona la fila en DataTables
                if (this.checked) {
                    table.row($row).select();
                } else {
                    table.row($row).deselect();
                }
                // Evitar que se propague el evento y cause dobles selecciones
                e.stopPropagation();
            });

            // Mostrar la selección en el checkbox cuando se seleccionan filas
            table.on('select', function(e, dt, type, indexes) {
                if (type === 'row') {
                    indexes.forEach(function(i) {
                        const rowNode = table.row(i).node();
                        $(rowNode).find('td.select-checkbox input[type="checkbox"]').prop('checked',
                            true);
                    });
                }
                updateSelectAllButton();
            });

            // Quitar la selección en el checkbox cuando se deseleccionan filas
            table.on('deselect', function(e, dt, type, indexes) {
                if (type === 'row') {
                    indexes.forEach(function(i) {
                        const rowNode = table.row(i).node();
                        $(rowNode).find('td.select-checkbox input[type="checkbox"]').prop('checked',
                            false);
                    });
                }
                updateSelectAllButton();
            });

            // Función para actualizar el texto del botón "Seleccionar todo"
            function updateSelectAllButton() {
                const totalRows = table.rows().count();
                const selectedCount = table.rows({
                    selected: true
                }).count();
                const selectAllButton = $('#selectAllButton');

                if (selectedCount === totalRows && totalRows !== 0) {
                    selectAllButton.text('Deseleccionar todo');
                } else {
                    selectAllButton.text('Seleccionar todo');
                }
            }

            // Lógica para el botón "Seleccionar todo"
            $('#selectAllButton').on('click', function() {
                const totalRows = table.rows().count();
                const selectedCount = table.rows({
                    selected: true
                }).count();

                // Si todo está seleccionado, deselecciona todo, de lo contrario selecciona todo
                if (selectedCount === totalRows) {
                    table.rows().deselect();
                } else {
                    table.rows().select();
                }
            });

            // Lógica para la exportación de los datos seleccionados
            $('.exportButton').on('click', function(event) {
                // Obtener los IDs seleccionados
                const selectedIds = table.rows('.selected').data().toArray().map(row => row[1]);
                console.log('IDs seleccionados:', selectedIds);

                // Verificar si se han seleccionado contenedores
                if (selectedIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No hay contenedores seleccionados',
                        text: 'Por favor seleccione al menos un contenedor antes de exportar.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    return; // Detener la ejecución si no hay selección
                }

                var fileType = $(this).data('filetype');

                // Enviar los IDs seleccionados al controlador por Ajax
                $.ajax({
                    url: '{{ route('export_documentos.export') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        selected_ids: selectedIds,
                        fileType: fileType
                    },
                    xhrFields: {
                        responseType: 'blob' // Indicar que esperamos una respuesta tipo blob (archivo)
                    },
                    success: function(response) {
                        // Crear un objeto URL del blob recibido
                        var blob = new Blob([response], {
                            type: 'application/' + fileType
                        });
                        var url = URL.createObjectURL(blob);

                        // Crear un elemento <a> para simular el clic de descarga
                        var a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = 'documentos_seleccionados.' + fileType;
                        document.body.appendChild(a);

                        // Simular el clic en el enlace para iniciar la descarga
                        a.click();

                        // Limpiar después de la descarga
                        window.URL.revokeObjectURL(url);
                        document.body.removeChild(a);

                        // Alerta con SweetAlert2 para indicar que se ha descargado correctamente
                        Swal.fire({
                            icon: 'success',
                            title: 'Descarga completa',
                            text: 'El archivo se ha descargado correctamente.',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    },
                    error: function(xhr, status, error) {
                        console.error(error);
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: 'Ocurrió un error al exportar los datos.',
                            confirmButtonColor: '#d33',
                            confirmButtonText: 'Cerrar'
                        });
                    }
                });
            });

            // Lógica para manejar la selección de subcliente basada en el cliente seleccionado
            $('#id_client').on('change', function() {
                var clientId = $(this).val();
                if (clientId) {
                    $.ajax({
                        url: '/subclientes/' + clientId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            $('#id_subcliente').empty();
                            $('#id_subcliente').append(
                                '<option selected value="">Seleccionar subcliente</option>');
                            $.each(data, function(key, subcliente) {
                                $('#id_subcliente').append('<option value="' +
                                    subcliente.id + '\">' + subcliente.nombre +
                                    '</option>');
                            });
                        }
                    });
                } else {
                    $('#id_subcliente').empty();
                    $('#id_subcliente').append('<option selected value="">Seleccionar subcliente</option>');
                }
            });
        });
        document.addEventListener("DOMContentLoaded", function() {
            // Verificar si estamos en la vista de documentos
            if (window.location.pathname.includes("/reporteria/documentos")) {
                restoreFilters();
            } else {
                clearFilters(); // Si no está en documentos, limpiar filtros
            }

            // Guardar valores antes de enviar la búsqueda
            document.getElementById("searchForm").addEventListener("submit", function() {
                saveFilters();
            });

            // Detectar cambio en cliente para actualizar subclientes
            document.getElementById("id_client").addEventListener("change", function() {
                loadSubclients(this.value, null);
            });

            // Detectar cuando el usuario realmente deja la página
            document.addEventListener("visibilitychange", function() {
                if (document.hidden && !window.location.pathname.includes("documentos")) {
                    clearFilters();
                }
            });

            // También limpiar los filtros cuando se navega a otra página
            window.addEventListener("beforeunload", function() {
                if (!window.location.pathname.includes("documentos")) {
                    clearFilters();
                }
            });
        });

        // Guardar valores en localStorage antes de enviar la búsqueda
        function saveFilters() {
            localStorage.setItem("id_client", document.getElementById("id_client").value);
            localStorage.setItem("id_subcliente", document.getElementById("id_subcliente").value);
            localStorage.setItem("id_proveedor", document.getElementById("id_proveedor").value);
            localStorage.setItem("fecha_inicio", document.getElementById("fecha_inicio").value);
            localStorage.setItem("fecha_fin", document.getElementById("fecha_fin").value);
        }

        // Restaurar valores después de recargar la página (solo si sigue en documentos)
        function restoreFilters() {
            if (localStorage.getItem("id_client")) {
                document.getElementById("id_client").value = localStorage.getItem("id_client");
                loadSubclients(localStorage.getItem("id_client"), localStorage.getItem("id_subcliente"));
            }
            if (localStorage.getItem("id_proveedor")) {
                document.getElementById("id_proveedor").value = localStorage.getItem("id_proveedor");
            }
            if (localStorage.getItem("fecha_inicio")) {
                document.getElementById("fecha_inicio").value = localStorage.getItem("fecha_inicio");
            }
            if (localStorage.getItem("fecha_fin")) {
                document.getElementById("fecha_fin").value = localStorage.getItem("fecha_fin");
            }
        }

        // Cargar subclientes dinámicamente según el cliente seleccionado
        function loadSubclients(clientId, subclientId) {
            if (clientId) {
                $.ajax({
                    url: '/subclientes/' + clientId,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let subclientSelect = document.getElementById("id_subcliente");
                        subclientSelect.innerHTML = '<option selected value="">Seleccionar subcliente</option>';

                        data.forEach(function(subcliente) {
                            let option = document.createElement("option");
                            option.value = subcliente.id;
                            option.textContent = subcliente.nombre;
                            if (subclientId && subclientId === subcliente.id.toString()) {
                                option.selected = true;
                            }
                            subclientSelect.appendChild(option);
                        });
                    }
                });
            } else {
                document.getElementById("id_subcliente").innerHTML =
                    '<option selected value="">Seleccionar subcliente</option>';
            }
        }

        // Limpiar los filtros cuando el usuario abandona la vista de documentos
        function clearFilters() {
            localStorage.removeItem("id_client");
            localStorage.removeItem("id_subcliente");
            localStorage.removeItem("id_proveedor");
            localStorage.removeItem("fecha_inicio");
            localStorage.removeItem("fecha_fin");
        }
    </script>
@endsection
