<div class="modal fade" id="kt_modal_whatsapp_login" tabindex="-1" aria-hidden="true">
    <!--begin::Modal dialog-->
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header pb-0 border-0 justify-content-end">
                <!--begin::Close-->
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::Close-->
            </div>
            <!--begin::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body scroll-y pt-0 pb-15">
                <!--begin::Wrapper-->
                <div class="mw-lg-600px mx-auto">
                    <!--begin::Heading-->
                    <div class="mb-13 text-center">
                        <!--begin::Title-->
                        <h1 class="mb-3">Conectar WhatsApp</h1>
                        <!--begin::Description-->
                        <div class="text-muted fw-semibold fs-5">
                            Envía documentos por
                            <a href="#" class="link-primary fw-bold">WhatsApp</a>
                            con un solo clic, rápido y seguro.
                        </div>
                        <!--end::Description-->
                        <div
                            class="notice d-flex bg-light-danger rounded border-danger border border-dashed mt-10 p-6 waElements"
                            id="waNotice"
                        >
                            <!--begin::Wrapper-->
                            <div class="d-flex flex-stack flex-grow-1">
                                <!--begin::Content-->
                                <div class="fw-semibold">
                                    <div class="fs-6 text-gray-700">
                                        Estimado usuario,
                                        <span class="fw-bold me-1">la conexión con WhatsApp</span>
                                        no está disponible en este momento. Parece que estamos experimentando una
                                        <span class="fw-bold me-1">interrupción temporal</span>
                                        , así que le pedimos que intente nuevamente en unos minutos.
                                    </div>
                                </div>
                                <!--end::Content-->
                            </div>
                            <!--end::Wrapper-->
                        </div>
                        <!--end::Title-->
                        <div id="whatsAppLoding" class="overlay overlay-block waElements mt-15">
                            <div class="overlay-wrapper p-5">
                                <h3>
                                    ¡Conectado!
                                    <span class="fw-bold me-1">Generando código QR.</span>
                                    Un momento, por favor..
                                </h3>
                            </div>
                            <div class="overlay-layer card-rounded bg-dark bg-opacity-5">
                                <div class="spinner-border text-primary" role="status">
                                    <span class="visually-hidden">Espere...</span>
                                </div>
                            </div>
                        </div>
                        <div id="whatsAppLogin" class="waElements">
                            <div class="col-sm-12 mt-10">
                                <!--begin::WhatsApp-->
                                <div class="timeline timeline-border-dashed">
                                    <!--begin::Timeline item-->
                                    <div class="timeline-item">
                                        <!--begin::Timeline line-->
                                        <div class="timeline-line"></div>
                                        <!--end::Timeline line-->
                                        <!--begin::Timeline icon-->
                                        <div class="timeline-icon me-4">
                                            <i class="ki-duotone ki-whatsapp fs-2 text-gray-500">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                        <!--end::Timeline icon-->
                                        <!--begin::Timeline content-->
                                        <div class="mt-2 mb-8">
                                            <!--begin::Timeline heading-->
                                            <div class="overflow-auto pe-3">
                                                <!--begin::Title-->
                                                <div class="fs-5 fw-semibold">Abre WhatsApp en tu teléfono</div>
                                                <!--end::Title-->
                                            </div>
                                            <!--end::Timeline heading-->
                                        </div>
                                        <!--end::Timeline content-->
                                    </div>
                                    <!--end::Timeline item-->
                                    <!--begin::Timeline item-->
                                    <div class="timeline-item">
                                        <!--begin::Timeline line-->
                                        <div class="timeline-line"></div>
                                        <!--end::Timeline line-->
                                        <!--begin::Timeline icon-->
                                        <div class="timeline-icon">
                                            <i class="ki-duotone ki-gear fs-2 text-gray-500">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                        <!--end::Timeline icon-->
                                        <!--begin::Timeline content-->
                                        <div class="mt-2 mb-8">
                                            <!--begin::Timeline heading-->
                                            <div class="pe-3 mb-5">
                                                <!--begin::Title-->
                                                <div class="fs-5 fw-semibold mb-2">
                                                    En Android, toca
                                                    <a href="#" class="text-primary fw-bold me-1">Menú</a>
                                                    . En iPhone, toca
                                                    <a href="#" class="text-primary fw-bold me-1">Ajustes</a>
                                                </div>
                                                <!--end::Title-->
                                            </div>
                                            <!--end::Timeline heading-->
                                        </div>
                                        <!--end::Timeline content-->
                                    </div>
                                    <!--end::Timeline item-->
                                    <!--begin::Timeline item-->
                                    <div class="timeline-item">
                                        <!--begin::Timeline line-->
                                        <div class="timeline-line"></div>
                                        <!--end::Timeline line-->
                                        <!--begin::Timeline icon-->
                                        <div class="timeline-icon">
                                            <i class="ki-duotone ki-monitor-mobile fs-2 text-gray-500">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                            </i>
                                        </div>
                                        <!--end::Timeline icon-->
                                        <!--begin::Timeline content-->
                                        <div class="mt-2 mb-6">
                                            <!--begin::Timeline heading-->
                                            <div class="pe-3 mb-5">
                                                <!--begin::Title-->
                                                <div class="fs-5 fw-semibold mb-2">
                                                    Toca,
                                                    <a href="#" class="text-primary fw-bold me-1">
                                                        Dispositivos vinculados
                                                    </a>
                                                    y, luego
                                                    <a href="#" class="text-primary fw-bold me-1">
                                                        Vincular Dispositivo
                                                    </a>
                                                </div>
                                                <!--end::Title-->
                                            </div>
                                            <!--end::Timeline heading-->
                                        </div>
                                        <!--end::Timeline content-->
                                    </div>
                                    <!--end::Timeline item-->
                                    <!--begin::Timeline item-->
                                    <div class="timeline-item">
                                        <!--begin::Timeline line-->
                                        <div class="timeline-line"></div>
                                        <!--end::Timeline line-->
                                        <!--begin::Timeline icon-->
                                        <div class="timeline-icon">
                                            <i class="ki-duotone ki-scan-barcode fs-2 text-gray-500">
                                                <span class="path1"></span>
                                                <span class="path2"></span>
                                                <span class="path3"></span>
                                                <span class="path4"></span>
                                                <span class="path5"></span>
                                                <span class="path6"></span>
                                                <span class="path7"></span>
                                                <span class="path8"></span>
                                            </i>
                                        </div>
                                        <!--end::Timeline icon-->
                                        <!--begin::Timeline content-->
                                        <div class="mt-2 mb-6">
                                            <!--begin::Timeline heading-->
                                            <div class="pe-3 mb-5">
                                                <!--begin::Title-->
                                                <div class="fs-5 fw-semibold mb-2">
                                                    Escanea el código QR para confirmar
                                                </div>
                                                <!--end::Title-->
                                            </div>
                                            <!--end::Timeline heading-->
                                        </div>
                                        <!--end::Timeline content-->
                                    </div>
                                    <!--end::Timeline item-->
                                </div>
                            </div>
                            <div class="offset-4 col-4">
                                <div class="">
                                    <div class="symbol symbol-150px symbol-lg-160px">
                                        <img
                                            id="WhatsAppQrPicture"
                                            src="/assets/metronic/media/patterns/pattern-1.jpg"
                                            alt="image"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--end::WhatsApp-->
                    </div>
                    <!--end::Heading-->
                    <!--seccion select archivos. begin::Input group-->
                    <div id="whatsAppMessageComposeGeneral" class="waElementsGeneral d-none">
                        <label class="form-label">Enviar a:</label>
                        <input
                            class="form-control d-flex align-items-center"
                            value=""
                            placeholder="Escribe aquí para buscar contactos..."
                            id="kt_tagify_users"
                        />
                        <div class="overflow-auto mt-10 border border-dashed border-gray-300 rounded">
                            <div class="d-flex justify-content-between m-5">
                                <!--begin::Label-->
                                <div class="flex-grow-1">
                                    <span class="fs-6 fw-semibold text-gray-800 d-block">CCP - Carta Porte</span>
                                </div>
                                <!--end::Label-->
                                <!--begin::Switch-->
                                <label class="form-check form-switch form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" checked="checked" />
                                    <span class="form-check-label">Adjuntar</span>
                                </label>
                                <!--end::Switch-->
                            </div>

                            <div class="d-flex justify-content-between m-5">
                                <!--begin::Label-->
                                <div class="flex-grow-1">
                                    <span class="fs-6 fw-semibold text-gray-800 d-block">Boleta de liberación</span>
                                </div>
                                <!--end::Label-->
                                <!--begin::Switch-->
                                <label class="form-check form-switch form-check-solid">
                                    <input class="form-check-input" type="checkbox" value="1" checked="checked" />
                                    <span class="form-check-label">Adjuntar</span>
                                </label>
                                <!--end::Switch-->
                            </div>
                        </div>

                        <!--seccion select archivos. end::Input group-->
                        <!--begin::Input group-->
                        <div class="mt-10 mb-10">
                            <!--begin::Title-->
                            <label class="form-label">Mensaje:</label>
                            <!--end::Title-->
                            <!--begin::Title-->
                            <div class="d-flex">
                                <input
                                    id="kt_whatsapp_text_input"
                                    type="text"
                                    placeholder="Escriba un mensaje..."
                                    class="form-control form-control-solid me-3 flex-grow-1"
                                    autocomplete="off"
                                    name="waMessage"
                                />
                                <button id="kt_whatsapp" class="btn btn-primary flex-shrink-0">
                                    <i class="ki-duotone ki-whatsapp following fs-3">
                                        <span class="path1"></span>
                                        <span class="path2"></span>
                                    </i>
                                    <span class="indicator-label">Enviar mensaje</span>
                                </button>
                            </div>
                            <!--end::Title-->
                        </div>
                        <!--end::Input group-->
                    </div>
                </div>
                <!--end::Wrapper-->
            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
    <!--end::Modal dialog-->
</div>
