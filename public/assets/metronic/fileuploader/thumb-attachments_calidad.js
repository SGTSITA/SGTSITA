var _Reference = 0;

function AttachmentsViewer(Reference, Producto) {
    _Reference = Reference;
    var _token = $('input[name="_token"]').val();

    $.ajax({
        url: '/FoliosCalidad/Attachments/Viewer',
        type: 'post',
        data: { _token: _token, Reference_: _Reference },
        beforeSend: () => {
            document.getElementById('content-file-input').innerHTML =
                '<div class="jumbotron jumbotron-fluid"><div class="container"><h2 class="display-4 text-bold">Buscando...</h2><p class="lead">Espere un momento, estamos localizando sus archivos adjuntos.</p></div></div>';
        },
        success: (R) => {
            document.getElementById('content-file-input').innerHTML =
                '<hr><div class="panel-heading padding-right-70"><h2 class="display-4 text-bold inline-block"><small>Evidencia del producto</small><br>' +
                Producto +
                '</h2></div><input type="file" name="files" id="preloaded" data-fileuploader-files=' +
                "'" +
                R.preloadedFiles +
                "'" +
                '>';

            thumb_attach_calidad(_Reference);
        },
        error: () => {
            document.getElementById('content-file-input').innerHTML =
                '<div class="jumbotron jumbotron-fluid"><div class="container"><h2 class="display-4 text-bold">Error 500</h2><p class="lead">Lo sentimos, ocurrio un error mientras procesabamos su solicitud. Por favor intentelo nuevamente, si el problema persiste comuniquelo al administrador.</p></div></div>';
        },
    });
}

function thumb_attach_calidad(_Reference_) {
    var _token = $('input[name="_token"]').val();
    // enable fileuploader plugin
    $('input[name="files"]').fileuploader({
        captions: 'es',
        extensions: null,
        changeInput: ' ',
        theme: 'thumbnails',
        enableApi: true,
        addMore: false,
        thumbnails: {
            box:
                '<div class="fileuploader-items">' +
                '<ul class="fileuploader-items-list">' +
                '<li class="fileuploader-thumbnails-input"><div class="fileuploader-thumbnails-input-inner"><i>+</i></div></li>' +
                '</ul>' +
                '</div>',
            item:
                '<li class="fileuploader-item">' +
                '<div class="fileuploader-item-inner">' +
                '<div class="type-holder">${extension}</div>' +
                '<div class="actions-holder">' +
                '<button type="button" class="fileuploader-action fileuploader-action-remove" title="${captions.remove}"><i class="fileuploader-icon-remove"></i></button>' +
                '</div>' +
                '<div class="thumbnail-holder">' +
                '${image}' +
                '<span class="fileuploader-action-popup"></span>' +
                '</div>' +
                '<div class="content-holder"><h5>${name}</h5><span>${size2}</span></div>' +
                '<div class="progress-holder">${progressBar}</div>' +
                '</div>' +
                '</li>',
            item2:
                '<li class="fileuploader-item">' +
                '<div class="fileuploader-item-inner">' +
                '<div class="type-holder">${extension}</div>' +
                '<div class="actions-holder">' +
                '<a href="${file}" class="fileuploader-action fileuploader-action-download" title="${captions.download}" download><i class="fileuploader-icon-download"></i></a>' +
                '<button type="button" class="fileuploader-action fileuploader-action-remove" title="${captions.remove}"><i class="fileuploader-icon-remove"></i></button>' +
                '</div>' +
                '<div class="thumbnail-holder">' +
                '${image}' +
                '<span class="fileuploader-action-popup"></span>' +
                '</div>' +
                '<div class="content-holder"><h5 title="${data.old_name}">${data.old_name}</h5><span>${size2}</span></div>' +
                '<div class="progress-holder">${progressBar}</div>' +
                '</div>' +
                '</li>',
            startImageRenderer: true,
            canvasImage: false,
            _selectors: {
                list: '.fileuploader-items-list',
                item: '.fileuploader-item',
                start: '.fileuploader-action-start',
                retry: '.fileuploader-action-retry',
                remove: '.fileuploader-action-remove',
            },
            onItemShow: function (item, listEl, parentEl, newInputEl, inputEl) {
                var plusInput = listEl.find('.fileuploader-thumbnails-input'),
                    api = $.fileuploader.getInstance(inputEl.get(0));

                plusInput
                    .insertAfter(item.html)
                    [
                        api.getOptions().limit && api.getChoosedFiles().length >= api.getOptions().limit
                            ? 'hide'
                            : 'show'
                    ]();

                if (item.format == 'image') {
                    item.html.find('.fileuploader-item-icon').hide();
                }

                //	item.html.find('.content-holder > h5').text(item.data.old_name).attr('title', item.data.name);
            },
            onItemRemove: function (html, listEl, parentEl, newInputEl, inputEl) {
                var plusInput = listEl.find('.fileuploader-thumbnails-input'),
                    api = $.fileuploader.getInstance(inputEl.get(0));

                html.children().animate({ opacity: 0 }, 200, function () {
                    html.remove();

                    if (api.getOptions().limit && api.getChoosedFiles().length - 1 < api.getOptions().limit)
                        plusInput.show();
                });
            },
        },
        dragDrop: {
            container: '.fileuploader-thumbnails-input',
        },
        afterRender: function (listEl, parentEl, newInputEl, inputEl) {
            var plusInput = listEl.find('.fileuploader-thumbnails-input'),
                api = $.fileuploader.getInstance(inputEl.get(0));

            plusInput.on('click', function () {
                api.open();
            });

            api.getOptions().dragDrop.container = plusInput;
        },

        /*
		// while using upload option, please set
		// startImageRenderer: false
		// for a better effect*/
        upload: {
            url: '/FoliosCalidad/Attachments/UploadFiles',
            data: { _token: _token, Reference_: _Reference },
            type: 'POST',
            enctype: 'multipart/form-data',
            start: true,
            synchron: true,
            beforeSend: null,
            onSuccess: function (result, item) {
                var data = {};

                if (result && result.files) data = result;
                else data.hasWarnings = true;

                // if success
                if (data.isSuccess && data.files.length) {
                    item.name = data.files[0].name;
                    item.html.find('.content-holder > h5').text(item.old_name).attr('title', item.old_name);
                }

                // if warnings
                if (data.hasWarnings) {
                    for (var warning in data.warnings) {
                        alert(data.warnings[warning]);
                    }

                    item.html.removeClass('upload-successful').addClass('upload-failed');
                    return this.onError ? this.onError(item) : null;
                }

                item.html.find('.fileuploader-action-remove').addClass('fileuploader-action-success');

                setTimeout(function () {
                    item.html.find('.progress-holder').hide();
                    item.renderThumbnail();

                    item.html.find('.fileuploader-action-popup, .fileuploader-item-image').show();
                }, 400);
            },
            onError: function (item) {
                item.html.find('.progress-holder, .fileuploader-action-popup, .fileuploader-item-image').hide();
            },
            onProgress: function (data, item) {
                var progressBar = item.html.find('.progress-holder');

                if (progressBar.length > 0) {
                    progressBar.show();
                    progressBar.find('.fileuploader-progressbar .bar').width(data.percentage + '%');
                }

                item.html.find('.fileuploader-action-popup, .fileuploader-item-image').hide();
            },
        },
        onRemove: function (item) {
            $.post('/FoliosCalidad/Attachments/DeleteFiles', {
                _token: _token,
                Reference_: _Reference,
                file: item.name,
            });
        },
    });
}
