var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let fileSettings = null;

let frm = document.querySelector('#cotizacionCreate')

function adjuntarDocumentos() {
   // document.getElementById('content-file-input').innerHTML = '<input type="file" name="files" id="fileuploader">';
   numContenedor = localStorage.getItem('numContenedor'); 
    $('#'+fileSettings.opcion).fileuploader({
        captions: 'es',
        enableApi: true,
        start: true,
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
                _Folio: _Folio,
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

   
   // api.uploadStart(); // Iniciar la carga manualmente

}

function initFileUploader(){
    var elementos = [
        {"opcion":"BoletaLib","titulo":"Boleta de Liberación","agGrid": "BoletaLiberacion", "mandatory": true},
        {"opcion":"Doda","titulo":"DODA","agGrid": "DODA", "mandatory": true},
        {"opcion":"PreAlta","titulo":"Pre Alta","agGrid": "PreAlta", "mandatory": false},
        {"opcion":"CartaPortePDF","titulo":"Carta Porte PDF","agGrid": "CartaPorte", "mandatory": false},
        {"opcion":"CartaPorteXML","titulo":"Carta Porte XML","agGrid": "CartaPorteXML", "mandatory": false},
        {"opcion":"CCP","titulo":"CCP - Carta Porte","agGrid": "CCP", "mandatory": true},
        {"opcion":"EIR","titulo":"EIR - Comprobante de vacío","agGrid": "EIR", "mandatory": false},
        
    ];

    elementos.forEach((el) =>{
        if(el.mandatory){
            fileSettings = el;
            adjuntarDocumentos();
        }
    })
    
    
}