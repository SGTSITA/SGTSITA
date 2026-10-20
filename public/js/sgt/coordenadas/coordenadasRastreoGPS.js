let map;
let markers = [];
function googleMapsReady() {
    initMap();
}
function initMap() {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 0, lng: 0 },
        zoom: 2,
    });

    const marker = new google.maps.Marker({
        position: { lat: 0, lng: 0 },
        map: map,
    });
}

const catalogoBusqueda = [];
let contenedoresDisponiblesAll = [];
let mapaAjustado = false;

let detalleConvoys;
let contenedoresDisponibles = [];
function cargarConvoysEnSelect(convoys) {
    const select = document.getElementById('convoys');

    // Limpiar opciones actuales excepto la primera
    select.innerHTML = '<option value="">Seleccione un convoy</option>';

    convoys.forEach((convoy) => {
        const option = document.createElement('option');
        option.value = convoy.id;
        option.textContent = `${convoy.no_conboy} - ${convoy.nombre}`;
        select.appendChild(option);
    });
}
function cargarEquiposEnSelect(dataequipos) {
    const select = document.getElementById('Equipo');

    // Limpiar opciones actuales excepto la primera
    select.innerHTML = '<option value="">Seleccione un equipo</option>';

    dataequipos.forEach((equipo) => {
        const option = document.createElement('option');
        option.value = `${equipo.id_equipo}|${equipo.imei}|${equipo.id}|${equipo.tipoGps}`;
        const textoPlaca = equipo.placas?.trim() ? equipo.placas : 'SIN PLACA';
        option.textContent = `${equipo.id_equipo} - ${equipo.marca}- ${equipo.tipo}- ${textoPlaca}`;
        select.appendChild(option);
    });
}
function cargarinicial() {
    fetch(`/coordenadas/contenedor/searchEquGps?`)
        .then((response) => response.json())
        .then((data) => {
            contenedoresDisponibles = data.datos;
            // cargarConvoysEnSelect( data.conboys);
            // cargarEquiposEnSelect( data.equipos);
            detalleConvoys = data.dataConten;

            contenedoresDisponiblesAll = data.dataContenAll;

            // Convoys detalle
            data.conboys.forEach((c) => {
                catalogoBusqueda.push({
                    tipo: 'Convoy',
                    label: c.no_conboy + ' ' + c.nombre,
                    value: c.no_conboy,
                    id: c.id,
                });
            });
            // Convoys detalle
            // detalleConvoys.forEach(c => {
            //   catalogoBusqueda.push({
            //     tipo: 'Convoy',
            //     label: c.no_conboy,
            //     value: c.num_contenedor + "|" + c.imei + "|" + c.id_contenedor+"|"+c.tipoGps,
            //     id: c.conboy_id,
            //   });
            // });

            // Contenedores (desde convoysDetalle)
            contenedoresDisponibles.forEach((cd) => {
                catalogoBusqueda.push({
                    tipo: 'Contenedor',
                    label: cd.contenedor,
                    value: cd.contenedor + '|' + cd.imei + '|' + cd.id_contenedor + '|' + cd.tipoGps,
                    id: cd.id_contenedor,
                });
            });

            // Equipos (si tienes un array separado)
            data.equipos.forEach((eq) => {
                const textoPlaca = eq.placas?.trim() ? eq.placas : '';
                catalogoBusqueda.push({
                    tipo: 'Equipo',

                    label: `${eq.id_equipo} - ${eq.marca} - ${eq.tipo} - ${textoPlaca}`,
                    value: `${eq.id_equipo}|${eq.imei}|${eq.id}|${eq.tipoGps}`,
                    idConvoy: null,
                });
            });
        })
        .catch((error) => {
            console.error('Error al traer coordenadas:', error);
        });
}

