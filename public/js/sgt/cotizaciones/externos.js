let selectProveedor = document.querySelector("#id_proveedor");
let selectTransport = document.querySelector("#id_transportista");

if (selectProveedor) {
    selectProveedor.addEventListener("change", () => {
        getTranspotistas();
    });
}

function getTranspotistas() {
    let _token = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    let proveedor = selectProveedor.value;
    $.ajax({
        type: "post",
        url: "/mec/transportistas/list",
        data: { proveedor, _token },
        beforeSend: () => {},
        success: (response) => {
            let opciones = response;
            selectTransport.innerHTML = "";

            opciones.forEach((opcion) => {
                selectTransport.add(new Option(opcion.nombre, opcion.id));
            });
        },
        error: () => {},
    });
}

let selectProveedorLocal = document.querySelector("#id_proveedorlocal");
let selectTransportLocal = document.querySelector("#id_transportistalocal");

if (selectProveedorLocal) {
    selectProveedorLocal.addEventListener("change", () => {
        getTranspotistasLocal();
    });
}

function getTranspotistasLocal() {
    let _token = document
        .querySelector('meta[name="csrf-token"]')
        .getAttribute("content");

    let proveedor = selectProveedorLocal.value;
    $.ajax({
        type: "post",
        url: "/mec/transportistas/list-local",
        data: { proveedor, _token },
        beforeSend: () => {},
        success: (response) => {
            let opciones = response;
            selectTransportLocal.innerHTML = "";

            opciones.forEach((opcion) => {
                selectTransportLocal.add(new Option(opcion.nombre, opcion.id));
            });
        },
        error: () => {},
    });
}

document.addEventListener("DOMContentLoaded", () => {
    const campos = [
        "dias_estadia",
        "tarifa_estadia",
        "dias_pernocta",
        "tarifa_pernocta",
        "Costomaniobra",
    ];

    const recalcularTotales = () => {
        const diasE =
            parseFloat(document.getElementById("dias_estadia").value) || 0;
        const tarifaE =
            parseFloat(document.getElementById("tarifa_estadia").value) || 0;
        const diasP =
            parseFloat(document.getElementById("dias_pernocta").value) || 0;
        const tarifaP =
            parseFloat(document.getElementById("tarifa_pernocta").value) || 0;
        const costoManiobra =
            parseFloat(document.getElementById("Costomaniobra").value) || 0;

        const totalE = diasE * tarifaE;
        const totalP = diasP * tarifaP;
        const totalG = totalE + totalP;
        const totalConManiobra = totalG + costoManiobra;

        document.getElementById("total_estadia").value = totalE.toLocaleString(
            "en-US",
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            },
        );
        document.getElementById("total_pernocta").value = totalP.toLocaleString(
            "en-US",
            {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            },
        );
        document.getElementById("total_general").value =
            totalConManiobra.toLocaleString("en-US", {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2,
            });
    };

    // Escucha cambios solo en los inputs que importan
    campos.forEach((id) => {
        const input = document.getElementById(id);
        //if (input) input.dispatchEvent(new Event('input', { bubbles: true }));
        if (input) input.addEventListener("input", recalcularTotales);
    });
    recalcularTotales();

    validarDestinoDireccion();
});

let alertaDestinoActiva = false;

const inputDestino = document.getElementById("destino");
const inputDireccion = document.getElementById("direccion_entrega");
const btnGuardar = document.getElementById("btnGuardarViaje");
const errorDestino = document.getElementById("errorDestino");

function normalizarTexto(texto) {
    return (texto || "")
        .toLowerCase()
        .normalize("NFD")
        .replace(/[\u0300-\u036f]/g, "")
        .replace(/\s+/g, " ")
        .trim();
}
function obtenerPalabras(texto) {
    return normalizarTexto(texto).split(/\s+/).filter(Boolean);
}
function validarDestinoDireccion() {
    const stopWords = ["de", "la", "el", "los", "las", "mx", "mexico"];
    const destino = normalizarTexto(inputDestino.value);

    const direccion = normalizarTexto(inputDireccion.value);

    if (!destino) {
        btnGuardar.disabled = true;
        return false;
    }

    if (!direccion) {
        btnGuardar.disabled = true;
        return false;
    }

    const palabrasDestino = destino
        .split(" ")
        .filter((p) => p.trim() !== "" && !stopWords.includes(p));

    const coincide = palabrasDestino.every((palabra) =>
        direccion.includes(palabra),
    );

    if (!coincide) {
        btnGuardar.disabled = true;

        if (!alertaDestinoActiva) {
            alertaDestinoActiva = true;

            Swal.fire({
                icon: "warning",
                title: "Destino inválido",
                html: `
                    <div class="text-start">
                        <p>
                            El destino capturado no coincide con la dirección seleccionada en el mapa.
                        </p>

                        <hr>

                        <b>Destino:</b><br>
                        ${destino}

                        <br><br>

                        <b>Dirección:</b><br>
                        ${direccion}
                    </div>
                `,
                confirmButtonText: "Entendido",
            }).then(() => {
                alertaDestinoActiva = false;
            });
        }

        return false;
    }

    btnGuardar.disabled = false;

    return true;
}
let timeoutDestino;

function validarConDelay() {
    clearTimeout(timeoutDestino);

    timeoutDestino = setTimeout(() => {
        validarDestinoDireccion();
    }, 800);
}

document
    .getElementById("cotizacionCreate")
    .addEventListener("keydown", function (e) {
        if (e.key === "Enter") {
            e.preventDefault();
        }
    });
inputDestino.addEventListener("input", validarConDelay);

inputDireccion.addEventListener("input", validarConDelay);
