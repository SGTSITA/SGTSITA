@extends('layouts.app')

@section('template_title', 'Intervalo Rastreo')

@section('content')
    <style>
        #contenedoreseditar {
            font-size: 0.85rem;
        }
        #contenedoreseditar th,
        #contenedoreseditar td {
            padding: 0.3rem 0.5rem;
            vertical-align: middle;
        }
        #contenedoreseditar thead {
            background-color: #f0f0f0;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
        }
    </style>

    <div class="container-fluid py-4 px-3 bg-gray-100 min-h-screen">
        <div class="row justify-content-center">
            <div class="col-sm-12">
                <div class="card shadow-lg border-0 bg-white rounded-4 p-4">
                    <div class="mb-4 d-flex align-items-center justify-content-between"></div>

                    <div class="container mt-5">
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                @if (session('success'))
                                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                                        {{ session('success') }}
                                        <button
                                            type="button"
                                            class="btn-close"
                                            data-bs-dismiss="alert"
                                            aria-label="Cerrar"
                                        ></button>
                                    </div>
                                @endif

                                <div class="text-center mb-4">
                                    <h3 class="fw-bold text-primary">Configurar Intervalo de Rastreo Automático</h3>
                                </div>
                                <div class="card shadow-sm border-0">
                                    <div class="card-body">
                                        <form action="{{ route('scheduler.update') }}" method="POST">
                                            @csrf
                                            @method('PUT')

                                            <div class="mb-3">
                                                <label for="interval" class="form-label">Intervalo</label>
                                                <select name="interval" id="interval" class="form-select">
                                                    <option
                                                        value="everyMinute"
                                                        {{ $intervals->interval == 'everyMinute' ? 'selected' : '' }}
                                                    >
                                                        Cada minuto
                                                    </option>
                                                    <option
                                                        value="everyFiveMinutes"
                                                        {{ $intervals->interval == 'everyFiveMinutes' ? 'selected' : '' }}
                                                    >
                                                        Cada 5 minutos
                                                    </option>
                                                    <option
                                                        value="hourly"
                                                        {{ $intervals->interval == 'hourly' ? 'selected' : '' }}
                                                    >
                                                        Cada hora
                                                    </option>
                                                    <option
                                                        value="daily"
                                                        {{ $intervals->interval == 'daily' ? 'selected' : '' }}
                                                    >
                                                        Diario
                                                    </option>
                                                    <option
                                                        value="weekly"
                                                        {{ $intervals->interval == 'weekly' ? 'selected' : '' }}
                                                    >
                                                        Semanal
                                                    </option>
                                                </select>
                                            </div>

                                            <button type="submit" class="btn btn-primary w-100">Guardar cambios</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card mb-4">
                        <div class="card-header bg-info text-white">
                            <strong>Bitácora de Rastreo GPS</strong>
                        </div>
                        <div class="card-body" style="max-height: 300px; overflow-y: auto; font-family: monospace">
                            <ul class="list-group list-group-flush"></ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <!-- AG Grid -->
    <script src="https://unpkg.com/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

    <!-- JS de Select2 -->

    <!-- Nuestro JavaScript unificado -->
    <script src="{{ asset('js/sgt/coordenadas/coordenadasconboys.js') }}?v={{ filemtime(public_path('js/sgt/coordenadas/coordenadasconboys.js')) }}"></script>
@endpush
