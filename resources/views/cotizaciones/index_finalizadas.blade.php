@extends('layouts.app')

@section('template_title')
    Cotizaciones Finalizadas
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center">
                            <span id="card_title">Cotizaciones Finalizadas</span>
                            <div class="float-right">
                                @can('cotizaciones-create')
                                    <a
                                        type="button"
                                        class="btn bg-gradient-info btn-xs mb-2"
                                        href="{{ route('create.cotizaciones') }}"
                                    >
                                        +&nbsp; Crear Cotizacion
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <!-- Menú de pestañas -->
                    <div class="nav-wrapper position-relative end-0">
                        <ul class="nav nav-pills nav-fill p-1 flex-row" role="tablist">
                            <li class="nav-item">
                                <a
                                    class="nav-link {{ request()->routeIs('index.cotizaciones') ? 'active' : '' }}"
                                    href="{{ route('index.cotizaciones') }}"
                                >
                                    <i class="fa-solid fa-clipboard-list"></i>
                                    <span>Planeadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link {{ request()->routeIs('index_finzaliadas.cotizaciones') ? 'active' : '' }}"
                                    href="{{ route('index_finzaliadas.cotizaciones') }}"
                                >
                                    <i class="fa-solid fa-check-circle"></i>
                                    <span>Finalizadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link {{ request()->routeIs('index_espera.cotizaciones') ? 'active' : '' }}"
                                    href="{{ route('index_espera.cotizaciones') }}"
                                >
                                    <i class="fa-solid fa-clock"></i>
                                    <span>En Espera</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link {{ request()->routeIs('index_aprobadas.cotizaciones') ? 'active' : '' }}"
                                    href="{{ route('index_aprobadas.cotizaciones') }}"
                                >
                                    <i class="fa-solid fa-thumbs-up"></i>
                                    <span>Aprobadas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a
                                    class="nav-link {{ request()->routeIs('index_canceladas.cotizaciones') ? 'active' : '' }}"
                                    href="{{ route('index_canceladas.cotizaciones') }}"
                                >
                                    <i class="fa-solid fa-ban"></i>
                                    <span>Canceladas</span>
                                </a>
                            </li>
                        </ul>
                    </div>

                    <!-- Contenedor para AG Grid -->
                    <div id="gridFinalizadas" class="ag-theme-alpine" style="height: 500px; width: 100%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Importar AG Grid -->
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <!-- Incluir el archivo JavaScript -->
    <script src="{{ asset('js/sgt/cotizaciones/finalizadas_list.js') }}"></script>
@endsection
