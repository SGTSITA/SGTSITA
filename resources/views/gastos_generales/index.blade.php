@extends('layouts.app')

@section('template_title')
Gastos Generales
@endsection

@section('content')
<div class="card">
<div class="card-header bg-transparent">
<h5 class="card-title">Gastos Generales</h5>
            <div class="row align-items-center mb-3">
  <!-- Texto y periodo -->
  <div class="col-lg-6 col-md-6 col-12 d-flex align-items-center gap-3">
    
    <div>
      <label for="daterange" class="small mb-1">Periodo</label>
      <input type="text" id="daterange" readonly
        class="form-control form-control-sm"
        style="min-width: 450px; border: none; box-shadow: none;" />
    </div>
  </div>
  @can('gastos-create')
    <!-- Botones -->
    <div class="col-lg-6 col-md-6 col-12 d-flex justify-content-end gap-2">
        <button class="btn btn-sm btn-info d-flex align-items-center gap-1"
        data-bs-toggle="modal" data-bs-target="#modalAgregarGasto">
        <i class="ni ni-plus"></i> Registrar gasto
        </button>
        {{--  <button type="button" class="btn btn-sm bg-gradient-success" id="btnDiferir">
                                        <i class="fa fa-fw fa-coins"></i> Aplicar Pago
                                    </button> --}}
        <button type="button" class="btn btn-sm btn-danger d-flex align-items-center gap-1" id="btnEliminarGasto">
        <i class="fa fa-fw fa-trash"></i> Eliminar gasto
        </button>
    </div>
    @endcan
</div>
              <hr class="horizontal light">
             
            </div>

   <div class="card-body pt-4 p-3">
   <div class="row">
                         <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 500px"></div>
                        </div>
     
   </div>
 
 </div>

@include('gastos_generales.modal_registro_gastoGral')
@include('gastos_generales.modal-diferir')

 <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/choices.js/public/assets/styles/choices.min.css" />
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
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
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

        let bancos = [];
            (async () => {
            bancos = await getBancos();
        // console.log(bancos);
        })();
    

    

           $('#daterange').daterangepicker({
        opens: 'right',
        locale: {
            format: 'YYYY-MM-DD', // Formato de fecha
            separator: " AL ", // Separador entre la fecha inicial y final
            applyLabel: "Aplicar",
            cancelLabel: "Cancelar",
            fromLabel: "Desde",
            toLabel: "Hasta",
            customRangeLabel: "Personalizado",
            daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
            monthNames: ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"],
            firstDay: 1
        },
        maxDate: moment()
    }, 
        function(start, end, label) {

let spanPeriodoc = document.getElementById('periodo');
spanPeriodoc.textContent = ` ${start.format('DD-MM-YYYY')} AL ${end.format('DD-MM-YYYY')}`;

      //  getUtilidadesViajes();
      getGastos(start.format('YYYY-MM-DD'),end.format('YYYY-MM-DD'))
        $('#daterange').attr('data-start', start.format('YYYY-MM-DD'));
        $('#daterange').attr('data-end', end.format('YYYY-MM-DD'));

  
    });

    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

    const formatDate = (date) => date.toISOString().split('T')[0];

    document.getElementById('daterange').value=`${formatDate(firstDayOfMonth)} AL ${formatDate(today)}`

    getGastos(formatDate(firstDayOfMonth),formatDate(today))
   

    $('#daterange').attr('data-start', formatDate(firstDayOfMonth));
    $('#daterange').attr('data-end', formatDate(today));
     let spanPeriodo = document.getElementById('periodo');
    spanPeriodo.textContent =  `${moment(firstDayOfMonth).format('DD-MM-YYYY')} AL ${moment(today).format('DD-MM-YYYY')}`;

        if (document.getElementById('selectUnidadesGeneral')) {
            var element = document.getElementById('selectUnidadesGeneral');
            const example = new Choices(element, {
                removeItemButton: true
            });
        }

        /* if (document.getElementById('selectViajes')) {
            var element = document.getElementById('selectViajes');
            const example = new Choices(element, {
                removeItemButton: true
            });
        } */
        
    });
</script>

@endpush
