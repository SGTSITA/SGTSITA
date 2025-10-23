@extends('layout.main')

@section('title-window')
 @include('layout.title-window')
@endsection

@section('content')
<div class="min-h-screen bg-gradient-to-b from-gray-50 to-gray-100 p-6">
  <div class="max-w-5xl mx-auto">


    <!-- Grid de tarjetas -->
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">

      <!-- Tarjeta BBVA -->
      <div class="kt-card rounded-2xl shadow-sm hover:shadow-md transition">
        <!-- Header: logo + banco -->
        <div class="kt-card-header flex items-center justify-between p-5">
          <div class="flex items-center gap-3">
            <img src="{{asset('/asset/media/bancos/bbva.png')}}" alt="BBVA" class="w-10 h-10 object-contain" />
            <div class="text-lg font-semibold text-mono">BBVA</div>
          </div>
          <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Secundaria</span>
        </div>

        <!-- Contenido -->
        <div class="kt-card-content p-5 flex flex-col gap-2.5 ">
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">Titular:</span>
            <span class="text-sm font-medium text-mono">Empresa XYZ</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">Cuenta:</span>
            <span class="text-sm font-medium text-mono">9876543210</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">CLABE:</span>
            <span class="text-sm font-medium text-mono">012345678901234567</span>
          </div>
        </div>

        <!-- Footer: botón + switch -->
        <div class="kt-card-footer flex justify-between items-center py-3.5 px-5 border-t border-border">
          <button class="kt-btn kt-btn-outline text-sm font-medium">
            Ver detalles
          </button>
          <label class="flex items-center gap-2">
            <input checked class="kt-switch" type="checkbox" value="1" name="cuenta_principal">
            <span class="text-sm font-medium text-mono">Principal</span>
          </label>
        </div>
      </div>

      <!-- Tarjeta Banamex -->
      <div class="kt-card rounded-2xl shadow-sm hover:shadow-md transition">
        <div class="kt-card-header flex items-center justify-between p-5">
          <div class="flex items-center gap-3">
            <img src="{{asset('/asset/media/bancos/banamex.jpg')}}" alt="Banamex" class="w-10 h-10 object-contain" />
            <div class="text-lg font-semibold text-mono">Banamex</div>
          </div>
          <span class="text-xs bg-blue-100 text-blue-600 px-2 py-1 rounded-full">Principal</span>
        </div>
        <div class="kt-card-content p-5 flex flex-col gap-2.5 ">
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">Titular:</span>
            <span class="text-sm font-medium text-mono">Juan Pérez</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">Cuenta:</span>
            <span class="text-sm font-medium text-mono">1234567890</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">CLABE:</span>
            <span class="text-sm font-medium text-mono">002910123456789012</span>
          </div>
        </div>
        <div class="kt-card-footer flex justify-between items-center py-3.5 px-5 border-t border-border">
          <button class="kt-btn kt-btn-outline text-sm font-medium">
            Ver detalles
          </button>
          <label class="flex items-center gap-2">
            <input checked class="kt-switch" type="checkbox" value="1" name="cuenta_principal">
            <span class="text-sm font-medium text-mono">Principal</span>
          </label>
        </div>
      </div>

      <!-- Tarjeta Banorte -->
      <div class="kt-card rounded-2xl shadow-sm hover:shadow-md transition">
        <div class="kt-card-header flex items-center justify-between p-5">
          <div class="flex items-center gap-3">
            <img src="{{asset('/asset/media/bancos/banorte.jpg')}}" alt="Banorte" class="w-10 h-10 object-contain" />
            <div class="text-lg font-semibold text-mono">Banorte</div>
          </div>
          <span class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full">Empresa</span>
        </div>
        <div class="kt-card-content p-5 flex flex-col gap-2.5 ">
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">Titular:</span>
            <span class="text-sm font-medium text-mono">Compañía ABC</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">Cuenta:</span>
            <span class="text-sm font-medium text-mono">4567890123</span>
          </div>
          <div class="flex justify-between">
            <span class="text-sm text-secondary-foreground">CLABE:</span>
            <span class="text-sm font-medium text-mono">014123456789012345</span>
          </div>
        </div>
        <div class="kt-card-footer flex justify-between items-center py-3.5 px-5 border-t border-border">
          <button class="kt-btn kt-btn-outline text-sm font-medium">
            Ver detalles
          </button>
          <label class="flex items-center gap-2">
            <input class="kt-switch" type="checkbox" value="1" name="cuenta_principal">
            <span class="text-sm font-medium text-mono">Principal</span>
          </label>
        </div>
      </div>

    </div>

  
  </div>
</div>
<style>
    .kt-card-content {
  background-image: url('/asset/media/images/2600x1200/bg-4.png');
  background-size: cover;
  background-repeat: no-repeat;
}
</style>
@endsection