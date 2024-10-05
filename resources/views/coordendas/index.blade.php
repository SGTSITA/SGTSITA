<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
  <link rel="apple-touch-icon" sizes="76x76" href="{{ asset('assets/img/apple-icon.png')}}">
  <link rel="icon" type="image/png" href="https://paradisus.mx/favicon/639893ee3d1ff63891f2fbd91b277248048_670190130923536_7018383830884135385_n__1_-removebg-preview.png">
  <title>
    SGT
  </title>
  <!--     Fonts and icons     -->
  <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />
  <!-- Nucleo Icons -->
  <link href="{{ asset('assets/css/nucleo-icons.css')}}" rel="stylesheet" />
  <link href="{{ asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
  <!-- Font Awesome Icons -->
  <script src="https://kit.fontawesome.com/42d5adcbca.js')}}" crossorigin="anonymous"></script>
  <link href="{{ asset('assets/css/nucleo-svg.css')}}" rel="stylesheet" />
  <!-- CSS Files -->
  <link id="pagestyle" href="{{ asset('assets/css/argon-dashboard.css?v=2.0.4')}}" rel="stylesheet" />

  <style>

    .coordenadas_contestado{
        background: #e4f3be;
        border-radius: 9px;
        padding: 10px 10px 10px 20px;
        box-shadow: 6px 6px 15px -10px rgb(0 0 0 / 50%);
    }

  </style>

</head>

