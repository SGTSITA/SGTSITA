<div class="row">
    <div class="col-6 form-group">
        <label for="name">Fecha inicio</label>
        <div class="input-group">
            <span class="input-group-text" id="basic-addon1">
                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
            </span>
            <input id="fecha_inicio_{{$cotizacion->id}}" name="fecha_inicio" type="date" class="form-control" value="{{ \Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fecha_inicio)->format('Y-m-d') }}">
        </div>
    </div>

    <div class="col-6 form-group">
        <label for="name">Fecha fin</label>
        <div class="input-group">
            <span class="input-group-text" id="basic-addon1">
                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
            </span>
            <input id="fecha_fin_{{$cotizacion->id}}" name="fecha_fin" type="date" class="form-control" value="{{ \Carbon\Carbon::parse($cotizacion->DocCotizacion->Asignaciones->fecha_fin)->format('Y-m-d') }}">
        </div>
    </div>

    <div class="col-12 form-group">
        <label for="name">Selecione el tipo de Unidad</label>
        <div class="input-group mb-3">
            <span class="input-group-text" id="basic-addon1">
                <img src="{{ asset('img/icon/camion.webp') }}" alt="" width="25px">
            </span>
            <select class="form-select d-inline-block" name="tipo" >
                <option  value="{{$cotizacion->tipo_viaje}}">{{$cotizacion->tipo_viaje}}</option>
                <option  value="Sencillo">Sencillo</option>
                <option  value="Full">Full</option>
            </select>
        </div>
    </div>
    @if ($cotizacion->DocCotizacion->Asignaciones->id_camion == NULL)
        <button class="btn" type="button" id="btn_clientes_search{{$cotizacion->id}}" data-cotizacion-id="{{$cotizacion->id}}">
            Buscar Disponibilidad
        </button>

        <div id="resultado_equipos{{ $cotizacion->id }}" class="row"></div>
    @endif

    @if ($cotizacion->DocCotizacion->Asignaciones->id_proveedor == NULL)
        <div class="col-12 form-group" id="camionGroup">
            <div class="form-group">
                <label for="name">Camion</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <img src="{{ asset('img/icon/camion.png') }}" alt="" width="35px">
                    </span>
                    <select class="form-select d-inline-block" id="camion" name="camion" value="{{ old('camion') }}">
                        @if ($cotizacion->DocCotizacion->Asignaciones->id_camion == NULL)
                            <option value="">Seleccione opcion</option>
                        @else
                            <option  value="{{ $cotizacion->DocCotizacion->Asignaciones->id_camion }}">{{ $cotizacion->DocCotizacion->Asignaciones->Camion->folio }} / {{ $cotizacion->DocCotizacion->Asignaciones->Camion->modelo }}</option>
                        @endif
                        @foreach ($equipos_camiones as $item)
                            <option value="{{$item->id}}">{{$item->id_equipo}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-12 form-group" id="operadorGroup">
            <div class="form-group">
                <label for="name">Operador</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <img src="{{ asset('img/icon/chofer.png') }}" alt="" width="35px">
                    </span>
                    <select class="form-select d-inline-block" id="operador" name="operador" value="{{ old('operador') }}">
                        @if ($cotizacion->DocCotizacion->Asignaciones->id_operador == NULL)
                            <option value="">Seleccione opcion</option>
                        @else
                            <option  value="{{ $cotizacion->DocCotizacion->Asignaciones->id_operador }}">{{ $cotizacion->DocCotizacion->Asignaciones->Operador->nombre }} / {{ $cotizacion->DocCotizacion->Asignaciones->Operador->telefono }}</option>
                        @endif
                        @foreach ($operadores as $item)
                            <option value="{{$item->id}}">{{$item->nombre}} / {{$item->telefono}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-12 form-group" id="chasisGroup">
            <div class="form-group">
                <label for="name">Chasis</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <img src="{{ asset('img/icon/troca.png') }}" alt="" width="35px">
                    </span>
                    <select class="form-select d-inline-block" id="chasis" name="chasis" value="{{ old('chasis') }}">
                        @if ($cotizacion->DocCotizacion->Asignaciones->id_chasis == NULL)
                            <option value="">Seleccione opcion</option>
                        @else
                        <option  value="{{ $cotizacion->DocCotizacion->Asignaciones->id_chasis }}">{{ $cotizacion->DocCotizacion->Asignaciones->Chasis->folio }} / {{ $cotizacion->DocCotizacion->Asignaciones->Chasis->modelo }}</option>
                        @endif

                        @foreach ($equipos_chasis as $item)
                            <option value="{{$item->id}}">{{$item->id_equipo}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-12 form-group" id="chasisAdicional1Group" style="display:none;">
            <label for="name">Chasis 2</label>
            <select class="form-select d-inline-block" id="chasisAdicional1" name="chasisAdicional1" value="{{ old('chasisAdicional1') }}">
                @if ($cotizacion->DocCotizacion->Asignaciones->id_chasis2 == NULL)
                    <option value="">Seleccione opcion</option>
                @else
                    <option  value="{{ $cotizacion->DocCotizacion->Asignaciones->id_chasis2 }}">{{ $cotizacion->DocCotizacion->Asignaciones->Chasis2->folio }} / {{ $cotizacion->DocCotizacion->Asignaciones->Chasis2->modelo }}</option>
                @endif

                @foreach ($equipos_chasis as $item)
                    <option value="{{$item->id}}">{{$item->id_equipo}}</option>
                @endforeach
            </select>
        </div>

        <div class="col-12 form-group" id="nuevoCampoDolyGroup" style="display:none;">
            <label for="name">Doly</label>
            <select class="form-select d-inline-block" id="nuevoCampoDoly" name="nuevoCampoDoly" value="{{ old('nuevoCampoDoly') }}">
                @if ($cotizacion->DocCotizacion->Asignaciones->id_dolys == NULL)
                    <option value="">Seleccione opcion</option>
                @else
                    <option  value="{{ $cotizacion->DocCotizacion->Asignaciones->id_dolys }}">{{ $cotizacion->DocCotizacion->Asignaciones->Doly->folio }} / {{ $cotizacion->DocCotizacion->Asignaciones->Doly->modelo }}</option>
                @endif

                @foreach ($equipos_dolys as $item)
                    <option value="{{$item->id}}">{{$item->id_equipo}}</option>
                @endforeach
            </select>
        </div>

        <div class="col-6 form-group" id="operadorGroup">
            <div class="form-group">
                <label for="name">Sueldo del operador</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <img src="{{ asset('img/icon/depositar.png') }}" alt="" width="35px">
                    </span>
                    <input name="sueldo_viaje" id="sueldo_viaje" type="text" class="form-control" value="{{ $cotizacion->DocCotizacion->Asignaciones->sueldo_viaje }}">
                </div>
            </div>
        </div>

        <div class="col-6 form-group" id="operadorGroup">
            <div class="form-group">
                <label for="name">Dinero para el viaje</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <img src="{{ asset('img/icon/billetera.png') }}" alt="" width="35px">
                    </span>
                    <input name="dinero_viaje" id="dinero_viaje" type="text" class="form-control" value="{{ $cotizacion->DocCotizacion->Asignaciones->dinero_viaje }}">
                </div>
            </div>
        </div>

        <div class="col-12">
            <h4>Salida de dinero</h4>
        </div>

        <div class="col-6">
            <div class="form-group">
                <label for="name">Banco 1</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                    </span>
                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="id_banco1_dinero_viaje" name="id_banco1_dinero_viaje" value="{{ old('id_banco1_dinero_viaje') }}">
                            @if ($cotizacion->DocCotizacion->Asignaciones->id_banco1_dinero_viaje == NULL)
                                <option value="">Seleccione opcion</option>
                            @else
                            <option  value="{{ $cotizacion->DocCotizacion->Asignaciones->id_banco1_dinero_viaje }}">{{ $cotizacion->DocCotizacion->Asignaciones->Banco1->nombre_banco }} - ${{ number_format($cotizacion->DocCotizacion->Asignaciones->Banco1->saldo, 2, '.', ',') }}</option>
                            @endif
                            @foreach ($bancos as $item)
                                <option value="{{$item->id}}">{{$item->nombre_banco}} - ${{ number_format($item->saldo, 2, '.', ',') }}</option>
                            @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-6 form-group" id="operadorGroup">
            <div class="form-group">
                <label for="name">Cantidad</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <img src="{{ asset('img/icon/depositar.png') }}" alt="" width="35px">
                    </span>
                    <input name="cantidad_banco1_dinero_viaje" id="cantidad_banco1_dinero_viaje" type="number" class="form-control" value="{{$cotizacion->DocCotizacion->Asignaciones->cantidad_banco1_dinero_viaje}}">
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="form-group">
                <label for="name">Banco 2</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                    </span>
                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="id_banco2_dinero_viaje" name="id_banco2_dinero_viaje" value="{{ old('id_banco2_dinero_viaje') }}">
                            @if ($cotizacion->DocCotizacion->Asignaciones->id_banco2_dinero_viaje == NULL)
                                <option value="">Seleccione opcion</option>
                            @else
                                <option  value="{{ $cotizacion->DocCotizacion->Asignaciones->id_banco2_dinero_viaje }}">{{ $cotizacion->DocCotizacion->Asignaciones->Banco2->nombre_banco }} - ${{ number_format($cotizacion->DocCotizacion->Asignaciones->Banco2->saldo, 2, '.', ',') }}</option>
                            @endif
                            @foreach ($bancos as $item)
                                <option value="{{$item->id}}">{{$item->nombre_banco}} - ${{ number_format($item->saldo, 2, '.', ',') }}</option>
                            @endforeach
                    </select>
                </div>
            </div>
        </div>

        <div class="col-6 form-group" id="operadorGroup">
            <div class="form-group">
                <label for="name">Cantidad 2</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">
                        <img src="{{ asset('img/icon/depositar.png') }}" alt="" width="35px">
                    </span>
                    <input name="cantidad_banco2_dinero_viaje" id="cantidad_banco2_dinero_viaje" type="number" class="form-control" value="{{$cotizacion->DocCotizacion->Asignaciones->cantidad_banco2_dinero_viaje}}">
                </div>
            </div>
        </div>
    @endif
</div>
