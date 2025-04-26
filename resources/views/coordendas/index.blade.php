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
            [ 'texto' => "6)¿ Modulado ?", 'campo' => 'modulado_tipo', 'opciones' => ["5.1) Verde","5.2) Amarillo","5.3) Rojo", "5.4) OVT"] ],
            [ 'texto' => "7)¿ Descarga en patio ?", 'campo' => 'descarga_patio' ],
        ],
        'f' => [
            [ 'texto' => "8) ¿Carga en patio?", 'campo' => 'cargado_patio' ],
            [ 'texto' => "9) ¿Inicio ruta?", 'campo' => 'en_destino'],
            [ 'texto' => "10)¿Inicia carga?", 'campo' => 'inicio_descarga'],
            [ 'texto' => "11)¿Fin descarga?", 'campo' => 'fin_descarga'],
            [ 'texto' => "12 ¿Recepción Doctos Firmados?", 'campo' => 'recepcion_doc_firmados' ],
        ],
        'c' => [
            [ 'texto' => "¿1) Registro en Puerto ?", 'campo' => 'registro_puerto' ],
            [ 'texto' => "¿2) Dentro de Puerto ?", 'campo' => 'dentro_puerto' ],
            [ 'texto' => "¿3) Descarga Vacío?", 'campo' => 'descarga_vacio' ],
            [ 'texto' => "¿4) Cargado Contenedor?", 'campo' => 'cargado_contenedor' ],
            [ 'texto' => "¿5) En Fila Fiscal?", 'campo' => 'fila_fiscal'],
            [ 'texto' => "¿6) Modulado?", 'campo' => 'modulado_tipo', 'opciones' => ["5.1) Verde","5.2) Amarillo","5.3) Rojo", "5.4) OVT"] ],
            [ 'texto' => "¿7) En Destino?", 'campo' => 'en_destino' ],
            [ 'texto' => "¿8) Inicio Descarga?", 'campo' => 'inicio_descarga' ],
            [ 'texto' => "¿9) Fin Descarga?", 'campo' => 'fin_descarga' ],
            [ 'texto' => "¿10) Recepción Doctos Firmados?", 'campo' => 'recepcion_doc_firmados' ],
        ]
    ];

    $tipo = $tipoCuestionario;
    $preguntas = $preguntas_A[$tipo];
    $primeraSinResponder = null;
@endphp

@foreach ($preguntas as $i => $pregunta)
    @php
        $campo = $pregunta['campo'];
        $respuesta = isset($coordenadas->$campo) ? $coordenadas->$campo : null;
    @endphp

    @if (!$respuesta && $primeraSinResponder === null)
        @php $primeraSinResponder = $i; @endphp
        @break
    @endif
@endforeach

@if (is_null($primeraSinResponder))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Aquí puedes pasar el número total de preguntas si lo necesitas
            actualizarProgresoInicial({{ count($preguntas) }});
        });
    </script>
