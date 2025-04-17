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
                                    <i class="fa fa-calendar text-success"></i>
                          
                                </p>
                            </h5>

                             
                             <div class="float-right">
                                <a href="{{route('planeacion.programar')}}" class="btn btn-sm bg-gradient-info" >
                                    <i class="fa fa-fw fa-plus"></i>  Planear
                                </a>
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
<script src="{{asset('DayPilot/js/daypilot-all.min.js?v=2022.3.5384')}}"></script>    
<script src="{{asset('DayPilot/helpers/v2/app.js?v=2022.3.5384')}}"></script>
<script type="text/javascript" src="{{asset('DayPilot/js/boardCarpos.js')}}"></script>
@endpush