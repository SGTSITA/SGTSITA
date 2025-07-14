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

                            @php
                                $agrupados = $permission->groupBy('modulo');

                                $traducciones = [
                                    'list' => 'Ver lista',
                                    'create' => 'Crear',
                                    'edit' => 'Editar',
                                    'delete' => 'Eliminar',
                                    'estatus' => 'Cambiar estatus',
                                    'cordeenadas' => 'Coordenadas',
                                    'cambio-tipo' => 'Cambiar tipo',
                                    'pdf' => 'Descargar PDF',
                                    'cambio-empresa' => 'Cambiar empresa',
                                    'cotizacion' => 'Cotización',
                                    'finalizar' => 'Finalizar',
                                    'entrar' => 'Entrar',
                                    'entrar-cotizacion' => 'Entrar cotización',
                                    'permisos-users' => 'Gestionar permisos y usuarios',
                                    'generales' => 'Gastos generales',
                                    'crear' => 'Crear',
                                    'catalogo' => 'Catálogo',
                                    'reportes' => 'Reportes',
                                    'viajes' => 'Viajes',
                                    'cxp' => 'Cuentas por pagar',
                                    'cxc' => 'Cuentas por cobrar',
                                    'utilidad' => 'Reporte de utilidad',
                                    'documentos' => 'Documentos',
                                    'liquidados-cxc' => 'CXC Liquidados',
                                    'liquidados-cxp' => 'CXP Liquidados',
                                    'pagos-p' => 'Pagos Primarios',
                                    'pagos-s' => 'Pagos Secundarios',
                                    'cuentas' => 'Cuentas',
                                    'cuentas-create' => 'Crear cuenta',
                                    'liquidaciones' => 'Liquidaciones',
                                    'coordenadasv' => 'Coordenadas',
                                ];
                            @endphp

                            @foreach ($agrupados as $modulo => $permisos)
                                <div class="mb-4 border rounded p-3 bg-light-subtle">
                                    <h5 class="text-primary fw-bold mb-1">
                                        <i class="fas fa-folder me-2"></i>{{ ucfirst($modulo) }}
                                    </h5>
                                    <p class="text-muted mb-3" style="font-size: 0.9rem;">
                                        {{ $permisos->first()->descripcion ?? 'Permisos del módulo ' . ucfirst($modulo) }}
                                    </p>

                                    <div class="row">
                                        @foreach ($permisos as $permiso)
                                            @php
                                                $accion = \Illuminate\Support\Str::after($permiso->name, '-');
                                                $texto =
                                                    $traducciones[$accion] ?? ucfirst(str_replace('-', ' ', $accion));
                                            @endphp
                                            <div class="col-md-4 mb-2">
                                                <div class="form-check form-switch">
                                                    {!! Form::checkbox('permission[]', $permiso->id, in_array($permiso->id, $rolePermissions), [
                                                        'class' => 'form-check-input',
                                                        'id' => 'perm_' . $permiso->id,
                                                    ]) !!}
                                                    <label class="form-check-label" for="perm_{{ $permiso->id }}">
                                                        {{ $texto }}
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach

                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn rounded-pill px-4"
                                style="background: {{ $configuracion->color_boton_save }}; color: #fff;">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>

                        {!! Form::close() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
