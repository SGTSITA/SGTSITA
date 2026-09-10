@extends('scb.layouts')

@section('template_title', 'Reportes')
@section('page_title', 'Reportes bancarios')
@section('page_subtitle', 'Consulta, exporta y descarga reportes por cuenta y periodo')

@section('content')
    <style>
        .reporte-card {
            border-radius: 18px;
            padding: 16px;
            border: 1px solid #e5e7eb;
            background: #ffffff;
            box-shadow: 0 6px 18px rgba(15, 23, 42, 0.06);
            height: 100%;
        }

        .reporte-card small {
            display: block;
            font-size: 12px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .03em;
            margin-bottom: 6px;
        }

        .reporte-card h5 {
            margin: 0;
            font-size: 20px;
            font-weight: 900;
        }

        .reporte-card-neutral {
            background: #f8fafc;
            border-color: #cbd5e1;
            color: #0f172a;
        }

        .reporte-card-danger {
            background: #fff1f2;
            border-color: #fecdd3;
            color: #991b1b;
        }

        .reporte-card-success {
            background: #ecfdf5;
            border-color: #bbf7d0;
            color: #166534;
        }

        .reporte-card-primary {
            background: #eff6ff;
            border-color: #bfdbfe;
            color: #1d4ed8;
        }

        .reporte-table thead th {
            background: #f8fafc;
            color: #334155;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .04em;
            border-bottom: 1px solid #e5e7eb;
        }

        .reporte-table tbody td {
            color: #1f2937;
            vertical-align: middle;
        }



        .monto-saldo-positivo {
            color: #1d4ed8 !important;
            font-weight: 900;
        }

        .monto-saldo-negativo {
            color: #b91c1c !important;
            font-weight: 900;
        }

        .reporte-master-row {
            cursor: pointer;
        }

        .reporte-chevron {
            transition: transform 0.18s ease;
            color: #64748b;
        }

        .reporte-master-row.detalle-abierto .reporte-chevron {
            transform: rotate(90deg);
        }

        .reporte-detalle-row td {
            background: #f8fafc;
        }

        .reporte-collapse-box {
            margin: 0;
            padding: 12px 16px;
            background: #f8fafc;
            border-top: 1px solid #e5e7eb;
            border-bottom: 1px solid #e5e7eb;
        }

        .reporte-collapse-title {
            font-size: 13px;
            font-weight: 900;
            color: #334155;
        }

        .reporte-detalle-table thead th {
            font-size: 11px;
            background: #eef2f7;
        }

        .reporte-detalle-total {
            font-weight: 900;
            color: #0f172a;
        }

        .scb-reporte-toolbar {
            border: 1px solid #edf0f4;
            background: #ffffff;
            border-radius: 14px;
            padding: 0.65rem 0.85rem;
            margin-bottom: 0.85rem;
        }

        .scb-reporte-toolbar-title {
            min-width: 220px;
        }

        .scb-reporte-actions {
            flex: 1;
            justify-content: flex-end;
        }

        .scb-select-orden-reporte {
            min-width: 300px;
            max-width: 380px;
        }

        .scb-search-reporte {
            min-width: 340px;
            max-width: 430px;
        }

        .scb-search-reporte .form-control,
        .scb-search-reporte .input-group-text,
        .scb-search-reporte .btn,
        .scb-select-orden-reporte {
            height: 34px;
            font-size: 0.8rem;
        }

        .scb-search-reporte .form-control:focus,
        .scb-select-orden-reporte:focus {
            box-shadow: none;
            border-color: #ced4da;
        }

        @media (max-width: 768px) {
            .scb-reporte-toolbar {
                align-items: stretch !important;
            }

            .scb-reporte-toolbar-title,
            .scb-reporte-actions,
            .scb-search-reporte,
            .scb-select-orden-reporte {
                width: 100%;
                max-width: 100%;
            }

            .scb-reporte-actions {
                justify-content: flex-start;
            }

            .scb-reporte-actions>div {
                width: 100%;
            }
        }

        .scb-filtros-collapse-btn {
            border: 1px solid #e5e7eb;
            background: #ffffff;
            color: #334155;
            border-radius: 999px;
            padding: 0.35rem 0.75rem;
            font-size: 0.8rem;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }

        .scb-filtros-collapse-btn:hover {
            background: #f8fafc;
            color: #0f172a;
        }

        .scb-filtros-collapse-btn .fa-chevron-up {
            transition: transform 0.18s ease;
        }

        .scb-filtros-collapse-btn.collapsed .fa-chevron-up {
            transform: rotate(180deg);
        }

        .scb-filtros-body {
            padding-top: 0.85rem;
        }

        .scb-filtros-resumen {
            font-size: 0.78rem;
            color: #64748b;
        }
    </style>

    <div class="scb-card">
        <div class="scb-card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <h5 class="mb-0">Filtros del reporte</h5>
                <small class="text-muted">
                    Selecciona cuenta, periodo y tipo de reporte.
                </small>
            </div>

            <button class="scb-filtros-collapse-btn" type="button" data-bs-toggle="collapse"
                data-bs-target="#collapseFiltrosReporte" aria-expanded="true" aria-controls="collapseFiltrosReporte">
                <i class="fas fa-filter"></i>
                <span>Mostrar / ocultar filtros</span>
                <i class="fas fa-chevron-up"></i>
            </button>
        </div>

        <div id="collapseFiltrosReporte" class="collapse show">
            <div class="scb-card-body scb-filtros-body">
                <div class="row g-3 align-items-end">
                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-bold">Cuenta bancaria</label>
                        <select id="cuenta_id" class="form-select">
                            <option value="">Seleccione cuenta</option>
                            @foreach ($cuentas as $cuenta)
                                <option value="{{ $cuenta->id }}">
                                    {{ $cuenta->banco?->nombre }} -
                                    {{ $cuenta->beneficiario ?? 'S/N' }} -
                                    {{ $cuenta->numero_cuenta ?? 'Sin cuenta' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-bold">Unidad</label>
                        <select id="unidad_id" class="form-select">
                            <option value="">Todas las unidades</option>
                            @foreach ($unidades as $unidad)
                                <option value="{{ $unidad->id }}">
                                    {{ $unidad->descripcion ?? 'S/N' }}
                                    @if (!empty($unidad->placas))
                                        - {{ $unidad->placas }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-lg-4">
                        <label class="form-label fw-bold">Tipo reporte</label>
                        <select id="tipo_reporte" class="form-select">
                            <option value="estado_cuenta">Estado de cuenta</option>
                            <option value="detallado">Detallado</option>
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label fw-bold">Fecha inicio</label>
                        <input type="date" id="fecha_inicio" class="form-control">
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label fw-bold">Fecha fin</label>
                        <input type="date" id="fecha_fin" class="form-control">
                    </div>

                    <div class="col-12 col-lg-6">
                        <label class="form-label fw-bold d-none d-lg-block">&nbsp;</label>

                        <div class="d-flex flex-column flex-md-row justify-content-lg-end gap-2">
                            <button type="button" class="btn scb-btn-primary px-4" id="btnConsultarReporte">
                                <i class="fas fa-search me-1"></i>
                                Consultar
                            </button>

                            <button type="button" class="btn btn-outline-danger px-4" id="btnReportePdf">
                                <i class="fas fa-file-pdf me-1"></i>
                                PDF
                            </button>

                            <button type="button" class="btn btn-outline-success px-4" id="btnReporteExcel">
                                <i class="fas fa-file-excel me-1"></i>
                                Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 my-4 d-none" id="resumenReporte">
        <div class="col-md-3">
            <div class="reporte-card reporte-card-neutral">
                <small>Saldo inicial</small>
                <h5 id="lblSaldoInicial">$0.00</h5>
            </div>
        </div>

        <div class="col-md-3">
            <div class="estado-card estado-card-cargos">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small>Cargos</small>
                        <h5 id="lblTotalCargos">$0.00</h5>
                    </div>
                    <i class="fas fa-arrow-up"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="estado-card estado-card-abonos">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small>Abonos</small>
                        <h5 id="lblTotalAbonos">$0.00</h5>
                    </div>

                    <i class="fas fa-arrow-down"></i>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="reporte-card reporte-card-primary">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <small>Saldo final</small>
                        <h5 id="lblSaldoFinal">$0.00</h5>
                    </div>
                    <i class="fas fa-balance-scale"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="scb-card">
        <div class="scb-card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0" id="tituloReporteTabla">Resultado del reporte</h5>
                <small class="text-muted" id="subtituloReporteTabla">
                    Selecciona filtros y consulta.
                </small>
            </div>
        </div>

        <div class="scb-card-body">
            <div class="scb-reporte-toolbar d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div class="scb-reporte-toolbar-title">
                    <h6 class="mb-0 fw-bold">Movimientos</h6>
                    <small class="text-muted" id="lblConteoReporte">
                        Sin resultados cargados.
                    </small>
                </div>

                <div class="scb-reporte-actions d-flex align-items-center flex-wrap gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <label for="ordenReporteTabla" class="form-label mb-0 small text-muted fw-semibold text-nowrap">
                            Ordenar por:
                        </label>

                        <select id="ordenReporteTabla" class="form-select form-select-sm scb-select-orden-reporte">
                            <option value="fecha_asc">Fecha: antigua a reciente</option>
                            <option value="fecha_desc">Fecha: reciente a antigua</option>
                            <option value="cargo_desc">Cargo: mayor a menor</option>
                            <option value="cargo_asc">Cargo: menor a mayor</option>
                            <option value="abono_desc">Abono: mayor a menor</option>
                            <option value="abono_asc">Abono: menor a mayor</option>
                            <option value="saldo_desc">Saldo: mayor a menor</option>
                            <option value="saldo_asc">Saldo: menor a mayor</option>
                            <option value="concepto_asc">Concepto: A-Z</option>
                            <option value="concepto_desc">Concepto: Z-A</option>
                            <option value="referencia_asc">Referencia: A-Z</option>
                            <option value="referencia_desc">Referencia: Z-A</option>
                        </select>
                    </div>

                    <div class="input-group input-group-sm scb-search-reporte">
                        <span class="input-group-text bg-white">
                            <i class="fas fa-search text-muted"></i>
                        </span>

                        <input type="text" class="form-control" id="buscarTablaReporte"
                            placeholder="Buscar movimiento, unidad, referencia...">

                        <button type="button" class="btn btn-light border d-none" id="btnLimpiarBusquedaReporte"
                            title="Limpiar búsqueda">
                            <i class="fas fa-times text-muted"></i>
                        </button>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table reporte-table align-items-center mb-0" id="tablaReporte">
                    <thead id="theadReporte">
                        <tr>
                            <th class="text-center">Resultado</th>
                        </tr>
                    </thead>

                    <tbody id="tbodyReporte">
                        <tr>
                            <td class="text-center text-muted py-4">
                                Selecciona filtros para consultar el reporte.
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
        const SCB_REPORTE_URLS = {
            consultar: "{{ route('scb.reportes.consultar') }}",
            pdf: "{{ route('scb.reportes.pdf') }}",
            excel: "{{ route('scb.reportes.excel') }}",
        };
    </script>

    <script src="{{ asset('js/scb/reportes.js') }}?v={{ filemtime(public_path('js/scb/reportes.js')) }}"></script>
@endpush
