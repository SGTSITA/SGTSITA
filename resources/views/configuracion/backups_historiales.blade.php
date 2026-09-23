@extends('layouts.app')

@section('template_title')
    Respaldos de Historiales
@endsection

@section('content')
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col-sm-12">

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show text-white" role="alert">
                        <span class="alert-icon"><i class="fas fa-check-circle"></i></span>
                        <span class="alert-text"><strong>Éxito:</strong> {{ session('success') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show text-white" role="alert">
                        <span class="alert-icon"><i class="fas fa-exclamation-triangle"></i></span>
                        <span class="alert-text"><strong>Error:</strong> {{ session('error') }}</span>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif

                <div class="card shadow">
                    <div class="card-header d-flex justify-content-between align-items-center bg-white border-0">
                        <div>
                            <h3 class="mb-0 text-dark">Respaldos de Historiales y Activity Log</h3>
                            <p class="text-muted small mb-0">Listado de archivos ZIP con respaldos de Coordenadas
                                (frecuencia 30 días) y Activity Log (frecuencia 60 días).</p>
                        </div>
                        <div class="d-flex gap-2">
                            <form id="formLimpiarHistoriales" action="{{ route('backups.historiales.limpiar') }}"
                                method="POST" style="display:inline;">
                                @csrf
                                <button type="button" class="btn btn-sm bg-gradient-warning text-white"
                                    onclick="confirmarLimpiezaYRespaldo();">
                                    <i class="fas fa-sync-alt me-1"></i> Depurar y Respaldar Ahora
                                </button>
                            </form>
                            <a href="{{ route('index.configuracion', auth()->user()->Empresa->Configuracion->id) }}"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-arrow-left me-1"></i> Regresar a Configuración
                            </a>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-items-center table-flush">
                            <thead class="thead-light">
                                <tr>
                                    <th scope="col" class="sort">Nombre del Archivo</th>
                                    <th scope="col" class="sort">Tipo</th>
                                    <th scope="col" class="sort">Fecha de Creación</th>
                                    <th scope="col" class="sort">Tamaño</th>
                                    <th scope="col" class="text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="list">
                                @if (count($backups) > 0)
                                    @foreach ($backups as $backup)
                                        <tr>
                                            <th scope="row">
                                                <div class="media align-items-center">
                                                    <span class="avatar avatar-sm rounded-circle bg-light me-3">
                                                        <i class="fas fa-file-archive text-warning"
                                                            style="font-size: 20px;"></i>
                                                    </span>
                                                    <div class="media-body">
                                                        <span
                                                            class="name mb-0 text-sm font-weight-bold text-dark">{{ $backup['name'] }}</span>
                                                    </div>
                                                </div>
                                            </th>
                                            <td>
                                                @if (str_contains($backup['name'], 'coordenadas'))
                                                    <span class="badge bg-gradient-success">Coordenadas Historial</span>
                                                @elseif (str_contains($backup['name'], 'activity_log'))
                                                    <span class="badge bg-gradient-info">Logs de Actividad</span>
                                                @elseif (str_contains($backup['name'], 'database_backup'))
                                                    <span class="badge bg-gradient-primary">Base de Datos</span>
                                                @else
                                                    <span class="badge bg-gradient-secondary">Otro</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $backup['date'] }}</span>
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $backup['size'] }}</span>
                                            </td>
                                            <td class="text-right">
                                                <a href="{{ route('backups.historiales.descargar', $backup['name']) }}"
                                                    class="btn btn-sm btn-primary">
                                                    <i class="fas fa-download me-1"></i> Descargar
                                                </a>
                                                <form id="form-delete-{{ $loop->index }}"
                                                    action="{{ route('backups.historiales.eliminar', $backup['name']) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-danger ms-1"
                                                        onclick="confirmarEliminacion('form-delete-{{ $loop->index }}', '{{ $backup['name'] }}');">
                                                        <i class="fas fa-trash me-1"></i> Eliminar
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="5" class="text-center py-5">
                                            <div class="text-muted">
                                                <i class="fas fa-folder-open mb-3" style="font-size: 48px;"></i>
                                                <p class="mb-0">No se encontraron archivos de respaldos generados aún.</p>
                                                <p class="small text-muted">Haz clic en "Depurar y Respaldar Ahora" para
                                                    procesar historiales según su periodicidad (30 días Coordenadas / 60
                                                    días Activity Log).</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script>
        function confirmarLimpiezaYRespaldo() {
            Swal.fire({
                title: '¿Iniciar depuración y respaldos?',
                text: 'Se respaldarán y eliminarán coordenadas (mayores a 30 días) y logs de actividad (mayores a 60 días) de la base de datos si ha transcurrido el tiempo requerido desde el último respaldo.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ffc107',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, depurar y respaldar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Procesando...',
                        text: 'Depurando base de datos y generando respaldos ZIP. Por favor, no cierre esta ventana.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById('formLimpiarHistoriales').submit();
                }
            });
        }

        function confirmarEliminacion(formId, fileName) {
            Swal.fire({
                title: '¿Eliminar respaldo?',
                text: `¿Estás seguro de que deseas eliminar el archivo "${fileName}"? Esta acción no se puede deshacer.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        text: 'Por favor, espere.',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });
                    document.getElementById(formId).submit();
                }
            });
        }
    </script>
@endpush
