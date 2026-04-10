<!DOCTYPE html>
<html>

<head>
    <title>Rastreo</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.3/dist/leaflet.css" />

    <style>
        html,
        body {
            height: 100%;
            margin: 0;
            padding: 0;
        }

        #mapaRastreo {
            height: 100vh;
            position: relative;
        }

        #info table {
            width: 100%;
            border-collapse: collapse;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
            font-family: 'Segoe UI', sans-serif;
            font-size: 14px;
            /* Más pequeña */
        }

        #info th,
        #info td {
            border: 1px solid #dee2e6;
            padding: 6px 8px;
            /* Menos padding = menos altura */
            text-align: center;
            line-height: 1.2;
            /* Más compacto verticalmente */
        }

        #info thead {
            background-color: #0d6efd;
            color: white;
        }

        #info tbody td span {
            font-weight: 400;
        }

        #info tr:nth-child(even) {
            background-color: #f8f9fa;
        }

        .btn-toggle {
            background-color: #f44366;
            color: white;
            font-weight: bold;
            padding: 10px 20px;
            border: none;
            border-radius: 50px;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 14px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
            transition: background-color 0.2s ease;
        }

        .btn-toggle:hover {
            background-color: #d63455;
        }

        .btn-toggle i {
            font-size: 16px;
        }
    </style>
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        crossorigin="anonymous" />

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body>
    <div id="info" class="shadow"
        style="
                position: absolute;
                top: 5px;
                left: 35%;
                background: rgba(255, 255, 255, 0.95);
                border-radius: 10px;
                padding: 5px;
                z-index: 999;
                width: 750px;
                max-width: 90%;
            ">
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
                        {{-- <td><span id="distanciaSpan" class="fw-semibold"></span></td> --}}
                        <td><span id="tipoSpan" class="fw-semibold"></span></td>
                        <td><span id="contenedorSpan" class="fw-semibold"></span></td>
                        <td>
                            <button id="btnDetener" class="btn btn-sm btn-secondary mb-1" style="display: none">
                                <i class="bi bi-pause-circle"></i>
                            </button>
                            <a href="{{ route('HistorialUbicaciones') }}" class="btn btn-sm btn-warning" target="_blank"
                                rel="noopener noreferrer">
                                <i class="bi bi-clock-history me-1"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div id="mapaRastreo"></div>

    <div class="modal fade" id="modalInfoViaje" tabindex="-1" aria-labelledby="modalInfoViajeLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content shadow-lg rounded-4">
                <div class="modal-header bg-primary text-white rounded-top-4">
                    <h5 class="modal-title" id="modalInfoViajeLabel">
                        <i class="bi bi-truck-front-fill me-2"></i>
                        Información del Viaje
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <!-- Nav tabs (se generan dinámicamente con los contenedores) -->
                    <ul class="nav nav-tabs" id="contenedorTabs" role="tablist">
                        <!-- Aquí se insertan las pestañas por contenedor -->
                    </ul>

                    <!-- Contenido de cada tab -->
                    <div class="tab-content mt-3" id="contenedorTabsContent">
                        <!-- Aquí se insertan los divs de cada contenedor -->
                    </div>
                </div>
                <div class="modal-footer bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        <i class="bi bi-x-circle"></i>
                        Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset('assets/js/core/bootstrap.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous">
    </script>
    <script>
        let directionsService = null;
        let directionsRenderer = [];
        let equiposSearch = [];
        let map;
        let markers = [];
        let convoyDisuelto = false;
        window.initMap = function() {
            directionsService = new google.maps.DirectionsService();
            map = new google.maps.Map(document.getElementById('mapaRastreo'), {
                center: {
                    lat: 0,
                    lng: 0
                },
                zoom: 2,
            });

            const marker = new google.maps.Marker({
                position: {
                    lat: 0,
                    lng: 0
                },
                map: map,
            });
        };

        const params = new URLSearchParams(window.location.search);
        let detalleConvoys;
        let convoysAll;
        let contenedoresDisponibles = [];
        let contenedoresDisponiblesAll = [];
        let mapaAjustado = false;
        let contador = 0;
        let detalleConvoysAll;
        let intervalId = null;
        let ItemsSelects = [];
        let idConvoyOContenedor = 0;
        const contenedor = params.get('contenedor');
        let tipoSpans = params.get('tipoS');

        function textoTipo() {
            let extraertipo = tipoSpans.replace(/Convoy\s*:\s*/, '').trim();
            let tipoDisolucion = convoysAll.find((c) => c.no_conboy === extraertipo)?.tipo_disolucion ?? null;
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

            document.getElementById('tipoSpan').textContent = tipoSpans;
        }

        function mostrarInfoConvoy(contenedores, equipo, chasis) {
            const tabs = document.getElementById("contenedorTabs");
            const content = document.getElementById("contenedorTabsContent");

            tabs.innerHTML = "";
            content.innerHTML = "";
            let info = "";

            contenedores.forEach((contenedor, index) => {
                let tabId = contenedor.num_contenedor;
                if (!tabId) {
                    tabId = contenedor.contenedor;
                }


                tabs.innerHTML += `
      <li class="nav-item" role="presentation">
        <button class="nav-link ${index === 0 ? "active" : ""}"
                id="${tabId}-tab"
                data-bs-toggle="tab"
                data-bs-target="#${tabId}"
                type="button"
                role="tab"
                aria-controls="${tabId}"
                aria-selected="${index === 0 ? "true" : "false"}">
           ${tabId}
        </button>
      </li>
    `;

                info = contenedoresDisponiblesAll.find(
                    (d) => d.contenedor === contenedor.num_contenedor,
                );
                if (!info) {
                    info = contenedoresDisponiblesAll.find(
                        (d) => d.contenedor === contenedor.contenedor,
                    );
                }
                // <p><strong>Contenedor:</strong> ${info.contenedor}</p>
                if (info) {
                    let filtroEqu = equiposSearch.find(
                        (equipo) => equipo.id === info.id_equipo_unico,
                    );

                    let infoContenido = `
                  <div class="tab-pane fade ${index === 0 ? "show active" : ""}"
           id="${tabId}"
           role="tabpanel"
           aria-labelledby="${tabId}-tab">

                    <p><strong>Cliente:</strong> ${info.cliente}</p>

                    <p><strong>Origen:</strong> ${info.origen}</p>
                    <p><strong>Destino:</strong> ${info.destino}</p>
                    <p><strong>Contrato:</strong> ${info.tipo_contrato}</p>
                    <p><strong>Fecha Inicio:</strong> ${info.fecha_inicio}</p>
                    <p><strong>Fecha Fin:</strong> ${info.fecha_fin}</p>
                    <p><strong>Contacto Entrega:</strong> ${info.cp_contacto_entrega}</p>
                    <p><strong>Proveedor:</strong> ${info.empresa}</p>
<p><strong>Transportista:</strong> ${info.transportista_nombre}</p>
<p><strong>Operador:</strong> ${info.operador}</p>
<p><strong>Telefono:</strong> ${info.beneficiario_telefono}</p>
                    <p>
                        <span style="margin-right: 15px;">
                            <strong>IMEI:</strong> ${info.imei}
                        </span>
                        <strong>Equipo:</strong> ${info.id_equipo}
                        <strong>Placas:</strong> ${filtroEqu.placas}
                    </p>
                    <p>
                        <span style="margin-right: 15px;">
                            <strong>IMEI CHASIS:</strong> ${info.imei_chasis}
                        </span>
                        <strong>Chasis:</strong> ${info.id_equipo_chasis}
                    </p>
                  </div>
                `;


                    content.innerHTML += infoContenido;
                } else {
                    Swal.fire({
                        title: "Información de viaje no disponible",
                        text: "No se encontró información para el contenedor seleccionado.",
                        icon: "warning",
                    });
                }
            });

            const modal = new bootstrap.Modal(
                document.getElementById("modalInfoViaje"),
            );
            modal.show();
        }

        function actualizarUbicacion(imeis, t) {
            textoTipo();
            document.getElementById('contenedorSpan').textContent = contenedor;
            let tipo = '';

            let responseOk = false;
            fetch('/coordenadas/ubicacion-vehiculo', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify({
                        imeis: imeis
                    }),
                })
                .then((res) => res.json())
                .then((data) => {
                    //console.log('Ubicaciones recibidas:', data);
                    const dataUbi = data;

                    //limpiarMarcadores();
                    responseOk = true;
                    if (Array.isArray(dataUbi)) {
                        dataUbi.forEach((item, index) => {
                            tipo = item.tipogps;
                            let latlocal = '';
                            let lnglocal = '';
                            let nEconomico = '';
                            let id_contenConvoy = '';

                            let extraertipoC = tipoSpans.replace(/Convoy\s*:\s*/, '').trim();
                            let datosGeocerca = convoysAll.find((c) => c.no_conboy === extraertipoC);

                            latlocal = parseFloat(item.ubicacion.lat);
                            lnglocal = parseFloat(item.ubicacion.lng);
                            idConvoyOContenedor = item.id_contenendor;

                            const markerKey = item.ubicacion.imei;

                            if (datosGeocerca) {
                                actualizarMapa(latlocal, lnglocal, datosGeocerca);
                            }
                            if (markers[item.ubicacion.imei]) {
                                markers[item.ubicacion.imei].setPosition({
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
    background: #ffffff;
    padding: 12px;
    border-radius: 12px;
    font-family: 'Segoe UI', Arial, sans-serif;
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    max-width: 260px;
    display: flex;
    flex-direction: column;
    gap: 10px;
">

    <!-- HEADER -->
    <div style="
        font-weight: 600;
        font-size: 15px;
        color: #1976d2;
    ">
        ${tipoSpans}
    </div>


    <div style="
        font-size: 14px;
        color: #333;
        line-height: 1.5;
    ">
        <div><strong>Equipo:</strong> ${item.EquipoBD}</div>
        <div><strong>Contenedor:</strong> ${item.contenedor}</div>
    </div>


      <button class="btnRuta" data-key="${markerKey}"
        style="
            background: linear-gradient(135deg, #1976d2, #42a5f5);
            color: white;
            border: none;
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
        ">
        📍 Mostrar ruta
    </button>


    <div id="infoRuta_${markerKey}" style="
        background: #f1f8ff;
        padding: 8px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        color: #0d47a1;
        display: none;
    ">
    </div>

</div>
          `;
                                    // const newMarker = L.marker([latlocal, lnglocal]).addTo(map).bindPopup(tipoSpans + ' '+ item.contenedor).openPopup();
                                    const infoWindow = new google.maps.InfoWindow({
                                        content: contentC,
                                    });
                                    infoWindow.open(map, newMarker);
                                    newMarker.addListener('click', () => {
                                        infoWindow.open(map, newMarker);
                                    });
                                    markers.push(newMarker);

                                    newMarker.addListener('click', () => {
                                        const contenedorRes = item.contenedor;

                                        let info = contenedoresDisponibles.find(
                                            (d) => d.contenedor === contenedorRes,
                                        );

                                        if (!info && t.toLowerCase().includes("convoy")) {
                                            info = contenedoresDisponiblesAll.find(
                                                (d) => d.contenedor === contenedorRes,
                                            );
                                        }

                                        if (t === "Convoy") {
                                            let contenedoresConvoy = detalleConvoys.filter(
                                                (d) => d.no_conboy === num_convoy,
                                            );

                                            mostrarInfoConvoy(contenedoresConvoy, item.EquipoBD, "");
                                        } else {
                                            mostrarInfoConvoy(info ? [info] : [], item.EquipoBD, "");
                                        }



                                    });
                                    //} //end mostrar primero
                                    tipo = tipo + ' ' + item.contenedor;

                                    // if (index === 0) {
                                    //   map.setCenter({ lat: latlocal, lng: lnglocal });
                                    // map.setZoom(15);
                                    // }
                                    markers[item.ubicacion.imei] = newMarker;
                                    if (!mapaAjustado) {
                                        const bounds = new google.maps.LatLngBounds();
                                        Object.values(markers).forEach((marker) =>
                                            bounds.extend(marker.getPosition()),
                                        );
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
                                status_api: item.status ?? 0,
                                new_id: item.new_id ?? null,
                                tiempo_respuesta_ms: item.tiemporespuesta ?? null,

                                valorSolicitado: item.value ?? null,

                                data: item,
                                messageAp: item.messageAp ?? null,
                            };
                            if (idConvoyOContenedor != '') {
                                actualizarUbicacionReal(datasave);
                            }
                        });
                    } else {
                        console.warn('La respuesta no es un array de ubicaciones:', data);
                    }
                })
                .catch((error) => {
                    console.error('Error al obtener ubicaciones:', error);
                    detener();
                });
        }
        cargarinicial();

        function limpiarMarcadores() {
            markers.forEach((marker) => marker.setMap(null));
            markers = [];
        }

        function cargarinicial() {
            fetch(`/coordenadas/contenedor/searchEquGps?`)
                .then((response) => response.json())
                .then((data) => {
                    contenedoresDisponibles = data.datos;
                    detalleConvoys = data.dataConten;
                    contenedoresDisponiblesAll = data.datosAll;
                    convoysAll = data.conboys;

                    equiposSearch = data.equiposAll;

                    detalleConvoysAll = data.dataContenAll;

                    if (!contenedoresDisponibles) {
                        alert('No se encontró información del contenedor.');
                    }

                    const contenedores = contenedor
                        .trim()
                        .replace(/\s*\/\s*/g, ' ')
                        .split(/\s+/);
                    contenedores.forEach((cod) => {
                        const infoc = contenedoresDisponibles.find((d) => d.contenedor === cod);

                        if (infoc) {
                            let conponerStrin =
                                cod + '|' + infoc.imei + '|' + infoc.id_contenedor + '|' + infoc.tipoGps;
                            ItemsSelects.push(conponerStrin);
                            if (infoc.imei_chasis != 'NO DISPONIBLE') {
                                let conponerStrin2 =
                                    cod + '|' + infoc.imei_chasis + '|' + infoc.id_contenedor + '|' + infoc
                                    .tipoGpsChasis;
                                ItemsSelects.push(conponerStrin2);
                            }
                        } else {
                            //buscamos en todos pero se valida si es convoy para saber si tenemos que buscar aunq no le pertenece el contenedor al user
                            if (tipoSpans.toLowerCase().includes('convoy')) {
                                const infoc2 = contenedoresDisponiblesAll.find((d) => d.contenedor === cod);
                                if (infoc2) {
                                    let conponerStrin =
                                        cod + '|' + infoc2.imei + '|' + infoc2.id_contenedor + '|' + infoc2
                                        .tipoGps;
                                    ItemsSelects.push(conponerStrin);
                                } else {
                                    console.warn(`Contenedor ${cod} no encontrado en contenedoresDisponibles.`);
                                }
                            } else {
                                console.warn(`Contenedor ${cod} no encontrado en contenedoresDisponibles.`);
                            }
                        }
                    });

                    if (ItemsSelects.length > 0) {
                        actualizarUbicacion(ItemsSelects, '');
                        document.getElementById('btnDetener').style.display = 'inline-block';

                        if (intervalId) clearInterval(intervalId);

                        intervalId = setInterval(() => {
                            actualizarUbicacion(ItemsSelects, '');
                        }, 5000);
                    } else {
                        Swal.fire('Atención', 'Ningún contenedor válido fue encontrado.', 'warning');
                    }
                }); // <-- cierre del .then()
        }

        let geocercaCircle = null;
        let marcadorActual = null;


        function actualizarMapa(lat_actual, lng_actual, datosConvoy) {

            let geocerca_lat = 0;
            let geocerca_lng = 0;
            let geocerca_radio = 0;
            let geocercaLatLng = null;
            let calcularDistancia = 0;
            let distancia = 0;
            const actualLatLng = new google.maps.LatLng(lat_actual, lng_actual);
            let mesaggeC = '';
            if (datosConvoy.tipo_disolucion === 'geocerca') {

                geocerca_lat = parseFloat(datosConvoy.geocerca_lat);
                geocerca_lng = parseFloat(datosConvoy.geocerca_lng);
                geocerca_radio = parseFloat(datosConvoy.geocerca_radio);

                if (!geocercaCircle && geocerca_lat && geocerca_lng) {
                    geocercaCircle = new google.maps.Circle({
                        strokeColor: '#FF0000',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: '#FF0000',
                        fillOpacity: 0.2,
                        map: map,
                        center: {
                            lat: geocerca_lat,
                            lng: geocerca_lng
                        },
                        radius: geocerca_radio,
                    });
                }
                if (geocercaCircle) {
                    geocercaLatLng = new google.maps.LatLng(geocerca_lat, geocerca_lng);
                    calcularDistancia = 1;
                }
            } else if (datosConvoy.tipo_disolucion === 'tiempo') {

                const margenMinutos = 10;
                let fechaFinalConvoy = new Date(datosConvoy.fecha_fin);
                let fechaActual = new Date();
                const margenMs = margenMinutos * 60 * 1000;

                const tiempoMinimo = new Date(fechaFinalConvoy.getTime() - margenMs);
                const tiempoMaximo = new Date(fechaFinalConvoy.getTime() + margenMs);

                const diferenciaMs = fechaFinalConvoy - fechaActual;

                distancia = 100;
                geocerca_radio = 0;
                console.log('Fecha actual:', fechaActual);
                console.log('Fecha fin del convoy:', fechaFinalConvoy);

                calcularDistancia = 0;
                const totalSegundos = Math.floor(Math.abs(diferenciaMs) / 1000);
                const dias = Math.floor(totalSegundos / 86400);
                const horas = Math.floor((totalSegundos % 86400) / 3600);
                const minutos = Math.floor((totalSegundos % 3600) / 60);

                const mensajeTiempo = `${dias} día(s), ${horas} hora(s), ${minutos} minuto(s)`;

                if (fechaActual >= tiempoMinimo && fechaActual <= tiempoMaximo) {
                    console.log('Dentro del rango. Tiempo para disolver:', mensajeTiempo);

                    distancia = 1;
                    geocerca_radio = 10;
                } else {
                    if (fechaActual < tiempoMinimo) {
                        console.log(`Aún no llega el momento. Faltan: ${mensajeTiempo}`);
                    } else {
                        console.log(`Ya pasó el tiempo permitido. Han pasado: ${mensajeTiempo}`);
                    }
                }

                mesaggeC = mensajeTiempo;
            } else {
                //por validar con el jefe, si un convoy tiene varios contenedores iria quitando 1 x 1 y los convoy se configuran solo por tiempo y geocerca , no es por individual.
            }

            //  const distanciaSpan = document.getElementById('distanciaSpan');

            if (calcularDistancia === 1) {
                // Calcular distancia en metros geocerca
                distancia = google.maps.geometry.spherical.computeDistanceBetween(actualLatLng, geocercaLatLng);
                const distanciaKm = distancia / 1000;

                mesaggeC = `Faltan ${distanciaKm.toFixed(2)} km para la geocerca`;
            }
            //distanciaSpan.innerHTML =mesaggeC;

            if (distancia <= geocerca_radio) {
                if (!convoyDisuelto) {
                    console.log('Entro a geocerca, se disolverá el convoy...');
                    convoyDisuelto = true;

                    fetch('/coordenadas/conboys/estatus', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document
                                    .querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                            },
                            body: JSON.stringify({
                                idconvoy: datosConvoy.id,
                                nuevoEstatus: 'disuelto',
                            }),
                        })
                        .then((response) => response.json())
                        .then((data) => {
                            console.log('🚚 Convoy disuelto correctamente', data);

                            if (geocercaCircle) {
                                geocercaCircle.setMap(null);
                                geocercaCircle = null;
                            }
                        })
                        .catch((err) => {
                            console.error('❌ Error al disolver convoy', err);
                        });
                }
            } else {
                console.log(mesaggeC);
            }
        }

        document.getElementById('btnDetener').addEventListener('click', function() {
            const icon = this.querySelector('i');

            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;

                this.innerHTML = '<i class="bi bi-play-circle"></i> Reanudar actualización';
                console.log('Actualización detenida.');
            } else {
                actualizarUbicacion(ItemsSelects, '');

                if (intervalId) clearInterval(intervalId);

                intervalId = setInterval(() => {
                    actualizarUbicacion(ItemsSelects, '');
                }, 5000);

                this.innerHTML = '<i class="bi bi-pause-circle"></i> Detener actualización';
                console.log('Reanudando actualización...');
            }
        });

        function detener() {
            if (intervalId) {
                clearInterval(intervalId);
                intervalId = null;
                document.getElementById('btnDetener').style.display = 'none';
            }
        }



        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("btnRuta")) {
                const key = e.target.dataset.key;

                const btn = e.target;
                const infoSpan = document.querySelector(`.infoRuta[data-key="${key}"]`);

                const marker = markers[key];

                if (!marker) return;

                const position = marker.getPosition();

                const origin = {
                    lat: position.lat(),
                    lng: position.lng(),
                };

                let info = contenedoresDisponibles.find((d) =>
                    key.includes(d.contenedor),
                );

                if (!info || !info.latitud || !info.longitud) {
                    Swal.fire({
                        icon: "warning",
                        title: "Sin ubicación",
                        text: "Este contenedor no tiene coordenadas",
                    });
                    return;
                }

                let latLlegada = parseFloat(info.latitud);
                let lngLlegada = parseFloat(info.longitud);

                if (directionsRenderer[key]) {
                    const isVisible = directionsRenderer[key].getMap();

                    directionsRenderer[key].setMap(isVisible ? null : map);

                    btn.textContent = isVisible ? "📍 Mostrar ruta" : "❌ Ocultar ruta";

                    if (infoSpan) {
                        infoSpan.style.display = isVisible ? "none" : "block";
                    }

                    return;
                }

                btn.textContent = "Calculando...";

                directionsRenderer[key] = new google.maps.DirectionsRenderer({
                    map: map,
                });

                const request = {
                    origin: origin,
                    destination: {
                        lat: latLlegada,
                        lng: lngLlegada,
                    },
                    travelMode: google.maps.TravelMode.DRIVING,
                };

                directionsService.route(request, (result, status) => {
                    if (status === "OK") {
                        directionsRenderer[key].setDirections(result);

                        btn.textContent = "❌ Ocultar ruta";

                        const leg = result.routes[0].legs[0];

                        if (infoSpan) {
                            infoSpan.style.display = "block";
                            infoSpan.innerHTML = `
                        🚗 <strong>${leg.distance.text}</strong><br>
                        ⏱ <strong>${leg.duration.text}</strong>
                    `;
                        }
                    } else {
                        btn.textContent = "📍 Mostrar ruta";

                        Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "No se pudo calcular la ruta",
                        });
                    }
                });
            }
        });

        function actualizarUbicacionReal(coordenadaData) {
            fetch('/coordenadas/rastrear/savehistori', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    },
                    body: JSON.stringify(coordenadaData),
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data.success) {
                        console.log('Coordenada guardada:', data.data);
                    } else {
                        console.warn('Error al guardar coordenada', data);
                    }
                })
                .catch((error) => {
                    console.error('Error en la solicitud:', error);
                });
        }
    </script>
    <script
        src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googleMapsApi.apikey') }}&libraries=geometry&callback=initMap"
        async defer></script>
</body>

</html>
