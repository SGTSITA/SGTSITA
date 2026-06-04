let equiposSearch = [];
let rastreosActivos = {};
let map;
const estadosLi = {};
let markers = [];
let elementoPanelRastro = [];
let catalogoBusqueda = [];
let contenedoresDisponiblesAll = [];
let mapaAjustado = false;

let valorAnteriorFiltro;

let detalleConvoys;
let contenedoresDisponibles = [];
let directionsService = null;
let directionsRenderer = [];

let ItemsSelectsID = {};
let intervalIdsID = {};

let contenedoresGuardados;
let contenedoresGuardadosTodos;
//let contenedoresDisponibles = [];
let userBloqueo = false;
const seleccionados = [];
const ItemsSelects = [];

let rastreoActivo = false;
let requestEnCurso = false;
let timeoutRastreo = null;

const INTERVALO_RASTREO = 30000;

let inicio = "";
let fin = "";
let catalogoBusquedaOriginal = [];

let mostrarTodos = false;
let tipoVistaMarker = "default";
let intervaloRastreo = null;

const input = document.getElementById("buscadorGeneral");
const resultados = document.getElementById("resultadosBusqueda");
const chipContainer = document.getElementById("chipsBusqueda");

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
let cargandoRastreo = false;
async function cargarinicial() {
    if (cargandoRastreo) return;

    cargandoRastreo = true;

    try {
        const params = new URLSearchParams();

        const fechaSalida = $("#filtroFechaSalida").val();

        if (fechaSalida) {
            params.append("fecha_salida", fechaSalida);
        }

        const response = await fetch(
            `/coordenadas/contenedor/searchEquGps?${params.toString()}`,
        );

        if (!response.ok) {
            throw new Error("Error al consultar rastreo");
        }

        const data = await response.json();

        catalogoBusquedaOriginal.length = 0;

        contenedoresDisponibles = data.datos ?? [];
        detalleConvoys = data.dataConten ?? [];
        contenedoresDisponiblesAll = data.datosAll ?? [];
        equiposSearch = data.equiposAll ?? [];

        // Convoys
        (data.conboys ?? []).forEach((c) => {
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
                empresas: c.empresas ? c.empresas.split(",").map(Number) : [],
                clientes: c.clientes ? c.clientes.split(",").map(Number) : [],
                lineas: c.lineas ? c.lineas.split(",").map(Number) : [],
            });
        });

        // Contenedores
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

        // Equipos
        (data.equipos ?? []).forEach((eq) => {
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

        actualizarFiltrosDisponiblesPanel();
    } catch (error) {
        console.error("Error al traer coordenadas:", error);
    } finally {
        cargandoRastreo = false;
    }
}
$("#filtroFechaSalida").on("change", async function () {
    await cargarinicial();
    aplicarFiltrosPanel();
    limpiarMapa();
});
$("#btnLimpiarFechaSalida").on("click", async function () {
    $("#filtroFechaSalida").val("");

    await cargarinicial();
    aplicarFiltrosPanel();
    limpiarMapa();
});
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

function obtenerTabActivo() {
    const tabActivo = document.querySelector("#filtroTabs .nav-link.active");
    return tabActivo ? tabActivo.getAttribute("data-bs-target") : null;
}

let filtroActivo = null;

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

function validarTipo(items) {
    let tabx = items.tipo;
    let labelMuestra = items.label;
    let value = items.value;

    if (Array.isArray(ItemsSelectsID[items.id + "|" + items.value])) {
        ItemsSelectsID[items.id + "|" + items.value].length = 0;
    } else {
        ItemsSelectsID[items.id + "|" + items.value] = [];
    }
    let valorFinal = items.value;

    if (items.value_chasis && items.value_chasis !== "NO DISPONIBLE|") {
        valorFinal = items.value + ";" + items.value_chasis;
    }

    ItemsSelectsID[items.id + "|" + items.value] = valorFinal;

    if (tabx === "Convoy") {
        ItemsSelectsID[items.id + "|" + items.value] = obtenerImeisPorConvoyId(
            items.id,
        );
    }
    mapaAjustado = false;

    elementoPanelRastro.push({
        id: items.id,
        tipo: tabx,
        value: items.value,
        label: labelMuestra,
    });

    catalogoBusqueda = catalogoBusqueda.filter(
        (itemFilter) =>
            itemFilter.id !== items.id && itemFilter.value !== items.value,
    );

    const listaPanel = document.getElementById("ElementosRastreoPanel");

    elementoPanelRastro.forEach((item) => {
        const existe = Array.from(listaPanel.children).some(
            (li) => li.dataset.valor === item.id + "|" + item.tipo,
        );

        if (!existe) {
            const li = document.createElement("li");
            li.classList.add("list-group-item", "p-0", "mb-2");
            li.dataset.valor = item.id + "|" + item.tipo;

            const randomColor = getRandomColor();
            li.style.backgroundColor = randomColor;
            li.style.color = "white";
            // Texto a la izquierda

            estadosLi[`${item.id}|${item.tipo}`] = false;

            const content = document.createElement("div");
            content.classList.add(
                "d-flex",
                "justify-content-between",
                "align-items-center",
                "p-2",
            );

            const spanTexto = document.createElement("span");
            spanTexto.textContent = `${item.tipo} #${item.label}`;
            content.appendChild(spanTexto);

            //validar primero si es convoy
            if (item.tipo === "Convoy") {
                const switchHeader = document.createElement("div");
                switchHeader.style.backgroundColor = "rgba(0,0,0,0.3)";
                switchHeader.style.color = "white";
                switchHeader.style.display = "flex";
                switchHeader.style.alignItems = "center";
                switchHeader.style.justifyContent = "center";
                switchHeader.style.height = "28px";
                switchHeader.style.width = "100%";

                const inputSwitch = document.createElement("input");
                inputSwitch.type = "checkbox";
                inputSwitch.classList.add("form-check-input", "me-2");

                const label = document.createElement("span");
                label.textContent = "Rastreo Individual";

                inputSwitch.addEventListener("change", function () {
                    if (this.checked) {
                        label.textContent = "Rastrear Grupo";
                        estadosLi[`${item.id}|${item.tipo}`] = this.checked;
                    } else {
                        label.textContent = "Rastreo Individual";
                        estadosLi[`${item.id}|${item.tipo}`] = this.checked;
                    }

                    actualizarUbicacion(
                        ItemsSelectsID[items.id + "|" + items.value],
                        tabx,
                        items.id + "|" + items.value + "|" + item.tipo,
                        labelMuestra,
                        value,
                        map,
                        items.id,
                        randomColor,
                        estadosLi[`${item.id}|${item.tipo}`],
                    );

                    intervalIdsID[`${item.id}|${item.value}`] = setInterval(
                        () => {
                            actualizarUbicacion(
                                ItemsSelectsID[items.id + "|" + items.value],
                                tabx,
                                items.id + "|" + items.value + "|" + item.tipo,
                                labelMuestra,
                                value,
                                map,
                                items.id,
                                randomColor,
                                estadosLi[`${item.id}|${item.tipo}`],
                            );
                            rastreosActivos[
                                `${item.id}|${item.value}|${item.tipo}`
                            ] = true;
                        },
                        5000,
                    );
                });

                switchHeader.appendChild(inputSwitch);
                switchHeader.appendChild(label);
                li.appendChild(switchHeader);
            }

            // Botón pausar/reanudar
            const btnPausar = document.createElement("button");
            btnPausar.classList.add("btn", "btn-sm", "btn-warning", "p-1");
            btnPausar.style.width = "35px";
            btnPausar.style.height = "35px";
            btnPausar.style.display = "flex";
            btnPausar.style.alignItems = "center";
            btnPausar.style.justifyContent = "center";
            btnPausar.innerHTML = '<i class="bi bi-pause-fill"></i>';
            btnPausar.id = `${item.id}|${item.value}`;

            // Evento click pausar/reanudar
            btnPausar.addEventListener("click", () => {
                const icon = btnPausar.querySelector("i");
                if (btnPausar.classList.contains("btn-warning")) {
                    btnPausar.classList.replace("btn-warning", "btn-success");
                    icon.classList.replace("bi-pause-fill", "bi-play-fill");
                    console.log(`${item.id}|${item.value} pausado`);
                } else {
                    btnPausar.classList.replace("btn-success", "btn-warning");
                    icon.classList.replace("bi-play-fill", "bi-pause-fill");
                    console.log(`${item.id}|${item.value} reanudado`);
                }

                if (intervalIdsID[`${item.id}|${item.value}`]) {
                    clearInterval(intervalIdsID[`${item.id}|${item.value}`]);
                    intervalIdsID[`${item.id}|${item.value}`] = null;
                    estadosLi[`${item.id}|${item.tipo}`] = false;
                    rastreosActivos[`${item.id}|${item.value}|${item.tipo}`] =
                        false;
                } else {
                    actualizarUbicacion(
                        ItemsSelectsID[items.id + "|" + items.value],
                        tabx,
                        items.id + "|" + items.value + "|" + item.tipo,
                        labelMuestra,
                        value,
                        map,
                        items.id,
                        randomColor,
                        estadosLi[`${item.id}|${item.tipo}`],
                    );

                    intervalIdsID[`${item.id}|${item.value}`] = setInterval(
                        () => {
                            actualizarUbicacion(
                                ItemsSelectsID[items.id + "|" + items.value],
                                tabx,
                                items.id + "|" + items.value + "|" + item.tipo,
                                labelMuestra,
                                value,
                                map,
                                items.id,
                                randomColor,
                                estadosLi[`${item.id}|${item.tipo}`],
                            );
                            rastreosActivos[
                                `${item.id}|${item.value}|${item.tipo}`
                            ] = true;
                        },
                        5000,
                    );
                }
            });

            // Botón eliminar
            const btnEliminar = document.createElement("button");
            btnEliminar.classList.add("btn", "btn-sm", "btn-danger", "p-1");
            btnEliminar.style.width = "35px";
            btnEliminar.style.height = "35px";
            btnEliminar.style.display = "flex";
            btnEliminar.style.alignItems = "center";
            btnEliminar.style.justifyContent = "center";
            btnEliminar.innerHTML = '<i class="bi bi-x"></i>';
            // Agregar botones al contenedor

            const btnGroup = document.createElement("div");
            btnGroup.appendChild(btnPausar);
            btnGroup.appendChild(btnEliminar);

            content.appendChild(btnGroup);

            li.appendChild(content);

            // finalmente añadimos a lista
            listaPanel.appendChild(li);

            btnEliminar.addEventListener("click", () => {
                let valorLi = li.dataset.valor;

                let [idStr, tipoStr] = valorLi.split("|");

                rastreosActivos[`${item.id}|${item.value}|${item.tipo}`] =
                    false;
                let elementoEliminado = elementoPanelRastro.find(
                    (item) =>
                        String(item.id) === idStr &&
                        String(item.tipo) === tipoStr,
                );

                if (elementoEliminado) {
                    elementoPanelRastro = elementoPanelRastro.filter(
                        (item) =>
                            String(item.id) !== idStr ||
                            String(item.tipo) !== tipoStr,
                    );

                    if (
                        !catalogoBusqueda.some(
                            (el) =>
                                el.id === elementoEliminado.id &&
                                el.value === elementoEliminado.value,
                        )
                    ) {
                        catalogoBusqueda.push(elementoEliminado);
                    }

                    const claveBase =
                        items.id + "|" + items.value + "|" + item.tipo;
                    let borro = false;

                    clearInterval(intervalIdsID[`${item.id}|${item.value}`]);
                    intervalIdsID[`${item.id}|${item.value}`] = null;
                    estadosLi[`${item.id}|${item.tipo}`] = false;

                    Object.keys(markers).forEach((key) => {
                        if (key.startsWith(claveBase + "|")) {
                            markers[key].setMap(null);
                            delete ItemsSelectsID[markers[key].keyItem];
                            delete markers[key];
                            borro = true;
                        }
                    });

                    if (borro) {
                        li.remove();
                    }

                    //ItemsSelectsID[items.id + "|"+  items.value]
                    console.log(
                        `${elementoEliminado.tipo} #${elementoEliminado.label} eliminado`,
                    );
                }
            });
            //alert('pasa siempre');

            actualizarUbicacion(
                ItemsSelectsID[items.id + "|" + items.value],
                tabx,
                items.id + "|" + items.value + "|" + item.tipo,
                labelMuestra,
                value,
                map,
                items.id,
                randomColor,
                estadosLi[`${item.id}|${item.tipo}`],
            );

            intervalIdsID[`${item.id}|${item.value}`] = setInterval(() => {
                actualizarUbicacion(
                    ItemsSelectsID[items.id + "|" + items.value],
                    tabx,
                    items.id + "|" + items.value + "|" + item.tipo,
                    labelMuestra,
                    value,
                    map,
                    items.id,
                    randomColor,
                    estadosLi[`${item.id}|${item.tipo}`],
                );
                rastreosActivos[`${item.id}|${item.value}|${item.tipo}`] = true;
            }, 5000);
        }
    });
}

