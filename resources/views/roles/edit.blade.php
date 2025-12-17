@extends('layouts.app')

@section('template_title')
    Editar Rol
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card border-0 shadow-lg rounded-4">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-primary fw-bold">
                            <i class="fas fa-user-shield me-2"></i>
                            Editar Rol
                        </h4>
                        <a
                            href="{{ route('roles.index') }}"
                            class="btn btn-sm rounded-pill"
                            style="background: {{ $configuracion->color_boton_close }}; color: #fff"
                        >
                            <i class="fas fa-arrow-left me-1"></i>
                            Regresar
                        </a>
                    </div>

                    <div class="card-body px-4 py-4">
                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3">
                                <strong>¡Ups!</strong>
                                Corrige los siguientes errores:
                                <br />
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {!! Form::model($role, ['method' => 'PATCH', 'route' => ['roles.update', $role->id], 'id' => 'formEditarRol']) !!}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Nombre del Rol</label>
                            {!! Form::text('name', null, ['class' => 'form-control rounded-3', 'placeholder' => 'Ej. Administrador']) !!}
                        </div>

                        {{-- ✅ Tabla AG Grid de permisos --}}
                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-2">Permisos Personalizados:</label>
                            <div
                                id="tablaPermisosAGGrid"
                                class="ag-theme-alpine"
                                style="height: 400px; overflow: auto"
                            ></div>
                            <input type="hidden" name="custom_permissions_json" id="custom_permissions_json" />
                        </div>

                        <div class="text-center mt-4">
                            <button
                                type="submit"
                                class="btn rounded-pill px-4"
                                style="background: {{ $configuracion->color_boton_save }}; color: #fff"
                            >
                                <i class="fas fa-save me-2"></i>
                                Guardar Cambios
                            </button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
    @include('roles.permisos_modal')
    {{-- Carga los permisos y los permisos asignados --}}
    <script>
        window.PERMISOS_EXISTENTES = @json($permission);
        window.PERMISOS_SELECCIONADOS = @json($rolePermissions);
    </script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/permisos/permisos_list.js') }}"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const form = document.getElementById('formEditarRol');

            form.addEventListener('submit', function (e) {
                const permisosInput = document.getElementById('custom_permissions_json');
                if (!permisosInput) return;

                try {
                    const permisosSeleccionados = JSON.parse(permisosInput.value || '[]');

                    if (permisosSeleccionados.length === 0) {
                        e.preventDefault();
                        Swal.fire('Advertencia', 'Debes seleccionar al menos un permiso.', 'warning');
                        return;
                    }

                    // Elimina inputs previos
                    document.querySelectorAll('input[name="permission[]"]').forEach((i) => i.remove());

                    permisosSeleccionados.forEach((p) => {
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'permission[]';
                        input.value = p.id; // o p.permiso_id según sea tu estructura
                        form.appendChild(input);
                    });
                } catch (error) {
                    console.error('Error al parsear permisos', error);
                }
            });
        });
    </script>
@endpush
