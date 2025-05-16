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
                            <h3 class="mb-3">Editar Cotizacion</h3>
                            <div class="col-3 offset-3">
                            <div><span class="text-xs text-muted text-bold d-none" id="referencia_full">{{$cotizacion->referencia_full}}</span></div>
                                <div class="option-group">
                                    @if($cotizacion->tipo_viaje != "Full")                                    
                                    <label class="custom-option selected">
                                        <input type="radio" checked name="plan" value="Sencillo" onchange="handleSelection(this)">
                                        <i class="fas fa-truck icon"></i>
                                        <span class="text">Sencillo</span>
                                        <i class="fas fa-check check-icon"></i>
                                    </label>
                                    @else
                                    <label class="custom-option selected">
                                        <input type="radio" checked name="plan" value="Full" onchange="handleSelection(this)">
                                        <i class="fas fa-truck-moving icon"></i>
                                        <span class="text">Full</span>
                                        <i class="fas fa-check check-icon"></i>
                                    </label>
                                    @endif
                                </div>
                                
                            </div>
                        </div>
                        <div class="row">
                        <div class="col-12">
                                        <div class="custom-nav-tabs">
                                            <label class="custom-nav-item">
                                                <input type="radio" checked="checked" value="Contenedor-A" class="custom-nav-radio" name="contenedorTabs" id="tab1" />
                                                <div class="custom-nav-link active">
                                                <i class="ni ni-box-2 text-warning text-gradient"></i>
                                                <h6>Contenedor A</h6>
                                                </div>
                                            </label>

                                            <label class="custom-nav-item @if($cotizacion->tipo_viaje != 'Full') d-none @endif" id="tab-contenedor-b">
                                                <input type="radio" class="custom-nav-radio" value="Contenedor-B" name="contenedorTabs" id="tab2" />
                                                <div class="custom-nav-link">
                                                <i class="ni ni-box-2 text-info text-gradient"></i>
                                                <h6>Contenedor B</h6>
                                                </div>
                                            </label>
                                        </div>
                                    </div>
                        </div>
                    </div>

                    <div class="card-body">

                        <nav class="mx-auto">
                            <div class="nav nav-tabs custom-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link custom-tab active" id="nav-cotizacion-tab" data-bs-toggle="tab" data-bs-target="#nav-cotizacion" type="button" role="tab" aria-controls="nav-planeadas" aria-selected="false">
                                <img src="{{ asset('img/icon/validando-billete.webp') }}" alt="" width="40px"> Cotización
                            </button>

                              

                              <button class="nav-link custom-tab" id="nav-Contenedor-tab" data-bs-toggle="tab" data-bs-target="#nav-Contenedor" type="button" role="tab" aria-controls="nav-Contenedor" aria-selected="false">
                                <img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="40px"> Contenedor
                              </button>

                              <button class="nav-link custom-tab" id="nav-Documentacion-tab" data-bs-toggle="tab" data-bs-target="#nav-Documentacion" type="button" role="tab" aria-controls="nav-Documentacion" aria-selected="false">
                                <img src="{{ asset('img/icon/pdf.webp') }}" alt="" width="40px"> Documentación
                              </button>

                              <button class="nav-link custom-tab" id="nav-Bloque-tab" data-bs-toggle="tab" data-bs-target="#nav-Bloque" type="button" role="tab" aria-controls="nav-Bloque" aria-selected="true">
                                <img src="{{ asset('img/icon/contenedores.png') }}" alt="" width="40px"> Bloque
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
                                        <img src="{{ asset('img/icon/efectivo.webp') }}" alt="" width="40px"> Gastos Viaje
                                    </button>
                                @endif
                              @endif

                            </div>
                        </nav>


                        <form method="POST" action="{{ route('update.cotizaciones', $cotizacion->id) }}" 
                        id="cotizacionCreateMultiple" enctype="multipart/form-data" sgt-cotizacion-action="edit" role="form">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">

                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="nav-cotizacion" role="tabpanel" aria-labelledby="nav-cotizacion-tab" tabindex="0">
                                                                

                                    @error('num_contenedor') <h4 class="error text-danger">{{ $message }}</h4> @enderror


                                    <div class="row">
                                    <h3 class="mb-5 mt5">Datos de cotizacion</h3>
                                            @if ($documentacion->num_contenedor != NULL)
                                                <label style="font-size: 20px;" class="labelNumContedor" id="labelNumContenedor-1">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                            @endif

                                            <div class="col-6 form-group">
                                                <!--label for="name">Cliente *</label>
                                                <select class="form-select cliente d-inline-block" data-toggle="select" id="id_cliente" name="id_cliente">
                                                    <option value="{{ $cotizacion->id_cliente }}">{{ $cotizacion->Cliente->nombre }} / {{ $cotizacion->Cliente->telefono }}</option>
                                                    @foreach ($clientes as $item)
                                                        <option value="{{ $item->id }}">{{ $item->nombre }} / {{ $item->telefono }}</option>
                                                    @endforeach
                                                </select-->
                                                <ul class="list-group">
                                                    <li class="list-group-item border-1 border-dashed d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                                                    <div class="d-flex flex-column">
                                                        <h6 class="mb-2 text-md">Cliente</h6>
                                                        <span class="mb-2 text-sm">
                                                            Nombre: <span class="text-dark font-weight-bold ms-2">
                                                            <select class="form-select bg-transparent cliente d-inline-block"  data-toggle="select" id="id_cliente" name="id_cliente" value="{{ old('id_cliente') }}">
                                                                <option value="">Seleccionar cliente</option>
                                                                @foreach ($clientes as $item)
                                                                    <option value="{{ $item->id }}" @if($item->id == $cotizacion->id_cliente) selected @endif>{{ucwords(strtolower( $item->nombre)) }} </option>
                                                                @endforeach
                                                            </select></span>
                                                        </span>
                                                        <span class="mb-2 text-sm">Teléfono: <span class="text-dark ms-2 font-weight-bold" id="telClient"></span></span>
                                                        <span class="text-xs">Correo Electrónico: <span class="text-dark ms-2 font-weight-bold" id="mailClient"></span></span>
                                                    </div>
                                                    </li>
                                                </ul>
                                                <input type="hidden" id="txtClientes" value ="{{($clientes)}}">
                                            </div>

                                            <div class="col-6 form-group">
                                                <!--label for="name">Subcliente *</label>
                                                <select class="form-select subcliente d-inline-block" id="id_subcliente" name="id_subcliente">
                                                    @if ($cotizacion->id_subcliente != NULL)
                                                        <option value="{{ $cotizacion->id_subcliente }}">{{ $cotizacion->Subcliente->nombre }} / {{ $cotizacion->Subcliente->telefono }}</option>
                                                    @else
                                                        <option value="">Seleccionar subcliente</option>
                                                    @endif
                                                </select-->
                                                <ul class="list-group">
                                                    <li class="list-group-item border-1 border-dashed d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                                                    <div class="d-flex flex-column">
                                                        <h6 class="mb-2 text-md">SubCliente</h6>
                                                        <span class="mb-2 text-sm">
                                                            Nombre: <span class="text-dark font-weight-bold ms-2">
                                                            <select class="form-select subcliente d-inline-block" id="id_subcliente" name="id_subcliente">
                                                                <option value="">Seleccionar subcliente</option>
                                                            </select></span>
                                                        </span>
                                                        <span class="mb-2 text-sm">Teléfono: <span class="text-dark ms-2 font-weight-bold" id="telClient"></span></span>
                                                        <span class="text-xs">Correo Electrónico: <span class="text-dark ms-2 font-weight-bold" id="mailClient"></span></span>
                                                    </div>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Origen</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="origen" id="origen" type="text" class="form-control" autocomplete="off" value="{{$cotizacion->origen}}">
                                                </div>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Destino</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="destino" id="destino" type="text" class="form-control" autocomplete="off" value="{{$cotizacion->destino}}">
                                                </div>
                                            </div>
                                            
                                            <div class="border-dashed border-1 border-secondary border-radius-md p-3">

                                            
                                            <div class="col-lg-4 col-md-6 col-7  text-start">
                                                <h5 class="fw-bold mb-2">¿El contenedor va a recinto?</h5>
                                                <div class="nav-wrapper  mt-2 mb-3 position-relative ">
                                                    <ul class="nav nav-pills bg-light rounded-pill nav-fill flex-row p-1" id="tabs-pricing" role="tablist">
                                                    <li class="nav-item">
                                                        <a class="recinto nav-link mb-0 rounded-pill active" id="tabs-iconpricing-tab-1" data-bs-toggle="tab" href="#monthly" role="tab" 
                                                        aria-controls="monthly" aria-selected="true" @if($cotizacion->uso_recinto == 0) data-kt-plan="recinto-no" @else data-kt-plan="recinto-si" @endif>
                                                        @if($cotizacion->uso_recinto == 0) No @else Si, va a recinto @endif
                                                        </a>
                                                    </li>
                                                    <li class="nav-item">
                                                        <a class="recinto nav-link mb-0 rounded-pill " id="tabs-iconpricing-tab-2" data-bs-toggle="tab" href="#annual" role="tab" 
                                                        aria-controls="annual" aria-selected="false" @if($cotizacion->uso_recinto != 0) data-kt-plan="recinto-no" @else data-kt-plan="recinto-si" @endif>
                                                        @if($cotizacion->uso_recinto != 0) No @else Si, va a recinto @endif
                                                        </a>
                                                    </li>
                                                    </ul>
                                                </div>
                                                
                                            </div>

                                            <div class="col-12">
                                                <input type="text" name="text_recinto" id="text_recinto" class="d-none" @if($cotizacion->uso_recinto == 1) value="recinto-si" @endif>

                                                <div class="input-group @if($cotizacion->uso_recinto == 0) d-none @endif" id="input-recinto">
                                                    <span class="input-group-text">Dirección recinto</span>
                                                    <textarea class="form-control" name="direccion_recinto" id="direccion_recinto" aria-label="Dirección recinto">{{$cotizacion->direccion_recinto}}</textarea>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <h5 class="fw-bold mb-2  mt-3">Dirección entrega</h5>
                                                <div class="input-group" >
                                                    <span class="input-group-text">Dirección Entrega</span>
                                                    <textarea class="form-control" name="direccion_entrega" id="direccion_entrega" aria-label="Dirección Entrega">{{$cotizacion->direccion_entrega}}</textarea>
                                                </div>
                                            </div>
                                            </div>

                                            <div class="col-6 mt-2 form-group">
                                                <label for="name">Fecha modulación</label>
                                                <div class="input-group  b-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="fecha_modulacion" id="fecha_modulacion" type="date" class="form-control" value="{{$cotizacion->fecha_modulacion}}">
                                                </div>
                                            </div>

                                            <div class="col-6 mt-2 form-group">
                                                <label for="name">Fecha entrega</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="fecha_entrega" id="fecha_entrega" type="date" class="form-control" value="{{$cotizacion->fecha_entrega}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Tamaño Contenedor</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/escala.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="tamano" id="tamano" type="text" class="form-control" oninput="allowOnlyDecimals(event)" autocomplete="off" value="{{$cotizacion->tamano}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Peso Reglamentario</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/perdida-de-peso.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="peso_reglamentario" id="peso_reglamentario" type="number" oninput="allowOnlyDecimals(event)" class="form-control calculo-cotizacion" autocomplete="off" value="{{$cotizacion->peso_reglamentario}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Peso Contenedor</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/peso.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="peso_contenedor" id="peso_contenedor" type="text" class="form-control calculo-cotizacion" oninput="allowOnlyDecimals(event)" autocomplete="off" value="{{$cotizacion->peso_contenedor}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Sobrepeso</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/pesa-rusa.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="sobrepeso" id="sobrepeso" type="text" class="form-control calculo-cotizacion" autocomplete="off" readonly value="{{$cotizacion->sobrepeso}}">
                                                </div>
                                            </div>
                                            <div class="col-3 form-group">
                                                <label for="name">Precio Sobre Peso</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="precio_sobre_peso" id="precio_sobre_peso" type="text" autocomplete="off" oninput="allowOnlyDecimals(event)" class="form-control moneyformat calculo-cotizacion" value="{{ number_format($cotizacion->precio_sobre_peso, 2, '.', ',') }}">
                                                </div>
                                            </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Sobre Peso Viaje</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/peso.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="sobrepeso_viaje" id="sobrepeso_viaje" readonly type="text" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Total Sobre Peso Viaje</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/peso.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="total_sobrepeso_viaje" id="total_sobrepeso_viaje" readonly type="text" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                            

                                            <div class="col-3 form-group">
                                                <label for="name">Precio Tonelada</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="precio_tonelada" id="precio_tonelada" type="text" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)" autocomplete="off" value="{{$cotizacion->precio_tonelada}}" readonly>
                                                </div>
                                            </div>
                                          

                                            <div class="col-3 form-group">
                                                <label for="name">Precio Viaje</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="precio_viaje" id="precio_viaje" type="text" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)" autocomplete="off" value="{{$cotizacion->precio_viaje}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Burreo</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/burro.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="burreo" id="burreo" type="float" class="form-control moneyformat calculo-cotizacion" autocomplete="off" oninput="allowOnlyDecimals(event)" value="{{$cotizacion->burreo}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Maniobra</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/logistica.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="maniobra" id="maniobra" type="float" class="form-control moneyformat calculo-cotizacion" autocomplete="off" oninput="allowOnlyDecimals(event)" value="{{$cotizacion->maniobra}}">
                                                </div>
                                            </div>

                                            <div class="col-3 form-group">
                                                <label for="name">Estadia</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/servidor-en-la-nube.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="estadia" id="estadia" type="float" class="form-control moneyformat calculo-cotizacion" autocomplete="off" oninput="allowOnlyDecimals(event)" value="{{$cotizacion->estadia}}">
                                                </div>
                                            </div>

                                            <div class="col-4 form-group">
                                                <label for="name">Otros</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="otro" id="otro" type="float" class="form-control moneyformat calculo-cotizacion" autocomplete="off" oninput="allowOnlyDecimals(event)" value="{{$cotizacion->otro}}">
                                                </div>
                                            </div>

                                            <div class="col-4 form-group">
                                                <label for="name">IVA</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/impuesto.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="iva" id="iva" type="text" class="form-control moneyformat calculo-cotizacion" autocomplete="off" readonly value="{{$cotizacion->iva}}">
                                                </div>
                                            </div>

                                            <div class="col-4 form-group">
                                                <label for="name">Retención</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="retencion" id="retencion" type="float" class="form-control moneyformat calculo-cotizacion" autocomplete="off" readonly value="{{$cotizacion->retencion}}">
                                                </div>
                                            </div>

                                            <div class="col-4 form-group">
                                                <label for="name">Base 1</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/factura.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="base_factura" id="base_factura" type="float" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)" autocomplete="off" value="{{$cotizacion->base_factura}}">
                                                </div>
                                            </div>

                                            <div class="col-4 form-group">
                                                <label for="name">Base 2</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="base_taref" id="base_taref" type="float" class="form-control moneyformat calculo-cotizacion" readonly value="{{$cotizacion->base_taref}}">
                                                </div>
                                            </div>
                                            <div class="col-4"></div>
                                            <div class="col-4 form-group">
                                            <label for="name">Total Gastos</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input type="text" class="form-control txtSumGastos" id="txtSumGastos" value="0" readonly>
                                                </div>
                                            </div>
                                            <div class="col-4 form-group">
                                                <label for="name">Total Cotización</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="total1" id="total1" type="float" class="form-control moneyformat calculo-cotizacion total-cotizacion" readonly>
                                                </div>
                                            </div>
                                            <div class="col-4 form-group">
                                            <h5 class="fs-14">Cotización + Gastos</h5>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                </span>
                                                <input type="text" class="form-control txtResultGastos" id="total" value="{{ $cotizacion->total }}" readonly>
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                <div class="tab-pane fade" id="nav-Bloque" role="tabpanel" aria-labelledby="nav-Bloque-tab" tabindex="0">
                                    <h3 class="mb-5 mt-3">Bloque de Entrada</h3>
                                    @if ($documentacion->num_contenedor != NULL)
                                        <label style="font-size: 20px;" class="labelNumContedor" id="labelNumContenedor-2">Num contenedor:  {{$documentacion->num_contenedor}} </label>
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
                                        <label style="font-size: 20px;" class="labelNumContedor" id="labelNumContenedor-3">Num contenedor:  {{$documentacion->num_contenedor}} </label>
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

                                      

                                     
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="nav-Documentacion" role="tabpanel" aria-labelledby="nav-Documentacion-tab" tabindex="0">
                                    <h3 class="mt-3 mb-5">Documentación</h3>
                                    @if ($documentacion->num_contenedor != NULL)
                                        <label style="font-size: 20px;" class="labelNumContedor" id="labelNumContenedor-4">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                    @endif
                                    <div class="row">
                                        <div class="col-12">
                                        <table class="table align-items-center mb-0">
                  <thead>
                    <tr>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Documento</th>
                      <th class="text-uppercase text-secondary text-xxs font-weight-bolder opacity-7 ps-2">Núm. Documento</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Estatus</th>
                      <th class="text-center text-uppercase text-secondary text-xxs font-weight-bolder opacity-7">Acciones</th>
                    </tr>
                  </thead>
                  <tbody>
                  <tr>
                      <td>
                        <div class="d-flex px-3 py-1">
                          <div>
                            <img src="{{asset('img/not-file.png')}}" class="avatar me-3" alt="image" id="img-Formato-para-Carta-porte">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Formato CCP</h6>
                            <p class="text-sm font-weight-bold text-secondary mb-0">
                                <span class="text-muted" id="filSize-Formato-para-Carta-porte">0</span>
                            </p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-sm font-weight-bold mb-0">
                        <input name="ccp" id="ccp" type="text" class="form-control" value="{{$documentacion->ccp}}">
                            
                        </p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-sm font-weight-bold mb-0">
                            <span class="badge bg-gradient-warning badge-sm" id="badge-Formato-para-Carta-porte">Pendiente</span>
                        </p>
                      </td>
                      <td class="align-middle text-end">
                        <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                        
                          <button type="button" 
                          class="btn btn-sm btn-icon-only btnDocs btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" id="btnFileFormato-para-Carta-porte"
                          data-bs-placement="bottom" title="Cargar archivo" 
                          data-bs-original-title="Cargar archivo">
                            <i class="fas fa-upload" aria-hidden="true"></i>
                          </button>
                          <a href="javasrcipt:void()" target="_blank" class="openFile btn btn-sm btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" 
                          data-bs-placement="bottom" 
                          title="Ver Documento" 
                          data-bs-original-title="Ver Documento" id="btn-ver-Formato-para-Carta-porte">
                            <i class="fas fa-eye" aria-hidden="true"></i>
