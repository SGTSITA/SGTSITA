@extends('layouts.app')

@section('template_title')
    Gastos por Pagar
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <div class="card shadow-sm border-0 rounded-3">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white">
                        <h5 class="mb-0 fw-bold">Gastos por pagar</h5>
                    </div>
                    <!-- Rango de fechas -->
                    <div class="d-flex align-items-center gap-2 px-4 pt-3">
                        <label class="mb-0 fw-semibold text-sm">Periodo:</label>
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

                        <div id="myGrid" class="ag-theme-alpine" style="height: 600px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.cotizacionesData = @json($gastos ?? []);
        const exportUrl = "{{ route('gxp.export') }}";
    </script>
@endsection


@section('datatable')
    {{-- Moment.js (para fechas) --}}
    <script src="https://cdn.jsdelivr.net/npm/moment@2.29.4/moment.min.js"></script>

    {{-- Daterangepicker (para seleccionar periodo) --}}
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

    {{-- AG Grid --}}
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    {{-- Tu script principal --}}
    <script src="{{ asset('js/sgt/reporteria/gxp.js') }}"></script>
@endsection
