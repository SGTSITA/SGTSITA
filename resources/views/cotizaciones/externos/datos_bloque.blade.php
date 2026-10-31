<!--begin::Timeline content-->
<div class="mb-10">
    <!--begin::Timeline details-->
    <div class="overflow-auto">
        <!--begin::Record-->
        <div class="d-flex align-items-center border border-dashed border-gray-300 rounded min-w-750px px-7 py-3 mb-5">
            <div class="flex-grow-1">
                <div class="mb-5"></div>
                <div class="fv-row row mb-3">
                    <div class="col-8">
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
                                    @if($action=="editar" ) value="{{ $cotizacion->bloque }}" @endif
                                    id="bloque"
                                />
                                <label for="bloque" class="text-gray-700">Núm. Bloque</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="fv-row row mb-3">
                    <div class="col-6">
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
                                    @if($action=="editar" ) value="{{ $cotizacion->bloque_hora_i }}" @endif
                                    id="bloque_hora_i"
                                />
                                <label for="bloque_hora_i" class="text-gray-700">Hora Inicio</label>
                            </div>
                        </div>
                    </div>
                    <div class="col-6">
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
                                    @if($action=="editar" ) value="{{ $cotizacion->bloque_hora_f }}" @endif
                                    id="bloque_hora_f"
                                />
                                <label for="bloque_hora_f" class="text-gray-700">Hora Fin</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!--end::Record-->
    </div>
    <!--end::Timeline details-->
</div>
<!--end::Timeline content-->
