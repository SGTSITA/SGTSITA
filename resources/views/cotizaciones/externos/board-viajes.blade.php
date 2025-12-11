@extends('layouts.usuario_externo')

@section('WorkSpace')
@csrf
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


#viajeModal.show .modal-content {
  animation: modalPop 0.3s ease-in-out;
}

@keyframes modalPop {
  from { transform: scale(0.95); opacity: 0; }
  to { transform: scale(1); opacity: 1; }
}
</style>
<div class="card">
<div class="card-header d-flex flex-column">


    <div class="d-flex justify-content-between w-100 align-items-center mb-2">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-gray-900">Programa de viajes</span>
            <span class="text-gray-500 mt-1 fw-semibold fs-6">
                Visualice fácilmente los viajes programados y mantenga el control de las rutas.
            </span>
        </h3>

        <div class="card-toolbar">
            <button onclick="confirmarCambiosPlaneacion()" type="button"
                class="btn btn-sm btn-success d-none" id="btnGuardarBoard">
                <i class="fa fa-fw fa-save"></i> Confirmar cambios en Board
            </button>

            <a href="{{ route('viajes.solicitar') }}" type="button"
                class="btn btn-sm btn-light-primary">
                <i class="ki-duotone ki-delivery">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                    <span class="path5"></span>
                </i>
                Solicitar viaje
            </a>
        </div>
    </div>


    <div class="d-flex justify-content-end w-100">
        <div class="p-2 parpadeando d-none" id="labelNotice"
            style="color:#444;border:1px dashed #ccc;border-radius:8px;background-color:#f8f9fa;font-weight:500;">
            Viajes con cambios sin confirmar: 3
        </div>
    </div>

</div>

    <div class="card-body">
    <div id="dp"></div>
        <div class="d-flex flex-column flex-lg-row-fluid">
          <div class="d-flex flex-center flex-column flex-column-fluid">

          </div>
        </div>
    </div>
</div>
<div id="loading-overlay">
        <div class="loading-content">
            <div class="sk-circle">
                <div class="sk-circle1 sk-child"></div>
                <div class="sk-circle2 sk-child"></div>
                <div class="sk-circle3 sk-child"></div>
                <div class="sk-circle4 sk-child"></div>
                <div class="sk-circle5 sk-child"></div>
                <div class="sk-circle6 sk-child"></div>
                <div class="sk-circle7 sk-child"></div>
                <div class="sk-circle8 sk-child"></div>
                <div class="sk-circle9 sk-child"></div>
                <div class="sk-circle10 sk-child"></div>
                <div class="sk-circle11 sk-child"></div>
                <div class="sk-circle12 sk-child"></div>
            </div>
            <div class="loading-text" id="loading-text">Procesando solicitud…</div>
        </div>
    </div>
@include('mec.Board.modal-infoviaje')
@endsection

@push('javascript')
<script src="{{asset('DayPilot/js/daypilot-all.min.js?v=2022.3.5384')}}"></script>
<script src="{{asset('DayPilot/helpers/v2/app.js?v=2022.3.5384')}}"></script>
<script type="text/javascript" src="{{asset('DayPilot/js/boardClient.js')}}?v={{ filemtime(public_path('DayPilot/js/boardClient.js')) }}"></script>
<script>
    $(document).ready(()=>{

    const today = new Date();

    const firstDay = new Date(today.getFullYear(), today.getMonth(), 1);
    const lastDay = new Date(today.getFullYear(), today.getMonth() + 1, 0);

    const sevenDaysAgo = new Date();
    sevenDaysAgo.setDate(today.getDate() - 7);

    const formatDate = (date) => date.toISOString().split('T')[0];

   // document.getElementById('daterange').value=`${formatDate(firstDay)} AL ${formatDate(lastDay)}`

    initBoard(formatDate(firstDay),formatDate(lastDay));

    });
</script>
@endpush
