let markers = [];
let map;
let filtroModalUI;
window.initMap = function () {
    map = new google.maps.Map(document.getElementById('map'), {
        center: { lat: 0, lng: 0 },
        zoom: 2,
    });

    const marker = new google.maps.Marker({
        position: { lat: 0, lng: 0 },
        map: map,
    });
};

const camposUbicacion = [
    { key: 'toma_foto_patio', orden: 15 },
    { key: 'cargado_patio', orden: 14 },
    { key: 'descarga_patio', orden: 13 },
    { key: 'recepcion_doc_firmados', orden: 12 },
    { key: 'fin_descarga', orden: 11 },
    { key: 'inicio_descarga', orden: 10 },
    { key: 'en_destino', orden: 9 },
    { key: 'modulado_coordenada', orden: 8 },
    { key: 'modulado_tipo', orden: 7 },
    { key: 'fila_fiscal', orden: 6 },
    { key: 'cargado_contenedor', orden: 5 },
    { key: 'descarga_vacio', orden: 4 },
    { key: 'dentro_puerto', orden: 3 },
    { key: 'registro_puerto', orden: 2 },
    { key: 'tipo_flujo', orden: 1 },
];

// Mostrar el modal de Bootstrap al cargar la página
window.onload = function () {
    if (!map) {
        map = new google.maps.Map(document.getElementById('map'), {
            center: { lat: 0, lng: 0 },
            zoom: 2,
        });
    }

    filtroModalUI = new bootstrap.Modal(document.getElementById('filtroModal'));
    filtroModalUI.show();

    // Centrar mapa en ubicación actual
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function (position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;

                // Centrar el mapa
                map.setCenter({ lat: parseFloat(lat), lng: parseFloat(lng) });
                map.setZoom(12);

                // Crear marcador
                const marker = new google.maps.Marker({
                    position: { lat: lat, lng: lng },
                    map: map,
                });

                // Agregar popup (info window)
                const infoWindow = new google.maps.InfoWindow({
                    content: 'Buscar Contenedores',
                });

                infoWindow.open(map, marker);

                // Opcional: guardar en un arreglo global
                window.markers = window.markers || [];
                window.markers.push(marker);
            },
            function (error) {
                console.warn('No se pudo obtener la ubicación:', error.message);
            },
        );
    } else {
        console.warn('Geolocalización no soportada por este navegador.');
    }
};
let contenedoresDisponibles = [];

function cargarinicial() {
    const params = new URLSearchParams({
        idCliente: idCliente,
    });

    fetch(`/coordenadas/contenedor/search?${params.toString()}`)
        .then((response) => response.json())
        .then((data) => {
            contenedoresDisponibles = data.datos;
        })
        .catch((error) => {
            console.error('Error al traer coordenadas:', error);
        });
}

// Enviar filtros al backend
document.getElementById('formFiltros').addEventListener('submit', function (event) {
    event.preventDefault();

    const formData = new FormData(this);
    const queryParams = new URLSearchParams();

    formData.forEach((valor, clave) => {
        if (valor.trim() !== '') {
            queryParams.append(clave, valor);
        }
    });

    queryParams.append('idCliente', idCliente);
    const mostrarUltimaUbicacion = document.getElementById('ubicacion-toggle').checked;
    limpiarMarcadores(); // Limpia los anteriores antes de buscar nuevos

    fetch(`/coordenadas/contenedor/search?${queryParams.toString()}`)
        .then((response) => response.json())
        .then((data) => {
            const preguntas = data.preguntas;
            const datos = data.datos;
            const infoWindow = new google.maps.InfoWindow();
            let esPrimero = true;
            datos.forEach((contenedor) => {
                if (mostrarUltimaUbicacion) {
                    for (let i = 0; i < camposUbicacion.length; i++) {
                        const campo = camposUbicacion[i].key;
                        const valor = contenedor[campo];

                        if (valor) {
                            const coords = valor.split(',');
                            if (coords.length === 2) {
                                const lat = parseFloat(coords[0]);
                                const lng = parseFloat(coords[1]);

                                markers[contenedor[campo] + contenedor['contenedor']] = new google.maps.Marker({
                                    position: { lat: lat, lng: lng },
                                    map: map,
                                });
                                if (esPrimero) {
                                    map.setCenter({ lat: lat, lng: lng });
                                    map.setZoom(12);
                                    esPrimero = false;
                                }

                                markers[contenedor[campo] + contenedor['contenedor']].addListener('click', () => {
                                    infoWindow.setContent(
                                        `Última ubicación: ${campo.replace(/_/g, ' ')}<br>Contenedor: <strong>${contenedor.contenedor}</strong>`,
                                    );
                                    infoWindow.open(map, markers[contenedor[campo] + contenedor['contenedor']]);
                                });

                                window.markers = window.markers || [];
                                window.markers.push(markers[contenedor[campo] + contenedor['contenedor']]);
                                break;
                            } else {
                                console.log(`Formato incorrecto de coordenadas: ${valor}`);
                            }
                        }
                    }
                } else {
                    let offset = 0;

                    preguntas.forEach((pregunta) => {
                        const campo = pregunta.campo;
                        const valor = contenedor[campo];

                        if (valor && typeof valor === 'string') {
                            const coords = valor.split(',');

                            if (coords.length === 2) {
                                let lat = parseFloat(coords[0].trim());
                                let lng = parseFloat(coords[1].trim());

                                const spread = 0.0005;
                                lat += spread * offset;
                                lng += spread * offset;
                                offset++;

                                if (!isNaN(lat) && !isNaN(lng)) {
                                    markers[contenedor[campo] + contenedor['contenedor']] = new google.maps.Marker({
                                        position: { lat, lng },
                                        map: map,
                                    });
                                    if (esPrimero) {
                                        map.setCenter({ lat: lat, lng: lng });
                                        map.setZoom(12);
                                        esPrimero = false;
                                    }

                                    markers[contenedor[campo] + contenedor['contenedor']].addListener('click', () => {
                                        infoWindow.setContent(
                                            `${pregunta.tooltip}<br>Contenedor: <strong>${contenedor.contenedor}</strong>`,
                                        );
                                        infoWindow.open(map, markers[contenedor[campo] + contenedor['contenedor']]);
                                    });

                                    window.markers = window.markers || [];
                                    window.markers.push(markers[contenedor[campo] + contenedor['contenedor']]);
                                }
                            }
                        }
                    });
                }
            });

            // Cierra el modal después de aplicar los filtros
            limpiarMOdal();
        })
        .catch((error) => {
            console.error('Error al traer coordenadas:', error);
        });
});

