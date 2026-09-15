document.addEventListener("DOMContentLoaded", function () {
    cargarNotificacionesNavbar();

    setInterval(() => {
        cargarNotificacionesNavbar();
    }, 30000);

    const btnMarcarTodas = document.getElementById(
        "btnMarcarTodasNotificaciones",
    );

    if (btnMarcarTodas) {
        btnMarcarTodas.addEventListener("click", function (event) {
            event.preventDefault();
            event.stopPropagation();

            marcarTodasNotificacionesLeidas();
        });
    }
});

async function cargarNotificacionesNavbar() {
    const lista = document.getElementById("listaNotificacionesNavbar");
    const badge = document.getElementById("badgeNotificaciones");

    if (!lista || !badge) {
        return;
    }

    try {
        const response = await fetch("/notificaciones/usuario/listar", {
            headers: {
                Accept: "application/json",
            },
        });

        const json = await response.json();

        if (!json.success) {
            return;
        }

        pintarBadgeNotificaciones(json.no_leidas || 0);
        pintarListaNotificaciones(json.data || []);
    } catch (error) {
        console.error("Error cargando notificaciones:", error);

        lista.innerHTML = `
            <div class="p-3 text-center text-danger">
                No se pudieron cargar las notificaciones.
            </div>
        `;
    }
}

function pintarBadgeNotificaciones(total) {
    const badge = document.getElementById("badgeNotificaciones");

    if (!badge) {
        return;
    }

    if (total > 0) {
        badge.textContent = total > 99 ? "99+" : total;
        badge.classList.remove("d-none");
    } else {
        badge.textContent = "0";
        badge.classList.add("d-none");
    }
}

function pintarListaNotificaciones(notificaciones) {
    const lista = document.getElementById("listaNotificacionesNavbar");

    if (!lista) {
        return;
    }

    if (notificaciones.length === 0) {
        lista.innerHTML = `
            <div class="p-3 text-center text-muted">
                <i class="fas fa-bell-slash d-block mb-2"></i>
                Sin notificaciones
            </div>
        `;
        return;
    }

    lista.innerHTML = notificaciones
        .map((n) => {
            const claseFondo = n.leida ? "bg-white" : "bg-light";
            const punto = n.leida
                ? ""
                : `<span class="badge bg-danger rounded-pill me-1" style="width: 8px; height: 8px;">&nbsp;</span>`;

            const url = n.url || "#";

            return `
            <a
                href="${url}"
                class="dropdown-item border-bottom py-3 ${claseFondo}"
                onclick="clickNotificacionNavbar(event, ${n.id}, '${escapeHtml(url)}')"
                style="white-space: normal;"
            >
                <div class="d-flex align-items-start">
                    <div class="me-2 pt-1">
                        ${punto}
                        <i class="${iconoNotificacion(n.tipo_clave)} text-primary"></i>
                    </div>

                    <div class="w-100">
                        <div class="fw-bold text-dark small">
                            ${escapeHtml(n.titulo || "Notificación")}
                        </div>

                        <div class="text-muted small">
                            ${escapeHtml(n.mensaje || "")}
                        </div>

                        <div class="text-muted mt-1" style="font-size: 11px;">
                            ${escapeHtml(n.created_at_humano || "")}
                        </div>
                    </div>
                </div>
            </a>
        `;
        })
        .join("");
}

async function clickNotificacionNavbar(event, id, url) {
    event.preventDefault();

    await marcarNotificacionLeida(id);

    if (url && url !== "#") {
        window.open(url, "_blank");
    } else {
        cargarNotificacionesNavbar();
    }
}

async function marcarNotificacionLeida(id) {
    try {
        await fetch(`/notificaciones/usuario/${id}/leer`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": getCsrfTokenNavbar(),
                Accept: "application/json",
            },
        });
    } catch (error) {
        console.error("Error marcando notificación como leída:", error);
    }
}

async function marcarTodasNotificacionesLeidas() {
    try {
        const response = await fetch(
            "/notificaciones/usuario/marcar-todas-leidas",
            {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": getCsrfTokenNavbar(),
                    Accept: "application/json",
                },
            },
        );

        const json = await response.json();

        if (json.success) {
            cargarNotificacionesNavbar();
        }
    } catch (error) {
        console.error("Error marcando todas como leídas:", error);
    }
}

function getCsrfTokenNavbar() {
    const meta = document.querySelector('meta[name="csrf-token"]');

    return meta ? meta.getAttribute("content") : "";
}

function iconoNotificacion(tipoClave) {
    if (!tipoClave) {
        return "fas fa-bell";
    }

    if (tipoClave.includes("documento")) {
        return "fas fa-file-alt";
    }

    if (tipoClave.includes("gps")) {
        return "fas fa-satellite-dish";
    }

    if (tipoClave.includes("cotizacion")) {
        return "fas fa-file-invoice";
    }

    if (tipoClave.includes("pago")) {
        return "fas fa-dollar-sign";
    }

    return "fas fa-bell";
}

function escapeHtml(text) {
    if (text === null || text === undefined) {
        return "";
    }

    return String(text)
        .replaceAll("&", "&amp;")
        .replaceAll("<", "&lt;")
        .replaceAll(">", "&gt;")
        .replaceAll('"', "&quot;")
        .replaceAll("'", "&#039;");
}
