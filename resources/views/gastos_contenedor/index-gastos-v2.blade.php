@extends('layouts.app')

@section('template_title')
    Gastos Viajes
@endsection

@section('content')
<div class="card">
   <div class="card-header">
     <h5 class="card-title">Registro de gastos por viaje
     <p class="text-sm mb-0">
                                       
          <div class="font-weight-bolder text-sm"><span class="small">Periodo</span></div>
          <input type="text" id="daterange" readonly 
          class="form-control form-control-sm min-w-100" 
          style="border: none; box-shadow: none;"
          
          />
      </p>
     </h5>

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
 
@endsection

@push('custom-javascript')
<link href="/assets/handsontable/handsontable.full.min.css" rel="stylesheet" media="screen">
<script src="/assets/handsontable/handsontable.full.min.js"></script>
<script src="/assets/handsontable/all.js"></script>
<!--script src="/js/sgt/cxp/cxp.js"></script-->
<script src="{{ asset('js/sgt/gastos/gastosViajes.js') }}?v={{ filemtime(public_path('js/sgt/gastos/gastosViajes.js')) }}"></script>

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- Moment.js -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<!-- JS de Date Range Picker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function() {

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