@extends('layouts.usuario_externo')



@section('WorkSpace')
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
</style>


<div class="container-fluid py-4 px-3 bg-gray-100 min-h-screen">
  <div class="row justify-content-center">
      <div class="col-sm-12">
        <div class="card shadow-lg border-0 bg-white rounded-4 p-4">
               <div class="mb-4 d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-primary" data-bs-toggle="modal"  id="btnNuevoconboy">
                    Buscar Convoy
                </button>
             
               
                <li class="nav-item">
   
                <i class="fas fa-route fa-3x me-2 text-primary"></i>
                <span class="sidenav-normal">Mis Convoys Virtuales</span>
                
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

<!-- Modal Mejorado -->
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
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal" onclick="limpiarFormularioConvoy()">Cerrar</button>
          <button type="button" class="btn btn-success" id="btnGuardarContenedores">Guardar</button>
        </div>
        </div>
      </div>
    </div>
  </div>
</div>
    

@endsection

@push('custom-javascript')
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>



<!-- JS de Select2 -->


    <!-- Nuestro JavaScript unificado -->
    <script
        src="{{ asset('js/sgt/coordenadas/encontrarconvoys.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/encontrarconvoys.js')) }}">
    </script>

@endpush