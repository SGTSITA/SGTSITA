@extends('layouts.app')

@section('template_title')
    Cotizaciones- Solicitudes Local
@endsection

@section('content')
    <style>
        .custom-tabs .custom-tab {
            background-color: #f8f9fa;
            /* Color por defecto */
            border-color: #dee2e6;
            /* Color del borde por defecto */
            color: #495057;
            /* Color del texto por defecto */
        }

        .custom-tabs .custom-tab.active {
            background-color: #47a0cd;
            /* Color de fondo del tab activo */
            border-color: #47a0cd;
            /* Color del borde del tab activo */
            color: #ffffff;
            /* Color del texto del tab activo */
        }
    </style>


    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">

                            <span id="card_title">
                             Solicitudes Maniobra local
                            </span>



                        </div>
                    </div>

                    <!-- Asegúrate de incluir Font Awesome en el head -->
                    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">

                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1 flex-row" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link mb-0 px-0 py-1 d-flex align-items-center justify-content-center
                                  {{ request()->routeIs('index.cotizaciones') ? 'active' : '' }}"
                                    href="#" role="tab"
                                    aria-selected="{{ request()->routeIs('index.cotizaciones') ? 'true' : 'false' }}">
                                    <i class="fa-solid fa-clipboard-list" style="font-size: 18px;"></i>
                                    <span class="ms-2">Local</span>
                                </a>
                            </li>


                        </ul>
                    </div>


                    <div class="table-responsive">
                        <table class="table table-flush" id="datatable-search">
                            <thead class="thead">
                                <tr>
                                    <th>No</th>
                                    <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt=""
                                            width="25px">Cliente</th>
                                    <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Origen
                                    </th>
                                    <th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">Destino
                                    </th>
                                    <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px">#
                                        Contenedor</th>
                                    <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt=""
                                            width="25px">Estatus</th>
                                    <th><img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cotizaciones as $cotizacion)
                                    <tr>
                                        <td>{{ $cotizacion->id }}</td>
                                        <td>{{ $cotizacion->nombre_cliente }}</td>
                                        <td>{{ $cotizacion->origen_local }}</td>
                                        <td>{{ $cotizacion->destino_local }}</td>
                                        <td>{{ $cotizacion->num_contenedor }}</td>
                                        <td>
                                            @if ($cotizacion->estatus == 'Local')
                                                {{-- @can('cotizaciones-estatus') --}}
                                                    <button type="button" class="btn btn-outline-success btn-xs"
                                                        data-bs-toggle="modal"
                                                        data-bs-target="#estatusModal{{ $cotizacion->id }}">
                                                        {{ $cotizacion->estatus }}
                                                    </button>
                                                {{-- @endcan --}}
                                            @endif
                                        </td>
                                        <td class="text-center">

                                        <!-- Botón: Subir documentos -->
                                        <button class="btn btn-sm btn-outline-primary me-1"
                                                onclick="abrirModalDocumentos({{ $cotizacion->id }}, '{{ $cotizacion->num_contenedor }}')"
                                                title="Subir documentos">
                                            <i class="fa-solid fa-upload"></i>
                                        </button>

                                        <!-- Botón: Ver documentos -->
                                        <button class="btn btn-sm btn-outline-success"
                                                onclick="abrirModalVerDocumentos({{ $cotizacion->id }}, '{{ $cotizacion->num_contenedor }}')"
                                                title="Ver documentos">
                                            <i class="fa-solid fa-folder-open"></i>
                                        </button>

                                    </td>
                                    </tr>
                                    @include('cotizaciones.modal_estatus_doc')
                                    @include('cotizaciones.modal_estatus')
                                @endforeach
                            </tbody>

                        </table>
                    </div>


                </div>
            </div>
        </div>
    </div>

    <!-- Modal Carga de Documentos -->
<div class="modal fade" id="modalDocumentos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Carga de Documentos Contenedor <span id="numContenedorcargar"></span></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">

                <form id="formDocumentos" method="POST" enctype="multipart/form-data">
                    @csrf

                    <input type="hidden" id="idContenedor">

                   <div id="contenedor-fileuploader">
                    <input type="file" name="files" id="fileuploader">
                </div>
                </form>

            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>

        </div>
    </div>
