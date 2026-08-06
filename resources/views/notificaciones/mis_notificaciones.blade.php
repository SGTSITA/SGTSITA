@extends('layouts.app')

@section('template_title')
    Mis notificaciones
@endsection

@section('css')
    <style>
        .notificaciones-page {
            width: 100%;
            max-width: 1350px;
            margin: 0 auto;
        }

        .notificacion-card {
            border: 0;
            border-radius: 1rem;
            overflow: hidden;
        }

        .notificacion-item {
            overflow: hidden;
            transition: all .15s ease-in-out;
        }

        .notificacion-item:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(15, 23, 42, .06);
        }

        .notificacion-row {
            min-width: 0;
        }

        .notificacion-contenido {
            min-width: 0;
            flex: 1 1 auto;
        }

        .notificacion-titulo,
        .notificacion-mensaje {
            overflow-wrap: anywhere;
            word-break: break-word;
        }

        .notificacion-actions {
            flex: 0 0 auto;
        }

        .notificaciones-pagination {
            display: flex;
            justify-content: center;
            width: 100%;
            max-width: 100%;
            overflow-x: auto;
            padding-bottom: 6px;
        }

        .notificaciones-pagination .pagination {
            margin-bottom: 0;
            flex-wrap: wrap;
            justify-content: center;
            gap: 4px;
        }

        .notificaciones-pagination svg {
            width: 16px !important;
            height: 16px !important;
        }

        @media (max-width: 575.98px) {
            .notificacion-row {
                flex-direction: column;
                gap: 12px;
            }

            .notificacion-actions {
                width: 100%;
            }

            .notificacion-actions .btn {
                width: 100%;
            }
        }
    </style>
@endsection

@section('content')
    <div class="notificaciones-page">
        <div class="card notificacion-card shadow-sm">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="mb-0 fw-bold text-dark">
                        <i class="fas fa-bell me-2 text-primary"></i>
                        Mis notificaciones
                    </h5>
                    <small class="text-muted">
                        Consulta tus avisos recientes del sistema.
                    </small>
                </div>
            </div>

            <div class="card-body">
                @forelse ($notificaciones as $notificacion)
                    <div
                        class="notificacion-item border rounded-4 p-3 mb-3 {{ $notificacion->leida_at ? 'bg-white' : 'bg-light' }}">
                        <div class="notificacion-row d-flex justify-content-between align-items-start gap-3">
                            <div class="notificacion-contenido">
                                <div class="notificacion-titulo fw-bold text-dark">
                                    @if (!$notificacion->leida_at)
                                        <span class="badge bg-danger me-1">
                                            Nueva
                                        </span>
                                    @endif

                                    {{ $notificacion->titulo }}
                                </div>

                                <div class="notificacion-mensaje text-muted small mt-1">
                                    {{ $notificacion->mensaje }}
                                </div>

                                <div class="text-muted mt-2" style="font-size: 12px;">
                                    <i class="fas fa-clock me-1"></i>
                                    {{ $notificacion->created_at?->format('d/m/Y H:i') }}
                                    —
                                    {{ $notificacion->created_at?->diffForHumans() }}
                                </div>
                            </div>

                            @if ($notificacion->url)
                                <div class="notificacion-actions">
                                    <button type="button" class="btn btn-sm bg-gradient-info btn-ver-notificacion"
                                        data-id="{{ $notificacion->id }}" data-url="{{ $notificacion->url }}">
                                        <i class="fas fa-eye me-1"></i>
                                        Ver
                                    </button>
                                </div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted p-5">
                        <i class="fas fa-bell-slash fa-2x mb-3"></i>
                        <div class="fw-semibold">
                            Sin notificaciones
                        </div>
                        <div class="small mt-1">
                            Por ahora no tienes avisos pendientes.
                        </div>
                    </div>
                @endforelse

                @if ($notificaciones->hasPages())
                    <div class="notificaciones-pagination mt-4">
                        {{ $notificaciones->onEachSide(1)->links('pagination::bootstrap-5') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@section('datatable')
    <script>
        async function marcarYRedirigirNotificacion(id, url) {
            try {
                const response = await fetch(`/notificaciones/usuario/${id}/leer`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                        'Accept': 'application/json',
                    },
                    credentials: 'same-origin',
                });

                if (response.status === 419 || response.status === 401) {
                    const data = await response.json().catch(() => null);
                    window.location.href = data?.redirect || '{{ url('login') }}';
                    return;
                }

                window.location.href = url;
            } catch (error) {
                console.error(error);
                window.location.href = url;
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.btn-ver-notificacion').forEach((button) => {
                button.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const url = this.dataset.url;

                    if (!id || !url) {
                        return;
                    }

                    marcarYRedirigirNotificacion(id, url);
                });
            });
        });
    </script>
@endsection
