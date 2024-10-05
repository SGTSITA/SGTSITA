@extends('layouts.app')

@section('template_title')
   Editar Cotizacion
@endsection

@section('content')

    <div class="contaboleta_liberacionr-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <a class="btn"  href="{{ route('index.cotizaciones') }}" style="background: {{$configuracion->color_boton_close}}; color: #ffff;margin-right: 3rem;">
                                Regresar
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        <nav class="mx-auto">
                            <div class="nav nav-tabs custom-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link custom-tab active" id="nav-cotizacion-tab" data-bs-toggle="tab" data-bs-target="#nav-cotizacion" type="button" role="tab" aria-controls="nav-planeadas" aria-selected="false">
                                <img src="{{ asset('img/icon/validando-billete.webp') }}" alt="" width="40px"> Cotización
                            </button>

                              <button class="nav-link custom-tab" id="nav-Bloque-tab" data-bs-toggle="tab" data-bs-target="#nav-Bloque" type="button" role="tab" aria-controls="nav-Bloque" aria-selected="true">
                                <img src="{{ asset('img/icon/contenedores.png') }}" alt="" width="40px"> Bloque
                              </button>

                              <button class="nav-link custom-tab" id="nav-Contenedor-tab" data-bs-toggle="tab" data-bs-target="#nav-Contenedor" type="button" role="tab" aria-controls="nav-Contenedor" aria-selected="false">
                                <img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="40px"> Contenedor
                              </button>

                              <button class="nav-link custom-tab" id="nav-Documentacion-tab" data-bs-toggle="tab" data-bs-target="#nav-Documentacion" type="button" role="tab" aria-controls="nav-Documentacion" aria-selected="false">
                                <img src="{{ asset('img/icon/pdf.webp') }}" alt="" width="40px"> Documentación
                              </button>

                              <button class="nav-link custom-tab" id="nav-Gastos-tab" data-bs-toggle="tab" data-bs-target="#nav-Gastos" type="button" role="tab" aria-controls="nav-Gastos" aria-selected="false">
                                <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="40px"> Gastos
                              </button>

                              @if ($cotizacion->estatus_planeacion == 1)
                                @if ($documentacion->Asignaciones->id_operador == NULL)
                                    <button class="nav-link custom-tab" id="nav-Proveedor-tab" data-bs-toggle="tab" data-bs-target="#nav-Proveedor" type="button" role="tab" aria-controls="nav-Proveedor" aria-selected="false">
                                        <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="40px"> Proveedor
                                    </button>
                                @elseif ($documentacion->Asignaciones->id_proveedor == NULL)
                                    <button class="nav-link custom-tab" id="nav-GastosOpe-tab" data-bs-toggle="tab" data-bs-target="#nav-GastosOpe" type="button" role="tab" aria-controls="nav-GastosOpe" aria-selected="false">
                                        <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="40px"> Gastos Operador
                                    </button>
                                @endif
                              @endif

                            </div>
                        </nav>


                        <form method="POST" action="{{ route('update.cotizaciones', $cotizacion->id) }}" id="" enctype="multipart/form-data" role="form">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">

                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="nav-cotizacion" role="tabpanel" aria-labelledby="nav-cotizacion-tab" tabindex="0">
                                    <h3 class="mb-5">Datos de cotizacion</h3>

                                    @error('num_contenedor') <h4 class="error text-danger">{{ $message }}</h4> @enderror


                                    <div class="row">
                                            @if ($documentacion->num_contenedor != NULL)
                                                <label style="font-size: 20px;">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                            @endif

                                            <div class="col-6 form-group">
                                                <label for="name">Cliente *</label>
                                                <select class="form-select cliente d-inline-block" data-toggle="select" id="id_cliente" name="id_cliente">
                                                    <option value="{{ $cotizacion->id_cliente }}">{{ $cotizacion->Cliente->nombre }} / {{ $cotizacion->Cliente->telefono }}</option>
                                                    @foreach ($clientes as $item)
                                                        <option value="{{ $item->id }}">{{ $item->nombre }} / {{ $item->telefono }}</option>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Subcliente *</label>
                                                <select class="form-select subcliente d-inline-block" id="id_subcliente" name="id_subcliente">
                                                    @if ($cotizacion->id_subcliente != NULL)
                                                        <option value="{{ $cotizacion->id_subcliente }}">{{ $cotizacion->Subcliente->nombre }} / {{ $cotizacion->Subcliente->telefono }}</option>
                                                    @else
                                                        <option value="">Seleccionar subcliente</option>
                                                    @endif
                                                </select>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Origen</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_origen" id="cot_origen" type="text" class="form-control" value="{{$cotizacion->origen}}">
                                                </div>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Destino</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_destino" id="cot_destino" type="text" class="form-control" value="{{$cotizacion->destino}}">
                                                </div>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Fecha modulación</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_fecha_modulacion" id="cot_fecha_modulacion" type="date" class="form-control" value="{{$cotizacion->fecha_modulacion}}">
                                                </div>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Fecha entrega</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_fecha_entrega" id="cot_fecha_entrega" type="date" class="form-control" value="{{$cotizacion->fecha_entrega}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Tamaño Contenedor</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/escala.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_tamano" id="cot_tamano" type="text" class="form-control"  value="{{$cotizacion->tamano}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Peso Reglamentario</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/perdida-de-peso.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="peso_reglamentario" id="peso_reglamentario" type="number" class="form-control"  value="{{$cotizacion->peso_reglamentario}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Peso Contenedor</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/peso.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_peso_contenedor" id="cot_peso_contenedor" type="text" class="form-control" value="{{$cotizacion->peso_contenedor}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Sobrepeso</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/pesa-rusa.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="sobrepeso" id="sobrepeso" type="text" class="form-control" readonly value="{{$cotizacion->sobrepeso}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Precio Sobre Peso</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="precio_sobre_peso" id="precio_sobre_peso" type="text" class="form-control" value="{{ number_format($cotizacion->precio_sobre_peso, 2, '.', ',') }}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Precio Tonelada</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="precio_tonelada" id="precio_tonelada" type="text" class="form-control" value="{{$cotizacion->precio_tonelada}}" readonly>
                                                </div>
                                            </div>
                                            <div class="col-6"></div>

                                            <div class="col-3 form-group">
                                                <label for="name">Precio Viaje</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_precio_viaje" id="cot_precio_viaje" type="text" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()" value="{{$cotizacion->precio_viaje}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Burreo</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/burro.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_burreo" id="cot_burreo" type="float" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()" value="{{$cotizacion->burreo}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Maniobra</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/logistica.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_maniobra" id="cot_maniobra" type="float" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()" value="{{$cotizacion->maniobra}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Estadia</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/servidor-en-la-nube.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_estadia" id="cot_estadia" type="float" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()" value="{{$cotizacion->estadia}}">
                                                </div>
                                            </div>

                                            <div class="col-4 form-group">
                                                <label for="name">Otros</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_otro" id="cot_otro" type="float" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()"  value="{{$cotizacion->otro}}">
                                                </div>
                                            </div>

                                            <div class="col-4 form-group">
                                                <label for="name">IVA</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/impuesto.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_iva" id="cot_iva" type="number" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()" value="{{$cotizacion->iva}}">
                                                </div>
                                            </div>

                                            <div class="col-4 form-group">
                                                <label for="name">Retención</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_retencion" id="cot_retencion" type="float" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()" value="{{$cotizacion->retencion}}">
                                                </div>
                                            </div>

                                            <div class="col-4 form-group">
                                                <label for="name">Base 1</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/factura.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="base_factura" id="base_factura" type="float" class="form-control" value="{{$cotizacion->base_factura}}">
                                                </div>
                                            </div>

                                            <div class="col-4 form-group">
                                                <label for="name">Base 2</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="base_taref" id="base_taref" type="float" class="form-control" value="{{$cotizacion->base_taref}}">
                                                </div>
                                            </div>
                                            <div class="col-4"></div>

                                            <div class="col-4 form-group">
                                                <label for="name">Total Cotización</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="total" id="total" type="float" class="form-control" readonly>
                                                </div>
                                            </div>

                                    </div>
                                </div>

                                <div class="tab-pane fade" id="nav-Bloque" role="tabpanel" aria-labelledby="nav-Bloque-tab" tabindex="0">
                                    <h3 class="mb-5 mt-3">Bloque de Entrada</h3>
                                    @if ($documentacion->num_contenedor != NULL)
                                        <label style="font-size: 20px;">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                    @endif
                                    <div class="row">
                                        <div class="col-4 form-group">
                                            <label for="name">Block</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/contenedores.png') }}" alt="" width="25px">
                                                </span>
                                                <input name="bloque" id="bloque" type="text" class="form-control" value="{{$cotizacion->bloque}}">
                                            </div>
                                        </div>

                                        <div class="col-4 form-group">
                                            <label for="name">Horario Inicio</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                                                </span>
                                                <input name="bloque_hora_i" id="bloque_hora_i" type="time" class="form-control" value="{{$cotizacion->bloque_hora_i}}">
                                            </div>
                                        </div>

                                        <div class="col-4 form-group">
                                            <label for="name">Horario Fin</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                                                </span>
                                                <input name="bloque_hora_f" id="bloque_hora_f" type="time" class="form-control" value="{{$cotizacion->bloque_hora_f}}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="nav-Contenedor" role="tabpanel" aria-labelledby="nav-Contenedor-tab" tabindex="0">
                                    <h3 class="mb-5 mt-3">Contenedor</h3>
                                    @if ($documentacion->num_contenedor != NULL)
                                        <label style="font-size: 20px;">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                    @endif
                                    <div class="row">
                                        <div class="col-4 form-group">
                                            <label for="name">Num. Contenedor</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px">
                                                </span>
                                                <input name="num_contenedor" id="num_contenedor" type="text" class="form-control" value="{{$documentacion->num_contenedor}}">@error('num_contenedor') <span class="error text-danger">{{ $message }}</span> @enderror

                                            </div>
                                        </div>

                                        <div class="col-4 form-group">
                                            <label for="name">Terminal(Nombre)</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/terminal.png') }}" alt="" width="25px">
                                                </span>
                                                <input name="terminal" id="terminal" type="text" class="form-control" value="{{$documentacion->terminal}}">
                                            </div>
                                        </div>

                                        <div class="col-4 form-group">
                                            <label for="name">Num. Autorización</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/persona-clave.png') }}" alt="" width="25px">
                                                </span>
                                                <input name="num_autorizacion" id="num_autorizacion" type="text" class="form-control" value="{{$documentacion->num_autorizacion}}">
                                            </div>
                                        </div>

                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>¿CCP - Carta Porte?</label><br>
                                                @if ($documentacion->ccp == 'si')
                                                    <input class="form-check-input" type="radio" name="ccp" value="si" id="option_si_ccp" checked> Sí<br>
                                                    <input class="form-check-input" type="radio" name="ccp" value="no" id="option_no_ccp"> No
                                                @else
                                                    <input class="form-check-input" type="radio" name="ccp" value="si" id="option_si_ccp"> Sí<br>
                                                    <input class="form-check-input" type="radio" name="ccp" value="no" id="option_no_ccp" checked> No
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            @if ($documentacion->ccp == 'si')
                                                <div class="form-group" id="inputFieldccp">
                                            @else
                                                <div class="form-group" id="inputFieldccp" style="display: none;">
                                            @endif
                                                <label for="input">Documento CCP:</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="doc_ccp" id="doc_ccp" type="file" class="form-control">
                                                </div>

                                                @if ($documentacion->ccp == 'si')
                                                    <div class="col-6">
                                                        @if (pathinfo($documentacion->doc_ccp, PATHINFO_EXTENSION) == 'pdf')
                                                        <p class="text-center ">
                                                            <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doc_ccp)}}" style="width: 100%; height: 100px;"></iframe>
                                                        </p>
                                                                <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doc_ccp) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                                        @elseif (pathinfo($documentacion->doc_ccp, PATHINFO_EXTENSION) == 'doc')
                                                        <p class="text-center ">
                                                            <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                                        </p>
                                                                <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doc_ccp) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                                        @elseif (pathinfo($documentacion->doc_ccp, PATHINFO_EXTENSION) == 'docx')
                                                        <p class="text-center ">
                                                            <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                                        </p>
                                                                <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doc_ccp) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                                        @else
                                                            <p class="text-center mt-2">
                                                                <img id="blah" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doc_ccp) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                            </p>
                                                                <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doc_ccp) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                                                        @endif
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="nav-Documentacion" role="tabpanel" aria-labelledby="nav-Documentacion-tab" tabindex="0">
                                    <h3 class="mt-3 mb-5">Documentación</h3>
                                    @if ($documentacion->num_contenedor != NULL)
                                        <label style="font-size: 20px;">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                    @endif
                                    <div class="row">
                                        <div class="col-6 form-group">
                                            <label for="name">Num. Boleta de Liberación</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/9.webp') }}" alt="" width="25px">
                                                </span>
                                                <input name="num_boleta_liberacion" id="num_boleta_liberacion" type="text" class="form-control" value="{{$documentacion->num_boleta_liberacion}}">
                                            </div>
                                        </div>

                                        <div class="col-6 form-group">
                                            <label for="name">Boleta de Liberación</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                                </span>
                                                <input name="boleta_liberacion" id="boleta_liberacion" type="file" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-6"></div>
                                        <div class="col-6">
                                            @if (pathinfo($documentacion->boleta_liberacion, PATHINFO_EXTENSION) == 'pdf')
                                            <p class="text-center ">
                                                <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion)}}" style="width: 100%; height: 100px;"></iframe>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                            @elseif (pathinfo($documentacion->boleta_liberacion, PATHINFO_EXTENSION) == 'doc')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @elseif (pathinfo($documentacion->boleta_liberacion, PATHINFO_EXTENSION) == 'docx')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @else
                                                <p class="text-center mt-2">
                                                    <img id="blah" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                </p>
                                                    <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                                            @endif
                                        </div>

                                        <div class="col-6 form-group">
                                            <label for="name">Num. Doda</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/cero.webp') }}" alt="" width="25px">
                                                </span>
                                                <input name="num_doda" id="num_doda" type="text" class="form-control" value="{{$documentacion->num_doda}}">
                                            </div>
                                        </div>

                                        <div class="col-6 form-group">
                                            <label for="name">Doda</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/documento.png') }}" alt="" width="25px">
                                                </span>
                                                <input name="doda" id="doda" type="file" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-6"></div>
                                        <div class="col-6">
                                            @if (pathinfo($documentacion->doda, PATHINFO_EXTENSION) == 'pdf')
                                            <p class="text-center ">
                                                <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doda)}}" style="width: 100%; height: 100px;"></iframe>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doda) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                            @elseif (pathinfo($documentacion->doda, PATHINFO_EXTENSION) == 'doc')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doda) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @elseif (pathinfo($documentacion->doda, PATHINFO_EXTENSION) == 'docx')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doda) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @else
                                                <p class="text-center mt-2">
                                                    <img id="blah" src="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doda) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                </p>
                                                    <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doda) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                                            @endif
                                        </div>

                                        <div class="col-6 form-group">
                                            <label for="name">Num. Carta Porte</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/9.webp') }}" alt="" width="25px">
                                                </span>
                                                <input name="num_carta_porte" id="num_carta_porte" type="text" class="form-control" value="{{$documentacion->num_carta_porte}}">
                                            </div>
                                        </div>

                                        <div class="col-6 form-group">
                                            <label for="name">Carta Porte</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                                </span>
                                                <input name="carta_porte" id="carta_porte" type="file" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-6"></div>

                                        <div class="col-6">
                                            @if (pathinfo($cotizacion->carta_porte, PATHINFO_EXTENSION) == 'pdf')
                                            <p class="text-center ">
                                                <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte)}}" style="width: 100%; height: 100px;"></iframe>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                            @elseif (pathinfo($cotizacion->carta_porte, PATHINFO_EXTENSION) == 'doc')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @elseif (pathinfo($cotizacion->carta_porte, PATHINFO_EXTENSION) == 'docx')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @else
                                                <p class="text-center mt-2">
                                                    <img id="blah" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                </p>
                                                    <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                                            @endif
                                        </div>

                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>¿Prealta?</label><br>
                                                @if ($documentacion->boleta_vacio == 'si')
                                                    <input class="form-check-input" type="radio" name="boleta_vacio" value="si" id="option_si" checked> Sí<br>
                                                    <input class="form-check-input" type="radio" name="boleta_vacio" value="no" id="option_no"> No
                                                @else
                                                    <input class="form-check-input" type="radio" name="boleta_vacio" value="si" id="option_si"> Sí<br>
                                                    <input class="form-check-input" type="radio" name="boleta_vacio" value="no" id="option_no" checked> No
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-4">
                                            @if ($documentacion->boleta_vacio == 'si')
                                                <div class="form-group" id="inputField">
                                            @else
                                                <div class="form-group" id="inputField" style="display: none;">
                                            @endif
                                                <label for="input">Fecha Boleta Vacio:</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="fecha_boleta_vacio" id="fecha_boleta_vacio" type="date" class="form-control" value="{{$documentacion->fecha_boleta_vacio}}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            @if ($documentacion->boleta_vacio == 'si')
                                                <div class="form-group" id="inputFieldIMG">
                                            @else
                                                <div class="form-group" id="inputFieldIMG" style="display: none;">
                                            @endif
                                                <label for="input">IMG Boleta Vacio:</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="img_boleta" id="img_boleta" type="file" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            @if (pathinfo($cotizacion->img_boleta, PATHINFO_EXTENSION) == 'pdf')
                                            <p class="text-center ">
                                                <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta)}}" style="width: 100%; height: 50px;"></iframe>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                            @elseif (pathinfo($cotizacion->img_boleta, PATHINFO_EXTENSION) == 'doc')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @elseif (pathinfo($cotizacion->img_boleta, PATHINFO_EXTENSION) == 'docx')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @else
                                                <p class="text-center mt-2">
                                                    <img id="blah" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                </p>
                                                    <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                                            @endif
                                        </div>

                                        <div class="col-6"></div>

                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>Comprobante de vacio?</label><br>
                                                @if ($documentacion->eir == 'si')
                                                    <input class="form-check-input" type="radio" name="eir" value="si" id="eir_si" checked> Sí<br>
                                                    <input class="form-check-input" type="radio" name="eir" value="no" id="eir_no"> No
                                                @else
                                                    <input class="form-check-input" type="radio" name="eir" value="si" id="eir_si"> Sí<br>
                                                    <input class="form-check-input" type="radio" name="eir" value="no" id="eir_no" checked> No
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-4">
                                            @if ($documentacion->eir == 'si')
                                                <div class="form-group" id="inputEir">
                                            @else
                                                <div class="form-group" id="inputEir" style="display: none;">
                                            @endif
                                                <label for="input">Doc EIR:</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="doc_eir" id="doc_eir" type="file" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-4">
                                            @if ($documentacion->eir == 'si')
                                                <div class="form-group" id="inputEirFecha">
                                            @else
                                                <div class="form-group" id="inputEirFecha" style="display: none;">
                                            @endif
                                                <label for="input">Fecha EIR:</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="fecha_eir" id="fecha_eir" type="date" class="form-control" value="{{$cotizacion->fecha_eir}}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            @if (pathinfo($documentacion->doc_eir, PATHINFO_EXTENSION) == 'pdf')
                                            <p class="text-center ">
                                                <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir)}}" style="width: 100%; height: 50px;"></iframe>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                            @elseif (pathinfo($documentacion->doc_eir, PATHINFO_EXTENSION) == 'doc')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @elseif (pathinfo($documentacion->doc_eir, PATHINFO_EXTENSION) == 'docx')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @else
                                                <p class="text-center mt-2">
                                                    <img id="blah" src="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                </p>
                                                    <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="nav-Gastos" role="tabpanel" aria-labelledby="nav-Gastos-tab" tabindex="0">
                                    <h3 class="mt-3 mb-5">Gastos Extras</h3>
                                    @if ($documentacion->num_contenedor != NULL)
                                        <label style="font-size: 20px;">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                    @endif
                                    <div class="row">
                                        @foreach ($gastos_extras as $gasto_extra)
                                            <input type="hidden" name="ticket_id[]" value="{{ $gasto_extra->id }}">
                                            <div class="col-6 form-group">
                                                <label for="name">Descripción:</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="gasto_descripcion[]" id="gasto_descripcion[]" type="text" class="form-control" value="{{$gasto_extra->descripcion}}">
                                                </div>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Monto:</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="gasto_monto[]" id="gasto_monto[]" type="text" class="form-control" value="{{$gasto_extra->monto}}">
                                                </div>
                                            </div>
                                        @endforeach
                                        <div id="formulario" class="mt-4">
                                            <button type="button" class="clonar btn btn-secondary btn-sm">Agregar</button>
                                            <div class="clonars">
                                                <div class="row">
                                                    <div class="col-6 form-group">
                                                        <label for="name">Descripción:</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="basic-addon1">
                                                                <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                                            </span>
                                                            <input name="gasto_descripcion[]" id="gasto_descripcion[]" type="text" class="form-control" >
                                                        </div>
                                                    </div>

                                                    <div class="col-6 form-group">
                                                        <label for="name">Monto:</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="basic-addon1">
                                                                <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px">
                                                            </span>
                                                            <input name="gasto_monto[]" id="gasto_monto[]" type="text" class="form-control" >
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-4 form-group">
                                            <label for="name">Total con gastos</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                </span>
                                                <input type="float" class="form-control" value="{{ number_format($cotizacion->total, 2, '.', ',') }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($cotizacion->estatus_planeacion == 1)
                                    @if ($documentacion->Asignaciones->id_operador == NULL)
                                        <div class="tab-pane fade" id="nav-Proveedor" role="tabpanel" aria-labelledby="nav-Proveedor-tab" tabindex="0">
                                            <h3 class="mt-3 mb-5">Proveedor - {{$documentacion->Asignaciones->Proveedor->nombre}} </h3>
                                            @if ($documentacion->num_contenedor != NULL)
                                                <label style="font-size: 20px;">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                            @endif
                                            <div class="row">
                                                <div class="col-3 form-group">
                                                    <label for="name">Costo viaje</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="precio_proveedor" id="precio_proveedor" type="number" class="form-control" value="{{$documentacion->Asignaciones->precio}}">
                                                    </div>
                                                </div>

                                                <div class="col-3 form-group">
                                                    <label for="name">Burreo</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/burro.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="burreo_proveedor" id="burreo_proveedor" type="float" class="form-control" value="{{$documentacion->Asignaciones->burreo}}">
                                                    </div>
                                                </div>

                                                <div class="col-3 form-group">
                                                    <label for="name">Maniobra</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/logistica.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="maniobra_proveedor" id="maniobra_proveedor" type="float" class="form-control" value="{{$documentacion->Asignaciones->maniobra}}">
                                                    </div>
                                                </div>

                                                <div class="col-3 form-group">
                                                    <label for="name">Estadia</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/servidor-en-la-nube.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="estadia_proveedor" id="estadia_proveedor" type="float" class="form-control" value="{{$documentacion->Asignaciones->estadia}}">
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Sobrepeso</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input id="cantidad_sobrepeso_proveedor" name="cantidad_sobrepeso_proveedor" type="float" class="form-control" value="{{$cotizacion->sobrepeso}}" disabled>
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Precio Sobre Peso</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/pago-en-efectivo.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input id="sobrepeso_proveedor" name="sobrepeso_proveedor" value="{{$documentacion->Asignaciones->sobrepeso_proveedor}}" type="float" class="form-control">
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Total tonelada</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/pago-en-efectivo.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input id="total_tonelada" name="total_tonelada" type="float" class="form-control" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Base 1</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/factura.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="base1_proveedor" id="base1_proveedor" type="float" class="form-control" value="{{$documentacion->Asignaciones->base1_proveedor}}">
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Base 2</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="base2_proveedor" id="base2_proveedor" type="float" class="form-control" value="{{$documentacion->Asignaciones->base2_proveedor}}">
                                                    </div>
                                                </div>

                                                <div class="col-4"></div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Otros</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="otro_proveedor" id="otro_proveedor" type="float" class="form-control" value="{{$documentacion->Asignaciones->otro}}">
                                                    </div>
                                                </div>

                                                <div class="col-3 form-group">
                                                    <label for="name">Descripcion Otros</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="descripcion_otro1" id="descripcion_otro1" type="text" class="form-control" value="{{$documentacion->Asignaciones->descripcion_otro1}}">
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-12">
                                                        <p class="d-inline-flex gap-1">
                                                            <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">
                                                             Agregar mas campos de Otros
                                                            </a>

                                                          </p>
                                                          <div class="collapse row" id="collapseExample">

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Otros 2 </label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="otro2" id="otro2" type="float" class="form-control" value="{{$documentacion->Asignaciones->otro2}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Descripcion Otros 2</label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="descripcion_otro2" id="descripcion_otro2" type="text" class="form-control" value="{{$documentacion->Asignaciones->descripcion_otro2}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Otros 3 </label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="otro3" id="otro3" type="float" class="form-control" value="{{$documentacion->Asignaciones->otro3}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Descripcion Otros 3</label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="descripcion_otro3" id="descripcion_otro3" type="text" class="form-control" value="{{$documentacion->Asignaciones->descripcion_otro3}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Otros 4 </label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="otro4" id="otro4" type="float" class="form-control" value="{{$documentacion->Asignaciones->otro4}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Descripcion Otros 4</label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="descripcion_otro4" id="descripcion_otro4" type="text" class="form-control" value="{{$documentacion->Asignaciones->descripcion_otro4}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Otros 5 </label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="otro5" id="otro5" type="float" class="form-control" value="{{$documentacion->Asignaciones->otro5}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Descripcion Otros 5</label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="descripcion_otro5" id="descripcion_otro5" type="text" class="form-control" value="{{$documentacion->Asignaciones->descripcion_otro5}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Otros 6 </label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="otro6" id="otro6" type="float" class="form-control" value="{{$documentacion->Asignaciones->otro6}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Descripcion Otros 6</label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="descripcion_otro6" id="descripcion_otro6" type="text" class="form-control" value="{{$documentacion->Asignaciones->descripcion_otro6}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Otros 7 </label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="otro7" id="otro7" type="float" class="form-control" value="{{$documentacion->Asignaciones->otro7}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Descripcion Otros 7</label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="descripcion_otro7" id="descripcion_otro7" type="text" class="form-control" value="{{$documentacion->Asignaciones->descripcion_otro7}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Otros 8 </label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="otro8" id="otro8" type="float" class="form-control" value="{{$documentacion->Asignaciones->otro8}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Descripcion Otros 8</label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="descripcion_otro8" id="descripcion_otro8" type="text" class="form-control" value="{{$documentacion->Asignaciones->descripcion_otro8}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Otros 9 </label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="otro9" id="otro9" type="float" class="form-control" value="{{$documentacion->Asignaciones->otro9}}">
                                                                    </div>
                                                                </div>

                                                                <div class="col-3 form-group">
                                                                    <label for="name">Descripcion Otros 9</label>
                                                                    <div class="input-group mb-3">
                                                                        <span class="input-group-text" id="basic-addon1">
                                                                            <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">
                                                                        </span>
                                                                        <input name="descripcion_otro10" id="descripcion_otro10" type="text" class="form-control" value="{{$documentacion->Asignaciones->descripcion_otro10}}">
                                                                    </div>
                                                                </div>

                                                          </div>
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">IVA</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/impuesto.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="iva_proveedor" id="iva_proveedor" type="number" class="form-control" value="{{$documentacion->Asignaciones->iva}}">
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Retención</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="retencion_proveedor" id="retencion_proveedor" type="float" class="form-control" value="{{$documentacion->Asignaciones->retencion}}">
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Total</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="total_proveedor" id="total_proveedor" type="number" class="form-control" value="{{$documentacion->Asignaciones->total_proveedor}}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @elseif ($documentacion->Asignaciones->id_proveedor == NULL)
                                        <div class="tab-pane fade" id="nav-GastosOpe" role="tabpanel" aria-labelledby="nav-GastosOpe-tab" tabindex="0">
                                            <h3 class="mt-3 mb-5">Gastos Operadores</h3>
                                            @if ($documentacion->num_contenedor != NULL)

                                                <label style="font-size: 20px;">Num contenedor:  {{$documentacion->Asignaciones->Operador->nombre}} </label>
                                                <label style="font-size: 20px;">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                            @endif
                                            <div class="row">
                                                @foreach ($gastos_ope as $gasto_extra)
                                                    <input type="hidden" name="ticket_id_ope[]" value="{{ $gasto_extra->id }}">
                                                    <div class="col-4 form-group">
                                                        <label for="name">Cantidad:</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="basic-addon1">
                                                                <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                                            </span>
                                                            <input name="cantidad_ope[]" id="cantidad_ope[]" type="text" class="form-control" value="{{$gasto_extra->cantidad}}">
                                                        </div>
                                                    </div>

                                                    <div class="col-4 form-group">
                                                        <label for="name">Tipo:</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="basic-addon1">
                                                                <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px">
                                                            </span>
                                                            <select class="form-select d-inline-block" id="tipo_ope" name="tipo_ope[]">
                                                                <option value="{{$gasto_extra->tipo}}">{{$gasto_extra->tipo}}</option>
                                                                <option value="Gasolina">Gasolina</option>
                                                                <option value="Casetas">Casetas</option>
                                                                <option value="Otros">Otros</option>
                                                            </select>
                                                        </div>
                                                    </div>

                                                    <div class="col-4 form-group">
                                                        <label for="name">Comprobante</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="basic-addon1">
                                                                <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                            </span>
                                                            <input name="comrpobante_ope[]" id="comrpobante_ope" type="file" class="form-control">
                                                        </div>
                                                    </div>
                                                @endforeach
                                                <div id="formulario2" class="mt-4">
                                                    <button type="button" class="clonar2 btn btn-secondary btn-sm">Agregar</button>
                                                    <div class="clonars2">
                                                        <div class="row">
                                                            <div class="col-4 form-group">
                                                                <label for="name">Cantidad:</label>
                                                                <div class="input-group mb-3">
                                                                    <span class="input-group-text" id="basic-addon1">
                                                                        <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                                                    </span>
                                                                    <input name="cantidad_ope[]" id="cantidad_ope[]" type="text" class="form-control" >
                                                                </div>
                                                            </div>

                                                            <div class="col-4 form-group">
                                                                <label for="name">Tipo:</label>
                                                                <div class="input-group mb-3">
                                                                    <span class="input-group-text" id="basic-addon1">
                                                                        <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="25px">
                                                                    </span>
                                                                    <select class="form-select d-inline-block" id="tipo_ope" name="tipo_ope[]">
                                                                        <option value="Gasolina">Gasolina</option>
                                                                        <option value="Casetas">Casetas</option>
                                                                        <option value="Otros">Otros</option>
                                                                    </select>
                                                                </div>
                                                            </div>

                                                            <div class="col-4 form-group">
                                                                <label for="name">Comprobante</label>
                                                                <div class="input-group mb-3">
                                                                    <span class="input-group-text" id="basic-addon1">
                                                                        <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                                    </span>
                                                                    <input name="comrpobante_ope[]" id="comrpobante_ope" type="file" class="form-control">
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endif
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('select2')
    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js')}}"></script>

    <script type="text/javascript">
    $(document).ready(function() {
    $('.cliente').select2();
    });
    </script>

    <script type="text/javascript">
        // ============= Agregar mas inputs dinamicamente =============
        $('.clonar').click(function() {
        // Clona el .input-group
        var $clone = $('#formulario .clonars').last().clone();

        // Borra los valores de los inputs clonados
        $clone.find(':input').each(function () {
            if ($(this).is('select')) {
            this.selectedIndex = 0;
            } else {
            this.value = '';
            }
        });

        // Agrega lo clonado al final del #formulario
        $clone.appendTo('#formulario');
        });

        function calcularTotal() {
            const precio_viaje = parseFloat(document.getElementById('cot_precio_viaje').value.replace(/,/g, '')) || 0;
            const burreo = parseFloat(document.getElementById('cot_burreo').value.replace(/,/g, '')) || 0;
            const retencion = parseFloat(document.getElementById('cot_retencion').value.replace(/,/g, '')) || 0;
            const iva = parseFloat(document.getElementById('cot_iva').value.replace(/,/g, '')) || 0;
            const otro = parseFloat(document.getElementById('cot_otro').value.replace(/,/g, '')) || 0;
            const estadia = parseFloat(document.getElementById('cot_estadia').value.replace(/,/g, '')) || 0;
            const maniobra = parseFloat(document.getElementById('cot_maniobra').value.replace(/,/g, '')) || 0;

            // Restar el valor de Retención del total
            const totalSinRetencion = precio_viaje + burreo + iva + otro + estadia + maniobra;
            const totalConRetencion = totalSinRetencion - retencion;

            // Obtener el valor de Precio Tonelada
            const precioTonelada = parseFloat(document.getElementById('precio_tonelada').value.replace(/,/g, '')) || 0;

            // Sumar el valor de Precio Tonelada al total
            const totalFinal = totalConRetencion + precioTonelada;

            // Formatear el total con comas
            const totalFormateado = totalFinal.toLocaleString('en-US');

            document.getElementById('total').value = totalFormateado;
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Obtener elementos del DOM
            var pesoReglamentarioInput = document.getElementById('peso_reglamentario');
            var pesoContenedorInput = document.getElementById('cot_peso_contenedor');
            var sobrepesoInput = document.getElementById('sobrepeso');
            var precioSobrePesoInput = document.getElementById('precio_sobre_peso');
            var precioToneladaInput = document.getElementById('precio_tonelada');

            // Agregar evento de cambio a los inputs
            pesoReglamentarioInput.addEventListener('input', calcularSobrepeso);
            pesoContenedorInput.addEventListener('input', calcularSobrepeso);

            // Función para calcular el sobrepeso
            function calcularSobrepeso() {
                var pesoReglamentario = parseFloat(pesoReglamentarioInput.value) || 0;
                var pesoContenedor = parseFloat(pesoContenedorInput.value) || 0;

                // Calcular sobrepeso
                var sobrepeso = Math.max(pesoContenedor - pesoReglamentario, 0);

                // Mostrar sobrepeso en el input correspondiente con dos decimales
                sobrepesoInput.value = sobrepeso.toFixed(2);

                // Obtener el valor de Precio Sobre Peso
                var precioSobrePeso = parseFloat(precioSobrePesoInput.value.replace(/,/g, '')) || 0;

                // Calcular el resultado de la multiplicación
                var resultado = sobrepeso * precioSobrePeso;

                // Mostrar el resultado en el campo "Precio Tonelada"
                precioToneladaInput.value = resultado.toLocaleString('en-US');

                // Calcular el total
                calcularTotal();
            }

            // Agregar evento de entrada al campo "Precio Sobre Peso"
            precioSobrePesoInput.addEventListener('input', function () {
                // Obtener el valor de Precio Sobre Peso
                var precioSobrePeso = parseFloat(precioSobrePesoInput.value.replace(/,/g, '')) || 0;

                // Calcular el resultado de la multiplicación
                var resultado = parseFloat(sobrepesoInput.value) * precioSobrePeso;

                // Mostrar el resultado en el campo "Precio Tonelada"
                precioToneladaInput.value = resultado.toLocaleString('en-US');

                // Calcular el total
                calcularTotal();
            });

            // Calcular sobrepeso inicialmente al cargar la página
            calcularSobrepeso();

            // Función para calcular base_taref
            function calcularBaseTaref() {
                // Obtener los valores de los inputs
                const total = parseFloat(document.getElementById('total').value.replace(/,/g, '')) || 0;
                const baseFactura = parseFloat(document.getElementById('base_factura').value.replace(/,/g, '')) || 0;
                const iva = parseFloat(document.getElementById('cot_iva').value.replace(/,/g, '')) || 0;
                const retencion = parseFloat(document.getElementById('cot_retencion').value.replace(/,/g, '')) || 0;

                // Realizar el cálculo
                const baseTaref = (total - baseFactura - iva) + retencion;

                // Mostrar el resultado en el input de base_taref
                document.getElementById('base_taref').value = baseTaref.toFixed(2);
            }

            // Agregar eventos de cambio a los inputs para calcular automáticamente
            document.getElementById('total').addEventListener('input', calcularBaseTaref);
            document.getElementById('base_factura').addEventListener('input', calcularBaseTaref);
            document.getElementById('cot_iva').addEventListener('input', calcularBaseTaref);
            document.getElementById('cot_retencion').addEventListener('input', calcularBaseTaref);
        });
    </script>

    <script>
        // ============= Agregar mas inputs dinamicamente =============
        $('.clonar2').click(function() {
        // Clona el .input-group
        var $clone = $('#formulario2 .clonars2').last().clone();

        // Borra los valores de los inputs clonados
        $clone.find(':input').each(function () {
            if ($(this).is('select')) {
            this.selectedIndex = 0;
            } else {
            this.value = '';
            }
        });

        // Agrega lo clonado al final del #formulario2
        $clone.appendTo('#formulario2');
        });
    </script>

    <script>
         document.addEventListener('DOMContentLoaded', function () {
            // Obtener referencias a los elementos
            var optionSi = document.getElementById('option_si_ccp');
            var optionNo = document.getElementById('option_no_ccp');
            var inputFieldIMG = document.getElementById('inputFieldccp');

            // Función para controlar la visibilidad del campo de entrada
            function toggleInputField() {
                // Si el radio button "Sí" está seleccionado, mostrar el campo de entrada
                if (optionSi.checked) {
                    inputFieldIMG.style.display = 'block';
                } else {
                    inputFieldIMG.style.display = 'none';
                }
            }

            // Agregar eventos change a los radio buttons
            optionSi.addEventListener('change', toggleInputField);
            optionNo.addEventListener('change', toggleInputField);

            // Llamar a la función inicialmente para asegurarse de que el campo se oculte o muestre correctamente
            toggleInputField();
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Obtener referencias a los elementos
            var optionSi = document.getElementById('option_si');
            var optionNo = document.getElementById('option_no');
            var inputField = document.getElementById('inputField');
            var inputFieldIMG = document.getElementById('inputFieldIMG');

            // Función para controlar la visibilidad del campo de entrada
            function toggleInputField() {
                // Si el radio button "Sí" está seleccionado, mostrar el campo de entrada
                if (optionSi.checked) {
                    inputField.style.display = 'block';
                    inputFieldIMG.style.display = 'block';
                } else {
                    inputField.style.display = 'none';
                    inputFieldIMG.style.display = 'none';
                }
            }

            // Agregar eventos change a los radio buttons
            optionSi.addEventListener('change', toggleInputField);
            optionNo.addEventListener('change', toggleInputField);

            // Llamar a la función inicialmente para asegurarse de que el campo se oculte o muestre correctamente
            toggleInputField();
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Obtener referencias a los elementos
            var eirSi = document.getElementById('eir_si');
            var eirNo = document.getElementById('eir_no');
            var inputEir = document.getElementById('inputEir');
            var inputEirFecha = document.getElementById('inputEirFecha');

            // Función para controlar la visibilidad del campo de entrada
            function toggleInputEir() {
                // Si el radio button "Sí" está seleccionado, mostrar el campo de entrada
                if (eirSi.checked) {
                    inputEir.style.display = 'block';
                    inputEirFecha.style.display = 'block';
                } else {
                    inputEir.style.display = 'none';
                    inputEirFecha.style.display = 'none';
                }
            }

            // Agregar eventos change a los radio buttons
            eirSi.addEventListener('change', toggleInputEir);
            eirNo.addEventListener('change', toggleInputEir);

            // Llamar a la función inicialmente para asegurarse de que el campo se oculte o muestre correctamente
            toggleInputEir();
        });

        $(document).ready(function() {
            function loadSubclientes(clienteId, selectedSubclienteId = null) {
                if (clienteId) {
                    $.ajax({
                        type: 'GET',
                        url: '/subclientes/' + clienteId,
                        success: function(data) {
                            $('#id_subcliente').empty();
                            $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
                            $.each(data, function(key, subcliente) {
                                if (selectedSubclienteId && selectedSubclienteId == subcliente.id) {
                                    $('#id_subcliente').append('<option value="' + subcliente.id + '" selected>' + subcliente.nombre + '</option>');
                                } else {
                                    $('#id_subcliente').append('<option value="' + subcliente.id + '">' + subcliente.nombre + '</option>');
                                }
                            });
                        }
                    });
                } else {
                    $('#id_subcliente').empty();
                    $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
                }
            }

            $('#id_cliente').change(function() {
                var clienteId = $(this).val();
                loadSubclientes(clienteId);
            });

            // Load subclientes on page load
            var initialClienteId = $('#id_cliente').val();
            var initialSubclienteId = '{{ $cotizacion->id_subcliente }}';
            loadSubclientes(initialClienteId, initialSubclienteId);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cantidadSobrepesoInput = document.getElementById('cantidad_sobrepeso_proveedor');
            const valorSobrepesoInput = document.getElementById('sobrepeso_proveedor');
            const sobrepesoProbInput = document.getElementById('total_tonelada');

            const precioInput = document.getElementById('precio_proveedor');
            const burreoInput = document.getElementById('burreo_proveedor');
            const maniobraInput = document.getElementById('maniobra_proveedor');
            const estadiaInput = document.getElementById('estadia_proveedor');
            const otroInput = document.getElementById('otro_proveedor');
            const otro2Input = document.getElementById('otro2');
            const otro3Input = document.getElementById('otro3');
            const otro4Input = document.getElementById('otro4');
            const otro5Input = document.getElementById('otro5');
            const otro6Input = document.getElementById('otro6');
            const otro7Input = document.getElementById('otro7');
            const otro8Input = document.getElementById('otro8');
            const otro9Input = document.getElementById('otro9');
            const ivaInput = document.getElementById('iva_proveedor');
            const retencionInput = document.getElementById('retencion_proveedor');
            const totalInput = document.getElementById('total_proveedor');

            // Función para actualizar el total
            function updateTotal() {
                let precio = parseFloat(precioInput.value) || 0;
                let burreo = parseFloat(burreoInput.value) || 0;
                let maniobra = parseFloat(maniobraInput.value) || 0;
                let estadia = parseFloat(estadiaInput.value) || 0;
                let sobrepesoProb = parseFloat(sobrepesoProbInput.value) || 0;
                let otro = parseFloat(otroInput.value) || 0;
                let otro2 = parseFloat(otro2Input.value) || 0;
                let otro3 = parseFloat(otro3Input.value) || 0;
                let otro4 = parseFloat(otro4Input.value) || 0;
                let otro5 = parseFloat(otro5Input.value) || 0;
                let otro6 = parseFloat(otro6Input.value) || 0;
                let otro7 = parseFloat(otro7Input.value) || 0;
                let otro8 = parseFloat(otro8Input.value) || 0;
                let otro9 = parseFloat(otro9Input.value) || 0;
                let iva = parseFloat(ivaInput.value) || 0;
                let retencion = parseFloat(retencionInput.value) || 0;

                // Sumar todos menos retencion
                let subtotal = precio + burreo + maniobra + estadia + otro +
                            otro2 + otro3 + otro4 + otro5 + otro6 +
                            otro7 + otro8 + otro9 + iva + sobrepesoProb;

                console.log(`Subtotal: ${subtotal}`);
                // Restar retencion
                let total = subtotal - retencion;
                console.log(`Total: ${total}`);

                // Actualizar el input de total
                totalInput.value = total.toFixed(2);
            }

            // Función para actualizar el resultado y el total
            function updateResultado() {
                const cantidadSobrepeso = parseFloat(cantidadSobrepesoInput.value) || 0;
                const valorSobrepeso = parseFloat(valorSobrepesoInput.value) || 0;

                console.log(`Cantidad sobrepeso: ${cantidadSobrepeso}, Valor sobrepeso: ${valorSobrepeso}`);

                // Multiplicar los valores
                const resultado = cantidadSobrepeso * valorSobrepeso;

                console.log(`Resultado sobrepeso: ${resultado}`);

                // Colocar el resultado en el input correspondiente
                sobrepesoProbInput.value = resultado.toFixed(2); // Redondear a dos decimales

                // Actualizar el total
                updateTotal();
            }

            // Asignar evento de input a todos los inputs relevantes
            const allInputs = [
                precioInput, burreoInput, maniobraInput, estadiaInput, otroInput,
                otro2Input, otro3Input, otro4Input, otro5Input, otro6Input,
                otro7Input, otro8Input, otro9Input, ivaInput, retencionInput,
                valorSobrepesoInput, cantidadSobrepesoInput
            ];

            allInputs.forEach(input => {
                input.addEventListener('input', () => {
                    updateResultado();
                    updateTotal();
                });
            });

            // Calcular el resultado y el total iniciales
            updateResultado();

                // Función para calcular base2_proveedor
            function calcularBase2Proveedor() {
                // Obtener los valores de los inputs
                const totalProveedor = parseFloat(document.getElementById('total_proveedor').value) || 0;
                const base1Proveedor = parseFloat(document.getElementById('base1_proveedor').value) || 0;
                const ivaProveedor = parseFloat(document.getElementById('iva_proveedor').value) || 0;
                const retencionProveedor = parseFloat(document.getElementById('retencion_proveedor').value) || 0;

                // Realizar el cálculo
                const base2Proveedor = (totalProveedor - base1Proveedor - ivaProveedor) + retencionProveedor;

                // Mostrar el resultado en el input de base2_proveedor
                document.getElementById('base2_proveedor').value = base2Proveedor.toFixed(2);
            }

            // Agregar eventos de cambio a los inputs para calcular automáticamente
            document.getElementById('total_proveedor').addEventListener('input', calcularBase2Proveedor);
            document.getElementById('base1_proveedor').addEventListener('input', calcularBase2Proveedor);
            document.getElementById('iva_proveedor').addEventListener('input', calcularBase2Proveedor);
            document.getElementById('retencion_proveedor').addEventListener('input', calcularBase2Proveedor);
        });

    </script>

@endsection
