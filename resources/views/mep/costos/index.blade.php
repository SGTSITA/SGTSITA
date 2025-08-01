@extends('layouts.app')

@section('template_title')
    Costos de Viaje MEP
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Costos de Viaje MEP</h4>
                <button class="btn btn-primary" onclick="mostrarPendientes()">
                    Ver Pendientes por Verificar
                </button>
            </div>

            <div id="tablaCostosMEP" class="ag-theme-alpine" style="height: 600px; width: 100%;"></div>
        </div>
    </div>

    <!-- Modal de Edición -->
    <div class="modal fade" id="modalEditarCostos" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <form id="formEditarCostos">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Costos del Viaje</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body row g-3">
                        <input type="hidden" name="id_asignacion" id="id_asignacion">

                        @foreach (['precio_viaje', 'burreo', 'maniobra', 'estadia', 'otro', 'iva', 'retencion', 'base1', 'base2', 'sobrepeso', 'precio_sobrepeso'] as $field)
                            <div class="col-md-6">
                                <label class="form-label text-capitalize">{{ str_replace('_', ' ', $field) }}</label>
                                <input type="number" step="0.0001" name="{{ $field }}" id="{{ $field }}"
                                    class="form-control">
                            </div>
                        @endforeach
                        <div class="col-md-6">
                            <label class="form-label">Total</label>
                            <input type="number" step="0.0001" name="total" id="total" class="form-control">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Contenedor</label>
                            <input type="text" id="contenedor" class="form-control" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Destino</label>
                            <input type="text" id="destino" class="form-control" disabled>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Estatus</label>
                            <input type="text" id="estatus" class="form-control" disabled>
                        </div>


                        <div class="col-12">
                            <label class="form-label">Motivo del cambio</label>
                            <textarea name="motivo_cambio" id="motivo_cambio" class="form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-success">Enviar para Revisión</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/mep/costosviajes/costosviajes_list.js') }}"></script>

    <script>
        function mostrarPendientes() {
            window.location.href = '/costos/mep/pendientes/vista';
        }
    </script>
@endpush
