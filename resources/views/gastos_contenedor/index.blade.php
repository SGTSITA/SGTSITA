@extends('layouts.app')

@section('template_title')
Gastos por Pagar
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h5 id="card_title">
                                Gastos por Pagar
                                <p class="text-sm mb-0">
                                  
                                    <span class="text-muted font-weight-bold"> Registro, control y seguimiento de gastos por pagar </span> 
                                </p>
                            </h5>

                              @can('gastos-create')
                             <div class="float-right">
                               
                                <button type="button" class="btn btn-sm bg-gradient-success" data-bs-toggle="modal" data-bs-target="#modalPagar">
                                    <i class="fa fa-fw fa-check"></i>  Liquidar / Pagar
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
                    <div class="card-footer text-end">
                        <div class="row">
                        <div class="col-3 offset-9 text-center">
                        <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                            <h6 class="text-primary mb-0">Total a pagar</h6>
                            <h4 class="font-weight-bolder"><span class="small totalPago" id="totalPago1">$ 0.00</span></h4>
                            </div>
                        </div>
                      
                
        
                </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('gastos_contenedor.modal_pagar_gastos')
@endsection

@push('custom-javascript')
<style>
    .rag-red {
    background-color: #cc222244;
}

.rag-green {
    background-color: #33cc3344;
}
</style>
<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/gastos/gastosContenedor.js') }}?v={{ filemtime(public_path('js/sgt/gastos/gastosContenedor.js')) }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://demos.creative-tim.com/argon-dashboard-pro/assets/js/plugins/choices.min.js"></script>


<script>
    $(document).ready(()=>{
        getGxp();

        flatpickr(".fechas", {
            locale: "es",
            dateFormat: "Y-m-d", // Formato de la fecha (Año-Mes-Día)
            allowInput: false     // Permite escribir manualmente la fecha
        });

        if (document.getElementById('selectUnidades')) {
            var element = document.getElementById('selectUnidades');
            const example = new Choices(element, {
                removeItemButton: true
            });
        }
        
    });
</script>
@endpush
