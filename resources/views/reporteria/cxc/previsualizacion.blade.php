@extends('layouts.app')

@section('template_title')
    Previsualización de Cotizaciones
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5>Previsualización de Reporte</h5>
                </div>
                <div class="card-body">
                    <div class="pdf-container">
                        <!-- Embed PDF in iframe -->
                        <iframe src="data:application/pdf;base64,{{ base64_encode($pdf) }}" width="100%" height="600px"></iframe>
                    </div>

                    <!-- Botón para exportar -->
                    <div class="mt-3">
                        <form action="{{ route('cotizaciones.export') }}" method="POST">
                            @csrf
                            <input type="hidden" name="selected_ids" value="{{ implode(',', $cotizacionIds) }}">
                            <button type="submit" class="btn btn-success">Exportar a Excel</button>
                            <button type="submit" class="btn btn-primary">Exportar a PDF</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection