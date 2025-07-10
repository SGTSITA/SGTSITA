

@extends('layouts.usuario_externo')

@section('WorkSpace')
<style>
.switch {
  position: relative;
  display: inline-block;
  width: 200px; /* Ancho mayor para incluir el texto */
  height: 30px;
}

.switch input {
  opacity: 0;
  width: 0;
  height: 0;
}

.slider {
  position: absolute;
  cursor: pointer;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: #ccc;
  transition: 0.4s;
  border-radius: 34px;
  display: flex;
  align-items: center;
  justify-content: center;
  color: white;
  font-weight: bold;
}

.slider span {
  position: absolute;
  transition: 0.4s;
  font-size: 14px;
}

.slider:before {
  position: absolute;
  content: "";
  height: 22px;
  width: 22px;
  border-radius: 50%;
  background-color: white;
  transition: 0.4s;
  left: 4px;
}

input:checked + .slider {
  background-color: #4CAF50;
}

input:checked + .slider:before {
  transform: translateX(170px); /* Mueve el círculo completamente a la derecha */
}

input:checked + .slider #ubicacion-texto {
  transform: translateX(80px); /* Mueve el texto hacia la derecha cuando activado */
}

input:not(:checked) + .slider #ubicacion-texto {
  transform: translateX(-80px); /* Mueve el texto a la izquierda cuando desactivado */
}
.btn-close {
    filter: invert(1); /* Invierte el color (útil en fondos blancos) */
}
</style>

<div class="container-fluid py-4 px-3 bg-gray-100 min-h-screen">
  <div class="row justify-content-center">
      <div class="col-sm-12">
            <div class="card shadow-lg border-0 bg-white rounded-4 p-4">
                
              <div class="mb-4 d-flex align-items-center justify-content-between">
                     <li class="nav-item">
   
                <i class="fas fa-route fa-3x me-2 text-primary"></i>
                <span class="sidenav-normal">Comparativa de ubicaciones</span>
                
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

@push('javascript')

 <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <script
        src="{{ asset('js/sgt/coordenadas/ubicaciones-listcontenedores.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/ubicaciones-listcontenedores.js')) }}">
    </script>
@endpush


