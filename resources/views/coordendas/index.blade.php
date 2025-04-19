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
  <!--<script src="https://kit.fontawesome.com/42d5adcbca.js"></script>-->
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
  <div class="page-header min-vh-100 d-flex justify-content-center align-items-center" style="background-image: url('{{ asset('img/contenedores.jpg') }}'); background-size: cover; background-position: center;">
 
    <div class="container d-flex flex-column align-items-center justify-content-center">
    @php
    $preguntas_A = [
            'b' => [
                            [ 'texto' => "1)¿ Registro en Puerto ?", 'campo' => 'registro_puerto' ],
                            [ 'texto' => "2)¿ Dentro de Puerto ?", 'campo' => 'dentro_puerto' ],
                            [ 'texto' => "3)¿ Descarga Vacío ?", 'campo' => 'descarga_vacio' ],
                            [ 'texto' => "4)¿ Cargado Contenedor ?", 'campo' => 'cargado_contenedor' ],
                            [ 'texto' => "5)¿ En Fila Fiscal ?", 'campo' => 'fila_fiscal' ],
                            [ 'texto' => "6)¿ Modulado ?", 'campo' => 'modulado_tipo' ,'opciones' => ["5.1) Verde","5.2) Amarillo","5.3) Rojo",  "5.4) OVT"  ]
                                                                                                    ],
                            [ 'texto' => "7)¿ Descarga en patio ?", 'campo' => 'descarga_patio' ],
                
                        ],
            'f' => [
                            [ 'texto' => "8) ¿Carga en patio?", 'campo' => 'cargado_patio' ],
                            [ 'texto' => "9) ¿Inicio ruta?", 'campo' => 'en_destino'],
                            [ 'texto' => "10)¿Inicia carga?",   'campo' => 'inicio_descarga'],
                            [ 'texto' => "11)¿Fin descarga?",   'campo' => 'fin_descarga'],
                            [ 'texto' => "12 ¿Recepción Doctos Firmados?", 'campo' => 'recepcion_doc_firmados' ],
                        ],
            'c' => [
                        [ 'texto' => "¿1) Registro en Puerto ?", 'campo' => 'registro_puerto' ],
                        [ 'texto' => "¿2) Dentro de Puerto ?", 'campo' => 'dentro_puerto' ],
                        [ 'texto' => "¿3) Descarga Vacío?", 'campo' => 'descarga_vacio' ],
                        [ 'texto' => "¿4) Cargado Contenedor?", 'campo' => 'cargado_contenedor' ],
                        [ 'texto' => "¿5) En Fila Fiscal?", 'campo' => 'fila_fiscal'],
                        [ 'texto' => "¿6) Modulado?", 'campo' => 'modulado_tipo','opciones' => ["5.1) Verde","5.2) Amarillo","5.3) Rojo",  "5.4) OVT"  ] ],
                        [ 'texto' => "¿7) En Destino?", 'campo' => 'en_destino' ],
                        [ 'texto' => "¿8) Inicio Descarga?", 'campo' => 'inicio_descarga' ],
                        [ 'texto' => "¿9) Fin Descarga?", 'campo' => 'fin_descarga' ],
                        [ 'texto' => "¿10) Recepción Doctos Firmados?", 'campo' => 'recepcion_doc_firmados' ],
                    ]

        ];
            
            
       

        $tipo = $tipoCuestionario; 
        $preguntas = $preguntas_A[$tipo];

      //buscar index de pregunta sin responder
        $primeraSinResponder = null;

      

            foreach ($preguntas as $i => $pregunta) {
                $campo = $pregunta['campo'];
                $respuesta = isset($coordenadas->$campo) ? $coordenadas->$campo : null;

                if (!$respuesta) {
                    $primeraSinResponder = $i;
                    break;
                }
              
            }
            // Convertimos el array de coordenadas a formato JSON para pasar a JavaScript
            //  $coordenadasJson = json_encode($coordenadasRuta);
    @endphp
   
      <!-- Info Estática -->
      <div id="infoEstatica" class="mb-4 p-4 text-dark"
        style="width: 100%; max-width: 600px; background-color: white; border-radius: 12px; box-shadow: 0 4px 8px rgba(0,0,0,0.1);">
        <input type="hidden" id="id_asignacion" value="{{ $coordenadas-> id_asignacion }}">
        <input type="hidden" id="id_coordenada" value="{{ $coordenadas-> id_coordenadas }}">
        <p>📋 <strong>Subcontratado: </strong>{{ $coordenadas-> nombre_empresa  .' - '. $coordenadas-> tipo_contrato}}</p>
        <p><strong>Teléfono operador:</strong> {{ $coordenadas-> telefono }}</p>
        <p><strong>Num. placas:</strong> {{ $coordenadas-> placas }}</p>
        <p><strong>Nombre del operador:</strong> {{ $coordenadas-> nombre }}</p>

        
       
      </div>
     
      <!-- Carrusel de preguntas -->
      <div id="carruselPreguntas" class="preguntas-container" style="width: 100%; max-width: 600px;">
   
  
    

    @foreach ($preguntas as $index => $pregunta)
        @php
            $respuesta = $coordenadas->{$pregunta['campo']};   
           // Si hay respuesta, y es una coordenada tipo 'lat,lng'
             if ($respuesta ) {
              [$lat, $lng] = explode(',', $respuesta);
             } else {
              $lat = $lng = null;
              }
           @endphp

        <div class="pregunta" style="display: {{ $index === $primeraSinResponder ? 'block' : 'none' }};" data-index="{{ $index }}">
            <div class="p-4 mb-3 text-dark"
                style="background-color: white; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                <h4>{{ $pregunta['texto'] }}</h4>

               

                @if ($respuesta)
                    <p><strong>Ubicación registrada:</strong> Lat: {{ $lat }}, Lng: {{ $lng }}</p>
                    <a href="https://www.google.com/maps?q={{ $lat }},{{ $lng }}" class="btn btn-primary btn-sm" target="_blank">
                        <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="15px"> Ver en Maps
                    </a>
                @endif

                @if (!$respuesta)
                    @if (isset($pregunta['opciones']) && count($pregunta['opciones']) > 0)
                        <div class="form-floating mt-3">
                            <select class="form-select" name="{{ $pregunta['campo'] }}_tipo" id="{{ $pregunta['campo'] }}_tipo"
                                onchange="guardarRespuesta({{ $index }}, this.value)">
                                <option value="">Seleccionar</option>
                                @foreach ($pregunta['opciones'] as $opcion)
                                    <option value="{{ $opcion }}">{{ $opcion }}</option>
                                @endforeach
                            </select>
                            <label for="{{ $pregunta['campo'] }}_tipo">Selecciona una opción</label>
                        </div>
                    @else
                      {{-- Si no hay opciones, muestra botones Sí / No --}}
                      <div class="d-flex gap-3 mt-3">
                          <button class="btn btn-success" onclick="guardarRespuesta({{ $index }}, '{{ $tipo }}')">✔️ Sí</button>
                          
                      </div>
                    @endif
                @endif
            </div>
        </div>
    @endforeach
