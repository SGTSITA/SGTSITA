@extends('layouts.app')

@section('template_title')
    Crear Rol
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card shadow rounded-4">
                    <div class="card-header bg-white d-flex justify-content-between align-items-center border-bottom">
                        <h4 class="mb-0 text-primary fw-bold">
                            <i class="fas fa-user-plus me-2"></i>Crear Nuevo Rol
                        </h4>
                        <div class="d-flex gap-2">
                            <a href="{{ route('roles.index') }}" class="btn btn-sm rounded-pill"
                                style="background: {{ $configuracion->color_boton_close }}; color: #fff;">
                                <i class="fas fa-arrow-left me-1"></i> Volver
                            </a>
                        </div>
                    </div>

                    <div class="card-body px-4 py-4">
                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3">
                                <strong>¡Ups!</strong> Hay problemas con tus entradas:<br><br>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {!! Form::open(['route' => 'roles.store', 'method' => 'POST', 'id' => 'formCrearRol']) !!}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-semibold">Nombre del Rol:</label>
                                    {!! Form::text('name', null, ['class' => 'form-control rounded-3', 'placeholder' => 'Ej. Supervisor']) !!}
                                </div>
                            </div>
                        </div>

                        {{-- ✅ Tabla AG Grid de permisos personalizados --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-2">Permisos Personalizados:</label>
                            <div id="tablaPermisosAGGrid" class="ag-theme-alpine" style="height: 400px; overflow: auto;">
                            </div>
                            <input type="hidden" name="custom_permissions_json" id="custom_permissions_json" />
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn rounded-pill px-4"
                                style="background: {{ $configuracion->color_boton_save }}; color: #fff;">
                                <i class="fas fa-save me-2"></i> Guardar Rol
                            </button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Carga los permisos desde backend --}}
    <script>
        window.PERMISOS_EXISTENTES = @json($permission);
    </script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/permisos/permisos_list.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formCrearRol');

            form.addEventListener('submit', function(e) {
                const permisosInput = document.getElementById('custom_permissions_json');
                if (!permisosInput) return;

                try {
                    const permisosSeleccionados = JSON.parse(permisosInput.value || '[]');

                    // Validación: no permitir guardar sin permisos
                    if (permisosSeleccionados.length === 0) {
                        e.preventDefault();
                        Swal.fire("Advertencia", "Debes seleccionar al menos un permiso.", "warning");
                        return;
                    }

                    // Elimina inputs anteriores
                    document.querySelectorAll('input[name="permission[]"]').forEach(i => i.remove());

                    // Crea campos ocultos tipo permission[]
                    permisosSeleccionados.forEach(p => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'permission[]';
                        input.value = p.id; // o p.permiso_id según estructura
                        form.appendChild(input);
                    });

                } catch (error) {
                    console.error("Error al parsear permisos", error);
                }
            });
        });
    </script>
@endpush
