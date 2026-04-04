@extends('layouts.app')

@section('template_title')
    Detalle de Préstamos - {{ $operador->nombre }}
@endsection

@section('content')
    <div class="container-fluid mt-4">

        {{-- 🔹 Resumen superior --}}
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Total Préstamos</h6>
                    <h4>${{ number_format($totalPrestamos, 2) }}</h4>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Total Adelantos</h6>
                    <h4>${{ number_format($totalAdelantos, 2) }}</h4>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3">
                    <h6>Total Deuda</h6>
                    <h4>${{ number_format($totalDeuda, 2) }}</h4>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card shadow-sm text-center p-3 bg-light">
                    <h6>Saldo Final</h6>
                    <h4 class="text-danger fw-bold">
                        ${{ number_format($saldoFinal, 2) }}
                    </h4>
                </div>
            </div>
        </div>

        {{-- 🔹 Tabla detallada --}}
        <div class="card shadow-sm">
            <div class="card-header bg-light d-flex justify-content-between">
                <h5 class="mb-0">
                    Movimientos de {{ $operador->nombre }}
                </h5>

                <a href="{{ route('operadores.prestamo') }}" class="btn btn-secondary btn-sm">
                    ← Volver
                </a>
            </div>

            <div class="card-body table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light text-center">
                        <tr>
                            <th>Tipo</th>
                            <th>Banco</th>
                            <th>Cantidad</th>
                            <th>Total Abonado</th>
                            <th>Saldo</th>
                            <th>Fecha</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($prestamos as $p)
                            @php
                                $abonado = $p->pagoprestamos->sum('monto_pago');
                                $saldo = $p->cantidad - $abonado;
                            @endphp

                            <tr class="text-center">
                                <td>
                                    @if ($p->tipo == \App\Models\Prestamo::TIPO_PRESTAMO)
                                        <span class="badge bg-primary">Préstamo</span>
                                    @else
                                        <span class="badge bg-warning text-dark">Adelanto</span>
                                    @endif
                                </td>

                                <td>{{ $p->banco->nombre_banco ?? 'N/A' }}</td>

                                <td class="text-end">
                                    ${{ number_format($p->cantidad, 2) }}
                                </td>

                                <td class="text-end">
                                    ${{ number_format($abonado, 2) }}
                                </td>

                                <td class="text-end fw-bold text-danger">
                                    ${{ number_format($saldo, 2) }}
                                </td>

                                <td>
                                    {{ $p->created_at->format('d/m/Y') }}
                                </td>

                                <td>
                                    <button class="btn btn-sm btn-success btn-abonar" data-id="{{ $p->id }}"
                                        onclick="abrirModalAbono({{ $p->id }})">
                                        💰 Abonar
                                    </button>

                                    <button class="btn btn-sm btn-info btn-ver-historial" data-id="{{ $p->id }}"
                                        onclick="abrirDetallePrestamo({{ $p->id }})">
                                        📄 Historial
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>
    @include('operadores.prestamos.modal-abono-prestamo')
    @include('operadores.prestamos.modal-detalle-abonos')
@endsection
@push('custom-javascript')
    <script src="https://cdn.jsdelivr.net/npm/ag-grid-community/dist/ag-grid-community.min.js"></script>
    <script
        src="{{ asset('js/sgt/operadores/prestamos-show.js') }}?v={{ filemtime(public_path('js/sgt/operadores/prestamos-show.js')) }}">
    </script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.28/jspdf.plugin.autotable.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        flatpickr(".dateInput", {
            dateFormat: "d/m/Y",
            locale: "es"
        });
    </script>
@endpush
