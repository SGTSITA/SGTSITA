<div class="modal fade" id="modalWhatsapp" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Compartir enlace por WhatsApp</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div class="alert alert-info d-flex align-items-start mb-4">
                    <i class="fab fa-whatsapp fs-3 me-3 mt-1"></i>
                    <div>
                        <strong>Importante:</strong><br>
                        Para poder compartir el enlace por WhatsApp, es necesario tener
                        <b>WhatsApp Web</b> o la <b>aplicación de WhatsApp</b> instalada y con
                        la <b>sesión iniciada</b> en este dispositivo.
                    </div>
                </div>

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

                    <div class="d-none col-md-12" id="div_terminal_whatsapp">
                        <label class="form-label">Terminal</label>
                        <input type="text" class="form-control" id="wa_terminal">
                    </div>

                    <div class="d-none col-md-12" id="div_servicios_whatsapp">
                        <div class="form-check mt-2">
                            <input class="form-check-input" type="checkbox" id="wa_cambio_sello">
                            <label class="form-check-label">
                                CAMBIO DE SELLO (R1 / A4)
                            </label>
                        </div>
                    </div>

                    <div class="d-none col-md-12" id="div_observaciones_whatsapp">
                        <label class="form-label">Observaciones</label>
                        <textarea class="form-control" rows="3" id="wa_observaciones"></textarea>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button class="btn btn-secondary" data-bs-dismiss="modal" id="btnCancelarWhatsApp">Cerrar</button>
                <button class="btn btn-success" id="btnEnviarWhatsapp">
                    Enviar WhatsApp
                </button>
            </div>

        </div>
    </div>
</div>
