let documentosPendientes = false;
let fileUploaderInitPromise = null;
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
        enableApi: true,
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

function initFileUploader() {
    if (fileUploaderInitPromise) return fileUploaderInitPromise;

    fileUploaderInitPromise = (async () => {
        const numContenedor = localStorage.getItem("numContenedor");
        const archivos = numContenedor ? await consultarArchivos(numContenedor) : [];

        Object.keys(documentosConfig).forEach((key) => {
            const config = documentosConfig[key];
            const archivoExistente = archivos.find(
                (file) => file.fileCode === config.fileCode,
            );
            initSingleFileUploader(
                config,
                archivoExistente ? generarPreloadArchivo(archivoExistente) : null,
            );
        });
    })().finally(() => {
        fileUploaderInitPromise = null;
    });

    return fileUploaderInitPromise;
}

async function reinicializarFileUploader() {
    // Esperar cualquier consulta inicial para no dejar varias inicializaciones
    // compitiendo por el mismo input.
    if (fileUploaderInitPromise) {
        await fileUploaderInitPromise;
    }

    const numContenedor = localStorage.getItem("numContenedor");
    const archivos = numContenedor ? await consultarArchivos(numContenedor) : [];

    Object.values(documentosConfig).forEach((config) => {
        const input = $(config.input);
        if (!input.length) return;

        const archivoExistente = archivos.find(
            (file) => file.fileCode === config.fileCode,
        );
        const preload = archivoExistente
            ? generarPreloadArchivo(archivoExistente)
            : null;
        const instance = $.fileuploader.getInstance(input[0]);

        if (instance) {
            // Mantener el wrapper ya renderizado; solo limpiar la selección
            // enviada y reemplazar la lista con los archivos persistidos.
            instance.reset();
            if (preload) instance.append([preload]);
            return;
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
    // En una solicitud nueva todavía no se pueden adjuntar documentos. Los
    // folios solo se validan al editar un viaje y cuando se adjunta su archivo.
    if (frm?.getAttribute("sgt-cotizacion-action") !== "editar") {
        return true;
    }

    for (const key in documentosConfig) {
        const config = documentosConfig[key];
        const archivo = document.querySelector(config.input)?.files?.length > 0;

        if (config.requiereFolio && archivo) {
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
    if (frm?.getAttribute("sgt-cotizacion-action") !== "editar") return;
    marcarDocumentosPendientes();
});
$("#BoletaLib, #Doda, #CCP").on("change", function () {
    if (frm?.getAttribute("sgt-cotizacion-action") !== "editar") return;
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
