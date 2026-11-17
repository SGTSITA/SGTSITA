@extends('layouts.app')

@section('template_title')
    Liquidaciones
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center">
                            <h5 id="card_title">
                                Liquidaciones
                                <p class="text-sm mb-0">
                                    <span class="font-weight-bold">Pagos pendientes a operadores</span>
                                </p>
                            </h5>

                            <div class="float-right">
                                <button type="button" class="btn btn-sm bg-gradient-info" id="openPay">
                                    <i class="fa fa-fw fa-money-bill"></i>
                                    Pagar viajes
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 500px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
    <script src="{{ asset('js/sgt/liquidaciones/liquidaciones.js') }}?v={{ filemtime(public_path('js/sgt/liquidaciones/liquidaciones.js')) }}"></script>

    <script>
        $(document).ready(() => {
            getViajesOperadores();
        });
    </script>
@endpush