function eliminarDelPanel(id) {
    let elementoEliminado = elementoPanelRastro.find((item) => item.id === id);
    if (elementoEliminado) {
        elementoPanelRastro = elementoPanelRastro.filter(
            (item) => item.id !== id,
        );
        li.remove();
        let index = markers.findIndex(
            (m) => m.keyItem === items.id + "|" + items.value + "|" + item.tipo,
        );
        if (index !== -1) {
            markers[index].setMap(null);
            markers.splice(index, 1);
            delete ItemsSelectsID[items.id + "|" + items.value];
        }
        console.log(
            `${elementoEliminado.tipo} #${elementoEliminado.label} eliminado`,
        );
    }
}

input.addEventListener("input", function () {
    const query = this.value.trim().toLowerCase();
    resultados.innerHTML = "";
    chipContainer.innerHTML = "";
    filtroActivo = null;

    if (query.length < 2) {
        //detener();
        // limpiarMarcadores();
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

    // Mostrar chips por tipo
    const tiposUnicos = [...new Set(coincidencias.map((item) => item.tipo))];
    tiposUnicos.forEach((tipo) => {
        const chip = document.createElement("button");
        chip.className =
            "btn btn-outline-secondary btn-sm rounded-pill me-2 mb-1";
        chip.textContent = tipo;
        chip.onclick = () => {
            filtroActivo = tipo;
            //document.getElementById('tituloSeguimiento').textContent = 'Seguimiento '+  tipo;
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
        div.textContent = `${item.label}`;
        div.onclick = () => {
            //document.getElementById('tituloSeguimiento').textContent = 'Seguimiento '+  item.tipo;
            // document.querySelectorAll('#chipsBusqueda .btn').forEach(btn => {
            //       if (btn.textContent.trim() === item.tipo) {
            //         btn.classList.add('active');
            //       } else {
            //         btn.classList.remove('active');
            //       }
            //     });

            //input.value =item.label;
            input.value = "";
            document.getElementById("resultadosBusqueda").innerHTML = "";
            chipContainer.innerHTML = "";
            //input.dispatchEvent(new Event('input'));
            validarTipo(item);
        };
        resultados.appendChild(div);
    });
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
                console.log("Coordenada guardada:");
            } else {
                console.warn("Error al guardar coordenada", data);
            }
        })
        .catch((error) => {
            console.error("Error en la solicitud:", error);
        });
}

// Para detener la actualización con un botón
//document.getElementById('btnDetener').addEventListener('click', function() {
//  detener();
//});

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

            const valor = (v) => v ?? "S/N";

            let infoContenido = `
<div class="tab-pane fade ${index === 0 ? "show active" : ""}"
    id="${tabId}"
    role="tabpanel"
    aria-labelledby="${tabId}-tab">

    <div style="
        font-family: Arial, sans-serif;
        font-size: 12.5px;
        color: #2b2b2b;
        min-width: 360px;
        max-width: 430px;
    ">

        <!-- Cliente -->
        <div style="
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 8px;
        ">
            <div style="font-size: 11px; color:#6c757d; font-weight:700; text-transform:uppercase;">
                Cliente
            </div>
            <div style="font-size: 14px; font-weight: 800; color:#212529;">
                ${valor(info.cliente)}
            </div>
        </div>

        <!-- Ruta -->
        <div style="
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 8px;
            background: white;
        ">
            <div style="font-weight:800; color:#0d6efd; margin-bottom:6px;">
                <i class="fas fa-route"></i> Ruta
            </div>

            <div style="display:grid; grid-template-columns: 80px 1fr; row-gap:5px;">
                <div style="color:#6c757d; font-weight:700;">Origen:</div>
                <div style="font-weight:700;">${valor(info.origen)}</div>

                <div style="color:#6c757d; font-weight:700;">Destino:</div>
                <div style="font-weight:700;">${valor(info.destino)}</div>

                <div style="color:#6c757d; font-weight:700;">Contrato:</div>
                <div>${valor(info.tipo_contrato)}</div>
            </div>
        </div>

        <!-- Fechas -->
        <div style="
            display:grid;
            grid-template-columns: 1fr 1fr;
            gap:8px;
            margin-bottom: 8px;
        ">
            <div style="background:#f8f9fa; border-radius:8px; padding:8px;">
                <div style="font-size:11px; color:#6c757d; font-weight:700;">Fecha Inicio</div>
                <div style="font-weight:800;">${valor(info.fecha_inicio)}</div>
            </div>

            <div style="background:#f8f9fa; border-radius:8px; padding:8px;">
                <div style="font-size:11px; color:#6c757d; font-weight:700;">Fecha Fin</div>
                <div style="font-weight:800;">${valor(info.fecha_fin)}</div>
            </div>
        </div>

        <!-- Contacto / Operador -->
        <div style="
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 10px;
            margin-bottom: 8px;
            background: white;
        ">
            <div style="font-weight:800; color:#198754; margin-bottom:6px;">
                <i class="fas fa-user"></i> Contacto / Operador
            </div>

            <div style="display:grid; grid-template-columns: 110px 1fr; row-gap:5px;">
                <div style="color:#6c757d; font-weight:700;">Contacto:</div>
                <div>${valor(info.cp_contacto_entrega)}</div>

                <div style="color:#6c757d; font-weight:700;">Operador:</div>
                <div style="font-weight:700;">${valor(info.beneficiario)}</div>

                <div style="color:#6c757d; font-weight:700;">Teléfono:</div>
                <div>
                    ${
                        info.telefono_beneficiario
                            ? `<a href="tel:${info.telefono_beneficiario}" style="color:#0d6efd; font-weight:800; text-decoration:none;">
                                ${info.telefono_beneficiario}
                               </a>`
                            : "S/N"
                    }
                </div>
            </div>
        </div>

        <!-- Equipo / GPS -->
        <div style="
            border: 1px solid #e9ecef;
            border-radius: 8px;
            padding: 8px 10px;
            background: white;
        ">
            <div style="font-weight:800; color:#fd7e14; margin-bottom:6px;">
                <i class="fas fa-satellite-dish"></i> Equipo / GPS
            </div>

            <div style="display:grid; grid-template-columns: 90px 1fr; row-gap:5px;">
                <div style="color:#6c757d; font-weight:700;">IMEI:</div>
                <div style="font-family:monospace; font-weight:700;">${valor(info.imei)}</div>

                <div style="color:#6c757d; font-weight:700;">Equipo:</div>
                <div style="font-weight:700;">${valor(info.id_equipo)}</div>

                <div style="color:#6c757d; font-weight:700;">Placas:</div>
                <div style="font-weight:700;">${valor(filtroEqu?.placas)}</div>

                <div style="color:#6c757d; font-weight:700;">IMEI Chasis:</div>
                <div style="font-family:monospace; font-weight:700;">${valor(info.imei_chasis)}</div>

                <div style="color:#6c757d; font-weight:700;">Chasis:</div>
                <div style="font-weight:700;">${valor(info.id_equipo_chasis)}</div>
            </div>
        </div>

    </div>
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
function limpiarMarcadoresItemPrincipal(intemDad) {
    markers.forEach((marker) => marker.setMap(null));
    markers = [];
}

document.addEventListener("DOMContentLoaded", function () {
    const hoy = moment();
    inicio = hoy.clone().subtract(10, "days");
    fin = hoy.clone().add(10, "days");

    $("#daterange").daterangepicker({
        startDate: inicio,
        endDate: fin,
        minDate: inicio,
        maxDate: fin,
        locale: { format: "YYYY-MM-DD" },
        opens: "left",
    });

    cargarinicial();
    cargaConvoysTab();

    $("#daterange").on("apply.daterangepicker", function (ev, picker) {
        const fechaInicio = picker.startDate.format("YYYY-MM-DD");
        const fechaFin = picker.endDate.format("YYYY-MM-DD");

        console.log("Rango seleccionado:", fechaInicio, fechaFin);

        cargaConboys2(fechaInicio, fechaFin);
    });
});

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
                //  container.classList.add("d-flex", "flex-wrap", "gap-1", "justify-content-start");
                const data = params.data;

                // Botón Editar
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

                    // Llenas la tabla con los contenedoresFiltrados
                    llenarTablaContenedores(
                        contenedoresFiltrados,
                        data.BlockUser,
                    );
                    const modal = new bootstrap.Modal(
                        document.getElementById("CreateModal"),
                    );
                    modal.show();
                };

                // Botón Compartir
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

                    //
                    // mail
                    // document.getElementById('linkMail').innerText = link;
                    document.getElementById("mensajeText").innerText = mensaje;
                    // whatsapp
                    // document.getElementById("wmensajeText").innerText = mensaje;
                    // document.getElementById("linkWhatsapp").value = link;

                    const textoWhatsapp = ` ${mensaje}`;
                    document.getElementById("whatsappLink").href =
                        `https://wa.me/?text=${encodeURIComponent(textoWhatsapp)}`;
                    // Mostrar modal
                    document.getElementById("modalCoordenadas").style.display =
                        "block";
                };
                //boton rastreo contenedores de los convoys
                const btnRastreo = document.createElement("button");
                btnRastreo.type = "button";
                btnRastreo.classList.add("btn", "btn-sm", "btn-success");
                btnRastreo.title = "Rastrear contenedor";
                btnRastreo.id = "btnRastreo";

                // Añadir ícono + texto como HTML
                btnRastreo.innerHTML = `<i class="fa fa-shipping-fast me-1"></i>`;

                // Evento onclick personalizado (usa el contenedor que necesitas)
                btnRastreo.onclick = function () {
                    const contenedoresDelConvoy = contenedoresGuardadosTodos
                        .filter((c) => c.conboy_id === data.id)
                        .map((c) => c.num_contenedor);
                    const listaStr = contenedoresDelConvoy.join(" / ");
                    let tipos = "Convoy: " + data.no_conboy;
                    abrirMapaEnNuevaPestana(listaStr, tipos);
                };
                //boton cam cbiar estatus de los convoys
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

//aki empieza la nueva funcionalidad tods

