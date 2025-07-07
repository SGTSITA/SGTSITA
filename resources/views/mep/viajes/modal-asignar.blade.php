<!-- Modal Detalles del Viaje -->
<div class="modal fade" id="viajeModal" tabindex="-1" aria-labelledby="viajeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content shadow rounded-4 border-0">
      
      <!-- Encabezado -->
      <div class="modal-header bg-light border-bottom">
        <h5 class="modal-title fw-semibold" id="viajeModalLabel">Resumen del Viaje Seleccionado</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      
      <!-- Cuerpo -->
      <div class="modal-body">
        <form>
          <div class="row g-3 mb-3">
            <div class="col-md-6">
              <label for="operadorSelect" class="form-label">Operador</label>
              <select class="form-select" id="operadorSelect">
                <option selected disabled>Seleccione un operador</option>
                @foreach($operadores as $e)
                    <option value="{{$e->id}}">{{$e->nombre}}</option>
                @endforeach
              </select>
            </div>
            <div class="col-md-6">
              <label for="unidadSelect" class="form-label">Unidad</label>
              <select class="form-select" id="unidadSelect">
                <option selected disabled>Seleccione una unidad</option>
                @foreach($equipo as $e)
                    <option value="{{$e->id}}">{{$e->id_equipo}}</option>
                @endforeach
              </select>
            </div>
          </div>

          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label">Número de Contenedor</label>
              <p id="numeroContenedor" class="form-control-plaintext text-dark"></p>
            </div>
            <div class="col-md-6">
              <label class="form-label">Fecha del Viaje</label>
              <p id="fechaViaje" class="form-control-plaintext text-dark"></p>
            </div>
            <div class="col-md-6">
              <label class="form-label">Origen</label>
              <p id="origenViaje" class="form-control-plaintext text-dark"></p>
            </div>
            <div class="col-md-6">
              <label class="form-label">Destino</label>
              <p id="destinoViaje" class="form-control-plaintext text-dark"></p>
            </div>
            <div class="col-md-6">
              <label class="form-label">Estatus</label>
              <p id="estatusViaje" class="form-control-plaintext text-dark"></p>
            </div>
          </div>
        </form>
      </div>

      <!-- Pie de modal -->
      <div class="modal-footer bg-light border-top">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
          <i class="bi bi-x-circle me-1"></i> Cerrar
        </button>
        <button type="button" class="btn bg-gradient-success" id="btnAsignaOperador" >
          <i class="bi bi-save me-1"></i> Guardar
        </button>
      </div>

    </div>
  </div>
</div>
