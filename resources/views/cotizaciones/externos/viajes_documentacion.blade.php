@extends('layouts.usuario_externo')

@section('WorkSpace')
    <div class="row gx-5 gx-xl-10">
        <div class="col-sm-12 mb-5 mb-xl-10">
            <div class="card card-flush h-lg-100">
                <div class="card-header">

                    <div class="d-flex flex-column gap-3">

                        <!-- Título -->
                        <div>
                            <h3 class="card-title align-items-start flex-column">
                                <span class="card-label fw-bold text-gray-900">Cargar documentos</span>
                                <span class="text-gray-500 mt-1 fw-semibold fs-6">
                                    Lista de contenedores con
                                    <span class="text-gray-600 fw-bold">Documentos</span>
                                    pendientes
                                </span>
                            </h3>
                        </div>

                        <!-- Filtro -->
                        <div class="d-flex align-items-center gap-3">

                            <label class="fw-semibold text-gray-600 mb-0">
                                Periodo:
                            </label>

                            <div class="position-relative">
                                <input type="text" id="rangoFechasViajes"
                                    class="form-control form-control-sm ps-12 w-275px" placeholder="Seleccionar rango" />

                                <i
                                    class="ki-outline ki-calendar fs-2 position-absolute top-50 start-0 translate-middle-y ms-4"></i>
                            </div>

                        </div>

                    </div>

                    <div class="card-toolbar">
                        <button name="btnDocs1" id="btnDocs1" onclick="fileManager()" disabled="true"
                            class="btnDocs btn btn-sm btn-primary me-3">
                            Ver Documentos
                        </button>
                        <button name="btnDocs" id="btnDocs" disabled="true" class="btnDocs btn btn-sm btn-primary me-3">
                            Cargar Documentos
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div id="myGrid" class="col-12 ag-theme-quartz" style="height: 500px"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('cotizaciones.externos.modal_fileuploader')
@endsection

@push('javascript')
    <link href="{{ asset('assets/metronic/fileuploader/font/font-fileuploader.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/metronic/fileuploader/jquery.fileuploader.min.css') }}" media="all" rel="stylesheet" />
    <link href="{{ asset('assets/metronic/fileuploader/jquery.fileuploader-theme-dragdrop.css') }}" media="all"
        rel="stylesheet" />
    <script src="{{ asset('assets/metronic/fileuploader/jquery.fileuploader.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/metronic/fileuploader/cotizacion-cliente-externo.js') }}" type="text/javascript"></script>

    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <script
        src="{{ asset('js/sgt/cotizaciones/cotizacion-documentacion.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizacion-documentacion.js')) }}">
    </script>
    <script>
        let estatusSearch = 'Documentos Faltantes';
        $(document).ready(() => {
            getContenedoresPendientes(estatusSearch);
            adjuntarDocumentos();
        });
    </script>
@endpush