</a>
                        </div>
                      </td>
                    </tr>
                  <tr>
                      <td>
                        <div class="d-flex px-3 py-1">
                          <div>
                            <img src="{{asset('img/not-file.png')}}" class="avatar me-3" alt="image" id="img-Boleta-de-liberacion">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Boleta de liberación</h6>
                            <p class="text-sm font-weight-bold text-secondary mb-0">
                                <span class="text-muted" id="filSize-Boleta-de-liberacion">0</span>
                            </p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-sm font-weight-bold mb-0">
                        <input name="num_boleta_liberacion" id="num_boleta_liberacion" type="text" class="form-control" value="{{$documentacion->num_boleta_liberacion}}">
                            
                        </p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-sm font-weight-bold mb-0">
                            <span class="badge bg-gradient-warning badge-sm" id="badge-Boleta-de-liberacion">Pendiente</span>
                        </p>
                      </td>
                      <td class="align-middle text-end">
                        <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                        
                          <button type="button" 
                          class="btn btn-sm btn-icon-only btnDocs btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" id="btnFileBoletaLiberacion"
                          data-bs-placement="bottom" title="Cargar archivo" 
                          data-bs-original-title="Cargar archivo">
                            <i class="fas fa-upload" aria-hidden="true"></i>
                          </button>
                          <a href="javasrcipt:void()" target="_blank" class="openFile btn btn-sm btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" 
                          data-bs-placement="bottom" 
                          title="Ver Documento" 
                          data-bs-original-title="Ver Documento" id="btn-ver-Boleta-de-liberacion">
                            <i class="fas fa-eye" aria-hidden="true"></i>
