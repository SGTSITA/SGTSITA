@extends('layouts.app')

@section('template_title')
 Liquidaciones Historial
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                            <div style="display: flex; justify-content: space-between; align-items: flex-end; flex-wrap: wrap; gap: 15px;">
                                <div>
                                    <h5 id="card_title" class="mb-2">
                                        <i class="fa fa-history"></i> Historial Liquidaciones
                                    </h5>
                                    <div style="display: flex; align-items: center; gap: 15px; flex-wrap: wrap;">
                                        <div>
                                            <div class="font-weight-bolder text-sm"><span class="small">No. Contenedor</span></div>
                                            <input type="text" id="num_contenedor_search" class="form-control form-control-sm" placeholder="Buscar contenedor..." style="min-width: 180px;">
                                        </div>
                                        <div>
                                            <div class="font-weight-bolder text-sm"><span class="small">Periodo</span></div>
                                            <input type="text" id="daterange" readonly 
                                            class="form-control form-control-sm min-w-100" 
                                            style="border: none; box-shadow: none;"
                                            />
                                        </div>
                                    </div>
                                </div>

                                <div class="float-right">
                                    <button type="button" class="btn btn-sm bg-gradient-danger" id="comprobantePdf" onclick="getComprobantePago()">
                                        <i class="fa fa-fw fa-file-pdf"></i>  Ver Comprobante
                                    </button>
                                </div>
                            </div>
                    </div>

                    <div class="card-body">
                    <div id="gridHistorial" class="col-12 ag-theme-quartz" style="height: 500px"></div>  
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/liquidaciones/historial.js') }}?v={{ filemtime(public_path('js/sgt/liquidaciones/historial.js')) }}"></script>

<script>
$(document).ready(function() {
    initSgtDateRange('#daterange', {}, function(start, end, label) {
        getHistorial(start.format('YYYY-MM-DD'), end.format('YYYY-MM-DD'));
    });

    const today = new Date();
    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(today.getDate() - 7);

    const formatDate = (date) => date.toISOString().split('T')[0];

    document.getElementById('daterange').value=`${formatDate(sevenDaysAgo)} AL ${formatDate(today)}`

    getHistorial(formatDate(sevenDaysAgo),formatDate(today));
    });
</script>
@endpush