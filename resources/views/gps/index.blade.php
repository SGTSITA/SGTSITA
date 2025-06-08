@extends('layouts.app')

@section('template_title', 'Proveedores GPS')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header text-white d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Proveedores GPS</h5>
                        <div class="d-flex gap-2">
                            <a href="#" class="btn btn-info btn-sm" data-bs-toggle="modal" data-bs-target="#modalGps">
                                +&nbsp; Nuevo Proveedor GPS
                            </a>
                        </div>
                    </div>

                    <div class="card-body mt-3">
                        <div class="row">
                            <div id="gpsGrid" class="ag-theme-alpine" style="height: 500px; width: 100%;"></div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para crear/editar proveedor GPS --}}
    <div class="modal fade" id="modalGps" tabindex="-1" aria-labelledby="modalGpsLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header  text-white">
                    <h5 class="modal-title" id="modalGpsLabel">Nuevo Proveedor GPS</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>
                <div class="modal-body">
                    <form id="formGps">
                        @csrf
                        <input type="hidden" id="gps_id" name="id">
                        <div class="mb-3">
                            <label for="nombre" class="form-label">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="mb-3">
                            <label for="url" class="form-label">URL</label>
                            <input type="text" class="form-control" id="url" name="url" required>
                        </div>
                        <div class="mb-3">
                            <label for="url_conexion" class="form-label">URL Conexión</label>
                            <input type="text" class="form-control" id="url_conexion" name="url_conexion">
                        </div>

                        <div class="mb-3">
                            <label for="telefono" class="form-label">Teléfono</label>
                            <input type="text" class="form-control" id="telefono" name="telefono">
                        </div>
                        <div class="mb-3">
                            <label for="correo" class="form-label">Correo</label>
                            <input type="email" class="form-control" id="correo" name="correo">
                        </div>
                        <div class="mb-3">
                            <label for="contacto" class="form-label">Contacto</label>
                            <input type="text" class="form-control" id="contacto" name="contacto">
                        </div>
                        <div class="text-end">
                            <button type="submit" class="btn btn-success">Guardar</button>
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    {{-- AG Grid y JS --}}
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/gps/gps_list.js') }}"></script>

@endsection
