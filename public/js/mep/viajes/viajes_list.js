let operadores = [];
let unidades = [];
let distanciaEquipos = 0;
let equipoActivos = 0;
let cargaIni = true;

const formFieldsMep = [
    {
        field: "txtOperador",
        id: "txtOperador",
        label: "Nombre operador",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtTelefono",
        id: "txtTelefono",
        label: "Teléfono",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtNumUnidad",
        id: "txtNumUnidad",
        label: "Núm Eco/ Núm Unidad / Identificador",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtPlacas",
        id: "txtPlacas",
        label: "Placas",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtSerie",
        id: "txtSerie",
        label: "Núm Serie / VIN",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "selectGPS",
        id: "selectGPS",
        label: "Compañia GPS",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtImei",
        id: "txtImei",
        label: "IMEI",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtTipoViaje",
        id: "txtTipoViaje",
        label: "Tipo Viaje",
        required: false,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtFechaInicio",
        id: "txtFechaInicio",
        label: "Fecha Salida",
        required: false,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtFechaFinal",
        id: "txtFechaFinal",
        label: "Fecha Entrega",
        required: false,
        type: "text",
        trigger: "none",
    },

    {
        field: "txtNumChasisA",
        id: "txtNumChasisA",
        label: "Núm Eco/ Núm Chasis / Identificador",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtPlacasA",
        id: "txtPlacasA",
        label: "Placas Chasis A",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "selectChasisAGPS",
        id: "selectChasisAGPS",
        label: "Compañia GPS Chasis A",
        required: true,
        type: "text",
        trigger: "none",
    },
    {
        field: "txtImeiChasisA",
        id: "txtImeiChasisA",
        label: "IMEI Chasis A",
        required: true,
        type: "text",
        trigger: "none",
    },

    {
        field: "txtNumChasisB",
        id: "txtNumChasisB",
        label: "Núm Eco/ Núm Chasis B / Identificador",
        required: false,
        type: "text",
        trigger: "txtTipoViaje",
        expectedValue: "Full",
        labelTrigger: "Tipo Viaje",
    },
    {
        field: "txtPlacasB",
        id: "txtPlacasB",
        label: "Placas Chasis B",
        required: false,
        type: "text",
        trigger: "txtNumChasisB",
    },
    {
        field: "selectChasisBGPS",
        id: "selectChasisBGPS",
        label: "Compañia GPS Chasis B",
        required: false,
        type: "text",
        trigger: "txtNumChasisB",
    },
    {
        field: "txtImeiChasisB",
        id: "txtImeiChasisB",
        label: "IMEI Chasis B",
        required: false,
        type: "text",
        trigger: "txtNumChasisB",
    },
    {
        field: "cmbProveedor",
        id: "cmbProveedor",
        label: "Proveedor",
        required: false,
        type: "hidden",
        trigger: "none",
    },
];

const normalizarFecha = (valueFecha) => {
    if (!valueFecha) return null;

    if (valueFecha.includes("/")) {
        const [d, m, y] = valueFecha.split("/");
        return `${y}-${m}-${d}`;
    }

    return valueFecha.substring(0, 10);
};

document.getElementById("btnMapaUnidad").addEventListener("click", function () {
    // ocultar formulario
    document.getElementById("formPlaneacion").classList.add("d-none");

    // mostrar mapa
    document.getElementById("seccionMapa").classList.remove("d-none");

    // asegurar modal abierto
    $("#viajeModal").modal("show");

    // inicializar mapa
    setTimeout(() => {
        googleMapsReady();
    }, 200);
});

document
    .getElementById("btnRegresarPlaneacion")
    .addEventListener("click", function () {
        // ocultar mapa
        document.getElementById("seccionMapa").classList.add("d-none");

        // mostrar formulario
        document.getElementById("formPlaneacion").classList.remove("d-none");
    });
const btnAsignaOperador = document.querySelector("#btnAsignaOperador");
const btnPlanearViaje = document.querySelector("#btnPlanearViaje");

function asignarOperador2(planear = 0) {
    const formData = {};
    distanciaEquipos = 0;
    actualizarDistanciaEquipos();

    if (distanciaEquipos > 10 && planear == 1) {
        Swal.fire(
            `Planeacion viajes`,
            `Los equipos que intenta planear no estar cercanos , y no se pueden planear.`,
            "warning",
        );
        return false;
    }

    let equiposInvalidos = [];

    Object.keys(mapInputs).forEach((key) => {
        const config = mapInputs[key];

        if (!esEquipoValido(config)) {
            const input = document.getElementById(config.input);
            const placas = document.getElementById(config.placas);

            equiposInvalidos.push({
                tipo: key,
                unidad: input?.value || "Sin unidad",
                placas: placas?.value || "Sin placas",
            });
        }
    });

    if (equiposInvalidos.length > 0 && planear == 1) {
        let html = equiposInvalidos
            .map((e) => {
                return `
            <div style="margin-bottom:8px;">
                Tipo:  <b>${e.tipo}</b><br>
                Unidad: ${e.unidad}<br>
                Placas: ${e.placas}
            </div>
        `;
            })
            .join("");

        Swal.fire({
            icon: "error",
            title: "No se puede Planear,Equipos sin conexión",
            html: `
            <div style="text-align:left">
                ${html}
                <hr>
                Verifica GPS, IMEI y coordenadas.
            </div>
        `,
        });

        return false;
    }

    //formFieldsMep
    let passValidation = formFieldsMep.every((item) => {
        let field = document.getElementById(item.field);
        let trigger = item.trigger;

        if (trigger != "none") {
            let primaryField = document.getElementById(trigger);

            if (
                field.value.length <= 0 &&
                primaryField.value == item.expectedValue
            ) {
                if (field.value.length === 0) {
                    Swal.fire(
                        `El campo "${item.label}" es condicional y está vacío`,
                        `El campo "${item.label}" es obligatorio cuando el valor de ${item.labelTrigger} es ${item.expectedValue}. Por favor proporcione está información.`,
                        "warning",
                    );
                    return false;
                }
            }
        }

        if (field) {
            if (item.required === true && field.value.length == 0) {
                Swal.fire(
                    "El campo " + item.label + " es obligatorio",
                    "Parece que no ha proporcionado información en el campo " +
                        item.label,
                    "warning",
                );
                return false;
            }

            //validar fechas de inicio y fin si se planea el viaje.
            if (
                planear === 1 &&
                (item.field === "txtFechaInicio" ||
                    item.field === "txtFechaFinal")
            ) {
                let valueFecha = field.value;

                if (!valueFecha) {
                    Swal.fire(
                        "El campo " +
                            item.label +
                            " es obligatorio al planear el viaje",
                        "Parece que no ha proporcionado información en el campo " +
                            item.label,
                        "warning",
                    );
                    return false;
                }
                //formatear fecha para yyy-mm-dd hh:mm:ss
                let fechaObj = normalizarFecha(valueFecha);

                formData[item.field] = fechaObj;
                return true;
            }
        }

        if (field.dataset.mepUnidad) {
            formData["mepUnidad"] = field.dataset.mepUnidad;
        }

        if (field.dataset.mepOperador) {
            formData["mepOperador"] = field.dataset.mepOperador;
        }

        formData[item.field] = field.value;

        return true;
    });
    formData["planear"] = planear;

    if (!passValidation) return passValidation;

    let idContenedor = localStorage.getItem("idContenedor");

    let data = { idContenedor: idContenedor, formData: formData };
    fetch("/mep/viajes/operador/asignar", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": document
                .querySelector('meta[name="csrf-token"]')
                ?.getAttribute("content"),
        },
        body: JSON.stringify(data),
    })
        .then((response) => {
            if (!response.ok) {
                throw new Error("Error en la respuesta del servidor");
            }
            return response.json();
        })
        .then((data) => {
            console.log("Respuesta del backend:", data);

            Swal.fire(data.Titulo, data.Mensaje, data.TMensaje);
        })
        .catch((error) => {
            console.error("Error al enviar los datos:", error);
            alert("Ocurrió un error al asignar el operador.");
        });
}
if (btnAsignaOperador) {
    btnAsignaOperador.addEventListener("click", () => asignarOperador2(0));
}
if (btnPlanearViaje) {
    btnPlanearViaje.addEventListener("click", () => asignarOperador2(1));
}

// async function buscarRecurso(params = {}) {
//     const query = new URLSearchParams(params).toString();

