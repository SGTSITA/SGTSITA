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
                                       
                                        <div class="font-weight-bolder text-sm"><span class="small">Periodo</span></div>
                                        <input type="text" id="daterange" readonly 
                                        class="form-control form-control-sm min-w-100" 
                                        style="border: none; box-shadow: none;"
                                        />
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

<link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.css" />
<!-- Moment.js -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/moment@2.29.1/moment.min.js"></script>
<!-- JS de Date Range Picker -->
<script type="text/javascript" src="https://cdn.jsdelivr.net/npm/daterangepicker/daterangepicker.min.js"></script>
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
        // Callback para el botón "Aplicar"
      //  $("#fecha_de").val(start.format('YYYY-MM-DD'));
      //  $("#fecha_hasta").val(end.format('YYYY-MM-DD'));
       // document.getElementById("form-buscar").submit();
        getHistorial(start.format('YYYY-MM-DD'),end.format('YYYY-MM-DD'));

  
    });

    const today = new Date();
    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(today.getDate() - 7);

    const formatDate = (date) => date.toISOString().split('T')[0];

    document.getElementById('daterange').value=`${formatDate(sevenDaysAgo)} AL ${formatDate(today)}`

    getHistorial(formatDate(sevenDaysAgo),formatDate(today));
    });
</script>
@endpush