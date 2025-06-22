@extends('layouts.app')

@section('template_title', 'Ver Coordenadas')


@section('content')
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
                <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalFiltros" id="btnEditarFiltros">
                    Editar Filtros
                </button>
             <button id="btnDetener2" class="btn btn-danger mt-2" style="display: none;">
  <i class="bi bi-pause-circle"></i> Detener actualización
</button>
                <h3 class="text-xl font-semibold text-center mb-0" id="tituloSeguimiento">Seguimiento Convoys</h3>
            </div>
            <div id="map" 
                    style="height: 800px; width: 100%;" 
                    class="rounded-4 border-4 border-blue-500 shadow-md">
                </div>
        </div>
    </div>
</div>

<div class="modal fade" id="filtroModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form id="filtroForm">
        <div class="modal-body">
          <button type="button" class="btn-close" id="btnCerrarModal" data-bs-dismiss="modal" aria-label="Close"></button>
      <button id="btnDetener" class="btn btn-danger mt-2" style="display: none;">
  <i class="bi bi-pause-circle"></i> Detener actualización
</button>
    <!-- Nav tabs -->
    <ul class="nav nav-tabs" id="filtroTabs" role="tablist">
      <li class="nav-item" role="presentation">
        <button class="nav-link active" id="convoy-tab" data-bs-toggle="tab" data-bs-target="#filtro-convoy" data-id="Convoys" type="button" role="tab">
          Filtrar por Convoy
        </button>
      </li>
      <li class="nav-item" role="presentation">
        <button class="nav-link" id="contenedor-tab" data-bs-toggle="tab" data-bs-target="#filtro-contenedor" data-id="Contenedores" type="button" role="tab">
          Filtrar por Contenedor
        </button>
      </li>
       <li class="nav-item" role="presentation">
        <button class="nav-link" id="equipo-tab" data-bs-toggle="tab" data-bs-target="#filtro-Equipo"  data-id="Equipos" type="button" role="tab">
          Equipo
        </button>
      </li>
    </ul>

    <!-- Tab panes -->
    <div class="tab-content pt-3">
      <!-- CONVOY TAB -->
      <div class="tab-pane fade show active" id="filtro-convoy" role="tabpanel">
        <div class="mb-3">
          <label for="convoys" class="form-label">Convoy</label>
          <select class="form-select" name="conboy" id="convoys">
            <option value="">Seleccione un convoy</option>
          </select>
        </div>
      </div>

      <!-- CONTENEDOR TAB -->
      <div class="tab-pane fade" id="filtro-contenedor" role="tabpanel">
        <div class="mb-3 position-relative">
          <label for="contenedor-input" class="form-label">Contenedores</label>
          <input type="text" class="form-control" id="contenedor-input" oninput="mostrarSugerencias()" placeholder="Buscar contenedor...">
          <div id="sugerencias" style="border: 1px solid #ccc; max-height: 150px; overflow-y: auto; display: none; position: absolute; background: white; z-index: 1050; width: 100%;"></div>
          <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="agregarContenedor()">Agregar</button>
          <div id="contenedores-seleccionados" class="mt-2"></div>
          <input type="hidden" name="contenedores" id="contenedores">
          <input type="hidden" id="ItemsSelects" name="ItemsSelects">
        </div>
      </div>
    </div>
       <div class="tab-pane fade" id="filtro-Equipo" role="tabpanel">
        <div class="mb-3 position-relative">
          <label for="Equipo" class="form-label">Equipo</label>
          <select class="form-select" name="equipo" id="Equipo">
            <option value="">Seleccione un Equipo</option>
          </select>
        </div>
      </div>

    <!-- Tipo búsqueda común -->
    <div class="mb-3 mt-2">
      <label for="tipo" class="form-label">Tipo Búsqueda</label>
      <select class="form-select" name="tipo" id="tipo">
        <option value="Global" selected>Global</option>
        <option value="skyGps">skyGps</option>
      </select>
    </div>

    <!-- Botones -->
    <div class="modal-footer">
      <button type="button" class="btn btn-secondary" onclick="limpiarFiltros()">Limpiar</button>
      <button type="submit" class="btn btn-primary">Aplicar Filtro</button>
    </div>
        </div>
      </form>
    </div>
  </div>
</div>

<div class="modal fade" id="modalInfoViaje" tabindex="-1" aria-labelledby="modalInfoViajeLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg">
    <div class="modal-content shadow-lg rounded-4">
      <div class="modal-header bg-primary text-white rounded-top-4">
        <h5 class="modal-title" id="modalInfoViajeLabel">
          <i class="bi bi-truck-front-fill me-2"></i> Información del Viaje
        </h5>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body" id="contenidoModalViaje">
        <!-- Aquí se insertará el contenido dinámico -->
      </div>
      <div class="modal-footer bg-light rounded-bottom-4">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle"></i> Cerrar
        </button>
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


<!-- JS de Select2 -->


    <!-- Nuestro JavaScript unificado -->
    <script
        src="{{ asset('js/sgt/coordenadas/coordenadasRastreoGPS.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/coordenadasRastreoGPS.js')) }}">
    </script>

@endpush
