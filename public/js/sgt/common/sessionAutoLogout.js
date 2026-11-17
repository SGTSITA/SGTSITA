(function () {
    const timeoutMs = window.SGT_SESSION_TIMEOUT_MS || 30 * 60 * 1000;
    const logoutUrl = window.SGT_LOGOUT_URL;
    const loginUrl = window.SGT_LOGIN_URL;
    const keepAliveUrl = window.SGT_KEEPALIVE_URL || "/session/keep-alive";
    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]',
    )?.content;

    if (!logoutUrl || !loginUrl) {
        console.warn("No se configuró SGT_LOGOUT_URL o SGT_LOGIN_URL.");
        return;
    }

    const STORAGE_ACTIVITY_KEY = "sgt_last_activity_at";
    const STORAGE_SERVER_SYNC_KEY = "sgt_last_server_sync_at";
    const STORAGE_MAP_TRACKING_KEY = "sgt_map_tracking_active_at";

    const ACTIVITY_THROTTLE_MS = 5 * 1000;
    const SERVER_KEEPALIVE_INTERVAL_MS = 4 * 60 * 1000;
    const MAP_HEARTBEAT_TTL_MS = 45 * 1000;

    let timer = null;
    let lastLocalDomEventAt = 0;
    let keepAliveInFlight = false;

    function esElementoVisible(el) {
        if (!el) return false;
        if (el.closest(".d-none")) return false;
        const modalPadre = el.closest(".modal");
        if (modalPadre && !modalPadre.classList.contains("show")) {
            return false;
        }
        return !!(
            el.offsetWidth ||
            el.offsetHeight ||
            el.getClientRects().length
        );
    }

    function esModoRastreoMapa() {
        if (window.SGT_DISABLE_AUTO_LOGOUT === true) {
            return true;
        }

        const path = (window.location.pathname || "").toLowerCase();
        const prefijosRastreo = [
            "/coordenadas",
            "/mapa-comparacion",
            "/configurar-geocerca",
            "/gps",
            "/reporteria/consumo-unidades/mapa",
        ];

        if (prefijosRastreo.some((prefijo) => path.startsWith(prefijo))) {
            return true;
        }

        const contenedoresMapa = [
            document.getElementById("mapaRastreo"),
            document.getElementById("mapaComparacion"),
            document.getElementById("seccionMapa"),
            document.getElementById("cardGpsMapa"),
            document.getElementById("map"),
            ...document.querySelectorAll('[id^="map-"]'),
        ];

        return contenedoresMapa.some((el) => esElementoVisible(el));
    }

    function actualizarLatidoMapaLocal() {
        if (esModoRastreoMapa()) {
            const ahora = Date.now();
            try {
                localStorage.setItem(STORAGE_MAP_TRACKING_KEY, String(ahora));
                localStorage.setItem(STORAGE_ACTIVITY_KEY, String(ahora));
            } catch (e) {
                // Ignorar errores de quota/storage
            }
            return true;
        }
        return false;
    }

    function hayRastreoMapaActivoEnCualquierPestana() {
        if (esModoRastreoMapa()) {
            return true;
        }
        try {
            const ultimoLatidoMapa = parseInt(
                localStorage.getItem(STORAGE_MAP_TRACKING_KEY) || "0",
                10,
            );
            return (
                ultimoLatidoMapa > 0 &&
                Date.now() - ultimoLatidoMapa <= MAP_HEARTBEAT_TTL_MS
            );
        } catch (e) {
            return false;
        }
    }

    function obtenerUltimaActividadGlobal() {
        try {
            const guardada = parseInt(
                localStorage.getItem(STORAGE_ACTIVITY_KEY) || "0",
                10,
            );
            return guardada > 0 ? guardada : Date.now();
        } catch (e) {
            return Date.now();
        }
    }

    function obtenerUltimaSincronizacionServidor() {
        try {
            return parseInt(
                localStorage.getItem(STORAGE_SERVER_SYNC_KEY) || "0",
                10,
            );
        } catch (e) {
            return 0;
        }
    }

    function registrarActividadUsuario(sincronizadoConServidor = false) {
        const ahora = Date.now();
        try {
            localStorage.setItem(STORAGE_ACTIVITY_KEY, String(ahora));
            if (sincronizadoConServidor) {
                localStorage.setItem(STORAGE_SERVER_SYNC_KEY, String(ahora));
            }
        } catch (e) {
            // Ignorar errores de storage
        }
        programarCierreSesion();
    }

    async function enviarKeepAliveServidor() {
        if (keepAliveInFlight) return;

        const ahora = Date.now();
        const ultimaSync = obtenerUltimaSincronizacionServidor();
        if (ahora - ultimaSync < SERVER_KEEPALIVE_INTERVAL_MS) {
            return;
        }

        keepAliveInFlight = true;
        try {
            localStorage.setItem(STORAGE_SERVER_SYNC_KEY, String(ahora));
            await fetch(keepAliveUrl, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken || "",
                    "X-Requested-With": "XMLHttpRequest",
                    "X-SGT-Map-Tracking": hayRastreoMapaActivoEnCualquierPestana()
                        ? "1"
                        : "0",
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                credentials: "same-origin",
            });
        } catch (e) {
            // Silencioso en caso de desconexión temporal
        } finally {
            keepAliveInFlight = false;
        }
    }

    function programarCierreSesion() {
        clearTimeout(timer);

        const ahora = Date.now();
        const ultimaActividad = obtenerUltimaActividadGlobal();
        const transcurrido = Math.max(0, ahora - ultimaActividad);
        const restante = Math.max(5000, timeoutMs - transcurrido);

        timer = setTimeout(() => {
            verificarYCerrarSesionPorTiempo();
        }, restante);
    }

    async function verificarYCerrarSesionPorTiempo() {
        if (hayRastreoMapaActivoEnCualquierPestana()) {
            registrarActividadUsuario(false);
            enviarKeepAliveServidor();
            return;
        }

        const ahora = Date.now();
        const ultimaActividad = obtenerUltimaActividadGlobal();
        if (ahora - ultimaActividad < timeoutMs) {
            programarCierreSesion();
            return;
        }

        await cerrarSesionPorTiempo();
    }

    async function cerrarSesionPorTiempo() {
        try {
            await fetch(logoutUrl, {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": csrfToken,
                    Accept: "application/json",
                    "Content-Type": "application/json",
                },
                credentials: "same-origin",
            });
        } catch (error) {
            console.warn(
                "No se pudo cerrar sesión por fetch. Redirigiendo al login.",
                error,
            );
        }

        window.location.href = loginUrl;
    }

    function manejarRespuestaExpirada(response) {
        if (!response) return;

        if (response.status === 401 || response.status === 419) {
            if (hayRastreoMapaActivoEnCualquierPestana()) {
                return;
            }
            response
                .clone()
                .json()
                .then((data) => {
                    window.location.href = data?.redirect || loginUrl;
                })
                .catch(() => {
                    window.location.href = loginUrl;
                });
        }
    }

    function esRequestMismoOrigen(input) {
        try {
            const url = typeof input === "string" ? input : input?.url;

            if (!url) return true;

            const requestUrl = new URL(url, window.location.origin);

            return requestUrl.origin === window.location.origin;
        } catch (e) {
            return true;
        }
    }

    function esRutaPasiva(url) {
        try {
            const rawUrl = typeof url === "string" ? url : url?.url || "";
            const requestUrl = new URL(rawUrl, window.location.origin);
            const path = requestUrl.pathname;

            const rutasPasivas = [
                "/notificaciones/usuario/listar",
                "/notificaciones/usuario/contador",
                "/whatsapp/status",
                "/whatsapp/check",
            ];

            return rutasPasivas.some((ruta) => path.startsWith(ruta));
        } catch (e) {
            return false;
        }
    }

    if (window.fetch) {
        const originalFetch = window.fetch;

        window.fetch = async function (...args) {
            const response = await originalFetch.apply(this, args);

            if (esRequestMismoOrigen(args[0])) {
                manejarRespuestaExpirada(response);

                if (response.ok && !esRutaPasiva(args[0])) {
                    registrarActividadUsuario(true);
                }
            }

            return response;
        };
    }

    const originalOpen = XMLHttpRequest.prototype.open;
    const originalSend = XMLHttpRequest.prototype.send;

    XMLHttpRequest.prototype.open = function (method, url) {
        this.__sgtRequestUrl = url || null;
        return originalOpen.apply(this, arguments);
    };

    XMLHttpRequest.prototype.send = function () {
        this.addEventListener("loadend", function () {
            try {
                const requestUrl = this.__sgtRequestUrl;

                if (!requestUrl) {
                    return;
                }

                if (
                    typeof esRequestMismoOrigen === "function" &&
                    !esRequestMismoOrigen(requestUrl)
                ) {
                    return;
                }

                if (this.status === 401 || this.status === 419) {
                    if (!hayRastreoMapaActivoEnCualquierPestana()) {
                        window.location.href = loginUrl;
                    }
                    return;
                }

                const esExitosa = this.status >= 200 && this.status < 300;
                const rutaPasiva =
                    typeof esRutaPasiva === "function"
                        ? esRutaPasiva(requestUrl)
                        : false;

                if (esExitosa && !rutaPasiva) {
                    registrarActividadUsuario(true);
                }
            } catch (error) {
                console.warn(
                    "Error controlado en interceptor XMLHttpRequest:",
                    error,
                );
            }
        });

        return originalSend.apply(this, arguments);
    };

    // Escuchar interacción real del usuario en la interfaz (mouse, teclado, scroll, touch)
    const eventosInteraccion = [
        "mousemove",
        "mousedown",
        "keydown",
        "scroll",
        "touchstart",
        "click",
        "input",
        "change",
    ];

    function manejarInteraccionDom() {
        const ahora = Date.now();
        if (ahora - lastLocalDomEventAt < ACTIVITY_THROTTLE_MS) {
            return;
        }
        lastLocalDomEventAt = ahora;
        registrarActividadUsuario(false);

        if (ahora - obtenerUltimaSincronizacionServidor() >= SERVER_KEEPALIVE_INTERVAL_MS) {
            enviarKeepAliveServidor();
        }
    }

    eventosInteraccion.forEach((evento) => {
        window.addEventListener(evento, manejarInteraccionDom, {
            passive: true,
        });
    });

    // Sincronizar entre múltiples pestañas abiertas
    window.addEventListener("storage", function (event) {
        if (
            event.key === STORAGE_ACTIVITY_KEY ||
            event.key === STORAGE_MAP_TRACKING_KEY
        ) {
            programarCierreSesion();
        }
    });

    // Latido periódico para pantallas de rastreo de mapas y sincronización con servidor
    setInterval(() => {
        const enMapa = actualizarLatidoMapaLocal();
        const ahora = Date.now();
        const ultimaActividad = obtenerUltimaActividadGlobal();
        const usuarioActivoReciente =
            ahora - ultimaActividad <= ACTIVITY_THROTTLE_MS * 12;

        if (enMapa || hayRastreoMapaActivoEnCualquierPestana() || usuarioActivoReciente) {
            programarCierreSesion();
            if (
                ahora - obtenerUltimaSincronizacionServidor() >=
                SERVER_KEEPALIVE_INTERVAL_MS
            ) {
                enviarKeepAliveServidor();
            }
        }
    }, 15 * 1000);

    registrarActividadUsuario(true);
    actualizarLatidoMapaLocal();
})();
