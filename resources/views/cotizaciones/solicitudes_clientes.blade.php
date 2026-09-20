@extends('layouts.app')

@section('template_title')
    Solicitudes entrantes
@endsection

@section('content')
    <div class="card">
        <div class="card-header">
            <h5 class="card-title">Solicitudes por asignar</h5>
            <div class="card-toolbar"></div>
        </div>
        <div class="card-body">
            <div class="row">
                <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 500px"></div>
            </div>
        </div>
        <div class="card-footer">
            <div class="row align-items-end">

                <!-- Empresa -->
                <div class="col-md-4">
                    <div class="form-group">
                        <label class="form-control-label">Empresa</label>
                        <select name="cmbEmpresa" id="cmbEmpresa" class="form-control">
                            <option value="">Seleccione empresa</option>
                            @foreach ($empresas as $empresa)
                                <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <!-- Proveedor -->
                <div class="col-md-5">
                    <div class="form-group">
                        <label class="form-control-label">Proveedor (opcional)</label>
                        <select name="id_transportista" id="id_transportista" class="form-control">
                            <option value="">Ninguno</option>
                        </select>
                    </div>
                </div>

                <!-- Botón -->
                <div class="col-md-2">
                    <button class="btn btn-sm bg-gradient-success w-100" onclick="asignarContenedores()">
                        <i class="fas fa-check"></i>
                        Asignar
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <script
        src="{{ asset('js/sgt/cotizaciones/cotizaciones-para-asignar.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones-para-asignar.js')) }}">
    </script>
    <script>
        $(document).ready(() => {
            getContenedoresPorAsignar();
        });
    </script>
@endpush
