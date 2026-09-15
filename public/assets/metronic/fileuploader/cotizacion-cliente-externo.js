let urlRepo = "";
var _Folio = null;

var _token = document
    .querySelector('meta[name="csrf-token"]')
    .getAttribute("content");

var [BoletaLib, Doda, CartaPorte, PreAlta] = [
    {
        opcion: "BoletaLib",
        titulo: "Boleta de Liberación",
        agGrid: "BoletaLiberacion",
    },
    { opcion: "Doda", titulo: "DODA", agGrid: "DODA" },
    { opcion: "CartaPorte", titulo: "Carta Porte", agGrid: "CartaPorte" },
    { opcion: "PreAlta", titulo: "Pre Alta", agGrid: "PreAlta" },
];

let fileSettings = BoletaLib;

let fileCartaPorte = document.querySelector("#fileCartaPorte");
let btnFileCartaPorte = document.querySelector("#btnFileCartaPorte");
let btnFileDODA = document.querySelector("#btnFileDODA");
let btnFileBoletaLiberacion = document.querySelector(
    "#btnFileBoletaLiberacion",
);
let btnPreAlta = document.querySelector("#btnFilePrealta");

if (btnFileCartaPorte) {
    btnFileCartaPorte.addEventListener("click", () => {
        fileSettings = CartaPorte;
    });
}
if (btnFileDODA) {
    btnFileDODA.addEventListener("click", () => {
        fileSettings = Doda;
    });
}

if (btnFileBoletaLiberacion) {
    btnFileBoletaLiberacion.addEventListener("click", () => {
        fileSettings = BoletaLib;
    });
}

if (btnPreAlta) {
    btnPreAlta.addEventListener("click", () => {
        fileSettings = PreAlta;
    });
}

function getSubClientes() {
    var clienteId = $(this).val();
    if (clienteId) {
        $.ajax({
            type: "GET",
            url: "/subclientes/" + clienteId,
            success: function (data) {
                $.each(data, function (key, subcliente) {
                    $("#id_subcliente").append(
                        '<option value="' +
                            subcliente.id +
                            '">' +
                            subcliente.nombre +
                            "</option>",
                    );
                });
            },
        });
    }
}