<body class="">

  <main class="main-content main-content-bg mt-0">
    <div class="page-header min-vh-100" style="background-image: url('{{ asset('img/contenedores.jpg') }}');">
      <span class="mask bg-gradient-dark opacity-6"></span>
      <div class="container">
        <div class="row justify-content-center">
          <div class="col-12 col-md-10 col-lg-8">
            <div class="card border-0 mb-0">
              <div class="card-header bg-transparent">

                <h5 class="text-dark text-center mt-2 mb-3">¡Bienvenido!</h5>

                <h4 class="text-dark text-center mt-2 mb-3">
                    Sistema de Gestion de Transporte
                </h4>

                @if($coordenadas->Cotizacion->DocCotizacion->num_contenedor == null)
                    Falta asignar Contenedor
                @else
                    <p><strong>Num de Contenedor :</strong> #{{ $coordenadas->Cotizacion->DocCotizacion->num_contenedor }}</p>
                @endif

                @if($coordenadas->Asignaciones->id_operador == NULL)

                <p>Subcontratado</p>
                <p><strong>Telefono operador : </strong> {{ $coordenadas->Asignaciones->telefonoOperadorSub}}</p>
                <p><strong>Num. placas : </strong> {{ $coordenadas->Asignaciones->placasOperadorSub }}</p>
                <p><strong>Nombre del operador: </strong> {{ $coordenadas->Asignaciones->nombreOperadorSub }}</p>

                @else

                    <p><strong>Telefono operador : </strong> {{ $coordenadas->Asignaciones->Operador->telefono }}</p>
                    <p><strong>Num. placas : </strong> {{ $coordenadas->Asignaciones->Camion->placas }}</p>
                    <p><strong>Nombre del operador: </strong> {{ $coordenadas->Asignaciones->Operador->nombre }}</p>
                @endif

              </div>
              <div class="card-body px-lg-5 pt-0">
                <div class="text-center text-muted mb-4">
                  <small></small>
                </div>

                <form method="POST" action="{{ route('edit.cooredenadas', $coordenadas->id) }}">
                    @csrf

                @if($coordenadas->validaroperador != 'Si')

                    <div class="col-12 mb-4">

                        @if($coordenadas->Asignaciones->id_operador == NULL)
                            <h5 class="text-left">0) ¿Eres el operador " {{ $coordenadas->Asignaciones->nombreOperadorSub }} " ?</h5>
                        @else
                            <h5 class="text-left">0) ¿Eres el operador " {{ $coordenadas->Asignaciones->Operador->nombre }} " ?</h5>
                        @endif

                            <div class="form-check" style="display: inline-block;margin-right:3rem;" >
                                <input class="form-check-input" type="radio" name="validaroperador" id="validaroperador_no" value="No" >
                                <label class="form-check-label" for="validaroperador_no" >
                                    No
                                </label>
                            </div>

                            <div class="form-check" style="display: inline-block;">
                                <input class="form-check-input" type="radio" name="validaroperador" id="validaroperador_si" value="Si">
                                <label class="form-check-label" for="validaroperador_si">
                                    Si
                                </label>
                            </div>

                    </div>

                    @else

                    <div class="col-12 mb-4 coordenadas_contestado">
                        @if($coordenadas->Asignaciones->id_operador == NULL)
                            <h5 class="text-left">0) ¿ Eres el operador " {{ $coordenadas->Asignaciones->nombreOperadorSub }} " ?</h5>
                        @else
                            <h5 class="text-left">0) ¿ Eres el operador " {{ $coordenadas->Asignaciones->Operador->nombre }} " ?</h5>
                        @endif
                        <div class="form-check" style="display: inline-block;">
                            <input class="form-check-input" type="radio"  id="validaroperador_si" checked disabled>
                            <label class="form-check-label" for="validaroperador_si">
                                {{ $coordenadas->validaroperador }}
                            </label>
                        </div>
                    </div>

                @endif



                    @if($coordenadas->validaroperador != null)
                        @if($coordenadas->registro_puerto == null)
                            <div class="col-12 mb-4">
                                <h5 class="text-left">1) Registro en Puerto ?</h5>

                                <div class="form-check" style="display: inline-block;margin-right:3rem;display: none" >
                                    <input class="form-check-input" type="radio" name="registro_puerto" id="registro_puerto_no" value="No" checked>
                                    <label class="form-check-label" for="registro_puerto_no" >
                                        No
                                    </label>
                                </div>

                                <div class="form-check" style="display: inline-block;">
                                    <input class="form-check-input" type="radio" name="registro_puerto" id="registro_puerto_si" value="Si">
                                    <label class="form-check-label" for="registro_puerto_si">
                                        Si
                                    </label>
                                </div>

                                <input type="hidden" id="latitud_longitud_registro_puerto" name="latitud_longitud_registro_puerto">

                            </div>

                        @else

                        <div class="col-12 mb-4 coordenadas_contestado">
                            <h5 class="text-left">1) Registro en Puerto ?</h5>

                            <div class="form-check" style="display: inline-block;">
                                <input class="form-check-input" type="radio"  id="registro_puerto_si" checked disabled>
                                <label class="form-check-label" for="registro_puerto_si">
                                    Si
                                </label>
                            </div>

                            <input type="hidden" name="latitud_longitud_registro_puerto" value="{{ $coordenadas->registro_puerto }}">

                        @auth

                            <br>
                            <button onclick="abrirEnMaps()" class="btn btn-primary btn-sm"> <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="15px"> Ver en Maps</button>


                            <script>

                                function abrirEnMaps() {
                                    // Obtener las coordenadas de la variable PHP
                                    var coordenadas = "{{ $coordenadas->registro_puerto }}";

                                    // Convertir las coordenadas al formato necesario para el enlace
                                    var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");

                                    // Crear el enlace con las coordenadas
                                    var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;

                                    // Abrir la página de Google Maps en una nueva ventana o pestaña
                                    window.open(url, '_blank');
                                }


                            </script>

                        @endauth

                        </div>

                    @endif

                    @if ($coordenadas->registro_puerto != null)
                        @if($coordenadas->dentro_puerto == null)

                            <div class="col-12 mb-4">
                                <h5 class="text-left">2) Dentro de Puerto ?</h5>

                                <div class="form-check" style="display: inline-block;margin-right:3rem;display: none">
                                    <input class="form-check-input" type="radio" name="dentro_puerto" id="dentro_puerto_no" value="No" checked>
                                    <label class="form-check-label" for="dentro_puerto_no">
                                        No
                                    </label>
                                </div>

                                <div class="form-check" style="display: inline-block;">
                                    <input class="form-check-input" type="radio" name="dentro_puerto" id="dentro_puerto_si" value="Si">
                                    <label class="form-check-label" for="dentro_puerto_si">
                                        Si
                                    </label>
                                </div>

                                <input type="hidden" id="latitud_longitud_dentro_puerto" name="latitud_longitud_dentro_puerto">
                            </div>

                            @else

                            <div class="col-12 mb-4 coordenadas_contestado">
                                <h5 class="text-left">2) Dentro de Puerto ?</h5>

                                <div class="form-check" style="display: inline-block;">
                                    <input class="form-check-input" type="radio"  id="dentro_puerto_si"  checked disabled>
                                    <label class="form-check-label" for="dentro_puerto_si">
                                        Si
                                    </label>
                                </div>

                                <input type="hidden" name="latitud_longitud_dentro_puerto" value="{{ $coordenadas->dentro_puerto }}">


                                @auth

                                    <br>
                                    <button onclick="abrirEnMapsdentro_puerto()" class="btn btn-primary btn-sm"> <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="15px"> Ver en Maps</button>


                                    <script>

                                        function abrirEnMapsdentro_puerto() {
                                            // Obtener las coordenadas de la variable PHP
                                            var coordenadas = "{{ $coordenadas->dentro_puerto }}";

                                            // Convertir las coordenadas al formato necesario para el enlace
                                            var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");

                                            // Crear el enlace con las coordenadas
                                            var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;

                                            // Abrir la página de Google Maps en una nueva ventana o pestaña
                                            window.open(url, '_blank');
                                        }
                                    </script>

                                @endauth

                            </div>

                        @endif

                        @if ($coordenadas->dentro_puerto != null)
                            @if($coordenadas->descarga_vacio == null)

                                <div class="col-12 mb-4">
                                    <h5 class="text-left">3) Descarga Vacío?</h5>

                                    <div class="form-check" style="display: inline-block;margin-right:3rem;display: none">
                                        <input class="form-check-input" type="radio" name="descarga_vacio" id="descarga_vacio_no" value="No" checked>
                                        <label class="form-check-label" for="descarga_vacio_no">
                                            No
                                        </label>
                                    </div>

                                    <div class="form-check" style="display: inline-block;">
                                        <input class="form-check-input" type="radio" name="descarga_vacio" id="descarga_vacio_si" value="Si">
                                        <label class="form-check-label" for="descarga_vacio_si">
                                            Si
                                        </label>
                                    </div>

                                    <input type="hidden" id="latitud_longitud_descarga_vacio" name="latitud_longitud_descarga_vacio">
                                </div>

                                @else

                                <div class="col-12 mb-4 coordenadas_contestado">
                                    <h5 class="text-left">3) Descarga Vacío?</h5>

                                    <div class="form-check" style="display: inline-block;">
                                        <input class="form-check-input" type="radio"  id="descarga_vacio_si" checked disabled >
                                        <label class="form-check-label" for="descarga_vacio_si">
                                            Si
                                        </label>
                                    </div>

                                    <input type="hidden" name="latitud_longitud_descarga_vacio" value="{{ $coordenadas->descarga_vacio }}">
                                    @auth

                                        <br>
                                        <button onclick="abrirEnMapsdescarga()" class="btn btn-primary btn-sm"> <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="15px"> Ver en Maps</button>


                                        <script>

                                            function abrirEnMapsdescarga() {
                                                // Obtener las coordenadas de la variable PHP
                                                var coordenadas = "{{ $coordenadas->descarga_vacio }}";

                                                // Convertir las coordenadas al formato necesario para el enlace
                                                var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");

                                                // Crear el enlace con las coordenadas
                                                var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;

                                                // Abrir la página de Google Maps en una nueva ventana o pestaña
                                                window.open(url, '_blank');
                                            }
                                        </script>

                                    @endauth

                                </div>

                            @endif

                            @if ($coordenadas->descarga_vacio != null)
                                @if($coordenadas->cargado_contenedor == null)
                                    <div class="col-12 mb-4">
                                        <h5 class="text-left">4)Cargado Contenedor ?</h5>

                                        <div class="form-check" style="display: inline-block;margin-right:3rem;display: none">
                                            <input class="form-check-input" type="radio" name="cargado_contenedor" id="cargado_contenedor_no" value="No" checked>
                                            <label class="form-check-label" for="cargado_contenedor_no">
                                                No
                                            </label>
                                        </div>

                                        <div class="form-check" style="display: inline-block;">
                                            <input class="form-check-input" type="radio" name="cargado_contenedor" id="cargado_contenedor_si" value="Si">
                                            <label class="form-check-label" for="cargado_contenedor_si">
                                                Si
                                            </label>
                                        </div>

                                        <input type="hidden" id="latitud_longitud_cargado_contenedor" name="latitud_longitud_cargado_contenedor">
                                    </div>

                                    @else
                                    <div class="col-12 mb-4 coordenadas_contestado">
                                        <h5 class="text-left">4)Cargado Contenedor ?</h5>

                                        <div class="form-check" style="display: inline-block;">
                                            <input class="form-check-input" type="radio"  id="cargado_contenedor_si" disabled checked>
                                            <label class="form-check-label" for="cargado_contenedor_si">
                                                Si
                                            </label>
                                        </div>

                                        <input type="hidden" name="latitud_longitud_cargado_contenedor" value="{{ $coordenadas->cargado_contenedor }}">
                                        @auth

                                            <br>
                                            <button onclick="abrirEnMapscargado_contenedor()" class="btn btn-primary btn-sm"> <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="15px"> Ver en Maps</button>


                                            <script>

                                                function abrirEnMapscargado_contenedor() {
                                                    // Obtener las coordenadas de la variable PHP
                                                    var coordenadas = "{{ $coordenadas->cargado_contenedor }}";

                                                    // Convertir las coordenadas al formato necesario para el enlace
                                                    var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");

                                                    // Crear el enlace con las coordenadas
                                                    var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;

                                                    // Abrir la página de Google Maps en una nueva ventana o pestaña
                                                    window.open(url, '_blank');
                                                }
                                            </script>

                                        @endauth
                                    </div>
                                @endif

                                @if ($coordenadas->cargado_contenedor != null)
                                    @if($coordenadas->fila_fiscal == null)
                                        <div class="col-12 mb-4">
                                            <h5 class="text-left">5) En Fila Fiscal ?</h5>

                                            <div class="form-check" style="display: inline-block;margin-right:3rem;display: none">
                                                <input class="form-check-input" type="radio" name="fila_fiscal" id="fila_fiscal_no" value="No" checked>
                                                <label class="form-check-label" for="fila_fiscal_no">
                                                    No
                                                </label>
                                            </div>

                                            <div class="form-check" style="display: inline-block;">
                                                <input class="form-check-input" type="radio" name="fila_fiscal" id="fila_fiscal_si" value="Si">
                                                <label class="form-check-label" for="fila_fiscal_si">
                                                    Si
                                                </label>
                                            </div>

                                            <input type="hidden" id="latitud_longitud_fila_fiscal" name="latitud_longitud_fila_fiscal">



                                        </div>

                                        @else

                                        <div class="col-12 mb-4 coordenadas_contestado">
                                            <h5 class="text-left">5) En Fila Fiscal ?</h5>

                                            <div class="form-check" style="display: inline-block;">
                                                <input class="form-check-input" type="radio"  id="fila_fiscal_si" disabled checked >
                                                <label class="form-check-label" for="fila_fiscal_si">
                                                    Si
                                                </label>
                                            </div>

                                            <input type="hidden" name="latitud_longitud_fila_fiscal" value="{{ $coordenadas->fila_fiscal }}">
                                            @auth

                                                <br>
                                                <button onclick="abrirEnMapsfila_fiscal()" class="btn btn-primary btn-sm"> <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="15px"> Ver en Maps</button>


                                                <script>

                                                    function abrirEnMapsfila_fiscal() {
                                                        // Obtener las coordenadas de la variable PHP
                                                        var coordenadas = "{{ $coordenadas->fila_fiscal }}";

                                                        // Convertir las coordenadas al formato necesario para el enlace
                                                        var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");

                                                        // Crear el enlace con las coordenadas
                                                        var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;

                                                        // Abrir la página de Google Maps en una nueva ventana o pestaña
                                                        window.open(url, '_blank');
                                                    }
                                                </script>

                                            @endauth
                                        </div>
                                    @endif

                                    @if ($coordenadas->fila_fiscal != null)
                                        @if($coordenadas->modulado_tipo == null)
                                            <div class="col-12 mb-4">
                                                <h5 class="text-left">6) Modulado </h5>

                                                <div class="form-floating">
                                                    <select class="form-select"  name="modulado_tipo" id="modulado_tipo">
                                                    <option value="">Selecionar</option>
                                                    <option value="5.1) Verde"> 5.1) Verde </option>
                                                    <option value="5.2) Amarillo"> 5.2) Amarillo</option>
                                                    <option value="5.3) Rojo">5.3) Rojo</option>
                                                    <option value="5.4) OVT">5.4) OVT</option>

                                                    </select>
                                                    <label for="floatingSelectGrid">Seleciona una opcion</label>
                                                </div>

                                                <input type="hidden" id="latitud_longitud_modulado_tipo" name="latitud_longitud_modulado_tipo">

                                            </div>

                                            @else
                                            <div class="col-12 mb-4 coordenadas_contestado">
                                                <h5 class="text-left">6) Modulado </h5>

                                                <div class="form-floating">
                                                    <select class="form-select"  name="modulado_tipo" id="modulado_tipo" redonly>
                                                    <option value="{{ $coordenadas->modulado_tipo }}">{{ $coordenadas->modulado_tipo }}</option>
                                                    </select>
                                                    <label for="floatingSelectGrid">Selecionado</label>
                                                </div>

                                            </div>
                                        @endif

                                        @if ($coordenadas->modulado_tipo != null)
                                            @if($coordenadas->en_destino == null)
                                                <div class="col-12 mb-4">
                                                    <h5 class="text-left">7) En Destino</h5>

                                                    <div class="form-check" style="display: inline-block;margin-right:3rem;display: none">
                                                        <input class="form-check-input" type="radio" name="en_destino" id="en_destino_no" value="No" checked>
                                                        <label class="form-check-label" for="en_destino_no">
                                                            No
                                                        </label>
                                                    </div>

                                                    <div class="form-check" style="display: inline-block;">
                                                        <input class="form-check-input" type="radio" name="en_destino" id="en_destino_si" value="Si">
                                                        <label class="form-check-label" for="en_destino_si">
                                                            Si
                                                        </label>
                                                    </div>

                                                    <input type="hidden" id="latitud_longitud_en_destino" name="latitud_longitud_en_destino">
                                                </div>

                                                @else
                                                <div class="col-12 mb-4 coordenadas_contestado">
                                                    <h5 class="text-left">7) En Destino</h5>

                                                    <div class="form-check" style="display: inline-block;">
                                                        <input class="form-check-input" type="radio"  id="en_destino_si"  checked disabled>
                                                        <label class="form-check-label" for="en_destino_si">
                                                            Si
                                                        </label>
                                                    </div>
                                                    <input type="hidden" name="latitud_longitud_en_destino" value="{{ $coordenadas->en_destino }}">
                                                    @auth

                                                        <br>
                                                        <button onclick="abrirEnMapsen_destino()" class="btn btn-primary btn-sm"> <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="15px"> Ver en Maps</button>

                                                        <script>

                                                            function abrirEnMapsen_destino() {
                                                                // Obtener las coordenadas de la variable PHP
                                                                var coordenadas = "{{ $coordenadas->en_destino }}";

                                                                // Convertir las coordenadas al formato necesario para el enlace
                                                                var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");

                                                                // Crear el enlace con las coordenadas
                                                                var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;

                                                                // Abrir la página de Google Maps en una nueva ventana o pestaña
                                                                window.open(url, '_blank');
                                                            }
                                                        </script>

                                                    @endauth

                                                </div>
                                            @endif

                                            @if ($coordenadas->en_destino != null)
                                                @if($coordenadas->inicio_descarga == null)

                                                    <div class="col-12 mb-4">
                                                        <h5 class="text-left">8) Inicio Descarga</h5>

                                                        <div class="form-check" style="display: inline-block;margin-right:3rem;display: none">
                                                            <input class="form-check-input" type="radio" name="inicio_descarga" id="inicio_descarga_no" value="No" checked>
                                                            <label class="form-check-label" for="inicio_descarga_no">
                                                                No
                                                            </label>
                                                        </div>

                                                        <div class="form-check" style="display: inline-block;">
                                                            <input class="form-check-input" type="radio" name="inicio_descarga" id="inicio_descarga_si" value="Si">
                                                            <label class="form-check-label" for="inicio_descarga_si">
                                                                Si
                                                            </label>
                                                        </div>

                                                        <input type="hidden" id="latitud_longitud_inicio_descarga" name="latitud_longitud_inicio_descarga">
                                                    </div>

                                                    @else

                                                    <div class="col-12 mb-4 coordenadas_contestado">
                                                        <h5 class="text-left">8) Inicio Descarga</h5>

                                                        <div class="form-check" style="display: inline-block;">
                                                            <input class="form-check-input" type="radio"  id="inicio_descarga_si"  checked disabled>
                                                            <label class="form-check-label" for="inicio_descarga_si">
                                                                Si
                                                            </label>
                                                        </div>

                                                        <input type="hidden" name="latitud_longitud_inicio_descarga" value="{{ $coordenadas->inicio_descarga }}">
                                                        @auth

                                                            <br>
                                                            <button onclick="abrirEnMapsinicio_descarga()" class="btn btn-primary btn-sm"> <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="15px"> Ver en Maps</button>

                                                            <script>

                                                                function abrirEnMapsinicio_descarga() {
                                                                    // Obtener las coordenadas de la variable PHP
                                                                    var coordenadas = "{{ $coordenadas->inicio_descarga }}";

                                                                    // Convertir las coordenadas al formato necesario para el enlace
                                                                    var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");

                                                                    // Crear el enlace con las coordenadas
                                                                    var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;

                                                                    // Abrir la página de Google Maps en una nueva ventana o pestaña
                                                                    window.open(url, '_blank');
                                                                }
                                                            </script>

                                                        @endauth
                                                    </div>

                                                @endif

                                                @if ($coordenadas->inicio_descarga != null)
                                                    @if($coordenadas->fin_descarga == null)
                                                        <div class="col-12 mb-4">
                                                            <h5 class="text-left">9) Fin Descarga</h5>

                                                            <div class="form-check" style="display: inline-block;margin-right:3rem;display: none">
                                                                <input class="form-check-input" type="radio" name="fin_descarga" id="fin_descarga_no" value="No" checked>
                                                                <label class="form-check-label" for="fin_descarga_no">
                                                                    No
                                                                </label>
                                                            </div>

                                                            <div class="form-check" style="display: inline-block;">
                                                                <input class="form-check-input" type="radio" name="fin_descarga" id="fin_descarga_si" value="Si">
                                                                <label class="form-check-label" for="fin_descarga_si">
                                                                    Si
                                                                </label>
                                                            </div>

                                                            <input type="hidden" id="latitud_longitud_fin_descarga" name="latitud_longitud_fin_descarga">
                                                        </div>

                                                        @else
                                                        <div class="col-12 mb-4 coordenadas_contestado">
                                                            <h5 class="text-left">9) Fin Descarga</h5>

                                                            <div class="form-check" style="display: inline-block;">
                                                                <input class="form-check-input" type="radio"  id="fin_descarga_si" value="{{ $coordenadas->fin_descarga }}" disabled checked>
                                                                <label class="form-check-label" for="fin_descarga_si">
                                                                    Si
                                                                </label>
                                                            </div>
                                                            <input type="hidden" name="latitud_longitud_fin_descarga" value="{{ $coordenadas->fin_descarga }}">
                                                            @auth

                                                            <br>
                                                            <button onclick="abrirEnMapsfin_descarga()" class="btn btn-primary btn-sm"> <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="15px"> Ver en Maps</button>

                                                            <script>

                                                                function abrirEnMapsfin_descarga() {
                                                                    // Obtener las coordenadas de la variable PHP
                                                                    var coordenadas = "{{ $coordenadas->fin_descarga }}";

                                                                    // Convertir las coordenadas al formato necesario para el enlace
                                                                    var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");

                                                                    // Crear el enlace con las coordenadas
                                                                    var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;

                                                                    // Abrir la página de Google Maps en una nueva ventana o pestaña
                                                                    window.open(url, '_blank');
                                                                }
                                                            </script>

                                                        @endauth
                                                        </div>
                                                    @endif

                                                    @if ($coordenadas->fin_descarga != null)
                                                        @if($coordenadas->recepcion_doc_firmados == null)
                                                                <div class="col-12 mb-4">
                                                                    <h5 class="text-left">10) Recepción Doctos Firmados</h5>

                                                                    <div class="form-check" style="display: inline-block;margin-right:3rem;display: none">
                                                                        <input class="form-check-input" type="radio" name="recepcion_doc_firmados" id="recepcion_doc_firmados_no" value="No" checked>
                                                                        <label class="form-check-label" for="recepcion_doc_firmados_no">
                                                                            No
                                                                        </label>
                                                                    </div>

                                                                    <div class="form-check" style="display: inline-block;">
                                                                        <input class="form-check-input" type="radio" name="recepcion_doc_firmados" id="recepcion_doc_firmados_si" value="Si">
                                                                        <label class="form-check-label" for="recepcion_doc_firmados_si">
                                                                            Si
                                                                        </label>
                                                                    </div>

                                                                    <input type="hidden" id="latitud_longitud_recepcion_doc_firmados" name="latitud_longitud_recepcion_doc_firmados">
                                                                </div>
                                                            @else
                                                            <div class="col-12 mb-4 coordenadas_contestado">
                                                                <h5 class="text-left">10) Recepción Doctos Firmados</h5>

                                                                <div class="form-check" style="display: inline-block;">
                                                                    <input class="form-check-input" type="radio"  id="recepcion_doc_firmados_si" disabled checked>
                                                                    <label class="form-check-label" for="recepcion_doc_firmados_si">
                                                                        Si
                                                                    </label>
                                                                </div>
                                                                <input type="hidden" name="latitud_longitud_recepcion_doc_firmados" value="{{ $coordenadas->recepcion_doc_firmados }}">
                                                                @auth

                                                                <br>
                                                                <button onclick="abrirEnMapsrecepcion_doc_firmados()" class="btn btn-primary btn-sm"> <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="15px"> Ver en Maps</button>

                                                                <script>

                                                                    function abrirEnMapsrecepcion_doc_firmados() {
                                                                        // Obtener las coordenadas de la variable PHP
                                                                        var coordenadas = "{{ $coordenadas->recepcion_doc_firmados }}";

                                                                        // Convertir las coordenadas al formato necesario para el enlace
                                                                        var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");

                                                                        // Crear el enlace con las coordenadas
                                                                        var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;

                                                                        // Abrir la página de Google Maps en una nueva ventana o pestaña
                                                                        window.open(url, '_blank');
                                                                    }
                                                                </script>

                                                            @endauth
                                                            </div>
                                                        @endif
                                                    @endif
                                                @endif
                                            @endif
                                        @endif
                                    @endif
                                @endif
                            @endif
                        @endif
                    @endif
                    @endif


                    <div class="text-center">
                        @if($coordenadas->validaroperador == 'Si')

                            @if($coordenadas->Cotizacion->estatus == 'Aprobada')
                                <button type="submit" class="btn btn-success w-100 my-4 mb-2">
                                    <img src="{{ asset('img/icon/disquete-imageonline.co-5785320.webp') }}" alt="" width="20px"> - Actualizar
                                </button>
                            @endif

                        @elseif($coordenadas->validaroperador == null && $coordenadas->Cotizacion->estatus == 'Aprobada')
                            <button type="submit" class="btn btn-success w-100 my-4 mb-2">
                                <img src="{{ asset('img/icon/disquete-imageonline.co-5785320.webp') }}" alt="" width="20px"> - Actualizar
                            </button>
                        @else
                            Este no es tu viaje
                        @endif
                    </div>

                </form>

              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  <!-- -------- START FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
  <footer class="footer py-2">
    <div class="container">
      <div class="row">
        <div class="col-8 mx-auto text-center mt-1">
          <p class="mb-0 text-secondary">
            Power By <script>
              document.write(new Date().getFullYear())
            </script> WebTech
          </p>
        </div>
      </div>
    </div>
  </footer>
  <!-- -------- END FOOTER 3 w/ COMPANY DESCRIPTION WITH LINKS & SOCIAL ICONS & COPYRIGHT ------- -->
  <!--   Core JS Files   -->
  <script src="{{ asset('assets/js/core/popper.min.js')}}"></script>
  <script src="{{ asset('assets/js/core/bootstrap.min.js')}}"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        var registro_puerto_si = document.getElementById('registro_puerto_si');
        var latitudLongitudInputRegistro_puerto = document.getElementById('latitud_longitud_registro_puerto');

        var dentro_puerto_si = document.getElementById('dentro_puerto_si');
        var latitudLongitudInputDentro_puerto = document.getElementById('latitud_longitud_dentro_puerto');

        var descarga_vacio_si = document.getElementById('descarga_vacio_si');
        var latitudLongitudInputDescarga_vacio = document.getElementById('latitud_longitud_descarga_vacio');

        var cargado_contenedor_si = document.getElementById('cargado_contenedor_si');
        var latitudLongitudInputCargadoContenedor = document.getElementById('latitud_longitud_cargado_contenedor');

        var fila_fiscal_si = document.getElementById('fila_fiscal_si');
        var latitudLongitudInputFilafiscal = document.getElementById('latitud_longitud_fila_fiscal');

        var en_destino_si = document.getElementById('en_destino_si');
        var latitudLongitudInputDestino = document.getElementById('latitud_longitud_en_destino');

        var inicio_descarga_si = document.getElementById('inicio_descarga_si');
        var latitudLongitudInputInicioDescarga = document.getElementById('latitud_longitud_inicio_descarga');

        var fin_descarga_si = document.getElementById('fin_descarga_si');
        var latitudLongitudInputFinDescarga = document.getElementById('latitud_longitud_fin_descarga');

        var recepcion_doc_firmados_si = document.getElementById('recepcion_doc_firmados_si');
        var latitudLongitudInputDocFirmado = document.getElementById('latitud_longitud_recepcion_doc_firmados');

        var modulado_tipo = document.getElementById('modulado_tipo');
        var latitudLongitudInputmodulado_tipo = document.getElementById('latitud_longitud_modulado_tipo');


        registro_puerto_si.addEventListener('change', function () {
            if (this.checked) {
                obtenerCoordenadas();
            }
        });


        dentro_puerto_si.addEventListener('change', function () {
            if (this.checked) {
                obtenerCoordenadasDentroPuerto();
            }
        });

        descarga_vacio_si.addEventListener('change', function () {
            if (this.checked) {
                obtenerCoordenadasdescarga_vacio();
            }
        });

        cargado_contenedor_si.addEventListener('change', function () {
            if (this.checked) {
                obtenerCoordenadascargado_contenedor_si();
            }
        });

        fila_fiscal_si.addEventListener('change', function () {
            if (this.checked) {
                obtenerCoordenadasfila_fiscal_si();
            }
        });

        en_destino_si.addEventListener('change', function () {
            if (this.checked) {
                obtenerCoordenadasen_destino_si();
            }
        });

        inicio_descarga_si.addEventListener('change', function () {
            if (this.checked) {
                obtenerCoordenadasinicio_descarga_si();
            }
        });

        fin_descarga_si.addEventListener('change', function () {
            if (this.checked) {
                obtenerCoordenadasfin_descarga_si();
            }
        });

        recepcion_doc_firmados_si.addEventListener('change', function () {
            if (this.checked) {
                obtenerCoordenadasrecepcion_doc_firmados_si();
            }
        });

        modulado_tipo.addEventListener('change', function () {
            if (this.checked) {
                obtenerCoordenadasrecepcion_modulado_tipo();
            }
        });


        function obtenerCoordenadas() {
            // Verificar si el navegador es compatible con la geolocalización
            if (navigator.geolocation) {
                // Opciones para solicitar la ubicación con alta precisión
                var options = {
                    enableHighAccuracy: true, // Solicitar alta precisión
                    timeout: 5000, // Tiempo máximo en milisegundos para obtener la ubicación
                    maximumAge: 0 // Tiempo máximo en milisegundos para usar la caché de ubicación anterior
                };

                // Obtener las coordenadas actuales
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latitud = position.coords.latitude;
                    var longitud = position.coords.longitude;
                    var latitudLongitud = latitud + ',' + longitud;
                    console.log(latitudLongitud);
                    latitudLongitudInputRegistro_puerto.value = latitudLongitud;
                }, function (error) {
                    console.error('Error al obtener la ubicación:', error.message);
                }, options);
            } else {
                console.error('La geolocalización no es compatible con este navegador.');
            }
        }

        function obtenerCoordenadasDentroPuerto() {
            // Verificar si el navegador es compatible con la geolocalización
            if (navigator.geolocation) {
                // Opciones para solicitar la ubicación con alta precisión
                var options = {
                    enableHighAccuracy: true, // Solicitar alta precisión
                    timeout: 5000, // Tiempo máximo en milisegundos para obtener la ubicación
                    maximumAge: 0 // Tiempo máximo en milisegundos para usar la caché de ubicación anterior
                };

                // Obtener las coordenadas actuales
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latitud = position.coords.latitude;
                    var longitud = position.coords.longitude;
                    var latitudLongitud = latitud + ',' + longitud;
                    console.log(latitudLongitud);
                    latitudLongitudInputDentro_puerto.value = latitudLongitud;
                }, function (error) {
                    console.error('Error al obtener la ubicación:', error.message);
                }, options);
            } else {
                console.error('La geolocalización no es compatible con este navegador.');
            }
        }

        function obtenerCoordenadasdescarga_vacio() {
            // Verificar si el navegador es compatible con la geolocalización
            if (navigator.geolocation) {
                // Opciones para solicitar la ubicación con alta precisión
                var options = {
                    enableHighAccuracy: true, // Solicitar alta precisión
                    timeout: 5000, // Tiempo máximo en milisegundos para obtener la ubicación
                    maximumAge: 0 // Tiempo máximo en milisegundos para usar la caché de ubicación anterior
                };

                // Obtener las coordenadas actuales
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latitud = position.coords.latitude;
                    var longitud = position.coords.longitude;
                    var latitudLongitud = latitud + ',' + longitud;
                    console.log(latitudLongitud);
                    latitudLongitudInputDescarga_vacio.value = latitudLongitud;
                }, function (error) {
                    console.error('Error al obtener la ubicación:', error.message);
                }, options);
            } else {
                console.error('La geolocalización no es compatible con este navegador.');
            }
        }

        function obtenerCoordenadascargado_contenedor_si() {
            // Verificar si el navegador es compatible con la geolocalización
            if (navigator.geolocation) {
                // Opciones para solicitar la ubicación con alta precisión
                var options = {
                    enableHighAccuracy: true, // Solicitar alta precisión
                    timeout: 5000, // Tiempo máximo en milisegundos para obtener la ubicación
                    maximumAge: 0 // Tiempo máximo en milisegundos para usar la caché de ubicación anterior
                };

                // Obtener las coordenadas actuales
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latitud = position.coords.latitude;
                    var longitud = position.coords.longitude;
                    var latitudLongitud = latitud + ',' + longitud;
                    console.log(latitudLongitud);
                    latitudLongitudInputCargadoContenedor.value = latitudLongitud;
                }, function (error) {
                    console.error('Error al obtener la ubicación:', error.message);
                }, options);
            } else {
                console.error('La geolocalización no es compatible con este navegador.');
            }
        }

        function obtenerCoordenadasfila_fiscal_si() {
            // Verificar si el navegador es compatible con la geolocalización
            if (navigator.geolocation) {
                // Opciones para solicitar la ubicación con alta precisión
                var options = {
                    enableHighAccuracy: true, // Solicitar alta precisión
                    timeout: 5000, // Tiempo máximo en milisegundos para obtener la ubicación
                    maximumAge: 0 // Tiempo máximo en milisegundos para usar la caché de ubicación anterior
                };

                // Obtener las coordenadas actuales
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latitud = position.coords.latitude;
                    var longitud = position.coords.longitude;
                    var latitudLongitud = latitud + ',' + longitud;
                    console.log(latitudLongitud);
                    latitudLongitudInputFilafiscal.value = latitudLongitud;
                }, function (error) {
                    console.error('Error al obtener la ubicación:', error.message);
                }, options);
            } else {
                console.error('La geolocalización no es compatible con este navegador.');
            }
        }

        function obtenerCoordenadasen_destino_si() {
            // Verificar si el navegador es compatible con la geolocalización
            if (navigator.geolocation) {
                // Opciones para solicitar la ubicación con alta precisión
                var options = {
                    enableHighAccuracy: true, // Solicitar alta precisión
                    timeout: 5000, // Tiempo máximo en milisegundos para obtener la ubicación
                    maximumAge: 0 // Tiempo máximo en milisegundos para usar la caché de ubicación anterior
                };

                // Obtener las coordenadas actuales
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latitud = position.coords.latitude;
                    var longitud = position.coords.longitude;
                    var latitudLongitud = latitud + ',' + longitud;
                    console.log(latitudLongitud);
                    latitudLongitudInputDestino.value = latitudLongitud;
                }, function (error) {
                    console.error('Error al obtener la ubicación:', error.message);
                }, options);
            } else {
                console.error('La geolocalización no es compatible con este navegador.');
            }
        }

        function obtenerCoordenadasinicio_descarga_si() {
            // Verificar si el navegador es compatible con la geolocalización
            if (navigator.geolocation) {
                // Opciones para solicitar la ubicación con alta precisión
                var options = {
                    enableHighAccuracy: true, // Solicitar alta precisión
                    timeout: 5000, // Tiempo máximo en milisegundos para obtener la ubicación
                    maximumAge: 0 // Tiempo máximo en milisegundos para usar la caché de ubicación anterior
                };

                // Obtener las coordenadas actuales
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latitud = position.coords.latitude;
                    var longitud = position.coords.longitude;
                    var latitudLongitud = latitud + ',' + longitud;
                    console.log(latitudLongitud);
                    latitudLongitudInputInicioDescarga.value = latitudLongitud;
                }, function (error) {
                    console.error('Error al obtener la ubicación:', error.message);
                }, options);
            } else {
                console.error('La geolocalización no es compatible con este navegador.');
            }
        }

        function obtenerCoordenadasfin_descarga_si() {
            // Verificar si el navegador es compatible con la geolocalización
            if (navigator.geolocation) {
                // Opciones para solicitar la ubicación con alta precisión
                var options = {
                    enableHighAccuracy: true, // Solicitar alta precisión
                    timeout: 5000, // Tiempo máximo en milisegundos para obtener la ubicación
                    maximumAge: 0 // Tiempo máximo en milisegundos para usar la caché de ubicación anterior
                };

                // Obtener las coordenadas actuales
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latitud = position.coords.latitude;
                    var longitud = position.coords.longitude;
                    var latitudLongitud = latitud + ',' + longitud;
                    console.log(latitudLongitud);
                    latitudLongitudInputFinDescarga.value = latitudLongitud;
                }, function (error) {
                    console.error('Error al obtener la ubicación:', error.message);
                }, options);
            } else {
                console.error('La geolocalización no es compatible con este navegador.');
            }
        }

        function obtenerCoordenadasrecepcion_doc_firmados_si() {
            // Verificar si el navegador es compatible con la geolocalización
            if (navigator.geolocation) {
                // Opciones para solicitar la ubicación con alta precisión
                var options = {
                    enableHighAccuracy: true, // Solicitar alta precisión
                    timeout: 5000, // Tiempo máximo en milisegundos para obtener la ubicación
                    maximumAge: 0 // Tiempo máximo en milisegundos para usar la caché de ubicación anterior
                };

                // Obtener las coordenadas actuales
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latitud = position.coords.latitude;
                    var longitud = position.coords.longitude;
                    var latitudLongitud = latitud + ',' + longitud;
                    console.log(latitudLongitud);
                    latitudLongitudInputDocFirmado.value = latitudLongitud;
                }, function (error) {
                    console.error('Error al obtener la ubicación:', error.message);
                }, options);
            } else {
                console.error('La geolocalización no es compatible con este navegador.');
            }
        }

        function obtenerCoordenadasrecepcion_modulado_tipo() {
            // Verificar si el navegador es compatible con la geolocalización
            if (navigator.geolocation) {
                // Opciones para solicitar la ubicación con alta precisión
                var options = {
                    enableHighAccuracy: true, // Solicitar alta precisión
                    timeout: 5000, // Tiempo máximo en milisegundos para obtener la ubicación
                    maximumAge: 0 // Tiempo máximo en milisegundos para usar la caché de ubicación anterior
                };

                // Obtener las coordenadas actuales
                navigator.geolocation.getCurrentPosition(function (position) {
                    var latitud = position.coords.latitude;
                    var longitud = position.coords.longitude;
                    var latitudLongitud = latitud + ',' + longitud;
                    console.log(latitudLongitud);
                    latitudLongitudInputmodulado_tipo.value = latitudLongitud;
                }, function (error) {
                    console.error('Error al obtener la ubicación:', error.message);
                }, options);
            } else {
                console.error('La geolocalización no es compatible con este navegador.');
            }
        }


    });


</script>

  <!-- Github buttons -->
  <script async defer src="https://buttons.github.io/buttons.js"></script>
  <!-- Control Center for Soft Dashboard: parallax effects, scripts for the example pages etc -->
  <script src="{{ asset('assets/js/argon-dashboard.min.js?v=2.0.4')}}"></script>
  <script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBDaeWicvigtP9xPv919E-RNoxfvC-Hqik&callback=iniciarMap"></script>

</body>

</html>
