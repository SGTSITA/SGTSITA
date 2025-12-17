<!DOCTYPE html>
<html>
    <head>
        <title>Comparación de Ubicaciones</title>
        <meta charset="utf-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        <style>
            html,
            body {
                height: 100%;
                margin: 0;
                padding: 0;
            }

            #mapaComparacion {
                height: 93vh; /* Ocupa toda la altura visible del navegador */
                width: 100%;
                margin: 0;
                border: none;
                border-radius: 0;
            }
            #info table {
                width: 100%;
                border-collapse: collapse;
                box-shadow: 0 0 10px rgba(0, 0, 0, 0.05);
                font-family: 'Segoe UI', sans-serif;
                font-size: 14px; /* Más pequeña */
            }

            #info th,
            #info td {
                border: 1px solid #dee2e6;
                padding: 6px 8px; /* Menos padding = menos altura */
                text-align: center;
                line-height: 1.2; /* Más compacto verticalmente */
            }

            #info thead {
                background-color: #0d6efd;
                color: white;
            }

            #info tbody td span {
                font-weight: 500;
            }

            #info tr:nth-child(even) {
                background-color: #f8f9fa;
            }
        </style>
    </head>
    <body>
        <div id="info" class="container mt-3">
            <div class="table-responsive">
                <table class="table table-bordered align-middle text-center shadow-sm">
                    <thead class="table-primary">
                        <tr>
                            <th>Rastreo</th>
                            <th>Origen</th>
                            <th>Destino</th>
                            <th>Distancia aprox.</th>
                            <th>Tiempo llegada</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span id="contenedorSpan" class="fw-semibold"></span></td>
                            <td><span id="esperadaSpan"></span></td>
                            <td><span id="gpsSpan"></span></td>
                            <td><span id="distanciaSpan">km</span></td>
                            <td><span id="TiempoLLegadaSpan">00 hrs - 00 min : 00 seg</span></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div id="mapaComparacion"></div>

        <!-- Leaflet JS -->

        <script>
            const coordenadas = @json($waypoints);
            const contenedor = @json($contenedor);

            document.getElementById('contenedorSpan').innerHTML = contenedor;
            const waypoints = coordenadas.slice(1, -1).map((c) => ({
                location: { lat: parseFloat(c.latitud), lng: parseFloat(c.longitud) },
                stopover: false,
            }));
            let map;
            let markers = [];
            function googleMapsReady() {
                initMap();
            }

            function initMap() {
                const map = new google.maps.Map(document.getElementById('mapaComparacion'), {
                    zoom: 12,
                    center: waypoints.length > 0 ? waypoints[0].location : { lat: 0, lng: 0 },
                });

                const directionsService = new google.maps.DirectionsService();
                const directionsRenderer = new google.maps.DirectionsRenderer({
                    map: map,
                    suppressMarkers: false,
                });

                if (coordenadas.length < 2) return;

                directionsService.route(
                    {
                        origin: { lat: parseFloat(coordenadas[0].latitud), lng: parseFloat(coordenadas[0].longitud) },
                        destination: {
                            lat: parseFloat(coordenadas[coordenadas.length - 1].latitud),
                            lng: parseFloat(coordenadas[coordenadas.length - 1].longitud),
                        },
                        waypoints: waypoints,
                        travelMode: google.maps.TravelMode.DRIVING,
                        optimizeWaypoints: false,
                    },
                    (result, status) => {
                        if (status === 'OK') {
                            directionsRenderer.setDirections(result);

                            let totalDistancia = 0;
                            let totalDuracion = 0;

                            const route = result.routes[0];
                            route.legs.forEach((leg) => {
                                totalDistancia += leg.distance.value; // metros
                                totalDuracion += leg.duration.value; // segundos
                            });

                            const kms = (totalDistancia / 1000).toFixed(2);
                            const horas = Math.floor(totalDuracion / 3600);
                            const minutos = Math.floor((totalDuracion % 3600) / 60);

                            console.log(`Distancia total: ${kms} km`);
                            console.log(`Tiempo estimado: ${horas}h ${minutos}m`);

                            document.getElementById('distanciaSpan').innerHTML = `${kms} km`;

                            document.getElementById('TiempoLLegadaSpan').innerHTML = `${horas}h ${minutos}m`;

                            //unicaciones en texto para espan

                            const leg = route.legs[0];
                            console.log('Inicio:', leg.start_address);
                            console.log('Fin:', leg.end_address);

                            document.getElementById('esperadaSpan').innerHTML = leg.start_address;
                            document.getElementById('gpsSpan').innerHTML = leg.end_address;
                        } else {
                            console.error('Error en DirectionsService:', status);
                        }
                    },
                );
            }

            // Espera que cargue la API con callback
            window.initMap = initMap;

            function getAddressFromLatLng(lat, lng) {
                const geocoder = new google.maps.Geocoder();

                return new Promise((resolve, reject) => {
                    geocoder.geocode({ location: { lat, lng } }, (results, status) => {
                        if (status === 'OK') {
                            if (results[0]) {
                                resolve(results[0].formatted_address);
                            } else {
                                reject('No se encontró dirección para estas coordenadas');
                            }
                        } else {
                            reject('Error de geocodificación: ' + status);
                        }
                    });
                });
            }
        </script>

        <script
            src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAtAO2AZBgzC7QaBxnMnPoa-DAq8vaEvUc"
            async
            defer
            onload="googleMapsReady()"
        ></script>
    </body>
</html>
