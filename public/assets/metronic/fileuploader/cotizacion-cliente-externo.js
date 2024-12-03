var _Folio = 0;
let urlRepo = '';

var _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

let fileCartaPorte = document.querySelector('#fileCartaPorte');
let titleFileUploader = document.querySelector('#titleFileUploader');
let btnFileCartaPorte = document.querySelector('#btnFileCartaPorte');
let btnFileDODA = document.querySelector('#btnFileDODA');
let btnFileBoletaLiberacion = document.querySelector('#btnFileBoletaLiberacion');

fileCartaPorte.addEventListener('click',()=>{
    setFileUploaderSettings('files/upload','Carta Porte');
})

btnFileCartaPorte.addEventListener('click',()=>{
    setFileUploaderSettings('files/upload','Carta Porte');
})

btnFileDODA.addEventListener('click',()=>{
    setFileUploaderSettings('files/upload','DODA');
})

btnFileBoletaLiberacion.addEventListener('click',()=>{
    setFileUploaderSettings('files/upload','Boleta de Liberación');
})

function setFileUploaderSettings(url,title){
    urlRepo = url;
    titleFileUploader.innerText = title;
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

function adjuntarDocumentos(_Folio_) {
    _Folio = _Folio_;
    //var _token = $('input[name="_token"]').val();
    // enable fileuploader plugin
    $('input[name="files"]').fileuploader({
        captions: 'es',
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
            url: urlRepo,
            data: {
                _token: _token,
                _Folio: _Folio
            },
            type: 'POST',
            enctype: 'multipart/form-data',
            start: true,
            synchron: true,
            beforeSend: null,
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
            onComplete: null,
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

}

