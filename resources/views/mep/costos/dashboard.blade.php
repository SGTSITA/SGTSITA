@extends('layouts.app')

@section('template_title', 'Costos de Viaje MEP - Dashboard')

@push('custom-css')
    <style>
        .card-stat {
            border: 0;
            border-radius: 1rem;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
            transition:
                transform 0.15s,
                box-shadow 0.15s;
            color: #0f172a;
        }

        .card-stat:hover {
            transform: translateY(-2px);
            box-shadow: 0 12px 24px rgba(0, 0, 0, 0.08);
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: grid;
            place-items: center;
            font-size: 20px;
            background: rgba(0, 0, 0, 0.06);
            backdrop-filter: blur(4px);
        }

        /* Gradientes claros + texto oscuro CONSISTENTE */
        .bg-gradient-total {
            background: linear-gradient(135deg, #e0f2fe, #bae6fd);
        }

        .bg-gradient-pendiente {
            background: linear-gradient(135deg, #fde68a, #fcd34d);
        }

        .bg-gradient-aprobado {
            background: linear-gradient(135deg, #bbf7d0, #86efac);
        }

        .bg-gradient-rechazado {
            background: linear-gradient(135deg, #fecaca, #fca5a5);
        }

        .stat-value {
            font-size: 2rem;
            font-weight: 800;
            line-height: 1;
        }

        .stat-label {
            margin: 0;
            opacity: 0.9;
            font-weight: 600;
        }

        .btn-clean {
            border-radius: 0.75rem;
            padding: 0.6rem 1rem;
        }
    </style>
@endpush

@section('content')
    <div class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <div>
                <h3 class="mb-0">Costos de Viaje</h3>
                <small class="text-muted">Resumen general y accesos rápidos</small>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-6 col-xl-3">
                <div class="card card-stat bg-gradient-total">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Total de los costos de viaje solicitados</p>
                            <div id="stat-total" class="stat-value">{{ $total }}</div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-layer-group"></i></div>
                    </div>
                    <div class="card-footer border-0 bg-transparent pt-0">
                        <a
                            href="{{ route('viajes.costos_mep', ['status' => 'all']) }}"
                            class="fw-semibold text-decoration-underline"
                        >
                            Ver costos solicitados
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card card-stat bg-gradient-pendiente">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Por revisar (pendientes)</p>
                            <div id="stat-pendientes" class="stat-value">{{ $porRevisar }}</div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-hourglass-half"></i></div>
                    </div>
                    <div class="card-footer border-0 bg-transparent pt-0">
                        <a
                            href="{{ route('viajes.costos_mep', ['status' => 'pendiente']) }}"
                            class="fw-semibold text-decoration-underline"
                        >
                            Ver pendientes
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card card-stat bg-gradient-aprobado">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Aprobados</p>
                            <div id="stat-aprobados" class="stat-value">{{ $aprobados }}</div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                    </div>
                    <div class="card-footer border-0 bg-transparent pt-0">
                        <a
                            href="{{ route('viajes.costos_mep', ['status' => 'aprobado']) }}"
                            class="fw-semibold text-decoration-underline"
                        >
                            Ver aprobados
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-3">
                <div class="card card-stat bg-gradient-rechazado">
                    <div class="card-body d-flex align-items-center justify-content-between">
                        <div>
                            <p class="stat-label">Rechazados</p>
                            <div id="stat-rechazados" class="stat-value">{{ $rechazados }}</div>
                        </div>
                        <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                    </div>
                    <div class="card-footer border-0 bg-transparent pt-0">
                        <a
                            href="{{ route('viajes.costos_mep', ['status' => 'rechazado']) }}"
                            class="fw-semibold text-decoration-underline"
                        >
                            Ver rechazados
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-body d-flex flex-wrap align-items-center justify-content-between">
                <div class="mb-2">
                    <h5 class="mb-1">¿Listo para capturar o editar costos?</h5>
                    <p class="text-muted mb-0">
                        Ve al tablero de trabajo para buscar por periodo, contenedor o proveedor.
                    </p>
                </div>
                <a href="{{ route('index.costos_mep') }}" class="btn btn-success btn-clean">
                    <i class="fas fa-arrow-right me-2"></i>
                    Ir al tablero de trabajo
                </a>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script>
        const urlConteos = @json(route('conteos.costos_mep'));
        const REFRESH_MS = 60000;
        async function refrescarConteos() {
            try {
                const r = await fetch(urlConteos, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                });
                if (!r.ok) return;
                const d = await r.json();
                document.getElementById('stat-total').textContent = d.total ?? 0;
                document.getElementById('stat-pendientes').textContent = d.porrevisar ?? 0;
                document.getElementById('stat-aprobados').textContent = d.aprobados ?? 0;
                document.getElementById('stat-rechazados').textContent = d.rechazados ?? 0;
            } catch (e) {
                console.warn('No se pudo refrescar conteos', e);
            }
        }
        setInterval(refrescarConteos, REFRESH_MS);
    </script>
@endpush