function obtenerImeisPorConvoyId(convoyId) {
    return detalleConvoys
        .filter((item) => item.conboy_id == convoyId && item.imei && item.id_contenedor)
        .map((item) => item.num_contenedor + '|' + item.imei + '|' + item.id_contenedor + '|' + item.tipoGps);
}

function obtenerTabActivo() {
    const tabActivo = document.querySelector('#filtroTabs .nav-link.active');
    return tabActivo ? tabActivo.getAttribute('data-bs-target') : null;
}

const input = document.getElementById('buscadorGeneral');
const resultados = document.getElementById('resultadosBusqueda');
const chipContainer = document.getElementById('chipsBusqueda');

let filtroActivo = null;

function validarTipo(items) {
    let tabx = items.tipo;
    let labelMuestra = items.label;
    if ((items.length = 0)) {
        return;
    }
    ItemsSelects.length = 0;
    ItemsSelects = items.value;
    if (tabx === 'Convoy') {
        ItemsSelects = obtenerImeisPorConvoyId(items.id);
    }
    mapaAjustado = false;

    actualizarUbicacion(ItemsSelects, tabx, labelMuestra);
    document.getElementById('btnDetener').style.display = 'inline-block';

    if (intervalId) clearInterval(intervalId);

    intervalId = setInterval(() => {
        actualizarUbicacion(ItemsSelects, tabx, labelMuestra);
    }, 5000);
}
let intervalId = null;
let idConvoyOContenedor = 0;
input.addEventListener('input', function () {
    const query = this.value.trim().toLowerCase();
    resultados.innerHTML = '';
    chipContainer.innerHTML = '';
    filtroActivo = null;

    if (query.length < 2) {
        detener();
        limpiarMarcadores();
        return;
    }

    const coincidencias = catalogoBusqueda.filter((item) => item.label.toLowerCase().includes(query));

    if (coincidencias.length === 0) {
        const div = document.createElement('div');
        div.classList.add('dropdown-item', 'text-muted');
        div.textContent = 'Sin resultados';
        resultados.appendChild(div);
        return;
    }

    // Mostrar chips por tipo
    const tiposUnicos = [...new Set(coincidencias.map((item) => item.tipo))];
    tiposUnicos.forEach((tipo) => {
        const chip = document.createElement('button');
        chip.className = 'btn btn-outline-secondary btn-sm rounded-pill me-2 mb-1';
        chip.textContent = tipo;
        chip.onclick = () => {
            filtroActivo = tipo;
            document.getElementById('tituloSeguimiento').textContent = 'Seguimiento ' + tipo;
            document.querySelectorAll('#chipsBusqueda .btn').forEach((btn) => btn.classList.remove('active'));
            chip.classList.add('active');
            mostrarResultadosFiltrados(query);
        };
        chipContainer.appendChild(chip);
    });

    mostrarResultadosFiltrados(query);
});

