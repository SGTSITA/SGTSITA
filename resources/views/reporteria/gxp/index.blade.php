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
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/reporteria/gxp.js') }}"></script>
@endsection
