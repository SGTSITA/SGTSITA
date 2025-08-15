@extends('layouts.app')
<meta name="csrf-token" content="{{ csrf_token() }}">
@section('template_title', 'Viajes solicitados / Cambios de costos')

<style>
    /* Grid de cards */
    .field-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 12px;
    }

    #btnReenviarCambios {
        background: linear-gradient(135deg, #0061f2, #3b82f6);
        color: white;
        transition: all 0.3s ease;
    }

    #btnReenviarCambios:hover {
        background: linear-gradient(135deg, #0051d4, #2563eb);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    /* Card base */
    .field-card {
        position: relative;
        border-radius: 14px;
        padding: 14px 12px;
        background: linear-gradient(180deg, #ffffff, #f8fafc);
        border: 1px solid rgba(0, 0, 0, 0.06);
        box-shadow: 0 10px 20px rgba(15, 23, 42, 0.05);
        transition: transform .15s ease, box-shadow .15s ease, border-color .15s ease;
        cursor: pointer;
        user-select: none;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .field-card:hover {
        transform: translateY(-1px);
        box-shadow: 0 14px 28px rgba(15, 23, 42, 0.08);
    }

    .field-card .fc-icon {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: grid;
        place-items: center;
        background: #eef2ff;
        color: #1d4ed8;
        font-size: 16px;
        flex: 0 0 auto;
    }

    .field-card .fc-name {
        font-weight: 700;
        color: #0f172a;
        letter-spacing: .2px;
        text-transform: capitalize;
    }

    .field-card input[type="checkbox"] {
        display: none;
    }

    /* Estado seleccionado */
    .field-card.is-selected {
        background: linear-gradient(180deg, #ecfeff, #e0f2fe);
        border-color: #38bdf8;
        box-shadow: 0 10px 22px rgba(14, 165, 233, 0.18);
    }

    .field-card.is-selected .fc-icon {
        background: #dcfce7;
        color: #047857;
    }

    /* Sticker de check arriba a la derecha */
    .field-card .fc-check {
        position: absolute;
        top: 8px;
        right: 8px;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        background: #e2e8f0;
        color: #475569;
        font-size: 12px;
        transition: background .15s ease, color .15s ease;
    }

    .field-card.is-selected .fc-check {
        background: #22c55e;
        color: white;
    }

    /* Header modal info */
    .obs-meta {
        display: flex;
        align-items: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .obs-pill {
        background: #0ea5e9;
        color: white;
        border-radius: 999px;
        padding: 4px 10px;
        font-weight: 700;
    }

    .obs-tip {
        color: #64748b;
        font-size: 12px;
    }

    .obs-actions {
        display: flex;
        gap: 8px;
        margin-left: auto;
    }

    /* Botón mac-ish */
    .btn-mac {
        border-radius: 10px;
        padding: .55rem .9rem;
        font-weight: 700;
        border: 1px solid rgba(0, 0, 0, .06);
        box-shadow: 0 6px 14px rgba(15, 23, 42, .06);
    }

    .modal-content {
        background-color: #fff;
        border-radius: 16px;
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.08);
    }

    .table th,
    .table td {
        font-size: 14px;
        vertical-align: middle;
        padding: 0.6rem 0.75rem;
    }

    .table th {
        font-weight: 600;
        color: #334155;
        background-color: #f1f5f9;
    }

    #motivoRechazo {
        font-size: 14px;
    }
</style>


@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="mb-0"> Costos de viajes solicitados</h3>
                <small class="text-muted">Lista por estatus </small>
            </div>
            <a href="{{ route('dashboard.costos_mep') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-2"></i> Volver al dashboard
            </a>
        </div>

        <div class="card p-3 mb-3 toolbar shadow-sm border-0 rounded-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="btn-group btn-group-toggle" data-bs-toggle="buttons">
                    <input type="radio" class="btn-check" name="statusFilter" id="btnAll" autocomplete="off" checked>
                    <label class="btn btn-light border  px-4" for="btnAll">
                        <i class="fas fa-list me-1"></i> Todos
                    </label>

                    <input type="radio" class="btn-check" name="statusFilter" id="btnPendientes" autocomplete="off">
                    <label class="btn btn-warning-light text-dark border  px-4" for="btnPendientes">
                        <i class="fas fa-hourglass-half me-1"></i> Pendientes
                    </label>

                    <input type="radio" class="btn-check" name="statusFilter" id="btnAprobados" autocomplete="off">
                    <label class="btn btn-success-light text-dark border  px-4" for="btnAprobados">
                        <i class="fas fa-check-circle me-1"></i> Aprobados
                    </label>

                    <input type="radio" class="btn-check" name="statusFilter" id="btnRechazados" autocomplete="off">
                    <label class="btn btn-danger-light text-dark border  px-4" for="btnRechazados">
                        <i class="fas fa-times-circle me-1"></i> Rechazados
                    </label>
                </div>

                <div class="ms-auto">
                    <input type="text" id="quickSearch" class="form-control form-control-sm rounded-pill shadow-sm"
                        placeholder="🔍 Buscar..." style="min-width: 220px;" />
                </div>
            </div>
        </div>


        <div id="gridCambios" class="ag-theme-alpine" style="height: 68vh;"></div>
    </div>

    <div class="modal fade" id="modalObservaciones" tabindex="-1" aria-labelledby="modalObservacionesLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 rounded-4 shadow-sm">
                <div class="modal-header border-0 pb-0">
                    <div>
                        <h5 class="modal-title fw-semibold text-dark" id="modalObservacionesLabel">Observaciones del cambio
                        </h5>
                        <small class="text-muted">Detalle de campos observados y motivo del rechazo</small>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body pt-3">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted mb-1">Contenedor</label>
                            <div id="infoContenedor" class="fw-semibold text-dark">-</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted mb-1">Fecha del viaje</label>
                            <div id="infoFechaViaje" class="fw-semibold text-dark">-</div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-medium text-muted mb-1">Fecha del cambio</label>
                            <div id="infoFechaCambio" class="fw-semibold text-dark">-</div>
                        </div>
                    </div>
                    <div class="bg-light border rounded-3 p-3 mb-4">
                        <label class="form-label mb-1 fw-medium text-muted">Motivo del rechazo</label>
                        <div id="motivoRechazo" class="text-dark fw-semibold" style="white-space: pre-wrap;"></div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-start">Costos del viaje</th>
                                    <th class="text-start">Valores</th>
                                </tr>
                            </thead>
                            <tbody id="tablaCamposObservados">
                                <!-- Contenido generado dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-3">
                    <button type="button" class="btn btn-light rounded-pill px-4 shadow-sm" data-bs-dismiss="modal">
                        Cancelar
                    </button>
                    <button id="btnReenviarCambios" class="btn rounded-pill px-4 py-2 shadow-sm">
                        <i class="fas fa-sync-alt me-2"></i> Reenviar cambios
                    </button>
                </div>
            </div>
        </div>
    </div>



@endsection

@push('custom-javascript')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/mep/costosviajes/viajes_cambios_list.js') }}"></script>
@endpush