// Función para limpiar marcadores
function limpiarMarcadores() {
    if (window.markers && window.markers.length > 0) {
        window.markers.forEach((marker) => {
            marker.setMap(null);
        });
        window.markers = [];
    }
}

const clienteSelect = document.getElementById('cliente');
clienteSelect.addEventListener('change', function () {
    const clienteId = this.value;
    const subclienteSelect = document.getElementById('subcliente');

    // Limpia las opciones anteriores
    subclienteSelect.innerHTML = '<option value="">Seleccione un subcliente</option>';

    if (clienteId) {
        // Puedes mostrar un "cargando..." si quieres
        const loadingOption = document.createElement('option');
        loadingOption.textContent = 'Cargando subclientes...';
        loadingOption.disabled = true;
        loadingOption.selected = true;
        subclienteSelect.appendChild(loadingOption);

        fetch(`/api/coordenadas/subclientes/${clienteId}`)
            .then((response) => response.json())
            .then((subclientes) => {
                subclienteSelect.innerHTML = '<option value="">Seleccione un subcliente</option>'; // Resetea

                if (subclientes.length > 0) {
                    subclientes.forEach((subcliente) => {
                        const option = document.createElement('option');
                        option.value = subcliente.id;
                        option.textContent = subcliente.nombre;
                        subclienteSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.textContent = 'No hay subclientes disponibles';
                    option.disabled = true;
                    subclienteSelect.appendChild(option);
                }
            })
            .catch((error) => {
                console.error('Error al cargar subclientes:', error);
                subclienteSelect.innerHTML = '<option value="">Error al cargar subclientes</option>';
            });
    }
});
document.addEventListener('DOMContentLoaded', function () {
    cargarinicial();
    const btnEditarFiltros = document.getElementById('btnEditarFiltros');
    const btnCerrarModal = document.getElementById('btnCerrarModal');

    filtroModalUI = new bootstrap.Modal(document.getElementById('filtroModal'));
    if (filtroModalUI && btnEditarFiltros && btnCerrarModal) {
        btnEditarFiltros.addEventListener('click', function () {
            filtroModalUI.show();
        });

        setTimeout(function () {
            if (btnCerrarModal) {
                // Cerrar modal
                btnCerrarModal.addEventListener('click', function () {
                    filtroModalUI.hide();
                });
            } else {
                console.error('Botón de cierre no encontrado.');
            }

            fetch('/api/coordenadas/entidadesPC')
                .then((response) => response.json())
                .then((data) => {
                    // const proveedorSelect = document.getElementById('proveedor');
                    const clienteSelect = document.getElementById('cliente');

                    // Añadir una opción predeterminada
                    //  proveedorSelect.innerHTML = '<option value="">Seleccione un proveedor</option>';
                    clienteSelect.innerHTML = '<option value="">Seleccione un cliente</option>';

                    // Cargar proveedores
                    // data.proveedor.forEach(proveedor => {
                    //     const option = document.createElement('option');
                    //     option.value = proveedor.id;
                    //     option.textContent = proveedor.nombre;
                    //     proveedorSelect.appendChild(option);
                    // });

                    // Cargar clientes
                    data.client.forEach((cliente) => {
                        const option = document.createElement('option');
                        option.value = cliente.id;
                        option.textContent = cliente.nombre;
                        if (cliente.id == idCliente) {
                            option.selected = true;
                            clienteSelect.disabled = true;
                        }
                        clienteSelect.appendChild(option);
                    });

                    clienteSelect.dispatchEvent(new Event('change'));
                })
                .catch((error) => console.error('Error al cargar proveedores y clientes:', error));
        }, 100);
    } else {
        console.error('No se encontraron los elementos necesarios.');
    }
});
function limpiarMOdal() {
    if (filtroModalUI) {
        filtroModalUI.hide();
    }
}
function limpiarFiltros() {
    const modal = document.getElementById('filtroModal');
    const inputs = modal.querySelectorAll('input, select, textarea');

    inputs.forEach((element) => {
        if (element.tagName === 'SELECT') {
            if (element.disabled == false) {
                element.selectedIndex = 0;
            }
        } else {
            element.value = '';
        }
    });

    seleccionados.length = 0;
    document.getElementById('contenedores-seleccionados').innerHTML = '';
    const inputContenedores = document.getElementById('contenedores');
    if (inputContenedores) {
        inputContenedores.value = '';
    }
}

const seleccionados = [];

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
}

document.getElementById('ubicacion-toggle').addEventListener('change', function () {
    const ubicacionTexto = document.getElementById('ubicacion-texto');

    if (this.checked) {
        ubicacionTexto.textContent = 'Última ubicación';
    } else {
        ubicacionTexto.textContent = 'Todas las ubicaciones';
    }
});
