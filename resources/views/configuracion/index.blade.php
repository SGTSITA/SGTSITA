@extends('layouts.app')

@section('template_title')
    Configuracion
@endsection

@section('content')
    <div class="container-fluid mt-3">
        <div class="row">
            <div class="col">
                <div class="card">
                    <!-- Card header -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h3 class="mb-0">Configuracion Sistema</h3>
                        <div class="d-flex gap-2">
                            <a href="{{ route('descargar.db') }}" class="btn btn-sm btn-primary" onclick="event.preventDefault(); confirmarDescargaDb(this.href);">
                                <i class="fas fa-database me-1"></i> Descargar Base de Datos
                            </a>
                            <a href="{{ route('backups.historiales') }}" class="btn btn-sm btn-info">
                                <i class="fas fa-file-archive me-1"></i> Respaldos de Historiales (ZIP)
                            </a>
                        </div>
                    </div>

                    <div class="card-body mb-5">
                        <form
                            method="POST"
                            action="{{ route('update.configuracion', auth()->user()->Empresa->Configuracion->id) }}"
                            enctype="multipart/form-data"
                            role="form"
                        >
                            @csrf
                            <input type="hidden" name="_method" value="PATCH" />
                            <div class="row">
                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <h3 class="mb-3">Sistema</h3>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Nombre Sistema:</label>
                                        <input
                                            class="form-control"
                                            type="text"
                                            id="nombre_sistema"
                                            name="nombre_sistema"
                                            value="{{ $configuracion->nombre_sistema }}"
                                        />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Logo:</label>
                                        <input
                                            class="form-control"
                                            type="file"
                                            id="logo"
                                            name="logo"
                                            value="{{ $configuracion->logo }}"
                                        />
                                        <img
                                            src="{{ asset('logo/' . $configuracion->logo) }}"
                                            style="width: 50%; heigth: 50%"
                                        />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Favicon:</label>
                                        <input
                                            class="form-control"
                                            type="file"
                                            id="favicon"
                                            name="favicon"
                                            value="{{ $configuracion->favicon }}"
                                        />
                                        <img
                                            src="{{ asset('favicon/' . $configuracion->favicon) }}"
                                            style="width: 20%; heigth: 20%"
                                        />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Color Principal:</label>
                                        <input
                                            class="form-control"
                                            type="color"
                                            id="color_principal"
                                            name="color_principal"
                                            value="{{ $configuracion->color_principal }}"
                                        />
                                    </div>
                                </div>



                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <h3 class="mt-3">Color Iconos</h3>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Color iconos sidebar:</label>
                                        <input
                                            class="form-control"
                                            type="color"
                                            id="color_iconos_sidebar"
                                            name="color_iconos_sidebar"
                                            value="{{ $configuracion->color_iconos_sidebar }}"
                                        />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Color iconos cards:</label>
                                        <input
                                            class="form-control"
                                            type="color"
                                            id="color_iconos_cards"
                                            name="color_iconos_cards"
                                            value="{{ $configuracion->color_iconos_cards }}"
                                        />
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-12">
                                    <h3 class="mt-3">Botones</h3>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Color boton add:</label>
                                        <input
                                            class="form-control"
                                            type="color"
                                            id="color_boton_add"
                                            name="color_boton_add"
                                            value="{{ $configuracion->color_boton_add }}"
                                        />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Icono boton add:</label>
                                        <input
                                            class="form-control"
                                            type="text"
                                            id="icon_boton_add"
                                            name="icon_boton_add"
                                            value="{{ $configuracion->icon_boton_add }}"
                                        />
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Color boton save:</label>
                                        <input
                                            class="form-control"
                                            type="color"
                                            id="color_boton_save"
                                            name="color_boton_save"
                                            value="{{ $configuracion->color_boton_save }}"
                                        />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Icono boton save:</label>
                                        <input
                                            class="form-control"
                                            type="text"
                                            id="icon_boton_save"
                                            name="icon_boton_save"
                                            value="{{ $configuracion->icon_boton_save }}"
                                        />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Color boton close:</label>
                                        <input
                                            class="form-control"
                                            type="color"
                                            id="color_boton_close"
                                            name="color_boton_close"
                                            value="{{ $configuracion->color_boton_close }}"
                                        />
                                    </div>
                                </div>
                                <div class="col-xs-12 col-sm-12 col-md-3">
                                    <div class="form-group">
                                        <label class="form-control-label">Icono boton close:</label>
                                        <input
                                            class="form-control"
                                            type="text"
                                            id="icon_boton_close"
                                            name="icon_boton_close"
                                            value="{{ $configuracion->icon_boton_close }}"
                                        />
                                    </div>
                                </div>

                                <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                                    <button
                                        type="submit"
                                        class="btn"
                                        style="background: {{ $configuracion->color_boton_save }}; color: #ffff"
                                    >
                                        Actualizar
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
<script>
function confirmarDescargaDb(url) {
    Swal.fire({
        title: 'Generar Respaldo de Base de Datos',
        text: '¿Deseas iniciar la generación del respaldo completo de la base de datos? Esto guardará un archivo ZIP en el servidor.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, generar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Generando respaldo...',
                text: 'Por favor, espera un momento mientras se crea el archivo.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            window.location.href = url;
        }
    });
}
</script>
@endpush
