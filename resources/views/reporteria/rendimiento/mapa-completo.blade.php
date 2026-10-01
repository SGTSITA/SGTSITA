@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="card card-body">
        <div class="d-flex justify-content-between align-items-center pb-2 border-bottom mb-3">
            <div>
                <h4 class="mb-1" style="font-weight: 700; color: #344767;"><i class="fas fa-map-marked-alt me-2 text-primary"></i>Ruta del Viaje</h4>
                <div class="d-flex flex-wrap gap-2 align-items-center mt-1">
                    <span class="badge bg-gradient-info" style="font-size: 11px; padding: 4px 8px;">Contenedor: {{ $rowData['contenedor'] }}</span>
                    <span class="badge bg-gradient-secondary" style="font-size: 11px; padding: 4px 8px;">Ruta: {{ $rowData['fecha_inicio'] }} al {{ $rowData['fecha_fin'] }}</span>
                    <span class="badge bg-gradient-dark" style="font-size: 11px; padding: 4px 8px;">Operador: {{ $rowData['operador'] }}</span>
                    <span id="badgeKmRecorridosGoogle" class="badge bg-gradient-success" style="font-size: 11px; padding: 4px 8px;"><i class="fas fa-road me-1"></i>KMs por Carretera: Calculando...</span>
                </div>
            </div>
            <div class="d-flex align-items-center">
                <label class="me-2 mb-0 font-weight-bold" style="font-size: 13px; white-space: nowrap;"><i class="fas fa-route me-1"></i>Tramo:</label>
                <select id="selectSegmentoMapa" class="form-select form-select-sm me-3" style="width: auto; min-width: 250px; padding: 4px 8px; font-size: 12px; border-radius: 4px;">
                    <option value="todos">Ambos (Ida Azul / Vuelta Roja)</option>
                    <option value="ida">Ida (Ruta Azul)</option>
                    <option value="regreso">Vuelta (Ruta Roja)</option>
                </select>
                <button type="button" id="btnGuardarHistorial" class="btn btn-sm bg-gradient-success mb-0 me-2"><i class="fas fa-save me-1"></i>Guardar Historial</button>
                <button type="button" class="btn btn-sm bg-gradient-secondary mb-0" onclick="window.close()"><i class="fas fa-times me-1"></i>Cerrar Pantalla</button>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-lg-9 col-12">
                <div id="mapaRutaConsumo" style="height: 700px; width: 100%; border-radius: 8px; border: 1px solid #dee2e6; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);"></div>
            </div>
            <div class="col-lg-3 col-12 border-start bg-light" style="max-height: 700px; overflow-y: auto; border-radius: 8px;">
                <div class="p-3">
                    <h6 class="mb-3 text-uppercase font-weight-bold" style="font-size: 12px; letter-spacing: 0.5px; color: #495057;">
                        <i class="fas fa-history me-1"></i> Línea de Tiempo del Viaje
                    </h6>
                    <div id="timelineRutaConsumo" style="font-size: 13px;">
                        <p class="text-muted text-center my-4">No hay puntos de rastreo registrados.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('custom-javascript')
    <script>
        const URL_GUARDAR_COORDENADAS = "{{ route('reporteria.consumo-unidades.guardar-coordenadas') }}";
        const rowData = @json($rowData);
    </script>

    <script src="https://maps.googleapis.com/maps/api/js?key={{ config('services.googleMapsApi.apikey') }}&libraries=geometry"></script>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            let googleMapInstance = null;
            let mapMarkers = [];
            let mapPathPolyline = null;
            let directionsRenderers = [];
            let currentCalculatedKm = 0;

            // Initialize rendering
            renderizarMapaRuta(rowData, 'todos');

            if (rowData.origen_km === 'Captura Manual' && !rowData.es_historico_guardado && rowData.coordenadas_ruta && rowData.coordenadas_ruta.length > 0) {
                setTimeout(() => {
                    Swal.fire({
                        title: 'Sincronizar Coordenadas',
                        text: 'Este viaje tiene kilómetros registrados manualmente (' + rowData.km_recorridos + ' km) pero no tiene el historial de coordenadas guardado en la base de datos. ¿Deseas guardar las coordenadas y sincronizar con los KMs del mapa?',
                        icon: 'info',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, sincronizar y guardar',
                        cancelButtonText: 'No por ahora',
                        confirmButtonColor: '#2dce89'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            document.getElementById('btnGuardarHistorial').click();
                        }
                    });
                }, 800);
            }

            document.getElementById('selectSegmentoMapa').addEventListener('change', function() {
                renderizarMapaRuta(rowData, this.value);
            });

            document.getElementById('btnGuardarHistorial').addEventListener('click', async function() {
                if (!rowData || !rowData.coordenadas_ruta || rowData.coordenadas_ruta.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Sin datos',
                        text: 'No hay coordenadas en el historial de este viaje para guardar.'
                    });
                    return;
                }

                Swal.fire({
                    title: '¿Guardar historial?',
                    text: 'Se registrarán de forma permanente estas coordenadas y los kilómetros calculados en el reporte.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, guardar',
                    cancelButtonText: 'Cancelar',
                    showLoaderOnConfirm: true,
                    preConfirm: async () => {
                        try {
                            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
                            const response = await fetch(URL_GUARDAR_COORDENADAS, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    asignacion_id: rowData.asignacion_id,
                                    coordenadas: rowData.coordenadas_ruta,
                                    km_recorridos: currentCalculatedKm
                                })
                            });
                            const data = await response.json();
                            if (!response.ok || !data.success) {
                                throw new Error(data.message || 'Error al guardar coordenadas.');
                            }
                            return data;
                        } catch (error) {
                            Swal.showValidationMessage(`Request failed: ${error}`);
                        }
                    },
                    allowOutsideClick: () => !Swal.isLoading()
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: 'success',
                            title: '¡Guardado!',
                            text: 'El historial de coordenadas ha sido guardado exitosamente.'
                        });
                    }
                });
            });

            function renderizarMapaRuta(rowData, tramo) {
                if (mapMarkers) {
                    mapMarkers.forEach(m => m.setMap(null));
                }
                mapMarkers = [];
                if (mapPathPolyline) {
                    mapPathPolyline.setMap(null);
                    mapPathPolyline = null;
                }
                if (directionsRenderers) {
                    directionsRenderers.forEach(r => r.setMap(null));
                }
                directionsRenderers = [];

                const mapDiv = document.getElementById('mapaRutaConsumo');
                if (typeof google === 'undefined' || typeof google.maps === 'undefined') {
                    mapDiv.innerHTML = '<div class="alert alert-danger m-3">Google Maps API no está cargado correctamente.</div>';
                    return;
                }

                const defaultCenter = { lat: 19.4326, lng: -99.1332 };
                const mapOptions = {
                    zoom: 8,
                    center: defaultCenter,
                    mapTypeId: google.maps.MapTypeId.ROADMAP
                };

                googleMapInstance = new google.maps.Map(mapDiv, mapOptions);
                const bounds = new google.maps.LatLngBounds();
                const infoWindow = new google.maps.InfoWindow();

                const timelineDiv = document.getElementById('timelineRutaConsumo');
                timelineDiv.innerHTML = '';
                let timelineItems = [];

                function getDistanceKm(lat1, lon1, lat2, lon2) {
                    const R = 6371;
                    const dLat = (lat2 - lat1) * Math.PI / 180;
                    const dLon = (lon2 - lon1) * Math.PI / 180;
                    const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                              Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                              Math.sin(dLon/2) * Math.sin(dLon/2);
                    return R * 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                }

                let accumulatedDistances = [];
                let totalAccumulatedDist = 0;
                let deliveryIdx = -1;

                if (rowData.coordenadas_ruta && rowData.coordenadas_ruta.length > 0) {
                    accumulatedDistances.push(0);
                    for (let i = 1; i < rowData.coordenadas_ruta.length; i++) {
                        const prev = rowData.coordenadas_ruta[i-1];
                        const curr = rowData.coordenadas_ruta[i];
                        totalAccumulatedDist += getDistanceKm(
                            parseFloat(prev.latitud), parseFloat(prev.longitud),
                            parseFloat(curr.latitud), parseFloat(curr.longitud)
                        );
                        accumulatedDistances.push(totalAccumulatedDist);
                    }

                    if (rowData.destino_lat && rowData.destino_lng) {
                        const destLat = parseFloat(rowData.destino_lat);
                        const destLng = parseFloat(rowData.destino_lng);
                        let minCotiDist = Infinity;
                        rowData.coordenadas_ruta.forEach((coord, idx) => {
                            const dist = getDistanceKm(destLat, destLng, parseFloat(coord.latitud), parseFloat(coord.longitud));
                            if (dist < minCotiDist) {
                                minCotiDist = dist;
                                deliveryIdx = idx;
                            }
                        });
                    }
                }

                let fullRuta = rowData.coordenadas_ruta || [];
                let filteredRuta = [];
                let startIdx = 0;
                let endIdx = fullRuta.length - 1;

                if (deliveryIdx !== -1) {
                    if (tramo === 'ida') {
                        endIdx = deliveryIdx;
                    } else if (tramo === 'regreso') {
                        startIdx = deliveryIdx;
                    }
                }
                filteredRuta = fullRuta.slice(startIdx, endIdx + 1);

                let originLatLng = null;
                let destLatLng = null;

                if (filteredRuta.length > 0) {
                    originLatLng = new google.maps.LatLng(parseFloat(filteredRuta[0].latitud), parseFloat(filteredRuta[0].longitud));
                    destLatLng = new google.maps.LatLng(parseFloat(filteredRuta[filteredRuta.length - 1].latitud), parseFloat(filteredRuta[filteredRuta.length - 1].longitud));
                }

                // Draw start marker
                if (originLatLng) {
                    let startTitle = tramo === 'regreso' ? "Punto de Entrega (Inicio Regreso)" : "Inicio del viaje (Origen)";
                    let startTimelineTitle = tramo === 'regreso' ? "Punto de Entrega (Destino)" : "Inicio (Origen)";
                    const marker = new google.maps.Marker({
                        position: originLatLng,
                        map: googleMapInstance,
                        title: startTitle,
                        icon: tramo === 'regreso' ? 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png' : 'https://maps.google.com/mapfiles/ms/icons/green-dot.png'
                    });
                    mapMarkers.push(marker);
                    bounds.extend(originLatLng);

                    const startInfoContent = `
                        <div style="font-family: sans-serif; font-size: 13px; line-height: 1.4; padding: 5px;">
                            <h6 style="margin: 0 0 5px 0; color: #198754; font-weight: bold;"><i class="fas fa-play-circle me-1"></i>${startTitle}</h6>
                            <strong>Fecha/Hora:</strong> ${rowData.fecha_inicio || 'S/N'}<br>
                            <strong>Lat/Lng:</strong> ${originLatLng.lat().toFixed(6)}, ${originLatLng.lng().toFixed(6)}
                        </div>
                    `;
                    marker.addListener('click', () => {
                        infoWindow.setContent(startInfoContent);
                        infoWindow.open(googleMapInstance, marker);
                    });

                    timelineItems.push({
                        title: startTimelineTitle,
                        time: rowData.fecha_inicio || 'S/N',
                        lat: originLatLng.lat(),
                        lng: originLatLng.lng(),
                        badgeClass: tramo === 'regreso' ? 'bg-primary' : 'bg-success',
                        iconClass: tramo === 'regreso' ? 'fa-map-marker-alt' : 'fa-play'
                    });
                }

                // Find closest point to diesel loading in this filtered subset
                let dieselInsertIdx = -1;
                if (rowData.diesel_lat && rowData.diesel_lng) {
                    const dLat = parseFloat(rowData.diesel_lat);
                    const dLng = parseFloat(rowData.diesel_lng);
                    let minTrackDist = Infinity;
                    filteredRuta.forEach((coord, idx) => {
                        const dist = getDistanceKm(dLat, dLng, parseFloat(coord.latitud), parseFloat(coord.longitud));
                        if (dist < minTrackDist) {
                            minTrackDist = dist;
                            dieselInsertIdx = idx;
                        }
                    });
                }

                // Render intermediate tracking markers
                let lastMarkerPos = null;
                filteredRuta.forEach((coord, idx) => {
                    const globalIdx = startIdx + idx;
                    const pos = { lat: parseFloat(coord.latitud), lng: parseFloat(coord.longitud) };
                    bounds.extend(pos);

                    let labelType = "Ida";
                    let labelClass = "badge bg-info text-white ms-2";
                    if (deliveryIdx !== -1) {
                        if (globalIdx < deliveryIdx) {
                            labelType = "Ida";
                            labelClass = "badge bg-info text-white ms-2";
                        } else if (globalIdx === deliveryIdx) {
                            labelType = "Entrega";
                            labelClass = "badge bg-dark text-white ms-2";
                        } else {
                            labelType = "Vuelta";
                            labelClass = "badge bg-secondary text-white ms-2";
                        }
                    }

                    // Downsample markers to avoid clustering (threshold: 4 km)
                    let shouldDrawMarker = idx === 0 || 
                                           idx === filteredRuta.length - 1 || 
                                           globalIdx === deliveryIdx ||
                                           globalIdx === (deliveryIdx - 1) ||
                                           globalIdx === (deliveryIdx + 1);
                    if (!shouldDrawMarker && lastMarkerPos) {
                        const dist = getDistanceKm(lastMarkerPos.lat, lastMarkerPos.lng, pos.lat, pos.lng);
                        if (dist >= 4.0) {
                            shouldDrawMarker = true;
                        }
                    }

                    if (shouldDrawMarker) {
                        lastMarkerPos = pos;
                        let markerTitle = `Punto de rastreo (${labelType})`;
                        let markerIcon = {
                            path: google.maps.SymbolPath.CIRCLE,
                            scale: 5,
                            fillColor: globalIdx === deliveryIdx ? "#343a40" : (globalIdx < deliveryIdx ? "#007bff" : "#6c757d"),
                            fillOpacity: 0.9,
                            strokeColor: "#ffffff",
                            strokeWeight: 1,
                        };

                        let dotMarker;
                        if (globalIdx === deliveryIdx && tramo === 'todos') {
                            dotMarker = new google.maps.Marker({
                                position: pos,
                                map: googleMapInstance,
                                title: "Punto de Entrega (Destino)",
                                icon: 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png'
                            });
                        } else {
                            dotMarker = new google.maps.Marker({
                                position: pos,
                                map: googleMapInstance,
                                title: `${markerTitle}: ${coord.registrado_en || ''} (A ${accumulatedDistances[globalIdx].toFixed(1)} km)`,
                                icon: markerIcon
                            });
                        }
                        mapMarkers.push(dotMarker);

                        const infoContent = `
                            <div style="font-family: sans-serif; font-size: 13px; line-height: 1.4; padding: 5px;">
                                <h6 style="margin: 0 0 5px 0; color: #007bff; font-weight: bold;"><i class="fas fa-map-marker-alt me-1"></i>Punto de Rastreo <span class="${labelClass}">${labelType}</span></h6>
                                <strong>Fecha/Hora:</strong> ${coord.registrado_en || 'S/N'}<br>
                                <strong>Distancia recorrida:</strong> ${accumulatedDistances[globalIdx].toFixed(1)} km<br>
                                <strong>Lat/Lng:</strong> ${parseFloat(coord.latitud).toFixed(5)}, ${parseFloat(coord.longitud).toFixed(5)}
                            </div>
                        `;
                        dotMarker.addListener('click', () => {
                            infoWindow.setContent(infoContent);
                            infoWindow.open(googleMapInstance, dotMarker);
                        });

                        timelineItems.push({
                            title: `Ruta (${accumulatedDistances[globalIdx].toFixed(1)} km) <span class="${labelClass}" style="font-size:10px;">${labelType}</span>`,
                            time: coord.registrado_en || 'S/N',
                            lat: pos.lat,
                            lng: pos.lng,
                            badgeClass: globalIdx === deliveryIdx ? 'bg-dark' : (globalIdx < deliveryIdx ? 'bg-info' : 'bg-secondary'),
                            iconClass: 'fa-map-pin'
                        });
                    }

                    // Diesel refueling event marker
                    if (idx === dieselInsertIdx && rowData.diesel_lat && rowData.diesel_lng) {
                        const dieselLatLng = new google.maps.LatLng(parseFloat(rowData.diesel_lat), parseFloat(rowData.diesel_lng));
                        const dieselMarker = new google.maps.Marker({
                            position: dieselLatLng,
                            map: googleMapInstance,
                            title: `Carga de Diésel (A ${accumulatedDistances[globalIdx].toFixed(1)} km del inicio)`,
                            icon: 'https://maps.google.com/mapfiles/ms/icons/orange-dot.png'
                        });
                        mapMarkers.push(dieselMarker);

                        const dieselInfoContent = `
                            <div style="font-family: sans-serif; font-size: 13px; line-height: 1.4; padding: 5px;">
                                <h6 style="margin: 0 0 5px 0; color: #fd7e14; font-weight: bold;"><i class="fas fa-gas-pump me-1"></i>Carga de Diésel <span class="${labelClass}">${labelType}</span></h6>
                                <strong>Aprox. Recorrido:</strong> ${accumulatedDistances[globalIdx].toFixed(1)} km<br>
                                <strong>Lat/Lng:</strong> ${dieselLatLng.lat().toFixed(5)}, ${dieselLatLng.lng().toFixed(5)}
                            </div>
                        `;
                        dieselMarker.addListener('click', () => {
                            infoWindow.setContent(dieselInfoContent);
                            infoWindow.open(googleMapInstance, dieselMarker);
                        });

                        timelineItems.push({
                            title: `Carga de Diésel (${accumulatedDistances[globalIdx].toFixed(1)} km) <span class="${labelClass}" style="font-size:10px;">${labelType}</span>`,
                            time: coord.registrado_en || 'S/N',
                            lat: dieselLatLng.lat(),
                            lng: dieselLatLng.lng(),
                            badgeClass: 'bg-warning text-dark',
                            iconClass: 'fa-gas-pump'
                        });
                    }
                });

                // Destination marker
                if (destLatLng) {
                    let destTitle = tramo === 'ida' ? "Punto de Entrega (Destino)" : "Fin del viaje (Retorno Lázaro)";
                    let destTimelineTitle = tramo === 'ida' ? "Punto de Entrega (Destino)" : "Fin (Retorno Lázaro)";
                    const marker = new google.maps.Marker({
                        position: destLatLng,
                        map: googleMapInstance,
                        title: destTitle,
                        icon: tramo === 'ida' ? 'https://maps.google.com/mapfiles/ms/icons/blue-dot.png' : 'https://maps.google.com/mapfiles/ms/icons/red-dot.png'
                    });
                    mapMarkers.push(marker);
                    bounds.extend(destLatLng);

                    let fechaFinDisplay = 'S/N';
                    if (tramo === 'ida' && deliveryIdx !== -1) {
                        fechaFinDisplay = fullRuta[deliveryIdx].registrado_en || 'S/N';
                    } else if (rowData.viaje_finalizado_lat && rowData.viaje_finalizado_lng) {
                        fechaFinDisplay = rowData.viaje_finalizado_fecha || 'S/N';
                    } else if (filteredRuta.length > 0) {
                        fechaFinDisplay = filteredRuta[filteredRuta.length - 1].registrado_en || 'S/N';
                    }

                    const destInfoContent = `
                        <div style="font-family: sans-serif; font-size: 13px; line-height: 1.4; padding: 5px;">
                            <h6 style="margin: 0 0 5px 0; color: #dc3545; font-weight: bold;"><i class="fas fa-flag-checkered me-1"></i>${destTitle}</h6>
                            <strong>Fecha/Hora:</strong> ${fechaFinDisplay}<br>
                            <strong>Lat/Lng:</strong> ${destLatLng.lat().toFixed(6)}, ${destLatLng.lng().toFixed(6)}
                        </div>
                    `;
                    marker.addListener('click', () => {
                        infoWindow.setContent(destInfoContent);
                        infoWindow.open(googleMapInstance, marker);
                    });

                    timelineItems.push({
                        title: destTimelineTitle,
                        time: fechaFinDisplay,
                        lat: destLatLng.lat(),
                        lng: destLatLng.lng(),
                        badgeClass: tramo === 'ida' ? 'bg-primary' : 'bg-danger',
                        iconClass: 'fa-flag-checkered'
                    });
                }

                let totalDistances = {
                    ida: 0,
                    regreso: 0
                };

                function updateDistanceBadge() {
                    let totalKm = 0;
                    if (tramo === 'todos') {
                        totalKm = totalDistances.ida + totalDistances.regreso;
                    } else if (tramo === 'ida') {
                        totalKm = totalDistances.ida;
                    } else if (tramo === 'regreso') {
                        totalKm = totalDistances.regreso;
                    }
                    currentCalculatedKm = totalKm;
                    document.getElementById('badgeKmRecorridosGoogle').innerHTML = `<i class="fas fa-road me-1"></i>KMs por Carretera: ${totalKm.toFixed(1)} km`;
                }

                // Draw path using Directions Service (Route by road)
                function trazarRutaCarretera(origin, dest, pts, color, type) {
                    let waypoints = [];
                    if (pts && pts.length > 2) {
                        let rawIntermediates = pts.slice(1, -1);
                        let step = 1;
                        if (rawIntermediates.length > 21) {
                            step = rawIntermediates.length / 21;
                        }
                        for (let i = 0; i < 21 && i * step < rawIntermediates.length; i++) {
                            let idx = Math.floor(i * step);
                            let pt = rawIntermediates[idx];
                            waypoints.push({
                                location: new google.maps.LatLng(parseFloat(pt.latitud), parseFloat(pt.longitud)),
                                stopover: false
                            });
                        }
                    }

                    const directionsService = new google.maps.DirectionsService();
                    const directionsRenderer = new google.maps.DirectionsRenderer({
                        map: googleMapInstance,
                        suppressMarkers: true,
                        polylineOptions: {
                            strokeColor: color,
                            strokeOpacity: 0.8,
                            strokeWeight: 5
                        }
                    });
                    directionsRenderers.push(directionsRenderer);

                    directionsService.route({
                        origin: origin,
                        destination: dest,
                        waypoints: waypoints,
                        optimizeWaypoints: false,
                        travelMode: google.maps.TravelMode.DRIVING
                    }, function(response, status) {
                        if (status === 'OK') {
                            directionsRenderer.setDirections(response);

                            let distMeters = 0;
                            response.routes[0].legs.forEach(leg => {
                                distMeters += leg.distance.value;
                            });
                            let distKm = distMeters / 1000;

                            if (type === 'ida') {
                                totalDistances.ida = distKm;
                            } else if (type === 'regreso') {
                                totalDistances.regreso = distKm;
                            }
                            updateDistanceBadge();
                        } else {
                            console.warn('Directions API failed (' + status + '). Fallback to polyline.');
                            trazarPolylineDirecta(origin, dest, pts, color);
                        }
                    });
                }

                function trazarPolylineDirecta(origin, dest, pts, color) {
                    const pathCoordinates = [];
                    if (origin) pathCoordinates.push(origin);
                    if (pts && pts.length > 0) {
                        pts.forEach(c => {
                            pathCoordinates.push({ lat: parseFloat(c.latitud), lng: parseFloat(c.longitud) });
                        });
                    }
                    if (dest) pathCoordinates.push(dest);

                    if (pathCoordinates.length > 0) {
                        const poly = new google.maps.Polyline({
                            path: pathCoordinates,
                            geodesic: true,
                            strokeColor: color,
                            strokeOpacity: 0.8,
                            strokeWeight: 4,
                            map: googleMapInstance
                        });
                    }
                }

                // Trigger road-based routing depending on segment selection
                if (tramo === 'todos' && deliveryIdx !== -1) {
                    let rutaIda = fullRuta.slice(0, deliveryIdx + 1);
                    let rutaRegreso = fullRuta.slice(deliveryIdx);
                    
                    let oIda = new google.maps.LatLng(parseFloat(rutaIda[0].latitud), parseFloat(rutaIda[0].longitud));
                    let dIda = new google.maps.LatLng(parseFloat(rutaIda[rutaIda.length - 1].latitud), parseFloat(rutaIda[rutaIda.length - 1].longitud));
                    
                    let oRegreso = new google.maps.LatLng(parseFloat(rutaRegreso[0].latitud), parseFloat(rutaRegreso[0].longitud));
                    let dRegreso = new google.maps.LatLng(parseFloat(rutaRegreso[rutaRegreso.length - 1].latitud), parseFloat(rutaRegreso[rutaRegreso.length - 1].longitud));

                    trazarRutaCarretera(oIda, dIda, rutaIda, '#007bff', 'ida'); // Blue for Ida
                    trazarRutaCarretera(oRegreso, dRegreso, rutaRegreso, '#dc3545', 'regreso'); // Red/Orange for Regreso
                } else {
                    if (originLatLng && destLatLng) {
                        trazarRutaCarretera(originLatLng, destLatLng, filteredRuta, tramo === 'ida' ? '#007bff' : '#dc3545', tramo);
                    }
                }

                // Render Timeline
                if (timelineItems.length > 0) {
                    let html = '<div class="list-group list-group-flush">';
                    timelineItems.forEach((item, index) => {
                        html += `
                            <a href="javascript:void(0);" class="list-group-item list-group-item-action py-2 lh-sm item-timeline-map" data-lat="${item.lat}" data-lng="${item.lng}" data-index="${index}" style="font-size: 11.5px;">
                                <div class="d-flex w-100 flex-column mb-1">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <strong class="mb-0 text-dark" style="font-size: 11.5px;"><span class="badge ${item.badgeClass} me-2"><i class="fas ${item.iconClass}"></i></span>${item.title}</strong>
                                        <small class="text-muted font-weight-bold" style="font-size: 10px;">${item.time}</small>
                                    </div>
                                </div>
                                <div class="text-muted ps-4" style="font-size: 10.5px; margin-top: 2px;">
                                    <i class="fas fa-map-marker-alt me-1"></i>Coord: <strong>${item.lat.toFixed(6)}, ${item.lng.toFixed(6)}</strong>
                                </div>
                            </a>
                        `;
                    });
                    html += '</div>';
                    timelineDiv.innerHTML = html;

                    document.querySelectorAll('.item-timeline-map').forEach(el => {
                        el.addEventListener('click', function() {
                            const lat = parseFloat(this.getAttribute('data-lat'));
                            const lng = parseFloat(this.getAttribute('data-lng'));
                            if (googleMapInstance) {
                                googleMapInstance.setCenter({ lat, lng });
                                googleMapInstance.setZoom(14);
                            }
                        });
                    });
                } else {
                    timelineDiv.innerHTML = '<p class="text-muted text-center my-4">No hay puntos de rastreo registrados.</p>';
                }

                if (!bounds.isEmpty()) {
                    googleMapInstance.fitBounds(bounds);
                } else {
                    googleMapInstance.setCenter(defaultCenter);
                    googleMapInstance.setZoom(6);
                }
            }
        });
    </script>
@endpush