function googleMapsReady() {
    initMap();
}
function initMap() {
    directionsService = new google.maps.DirectionsService();

    map = new google.maps.Map(document.getElementById("map"), {
        center: { lat: 23.7066, lng: -102.3907 },
        zoom: 7,
        gestureHandling: "greedy",
        scrollwheel: true,
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

function createMarkerIconByStatusold(
    status,
    label = "GPS",
    containerColor = "#FF0000",
    size = 44,
) {
    label = String(label ?? "GPS")
        .replace(/[^\w\s\-]/g, "")
        .substring(0, 12);

    const estados = {
        moving: {
            color: "#198754",
            bg: "#198754",
            text: "EN RUTA",
            icon: "🚚",
        },
        parked: {
            color: "#f59f00",
            bg: "#fff3cd",
            text: "DETENIDO",
            icon: "P",
        },
        offline: {
            color: "#6c757d",
            bg: "#e9ecef",
            text: "SIN SEÑAL",
            icon: "?",
        },
    };

    const e = estados[status] || estados.offline;

    const svg = `
    <svg width="170" height="82" viewBox="0 0 170 82" xmlns="http://www.w3.org/2000/svg">

        ${
            status === "moving"
                ? `
        <circle cx="85" cy="40" r="22" fill="${e.color}" opacity="0.25">
            <animate attributeName="r" values="22;38;22" dur="1.2s" repeatCount="indefinite"/>
            <animate attributeName="opacity" values="0.35;0;0.35" dur="1.2s" repeatCount="indefinite"/>
        </circle>`
                : ""
        }

        <rect x="8" y="10" width="154" height="46" rx="23"
              fill="${containerColor}" stroke="${e.color}" stroke-width="5"/>

        <circle cx="34" cy="33" r="17" fill="${e.bg}" stroke="${e.color}" stroke-width="4"/>
        <text x="34" y="39" text-anchor="middle" font-size="18" font-weight="bold" fill="${e.color}">${e.icon}</text>

        <rect x="92" y="17" width="60" height="18" rx="9" fill="${e.color}"/>
        <text x="122" y="30" text-anchor="middle" font-size="9" font-weight="bold" fill="white">${e.text}</text>

        <text x="58" y="47" font-size="11" font-weight="bold" fill="white">${label}</text>

        <path d="M85 76 L73 56 H97 Z" fill="${containerColor}" stroke="${e.color}" stroke-width="5"/>
    </svg>`;

    return {
        url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg),
        scaledSize: new google.maps.Size(170, 82),
        anchor: new google.maps.Point(85, 76),
    };
}

function getEstadoGps(velocidad, lat = null, lng = null) {
    const latNum = Number(lat ?? 0);
    const lngNum = Number(lng ?? 0);
    const velocidadNum = Number(velocidad ?? 0);

    const tieneCoordenadas = latNum !== 0 && lngNum !== 0;

    if (!tieneCoordenadas) {
        return {
            key: "offline",
            textoPanel: "Sin señal",
            textoMarker: "SIN SEÑAL",
            iconoPanel: "⚫",
            iconoMarker: "!",
            claseBootstrap: "secondary",
            color: "#8392ab",
            soft: "#eef0f4",
        };
    }

    if (velocidadNum > 5) {
        return {
            key: "moving",
            textoPanel: "Moviendo",
            textoMarker: "EN RUTA",
            iconoPanel: "🚚",
            iconoMarker: "▶",
            claseBootstrap: "success",
            color: "#2dce89",
            soft: "#d7f7e8",
        };
    }

    return {
        key: "parked",
        textoPanel: "Detenido",
        textoMarker: "DETENIDO",
        iconoPanel: "🅿️",
        iconoMarker: "P",
        claseBootstrap: "warning",
        color: "#fb6340",
        soft: "#ffe3db",
    };
}
function getEstadoMarker(status) {
    const ESTADOS = {
        moving: {
            key: "moving",
            textoMarker: "EN RUTA",
            color: "#2dce89",
            soft: "#d7f7e8",
        },
        parked: {
            key: "parked",
            textoMarker: "DETENIDO",
            color: "#fb6340",
            soft: "#ffe3db",
        },
        offline: {
            key: "offline",
            textoMarker: "SIN SEÑAL",
            color: "#8392ab",
            soft: "#eef0f4",
        },
    };

    return ESTADOS[status] || ESTADOS.offline;
}
function obtenerTextoVelocidad(ubicacion) {
    const velocidad = Number(ubicacion?.velocidad ?? 0);

    if (!Number.isFinite(velocidad) || velocidad <= 0) {
        return "";
    }

    return `${Math.round(velocidad)} km/h`;
}
function obtenerTextoTipoEquipo(item) {
    const tipoEquipo = normalizarTipoEquipoMarker(
        item?.TipoEquipo || item?.ubicacion?.tipoEquipo || "GPS",
    );

    const tipoTexto =
        tipoEquipo === "Camion"
            ? "TRACTO"
            : tipoEquipo === "ChasisA"
              ? "CHASIS A"
              : tipoEquipo === "ChasisB"
                ? "CHASIS B"
                : "GPS";

    const equipoBd = String(item?.EquipoBD ?? item?.equipo ?? "").trim();

    if (equipoBd) {
        return `${tipoTexto} - ${equipoBd}`;
    }

    return tipoTexto;
}

function construirLineaInfoEquipo(item) {
    const velocidadTexto = obtenerTextoVelocidad(item?.ubicacion);
    const tipoEquipoTexto = obtenerTextoTipoEquipo(item);

    if (velocidadTexto) {
        return `${velocidadTexto} · ${tipoEquipoTexto}`;
    }

    return tipoEquipoTexto;
}
function crearIconoMarkerPorVista(
    status,
    item,
    containerColor = "#0d6efd",
    vista = tipoVistaMarker,
) {
    if (vista === "transparente") {
        return createMarkerIconModern(status, item, containerColor);
    } else if (vista === "live") {
        return createMarkerIconByStatus(status, item, containerColor);
    }

    //default

    return createMarkerIconLive(status, item, containerColor);
}
function createMarkerIconByStatus(status, item, containerColor = "#0d6efd") {
    const estadoMarker = getEstadoMarker(status);

    const TIPOS = {
        Camion: { icon: "T", text: "TRACTO" },
        ChasisA: { icon: "A", text: "CHASIS A" },
        ChasisB: { icon: "B", text: "CHASIS B" },
        GPS: { icon: "G", text: "GPS" },
    };

    const tipoNormalizado = normalizarTipoEquipoMarker(
        item?.TipoEquipo || item?.ubicacion?.tipoEquipo || "GPS",
    );

    const tipo = TIPOS[tipoNormalizado] || TIPOS.GPS;

    const texto = construirTextoMarker(item);
    const textColor = getReadableTextColor(containerColor);

    const estadoTextoCorto =
        status === "moving"
            ? "EN RUTA"
            : status === "parked"
              ? "DETENIDO"
              : "SIN SEÑAL";

    const mostrarAnimacion = status === "moving";

    const svg = `
<svg width="310" height="154" viewBox="0 0 310 154" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <filter id="shadow" x="-20%" y="-20%" width="160%" height="180%">
            <feDropShadow dx="0" dy="4" stdDeviation="4" flood-color="rgba(0,0,0,0.28)" flood-opacity="0.45"/>
        </filter>
    </defs>

    ${
        mostrarAnimacion
            ? `
    <circle cx="155" cy="58" r="26" fill="${estadoMarker.color}" opacity="0.20">
        <animate attributeName="r" values="26;48;26" dur="1.2s" repeatCount="indefinite"/>
        <animate attributeName="opacity" values="0.20;0;0.20" dur="1.2s" repeatCount="indefinite"/>
    </circle>

    <g opacity="0.70">
        <line x1="8" y1="48" x2="18" y2="48" stroke="${estadoMarker.color}" stroke-width="3" stroke-linecap="round">
            <animate attributeName="x1" values="8;12;8" dur="0.8s" repeatCount="indefinite"/>
            <animate attributeName="x2" values="18;22;18" dur="0.8s" repeatCount="indefinite"/>
        </line>
        <line x1="4" y1="58" x2="18" y2="58" stroke="${estadoMarker.color}" stroke-width="3" stroke-linecap="round">
            <animate attributeName="x1" values="4;10;4" dur="0.8s" repeatCount="indefinite"/>
            <animate attributeName="x2" values="18;24;18" dur="0.8s" repeatCount="indefinite"/>
        </line>
        <line x1="10" y1="68" x2="18" y2="68" stroke="${estadoMarker.color}" stroke-width="3" stroke-linecap="round">
            <animate attributeName="x1" values="10;14;10" dur="0.8s" repeatCount="indefinite"/>
            <animate attributeName="x2" values="18;22;18" dur="0.8s" repeatCount="indefinite"/>
        </line>
    </g>`
            : ""
    }

    <g filter="url(#shadow)">
        <rect x="12" y="12" width="286" height="110" rx="18" fill="${containerColor}"/>

        <rect x="12" y="12" width="286" height="8" rx="4" fill="${estadoMarker.color}"/>

        <circle cx="42" cy="50" r="22" fill="white" opacity="0.96"/>
        <circle cx="42" cy="50" r="17" fill="${estadoMarker.soft}" stroke="${estadoMarker.color}" stroke-width="3"/>
        <text x="42" y="56" text-anchor="middle" font-size="17" font-family="Arial, sans-serif" font-weight="900" fill="${estadoMarker.color}">
            ${tipo.icon}
        </text>

        <!-- estado debajo del icono -->
        <rect x="14" y="76" width="56" height="16" rx="8" fill="${estadoMarker.color}"/>
        <text x="42" y="87" text-anchor="middle" font-size="7.5" font-family="Arial, sans-serif" font-weight="900" fill="#ffffff">
            ${estadoTextoCorto}
        </text>

        <!-- contenedor A -->
        <text x="74" y="38" font-size="14" font-family="Arial, sans-serif" font-weight="900" fill="${textColor}">
            ${escapeSvgText(texto.titulo)}
        </text>

        <!-- contenedor B o línea info -->
        <text x="74" y="57" font-size="12" font-family="Arial, sans-serif" font-weight="800" fill="${textColor}" opacity="0.96">
            ${escapeSvgText(texto.subtitulo)}
        </text>

        ${
            texto.detalle
                ? `
        <text x="74" y="74" font-size="10.5" font-family="Arial, sans-serif" font-weight="900" fill="${textColor}" opacity="0.88">
            ${escapeSvgText(texto.detalle)}
        </text>`
                : ""
        }

     ${
         texto.transportista
             ? `
<rect x="14" y="94" width="270" height="20" rx="10" fill="rgba(0,0,0,0.18)"/>
<text x="24" y="108" font-size="10.5" font-family="Arial, sans-serif" font-weight="900" fill="#ffffff">
    LT: ${escapeSvgText(texto.transportista)}
</text>`
             : ""
     }

        <path d="M155 146 L141 122 H169 Z" fill="${containerColor}"/>
<path d="M155 146 L141 122 H169 Z" fill="none" stroke="${estadoMarker.color}" stroke-width="3"/>
    </g>
</svg>`;

    return {
        url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg),
        scaledSize: new google.maps.Size(310, 154),
        anchor: new google.maps.Point(155, 146),
    };
}

function createMarkerIconModern(status, item, containerColor = "#0d6efd") {
    const estadoMarker = getEstadoMarker(status);

    const TIPOS = {
        Camion: { icon: "T", text: "TRACTO" },
        ChasisA: { icon: "A", text: "CHASIS A" },
        ChasisB: { icon: "B", text: "CHASIS B" },
        GPS: { icon: "G", text: "GPS" },
    };

    const tipoNormalizado = normalizarTipoEquipoMarker(
        item?.TipoEquipo || item?.ubicacion?.tipoEquipo || "GPS",
    );

    const tipo = TIPOS[tipoNormalizado] || TIPOS.GPS;

    const texto = construirTextoMarker(item);
    const textColor = getReadableTextColor(containerColor);

    const estadoTextoCorto =
        status === "moving"
            ? "EN RUTA"
            : status === "parked"
              ? "DETENIDO"
              : "SIN SEÑAL";

    const mostrarAnimacion = status === "moving";

    const svg = `
<svg width="310" height="154" viewBox="0 0 310 154" xmlns="http://www.w3.org/2000/svg">
    <defs>
        <filter id="shadow" x="-30%" y="-30%" width="180%" height="200%">
            <feDropShadow dx="0" dy="6" stdDeviation="8" flood-color="rgba(0,0,0,0.25)" flood-opacity="0.4"/>
        </filter>
        <linearGradient id="grad" x1="0" y1="0" x2="1" y2="1">
            <stop offset="0%" stop-color="${containerColor}" stop-opacity="0.95"/>
            <stop offset="100%" stop-color="${containerColor}" stop-opacity="0.75"/>
        </linearGradient>
    </defs>

    ${
        mostrarAnimacion
            ? `
    <!-- Ondas circulares alrededor del recuadro -->
    <circle cx="155" cy="67" r="120" fill="none" stroke="${estadoMarker.color}" stroke-width="2" opacity="0.15">
        <animate attributeName="r" values="120;160;120" dur="2.5s" repeatCount="indefinite"/>
        <animate attributeName="opacity" values="0.15;0;0.15" dur="2.5s" repeatCount="indefinite"/>
    </circle>
    <circle cx="155" cy="67" r="140" fill="none" stroke="${estadoMarker.color}" stroke-width="2" opacity="0.10">
        <animate attributeName="r" values="140;180;140" dur="2.5s" repeatCount="indefinite" begin="0.8s"/>
        <animate attributeName="opacity" values="0.10;0;0.10" dur="2.5s" repeatCount="indefinite" begin="0.8s"/>
    </circle>`
            : ""
    }

    <g filter="url(#shadow)">
        <!-- Contenedor principal con gradiente -->
        <rect x="12" y="12" width="286" height="110" rx="24" fill="url(#grad)"/>

        <!-- Barra superior de estado -->
        <rect x="12" y="12" width="286" height="8" rx="4" fill="${estadoMarker.color}"/>

        <!-- Icono circular -->
        <circle cx="42" cy="50" r="22" fill="white" opacity="0.95" filter="url(#shadow)"/>
        <circle cx="42" cy="50" r="17" fill="${estadoMarker.soft}" stroke="${estadoMarker.color}" stroke-width="3"/>
        <text x="42" y="55" text-anchor="middle" font-size="16" font-family="Inter, sans-serif" font-weight="700" fill="${estadoMarker.color}">
            ${tipo.icon}
        </text>

        <!-- Badge de estado -->
        <rect x="14" y="76" width="60" height="18" rx="9" fill="${estadoMarker.color}" filter="url(#shadow)"/>
        <text x="44" y="88" text-anchor="middle" font-size="9" font-family="Inter, sans-serif" font-weight="600" fill="#ffffff">
            ${estadoTextoCorto}
        </text>

        <!-- Texto principal -->
        <text x="74" y="38" font-size="14" font-family="Inter, sans-serif" font-weight="700" fill="${textColor}">
            ${escapeSvgText(texto.titulo)}
        </text>

        <!-- Subtítulo -->
        <text x="74" y="57" font-size="12" font-family="Inter, sans-serif" font-weight="500" fill="${textColor}" opacity="0.9">
            ${escapeSvgText(texto.subtitulo)}
        </text>

        ${
            texto.detalle
                ? `
        <text x="74" y="74" font-size="11" font-family="Inter, sans-serif" font-weight="600" fill="${textColor}" opacity="0.85">
            ${escapeSvgText(texto.detalle)}
        </text>`
                : ""
        }

        ${
            texto.transportista
                ? `
<rect x="14" y="94" width="270" height="22" rx="11" fill="rgba(0,0,0,0.18)" filter="url(#shadow)"/>
<text x="24" y="109" font-size="11" font-family="Inter, sans-serif" font-weight="600" fill="#ffffff">
    LT: ${escapeSvgText(texto.transportista)}
</text>`
                : ""
        }

        <!-- Flecha inferior -->
        <path d="M155 146 L141 122 H169 Z" fill="${containerColor}"/>
        <path d="M155 146 L141 122 H169 Z" fill="none" stroke="${estadoMarker.color}" stroke-width="3"/>
    </g>
</svg>`;

    return {
        url: "data:image/svg+xml;charset=UTF-8," + encodeURIComponent(svg),
        scaledSize: new google.maps.Size(310, 154),
        anchor: new google.maps.Point(155, 146),
    };
}

function createMarkerIconLive(status, item, containerColor = "#0d6efd") {
    const estadoMarker = getEstadoMarker(status);

    const TIPOS = {
        Camion: { icon: "T", text: "TRACTO" },
        ChasisA: { icon: "A", text: "CHASIS A" },
        ChasisB: { icon: "B", text: "CHASIS B" },
        GPS: { icon: "G", text: "GPS" },
    };

    const tipoNormalizado = normalizarTipoEquipoMarker(
        item?.TipoEquipo || item?.ubicacion?.tipoEquipo || "GPS",
    );

    const tipo = TIPOS[tipoNormalizado] || TIPOS.GPS;
    const texto = construirTextoMarker(item);

    const estadoTextoCorto =
        status === "moving"
            ? "EN RUTA"
            : status === "parked"
              ? "DETENIDO"
              : "SIN SEÑAL";

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

function obtenerTransportistaMarker(item) {
    return item?.transportista_nombre ?? "";
}
function normalizarTipoEquipoMarker(tipoEquipo) {
    if (!tipoEquipo) return "GPS";

    const tipo = String(tipoEquipo).toLowerCase().replace(/\s+/g, "");

    if (
        tipo.includes("camion") ||
        tipo.includes("camión") ||
        tipo.includes("tracto")
    ) {
        return "Camion";
    }

    if (tipo.includes("chasisb") || tipo.includes("chasis2")) {
        return "ChasisB";
    }

    if (tipo.includes("chasisa") || tipo.includes("chasis1")) {
        return "ChasisA";
    }

    if (tipo.includes("chasis")) {
        return "ChasisA";
    }

    return "GPS";
}

function escapeSvgText(text) {
    return String(text ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function getReadableTextColor(hexColor) {
    const rgb = hexToRgb(hexColor);
    if (!rgb) return "#ffffff";

    const brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;
    return brightness > 165 ? "#1f2937" : "#ffffff";
}

function hexToRgb(hex) {
    if (!hex) return null;

    hex = String(hex).replace("#", "");

    if (hex.length === 3) {
        hex = hex
            .split("")
            .map((x) => x + x)
            .join("");
    }

    const bigint = parseInt(hex, 16);
    if (Number.isNaN(bigint)) return null;

    return {
        r: (bigint >> 16) & 255,
        g: (bigint >> 8) & 255,
        b: bigint & 255,
    };
}

function rgbToHex(r, g, b) {
    return (
        "#" +
        [r, g, b]
            .map((x) => {
                const h = Math.max(0, Math.min(255, x)).toString(16);
                return h.length === 1 ? "0" + h : h;
            })
            .join("")
    );
}

function darkenHexColor(hexColor, amount = 20) {
    const rgb = hexToRgb(hexColor);
    if (!rgb) return "#0b5ed7";

    return rgbToHex(rgb.r - amount, rgb.g - amount, rgb.b - amount);
}
function construirTextoMarker(item) {
    const contenedorA = item?.contenedor ?? "";
    const contenedorB = item?.contenedorB ?? "";
    const transportista = obtenerTransportistaMarker(item);
    const lineaInfoEquipo = construirLineaInfoEquipo(item);

    if (contenedorA && contenedorB) {
        return {
            titulo: truncarTexto(contenedorA, 25),
            subtitulo: truncarTexto(contenedorB, 25),
            detalle: truncarTexto(lineaInfoEquipo, 28),
            transportista: transportista ? truncarTexto(transportista, 40) : "",
            esFull: true,
        };
    }

    return {
        titulo: truncarTexto(contenedorA || item?.EquipoBD || "GPS", 25),
        subtitulo: truncarTexto(lineaInfoEquipo, 28),
        detalle: "",
        transportista: transportista ? truncarTexto(transportista, 40) : "",
        esFull: false,
    };
}

function obtenerTransportistaMarker(item) {
    return (
        item?.transportista_nombre ||
        item?.transportista ||
        item?.Transportista ||
        item?.ubicacion?.transportista ||
        ""
    );
}

function truncarTexto(texto, max = 23) {
    texto = String(texto ?? "");
    return texto.length > max ? texto.substring(0, max - 1) + "…" : texto;
}
function escapeSvgText(text) {
    return String(text ?? "")
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}

function getReadableTextColor(hexColor) {
    const rgb = hexToRgb(hexColor);

    if (!rgb) {
        return "#ffffff";
    }

    const brightness = (rgb.r * 299 + rgb.g * 587 + rgb.b * 114) / 1000;

    return brightness > 165 ? "#1f2937" : "#ffffff";
}

function hexToRgb(hex) {
    if (!hex) {
        return null;
    }

    hex = String(hex).replace("#", "");

    if (hex.length === 3) {
        hex = hex
            .split("")
            .map((x) => x + x)
            .join("");
    }

    const bigint = parseInt(hex, 16);

    if (Number.isNaN(bigint)) {
        return null;
    }

    return {
        r: (bigint >> 16) & 255,
        g: (bigint >> 8) & 255,
        b: bigint & 255,
    };
}

function rgbToHex(r, g, b) {
    return (
        "#" +
        [r, g, b]
            .map((x) => {
                const hex = Math.max(0, Math.min(255, x)).toString(16);
                return hex.length === 1 ? "0" + hex : hex;
            })
            .join("")
    );
}

function darkenHexColor(hexColor, amount = 20) {
    const rgb = hexToRgb(hexColor);

    if (!rgb) {
        return "#0b5ed7";
    }

    return rgbToHex(rgb.r - amount, rgb.g - amount, rgb.b - amount);
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
function obtenerEstadoMarkerPorMovimiento(markerKey, ubicacion) {
    const latActual = Number(ubicacion?.lat ?? 0);
    const lngActual = Number(ubicacion?.lng ?? 0);
    const velocidad = Number(ubicacion?.velocidad ?? 0);

    if (!latActual || !lngActual) {
        return "offline";
    }

    if (velocidad > 5) {
        return "moving";
    }

    const markerAnterior = markers[markerKey];

    if (markerAnterior) {
        const posicionAnterior = markerAnterior.getPosition();

        if (posicionAnterior) {
            const latAnterior = Number(posicionAnterior.lat());
            const lngAnterior = Number(posicionAnterior.lng());

            const diffLat = Math.abs(latActual - latAnterior);
            const diffLng = Math.abs(lngActual - lngAnterior);

            if (diffLat > 0.0001 || diffLng > 0.0001) {
                return "moving";
            }
        }
    }

    return "parked";
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

        // let = obtenerColorGrupoMarker(item);
        const { colorMarker, tipoitemPanel } = obtenerColorYTipo(item);
        let colorBG = colorMarker;
        t = tipoitemPanel;

        tipo = item.tipogps;
        idProceso = item.id_grupo;

        let latlocal = "";
        let lnglocal = "";
        let idConvoyOContenedor = "";

        latlocal = parseFloat(item.ubicacion.lat);
        lnglocal = parseFloat(item.ubicacion.lng);

        idConvoyOContenedor = item.id_contenendor;

        tipo = tipo + " " + item.grupo_tipo;

        const tipoEquipoMarker = normalizarTipoEquipoMapa(
            item.TipoEquipo || item.ubicacion?.tipoEquipo || "Desconocido",
        );

        const markerKey = [
            item.id_contenendor,
            item.contenedor,
            tipoEquipoMarker,
            item.ubicacion?.imei,
        ].join("|");

        const estadoMarker = obtenerEstadoMarkerPorMovimiento(
            markerKey,
            item.ubicacion,
        );

        const labelMarker = String(
            item.contenedor ?? item.ubicacion?.imei ?? item.tipogps,
        );

        if (markers[markerKey]) {
            markers[markerKey].setPosition({
                lat: latlocal,
                lng: lnglocal,
            });

            markers[markerKey].setIcon(
                crearIconoMarkerPorVista(
                    estadoMarker,
                    item,
                    colorMarker,
                    tipoVistaMarker,
                ),
            );

            markers[markerKey].tipoEquipo = tipoEquipoMarker;
            markers[markerKey].idContenedor = String(item.id_contenendor ?? "");
            markers[markerKey].id_grupo = String(item.id_grupo ?? "");
            markers[markerKey].contenedor = String(item.contenedor ?? "");
            markers[markerKey].imei = String(item.ubicacion?.imei ?? "");
            markers[markerKey].dataItem = item;
            markers[markerKey].estadoMarker = estadoMarker;
            markers[markerKey].colobgMarker = colorMarker;
        } else {
            if (!latlocal || !lnglocal) return;

            const newMarker = new google.maps.Marker({
                position: { lat: latlocal, lng: lnglocal },
                map: markerDebeMostrarse({
                    tipoEquipo: tipoEquipoMarker,
                    idContenedor: String(item.id_contenendor ?? ""),
                    id_grupo: String(item.id_grupo ?? ""),
                    contenedor: String(item.contenedor ?? ""),
                    imei: String(item.ubicacion?.imei ?? ""),
                    dataItem: item,
                })
                    ? map
                    : null,
                icon: crearIconoMarkerPorVista(
                    estadoMarker,
                    item,
                    colorMarker,
                    tipoVistaMarker,
                ),
            });

            newMarker.keyItem = markerKey;
            newMarker.tipoEquipo = tipoEquipoMarker;
            newMarker.idContenedor = String(item.id_contenendor ?? "");
            newMarker.id_grupo = String(item.id_grupo ?? "");
            newMarker.contenedor = String(item.contenedor ?? "");
            newMarker.imei = String(item.ubicacion?.imei ?? "");
            newMarker.dataItem = item;
            newMarker.estadoMarker = estadoMarker;
            newMarker.colobgMarker = colorMarker;

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

       <button class="btnRuta" data-key="${markerKey}"   data-opcion="infowindow"
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



    <button class="btnRuta" data-key="${markerKey}"
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
                        (d) => d.conboy_id === item.id_grupo,
                    );

                    mostrarInfoConvoy(contenedoresConvoy, item.EquipoBD, "");
                } else {
                    mostrarInfoConvoy(info ? [info] : [], item.EquipoBD, "");
                }
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
function refrescarIconosMarkers() {
    Object.keys(markers).forEach((key) => {
        const marker = markers[key];

        if (!marker) return;

        const item = marker.dataItem;
        const status = marker.estadoMarker;
        const color = marker.colobgMarker || "#0d6efd";

        if (!item || !status) return;

        marker.setIcon(crearIconoMarkerPorVista(status, item, color));
    });
}
$(document).on("change", ".radioVistaMarker", function () {
    tipoVistaMarker = this.value;

    refrescarIconosMarkers();
});

function markerEstaSeleccionadoEnPanel(marker) {
    const idContenedorMarker = String(marker?.idContenedor ?? "");
    const contenedorMarker = String(marker?.contenedor ?? "");
    const dataItem = marker?.dataItem;

    let seleccionado = false;

    $(".checkDispositivo:checked").each(function () {
        const chk = $(this);
        const li = chk.closest(".dispositivoItem");

        const tipoPanel = String(li.data("tipo") ?? "");
        const idPanel = String(chk.data("id") ?? "");
        const valuePanel = String(chk.data("value") ?? "");
        const contenedorPanel = String(li.data("contenedor") ?? "");

        if (tipoPanel === "Contenedor") {
            if (idPanel === idContenedorMarker) {
                seleccionado = true;
                return false;
            }

            if (contenedorPanel && contenedorPanel === contenedorMarker) {
                seleccionado = true;
                return false;
            }

            if (valuePanel.includes(contenedorMarker)) {
                seleccionado = true;
                return false;
            }
        }

        if (tipoPanel === "Convoy") {
            const grupoTipo = String(dataItem?.grupo_tipo ?? "");
            const grupoId = String(dataItem?.id_grupo ?? "");

            if (grupoTipo === "Convoy" && grupoId && idPanel === grupoId) {
                seleccionado = true;
                return false;
            }

            if (valuePanel.includes(contenedorMarker)) {
                seleccionado = true;
                return false;
            }
        }

        if (tipoPanel === "Equipo") {
            const imeiMarker = String(marker?.imei ?? "");

            if (
                idPanel === idContenedorMarker ||
                valuePanel.includes(imeiMarker)
            ) {
                seleccionado = true;
                return false;
            }
        }
    });

    return seleccionado;
}
function markerDebeMostrarse(marker) {
    const estaSeleccionado = markerEstaSeleccionadoEnPanel(marker);
    const cumpleFiltroVista = markerCumpleFiltroVista(marker);

    return estaSeleccionado && cumpleFiltroVista;
}
function sincronizarBotonRuta(key) {
    if (!key) return;

    const renderer = directionsRenderer[key];
    const rutaVisible = renderer && renderer.getMap();

    document.querySelectorAll(`.btnRuta[data-key="${key}"]`).forEach((btn) => {
        btn.disabled = false;
        const label = btn.dataset.label ? " " + btn.dataset.label : "";

        btn.textContent = rutaVisible
            ? "Ocultar ruta" + label
            : "Mostrar ruta" + label;
        if (btn.dataset.opcion === "panel") {
            btn.style.background = "white";
            btn.style.color = "#333";
            btn.style.cursor = "pointer";
        }
    });

    document
        .querySelectorAll(`.infoRuta[data-key="${key}"]`)
        .forEach((info) => {
            info.style.display = rutaVisible ? "block" : "none";
        });
}
document.addEventListener("click", function (e) {
    const btn = e.target.closest(".btnRuta");
    if (!btn) return;

    const key = btn.dataset.key;
    const origen = btn.dataset.opcion;
    console.log("Botón de ruta clickeado. Key:", key, "Origen:", origen, btn);

    if (!key || !markers[key]) {
        btn.textContent = "📍 Ruta no disponible";
        btn.disabled = true;

        Swal.fire({
            icon: "warning",
            title: "Ruta no disponible",
            text: "Este dispositivo aún no tiene ubicación válida en el mapa.",
        });

        return;
    }

    const marker = markers[key];

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

        sincronizarBotonRuta(key);

        return;
    }

    btn.textContent = "Calculando...";
    btn.disabled = true;

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
        btn.disabled = false;

        if (status === "OK") {
            directionsRenderer[key].setDirections(result);

            const leg = result.routes[0].legs[0];

            document
                .querySelectorAll(`.infoRuta[data-key="${key}"]`)
                .forEach((infoSpan) => {
                    infoSpan.style.display = "block";
                    infoSpan.innerHTML = `
                    🚗 <strong>${leg.distance.text}</strong><br>
                    ⏱ <strong>${leg.duration.text}</strong>
                `;
                });

            sincronizarBotonRuta(key);
        } else {
            Swal.fire({
                icon: "error",
                title: "Error",
                text: "No se pudo calcular la ruta",
            });

            sincronizarBotonRuta(key);
        }
    });
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

    if (!algunoActivo) {
        detenerRastreo();
        limpiarMapa();
        return;
    }

    aplicarFiltroVistaMapa();
    iniciarRastreo();
});
function detener(keyInterval) {
    if (intervalIdsID[keyInterval]) {
        clearInterval(intervalIdsID[keyInterval]);
        intervalIdsID[keyInterval] = null;
    }
}
function mapIconGps(icono) {
    if (!icono) {
        return "/assets/icons/default gps.png";
    }

    return `/assets/icons/${icono}`;
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

            const tieneDato = (v) => {
                if (v === null || v === undefined) return false;

                const txt = String(v).trim();

                return (
                    txt !== "" &&
                    txt.toLowerCase() !== "null" &&
                    txt.toLowerCase() !== "undefined"
                );
            };

            const valor = (v) => {
                return tieneDato(v) ? String(v).trim() : "S/N";
            };

            function iconoProveedorGps(nombreGps, iconogps) {
                const icono = mapIconGps(iconogps);
                const nombre = valor(nombreGps);

                return `
        <div style="
            display:flex;
            flex-direction:column;
            align-items:center;
            justify-content:center;
            gap:4px;
            background:#eef5ff;
            color:#0d6efd;
            border:1px solid #cfe2ff;
            border-radius:12px;
            padding:6px 8px;
            width:104px;
            min-width:104px;
            max-width:104px;
            height:66px;
            text-align:center;
            line-height:1.1;
            overflow:hidden;
        ">
            <div style="
                width:76px;
                height:34px;
                display:flex;
                align-items:center;
                justify-content:center;
                overflow:hidden;
                border-radius:6px;
                background:white;
            ">
                <img src="${icono}"
                    alt="${nombre}"
                    onerror="this.onerror=null; this.src='/assets/icons/default-gps.svg';"
                    style="
                        width:76px;
                        height:34px;
                        object-fit:contain;
                        display:block;
                        transform:scale(1.22);
                        transform-origin:center;
                    ">
            </div>

            <span style="
                font-size:9.5px;
                font-weight:900;
                color:#0d6efd;
                overflow:hidden;
                text-overflow:ellipsis;
                white-space:nowrap;
                max-width:92px;
                display:block;
            ">
                ${nombre}
            </span>
        </div>
    `;
            }

            function bloqueEquipoGps({
                titulo,
                icono = "🚛",
                imei,
                equipo,
                placas,
                iconogps,
                nombreGps,
            }) {
                return `
    <div style="
        background:#f8f9fa;
        border-radius:8px;
        padding:8px 9px;
        border:1px solid #fd7e14;
        min-width:0;
    ">
        <div style="
            display:grid;
            grid-template-columns: 1fr 104px;
            column-gap:8px;
            align-items:start;
        ">
            <!-- Datos equipo -->
            <div style="min-width:0;">
                <div style="
                    font-size:12px;
                    font-weight:900;
                    color:#343a40;
                    white-space:nowrap;
                    margin-bottom:7px;
                ">
                    ${icono} ${titulo}
                </div>

                <div style="
                    display:grid;
                    grid-template-columns:58px 1fr;
                    row-gap:4px;
                    font-size:11.5px;
                ">
                    <div style="color:#6c757d; font-weight:800;">IMEI:</div>
                    <div style="font-family:monospace; font-weight:700; word-break:break-word;">
                        ${valor(imei)}
                    </div>

                    <div style="color:#6c757d; font-weight:800;">Equipo:</div>
                    <div style="font-weight:700; word-break:break-word;">
                        ${valor(equipo)}
                    </div>

                    <div style="color:#6c757d; font-weight:800;">Placas:</div>
                    <div style="font-weight:700; word-break:break-word;">
                        ${valor(placas)}
                    </div>


                </div>
            </div>

            <!-- Proveedor GPS -->
            <div style="
                display:flex;
                justify-content:flex-end;
                align-items:flex-start;
            ">
                ${iconoProveedorGps(nombreGps, iconogps)}
            </div>
        </div>
    </div>`;
            }

            let es_full =
                Number(info.es_full) === 1 &&
                (info.tipo_viaje === "Full" || info.tipo_viaje === "full")
                    ? true
                    : false;

            let infoContenido = `
<div class="tab-pane fade ${index === 0 ? "show active" : ""}"
    id="${tabId}"
    role="tabpanel"
    aria-labelledby="${tabId}-tab">

    <div style="
    font-family: Arial, sans-serif;
    font-size: 12.5px;
    color: #2b2b2b;
   width: 100%;
max-width: 100%;
">

   <div style="
    background:#f8f9fa;
    border:1px solid #0d6efd;
    border-left:5px solid #0d6efd;
    border-radius:8px;
    padding:8px 10px;
    margin-bottom:8px;
">
    <div style="
        display:grid;
        grid-template-columns: 85px 1fr 105px 230px;
        column-gap:10px;
        row-gap:6px;
        align-items:center;
    ">
        <div style="font-size:11px; color:#6c757d; font-weight:800; text-transform:uppercase;">
            Cliente:
        </div>

        <div style="font-size:14px; font-weight:900; color:#212529;">
            ${valor(info.cliente)}
        </div>

        <div style="color:#6c757d; font-weight:800; text-align:right;">
            Contrato:
        </div>


        <div style="
            display:flex;
            align-items:center;
            gap:6px;
            flex-wrap:nowrap;
            min-width:0;
        ">
         ${
             Number(window.escliente || 0) === 0
                 ? `
    <span style="
        display:inline-block;
        background:#e7f1ff;
        border:1px solid #b6d4fe;
        color:#0d6efd;
        border-radius:999px;
        padding:3px 9px;
        font-size:12px;
        font-weight:900;
        white-space:nowrap;
    ">
        ${valor(info.tipo_contrato)}
    </span>
`
                 : ""
         }

            <span style="
                display:inline-block;
                background:${es_full ? "#fff3cd" : "#f8f9fa"};
                border:1px solid ${es_full ? "#ffda6a" : "#ced4da"};
                color:${es_full ? "#997404" : "#495057"};
                border-radius:999px;
                padding:3px 9px;
                font-size:12px;
                font-weight:900;
                white-space:nowrap;
            ">
                ${es_full ? "FULL" : "Sencillo"}
            </span>
        </div>
    </div>
</div>




<!-- DATOS DEL VIAJE -->
<div style="
    border:1px solid #0d6efd;
    border-radius:8px;
    padding:8px 10px;
    margin-bottom:8px;
    background:white;
">
    <div style="font-weight:900; color:#0d6efd; margin-bottom:7px;">
        <i class="fas fa-route"></i> Datos del viaje
    </div>

    <div style="
        display:grid;
        grid-template-columns: 85px 1fr 105px 230px;
        column-gap:10px;
        row-gap:7px;
        align-items:center;
    ">
        <div style="color:#6c757d; font-weight:800;">Origen:</div>
        <div style="
            font-weight:700;
            color:#212529;
            word-break:break-word;
            line-height:1.3;
        ">
            ${valor(info.origen)}
        </div>

        <div style="
            color:#6c757d;
            font-weight:800;
            text-align:right;
        ">
            Fecha Inicio:
        </div>

        <div style="
            background:#e7f1ff;
            border:1px solid #b6d4fe;
            border-radius:8px;
            padding:5px 8px;
            font-size:11px;
            font-weight:900;
            color:#212529;
            white-space:nowrap;
            width:max-content;
            min-width:140px;
        ">
            ${valor(info.fecha_inicio)}
        </div>

        <div style="color:#6c757d; font-weight:800;">Destino:</div>
        <div style="
            font-weight:700;
            color:#212529;
            word-break:break-word;
            line-height:1.3;
        ">
            ${valor(info.destino)}
        </div>

        <div style="
            color:#6c757d;
            font-weight:800;
            text-align:right;
        ">
            Fecha Fin:
        </div>

        <div style="
            background:#e7f1ff;
            border:1px solid #b6d4fe;
            border-radius:8px;
            padding:5px 8px;
            font-size:11px;
            font-weight:900;
            color:#212529;
            white-space:nowrap;
            width:max-content;
            min-width:140px;
        ">
            ${valor(info.fecha_fin)}
        </div>
    </div>
</div>
<!-- CONTACTO / OPERADOR -->
<div style="
    border:1px solid #198754;
    border-radius:8px;
    padding:8px 10px;
    margin-bottom:8px;
    background:white;
">
    <div style="font-weight:900; color:#198754; margin-bottom:7px;">
        <i class="fas fa-user"></i> Contacto / Operador
    </div>

    <div style="
        display:grid;
        grid-template-columns: 85px 1fr 105px 230px;
        column-gap:10px;
        row-gap:7px;
        align-items:center;
    ">
        <div style="color:#6c757d; font-weight:800;">Contacto:</div>
        <div style="grid-column:span 3; font-weight:700; color:#212529;">
            ${valor(info.cp_contacto_entrega)}
        </div>

        <div style="color:#6c757d; font-weight:800;">Operador:</div>
        <div
            title="${valor(info.operador)}"
            style="
                font-weight:700;
                color:#212529;
                white-space:nowrap;
                overflow:hidden;
                text-overflow:ellipsis;
                min-width:0;
            ">
            ${valor(info.operador)}
        </div>

        <div style="color:#6c757d; font-weight:800; text-align:right;">
            Teléfono:
        </div>

        <div style="white-space:nowrap;">
            ${
                info.beneficiario_telefono
                    ? `<div style="color:#0d6efd; font-weight:900; text-decoration:none;">
                          ${info.beneficiario_telefono}
                       </div>`
                    : `<span style="font-weight:700; color:#212529;">S/N</span>`
            }
        </div>
    </div>
</div>

<!-- TRANSPORTE -->
<div style="
    border:1px solid #6f42c1;
    border-radius:8px;
    padding:8px 10px;
    margin-bottom:8px;
    background:white;
">
    <div style="font-weight:900; color:#6f42c1; margin-bottom:7px;">
        <i class="fas fa-truck-moving"></i> Transporte
    </div>

    <div style="
        display:grid;
        grid-template-columns: 85px 1fr 105px 230px;
        column-gap:10px;
        row-gap:7px;
        align-items:center;
    ">
        <div style="color:#6c757d; font-weight:800;">Proveedor:</div>
        <div style="font-weight:700; color:#212529;">
            ${valor(info.empresa)}
        </div>

        <div style="color:#6c757d; font-weight:800; text-align:right;">
            Transportista:
        </div>

        <div
            title="${valor(info.transportista_nombre)}"
            style="
                font-weight:700;
                color:#212529;
                white-space:nowrap;
                overflow:hidden;
                text-overflow:ellipsis;
                min-width:0;
            ">
            ${valor(info.transportista_nombre)}
        </div>
    </div>
</div>



     <!-- EQUIPOS / GPS -->
<div style="
    border:1px solid #fd7e14;
    border-radius:8px;
    padding:8px 10px;
    background:white;
">
    <div style="font-weight:900; color:#fd7e14; margin-bottom:7px;">
        <i class="fas fa-satellite-dish"></i> Equipos / GPS
    </div>

    <div style="
        display:grid;
        grid-template-columns: repeat(auto-fit, minmax(195px, 1fr));
        gap:8px;
        align-items:start;
    ">
        ${bloqueEquipoGps({
            titulo: "Tracto",
            icono: "🚛",
            imei: info.imei,
            equipo: info.id_equipo,
            placas: filtroEqu?.placas,
            iconogps: info.icono_gps,
            nombreGps: info.nombre_gps,
        })}

        ${bloqueEquipoGps({
            titulo: "Chasis A",
            icono: "🧱",
            imei: info.imei_chasis,
            equipo: info.id_equipo_chasis,
            placas: info.placasChasis ?? info.placas_chasis,
            iconogps: info.icono_gpschasis,
            nombreGps: info.nombre_gpschasis,
        })}

        ${
            tieneDato(info.id_equipo_chasis2)
                ? bloqueEquipoGps({
                      titulo: "Chasis B / Full",
                      icono: "🧱",
                      imei: info.imei_chasis2,
                      equipo: info.id_equipo_chasis2,
                      placas: info.placasChasis2,
                      iconogps: info.icono_gpschasis2,
                      nombreGps: info.nombre_gpschasis2,
                  })
                : ""
        }
    </div>
</div>



        </div>

    </div>
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
    // cargaConvoysTab();
});

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

    if (!el) {
        return;
    }

    el.innerHTML = `<option value="">${placeholder}</option>`;

    data.forEach((item) => {
        el.innerHTML += `
<option value="${item.id}">
${item.nombre}
</option>
`;
    });
}

function existeOpcionSelect(selectId, value) {
    if (!value) {
        return true;
    }

    const el = document.getElementById(selectId);

    if (!el) {
        return false;
    }

    return Array.from(el.options).some((option) => option.value == value);
}

function restaurarValorSelect(selectId, value) {
    const el = document.getElementById(selectId);

    if (!el) {
        return "";
    }

    el.value = existeOpcionSelect(selectId, value) ? value : "";

    return el.value;
}

function obtenerCatalogoDesdeIds(ids = [], fuente = [], prefijo = "Item") {
    const idsUnicos = [
        ...new Set(
            ids
                .flat()
                .filter((id) => id !== null && id !== undefined && id !== "")
                .map((id) => String(id)),
        ),
    ];

    return idsUnicos
        .map((id) => {
            const itemFuente = fuente.find((item) => String(item.id) === id);

            return {
                id: id,
                nombre: itemFuente?.nombre ?? `${prefijo} ${id}`,
            };
        })
        .sort((a, b) => String(a.nombre).localeCompare(String(b.nombre)));
}

function obtenerCatalogoLineasDesdeCatalogo(datos = []) {
    const proveedoresDisponibles =
        typeof proveedores !== "undefined" ? proveedores : [];

    return obtenerCatalogoDesdeIds(
        datos.flatMap((item) => item.lineas ?? []),
        proveedoresDisponibles,
        "Linea",
    );
}

function obtenerCatalogoClientesDesdeCatalogo(datos = []) {
    const clientesDisponibles = typeof clientes !== "undefined" ? clientes : [];

    return obtenerCatalogoDesdeIds(
        datos.flatMap((item) => item.clientes ?? []),
        clientesDisponibles,
        "Cliente",
    );
}

function obtenerCatalogoBaseFiltros() {
    return catalogoBusquedaOriginal.length
        ? catalogoBusquedaOriginal
        : catalogoBusqueda;
}

function actualizarFiltrosDisponiblesPanel() {
    const lineaActual = $("#filtroLineaT").val() || "";
    const clienteActual = $("#filtrocliente").val() || "";
    const empresaSeleccionada = $("#filtroEmpresa").val() || "";
    const catalogoBase = obtenerCatalogoBaseFiltros();

    const catalogoPorEmpresa = empresaSeleccionada
        ? catalogoBase.filter((item) =>
              item.empresas?.includes(Number(empresaSeleccionada)),
          )
        : catalogoBase;

    let lineasDisponibles =
        obtenerCatalogoLineasDesdeCatalogo(catalogoPorEmpresa);
    let clientesDisponibles =
        obtenerCatalogoClientesDesdeCatalogo(catalogoPorEmpresa);

    if (!catalogoBase.length) {
        lineasDisponibles =
            typeof proveedores !== "undefined" ? proveedores : [];
        clientesDisponibles = typeof clientes !== "undefined" ? clientes : [];
    }

    llenarSelect("filtroLineaT", lineasDisponibles);
    llenarSelect("filtrocliente", clientesDisponibles);

    restaurarValorSelect("filtroLineaT", lineaActual);
    restaurarValorSelect("filtrocliente", clienteActual);
}

function obtenerCatalogoLineasDesdeRastreo(datos = []) {
    const lineasMap = new Map();

    datos.forEach((item) => {
        const id = item.proveedor_id;

        if (!id) {
            return;
        }

        const nombre = item.transportista_nombre || `Linea ${id}`;

        if (!lineasMap.has(String(id))) {
            lineasMap.set(String(id), {
                id: id,
                nombre: nombre,
            });
        }
    });

    return Array.from(lineasMap.values()).sort((a, b) =>
        String(a.nombre).localeCompare(String(b.nombre)),
    );
}

function cargarLineasDesdeRastreo(datos = []) {
    const proveedoresDisponibles =
        typeof proveedores !== "undefined" ? proveedores : [];

    if (proveedoresDisponibles.length) {
        return;
    }

    llenarSelect("filtroLineaT", obtenerCatalogoLineasDesdeRastreo(datos));
}

function cargarFiltrosInicialesPanel() {
    actualizarFiltrosDisponiblesPanel();
}

document.addEventListener("DOMContentLoaded", cargarFiltrosInicialesPanel);

$("#filtroEmpresa").on("change", function () {
    actualizarFiltrosDisponiblesPanel();
    aplicarFiltrosPanel();
});

$("#filtroTipo,#filtroLineaT,#filtrocliente,#buscadorGeneral").on(
    "change keyup",
    function () {
        let selector = "#" + $(this).attr("id");
        let valor = $(this).val();
        cambiofiltros(selector, valor);
        //aplicarFiltrosPanel();
    },
);

function obtenerFiltros() {
    return {
        empresa: $("#filtroEmpresa").val() || "",

        linea: $("#filtroLineaT").val(),

        cliente: $("#filtrocliente").val(),

        tipo: $("#filtroTipo").val(),

        buscar: $("#buscadorGeneral").val().toLowerCase(),
    };
}

function obtenerItemsSeleccionados() {
    const items = [];

    $(".checkDispositivo:checked").each(function () {
        const li = $(this).closest(".dispositivoItem");

        const tipo = li.data("tipo");
        const id = $(this).data("id");
        const value = $(this).data("value");

        items.push({
            tipo: tipo,
            id: id,
            value: value,
        });
    });

    return items;
}
function buscarUbicaciones() {
    const items = obtenerItemsSeleccionados();

    if (!items.length) {
        return Promise.resolve();
    }

    return fetch("/coordenadas/ubicacion-vehiculo", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                .getAttribute("content"),
        },
        body: JSON.stringify({ items: items }),
    })
        .then((res) => res.json())
        .then((data) => {
            actualizarEstadosPanel(data);
            actualizarUbicacion(data, "Equipo", "all", 0, map, 0, 0);

            return data;
        })
        .catch((err) => {
            console.error(err);
            throw err;
        });
}
function actualizarPanelConvoy(idConvoy, noConvoy, ubicaciones) {
    imeis = obtenerImeisPorConvoyId(idConvoy);
    //aki obtengo mi cadena compuesta string con | |

    imeis.forEach({
        //aki por cada vuelta descomponer el strin sacar posicion 1 = imei y buscar en ubicaciones, asi ya pinto
    });
}

function actualizarEstadoDispositivo(li, data) {
    const COLORES_ESTADO_GPS = {
        moviendo: "#2dce89",
        detenido: "#fb6340",
        offline: "#8392ab",
    };
    const badge = li.find(".estadoDispositivo");
    const icono = li.find(".iconoEstado");

    if (!data || !data.ubicacion?.lat || !data.ubicacion?.lng) {
        badge
            .removeClass("bg-success bg-warning bg-secondary")
            .css("background-color", COLORES_ESTADO_GPS.offline)
            .text("Sin señal");

        icono.text("⚫");
        return;
    }

    const latNueva = Number(data.ubicacion.lat);
    const lngNueva = Number(data.ubicacion.lng);

    const latAnterior = Number(li.data("lat"));
    const lngAnterior = Number(li.data("lng"));

    const velocidad = Number(data.velocidad ?? 0);

    let moviendo = false;
    let localVelocidad = velocidad;
    if (velocidad > 5) {
        moviendo = true;
    } else if (latAnterior !== 0 && lngAnterior !== 0) {
        const diffLat = Math.abs(latNueva - latAnterior);
        const diffLng = Math.abs(lngNueva - lngAnterior);

        if (diffLat > 0.0001 || diffLng > 0.0001) {
            moviendo = true;
            localVelocidad = 1;
        }
    }

    li.data("lat", latNueva);
    li.data("lng", lngNueva);

    let estado = getEstadoGps(localVelocidad, latNueva, lngNueva);

    if (moviendo) {
        badge
            .removeClass("bg-success bg-warning bg-secondary")
            .css("background-color", COLORES_ESTADO_GPS.moviendo)
            .text("Moviendo");

        icono.text("🚚");
    } else {
        badge
            .removeClass("bg-success bg-warning bg-secondary")
            .css("background-color", COLORES_ESTADO_GPS.detenido)
            .text("Detenido");

        icono.text("🅿️");
    }
}
function actualizarEstadosPanel(respuesta) {
    if (!Array.isArray(respuesta)) {
        console.warn("Respuesta invalida para actualizar panel:", respuesta);
        return;
    }

    $(".checkDispositivo").each(function () {
        const chk = $(this);
        const li = chk.closest(".dispositivoItem");

        const tipoPanel = String(li.data("tipo") ?? "");
        const idPanel = String(chk.data("id") ?? li.data("id") ?? "");
        const valuePanel = String(chk.data("value") ?? li.data("key") ?? "");
        const contenedorPanel = String(li.data("contenedor") ?? "");

        let dataFinal = null;

        if (tipoPanel === "Contenedor") {
            dataFinal = obtenerUbicacionRepresentativaContenedor(
                respuesta,
                idPanel,
                contenedorPanel,
                valuePanel,
            );
        } else if (tipoPanel === "Convoy") {
            dataFinal = obtenerUbicacionRepresentativaConvoy(
                respuesta,
                idPanel,
                valuePanel,
            );
        } else if (tipoPanel === "Equipo") {
            dataFinal = obtenerUbicacionRepresentativaEquipo(
                respuesta,
                idPanel,
                valuePanel,
            );
        } else {
            dataFinal = obtenerUbicacionRepresentativaGenerica(
                respuesta,
                idPanel,
                contenedorPanel,
                valuePanel,
            );
        }

        actualizarEstadoDispositivo(li, dataFinal);
        actualizarClickMapaPanel(li, dataFinal);

        const ubicacionesPanel = obtenerUbicacionesPanel(
            respuesta,
            tipoPanel,
            idPanel,
            contenedorPanel,
            valuePanel,
        );

        if (ubicacionesPanel.length) {
            actualizarOpcionesRutaPanel(li, ubicacionesPanel);
        } else {
            limpiarOpcionesRutaPanel(li);
        }
    });
}

function obtenerUbicacionesPanel(
    respuesta,
    tipoPanel,
    idPanel,
    contenedorPanel,
    valuePanel,
) {
    const idBuscado = String(idPanel ?? "");
    const contenedorBuscado = normalizarTextoSimple(contenedorPanel);
    const valueNormalizado = normalizarTextoSimple(valuePanel);

    return respuesta.filter((item) => {
        if (!tieneCoordenadasValidas(item?.ubicacion)) {
            return false;
        }

        const idResp = String(item.id_contenendor ?? "");
        const grupoTipo = String(item.grupo_tipo ?? "");
        const grupoId = String(item.id_grupo ?? "");
        const contenedorResp = normalizarTextoSimple(item.contenedor);
        const imeiResp = normalizarTextoSimple(item.ubicacion?.imei);

        if (tipoPanel === "Contenedor") {
            return (
                (idBuscado && idResp === idBuscado) ||
                (contenedorBuscado && contenedorResp === contenedorBuscado) ||
                (valueNormalizado &&
                    (valueNormalizado.includes(contenedorResp) ||
                        valueNormalizado.includes(imeiResp)))
            );
        }

        if (tipoPanel === "Convoy") {
            return (
                (grupoTipo === "Convoy" && grupoId === idBuscado) ||
                (valueNormalizado &&
                    (valueNormalizado.includes(contenedorResp) ||
                        valueNormalizado.includes(imeiResp)))
            );
        }

        if (tipoPanel === "Equipo") {
            return (
                (idBuscado && idResp === idBuscado) ||
                (valueNormalizado && valueNormalizado.includes(imeiResp))
            );
        }

        return (
            (idBuscado && idResp === idBuscado) ||
            (contenedorBuscado && contenedorResp === contenedorBuscado) ||
            (valueNormalizado &&
                (valueNormalizado.includes(contenedorResp) ||
                    valueNormalizado.includes(imeiResp)))
        );
    });
}

function construirMarkerKeyDesdeUbicacion(item) {
    return [
        item.id_contenendor,
        item.contenedor,
        normalizarTipoEquipoMapa(
            item.TipoEquipo || item.ubicacion?.tipoEquipo || "Desconocido",
        ),
        item.ubicacion?.imei,
    ].join("|");
}

function obtenerLabelTipoRuta(item) {
    const tipoEquipo = normalizarTipoEquipoMapa(
        item.TipoEquipo || item.ubicacion?.tipoEquipo || "Desconocido",
    );

    if (tipoEquipo === "Camion") {
        return "Tracto";
    }

    if (tipoEquipo === "ChasisA") {
        return "Chasis A";
    }

    if (tipoEquipo === "ChasisB") {
        return "Chasis B";
    }

    return "GPS";
}

function actualizarOpcionesRutaPanel(li, ubicacionesPanel) {
    const menu = li.find(".menuOpcionesDispositivo");
    const infoRuta = li.find(".infoRuta").first();
    const btnOpciones = li.find(".btnOpcionesDispositivo");
    const panelKey = String(li.data("panel-key") ?? "");
    const keysAgregadas = new Set();
    const opciones = [];

    ubicacionesPanel.forEach((item) => {
        const markerKey = construirMarkerKeyDesdeUbicacion(item);

        if (!markerKey || keysAgregadas.has(markerKey)) {
            return;
        }

        keysAgregadas.add(markerKey);
        opciones.push({
            key: markerKey,
            label: obtenerLabelTipoRuta(item),
        });
    });

    if (!opciones.length) {
        limpiarOpcionesRutaPanel(li);
        return;
    }

    li.attr("data-marker-key", opciones.map((opcion) => opcion.key).join(";"));
    btnOpciones.attr("data-key", opciones[0].key);

    if (menu.length) {
        const botonesRuta = opciones
            .map(
                (opcion) => `
        <button type="button"
            class="opcionMenuDispositivo btnRuta"
            data-panel-key="${panelKey}"
            data-opcion="panel"
            data-key="${opcion.key}"
            data-label="${opcion.label}"
            style="
                width:100%;
                border:none;
                background:white;
                padding:9px 12px;
                text-align:left;
                cursor:pointer;
                color:#333;
            ">
            Mostrar ruta ${opcion.label}
        </button>
        <div class="infoRuta"
            data-key="${opcion.key}"
            style="
                display:none;
                background:#f8f9fa;
                color:#333;
                padding:6px 12px;
                font-size:12px;
                border-top:1px solid #e9ecef;
            "></div>`,
            )
            .join("");

        menu.html(`
            ${botonesRuta}

            <button type="button" class="d-none" disabled
                style="width:100%; border:none; background:#f5f5f5; padding:9px 12px; text-align:left; color:#999; cursor:not-allowed;">
                Detalle GPS / equipo
            </button>

            <button type="button" disabled
                style="width:100%; border:none; background:#f5f5f5; padding:9px 12px; text-align:left; color:#999; cursor:not-allowed;">
                Historial
            </button>
        `);
    }

    if (infoRuta.length) {
        infoRuta.attr(
            "data-key",
            opciones.map((opcion) => opcion.key).join(";"),
        );
    }

    opciones.forEach((opcion) => sincronizarBotonRuta(opcion.key));
}

