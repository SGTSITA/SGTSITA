@extends('scb.layouts')

@section('template_title', 'Bancos')
@section('page_title', 'Bancos')
@section('page_subtitle', 'Catálogo de bancos del módulo bancario')

@section('content')
    <div class="scb-card">
        <div class="scb-card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Bancos registrados</h5>
                <small class="text-muted">Administra los bancos disponibles para las cuentas.</small>
            </div>

            <button type="button" class="btn scb-btn-primary" id="btnNuevoBanco">
                <i class="fas fa-plus me-1"></i>
                Nuevo banco
            </button>
        </div>

        <div class="scb-card-body">
            <div class="table-responsive">
                <table class="table align-items-center mb-0" id="tablaBancos">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Clave</th>
                            <th>Estatus</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($bancos as $banco)
                            <tr id="banco-row-{{ $banco->id }}">
                                <td>{{ $banco->id }}</td>
                                <td class="fw-bold banco-nombre">{{ $banco->nombre }}</td>
                                <td class="banco-clave">{{ $banco->clave ?? 'S/N' }}</td>
                                <td class="banco-activo">
                                    @if ($banco->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary btnEditarBanco"
                                        data-id="{{ $banco->id }}" data-nombre="{{ $banco->nombre }}"
                                        data-clave="{{ $banco->clave }}" data-activo="{{ $banco->activo ? 1 : 0 }}">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-danger btnEliminarBanco"
                                        data-id="{{ $banco->id }}" data-activo="{{ $banco->activo ? 1 : 0 }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="bancos-empty-row">
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay bancos registrados.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @include('scb.bancos.modal')
@endsection

@push('custom-javascript')
    <script src="{{ asset('js/scb/bancos.js') }}?v={{ filemtime(public_path('js/scb/bancos.js')) }}"></script>
@endpush
