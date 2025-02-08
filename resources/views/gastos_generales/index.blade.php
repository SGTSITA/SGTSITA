@extends('layouts.app')

@section('template_title')
Gastos Generales
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h5 id="card_title">
                                Gastos Generales
                                <p class="text-sm mb-0">
                                    <i class="fa fa-calendar text-success"></i>
                                    <span class="font-weight-bold">Periodo:</span> del {{$initDay}} al {{$now}}
                                </p>
                            </h5>

                              @can('gastos-create')
                             <div class="float-right">
                                <button type="button" class="btn btn-sm bg-gradient-warning" id="btnDiferir">
                                    <i class="fa fa-fw fa-coins"></i>  Diferir
                                </button>
                                <button type="button" class="btn btn-sm bg-gradient-info" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                    <i class="fa fa-fw fa-plus"></i>  Agregar Gasto
                                </button>
                              </div>
                              @endcan

                        </div>
                    </div>

                    <div class="card-body"style=" padding: 0rem 1.5rem 1.5rem 1.5rem;">
                        <div class="row">
                         <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 500px"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('gastos_generales.create')
@include('gastos_generales.modal-diferir')
@endsection

@push('custom-javascript')
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/gastos/gastos.js') }}?v={{ filemtime(public_path('js/sgt/gastos/gastos.js')) }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
    $(document).ready(()=>{
        getGastos('{{$initDay}}','{{$now}}');

        flatpickr(".fechas", {
            locale: "es",
            dateFormat: "Y-m-d", // Formato de la fecha (Año-Mes-Día)
            allowInput: false     // Permite escribir manualmente la fecha
        });
        
    });
</script>
@endpush
