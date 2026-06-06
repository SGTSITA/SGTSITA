@extends('layouts.app')

@section('content')
    <style>
        .consumo-card {
            border-radius: 18px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
            height: 100%;
        }

        .consumo-card small {
            display: block;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            margin-bottom: 6px;
        }

        .consumo-card h5 {
            margin: 0;
            font-weight: 900;
            color: #0f172a;
        }

        .consumo-card-primary {
            background: #eff6ff;
            border-color: #bfdbfe;
        }

        .consumo-card-success {
            background: #ecfdf5;
            border-color: #bbf7d0;
        }

        .consumo-card-warning {
            background: #fffbeb;
            border-color: #fde68a;
        }

        .consumo-card-danger {
            background: #fff1f2;
            border-color: #fecdd3;
        }

        .consumo-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
        }

        .rendimiento-bueno {
            color: #15803d !important;
            font-weight: 800;
        }

        .rendimiento-medio {
            color: #ca8a04 !important;
            font-weight: 800;
        }

        .rendimiento-bajo {
            color: #b91c1c !important;
            font-weight: 800;
        }


        .ruta-cell {
            min-width: 260px;
            max-width: 340px;
            white-space: normal !important;
        }

        .ruta-box {
            display: flex;
            flex-direction: column;
            gap: 6px;
            max-width: 340px;
        }

        .ruta-item {
            display: flex;
            flex-direction: column;
            line-height: 1.25;
        }

        .ruta-label {
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            color: #64748b;
            letter-spacing: .04em;
        }

        .ruta-text {
            font-size: 12px;
            font-weight: 600;
            color: #111827;
            white-space: normal;
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .ruta-divider {
            height: 1px;
            background: #e5e7eb;
            width: 100%;
        }
    </style>

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
                    <button type="button" class="btn bg-gradient-primary w-100" id="btnConsultarConsumo">
                        <i class="fas fa-search me-1"></i>
                        Consultar
                    </button>
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
            <div class="table-responsive">
                <table class="table consumo-table align-items-center mb-0">
                    <thead>
                        <tr>
                            <th>Fecha salida</th>
                            <th>Contenedor</th>
                            <th>Operador</th>
                            <th>Ruta</th>
                            <th class="text-end">KM</th>
                            <th class="text-end">Litros</th>
                            <th class="text-end">KM/L</th>
                            <th>Observación</th>
                        </tr>
                    </thead>

                    <tbody id="tbodyConsumoUnidad">
                        <tr>
                            <td colspan="7" class="text-center text-muted py-4">
                                Selecciona unidad y rango de fechas para consultar.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script>
        const URL_CONSUMO_UNIDADES = "{{ route('reporteria.consumo-unidades.data') }}";
    </script>

    <script
        src="{{ asset('js/sgt/reporteria/consumo-unidades.js') }}?v={{ filemtime(public_path('js/sgt/reporteria/consumo-unidades.js')) }}">
    </script>
@endpush
