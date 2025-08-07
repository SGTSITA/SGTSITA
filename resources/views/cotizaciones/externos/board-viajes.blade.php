@extends('layouts.usuario_externo')

@section('WorkSpace')
@csrf
<div class="card">
   <div class="card-header ">
        <h3 class="card-title align-items-start flex-column">
            <span class="card-label fw-bold text-gray-900">Programa de viajes</span>
            <span class="text-gray-500 mt-1 fw-semibold fs-6">Visualice fácilmente los viajes programados y mantenga el control de las rutas.</span>
        </h3>
        <div class="card-toolbar">
            <button type="button" class="btn btn-sm btn-light-primary">
            <i class="ki-duotone ki-delivery">
                <span class="path1"></span>
                <span class="path2"></span>
                <span class="path3"></span>
                <span class="path4"></span>
                <span class="path5"></span>
            </i>
                Solicitar viaje
            </button>
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