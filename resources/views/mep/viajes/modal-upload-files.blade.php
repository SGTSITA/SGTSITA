<div class="modal fade" id="kt_modal_fileuploader" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    Documentos del contenedor
                    <span id="numContenedorLabel"></span>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body scroll-y pt-0 pb-15">
                <div class="mw-lg-600px mx-auto">
                    <div class="mb-13 text-center">
                        <div class="text-muted fw-semibold fs-5">
                            Documento:
                            <h1 class="mb-3" id="titleFileUploader"></h1>
                            <div class="mb-2 fv-row">
                                <label class="d-flex align-items-center form-label mb-3">
                                    <span
                                        class="ms-1 fs-4"
                                        id="labelTitleDoc"
                                        data-bs-toggle="tooltip"
                                        title="Documento que desea cargar"
                                    >
                                        Tipo Documento
                                    </span>
                                </label>

                                <div data-kt-buttons="true">
                                    <div class="row mb-2"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="scroll-y"
                        id="kt_catalog_clients"
                        data-kt-scroll="true"
                        data-kt-scroll-activate="{default: false, lg: true}"
                        data-kt-scroll-max-height="auto"
                        data-kt-scroll-dependencies="#kt_modal_new_address_header"
                        data-kt-scroll-wrappers="#kt_modal_new_address_scroll"
                        data-kt-scroll-offset="300px"
                        style="max-height: 447px"
                    >
                        <div id="content-file-input">
                            <input type="file" name="files" id="fileuploader" />
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
