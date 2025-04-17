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
@endsection
