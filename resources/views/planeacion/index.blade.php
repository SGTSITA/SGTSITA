@extends('layouts.app')

@section('template_title')
    Planeacion
@endsection

@section('content')
@php
use Carbon\Carbon;
@endphp
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            

                             <div class="float-right">
                                @can('cotizaciones-create')
                                <a type="button" class="btn btn-primary" href="{{ route('create.cotizaciones') }}" style="background: {{$configuracion->color_boton_add}}; color: #ffff">
                                    Crear
                                </a>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-sm-6">
                            <div class="card">
                                <form action="{{ route('advance_planeaciones.buscador') }}" method="GET">
                                    <div class="card-body" style="padding-left: 1.5rem; padding-top: 1rem;">
                                        <h5>Buscar en Almanaque</h5>
                                        <div class="row">
                                            <div class="col-6">
                                                <select class="form-control contenedor" name="contenedor" id="contenedor">
                                                    <option selected value="">Seleccionar contenedor</option>
                                                    @foreach($planeaciones as $planeacion)
                                                    <option value="{{ $planeacion->id }}">{{ $planeacion->num_contenedor }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-6">
                                                <button class="btn btn-sm mb-0 mt-sm-0 mt-1" type="submit" style="background-color: #F82018; color: #ffffff;">Buscar</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-sm-6">
                            <div class="card">
                                <form action="{{ route('advance_planeaciones_faltantes.buscador') }}" method="GET">
                                    <div class="card-body" style="padding-left: 1.5rem; padding-top: 1rem;">
                                        <h5>Buscar en Faltantes</h5>
                                        <div class="row">
                                            <div class="col-9">
                                                <select class="form-control contenedor_faltantes" name="contenedor_faltantes" id="contenedor_faltantes">
                                                    <option selected value="">seleccionar contenedor</option>
                                                    @foreach($cotizaciones as $cotizacion)
                                                    <option value="{{ $cotizacion->id }}">{{$cotizacion->Cliente->nombre}} / #{{ $cotizacion->DocCotizacion->num_contenedor }}
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-3">
                                                <button class="btn btn-sm mb-0 mt-sm-0 mt-1" type="submit" style="background-color: #F82018; color: #ffffff;">Buscar</button>
                                            </div>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <div class="col-12">
                            @if(Route::currentRouteName() == 'advance_planeaciones.buscador')
                                <table class="table table-flush" id="datatable-search">
                                    <thead class="thead">
                                        <tr>
                                            <th>#</th>
                                            <th><img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">Fecha Inicio</th>
                                            <th><img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">Fecha Fin</th>
                                            <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen</th>
                                            <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino</th>
                                            <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                            <th><img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            <tr>
                                                <td>{{ $asignaciones->id }}</td>
                                                <td>{{ Carbon::parse($asignaciones->fecha_inicio)->format('d-m-Y') }}</td>
                                                <td>
                                                    @if ($asignaciones->fecha_fin == NULL)
                                                        {{ Carbon::parse($asignaciones->fecha_inicio)->format('d-m-Y') }}
                                                    @else
                                                        {{ Carbon::parse($asignaciones->fecha_fin)->format('d-m-Y') }}
                                                    @endif
                                                </td>
                                                <td>{{$asignaciones->Contenedor->Cotizacion->origen}}</td>
                                                <td>{{$asignaciones->Contenedor->Cotizacion->destino}}</td>
                                                <td>{{$asignaciones->Contenedor->num_contenedor}}</td>
                                                <td>
                                                    <a type="button" class="btn btn-xs" href="{{ route('edit.cotizaciones', $asignaciones->Contenedor->id_cotizacion) }}">
                                                        <img src="{{ asset('img/icon/quotes.webp') }}" alt="" width="25px">
                                                    </a>
                                                    <a type="button" class="btn btn-xs" href="{{ route('index.cooredenadas', $asignaciones->id) }}">
                                                        <img src="{{ asset('img/icon/coordenadas.png') }}" alt="" width="25px">
                                                    </a>
                                                </td>
                                            </tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>

                        <div class="col-12">
                            @if(Route::currentRouteName() == 'advance_planeaciones_faltantes.buscador')
                                <table class="table table-flush" id="datatable-search">
                                    <thead class="thead">
                                        <tr>
                                            <th>#</th>
                                            <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen</th>
                                            <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino</th>
                                            <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                            <th><img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">Accion</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                            <tr>
                                                <td>{{ $cotizaciones_faltantes->id }}</td>
                                                <td>{{$cotizaciones_faltantes->origen}}</td>
                                                <td>{{$cotizaciones_faltantes->destino}}</td>
                                                <td>{{$cotizaciones_faltantes->DocCotizacion->num_contenedor}}</td>
                                                <td>
                                                    <a type="button" class="btn btn-xs" href="{{ route('edit.cotizaciones', $cotizaciones_faltantes->id) }}">
                                                        <img src="{{ asset('img/icon/quotes.webp') }}" alt="" width="25px">
                                                    </a>
                                                    <button type="button" class="btn btn-xs" data-bs-toggle="modal" data-bs-target="#planeacionModal{{$cotizaciones_faltantes->id}}">
                                                        <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                                    </button>
                                                </td>
                                            </tr>
                                    </tbody>
                                </table>
                            @endif
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <div class="col-12 col-md-12 col-lg-2">
                                <h4>Faltantes de Planeación</h4>
                                <div class="row">
                                    @foreach ($cotizaciones as $cotizacion)
                                        <div class="col-{{$numCotizaciones <= 15 ? '12' : '6'}}">
                                            <button type="button" class="btn btn-xs btn-primary w-100" data-bs-toggle="modal" data-bs-target="#planeacionModal{{$cotizacion->id}}">
                                                   {{$cotizacion->Cliente->nombre}} / #{{ $cotizacion->DocCotizacion->num_contenedor }}
                                            </button>
                                        </div>
                                        @include('planeacion.edit')
                                    @endforeach
                                </div>
                            </div>

                            <div class="col-12 col-md-12 col-lg-10">
                                <h3>Calendario</h3>
                                <div id="calendar"></div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('planeacion.modal')

@endsection

@section('fullcalendar')
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>

    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js')}}"></script>
    <script>
        $(document).ready(function() {
            $('.contenedor').select2();
        });

        $(document).ready(function() {
            $('.contenedor_faltantes').select2();
        });

        document.addEventListener('DOMContentLoaded', function () {
            const formulario = document.getElementById('miFormulario');
            const btnEnviar = document.getElementById('btnEnviar');

            formulario.addEventListener('submit', function () {
                btnEnviar.disabled = true;
                btnEnviar.innerText = 'Guardando...'; // Cambia el texto del botón si lo deseas
            });
        });
    </script>
    <script type="text/javascript">

        document.addEventListener('DOMContentLoaded', function () {
            
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                events: @json($events),
                height: 'auto',
                initialDate: '{{ date('Y-m-d')}}',
                initialView: 'dayGridMonth',
                navLinks: false,
                editable: true,
                dayMaxEvents: 3,


                headerToolbar:{
                    left:'prev,next today',
                    center:'title',
                    right: 'listMonth,dayGridMonth'

                    },

                    views: {
                    dayGridMonth: {
                        buttonText: 'MES'
                    },
                    listMonth: {
                        buttonText: 'LISTA'
                    }
                },
                eventClick: function(info) {
                    // Colocar los detalles del evento en el modal
                    document.getElementById('eventoTitulo').innerText = info.event.title;
                    document.getElementById('eventoDescripcion').innerText = info.event.extendedProps.description;

                    // Formatear las fechas para los inputs y ajustarlas
                    var fechaInicio = formatDate(info.event.start);
                    var fechaFin = formatDate(info.event.end);
                    var urlId = info.event.extendedProps.urlId;

                    // Obteniendo la URL de la ruta sin el parámetro
                    var telOperadorBaseUrl = '{{ route('index.cooredenadas', ['id' => 'ID_PLACEHOLDER']) }}';
                    // Reemplazando 'ID_PLACEHOLDER' con el ID real en JavaScript
                    telOperadorBaseUrl = telOperadorBaseUrl.replace('ID_PLACEHOLDER', info.event.extendedProps.idCoordenda);
                    // Ahora puedes usar la URL completa
                    var telOperadorUrl = telOperadorBaseUrl;

                    var cotizacionBaseUrl = '{{ route('edit.cotizaciones', ['id' => 'ID_PLACEHOLDER']) }}';
                    cotizacionBaseUrl = cotizacionBaseUrl.replace('ID_PLACEHOLDER', info.event.extendedProps.idCotizacion);


                    // Establecer los valores en los inputs del formulario
                    document.getElementById('eventoFechaStart').value = fechaInicio;
                    document.getElementById('eventoFechaEnd').value = fechaFin;
                    document.getElementById('idCotizacion').setAttribute('href', cotizacionBaseUrl);

                    // Establecer el atributo href del elemento
                    document.getElementById('idCoordenda').setAttribute('href', telOperadorBaseUrl);

                    document.getElementById('urlId').value = urlId;
                    document.getElementById('telOperadorUrl').value = telOperadorUrl;
                    // Completar los valores del formulario si existen
                    document.getElementById('nombreOperadorSub').value = info.event.extendedProps.nombreOperadorSub || '';
                    document.getElementById('telefonoOperadorSub').value = info.event.extendedProps.telefonoOperadorSub || '';
                    document.getElementById('placasOperadorSub').value = info.event.extendedProps.placasOperadorSub || '';


                    // Mostrar u ocultar el formulario del operador según isOperadorNull
                    const formularioOperador = document.getElementById('formularioOperador');
                    if (info.event.extendedProps.isOperadorNull) {
                        console.log("Operador es NULL");
                        formularioOperador.style.display = 'block';
                    } else {
                        console.log("Operador NO es NULL");
                        formularioOperador.style.display = 'none';
                    }

                    // Mostrar el modal
                    var eventoModal = new bootstrap.Modal(document.getElementById('eventoModal'));
                    eventoModal.show();

                    // Funcionalidad para copiar al portapapeles
                    var telOperadorBtn = document.getElementById('telOperador');

                    telOperadorBtn.addEventListener('click', function() {
                    var url = document.getElementById('telOperadorUrl').value;
                    console.log('entro');
                    console.log(url);

                    navigator.clipboard.writeText(url).then(function() {
                        $(telOperadorBtn).popover('show');
                        setTimeout(function () {
                            $(telOperadorBtn).popover('hide');
                        }, 2000);
                    }).catch(function(err) {
                        console.error('Error al copiar al portapapeles: ', err);
                    });
                });

                    // Configurar el popover de Bootstrap
                    $(telOperadorBtn).popover({
                        trigger: 'manual',
                        content: 'URL copiada!',
                        placement: 'top'
                    });

                    // Escuchar el clic en el botón de "Actualizar fecha"
                    document.getElementById('actualizarFechaBtn').addEventListener('click', function () {
                        // Obtener los nuevos valores de las fechas
                        var finzalizar_vieje = document.getElementById('finzalizar_vieje').value;
                        var nuevaFechaInicio = document.getElementById('eventoFechaStart').value;
                        var nuevaFechaFin = document.getElementById('eventoFechaEnd').value;
                        var urlId = document.getElementById('urlId').value;
                        var idCoordenda = info.event.extendedProps.idCoordenda;
                        var nombreOperadorSub = document.getElementById('nombreOperadorSub').value;
                        var telefonoOperadorSub = document.getElementById('telefonoOperadorSub').value;
                        var placasOperadorSub = document.getElementById('placasOperadorSub').value;


                        $.ajax({
                            url: '{{ route('asignacion.edit_fecha') }}',
                            type: 'post',
                            data: {
                                'finzalizar_vieje': finzalizar_vieje,
                                'nuevaFechaInicio': nuevaFechaInicio,
                                'nuevaFechaFin': nuevaFechaFin,
                                'nombreOperadorSub': nombreOperadorSub,
                                'telefonoOperadorSub': telefonoOperadorSub,
                                'placasOperadorSub': placasOperadorSub,
                                'urlId': urlId,
                                'idCoordenda': idCoordenda,
                                '_token': token // Agregar el token CSRF a los datos enviados
                            },
                            success: function(data) {
                                // Realizar alguna acción si la solicitud es exitosa
                                Swal.fire('',data.message,data.TMensaje).then((click)=>{
                                     location.reaload();
                                    if(!click.isConfirmed){
                                       
                                    }
                                })
                                
                            },
                            error: function(error) {
                                console.log(error);
                            },
                            complete: function() {
                                // Ocultar el spinner cuando la búsqueda esté completa
                               
                               
                            }
                        });


                    });
                }

            });

            @foreach ($cotizaciones as $cotizacion)
            $("#miFormulario{{$cotizacion->id}}").on("submit", function (event) {
                event.preventDefault();
                var form = $(this);
                var btnEnviar = form.find('.btnEnviar');
                var spinner = form.find('#loadingSpinner');

                btnEnviar.prop('disabled', true);
                spinner.show();

                $.ajax({
                    url: form.attr("action"),
                    type: "POST",
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        alert('Agregada con Exito');
                        location.reload();
                    },
                    error: function (xhr, status, error) {
                        var errors = xhr.responseJSON.errors;
                        var errorMessage = '';

                        for (var key in errors) {
                            if (errors.hasOwnProperty(key)) {
                                var errorMessages = errors[key].join('<br>');
                                errorMessage += '<strong>' + key + ':</strong><br>' + errorMessages + '<br>';
                            }
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Faltan Campos',
                            html: errorMessage,
                        });

                        btnEnviar.prop('disabled', false);
                        spinner.hide();
                    }
                });
            });
            @endforeach

            calendar.render();

            $('[id^="tipo"]').change(function() {
            // Obtén el valor seleccionado del elemento actual
            var tipo = $(this).val();
            // Obtén el número de ID eliminando 'tipo' del ID del elemento actual
            var idNum = $(this).attr('id').replace('tipo', '');
            // Construye los selectores de los grupos de elementos adicionales utilizando el número de ID
            var chasisAdicional1Group = $('#chasisAdicional1Group');
            var nuevoCampoDolyGroup = $('#nuevoCampoDolyGroup');

            if (tipo === 'Sencillo') {
                chasisAdicional1Group.hide();
                nuevoCampoDolyGroup.hide();
            } else if (tipo === 'Full') {
                chasisAdicional1Group.show();
                nuevoCampoDolyGroup.show();
            }
        });

        $('[id^="tipo"]').change(function() {
            // Obtén el valor seleccionado del elemento actual
            var tipo = $(this).val();
            // Obtén el número de ID eliminando 'tipo' del ID del elemento actual
            var idNum = $(this).attr('id').replace('tipo', '');
            // Construye los selectores de los grupos de elementos adicionales utilizando el número de ID
            var chasisAdicional1Group = $('#chasisAdicional1Group');
            var nuevoCampoDolyGroup = $('#nuevoCampoDolyGroup');

            if (tipo === 'Sencillo') {
                chasisAdicional1Group.hide();
                nuevoCampoDolyGroup.hide();
            } else if (tipo === 'Full') {
                chasisAdicional1Group.show();
                nuevoCampoDolyGroup.show();
            }
        });

        $('[id^="viaje"]').change(function() {
            var elementId = $(this).attr('id');
            var idNum = elementId.replace('viaje', '');
            var viaje = $('#viaje' + idNum).val();
            var idProveedor = $('#id_proveedor' + idNum);

            if (viaje === 'Camion Subcontratado') {
                idProveedor.prop('required', true);
            } else {
                idProveedor.prop('required', false);
            }
        });


        $('[id^="btn_clientes_search"]').click(function() {
            var cotizacionId = $(this).data('cotizacion-id'); // Obtener el ID de la cotización del atributo data
            buscar_clientes(cotizacionId);
        });

        function buscar_clientes(cotizacionId) {
            $('#loadingSpinner').show();

            var fecha_inicio = $('#fecha_inicio_' + cotizacionId).val();
            var fecha_fin = $('#fecha_fin_' + cotizacionId).val();

            $.ajax({
                url: '{{ route('equipos.planeaciones') }}',
                type: 'get',
                data: {
                    'fecha_inicio': fecha_inicio,
                    'fecha_fin': fecha_fin,
                    '_token': token // Agregar el token CSRF a los datos enviados
                },
                success: function(data) {
                    $('#resultado_equipos' + cotizacionId).html(data); // Actualiza la sección con los datos del servicio
                },
                error: function(error) {
                    console.log(error);
                },
                complete: function() {
                    // Ocultar el spinner cuando la búsqueda esté completa
                    $('#loadingSpinner').hide();

                }

            });

        }

        });

        function formatDate(date) {
            if (date !== null && typeof date !== 'undefined') {
                // Obtener la fecha y hora local en la zona horaria de la Ciudad de México
                var mexicoCityTime = date.toLocaleString('en-US', { timeZone: 'America/Mexico_City' });
                var mexicoCityDate = new Date(mexicoCityTime);

                // Obtener el año, mes y día de la fecha ajustada
                var year = mexicoCityDate.getFullYear();
                var month = pad(mexicoCityDate.getMonth() + 1);
                var day = pad(mexicoCityDate.getDate());

                // Construir la fecha en el formato YYYY-MM-DD
                return year + '-' + month + '-' + day;
            } else {
                // Si date es null o indefinido, devuelve una cadena vacía o maneja el caso según sea necesario
                return '';
            }
        }


        // Función para agregar un cero delante de números menores a 10
        function pad(number) {
            if (number < 10) {
                return '0' + number;
            }
            return number;
        }

        function mostrarDiv(cotizacionId) {
            var viajeSelect = document.getElementById("viaje" + cotizacionId);
            var camionSubcontratadoDiv = document.getElementById("camionSubcontratadoDiv" + cotizacionId);
            var camionPropioDiv = document.getElementById("camionPropioDiv" + cotizacionId);

            if (viajeSelect.value === "Camion Subcontratado") {
                camionSubcontratadoDiv.style.display = "block";
                camionPropioDiv.style.display = "none";
            }else if(viajeSelect.value === "Camion Propio"){
                camionPropioDiv.style.display = "block";
                camionSubcontratadoDiv.style.display = "none";
            }
        }

        $(document).ready(function() {
            const tasa_iva = 0.16;
            const tasa_retencion = 0.04;

            function calculateTotal(cotizacionId) {
                var precio = parseFloat($('#precio_proveedor_' + cotizacionId).val()) || 0;
                var burreo = parseFloat($('#burreo_proveedor_' + cotizacionId).val()) || 0;
                var maniobra = parseFloat($('#maniobra_proveedor_' + cotizacionId).val()) || 0;
                var estadia = parseFloat($('#estadia_proveedor_' + cotizacionId).val()) || 0;
                var otro = parseFloat($('#otro_proveedor_' + cotizacionId).val()) || 0;
                
                var sobrepeso = parseFloat($('#sobrepeso_proveedor_' + cotizacionId).val()) || 0;
                var cantidadsob = parseFloat($('#cantidad_sobrepeso_proveedor_' + cotizacionId).val()) || 0;

                var sobre = cantidadsob * sobrepeso;
                var subTotal = (precio + burreo + maniobra + estadia + otro );

                const baseFactura = parseFloat(document.getElementById('base_factura_' + cotizacionId).value) || 0;
                var iva = (baseFactura * tasa_iva);
                var retencion = (baseFactura * tasa_retencion);

                document.getElementById('iva_proveedor_' + cotizacionId).value = (iva.toFixed(2));
                document.getElementById('retencion_proveedor_' + cotizacionId).value = (retencion.toFixed(2));
                var total = (precio + burreo + maniobra + estadia + otro + iva + sobre) - retencion;

                const baseTaref = (total - baseFactura - iva) + retencion;
               
                document.getElementById('base_taref_' + cotizacionId).value = baseTaref.toFixed(2);
                
               
                $('#total_proveedor_' + cotizacionId).val(total.toFixed(2));

                
                
            }

            @foreach($cotizaciones as $cotizacion)
                (function(cotizacionId) {
                    // Eventos para calcular el total
                    $('.fieldsCalculo').on('input', function() {
                        calculateTotal(cotizacionId);
                       
                    });

                    // Llamar a las funciones al cargar la página
                    calculateTotal(cotizacionId);
                   
                })('{{$cotizacion->id}}');
            @endforeach
        });


    </script>

@endsection

