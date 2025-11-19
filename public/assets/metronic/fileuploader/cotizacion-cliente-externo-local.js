let urlRepo = '';

var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

var [BoletaLib, Doda, BoletaPatio] = [
    {"opcion":"BoletaLib","titulo":"Boleta de Liberación","agGrid": "BoletaLiberacion"},
    {"opcion":"Doda","titulo":"DODA","agGrid": "DODA"},
    {"opcion":"BoletaPatio","titulo":"Boleta de Patio","agGrid": "Boleta-de-patio"},
    
];

  

let fileSettings = BoletaLib;

let btnBoletaPatio = document.querySelector('#btnBoletaPatio');
let btnFileDODA = document.querySelector('#btnFileDODA');
let btnFileBoletaLiberacion = document.querySelector('#btnFileBoletaLiberacion');


if (btnBoletaPatio) {
  btnBoletaPatio.addEventListener('click', () => {
    fileSettings = BoletaPatio;
  });
}
if (btnFileDODA) {
    btnFileDODA.addEventListener('click',()=>{
    fileSettings = Doda;
})
}

if (btnFileBoletaLiberacion) {
btnFileBoletaLiberacion.addEventListener('click',()=>{
    fileSettings = BoletaLib;
})
}





function getSubClientes() {
    var clienteId = $(this).val();
    if (clienteId) {
     
        $.ajax({
            type: 'GET',
            url: '/subclientes/' + clienteId,
            success: function(data) {
                $.each(data, function(key, subcliente) {
                    $('#id_subcliente').append('<option value="' + subcliente.id + '">' + subcliente.nombre + '</option>');
                });
            }
        });
    } 
}

var uploadConfig = null;



function resetUploadConfig(){
    var fileInputElement = document.getElementById('fileuploader');
    // Obtener la instancia de Fileuploader asociada a este campo de carga
    var api = $.fileuploader.getInstance(fileInputElement);

   urlRepo = fileSettings.opcion;   
   numContenedor = localStorage.getItem('numContenedor'); 
   
    api.setOption('upload', {
        url: '/contenedores/files/upload',
        data: {
            urlRepo:urlRepo,
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
                for (var warning in data.warnings) {
                    alert(data.warnings[warning]);
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
            if(apiGrid){
                let dataGrid = apiGrid.getGridOption('rowData');
                var rowIndex = dataGrid.findIndex(d => d.NumContenedor == numContenedor)
                
                const colId = fileSettings.agGrid;

                // Obtener el nodo de la fila
                const rowNode = apiGrid.getDisplayedRowAtIndex(rowIndex);

                // Establecer un nuevo valor en la celda
                if (rowNode) {
                    rowNode.setDataValue(colId, true);
                }
            }
            

            toastr.options = {
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
              
              toastr.success( `Se cargó el archivo correctamente en el contenedor ${fileSettings.titulo}`,`${fileSettings.titulo}: Carga Exitosa`);

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

           
            setTimeout(()=> {
       
               adjuntarDocumentos()
            },2500)
            
        },
    });

 
}

function adjuntarDocumentos() {
    document.getElementById('content-file-input').innerHTML = '<input type="file" name="files" id="fileuploader">';
    $('input[name="files"]').fileuploader({
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
        upload: uploadConfig,
        beforeSelect: function(listEl, parentEl, newInputEl, inputEl) {
            resetUploadConfig();
        },
        onRemove: function(item) {
            $.post('remove', {
                _token: _token,
                _Folio: _Folio,
                file: item.name
            });
        },
        captions: $.extend(true, {}, $.fn.fileuploader.languages['es'], {
            feedback: 'Arrastre y suelte sus archivos aquí',
            feedback2: 'Arrastre y suelte sus archivos aquí',
            drop: 'Arrastre y suelte sus archivos aquí',
            or: 'o',
            button: 'Examinar archivos',
        }),
    });

   
   // api.uploadStart(); // Iniciar la carga manualmente

}