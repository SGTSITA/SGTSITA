@extends('layouts.app')

@section('template_title')
    Documentos
@endsection

@section('content')
    <style>
        #myGrid {
            height: 600px;
            width: 100%;
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white">
                        <h5 class="mb-0 fw-bold">Reporte de documentos</h5>
                    </div>
                    <div class="d-flex align-items-center gap-2" style="margin-left: 20px;">
                        <label class="mb-0 fw-semibold text-sm"> Periodo:</label>
                        <input type="text" id="daterange" readonly class="form-control form-control-sm"
                            style="width: auto; min-width: 200px; box-shadow: none;" />
                    </div>


                    <div class="card-body">

                        <div class="d-flex justify-content-start my-2 gap-2">
                            <button type="button" id="exportButtonExcel" data-filetype="xlsx"
                                class="btn btn-outline-info btn-xs exportButton">
                                Exportar a Excel
                            </button>
                            <button type="button" id="exportButtonPDF" data-filetype="pdf"
                                class="btn btn-outline-info btn-xs exportButton">
                                Exportar a PDF
                            </button>

                        </div>

                        <!-- AG Grid -->
                        <div id="myGrid" class="ag-theme-alpine"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Inyectar todos los datos disponibles y la ruta de exportación -->
    <script>
        window.cotizacionesData = @json($cotizaciones ?? []);
        const exportUrl = "{{ route('export_documentos.export') }}";
    </script>
@endsection

@section('datatable')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script src="{{ asset('js/sgt/reporteria/documento.js') }}"></script>
    <!-- Date Range Picker CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <!-- Moment.js -->
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>

    <!-- Date Range Picker JS -->
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <script>
        $(document).ready(function() {
            const today = moment();
            const sevenDaysAgo = moment().subtract(7, 'days');

            $('#daterange').daterangepicker({
                startDate: sevenDaysAgo,
                endDate: today,
                maxDate: today,
                opens: 'right',
                locale: {
                    format: 'YYYY-MM-DD',
                    separator: ' AL ',
                    applyLabel: 'Aplicar',
                    cancelLabel: 'Cancelar',
                    fromLabel: 'Desde',
                    toLabel: 'Hasta',
                    customRangeLabel: 'Personalizado',
                    daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                    monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto',
                        'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'
                    ],
                    firstDay: 1
                }
            }, function(start, end, label) {
                // Aquí llamas a tu función para recargar datos
                getDatosFiltradosPorFecha(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
            });


            // Llama la primera vez al cargar
            getDatosFiltradosPorFecha(sevenDaysAgo.format('YYYY-MM-DD'), today.format('YYYY-MM-DD'));

            // === Actualizar el texto del botón "Seleccionar todo" ===
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

            // === Botón "Seleccionar todo" ===
            $('#selectAllButton').on('click', function() {
                const totalRows = table.rows().count();
                const selectedCount = table.rows({
                    selected: true
                }).count();
                selectedCount === totalRows ? table.rows().deselect() : table.rows().select();
            });

            // === Exportar datos seleccionados ===
            $('.exportButton').on('click', function() {
                const selectedIds = table.rows('.selected').data().toArray().map(row => row[1]);
                console.log('IDs seleccionados:', selectedIds);

                if (selectedIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No hay contenedores seleccionados',
                        text: 'Por favor seleccione al menos un contenedor antes de exportar.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                const fileType = $(this).data('filetype');
                $.ajax({
                    url: '{{ route('export_documentos.export') }}',
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        selected_ids: selectedIds,
                        fileType: fileType
                    },
                    xhrFields: {
                        responseType: 'blob'
                    },
                    success: function(response) {
                        const blob = new Blob([response], {
                            type: 'application/' + fileType
                        });
                        const url = URL.createObjectURL(blob);

                        const a = document.createElement('a');
                        a.style.display = 'none';
                        a.href = url;
                        a.download = 'documentos_seleccionados.' + fileType;
                        document.body.appendChild(a);
                        a.click();
                        URL.revokeObjectURL(url);
                        document.body.removeChild(a);

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

            // === Carga dinámica de subclientes al cambiar el cliente ===
            $('#id_client').on('change', function() {
                const clientId = $(this).val();
                if (clientId) {
                    $.ajax({
                        url: '/subclientes/' + clientId,
                        type: 'GET',
                        dataType: 'json',
                        success: function(data) {
                            const subclienteSelect = $('#id_subcliente');
                            subclienteSelect.empty().append(
                                '<option selected value="">Seleccionar subcliente</option>');
                            $.each(data, function(index, subcliente) {
                                subclienteSelect.append('<option value="' + subcliente
                                    .id + '">' + subcliente.nombre + '</option>');
                            });
                        }
                    });
                } else {
                    $('#id_subcliente').empty().append(
                        '<option selected value="">Seleccionar subcliente</option>');
                }
            });

        });
    </script>
@endsection
