@extends('layouts.usuario_externo')



@section('WorkSpace')
<style>
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
</style>


<div class="container-fluid py-4 px-3 bg-gray-100 min-h-screen">
  <div class="row justify-content-center">
      <div class="col-sm-12">
        <div class="card shadow-lg border-0 bg-white rounded-4 p-4">
               <div class="mb-4 d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"  id="btnNuevoconboy">
                    Nuevo Convoy
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
              <div class="mb-3">
            <label for="fecha_inicio" class="form-label">Fecha Inicio</label>
            <input type="date" class="form-control" name="fecha_inicio" id="fecha_inicio">
          </div>
          <div class="mb-3">
            <label for="fecha_fin" class="form-label">Fecha Fin</label>
            <input type="date" class="form-control" name="fecha_fin" id="fecha_fin">
          </div> 
          <div class="mb-3">
            <label for="nombre" class="form-label">Descripcion</label>
            <input type="textarea" class="form-control" name="nombre" id="nombre">
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
          <a class="nav-link active" href="#" onclick="mostrarTab('mail', event)">📧 Mail</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#" onclick="mostrarTab('whatsapp', event)">📲 WhatsApp</a>
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
@endsection

@push('javascript')
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>


    <script
        src="{{ asset('js/sgt/coordenadas/coordenadasconboys.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/coordenadasconboys.js')) }}">
    </script>

@endpush