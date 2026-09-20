@extends('layouts.app')

@section('template_title')
    Reporte: Viajes por Cobrar
@endsection

@section('content')
    <div class="container-fluid py-4">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h4 class="mb-0">VXC - Viajes por cobrar</h4>
                <div>
                    <button class="btn btn-success" id="exportExcel">Exportar Excel</button>
                    <button class="btn btn-danger" id="exportPDF">Exportar PDF</button>
                </div>
            </div>

            <div class="card-body">
                {{-- 🔹Resumen financiero --}}
                <div class="row mb-4">
                    <div class="col-lg-4 col-6 text-center">
                        <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                            <h6 class="text-primary mb-0">Total Generado</h6>
                            <h4 class="font-weight-bolder">
                                <span class="small text-dark">${{ number_format($totalGenerado, 2, '.', ',') }}</span>
                            </h4>
                        </div>
                    </div>

                    <div class="col-lg-4 col-6 text-center">
                        <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                            <h6 class="text-primary mb-0">Retenido</h6>
                            <h4 class="font-weight-bolder">
                                <span class="small text-warning">${{ number_format($retenido, 2, '.', ',') }}</span>
                            </h4>
                        </div>
                    </div>

                    <div class="col-lg-4 col-6 text-center">
                        <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                            <h6 class="text-primary mb-0">Pago Neto</h6>
                            <h4 class="font-weight-bolder">
                                <span class="small text-success">${{ number_format($pagoNeto, 2, '.', ',') }}</span>
                            </h4>
                        </div>
                    </div>
                </div>

                {{-- 🔹AG Grid --}}
                <div id="vxcGrid" class="ag-theme-alpine" style="height: 600px; width: 100%"></div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <!-- AG Grid -->
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>

    <!-- Variables globales -->
    <script>
        window.cotizacionesVXC = @json($cotizaciones);
        window.csrfToken = '{{ csrf_token() }}';
    </script>

    <!-- Tu JS personalizado -->
    <script src="{{ asset('js/mep/vxc/vxc_list.js') }}"></script>
    <script>
        window._totalesExport = {
            totalGenerado: {{ $totalGenerado }},
            retenido: {{ $retenido }},
            pagoNeto: {{ $pagoNeto }},
        };
    </script>
@endpush
