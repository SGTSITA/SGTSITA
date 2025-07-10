@extends('layouts.usuario_externo')

@section('WorkSpace')
<div class="row gx-5 gx-xl-10">
  <div class="col-sm-12 mb-5 mb-xl-10">
    <div class="card card-flush h-lg-100">
      <div class="card-header ">
        <h3 class="card-title align-items-start flex-column">
          <span class="card-label fw-bold text-gray-900">Mis Viajes</span>
          <span class="text-gray-500 mt-1 fw-semibold fs-6">Lista de viajes <span class="text-gray-600 fw-bold">Aprobados</span> </span>
        </h3>
        <div class="card-toolbar">
          <div >
            <button class="btn btn-primary btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-overflow="true">
               Opciones<i class="ki-outline ki-plus fs-1 text-gray-500 me-n1"></i>
            </button>
            <!--begin::Menu 2-->
            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px" data-kt-menu="true">
              <!--begin::Menu item-->
              <div class="menu-item px-3">
                <div class="menu-content fs-6 text-gray-900 fw-bold px-3 py-4">Acciones rápidas</div>
              </div>
              <!--end::Menu item-->
              <!--begin::Menu separator-->
              <div class="separator mb-3 opacity-75"></div>
              <!--end::Menu separator-->
              <!--begin::Menu item-->
              <div class="menu-item px-3">
                <a class="menu-link px-3" onclick="getFilesCFDI()"> Obtener CFDI Carta Porte </a>
              </div>
              <div class="menu-item px-3">
                <a class="menu-link px-3" onclick="cancelarViajeQuestion()"> Cancelar Viaje </a>
              </div>

              <div class="menu-item px-3">
                <a class="menu-link px-3 " onclick="fileManager()"> Ver Documentos </a>
              </div>
              <!--end::Menu item-->
     
              
              <!--div class="menu-item px-3" data-kt-menu-trigger="hover" data-kt-menu-placement="right-start">
                
                <a href="#" class="menu-link px-3" data-bs-toggle="modal" data-bs-target="#kt_modal_top_up_wallet">
                  <span class="menu-title">Ver documentos</span>
                  <span class="menu-arrow"></span>
                </a>
              
               
                <div class="menu-sub menu-sub-dropdown w-175px py-4">
                
                  <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3"> DODA </a>
                  </div>
               
                  <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3"> Pre Alta </a>
                  </div>
                 
                  <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3"> Boleta de liberación </a>
                  </div>
                  <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3"> Formato Carta Porte </a>
                  </div>
                 
                </div>
               
              </div-->
              
              
              <!--begin::Menu separator-->
              <div class="separator mt-3 opacity-75"></div>
              <div class="menu-item px-3">
                <a class="menu-link px-3 " onclick="viajeFull()"> Viajar en Full </a>
              </div>
              <!--end::Menu separator-->
              <!--begin::Menu item-->
              <div class="menu-item px-3">
                <div class="menu-content px-3 py-3">
                  <button class="btn btn-primary  btn-sm px-4" name="btnDocs" id="btnDocs" disabled="true" > Agregar documentos </button>
                </div>
              </div>
              <!--end::Menu item-->
            </div>
            <!--end::Menu 2-->
            <!--end::Menu-->
            <!--begin::Wrapper-->
         
          </div>
        </div>
        
      </div>
      <div class="card-body">
         
            <div class="row">
                <div id="myGrid" class="col-12 ag-theme-quartz mb-6" style="height: 500px">
                   
                </div>

                <div class="modal fade" id="kt_modal_top_up_wallet" tabindex="-1" aria-hidden="true">
                  <!--begin::Modal dialog-->
                  <div class="modal-dialog modal-fullscreen p-9">
                      <!--begin::Modal content-->
                      <div class="modal-content modal-rounded">
                          <!--begin::Modal header-->
                          <div class="modal-header py-7 d-flex justify-content-between">
                              <!--begin::Modal title-->
                              <div class="mb-3">
                        <!--begin::Title-->
                        <h3 class="mb-3">Boleta de liberación</h3>
                        <!--end::Title-->

                        <!--begin::Description-->
                        <div class="text-muted fw-semibold fs-5">
                            Contenedor <span class="fw-bold link-primary">PPPP0009991</span>.
                        </div>
                        <!--end::Description-->
                    </div>
                           
                              <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                                <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>                
                              </div>
                          </div>
                          <div class="modal-body scroll-y m-5">
                       
                          </div>
                      </div>
                  </div>
                </div>

               

              
             
            </div>
      </div>
    </div>
  </div>
</div>

@include('cotizaciones.externos.modal_fileuploader')
@endsection

@push('javascript')
<style>
  .disabled-link {
  pointer-events: none; /* Desactiva los clics */
  color: gray; /* Cambia el estilo visual */
  cursor: default;
}
</style>
<link href="{{asset('assets/metronic/fileuploader/font/font-fileuploader.css')}}" rel="stylesheet">
<link href="{{asset('assets/metronic/fileuploader/jquery.fileuploader.min.css')}}" media="all" rel="stylesheet">
<link href="{{asset('assets/metronic/fileuploader/jquery.fileuploader-theme-dragdrop.css')}}" media="all" rel="stylesheet">
<script src="{{asset('assets/metronic/fileuploader/jquery.fileuploader.min.js')}}" type="text/javascript"></script>
<script src="{{asset('assets/metronic/fileuploader/cotizacion-cliente-externo.js')}}" type="text/javascript"></script>

<script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

<script src="{{ asset('js/sgt/cotizaciones/cotizacion-documentacion.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizacion-documentacion.js')) }}"></script>
<script>
    $(document).ready(()=>{
        getContenedoresPendientes('all');
        adjuntarDocumentos();
    });
</script>
<style>
  .rag-red {
    background-color: #cc222244;
  }
  .rag-green {
      background-color: #198754;
  }
</style>
@endpush