</div>
 
      <!-- Resumen final -->
      <div id="resumen" class="mt-5" style="display: none;">
        <h4 class="text-white">✅ Respuestas registradas:</h4>
        <pre id="resumenRespuestas" style="background: rgba(255,255,255,0.9); padding: 15px; border-radius: 10px;"></pre>
      </div>

    </div>
  </div>
</main>
<script>
  let indiceActual = 0;

const totalPreguntas = {{ count($preguntas) }}; // Asegúrate de que esta variable esté definida correctamente en el Blade

let respuestas = [];

function mostrarPregunta(index) {
    const preguntas = document.querySelectorAll('.pregunta');
    preguntas[indiceActual].style.display = 'none'; // Ocultar la pregunta actual
    indiceActual = index; // Actualizar el índice de la pregunta
    preguntas[indiceActual].style.display = 'block'; // Mostrar la siguiente pregunta
}


const columnasPorTipo = {
    b: [
        { columna: 'registro_puerto', datetime: 'registro_puerto_datetime' },
        { columna: 'dentro_puerto', datetime: 'dentro_puerto_datetime' },
        { columna: 'descarga_vacio', datetime: 'descarga_vacio_datetime' },
        { columna: 'cargado_contenedor', datetime: 'cargado_contenedor_datetime' },
        { columna: 'fila_fiscal', datetime: 'fila_fiscal_datetime' },
        { columna: 'modulado_tipo', datetime: 'modulado_tipo_datetime' },
        { columna: 'descarga_patio', datetime: 'descarga_patio_datetime' },
    ],
    f: [
        { columna: 'cargado_patio', datetime: 'cargado_patio_datetime' },
        { columna: 'en_destino', datetime: 'en_destino_datetime' },
        { columna: 'inicio_descarga', datetime: 'inicio_descarga_datetime' },
        { columna: 'fin_descarga', datetime: 'fin_descarga_datetime' },
        { columna: 'recepcion_doc_firmados', datetime: 'recepcion_doc_firmados_datetime' },
    ],
    c: [
        { columna: 'registro_puerto', datetime: 'registro_puerto_datetime' },
        { columna: 'dentro_puerto', datetime: 'dentro_puerto_datetime' },
        { columna: 'descarga_vacio', datetime: 'descarga_vacio_datetime' },
        { columna: 'cargado_contenedor', datetime: 'cargado_contenedor_datetime' },
        { columna: 'fila_fiscal', datetime: 'fila_fiscal_datetime' },
        { columna: 'modulado_tipo', datetime: 'modulado_tipo_datetime' },
        { columna: 'modulado_coordenada', datetime: 'modulado_coordenada_datetime' },
        { columna: 'en_destino', datetime: 'en_destino_datetime' },
        { columna: 'inicio_descarga', datetime: 'inicio_descarga_datetime' },
        { columna: 'fin_descarga', datetime: 'fin_descarga_datetime' },
        { columna: 'recepcion_doc_firmados', datetime: 'recepcion_doc_firmados_datetime' }
    ]
};
function guardarRespuesta(index,tc) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        const fechaHora = new Date().toISOString();

        const idAsignacion = document.getElementById('id_asignacion')?.value || null;
        const idCoordenada = document.getElementById('id_coordenada')?.value || null;
        const coordenadas = `${lat},${lng}`;
            // Obtener las columnas correspondientes a la pregunta
        const columnas = columnasPorTipo[tc]; // obtenés el array correspondiente
        let columna='';
        let columnaDatetime='';
        if (columnas && columnas[index]) {
            columna = columnas[index].columna;
          columnaDatetime = columnas[index].datetime;

            console.log("Columna:", columna);
            console.log("Fecha:", columnaDatetime);
        }

        // Enviar por AJAX a Laravel
        fetch("{{ route('guardar.respuesta') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({
                id_asignacion: idAsignacion,
                id_coordenada: idCoordenada,
                pregunta: document.querySelectorAll('.pregunta')[index].querySelector('h4').innerText,
                coordenadas: coordenadas,
               fecha_hora: fechaHora,
                columna: columna,
                columna_datetime: columnaDatetime
            })
        })
        .then(response => response.json())
        .then(data => {
            if (index < totalPreguntas - 1) {
                mostrarPregunta(index + 1);
            } 
        })
        .catch(err => {
            console.error("Error al guardar respuesta:", err);
            alert("Hubo un error al guardar.");
        });
    }, function(err) {
        alert('No se pudo obtener la ubicación');
    });
}

function abrirEnMapsfila_fiscal(coordenadas) {

  var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");
  var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;
  window.open(url, '_blank');
 }


</script>