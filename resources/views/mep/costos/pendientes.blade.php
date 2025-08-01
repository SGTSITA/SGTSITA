@extends('layouts.app')

@section('template_title')
    Pendientes por Verificar
@endsection

<style>
    .highlight-cell {
        background-color: #fff4e5 !important;
        border-bottom: 3px solid #ffa500 !important;
        /* naranja */
        font-weight: bold;
    }
</style>
@section('content')
    <div class="container-fluid py-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Pendientes por Verificar</h4>
                <a href="{{ route('index.costos_mep') }}" class="btn btn-secondary">Volver</a>
            </div>

            <div id="tablaPendientesMEP" class="ag-theme-alpine" style="height: 600px; width: 100%;"></div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/mep/costosviajes/costosverificar_list.js') }}"></script>
@endpush
