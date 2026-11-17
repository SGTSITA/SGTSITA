@extends('layouts.app')

@section('template_title')
    Captura Múltiple de Costos
@endsection

@section('content')
    <style>
        .resumen-card {
            background: #ffffff;
            border: 1px dashed #d2d6da;
            border-radius: 0.75rem;
            padding: 0.65rem 0.85rem;
            transition: all 0.2s ease;
            min-width: 110px;
        }

        .resumen-card:hover {
            border-color: #cb0c9f;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        .resumen-card .resumen-label {
            font-size: 0.72rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #6c757d;
            margin-bottom: 0.15rem;
            letter-spacing: 0.5px;
        }

        .resumen-card .resumen-valor {
            font-size: 0.95rem;
            font-weight: 800;
            color: #344767;
            margin-bottom: 0;
        }

        .resumen-card.destacado {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-color: #17c1e8;
        }

        .resumen-card.destacado .resumen-valor {
            color: #17c1e8;
        }

        .resumen-card.destacado-prov {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-color: #82d616;
        }

        .resumen-card.destacado-prov .resumen-valor {
            color: #82d616;
        }

        .nav-tabs .nav-link {
            font-weight: 700;
            color: #6c757d;
            border: none;
            border-bottom: 3px solid transparent;
            padding: 0.75rem 1.5rem;
            cursor: pointer;
        }

        .nav-tabs .nav-link.active {
            color: #cb0c9f;
            border-bottom: 3px solid #cb0c9f;
            background: transparent;
        }

        .hot-table {
            width: 100%;
            height: 520px;
            overflow: hidden;
            border: 1px solid #e9ecef;
            border-radius: 0.5rem;
        }

        .handsontable td.htCenter {
            text-align: center;
        }

        .handsontable td.htRight {
            text-align: right;
        }

        .handsontable .btn-mapa-grid {
            padding: 2px 8px;
            font-size: 11px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }

        .badge-empresa-global {
            background: #e8fadf;
            color: #28a745;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 6px;
            font-weight: 600;
        }
    </style>

    <div class="container-fluid py-2">
        <div class="row">
            <div class="col-12">
                {{-- Encabezado Principal --}}
                <div class="card mb-3">
                    <div class="card-header pb-0 p-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <div>
                                <h5 class="mb-0 font-weight-bolder text-dark">
                                    <i class="fas fa-layer-group text-primary me-2"></i>
                                    Captura Múltiple de Costos — Seguimiento
                                </h5>
                                <p class="text-xs text-muted mb-0">
                                    Edición masiva de costos de cotización (cliente en viajes->costos) y costos de proveedor (en asignaciones)
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                @if ($esGlobal)
                                    <span class="badge-empresa-global" title="Empresa Multi-Equipo H00">
                                        <i class="fas fa-building me-1"></i> Empresa Global (Multi-Equipo)
                                    </span>
                                @endif
                                <a class="btn btn-sm btn-outline-secondary mb-0" href="{{ route('index.cotizaciones') }}">
                                    <i class="fas fa-chevron-left me-1"></i> Regresar a Seguimiento
                                </a>
                            </div>
                        </div>
                    </div>

                    {{-- Barra de Filtros Superiores --}}
                    <div class="card-body p-3">
                        <div class="row g-3 align-items-end">
                            <div class="{{ $esGlobal ? 'col-md-4' : 'col-md-5' }} col-sm-12">
                                <label for="selectCliente" class="form-label text-xs font-weight-bold text-uppercase mb-1">
                                    Cliente
                                </label>
                                <select class="form-select form-select-sm" id="selectCliente" name="cliente_id" data-toggle="select">
                                    <option value="" selected>Todos los clientes</option>
                                    @foreach ($clientes as $c)
                                        <option value="{{ $c->id }}">{{ $c->nombre }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="{{ $esGlobal ? 'col-md-3' : 'col-md-4' }} col-sm-12">
                                <label for="selectEstadoCosto" class="form-label text-xs font-weight-bold text-uppercase mb-1">
                                    Filtro de Costos
                                </label>
                                <select class="form-select form-select-sm" id="selectEstadoCosto" name="estado_costo">
                                    <option value="todos" selected>Todos</option>
                                    <option value="no_capturados">No capturados</option>
                                    <option value="capturados">Capturados</option>
                                </select>
                            </div>

                            @if ($esGlobal)
                                <div class="col-md-3 col-sm-12">
                                    <label for="selectEquipo" class="form-label text-xs font-weight-bold text-uppercase mb-1">
                                        Equipo / Unidad (Tracto)
                                    </label>
                                    <select class="form-select form-select-sm" id="selectEquipo" name="equipo_id" data-toggle="select">
                                        <option value="">Todos los equipos</option>
                                        @foreach ($equipos as $eq)
                                            <option value="{{ $eq->id_equipo }}">
                                                {{ $eq->id_equipo }} - {{ $eq->marca ?? 'Sin marca' }} ({{ $eq->placas ?? 'S/P' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="{{ $esGlobal ? 'col-md-2' : 'col-md-3' }} col-sm-12 d-flex gap-2">
                                <button type="button" class="btn btn-sm bg-gradient-primary w-100 mb-0" id="btnConsultar">
                                    <i class="fas fa-search me-1"></i> Consultar
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary mb-0" id="btnLimpiar" title="Restablecer filtros">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Contenedor de Pestañas y Tablas --}}
                <div class="card">
                    <div class="card-header pb-0 p-3 border-bottom">
                        <ul class="nav nav-tabs" id="costosTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" id="tab-cotizacion-nav" data-bs-toggle="tab"
                                    data-bs-target="#tab-cotizacion" type="button" role="tab" aria-controls="tab-cotizacion"
                                    aria-selected="true">
                                    <i class="fas fa-file-invoice-dollar me-2 text-info"></i>
                                    Cotización
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" id="tab-proveedor-nav" data-bs-toggle="tab"
                                    data-bs-target="#tab-proveedor" type="button" role="tab" aria-controls="tab-proveedor"
                                    aria-selected="false">
                                    <i class="fas fa-truck-moving me-2 text-success"></i>
                                    Proveedor
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="card-body p-3">
                        <div class="tab-content" id="costosTabContent">

                            {{-- ==================== TAB 1: COTIZACIÓN ==================== --}}
                            <div class="tab-pane fade show active" id="tab-cotizacion" role="tabpanel"
                                aria-labelledby="tab-cotizacion-nav">

                                {{-- Tarjetas de Resumen Cotización --}}
                                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="resumen-card">
                                            <div class="resumen-label">Viajes</div>
                                            <div class="resumen-valor" id="cot_resumen_viajes">0</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Fletes</div>
                                            <div class="resumen-valor" id="cot_resumen_fletes">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Burreo</div>
                                            <div class="resumen-valor" id="cot_resumen_burreo">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Maniobra</div>
                                            <div class="resumen-valor" id="cot_resumen_maniobra">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Estadía</div>
                                            <div class="resumen-valor" id="cot_resumen_estadia">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Sobrepeso</div>
                                            <div class="resumen-valor" id="cot_resumen_sobrepeso">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Otros</div>
                                            <div class="resumen-valor" id="cot_resumen_otros">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">IVA (16%)</div>
                                            <div class="resumen-valor" id="cot_resumen_iva">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Ret. (4%)</div>
                                            <div class="resumen-valor" id="cot_resumen_retencion">$0.00</div>
                                        </div>
                                        <div class="resumen-card destacado">
                                            <div class="resumen-label text-info">Total Cotización</div>
                                            <div class="resumen-valor" id="cot_resumen_total">$0.00</div>
                                        </div>
                                    </div>

                                    <div>
                                        <button type="button" class="btn btn-sm bg-gradient-success mb-0" id="btnGuardarCotizacion" disabled>
                                            <i class="fas fa-save me-1"></i> Guardar Costos Cotización
                                        </button>
                                    </div>
                                </div>

                                {{-- Contenedor Handsontable Cotización --}}
                                <div id="gridCostosCotizacion" class="hot-table"></div>
                                <div class="text-xs text-muted mt-2 d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-info-circle me-1"></i>
                                        Haga doble clic sobre las celdas numéricas para editar. IVA (16%), Retención (4%), Base 2 y Totales se recalculan automáticamente.
                                    </span>
                                    <span id="cot_status_cambios" class="text-secondary font-weight-bold">Sin cambios pendientes</span>
                                </div>
                            </div>

                            {{-- ==================== TAB 2: PROVEEDOR ==================== --}}
                            <div class="tab-pane fade" id="tab-proveedor" role="tabpanel"
                                aria-labelledby="tab-proveedor-nav">

                                {{-- Tarjetas de Resumen Proveedor --}}
                                <div class="d-flex flex-wrap gap-2 align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="resumen-card">
                                            <div class="resumen-label">Viajes</div>
                                            <div class="resumen-valor" id="prov_resumen_viajes">0</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Costo Viaje</div>
                                            <div class="resumen-valor" id="prov_resumen_fletes">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Burreo</div>
                                            <div class="resumen-valor" id="prov_resumen_burreo">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Maniobra</div>
                                            <div class="resumen-valor" id="prov_resumen_maniobra">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Estadía</div>
                                            <div class="resumen-valor" id="prov_resumen_estadia">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Sobrepeso</div>
                                            <div class="resumen-valor" id="prov_resumen_sobrepeso">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Otros</div>
                                            <div class="resumen-valor" id="prov_resumen_otros">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">IVA</div>
                                            <div class="resumen-valor" id="prov_resumen_iva">$0.00</div>
                                        </div>
                                        <div class="resumen-card">
                                            <div class="resumen-label">Retención</div>
                                            <div class="resumen-valor" id="prov_resumen_retencion">$0.00</div>
                                        </div>
                                        <div class="resumen-card destacado-prov">
                                            <div class="resumen-label text-success">Total Proveedor</div>
                                            <div class="resumen-valor" id="prov_resumen_total">$0.00</div>
                                        </div>
                                    </div>

                                    <div>
                                        <button type="button" class="btn btn-sm bg-gradient-success mb-0" id="btnGuardarProveedor" disabled>
                                            <i class="fas fa-save me-1"></i> Guardar Costos Proveedor
                                        </button>
                                    </div>
                                </div>

                                {{-- Contenedor Handsontable Proveedor --}}
                                <div id="gridCostosProveedor" class="hot-table"></div>
                                <div class="text-xs text-muted mt-2 d-flex justify-content-between align-items-center">
                                    <span>
                                        <i class="fas fa-info-circle me-1"></i>
                                        Los costos de proveedor se persisten directamente en la tabla de asignaciones del viaje.
                                    </span>
                                    <span id="prov_status_cambios" class="text-secondary font-weight-bold">Sin cambios pendientes</span>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <link href="{{ asset('assets/handsontable/handsontable.full.min.css') }}" rel="stylesheet" media="screen" />
    <script src="{{ asset('assets/handsontable/handsontable.full.min.js') }}"></script>
    <script src="{{ asset('assets/handsontable/all.js') }}"></script>
    <script src="{{ asset('assets/admin/vendor/select2/dist/js/select2.min.js') }}"></script>
    <script>
        window.sgtCostosMultipleCatalogoProveedores = @json($proveedores);
    </script>
    <script src="{{ asset('js/sgt/cotizaciones/costos-multiple.js') }}?v={{ filemtime(public_path('js/sgt/cotizaciones/costos-multiple.js')) }}"></script>
@endpush