function limpiarOpcionesRutaPanel(li) {
    const menu = li.find(".menuOpcionesDispositivo");
    const infoRuta = li.find(".infoRuta");
    const btnOpciones = li.find(".btnOpcionesDispositivo");
    const panelKey = String(li.data("panel-key") ?? "");

    li.attr("data-marker-key", "");
    btnOpciones.attr("data-key", "");

    if (menu.length) {
        menu.html(`
            <button type="button"
                class="opcionMenuDispositivo btnRuta"
                data-panel-key="${panelKey}"
                data-opcion="panel"
                data-key=""
                disabled
                style="
                    width:100%;
                    border:none;
                    background:#f5f5f5;
                    padding:9px 12px;
                    text-align:left;
                    cursor:not-allowed;
                    color:#999;
                ">
                Ruta no disponible
            </button>
        `);
    }

    infoRuta.attr("data-key", "");
    infoRuta.hide();
    infoRuta.empty();
}
function tieneCoordenadasValidas(data) {
    if (!data) {
        return false;
    }

    const lat = Number(data.lat ?? 0);
    const lng = Number(data.lng ?? 0);

    return lat !== 0 && lng !== 0;
}

function normalizarTextoSimple(valor) {
    return String(valor ?? "")
        .toLowerCase()
        .replace(/\s+/g, "")
        .replace(/-/g, "");
}

