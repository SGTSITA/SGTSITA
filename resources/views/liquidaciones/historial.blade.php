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
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h5 id="card_title">
                                <i class="fa fa-history"></i>
                                Historial Liquidaciones
                                <p class="text-sm mb-0">
                                    <span class="font-weight-bold">Hisotiral de Pagos a operadores</span> 
                                </p>
                            </h5>

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
    $(document).ready(()=>{
        getHistorial();
    });
</script>
@endpush