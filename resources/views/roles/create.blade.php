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
                            <button type="button" class="btn btn-sm btn-primary rounded-pill" data-bs-toggle="modal"
                                data-bs-target="#exampleModal">
                                <i class="fas fa-info-circle me-1"></i> Roles
                            </button>
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

                        {!! Form::open(['route' => 'roles.store', 'method' => 'POST']) !!}
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label fw-semibold">Nombre del Rol:</label>
                                    {!! Form::text('name', null, ['class' => 'form-control rounded-3', 'placeholder' => 'Ej. Supervisor']) !!}
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-semibold mb-2">Permisos:</label>
                            <div class="row">
                                @foreach ($permission as $value)
                                    <div class="col-md-4 mb-2">
                                        <div class="form-check form-switch">
                                            {!! Form::checkbox('permission[]', $value->id, false, [
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
                                <i class="fas fa-save me-2"></i> Guardar Rol
                            </button>
                        </div>
                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('roles.modal')
@endsection
