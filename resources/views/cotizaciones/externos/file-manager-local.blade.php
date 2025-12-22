@extends('layouts.usuario_externo')

@section('WorkSpace')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">
                Núm Contenedor:
                <span id="numContenedor" style="text-transform: uppercase">{{ $numContenedor }}</span>
            </h3>
        </div>
        <div class="card-body">
            <!--begin::Wrapper-->
            <div class="d-flex flex-stack mb-5">
                <!--begin::Search-->
                <div class="d-flex align-items-center position-relative my-1">
                    <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                    <input type="text" data-kt-docs-table-filter="search"
                        class="form-control form-control-solid w-250px ps-15" placeholder="Buscar archivo" />
                </div>
                <!--end::Search-->

                <!--begin::Toolbar-->
                <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                    <!--begin::Add customer-->
                    <button type="button" class="btn btn-sm btn-primary" name="btnDocs" id="btnDocs">
                        Agregar documento
                    </button>
                    <!--end::Add customer-->
                </div>
                <!--end::Toolbar-->

                <!--begin::Group actions-->
                <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">
                    <div class="fw-bold me-5">
                        Archivos seleccionados:
                        <span class="me-2" data-kt-docs-table-select="selected_count"></span>
                    </div>

                    <button type="button" class="btn btn-sm btn-secondary" name="btnAdjuntos" id="btnAdjuntos">
                        <i class="ki-duotone ki-folder-up fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        Enviar archivos
                    </button>
                    {{--   <button type="button" class="btn btn-sm btn-success ms-2" name="btnWhatsApp" id="btnWhatsApp">
                        <i class="ki-duotone ki-whatsapp fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        WhatsApp
                    </button> --}}

                    <button type="button" class="btn btn-sm btn-success ms-2" name="btnWhatsAppgrupo" id="btnWhatsAppgrupo"
                        onclick='abrirModalWhatsapp()'>
                        <i class="ki-duotone ki-whatsapp fs-2">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        WhatsApp
                    </button>
                </div>
                <!--end::Group actions-->
            </div>
            <!--end::Wrapper-->

            <!--begin::Datatable-->
            <table id="kt_datatable_example_1" class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">
                            <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                                <input class="form-check-input" type="checkbox" data-kt-check="true"
                                    data-kt-check-target="#kt_datatable_example_1 .form-check-input" value="1" />
                            </div>
                        </th>
                        <th>Archivo</th>
                        <th>Tipo</th>
                        <th>Tamaño</th>
                        <th>Fecha documento</th>
                        <th class="text-end min-w-100px"></th>
                    </tr>
                </thead>
                <tbody class="text-gray-600 fw-semibold"></tbody>
            </table>

            <!--end::Datatable-->
        </div>
    </div>
    @include('cotizaciones.externos.modal_fileuploader-local')
    @include('cotizaciones.externos.email_compose')

    <div class="modal fade" id="modalWhatsapp" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Enviar WhatsApp</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <div class="row g-3">

                        <div class="col-md-4">
                            <label class="form-label">Fecha</label>
                            <input type="date" class="form-control" id="wa_fecha">
                        </div>

                        <div class="col-md-8">
                            <label class="form-label">Referencia</label>
                            <input type="text" class="form-control" id="wa_referencia">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Hora inicio</label>
                            <input type="time" class="form-control" id="wa_hora_inicio">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Hora fin</label>
                            <input type="time" class="form-control" id="wa_hora_fin">
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Terminal</label>
                            <input type="text" class="form-control" id="wa_terminal">
                        </div>

                        <div class="col-md-12">
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" id="wa_cambio_sello">
                                <label class="form-check-label">
                                    CAMBIO DE SELLO (R1 / A4)
                                </label>
                            </div>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Observaciones</label>
                            <textarea class="form-control" rows="3" id="wa_observaciones"></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button class="btn btn-success" id="btnEnviarWhatsapp">
                        Enviar WhatsApp
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('javascript')
    <script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
    <link href="{{ asset('assets/metronic/fileuploader/font/font-fileuploader.css') }}" rel="stylesheet" />
    <link href="{{ asset('assets/metronic/fileuploader/jquery.fileuploader.min.css') }}" media="all"
        rel="stylesheet" />
    <link href="{{ asset('assets/metronic/fileuploader/jquery.fileuploader-theme-dragdrop.css') }}" media="all"
        rel="stylesheet" />
    <script src="{{ asset('assets/metronic/fileuploader/jquery.fileuploader.min.js') }}" type="text/javascript"></script>
    <script src="{{ asset('assets/metronic/fileuploader/cotizacion-cliente-externo-local.js') }}" type="text/javascript">
    </script>
    <script
        src="{{ asset('/assets/metronic/js/custom/apps/inbox/compose.js') }}?v={{ filemtime(public_path('/assets/metronic/js/custom/apps/inbox/compose.js')) }}">
    </script>
    <script
        src="{{ asset('js/sgt/cotizaciones/file-manager.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/file-manager.js')) }}">
    </script>
    <script>
        $(document).ready(() => {
            adjuntarDocumentos();
        });
    </script>
@endpush
