@extends('layouts.usuario_externo')



@section('WorkSpace')
    
<style>
    input[type="date"], select.form-select {
  height: 35px; /* Ajusta el valor según necesites */
  padding: .375rem .75rem; /* Igualar el padding de otros campos */
}
</style>
<script>
    const idCliente = @json($idCliente);
</script>
<div id="estadoCuestionarios" style="display: none;">
        <input type="hidden" id="estadoC" name="estadoC" value="0">
        <input type="hidden" id="estadoB" name="estadoB" value="0">
        <input type="hidden" id="estadoF" name="estadoF" value="0">
    </div>
    <input type="hidden" id="idCotizacionCompartir" value="">
    <input type="hidden" id="idAsignacionCompartir" value="">
    <!-- Modal Coordenadas con Tabs -->
<div class="modal" id="modalCoordenadas" tabindex="-1" style="display:none;">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content p-3">
      <h5>Compartir coordenadas</h5>
        <div class="form-group">
            <label for="optipoCuestionario">Seleccione tipo de cuestionario</label>
            <select id="optipoCuestionario" name="tipoCuestionario" class="form-control">
                <option value="" disabled selected>Seleccione tipo</option>
                <option value="b">Burrero</option>
                
            </select>
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
                @include('emails.email-coordenadas')
      </div>

      <!-- Tab contenido: WHATSAPP -->
      <div id="tab-whatsapp" class="tab-content" style="display: none;">
            

            <label>Contenedor:</label>
            <div id="wmensajeText" class="mb-2"></div>

            <label>Enlace para compartir por WhatsApp:</label>
            <input type="text" id="linkWhatsapp" class="form-control mb-2" readonly>

            <button class="btn btn-primary mb-2" onclick="copiarDesdeInput('linkWhatsapp')">📋 Copiar enlace</button>
            <a href="#" id="whatsappLink" class="btn btn-success" target="_blank" onclick="guardarYAbrirWhatsApp(event)">Abrir WhatsApp</a>
        </div>

      <button class="btn btn-secondary mt-2" onclick="cerrarModal()">Cerrar</button>
    </div>
  </div>
</div>
</div>
<div class="container-fluid py-4 px-3 bg-gray-100 min-h-screen">
    <div class="row justify-content-center">
        <div class="col-sm-12">
            <div class="card shadow-lg border-0 bg-white rounded-4 p-4">
                <div class="mb-4 d-flex align-items-center justify-content-between">
                    <h3 class="text-xl font-semibold text-center mb-0">Busqueda de Coordenadas Lista</h3>
                </div>
           
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <h6 class="mb-0">Filtros de Búsqueda</h6>
                    </div>

                    <div class="card-body py-3">
                        <form id="formFiltros">
                        <div class="row g-2">
                         <div class="col-md-2">
                            <label for="contenedor" class="form-label small"># Contenedor</label>
                            <input type="text" class="form-control form-control-sm" name="contenedor" id="contenedor">
                            </div>

                            <!-- <div class="col-md-4">
                            <label for="proveedor" class="form-label small">Proveedor</label>
                            <select class="form-select form-select-sm" name="proveedor" id="proveedor">
                                <option value="">Todos</option>
                            </select>
                            </div> -->
                            <div class="col-md-2">
                            <label for="fecha_inicio" class="form-label small">Fecha Inicio</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_inicio" id="fecha_inicio">
                            </div>

                            <div class="col-md-2">
                            <label for="fecha_fin" class="form-label small">Fecha Fin</label>
                            <input type="date" class="form-control form-control-sm" name="fecha_fin" id="fecha_fin">
                            </div>
                        </div>
                        
                        <div class="row g-2">
                           

                           

                            <div class="col-md-3">
                            <label for="cliente" class="form-label small">Cliente</label>
                            <select class="form-select form-select-sm" name="cliente" id="cliente">
                                <option value="">Todos</option>
                            </select>
                            </div>

                            <div class="col-md-3">
                            <label for="subcliente" class="form-label small">Subcliente</label>
                            <select class="form-select form-select-sm" name="subcliente" id="subcliente">
                                <option value="">Todos</option>
                            </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-center gap-2 mt-3">
                            <button type="button" onclick="limpiarFiltros()" class="btn btn-outline-secondary btn-sm">
                                Limpiar
                            </button>
                            <button type="submit" class="btn btn-primary btn-sm">
                                Buscar
                            </button>
                        </div>

                        </form>
                    </div>
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




<!-- Estilos -->
<style>

 

  #btnVerMapa {
  font-size: 11px;       /* texto más pequeño */
  padding: 2px 6px;      /* menos relleno */
}

#btnCerrar {
  margin-top: -8px;  /* Sube el botón */
}
</style>


    
@endsection

@push('javascript')
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>
 



    <!-- Nuestro JavaScript unificado -->
    <script
        src="{{ asset('js/sgt/coordenadas/extcoordenadascompartir.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/extcoordenadascompartir.js')) }}">
    </script>

    <!-- SweetAlert para mostrar mensajes -->
    <script>
       
       </script>
@endpush