@endif

   
      <!-- Info Estática -->
      
      <div id="infoEstatica" class="card border-0 shadow-sm mb-4" style="width:100%;max-width: 600px; background-color: #f8fafc;">
    <div class="card-body">
        <input type="hidden" id="id_asignacion" value="{{ $coordenadas->id_asignacion }}">
        <input type="hidden" id="id_coordenada" value="{{ $coordenadas->id_coordenadas }}">

        <input type="hidden" id="estadoC" name="estadoC" value="{{ $coordenadas->tipo_c_estado }}">
        <input type="hidden" id="estadoB" name="estadoB" value="{{ $coordenadas->tipo_b_estado }}">
        <input type="hidden" id="estadoF" name="estadoF" value="{{ $coordenadas->tipo_f_estado }}">

        <h5 class="card-title mb-3 text-primary">
            📋 Información del Viaje
        </h5>

        <ul class="list-group list-group-flush">
            <li class="list-group-item" style="background-color: #f1f5f9;">
                <strong class="text-secondary">Empresa / Contrato:</strong>
                <span class="text-dark">{{ $coordenadas->nombre_empresa }} - {{ $coordenadas->tipo_contrato }}</span>
            </li>
            <li class="list-group-item" style="background-color: #f1f5f9;">
                <strong class="text-secondary">Teléfono operador:</strong>
                <span class="text-dark">{{ $coordenadas->telefono }}</span>
            </li>
            <li class="list-group-item" style="background-color: #f1f5f9;">
                <strong class="text-secondary">No. contenedor:</strong>
                <span class="text-dark">{{ $coordenadas->num_contenedor }}</span>
            </li>
            <li class="list-group-item" style="background-color: #f1f5f9;">
                <strong class="text-secondary">Num. placas:</strong>
                <span class="text-dark">{{ $coordenadas->placas }}</span>
            </li>
            <li class="list-group-item" style="background-color: #f1f5f9;">
                <strong class="text-secondary">Nombre del operador:</strong>
                <span class="text-dark">{{ $coordenadas->nombre }}</span>
            </li>
        </ul>
        <br/>
        <div style="width: 100%; max-width: 550px; margin-bottom: 10px;">
      <div style="background-color: #eee; border-radius: 10px; overflow: hidden;">
        <div id="barraProgreso" style="height: 20px; width: 0%; background-color: #4caf50; transition: width 0.3s;"></div>
      </div>
       <p id="textoProgreso" class="mt-1 text-dark" style="font-weight: bold;">Progreso: 0%</p>
  </div>
    </div>
    
</div>
@if($coordenadas->id_asignacion !=0)
      <!-- Barra de progreso -->

     
      <!-- Carrusel de preguntas -->
      <div id="carruselPreguntas" class="preguntas-container" style="width: 100%; max-width: 600px;">
   
  
    

    @foreach ($preguntas as $index => $pregunta)
           
        <div class="pregunta" style="display: {{ $index === $primeraSinResponder ? 'block' : 'none' }};" data-index="{{ $index }}">
                    <div class="p-4 mb-3 text-dark"
                        style="background-color: white; border-radius: 8px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);">
                        <h4>{{ $pregunta['texto'] }}</h4>
                        @if (!$respuesta)
                            @if (isset($pregunta['opciones']) && count($pregunta['opciones']) > 0)
                                <div class="form-floating mt-3">
                                    <select class="form-select" name="{{ $pregunta['campo'] }}_tipo" id="{{ $pregunta['campo'] }}_tipo"
                                        onchange="guardarRespuesta({{ $index }}, '{{ $tipo }}')">
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
          @if( $index < $primeraSinResponder)
         <script> 
          document.addEventListener('DOMContentLoaded', function () {
            actualizarProgresoInicial({{  $index }}); 
});
         </script>
          @endif
        
    @endforeach
