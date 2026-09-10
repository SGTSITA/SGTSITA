(function () {
    const timeoutMs = window.SGT_SESSION_TIMEOUT_MS || 30 * 60 * 1000;
    const logoutUrl = window.SGT_LOGOUT_URL;
    const loginUrl = window.SGT_LOGIN_URL;
    const csrfToken = document.querySelector(
        'meta[name="csrf-token"]',
    )?.content;

    if (!logoutUrl || !loginUrl) {
        console.warn("No se configuró SGT_LOGOUT_URL o SGT_LOGIN_URL.");
        return;
    }

    let timer = null;

    function programarCierreSesion() {
        clearTimeout(timer);

        timer = setTimeout(() => {
            cerrarSesionPorTiempo();
        }, timeoutMs);
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

    /*
     * Reinicia el temporizador cuando hay requests internos.
     * Esto hace que si el mapa/rastreo consulta cada 30 segundos,
     * por ahora mantenga viva la sesión.
     */
    if (window.fetch) {
        const originalFetch = window.fetch;

        window.fetch = async function (...args) {
            const response = await originalFetch.apply(this, args);

            if (esRequestMismoOrigen(args[0])) {
                manejarRespuestaExpirada(response);

                if (response.ok && !esRutaPasiva(args[0])) {
                    programarCierreSesion();
                }
            }

            return response;
        };
    }
    function esRutaPasiva(url) {
        try {
            const requestUrl = new URL(url, window.location.origin);
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
                    window.location.href = loginUrl;
                    return;
                }

                const esExitosa = this.status >= 200 && this.status < 300;

                const rutaPasiva =
                    typeof esRutaPasiva === "function"
                        ? esRutaPasiva(requestUrl)
                        : false;

                if (esExitosa && !rutaPasiva) {
                    if (typeof programarCierreSesion === "function") {
                        programarCierreSesion();
                    }
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

    programarCierreSesion();
})();