function obtenerUbicacionPreferida(items) {
    if (!items || items.length === 0) {
        return null;
    }

    /*
     * Prioridad:
     * 1. Camión con coordenadas
     * 2. Chasis A con coordenadas
     * 3. Chasis B con coordenadas
     * 4. Cualquier ubicación con coordenadas
     * 5. Primer registro aunque no tenga coordenadas
     */
    const prioridades = ["Camion", "ChasisA", "ChasisB"];

    for (const tipo of prioridades) {
        const encontrado = items.find((r) => {
            const tipoEquipo = r.TipoEquipo || r.ubicacion?.tipoEquipo;
            return tipoEquipo === tipo && tieneCoordenadasValidas(r.ubicacion);
        });

        if (encontrado) {
            return encontrado;
        }
    }

    const cualquieraConCoordenadas = items.find((r) =>
        tieneCoordenadasValidas(r.ubicacion),
    );

    if (cualquieraConCoordenadas) {
        return cualquieraConCoordenadas.ubicacion;
    }

    return items[0]?.ubicacion ?? null;
}

function obtenerUbicacionRepresentativaContenedor(
    respuesta,
    idPanel,
    contenedorPanel,
    valuePanel,
) {
    const idBuscado = String(idPanel ?? "");
    const contenedorBuscado = normalizarTextoSimple(
        contenedorPanel || valuePanel,
    );

    const items = respuesta.filter((r) => {
        const idResp = String(r.id_contenendor ?? "");
        const contenedorResp = normalizarTextoSimple(r.contenedor);

        if (idBuscado && idResp === idBuscado) {
            return true;
        }

        if (
            contenedorBuscado &&
            contenedorResp &&
            contenedorBuscado.includes(contenedorResp)
        ) {
            return true;
        }

        if (
            contenedorBuscado &&
            contenedorResp &&
            contenedorResp.includes(contenedorBuscado)
        ) {
            return true;
        }

        return false;
    });

    return obtenerUbicacionPreferida(items);
}

