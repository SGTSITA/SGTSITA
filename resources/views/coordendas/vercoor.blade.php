@extends('layouts.app')

@section('template_title', 'Ver Coordenadas')


@section('content')
    
<div class="container-fluid py-4 px-3 bg-gray-100 min-h-screen">
    <div class="row justify-content-center">
        <div class="col-sm-12">
            <div class="card shadow-lg border-0 bg-white rounded-4 p-4">
                <div class="mb-4">
                <input type="text"  id="buscadorContenedor" 
                        placeholder="Buscar contenedor..." 
                        class="form-control shadow-sm border-2 border-blue-300 focus:border-blue-500 rounded-pill"
                    />
                </div>

                <h3 class="text-xl font-semibold text-center mb-3">Mapa de Coordenadas</h3>

                <div id="map" 
                    style="height: 600px; width: 100%;" 
                    class="rounded-4 border-4 border-blue-500 shadow-md">
                </div>
            </div>
        </div>
    </div>
</div>
    
@endsection

@push('custom-javascript')
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


    <!-- Nuestro JavaScript unificado -->
    <script
        src="{{ asset('js/sgt/coordenadas/coordenadasver.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/coordenadasver.js')) }}">
    </script>

    <!-- SweetAlert para mostrar mensajes -->
    <script>
       
       </script>
@endpush