</div>
<div class="modal fade" id="modalVerDocumentos" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fa-solid fa-folder-open"></i> Documentos del Contenedor <span id="numContenedorver"></span>
                </h5>
                <button class="btn-close" data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body">
                <div id="listaDocumentos" class="row g-3">
                    <!-- Aquí se cargarán los documentos -->
                </div>
            </div>

        </div>
    </div>
</div>

@endsection
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">

<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

    <link href="{{ asset('assets/metronic/fileuploader/font/font-fileuploader.css') }}" rel="stylesheet">
    <link href="{{ asset('assets/metronic/fileuploader/jquery.fileuploader.min.css') }}" media="all" rel="stylesheet">
    <link href="{{ asset('assets/metronic/fileuploader/jquery.fileuploader-theme-dragdrop.css') }}" media="all" rel="stylesheet">

    <script src="{{ asset('assets/metronic/fileuploader/jquery.fileuploader.min.js') }}"></script>
@endpush


@section('datatable')
    <script type="text/javascript">
        const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
            searchable: true,
            fixedHeight: false
        });

        $(document).ready(function() {



            $('[id^="btn_clientes_search"]').click(function() {
                var cotizacionId = $(this).data(
                'cotizacion-id'); // Obtener el ID de la cotización del atributo data
                buscar_clientes(cotizacionId);
            });

            function buscar_clientes(cotizacionId) {
                $('#loadingSpinner').show();

                var fecha_inicio = $('#fecha_inicio_' + cotizacionId).val();
                var fecha_fin = $('#fecha_fin_' + cotizacionId).val();

                $.ajax({
                    url: '{{ route('equipos.planeaciones') }}',
                    type: 'get',
                    data: {
                        'fecha_inicio': fecha_inicio,
                        'fecha_fin': fecha_fin,
                        '_token': '{{ csrf_token() }}' // Agregar el token CSRF a los datos enviados
                    },
                    success: function(data) {
                        $('#resultado_equipos' + cotizacionId).html(
                        data); // Actualiza la sección con los datos del servicio
                    },
                    error: function(error) {
                        console.log(error);
                    },
                    complete: function() {
                        // Ocultar el spinner cuando la búsqueda esté completa
                        $('#loadingSpinner').hide();
                    }
                });
            }
        });


        document.addEventListener("DOMContentLoaded", function () {

    // ✔ Función para abrir el modal
    window.abrirModalDocumentos = function(idSolicitud,numContenedor) {
        console.log("Solicitud recibida:", idSolicitud);

          document.getElementById('numContenedorcargar').textContent = numContenedor;

        // Abrir modal
        $('#modalDocumentos').modal('show');

         setTimeout(() => {
            inicializarFileUploader(idSolicitud, numContenedor);
        }, 200);
    }

  function inicializarFileUploader(idSolicitud, num_contenedor) {

    let urlRepo = "BoletaPatio";
    let _token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    let DocNameSubir ="Boleta Patio";

    // 🧹 1. Eliminar el input anterior (si existe)
    if ($("#fileuploader").length) {
        $("#fileuploader").remove();
    }

    // 🔄 2. Crear un input nuevo limpio
    $("#contenedor-fileuploader").html(`
        <input type="file" name="files" id="fileuploader">
    `);

    // 🚀 3. Inicializar FileUploader REAL
    $('#fileuploader').fileuploader({
        theme: 'dragdrop',
        limit: 1,
        extensions: ['jpg','png','pdf','jpeg','xlsx'],
         captions: {
        button: "Seleccionar archivos",
        feedback: "Seleccione o arrastre archivos aquí ${DocNameSubir}",
        feedback2: "Archivos seleccionados",
        drop: "Arrastra los archivos aquí para subirlos",
        removeConfirmation: "¿Seguro que deseas eliminar este archivo?",
        errors: {
            filesLimit: "Solo puedes subir hasta ${limit} archivos.",
            filesType: "Solo se permiten archivos del tipo: ${extensions}.",
            fileSize: "El archivo ${name} es demasiado grande. Máximo: ${fileMaxSize} MB.",
            filesSizeAll: "El total de archivos excede el tamaño permitido (${maxSize} MB).",
            fileName: "Ya existe un archivo con el nombre ${name}.",
            folderUpload: "No se permiten carpetas."
        }
    },
         templates: {
        item: function(item) {
            return `
                <li class="fileuploader-item">
                    <div class="fileuploader-item-info">
                        <span class="fileuploader-item-title">${item.name}</span>
                        <span class="fileuploader-item-size">${item.size2}</span>
                    </div>

                    <div class="fileuploader-progressbar">
                        <div class="bar" style="width:0%">0%</div>
                    </div>
                </li>
            `;
        }
    },


        upload: {
            url: '/contenedores/files/upload',
            type: 'POST',
            enctype: 'multipart/form-data',
            synchron: true,
            start: true,


            data: {
                urlRepo: urlRepo,
                numContenedor: num_contenedor,
                idSolicitud: idSolicitud,
                _token: _token
            },
              onProgress: function(data, item) {
            var progreso = data.percentage;

            item.html.find(".fileuploader-progressbar .bar")
                .css("width", progreso + "%");

            item.html.find(".progress-text")
                .html(progreso + "%");
        },

            onSuccess: function(result, item) {
                toastr.success("Archivo cargado correctamente");
                console.log(result);
            },

            onError: function(item) {
                toastr.error("Error al subir el archivo");
            }
        },

        onProgress: function(data, item) {
            let progreso = data.percentage;
            item.html.find(".progress-bar2").css("width", progreso + "%");
            item.html.find(".fileuploader-progressbar .bar").html(progreso + "%");
        },

        onUploadComplete: function() {
            console.log("✔ Todos los archivos subidos");
        }
    });

}


window.abrirModalVerDocumentos= function (idSolicitud, numContenedor) {

     document.getElementById('numContenedorver').textContent = numContenedor;

    $("#listaDocumentos").html(`
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <p class="mt-2">Cargando documentos...</p>
        </div>
    `);

    let _token = document.querySelector('meta[name="csrf-token"]').content;

    $.ajax({
        url: "/contenedores/files/listar",
        type: "POST",
        data: {
            idSolicitud: idSolicitud,
            numContenedor: numContenedor,
            _token: _token
        },
        success: function (response) {

            if (!response || response.length === 0) {
                $("#listaDocumentos").html(`
                    <div class="text-center text-muted py-5">
                        <i class="fa-regular fa-folder-open fa-2x"></i>
                        <p class="mt-2">No hay documentos cargados</p>
                    </div>
                `);
                return;
            }

            let html = "";

            response.forEach(doc => {

              let ext = doc.fileType.toLowerCase();

                let isImage = ["jpg", "jpeg", "png", "gif", "webp"].includes(ext);
                let isPDF   = ext === "pdf";
                let isExcel = ext === "xlsx" || ext === "xls";

                let urlPath = doc.publicUrl + doc.filePath

                html += `
                        <div class="col-md-4">
                            <div class="card shadow-sm">
                                <div class="card-body text-center">
                                    ${
                                        isImage
                                        ? `<img src="${urlPath}" class="img-fluid rounded" style="max-height:120px;">`

                                        : isPDF
                                        ? `<i class="fa-solid fa-file-pdf fa-4x text-danger mb-2"></i>`

                                        : isExcel
                                        ? `<i class="fa-solid fa-file-excel fa-4x text-success mb-2"></i>`

                                        : `<i class="fa-solid fa-file fa-4x text-secondary mb-2"></i>`
                                    }

                                    <div class="mt-2 fw-bold">${doc.fileName}</div>

                                    <a href="${urlPath}" target="_blank" class="btn btn-sm btn-primary mt-2">
                                        <i class="fa-solid fa-eye"></i> Ver / Descargar
                                    </a>
                                </div>
                            </div>
                        </div>
                    `;
            });

            $("#listaDocumentos").html(html);
        },
        error: function () {
            $("#listaDocumentos").html(`
                <div class="text-center text-danger py-5">
                    <p>Error al cargar los documentos.</p>
                </div>
            `);
        }
    });

    $("#modalVerDocumentos").modal("show");
}




});
    </script>
@endsection
