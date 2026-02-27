var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let fileSettings = null;

let frm = document.querySelector('#cotizacionCreate');

function adjuntarDocumentos(filesContenedor) {
    // document.getElementById('content-file-input').innerHTML = '<input type="file" name="files" id="fileuploader">';
    numContenedor = localStorage.getItem('numContenedor');
    idCotizacion = localStorage.getItem('cotizacionId');
    let labelDocsViaje = document.getElementById('labelDocsViaje');
    labelDocsViaje.textContent = `Documentos de viaje ${numContenedor}`;

    const input = $('#' + fileSettings.opcion);

    const fileUploaderInstance = $.fileuploader.getInstance(input);
    console.log('fileUploaderInstance:', fileUploaderInstance);
    if (fileUploaderInstance) {
        fileUploaderInstance.setOption('upload', {
            files: filesContenedor != null ? [filesContenedor] : null,
            url: '/contenedores/files/upload',
            data: (item) => ({
                urlRepo: fileSettings.opcion,
                numContenedor: numContenedor,
                idCotizacion: idCotizacion,
                _token: _token,
            }),
            type: 'POST',
            enctype: 'multipart/form-data',
            start: true,
            synchron: true,

            onBeforeSend: (xhr, settings) => {
                const file = settings.files ? settings.files[0] : null;
                if (!file || !file.name) {
                    alert('Archivo inválido o vacío.');
                    return false;
                }
            },

            onSuccess: function (result, item) {
                let data = {};

                if (result && typeof result === 'object') data = result;
                else data.hasWarnings = true;

                if (data.isSuccess && data.files && data.files[0]) {
                    const fileData = data.files[0];
                    item.name = fileData.name;
                    item.html
                        .find('.column-title > div:first-child')
                        .text(fileData.old_name)
                        .attr('title', fileData.old_name);
                }

                if (data.hasWarnings) {
                    if (data.warnings) {
                        for (const warning in data.warnings) {
                            alert(data.warnings[warning]);
                        }
                    }
                    item.html.removeClass('upload-successful').addClass('upload-failed');
                    return this.onError ? this.onError(item) : null;
                }

                item.html.find('.fileuploader-action-remove').addClass('fileuploader-action-success');
                setTimeout(() => item.html.find('.progress-bar2').fadeOut(400), 400);

                if (apiGrid) {
                    const dataGrid = apiGrid.getGridOption('rowData') || [];
                    const rowIndex = dataGrid.findIndex((d) => d.NumContenedor === numContenedor);
                    const colId = fileSettings.agGrid;
                    const rowNode = apiGrid.getDisplayedRowAtIndex(rowIndex);
                    if (rowNode) rowNode.setDataValue(colId, true);
                }
            },

            onError: function (item) {
                const progressBar = item.html.find('.progress-bar2');
                if (progressBar.length) {
                    progressBar.find('span').html('0%');
                    progressBar.find('.fileuploader-progressbar .bar').width('0%');
                    item.html.find('.progress-bar2').fadeOut(400);
                }
                if (item.upload.status !== 'cancelled' && !item.html.find('.fileuploader-action-retry').length) {
                    item.html
                        .find('.column-actions')
                        .prepend(
                            '<button type="button" class="fileuploader-action fileuploader-action-retry" title="Reintentar"><i class="fileuploader-icon-retry"></i></button>',
                        );
                }
            },

            onProgress: function (data, item) {
                const progressBar = item.html.find('.progress-bar2');
                if (progressBar.length > 0) {
                    progressBar.show();
                    progressBar.find('span').html(`${data.percentage}%`);
                    progressBar.find('.fileuploader-progressbar .bar').width(`${data.percentage}%`);
                }
            },

            onComplete: () => {
                getFilesContenedor();

                setTimeout(() => {
                    adjuntarDocumentos();
                }, 2500);
            },
        });
    } else {
        input.fileuploader({
            captions: 'es',
            enableApi: true,
            limit: 1,
            start: true,
            files: filesContenedor != null ? [filesContenedor] : null,
            changeInput:
                '<div class="fileuploader-input">' +
                '<div class="fileuploader-input-inner">' +
                '<div class="fileuploader-icon-main"></div>' +
                '<h3 class="fileuploader-input-caption"><span>${captions.feedback}</span></h3>' +
                '<p>${captions.or}</p>' +
                '<button type="button" class="fileuploader-input-button"><span>${captions.button}</span></button>' +
                '</div>' +
                '</div>',
            theme: 'dragdrop',
            upload: {
                url: '/contenedores/files/upload',
                data: {
                    urlRepo: fileSettings.opcion,
                    numContenedor: numContenedor,
                    idCotizacion: idCotizacion,
                    _token: _token,
                },
                type: 'POST',
                enctype: 'multipart/form-data',
                start: true,
                synchron: true,
                onBeforeSend: (xhr, settings) => {},
                onSuccess: function (result, item) {
                    var data = {};

                    if (result && result.files) data = result;
                    else data.hasWarnings = true;

                    if (data.isSuccess && data.files[0]) {
                        item.name = data.files[0].name;
                        item.html
                            .find('.column-title > div:first-child')
                            .text(data.files[0].old_name)
                            .attr('title', data.files[0].old_name);
                    }

                    if (data.hasWarnings) {
                        for (var warning in result.warnings) {
                            Swal.fire(result.warnings[warning], '', 'warning');
                        }

                        item.html.removeClass('upload-successful').addClass('upload-failed');

                        return this.onError ? this.onError(item) : null;
                    }

                    item.html.find('.fileuploader-action-remove').addClass('fileuploader-action-success');
                    setTimeout(function () {
                        item.html.find('.progress-bar2').fadeOut(400);
                    }, 400);

                    //  const gridApi = gridOptions.api;

                    /*toastr.options = {
                    "closeButton": true,
                    "debug": false,
                    "newestOnTop": false,
                    "progressBar": true,
                    "positionClass": "toastr-bottom-center",
                    "preventDuplicates": false,
                    "onclick": null,
                    "showDuration": "1500",
                    "hideDuration": "1000",
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showEasing": "swing",
                    "hideEasing": "linear",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                  };

                  toastr.success( `Se cargó el archivo correctamente en el contenedor ${fileSettings.titulo}`,`${fileSettings.titulo}: Carga Exitosa`);*/
                },
                onError: function (item) {
                    var progressBar = item.html.find('.progress-bar2');

                    if (progressBar.length) {
                        progressBar.find('span').html(0 + '%');
                        progressBar.find('.fileuploader-progressbar .bar').width(0 + '%');
                        item.html.find('.progress-bar2').fadeOut(400);
                    }

                    item.upload.status != 'cancelled' && item.html.find('.fileuploader-action-retry').length == 0
                        ? item.html
                              .find('.column-actions')
                              .prepend(
                                  '<button type="button" class="fileuploader-action fileuploader-action-retry" title="Retry"><i class="fileuploader-icon-retry"></i></button>',
                              )
                        : null;
                },
                onProgress: function (data, item) {
                    var progressBar = item.html.find('.progress-bar2');

                    if (progressBar.length > 0) {
                        progressBar.show();
                        progressBar.find('span').html(data.percentage + '%');
                        progressBar.find('.fileuploader-progressbar .bar').width(data.percentage + '%');
                    }
                },
                onComplete: () => {},
            },
            beforeSelect: function (listEl, parentEl, newInputEl, inputEl) {
                // Guardar la info del contenedor
            },
            onRemove: function (item) {
                $.post('remove', {
                    _token: _token,
                    numContenedor: numContenedor,
                    urlRepo: fileSettings.opcion,
                    file: item.name,
                });
            },
            captions: $.extend(true, {}, $.fn.fileuploader.languages['es'], {
                feedback: `Arrastra tu archivo "${fileSettings.titulo}" y suéltalo aquí`,
                feedback2: `Arrastra tu archivo "${fileSettings.titulo}" y suéltalo aquí`,
                drop: `Arrastra tu archivo "${fileSettings.titulo}" y suéltalo aquí`,
                or: 'o',
                button: 'Examinar archivos',
            }),
        });
    }

    var fileInputElement = document.getElementById(fileSettings.opcion);
    // Obtener la instancia de Fileuploader asociada a este campo de carga
    //var api = $.fileuploader.getInstance(fileInputElement);
    //ejemplo cambio de configuracion en tiempo real

    // api.uploadStart(); // Iniciar la carga manualmente
}

