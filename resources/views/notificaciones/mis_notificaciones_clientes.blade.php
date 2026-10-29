@extends('layouts.usuario_externo')

@section('template_title')
    Mis notificaciones
@endsection

@push('handsontable')
    <style>
        .notificaciones-page {
            width: 100%;
            max-width: 100%;
            min-width: 0;
        }

        .notificaciones-card {
            max-width: 100%;
            min-width: 0;
        }

        .notificacion-item {
            max-width: 100%;
            min-width: 0;
            transition: box-shadow .15s ease, transform .15s ease;
        }

        .notificacion-item:hover {
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
            transform: translateY(-1px);
        }

        .notificacion-contenido {
            min-width: 0;
            max-width: 100%;
        }

        .notificacion-titulo,
        .notificacion-mensaje {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .notificaciones-pagination {
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            padding-bottom: 2px;
        }

        .notificaciones-pagination nav {
            width: 100%;
        }

        .notificaciones-pagination .pagination {
            margin-bottom: 0;
            flex-wrap: wrap;
            gap: .35rem;
            justify-content: center;
        }

        .notificaciones-pagination .page-link {
            border-radius: .65rem;
            min-width: 36px;
            min-height: 36px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        /* Seguridad por si Laravel vuelve a renderizar iconos SVG tipo Tailwind */
        .notificaciones-pagination svg {
            width: 16px !important;
            height: 16px !important;
            max-width: 16px !important;
            max-height: 16px !important;
            vertical-align: middle;
        }

        @media (max-width: 575.98px) {

            .notificaciones-card .card-header,
            .notificaciones-card .card-body {
                padding-left: 1rem;
                padding-right: 1rem;
            }

            .notificacion-accion {
                width: 100%;
            }

            .notificacion-accion .btn {
                width: 100%;
            }
        }
    </style>
@endpush

@section('WorkSpace')
    <div class="notificaciones-page">
        <div class="card notificaciones-card shadow-sm border-0 rounded-4">
            <div
                class="card-header bg-white border-0 py-4 d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="min-w-0">
                    <h3 class="card-title mb-0 fw-bold text-dark text-break">
                        <i class="fas fa-bell me-2 text-primary"></i>
                        Mis notificaciones
                    </h3>
                    <div class="text-muted fs-7 mt-1">
                        Consulta tus avisos recientes y notificaciones pendientes.
                    </div>
                </div>
            </div>

            <div class="card-body pt-0">
                @forelse ($notificaciones as $notificacion)
                    <div
                        class="notificacion-item border rounded-4 p-3 mb-3 {{ $notificacion->leida_at ? 'bg-white' : 'bg-light' }}">
                        <div class="d-flex flex-column flex-md-row justify-content-between align-items-start gap-3">
                            <div class="notificacion-contenido flex-grow-1">
                                <div
                                    class="notificacion-titulo fw-bold text-dark d-flex flex-wrap align-items-center gap-2">
                                    @if (!$notificacion->leida_at)
                                        <span class="badge bg-danger">Nueva</span>
                                    @endif

                                    <span>{{ $notificacion->titulo }}</span>
                                </div>

                                <div class="notificacion-mensaje text-muted small mt-2">
                                    {{ $notificacion->mensaje }}
                                </div>

                                <div class="text-muted mt-3 d-flex flex-wrap align-items-center gap-2"
                                    style="font-size: 12px;">
                                    <span>
                                        <i class="fas fa-clock me-1"></i>
                                        {{ $notificacion->created_at?->format('d/m/Y H:i') }}
                                    </span>
                                    <span class="d-none d-sm-inline">—</span>
                                    <span>{{ $notificacion->created_at?->diffForHumans() }}</span>
                                </div>
                            </div>

                            @if ($notificacion->url)
                                <div class="notificacion-accion flex-shrink-0">
                                    <a href="{{ route('notificaciones.usuario.leer', $notificacion->id) }}"
                                        onclick="event.preventDefault(); marcarYRedirigirNotificacion({{ $notificacion->id }}, @json($notificacion->url))"
                                        class="btn btn-sm btn-light-primary fw-bold">
                                        <i class="fas fa-eye me-1"></i>
                                        Ver
                                    </a>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-10 px-4">
                        <i class="fas fa-bell-slash fa-2x mb-3"></i>
                        <div class="fw-semibold">Sin notificaciones</div>
                    </div>
                @endforelse

                @if ($notificaciones->hasPages())
                    <div class="notificaciones-pagination mt-4 d-flex justify-content-center">
                        {{ $notificaciones->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('javascript')
    <script>
        async function marcarYRedirigirNotificacion(id, url) {
            try {
                await fetch(`/notificaciones/usuario/${id}/leer`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                window.open(url, '_blank');
            } catch (error) {
                console.error(error);
                window.location.href = url;
            }
        }
    </script>
@endpush
