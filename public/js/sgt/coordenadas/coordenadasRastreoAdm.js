let equiposSearch = [];
let rastreosActivos = {};
let map;
const estadosLi = {};
let markers = [];
let elementoPanelRastro = [];
let catalogoBusquedaOriginal = [];
let catalogoBusqueda = [];
let contenedoresDisponiblesAll = [];
let mapaAjustado = false;

let detalleConvoys;
let contenedoresDisponibles = [];
let directionsService = null;
let directionsRenderer = [];

let ItemsSelectsID = {};
let intervalIdsID = {};
let mostrarTodos = false;

let intervaloRastreo = null;

const input = document.getElementById("buscadorGeneral");
const resultados = document.getElementById("resultadosBusqueda");
const chipContainer = document.getElementById("chipsBusqueda");

function googleMapsReady() {
    initMap();
}
function initMap() {
    directionsService = new google.maps.DirectionsService();

    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 0, lng: 0 },
        zoom: 2,
    });

    const marker = new google.maps.Marker({
        position: { lat: 0, lng: 0 },
        map: map,
    });
}

function cargaConboys2(fecha_inicio, fecha_fin) {
    const overlay = document.getElementById("gridLoadingOverlay");
    overlay.style.display = "flex";

    gridApi2.setGridOption("rowData", []);

    fetch(
        `/coordenadas/conboys/getconboysFinalizados?inicio=${fecha_inicio}&fin=${fecha_fin}`,
    )
        .then((response) => response.json())
        .then((data) => {
            const rowData = data.data;
            gridApi2.setGridOption("rowData", rowData);
        })
        .catch((error) => {
            console.error(
                "❌ Error al obtener la lista de convoys grid 2:",
                error,
            );
        })
        .finally(() => {
            overlay.style.display = "none";
        });
}
function cargarConvoysEnSelect(convoys) {
    const select = document.getElementById("convoys");

    select.innerHTML = '<option value="">Seleccione un convoy</option>';

    convoys.forEach((convoy) => {
        const option = document.createElement("option");
        option.value = convoy.id;
        option.textContent = `${convoy.no_conboy} - ${convoy.nombre}`;
        select.appendChild(option);
    });
}
function cargarEquiposEnSelect(dataequipos) {
    const select = document.getElementById("Equipo");

    select.innerHTML = '<option value="">Seleccione un equipo</option>';

    dataequipos.forEach((equipo) => {
        const option = document.createElement("option");
        option.value = `${equipo.id_equipo}|${equipo.imei}|${equipo.id}|${equipo.tipoGps}`;
        const textoPlaca = equipo.placas?.trim() ? equipo.placas : "SIN PLACA";
        option.textContent = `${equipo.id_equipo} - ${equipo.marca}- ${equipo.tipo}- ${textoPlaca}`;
        select.appendChild(option);
    });
}
function cargarinicial() {
    fetch(`/coordenadas/contenedor/searchEquGps?`)
        .then((response) => response.json())
        .then((data) => {
            catalogoBusquedaOriginal.length = 0;
            contenedoresDisponibles = data.datos;

            detalleConvoys = data.dataConten;

            contenedoresDisponiblesAll = data.datosAll;

            equiposSearch = data.equiposAll;
            // Convoys detalle
            data.conboys.forEach((c) => {
                catalogoBusquedaOriginal.push({
                    tipo: "Convoy",
                    label: c.no_conboy + " " + c.nombre,
                    value: c.no_conboy,
                    id: c.id,
                    value_chasis: `NO DISPONIBLE|`,
                    llegada:
                        c.geocerca_lat +
                        "|" +
                        c.geocerca_long +
                        "|" +
                        c.geocerca_radio,
                    empresas: c.empresas.split(",").map(Number),
                    clientes: c.clientes.split(",").map(Number),
                    lineas: c.lineas.split(",").map(Number),
                });
            });
            // Contenedores (desde convoysDetalle)
            contenedoresDisponibles.forEach((cd) => {
                catalogoBusquedaOriginal.push({
                    tipo: "Contenedor",
                    label: cd.contenedor + " Eq: " + cd.id_equipo,
                    value:
                        cd.contenedor +
                        "|" +
                        cd.imei +
                        "|" +
                        cd.id_contenedor +
                        "|" +
                        cd.tipoGps,
                    id: cd.id_contenedor,
                    value_chasis:
                        cd.contenedor +
                        "|" +
                        cd.imei_chasis +
                        "|" +
                        cd.id_contenedor +
                        "|" +
                        cd.tipoGpsChasis,
                    llegada: cd.latitud + "|" + cd.longitud + "|0",

                    empresas: [cd.id_empresa],
                    lineas: [cd.proveedor_id],
                    clientes: [cd.id_cliente],
                });
            });

            // Equipos (si tienes un array separado)
            data.equipos.forEach((eq) => {
                const textoPlaca = eq.placas?.trim() ? eq.placas : "";
                const textomarca = eq.marca?.trim() ? eq.marca : "";
                const textotipo = eq.tipo?.trim() ? eq.tipo : "";
                catalogoBusquedaOriginal.push({
                    tipo: "Equipo",

                    label: `${eq.id_equipo} - ${textomarca} - ${textotipo} - ${textoPlaca}`,
                    value: `${eq.id_equipo}|${eq.imei}|${eq.id}|${eq.tipoGps}`,
                    id: eq.id,
                    value_chasis: `NO DISPONIBLE|`,
                    llegada: `0|0|0`,
                    empresas: [eq.id_empresa],
                    lineas: [],
                    clientes: [],
                });
            });

            catalogoBusqueda = [...catalogoBusquedaOriginal];
        })
        .catch((error) => {
            console.error("Error al traer coordenadas:", error);
        });
}

function getRandomColor() {
    const min = 127;
    const max = 200;
    const r = Math.floor(Math.random() * (max - min + 1)) + min;
    const g = Math.floor(Math.random() * (max - min + 1)) + min;
    const b = Math.floor(Math.random() * (max - min + 1)) + min;
    return `rgb(${r}, ${g}, ${b})`;
}
function getStrongColor() {
    // Hue (0-360): distinto tono
    const hue = Math.floor(Math.random() * 365);
    // Saturation alto (70–100%)
    const saturation = 90;
    // Lightness medio (40–50%) → ni muy claro ni muy oscuro
    const lightness = 65;

    return `hsl(${hue}, ${saturation}%, ${lightness}%)`;
}

function strengthenColor(rgb) {
    const [r, g, b] = rgb.match(/\d+/g).map(Number);
    // Escalar los valores hacia un rango de 100–220
    const scale = (val) => {
        if (val < 100) return 100;
        if (val > 220) return 220;
        return val;
    };
    return `rgb(${scale(r)},${scale(g)},${scale(b)})`;
}
function lightenColor(rgb, amount = 40) {
    const [r, g, b] = rgb.match(/\d+/g).map(Number);
    const nr = Math.min(255, r + amount);
    const ng = Math.min(255, g + amount);
    const nb = Math.min(255, b + amount);
    return `rgb(${nr},${ng},${nb})`;
}

function darkenColor(rgb, amount = 40) {
    const [r, g, b] = rgb.match(/\d+/g).map(Number);
    const nr = Math.max(0, r - amount);
    const ng = Math.max(0, g - amount);
    const nb = Math.max(0, b - amount);
    return `rgb(${nr},${ng},${nb})`;
}

function createMarkerIcon(color = "#FF0000", size = 40) {
    const svg = `
    <svg width="${size}" height="${size}" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <!-- Pin principal -->
      <path d="M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7z"
            fill="${color}" stroke="white" stroke-width="2"/>
      <!-- Círculo animado central -->
      <circle cx="12" cy="9" r="3" fill="white">
        <animate attributeName="r" values="3;6;3" dur="1s" repeatCount="indefinite" />
      </circle>
    </svg>
  `;
    return {
        url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg),
        scaledSize: new google.maps.Size(size, size),
    };
}

