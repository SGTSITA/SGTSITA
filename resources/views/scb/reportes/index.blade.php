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
    </style>

    <div class="scb-card">
        <div class="scb-card-header">
            <div>
                <h5 class="mb-0">Filtros del reporte</h5>
                <small class="text-muted">Selecciona cuenta, periodo y tipo de reporte.</small>
            </div>
        </div>

        <div class="scb-card-body">
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
