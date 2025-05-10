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
                        <div class="col-6 ">

                            <div class="option-group">
                                <label class="custom-option selected">
                                    <input type="radio" checked name="plan" value="Sencillo" onchange="handleSelection(this)">
                                    <i class="fas fa-truck icon"></i>
                                    <span class="text">Sencillo</span>
                                    <i class="fas fa-check check-icon"></i>
                                </label>

                                <label class="custom-option">
                                    <input type="radio" name="plan" value="Full" onchange="handleSelection(this)">
                                    <i class="fas fa-truck-moving icon"></i>
                                    <span class="text">Full</span>
                                    <i class="fas fa-check check-icon"></i>
                                </label>
                            </div>
                        </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('v2store.cotizaciones') }}" 
                        id="cotizacionCreateMultiple" enctype="multipart/form-data" sgt-cotizacion-action="create" role="form">
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
                                            <div class="col-4 col-md-6">
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

                                            <div class="col-4 col-md-6">
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
                                            <!--div class="col-lg-2 col-md-2 col-2 my-auto text-end">
                                                <a href="javascript:;" class="btn btn-sm bg-gradient-info mb-0"><font style="vertical-align: inherit;"><font style="vertical-align: inherit;">Crear Cliente</font></font></a>
                                                <p class="text-sm mt-2 mb-0">
                                                   
                                                    <font style="vertical-align: inherit;">
                                                    ¿Cliente no registrado? Puede crearlo aquí
                                                    </font>
                                                   
                                                </p>
                                            </div-->
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
                                        <label for="name">Precio Sobre Peso</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="precio_sobre_peso" id="precio_sobre_peso" type="text" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Sobre Peso Viaje</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/peso.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="sobrepeso_viaje" id="sobrepeso_viaje" type="text" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Total Sobre Peso Viaje</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/peso.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="total_sobrepeso_viaje" id="total_sobrepeso_viaje" type="text" autocomplete="off" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
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
                                   
                                    

                                    <div class="col-4 form-group">
                                        <label for="name">Total</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="total" id="total" readonly type="float" class="form-control moneyformat calculo-cotizacion" oninput="allowOnlyDecimals(event)">
                                        </div>
                                    </div>

                                    <div class="col-12">
                                        <div class="custom-nav-tabs">
                                            <label class="custom-nav-item">
                                                <input type="radio" checked="checked" value="Contenedor-A" class="custom-nav-radio" name="contenedorTabs" id="tab1" />
                                                <div class="custom-nav-link active">
                                               
                                                <h6><i class="ni ni-box-2 text-warning text-gradient"></i> Contenedor A  </h6>
                                                </div>
                                            </label>

                                            <label class="custom-nav-item d-none" id="tab-contenedor-b">
                                                <input type="radio" class="custom-nav-radio" value="Contenedor-B" name="contenedorTabs" id="tab2" />
                                                <div class="custom-nav-link">
                                               
                                                <h6> <i class="ni ni-box-2 text-info text-gradient"></i> Contenedor B</h6>
                                                </div>
                                            </label>
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
                                        <label for="name">Sobrepeso Contenedor</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/pesa-rusa.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="sobrepeso" id="sobrepeso" type="text" autocomplete="off" class="form-control calculo-cotizacion" readonly>
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
  padding: 3px; /* Ajustado para reducir la altura */
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

@endsection

@push('custom-javascript')
<script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
<script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js')}}"></script>
<script src="{{ asset('js/sgt/common.js') }}?v={{ filemtime(public_path('js/sgt/common.js')) }}"></script>
<script src="{{ asset('js/sgt/cotizaciones/cotizaciones.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones.js')) }}"></script>
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

<script>
  function handleSelection(input) {
    let tabB = document.querySelector("#tab-contenedor-b")
    document.querySelectorAll('.custom-option').forEach(opt =>{
         opt.classList.remove('selected')
         
        });
    input.parentElement.classList.add('selected');
    if(input.parentElement.innerText == "Full") {tabB.classList.remove('d-none')}  else {tabB.classList.add('d-none')}
    sobrePesoViaje()
  }
</script>
<script>
   $(document).ready(()=>{
        $('.cliente').select2();
        $('#id_subcliente').select2();
        initContenedores('Contenedor-A')
        initContenedores('Contenedor-B')
        showInfoContenedor('Contenedor-A')
   });
</script>
@endpush