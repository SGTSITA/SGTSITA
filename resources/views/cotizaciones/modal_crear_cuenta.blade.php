<div
    class="modal fade"
    id="editarModal{{ $proveedor->id }}"
    tabindex="-1"
    aria-labelledby="exampleModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Cuenta Bancaria</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form
                method="POST"
                action="{{ route('cuenta.proveedores') }}"
                id=""
                enctype="multipart/form-data"
                role="form"
            >
                @csrf

                <div class="modal-body">
                    <div class="row">
                        <input
                            name="id_proveedores"
                            id="id_proveedores"
                            value="{{ $proveedor->id }}"
                            style="display: none"
                            class="form-control"
                        />

                        <div class="col-12 form-group">
                            <label for="name">Nombre Beneficiario*</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px" />
                                </span>
                                <input
                                    name="nombre_beneficiario"
                                    id="nombre_beneficiario"
                                    type="text"
                                    class="form-control"
                                />
                            </div>
                        </div>

                        <div class="col-12 form-group">
                            <label for="name">Nombre Banco*</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px" />
                                </span>
                                <input name="nombre_banco" id="nombre_banco" type="text" class="form-control" />
                            </div>
                        </div>

                        <div class="col-12 form-group">
                            <label for="name">Cuenta Bancaria *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/t debito.webp') }}" alt="" width="25px" />
                                </span>
                                <input name="cuenta_bancaria" id="cuenta_bancaria" type="number" class="form-control" />
                            </div>
                        </div>

                        <div class="col-12 form-group">
                            <label for="name">Clabe *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/t credito.png.webp') }}" alt="" width="25px" />
                                </span>
                                <input name="cuenta_clabe" id="cuenta_clabe" type="number" class="form-control" />
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
