@extends('layout.main')

@section('title-window')
 @include('layout.title-window')
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 p-6">
  <div class="max-w-5xl mx-auto">
    <!-- Grid de tarjetas -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

    @foreach($bancos as $b)
      @php
       $bank = strtolower($b->nombre_banco);
       $logo = $logos[$bank] ?? $logos['default'];
      @endphp
      <!-- Tarjeta BBVA -->
      <div class="kt-card rounded-2xl shadow-sm hover:shadow-md transition">
        <!-- Header: logo + banco -->
        <div class="kt-card-header flex items-center justify-between p-5">
          <div class="flex items-center gap-3">
            <img src="{{$logo}}" alt="BBVA" class="w-10 h-10 object-contain" />
            <div class="text-lg font-semibold text-mono">{{ucwords(strtolower($b->nombre_banco))}}</div>
          </div>
          <span class="">
                 <span class="kt-badge rounded-full kt-badge-outline @if($b->cuenta_global == 1) kt-badge-success @else kt-badge-primary @endif  gap-1 items-center">
                    <span class="kt-badge-dot size-2.0">
                    </span>
                    @if($b->cuenta_global == 1) 
                     Cuenta Global 
                    @else 
                     @if($b->banco_1 == 1) Cuenta 1 @else Otros @endif
                    @endif
                 </span>
            </span>
        </div>

        <!-- Contenido -->
        <div class="kt-card-content card-bank p-5 flex flex-col gap-2.5 ">
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">Titular:</span>
            <span class="text-sm font-medium text-mono">{{$b->nombre_beneficiario}}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">Cuenta:</span>
            <span class="text-sm font-medium text-mono">{{$b->cuenta_bancaria}}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">CLABE:</span>
            <span class="text-sm font-medium text-mono">{{$b->clabe}}</span>
          </div>
        </div>

        <!-- Footer: botón + switch -->
        <div class="kt-card-footer flex justify-between items-center py-3.5 px-5 border-t border-border">
          <button class="kt-btn kt-btn-outline text-sm font-medium">
            Ver detalles
          </button>
          <label class="flex items-center gap-2">
            <input @if($b->cuenta_global == 1) checked @endif class="kt-switch" type="checkbox" value="1" name="cuenta_principal">
            <span class="text-sm font-medium text-mono">Cuenta Global</span>
          </label>
        </div>
      </div>

      @endforeach
  </div>
</div>

@include('bancos.drawer_crear_cuenta')
<style>
  .card-bank {
  background-image: url('/asset/media/images/2600x1200/bg-4.png');
  background-size: cover;
  background-repeat: no-repeat;
}
</style>
@endsection