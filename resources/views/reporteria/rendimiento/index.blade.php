@extends('layouts.app')

@section('css')
    <style>
        .header-center .ag-header-cell-label {
            justify-content: center;
            text-align: center;
        }

        .header-center .ag-header-cell-text {
            white-space: normal;
            line-height: 1.3;
        }

        .cell-two-lines {
            width: 100%;
            overflow: hidden;
            line-height: 1.25;
        }

        .cell-main {
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cell-sub {
            font-size: 11px;
            color: #6c757d;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cell-truncate {
            width: 100%;
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .rendimiento-bueno {
            color: #198754;
            font-weight: 700;
        }

        .rendimiento-medio {
            color: #b7791f;
            font-weight: 700;
        }

        .rendimiento-bajo {
            color: #dc3545;
            font-weight: 700;
        }

        .badge-observacion-grid {
            display: block;
            max-width: 100%;
            white-space: normal !important;
            word-break: break-word;
            overflow-wrap: anywhere;
            line-height: 1.3;
            text-align: left;
        }
    </style>
@endsection


@section('content')
    <div class="card mb-4">
        <div class="card-header pb-0">
            <h5>Reporte de consumo por unidad</h5>
            <p class="text-sm text-muted mb-0">
                Consulta kilómetros, litros diesel y rendimiento por viaje.
            </p>
        </div>

        <div class="card-body">
            <div class="row g-3 align-items-end">
                <div class="col-md-5">
                    <label class="form-label fw-bold">Unidad / Tracto</label>
                    <select id="unidad_id" class="form-select">
                        <option value="">Seleccione unidad</option>
                        @foreach ($equipos as $equipo)
                            <option value="{{ $equipo->id }}">
                                {{ $equipo->id_equipo ?? 'S/N' }}
                                {{ $equipo->marca ? ' - ' . $equipo->marca : '' }}
                                {{ $equipo->placas ? ' - ' . $equipo->placas : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Fecha inicio</label>
                    <input type="date" id="fecha_inicio" class="form-control">
                </div>

                <div class="col-md-2">
                    <label class="form-label fw-bold">Fecha fin</label>
                    <input type="date" id="fecha_fin" class="form-control">
                </div>

                <div class="col-md-3">
                    <label class="form-label fw-bold d-block">&nbsp;</label>

                    <div class="d-flex gap-2">
                        <button type="button" class="btn bg-gradient-primary flex-fill" id="btnConsultarConsumo">
                            <i class="fas fa-search me-1"></i>
                            Consultar
                        </button>

                        <button type="button" class="btn btn-outline-danger" id="btnExportarPdfConsumo"
                            title="Exportar PDF">
                            <i class="fas fa-file-pdf"></i>
                        </button>

                        <button type="button" class="btn btn-outline-success" id="btnExportarExcelConsumo"
                            title="Exportar Excel">
                            <i class="fas fa-file-excel"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 mb-4 d-none" id="resumenConsumoUnidad">
        <div class="col-md-2">
            <div class="consumo-card consumo-card-primary">
                <small>Viajes</small>
                <h5 id="lblTotalViajes">0</h5>
            </div>
        </div>

        <div class="col-md-2">
            <div class="consumo-card consumo-card-success">
                <small>Con datos</small>
                <h5 id="lblViajesConDatos">0</h5>
            </div>
        </div>

        <div class="col-md-2">
            <div class="consumo-card consumo-card-warning">
                <small>Sin datos</small>
                <h5 id="lblViajesSinDatos">0</h5>
            </div>
        </div>

        <div class="col-md-2">
            <div class="consumo-card">
                <small>Total KM</small>
                <h5 id="lblTotalKm">0.00</h5>
            </div>
        </div>

        <div class="col-md-2">
            <div class="consumo-card">
                <small>Total litros</small>
                <h5 id="lblTotalLitros">0.000</h5>
            </div>
        </div>

        <div class="col-md-2">
            <div class="consumo-card consumo-card-danger">
                <small>KM / Litro</small>
                <h5 id="lblRendimientoPromedio">0.000</h5>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header pb-0">
            <h6 class="mb-0">Detalle de consumo por viaje</h6>
        </div>

        <div class="card-body">
            <div id="gridConsumoUnidad" class="col-12 ag-theme-quartz mb-6" style="height: 610px"></div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script>
        const URL_CONSUMO_UNIDADES = "{{ route('reporteria.consumo-unidades.data') }}";
        const URL_CONSUMO_UNIDADES_EXPORTAR = "{{ route('reporteria.consumo-unidades.exportar', ['tipo' => '__TIPO__']) }}";
    </script>

    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <script
        src="{{ asset('js/sgt/reporteria/consumo-unidades.js') }}?v={{ filemtime(public_path('js/sgt/reporteria/consumo-unidades.js')) }}">
    </script>
@endpush