function mostrarResultadosFiltrados(query) {
    resultados.innerHTML = '';
    const sugerencias = catalogoBusqueda
        .filter((item) => item.label.toLowerCase().includes(query) && (!filtroActivo || item.tipo === filtroActivo))
        .slice(0, 10);

    sugerencias.forEach((item) => {
        const div = document.createElement('div');
        div.classList.add('dropdown-item');
        div.textContent = `${item.label}`;
        div.onclick = () => {
            document.getElementById('tituloSeguimiento').textContent = 'Seguimiento ' + item.tipo;
            document.querySelectorAll('#chipsBusqueda .btn').forEach((btn) => {
                if (btn.textContent.trim() === item.tipo) {
                    btn.classList.add('active');
                } else {
                    btn.classList.remove('active');
                }
            });

            input.value = item.label;
            document.getElementById('resultadosBusqueda').innerHTML = '';
            //input.dispatchEvent(new Event('input'));
            validarTipo(item);
        };
        resultados.appendChild(div);
    });
}

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
function actualizarUbicacion(imeis, t, labelMuestra) {
    let tipo = '';

    let responseOk = false;
    fetch('/coordenadas/ubicacion-vehiculo', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        },
        body: JSON.stringify({ imeis: imeis }),
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

                    //   let datosGeocerca = convoysAll.find(c => c.no_conboy === extraertipoC)

                    latlocal = parseFloat(item.ubicacion.lat);
                    lnglocal = parseFloat(item.ubicacion.lng);
                    idConvoyOContenedor = item.id_contenendor;

                    // if(datosGeocerca ) {

                    // actualizarMapa(latlocal, lnglocal,datosGeocerca)
                    // }
                    if (markers[item.ubicacion.imei]) {
                        markers[item.ubicacion.imei].setPosition({ lat: latlocal, lng: lnglocal });
                    } else {
                        if (latlocal && lnglocal) {
                            // let esMostrarPrimero =  1
                            // if(esMostrarPrimero){
                            const newMarker = new google.maps.Marker({
                                position: { lat: latlocal, lng: lnglocal },
                                map: map,
                            });
                            let contentC = '';
                            if (t === 'Equipo') {
                                // `${eq.id_equipo } - ${eq.marca}- ${eq.tipo}- ${textoPlaca}`,
                                const equipo = labelMuestra.split(' - ').map((part) => part.trim());

                                let marcaLocal = equipo[1];
                                let placaLocal = equipo[3];
                                contentC = `
            <div style="
                    background-color: #e3f2fd;
                    padding: 5px;
                    border-radius: 8px;
                    font-family: Arial, sans-serif;
                    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                    max-width: 240px;
                  ">
              <div style="font-weight: bold; font-size: 17px; margin-bottom: 6px;">
               
              </div>
              <div style="font-size: 17px; line-height: 1.5;">
                <strong>Equipo:</strong> ${item.EquipoBD}<br>
                <strong>Marca:</strong> ${marcaLocal}<br>
                <strong>Placas:</strong> ${placaLocal}<br>
             
              </div>
            </div>
          `;
                            } else {
                                contentC = `
            <div style="
                    background-color: #e3f2fd;
                    padding: 5px;
                    border-radius: 8px;
                    font-family: Arial, sans-serif;
                    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                    max-width: 240px;
                  ">
              <div style="font-weight: bold; font-size: 17px; margin-bottom: 6px;">
               
              </div>
              <div style="font-size: 17px; line-height: 1.5;">
                <strong>Equipo:</strong> ${item.EquipoBD}<br>
                <strong>Contenedor:</strong> ${item.contenedor}
              </div>
            </div>
          `;
                            }

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
                                const contenedor = item.contenedor;
                                let info = contenedoresDisponibles.find((d) => d.contenedor === contenedor);
                                if (!info) {
                                    if (t.includes('convoy')) {
                                        info = contenedoresDisponiblesAll.find((d) => d.contenedor === contenedor);
                                    } else if (t === 'Equipo') {
                                        const ahora = new Date();
                                        info = contenedoresDisponibles.find((d) => {
                                            const inicio = new Date(d.fecha_inicio);
                                            const fin = new Date(d.fecha_fin);

                                            return ahora >= inicio && ahora <= fin;
                                        });
                                        console.log(info);
                                        if (!info) {
                                            alert('Información del viaje no encontrada.');
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
                    <p><strong>Fecha Fin:</strong> ${info.fecha_fin}</p>
                    <p><strong>IMEI:</strong> ${info.imei}</p>
                   
                                   ${extraInfo}
                  </div>
                `;

                                document.getElementById('contenidoModalViaje').innerHTML = contenido;

                                // Mostrar el modal con Bootstrap 5
                                const modal = new bootstrap.Modal(document.getElementById('modalInfoViaje'));
                                modal.show();
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
                                Object.values(markers).forEach((marker) => bounds.extend(marker.getPosition()));
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

// Para detener la actualización con un botón
document.getElementById('btnDetener').addEventListener('click', function () {
    detener();
});

function detener() {
    if (intervalId) {
        clearInterval(intervalId);
        intervalId = null;
        document.getElementById('btnDetener').style.display = 'none';
    }
}

// Función para limpiar marcadores
function limpiarMarcadores() {
    markers.forEach((marker) => marker.setMap(null));
    markers = [];
}

document.addEventListener('DOMContentLoaded', function () {
    cargarinicial();
});

function limpiarFiltros() {
    const modal = document.getElementById('filtroModal');
    const inputs = modal.querySelectorAll('input, select, textarea');

    inputs.forEach((element) => {
        if (element.tagName === 'SELECT') {
            element.selectedIndex = 0;
        } else {
            element.value = '';
        }
    });

    seleccionados.length = 0;
    ItemsSelects.length = 0;
    const div = document.getElementById('contenedores-seleccionados');
    div.innerHTML = '';

    document.getElementById('ItemsSelects').value = '';
    limpiarMarcadores();
}

const seleccionados = [];
let ItemsSelects = [];

function mostrarSugerencias() {
    const input = document.getElementById('contenedor-input');
    const filtro = input.value.trim().toUpperCase();
    const sugerenciasDiv = document.getElementById('sugerencias');
    sugerenciasDiv.innerHTML = '';

    if (filtro.length === 0) {
        sugerenciasDiv.style.display = 'none';
        return;
    }

    const filtrados = contenedoresDisponibles.filter(
        (c) => (c.contenedor || '').toUpperCase().includes(filtro) && !seleccionados.includes(c.contenedor),
    );

    filtrados.forEach((c) => {
        const item = document.createElement('div');
        item.textContent = c.contenedor;
        item.style.padding = '5px';
        item.style.cursor = 'pointer';
        item.onclick = () => seleccionarContenedor(c.contenedor);
        sugerenciasDiv.appendChild(item);
    });

    sugerenciasDiv.style.display = filtrados.length ? 'block' : 'none';
}

function seleccionarContenedor(valor) {
    seleccionados.push(valor);
    const contenedorData = contenedoresDisponibles.find((c) => c.contenedor === valor);

    ItemsSelects.push(
        valor + '|' + contenedorData.imei + '|' + contenedorData.id_contenedor + '|' + contenedorData.tipoGps,
    );
    document.getElementById('contenedor-input').value = '';
    document.getElementById('sugerencias').style.display = 'none';
    actualizarVista();
}

function agregarContenedor() {
    const input = document.getElementById('contenedor-input');
    const valor = input.value.trim().toUpperCase();
    if (valor && contenedoresDisponibles.includes(valor) && !seleccionados.includes(valor)) {
        seleccionados.push(valor);

        input.value = '';
        actualizarVista();
    }
}

function eliminarContenedor(idx) {
    seleccionados.splice(idx, 1);
    ItemsSelects.splice(idx, 1);
    actualizarVista();
}

function actualizarVista() {
    const div = document.getElementById('contenedores-seleccionados');
    div.innerHTML = '';

    seleccionados.forEach((cont, i) => {
        div.innerHTML += `
                 <span class="badge bg-secondary me-1">
            ${cont}
            <button type="button" 
                onclick="eliminarContenedor(${i})" 
                style="background:none; border:none; color:red; margin-left:5px; font-weight:bold;" 
                title="Eliminar">
                &times;
            </button>
        </span>
                
            `;
    });

    document.getElementById('contenedores').value = seleccionados.join(';');
    document.getElementById('ItemsSelects').value = ItemsSelects.join(';');
}

// document.getElementById('ubicacion-toggle').addEventListener('change', function() {
//     const ubicacionTexto = document.getElementById('ubicacion-texto');

//     if (this.checked) {
//         ubicacionTexto.textContent = 'Última ubicación';
//     } else {
//         ubicacionTexto.textContent = 'Todas las ubicaciones';
//     }
// });
