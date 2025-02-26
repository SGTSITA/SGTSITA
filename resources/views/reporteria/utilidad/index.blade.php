@extends('layouts.app')

@section('template_title')
Reporte de Utilidades
@endsection

@section('content')
<div id="miModal" class="modal">
  <div class="modal-content">
   
    <div class="card h-100" style="box-shadow: none !important;">
            <div class="card-header border-bottom border-1 pb-3">
              <div class="row">
                <div class="col-12 d-flex justify-content-between">
                  <div class="d-flex flex-column">
                    <h6 class="font-weight-bold fs-18" style="color:#333335 !important">Detalle de gastos</h6>
                    <span class="text-xs" id="labelContenedor">Contenedor 01018373kin</span>
                  </div>
                  <span class="close" onclick="cerrarModal()">&times;</span>
                </div>
               
              </div>
            </div>
            <div class="card-body">
              <ul class="list-group" id="infoGastos">
                
              </ul>
            </div>
            <div class="card-footer">
                <div class="row">
                    <div class="col-12">
                    <button type="button" class="btn btn-sm w-100 bg-gradient-info" onclick="cerrarModal()" id="close">
                     De acuerdo
                    </button>

                    </div>
                </div>
            </div>
          </div>
  </div>
</div>
    <div class="container-fluid">
        <div class="row">
        

            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h5 id="card_title">
                                Reporte de utilidades
                                <p class="text-sm mb-0">
                                   
                                    <span class="font-weight-bold">Lista de utilidades por contenedor</span> 
                                </p>
                            </h5>

                            <div class="float-right">
                            <button class="btn btn-sm btn-outline" id="btnVerDetalle">Ver Gastos</button>
                                <button type="button" class="btn btn-sm bg-gradient-danger" id="btnVerDetalle1" onclick="exportUtilidades()">
                                    <i class="fa fa-fw fa-money-bill"></i>  Exportar Reporte
                                </button>
                              </div>
                        </div>
                    </div>

                    <div class="card-body">
                    <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 500px"></div>
                      
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('custom-javascript')
<style>
    /* Fondo del modal */
    .modal {
      display: none; /* Oculto por defecto */
      position: fixed;
      z-index: 1000000;
      left: 0;
      top: 0;
      width: 100%;
      height: 100%;
      overflow: auto;
      background-color: rgba(0,0,0,0.5); /* Fondo oscuro semitransparente */
    }

    /* Contenido del modal */
    .modal-content {
      background-color: #fefefe;
      margin: 10% auto;
      padding: 20px;
      border-radius: 10px;
      width: 400px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.2);
      animation: fadeIn 0.3s;
    }

    /* Botón de cerrar */
    .close {
      color: #aaa;
      float: right;
      font-size: 24px;
      
      cursor: pointer;
    }

    .close:hover {
      color: #000;
    }

    .bg-purple-transparent{
        background-color:rgba(137,32,173, 0.1) !important;
        color: rgb(137,32,173) !important;
        font-size:0.75em !important;
    }

    /* Animación */
    @keyframes fadeIn {
      from {opacity: 0;}
      to {opacity: 1;}
    }

  
  </style>

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/reporteria/rpt-utilidades.js') }}?v={{ filemtime(public_path('js/sgt/reporteria/rpt-utilidades.js')) }}"></script>
<script src="{{asset('js/reporteria/genericExcel.js')}}"></script>

<script>
      function mostrarModal() {
    document.getElementById('miModal').style.display = 'block';
  }

  function cerrarModal() {
    document.getElementById('miModal').style.display = 'none';
  }

  // Cerrar modal si el usuario hace clic fuera
  window.onclick = function(event) {
    const modal = document.getElementById('miModal');
    if (event.target === modal) {
      modal.style.display = 'none';
    }
  }

    $(document).ready(()=>{
        getUtilidadesViajes();
    });
</script>
@endpush

