<div class="flex-grow-1">
    <div class="fv-row row mb-3">
        <div class="col-12 min-w-450px">
            <div class="border border-gray-300 border-dashed rounded min-w-450px py-3 px-4 me-6 mb-3">
                <div class="d-flex align-items-center">
                    <div class="fs-4 fw-bold" id="proveedorName">Sub Cliente:</div>
                </div>
                <select class="form-select subcliente d-inline-block" id="id_subcliente" name="id_subcliente">
                    <option value="">Seleccionar subcliente</option>
                </select>
            </div>
        </div>
        @can('mec-elegir-proveedor')
            <div class="col-12 min-w-450px">
                <div class="border border-gray-300 border-dashed rounded min-w-450px py-3 px-4 me-6 mb-3">
                    <div class="row">
                        <div class="col-12 mb-5">
                            <div class="d-flex align-items-center">
                                <div class="fs-4 fw-bold" id="proveedorName">Proveedor:</div>
                            </div>
                            <select class="form-select subcliente d-inline-block" id="id_proveedor" name="id_proveedor">
                                <option value="">Seleccionar proveedor</option>
                                @foreach ($proveedores as $p)
                                    <option
                                        value="{{ $p->id }}"
                                        @if ($action == 'editar' && $cotizacion?->id_empresa == $p->id) selected @endif
                                    >
                                        {{ $p->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12 mb-5">
                            <div class="d-flex align-items-center">
                                <div class="fs-4 fw-bold" id="proveedorName">Transportista:</div>
                            </div>
                            <select
                                class="form-select subcliente d-inline-block"
                                id="id_transportista"
                                name="id_transportista"
                            >
                                <option value="">Seleccionar transportista</option>
                                @foreach ($transportista as $tr)
                                    <option
                                        value="{{ $tr->id }}"
                                        @if ($action == 'editar' && $cotizacion?->id_proveedor == $tr->id) selected @endif
                                    >
                                        {{ $tr->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        @endcan
    </div>
    <div class="mb-2"></div>
    <div class="text-muted fs-7">Ingrese los datos del contenedor que se solicitan a continuación</div>
    <div class="mb-2"></div>
    <div class="fv-row row mb-3">
        <div class="col-6">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-map fs-1 text-gray-650 active">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input
                        @if ($action == 'editar') value="{{ $cotizacion->origen }}" @endif
                        type="text"
                        class="form-control"
                        id="origen"
                        autocomplete="off"
                        placeholder=""
                    />
                    <label for="origen" class="text-gray-700">Origen</label>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-route fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input
                        @if ($action == 'editar') value="{{ $cotizacion->destino }}" @endif
                        type="text"
                        class="form-control"
                        id="destino"
                        autocomplete="off"
                        placeholder=""
                    />
                    <label for="destino" class="text-gray-700">Destino</label>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" value="MEC" id="origen_captura" name="origen_captura" />
    <div class="fv-row row mb-3">
        <div class="col-6">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-logistic fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                        <span class="path7"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input
                        type="text"
                        class="form-control"
                        autocomplete="off"
                        id="num_contenedor"
                        placeholder=""
                        @if ($action == 'editar') value="{{ $cotizacion->DocCotizacion->num_contenedor }}" @endif
                        oninput="changeTag('tagContenedor', this.value)"
                    />
                    <label for="num_contenedor" class="text-gray-700">Número de Contenedor</label>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-delivery-3 fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input
                        @if ($action == 'editar') value="{{ $cotizacion->tamano }}" @endif
                        type="text"
                        class="form-control"
                        autocomplete="off"
                        id="tamano"
                        placeholder=""
                        oninput="allowOnlyDecimals(event)"
                    />
                    <label for="tamano" class="text-gray-700">Tamaño de Contenedor</label>
                </div>
            </div>
        </div>
        <div class="col-6 d-none">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-delivery-2 fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                        <span class="path7"></span>
                        <span class="path8"></span>
                        <span class="path9"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input
                        type="text"
                        class="form-control"
                        autocomplete="off"
                        value="22"
                        id="peso_reglamentario"
                        placeholder=""
                        oninput="allowOnlyDecimals(event)"
                    />
                    <label for="peso_reglamentario" class="text-gray-700">Peso de Reglamentario</label>
                    <input
                        type="text"
                        class="form-control"
                        autocomplete="off"
                        id="sobrepeso"
                        placeholder=""
                        value="0"
                        oninput="allowOnlyDecimals(event)"
                    />
                    <input
                        type="text"
                        class="form-control"
                        autocomplete="off"
                        id="precio_sobre_peso"
                        placeholder=""
                        value="0"
                        oninput="allowOnlyDecimals(event)"
                    />
                    <input
                        type="text"
                        class="form-control"
                        autocomplete="off"
                        id="precio_tonelada"
                        placeholder=""
                        value="0"
                        oninput="allowOnlyDecimals(event)"
                    />
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-delivery-2 fs-2">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                        <span class="path7"></span>
                        <span class="path8"></span>
                        <span class="path9"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input
                        type="text"
                        @if ($action == 'editar') value="{{ $cotizacion->peso_contenedor }}" @endif
                        class="form-control"
                        autocomplete="off"
                        id="peso_contenedor"
                        placeholder=""
                        oninput="allowOnlyDecimals(event)"
                    />
                    <label for="peso_contenedor" class="text-gray-700">Peso de Contenedor</label>
                </div>
            </div>
        </div>
        <div class="col-6"></div>
        <div class="col-6">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-calendar-8 fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input
                        @if ($action == 'editar') value="{{ $cotizacion->fecha_modulacion }}" @endif
                        type="text"
                        class="form-control fechas"
                        autocomplete="off"
                        id="fecha_modulacion"
                        name="fecha_modulacion"
                        placeholder=""
                        oninput="allowOnlyDecimals(event)"
                    />
                    <label for="fecha_modulacion" class="text-gray-700">Fecha Modulación</label>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="input-group mb-5">
                <span class="input-group-text" id="basic-addon1">
                    <i class="ki-duotone ki-calendar-8 fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                        <span class="path3"></span>
                        <span class="path4"></span>
                        <span class="path5"></span>
                        <span class="path6"></span>
                    </i>
                </span>
                <div class="form-floating">
                    <input
                        @if ($action == 'editar') value="{{ $cotizacion->fecha_entrega }}" @endif
                        type="text"
                        class="form-control fechas"
                        autocomplete="off"
                        id="fecha_entrega"
                        name="fecha_entrega"
                        placeholder=""
                        oninput="allowOnlyDecimals(event)"
                    />
                    <label for="fecha_entrega" class="text-gray-700">Fecha Entrega</label>
                </div>
            </div>
        </div>
    </div>
</div>