function getUploadConfig() {
    let currentContenedor = localStorage.getItem("numContenedor");
    return {
        url: "/contenedores/files/upload",
        data: {
            urlRepo: fileSettings.opcion,
            numContenedor: currentContenedor,
            tipo_documento: document.querySelector(".CheckTypeFile:checked")?.value,
            folio: document.getElementById("inputFolio")?.value,
            _token: _token,
        },
        type: "POST",
        enctype: "multipart/form-data",
        start: true,
        synchron: true,
        beforeSend: function (item, listEl, parentEl, newInputEl, inputEl) {
            let folioInput = document.getElementById("inputFolio");
            let container = document.getElementById("containerFolio");

            if (container && !container.classList.contains("d-none")) {
                let folio = folioInput?.value?.trim();

                if (!folio) {
                    Swal.fire(
                        "Debe ingresar el folio antes de subir el archivo",
                    );
                    setTimeout(() => {
                        adjuntarDocumentos();
                    }, 400);
                    return false;
                }
            }

            // Sincronizar datos dinámicos justo antes del envío
            if (item && item.upload && item.upload.data) {
                item.upload.data.urlRepo = fileSettings.opcion;
                item.upload.data.numContenedor = localStorage.getItem("numContenedor");
                item.upload.data.tipo_documento = document.querySelector(".CheckTypeFile:checked")?.value;
                item.upload.data.folio = document.getElementById("inputFolio")?.value;
                item.upload.data._token = _token;
            }

            return true;
        },
        onSuccess: function (result, item) {
            var data = {};

            // get data
            if (result && result.files) data = result;
            else data.hasWarnings = true;

            // if success
            if (data.isSuccess && data.files[0]) {
                item.name = data.files[0].name;
                item.html
                    .find(".column-title > div:first-child")
                    .text(data.files[0].old_name)
                    .attr("title", data.files[0].old_name);
            }

            // if warnings
            if (data.hasWarnings) {
                for (var warning in data.warnings) {
                    alert(data.warnings[warning]);
                }

                item.html
                    .removeClass("upload-successful")
                    .addClass("upload-failed");
                return this.onError ? this.onError(item) : null;
            }

            item.html
                .find(".fileuploader-action-remove")
                .addClass("fileuploader-action-success");
            setTimeout(function () {
                item.html.find(".progress-bar2").fadeOut(400);
            }, 400);

            let gridApi = null;
            if (typeof apiGrid !== "undefined" && apiGrid) {
                gridApi = apiGrid;
            } else if (typeof gridOptions !== "undefined" && gridOptions?.api) {
                gridApi = gridOptions.api;
            }
            if (gridApi) {
                let dataGrid = gridApi.getGridOption("rowData");
                let cNum = localStorage.getItem("numContenedor");
                var rowIndex = dataGrid.findIndex(
                    (d) => d.NumContenedor == cNum,
                );

                const colId = fileSettings.agGrid;

                // Obtener el nodo de la fila
                const rowNode = gridApi.getDisplayedRowAtIndex(rowIndex);

                // Establecer un nuevo valor en la celda
                if (rowNode) {
                    rowNode.setDataValue(colId, true);
                }
            }

            toastr.options = {
                closeButton: true,
                debug: false,
                newestOnTop: false,
                progressBar: true,
                positionClass: "toastr-bottom-center",
                preventDuplicates: false,
                onclick: null,
                showDuration: "1500",
                hideDuration: "1000",
                timeOut: "5000",
                extendedTimeOut: "1000",
                showEasing: "swing",
                hideEasing: "linear",
                showMethod: "fadeIn",
                hideMethod: "fadeOut",
            };

            toastr.success(
                `Se cargó el archivo correctamente en el contenedor ${fileSettings.titulo}`,
                `${fileSettings.titulo}: Carga Exitosa`,
            );

            let folioInput = document.getElementById("inputFolio");
            if (folioInput) {
                folioInput.value = "";
            }

            // Actualizar documentos en memoria si existe docsData
            let cNum = localStorage.getItem("numContenedor");
            if (typeof docsData !== "undefined" && cNum && typeof fetch === "function") {
                fetch(`/viajes/file-manager/get-file-list/${cNum}`)
                    .then(response => response.json())
                    .then(json => {
                        if (json && json.data) {
                            docsData = json.data;
                            let seleccionado = document.querySelector(".CheckTypeFile:checked");
                            if (seleccionado && typeof actualizarFolio === "function") {
                                actualizarFolio(seleccionado);
                            }
                        }
                    })
                    .catch(err => console.log(err));
            }
        },
        onError: function (item) {
            var progressBar = item.html.find(".progress-bar2");

            if (progressBar.length) {
                progressBar.find("span").html(0 + "%");
                progressBar
                    .find(".fileuploader-progressbar .bar")
                    .width(0 + "%");
                item.html.find(".progress-bar2").fadeOut(400);
            }

            item.upload.status != "cancelled" &&
            item.html.find(".fileuploader-action-retry").length == 0
                ? item.html
                      .find(".column-actions")
                      .prepend(
                          '<button type="button" class="fileuploader-action fileuploader-action-retry" title="Retry"><i class="fileuploader-icon-retry"></i></button>',
                      )
                : null;
        },
        onProgress: function (data, item) {
            var progressBar = item.html.find(".progress-bar2");

            if (progressBar.length > 0) {
                progressBar.show();
                progressBar.find("span").html(data.percentage + "%");
                progressBar
                    .find(".fileuploader-progressbar .bar")
                    .width(data.percentage + "%");
            }
        },
        onComplete: (listEl) => {
            let hasErrors = false;
            if (listEl && listEl.find) {
                hasErrors = listEl.find(".upload-failed, .has-warnings").length > 0;
            }

            // Reiniciar automáticamente el fileuploader si subió sin error para preparar el siguiente documento
            if (!hasErrors) {
                setTimeout(() => {
                    adjuntarDocumentos();
                    if (
                        typeof dt !== "undefined" &&
                        dt !== null &&
                        $.fn.DataTable.isDataTable("#kt_datatable_example_1")
                    ) {
                        dt.ajax.reload(null, false);
                    }
                }, 1000);
            }
        },
    };
}

function resetUploadConfig() {
    var $input = $("#content-file-input").find('input[type="file"]');
    if (!$input.length) return;
    var api = $.fileuploader.getInstance($input);
    if (!api) return;

    api.setOption("upload", getUploadConfig());
}

function adjuntarDocumentos() {
    var container = document.getElementById("content-file-input");
    if (!container) return;

    var oldInput = container.querySelector('input[type="file"]');
    if (oldInput) {
        try {
            var oldApi = $.fileuploader.getInstance(oldInput);
            if (oldApi && typeof oldApi.destroy === "function") {
                oldApi.destroy();
            }
        } catch (e) {
            console.warn("Error destruyendo fileuploader previo:", e);
        }
    }

    container.innerHTML = '<input type="file" name="files" id="fileuploader">';

    $("#content-file-input").find('input[type="file"]').fileuploader({
        captions: "es",
        enableApi: true,
        start: true,
        changeInput:
            '<div class="fileuploader-input">' +
            '<div class="fileuploader-input-inner">' +
            '<div class="fileuploader-icon-main"></div>' +
            '<h3 class="fileuploader-input-caption"><span>${captions.feedback}</span></h3>' +
            "<p>${captions.or}</p>" +
            '<button type="button" class="fileuploader-input-button"><span>${captions.button}</span></button>' +
            "</div>" +
            "</div>",
        theme: "dragdrop",
        upload: getUploadConfig(),
        beforeSelect: function (listEl, parentEl, newInputEl, inputEl) {
            resetUploadConfig();
        },
        onRemove: function (item) {
            $.post("remove", {
                _token: _token,
                _Folio: typeof _Folio !== "undefined" ? _Folio : null,
                file: item.name,
            });
        },
        captions: $.extend(true, {}, $.fn.fileuploader.languages["es"], {
            feedback: "Arrastre y suelte sus archivos aquí",
            feedback2: "Arrastre y suelte sus archivos aquí",
            drop: "Arrastre y suelte sus archivos aquí",
            or: "o",
            button: "Examinar archivos",
        }),
    });
}