</a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="d-flex px-3 py-1">
                          <div>
                            <img src="{{asset('img/not-file.png')}}" class="avatar me-3" alt="image" id="img-Doda">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Doda</h6>
                            <p class="text-sm font-weight-bold text-secondary mb-0"><span class="text-muted" id="filSize-Doda">0</span></p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-sm font-weight-bold mb-0">
                        <input name="num_doda" id="num_doda" type="text" class="form-control" value="{{$documentacion->num_doda}}">
                            
                        </p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-sm font-weight-bold mb-0">
                            <span class="badge bg-gradient-warning badge-sm" id="badge-Doda">Pendiente</span>
                        </p>
                      </td>
                      <td class="align-middle text-end">
                        <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                        
                          <button type="button" id="btnFileDODA" class="btnDocs btn btn-sm btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Cargar archivo" data-bs-original-title="Cargar archivo">
                            <i class="fas fa-upload" aria-hidden="true"></i>
                          </button>
                          <a href="javasrcipt:void()" target="_blank" id="btn-ver-Doda" 
                          class="openFile btn btn-sm btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ver Documento" data-bs-original-title="Ver Documento">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="d-flex px-3 py-1">
                          <div>
                            <img src="{{asset('img/not-file.png')}}" class="avatar me-3" id="img-Carta-Porte" alt="image">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Carta Porte PDF</h6>
                            <p class="text-sm font-weight-bold text-secondary mb-0"><span class="text-muted" id="filSize-Carta-Porte">0</span></p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-sm font-weight-bold mb-0">
                        <input name="num_carta_porte" id="num_carta_porte" type="text" class="form-control" value="{{$documentacion->num_carta_porte}}">                            
                        </p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-sm font-weight-bold mb-0">
                            <span class="badge bg-gradient-warning badge-sm" id="badge-Carta-Porte">Pendiente</span>
                        </p>
                      </td>
                      <td class="align-middle text-end">
                        <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                        
                          <button type="button" id="btnFileCartaPortePDF" class="btnDocs btn btn-sm btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Cargar archivo" data-bs-original-title="Cargar archivo">
                            <i class="fas fa-upload" aria-hidden="true"></i>
                          </button>
                          <a href="javasrcipt:void()" target="_blank" class="btn btn-sm btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" id="btn-ver-Carta-Porte" data-bs-placement="bottom" title="Ver Documento" data-bs-original-title="Ver Documento">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    <tr>
                      <td>
                        <div class="d-flex px-3 py-1">
                          <div>
                            <img src="{{asset('img/not-file.png')}}" class="avatar me-3" alt="image" id="img-Carta-Porte-XML">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Carta Porte XML</h6>
                            <p class="text-sm font-weight-bold text-secondary mb-0"><span class="text-muted" id="filSize-Carta-Porte-XML">0</span></p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-sm font-weight-bold mb-0">
                        <input name="num_carta_porte_xml" id="num_carta_porte_xml" type="text" class="form-control" value="{{$documentacion->num_carta_porte}}">

                            
                        </p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-sm font-weight-bold mb-0">
                            <span class="badge bg-gradient-warning badge-sm" id="badge-Carta-Porte-XML">Pendiente</span>
                        </p>
                      </td>
                      <td class="align-middle text-end">
                        <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                        
                          <button type="button" id="btnFileCartaPorteXML" class="btnDocs btn btn-sm btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" data-bs-toggle="tooltip" data-bs-placement="bottom" title="Cargar archivo" data-bs-original-title="Cargar archivo">
                            <i class="fas fa-upload" aria-hidden="true"></i>
                          </button>
                          <a href="javasrcipt:void()" target="_blank" class="btn btn-sm btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" data-bs-placement="bottom" title="Ver Documento" data-bs-original-title="Ver Documento" id="btn-ver-Carta-Porte-XML">
                            <i class="fas fa-eye" aria-hidden="true"></i>
                          </a>
                        </div>
                      </td>
                    </tr>
                    

                    <tr>
                      <td>
                        <div class="d-flex px-3 py-1">
                          <div>
                            <img src="{{asset('img/not-file.png')}}" class="avatar me-3" alt="image" id="img-Pre-Alta">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">Prealta - Boleta vacío</h6>
                            <p class="text-sm font-weight-bold text-secondary mb-0">
                                <span class="text-muted" id="filSize-Pre-Alta">0</span>
                            </p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-sm font-weight-bold mb-0">
                        <input name="fecha_boleta_vacio" id="fecha_boleta_vacio" type="date" class="form-control" value="{{$documentacion->fecha_boleta_vacio}}">
                            
                        </p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-sm font-weight-bold mb-0">
                            <span class="badge bg-gradient-warning badge-sm" id="badge-Pre-Alta">Pendiente</span>
                        </p>
                      </td>
                      <td class="align-middle text-end">
                        <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                        
                          <button type="button" 
                          class="btn btn-sm btn-icon-only btnDocs btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" id="btnFilePre-Alta"
                          data-bs-placement="bottom" title="Cargar archivo" 
                          data-bs-original-title="Cargar archivo">
                            <i class="fas fa-upload" aria-hidden="true"></i>
                          </button>
                          <a href="javasrcipt:void()" target="_blank" class="openFile btn btn-sm btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" 
                          data-bs-placement="bottom" 
                          title="Ver Documento" 
                          data-bs-original-title="Ver Documento" id="btn-ver-Pre-Alta">
                            <i class="fas fa-eye" aria-hidden="true"></i>
