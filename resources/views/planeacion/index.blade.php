@extends('layouts.app')

@section('template_title')
    Planeacion
@endsection

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-sm-12">
            <div class="card">
            <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h5 id="card_title">
                                Planeación
                                <p class="text-sm mb-0">
                                       
                                       <div class="font-weight-bolder text-sm"><span class="small">Periodo</span></div>
                                       <input type="text" id="daterange" readonly 
                                       class="form-control form-control-sm min-w-100" 
                                       style="min-width:200px !important;border: none; box-shadow: none;"
                                       
                                       />
                                   </p>
                            </h5>

                             
                            
                                <div class="search-container">
                                    <i class="fas fa-search search-icon"></i>
                                    <input type="text" id="txtBuscarContenedor" class="input-apple-style" placeholder="Buscar...">
                                </div>

                                <a href="{{route('planeacion.programar')}}" class="btn btn-sm bg-gradient-info" >
                                    <i class="fa fa-fw fa-plus"></i>  Planear
                                </a>
                             
                           

                        </div>
                    </div>
                <div class="card-body" style="padding-left: 1.5rem; padding-top: 1rem;">
                    <div id="dp"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@include('planeacion.modal_info_planeacion')
@endsection

@push('custom-javascript')
<style>
    #dp {
  flex: 1;
  min-height: 0;
}
.search-container {
  position: relative;
  width: 100%;
  max-width: 400px;
}

.search-icon {
  position: absolute;
  top: 50%;
  left: 12px;
  transform: translateY(-50%);
  color: #aaa;
  font-size: 14px;
  pointer-events: none;
}

.input-apple-style {
  width: 100%;
  padding: 8px 14px 8px 36px;
  font-size: 15px;
  border: none;
  border-radius: 9999px;
  background-color: #e9ecef;
  color: #333;
  box-shadow: inset 0 0 0 1px rgba(0,0,0,0.05), 0 1px 2px rgba(0,0,0,0.05);
  transition: all 0.2s ease;
  outline: none;
  font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
}

.input-apple-style::placeholder {
  color: #bbb;
  font-weight: 300;
}

.input-apple-style:focus {
  background-color: #fff;
  box-shadow: 0 0 0 3px rgba(0, 122, 255, 0.2), inset 0 0 0 1px rgba(0,0,0,0.05);
}
</style>
<script src="{{asset('DayPilot/js/daypilot-all.min.js?v=2022.3.5384')}}"></script>    
<script src="{{asset('DayPilot/helpers/v2/app.js?v=2022.3.5384')}}"></script>
<script type="text/javascript" src="{{asset('DayPilot/js/boardCarpos.js')}}?v={{ filemtime(public_path('DayPilot/js/boardCarpos.js')) }}"></script>

<!-- Date Range Picker CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />

<!-- Moment.js -->
<script src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>

<!-- Date Range Picker JS -->
<script src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
<script>
$(document).ready(function() {

  
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
        initBoard(start.format('YYYY-MM-DD'),end.format('YYYY-MM-DD'));
        $('#daterange').attr('data-start', start.format('YYYY-MM-DD'));
        $('#daterange').attr('data-end', end.format('YYYY-MM-DD'));

  
    });

    const today = new Date();
   
    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(today.getDate() - 7);

    const formatDate = (date) => date.toISOString().split('T')[0];

    document.getElementById('daterange').value=`${formatDate(firstDay)} AL ${formatDate(lastDay)}`

    initBoard(formatDate(firstDay),formatDate(lastDay));
    $('#daterange').attr('data-start', formatDate(firstDay));
    $('#daterange').attr('data-end', formatDate(lastDay));
    });
</script>
@endpush