function obtenerUbicacionRepresentativaConvoy(respuesta, idPanel, valuePanel) {
    const idBuscado = String(idPanel ?? "");
    const valueNormalizado = normalizarTextoSimple(valuePanel);

    const items = respuesta.filter((r) => {
        const grupoTipo = String(r.grupo_tipo ?? "");
        const grupoId = String(r.id_grupo ?? "");
        const contenedorResp = normalizarTextoSimple(r.contenedor);

        /*
         * Caso ideal: backend manda grupo_tipo = Convoy y grupo_id.
         */
        if (grupoTipo === "Convoy" && grupoId && grupoId === idBuscado) {
            return true;
        }

        /*
         * Fallback: si el value del convoy contiene los contenedores.
         */
        if (
            valueNormalizado &&
            contenedorResp &&
            valueNormalizado.includes(contenedorResp)
        ) {
            return true;
        }

        return false;
    });

    return obtenerUbicacionPreferida(items);
}

function obtenerUbicacionRepresentativaEquipo(respuesta, idPanel, valuePanel) {
    const idBuscado = String(idPanel ?? "");
    const valueNormalizado = normalizarTextoSimple(valuePanel);

    const items = respuesta.filter((r) => {
        const idResp = String(r.id_contenendor ?? "");
        const imeiResp = normalizarTextoSimple(
            r.ubicacion?.imei ?? r.value ?? "",
        );

        if (idBuscado && idResp === idBuscado) {
            return true;
        }

        if (
            valueNormalizado &&
            imeiResp &&
            valueNormalizado.includes(imeiResp)
        ) {
            return true;
        }

        return false;
    });

    return obtenerUbicacionPreferida(items);
}

