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
                            <a class="btn"  href="{{ route('index.cotizaciones_manual') }}" style="background: {{$configuracion->color_boton_close}}; color: #ffff;margin-right: 3rem;">
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
                                    <input name="id_cliente_clientes" id="id_cliente_clientes" type="hidden" class="form-control" value="{{auth()->user()->id_cliente}}">

                                    <div class="col-12">
                                        <div class="form-group">
                                            <label for="name">Subcliente *</label>
                                            <select class="form-select cliente d-inline-block"  data-toggle="select" id="id_cliente" name="id_cliente" value="{{ old('id_cliente') }}">
                                                <option value="">Seleccionar Subcliente</option>
                                                @foreach ($subclientes as $item)
                                                    <option value="{{ $item->id }}">{{ $item->nombre }} / {{ $item->telefono }}</option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-4">
                                        <div class="form-group">
                                            <label for="name">¿Pasa por recinto?</label>
                                            <select class="form-select d-inline-block"  data-toggle="select" id="recinto_clientes" name="recinto_clientes">
                                                <option value="Si">Si</option>
                                                <option value="No">No</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Origen</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="origen" id="origen" type="text" class="form-control"value="{{old('origen')}}">@error('origen') <span class="error text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Destino</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="destino" id="destino" type="text" class="form-control" value="APARTADO">@error('destino') <span class="error text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="col-4 form-group">
                                        <label for="name">Num. Contenedor</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px">
                                            </span>
                                            <input name="num_contenedor" id="num_contenedor" type="text" class="form-control" value="{{old('num_contenedor')}}">@error('num_contenedor') <span class="error text-danger">{{ $message }}</span> @enderror
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

                                    <div class="col-4 form-group">
                                        <label for="name">Excel</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="excel_clientes" id="excel_clientes" type="file" class="form-control">
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
            const otro = parseFloat(document.getElementById('otro').value.replace(/,/g, '')) || 0;
            const estadia = parseFloat(document.getElementById('estadia').value.replace(/,/g, '')) || 0;
            const maniobra = parseFloat(document.getElementById('maniobra').value.replace(/,/g, '')) || 0;

            const subTotal = precio_viaje + burreo + maniobra + estadia + otro;
            calcularImpuestos(subTotal);
            const retencion = parseFloat(document.getElementById('retencion').value.replace(/,/g, '')) || 0;
            const iva = parseFloat(document.getElementById('iva').value.replace(/,/g, '')) || 0;
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

        function calcularImpuestos(total){
            const baseFactura = parseFloat(document.getElementById('base_factura').value.replace(/,/g, '')) || 0;
            //const total = parseFloat(document.getElementById('total').value.replace(/,/g, '')) || 0;


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

                //calcularImpuestos();

                // Realizar el cálculo
                const baseTaref = (total - baseFactura - iva) + retencion;

                // Mostrar el resultado en el input de base_taref
                document.getElementById('base_taref').value = baseTaref.toFixed(2);
            }

            // Agregar eventos de cambio a los inputs para calcular automáticamente
           // document.getElementById('total').addEventListener('input', calcularBaseTaref);
            document.getElementById('base_factura').addEventListener('input', calcularTotal);
           // document.getElementById('iva').addEventListener('input', calcularBaseTaref);
          //  document.getElementById('retencion').addEventListener('input', calcularBaseTaref);
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
