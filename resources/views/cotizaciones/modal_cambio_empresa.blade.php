<div
    class="modal fade"
    id="cambioEmpresa{{ $cotizacion->id }}"
    tabindex="-1"
    aria-labelledby="exampleModalLabel"
    aria-hidden="true"
>
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Cambio de Empresa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form
                method="POST"
                action="{{ route('cambiar_empresa.cotizaciones', $cotizacion->id) }}"
                enctype="multipart/form-data"
                role="form"
                class="form-cambio-empresa-modal"
                data-cotizacion-id="{{ $cotizacion->id }}"
                data-planeado="{{ ($cotizacion->estatus_planeacion == 1 || ($cotizacion->DocCotizacion && $cotizacion->DocCotizacion->Asignaciones()->exists())) ? '1' : '0' }}"
            >
                @csrf
                <input type="hidden" name="_method" value="PATCH" />
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group mb-3">
                            <label for="id_empresa_{{ $cotizacion->id }}">Empresas *</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon-empresa-{{ $cotizacion->id }}">
                                    <img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="35px" />
                                </span>
                                <select
                                    class="form-select cliente d-inline-block select-empresa-modal"
                                    data-cotizacion-id="{{ $cotizacion->id }}"
                                    id="id_empresa_{{ $cotizacion->id }}"
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

                        <div class="form-group mb-3">
                            <label for="id_proveedor_{{ $cotizacion->id }}">Línea de transporte / Proveedor (opcional)</label>
                            <div class="input-group">
                                <span class="input-group-text" id="basic-addon-proveedor-{{ $cotizacion->id }}">
                                    <img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="35px" style="filter: hue-rotate(90deg);" />
                                </span>
                                <select
                                    class="form-select select-proveedor-modal"
                                    id="id_proveedor_{{ $cotizacion->id }}"
                                    name="id_proveedor"
                                >
                                    <option value="">Ninguno</option>
                                </select>
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
