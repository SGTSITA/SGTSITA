@extends('layouts.app')

@section('template_title')
    Viajes
@endsection

@section('content')
    <style>
        #viajesGrid {
            height: 620px;
            width: 98%;
        }
    </style>

    <meta name="csrf-token" content="{{ csrf_token() }}" />

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white">
                        <h5 class="mb-0 fw-bold">Reporte de viajes</h5>
                    </div>

                    <!-- Rango de fechas -->
                    <div class="d-flex align-items-center gap-2 px-4 pt-3">
                        <label class="mb-0 fw-semibold text-sm">Periodo:</label>
                        <input type="text" id="daterange" readonly class="form-control form-control-sm"
                            style="width: auto; min-width: 200px; box-shadow: none" />
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-start my-2 gap-2">
                            <form id="exportForm" action="{{ route('export_viajes.viajes') }}" method="POST">
                                @csrf

                                <button type="button" id="exportButtonGenericExcel" class="btn btn-outline-info btn-xs">
                                    Exportar Tablero
                                </button>

                                <button type="button" id="exportButtonExcel" data-filetype="xlsx"
                                    class="btn btn-outline-info btn-xs exportButton">
                                    Exportar a Excel
                                </button>
                                <button type="button" id="exportButtonPDF" data-filetype="pdf"
                                    class="btn btn-outline-info btn-xs exportButton">
                                    Exportar a PDF
                                </button>

                                <!-- 👇 Este input oculta todos los datos del grid -->
                                <input type="hidden" id="txtDataGenericExcel" value="@json($viajesData ?? [])" />
                            </form>
                        </div>

                        <div id="viajesGrid" class="ag-theme-alpine"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.viajesData = @json($viajesData ?? []);
        const exportUrl = '{{ route('export_viajes.viajes') }}';
    </script>
@endsection

@section('datatable')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    <script
        src="{{ asset('js/sgt/reporteria/viajes_list.js') }}?v={{ filemtime(public_path('js/sgt/reporteria/viajes_list.js')) }}">
    </script>
@endsection
