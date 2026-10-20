@extends('scb.layouts')

@section('template_title', 'Dashboard')
@section('page_title', 'Dashboard bancario')
@section('page_subtitle', 'Resumen general del módulo de control bancario')

@section('content')
    <style>
        .scb-stat-card {
            border: 1px solid #e9eef5;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
            overflow: hidden;
            height: 100%;
        }

        .scb-stat-card .scb-stat-topline {
            height: 4px;
            background: linear-gradient(90deg, #12355b, #1f7a8c);
        }

        .scb-stat-card .scb-stat-body {
            padding: 1rem 1.1rem;
        }

        .scb-stat-label {
            font-size: 12px;
            font-weight: 700;
            color: #7b8794;
            text-transform: uppercase;
            letter-spacing: .04em;
            margin-bottom: .35rem;
        }

        .scb-stat-value {
            font-size: 1.55rem;
            font-weight: 800;
            color: #12355b;
            margin-bottom: .2rem;
            line-height: 1.1;
        }

        .scb-stat-sub {
            font-size: 12px;
            color: #7b8794;
        }

        .scb-stat-icon {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f4f7fb;
            color: #12355b;
            border: 1px solid #e3eaf2;
            font-size: 16px;
            flex-shrink: 0;
        }

        .scb-hero-card {
            border: 1px solid #e9eef5;
            border-radius: 18px;
            background: linear-gradient(135deg, #12355b 0%, #0f2741 100%);
            color: #fff;
            box-shadow: 0 14px 32px rgba(18, 53, 91, 0.12);
        }

        .scb-hero-body {
            padding: 1.4rem 1.4rem;
        }

        .scb-hero-label {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: .05em;
            opacity: .75;
            font-weight: 700;
        }

        .scb-hero-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1.1;
            margin: .35rem 0 .4rem;
        }

        .scb-hero-mini {
            border: 1px solid rgba(255, 255, 255, .12);
            background: rgba(255, 255, 255, .06);
            border-radius: 14px;
            padding: .85rem 1rem;
            height: 100%;
        }

        .scb-hero-mini-label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: .04em;
            opacity: .72;
            margin-bottom: .2rem;
            font-weight: 700;
        }

        .scb-hero-mini-value {
            font-size: 1.1rem;
            font-weight: 800;
        }

        .scb-kpi-positive {
            color: #198754 !important;
        }

        .scb-kpi-negative {
            color: #dc3545 !important;
        }

        .scb-table-card {
            border: 1px solid #e9eef5;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 8px 22px rgba(15, 23, 42, 0.04);
        }

        .scb-table-card .scb-card-header {
            border-bottom: 1px solid #edf2f7;
            background: #fff;
        }
    </style>

    <div class="row g-3">

        {{-- HERO --}}
        <div class="col-12">
            <div class="scb-hero-card">
                <div class="scb-hero-body">
                    <div class="row g-3 align-items-stretch">
                        <div class="col-lg-4">
                            <div class="h-100 d-flex flex-column justify-content-center">
                                <div class="scb-hero-label">Saldo global consolidado</div>
                                <div class="scb-hero-value">
                                    ${{ number_format($saldoGlobal ?? 0, 2) }}
                                </div>
                                <div class="small" style="opacity:.8;">
                                    Saldo acumulado considerando saldo inicial, ingresos y egresos.
                                </div>
                            </div>
                        </div>

                        <div class="col-lg-8">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <div class="scb-hero-mini">
                                        <div class="scb-hero-mini-label">Flujo neto del mes</div>
                                        <div class="scb-hero-mini-value">
                                            ${{ number_format($flujoNetoMes ?? 0, 2) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="scb-hero-mini">
                                        <div class="scb-hero-mini-label">Movimientos del mes</div>
                                        <div class="scb-hero-mini-value">
                                            {{ $totalMovimientosMes ?? 0 }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="scb-hero-mini">
                                        <div class="scb-hero-mini-label">Ticket promedio mensual</div>
                                        <div class="scb-hero-mini-value">
                                            ${{ number_format($ticketPromedioMes ?? 0, 2) }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="scb-hero-mini">
                                        <div class="scb-hero-mini-label">Bancos activos</div>
                                        <div class="scb-hero-mini-value">
                                            {{ $totalBancosActivos ?? 0 }} / {{ $totalBancos ?? 0 }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="scb-hero-mini">
                                        <div class="scb-hero-mini-label">Cuentas activas</div>
                                        <div class="scb-hero-mini-value">
                                            {{ $totalCuentasActivas ?? 0 }} / {{ $totalCuentas ?? 0 }}
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <div class="scb-hero-mini">
                                        <div class="scb-hero-mini-label">Unidades registradas</div>
                                        <div class="scb-hero-mini-value">
                                            {{ $totalUnidades ?? 0 }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- KPIS OPERATIVOS --}}



        {{-- KPIS FINANCIEROS --}}

        <div class="col-xl-4 col-md-6">
            <div class="scb-stat-card">
                <div class="scb-stat-topline"></div>
                <div class="scb-stat-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="scb-stat-label">Ingresos del mes</div>
                            <div class="scb-stat-value scb-kpi-positive">
                                ${{ number_format($totalCargosMes ?? 0, 2) }}
                            </div>
                            <div class="scb-stat-sub">
                                Total de cargos registrados este mes
                            </div>
                        </div>
                        <div class="scb-stat-icon">
                            <i class="fas fa-arrow-trend-up"></i>

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6">
            <div class="scb-stat-card">
                <div class="scb-stat-topline"></div>
                <div class="scb-stat-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="scb-stat-label">Egresos del mes</div>
                            <div class="scb-stat-value scb-kpi-negative">
                                ${{ number_format($totalAbonosMes ?? 0, 2) }}
                            </div>
                            <div class="scb-stat-sub">
                                Total de abonos registrados este mes
                            </div>
                        </div>
                        <div class="scb-stat-icon">
                            <i class="fas fa-arrow-trend-down"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>



        <div class="col-xl-4 col-md-12">
            <div class="scb-stat-card">
                <div class="scb-stat-topline"></div>
                <div class="scb-stat-body">
                    <div class="d-flex justify-content-between align-items-start gap-3">
                        <div>
                            <div class="scb-stat-label">Promedio saldo inicial</div>
                            <div class="scb-stat-value">
                                ${{ number_format($promedioSaldoInicial ?? 0, 2) }}
                            </div>
                            <div class="scb-stat-sub">
                                Promedio de saldo inicial por cuenta
                            </div>
                        </div>
                        <div class="scb-stat-icon">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- TABLA --}}
        <div class="col-12">
            <div class="scb-table-card mt-2">
                <div class="scb-card-header d-flex justify-content-between align-items-center p-3">
                    <div>
                        <h5 class="mb-0">Últimos movimientos</h5>
                        <small class="text-muted">
                            Movimientos bancarios registrados recientemente
                        </small>
                    </div>

                    <a href="{{ route('scb.movimientos.index') }}" class="btn btn-sm scb-btn-primary">
                        <i class="fas fa-eye me-1"></i>
                        Ver todos
                    </a>
                </div>

                <div class="scb-card-body p-3">
                    <div class="table-responsive">
                        <table class="table align-items-center mb-0">
                            <thead>
                                <tr>
                                    <th>Fecha</th>
                                    <th>Banco</th>
                                    <th>Cuenta / Beneficiario</th>
                                    <th>Tipo</th>
                                    <th>Concepto</th>
                                    <th>Referencia</th>
                                    <th>Usuario</th>
                                    <th class="text-end">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                @forelse ($ultimosMovimientos as $movimiento)
                                    <tr>
                                        <td>
                                            {{ $movimiento->fecha_movimiento?->format('d/m/Y') }}
                                        </td>

                                        <td class="fw-bold">
                                            {{ $movimiento->cuenta?->banco?->nombre ?? 'S/N' }}
                                        </td>

                                        <td>
                                            <div class="fw-bold">
                                                {{ $movimiento->cuenta?->beneficiario ?? 'S/N' }}
                                            </div>
                                            <small class="text-muted">
                                                {{ $movimiento->cuenta?->numero_cuenta ?? 'Sin cuenta' }}
                                            </small>
                                        </td>

                                        <td>
                                            @if ($movimiento->tipo === 'abono')
                                                <span class="badge bg-success">Abono</span>
                                            @else
                                                <span class="badge bg-danger">Cargo</span>
                                            @endif
                                        </td>

                                        <td>{{ $movimiento->concepto }}</td>
                                        <td>{{ $movimiento->referencia_bancaria ?? 'S/N' }}</td>
                                        <td>{{ $movimiento->usuario?->name ?? ($movimiento->usuario?->nombre ?? 'S/N') }}
                                        </td>

                                        <td class="text-end fw-bold">
                                            ${{ number_format($movimiento->total ?? 0, 2) }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-muted py-4">
                                            No hay movimientos registrados todavía.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
