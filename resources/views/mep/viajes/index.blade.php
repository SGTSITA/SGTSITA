@extends('layouts.app')

@section('template_title', 'MEP - Viajes')


@section('content')
    <style>
        .toast-middle-center {
            top: 50% !important;
            left: 50% !important;
            transform: translate(-50%, -50%) !important;
            position: fixed !important;
        }
        #myGrid {
            height: 500px;
            width: 100%;
        }

        #cotTabs .nav-link {
            cursor: pointer;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(255, 255, 255, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 10;
        }
    </style>


    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
            <div class="d-sm-flex justify-content-between">
        <div>
         
        </div>
        <div class="d-flex">
          <div class="dropdown d-inline">
            <a href="javascript:;" class="btn btn-white dropdown-toggle" data-bs-toggle="dropdown" id="navbarDropdownMenuLink2" aria-expanded="false">
              Opciones
            </a>
            <ul class="dropdown-menu dropdown-menu-lg-start px-2 py-3" aria-labelledby="navbarDropdownMenuLink2" style="">
              <li><a class="dropdown-item border-radius-md btnDocs" href="javascript:;" id="btnFileCartaPorteXML" >Archivo: Carta Porte XML</a></li>
              <li><a class="dropdown-item border-radius-md btnDocs" href="javascript:;" id="btnFileCartaPortePDF" >Archivo: Carta Porte PDF</a></li>
              <li><a class="dropdown-item border-radius-md" href="javascript:;" id="abrirModalBtn">Asignar Operador</a></li>
              
              <li>
                <hr class="horizontal dark my-2">
              </li>
              <!--li><a class="dropdown-item border-radius-md text-danger" href="javascript:;">Remove Filter</a></li-->
            </ul>
          </div>
          <button class="btn btn-icon btn-white ms-2 "  type="button" id="btnFull" disabled>
            <span class="btn-inner--icon"> <i class="fas fa-truck-moving"></i> </span>
            <span class="btn-inner--text" >Convertir a Full</span>
          </button>
        </div>
      </div>
                <div class="card">
                    
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h6 id="card_title">Viajes</span>
                        @can('cotizaciones-create')
                        
                            <div>
                            
                               
                            </div>
                        @endcan
                    </div>

                    <!-- Pestañas sin recargar página -->
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1 flex-row" id="cotTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-status="planeadas">
                                    <i class="fa-solid fa-clipboard-list " style="font-size: 18px;"></i>
                                    <span class="ms-2">Planeadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="finalizadas">
                                    <i class="fa-solid fa-check-circle" style="font-size: 18px;"></i>
                                    <span class="ms-2">Finalizadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="en_espera">
                                    <i class="fa-solid fa-clock" style="font-size: 18px;"></i>
                                    <span class="ms-2">En Espera</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="aprobadas">
                                    <i class="fa-solid fa-thumbs-up" style="font-size: 18px;"></i>
                                    <span class="ms-2">Aprobadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="canceladas">
                                    <i class="fa-solid fa-ban" style="font-size: 18px;"></i>
                                    <span class="ms-2">Canceladas</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Contenedor de AG Grid -->
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
   

    <!-- Modal: Estatus de Documentos -->
    <div class="modal fade" id="modalEstatusDocumentos" tabindex="-1" aria-labelledby="modalEstatusDocumentosLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content shadow-lg border-0 rounded-4">
                <div class="modal-header  text-white rounded-top-4">
                    <h5 class="modal-title d-flex align-items-center" id="modalEstatusDocumentosLabel">
                        <i class="fa-solid fa-folder-open me-2"></i> Estatus de Documentos
                        <span id="tituloContenedor" class="ms-2 t fw-bold"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3" id="estatusDocumentosBody">
                        {{-- Aquí se insertan dinámicamente los checkboxes --}}
                    </div>
                </div>

                <div class="modal-footer border-top-0 d-flex justify-content-end px-4 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>
    <!-- Modal: Cambio de Estatus -->
    <div class="modal fade" id="modalCambioEstatus" tabindex="-1" aria-labelledby="modalCambioEstatusLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
                <form id="formCambioEstatus" method="POST" action="" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="_method" value="PATCH">

                    <div class="modal-header text-white py-3">
                        <h5 class="modal-title d-flex align-items-center" id="modalCambioEstatusLabel">
                            <i class="fa-solid fa-sync-alt me-2"></i> Cambio de Estatus
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>

                    <div class="modal-body px-4 py-3">
                        <div class="mb-3">
                            <label for="estatus" class="form-label fw-bold text-dark">
                                Seleccione el nuevo estatus <span class="text-danger">*</span>
                            </label>
                            <div class="input-group rounded-3 shadow-sm">
                                <select class="form-select border-start-0" name="estatus" id="estatus" required>
                                    <option value="">Seleccionar Estatus</option>
                                    <option value="Pendiente"> Pendiente</option>
                                    <option value="Aprobada"> Aprobada</option>
                                    <option value="Cancelada"> Cancelada</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top-0 px-4 py-3 d-flex justify-content-end">
                        <button type="button" class="btn btn-outline-secondary me-2" data-bs-dismiss="modal">
                            <i class="fa-solid fa-xmark me-1"></i> Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa-solid fa-floppy-disk me-1"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

@include('mep.viajes.modal-asignar')
@include('mep.viajes.modal-alert')
@include('mep.viajes.modal-upload-files')

@endsection

@push('custom-javascript')
    <!-- AG Grid -->
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <link href="{{asset('assets/metronic/fileuploader/font/font-fileuploader.css')}}" rel="stylesheet">
    <link href="{{asset('assets/metronic/fileuploader/jquery.fileuploader.min.css')}}" media="all" rel="stylesheet">
    <link href="{{asset('assets/metronic/fileuploader/jquery.fileuploader-theme-dragdrop.css')}}" media="all" rel="stylesheet">
    <script src="{{asset('assets/metronic/fileuploader/jquery.fileuploader.min.js')}}" type="text/javascript"></script>

    <script src="{{ asset('js/mep/viajes/viajes-fileuploader.js') }}?v={{ filemtime(public_path('js/mep/viajes/viajes-fileuploader.js')) }}"></script>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet"/>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <script
    src="{{ asset('js/mep/viajes/cotizaciones_list.js') }}?v={{ filemtime(public_path('js/mep/viajes/cotizaciones_list.js')) }}">
    </script>

    <!-- SweetAlert para mostrar mensajes -->
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            adjuntarDocumentos();
            getCatalogoOperadorUnidad();
        });
    </script>
@endpush