//     try {
//         const response = await fetch(`/api/buscar-recursos?${query}`, {
//             headers: {
//                 Accept: 'application/json',
//             },
//         });

//         if (!response.ok) {
//             throw new Error('Error en la búsqueda');
//         }

//         return await response.json();
//     } catch (error) {
//         console.error(error);
//         return [];
//     }
// }

function buscarOperador(nombre) {
    let operador = operadores.find((op) => {
        return op.nombre === nombre ? op : false;
    });

    let txtTelefono = document.querySelector("#txtTelefono");

    toastr.options.positionClass = "toast-middle-center";
    let txtOperador = document.querySelector("#txtOperador");

    if (operador) {
        txtTelefono.value = operador.telefono;
        txtOperador.dataset.mepOperador = operador.id;
        toastr.success("Operador identificado");
    } else {
        txtTelefono.value = "";
        txtOperador.dataset.mepOperador = 0;
        toastr.warning("Operador no encontrado");
    }
}
const mapInputs = {
    Unidad: {
        input: "txtNumUnidad",
        box: "sugerenciasUnidad",
        tipoFiltro: "Tractos / Camiones",
        placas: "txtPlacas",
        serie: "txtSerie",
        imei: "txtImei",
        gps: "selectGPS",

        // NUEVO
        statusGps: "gpsStatusUnidad",
        latitud: null,
        longitud: null,
    },

    ChasisA: {
        input: "txtNumChasisA",
        box: "sugerenciasChasisA",
        tipoFiltro: "Chasis / Plataforma",
        placas: "txtPlacasA",
        serie: null,
        imei: "txtImeiChasisA",
        gps: "selectChasisAGPS",

        // NUEVO
        statusGps: "gpsStatusChasisA",
        latitud: null,
        longitud: null,
    },

    ChasisB: {
        input: "txtNumChasisB",
        box: "sugerenciasChasisB",
        tipoFiltro: "Chasis / Plataforma",
        placas: "txtPlacasB",
        serie: null,
        imei: "txtImeiChasisB",
        gps: "selectChasisBGPS",

        // NUEVO
        statusGps: "gpsStatusChasisB",
        latitud: null,
        longitud: null,
    },
};

function coordenadasValidas(lat, lng) {
    lat = parseFloat(lat);
    lng = parseFloat(lng);

    return !isNaN(lat) && !isNaN(lng) && lat !== 0 && lng !== 0;
}
function esEquipoValido(config) {
    const input = document.getElementById(config.input);
    const imei = document.getElementById(config.imei);
    const gps = document.getElementById(config.gps);

    const lat = config.latitud;
    const lng = config.longitud;

    const tieneInput = input && input.value.trim() !== "";
    const tieneImei = imei && imei.value.trim() !== "";
    const tieneGps = gps && gps.value !== "";
    if (tieneInput && tieneImei && tieneGps) {
        const coordsOk = coordenadasValidas(lat, lng);

        return coordsOk;
    }
    return true;
}
function buscarUnidad(numUnidad, sTipo) {
    let unidad = unidades.find((u) => {
        return u.id_equipo === numUnidad.toUpperCase() ? u : false;
    });

    // let txtPlacas = document.querySelector('#txtPlacas')
    // let txtSerie = document.querySelector('#txtSerie')
    // let txtImei = document.querySelector('#txtImei')
    // let selectGPS = document.querySelector('#selectGPS')

    // let txtNumUnidad = document.querySelector("#txtNumUnidad")

    let config = mapInputs[sTipo];

    toastr.options.positionClass = "toast-middle-center";
    if (unidad) {
        if (config.placas)
            document.getElementById(config.placas).value = unidad.placas ?? "";

        if (config.serie)
            document.getElementById(config.serie).value =
                unidad.num_serie ?? "";

        if (config.imei)
            document.getElementById(config.imei).value = unidad.imei ?? "";

        if (config.gps) {
            let select = document.getElementById(config.gps);
            for (let i = 0; i < select.options.length; i++) {
                if (
                    String(select.options[i].value) ===
                    String(unidad.gps_company_id)
                ) {
                    select.selectedIndex = i;
                    break;
                }
            }
        }

        document.getElementById(config.dataset).dataset.mepUnidad = unidad.id;

        toastr.success("Unidad identificado");
    } else {
        if (config.placas) document.getElementById(config.placas).value = "";

        if (config.serie) document.getElementById(config.serie).value = "";

        if (config.imei) document.getElementById(config.imei).value = "";

        if (config.gps) document.getElementById(config.gps).selectedIndex = 0;

        document.getElementById(config.dataset).dataset.mepUnidad = 0;
        toastr.warning("No se encontró unidad");
    }
}