</a>
                        </div>
                      </td>
                    </tr>

                    <tr>
                      <td>
                        <div class="d-flex px-3 py-1">
                          <div>
                            <img src="{{asset('img/not-file.png')}}" class="avatar me-3" alt="image" id="img-eir">
                          </div>
                          <div class="d-flex flex-column justify-content-center">
                            <h6 class="mb-0 text-sm">EIR - Comprobante vacío</h6>
                            <p class="text-sm font-weight-bold text-secondary mb-0">
                                <span class="text-muted" id="filSize-eir">0</span>
                            </p>
                          </div>
                        </div>
                      </td>
                      <td>
                        <p class="text-sm font-weight-bold mb-0">
                        <input name="fecha_eir" id="fecha_eir" type="date" class="form-control" value="{{$cotizacion->fecha_eir}}">
                            
                        </p>
                      </td>
                      <td class="align-middle text-center text-sm">
                        <p class="text-sm font-weight-bold mb-0">
                            <span class="badge bg-gradient-warning badge-sm" id="badge-eir">Pendiente</span>
                        </p>
                      </td>
                      <td class="align-middle text-end">
                        <div class="d-flex px-3 py-1 justify-content-center align-items-center">
                        
                          <button type="button" 
                          class="btn btn-sm btn-icon-only btnDocs btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" id="btnFileeir"
                          data-bs-placement="bottom" title="Cargar archivo" 
                          data-bs-original-title="Cargar archivo">
                            <i class="fas fa-upload" aria-hidden="true"></i>
                          </button>
                          <a href="javasrcipt:void()" target="_blank" class="openFile btn btn-sm btn-icon-only btn-rounded btn-outline-secondary mb-0 ms-2 btn-sm d-flex align-items-center justify-content-center ms-3" 
                          data-bs-toggle="tooltip" 
                          data-bs-placement="bottom" 
                          title="Ver Documento" 
                          data-bs-original-title="Ver Documento" id="btn-ver-eir">
                            <i class="fas fa-eye" aria-hidden="true"></i>
