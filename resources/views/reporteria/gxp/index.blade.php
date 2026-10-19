@extends('layouts.app')

@section('template_title')
    Gastos por Pagar
@endsection

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}" />
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
                        <input
                            type="text"
                            id="daterange"
                            readonly
                            class="form-control form-control-sm"
                            style="width: auto; min-width: 200px; box-shadow: none"
                        />
                        
                        <label class="mb-0 fw-semibold text-sm ms-3">Estatus:</label>
                        <select id="statusFilter" class="form-select form-select-sm" style="width: auto; min-width: 150px; box-shadow: none">
                            <option value="por_pagar">Por pagar</option>
                            <option value="pagados">Pagados</option>
                            <option value="todos">Todos</option>
                        </select>

                        <label class="mb-0 fw-semibold text-sm ms-3">Unidad / Equipo:</label>
                        <select id="selEquipoGxp" class="form-select form-select-sm" style="width: auto; min-width: 180px; box-shadow: none">
                            <option value="">-- Todas las Unidades --</option>
                            @if(isset($equipos))
                                @foreach ($equipos as $eq)
                                    <option value="{{ $eq->id }}">{{ $eq->texto_select }}</option>
                                @endforeach
                            @endif
                        </select>
                    </div>

                    <div class="card-body">
                        <div class="d-flex justify-content-start my-2 gap-2 align-items-center flex-wrap">
                            <div class="form-check form-switch d-inline-flex align-items-center me-2">
                                <input class="form-check-input" type="checkbox" id="checkSepararUnidadesGxp" name="separar_unidades" value="1">
                                <label class="form-check-label ms-2 mb-0 fw-bold text-dark text-xs" for="checkSepararUnidadesGxp">
                                    Separar por unidades
                                </label>
                            </div>
                            <button
                                type="button"
                                id="exportButtonExcel"
                                data-filetype="xlsx"
                                class="btn btn-outline-info btn-xs exportButton"
                            >
                                Exportar a Excel
                            </button>
                            <button
                                type="button"
                                id="exportButtonPDF"
                                data-filetype="pdf"
                                class="btn btn-outline-info btn-xs exportButton"
                            >
                                Exportar a PDF
                            </button>
                        </div>

                        <div id="myGrid" class="ag-theme-alpine" style="height: 600px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const exportUrl = '{{ route('gxp.export') }}';
    </script>
@endsection

@section('datatable')
    {{-- AG Grid --}}
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    {{-- Tu script principal --}}
    <script src="{{ asset('js/sgt/reporteria/gxp.js') }}?v={{ filemtime(public_path('js/sgt/reporteria/gxp.js')) }}">
        >
    </script>
@endsection