function getCatalogoOperadorUnidad() {
    let _token = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");
    $.ajax({
        url: "/mep/catalogos/operador-unidad",
        type: "post",
        data: { _token },
        beforeSend: () => {},
        success: (response) => {
            operadores = response.operadores;
            unidades = response.unidades;
        },
        error: () => {
            console.error(
                "No pudimos obtener los datos de operadores y unidades de la empresa.",
            );
        },
    });
}

const inputOperador = document.getElementById("txtOperador");
const boxOperador = document.getElementById("sugerenciasOperador");

inputOperador.addEventListener("input", function () {
    const valor = this.value.toLowerCase().trim();

    this.dataset.mepOperador = 0;
    document.getElementById("txtTelefono").value = "";

    if (!valor) {
        boxOperador.style.display = "none";
        return;
    }

    const resultados = operadores.filter((op) =>
        op.nombre.toLowerCase().includes(valor),
    );

    if (resultados.length === 0) {
        boxOperador.style.display = "none";
        return;
    }

    boxOperador.innerHTML = "";

    resultados.forEach((op) => {
        const item = document.createElement("div");

        item.textContent = op.nombre;
        item.style.padding = "8px";
        item.style.cursor = "pointer";

        item.onmouseenter = () => (item.style.background = "#f1f1f1");
        item.onmouseleave = () => (item.style.background = "white");

        item.onclick = () => {
            inputOperador.value = op.nombre;
            inputOperador.dataset.mepOperador = op.id;

            document.getElementById("txtTelefono").value = op.telefono;

            boxOperador.style.display = "none";

            toastr.success("Operador seleccionado");
        };

        boxOperador.appendChild(item);
    });

    boxOperador.style.display = "block";
});

inputOperador.addEventListener("blur", () => {
    setTimeout(() => {
        boxOperador.style.display = "none";
    }, 200);
});
function actualizarBotonMapa() {
    const cardmapa = document.getElementById("cardGpsMapa");

    if (!cardmapa) return;

    const tieneCoords = [
        mapInputs.Unidad,
        mapInputs.ChasisA,
        mapInputs.ChasisB,
    ].some((item) => {
        return (
            item.latitud &&
            item.longitud &&
            parseFloat(item.latitud) !== 0 &&
            parseFloat(item.longitud) !== 0
        );
    });

    if (tieneCoords) {
        cardmapa.classList.remove("d-none");

        //   document.getElementById("cardGpsMapa").classList.remove("d-none");
    } else {
        cardmapa.classList.add("d-none");
        //  document.getElementById("cardGpsMapa").classList.add("d-none");
    }
}
async function validarConexionGPS(tipoKey, imei, gpsCompanyId, equipos = []) {
    const config = mapInputs[tipoKey];

    if (!imei || !gpsCompanyId) {
        actualizarEstadoGPS(config.statusGps, "secondary", "Sin GPS");

        config.latitud = null;
        config.longitud = null;

        return false;
    }
    actualizarBotonMapa();
    actualizarEstadoGPS(config.statusGps, "warning", "Conectando GPS...");

    try {
        const response = await fetch("/mep/viajes/ubicaciones", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": document
                    .querySelector('meta[name="csrf-token"]')
                    .getAttribute("content"),
            },
            body: JSON.stringify({
                equipos: equipos,
            }),
        });

        const data = await response.json();
        const ubi = data[0]?.ubicacion ?? null;

        if (ubi.lat && ubi.lng) {
            actualizarEstadoGPS(config.statusGps, "success", "Equipo en línea");

            config.latitud = parseFloat(ubi.lat);
            config.longitud = parseFloat(ubi.lng);
            actualizarBotonMapa();
            actualizarDistanciaEquipos();
            return true;
        } else {
            actualizarEstadoGPS(config.statusGps, "danger", "GPS sin señal");

            config.latitud = null;
            config.longitud = null;

            return false;
        }
    } catch (e) {
        actualizarEstadoGPS(config.statusGps, "danger", "Error conexión GPS");

        config.latitud = null;
        config.longitud = null;

        console.error(e);

        return false;
    }
}

