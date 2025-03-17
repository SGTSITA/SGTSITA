@extends('layouts.app')

@section('template_title', 'Cotizaciones')

@section('content')
    <style>
        #myGrid {
            height: 500px;
            width: 100%;
        }
    </style>

    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <span id="card_title">Cotizaciones</span>
                        @can('cotizaciones-create')
                            <a type="button" class="btn bg-gradient-info btn-xs mb-2" href="{{ route('create.cotizaciones') }}">
                                +&nbsp; Crear Cotizacion
                            </a>
                        @endcan
                    </div>

                    <!-- Pestañas sin recargar página -->
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1 flex-row" id="cotTabs">
                            <li class="nav-item">
                                <a class="nav-link active" data-status="planeadas">
                                    <i class="fa-solid fa-clipboard-list" style="font-size: 18px;"></i>
                                    <span class="ms-2">Planeadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="finalizadas">
                                    <i class="fa-solid fa-check-circle" style="font-size: 18px;"></i>
                                    <span class="ms-2">Finalizadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="en_espera">
                                    <i class="fa-solid fa-clock" style="font-size: 18px;"></i>
                                    <span class="ms-2">En Espera</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="aprobadas">
                                    <i class="fa-solid fa-thumbs-up" style="font-size: 18px;"></i>
                                    <span class="ms-2">Aprobadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" data-status="canceladas">
                                    <i class="fa-solid fa-ban" style="font-size: 18px;"></i>
                                    <span class="ms-2">Canceladas</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Contenedor de AG Grid -->
                    <div id="myGrid" class="ag-theme-alpine"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <!-- Nuestro JavaScript unificado -->
    <script
        src="{{ asset('js/sgt/cotizaciones/cotizaciones_list.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/cotizaciones_list.js')) }}">
    </script>
@endpush
