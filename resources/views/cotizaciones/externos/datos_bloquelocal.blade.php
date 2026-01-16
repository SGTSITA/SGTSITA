<!--begin::Timeline content-->
<div class="mb-10">
    <!--begin::Timeline details-->
    <div class="overflow-auto">
        <!--begin::Record-->
        <div class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-750px px-7 py-3 mb-5">
            <div class="flex-grow-1">
                <div class="mb-5"></div>
                <div class="fv-row row mb-3">
                    <div class="col-4">
                        <div class="input-group mb-5">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="ki-duotone ki-parcel-tracking fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                    <span class="path3"></span>
                                </i>
                            </span>
                            <div class="form-floating">
                                <input
                                    type="text"
                                    class="form-control"
                                    @if($action=="editar" ) value="{{ $cotizacion->bloque_local }}" @endif
                                    id="bloque"
                                />
                                <label for="bloque" class="text-gray-700">Núm. Bloque</label>
                            </div>
                        </div>
                    </div>
                    <!--F Inicio-->
                    <div class="col-4">
                        <div class="input-group mb-5">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="ki-duotone ki-time fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <div class="form-floating">
                                <input
                                    type="time"
                                    class="form-control"
                                    @if($action=="editar" ) value="{{ $cotizacion->bloque_hora_i_local }}" @endif
                                    id="bloque_hora_i"
                                    required
                                />
                                <label for="bloque_hora_i" class="text-gray-700">Hora Inicio</label>
                            </div>
                        </div>
                    </div>
                    <!--Final-->
                    <div class="col-4">
                        <div class="input-group mb-5">
                            <span class="input-group-text" id="basic-addon1">
                                <i class="ki-duotone ki-time fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <div class="form-floating">
                                <input
                                    type="time"
                                    class="form-control"
                                    @if($action=="editar" ) value="{{ $cotizacion->bloque_hora_f_local }}" @endif
                                    id="bloque_hora_f"
                                    required
                                />
                                <label for="bloque_hora_f" class="text-gray-700">Hora Fin</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fv-row row mb-3">
                    <!-- Número de autorización -->
                    <div class="col-4">
                        <div class="input-group mb-5">
                            <span class="input-group-text">
                                <i class="ki-duotone ki-information fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <div class="form-floating">
                                <input
                                    type="text"
                                    class="form-control"
                                    id="num_autorizacion"
                                    name="num_autorizacion"
                                    @if($action=="editar") value="{{ $cotizacion->DocCotizacion->num_autorizacion }}" @endif
                                />
                                <label class="text-gray-700">Número de Autorización</label>
                            </div>
                        </div>
                    </div>

                    <!-- Selección de Puerto -->
                    <div class="col-4">
                        <div class="input-group mb-5">
                            <span class="input-group-text">
                                <i class="ki-duotone ki-geolocation fs-1">
                                    <span class="path1"></span>
                                    <span class="path2"></span>
                                </i>
                            </span>
                            <div class="form-floating">
                                <select class="form-select" id="puerto" name="puerto" required>
                                    <option value="">Seleccione...</option>
                                    @foreach ($Puertos as $key => $value)
                                        <option
                                            value="{{ $key }}"
                                            @if($action=="editar" && $cotizacion->puerto == $key) selected @endif
                                        >
                                            {{ $value }}
                                        </option>
                                    @endforeach
                                </select>
                                <label class="text-gray-700">Puerto</label>
                            </div>
                        </div>
                    </div>

                    <!-- for por si ay mas opciones despues -->

                    <div class="col-4" id="sub_puerto_container" style="display: none">
                        <label class="fw-bold mb-2">Terminal</label>

                        <div class="d-flex gap-5 align-items-center">
                            @foreach ($opcionesPuertos['LAZARO'] as $key => $value)
                                <label class="form-check form-check-custom form-check-solid d-flex align-items-center">
                                    <input
                                        class="form-check-input me-2"
                                        type="radio"
                                        name="terminal_local"
                                        id="terminal_local"
                                        value="{{ $key }}"
                                        @if($action=='editar' && $cotizacion->DocCotizacion->terminal == $key) checked @endif
                                    />
                                    <span class="form-check-label d-flex align-items-center">
                                        <i class="ki-duotone ki-geolocation fs-2 me-2">
                                            <span class="path1"></span>
                                            <span class="path2"></span>
                                        </i>
                                        {{ $value }}
                                    </span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!--end::Record-->
</div>

<div class="fv-row row mb-3">
    <!--end::Timeline details-->
</div>
<!--end::Timeline content-->
