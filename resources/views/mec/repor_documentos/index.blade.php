@extends('layouts.usuario_externo')

@section('template_title')
    Reporte Documentos
@endsection

@section('WorkSpace')
    <style>
        #myGrid {
            height: 600px;
            width: 100%;
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white">
                        <h5 class="mb-0 fw-bold">Reporte de documentos</h5>
                    </div>
                    <div class="row mb-4 align-items-end">
                        <div class="col-md-1">
                        </div>

                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-sm">Periodo</label>
                            <input type="text" id="daterange" readonly class="form-control form-control-sm" />
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Proveedor</label>
                            <select id="filtroProveedor" class="form-select form-select-sm">
                                <option value="">Todos</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label class="form-label">Subcliente</label>
                            <select id="filtroSubcliente" class="form-select form-select-sm" disabled>
                                <option value="">Todos</option>
                            </select>
                        </div>
                    </div>
                    {{-- <div class="d-flex align-items-center gap-2" style="margin-left: 20px">
                        <label class="mb-0 fw-semibold text-sm">Periodo:</label>
                        <input type="text" id="daterange" readonly class="form-control form-control-sm"
                            style="width: auto; min-width: 200px; box-shadow: none" />
                    </div> --}}


                    <div class="card-body">
                        <div class="d-flex justify-content-start my-2 gap-2">
                            <button type="button" id="exportButtonExcel" data-filetype="xlsx"
                                class="btn btn-info btn-sm exportButton">
                                <i class="fa-solid fa-file-excel me-1"></i> Exportar a Excel
                            </button>

                            <button type="button" id="exportButtonPDF" data-filetype="pdf"
                                class="btn btn-danger btn-sm exportButton">
                                <i class="fa-solid fa-file-pdf me-1"></i> Exportar a PDF
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
        const exportUrl = '{{ route('ext_export_documentos.export') }}';
    </script>
@endsection
@push('javascript')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/sgt/reporteria/documento_ext.js') }}"></script>

    <!-- Date Range Picker -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
@endpush