/* async function consultarArchivos(numContenedor) {
    try {
      const response = await fetch(`/viajes/file-manager/get-file-list/${numContenedor}`, {
        method: 'get',

      });

      const fileList = await response.json();
      let containerFiles = fileList.data
      if(containerFiles.length == 0) return null;
      let filter = containerFiles.find((f)=> fileSettings.agGrid == f.fileCode)
      fileProperties = {
        name:filter.fileName,
        size:filter.fileSizeBytes ,
        type:filter.mimeType,
        file:`cotizaciones/cotizacion${filter.folder}/${filter.filePath}`,
        data:{thumbnail: `cotizaciones/cotizacion${filter.folder}/${filter.filePath}`, // (optional)
        readerForce: true}
    }

      return fileProperties;
    } catch (error) {
      console.error('Error:', error);
    }
  } */

async function consultarArchivos(numContenedor) {
    try {
        const response = await fetch(`/viajes/file-manager/get-file-list/${numContenedor}`, {
            method: 'GET',
        });

        const fileList = await response.json();
        const containerFiles = fileList.data || [];

        if (containerFiles.length === 0) return null;

        const filter = containerFiles.find((f) => f.fileCode === fileSettings.agGrid);

        if (!filter) {
            console.warn(
                `No se encontró archivo con fileCode: ${fileSettings.agGrid} para el contenedor ${numContenedor}`,
            );
            return null;
        }

        const fileProperties = {
            name: filter.fileName || 'SinNombre',
            size: filter.fileSizeBytes || 0,
            type: filter.mimeType || 'application/octet-stream',
            file: `cotizaciones/cotizacion${filter.folder}/${filter.filePath}`,
            data: {
                thumbnail: `cotizaciones/cotizacion${filter.folder}/${filter.filePath}`,
                readerForce: true,
            },
        };

        return fileProperties;
    } catch (error) {
        console.error('Error en consultarArchivos:', error);
        return null;
    }
}

function fileCheckTemplate(fileName, fileUrl) {
    return `<div class="d-flex justify-content-between m-5">
    <div class="flex-grow-1">
      <span class="fs-6 fw-semibold text-gray-800 d-block">${fileName}</span>
    </div>
    <label class="form-check form-switch form-check-solid">
      <input class="form-check-input" type="checkbox" name="waFiles" data-wafile="${fileName}" value="${fileUrl}" checked="checked" />
      <span class="form-check-label"> Adjuntar </span>
    </label>
  </div>`;
}

async function initFileUploader() {
    const elementos = [
        { opcion: 'BoletaLib', titulo: 'Boleta de Liberación', agGrid: 'Boleta-de-liberacion', mandatory: true },
        { opcion: 'Doda', titulo: 'DODA', agGrid: 'Doda', mandatory: true },
        { opcion: 'BoletaPatio', titulo: 'Boleta de Patio', agGrid: 'Boleta-de-patio', mandatory: true },
    ];

    let numContenedor = localStorage.getItem('numContenedor');

    for (const el of elementos) {
        fileSettings = el;

        let fileData = await consultarArchivos(numContenedor);

        adjuntarDocumentos(fileData);
    }
}
