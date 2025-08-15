@extends('layouts.app')

@section('template_title')
    Viajes pendientes para verfificar cambio
@endsection
<style>
    /* ==== Estilo Modal macOS Minimalista ==== */
    .mac-modal {
        border-radius: 18px;
        background-color: #fefefe;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        border: none;
        overflow: hidden;
        font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen, Ubuntu, Cantarell, "Open Sans",
            "Helvetica Neue", sans-serif;
    }

    .mac-modal-header {
        background: linear-gradient(to right, #f0f0f5, #eaeaf0);
        padding: 16px 24px;
        border-bottom: 1px solid #ddd;
        font-weight: 500;
        color: #333;
    }

    .mac-alert {
        background-color: #f8f9fa;
        border-left: 4px solid #007aff;
        padding: 12px 16px;
        border-radius: 8px;
        font-size: 0.95rem;
        color: #333;
        display: flex;
        align-items: center;
    }

    .mac-thead th {
        background-color: #f1f1f1;
        font-weight: 600;
        border-bottom: 1px solid #ddd;
        color: #444;
    }

    .table-hover tbody tr:hover {
        background-color: #f9f9f9;
    }

    /* ==== Botones estilo macOS ==== */
    .soft-btn {
        border: none;
        border-radius: 10px;
        padding: 10px 24px;
        font-size: 15px;
        font-weight: 500;
        transition: all 0.3s ease;
    }

    .soft-btn:focus {
        outline: none;
        box-shadow: 0 0 0 2px rgba(0, 122, 255, 0.3);
    }

    .btn-success.soft-btn {
        background-color: #007aff;
        color: white;
    }

    .btn-success.soft-btn:hover {
        background-color: #006ae6;
    }

    .btn-outline-danger.soft-btn {
        background-color: transparent;
        color: #ff3b30;
        border: 1px solid #ff3b30;
    }

    .btn-outline-danger.soft-btn:hover {
        background-color: #ff3b30;
        color: white;
    }

    /* ==== Checkbox personalizado estilo mac ==== */
    input[type="checkbox"].campo-checkbox {
        appearance: none;
        width: 20px;
        height: 20px;
        background-color: #f1f1f1;
        border: 2px solid #ccc;
        border-radius: 6px;
        position: relative;
        cursor: pointer;
        transition: all 0.2s;
    }

    input[type="checkbox"].campo-checkbox:checked {
        background-color: #007aff;
        border-color: #007aff;
    }

    input[type="checkbox"].campo-checkbox:checked::after {
        content: '';
        position: absolute;
        top: 2px;
        left: 6px;
        width: 5px;
        height: 10px;
        border: solid white;
        border-width: 0 2px 2px 0;
        transform: rotate(45deg);
    }

    /* ==== Celdas diferentes resaltadas ==== */
    .diff-cell {
        background-color: #f0f4ff !important;
        font-weight: 500;
        color: #1a1a1a;
        border-radius: 6px;
    }

    .diff-cell::after {
        content: " ✱";
        color: #007aff;
        font-size: 0.9rem;
        margin-left: 4px;
    }

    .mac-close-btn {
        font-size: 1.5rem;
        font-weight: bold;
        color: #666;
        background: transparent;
        border: none;
        outline: none;
        transition: color 0.2s ease;
    }

    .mac-close-btn:hover {
        color: #ff3b30;
        transform: scale(1.2);
        cursor: pointer;
    }
</style>

@section('content')
    <div class="container-fluid py-4">
        <div class="card p-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4 class="mb-0">Costos de viaje de Proveedores para verificar</h4>
            </div>


            <div id="tablaPendientesMEP" class="ag-theme-alpine" style="height: 600px; min-width: 1200px;"></div>

        </div>
    </div>

    <!-- Modal comparación estilo macOS minimalista -->
    <div class="modal fade" id="modalCompararCostos" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content mac-modal">
                <div class="modal-header mac-modal-header">
                    <h5 class="modal-title">
                        <i class="fas fa-balance-scale me-2"></i> Comparación de Costos del Viaje
                    </h5>
                    <button type="button" class="mac-close-btn" data-bs-dismiss="modal" aria-label="Cerrar">
                        &times;
                    </button>
                </div>
                <div class="modal-body p-4">
                    <div class="mac-alert mb-4">
                        <i class="fas fa-info-circle me-2 text-primary"></i>
                        Selecciona los campos que deseas observar (si consideras que están incorrectos).
                    </div>

                    <!-- NUEVA SECCIÓN INFORMATIVA -->
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <p><strong>Número de contenedor:</strong> <span id="infoContenedor">-</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Proveedor:</strong> <span id="infoProveedor">-</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha de inicio:</strong> <span id="infoFechaInicio">-</span></p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Fecha solicitud del proveedor:</strong> <span id="infoFechaSolicitud">-</span></p>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-hover table-borderless text-center align-middle">
                            <thead class="mac-thead">
                                <tr>
                                    <th>Campo</th>
                                    <th>Original</th>
                                    <th>Propuesta</th>
                                    <th>Observar</th>
                                </tr>
                            </thead>
                            <tbody id="tablaComparacionCostos">
                                <!-- Se llena con JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer justify-content-end gap-3 p-3">
                    <button type="button" class="btn btn-outline-danger soft-btn" id="btnRechazarCambio">
                        <i class="fas fa-times me-2"></i> Rechazar Cambio
                    </button>
                    <button type="button" class="btn btn-success soft-btn" id="btnAceptarCambio">
                        <i class="fas fa-check me-2"></i> Aceptar Cambio
                    </button>
                </div>
            </div>
        </div>
    </div>



    {{-- Inyectar token CSRF para JS --}}
    <script>
        const csrf = "{{ csrf_token() }}";
    </script>
@endsection


@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script src="{{ asset('js/mep/costosviajes/costosverificar_list.js') }}"></script>
@endpush
