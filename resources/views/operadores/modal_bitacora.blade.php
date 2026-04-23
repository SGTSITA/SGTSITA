<div
    class="modal fade"
    id="operadoresModal_bitacora{{ $item->id }}"
    tabindex="-1"
    aria-labelledby="operadoresModal_bitacora{{ $item->id }}Label"
    aria-hidden="true"
>
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Bitacora de Viaje</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body">
                <form
                    method="POST"
                    action="{{ route('update_pago.operadores', $item->id) }}"
                    id=""
                    enctype="multipart/form-data"
                    role="form"
                >
                    <input type="hidden" name="_method" value="PATCH" />
                    <!-- Pasar los valores de dinero_viaje y sueldo_viaje a JavaScript -->
                    <input type="hidden" id="dinero_viaje_{{ $item->id }}" value="{{ $item->dinero_viaje }}" />
                    <input type="hidden" id="sueldo_viaje_{{ $item->id }}" value="{{ $item->sueldo_viaje }}" />

                    @csrf
                    <div class="row estilos_equipo">
                        <div class="col-12">
                            <div class="row">
                                <div class="col-3 mb-3">
                                    <h5 class="titulos_bitacora mb-2">
                                        #
                                        <strong>ID Cotizacion</strong>
                                    </h5>
                                    <p class="mb-4">
                                        <a
                                            type="button"
                                            class="btn"
                                            target="_blank"
                                            href="{{ route('edit.cotizaciones', $item->Contenedor->Cotizacion->id) }}"
                                        >
                                            {{ $item->Contenedor->Cotizacion->id }}
                                        </a>
                                    </p>
                                </div>

                                <div class="col-3 mb-3">
                                    <h5 class="titulos_bitacora mb-2">
                                        #
                                        <strong>Num. Contenedor</strong>
                                    </h5>
                                    <p class="mb-4">
                                        {{ $item->Contenedor->Cotizacion->DocCotizacion->num_contenedor }}
                                    </p>
                                </div>

                                <div class="col-6 mb-3">
                                    <h5 class="titulos_bitacora mb-2">
                                        <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px" />
                                        <strong>Fecha modulación y Fecha entrega</strong>
                                    </h5>
                                    @php
                                        $fecha_modulacion = $item->Contenedor->Cotizacion->fecha_modulacion;
                                        $fecha_timestamp_modulacion = strtotime($fecha_modulacion);
                                        $fecha_formateada_modulacion = date('d \d\e F \d\e\l Y', $fecha_timestamp_modulacion);

                                        $fecha_entrega = $item->Contenedor->Cotizacion->fecha_entrega;
                                        $fecha_timestamp_entrega = strtotime($fecha_entrega);
                                        $fecha_formateada_entrega = date('d \d\e F \d\e\l Y', $fecha_timestamp_entrega);
                                    @endphp

                                    <p class="mb-4">
                                        {{ $fecha_formateada_modulacion }}
                                        <strong>al</strong>
                                        {{ $fecha_formateada_entrega }}
                                    </p>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-3">
                                    <h5 class="titulos_bitacora">
                                        <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px" />
                                        <strong>Viaje</strong>
                                    </h5>
                                    <p class="mb-4">
                                        {{ $item->Contenedor->Cotizacion->origen }}
                                        <strong>a</strong>
                                        {{ $item->Contenedor->Cotizacion->destino }}
                                    </p>
                                </div>

                                <div class="col-3">
                                    <h5 class="titulos_bitacora">
                                        <img src="{{ asset('img/icon/chofer.png') }}" alt="" width="25px" />
                                        <strong>Operador</strong>
                                    </h5>
                                    <p class="mb-4">{{ $operador->nombre }}</p>
                                </div>

                                <div class="col-3">
                                    <h5 class="titulos_bitacora">
                                        <img src="{{ asset('img/icon/depositar.png') }}" alt="" width="25px" />
                                        <strong>Sueldo</strong>
                                    </h5>
                                    <p class="mb-4">${{ number_format($item->sueldo_viaje, 2, '.', ',') }}</p>
                                </div>

                                <div class="col-3">
                                    <h5 class="titulos_bitacora">
                                        <img src="{{ asset('img/icon/billetera.png') }}" alt="" width="25px" />
                                        <strong>Dinero del viaje</strong>
                                    </h5>
                                    <p class="mb-4">${{ number_format($item->dinero_viaje, 2, '.', ',') }}</p>
                                </div>

                                <div class="col-8"></div>

                                <div class="col-12">
                                    <h5 class="titulos_bitacora mb-4">
                                        <img
                                            src="{{ asset('img/icon/user_predeterminado.webp') }}"
                                            alt=""
                                            width="25px"
                                        />
                                        <strong>Comprobantes de gastos</strong>
                                    </h5>
                                    <div class="row">
                                        <div class="col-6 form-group">
                                            <label for="name">Suma gastos</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img
                                                        src="{{ asset('img/icon/depositar.png') }}"
                                                        alt=""
                                                        width="25px"
                                                    />
                                                </span>
                                                <input
                                                    name="resta"
                                                    id="gastos_{{ $item->id }}"
                                                    type="text"
                                                    class="form-control"
                                                    value="{{ $item->sueldo_viaje }}"
                                                    readonly
                                                />
                                            </div>
                                        </div>

                                        <div class="col-6 form-group">
                                            <label id="faltanteRestanteLabel_{{ $item->id }}" for="name">
                                                Faltante / Restante
                                            </label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img
                                                        src="{{ asset('img/icon/depositar.png') }}"
                                                        alt=""
                                                        width="25px"
                                                    />
                                                </span>
                                                <input
                                                    name="resta"
                                                    id="resta_{{ $item->id }}"
                                                    type="text"
                                                    class="form-control"
                                                    value="{{ $item->dinero_viaje }}"
                                                    readonly
                                                />
                                            </div>
                                        </div>

                                        <div class="col-1">
                                            <div class="form-group">
                                                <button
                                                    class="mt-5"
                                                    type="button"
                                                    id="agregarCampo2"
                                                    style="border-radius: 9px; width: 36px; height: 40px"
                                                >
                                                    <i class="fa fa-plus"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div id="camposContainer2">
                                            <div class="campo2 mt-3">
                                                <div class="col-4 form-group">
                                                    <label for="name">Gasolina</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img
                                                                src="{{ asset('img/icon/bomba-de-gas.png') }}"
                                                                alt=""
                                                                width="25px"
                                                            />
                                                        </span>
                                                        <input
                                                            name="gasolina_{{ $item->id }}[]"
                                                            id="gasolina_{{ $item->id }}"
                                                            type="text"
                                                            class="form-control gasolina"
                                                        />
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Casetas</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img
                                                                src="{{ asset('img/icon/guardia.png') }}"
                                                                alt=""
                                                                width="25px"
                                                            />
                                                        </span>
                                                        <input
                                                            name="casetas"
                                                            id="casetas_{{ $item->id }}"
                                                            type="text"
                                                            class="form-control casetas"
                                                        />
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Otros</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img
                                                                src="{{ asset('img/icon/menu.png') }}"
                                                                alt=""
                                                                width="25px"
                                                            />
                                                        </span>
                                                        <input
                                                            name="otros"
                                                            id="otros_{{ $item->id }}"
                                                            type="text"
                                                            class="form-control otros"
                                                        />
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-4 form-group">
                                            <label for="name">Comprobante gasolina</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img
                                                        src="{{ asset('img/icon/factura.png') }}"
                                                        alt=""
                                                        width="25px"
                                                    />
                                                </span>
                                                <input
                                                    name="comprobante_gasolina[]"
                                                    id="comprobante_gasolina"
                                                    type="file"
                                                    class="form-control"
                                                    multiple
                                                />
                                            </div>
                                        </div>

                                        <div class="col-4 form-group">
                                            <label for="name">Comprobante casetas</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img
                                                        src="{{ asset('img/icon/factura.png') }}"
                                                        alt=""
                                                        width="25px"
                                                    />
                                                </span>
                                                <input
                                                    name="comprobante_casetas[]"
                                                    id="comprobante_casetas"
                                                    type="file"
                                                    class="form-control"
                                                    multiple
                                                />
                                            </div>
                                        </div>

                                        <div class="col-4 form-group">
                                            <label for="name">Comprobante otros</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img
                                                        src="{{ asset('img/icon/factura.png') }}"
                                                        alt=""
                                                        width="25px"
                                                    />
                                                </span>
                                                <input
                                                    name="comprobante_otros[]"
                                                    id="comprobante_otros"
                                                    type="file"
                                                    class="form-control"
                                                    multiple
                                                />
                                            </div>
                                        </div>

                                        <h5 class="titulos_bitacora mb-4">
                                            <img
                                                src="{{ asset('img/icon/user_predeterminado.webp') }}"
                                                alt=""
                                                width="25px"
                                            />
                                            <strong>Salida de efectivo</strong>
                                        </h5>

                                        <div class="col-3 form-group">
                                            <label for="name">Banco 1</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img
                                                        src="{{ asset('img/icon/metodo-de-pago.webp') }}"
                                                        alt=""
                                                        width="25px"
                                                    />
                                                </span>
                                                <select
                                                    class="form-select cliente d-inline-block"
                                                    data-toggle="select"
                                                    id="id_banco1_pago_operador"
                                                    name="id_banco1_pago_operador"
                                                    value="{{ old('id_banco1_dinero_viaje') }}"
                                                >
                                                    <option value="">Selecciona</option>
                                                    @foreach ($bancos as $item)
                                                        <option value="{{ $item->id }}">
                                                            {{ $item->nombre_banco }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-3 form-group" id="operadorGroup">
                                            <div class="form-group">
                                                <label for="name">Cantidad</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img
                                                            src="{{ asset('img/icon/depositar.png') }}"
                                                            alt=""
                                                            width="35px"
                                                        />
                                                    </span>
                                                    <input
                                                        name="cantidad_banco1_pago_operador"
                                                        id="cantidad_banco1_pago_operador"
                                                        type="number"
                                                        class="form-control"
                                                    />
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-3 form-group">
                                            <label for="name">Banco 2</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img
                                                        src="{{ asset('img/icon/metodo-de-pago.webp') }}"
                                                        alt=""
                                                        width="25px"
                                                    />
                                                </span>
                                                <select
                                                    class="form-select cliente d-inline-block"
                                                    data-toggle="select"
                                                    id="id_banco2_pago_operador"
                                                    name="id_banco2_pago_operador"
                                                    value="{{ old('id_banco1_dinero_viaje') }}"
                                                >
                                                    <option value="">Selecciona</option>
                                                    @foreach ($bancos as $item)
                                                        <option value="{{ $item->id }}">
                                                            {{ $item->nombre_banco }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-3 form-group" id="operadorGroup">
                                            <div class="form-group">
                                                <label for="name">Cantidad 2</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img
                                                            src="{{ asset('img/icon/depositar.png') }}"
                                                            alt=""
                                                            width="35px"
                                                        />
                                                    </span>
                                                    <input
                                                        name="cantidad_banco2_pago_operador"
                                                        id="cantidad_banco2_pago_operador"
                                                        type="number"
                                                        class="form-control"
                                                    />
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-6">
                            <button type="submit" class="btn btn-success">Guardar</button>
                        </div>
                    </div>
                </form>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