</div>
 
      <!-- Resumen final -->
      <div id="resumen" class="mt-5" style="display: none;">
        <h4 class="text-white">✅ Respuestas registradas:</h4>
        <pre id="resumenRespuestas" style="background: rgba(255,255,255,0.9); padding: 15px; border-radius: 10px;"></pre>
      </div>

    </div>
  </div>
  @endif
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
        { columna: 'registro_puerto', datetime: 'registro_puerto_datatime' },
        { columna: 'dentro_puerto', datetime: 'dentro_puerto_datatime' },
        { columna: 'descarga_vacio', datetime: 'descarga_vacio_datatime' },
        { columna: 'cargado_contenedor', datetime: 'cargado_contenedor_datatime' },
        { columna: 'fila_fiscal', datetime: 'fila_fiscal_datatime' },
        { columna: 'modulado_tipo', datetime: 'modulado_tipo_datatime' },
        { columna: 'descarga_patio', datetime: 'descarga_patio_datetime' },
    ],
    f: [
        { columna: 'cargado_patio', datetime: 'cargado_patio_datetime' },
        { columna: 'en_destino', datetime: 'en_destino_datatime' },
        { columna: 'inicio_descarga', datetime: 'inicio_descarga_datatime' },
        { columna: 'fin_descarga', datetime: 'fin_descarga_datatime' },
        { columna: 'recepcion_doc_firmados', datetime: 'recepcion_doc_firmados_datatime' },
    ],
    c: [
        { columna: 'registro_puerto', datetime: 'registro_puerto_datatime' },
        { columna: 'dentro_puerto', datetime: 'dentro_puerto_datatime' },
        { columna: 'descarga_vacio', datetime: 'descarga_vacio_datatime' },
        { columna: 'cargado_contenedor', datetime: 'cargado_contenedor_datatime' },
        { columna: 'fila_fiscal', datetime: 'fila_fiscal_datatime' },
        { columna: 'modulado_tipo', datetime: 'modulado_tipo_datatime' },
        { columna: 'modulado_coordenada', datetime: 'modulado_coordenada_datatime' },
        { columna: 'en_destino', datetime: 'en_destino_datatime' },
        { columna: 'inicio_descarga', datetime: 'inicio_descarga_datatime' },
        { columna: 'fin_descarga', datetime: 'fin_descarga_datatime' },
        { columna: 'recepcion_doc_firmados', datetime: 'recepcion_doc_firmados_datatime' }
    ]
};
function guardarRespuesta(index,tquestio) {
    navigator.geolocation.getCurrentPosition(function(pos) {
        const lat = pos.coords.latitude;
        const lng = pos.coords.longitude;
        const fechaHora = new Date().toISOString();

        const idAsignacion = document.getElementById('id_asignacion')?.value || null;
        const idCoordenada = document.getElementById('id_coordenada')?.value || null;
        const estadoC = document.getElementById('estadoC')?.value || null;
        const estadoB = document.getElementById('estadoB')?.value || null;
        const estadoF = document.getElementById('estadoF')?.value || null;

       if( (index+1) == totalPreguntas ){
          if (tquestio ==='c'){
            estadoC=2;
          } else if (tquestio ==='b'){
            estadoB=2;
          }else if (tquestio ==='f'){
            estadoF=2;
          }

       }


        const coordenadas = `${lat},${lng}`;
            // Obtener las columnas correspondientes a la pregunta
        const columnas = columnasPorTipo[tquestio]; // obtenés el array correspondiente
        let columna='';
        let columnaDatetime='';
        if (columnas && columnas[index]) {
            columna = columnas[index].columna;
          columnaDatetime = columnas[index].datetime;

            console.log("Columna:", columna);
            console.log("Fecha:", columnaDatetime);
        }

        // Enviar por AJAX a Laravel
        fetch("{{ route('guardar.respuestaCoordenada') }}", {
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
                columna_datetime: columnaDatetime,
                tipo_b_estado :estadoB,
                tipo_f_estado:estadoF,
                tipo_c_estado:estadoC,
            })
        })
        .then(response => response.json())
        .then(data => {
            if (index < totalPreguntas - 1) {
                mostrarPregunta(index + 1);
                actualizarProgreso();
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
function actualizarProgreso() {
 const preguntaActual= indiceActual+1;
    const porcentaje = Math.round((preguntaActual / totalPreguntas) * 100);

    // Actualiza barra y texto
    document.getElementById('barraProgreso').style.width = porcentaje + '%';
    document.getElementById('textoProgreso').textContent = `Progreso: ${porcentaje}%`;
}
function actualizarProgresoInicial(indiceGuardado) {
 const preguntaActual= indiceGuardado;
    const porcentaje = Math.round((preguntaActual / totalPreguntas) * 100);

    // Actualiza barra y texto
    document.getElementById('barraProgreso').style.width = porcentaje + '%';
    document.getElementById('textoProgreso').textContent = `Progreso: ${porcentaje}%`;
}
function abrirEnMapsfila_fiscal(coordenadas) {

  var coordenadasFormato = coordenadas.replace(",", "+").replace(" ", "+");
  var url = 'https://www.google.com/maps/search/?api=1&query=' + coordenadasFormato;
  window.open(url, '_blank');
 }


</script>