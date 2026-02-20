<div class="modal fade" id="kt_modal_fileuploader" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
            </div>
            <div class="modal-body scroll-y pt-0 pb-15">
                <div class="mw-lg-600px mx-auto">
                    <div class="mb-13 text-center">
                        <div class="text-muted fw-semibold fs-5">
                            Documentos del contenedor:
                            <select
                                class="form-select mb-2"
                                style="
                                    color: #000000;
                                    font-size: 24px;
                                    font-weight: bold;
                                    text-align: center;
                                    text-align-last: center;
                                "
                                id="selectContenedores"
                            ></select>
                            <h1 class="mb-3 d-none" id="titleFileUploader"></h1>
                            <div class="mb-10 fv-row">
                                <label class="d-flex align-items-center form-label mb-3">
                                    Tipo Documento
                                    <span
                                        class="ms-1"
                                        data-bs-toggle="tooltip"
                                        title="Seleccione el documento que desea cargar"
                                    >
                                        <i class="ki-duotone ki-information-5 text-gray-500 fs-6">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                            <span class="path3"></span>
                                        </i>
                                    </span>
                                </label>

                                <div data-kt-buttons="true">
                                    <div class="row mb-2">
                                        <div class="col">
                                            <label
                                                class="btn btn-outline btn-outline-dashed btn-active-light-primary w-100 p-4 active"
                                            >
                                                <input
                                                    type="radio"
                                                    class="btn-check CheckTypeFile"
                                                    name="CheckTypeFile"
                                                    checked
                                                    value="BoletaLiberacion"
                                                    id="btnFileBoletaLiberacion"
                                                />
                                                <span class="fw-bold fs-3">Boleta Liberación</span>
                                            </label>
                                        </div>
                                        <div class="col">
                                            <label
                                                class="btn btn-outline btn-outline-dashed btn-active-light-primary w-100 p-4"
                                            >
                                                <input
                                                    type="radio"
                                                    class="btn-check CheckTypeFile"
                                                    name="CheckTypeFile"
                                                    value="DODA"
                                                    id="btnFileDODA"
                                                />
                                                <span class="fw-bold fs-3">DODA</span>
                                            </label>
                                        </div>
                                        <div class="col">
                                            <label
                                                class="btn btn-outline btn-outline-dashed btn-active-light-primary w-100 p-4"
                                            >
                                                <input
                                                    type="radio"
                                                    class="btn-check CheckTypeFile"
                                                    name="CheckTypeFile"
                                                    value="CartaPorte"
                                                    id="btnBoletaPatio"
                                                />
                                                <span class="fw-bold fs-4">Boleta Patio</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-text">
                                    La solicitud del viaje se enviará al subir todos los documentos
                                </div>
                            </div>
                        </div>
                    </div>
                    <div
                        class="scroll-y me-n7 pe-7 mt-5"
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
