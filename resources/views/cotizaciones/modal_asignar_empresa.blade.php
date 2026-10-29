<div class="modal fade" id="cambioEmpresa" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Asignar de Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form
                action="/cotizaciones/cambiar/empresa/"
                enctype="multipart/form-data"
                name="frmAsignarEmpresa"
                id="frmAsignarEmpresa"
                role="form"
            >
                @csrf

                <div class="modal-body">
                    <div class="row">
                        <div class="form-group">
                            <label for="name">Empresas *</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="35px" />
                                </span>
                                <select
                                    class="form-select cliente d-inline-block"
                                    data-toggle="select"
                                    id="id_empresa"
                                    name="id_empresa"
                                    required
                                >
                                    <option value="">Seleccione empresa</option>
                                    @foreach ($empresas as $empresa)
                                        <option value="{{ $empresa->id }}">{{ $empresa->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm bg-gradient-success">Asignar</button>
                </div>
            </form>
        </div>
    </div>
</div>