function calcularDistanciaKm(lat1, lon1, lat2, lon2) {
    const R = 6371;

    const dLat = ((lat2 - lat1) * Math.PI) / 180;
    const dLon = ((lon2 - lon1) * Math.PI) / 180;

    const a =
        Math.sin(dLat / 2) * Math.sin(dLat / 2) +
        Math.cos((lat1 * Math.PI) / 180) *
            Math.cos((lat2 * Math.PI) / 180) *
            Math.sin(dLon / 2) *
            Math.sin(dLon / 2);

    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));

    return R * c;
}

function guardarDistancia(distancia) {
    if (distancia > distanciaEquipos) {
        distanciaEquipos = distancia;
    }
}

function actualizarDistanciaEquipos() {
    const unidad = mapInputs.Unidad;

    const chasisA = mapInputs.ChasisA;

    const chasisB = mapInputs.ChasisB;

    const lbl = document.getElementById("lblDistanciaEquipos");

    if (!lbl) return;

    let html = "";

    function renderDistancia(nombreA, itemA, nombreB, itemB) {
        if (
            !itemA.latitud ||
            !itemA.longitud ||
            !itemB.latitud ||
            !itemB.longitud
        ) {
            return "";
        }

        const distancia = calcularDistanciaKm(
            parseFloat(itemA.latitud),
            parseFloat(itemA.longitud),
            parseFloat(itemB.latitud),
            parseFloat(itemB.longitud),
        );

        guardarDistancia(distancia);

        return `
            <div class="d-flex align-items-center justify-content-between mb-1">

                <span class="small text-dark">
                    ${nombreA} ↔ ${nombreB}
                </span>

                <span class="
                    badge rounded-pill
                    ${distancia > 10 ? "bg-danger" : "bg-success"}
                ">
                    ${distancia.toFixed(2)} KM
                </span>

            </div>
        `;
    }

    html += renderDistancia("Unidad", unidad, "Chasis A", chasisA);

    if (chasisB.latitud && chasisB.longitud) {
        html += renderDistancia("Unidad", unidad, "Chasis B", chasisB);

        html += renderDistancia("Chasis A", chasisA, "Chasis B", chasisB);
    }

    if (!html) {
        lbl.innerHTML = `
            <span class="text-muted small">
                Sin datos GPS
            </span>
        `;

        return;
    }

    lbl.innerHTML = html;
}
//sugerencias unidad/chasis A y chasis B
function initAutocompleteUnidad(tipoKey) {
    const config = mapInputs[tipoKey];

    const input = document.getElementById(config.input);
    const box = document.getElementById(config.box);

    input.addEventListener("input", function () {
        const valor = this.value.toLowerCase().trim();

        this.dataset.mepUnidad = 0;

        config.latitud = null;
        config.longitud = null;

        actualizarEstadoGPS(config.statusGps, "secondary", "Sin validar");

        if (config.placas) document.getElementById(config.placas).value = "";
        if (config.serie) document.getElementById(config.serie).value = "";
        if (config.imei) document.getElementById(config.imei).value = "";
        if (config.gps) document.getElementById(config.gps).selectedIndex = 0;

        if (!valor) {
            box.style.display = "none";
            return;
        }

        const resultados = unidades.filter(
            (u) =>
                u.tipo === config.tipoFiltro &&
                (u.id_equipo + u.placas || "").toLowerCase().includes(valor),
        );

        if (resultados.length === 0) {
            box.style.display = "none";
            return;
        }

        box.innerHTML = "";

        resultados.forEach((u) => {
            const item = document.createElement("div");

            item.innerHTML = `
                <div style="font-size:13px;">
                    <strong>${u.id_equipo}</strong><br>
                    <small>${u.placas || "Sin placas"}</small>
                </div>
            `;

            item.style.padding = "8px";
            item.style.cursor = "pointer";

            item.onmouseenter = () => (item.style.background = "#f1f1f1");
            item.onmouseleave = () => (item.style.background = "white");

            item.onclick = async () => {
                input.value = u.id_equipo;
                input.dataset.mepUnidad = u.id;

                if (config.placas)
                    document.getElementById(config.placas).value =
                        u.placas ?? "";

                if (config.serie)
                    document.getElementById(config.serie).value =
                        u.num_serie ?? "";

                if (config.imei)
                    document.getElementById(config.imei).value = u.imei ?? "";

                if (config.gps) {
                    let select = document.getElementById(config.gps);

                    for (let i = 0; i < select.options.length; i++) {
                        if (
                            String(select.options[i].value) ===
                            String(u.gps_company_id)
                        ) {
                            select.selectedIndex = i;
                            break;
                        }
                    }
                }

                box.style.display = "none";

                toastr.success(`${tipoKey} seleccionado`);

                if (u.imei && u.gps_company_id) {
                    await validarConexionGPS(
                        tipoKey,
                        u.imei,
                        u.gps_company_id,
                        [u.id],
                    );
                } else {
                    actualizarEstadoGPS(
                        config.statusGps,
                        "danger",
                        "Equipo sin IMEI configurado",
                    );
                }
            };

            box.appendChild(item);
        });

        box.style.display = "block";
    });

    input.addEventListener("blur", () => {
        setTimeout(() => {
            box.style.display = "none";
        }, 200);
    });
}