</a>
                        </div>
                      </td>
                    </tr>
                  </tbody>
                </table>
                                        </div>
                                       

                                      

                                      

                                      

                                     

                                 
                                    </div>
                                </div>

                                <div class="tab-pane fade" id="nav-Gastos" role="tabpanel" aria-labelledby="nav-Gastos-tab" tabindex="0">
                                    <h3 class="mt-3 mb-3">Gastos Extras</h3>
                                    @if ($documentacion->num_contenedor != NULL)
                                    <div class="d-flex justify-content-between">
                                        <label style="font-size: 20px;">Núm contenedor:  <span id="spanContenedor">{{$documentacion->num_contenedor}}</span> </label>
                                        <div>
                                            <button type="button" disabled class="btn btn-sm bg-gradient-danger" id="btnDelete">
                                                <i class="fa fa-trash"></i> Eliminar
                                            </button>
                                            <button type="button" data-bs-toggle="modal" data-bs-target="#modal-form" class="btn btn-sm bg-gradient-info">Agregar gasto</button>
                                        </div>
                                        
                                    </div>
                                        
                                    @endif
                                    <div class="row">
                                    
                                    <div id="gridGastos" class="col-12 ag-theme-quartz" style="height: 500px"></div>


                                        <div class="col-4 form-group">
                                            <label for="name">Total Gastos</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                </span>
                                                <input type="text" id="txtSumGastos" class="form-control txtSumGastos" value="0" readonly>
                                            </div>
                                        </div>
                                        <div class="col-4 form-group">
                                            <label for="name">Total Cotización</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                </span>
                                                <input type="text" id="txtTotalCotizacion" class="form-control total-cotizacion" value="{{ $cotizacion->total }}" readonly>
                                            </div>
                                        </div>
                                        <div class="col-4 form-group">
                                            <label for="name">Cotización + Gastos</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                </span>
                                                <input type="text" class="form-control txtResultGastos" id="txtResultGastos" value="{{ $cotizacion->total }}" readonly>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($cotizacion->estatus_planeacion == 1)
                                    @if ($documentacion->Asignaciones->id_operador == NULL)
                                        <div class="tab-pane fade" id="nav-Proveedor" role="tabpanel" aria-labelledby="nav-Proveedor-tab" tabindex="0">
                                             
                                       <div class="row">
                                       @if ($documentacion->num_contenedor != NULL)
                                                <label style="font-size: 20px;">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                            @endif
                                       </div>
                                            <div class="row">
                                                <ul class="list-group">
                                                    <li class="list-group-item border-1 border-dashed d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                                                    <div class="d-flex flex-column">
                                                        <h5 class="mb-2 text-md">Proveedor</h5>
                                                        <span class="mb-2 text-md">
                                                            Nombre: <span class="text-dark font-weight-bold ms-2">
                                                            <select class="form-select bg-transparent cliente d-inline-block"  data-toggle="select" id="id_proveedor" name="id_proveedor">
                                                                <option value="">Seleccionar Proveedor</option>
                                                                @foreach($proveedores as $p)
                                                                  <option value="{{$p->id}}" @if($p->id == $documentacion->Asignaciones->Proveedor->id) selected @endif>{{$p->nombre}}</option>
                                                                @endforeach
                                                            </select></span>
                                                        </span>

                                                    </div>
                                                    </li>
                                                </ul>
                                            </div>
                                            
                                           
                                            
                                            <div class="row">
                                                <div class="col-3 form-group">
                                                    <label for="name">Costo viaje</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="precio_proveedor" id="precio_proveedor" type="text" class="form-control moneyformat calculo-proveedor" value="{{$documentacion->Asignaciones->precio}}">
                                                    </div>
                                                </div>

                                                <div class="col-3 form-group">
                                                    <label for="name">Burreo</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/burro.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="burreo_proveedor" id="burreo_proveedor" type="float" class="form-control moneyformat calculo-proveedor" value="{{$documentacion->Asignaciones->burreo}}">
                                                    </div>
                                                </div>

                                                <div class="col-3 form-group">
                                                    <label for="name">Maniobra</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/logistica.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="maniobra_proveedor" id="maniobra_proveedor" type="float" class="form-control moneyformat calculo-proveedor" value="{{$documentacion->Asignaciones->maniobra}}">
                                                    </div>
                                                </div>

                                                <div class="col-3 form-group">
                                                    <label for="name">Estadia</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/servidor-en-la-nube.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="estadia_proveedor" id="estadia_proveedor" type="float" class="form-control moneyformat calculo-proveedor" value="{{$documentacion->Asignaciones->estadia}}">
                                                    </div>
                                                </div>

                                                <div class="col-3 form-group">
                                                    <label for="name">Sobrepeso</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input id="cantidad_sobrepeso_proveedor" name="cantidad_sobrepeso_proveedor" type="float" class="form-control calculo-proveedor" value="{{$cotizacion->sobrepeso}}" disabled>
                                                    </div>
                                                </div>

                                                <div class="col-3 form-group">
                                                    <label for="name">Precio Sobre Peso</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/pago-en-efectivo.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input id="sobrepeso_proveedor" name="sobrepeso_proveedor" value="{{$documentacion->Asignaciones->sobrepeso_proveedor}}" type="float" class="form-control moneyformat calculo-proveedor">
                                                    </div>
                                                </div>

                                                <div class="col-3 form-group">
                                                    <label for="name">Total tonelada {{ ($cotizacion->sobrepeso)}}</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/pago-en-efectivo.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input id="total_tonelada" name="total_tonelada" type="text" value="{{$documentacion->Asignaciones->total_tonelada}}" class="form-control moneyformat calculo-proveedor" readonly>
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Base 1</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/factura.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="base1_proveedor" id="base1_proveedor" type="float" class="form-control moneyformat calculo-proveedor" value="{{$documentacion->Asignaciones->base1_proveedor}}">
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Base 2</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="base2_proveedor" id="base2_proveedor" type="float" class="form-control moneyformat calculo-proveedor" readonly value="{{$documentacion->Asignaciones->base2_proveedor}}">
                                                    </div>
                                                </div>

                                                <div class="col-4"></div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Otros</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="otro_proveedor" id="otro_proveedor" type="float" class="form-control moneyformat calculo-proveedor" value="{{$documentacion->Asignaciones->otro}}">
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

                                             

                                                <div class="col-4 form-group">
                                                    <label for="name">IVA</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/impuesto.png') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="iva_proveedor" id="iva_proveedor" type="text" readonly class="form-control moneyformat calculo-proveedor" value="{{$documentacion->Asignaciones->iva}}">
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Retención</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="retencion_proveedor" id="retencion_proveedor" type="text" class="form-control moneyformat calculo-proveedor" readonly value="{{$documentacion->Asignaciones->retencion}}">
                                                    </div>
                                                </div>

                                                <div class="col-4 form-group">
                                                    <label for="name">Total</label>
                                                    <div class="input-group mb-3">
                                                        <span class="input-group-text" id="basic-addon1">
                                                            <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                        </span>
                                                        <input name="total_proveedor" id="total_proveedor" type="text" class="form-control moneyformat calculo-proveedor total-cotizacion-proveedor" value="{{$documentacion->Asignaciones->total_proveedor}}" readonly>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @elseif ($documentacion->Asignaciones->id_proveedor == NULL)
                                        <div class="tab-pane fade" id="nav-GastosOpe" role="tabpanel" aria-labelledby="nav-GastosOpe-tab" tabindex="0">
                                            <div class="col-sm-12">
                                                <div class="card card-body" id="profile">
                                                    <div class="row justify-content-between align-items-center">
                                                            <div class="col-sm-auto col-8 my-auto">
                                                                <div class="h-100">
                                                                <h5 class="mb-1 font-weight-bolder">
                                                                {{$documentacion->Asignaciones->Operador->nombre}}
                                                                </h5>
                                                                <p class="mb-0 font-weight-bold text-sm">
                                                                    Gastos Viaje
                                                                </p>
                                                                </div>
                                                            </div>
                                                            <div class="col-4 text-center">
                                                               
                                                                <button type="button" class="btn btn-sm bg-gradient-info" id="btnEdit">
                                                                    <i class="fa fa-fw fa-coins"></i>
                                                                    Editar
                                                                </button>
                                                                <button type="button" class="btn btn-sm bg-gradient-danger" id="btnDelete2">
                                                                    <i class="fa fa-fw fa-trash"></i>
                                                                    Eliminar
                                                                </button>
                                                                <button type="button" class="btn btn-sm bg-gradient-warning" id="btnPayment">
                                                                    <i class="fa fa-fw fa-coins"></i>
                                                                    Pagar
                                                                </button>
                                                                <button type="button" data-bs-toggle="modal" data-bs-target="#modal-gastos-operador" class="btn btn-sm bg-gradient-success" id="btnNuevoGasto">
                                                                Registrar Gasto
                                                                </button>
                                                            </div>
                                                    </div>
                                                </div>
                                            </div>
                                
                                            <div class="row">
                                              <div id="gridGastosOperador" class="col-12 ag-theme-quartz" style="height: 500px"></div>
                                           
                                            </div>
                                            <div class="row">
                                            <div class="card card-body" id="profile">
                                                <div class="row justify-content-between align-items-center">
                                                        <div class="col-sm-auto col-8 my-auto">
                                                        </div>
                                                        <div class="col-3 text-center">
                                                            <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                                                                <h6 class="text-primary mb-0">Total Gastos</h6>
                                                                <h4 class="font-weight-bolder"><span class="small" id="totalGastosOperador">$ 0.00</span></h4>
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
@include('cotizaciones.modal_agregar_gasto')
@include('cotizaciones.modal_agregar_gasto_operador')
@include('cotizaciones.modal_pagar_gastos_operador')
@include('cotizaciones.modal_fileuploader')
@endsection

