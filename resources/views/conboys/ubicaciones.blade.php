@extends('layouts.app')

@section('template_title', 'Historial de Ubicaciones')


@section('content')
<style>
  .info-convoy-box {
    border: 2px solid #007bff;
    border-radius: 10px;
    padding: 20px;
    margin-top: 20px;
    background-color: #f8f9fa;
}

.info-convoy-title {
    text-align: center;
    font-weight: bold;
    margin-bottom: 15px;
    font-size: 1.1rem;
    color: #007bff;
}
.loading-overlay {
    position: absolute;
    top: 0;
    left: 0;
    z-index: 10; /* asegúrate que esté por encima del grid */
    width: 100%;
    height: 100%;
    background-color: rgba(255, 255, 255, 0.6); /* opcional para desenfoque */
    display: flex;
    justify-content: center;
    align-items: center;
}
</style>


<div class="container-fluid py-4 px-3 bg-gray-100 min-h-screen">
  <div class="row justify-content-center">
      <div class="col-sm-12">
        <div class="card shadow-lg border-0 bg-white rounded-4 p-4">
               <div class="mb-4 d-flex align-items-center justify-content-between">
                          
               
                <li class="nav-item">
   
                <i class="fas fa-route fa-3x me-2 text-primary"></i>
                <span class="sidenav-normal">Seguimiento a ubicaciones en Rutas</span>
                
                </li>

               
            </div>
           
                  
                    <div id="myGrid" class="ag-theme-alpine position-relative" style="height: 500px;">
                        <div id="gridLoadingOverlay" class="loading-overlay" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
        </div>
      </div>
  </div>
</div>

 <div class="modal fade" id="modalCompararUbicaciones" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-xl">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="tituloCompara">Comparar Ubicaciones</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <p><strong>Distancia aproximada:</strong> <span id="distancia_km">-</span> km</p>
        <p><strong>Ubicación esperada:</strong> <span id="lat1"></span>, <span id="lng1"></span></p>
        <p><strong>Ubicación final (GPS):</strong> <span id="lat2"></span>, <span id="lng2"></span></p>
        
        <div id="mapaComparacion" style="height: 300px;"></div>
      </div>
     <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
      </div>
    </div>
  </div>
</div>   

@endsection

@push('custom-javascript')
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>
  
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtAO2AZBgzC7QaBxnMnPoa-DAq8vaEvUc" async defer onload="googleMapsReady()"></script>

<!-- JS de Select2 -->


    <!-- Nuestro JavaScript unificado -->
    <script
        src="{{ asset('js/sgt/coordenadas/ubicaciones-list.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/ubicaciones-list.js')) }}">
    </script>

@endpush