function obtenerUbicacionRepresentativaGenerica(
    respuesta,
    idPanel,
    contenedorPanel,
    valuePanel,
) {
    return obtenerUbicacionRepresentativaContenedor(
        respuesta,
        idPanel,
        contenedorPanel,
        valuePanel,
    );
}
$(document).on("click", ".dispositivoClickable", function (e) {
    if ($(e.target).is("input")) {
        return;
    }

    const lat = Number($(this).attr("data-lat"));
    const lng = Number($(this).attr("data-lng"));

    if (!lat || !lng) {
        return;
    }

    map.panTo({
        lat: lat,
        lng: lng,
    });

    map.setZoom(15);
});
function actualizarClickMapaPanel(li, data) {
    const lat = Number(data?.ubicacion?.lat ?? 0);
    const lng = Number(data?.ubicacion?.lng ?? 0);

    const tieneCoordenadas = lat !== 0 && lng !== 0;

    li.attr("data-lat", lat);
    li.attr("data-lng", lng);

    if (tieneCoordenadas) {
        li.addClass("dispositivoClickable");
        li.css("cursor", "pointer");
        li.attr("title", "Ver en mapa");
    } else {
        li.removeClass("dispositivoClickable");
        li.css("cursor", "default");
        li.removeAttr("title");
    }
}

