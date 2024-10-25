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
                            <a class="btn"  href="{{ route('index.cotizaciones') }}" style="background: {{$configuracion->color_boton_close}}; color: #ffff;margin-right: 3rem;">
                                Regresar
                            </a>
                            <h3 class="mb-3">Crear Cotizacion</h3>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('store.cotizaciones') }}" id="" enctype="multipart/form-data" role="form">
                            @csrf

                            <div class="modal-body">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="row">
                                            <div class="col-3">
                                                <label for="precio">Nuevo cliente</label><br>
                                                <button class="btn btn-success btn-sm" type="button" data-bs-toggle="collapse" data-bs-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                                                    Agregar <img src="{{ asset('assets/icons/cliente.png') }}" alt="" width="25px">
                                                </button>
                                            </div>
                                            <div class="col-4">
                                                <div class="form-group">
                                                    <label for="name">Cliente *</label>
                                                    <select class="form-select cliente d-inline-block"  data-toggle="select" id="id_cliente" name="id_cliente" value="{{ old('id_cliente') }}">
                                                        <option value="">Seleccionar cliente</option>
                                                        @foreach ($clientes as $item)
                                                            <option value="{{ $item->id }}">{{ $item->nombre }} / {{ $item->telefono }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="col-4">
                                                <div class="form-group">
                                                    <label for="name">Subcliente *</label>
                                                    <select class="form-select subcliente d-inline-block" id="id_subcliente" name="id_subcliente">
                                                        <option value="">Seleccionar subcliente</option>
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

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
                                            <input name="origen" id="origen" type="text" class="form-control"value="{{old('origen')}}">@error('origen') <span class="error text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Destino</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="destino" id="destino" type="text" class="form-control" value="APARTADO">@error('destino') <span class="error text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Num. Contenedor</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="num_contenedor" id="num_contenedor" type="text" class="form-control" value="{{old('num_contenedor')}}">@error('num_contenedor') <span class="error text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Tamaño Contenedor</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/escala.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="tamano" id="tamano" type="text" class="form-control"value="{{old('tamano')}}">@error('tamano') <span class="error text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Peso Reglamentario</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/perdida-de-peso.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="peso_reglamentario" id="peso_reglamentario" type="number" class="form-control" value="22">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Peso Contenedor</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/peso.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="peso_contenedor" id="peso_contenedor" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Sobrepeso</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/pesa-rusa.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="sobrepeso" id="sobrepeso" type="text" class="form-control" readonly>
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Precio Sobre Peso</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="precio_sobre_peso" id="precio_sobre_peso" type="text" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Precio Tonelada</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="precio_tonelada" id="precio_tonelada" type="text" class="form-control" value="0" readonly>
                                        </div>
                                    </div>

                                    <div class="col-3"></div>

                                    <div class="col-3 form-group">
                                        <label for="name">Precio Viaje</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="precio_viaje" id="precio_viaje" type="text" class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Burreo</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/burro.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="burreo" id="burreo" type="float" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Maniobra</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/logistica.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="maniobra" id="maniobra" type="float" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()">
                                        </div>
                                    </div>

                                    <div class="col-3 form-group">
                                        <label for="name">Estadia</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/servidor-en-la-nube.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="estadia" id="estadia" type="float" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Otros</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="otro" id="otro" type="float" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">IVA</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/impuesto.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="iva" id="iva" type="number" readonly class="form-control" onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Retención</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="retencion" readonly id="retencion" type="float" class="form-control"onkeypress="return event.charCode >= 48 && event.charCode <= 57 || event.charCode === 46" oninput="calcularTotal()">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Base 1</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/factura.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="base_factura" id="base_factura" type="float" class="form-control">
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Base 2</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="base_taref" id="base_taref" type="float" readonly class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-4"></div>

                                    <div class="col-4 form-group">
                                        <label for="name">Total</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="total" id="total" type="float" class="form-control">
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

        const tasa_iva = 0.16;
        const tasa_retencion = 0.04;

        function moneyFormat(moneyValue){
            const $formatMoneda = new Intl.NumberFormat('es-MX', {
                style: 'currency',
                currency: 'MXN',
                minimumFractionDigits: 2
            }).format(moneyValue);

            return $formatMoneda;
        }

        function calcularTotal() {
            const precio_viaje = parseFloat(document.getElementById('precio_viaje').value.replace(/,/g, '')) || 0;
            const burreo = parseFloat(document.getElementById('burreo').value.replace(/,/g, '')) || 0;
            const retencion = parseFloat(document.getElementById('retencion').value.replace(/,/g, '')) || 0;
            const iva = parseFloat(document.getElementById('iva').value.replace(/,/g, '')) || 0;
            const otro = parseFloat(document.getElementById('otro').value.replace(/,/g, '')) || 0;
            const estadia = parseFloat(document.getElementById('estadia').value.replace(/,/g, '')) || 0;
            const maniobra = parseFloat(document.getElementById('maniobra').value.replace(/,/g, '')) || 0;

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
            calcularImpuestos();
        }

        function calcularImpuestos(){
          //  const precio_viaje = parseFloat(document.getElementById('precio_viaje').value.replace(/,/g, '')) || 0;
            const baseFactura = parseFloat(document.getElementById('base_factura').value.replace(/,/g, '')) || 0;
            const total = parseFloat(document.getElementById('total').value.replace(/,/g, '')) || 0;


            const iva = (baseFactura * tasa_iva);
            const retencion = (baseFactura * tasa_retencion);

            document.getElementById('iva').value = (iva.toFixed(2));
            document.getElementById('retencion').value = (retencion.toFixed(2));
            // Realizar el cálculo
            const baseTaref = (total - baseFactura - iva) + retencion;

            // Mostrar el resultado en el input de base_taref
            document.getElementById('base_taref').value = baseTaref.toFixed(2);
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Obtener elementos del DOM
            var pesoReglamentarioInput = document.getElementById('peso_reglamentario');
            var pesoContenedorInput = document.getElementById('peso_contenedor');
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
            }

            // Agregar evento de entrada al campo "Precio Sobre Peso"
            precioSobrePesoInput.addEventListener('input', function () {
                // Obtener el valor de Sobrepeso
                var sobrepeso = parseFloat(sobrepesoInput.value.replace(/,/g, '')) || 0;

                // Obtener el valor de Precio Sobre Peso
                var precioSobrePeso = parseFloat(precioSobrePesoInput.value.replace(/,/g, '')) || 0;

                // Calcular el resultado de la multiplicación
                var resultado = sobrepeso * precioSobrePeso;

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
                const precio_viaje = parseFloat(document.getElementById('precio_viaje').value.replace(/,/g, '')) || 0;
                const baseFactura = parseFloat(document.getElementById('base_factura').value) || 0;

                //Calculamos IVA y retencion
                const iva = (baseFactura * tasa_iva);
                const retencion = (baseFactura * tasa_retencion);

                calcularImpuestos();

                // Realizar el cálculo
                const baseTaref = (total - baseFactura - iva) + retencion;

                // Mostrar el resultado en el input de base_taref
                document.getElementById('base_taref').value = baseTaref.toFixed(2);
            }

            // Agregar eventos de cambio a los inputs para calcular automáticamente
            document.getElementById('total').addEventListener('input', calcularBaseTaref);
            document.getElementById('base_factura').addEventListener('input', calcularBaseTaref);
            document.getElementById('iva').addEventListener('input', calcularBaseTaref);
            document.getElementById('retencion').addEventListener('input', calcularBaseTaref);
        });

        $(document).ready(function() {
            $('#id_cliente').change(function() {
                var clienteId = $(this).val();
                if (clienteId) {
                    $.ajax({
                        type: 'GET',
                        url: '/subclientes/' + clienteId,
                        success: function(data) {
                            $('#id_subcliente').empty();
                            $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
                            $.each(data, function(key, subcliente) {
                                $('#id_subcliente').append('<option value="' + subcliente.id + '">' + subcliente.nombre + '</option>');
                            });
                        }
                    });
                } else {
                    $('#id_subcliente').empty();
                    $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
                }
            });
        });
    </script>


@endsection
