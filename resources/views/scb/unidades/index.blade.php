@extends('scb.layouts')

@section('template_title', 'Unidades')
@section('page_title', 'Unidades')
@section('page_subtitle', 'Catálogo de unidades para detalles de movimientos bancarios')

@section('content')
    <div class="scb-card">
        <div class="scb-card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Unidades registradas</h5>
                <small class="text-muted">Administra las unidades que se usarán en los detalles de movimientos.</small>
            </div>

            <button type="button" class="btn scb-btn-primary" id="btnNuevaUnidad">
                <i class="fas fa-plus me-1"></i>
                Nueva unidad
            </button>
        </div>

        <div class="scb-card-body">
            <div class="table-responsive">
                <table class="table align-items-center mb-0" id="tablaUnidades">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Descripción</th>
                            <th>Placas</th>
                            <th>Estatus</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($unidades as $unidad)
                            <tr id="unidad-row-{{ $unidad->id }}">
                                <td>{{ $unidad->id }}</td>

                                <td class="fw-bold unidad-descripcion">
                                    {{ $unidad->descripcion }}
                                </td>

                                <td class="unidad-placas">
                                    {{ $unidad->placas ?? 'S/N' }}
                                </td>

                                <td class="unidad-activo">
                                    @if ($unidad->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary btnEditarUnidad"
                                        data-id="{{ $unidad->id }}" data-descripcion="{{ $unidad->descripcion }}"
                                        data-placas="{{ $unidad->placas }}" data-activo="{{ $unidad->activo ? 1 : 0 }}">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-danger btnEliminarUnidad"
                                        data-id="{{ $unidad->id }}" data-activo="{{ $unidad->activo ? 1 : 0 }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="unidades-empty-row">
                                <td colspan="5" class="text-center text-muted py-4">
                                    No hay unidades registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Crear / Editar --}}
    <div class="modal fade" id="modalUnidad" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalUnidadTitulo">Nueva unidad</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="formUnidad">
                    @csrf

                    <input type="hidden" id="unidad_id" name="unidad_id">

                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label">Descripción</label>
                            <input type="text" name="descripcion" id="descripcion" class="form-control"
                                placeholder="Ej. Camión 01, Eco 18, Unidad 03" required>
                            <div class="invalid-feedback" id="error_descripcion"></div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Placas</label>
                            <input type="text" name="placas" id="placas" class="form-control text-uppercase"
                                placeholder="Ej. 13BK8R">
                            <div class="invalid-feedback" id="error_placas"></div>
                        </div>

                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="activo" id="activo" value="1"
                                checked>
                            <label class="form-check-label" for="activo">
                                Activo
                            </label>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="submit" class="btn scb-btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script src="{{ asset('js/scb/unidades.js') }}?v={{ filemtime(public_path('js/scb/unidades.js')) }}"></script>
@endpush
