@extends('layouts.app')

@section('template_title')
    App Móvil SGT Logistics
@endsection

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="card-header pb-0">
                <div class="d-flex align-items-center justify-content-between">
                    <h5 class="mb-0">Administración de App Móvil SGT Logistics</h5>
                    <a href="{{ route('app-movil-admin.create') }}" class="btn btn-sm text-white" style="background: {{ $configuracion->color_boton_add ?? '#5e72e4' }}">
                        <i class="fa fa-plus"></i> Vincular Nueva Bitácora
                    </a>
                </div>
            </div>
            
            <div class="card-body">
                <form action="{{ route('app-movil-admin.index') }}" method="GET" class="mb-4">
                    <div class="input-group">
                        <input type="text" class="form-control" name="buscar" placeholder="Buscar por operador o contenedor..." value="{{ request('buscar') }}">
                        <button class="btn text-white mb-0" style="background: {{ $configuracion->color_boton_save ?? '#2dce89' }}" type="submit">
                            <i class="fa fa-search"></i> Buscar
                        </button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table align-items-center mb-0">
                        <thead>
                            <tr>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Asignación / Operador</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Contenedor / Cotización</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Diésel (Litros / Costo)</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Urea (Litros / Costo)</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Fecha Inicio</th>
                                <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Fecha Fin</th>
                                <th class="text-secondary opacity-7">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bitacoras as $bitacora)
                                <tr>
                                    <td>
                                        <div class="d-flex px-2 py-1">
                                            <div class="d-flex flex-column justify-content-center">
                                                <h6 class="mb-0 text-sm">Asignación #{{ $bitacora->id_asignacion }}</h6>
                                                <p class="text-xs text-secondary mb-0">
                                                    {{ $bitacora->Asignacion?->Operador?->nombre ?? 'N/A' }}
                                                </p>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <h6 class="mb-0 text-sm">{{ $bitacora->Asignacion?->Contenedor?->num_contenedor ?? 'N/A' }}</h6>
                                        <p class="text-xs text-secondary mb-0">
                                            Cotización: #{{ $bitacora->Asignacion?->Contenedor?->id_cotizacion ?? 'N/A' }}
                                        </p>
                                    </td>
                                    <td>
                                        <span class="text-sm font-weight-bold">
                                            {{ $bitacora->litros ?? '0' }} L
                                        </span>
                                        <p class="text-xs text-secondary mb-0">
                                            ${{ number_format($bitacora->costo ?? 0, 2) }}
                                        </p>
                                    </td>
                                    <td>
                                        <span class="text-sm font-weight-bold">
                                            {{ $bitacora->litros_urea ?? '0' }} L
                                        </span>
                                        <p class="text-xs text-secondary mb-0">
                                            ${{ number_format($bitacora->costo_urea ?? 0, 2) }}
                                        </p>
                                    </td>
                                    <td>
                                        <span class="text-xs font-weight-bold">
                                            {{ $bitacora->viaje_iniciado ?? 'No Iniciado' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="text-xs font-weight-bold">
                                            {{ $bitacora->viaje_finalizado ?? 'En Progreso' }}
                                        </span>
                                    </td>
                                    <td class="align-middle">
                                        <a href="{{ route('app-movil-admin.edit', $bitacora->id) }}" class="btn btn-xs btn-info text-white">
                                            <i class="fa fa-edit"></i> Editar / Ver
                                        </a>
                                        <form action="{{ route('app-movil-admin.destroy', $bitacora->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Está seguro de eliminar este registro de Bitácora?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-xs btn-danger text-white">
                                                <i class="fa fa-trash"></i> Eliminar
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4">No se encontraron registros de bitácoras de la App Móvil.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    {{ $bitacoras->appends(request()->all())->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
