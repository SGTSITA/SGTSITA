@extends('layouts.app')

@section('template_title')
 Ajustes de Liquidación
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card shadow-sm">
                    <div class="card-header bg-white border-bottom">
                        <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
                            <div>
                                <h5 id="card_title" class="mb-2 text-dark font-weight-bold">
                                    <i class="fa fa-sliders-h text-primary me-2"></i> Ajustes y Complementos de Liquidación
                                </h5>
                                <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                                    <div>
                                        <div class="font-weight-bolder text-sm"><span class="small text-muted">Periodo</span></div>
                                        <input type="text" id="daterange" readonly 
                                            class="form-control form-control-sm min-w-100 bg-light" 
                                            style="border: 1px solid #dee2e6; box-shadow: none;"
                                        />
                                    </div>
                                    <div>
                                        <div class="font-weight-bolder text-sm"><span class="small text-muted">Operador</span></div>
                                        <select id="filtro_operador" class="form-control form-control-sm" style="min-width: 190px;">
                                            <option value="">-- Todos los operadores --</option>
                                            @foreach($operadores as $op)
                                                <option value="{{ $op->id }}">{{ $op->nombre }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <div class="font-weight-bolder text-sm"><span class="small text-muted">Cuenta Bancaria</span></div>
                                        <select id="filtro_banco" class="form-control form-control-sm" style="min-width: 190px;">
                                            <option value="">-- Todos los bancos --</option>
                                            @foreach($bancos as $banco)
                                                <option value="{{ $banco->id }}">{{ $banco->nombre_banco }} ({{ $banco->cuenta_bancaria }})</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <div class="font-weight-bolder text-sm"><span class="small text-muted">Estatus</span></div>
                                        <select id="filtro_estatus" class="form-control form-control-sm" style="min-width: 140px;">
                                            <option value="">-- Todos --</option>
                                            <option value="aplicado" selected>Aplicados</option>
                                            <option value="cancelado">Cancelados</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="float-right d-flex gap-2">
                                <button type="button" class="btn btn-sm btn-primary shadow-sm" onclick="abrirModalNuevoAjuste()">
                                    <i class="fa fa-plus-circle me-1"></i> Nuevo Ajuste
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body p-3">
                        <div id="gridAjustes" class="ag-theme-quartz w-100" style="height: 540px;"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('liquidaciones.ajustes.modal-crear')
@endsection

@push('custom-javascript')
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/liquidaciones/ajustes.js') }}?v={{ time() }}"></script>

<script>
$(document).ready(function() {
    initSgtDateRange('#daterange', {}, function(start, end, label) {
        getAjustes(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
    });

    const today = new Date();
    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(today.getDate() - 30); // Ultimos 30 dias por defecto

    const formatDate = (date) => date.toISOString().split('T')[0];

    document.getElementById('daterange').value = `${formatDate(sevenDaysAgo)} AL ${formatDate(today)}`;
    getAjustes(formatDate(sevenDaysAgo), formatDate(today));
});
</script>
@endpush