@section('select2')
    <style>
        /* Fondo transparente y sin bordes */
    .select2-container .select2-selection--single {
    background-color: transparent !important;
    border: none !important;
    box-shadow: none !important; /* Eliminar sombras */
    }


    .select2-container .select2-selection--single:focus {
    outline: none !important;
    }


    .select2-container .select2-selection--single .select2-selection__rendered {
    color: inherit; /* Heredar color del texto */
    background-color: transparent !important;
    }
    </style>
    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js')}}"></script>
    <script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
    <script src="{{ asset('js/sgt/cotizaciones/cotizaciones.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones.js')) }}"></script>

    <link href="{{asset('assets/metronic/fileuploader/font/font-fileuploader.css')}}" rel="stylesheet">
    <link href="{{asset('assets/metronic/fileuploader/jquery.fileuploader.min.css')}}" media="all" rel="stylesheet">
    <link href="{{asset('assets/metronic/fileuploader/jquery.fileuploader-theme-dragdrop.css')}}" media="all" rel="stylesheet">
    <script src="{{asset('assets/metronic/fileuploader/jquery.fileuploader.min.js')}}" type="text/javascript"></script>

    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/cotizaciones/cotizacion-gastos.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizacion-gastos.js')) }}"></script>
    <script src="{{ asset('js/sgt/cotizaciones/cotizacion-gastos-operador.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizacion-gastos-operador.js')) }}"></script>

    <script src="{{ asset('js/sgt/cotizaciones/cotizacion-fileuploader.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizacion-fileuploader.js')) }}"></script>
    <style>
  .custom-nav-tabs {
    display: flex;
    width: 100%;
    border-bottom: none;
    gap: 0.3rem;
  }

  .custom-nav-item {
    flex: 1;
    text-align: center;
  }

  .custom-nav-link {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 14px;
    background-color: #f1f1f1;
    color: #999;
    border-radius: 12px 12px 0 0;
    cursor: pointer;
    border: 1px solid transparent;
    transition: all 0.3s ease;
  }

  .custom-nav-link i {
    font-size: 20px;
    color: inherit !important;
  }

  .custom-nav-link h6 {
    margin: 2px 0 0;
    font-size: 1rem;
    font-weight: 500;
    color: inherit;
  }

  .custom-nav-link.active {
    background-color: #fff;
    color: #111;
    border: 1px solid #0d6efd; /* más delgado */
    border-bottom: 2px solid #fff;
   
    transform: scale(1.02);
    z-index: 1;
  }

  .custom-nav-link.active h6 {
    font-weight: 600; /* negrita */
  }

  .custom-nav-link:not(.active) i {
    color: #bbb;
  }

  .custom-nav-link:not(.active) h6 {
    color: #aaa;
  }

  .custom-nav-radio {
    display: none;
  }
