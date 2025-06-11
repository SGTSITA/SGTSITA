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
                                    <span class="font-weight-bold">Periodo:</span> del 
                                </p>
                            </h5>

                              @can('gastos-create')
                             <div class="float-right">
                                <button type="button" class="btn btn-sm bg-gradient-success" id="btnDiferir">
                                    <i class="fa fa-fw fa-coins"></i> Aplicar Pago
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
<style>
    .rag-red {
    background-color: #cc222244;
}

.rag-warning{
    background-color: #ffff3344;
    
}

.rag-green {
    background-color: #33cc3344;
}
.option-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    max-width: 100%;
  }

  .custom-option {
    position: relative;
    display: flex;
    align-items: center;
    border: 1px dashed #ccc;
    border-radius: 8px;
    padding: 12px 16px;
    min-height: 79px;
    flex: 1 1 200px;
    cursor: pointer;
    transition: background-color 0.2s, border-color 0.2s;
  }

  .custom-option input[type="radio"] {
    display: none;
  }

  .custom-option .icon {
    margin-right: 16px;
    font-size: 24px;
    color: #ccc;
    flex-shrink: 0;
    transition: color 0.2s;
  }

  .custom-option .text {
    font-size: 1rem;
    color: #333;
  }

  .custom-option.selected {
    background-color: #e6f4ff;
    border-color: #007BFF;
  }

  .custom-option.selected .icon {
    color: #007BFF;
  }

  .check-icon {
  position: absolute;
  top: 50%;
  right: 8px;
  transform: translateY(-50%);
  background-color: #a5dc86;
  border-radius: 50%;
  padding: 4px;
  font-size: 14px;
  color: white;
  display: none;
}

  .custom-option.selected .check-icon {
    display: inline-block;
  }
</style>

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/gastos/gastos.js') }}?v={{ filemtime(public_path('js/sgt/gastos/gastos.js')) }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://demos.creative-tim.com/argon-dashboard-pro/assets/js/plugins/choices.min.js"></script>


<script>
    $(document).ready(()=>{
        getGastos('{{$initDay}}','{{$now}}');

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

        if (document.getElementById('selectViajes')) {
            var element = document.getElementById('selectViajes');
            const example = new Choices(element, {
                removeItemButton: true
            });
        }
        
    });
</script>
<script>
  function handleSelection(input) {
    
    document.querySelectorAll('.custom-option').forEach(opt =>{
        opt.classList.remove('selected') 
    });
    
    input.parentElement.classList.add('selected');

    document.querySelectorAll(".aplicacion-gastos").forEach( opt => {
        opt.classList.add('d-none')
    })

    if(input.parentElement.innerText == "Viaje") document.querySelector("#aplicacion-viaje").classList.remove('d-none');
    if(input.parentElement.innerText == "Equipo") document.querySelector("#aplicacion-equipo").classList.remove('d-none');
    
    
  }
</script>
@endpush
