@extends('layouts.app')

@section('template_title')
 Liquidacion - {{$operador->nombre}}
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card card-body" id="profile">
                <div class="row justify-content-center align-items-center">
                <div class="col-sm-auto col-4">
                    <div class="avatar avatar-xl position-relative">
                    <img src="/img/icon-user.jpg" alt="bruce" class="w-100 border-radius-lg shadow-sm">
                    </div>
                </div>
                <div class="col-sm-auto col-8 my-auto">
                    <div class="h-100">
                    <h5 class="mb-1 font-weight-bolder">
                        {{$operador->nombre}}
                    </h5>
                    <p class="mb-0 font-weight-bold text-sm">
                        Liquidación viajes operador
                    </p>
                    </div>
                </div>
                <div class="col-2 offset-3 text-center">
                            <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                            <h6 class="text-primary mb-0">Viajes realizados</h6>
                            <h4 class="font-weight-bolder"><span class="small" id="numViajes">0.00</span></h4>
                            </div>
                        </div>
                        <div class="col-3 text-center">
                            <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                            <h6 class="text-primary mb-0">Monto pendiente</h6>
                            <h4 class="font-weight-bolder"><span class="small" id="totalPago">$ 0.00</span></h4>
                            </div>
                        </div>
                
        
                </div>
            </div>
        </div>
    </div>
    <div class="row mt-2">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                    <h5 id="card_title">
                            Liquidaciones
                            <p class="text-sm mb-0">
                                <i class="fa fa-money-bill text-success"></i>
                                <span class="font-weight-bold">Seleccione los viajes que desea liquidar:</span> 
                            </p>
                        </h5>

                        <div class="float-right">
                         <button type="button" id="btnJustificar" class="btn btn-sm bg-gradient-info">
                            <i class="fa fa-coins"></i>
                            Justificar Gastos
                         </button>
                        </div>
                    </div>
                </div>

                <div class="card-body">
                <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 400px"></div>
                    
                </div>
                <div class="card-footer text-end">
                    <a href="{{route('index.liquidacion')}}" class="btn btn-sn btn-link text-muted">Cancelar</a>
                    <button class="btn btn-sm bg-gradient-success" id="btnSummaryPayment" > 
                        <i class="fa fa-check"></i>
                        Aplicar pago
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
@include('liquidaciones.modal-pagar')
@include('liquidaciones.modal-justificar-gasto')
@endsection

@push('custom-javascript')
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/liquidaciones/liquidar-viajes.js') }}?v={{ filemtime(public_path('js/sgt/liquidaciones/liquidar-viajes.js')) }}"></script>

<script>
    $(document).ready(()=>{
        mostrarViajesOperador({{$id}});
    });
</script>
@endpush