</style>
<style>
  .option-group {
    display: flex;
    flex-wrap: wrap;
    gap: 16px;
    max-width: 100%;
  }

  .custom-option {
    position: relative;
    display: flex;
    align-items: center;
    border: 1px dashed #ccc;
    border-radius: 8px;
    padding: 12px 16px;
    min-height: 79px;
    flex: 1 1 200px;
    cursor: pointer;
    transition: background-color 0.2s, border-color 0.2s;
  }

  .custom-option input[type="radio"] {
    display: none;
  }

  .custom-option .icon {
    margin-right: 16px;
    font-size: 24px;
    color: #ccc;
    flex-shrink: 0;
    transition: color 0.2s;
  }

  .custom-option .text {
    font-size: 1rem;
    color: #333;
  }

  .custom-option.selected {
    background-color: #e6f4ff;
    border-color: #007BFF;
  }

  .custom-option.selected .icon {
    color: #007BFF;
  }

  .check-icon {
  position: absolute;
  top: 50%;
  right: 8px;
  transform: translateY(-50%);
  background-color: #a5dc86;
  border-radius: 50%;
  padding: 4px;
  font-size: 14px;
  color: white;
  display: none;
}

  .custom-option.selected .check-icon {
    display: inline-block;
  }
</style>
<script>
  // JavaScript para manejar la clase 'active'
  const radios = document.querySelectorAll('.custom-nav-radio');
  const links = document.querySelectorAll('.custom-nav-link');

  radios.forEach((radio, index) => {
    radio.addEventListener('change', () => {
      links.forEach((link) => link.classList.remove('active')); // Remover la clase active de todos
      links[index].classList.add('active'); 
      let Contenedor = radios[index].value
      showInfoContenedor(Contenedor)
    });
  });

 
