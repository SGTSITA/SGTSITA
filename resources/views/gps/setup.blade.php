@extends('layouts.app')

@section('template_title', 'Configuración GPS')

@section('content')
<div class="container-fluid">
  <div class="row">
    <div class="card mt-4" id="accounts">
      <div class="card-header">
        <h5>Servicios GPS</h5>
        <p class="text-sm">Aquí puede agregar la configuración de conexión de su servicio GPS.</p>
      </div>
      <div class="card-body pt-0">
        @foreach($companies as $c)
        <div class="d-flex">
          <img class="width-48-px" src="../../assets/img/small-logos/logo-atlassian.svg" alt="logo_atlassian">
          <div class="my-auto ms-3">
            <div class="h-100">
              <h5 class="mb-0">{{$c->nombre}}</h5>
              <p class="mb-0 text-sm">{{$c->url}}</p>
            </div>
          </div>
          <p class="text-sm text-secondary ms-auto me-3 my-auto">{{$c->estado}}</p>
          <div class="form-check form-switch my-auto">
            <button data-gps="{{$c->id}}" class="btn btn-sm btn-config-gps @if($c->estado == 'Activo) bg-gradient-success @else bg-gradient-info @endif" id="btn-config-gps-{{$c->id}}">
                @if($c->estado == 'Activo') 
                <i class="fa fa-location-arrow"></i> Activo 
                @else 
                <i class="fa fa-cog"></i> Configurar Servicio 
                @endif
            </button>
          </div>
        </div>
        <hr class="horizontal dark">
        @endforeach

      </div>
    </div>
  </div>
</div>

@include('gps.modal_configurar_gps')
@endsection

@push('custom-javascript')
<script src="{{ asset('js/mep/gps/configurar_gps.js') }}?v={{ filemtime(public_path('js/mep/gps/configurar_gps.js')) }}"></script>
@endpush