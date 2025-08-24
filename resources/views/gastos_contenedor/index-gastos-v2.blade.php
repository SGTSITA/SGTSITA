@extends('layouts.app')

@section('template_title')
    Gastos Viajes
@endsection

@section('content')
<div class="card">
<div class="card-header bg-transparent">
<h5 class="card-title">Registro de gastos por viaje</h5>
              <div class="row">
                <div class="col-lg-4 col-md-6 col-12">
                  
                <div class="font-weight-bolder text-sm"><span class="small">Periodo</span></div>
          <input type="text" id="daterange" readonly 
          class="form-control form-control-sm min-w-100" 
          style="border: none; box-shadow: none;"
          
          />
                </div>
                <div class="col-lg-6 col-md-6 col-12 my-auto ms-auto">
                  <div class="d-flex justify-content-end">
                    <button class="btn btn-sm bg-gradient-info m-1"  data-bs-toggle="modal" data-bs-target="#modalAgregarGasto">
                    <i class="ni ni-plus text-lg text-body ms-auto"></i> Registrar gasto
                    </button>
                  
                  </div>
                </div>
              </div>
              <hr class="horizontal light">
             
            </div>

   <div class="card-body pt-4 p-3">
   <div id="pagosPendientes" style="height: 400px; overflow: hidden; width: 100%;"></div>
     
   </div>
   <div class="card-footer text-end">  
     <div class="col-md-4 offset-8">
       <button class="btn btn-sm bg-gradient-success" id="btnStore">
         <i class="fas fa-check" aria-hidden="true"></i>&nbsp;&nbsp; Guardar gastos </button>
     </div>
   </div>
 </div>
@include('gastos_contenedor.modal_registro_gasto')
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
<link href="/assets/handsontable/handsontable.full.min.css" rel="stylesheet" media="screen">
<script src="/assets/handsontable/handsontable.full.min.js"></script>
<script src="/assets/handsontable/all.js"></script>

<script src="{{ asset('js/sgt/gastos/gastosViajes.js') }}?v={{ filemtime(public_path('js/sgt/gastos/gastosViajes.js')) }}"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>

<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/gastos/gastosV2.js') }}?v={{ filemtime(public_path('js/sgt/gastos/gastosV2.js')) }}"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script src="https://demos.creative-tim.com/argon-dashboard-pro/assets/js/plugins/choices.min.js"></script>
<script>
$(document).ready(function() {
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

       /* if (document.getElementById('selectViajes')) {
            var element = document.getElementById('selectViajes');
            const example = new Choices(element, {
                removeItemButton: true
            });
        }*/
    });
</script>
<script>
   $(document).ready(async()=>{
   //getViajes();
    let bancos = await getBancos();
    const gastosHandsOnTable = buildGastosHandsOnTable();
    

    const btnStore = document.querySelector('#btnStore')
    btnStore.addEventListener('click',i=> gastosHandsOnTable.storeDataHTGastos())

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
      //  getUtilidadesViajes();
      gastosHandsOnTable.fillDataHTGastos(start.format('YYYY-MM-DD'),end.format('YYYY-MM-DD'))
        $('#daterange').attr('data-start', start.format('YYYY-MM-DD'));
        $('#daterange').attr('data-end', end.format('YYYY-MM-DD'));

  
    });

    const today = new Date();
    const firstDayOfMonth = new Date(today.getFullYear(), today.getMonth(), 1);

    const formatDate = (date) => date.toISOString().split('T')[0];

    document.getElementById('daterange').value=`${formatDate(firstDayOfMonth)} AL ${formatDate(today)}`

    gastosHandsOnTable.fillDataHTGastos(formatDate(firstDayOfMonth),formatDate(today))

    $('#daterange').attr('data-start', formatDate(firstDayOfMonth));
    $('#daterange').attr('data-end', formatDate(today));

   });
</script>
@endpush