</script>

    <script type="text/javascript">
    $(document).ready(async function() {
        $('.cliente').select2();
        getGastosContenedor();
        getGastosOperador();
        btnPaymentStatus();

        adjuntarDocumentos();
        localStorage.setItem('numContenedor','{{$documentacion->num_contenedor}}'); 
        await getContenedoresOnFull()
       // showInfoContenedor('Contenedor-A')
      
        getFilesContenedor();
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
                            $('#id_subcliente').select2()
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

        $(document).ready(()=>{
            
            sobrePesoViaje()

            formFields.forEach((item) =>{
                if(item.type == "money") {
                    var field = document.getElementById(item.field);
                    field.value =  (field.value.length > 0) ? reverseMoneyFormat(field.value) : 0
                    field.value = moneyFormat(field.value || 0);
                }
            });

            formFieldsProveedor.forEach((item) =>{
                if(item.type == "money") {
                    var field = document.getElementById(item.id);
                    if(field){
                      field.value = (field.value.length > 0) ? reverseMoneyFormat(field.value) : 0
                      field.value = moneyFormat(field.value || 0);
                    }
                   
                }
            });

           
        })

        document.addEventListener('DOMContentLoaded', function () {
            
            let condicionRecinto = document.querySelectorAll('.recinto');
            let inputRecinto = document.querySelector('#input-recinto');
            let textRecinto = document.querySelector('#text_recinto');

            condicionRecinto.forEach(function(elemento) {
              //  elemento.classList.remove('active')
                elemento.addEventListener('click', function() {
                    inputRecinto.classList.toggle('d-none',elemento.attributes['data-kt-plan'].value != 'recinto-si') 
                    textRecinto.value = (elemento.attributes['data-kt-plan'].value != 'recinto-si') ? '' : 'recinto-si';
                });
             

            });
        });

    </script>

@endsection
