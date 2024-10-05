<div class="modal fade" id="eventoModal" tabindex="-1" aria-labelledby="eventoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="eventoModalLabel">Detalles Planeación</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <p id="eventoTitulo"></p>
                <p id="eventoDescripcion"></p>
                <!-- Cambiar los inputs a tipo date -->

                <input type="date" id="eventoFechaStart" class="form-control mb-3">
                <input type="date" id="eventoFechaEnd" class="form-control">

                <input type="hidden" id="urlId" class="form-control">
                <input type="hidden" id="" class="form-control">

                <a id="idCotizacion" class="btn mt-3 btn-sm btn-primary mt-2" target="_blank">Cotizacion</a>

                <a id="idCoordenda" class="btn mt-3 btn-sm btn-warning mt-2" target="_blank">Ver Coordenadas</a>

                <h6 class="mt-3 mb-3">Copiar url</h6>

                <input type="text" id="telOperadorUrl" class="form-control">

                <a id="telOperador" class="btn btn-sm btn-success mt-2" data-toggle="popover" data-content="URL copiada!" data-trigger="manual">Copiar url</a>

                 <!-- Formulario condicional -->
                 <div id="formularioOperador" style="display: none;">
                    <h6 class="mt-3 mb-3">Información del Operador</h6>
                    <p>Viaje Subcontratado</p>

                    <div class="row">

                        <div class="col-6 form-group">
                            <label for="name">Nombre Completo</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">
                                </span>
                                <input name="nombreOperadorSub" id="nombreOperadorSub" type="text" class="form-control">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Telefono </label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/telefono.png.webp') }}" alt="" width="25px">
                                </span>
                                <input name="telefonoOperadorSub" id="telefonoOperadorSub" type="text" class="form-control" maxlength="10" pattern="\d{10}">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Placas </label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/sku.webp') }}" alt="" width="25px">
                                </span>
                                <input name="placasOperadorSub" id="placasOperadorSub" type="text" class="form-control">
                            </div>
                        </div>

                    </div>

                </div>

                @can('planeacion-finalizar')

                <h6 class="mt-3 mb-3">
                    ¿Deseas Finalizar el Viaje?
                </h6>

                <select name="finzalizar_vieje" id="finzalizar_vieje" class="form-control">
                    <option value="">Seleciona la opcion</option>
                    <option value="Finalizado">Si</option>
                </select>

                @endcan


            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-success" id="actualizarFechaBtn">Actualizar</button>
            </div>
        </div>
    </div>
</div>
