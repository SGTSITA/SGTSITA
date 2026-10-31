let documentosPendientes = false;
var _token = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

let frm = document.querySelector("#cotizacionCreate");

const documentosConfig = {
    BoletaLib: {
        input: "#BoletaLib",
        titulo: "Boleta de Liberación",
        folioInput: "#numBoleta",
        requiereFolio: true,
        fileCode: "Boleta-de-liberacion",
    },

    Doda: {
        input: "#Doda",
        titulo: "DODA",
        folioInput: "#numDoda",
        requiereFolio: true,
        fileCode: "Doda",
    },

    CCP: {
        input: "#CCP",
        titulo: "Carta Porte",
        folioInput: null,
        requiereFolio: false,
        fileCode: "Formato-para-Carta-porte",
    },
};

/*
|--------------------------------------------------------------------------
| HELPERS
|--------------------------------------------------------------------------
*/

function getDocumentoConfig(key) {
    return documentosConfig[key] || null;
}

function getFolioDocumento(key) {
    const config = getDocumentoConfig(key);

    if (!config || !config.folioInput) {
        return "";
    }

    const input = document.querySelector(config.folioInput);

    if (!input) {
        return "";
    }

    return input.value.trim();
}

function requiereFolioDocumento(key) {
    const config = getDocumentoConfig(key);

    if (!config) {
        return false;
    }

    return config.requiereFolio;
}

/*
|--------------------------------------------------------------------------
| CONSULTAR ARCHIVOS EXISTENTES
|--------------------------------------------------------------------------
*/

async function consultarArchivos(numContenedor) {
    try {
        const response = await fetch(
            `/viajes/file-manager/get-file-list/${numContenedor}`,
            {
                method: "GET",
            },
        );

        const result = await response.json();

        return result.data || [];
    } catch (error) {
        console.error("Error consultando archivos:", error);

        return [];
    }
}

/*
|--------------------------------------------------------------------------
| PRELOAD
|--------------------------------------------------------------------------
*/

function generarPreloadArchivo(file) {
    return {
        name: file.fileName,
        size: file.fileSizeBytes || 0,
        type: file.mimeType || "application/octet-stream",

        file: `cotizaciones/cotizacion${file.folder}/${file.filePath}`,

        data: {
            readerForce: true,
            thumbnail: `cotizaciones/cotizacion${file.folder}/${file.filePath}`,
        },
    };
}

/*
|--------------------------------------------------------------------------
| INICIALIZAR UN FILEUPLOADER
|--------------------------------------------------------------------------
*/

function initSingleFileUploader(config, preloadFile = null) {
    const input = $(config.input);

    if (!input.length) {
        return;
    }

    const instance = $.fileuploader.getInstance(input[0]);

    if (instance) {
        return;
    }

    input.fileuploader({
        limit: 1,

        addMore: false,

        theme: "dragdrop",

        files: preloadFile ? [preloadFile] : null,

        changeInput:
            '<div class="fileuploader-input">' +
            '<div class="fileuploader-input-inner">' +
            '<div class="fileuploader-icon-main"></div>' +
            '<h3 class="fileuploader-input-caption">' +
            `<span>Arrastra tu archivo "${config.titulo}" aquí</span>` +
            "</h3>" +
            "<p>o</p>" +
            '<button type="button" class="fileuploader-input-button">' +
            "<span>Examinar archivos</span>" +
            "</button>" +
            "</div>" +
            "</div>",

        captions: $.extend(true, {}, $.fn.fileuploader.languages["es"], {
            feedback: `Arrastra tu archivo "${config.titulo}" aquí`,

            feedback2: `Arrastra tu archivo "${config.titulo}" aquí`,

            drop: `Arrastra tu archivo "${config.titulo}" aquí`,

            or: "o",

            button: "Examinar archivos",
        }),
    });
}

/*
|--------------------------------------------------------------------------
| INIT GENERAL
|--------------------------------------------------------------------------
*/

async function initFileUploader() {
    const numContenedor = localStorage.getItem("numContenedor");

    let archivos = [];

    // consultar existentes
    if (numContenedor) {
        archivos = await consultarArchivos(numContenedor);
    }

    Object.keys(documentosConfig).forEach((key) => {
        const config = documentosConfig[key];

        // buscar archivo existente
        const archivoExistente = archivos.find(
            (f) => f.fileCode === config.fileCode,
        );

        let preload = null;

        if (archivoExistente) {
            preload = generarPreloadArchivo(archivoExistente);
        }

        initSingleFileUploader(config, preload);
    });
}

/*
|--------------------------------------------------------------------------
| VALIDAR FORM
|--------------------------------------------------------------------------
*/

function validarDocumentos() {
    for (const key in documentosConfig) {
        const config = documentosConfig[key];

        if (config.requiereFolio) {
            const folio = getFolioDocumento(key);

            if (!folio) {
                Swal.fire({
                    icon: "warning",
                    title: "Folio requerido",
                    text: `Debe ingresar el folio para ${config.titulo}`,
                });

                return false;
            }
        }
    }

    return true;
}

/*
|--------------------------------------------------------------------------
| VALIDAR SUBMIT
|--------------------------------------------------------------------------
*/

if (frm) {
    frm.addEventListener("submit", function (e) {
        const valido = validarDocumentos();

        if (!valido) {
            e.preventDefault();

            return false;
        }
    });
}

function marcarDocumentosPendientes() {
    documentosPendientes = true;

    $("#docsPendingAlert").removeClass("d-none");

    $("#btnGuardarViaje").addClass("pulse pulse-warning");

    $("#btnGuardarViaje").prepend(`
        <span class="pulse-ring"></span>
    `);
}

$("#numBoleta, #numDoda").on("input", function () {
    marcarDocumentosPendientes();
});
$("#BoletaLib, #Doda, #CCP").on("change", function () {
    marcarDocumentosPendientes();
});

window.addEventListener("beforeunload", function (e) {
    if (documentosPendientes) {
        e.preventDefault();
        e.returnValue = "";
    }
});

function limpiarEstadoDocumentos() {
    documentosPendientes = false;

    $("#docsPendingAlert").addClass("d-none");

    $("#btnGuardarViaje").removeClass("pulse pulse-warning");

    $("#btnGuardarViaje .pulse-ring").remove();
}
