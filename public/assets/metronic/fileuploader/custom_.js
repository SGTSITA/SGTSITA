function SubirDocs() {
	var _token = $('input[name="_token"]').val();
	// enable fileupload plugin
	var FileName = "";
	
	$('input[name="files"]').fileuploader({
		captions: 'es',
		theme: 'dragdrop',
        onSelect: function(item) {
            if (!item.html.find('.fileuploader-action-start').length)
                item.html.find('.fileuploader-action-remove').before('<button type="button" class="fileuploader-action fileuploader-action-start" title="Upload"><i class="fileuploader-icon-upload"></i></button>');
        },
		upload: {
            url: '',
            data: {_token:_token},
            type: 'POST',
            enctype: 'multipart/form-data',
            start: false,
            synchron: true,
            onSuccess: function(result, item) {
				console.log(result);
				var Files = result.files;
				Files.forEach((d)=>{
					FileName = d.name;
				});
                item.html.find('.fileuploader-action-remove').addClass('fileuploader-action-success');
            },
            onError: function(item) {
                 item.upload.status != 'cancelled' && item.html.find('.fileuploader-action-retry').length == 0 ? item.html.find('.column-actions').prepend(
                    '<button type="button" class="fileuploader-action fileuploader-action-retry" title="Retry"><i class="fileuploader-icon-retry"></i></button>'
                ) : null;
            },
            onComplete: null,
        },
		onRemove: function(item) {
			// send POST request
			console.log(item);
			$.post('', {
				_token:_token,
				file: item.name
			});
		}
	});
	
}