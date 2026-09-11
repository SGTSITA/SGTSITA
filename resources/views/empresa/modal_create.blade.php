<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Crear Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('store.empresas') }}" id="" enctype="multipart/form-data" role="form">
                @csrf

                <div class="modal-body">
                    <div class="row">
                        <div class="col-12 form-group">
                            <label for="name">Tipo Empresa*</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px" />
                                </span>
                                <select class="form-select" name="id_tipo_empresa" id="">
                                    @foreach ($tipoEmpresa as $te)
                                        <option value="{{ $te->id }}">{{ $te->tipo_empresa }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-12 form-group">
                            <label for="name">Nombre Empresa*</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px" />
                                </span>
                                <input name="nombre" id="nombre" type="text" class="form-control" />
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">correo *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/sobre.png.webp') }}" alt="" width="25px" />
                                </span>
                                <input name="correo" id="correo" type="email" class="form-control" />
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Telefono *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/telefono.png.webp') }}" alt="" width="25px" />
                                </span>
                                <input name="telefono" id="telefono" type="number" class="form-control" />
                            </div>
                        </div>

                        <div class="col-12 form-group">
                            <label for="name">Direccion *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/mapa-de-la-ciudad.webp') }}" alt="" width="25px" />
                                </span>
                                <input name="direccion" id="direccion" type="text" class="form-control" />
                            </div>
                        </div>

                        <div class="col-12 form-group d-none">
                            <label for="name">Regimen Fiscal*</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px" />
                                </span>
                                <input
                                    name="regimen_fiscal"
                                    id="regimen_fiscal"
                                    type="text"
                                    value="---"
                                    class="form-control"
                                />
                            </div>
                        </div>

                        <div class="col-12 form-group">
                            <label for="name">Regimen Fiscal*</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px" />
                                </span>
                                <select class="form-select" name="id_regimen_fiscal" id="id_regimen_fiscal">
                                    @foreach ($satRegimen as $sat)
                                        <option value="{{ $sat->id }}">
                                            {{ $sat->sat_code }} - {{ $sat->regimen_fiscal }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="col-12 form-group">
                            <label for="name">RFC*</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px" />
                                </span>
                                <input name="rfc" id="rfc" type="text" class="form-control" />
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