$(document).on("change", ".checkDispositivo", function () {
    if ($(".checkDispositivo:checked").length === 0) {
        detenerRastreo();
        return;
    }

    iniciarRastreo();
});

function iniciarRastreo() {
    if (rastreoActivo) return;

    rastreoActivo = true;

    ejecutarRastreo();
}

function detenerRastreo() {
    rastreoActivo = false;

    if (timeoutRastreo) {
        clearTimeout(timeoutRastreo);
        timeoutRastreo = null;
    }

    requestEnCurso = false;
}

async function ejecutarRastreo() {
    if (!rastreoActivo) return;

    if (requestEnCurso) {
        console.warn("Rastreo omitido: request en curso");
        return;
    }

    requestEnCurso = true;

    try {
        await buscarUbicaciones();
    } catch (e) {
        console.error("Error rastreo:", e);
    } finally {
        requestEnCurso = false;

        if (rastreoActivo) {
            timeoutRastreo = setTimeout(() => {
                ejecutarRastreo();
            }, INTERVALO_RASTREO);
        }
    }
}
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

        const estado = getEstadoGps(0, 0, 0);
        estadoColor = estado.color;
        estadoIcono = estado.iconoPanel;
        estadoTexto = estado.textoPanel;

        const color = getRandomColor();

        const panelKey = `${d.tipo ?? "NA"}_${d.id ?? d.contenedor ?? d.label}`;

        const item = `
<li class="list-group-item dispositivoItem position-relative"
    data-panel-key="${panelKey}"
    data-marker-key=""
    data-key="${d.value ?? ""}"
    data-contenedor="${d.contenedor ?? d.label}"
    data-color="${color}"
    data-lat="0"
    data-lng="0"
    data-tipo="${d.tipo}"
    data-id="${d.id}"
    style="
        background-color:${color};
        color:white;
        cursor:default;
        padding:10px 12px;
        min-height:62px;
        border-radius:10px;
        margin-bottom:6px;
    ">

    <div class="d-flex justify-content-between align-items-center w-100 gap-2">

        <!-- ESTA ES EL ÁREA QUE SÍ MANDA AL MAPA -->
      <div class="areaClickDispositivo d-flex align-items-center flex-grow-1"
    style="
        min-width:0;
        cursor:inherit;
        padding:4px 0;
        overflow:hidden;
    ">

    <input type="checkbox"
        class="checkDispositivo me-2 flex-shrink-0"
        data-id="${d.id}"
        data-value="${d.value ?? ""}"
        data-panel-key="${panelKey}"
        data-contenedor="${d.contenedor ?? d.label}">

    <span class="iconoEstado me-1 flex-shrink-0">${estadoIcono}</span>

    <div style="min-width:0; flex:1; overflow:hidden;">
        <div class="fw-bold labelDispositivoPanel">
            ${d.label}
        </div>
    </div>
</div>

        <div class="d-flex align-items-center gap-2 ms-2 flex-shrink-0">
    <span class="badge estadoDispositivo bg-${estadoColor}">
        ${estadoTexto}
    </span>

    <button type="button"
        class="btnOpcionesDispositivo"
        data-panel-key="${panelKey}"
        style="
            width:30px;
            height:30px;
            border:none;
            border-radius:50%;
            background:rgba(255,255,255,0.22);
            color:white;
            font-weight:bold;
            line-height:1;
            cursor:pointer;
            font-size:18px;
            flex-shrink:0;
        ">
        ⋯
    </button>
</div>
    </div>

  <div class="menuOpcionesDispositivo"
    data-panel-key="${panelKey}"
    style="
        display:none;
        position:absolute;
        top:52px;
        right:10px;
        min-width:210px;
        background:white;
        color:#333;
        border-radius:10px;
        box-shadow:0 8px 20px rgba(0,0,0,0.25);
        z-index:99999;
        overflow:hidden;
        font-size:13px;
    ">

        <button type="button"
            class="opcionMenuDispositivo btnRuta"
            data-panel-key="${panelKey}"
            data-opcion="panel"
            data-key=""
            disabled
            style="
                width:100%;
                border:none;
                background:#f5f5f5;
                padding:9px 12px;
                text-align:left;
                cursor:not-allowed;
                color:#999;
            ">
            📍 Ruta no disponible
        </button>

        <button type="button" class="d-none"
            disabled
            style="
                width:100%;
                border:none;
                background:#f5f5f5;
                padding:9px 12px;
                text-align:left;
                color:#999;
                cursor:not-allowed;
            ">
            ℹ️ Detalle GPS / equipo
        </button>

        <button type="button"
            disabled
            style="
                width:100%;
                border:none;
                background:#f5f5f5;
                padding:9px 12px;
                text-align:left;
                color:#999;
                cursor:not-allowed;
            ">
            📜 Historial
        </button>
    </div>

    <div class="infoRuta"
        data-panel-key="${panelKey}"
        data-key=""
        style="
            display:none;
            margin-top:8px;
            background:rgba(255,255,255,0.18);
            padding:6px 8px;
            border-radius:8px;
            font-size:12px;
            color:white;
        ">
    </div>

</li>
`;

        lista.append(item);
    });
}

$("#filtroTipo, #filtroLineaT, #filtrocliente, #buscadorGeneral").on(
    "focus",
    function () {
        valorAnteriorFiltro = $(this).val();
    },
);
function obtenerColorYTipo(item) {
    const idContenedor = String(item.id_grupo ?? "");
    const contenedor = String(item.grupo_tipo ?? "");
    let resultado = { colorMarker: "#0d6efd", tipoitemPanel: "" };

    $(".dispositivoItem").each(function () {
        const li = $(this);
        const tipoLi = String(li.data("tipo") ?? "");
        const idLi = String(li.data("id") ?? "");
        const contenedorLi = String(li.data("contenedor") ?? "");
        const keyLi = String(li.data("key") ?? "");

        if (tipoLi === "Contenedor" && idLi === idContenedor) {
            resultado.colorMarker = li.data("color");
            resultado.tipoitemPanel = tipoLi;
            return false;
        }

        if (contenedorLi && contenedorLi === contenedor) {
            resultado.colorMarker = li.data("color");
            resultado.tipoitemPanel = tipoLi;
            return false;
        }

        if (
            keyLi.includes(contenedor) ||
            (li.data("label") ?? "").includes(contenedor)
        ) {
            resultado.colorMarker = li.data("color");
            resultado.tipoitemPanel = tipoLi;
            return false;
        }

        if (tipoLi === "Convoy") {
            if (idContenedor && idLi === idContenedor) {
                resultado.colorMarker = li.data("color");
                resultado.tipoitemPanel = tipoLi;
                return false;
            }
        }

        // EQUIPO
        if (tipoLi === "Equipo") {
            if (idEquipo && idLi === idEquipo) {
                resultado.colorMarker = li.data("color");
                resultado.tipoitemPanel = tipoLi;
                return false;
            }

            if (imei && textoPanel.includes(imei.toLowerCase())) {
                resultado.colorMarker = li.data("color");
                resultado.tipoitemPanel = tipoLi;
                return false;
            }
        }
    });

    return resultado;
}
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

function cambiofiltros(input, valorAnterior) {
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
                $(input).val(valorAnterior);
            }
        });
    } else {
        aplicarFiltrosPanel();
    }
}

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

function limpiarBackdrops() {
    document.querySelectorAll(".modal-backdrop").forEach((el) => el.remove());

    document.body.classList.remove("modal-open");

    document.body.style.overflow = "";
    document.body.style.paddingRight = "";
}

document.addEventListener("hidden.bs.modal", limpiarBackdrops);

//opciones de configuracion para el mapa
let filtrosVistaMapa = new Set(["todos", "Camion", "ChasisA", "ChasisB"]);

function normalizarTipoEquipoMapa(tipoEquipo) {
    if (!tipoEquipo) {
        return "Desconocido";
    }

    const tipo = String(tipoEquipo).toLowerCase().replace(/\s+/g, "");

    if (
        tipo.includes("camion") ||
        tipo.includes("camión") ||
        tipo.includes("tracto")
    ) {
        return "Camion";
    }

    if (tipo.includes("chasisb") || tipo.includes("chasis2")) {
        return "ChasisB";
    }

    if (tipo.includes("chasisa") || tipo.includes("chasis1")) {
        return "ChasisA";
    }

    if (tipo.includes("chasis")) {
        return "ChasisA";
    }

    return tipoEquipo;
}

function markerCumpleFiltroVista(marker) {
    if (!marker) {
        return false;
    }

    if (filtrosVistaMapa.has("todos")) {
        return true;
    }

    const tipoMarker = normalizarTipoEquipoMapa(marker.tipoEquipo);

    return filtrosVistaMapa.has(tipoMarker);
}

function aplicarFiltroVistaMapa() {
    Object.keys(markers).forEach((key) => {
        const marker = markers[key];

        if (!marker) {
            return;
        }

        marker.setMap(markerDebeMostrarse(marker) ? map : null);
    });

    actualizarLabelFiltroVistaMapa();
}

function actualizarLabelFiltroVistaMapa() {
    const label = document.getElementById("lblFiltroVistaMapa");

    if (!label) {
        return;
    }

    if (filtrosVistaMapa.has("todos")) {
        label.textContent = "Todos";
        return;
    }

    const textos = [];

    if (filtrosVistaMapa.has("Camion")) {
        textos.push("Tracto");
    }

    if (filtrosVistaMapa.has("ChasisA")) {
        textos.push("Chasis A");
    }

    if (filtrosVistaMapa.has("ChasisB")) {
        textos.push("Chasis B");
    }

    label.textContent = textos.length ? textos.join(", ") : "Ninguno";
}

$(document).on("change", ".filtroVistaMapaCheck", function () {
    const valor = $(this).val();

    if (valor === "todos") {
        const checked = this.checked;

        $(".filtroVistaMapaTipo").prop("checked", checked);

        filtrosVistaMapa.clear();

        if (checked) {
            filtrosVistaMapa.add("todos");
            filtrosVistaMapa.add("Camion");
            filtrosVistaMapa.add("ChasisA");
            filtrosVistaMapa.add("ChasisB");
        }

        aplicarFiltroVistaMapa();
        return;
    }

    if (this.checked) {
        filtrosVistaMapa.add(valor);
    } else {
        filtrosVistaMapa.delete(valor);
    }

    const todosTiposMarcados =
        $("#vistaCheckCamion").is(":checked") &&
        $("#vistaCheckChasisA").is(":checked") &&
        $("#vistaCheckChasisB").is(":checked");

    $("#vistaCheckTodos").prop("checked", todosTiposMarcados);

    filtrosVistaMapa.delete("todos");

    if (todosTiposMarcados) {
        filtrosVistaMapa.add("todos");
    }

    aplicarFiltroVistaMapa();
});

document.addEventListener("click", function (e) {
    const btnOpciones = e.target.closest(".btnOpcionesDispositivo");

    if (btnOpciones) {
        e.stopPropagation();

        const panelKey = btnOpciones.dataset.panelKey;

        const menuActual = document.querySelector(
            `.menuOpcionesDispositivo[data-panel-key="${panelKey}"]`,
        );

        document
            .querySelectorAll(".menuOpcionesDispositivo")
            .forEach((menu) => {
                if (menu !== menuActual) {
                    menu.style.display = "none";
                }
            });

        if (menuActual) {
            menuActual.style.display =
                menuActual.style.display === "block" ? "none" : "block";
        }

        return;
    }

    if (!e.target.closest(".menuOpcionesDispositivo")) {
        document
            .querySelectorAll(".menuOpcionesDispositivo")
            .forEach((menu) => {
                menu.style.display = "none";
            });
    }
});
