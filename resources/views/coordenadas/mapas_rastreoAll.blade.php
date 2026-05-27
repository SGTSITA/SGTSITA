<!DOCTYPE html>
<html>

<head>
    <title>Rastreo Varios</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>

    <div class="container-fluid">
        <div class="row">
            @foreach ($conboys as $conboy)
                <div class="col-12 col-md-6 p-2">
                    <div class="card shadow-sm">
                        <div id="info-{{ $conboy->id }}" class="shadow"
                            style="
                        position: absolute;
                        top: 1px;left: 25%;
                        background: rgba(255, 255, 255, 0.95);
                        border-radius: 10px;
                        padding: 5px;z-index: 999;
                        width: 500px;max-width: 90%;">
                            <div class="table-responsive">
                                <table class="table table-bordered align-middle text-center shadow-sm mb-0">
                                    <thead class="table-primary">
                                        <tr>
                                            {{-- <th>Distancia</th> --}}
                                            <th>Tipo</th>
                                            <th>Contenedores</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            {{-- <td><span id="distanciaSpan-{{ $conboy->id }}" class="fw-semibold"></span></td> --}}
                                            <td><span id="tipoSpan-{{ $conboy->id }}" class="fw-semibold"></span></td>
                                            <td><span id="contenedorSpan-{{ $conboy->id }}"
                                                    class="fw-semibold"></span></td>
                                            <td>
                                                <button id="btnDetener-{{ $conboy->id }}"
                                                    class="btn btn-sm btn-secondary mb-1" style="display: none;">
                                                    <i class="bi bi-pause-circle"></i>
                                                </button>

                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-header">Convoy {{ $conboy->no_conboy }}</div>
                        <div class="card-body p-0" style="height: 400px;">
                            <div id="map-{{ $conboy->id }}" style="height: 100%;"></div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="modal fade" id="modalInfoViaje" tabindex="-1" aria-labelledby="modalInfoViajeLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content shadow-lg rounded-4">
                    <div class="modal-header bg-primary text-white rounded-top-4">
                        <h5 class="modal-title" id="modalInfoViajeLabel">
                            <i class="bi bi-truck-front-fill me-2"></i> Información del Viaje
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body" id="contenidoModalViaje">
                        <!-- Aquí se insertará el contenido dinámico -->
                    </div>
                    <div class="modal-footer bg-light rounded-bottom-4">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                            <i class="bi bi-x-circle"></i> Cerrar
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
        </script>
        <script
            src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googleMapsApi.apikey') }}&libraries=geometry"
            async defer onload="googleMapsReady()"></script>
        <script>
            let ItemsSelectsPorConvoy = {};
            let intervalIdsPorConvoy = {};
            let markersConvoy = {};
            let mapaAjustado = false;
            let markers = [];

            function googleMapsReady() {

                inicializarTodosLosMapas();
            }
            let convoysAll = @json($conboys);
            let detalleConvoysAll = @json($conboysdetalleAll);
            let datosALlContenedores = @json($datosAll);


            let ItemsSelectsCompuesto = [];
            let ItemsSelects = [];

            function crearItemConvoy(conboy) {
                return {
                    tipo: "Convoy",
                    id: Number(conboy.id),
                    value: conboy.no_conboy,
                };
            }

            function obtenerContenedoresTextoConvoy(idConvoy) {
                return detalleConvoysAll
                    .filter(d => Number(d.conboy_id) === Number(idConvoy))
                    .map(d => d.num_contenedor ?? d.contenedor ?? "")
                    .filter(Boolean)
                    .join(" / ");
            }
            detalleConvoysAll.forEach(infoc => {
                const convoyId = parseInt(infoc.conboy_id);
                const idContenedor = parseInt(infoc.id_contenedor);


                const detalleContenedor = datosALlContenedores.find(dc => parseInt(dc.id_contenedor) === idContenedor);


                if (detalleContenedor) {
                    const cod = infoc.no_conboy ?? '';
                    const nContenedor = infoc.num_contenedor ?? '';
                    const imei = infoc.imei ?? '';
                    const tipoGps = infoc.tipoGps ?? '';

                    const cliente = detalleContenedor.cliente ?? '';
                    const origen = detalleContenedor.origen ?? '';
                    const destino = detalleContenedor.destino ?? '';

                    const conponerStrin = `${nContenedor}|${imei}|${idContenedor}|${tipoGps}`;

                    ItemsSelectsCompuesto.push({
                        convoy_id: convoyId,
                        imeisPeticion: conponerStrin,
                        cod,
                        imei,
                        idContenedor,
                        tipoGps,
                        cliente,
                        origen,
                        destino,
                        num_contenedor: infoc.num_contenedor ?? '',
                        tipo_contrato: detalleContenedor.tipo_contrato ?? '',
                        fecha_inicio: detalleContenedor.fecha_inicio ?? '',
                        fecha_fin: detalleContenedor.fecha_fin ?? ''
                    });
                }
            });

            function inicializarTodosLosMapas() {

                @foreach ($conboys as $conboy)
                    ItemsSelectsPorConvoy[{{ $conboy->id }}] = [];
                    ItemsSelects.length = 0;
                    mapaAjustado = false;
                    convoyDisuelto = false;
                    let mapDiv{{ $conboy->id }} = document.getElementById('map-{{ $conboy->id }}')
                    const map{{ $conboy->id }} = new google.maps.Map(mapDiv{{ $conboy->id }}, {
                        center: {
                            lat: {{ $conboy->latitud ?? 0 }},
                            lng: {{ $conboy->longitud ?? 0 }}
                        },
                        zoom: 12
                    });



                    let convoySeleccionado{{ $conboy->id }} = convoysAll.find(
                        c => Number(c.id) === Number({{ $conboy->id }})
                    );

                    if (convoySeleccionado{{ $conboy->id }}) {
                        ItemsSelectsPorConvoy[{{ $conboy->id }}] = [
                            crearItemConvoy(convoySeleccionado{{ $conboy->id }})
                        ];

                        let tipoDisolucion{{ $conboy->id }} = convoySeleccionado{{ $conboy->id }}.tipo_disolucion;

                        actualizarUbicacion(
                            ItemsSelectsPorConvoy[{{ $conboy->id }}],
                            "",
                            {{ $conboy->id }},
                            tipoDisolucion{{ $conboy->id }},
                            convoySeleccionado{{ $conboy->id }}.no_conboy,
                            map{{ $conboy->id }}
                        );


                        document.getElementById('btnDetener-{{ $conboy->id }}').style.display = 'inline-block';




                        //evento detener/reanudar
                        if (document.getElementById('btnDetener-{{ $conboy->id }}')) {
                            document.getElementById('btnDetener-{{ $conboy->id }}').addEventListener('click', function() {
                                const icon = this.querySelector('i');

                                if (intervalIdsPorConvoy[{{ $conboy->id }}]) {
                                    clearInterval(intervalIdsPorConvoy[{{ $conboy->id }}]);
                                    intervalIdsPorConvoy[{{ $conboy->id }}] = null;

                                    this.innerHTML = '<i class="bi bi-play-circle"></i> Reanudar actualización';
                                    console.log('⛔ Actualización detenida.');
                                } else {
                                    actualizarUbicacion(ItemsSelectsPorConvoy[{{ $conboy->id }}], "",
                                        {{ $conboy->id }}, tipoDisolucion{{ $conboy->id }},
                                        convoySeleccionado{{ $conboy->id }}.no_conboy, map{{ $conboy->id }});

                                    intervalIdsPorConvoy[{{ $conboy->id }}] = setInterval(() => {
                                        actualizarUbicacion(ItemsSelectsPorConvoy[{{ $conboy->id }}], "",
                                            {{ $conboy->id }}, tipoDisolucion{{ $conboy->id }},
                                            convoySeleccionado{{ $conboy->id }}.no_conboy,
                                            map{{ $conboy->id }});
                                    }, 5000);

                                    this.innerHTML = '<i class="bi bi-pause-circle"></i> Detener actualización';
                                    console.log('✅ Reanudando actualización...');
                                }
                            });
                        }

                        if (intervalIdsPorConvoy[{{ $conboy->id }}]) {
                            clearInterval(intervalIdsPorConvoy[{{ $conboy->id }}]);
                        }

                        intervalIdsPorConvoy[{{ $conboy->id }}] = setInterval(() => {
                            actualizarUbicacion(ItemsSelectsPorConvoy[{{ $conboy->id }}], "", {{ $conboy->id }},
                                tipoDisolucion{{ $conboy->id }}, convoySeleccionado{{ $conboy->id }}
                                .no_conboy, map{{ $conboy->id }});
                        }, 5000);

                    } // final validar convoy local
                @endforeach
            };

            function textoTipo(idConvoy, disolucion, num_convoy) {


                let tipoDisolucion = disolucion
                let textoTipo = '';

                switch (tipoDisolucion) {
                    case 'geocerca':
                        textoTipo = 'Geocerca activada';
                        break;
                    case 'manual':
                        textoTipo = 'Finalización manual';
                        break;
                    case 'tiempo':
                        textoTipo = 'Tiempo programado';
                        break;
                    default:
                        textoTipo = '';
                }

                document.getElementById('tipoSpan-' + idConvoy).textContent = "Convoy :" + num_convoy;
            }

            function actualizarUbicacion(items, t, idConvoy, disolucion, num_convoy, map) {
                console.log('obteniendo ubicacion convoy :', idConvoy);
                let tipoSpans = document.getElementById('tipoSpan-' + idConvoy);
                let contenedorLocal = ItemsSelectsCompuesto.filter(c => c.convoy_id === idConvoy);
                let contenedoresUnidos = contenedorLocal
                    .map(c => c.num_contenedor)
                    .join(' / ');
                textoTipo(idConvoy, disolucion, num_convoy);
                document.getElementById('contenedorSpan-' + idConvoy).textContent = contenedoresUnidos;
                let tipo = "";

                let responseOk = false;
                fetch("/coordenadas/ubicacion-vehiculo", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({
                            items: items
                        })
                    })
                    .then(res => res.json())
                    .then(data => {
                        //console.log('Ubicaciones recibidas:', data);
                        const dataUbi = data;
                        console.log('obteniendo unicacion convoy, sucess data :', idConvoy);
                        //limpiarMarcadores();
                        responseOk = true;
                        if (Array.isArray(dataUbi)) {
                            dataUbi.forEach((item, index) => {
                                tipo = item.tipogps;
                                let latlocal = '';
                                let lnglocal = '';
                                let nEconomico = '';
                                let id_contenConvoy = '';

                                console.log('For response ... :', idConvoy);

                                let datosGeocerca = convoysAll.find(c => c.no_conboy === num_convoy)


                                latlocal = parseFloat(item.ubicacion.lat);
                                lnglocal = parseFloat(item.ubicacion.lng);
                                idConvoyOContenedor = item.id_contenendor;

                                if (datosGeocerca) {

                                    actualizarMapa(latlocal, lnglocal, datosGeocerca, idConvoy, map)
                                }
                                if (markers[item.ubicacion.imei + idConvoy]) {

                                    markers[item.ubicacion.imei + idConvoy].setPosition({
                                        lat: latlocal,
                                        lng: lnglocal
                                    });
                                } else {
                                    if (latlocal && lnglocal) {

                                        // let esMostrarPrimero =  1
                                        // if(esMostrarPrimero){
                                        const newMarker = new google.maps.Marker({
                                            position: {
                                                lat: latlocal,
                                                lng: lnglocal
                                            },
                                            map: map,
                                        });

                                        const contentC = `
            <div style="
                    background-color: #e3f2fd;
                    padding: 5px;
                    border-radius: 8px;
                    font-family: Arial, sans-serif;
                    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                    max-width: 240px;
                  ">
              <div style="font-weight: bold; font-size: 17px; margin-bottom: 6px;">
                <strong>Convoy:</strong> ${num_convoy}
              </div>
              <div style="font-size: 17px; line-height: 1.5;">
                <strong>Equipo:</strong> ${item.EquipoBD}<br>
                <strong>Contenedor:</strong> ${item.contenedor}
              </div>
            </div>
          `;
                                        // const newMarker = L.marker([latlocal, lnglocal]).addTo(map).bindPopup(tipoSpans + ' '+ item.contenedor).openPopup();
                                        const infoWindow = new google.maps.InfoWindow({
                                            content: contentC
                                        });
                                        infoWindow.open(map, newMarker);
                                        newMarker.addListener('click', () => {
                                            infoWindow.open(map, newMarker);
                                        });
                                        markers.push(newMarker);


                                        newMarker.addListener('click', () => {
                                            const contenedor = item.contenedor;
                                            let info = ItemsSelectsCompuesto.find(d => d.num_contenedor ===
                                                contenedor);
                                            if (!info) {
                                                if (tipoSpans.toLowerCase().includes('convoy')) {
                                                    info = contenedoresDisponiblesAll.find(d => d
                                                        .contenedor === contenedor);
                                                } else if (t === '#filtro-Equipo') {

                                                    const ahora = new Date();
                                                    info = contenedoresDisponibles.find(d => {
                                                        const inicio = new Date(d.fecha_inicio);
                                                        const fin = new Date(d.fecha_fin);

                                                        return ahora >= inicio && ahora <= fin;
                                                    });
                                                    console.log(info);
                                                    if (!info) {
                                                        alert("Información del viaje no encontrada.");
                                                        return;
                                                    }
                                                }
                                            }
                                            let extraInfo = '';

                                            if (t === '#filtro-Equipo') {
                                                extraInfo = `
                    <p><strong>IMEI CHASIS:</strong> ${info.imei_chasis}</p>
                           `;
                                            }

                                            const contenido = `
                  <div class="p-3">
                    <h5 class="mb-2">🚚 Información del Viaje</h5>
                    <p><strong>Cliente:</strong> ${info.cliente}</p>
                    <p><strong>Contenedor:</strong> ${info.contenedor}</p>
                    <p><strong>Origen:</strong> ${info.origen}</p>
                    <p><strong>Destino:</strong> ${info.destino}</p>
                    <p><strong>Contrato:</strong> ${info.tipo_contrato}</p>
                    <p><strong>Fecha Inicio:</strong> ${info.fecha_inicio}</p>
                    <p><strong>IMEI:</strong> ${info.imei}</p>

                                   ${extraInfo}
                  </div>
                `;

                                            document.getElementById('contenidoModalViaje').innerHTML =
                                                contenido;

                                            // Mostrar el modal con Bootstrap 5
                                            const modal = new bootstrap.Modal(document.getElementById(
                                                'modalInfoViaje'));
                                            modal.show();
                                        });
                                        //} //end mostrar primero
                                        tipo = tipo + ' ' + item.contenedor;

                                        // if (index === 0) {
                                        //   map.setCenter({ lat: latlocal, lng: lnglocal });
                                        // map.setZoom(15);
                                        // }
                                        markers[item.ubicacion.imei + idConvoy] = newMarker;
                                        if (!mapaAjustado) {
                                            const bounds = new google.maps.LatLngBounds();
                                            Object.values(markers).forEach(marker => bounds.extend(marker
                                                .getPosition()));
                                            map.fitBounds(bounds);
                                            mapaAjustado = true;

                                        }
                                    }

                                } // fin de else de validacion imei existe en el array markers






                                const datasave = {
                                    latitud: latlocal,
                                    longitud: lnglocal,
                                    ubicacionable_id: idConvoyOContenedor,
                                    tipo: tipo,
                                    tipoRastreo: "Seguimiento",
                                    idProceso: 0,
                                };
                                if (idConvoyOContenedor != "") {
                                    actualizarUbicacionReal(datasave)
                                }


                            });
                        } else {
                            console.warn('La respuesta no es un array de ubicaciones:', data);
                        }


                    })
                    .catch(error => {
                        console.error('Error al obtener ubicaciones:', error);
                        detener();
                    });
            }
            let geocercaCircle = null;
            let marcadorActual = null;

            // Función que se llama cada vez que actualizas la posición (intervalo)
            function actualizarMapa(lat_actual, lng_actual, datosConvoy, idConvoy, map) {
                // Crear el círculo si no existe
                let geocerca_lat = 0;
                let geocerca_lng = 0;
                let geocerca_radio = 0;
                let geocercaLatLng = null;
                let calcularDistancia = 0;
                let distancia = 0;
                const actualLatLng = new google.maps.LatLng(lat_actual, lng_actual);
                let mesaggeC = "";
                if (datosConvoy.tipo_disolucion === 'geocerca') {
                    //hacemos lo q ya tenemos y disolvemos
                    geocerca_lat = parseFloat(datosConvoy.geocerca_lat);
                    geocerca_lng = parseFloat(datosConvoy.geocerca_lng);
                    geocerca_radio = parseFloat(datosConvoy.geocerca_radio);


                    if (!geocercaCircle && geocerca_lat && geocerca_lng) {
                        geocercaCircle = new google.maps.Circle({
                            strokeColor: "#FF0000",
                            strokeOpacity: 0.8,
                            strokeWeight: 2,
                            fillColor: "#FF0000",
                            fillOpacity: 0.2,
                            map: map,
                            center: {
                                lat: geocerca_lat,
                                lng: geocerca_lng
                            },
                            radius: geocerca_radio
                        });

                    }
                    if (geocercaCircle) {
                        geocercaLatLng = new google.maps.LatLng(geocerca_lat, geocerca_lng);
                        calcularDistancia = 1;
                    }



                } else if (datosConvoy.tipo_disolucion === 'tiempo') {
                    //validamos la fecha y hora final, con los datos actuales y disolvemos...
                    const margenMinutos = 10;
                    let fechaFinalConvoy = new Date(datosConvoy.fecha_fin);
                    let fechaActual = new Date();
                    const margenMs = margenMinutos * 60 * 1000;

                    const tiempoMinimo = new Date(fechaFinalConvoy.getTime() - margenMs);
                    const tiempoMaximo = new Date(fechaFinalConvoy.getTime() + margenMs);

                    const diferenciaMs = fechaFinalConvoy - fechaActual;

                    distancia = 100;
                    geocerca_radio = 0; //defaults para q no entre a disolver
                    console.log('Fecha actual:', fechaActual);
                    console.log('Fecha fin del convoy:', fechaFinalConvoy);


                    calcularDistancia = 0;
                    const totalSegundos = Math.floor(Math.abs(diferenciaMs) / 1000);
                    const dias = Math.floor(totalSegundos / 86400);
                    const horas = Math.floor((totalSegundos % 86400) / 3600);
                    const minutos = Math.floor((totalSegundos % 3600) / 60);

                    const mensajeTiempo = `${dias} día(s), ${horas} hora(s), ${minutos} minuto(s)`;

                    if (fechaActual >= tiempoMinimo && fechaActual <= tiempoMaximo) {

                        console.log("⏱️ Dentro del rango. Tiempo para disolver:", mensajeTiempo);

                        distancia = 1;
                        geocerca_radio = 10; //cambiamos aki para q se cumpla condision y se disuelva

                    } else {
                        if (fechaActual < tiempoMinimo) {
                            console.log(`⏳ Aún no llega el momento. Faltan: ${mensajeTiempo}`);
                        } else {
                            console.log(`⚠️ Ya pasó el tiempo permitido. Han pasado: ${mensajeTiempo}`);
                        }
                    }

                    mesaggeC = mensajeTiempo;


                } else {
                    //por validar con el jefe, si un convoy tiene varios contenedores iria quitando 1 x 1 y los convoy se configuran solo por tiempo y geocerca , no es por individual.

                }

                //  const distanciaSpan = document.getElementById('distanciaSpan-'+idConvoy);

                if (calcularDistancia === 1) {

                    // Calcular distancia en metros geocerca
                    distancia = google.maps.geometry.spherical.computeDistanceBetween(actualLatLng, geocercaLatLng);
                    const distanciaKm = distancia / 1000;

                    mesaggeC = `Faltan ${distanciaKm.toFixed(2)} km para la geocerca`;

                }
                // distanciaSpan.innerHTML =mesaggeC;

                if (distancia <= geocerca_radio) {
                    if (!convoyDisuelto) {
                        console.log('✅ Entro a geocerca, se disolverá el convoy...');
                        convoyDisuelto = true;


                        fetch('/coordenadas/conboys/estatus', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                                        'content')
                                },
                                body: JSON.stringify({
                                    idconvoy: datosConvoy.id,
                                    nuevoEstatus: 'disuelto'
                                })
                            })
                            .then(response => response.json())
                            .then(data => {
                                console.log('🚚 Convoy disuelto correctamente', data);

                                if (geocercaCircle) {
                                    geocercaCircle.setMap(null); // Esto la elimina visualmente del mapa
                                    geocercaCircle = null; // Opcional: para liberar memoria y evitar referencia
                                }

                            })
                            .catch(err => {
                                console.error('❌ Error al disolver convoy', err);

                            });
                    }

                } else {
                    console.log(mesaggeC);
                }


            }


            function createMarkerIconLive(status, item, containerColor = "#0d6efd") {
                const estadoMarker = getEstadoMarker(status);

                const TIPOS = {
                    Camion: {
                        icon: "T",
                        text: "TRACTO"
                    },
                    ChasisA: {
                        icon: "A",
                        text: "CHASIS A"
                    },
                    ChasisB: {
                        icon: "B",
                        text: "CHASIS B"
                    },
                    GPS: {
                        icon: "G",
                        text: "GPS"
                    },
                };

                const tipoNormalizado = normalizarTipoEquipoMarker(
                    item?.TipoEquipo || item?.ubicacion?.tipoEquipo || "GPS",
                );

                const tipo = TIPOS[tipoNormalizado] || TIPOS.GPS;
                const texto = construirTextoMarker(item);

                const estadoTextoCorto =
                    status === "moving" ?
                    "EN RUTA" :
                    status === "parked" ?
                    "DETENIDO" :
                    "SIN SEÑAL";

                const mostrarAnimacion = status === "moving";

                const svg = `
<svg width="360" height="138" viewBox="0 0 360 138" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <filter id="liveShadow" x="-30%" y="-30%" width="180%" height="200%">
            <feDropShadow dx="0" dy="8" stdDeviation="6" flood-color="rgba(0,0,0,0.35)" flood-opacity="0.55"/>
        </filter>

        <linearGradient id="liveMain" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="${containerColor}" stop-opacity="1"/>
            <stop offset="100%" stop-color="#0f172a" stop-opacity="0.96"/>
        </linearGradient>

        <linearGradient id="liveBadge" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="${containerColor}" stop-opacity="0.98"/>
            <stop offset="60%" stop-color="${containerColor}" stop-opacity="0.90"/>
            <stop offset="100%" stop-color="${containerColor}" stop-opacity="0.74"/>
        </linearGradient>

        <linearGradient id="liveShine" x1="0%" y1="0%" x2="100%" y2="0%">
            <stop offset="0%" stop-color="#ffffff" stop-opacity="0.24"/>
            <stop offset="45%" stop-color="#ffffff" stop-opacity="0.08"/>
            <stop offset="100%" stop-color="#ffffff" stop-opacity="0"/>
        </linearGradient>

        <clipPath id="textClipLive">
            <rect x="100" y="22" width="228" height="88" rx="6"/>
        </clipPath>

        <clipPath id="badgeClipLive">
            <rect x="88" y="18" width="248" height="92" rx="18"/>
        </clipPath>
    </defs>

    ${
        mostrarAnimacion
            ? `
            <circle cx="58" cy="52" r="24" fill="${estadoMarker.color}" opacity="0.20">
                <animate attributeName="r" values="24;44;24" dur="1.25s" repeatCount="indefinite"/>
                <animate attributeName="opacity" values="0.22;0;0.22" dur="1.25s" repeatCount="indefinite"/>
            </circle>

            <circle cx="58" cy="52" r="34" fill="none" stroke="${estadoMarker.color}" stroke-width="2" opacity="0.28">
                <animate attributeName="r" values="30;52;30" dur="1.55s" repeatCount="indefinite"/>
                <animate attributeName="opacity" values="0.35;0;0.35" dur="1.55s" repeatCount="indefinite"/>
            </circle>
            `
            : ""
    }

    <g filter="url(#liveShadow)">
        <!-- línea conectora -->
        <path d="M78 52 H93" stroke="${estadoMarker.color}" stroke-width="4" stroke-linecap="round"/>

        <!-- círculo principal -->
        <circle cx="58" cy="52" r="30" fill="url(#liveMain)"/>
        <circle cx="58" cy="52" r="24" fill="rgba(255,255,255,0.10)" stroke="rgba(255,255,255,0.35)" stroke-width="1.5"/>
        <circle cx="58" cy="52" r="19" fill="${estadoMarker.soft}" stroke="${estadoMarker.color}" stroke-width="3"/>

        <text x="58" y="59"
            text-anchor="middle"
            font-size="18"
            font-family="Arial, sans-serif"
            font-weight="900"
            fill="${estadoMarker.color}">
            ${tipo.icon}
        </text>

        <!-- punto status -->
        <circle cx="79" cy="30" r="8" fill="${estadoMarker.color}" stroke="#ffffff" stroke-width="3"/>

        <!-- estado debajo del círculo, sin tapar datos -->
        <rect x="25" y="82" width="66" height="17" rx="8.5" fill="${estadoMarker.color}"/>
        <text x="58" y="93.5"
            text-anchor="middle"
            font-size="7.5"
            font-family="Arial, sans-serif"
            font-weight="900"
            fill="#ffffff">
            ${estadoTextoCorto}
        </text>

        <!-- caja derecha color del marker -->
        <rect x="88" y="18" width="248" height="92" rx="18" fill="url(#liveBadge)"/>
        <rect x="88" y="18" width="248" height="92" rx="18" fill="rgba(0,0,0,0.14)"/>

        <g clip-path="url(#badgeClipLive)">
            <rect x="88" y="18" width="248" height="30" fill="url(#liveShine)"/>
            <circle cx="312" cy="12" r="54" fill="#ffffff" opacity="0.10"/>
        </g>

        <rect x="88.5" y="18.5" width="247" height="91" rx="17.5" fill="none" stroke="rgba(255,255,255,0.22)"/>

        <g clip-path="url(#textClipLive)">
            <!-- título / contenedor -->
            <text x="102" y="40"
                font-size="14"
                font-family="Arial, sans-serif"
                font-weight="900"
                fill="#ffffff">
                ${escapeSvgText(texto.titulo)}
            </text>

            <!-- subtítulo / equipo -->
            <text x="102" y="58"
                font-size="11.3"
                font-family="Arial, sans-serif"
                font-weight="800"
                fill="#ffffff"
                opacity="0.90">
                ${escapeSvgText(texto.subtitulo)}
            </text>

            ${
                texto.detalle
                    ? `
                    <!-- detalle -->
                    <text x="102" y="75"
                        font-size="10.2"
                        font-family="Arial, sans-serif"
                        font-weight="800"
                        fill="#ffffff"
                        opacity="0.78">
                        ${escapeSvgText(texto.detalle)}
                    </text>`
                    : ""
            }
        </g>

        ${
            texto.transportista
                ? `
                <!-- LT / transportista ancho completo -->
                <rect x="102" y="84" width="224" height="17" rx="8.5" fill="rgba(0,0,0,0.26)"/>
                <text x="110" y="95.5"
                    font-size="9.2"
                    font-family="Arial, sans-serif"
                    font-weight="900"
                    fill="#ffffff">
                    LT: ${escapeSvgText(texto.transportista)}
                </text>`
                : ""
        }

        <!-- punta -->
        <path d="M58 132 L45 104 H71 Z" fill="url(#liveMain)"/>
        <path d="M58 132 L45 104 H71 Z" fill="none" stroke="${estadoMarker.color}" stroke-width="2.5"/>
    </g>
</svg>`;

                return {
                    url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg),
                    scaledSize: new google.maps.Size(360, 138),
                    anchor: new google.maps.Point(58, 132),
                };
            }

            function actualizarUbicacionReal(coordenadaData) {
                fetch('/coordenadas/rastrear/savehistori', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify(coordenadaData)
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            console.log('Coordenada guardada:', data.data);
                        } else {
                            console.warn('Error al guardar coordenada', data);
                        }
                    })
                    .catch(error => {
                        console.error('Error en la solicitud:', error);
                    });

            }
        </script>


</body>

</html>