initAutocompleteUnidad("Unidad");
initAutocompleteUnidad("ChasisA");
initAutocompleteUnidad("ChasisB");

//
function abrirDocumentos(idCotizacion) {
    $(`#estatusDoc${idCotizacion}`).modal("show");
}

function descargarPDF(idCotizacion) {
    const fecha = new Date().toISOString().slice(0, 10); // formato: YYYY-MM-DD
    const link = document.createElement("a");
    link.href = `/cotizaciones/pdf/${idCotizacion}`;
    link.download = `cotizacion_${idCotizacion}_${fecha}.pdf`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function abrirDocumentos(idCotizacion) {
    fetch(`/cotizaciones/documentos/${idCotizacion}`)
        .then((response) => response.json())
        .then((data) => {
            const modal = new bootstrap.Modal(
                document.getElementById("modalEstatusDocumentos"),
            );
            const titulo = document.getElementById("tituloContenedor");
            const cuerpo = document.getElementById("estatusDocumentosBody");

            titulo.innerText = `#${data.num_contenedor ?? "N/A"}`;
            cuerpo.innerHTML = "";

            const campos = [
                { label: "Num contenedor", valor: data.num_contenedor },
                { label: "Documento CCP", valor: data.doc_ccp },
                {
                    label: "Boleta de Liberación",
                    valor: data.boleta_liberacion,
                },
                { label: "Doda", valor: data.doda },
                { label: "Carta Porte", valor: data.carta_porte },
                { label: "Boleta Vacio", valor: data.boleta_vacio === "si" },
                { label: "EIR", valor: data.doc_eir },
                // { label: 'Foto Patio', valor: data.foto_patio },
            ];

            campos.forEach((item) => {
                const col = document.createElement("div");
                col.className = "col-6";
                col.innerHTML = `
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid ${item.valor ? "fa-check-circle text-success" : "fa-times-circle text-muted"}"></i>
                        <span class="fw-semibold">${item.label}</span>
                    </div>
                `;
                cuerpo.appendChild(col);
            });

            modal.show();
        })
        .catch((error) => {
            console.error("Error al obtener documentos:", error);
            Swal.fire(
                "Error",
                "No se pudieron obtener los documentos",
                "error",
            );
        });
}

function cambiarTab(tabId) {
    const tabs = document.querySelectorAll(".tab-content");
    tabs.forEach((tab) => {
        tab.style.display = "none";
    });

    const tabToShow = document.getElementById("tab-" + tabId);
    if (tabToShow) {
        tabToShow.style.display = "block";
    } else {
        console.error(`No se encontró el tab: tab-${tabId}`);
    }
}

function actualizarEstadoGPS(id, tipo, texto) {
    const clases = {
        success: "text-success",
        danger: "text-danger",
        warning: "text-warning",
        muted: "text-muted",
    };

    const iconos = {
        success: "fa-circle",
        danger: "fa-triangle-exclamation",
        warning: "fa-spinner fa-spin",
        muted: "fa-minus-circle",
    };

    $("#" + id)
        .removeClass()
        .addClass(`small fw-bold ${clases[tipo]}`).html(`
            <i class="fas ${iconos[tipo]}"></i>
            ${texto}
        `);
}

let mapaEquiposInstance = null;

function googleMapsReady() {
    const equipos = [
        {
            nombre: "Unidad",
            data: mapInputs.Unidad,
            icono: "http://maps.google.com/mapfiles/ms/icons/blue-dot.png",
        },
        {
            nombre: "Chasis A",
            data: mapInputs.ChasisA,
            icono: "http://maps.google.com/mapfiles/ms/icons/green-dot.png",
        },
        {
            nombre: "Chasis B",
            data: mapInputs.ChasisB,
            icono: "http://maps.google.com/mapfiles/ms/icons/orange-dot.png",
        },
    ];

    const equiposValidos = equipos.filter((e) => {
        return (
            e.data.latitud &&
            e.data.longitud &&
            parseFloat(e.data.latitud) !== 0 &&
            parseFloat(e.data.longitud) !== 0
        );
    });

    if (equiposValidos.length === 0 && !cargaIni) {
        Swal.fire({
            icon: "warning",
            title: "Sin ubicación GPS",
            text: "No hay equipos con coordenadas válidas.",
        });

        return;
    }

    cargaIni = false;

    const centro = {
        lat: parseFloat(equiposValidos[0].data.latitud),
        lng: parseFloat(equiposValidos[0].data.longitud),
    };

    mapaEquiposInstance = new google.maps.Map(
        document.getElementById("mapaEquipos"),
        {
            zoom: 10,
            center: centro,
            mapTypeId: "roadmap",
        },
    );

    const bounds = new google.maps.LatLngBounds();

    equiposValidos.forEach((equipo) => {
        const lat = parseFloat(equipo.data.latitud);

        const lng = parseFloat(equipo.data.longitud);

        const marker = new google.maps.Marker({
            position: { lat, lng },
            map: mapaEquiposInstance,
            title: equipo.nombre,
            animation: google.maps.Animation.DROP,
            icon: equipo.icono,
        });

        bounds.extend({ lat, lng });

        const infoWindow = new google.maps.InfoWindow({
            content: `
                <div style="min-width:220px;">

                    <div class="fw-bold mb-1">
                        ${equipo.nombre}
                    </div>

                    <div>
                        <strong>Equipo:</strong>
                        ${document.getElementById(equipo.data.input)?.value || "N/D"}
                    </div>

                    <div>
                        <strong>IMEI:</strong>
                        ${document.getElementById(equipo.data.imei)?.value || "N/D"}
                    </div>

                    <div>
                        <strong>Lat:</strong>
                        ${lat}
                    </div>

                    <div>
                        <strong>Lng:</strong>
                        ${lng}
                    </div>

                </div>
            `,
        });

        marker.addListener("click", () => {
            infoWindow.open({
                anchor: marker,
                map: mapaEquiposInstance,
            });
        });
    });

    mapaEquiposInstance.fitBounds(bounds);

    function dibujarLinea(a, b, color = "#0d6efd") {
        if (
            !a.data.latitud ||
            !a.data.longitud ||
            !b.data.latitud ||
            !b.data.longitud
        ) {
            return;
        }

        new google.maps.Polyline({
            path: [
                {
                    lat: parseFloat(a.data.latitud),
                    lng: parseFloat(a.data.longitud),
                },
                {
                    lat: parseFloat(b.data.latitud),
                    lng: parseFloat(b.data.longitud),
                },
            ],
            geodesic: true,
            strokeColor: color,
            strokeOpacity: 0.8,
            strokeWeight: 3,
            map: mapaEquiposInstance,
        });
    }

    dibujarLinea(equipos[0], equipos[1], "#198754");

    if (mapInputs.ChasisB.latitud && mapInputs.ChasisB.longitud) {
        dibujarLinea(equipos[0], equipos[2], "#dc3545");

        dibujarLinea(equipos[1], equipos[2], "#fd7e14");
    }
}
