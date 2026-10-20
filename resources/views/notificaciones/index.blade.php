@extends('layouts.app')

@section('template_title')
    Notificaciones
@endsection

@section('content')
    <div class="container-fluid">

        {{-- Encabezado principal --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span id="card_title">
                            <i class="fas fa-bell me-1"></i>
                            Panel de Notificaciones
                        </span>

                        <div class="float-right d-flex gap-2">

                            @can('notificaciones-create')
                                <button type="button" class="btn bg-gradient-info btn-xs mb-2" data-bs-toggle="modal"
                                    data-bs-target="#modalCrearTipoNotificacion">
                                    <i class="fas fa-plus"></i>
                                    Nuevo tipo
                                </button>

                                <button type="button" class="btn bg-gradient-primary btn-xs mb-2" data-bs-toggle="modal"
                                    data-bs-target="#modalCrearReglaNotificacion">
                                    <i class="fas fa-sliders-h"></i>
                                    Nueva regla
                                </button>
                            @endcan

                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row g-3">

                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 bg-light h-100">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-tags fa-2x text-info"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Tipos registrados</div>
                                            <h4 class="mb-0">{{ $tipos->count() ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 bg-light h-100">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-list-check fa-2x text-primary"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Reglas configuradas</div>
                                            <h4 class="mb-0">{{ $reglas->count() ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 bg-light h-100">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-toggle-on fa-2x text-success"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Reglas activas</div>
                                            <h4 class="mb-0">
                                                {{ $reglas->where('activo', true)->count() ?? 0 }}
                                            </h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="border rounded-4 p-3 bg-light h-100">
                                    <div class="d-flex align-items-center">
                                        <div class="me-3">
                                            <i class="fas fa-users fa-2x text-warning"></i>
                                        </div>
                                        <div>
                                            <div class="text-muted small">Usuarios disponibles</div>
                                            <h4 class="mb-0">{{ $usuariosSelect->count() ?? 0 }}</h4>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="alert alert-info mt-4 mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Desde este panel puedes crear tipos de notificación, configurar reglas por empresa
                            y asignar usuarios que recibirán las alertas.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección: Tipos --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-tags me-1"></i>
                            Tipos de notificación
                        </span>

                        @can('notificaciones-create')
                            <button type="button" class="btn bg-gradient-info btn-xs" data-bs-toggle="modal"
                                data-bs-target="#modalCrearTipoNotificacion">
                                <i class="fas fa-plus"></i>
                                Crear tipo
                            </button>
                        @endcan
                    </div>

                    <div class="card-body">
                        <div id="tiposNotificacionGrid" class="ag-theme-alpine" style="height: 350px; width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección: Reglas --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-sliders-h me-1"></i>
                            Reglas de notificación
                        </span>

                        @can('notificaciones-create')
                            <button type="button" class="btn bg-gradient-primary btn-xs" data-bs-toggle="modal"
                                data-bs-target="#modalCrearReglaNotificacion">
                                <i class="fas fa-plus"></i>
                                Crear regla
                            </button>
                        @endcan
                    </div>

                    <div class="card-body">
                        <div id="reglasNotificacionGrid" class="ag-theme-alpine" style="height: 430px; width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección: Usuarios por regla --}}
        <div class="row">
            <div class="col-sm-12">
                <div class="card mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>
                            <i class="fas fa-user-check me-1"></i>
                            Usuarios asignados a reglas
                        </span>

                        @can('notificaciones-create')
                            <button type="button" class="btn bg-gradient-success btn-xs" data-bs-toggle="modal"
                                data-bs-target="#modalAsignarUsuarioRegla">
                                <i class="fas fa-user-plus"></i>
                                Asignar usuario
                            </button>
                        @endcan
                    </div>

                    <div class="card-body">
                        <div class="alert alert-light border mb-3">
                            <i class="fas fa-users-cog me-1"></i>
                            Aquí puedes consultar qué usuarios están asignados a cada regla.
                            Para agregar o quitar usuarios usa las acciones de cada registro.
                        </div>

                        <div id="usuariosReglasGrid" class="ag-theme-alpine" style="height: 430px; width: 100%"></div>
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- Modales --}}
    @include('notificaciones.modal_create_tipo')
    @include('notificaciones.modal_create_regla')
    @include('notificaciones.modal_asignar_usuario')

    @foreach ($tipos as $tipo)
        @include('notificaciones.modal_edit_tipo', ['tipo' => $tipo])
    @endforeach

    @foreach ($reglas as $regla)
        @include('notificaciones.modal_edit_regla', ['regla' => $regla])
    @endforeach

    {{-- Formularios ocultos --}}
    <form id="form-eliminar-tipo" method="POST" style="display: none">
        @csrf
        @method('DELETE')
    </form>

    <form id="form-eliminar-regla" method="POST" style="display: none">
        @csrf
        @method('DELETE')
    </form>

    <form id="form-quitar-usuario-regla" method="POST" style="display: none">
        @csrf
        @method('DELETE')
    </form>

    <form id="form-toggle-tipo" method="POST" style="display: none">
        @csrf
        @method('PUT')
    </form>

    <form id="form-toggle-regla" method="POST" style="display: none">
        @csrf
        @method('PUT')
    </form>
@endsection

@section('datatable')
    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- AG Grid --}}
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    {{-- Datos para JS --}}
    <script>
        const tiposNotificacionData = @json($tipos);
        const reglasNotificacionData = @json($reglas);
        const tiposSelectData = @json($tiposSelect);
        const empresasSelectData = @json($empresasSelect);
        const usuariosSelectData = @json($usuariosSelect);

        const permisosNotificaciones = {
            puedeCrear: @json(auth()->user()->can('notificaciones-create')),
            puedeEditar: @json(auth()->user()->can('notificaciones-edit')),
            puedeEliminar: @json(auth()->user()->can('notificaciones-delete')),
        };
    </script>

    <script src="{{ asset('js/sgt/notificaciones/notificaciones_index.js') }}"></script>

    @if (Session::has('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '{{ Session::get('success') }}',
                confirmButtonColor: '#3085d6',
            });
        </script>
    @endif

    @if (Session::has('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ Session::get('error') }}',
                confirmButtonColor: '#d33',
            });
        </script>
    @endif

    @if ($errors->any())
        <script>
            Swal.fire({
                icon: 'warning',
                title: 'Revisa la información',
                html: `{!! implode('<br>', $errors->all()) !!}`,
                confirmButtonColor: '#3085d6',
            });
        </script>
    @endif
@endsection
