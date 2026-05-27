<!DOCTYPE html>
<html>

<head>
    <title>Rastreo</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />



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

        .gm-style-iw {
            padding: 0 !important;
        }

        .gm-style-iw-d {
            overflow: visible !important;
            padding: 0 !important;
        }

        .gm-style-iw-c {
            padding: 8px !important;
            border-radius: 14px !important;
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
        let colorMarker = null;
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

        function getRandomColor() {
            const min = 127;
            const max = 200;
            const r = Math.floor(Math.random() * (max - min + 1)) + min;
            const g = Math.floor(Math.random() * (max - min + 1)) + min;
            const b = Math.floor(Math.random() * (max - min + 1)) + min;
            return `rgb(${r}, ${g}, ${b})`;
        }

        function truncarTexto(texto, max = 23) {
            texto = String(texto ?? "");
            return texto.length > max ? texto.substring(0, max - 1) + "…" : texto;
        }

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
                tipoEquipo === "Camion" ?
                "TRACTO" :
                tipoEquipo === "ChasisA" ?
                "CHASIS A" :
                tipoEquipo === "ChasisB" ?
                "CHASIS B" :
                "GPS";

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
                "#" + [r, g, b]
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

        function createMarkerIconByStatus(status, item, containerColor = "#0d6efd") {
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

        function agregarItemRastreoContenedor(lista, info, cod) {
            if (!info) return;

            lista.push({
                tipo: "Contenedor",
                id: info.id_contenedor,
                value: cod,
            });
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
                        (info.tipo_viaje === "Full" || info.tipo_viaje === "full") ?
                        true :
                        false;

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
            titulo: "Chasis 1",
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
                      titulo: "Chasis 2 / Full",
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

        function actualizarUbicacion(items, t) {
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
                        items: items
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
                            const color = getRandomColor();



                            if (!colorMarker) {
                                colorMarker = color;
                            }

                            let extraertipoC = tipoSpans.replace(/Convoy\s*:\s*/, '').trim();
                            let datosGeocerca = convoysAll.find((c) => c.no_conboy === extraertipoC);

                            latlocal = parseFloat(item.ubicacion.lat);
                            lnglocal = parseFloat(item.ubicacion.lng);
                            idConvoyOContenedor = item.id_contenendor;

                            const markerKey = item.ubicacion.imei;
                            const velocidad = Number(item.ubicacion.velocidad ?? 0);

                            let estadoMarker = "offline";

                            if (latlocal && lnglocal) {
                                let moviendo = false;

                                if (velocidad > 5) {
                                    moviendo = true;
                                } else if (markers[markerKey]) {
                                    const posicionAnterior = markers[markerKey].getPosition();

                                    if (posicionAnterior) {
                                        const latAnterior = posicionAnterior.lat();
                                        const lngAnterior = posicionAnterior.lng();

                                        const diffLat = Math.abs(latlocal - latAnterior);
                                        const diffLng = Math.abs(lnglocal - lngAnterior);

                                        if (diffLat > 0.0001 || diffLng > 0.0001) {
                                            moviendo = true;
                                        }
                                    }
                                }

                                estadoMarker = moviendo ? "moving" : "parked";
                            }

                            const labelMarker = String(
                                item.contenedor ?? item.ubicacion?.imei ?? item.tipogps,
                            );
                            if (datosGeocerca) {
                                actualizarMapa(latlocal, lnglocal, datosGeocerca);
                            }
                            if (markers[markerKey]) {
                                markers[markerKey].setPosition({
                                    lat: latlocal,
                                    lng: lnglocal
                                });

                                markers[markerKey].setIcon(
                                    createMarkerIconByStatus(
                                        estadoMarker,
                                        item,
                                        colorMarker,
                                    ),
                                );
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
                                        icon: createMarkerIconByStatus(
                                            estadoMarker,
                                            item,
                                            colorMarker,
                                        ),
                                    });

                                    const contentC = `
<div style="
    background-color:${colorMarker};
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
                                    // const newMarker = L.marker([latlocal, lnglocal]).addTo(map).bindPopup(tipoSpans + ' '+ item.contenedor).openPopup();
                                    const infoWindow = new google.maps.InfoWindow({
                                        content: contentC,
                                    });
                                    // infoWindow.open(map, newMarker);
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
                            agregarItemRastreoContenedor(ItemsSelects, infoc, cod);
                        } else {
                            //buscamos en todos pero se valida si es convoy para saber si tenemos que buscar aunq no le pertenece el contenedor al user
                            if (tipoSpans.toLowerCase().includes('convoy')) {
                                const infoc2 = contenedoresDisponiblesAll.find((d) => d.contenedor === cod);
                                if (infoc2) {
                                    agregarItemRastreoContenedor(ItemsSelects, infoc2, cod);
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
                }, 8000);

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

                let info = contenedoresDisponibles.find(d =>
                    (d.contenedor && key.includes(d.contenedor)) ||
                    (d.imei && key.includes(d.imei)) ||
                    (d.imei_chasis && key.includes(d.imei_chasis))
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
