@extends('layouts.app')

@section('template_title')
    Planeacion
@endsection

@section('content')
<style>
    <style>
/* Estilo general del modal */
#viajeModal .modal-content {
  background: #f9fafb; /* gris muy claro para diferenciar del fondo blanco */
  border-radius: 16px;
  box-shadow: 0 0 25px rgba(0,0,0,0.2);
  border: none;
}

/* Cabecera */
#viajeModal .modal-header {
  background: linear-gradient(90deg, #0062cc, #007bff);
  color: #fff;
  border-top-left-radius: 16px;
  border-top-right-radius: 16px;
  padding: 1rem 1.5rem;
}

#viajeModal .modal-title {
  font-size: 1.3rem;
  font-weight: bold;
  color: #fff;
}

#viajeModal .modal-header .badge {
  background-color: rgba(255, 255, 255, 0.2);
  color: #fff;
  font-size: 0.8rem;
  padding: 6px 10px;
  border-radius: 8px;
}

/* Cuerpo */
#viajeModal .modal-body {
  font-size: 1rem;
  color: #333;
  padding: 1.5rem;
}

#viajeModal h6 {
  font-weight: bold;
  color: #0056b3;
  border-left: 4px solid #007bff;
  padding-left: 8px;
  margin-top: 1rem;
}

#viajeModal p {
  font-size: 1rem;
  margin-bottom: 6px;
}

/* Tabla documentos */
#viajeModal table th {
  /*background-color: #748ea8fb;*/
  color: rgb(60, 100, 153);
  font-size: 0.9rem;
  padding: 6px;
  border-radius: 4px;
}

#viajeModal table td {
  padding: 10px;
  font-size: 1.2rem;
}

/* Iconos de documentos */
#viajeModal .documentos i.text-secondary {
  color: #ccc !important;
}
#viajeModal .documentos i.text-success {
  color: #28a745 !important;
}

/* Pie */
#viajeModal .modal-footer {
  background: #f1f3f5;
  border-top: 1px solid #dee2e6;
  border-bottom-left-radius: 16px;
  border-bottom-right-radius: 16px;
}

#viajeModal button {
  font-size: 0.95rem;
  padding: 6px 14px;
}

/* Efecto de enfoque */
#viajeModal.show .modal-content {
  animation: modalPop 0.3s ease-in-out;
}

@keyframes modalPop {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>
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

                                <div class="">
                                    <button onclick="confirmarCambiosPlaneacion()" type="button" class="btn btn-sm bg-gradient-success d-none" id="btnGuardarBoard">
                                        <i class="fa fa-fw fa-save"></i>  Confirmar cambios en Board
                                    </button>
                                    <a href="{{route('planeacion.programar')}}" class="btn btn-sm bg-gradient-info" >
                                        <i class="fa fa-fw fa-plus"></i>  Planear
                                    </a>
                                </div>

                        </div>
                        <div  class="d-flex justify-content-end ">
                            <div class="p-2 parpadeando d-none"id="labelNotice"  style="color: #444;border: 1px dashed #ccc;border-radius: 8px;background-color: #f8f9fa;font-weight: 500;">
                             Viajes con cambios sin confirmar: 3
                            </div>
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

@keyframes parpadeo {
  0% {
    opacity: 1;
    background-color: #ff5722; /* Coral */
    box-shadow: 0 0 10px 4px rgba(255, 111, 97, 0.6);
  }
  50% {
    opacity: 0.5;
    box-shadow: 0 0 20px 10px rgba(255, 255, 100, 0.9);
  }
  100% {
    opacity: 1;
    box-shadow: 0 0 10px 4px rgba(255, 255, 100, 0.7);
  }
}

.parpadeando {
  animation: parpadeo 1.2s ease-in-out infinite;
  border: 2px solid gold;
  background-color: #fff8dc;
  color: #333;
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