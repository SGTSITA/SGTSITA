@extends('layouts.app')

@section('template_title')
    Costos de Viaje MEP
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Costos de Viaje MEP</h4>
                <div class="d-flex align-items-center gap-2">
                    <label class="mb-0 fw-semibold text-sm">Periodo:</label>
                    <input
                        type="text"
                        id="daterange"
                        readonly
                        class="form-control form-control-sm"
                        style="width: auto; min-width: 200px; box-shadow: none"
                    />
                    <div class="text-muted small mt-2">
                        <i class="fas fa-info-circle me-1 text-primary"></i>
                        Ingresa el periodo de los viajes a registrar.
                    </div>
                </div>
            </div>
            <div>
                <button id="guardarCambios" class="btn btn-success mt-3">
                    <i class="fas fa-save me-2"></i>
                    Guardar Cambios
                </button>
            </div>
            <div id="tablaCostosMEP" class="mb-4"></div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/handsontable/dist/handsontable.full.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="{{ asset('js/mep/costosviajes/costosviajes_list.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
@endpush
