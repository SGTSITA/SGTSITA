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
                            <i class="fas fa-user-shield me-2"></i>Editar Rol
                        </h4>
                        <a href="{{ route('roles.index') }}" class="btn btn-sm rounded-pill"
                            style="background: {{ $configuracion->color_boton_close }}; color: #fff;">
                            <i class="fas fa-arrow-left me-1"></i> Regresar
                        </a>
                    </div>

                    <div class="card-body px-4 py-4">
                        @if ($errors->any())
                            <div class="alert alert-danger rounded-3">
                                <strong>¡Ups!</strong> Corrige los siguientes errores:<br>
                                <ul class="mb-0 mt-2">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        {!! Form::model($role, ['method' => 'PATCH', 'route' => ['roles.update', $role->id]]) !!}
                        <div class="mb-4">
                            <label class="form-label fw-semibold">Nombre del Rol</label>
                            {!! Form::text('name', null, ['class' => 'form-control rounded-3', 'placeholder' => 'Ej. Administrador']) !!}
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-2">Permisos Disponibles</label>
                            <div class="row">
                                @foreach ($permission as $value)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check form-switch">
                                            {!! Form::checkbox('permission[]', $value->id, in_array($value->id, $rolePermissions), [
                                                'class' => 'form-check-input',
                                                'id' => 'perm_' . $value->id,
                                            ]) !!}
                                            <label class="form-check-label" for="perm_{{ $value->id }}">
                                                {{ ucfirst($value->name) }}
                                            </label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn rounded-pill px-4"
                                style="background: {{ $configuracion->color_boton_save }}; color: #fff;">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
