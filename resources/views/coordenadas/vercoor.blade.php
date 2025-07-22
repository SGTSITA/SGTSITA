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
                <h3 class="text-xl font-semibold text-center mb-0">Coordenadas por Pregunta</h3>
            </div>
            <div id="map" 
                    style="height: 800px; width: 100%;" 
                    class="rounded-4 border-4 border-blue-500 shadow-md">
                </div>
        </div>
    </div>
</div>



<div class="modal fade" id="filtroModal" tabindex="-1" aria-labelledby="filtroModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filtroModalLabel">Filtros de Búsqueda</h5>
        <!-- Botón de cierre del modal -->
        <button type="button" class="btn-close" id="btnCerrarModal" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <form id="formFiltros">
             <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
          </div>
          <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha Fin</label>
            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
          </div>
         
           <div class="mb-3 position-relative">
            <label for="contenedor-input" class="form-label">Contenedores</label>
            <input type="text" class="form-control" id="contenedor-input" oninput="mostrarSugerencias()" placeholder="Buscar contenedor...">
            <div id="sugerencias" style="border: 1px solid #ccc; max-height: 150px; overflow-y: auto; display: none; position: absolute; background: white; z-index: 1050; width: 100%;"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="agregarContenedor()">Agregar</button>
            <div id="contenedores-seleccionados" class="mt-2"></div>
            <input type="hidden" name="contenedores" id="contenedores">
          </div>

          <div class="mb-3">
            <label for="proveedor" class="form-label">Proveedor</label>
            <select class="form-select" name="proveedor" id="proveedor">
              <option value="">Seleccione un proveedor</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="cliente" class="form-label">Cliente</label>
            <select class="form-select" name="cliente" id="cliente">
              <option value="">Seleccione un cliente</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="subcliente" class="form-label">Subcliente</label>
            <select class="form-select" name="subcliente" id="subcliente">
              <option value="">Seleccione un subcliente</option>
            </select>
          </div>
          
          <div class="mb-3">
            <label for="ubicacion-toggle" class="form-label">Ubicación</label>
            <div class="form-check form-switch">
              <input class="form-check-input" type="checkbox" role="switch" id="ubicacion-toggle" checked aria-checked="true">
              <label class="form-check-label" id="ubicacion-texto" for="ubicacion-toggle">Última ubicación</label>
            </div>
          </div>

          <div class="modal-footer">
            <button type="button" onclick="limpiarFiltros()" class="btn btn-secondary">
              Limpiar Filtros
            </button>
            <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
          </div>
        </form>
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

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- JS de Select2 -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <!-- Nuestro JavaScript unificado -->
    <script
        src="{{ asset('js/sgt/coordenadas/coordenadasver.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/coordenadasver.js')) }}">
    </script>

    <!-- SweetAlert para mostrar mensajes -->
    <script>
       
       </script>
@endpush
