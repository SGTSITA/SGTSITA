var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let fileSettings = null;

let frm = document.querySelector('#cotizacionCreate')

function adjuntarDocumentos(filesContenedor) {
   // document.getElementById('content-file-input').innerHTML = '<input type="file" name="files" id="fileuploader">';
   numContenedor = localStorage.getItem('numContenedor'); 
   let labelDocsViaje = document.getElementById('labelDocsViaje')
   labelDocsViaje.textContent = `Documentos de viaje ${numContenedor}`

    const input = $('#' + fileSettings.opcion);

     const fileUploaderInstance = $.fileuploader.getInstance(input);
 
    if (fileUploaderInstance && fileUploaderInstance.destroy) {
        fileUploaderInstance.destroy();
        console.log(`Instancia de fileuploader en #${fileSettings.opcion} destruida correctamente.`);
    }




    input.fileuploader({
        captions: 'es',
        enableApi: true,
        limit:1,
        start: true,
        files: (filesContenedor != null ) ? [filesContenedor] : null,
        changeInput: '<div class="fileuploader-input">' +
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
                urlRepo:fileSettings.opcion,
                numContenedor: numContenedor,
                _token: _token
            },
            type: 'POST',
            enctype: 'multipart/form-data',
            start: true,
            synchron: true,
            onBeforeSend: (xhr, settings) => {
    
            },
            onSuccess: function(result, item) {
    
                var data = {};
    
                // get data
                if (result && result.files)
                    data = result;
                else
                    data.hasWarnings = true;
    
                // if success
                if (data.isSuccess && data.files[0]) {
                    item.name = data.files[0].name;
                    item.html.find('.column-title > div:first-child').text(data.files[0].old_name).attr('title', data.files[0].old_name);
                }
    
                // if warnings
                if (data.hasWarnings) {
                   
                    for (var warning in result.warnings) {
                       Swal.fire(result.warnings[warning],'','warning');
                    }
    
                    item.html.removeClass('upload-successful').addClass('upload-failed');
                    // go out from success function by calling onError function
                    // in this case we have a animation there
                    // you can also response in PHP with 404
                    return this.onError ? this.onError(item) : null;
                }
    
                item.html.find('.fileuploader-action-remove').addClass('fileuploader-action-success');
                setTimeout(function() {
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
            onError: function(item) {
                var progressBar = item.html.find('.progress-bar2');
    
                if (progressBar.length) {
                    progressBar.find('span').html(0 + "%");
                    progressBar.find('.fileuploader-progressbar .bar').width(0 + "%");
                    item.html.find('.progress-bar2').fadeOut(400);
                }
    
                item.upload.status != 'cancelled' && item.html.find('.fileuploader-action-retry').length == 0 ? item.html.find('.column-actions').prepend(
                    '<button type="button" class="fileuploader-action fileuploader-action-retry" title="Retry"><i class="fileuploader-icon-retry"></i></button>'
                ) : null;
            },
            onProgress: function(data, item) {
                var progressBar = item.html.find('.progress-bar2');
    
                if (progressBar.length > 0) {
                    progressBar.show();
                    progressBar.find('span').html(data.percentage + "%");
                    progressBar.find('.fileuploader-progressbar .bar').width(data.percentage + "%");
                }
            },
            onComplete: ()=>{
           
                
            },
        },
        beforeSelect: function(listEl, parentEl, newInputEl, inputEl) {
          // Guardar la info del contenedor
        },
        onRemove: function(item) {
            $.post('remove', {
                _token: _token,
                numContenedor: numContenedor,
                urlRepo:fileSettings.opcion,
                file: item.name
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



   var fileInputElement = document.getElementById(fileSettings.opcion);
    // Obtener la instancia de Fileuploader asociada a este campo de carga
   //var api = $.fileuploader.getInstance(fileInputElement);
   
   
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

    // Si no hay archivos, salimos
    if (containerFiles.length === 0) return null;

    // Buscar por el código del archivo
    const filter = containerFiles.find((f) => f.fileCode === fileSettings.agGrid);

    // Si no se encuentra coincidencia, no sigas
    if (!filter) {
      console.warn(`No se encontró archivo con fileCode: ${fileSettings.agGrid} para el contenedor ${numContenedor}`);
      return null;
    }

    // Crear objeto seguro
    const fileProperties = {
      name: filter.fileName || "SinNombre",
      size: filter.fileSizeBytes || 0,
      type: filter.mimeType || "application/octet-stream",
      file: `cotizaciones/cotizacion${filter.folder}/${filter.filePath}`,
      data: {
        thumbnail: `cotizaciones/cotizacion${filter.folder}/${filter.filePath}`,
        readerForce: true,
      },
    };

    return fileProperties;
  } catch (error) {
    console.error("Error en consultarArchivos:", error);
    return null;
  }
}

function fileCheckTemplate(fileName, fileUrl){
    return `<div class="d-flex justify-content-between m-5">
    <div class="flex-grow-1">
      <span class="fs-6 fw-semibold text-gray-800 d-block">${fileName}</span>
    </div>
    <label class="form-check form-switch form-check-solid">
      <input class="form-check-input" type="checkbox" name="waFiles" data-wafile="${fileName}" value="${fileUrl}" checked="checked" />
      <span class="form-check-label"> Adjuntar </span>
    </label>
  </div>`
}

async function initFileUploader(){
    var elementos = [
        {"opcion":"BoletaLib","titulo":"Boleta de Liberación","agGrid": "Boleta-de-liberacion", "mandatory": true},
        {"opcion":"Doda","titulo":"DODA","agGrid": "Doda", "mandatory": true},
        {"opcion":"PreAlta","titulo":"Pre Alta","agGrid": "PreAlta", "mandatory": false},
        {"opcion":"CartaPortePDF","titulo":"Carta Porte PDF","agGrid": "CartaPorte", "mandatory": false},
        {"opcion":"CartaPorteXML","titulo":"Carta Porte XML","agGrid": "CartaPorteXML", "mandatory": false},
        {"opcion":"CCP","titulo":"CCP - Carta Porte","agGrid": "Formato-para-Carta-porte", "mandatory": true},
        {"opcion":"EIR","titulo":"EIR - Comprobante de vacío","agGrid": "EIR", "mandatory": false},
        
    ];

    let waSendFiles = document.querySelector("#waSendFiles")
    let itemTemplate = null

   for(const el of elementos){
        if(el.mandatory){
            fileSettings = el;
            numContenedor = localStorage.getItem('numContenedor'); 
            filesContenedor = await consultarArchivos(numContenedor)

            if(filesContenedor != null){
                itemTemplate += fileCheckTemplate(filesContenedor.name,filesContenedor.file)
                waSendFiles.innerHTML = itemTemplate
            }
            
            adjuntarDocumentos(filesContenedor);
            //console.log(filesContenedor)
            
        }
   }
}
