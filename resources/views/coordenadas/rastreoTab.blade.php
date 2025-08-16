{{-- resources/views/coordenadas/dashboard.blade.php --}}
@extends('layouts.app')

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
  transform: translateX(170px); 
}

input:checked + .slider #ubicacion-texto {
  transform: translateX(80px); 
}

input:not(:checked) + .slider #ubicacion-texto {
  transform: translateX(-80px); 
}
.btn-close {
    filter: invert(1); 
}

    #contenedoreseditar {
        font-size: 0.85rem;
    }
    #contenedoreseditar th,
    #contenedoreseditar td {
        padding: 0.3rem 0.5rem;
        vertical-align: middle;
    }
    #contenedoreseditar thead {
        background-color: #f0f0f0;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }
    .input-alto {
    height: 38px; /* o lo que tú necesites */
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
<div class="container-fluid bg-white">
    <h3 class="mb-3 text-center">📍 Módulo de Rastreo y Gestión</h3>

    {{-- Tabs --}}
    <ul class="nav nav-tabs" id="rastreoTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="tab-rastreo" data-bs-toggle="tab" data-bs-target="#rastreo" type="button" role="tab">Rastreo en Vivo</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-convoys" data-bs-toggle="tab" data-bs-target="#convoys" type="button" role="tab">Gestión de Convoys</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-historial" data-bs-toggle="tab" data-bs-target="#historial" type="button" role="tab">Historial / Reportes</button>
        </li>
         <li class="nav-item" role="presentation">
            <button class="nav-link" id="tab-config" data-bs-toggle="tab" data-bs-target="#config" type="button" role="tab">Configuraciones</button>
        </li>
    </ul>

    <div class="tab-content p-3 border border-top-0" id="rastreoTabsContent">

        {{-- Pestaña Rastreo --}}
        <div class="tab-pane fade show active" id="rastreo" role="tabpanel">
            <div class="row">
                <div class="col-md-9">
                   <div id="map" style="width: 100%; height: 700px;"></div>
                </div>
               <div class="col-md-3 bg-white p-3 rounded shadow-sm">

    <!-- Bloque de buscador y botón -->
    <div class="d-flex align-items-start gap-2 mb-3">
        
        <!-- Buscador -->
        <div class="flex-grow-1">
            <div class="position-relative">
                <input type="text" id="buscadorGeneral" 
                       placeholder="Buscar convoy, contenedor o equipo..." 
                        class="form-control bg-light shadow-sm"
                       style="min-width: 250px;">
                <div id="chipsBusqueda" class="d-flex flex-wrap gap-2 mt-2"></div>
                <div id="resultadosBusqueda" class="dropdown-menu show mt-1" 
                     style="max-height: 200px; overflow-y: auto; width: 100%;">
                </div>
            </div>
        </div>

       </div>

        <div class="border rounded p-2" style="height: 100%; overflow-y: auto;">
            <h5 class="mb-2 text-center bg-light py-2 rounded shadow-sm">Elementos en Rastreo</h5>
            <ul class="list-group" id="ElementosRastreoPanel">
                
            </ul>
        </div>

    </div>
            </div>
        </div>

        {{-- Pestaña Convoys --}}
        <div class="tab-pane fade" id="convoys" role="tabpanel">
           <div class="container-fluid py-4 px-3 bg-gray-100 min-h-screen">
  <div class="row justify-content-center">
      <div class="col-sm-12">
        <div class="card shadow-lg border-0 bg-white rounded-4 p-4">
               <div class="mb-4 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" id="btnNuevoconboy">
                        <i class="bi bi-plus-circle me-1"></i> Nuevo Convoy
                    </button>

                    <button type="button" class="btn btn-info" data-bs-toggle="modal" id="btnBuscarconboy">
                        <i class="bi bi-search me-1"></i> Buscar Convoy
                    </button>
                </div>

                
             <button type="button" class="btn btn-primary d-none" id="btnRastrearconboysSelec">
            <i class="bi bi-map-fill me-1"></i> Rastrear seleccionados
        </button>
             
               
                <li class="nav-item">
   
                <i class="fas fa-route fa-3x me-2 text-primary"></i>
                <span class="sidenav-normal">Convoys Virtuales</span>
                
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
<div class="modal fade" id="modalCambiarEstatus" tabindex="-1" aria-labelledby="estatusModalLabel" aria-hidden="true" data-id="">
  <div class="modal-dialog">
    <form id="formCambiarEstatus">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="estatusModalLabel">Cambiar Estatus</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <label for="nuevoEstatus" class="form-label">Selecciona nuevo estatus:</label>
          <select class="form-select" id="nuevoEstatus" name="nuevoEstatus" required>
            <option value="" selected>-- Selecciona --</option>
            <option value="Activo" selected>Activo</option>
            <option value="Disuelto">Disuelto</option>
           
          </select>
          <input type="hidden" id="idItem" name="idItem" value=""> <!-- Puedes llenar este input dinámicamente -->
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
          <button type="button" class="btn btn-primary"  id="btnGuardarCambios">Guardar Cambios</button>
        </div>
      </div>
    </form>
  </div>
</div>
<div class="modal fade" id="CreateModal" tabindex="-1" aria-labelledby="filtroModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="filtroModalLabel">Crear Convoy Virtual</h5>
        <!-- Botón de cierre del modal -->
        <button type="button" class="btn-close" id="btnCerrarModal" data-bs-dismiss="modal" aria-label="Close"></button>
      
      </div>
      <div class="modal-body">
        <form id="formFiltros" data-edit-id="0">
          <input type="hidden" class="form-control" name="id_convoy" id="id_convoy" >
           <div class="mb-3">
            <label for="no_convoy" class="form-label">No. Convoy</label>
            <input type="text" class="form-control" name="no_convoy" id="no_convoy" readonly>
          </div>
         <div class="row align-items-end">
          <div class="col-md-4">
            <label for="tipo_disolucion" class="form-label">Tipo de disolución</label>
            <select name="tipo_disolucion" id="tipo_disolucion" class="form-select" required>
              <option value="">Seleccione una opción</option>
              <option value="geocerca">Geocerca</option>
              <option value="tiempo">Tiempo</option>
            </select>
          </div>
          <div class="col-md-4">
            <label for="fecha_inicio" class="form-label">Inicio</label>
            <input type="datetime-local" name="fecha_inicio" id="fecha_inicio" class="form-control input-alto">
          </div>
          <div class="col-md-4">
            <label for="fecha_fin" class="form-label">Fin</label>
            <input type="datetime-local" name="fecha_fin" id="fecha_fin" class="form-control input-alto">
          </div>
        </div>

             <div id="geocercaConfig" class="mb-3" style="display: none;">
                <button type="button" class="btn btn-primary" onclick="abrirGeocerca()">Configurar geocerca</button>
             
                <!-- Campos ocultos para guardar lat/lng/radio -->
                <input type="hidden" name="geocerca_lat" id="geocerca_lat">
                <input type="hidden" name="geocerca_lng" id="geocerca_lng">
                <input type="hidden" name="geocerca_radio" id="geocerca_radio">
              </div>
           <div class="col-md-12">
              <label for="nombre" class="form-label">Descripción</label>
              <input type="text" class="form-control" name="nombre" id="nombre">
            </div>
          
           <div class="mb-3 position-relative">
            <label for="contenedor-input" class="form-label">Contenedores</label>
            <input type="text" class="form-control" id="contenedor-input" oninput="mostrarSugerencias()" placeholder="Buscar contenedor...">
            <div id="sugerencias" style="border: 1px solid #ccc; max-height: 150px; overflow-y: auto; display: none; position: absolute; background: white; z-index: 1050; width: 100%;"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="agregarContenedor()">Agregar</button>
            <div id="contenedores-seleccionados" class="mt-2"></div>
            <input type="hidden" name="contenedores" id="contenedores">
            <input type="hidden" id="ItemsSelects" name="ItemsSelects">
          </div>
          <table class="table table-sm table-bordered align-middle text-center" style="display: block;" id="tablaContenedores">
            <thead class="table-light">
                <tr>
                  
                    <th>Contenedor</th>
                    <th style="width: 20%;">Acción</th>
                </tr>
            </thead>
            <tbody id="tablaContenedoresBody">
                <!-- Se llenará dinámicamente -->
            </tbody>
        </table>
          
          <div class="modal-footer">
             <button type="button" class="btn btn-danger text-white" data-bs-dismiss="modal"><i class="fas fa-times"></i> Cancelar</button>
              <button type="submit" class="btn btn-primary text-white" id="btnActualizarEditar"><i class="fas fa-sync-alt"></i> Guardar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>
    <input type="hidden" id="idCotizacionCompartir" value="">
    <input type="hidden" id="idAsignacionCompartir" value="">
    <div class="modal" id="modalCoordenadas" tabindex="-1" style="display:none;">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content p-3">
          <h5>Compartir conboys</h5>
            <div class="form-group">
              
            </div>
          <!-- Tabs -->
          <ul class="nav nav-tabs mb-3">
            
            <li class="nav-item">
              <a class="nav-link active" href="#" onclick="mostrarTab('mail')">📧 Mail</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="#" onclick="mostrarTab('whatsapp')">📲 WhatsApp</a>
            </li>
          </ul>
      
          <!-- Tab contenido: MAIL -->
          <div id="tab-mail" class="tab-content">
                    @include('emails.email-conboys')
          </div>

          <!-- Tab contenido: WHATSAPP -->
          <div id="tab-whatsapp" class="tab-content" style="display: none;">
                

                <label>Se comparte el siguiente no. de Convoy:</label>
                <div id="wmensajeText" class="mb-2"></div>

                          
                
                <a href="#" id="whatsappLink" class="btn btn-success" target="_blank">Abrir WhatsApp</a>
            </div>

          <button class="btn btn-secondary mt-2" onclick="cerrarModal()">Cerrar</button>
        </div>
      </div>
    </div>
    <div class="modal fade" id="modalBuscarConvoy" tabindex="-1" aria-labelledby="modalBuscarConvoyLabel" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content rounded-4">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title" id="modalBuscarConvoyLabel">Buscar Convoy</h5>
      </div>
      <div class="modal-body">

        <form id="formBuscarConvoy" class="mb-3">
          <div class="mb-3">
             <input type="hidden" class="form-control" name="id_convoy" id="id_convoy" >
            <label for="numero_convoy" class="form-label">Número de convoy</label>
            <input type="text" class="form-control" id="numero_convoy" name="numero_convoy" required>
          </div>
          <button type="submit" class="btn btn-primary">Buscar</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="limpiarFormularioConvoy2()">Cerrar</button>
        </form>

        <div id="resultadoConvoy" style="display: none;">
          <hr>
        <div class="border rounded p-3 mb-4">
    <h5 class="text-center text-uppercase border-bottom pb-2 mb-3">Información del convoy</h5>

    <p><strong>Descripción:</strong> <span id="descripcionConvoy"></span></p>

    <div class="d-flex flex-wrap">
        <p class="me-4"><strong>Fecha inicio:</strong> <span id="fechaInicioConvoy"></span></p>
        <p><strong>Fecha fin:</strong> <span id="fechaFinConvoy"></span></p>
      </div>
  </div>

          
            <div class="mb-3 position-relative">
            <label for="contenedor-input" class="form-label">Agregar Contenedores</label>
            <input type="text" class="form-control" id="contenedor-input2" oninput="mostrarSugerencias2()" placeholder="Buscar contenedor...">
            <div id="sugerencias2" style="border: 1px solid #ccc; max-height: 150px; overflow-y: auto; display: none; position: absolute; background: white; z-index: 1050; width: 100%;"></div>
            <button type="button" class="btn btn-sm btn-outline-secondary mt-2" onclick="agregarContenedor2()">Agregar</button>
            <div id="contenedores-seleccionados2" class="mt-2"></div>
            <input type="hidden" name="contenedores" id="contenedores">
            <input type="hidden" id="ItemsSelects" name="ItemsSelects">
          </div>
          <table class="table table-sm table-bordered align-middle text-center" id="tablaContenedoresBuscar">
            <thead class="table-light">
                <tr>
                  
                    <th>Contenedor</th>
                    <th style="width: 20%;">Acción</th>
                </tr>
            </thead>
            <tbody id="tablaContenedoresBodyBuscar">
                <!-- Se llenará dinámicamente -->
            </tbody>
        </table>

         <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="limpiarFormularioConvoy2()">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnGuardarContenedores">Guardar</button>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
        </div>

        {{-- Pestaña Historial --}}
        <div class="tab-pane fade" id="historial" role="tabpanel">
            <h5>Convoys Finalizados</h5>
            <div id="myGridConvoyFinalizados" class="ag-theme-alpine position-relative" style="height: 500px;">
                        <div id="gridLoadingOverlay" class="loading-overlay" style="display: none;">
                            <div class="spinner-border text-primary" role="status">
                                <span class="visually-hidden">Cargando...</span>
                            </div>
                        </div>
                    </div>
        </div>

          <div class="tab-pane fade" id="config" role="tabpanel">
             <div class="text-center mb-4">
    <h3 class="fw-bold text-primary">Configurar Intervalo de Rastreo Automático</h3>
</div>
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <form action="{{ route('scheduler.update') }}" method="POST">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="interval" class="form-label">Intervalo</label>
                            <select name="interval" id="interval" class="form-select">
                                <option value="everyMinute" {{ $intervals->interval == 'everyMinute' ? 'selected' : '' }}>Cada minuto</option>
                                <option value="everyFiveMinutes" {{ $intervals->interval == 'everyFiveMinutes' ? 'selected' : '' }}>Cada 5 minutos</option>
                                <option value="hourly" {{ $intervals->interval == 'hourly' ? 'selected' : '' }}>Cada hora</option>
                                <option value="daily" {{ $intervals->interval == 'daily' ? 'selected' : '' }}>Diario</option>
                                <option value="weekly" {{ $intervals->interval == 'weekly' ? 'selected' : '' }}>Semanal</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
                    </form>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
@push('custom-javascript')
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>
  <script
        src="{{ asset('js/sgt/coordenadas/coordenadasRastreo.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/coordenadasRastreo.js')) }}">
    </script>
    

    <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtAO2AZBgzC7QaBxnMnPoa-DAq8vaEvUc" async defer onload="googleMapsReady()"></script>

@endpush
