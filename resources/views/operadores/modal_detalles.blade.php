<div class="modal fade" id="operadoresModal_detalles{{$item->id}}" tabindex="-1" aria-labelledby="operadoresModal_detalles{{$item->id}}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
            <h5 class="modal-title">Detalle de Viaje</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body ">
                <div class="col-12">
                    <div class="row">
                        <div class="col-3 mb-3">
                            <h5 class="titulos_bitacora mb-2">#<strong>ID Cotizacion</strong></h5>
                            <p class="mb-4">
                                <a type="button" class="btn" target="_blank" href="{{ route('edit.cotizaciones', $item->Contenedor->Cotizacion->id) }}">
                                    {{ $item->Contenedor->Cotizacion->id; }}
                                </a>
                            </p>
                        </div>

                        <div class="col-3 mb-3">
                            <h5 class="titulos_bitacora mb-2">#<strong>Num. Contenedor</strong></h5>
                            <p class="mb-4">
                                {{ $item->Contenedor->Cotizacion->DocCotizacion->num_contenedor; }}
                            </p>
                        </div>

                        <div class="col-6 mb-3">
                            <h5 class="titulos_bitacora mb-2"><img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px"><strong> Fecha modulación y Fecha entrega</strong></h5>
                            @php
                                $fecha_modulacion = $item->Contenedor->Cotizacion->fecha_modulacion;
                                $fecha_timestamp_modulacion = strtotime($fecha_modulacion);
                                $fecha_formateada_modulacion = date('d \d\e F \d\e\l Y', $fecha_timestamp_modulacion);

                                $fecha_entrega = $item->Contenedor->Cotizacion->fecha_entrega;
                                $fecha_timestamp_entrega = strtotime($fecha_entrega);
                                $fecha_formateada_entrega = date('d \d\e F \d\e\l Y', $fecha_timestamp_entrega);
                            @endphp

                            <p class="mb-4"> {{$fecha_formateada_modulacion}} <strong>al</strong> {{$fecha_formateada_entrega}}</p>

                        </div>
                    </div>

                    <div class="row">
                            <div class="col-3">
                            <h5 class="titulos_bitacora"><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px"><strong>Viaje</strong></h5>
                            <p class="mb-4">{{ $item->Contenedor->Cotizacion->origen }} <strong>a</strong> {{ $item->Contenedor->Cotizacion->destino }}</p>
                            </div>

                            <div class="col-3">
                            <h5 class="titulos_bitacora"><img src="{{ asset('img/icon/depositar.png') }}" alt="" width="25px"><strong>Sueldo</strong></h5>
                            <p class="mb-4">
                                ${{ number_format($item->sueldo_viaje, 2, '.', ','); }}
                            </p>
                            </div>

                            <div class="col-3">
                            <h5 class="titulos_bitacora"><img src="{{ asset('img/icon/billetera.png') }}" alt="" width="25px"><strong>Dinero del viaje</strong></h5>
                            <p class="mb-4">
                                ${{ number_format($item->dinero_viaje, 2, '.', ','); }}
                            </p>
                            </div>

                            <div class="col-8">

                            </div>

                            <div class="col-12">
                                <h5 class="titulos_bitacora mb-4"><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px"><strong> Comprobantes de Pago</strong></h5>
                                <div class="row">

                                    <div class="col-3 form-group">
                                        <label for="name">Faltante / Restante</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/depositar.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="resta" id="resta_{{ $item->id }}" type="text" class="form-control" value="{{ $item->dinero_viaje - $item->sueldo_viaje - $item->gasolina - $item->casetas -$item->otros }}" readonly>
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Gasolina</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/bomba-de-gas.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="gasolina" type="text" class="form-control" value="{{ $item->gasolina }}" disabled>
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Casetas</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/guardia.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="casetas" type="text" class="form-control" value="{{ $item->casetas }}" disabled>
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Otros</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/menu.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="otros" type="text" class="form-control" value="{{ $item->otros }}" disabled>
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <h5 for="name">Comprobante gasolina</h5><br>
                                        @foreach ($comprobantes_gasolina as $comprobante)
                                            @if ($item->id == $comprobante->id_asignacion)
                                                <a  href="{{asset('comprobantes/'.$comprobante->imagen) }}" data-lightbox="example-2" data-title="{{$comprobante->tipo}}" target="_blank">
                                                    <img src="{{ asset('comprobantes/'. $comprobante->imagen) }}" alt="" width="100px">
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>

                                    <div class="col-4 form-group">
                                        <h5 for="name">Comprobante casetas</h5><br>
                                        @foreach ($comprobantes_casetas as $comprobante)
                                            @if ($item->id == $comprobante->id_asignacion)
                                                <a  href="{{asset('comprobantes/'.$comprobante->imagen) }}" data-lightbox="example-2" data-title="{{$comprobante->tipo}}" target="_blank">
                                                    <img src="{{ asset('comprobantes/'. $comprobante->imagen) }}" alt="" width="100px">
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>

                                    <div class="col-4 form-group">
                                        <h5 for="name">Comprobante otros</h5><br>
                                        @foreach ($comprobantes_otros as $comprobante)
                                            @if ($item->id == $comprobante->id_asignacion)
                                                <a  href="{{asset('comprobantes/'.$comprobante->imagen) }}" data-lightbox="example-2" data-title="{{$comprobante->tipo}}" target="_blank">
                                                    <img src="{{ asset('comprobantes/'. $comprobante->imagen) }}" alt="" width="100px">
                                                </a>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
