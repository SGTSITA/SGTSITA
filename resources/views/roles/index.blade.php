@extends('layouts.app')

@section('template_title')
    Roles
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="row">
            <div class="col">
                <div class="card shadow rounded-4">
                    <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
                        <h4 class="mb-0 text-primary fw-bold">
                            <i class="fas fa-users-cog me-2"></i>
                            Gestión de Roles
                        </h4>
                        <div class="d-flex align-items-center gap-3">
                            @can('role-create')
                                <a
                                    href="{{ route('roles.create') }}"
                                    class="btn btn-sm rounded-pill"
                                    style="background: {{ $configuracion->color_boton_add }}; color: #fff"
                                >
                                    <i class="fas fa-plus-circle me-1"></i>
                                    Crear Rol
                                </a>
                            @endcan
                        </div>
                    </div>

                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table
                                class="table table-hover align-middle text-center table-bordered table-striped table_id"
                                id="datatable-basic"
                            >
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 5%">#</th>
                                        <th>Nombre del Rol</th>
                                        <th style="width: 150px">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $key => $role)
                                        <tr>
                                            <td>{{ ++$i }}</td>
                                            <td class="text-start">{{ $role->name }}</td>
                                            <td class="text-center">
                                                <div class="d-flex justify-content-center gap-2">
                                                    <a
                                                        href="{{ route('roles.edit', $role->id) }}"
                                                        class="btn btn-sm btn-outline-primary rounded-2 px-3"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Editar"
                                                    >
                                                        <i class="fas fa-edit me-1"></i>
                                                        Editar
                                                    </a>

                                                    {!! Form::open(['method' => 'DELETE', 'route' => ['permisos.destroy', $role->id], 'style' => 'display:inline']) !!}
                                                    <button
                                                        type="submit"
                                                        class="btn btn-sm btn-outline-danger rounded-2 px-3"
                                                        data-bs-toggle="tooltip"
                                                        data-bs-placement="top"
                                                        title="Eliminar"
                                                        onclick="return confirm('¿Estás seguro de eliminar este rol?');"
                                                    >
                                                        <i class="fas fa-trash-alt me-1"></i>
                                                        Eliminar
                                                    </button>
                                                    {!! Form::close() !!}
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- card-body -->
                </div>
            </div>
        </div>
    </div>
@endsection
