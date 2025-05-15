@extends('layouts.app')

@section('template_title')
    Equipos
@endsection
<style>
    .ag-theme-alpine .ag-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        line-height: 1.5rem !important;
        padding: 8px 4px !important;
        overflow: visible !important;
    }

    .ag-theme-alpine .ag-cell i {
        font-size: 1.25rem;
        /* asegúrate que sea visible */
        line-height: 1 !important;
    }
</style>
<style>
    /* Centrado vertical y horizontal */
    .ag-theme-alpine .ag-cell.actions-cell {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
        padding-top: 6px;
        padding-bottom: 6px;
    }

    /* Estilo uniforme para los botones */
    .ag-theme-alpine .actions-cell .btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        padding: 0.3rem 0.45rem;
        height: 32px;
        width: 32px;
        font-size: 14px;
    }

    /* Íconos centrados dentro del botón */
    .ag-theme-alpine .actions-cell i {
        margin: 0;
    }
</style>



<style>
    .nav-pills .nav-link {
        font-size: 0.85rem;
        padding: 6px 12px;
        color: #333;
        border-radius: 0.5rem;
        transition: background-color 0.3s ease, color 0.3s ease;
    }

    .nav-pills .nav-link i {
        font-size: 16px;
    }

    .nav-pills .nav-link.active {
        background-color: #354f8e !important;
        color: #fff !important;
    }

    .nav-pills .nav-link:hover {
        background-color: #e6e6e6;
        color: #111;
    }
</style>

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span id="card_title">Equipos</span>

                        @can('equipos-create')
                            <a type="button" class="btn bg-gradient-info btn-xs mb-2" data-bs-toggle="modal"
                                data-bs-target="#equipoModal">
                                <i class="fa fa-fw fa-plus"></i>&nbsp; Crear Equipo
                            </a>
                        @endcan
                    </div>

                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1 flex-row" id="equiposTabs">
                            <li class="nav-item">
                                <a class="nav-link active" id="tab-camiones" data-bs-toggle="tab"
                                    data-bs-target="#nav-camiones" role="tab">
                                    <i class="fa-solid fa-truck me-2"></i>
                                    Tractos / Camiones
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-chasis" data-bs-toggle="tab" data-bs-target="#nav-chasis"
                                    role="tab">
                                    <i class="fa-solid fa-truck-ramp-box me-2"></i>
                                    Chasis / Plataforma
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="tab-dollys" data-bs-toggle="tab" data-bs-target="#nav-dollys"
                                    role="tab">
                                    <i class="fa-solid fa-truck-monster me-2"></i>
                                    Dollys
                                </a>
                            </li>
                        </ul>
                    </div>

                    <div class="tab-content" id="nav-tabContent">
                        <!-- Camiones -->
                        <div class="tab-pane fade show active" id="nav-camiones" role="tabpanel"
                            aria-labelledby="tab-camiones" tabindex="0">
                            <div class="card-body">
                                <div id="gridCamiones" class="ag-theme-alpine" style="height: 500px;"></div>
                            </div>
                        </div>

                        <!-- Chasis -->
                        <div class="tab-pane fade" id="nav-chasis" role="tabpanel" aria-labelledby="tab-chasis"
                            tabindex="0">
                            <div class="card-body">
                                <div id="gridChasis" class="ag-theme-alpine" style="height: 500px;"></div>
                            </div>
                        </div>

                        <!-- Dollys -->
                        <div class="tab-pane fade" id="nav-dollys" role="tabpanel" aria-labelledby="tab-dollys"
                            tabindex="0">
                            <div class="card-body">
                                <div id="gridDolys" class="ag-theme-alpine" style="height: 500px;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('equipos.modal_create')

    @foreach ($equipos_dolys as $item)
        @include('equipos.modal_edit', ['item' => $item])
    @endforeach
    @foreach ($equipos_chasis as $item)
        @include('equipos.modal_edit', ['item' => $item])
    @endforeach
    @foreach ($equipos_camiones as $item)
        @include('equipos.modal_edit', ['item' => $item])
    @endforeach

    @foreach ($equipos_dolys as $item)
        @include('equipos.modal_docs', ['item' => $item])
    @endforeach

    @foreach ($equipos_chasis as $item)
        @include('equipos.modal_docs', ['item' => $item])
    @endforeach

    @foreach ($equipos_camiones as $item)
        @include('equipos.modal_docs', ['item' => $item])
    @endforeach
@endsection
@section('datatable')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/sgt/equipos/equipos_list.js') }}"></script>
    {{-- Forzar carga de FA con defer --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"
        integrity="sha512-papapasdf..." crossorigin="anonymous" referrerpolicy="no-referrer" />
@endsection
