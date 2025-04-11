<div class="modal fade" id="operadoresModal_Edit{{ $operador->id }}" tabindex="-1"
    aria-labelledby="operadoresModal_Edit{{ $operador->id }}Label" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Editar Operadores</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('update.operadores', $operador->id) }}" id=""
                enctype="multipart/form-data" role="form">
                <input type="hidden" name="_method" value="PATCH">

                @csrf

                <div class="modal-body">
                    <div class="row">

                        <div class="col-12 form-group">
                            <label for="name">Nombre Completo*</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt=""
                                        width="25px">
                                </span>
                                <input name="nombre" id="nombre" type="text" class="form-control"
                                    value="{{ $operador->nombre }}">
                            </div>
                        </div>
                        <div class="col-6 form-group">
                            <label for="curp">CURP *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">

                                </span>
                                <input name="curp" id="curp" type="text" class="form-control"
                                    value="{{ $operador->curp }}" required>
                            </div>
                        </div>


                        <div class="col-6 form-group">
                            <label for="name">correo *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/sobre.png.webp') }}" alt="" width="25px">
                                </span>
                                <input name="correo" id="correo" type="email" class="form-control"
                                    value="{{ $operador->correo }}">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Telefono *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/telefono.png.webp') }}" alt="" width="25px">
                                </span>
                                <input name="telefono" id="telefono" type="number" class="form-control"
                                    value="{{ $operador->telefono }}">
                            </div>
                        </div>

                        <div class="col-12 form-group">
                            <label for="name">Direccion *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/mapa-de-la-ciudad.webp') }}" alt=""
                                        width="25px">
                                </span>
                                <input name="domicilio" id="domicilio" type="text" class="form-control"
                                    value="{{ $operador->domicilio }}">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Fecha nacimiento *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                </span>
                                <input name="fecha_nacimiento" id="fecha_nacimiento" type="date" class="form-control"
                                    value="{{ $operador->fecha_nacimiento }}">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Acceso *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/iniciar-sesion.png') }}" alt=""
                                        width="25px">
                                </span>
                                <input name="acceso" id="acceso" type="text" class="form-control"
                                    value="{{ $operador->acceso }}">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Tipo Sangre *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/sangre.png') }}" alt="" width="25px">
                                </span>
                                <input name="tipo_sangre" id="tipo_sangre" type="text" class="form-control"
                                    value="{{ $operador->tipo_sangre }}">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">NNS *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px">
                                </span>
                                <input name="nss" id="nss" type="text" class="form-control"
                                    value="{{ $operador->nss }}">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Recomendacion *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/megafono.webp') }}" alt="" width="25px">
                                </span>
                                <input name="recomendacion" id="recomendacion" type="text" class="form-control"
                                    value="{{ $operador->recomendacion }}">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Comprobante Domicilio *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/quotes.webp') }}" alt="" width="25px">
                                </span>
                                <input name="comprobante_domicilio" id="comprobante_domicilio" type="file"
                                    class="form-control">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">INE *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/business-card-design.webp') }}" alt=""
                                        width="25px">
                                </span>
                                <input name="ine" id="ine" type="file" class="form-control">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Cedula Fiscal *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/catalogo.webp') }}" alt="" width="25px">
                                </span>
                                <input name="cedula_fiscal" id="cedula_fiscal" type="file" class="form-control">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Licencia Conducir *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/factura.png.webp') }}" alt=""
                                        width="25px">
                                </span>
                                <input name="licencia_conducir" id="licencia_conducir" type="file"
                                    class="form-control">
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Foto *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/imagen.webp') }}" alt="" width="25px">
                                </span>
                                <input name="foto" id="foto" type="file" class="form-control">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

@if (session('curp_error'))
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: '{{ session('curp_error') }}',
            confirmButtonText: 'Entendido'
        });

        /
        @if (isset($operador))
            const modal = new bootstrap.Modal(document.getElementById('operadoresModal_Edit{{ $operador->id }}'));
            modal.show();
        @endif
    </script>
@endif
