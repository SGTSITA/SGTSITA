@extends('layouts.app')

@section('template_title')
    Configuración GPS
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">

                <div class="card">

                    <!-- HEADER -->
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span>Configuración GPS de Equipos</span>
                        <button class="btn btn-success mb-2" id="btnNuevoEquipo">
                            + Nuevo equipo
                        </button>
                    </div>


                    <!-- GRID -->
                    <div class="card-body">
                        <div id="gridEquiposGPS" class="ag-theme-alpine" style="height: 550px;"></div>
                    </div>

                </div>

            </div>
        </div>
    </div>

    <!-- MODAL CONFIGURAR -->
    <div class="modal fade" id="modalConfigGPS" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-md"> {{-- más angosto --}}
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">
                        ⚙️ Configurar GPS
                        <br>
                        <small id="infoEquipo" class="text-muted"></small>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body" style="max-height: 70vh; overflow-y:auto;">

                    <form id="formConfigGPS">

                        <input type="hidden" id="equipo_id" name="gps_company_id">
                        <div class="mb-3">
                            <label class="form-label">Tipo de configuración</label>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_config" value="sistema" checked>
                                <label class="form-check-label">
                                    🌐 Sistema
                                </label>
                            </div>

                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipo_config" value="personalizado">
                                <label class="form-check-label">
                                    ⚙️ Personalizado
                                </label>
                            </div>
                        </div>


                        <div class="mb-3 d-none" id="bloqueProveedor">
                            <label>Proveedor GPS</label>
                            <select id="gps_company_id" class="form-control" name="gps_company_id">
                                <option value="">Seleccionar</option>
                                @foreach ($gps_companies as $g)
                                    <option value="{{ $g->id }}" data-config='@json($g->account_fields)'>
                                        {{ $g->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div id="camposDinamicos" class="d-none"></div>


                        <div id="statusConexion" class="alert d-none mt-3"></div>


                        <div class="mt-3">
                            <button type="submit" class="btn btn-success w-100">
                                Guardar configuración...
                            </button>
                        </div>

                    </form>

                </div>

            </div>
        </div>
    </div>


    <div class="modal fade" id="modalEquipo" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="tituloEquipo">Nuevo equipo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">

                    <input type="hidden" id="equipo_id" name="equipo_id">

                    <div class="mb-2">
                        <label>Tipo de equipo</label>
                        <select id="tipo_equipo" class="form-control">
                            <option value="">Seleccione...</option>
                            <option value="Tractos / Camiones">Tractos / Camiones</option>
                            <option value="Chasis / Plataforma">Chasis / Plataforma</option>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label>ID Equipo</label>
                        <input type="text" id="numero_equipo" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label>IMEI</label>
                        <input type="text" id="imei" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label>Marca</label>
                        <input type="text" id="marca" class="form-control">
                    </div>

                    <div class="mb-2">
                        <label>Num. Serie / vin</label>
                        <input type="text" id="num_serie" class="form-control">
                    </div>


                    <div class="mb-2">
                        <label>Placas</label>
                        <input type="text" id="placas" class="form-control">
                    </div>

                </div>

                <div class="modal-footer">
                    <button class="btn btn-primary" id="btnGuardarEquipo">
                        Guardar
                    </button>
                </div>

            </div>
        </div>
    </div>
@endsection

@section('datatable')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/equipos/equipos_list_mep.js') }}"></script>

    <script>
        window.equipos = @json($equipos);
    </script>
@endsection
