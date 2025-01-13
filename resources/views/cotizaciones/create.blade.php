@extends('layouts.app')

@section('template_title')
   Crear
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 class="mb-3">Crear Cotizacion</h3>
                            
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('store.cotizaciones') }}" id="cotizacionCreate" enctype="multipart/form-data" role="form">
                            @csrf

                            <div class="modal-body">
                                <div class="row">
                                <hr class="horizontal dark mt-0 mb-4">
                                    <div class="col-12">
                                        <div class="row">
                                            <!--div class="col-3">
                                                <label for="precio">Nuevo cliente</label><br>
                                                <button class="btn btn-success btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                                                    Agregar <img src="{{ asset('assets/icons/cliente.png') }}" alt="" width="25px">
                                                </button>
                                            </div-->
                                            <div class="col-4 col-md-5">
                                                <ul class="list-group">
                                                    <li class="list-group-item border-1 border-dashed d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                                                    <div class="d-flex flex-column">
                                                        <h6 class="mb-2 text-md">Cliente</h6>
                                                        <span class="mb-2 text-sm">
                                                            Nombre: <span class="text-dark font-weight-bold ms-2">
                                                            <select class="form-select bg-transparent cliente d-inline-block"  data-toggle="select" id="id_cliente" name="id_cliente" value="{{ old('id_cliente') }}">
                                                                <option value="">Seleccionar cliente</option>
                                                                @foreach ($clientes as $item)
                                                                    <option value="{{ $item->id }}">{{ucwords(strtolower( $item->nombre)) }} </option>
                                                                @endforeach
                                                            </select></span>
                                                        </span>
                                                        <span class="mb-2 text-sm">Teléfono: <span class="text-dark ms-2 font-weight-bold" id="telClient"></span></span>
                                                        <span class="text-xs">Correo Electrónico: <span class="text-dark ms-2 font-weight-bold" id="mailClient"></span></span>
                                                    </div>
                                                    </li>
                                                </ul>
                                            </div>

                                            <div class="col-4 col-md-5">
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
                                            <div class="col-lg-2 col-md-2 col-2 my-auto text-end">
                                            <a href="javascript:;" class="btn btn-sm bg-gradient-info mb-0"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Crear Cliente</font></font></a>
                                                <p class="text-sm mt-2 mb-0">
                                                   
                                                    <font style="vertical-align: inherit;">
                                                    ¿Cliente no registrado? Puede crearlo aquí
                                                    </font>
                                                   
                                                </p>
                                            </div>
                                        </div>
                                    </div>
                                <hr class="horizontal dark mt-0 mb-4">


                                    <div class="form-group col-12">
                                        <div class="collapse" id="collapseExample">
                                            <div class="card card-body">
                                                <div class="row">


                                                    <div class="col-4">
                                                        <label for="name">Nombre completo *</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="basic-addon1">
                                                                <img src="{{ asset('assets/icons/cliente.png') }}" alt="" width="29px">
                                                            </span>
                                                            <input  id="nombre_cliente" name="nombre_cliente" type="text" class="form-control" placeholder="Nombre(s) y Apellidos">
                                                        </div>
                                                    </div>

                                                    <div class="col-4">
                                                        <label for="name">Telefono *</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="basic-addon1">
                                                                <img src="{{ asset('assets/icons/phone.png') }}" alt="" width="29px">
                                                            </span>
                                                            <input  id="telefono_cliente" name="telefono_cliente" class="form-control" type="tel" minlength="10" maxlength="10" placeholder="555555555">
                                                        </div>
                                                    </div>

                                                    <div class="col-4">
                                                        <label for="name">Correo</label>
                                                        <div class="input-group mb-3">
                                                            <span class="input-group-text" id="basic-addon1">
                                                                <img src="{{ asset('assets/icons/correo-electronico.png') }}" alt="" width="29px">
                                                            </span>
                                                            <input  id="correo_cliente" name="correo_cliente" type="email" class="form-control" placeholder="correo@correo.com">
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Origen</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="origen" id="origen" autocomplete="off" type="text" class="form-control" value="{{old('origen')}}">@error('origen') <span class="error text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Destino</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="destino" id="destino" autocomplete="off" type="text" class="form-control" value="APARTADO">@error('destino') <span class="error text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Num. Contenedor</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="num_contenedor" id="num_contenedor" type="text" class="form-control" autocomplete="off">@error('num_contenedor') <span class="error text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Tamaño Contenedor</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/escala.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="tamano" id="tamano" autocomplete="off" type="text" oninput="allowOnlyDecimals(event)" class="form-control"value="{{old('tamano')}}">@error('tamano') <span class="error text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Peso Reglamentario</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/perdida-de-peso.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="peso_reglamentario" autocomplete="off" id="peso_reglamentario" type="number" class="form-control calculo-cotizacion" value="22">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Peso Contenedor</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/peso.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="peso_contenedor" id="peso_contenedor" autocomplete="off" type="text" class="form-control calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Sobrepeso</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/pesa-rusa.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="sobrepeso" id="sobrepeso" type="text" autocomplete="off" class="form-control calculo-cotizacion" readonly>
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Precio Sobre Peso</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="precio_sobre_peso" id="precio_sobre_peso" type="text" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Precio Tonelada</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="precio_tonelada" id="precio_tonelada" type="text" autocomplete="off" class="form-control moneyformat calculo-cotizacion" value="0" oninput="allowOnlyDecimals(event)" readonly>
                                        </div>
                                    </div>

                                    <div class="col-3"></div>

                                    <div class="col-3 form-group">
                                        <label for="name">Precio Viaje</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="precio_viaje" id="precio_viaje" type="text" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Burreo</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/burro.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="burreo" id="burreo" type="float" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Maniobra</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/logistica.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="maniobra" id="maniobra" type="float" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Estadia</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/servidor-en-la-nube.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="estadia" id="estadia" type="float" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Otros</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="otro" id="otro" type="float" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">IVA</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/impuesto.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="iva" id="iva" type="text" autocomplete="off" readonly class="form-control" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Retención</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="retencion" readonly id="retencion" autocomplete="off" type="text" class="form-control" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Base 1</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/factura.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="base_factura" id="base_factura" autocomplete="off" type="float" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Base 2</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="base_taref" id="base_taref" autocomplete="off" type="float" readonly class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>
                                    <div class="col-4"></div>

                                    <div class="col-4 form-group">
                                        <label for="name">Total</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="total" id="total" readonly type="float" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Fecha modulación</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="fecha_modulacion" id="fecha_modulacion" type="date" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Fecha entrega</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="fecha_entrega" id="fecha_entrega" type="date" class="form-control">
                                        </div>
                                    </div>

                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn bg-gradient-primary btn-sm mb-0">Guardar</button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <input type="hidden" id="txtClientes" value ="{{($clientes)}}">
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
@endsection

@push('custom-javascript')
<script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
<script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js')}}"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/cotizaciones/cotizaciones.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones.js')) }}"></script>


<script>
   $(document).ready(()=>{
        $('.cliente').select2();
        $('#id_subcliente').select2();
   });
</script>
@endpush