function actualizarUbicacionReal(coordenadaData) {
    fetch("/coordenadas/rastrear/savehistori", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify(coordenadaData),
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.success) {
                console.log("Coordenada guardada:", data.data);
            } else {
                console.warn("Error al guardar coordenada", data);
            }
        })
        .catch((error) => {
            console.error("Error en la solicitud:", error);
        });
}
function obtenerImeisPorConvoyId(convoyId) {
    let itemFiltrado = detalleConvoys
        .filter(
            (item) =>
                item.conboy_id == convoyId && item.imei && item.id_contenedor,
        )
        .map(
            (item) =>
                item.num_contenedor +
                "|" +
                item.imei +
                "|" +
                item.id_contenedor +
                "|" +
                item.tipoGps,
        );

    let itemFiltradoChasis = detalleConvoys
        .filter(
            (item) =>
                item.conboy_id == convoyId &&
                item.imei_chasis &&
                item.id_contenedor,
        )
        .map(
            (item) =>
                item.num_contenedor +
                "|" +
                item.imei_chasis +
                "|" +
                item.id_contenedor +
                "|" +
                item.tipoGpsChasis,
        );

    return [...itemFiltrado, ...itemFiltradoChasis].flat(Infinity);
}
function obtenerContenedorReal(valor) {
    if (!valor) return null;

    const partes = valor.split("|");

    const imei = partes[1];
    const idContenedor = partes[2];

    const encontrado = detalleConvoys.find(
        (item) =>
            item.id_contenedor == idContenedor &&
            (item.imei == imei || item.imei_chasis == imei),
    );

    return encontrado ? encontrado.no_conboy : null;
}
function actualizarUbicacion(
    dataUbi,
    t,
    KEYITEM,
    num_convoy,
    map,
    idProceso,

    estado,
) {
    console.log("procesando ubicacion", KEYITEM);

    let partes = KEYITEM.split("|");

    let id = partes[0];
    let value = partes[1];

    let keyInterval = id + "|" + value;

    let tipo = "";

    if (rastreosActivos[`${KEYITEM}`] === false) {
        console.log("Rastreo eliminado", KEYITEM);
        return;
    }

    if (!Array.isArray(dataUbi)) {
        console.warn("La respuesta no es un array:", dataUbi);
        return;
    }

    dataUbi.forEach((item, index) => {
        let valueSearch = obtenerContenedorReal(item.value);
        num_convoy = valueSearch;
        if (!valueSearch) {
            valueSearch = item.value;
        }

        let colorMarker = $(`li[data-key="${valueSearch}"]`).data("color");
        if (!colorMarker) {
            colorMarker = $(`li[data-key-chasis="${valueSearch}"]`).data(
                "color",
            );
        }
        let colorBG = colorMarker;
        t = $(`li[data-key="${valueSearch}"]`).data("tipo");
        if (!t) {
            t = $(`li[data-key-chasis="${valueSearch}"]`).data("tipo");
        }

        tipo = item.tipogps;

        let latlocal = "";
        let lnglocal = "";
        let idConvoyOContenedor = "";

        latlocal = parseFloat(item.ubicacion.lat);
        lnglocal = parseFloat(item.ubicacion.lng);

        idConvoyOContenedor = item.id_contenendor;

        tipo = tipo + " " + item.contenedor;

        let continueShowing = true;

        if (!continueShowing) return;

        const markerKey =
            KEYITEM + "|" + item.contenedor + "|" + item.ubicacion.tipoEquipo;

        if (markers[markerKey]) {
            markers[markerKey].setPosition({
                lat: latlocal,
                lng: lnglocal,
            });
        } else {
            if (!latlocal || !lnglocal) return;

            if (item.ubicacion.tipoEquipo === "Camion") {
                colorMarker = getStrongColor();
            } else {
                colorMarker = getStrongColor();
            }

            const newMarker = new google.maps.Marker({
                position: { lat: latlocal, lng: lnglocal },
                map: map,
                icon: createMarkerIcon(colorMarker, 40),
            });

            newMarker.keyItem = markerKey;

            let contentC = "";

            if (t === "Equipo") {
                let idEq = parseInt(item.id_contenendor);

                let filtroEqu = equiposSearch.find(
                    (equipo) => equipo.id === idEq,
                );

                contentC = `
                        <div style="
    background-color:${colorBG};
    padding:10px;
    border-radius:10px;
    box-shadow:0 2px 6px rgba(0,0,0,0.2);
    font-family: Arial, sans-serif;
">
    <div style="color:white; font-size:13px; line-height:1.5;">
        <div><strong>Equipo:</strong> ${filtroEqu?.id_equipo}</div>
        <div><strong>Marca:</strong> ${filtroEqu?.marca}</div>
        <div><strong>Placas:</strong> ${filtroEqu?.placas || "sin placas"}</div>
    </div>
</div>
                `;
            } else if (t === "Contenedor") {
                contentC = `
                <div style="
    background-color:${colorBG};
    padding:10px;
    border-radius:10px;
    font-family: Arial, sans-serif;
    display:flex;
    flex-direction:column;
    gap:8px;
">

    <div style="color:white; font-size:13px; line-height:1.5;">
        <div><strong>Equipo:</strong> ${item.EquipoBD}</div>
        <div><strong>Contenedor:</strong> ${item.contenedor}</div>
    </div>

    <button id="btnRuta_${markerKey}"
        style="
            background: linear-gradient(135deg, #1976d2, #42a5f5);
            color:white;
            border:none;
            padding:6px;
            border-radius:6px;
            cursor:pointer;
            font-size:12px;
        ">
        📍 Mostrar ruta
    </button>

    <div id="infoRuta_${markerKey}" style="
        display:none;
        background: rgba(255,255,255,0.2);
        padding:6px;
        border-radius:6px;
        font-size:12px;
        color:white;
    "></div>

</div>
                `;
            } else {
                contentC = `
                <div style="
    background-color:${colorBG};
    padding:10px;
    border-radius:10px;
    font-family: Arial, sans-serif;
    display:flex;
    flex-direction:column;
    gap:8px;
">

    <div style="color:white; font-size:13px; line-height:1.5;">
        <div><strong>Convoy:</strong> ${num_convoy}</div>
        <div><strong>Equipo:</strong> ${item.EquipoBD}</div>
        <div><strong>Contenedor:</strong> ${item.contenedor}</div>
    </div>

    <button id="btnRuta_${markerKey}"
        style="
            background: linear-gradient(135deg, #1976d2, #42a5f5);
            color:white;
            border:none;
            padding:6px;
            border-radius:6px;
            cursor:pointer;
            font-size:12px;
        ">
        📍 Mostrar ruta
    </button>

   <div class="infoRuta" data-key="${markerKey}" style="
        display:none;
        background: rgba(255,255,255,0.2);
        padding:6px;
        border-radius:6px;
        font-size:12px;
        color:white;
    "></div>

</div>
                `;
            }

            const infoWindow = new google.maps.InfoWindow({
                content: contentC,
            });

            newMarker.addListener("click", () => {
                infoWindow.open(map, newMarker);

                const contenedorRes = item.contenedor;

                let info = contenedoresDisponibles.find(
                    (d) => d.contenedor === contenedorRes,
                );

                if (t === "Convoy") {
                    let contenedoresConvoy = detalleConvoys.filter(
                        (d) => d.no_conboy === num_convoy,
                    );

                    mostrarInfoConvoy(contenedoresConvoy, item.EquipoBD, "");
                } else {
                    mostrarInfoConvoy(info ? [info] : [], item.EquipoBD, "");
                }

                google.maps.event.addListenerOnce(
                    infoWindow,
                    "domready",
                    () => {
                        const btn = document.getElementById(
                            `btnRuta_${markerKey}`,
                        );
                        const infoSpan = document.getElementById(
                            `infoRuta_${markerKey}`,
                        );
                        btn.addEventListener("click", () => {
                            const position = newMarker.getPosition();
                            const origin = {
                                lat: position.lat(),
                                lng: position.lng(),
                            };

                            let latLlegada = parseFloat(info.latitud);
                            let lngLlegada = parseFloat(info.longitud);

                            if (directionsRenderer[markerKey]) {
                                const isVisible =
                                    directionsRenderer[markerKey].getMap();

                                directionsRenderer[markerKey].setMap(
                                    isVisible ? null : map,
                                );

                                btn.textContent = isVisible
                                    ? "Mostrar ruta"
                                    : "Ocultar ruta";

                                const infoSpanNuevo = document.getElementById(
                                    `infoRuta_${markerKey}`,
                                );

                                if (infoSpanNuevo) {
                                    infoSpanNuevo.style.display = isVisible
                                        ? "none"
                                        : "block";
                                }

                                return;
                            }

                            btn.textContent = "Calculando...";

                            directionsRenderer[markerKey] =
                                new google.maps.DirectionsRenderer({
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

                            directionsService.route(
                                request,
                                (result, status) => {
                                    if (status === "OK") {
                                        directionsRenderer[
                                            markerKey
                                        ].setDirections(result);

                                        btn.textContent = "Ocultar ruta";

                                        const leg = result.routes[0].legs[0];

                                        // 🔥 SIEMPRE vuelve a buscar el span
                                        const infoSpanNuevo =
                                            document.getElementById(
                                                `infoRuta_${markerKey}`,
                                            );

                                        if (infoSpanNuevo) {
                                            infoSpanNuevo.style.display =
                                                "block";

                                            infoSpanNuevo.innerHTML = `
                    🚗 <strong>${leg.distance.text}</strong><br>
                    ⏱ <strong>${leg.duration.text}</strong>
                `;
                                        }
                                    } else {
                                        btn.textContent = "Mostrar ruta";

                                        Swal.fire({
                                            icon: "error",
                                            title: "Error",
                                            text: "No se ha configurado direccion del mapa en cotizaciones",
                                        });
                                    }
                                },
                            );
                        });
                    },
                );
            });

            markers[markerKey] = newMarker;

            if (!mapaAjustado) {
                const bounds = new google.maps.LatLngBounds();

                Object.values(markers).forEach((marker) =>
                    bounds.extend(marker.getPosition()),
                );

                map.fitBounds(bounds);

                mapaAjustado = true;
            }
        }

        Object.keys(markers).forEach((key) => {
            if (!key.startsWith(KEYITEM + "|")) return;

            if (estado) {
                if (
                    item.ubicacion.esDatoEmp === "SI" &&
                    key.includes(item.contenedor)
                ) {
                    markers[key].setMap(map);
                } else {
                    markers[key].setMap(null);
                }
            } else {
                markers[key].setMap(map);
            }
        });

        if (t !== "Convoy") {
            idProceso = 0;
        }

        const datasave = {
            latitud: latlocal,
            longitud: lnglocal,
            ubicacionable_id: idConvoyOContenedor,
            tipo: tipo,
            tipoRastreo: t ?? "Equipo",
            idProceso: idProceso,
            status_api: item.status ?? 0,
            new_id: item.new_id ?? null,
            tiempo_respuesta_ms: item.tiemporespuesta ?? null,

            valorSolicitado: item.value ?? null,

            data: item,
            messageAp: item.messageAp ?? null,
        };

        if (idConvoyOContenedor !== "") {
            actualizarUbicacionReal(datasave);
        }
    });
}
document.addEventListener("click", function (e) {
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

        let info = contenedoresDisponibles.find(
            (d) =>
                (d.contenedor && key.includes(d.contenedor)) ||
                (d.imei && key.includes(d.imei)) ||
                (d.imei_chasis && key.includes(d.imei_chasis)),
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
function limpiarMapa() {
    for (let key in markers) {
        markers[key].setMap(null);
    }
    markers = {};

    for (let key in directionsRenderer) {
        directionsRenderer[key].setMap(null);
    }
    directionsRenderer = {};

    $(".estadoDispositivo")
        .removeClass("bg-success bg-warning")
        .addClass("bg-secondary")
        .text("Sin señal");

    $(".iconoEstado").text("⚫");

    $(".checkDispositivo").each(function () {
        const li = $(this).closest("li");
        li.data("lat", 0);
        li.data("lng", 0);
    });

    if (intervaloRastreo) {
        clearInterval(intervaloRastreo);
    }
}
const toggle = document.getElementById("toggleTodos");
const label = document.getElementById("labelToggle");

toggle.addEventListener("change", function () {
    const estado = this.checked;
    label.textContent = estado ? "Ocultar todos" : "Mostrar todos";
    toggleTodos(estado);
});
function iniciarRastreo() {
    if (intervaloRastreo) {
        clearInterval(intervaloRastreo);
    }

    intervaloRastreo = setInterval(buscarUbicaciones, 8000);
    buscarUbicaciones();
}
function toggleTodos(estado) {
    const checks = document.querySelectorAll(".checkDispositivo");

    checks.forEach((chk) => {
        chk.checked = estado;
    });

    if (!estado) {
        limpiarMapa();
    } else {
        iniciarRastreo();
    }
}
$(document).on("change", ".checkDispositivo", function () {
    const algunoActivo = $(".checkDispositivo:checked").length > 0;

    if (algunoActivo) {
        iniciarRastreo();
    } else {
        limpiarMapa();
    }
});
function detener(keyInterval) {
    if (intervalIdsID[keyInterval]) {
        clearInterval(intervalIdsID[keyInterval]);
        intervalIdsID[keyInterval] = null;
    }
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

        // Crear pestaña
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

            // Crear contenido
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
function crearurlmapalatitudlongitud(lat, lng) {
    return `https://www.google.com/maps/search/?api=1&query=${lat},${lng}`;
}

// Función para limpiar marcadores
function limpiarMarcadores() {
    markers.forEach((marker) => marker.setMap(null));
    markers = [];
}

document.addEventListener("DOMContentLoaded", function () {
    const hoy = moment();
    inicio = hoy.clone().subtract(10, "days");
    fin = hoy.clone().add(10, "days");
    $("#daterange").daterangepicker(
        {
            startDate: moment(inicio),
            endDate: moment(fin),
            maxDate: moment(),
            opens: "right",
            locale: {
                format: "YYYY-MM-DD",
                separator: " AL ",
                applyLabel: "Aplicar",
                cancelLabel: "Cancelar",
                fromLabel: "Desde",
                toLabel: "Hasta",
                customRangeLabel: "Personalizado",
                daysOfWeek: ["Do", "Lu", "Ma", "Mi", "Ju", "Vi", "Sa"],
                monthNames: [
                    "Enero",
                    "Febrero",
                    "Marzo",
                    "Abril",
                    "Mayo",
                    "Junio",
                    "Julio",
                    "Agosto",
                    "Septiembre",
                    "Octubre",
                    "Noviembre",
                    "Diciembre",
                ],
                firstDay: 1,
            },
            ranges: {
                Hoy: [moment(), moment()],
                "Últimos 7 días": [moment().subtract(6, "days"), moment()],
                "Últimos 30 días": [moment().subtract(29, "days"), moment()],
                "Este mes": [
                    moment().startOf("month"),
                    moment().endOf("month"),
                ],
                "Mes anterior": [
                    moment().subtract(1, "month").startOf("month"),
                    moment().subtract(1, "month").endOf("month"),
                ],
            },
        },
        function (start, end) {
            inicio = start.format("YYYY-MM-DD");
            fin = end.format("YYYY-MM-DD");

            cargaConboys2(inicio, fin);
        },
    );
    cargarinicial();
    cargaConvoysTab();
});

let contenedoresGuardados;
let contenedoresGuardadosTodos;
//let contenedoresDisponibles = [];
let userBloqueo = false;
const seleccionados = [];
const ItemsSelects = [];

function BloquearHabilitarEdicion(block) {
    document.getElementById("id_convoy").readOnly = block;
    document.getElementById("nombre").readOnly = block;
    document.getElementById("fecha_inicio").readOnly = block;
    document.getElementById("fecha_fin").readOnly = block;
}

function abrirMapaEnNuevaPestana(contenedor, tipoS) {
    const url = `/coordenadas/mapa_rastreo?contenedor=${contenedor}&tipoS=${encodeURIComponent(tipoS)}`;
    window.open(url, "_blank");
}

//NUEVA FUNCIONALIDAD
function llenarSelect(select, data, placeholder = "Todos") {
    const el = document.getElementById(select);

    el.innerHTML = `<option value="">${placeholder}</option>`;

    data.forEach((item) => {
        el.innerHTML += `
<option value="${item.id}">
${item.nombre}
</option>
`;
    });
}

$("#filtroEmpresa").on("change", function () {
    const empresaId = $(this).val();

    let proveedoresFiltrados = proveedores;
    let clientesFiltrados = clientes;

    if (empresaId) {
        proveedoresFiltrados = proveedores.filter(
            (p) => p.id_empresa == empresaId,
        );

        clientesFiltrados = clientes.filter((c) => c.id_empresa == empresaId);
    }

    llenarSelect("filtroLineaT", proveedoresFiltrados);
    llenarSelect("filtrocliente", clientesFiltrados);

    aplicarFiltrosPanel();
});

$("#filtroLineaT,#filtrocliente,#buscarDispositivo").on(
    "change keyup",
    function () {
        aplicarFiltrosPanel();
    },
);

function obtenerFiltros() {
    return {
        empresa: $("#filtroEmpresa").val(),

        linea: $("#filtroLineaT").val(),

        cliente: $("#filtrocliente").val(),

        tipo: $("#filtroTipo").val(),

        buscar: $("#buscadorGeneral").val().toLowerCase(),
    };
}

function obtenerImeisSeleccionados() {
    let imeis = [];
    let tipo = "";
    $(".checkDispositivo:checked").each(function () {
        tipo = $(this).closest(".dispositivoItem").data("tipo");
        if (tipo == "Convoy") {
            let id = $(this).closest(".dispositivoItem").data("id");
            imeis = obtenerImeisPorConvoyId(id);
        } else {
            let imei = $(this).data("value");
            let imeiChasis = $(this).data("value-chasis");
            if (imei) {
                imeis.push(imei);
                if (!imeiChasis.includes("NO DISPONIBLE")) {
                    imeis.push(imeiChasis);
                }
            }
        }
    });

    return imeis;
}
function buscarUbicaciones() {
    const imeis = obtenerImeisSeleccionados();

    if (!imeis.length) {
        return;
    }

    fetch("/coordenadas/ubicacion-vehiculo", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({ imeis: imeis }),
    })
        .then((res) => res.json())
        .then((data) => {
            actualizarEstadosPanel(data);

            const randomColor = getRandomColor();

            actualizarUbicacion(data, "Equipo", "all", 0, map, 0, 0);
        })
        .catch((err) => console.error(err));
}
function actualizarEstadoDispositivo(li, data) {
    const badge = li.find(".estadoDispositivo");
    const icono = li.find(".iconoEstado");

    if (!data || !data.lat || !data.lng) {
        badge
            .removeClass()
            .addClass("badge bg-secondary estadoDispositivo")
            .text("Sin señal");

        icono.text("⚫");
        return;
    }

    const latNueva = Number(data.lat);
    const lngNueva = Number(data.lng);

    const latAnterior = Number(li.data("lat"));
    const lngAnterior = Number(li.data("lng"));

    const velocidad = Number(data.velocidad ?? 0);

    let moviendo = false;

    if (velocidad > 5) {
        moviendo = true;
    } else if (latAnterior !== 0 && lngAnterior !== 0) {
        const diffLat = Math.abs(latNueva - latAnterior);
        const diffLng = Math.abs(lngNueva - lngAnterior);

        if (diffLat > 0.0001 || diffLng > 0.0001) {
            moviendo = true;
        }
    }

    li.data("lat", latNueva);
    li.data("lng", lngNueva);

    if (moviendo) {
        badge
            .removeClass("bg-success bg-warning bg-secondary")
            .addClass("bg-success")
            .text("Moviendo");

        icono.text("🚚");
    } else {
        badge
            .removeClass("bg-success bg-warning bg-secondary")
            .addClass("bg-warning")
            .text("Detenido");

        icono.text("🅿️");
    }
}
function actualizarEstadosPanel(respuesta) {
    const ubicaciones = {};

    respuesta.forEach((r) => {
        if (r.ubicacion && r.ubicacion.imei) {
            let imeiUbi = r.ubicacion.imei.replace(/-/g, "");
            // let valueResp = r.value;
            // let valueAsignar = obtenerContenedorReal(valueResp);
            // if (valueAsignar) {
            //     imeiUbi = valueAsignar;
            // }
            ubicaciones[imeiUbi] = r.ubicacion;
        }
    });

    $(".checkDispositivo").each(function () {
        const value = $(this).data("value");
        const tipo = $(this).closest(".dispositivoItem").data("tipo");
        const li = $(this).closest("li");
        const partes = value.split("|");
        let imei = "";
        if (tipo == "Convoy") {
            let idConvoy = $(this).data("id");
            let imeis = obtenerImeisPorConvoyId(idConvoy);

            let dataFinal = null;

            imeis.forEach((imei, index) => {
                let valueC = imei;
                const partes2 = valueC.split("|");
                let imeiReal = partes2[1].replace(/-/g, "");

                const data = ubicaciones[imeiReal];

                if (index === 0) {
                    dataFinal = data ?? null;
                }

                if (
                    data &&
                    data.lat &&
                    data.lng &&
                    data.lat != 0 &&
                    data.lng != 0
                ) {
                    dataFinal = data;
                }
            });

            actualizarEstadoDispositivo(li, dataFinal);
            return;
        } else {
            imei = partes[1].replace(/-/g, "");
            const data = ubicaciones[imei];
            actualizarEstadoDispositivo(li, data);
        }

        /* if (!data || !data.lat || !data.lng) {
            badge
                .removeClass()
                .addClass("badge bg-secondary estadoDispositivo")
                .text("Sin señal");

            icono.text("⚫");

            return;
        }
        console.log("paso aki 1");
        const latNueva = Number(data.lat);
        const lngNueva = Number(data.lng);

        const latAnterior = Number(li.data("lat"));
        const lngAnterior = Number(li.data("lng"));

        const velocidad = Number(data.velocidad ?? 0);

        let moviendo = false;

        if (velocidad > 5) {
            moviendo = true;
        } else {
            if (latAnterior !== 0 && lngAnterior !== 0) {
                const diffLat = Math.abs(latNueva - latAnterior);
                const diffLng = Math.abs(lngNueva - lngAnterior);

                if (diffLat > 0.0001 || diffLng > 0.0001) {
                    moviendo = true;
                }
            }
        }

        li.data("lat", latNueva);
        li.data("lng", lngNueva);

        if (moviendo) {
            badge
                .removeClass("bg-success bg-warning bg-secondary")
                .addClass("bg-success")
                .text("Moviendo");

            icono.text("🚚");
        } else {
            badge
                .removeClass("bg-success bg-warning bg-secondary")
                .addClass("bg-warning")
                .text("Detenido");

            icono.text("🅿️");
        } */
    });
}

$(document).on("change", ".checkDispositivo", function () {
    if (intervaloRastreo) {
        clearInterval(intervaloRastreo);
    }

    intervaloRastreo = setInterval(buscarUbicaciones, 8000);

    buscarUbicaciones();
});
function pintarDispositivos(data) {
    const lista = $("#listaDispositivos");
    lista.empty();

    let count = data.length;
    const elTd = document.getElementById("totalDispositivos");

    if (elTd) {
        elTd.textContent = count;
    }

    data.forEach((d) => {
        let estadoIcono;
        let estadoTexto;
        let estadoColor;

        if (d.velocidad > 5) {
            estadoIcono = "🚚";
            estadoTexto = "Moviendo";
            estadoColor = "success";
        } else if (d.velocidad > 0) {
            estadoIcono = "🅿️";
            estadoTexto = "Detenido";
            estadoColor = "warning";
        } else {
            estadoIcono = "⚫";
            estadoTexto = "Sin señal";
            estadoColor = "secondary";
        }

        const color = getRandomColor();

        const item = `
<li class="list-group-item d-flex justify-content-between align-items-center dispositivoItem"
data-key="${d.value}"
data-key-chasis="${d.value_chasis}"
data-color="${color}"
data-lat="${0}"
data-lng="${0}"
data-tipo="${d.tipo}"
data-id="${d.id}"
style="background-color:${color}; color:white;">

<div>
<input type="checkbox"
class="checkDispositivo me-2"
data-id="${d.id}"
data-value="${d.value}"
data-value-chasis="${d.value_chasis}"
>

<span class="iconoEstado">${estadoIcono}</span>

<span class="fw-bold ms-1">${d.label}</span>

</div>

<span class="badge estadoDispositivo bg-${estadoColor}">
${estadoTexto}
</span>

</li>
`;

        lista.append(item);
    });
}
$("#filtroTipo").on("focus", function () {
    valorAnteriorTipo = $(this).val();
});

function hayRastreoActivo() {
    return Object.keys(markers).length;
}

function aplicarFiltrosPanel() {
    const filtros = obtenerFiltros();

    let dataFiltrada = catalogoBusqueda;

    if (filtros.empresa) {
        dataFiltrada = dataFiltrada.filter((d) =>
            d.empresas?.includes(Number(filtros.empresa)),
        );
    }

    if (filtros.linea) {
        dataFiltrada = dataFiltrada.filter((d) =>
            d.lineas?.includes(Number(filtros.linea)),
        );
    }

    if (filtros.cliente) {
        dataFiltrada = dataFiltrada.filter((d) =>
            d.clientes?.includes(Number(filtros.cliente)),
        );
    }

    if (filtros.buscar) {
        dataFiltrada = dataFiltrada.filter((d) =>
            d.label.toLowerCase().includes(filtros.buscar),
        );
    }
    if (filtros.tipo) {
        dataFiltrada = dataFiltrada.filter((d) =>
            d.tipo?.toLowerCase().includes(filtros.tipo?.toLowerCase()),
        );
    }

    pintarDispositivos(dataFiltrada);
}

$("#filtroTipo").on("change", function () {
    const nuevoValor = $(this).val();

    if (hayRastreoActivo()) {
        Swal.fire({
            title: "Cambiar tipo de rastreo",
            text: "Actualmente hay equipos en el mapa. Si continúas, se limpiará el mapa.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, limpiar y continuar",
            cancelButtonText: "Cancelar",
            reverseButtons: true,
        }).then((result) => {
            if (result.isConfirmed) {
                limpiarMapa();
                aplicarFiltrosPanel();
            } else {
                $("#filtroTipo").val(valorAnteriorTipo);
            }
        });
    } else {
        aplicarFiltrosPanel();
    }
});

input.addEventListener("input", function () {
    const query = this.value.trim().toLowerCase();

    resultados.innerHTML = "";
    chipContainer.innerHTML = "";
    filtroActivo = null;

    if (query.length < 2) {
        return;
    }

    const coincidencias = catalogoBusqueda.filter((item) =>
        item.label.toLowerCase().includes(query),
    );

    if (coincidencias.length === 0) {
        const div = document.createElement("div");
        div.classList.add("dropdown-item", "text-muted");
        div.textContent = "Sin resultados";

        resultados.appendChild(div);
        return;
    }

    // chips por tipo
    const tiposUnicos = [...new Set(coincidencias.map((item) => item.tipo))];

    tiposUnicos.forEach((tipo) => {
        const chip = document.createElement("button");

        chip.className =
            "btn btn-outline-secondary btn-sm rounded-pill me-2 mb-1";

        chip.textContent = tipo;

        chip.onclick = () => {
            filtroActivo = tipo;

            document
                .querySelectorAll("#chipsBusqueda .btn")
                .forEach((btn) => btn.classList.remove("active"));

            chip.classList.add("active");

            mostrarResultadosFiltrados(query);
        };

        chipContainer.appendChild(chip);
    });

    mostrarResultadosFiltrados(query);
});

function mostrarResultadosFiltrados(query) {
    resultados.innerHTML = "";

    const sugerencias = catalogoBusqueda
        .filter(
            (item) =>
                item.label.toLowerCase().includes(query) &&
                (!filtroActivo || item.tipo === filtroActivo),
        )
        .slice(0, 10);

    sugerencias.forEach((item) => {
        const div = document.createElement("div");

        div.classList.add("dropdown-item");

        div.textContent = item.label;

        div.onclick = () => {
            input.value = "";
            resultados.innerHTML = "";
            chipContainer.innerHTML = "";

            agregarDispositivoDesdeBusqueda(item);
        };

        resultados.appendChild(div);
    });
}

function agregarDispositivoDesdeBusqueda(item) {
    if (
        document.querySelector(`.checkDispositivo[data-value="${item.value}"]`)
    ) {
        return;
    }
    pintarDispositivos([item]);

    const checkbox = document.querySelector(
        `.checkDispositivo[data-value="${item.value}"]`,
    );

    if (!checkbox) return;

    checkbox.checked = true;

    checkbox.dispatchEvent(new Event("change"));
}

// tab convoys
function cargaConvoysTab() {
    let gridApi;
    let gridApi2;
    //  cargarinicial();
    definirTable();
    definirTable2();

    const modal = new bootstrap.Modal(
        document.getElementById("modalBuscarConvoy"),
        {
            backdrop: "static",
            keyboard: false,
        },
    );

    document
        .getElementById("btnBuscarconboy")
        .addEventListener("click", function () {
            modal.show();
        });

    document.getElementById("btnNuevoconboy").addEventListener("click", () => {
        limpiarFormulario();
        // Aquí abres el modal, por ejemplo con Bootstrap 5:
        const modal = new bootstrap.Modal(
            document.getElementById("CreateModal"),
        );
        modal.show();
    });

    document
        .getElementById("formBuscarConvoy")
        .addEventListener("submit", function (e) {
            e.preventDefault();

            const numero = document.getElementById("numero_convoy").value;

            fetch(`/coordenadas/conboys/getconvoy/${numero}`)
                .then((res) => res.json())
                .then((data) => {
                    if (data.success) {
                        let tablalimpiar = document.getElementById(
                            "tablaContenedoresBodyBuscar",
                        );
                        tablalimpiar.innerHTML = "";
                        seleccionados.length = 0;
                        ItemsSelects.length = 0;

                        const fechaInicio = new Date(data.data.fecha_inicio);
                        const fechaFin = new Date(data.data.fecha_fin);

                        let blockUser = data.data.BockUser;
                        const formatoFecha = (fecha) => {
                            return `${fecha.getDate().toString().padStart(2, "0")}/${(fecha.getMonth() + 1).toString().padStart(2, "0")}/${fecha.getFullYear()}`;
                        };
                        document.getElementById(
                            "descripcionConvoy",
                        ).textContent = data.data.nombre;
                        document.getElementById(
                            "fechaInicioConvoy",
                        ).textContent = formatoFecha(fechaInicio);
                        document.getElementById("fechaFinConvoy").textContent =
                            formatoFecha(fechaFin);
                        document.getElementById("id_convoy").value =
                            data.data.idconvoy;

                        document.getElementById("no_convoy").value =
                            data.data.no_conboy || "";
                        document.getElementById("fecha_inicio").value =
                            formatDateForInput(data.data.fecha_inicio) || "";
                        document.getElementById("fecha_fin").value =
                            formatDateForInput(data.data.fecha_fin) || "";
                        document.getElementById("nombre").value =
                            data.data.nombre || "";
                        document.getElementById("tipo_disolucion").value =
                            data.data.tipo_disolucion || "";
                        document.getElementById("geocerca_lat").value =
                            data.data.geocerca_lat || "";
                        document.getElementById("geocerca_lng").value =
                            data.data.geocerca_lng || "";
                        document.getElementById("geocerca_radio").value =
                            data.data.geocerca_radio || "";

                        contenedoresDisponibles = data.data.contenedoresPropios;
                        contenedoresAsignadosAntes =
                            data.data.contenedoresPropiosAsignados;
                        contenedoresAsignadosAntes.forEach(
                            (contenedor, index) => {
                                seleccionarContenedor2(
                                    contenedor.num_contenedor,
                                );
                            },
                        );

                        document.getElementById(
                            "resultadoConvoy",
                        ).style.display = "block";
                    } else {
                        alert("Convoy no encontrado.");
                    }
                });
        });

    function limpiarFormulario() {
        const form = document.getElementById("formFiltros");
        form.reset(); // Limpia todos los inputs

        // Además limpia inputs ocultos, o elementos dinámicos si tienes
        document.getElementById("contenedores-seleccionados").innerHTML = "";
        document.getElementById("contenedores").value = "";
        document.getElementById("ItemsSelects").value = "";
        const tablaBody = document.getElementById("tablaContenedoresBody");
        if (tablaBody) {
            tablaBody.innerHTML = "";
        }
        // Si usas dataset para editar, elimina también ese id para que no interfiera
        delete form.dataset.editId;
    }

    document
        .getElementById("tipo_disolucion")
        .addEventListener("change", function () {
            const tipo = this.value;
            document
                .querySelectorAll(".tipo-campo")
                .forEach((el) => (el.style.display = "none"));

            if (tipo === "geocerca") {
                document.getElementById("geocercaConfig").style.display =
                    "block";
            } else if (tipo === "tiempo") {
                //document.getElementById('campo-tiempo').style.display = 'block';
            }
        });
}

function abrirGeocerca() {
    const url = "/configurar-geocerca";
    const win = window.open(url, "ConfigurarGeocerca", "width=800,height=600");
}
function formatDateForInput(dateString) {
    if (!dateString) return "";
    const date = new Date(dateString);

    if (isNaN(date.getTime())) return "";

    const year = date.getFullYear();
    const month = `0${date.getMonth() + 1}`.slice(-2);
    const day = `0${date.getDate()}`.slice(-2);
    const hours = `0${date.getHours()}`.slice(-2);
    const minutes = `0${date.getMinutes()}`.slice(-2);

    return `${year}-${month}-${day}T${hours}:${minutes}`;
}
function setGeocercaData(lat, lng, radio) {
    document.getElementById("geocerca_lat").value = lat;
    document.getElementById("geocerca_lng").value = lng;
    document.getElementById("geocerca_radio").value = radio;

    alert("Geocerca guardada correctamente");
}
document
    .getElementById("btnGuardarContenedores")
    .addEventListener("click", function () {
        let idconvoy = document.getElementById("id_convoy").value;
        let finicio = document.getElementById("fecha_inicio").value;
        let ffin = document.getElementById("fecha_fin").value;
        let nombre = document.getElementById("nombre").value;
        let tipo_disolucion = document.getElementById("tipo_disolucion").value;
        let geocerca_lat = document.getElementById("geocerca_lat").value;
        let geocerca_lng = document.getElementById("geocerca_lng").value;
        let geocerca_radio = document.getElementById("geocerca_radio").value;

        document.getElementById("ItemsSelects").value = ItemsSelects.join(";");

        if (!ItemsSelects || ItemsSelects.length === 0) {
            alert("Por favor, seleccione al menos un contenedor.");
            return;
        }

        const numeroConvoy = document.getElementById("numero_convoy").value;
        //  let idconvoy = document.getElementById('id_convoy').value;
        document.getElementById("ItemsSelects").value = ItemsSelects.join(";");

        if (!ItemsSelects || ItemsSelects.length === 0) {
            alert("Por favor, seleccione al menos un contenedor.");
            return;
        }
        let datap = {
            fecha_inicio: finicio,
            fecha_fin: ffin,
            items_selects: ItemsSelects,
            nombre: nombre,
            idconvoy: idconvoy,
            numero_convoy: numeroConvoy,
            tipo_disolucion: tipo_disolucion,
            geocerca_lat: geocerca_lat,
            geocerca_lng: geocerca_lng,
            geocerca_radio: geocerca_radio,
        };

        fetch(`/coordenadas/conboys/agregar`, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify(datap),
        })
            .then(async (res) => {
                if (!res.ok) {
                    const errorText = await res.text();
                    throw new Error(
                        errorText || "Error desconocido del servidor",
                    );
                }
                return res.json();
            })
            .then((data) => {
                if (data.success) {
                    document.getElementById("modalBuscarConvoy").style.display =
                        "none";

                    Swal.fire({
                        title: "Guardado correctamente",
                        text: data.message + " " + data.no_conboy,
                        icon: "success",
                        confirmButtonText: "Aceptar",
                    });

                    const modal = bootstrap.Modal.getInstance(
                        document.getElementById("modalBuscarConvoy"),
                    );
                    modal.hide();
                } else {
                    Swal.fire({
                        title: "Error",
                        text: data.message || "No se pudo guardar.",
                        icon: "error",
                        confirmButtonText: "Cerrar",
                    });
                }
            })
            .catch((error) => {
                console.error("Error en la petición:", error);

                Swal.fire({
                    title: "Error inesperado",
                    text: error.message,
                    icon: "error",
                    confirmButtonText: "Cerrar",
                });
            });
    });

function definirTable() {
    const columnDefs = [
        {
            headerName: "",
            field: "checkbox",
            headerCheckboxSelection: true,
            checkboxSelection: true,
            width: 50,
            pinned: "left",
            suppressSizeToFit: true,
            resizable: false,
        },

        {
            headerName: "Convoy",
            field: "no_conboy",
            sortable: true,
            filter: true,
        },
        {
            headerName: "Descripcion",
            field: "nombre",
            sortable: true,
            filter: true,
        },
        {
            headerName: "Fecha Inicio",
            field: "fecha_inicio",
            sortable: true,
            filter: true,
        },
        {
            headerName: "Fecha Fin",
            field: "fecha_fin",
            sortable: true,
            filter: true,
        },

        {
            headerName: "Acciones",

            cellRenderer: function (params) {
                const container = document.createElement("div");

                const data = params.data;

                const btnEditar = document.createElement("button");
                btnEditar.innerHTML = `<i class="fas fa-edit text-white"></i>`;

                btnEditar.title = "Editar";
                btnEditar.classList.add("btn", "btn-sm", "btn-warning", "me-1");
                btnEditar.onclick = function () {
                    document.getElementById("filtroModalLabel").textContent =
                        "Editar Conboy";
                    limpCampos();
                    //document.getElementById("contenedoresTablaSection").style.display = "block";

                    document.getElementById("id_convoy").value = data.id;
                    document.getElementById("no_convoy").value =
                        data.no_conboy || "";
                    document.getElementById("fecha_inicio").value =
                        formatDateForInput(data.fecha_inicio) || "";
                    document.getElementById("fecha_fin").value =
                        formatDateForInput(data.fecha_fin) || "";
                    document.getElementById("nombre").value = data.nombre || "";
                    document.getElementById("tipo_disolucion").value =
                        data.tipo_disolucion || "";
                    document.getElementById("geocerca_lat").value =
                        data.geocerca_lat || "";
                    document.getElementById("geocerca_lng").value =
                        data.geocerca_lng || "";
                    document.getElementById("geocerca_radio").value =
                        data.geocerca_radio || "";

                    document.getElementById("formFiltros").dataset.editId =
                        data.id;

                    BloquearHabilitarEdicion(data.BlockUser);

                    const contenedoresFiltrados = contenedoresGuardados.filter(
                        (item) => item.conboy_id == data.id,
                    );

                    llenarTablaContenedores(
                        contenedoresFiltrados,
                        data.BlockUser,
                    );
                    const modal = new bootstrap.Modal(
                        document.getElementById("CreateModal"),
                    );
                    modal.show();
                };

                const btnCompartir = document.createElement("button");
                btnCompartir.innerText = "🔗";
                btnCompartir.title = "Compartir";
                btnCompartir.classList.add("btn", "btn-sm", "btn-info");
                btnCompartir.onclick = function () {
                    // const link = `${window.location.origin}/coordenadas/conboys/compartir/${data.no_conboy}/${data.id}`;
                    //
                    document.getElementById("wmensajeText").innerText =
                        `Se comparte el siguiente no. de Convoy:: ${data.no_conboy}`;

                    //
                    const mensaje = `Te comparto el convoy: ${data.no_conboy}`;

                    document.getElementById("mensajeText").innerText = mensaje;

                    const textoWhatsapp = ` ${mensaje}`;
                    document.getElementById("whatsappLink").href =
                        `https://wa.me/?text=${encodeURIComponent(textoWhatsapp)}`;

                    document.getElementById("modalCoordenadas").style.display =
                        "block";
                };

                const btnRastreo = document.createElement("button");
                btnRastreo.type = "button";
                btnRastreo.classList.add("btn", "btn-sm", "btn-success");
                btnRastreo.title = "Rastrear contenedor";
                btnRastreo.id = "btnRastreo";

                btnRastreo.innerHTML = `<i class="fa fa-shipping-fast me-1"></i>`;

                btnRastreo.onclick = function () {
                    const contenedoresDelConvoy = contenedoresGuardadosTodos
                        .filter((c) => c.conboy_id === data.id)
                        .map((c) => c.num_contenedor);
                    const listaStr = contenedoresDelConvoy.join(" / ");
                    let tipos = "Convoy: " + data.no_conboy;
                    abrirMapaEnNuevaPestana(listaStr, tipos);
                };

                const btnEstatus = document.createElement("button");
                btnEstatus.type = "button";
                btnEstatus.classList.add(
                    "btn",
                    "btn-sm",
                    "btn-outline-primary",
                );
                btnEstatus.title = "Cambio estatus";
                btnEstatus.id = "btnEstatus";
                btnEstatus.innerHTML = `<i class="fa fa-sync-alt me-1"></i>`;
                btnEstatus.setAttribute("data-id", data.id);

                btnEstatus.onclick = function () {
                    const modalElement = document.getElementById(
                        "modalCambiarEstatus",
                    );
                    modalElement.setAttribute("data-id", this.dataset.id);

                    const modal = new bootstrap.Modal(
                        document.getElementById("modalCambiarEstatus"),
                    );

                    modal.show();
                };

                container.appendChild(btnEditar);
                container.appendChild(btnCompartir);
                container.appendChild(btnRastreo);
                container.appendChild(btnEstatus);

                return container;
            },
        },
    ];

    function onSelectionChanged() {
        const selectedRows = gridApi.getSelectedRows();
        const btn = document.getElementById("btnRastrearconboysSelec");
        if (selectedRows.length > 1) {
            btn.classList.remove("d-none");
        } else {
            btn.classList.add("d-none");
        }
    }
    document
        .getElementById("btnRastrearconboysSelec")
        .addEventListener("click", () => {
            const selectedRows = gridApi.getSelectedRows();

            const ids = selectedRows.map((row) => row.id);

            if (ids.length > 1) {
                const query = new URLSearchParams({
                    ids: ids.join(","),
                }).toString();
                const url = `/coordenadas/mapa_rastreo_varios?${query}`;
                window.open(url, "_blank");
            }
        });

    function llenarTablaContenedores(contenedores, val) {
        const tabla = document.getElementById("tablaContenedoresBody"); // tbody
        tabla.innerHTML = "";

        seleccionados.length = 0;
        ItemsSelects.length = 0;

        contenedores.forEach((item, i) => {
            const row = document.createElement("tr");
            let botonEliminar = "";
            if (!val) {
                botonEliminar = `
                    <button type="button" class="btn btn-sm btn-danger" onclick="confirmarEliminacion('${item.id_contenedor}', '${item.conboy_id}', this,${i})">
                        <i class="bi bi-trash"></i>
                    </button>
                `;
            }

            row.innerHTML = `
            <td>${item.num_contenedor}</td>
            <td>${botonEliminar}</td>
        `;

            tabla.appendChild(row);

            seleccionados.push(item.num_contenedor);
            ItemsSelects.push(
                `${item.num_contenedor}|${item.id_contenedor}|${item.imei}`,
            );
        });
    }

    const gridOptions = {
        columnDefs: columnDefs,
        pagination: true,
        paginationPageSize: 100,
        rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1,
        },
        onSelectionChanged: onSelectionChanged,
    };

    const myGridElement = document.querySelector("#myGrid");
    gridApi = agGrid.createGrid(myGridElement, gridOptions);

    cargaConboys();

    function cargaConboys() {
        const overlay = document.getElementById("gridLoadingOverlay");
        overlay.style.display = "flex";

        gridApi.setGridOption("rowData", []);

        fetch("/coordenadas/conboys/getconboys")
            .then((response) => response.json())
            .then((data) => {
                contenedoresGuardados = data.dataConten;
                contenedoresGuardadosTodos = data.dataConten2;
                const rowData = data.data;
                gridApi.setGridOption("rowData", rowData);
            })
            .catch((error) => {
                console.error(
                    "❌ Error al obtener la lista de convoys:",
                    error,
                );
            })
            .finally(() => {
                overlay.style.display = "none";
            });
    }
}

function definirTable2() {
    const columnDefs2 = [
        {
            headerName: "",
            field: "checkbox",
            headerCheckboxSelection: true,
            checkboxSelection: true,
            width: 50,
            pinned: "left",
            suppressSizeToFit: true,
            resizable: false,
        },

        {
            headerName: "Tipo",
            field: "ubicacionable_type",
            sortable: true,
            filter: true,
        },
        {
            headerName: "Contenedor",
            field: "contenedor",
            sortable: true,
            filter: true,
        },
        {
            headerName: "# Convoy",
            field: "no_conboy",
            sortable: true,
            filter: true,
        },
        {
            headerName: "Info Extra",
            field: "cliente",
            sortable: true,
            filter: true,
        },

        {
            headerName: "Acciones",

            cellRenderer: function (params) {
                const container = document.createElement("div");
                //  container.classList.add("d-flex", "flex-wrap", "gap-1", "justify-content-start");
                const data = params.data;

                // Botón Editar
                const btnHistorial = document.createElement("button");
                btnHistorial.innerHTML = `<i class="fas fa-history text-white"></i>`;

                btnHistorial.title = "Historial";
                btnHistorial.classList.add(
                    "btn",
                    "btn-sm",
                    "btn-warning",
                    "me-1",
                );
                btnHistorial.onclick = function () {
                    const url = `/mapa-comparacion?idSearch=${data.ubicacionable_id}&type=${data.ubicacionable_type}&latitud_seguimiento=${0}&longitud_seguimiento=${0}&contenedor=${data.contenedor}`;
                    // Abrir
                    window.open(url, "_blank");
                };

                // Botón Compartir
                const btnCompartir = document.createElement("button");
                btnCompartir.innerText = "🔗";
                btnCompartir.title = "Compartir";
                btnCompartir.classList.add("btn", "btn-sm", "btn-info");
                btnCompartir.onclick = function () {};

                container.appendChild(btnHistorial);
                container.appendChild(btnCompartir);

                return container;
            },
        },
    ];

    const gridOptions2 = {
        columnDefs: columnDefs2,
        pagination: true,
        paginationPageSize: 100,
        rowSelection: "multiple",
        defaultColDef: {
            resizable: true,
            flex: 1,
        },
    };

    const myGridElement2 = document.querySelector("#myGridConvoyFinalizados");
    gridApi2 = agGrid.createGrid(myGridElement2, gridOptions2);

    cargaConboys2(inicio.format("YYYY-MM-DD"), fin.format("YYYY-MM-DD"));
}

document
    .getElementById("formFiltros")
    .addEventListener("submit", function (event) {
        event.preventDefault();
        let idconvoy = document.getElementById("id_convoy").value;
        let finicio = document.getElementById("fecha_inicio").value;
        let ffin = document.getElementById("fecha_fin").value;
        let nombre = document.getElementById("nombre").value;
        let tipo_disolucion = document.getElementById("tipo_disolucion").value;
        let geocerca_lat = document.getElementById("geocerca_lat").value;
        let geocerca_lng = document.getElementById("geocerca_lng").value;
        let geocerca_radio = document.getElementById("geocerca_radio").value;

        document.getElementById("ItemsSelects").value = ItemsSelects.join(";");

        if (!ItemsSelects || ItemsSelects.length === 0) {
            alert("Por favor, seleccione al menos un contenedor.");
            return;
        }
        let datap = {
            fecha_inicio: finicio,
            fecha_fin: ffin,
            items_selects: ItemsSelects,
            nombre: nombre,
            idconvoy: idconvoy,
            tipo_disolucion: tipo_disolucion,
            geocerca_lat: geocerca_lat,
            geocerca_lng: geocerca_lng,
            geocerca_radio: geocerca_radio,
        };
        let urlSave = "/coordenadas/conboys/store";

        if (idconvoy != "") {
            urlSave = "/coordenadas/conboys/update";
        }

        saveconvoys(datap, urlSave);
    });
document
    .getElementById("btnGuardarCambios")
    .addEventListener("click", function () {
        const form = document.getElementById("formCambiarEstatus");
        const formData = new FormData(form);
        const modal = document.getElementById("modalCambiarEstatus");
        const id = modal.getAttribute("data-id");
        formData.append("idconvoy", id);

        fetch("/coordenadas/conboys/estatus", {
            method: "POST",
            body: formData,
            headers: {
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
        })
            .then((response) => response.json())
            .then((data) => {
                if (data.success) {
                    Swal.fire({
                        title: "Cambio de Estatus realizado correctamente",
                        text: data.message + " " + data.no_conboy,
                        icon: "success",
                        confirmButtonText: "Aceptar",
                        timer: 1500,
                    }).then(() => {
                        setTimeout(() => {
                            window.location.reload();
                        }, 300);
                    });
                    const modal = bootstrap.Modal.getInstance(
                        document.getElementById("modalCambiarEstatus"),
                    );
                    modal.hide();
                } else {
                    // Mostrar errores
                    alert("Ocurrió un error al guardar");
                }
            })
            .catch((error) => {
                console.error("Error en el envío AJAX:", error);
            });
    });

function saveconvoys(datap, urlSave) {
    let responseOk = false;
    fetch(urlSave, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify(datap),
    })
        .then((res) => {
            if (!res.ok) throw new Error("Error en la respuesta de red");
            return res.json();
        })
        .then((data) => {
            console.log("convoy creado :", data);

            document.getElementById("no_convoy").value = data.no_convoy;

            const modalElement = document.getElementById("CreateModal");
            const filtroModal =
                bootstrap.Modal.getInstance(modalElement) ||
                new bootstrap.Modal(modalElement);
            filtroModal.hide();

            Swal.fire({
                title: "Guardado correctamente",
                text: data.message + " " + data.no_conboy,
                icon: "success",
                confirmButtonText: "Aceptar",
                timer: 1500,
            }).then(() => {
                setTimeout(() => {
                    window.location.reload();
                }, 300);
            });
        })
        .catch((error) => {
            console.error("Error al guardar un conboy:", error);
        });
}

function mostrarSugerencias() {
    const input = document.getElementById("contenedor-input");
    const filtro = input.value.trim().toUpperCase();
    const sugerenciasDiv = document.getElementById("sugerencias");
    sugerenciasDiv.innerHTML = "";

    if (filtro.length === 0) {
        sugerenciasDiv.style.display = "none";
        return;
    }

    const filtrados = contenedoresDisponibles.filter(
        (c) =>
            (c.contenedor || "").toUpperCase().includes(filtro) &&
            !seleccionados.includes(c.contenedor),
    );

    filtrados.forEach((c) => {
        const item = document.createElement("div");
        item.textContent = c.contenedor;
        item.style.padding = "5px";
        item.style.cursor = "pointer";
        item.onclick = () => seleccionarContenedor(c.contenedor);
        sugerenciasDiv.appendChild(item);
    });

    sugerenciasDiv.style.display = filtrados.length ? "block" : "none";
}

function seleccionarContenedor(valor) {
    seleccionados.push(valor);
    const contenedorData = contenedoresDisponibles.find(
        (c) => c.contenedor === valor,
    );

    ItemsSelects.push(
        valor + "|" + contenedorData.id_contenedor + "|" + contenedorData.imei,
    );
    document.getElementById("contenedor-input").value = "";
    document.getElementById("sugerencias").style.display = "none";
    actualizarVista();
}

function agregarContenedor() {
    const input = document.getElementById("contenedor-input");
    const valor = input.value.trim().toUpperCase();
    if (
        valor &&
        contenedoresDisponibles.includes(valor) &&
        !seleccionados.includes(valor)
    ) {
        seleccionados.push(valor);

        input.value = "";
        actualizarVista();
    }
}

function eliminarContenedor(idx) {
    seleccionados.splice(idx, 1);
    ItemsSelects.splice(idx, 1);
    actualizarVista();
}

function actualizarVista() {
    const tbody = document.querySelector("#tablaContenedores tbody");
    tbody.innerHTML = "";
    seleccionados.forEach((cont, i) => {
        const row = document.createElement("tr");
        row.innerHTML = `

            <td>${cont}</td>
            <td>
                <button type="button"
                        class="btn btn-sm btn-danger"
                        onclick="eliminarContenedor(${i})">
                     <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById("contenedores").value = seleccionados.join(";");
    document.getElementById("ItemsSelects").value = ItemsSelects.join(";");
}

function mostrarSugerencias2() {
    const input = document.getElementById("contenedor-input2");
    const filtro = input.value.trim().toUpperCase();
    const sugerenciasDiv = document.getElementById("sugerencias2");
    sugerenciasDiv.innerHTML = "";

    if (filtro.length === 0) {
        sugerenciasDiv.style.display = "none";
        return;
    }

    const filtrados = contenedoresDisponibles.filter(
        (c) =>
            (c.num_contenedor || "").toUpperCase().includes(filtro) &&
            !seleccionados.includes(c.num_contenedor),
    );

    filtrados.forEach((c) => {
        const item = document.createElement("div");
        item.textContent = c.num_contenedor;
        item.style.padding = "5px";
        item.style.cursor = "pointer";
        item.onclick = () => seleccionarContenedor2(c.num_contenedor);
        sugerenciasDiv.appendChild(item);
    });

    sugerenciasDiv.style.display = filtrados.length ? "block" : "none";
}
function seleccionarContenedor2(valor) {
    seleccionados.push(valor);
    const contenedorData = contenedoresDisponibles.find(
        (c) => c.num_contenedor === valor,
    );
    if (typeof contenedorData !== "undefined") {
        ItemsSelects.push(
            valor +
                "|" +
                contenedorData.id_contenedor +
                "|" +
                contenedorData.imei,
        );
        document.getElementById("contenedor-input2").value = "";
        document.getElementById("sugerencias2").style.display = "none";
        actualizarVista2();
    }
}

function agregarContenedor2() {
    const input = document.getElementById("contenedor-input2");
    const valor = input.value.trim().toUpperCase();
    if (
        valor &&
        contenedoresDisponibles.includes(valor) &&
        !seleccionados.includes(valor)
    ) {
        seleccionados.push(valor);

        input.value = "";
        actualizarVista2();
    }
}

function actualizarVista2() {
    const tbody = document.querySelector("#tablaContenedoresBuscar tbody");
    tbody.innerHTML = "";
    seleccionados.forEach((cont, i) => {
        const row = document.createElement("tr");
        row.innerHTML = `

            <td>${cont}</td>
            <td>
                <button type="button"
                        class="btn btn-sm btn-danger"
                        onclick="eliminarContenedor2(${i})">
                     <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(row);
    });

    document.getElementById("contenedores").value = seleccionados.join(";");
    document.getElementById("ItemsSelects").value = ItemsSelects.join(";");
}

function eliminarContenedor2(idx) {
    seleccionados.splice(idx, 1);
    ItemsSelects.splice(idx, 1);
    actualizarVista2();
}

function cambiarTab(tabId) {
    // Ocultamos todos los divs con clase 'tab-content'
    const tabs = document.querySelectorAll(".tab-content");
    tabs.forEach((tab) => {
        tab.style.display = "none";
    });

    // Mostramos solo el que corresponde
    const tabToShow = document.getElementById("tab-" + tabId);
    if (tabToShow) {
        tabToShow.style.display = "block";
    } else {
        console.error(`No se encontró el tab: tab-${tabId}`);
    }
}

function mostrarTab(tab, event) {
    event.preventDefault();

    // Ocultar ambos
    document.getElementById("tab-mail").style.display = "none";
    document.getElementById("tab-whatsapp").style.display = "none";

    // Quitar clase activa
    const tabs = document.querySelectorAll(".nav-link");
    tabs.forEach((el) => el.classList.remove("active"));

    // Mostrar el tab seleccionado
    document.getElementById(`tab-${tab}`).style.display = "block";

    // Activar tab
    event.currentTarget.classList.add("active");
}

function cerrarModal() {
    const modal = document.getElementById("modalCoordenadas");
    limpCampos();
    if (modal) {
        modal.style.display = "none";
    }

    // Eliminar el fondo oscuro si existe
    const backdrop = document.querySelector(".modal-backdrop");
    if (backdrop) {
        backdrop.remove();
    }

    // Quitar la clase modal-open del body
    document.body.classList.remove("modal-open");
    document.body.style.overflow = ""; // restaurar scroll
}

function limpCampos() {
    // document.getElementById('linkMail').innerText = "";

    document.getElementById("mensajeText").innerText = "";
    document.getElementById("correoDestino").value = "";
    document.getElementById("wmensajeText").innerText = "";
    // document.getElementById("linkWhatsapp").value = "";
    document.getElementById("whatsappLink").href = "#";
    document.getElementById("idAsignacionCompartir").value = "";

    document.getElementById("idCotizacionCompartir").value = "";
    document.getElementById("idAsignacionCompartir").value = "";
}
function copiarDesdeInput(inputId) {
    const input = document.getElementById(inputId);
    input.select();
    input.setSelectionRange(0, 99999);
    document.execCommand("copy");
    alert("¡Enlace copiado!");
}

function enviarMailCoordenadas() {
    const mensaje = document.getElementById("mensajeText").innerText;
    const asunto = mensaje;

    const link = "";
    const correo = document.getElementById("correoDestino").value;

    fetch("/coordenadas/cotizaciones/mail-coordenadas", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                .content,
        },
        body: JSON.stringify({
            correo: correo,
            asunto: asunto,
            mensaje: mensaje,
            link: link,
        }),
    })
        .then((res) => res.json())
        .then((data) => alert("Correo enviado ✅"))
        .catch((err) => console.error("Error:", err));
}
function guardarYAbrirWhatsApp(event) {
    event.preventDefault(); // Evita que el enlace se abra inmediatamente

    window.open(document.getElementById("whatsappLink").href, "_blank");
}

function limpiarFormularioConvoy2() {
    // Limpiar tabla de contenedores
    const tbody = document.getElementById("tablaContenedoresBuscar");
    tbody.innerHTML = "";

    // Limpiar inputs
    document.getElementById("numero_convoy").value = "";
    document.getElementById("id_convoy").value = "";

    // Limpiar selects ocultos o arrays usados
    ItemsSelects.length = 0; // Si es global, la reinicias
    document.getElementById("ItemsSelects").value = "";

    // Ocultar modal si es necesario
    document.getElementById("modalBuscarConvoy").style.display = "none";

    // Limpiar también posibles mensajes o alertas
    // document.getElementById('resultadoBusquedaConvoy')?.innerHTML = '';
}

function confirmarEliminacion(idContenedor, idConvoy, boton, idx) {
    Swal.fire({
        title: "¿Eliminar contenedor?",
        text: "Esta acción no se puede deshacer.",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Sí, eliminar",
        cancelButtonText: "Cancelar",
        confirmButtonColor: "#d33",
        cancelButtonColor: "#6c757d",
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(
                `/coordenadas/conboys/eliminar-contenedor/${idContenedor}/${idConvoy}`,
                {
                    method: "DELETE",
                    headers: {
                        "X-CSRF-TOKEN": document.querySelector(
                            'meta[name="csrf-token"]',
                        ).content,
                        Accept: "application/json",
                    },
                },
            )
                .then((response) => {
                    if (!response.ok) throw new Error("Error al eliminar");
                    return response.json();
                })
                .then((data) => {
                    Swal.fire(
                        "¡Eliminado!",
                        "El contenedor ha sido eliminado.",
                        "success",
                    );

                    eliminarContenedor(idx);
                    const fila = boton.closest("tr");
                    fila.remove();
                })
                .catch((error) => {
                    console.error(error);
                    Swal.fire(
                        "Error",
                        "No se pudo eliminar el contenedor.",
                        "error",
                    );
                });
        }
    });
}

//termina tab convoys
