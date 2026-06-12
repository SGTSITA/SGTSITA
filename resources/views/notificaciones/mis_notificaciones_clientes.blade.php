@extends('layouts.usuario_externo')

@section('template_title')
    Mis notificaciones
@endsection

@section('WorkSpace')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">

                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span id="card_title">
                            <i class="fas fa-bell me-1"></i>
                            Mis notificaciones
                        </span>
                    </div>

                    <div class="card-body">

                        @forelse ($notificaciones as $notificacion)
                            <div class="border rounded-4 p-3 mb-3 {{ $notificacion->leida_at ? 'bg-white' : 'bg-light' }}">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="fw-bold text-dark">
                                            @if (!$notificacion->leida_at)
                                                <span class="badge bg-danger me-1">Nueva</span>
                                            @endif

                                            {{ $notificacion->titulo }}
                                        </div>

                                        <div class="text-muted small mt-1">
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
                                        <a href="{{ route('notificaciones.usuario.leer', $notificacion->id) }}"
                                            onclick="event.preventDefault(); marcarYRedirigirNotificacion({{ $notificacion->id }}, '{{ $notificacion->url }}')"
                                            class="btn bg-gradient-info btn-sm">
                                            <i class="fas fa-eye"></i>
                                            Ver
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @empty
                            <div class="text-center text-muted p-5">
                                <i class="fas fa-bell-slash fa-2x mb-3"></i>
                                <div>Sin notificaciones</div>
                            </div>
                        @endforelse

                        <div class="mt-3">
                            {{ $notificaciones->links() }}
                        </div>

                    </div>
                </div>

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
