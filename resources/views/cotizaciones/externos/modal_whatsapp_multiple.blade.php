<div class="modal fade" id="modalCompartirWhatsappMultiple" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header py-4 bg-light">
                <div class="d-flex align-items-center">
                    <i class="fab fa-whatsapp text-success fs-1 me-3"></i>
                    <div>
                        <h4 class="modal-title fw-bold text-gray-900 mb-0">Compartir información por WhatsApp</h4>
                        <span class="text-muted fs-7" id="waMultipleContenedoresCount">0 contenedores seleccionados</span>
                    </div>
                </div>
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-outline ki-cross fs-1"></i>
                </div>
            </div>

            <div class="modal-body py-4">
                <div class="alert alert-dismissible bg-light-primary border border-primary d-flex flex-column flex-sm-row p-3 mb-4">
                    <i class="ki-outline ki-information-5 fs-2hx text-primary me-3 mb-2 mb-sm-0"></i>
                    <div class="d-flex flex-column pe-0 pe-sm-10">
                        <span class="fw-semibold text-gray-800 fs-7">
                            Se ha generado la información de los contenedores seleccionados sin adjuntar documentos. Puedes revisar o editar el texto antes de abrir WhatsApp.
                        </span>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold text-gray-700 fs-7">Mensaje para WhatsApp:</label>
                    <textarea class="form-control form-control-solid font-monospace fs-7" id="waTextoMensajeMultiple" rows="12" style="white-space: pre-wrap; font-family: monospace, sans-serif; resize: vertical;"></textarea>
                </div>
            </div>

            <div class="modal-footer py-3 d-flex justify-content-between">
                <div>
                    <button type="button" class="btn btn-sm btn-light-primary" id="btnCopiarTextoWhatsapp">
                        <i class="ki-outline ki-copy fs-5 me-1"></i> Copiar texto
                    </button>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="button" class="btn btn-sm btn-success" id="btnEnviarWhatsappMultiple">
                        <i class="fab fa-whatsapp fs-5 me-1"></i> Abrir WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
