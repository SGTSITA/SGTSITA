@extends('scb.layouts')

@section('template_title', 'Cuentas bancarias')
@section('page_title', 'Cuentas bancarias')
@section('page_subtitle', 'Catálogo de cuentas del módulo bancario')

@section('content')
    <div class="scb-card">
        <div class="scb-card-header d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-0">Cuentas registradas</h5>
                <small class="text-muted">Administra las cuentas bancarias disponibles.</small>
            </div>

            <button type="button" class="btn scb-btn-primary" id="btnNuevaCuenta">
                <i class="fas fa-plus me-1"></i>
                Nueva cuenta
            </button>
        </div>

        <div class="scb-card-body">
            <div class="table-responsive">
                <table class="table align-items-center mb-0" id="tablaCuentas">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Banco</th>
                            <th>Beneficiario</th>
                            <th>Cuenta</th>
                            <th>CLABE</th>
                            <th>Moneda</th>
                            <th class="text-end">Saldo inicial</th>
                            <th>Estatus</th>
                            <th class="text-end">Acciones</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse ($cuentas as $cuenta)
                            <tr id="cuenta-row-{{ $cuenta->id }}">
                                <td>{{ $cuenta->id }}</td>

                                <td class="cuenta-banco fw-bold">
                                    {{ $cuenta->banco?->nombre ?? 'S/N' }}
                                </td>

                                <td class="cuenta-beneficiario">
                                    {{ $cuenta->beneficiario ?? 'S/N' }}
                                </td>

                                <td class="cuenta-numero">
                                    {{ $cuenta->numero_cuenta ?? 'S/N' }}
                                </td>

                                <td class="cuenta-clabe">
                                    {{ $cuenta->clabe ?? 'S/N' }}
                                </td>

                                <td class="cuenta-moneda">
                                    {{ $cuenta->moneda }}
                                </td>

                                <td class="cuenta-saldo text-end">
                                    ${{ number_format($cuenta->saldo_inicial, 2) }}
                                </td>

                                <td class="cuenta-activo">
                                    @if ($cuenta->activo)
                                        <span class="badge bg-success">Activo</span>
                                    @else
                                        <span class="badge bg-secondary">Inactivo</span>
                                    @endif
                                </td>

                                <td class="text-end">
                                    <button type="button" class="btn btn-sm btn-outline-primary btnEditarCuenta"
                                        data-id="{{ $cuenta->id }}" data-banco-id="{{ $cuenta->banco_id }}"
                                        data-beneficiario="{{ $cuenta->beneficiario }}"
                                        data-numero-cuenta="{{ $cuenta->numero_cuenta }}"
                                        data-clabe="{{ $cuenta->clabe }}" data-moneda="{{ $cuenta->moneda }}"
                                        data-saldo-inicial="{{ $cuenta->saldo_inicial }}"
                                        data-activo="{{ $cuenta->activo ? 1 : 0 }}">
                                        <i class="fas fa-edit"></i>
                                    </button>

                                    <button type="button" class="btn btn-sm btn-outline-danger btnEliminarCuenta"
                                        data-id="{{ $cuenta->id }}" data-activo="{{ $cuenta->activo ? 1 : 0 }}">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr id="cuentas-empty-row">
                                <td colspan="9" class="text-center text-muted py-4">
                                    No hay cuentas registradas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Modal Crear / Editar --}}
    <div class="modal fade" id="modalCuenta" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content rounded-4">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalCuentaTitulo">Nueva cuenta</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <form id="formCuenta">
                    @csrf

                    <input type="hidden" id="cuenta_id" name="cuenta_id">

                    <div class="modal-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label">Banco</label>
                                <select name="banco_id" id="banco_id" class="form-select" required>
                                    <option value="">Seleccione banco</option>
                                    @foreach ($bancos as $banco)
                                        <option value="{{ $banco->id }}">
                                            {{ $banco->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="invalid-feedback" id="error_banco_id"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Beneficiario</label>
                                <input type="text" name="beneficiario" id="beneficiario" class="form-control"
                                    placeholder="Nombre del beneficiario">
                                <div class="invalid-feedback" id="error_beneficiario"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">Número de cuenta</label>
                                <input type="text" name="numero_cuenta" id="numero_cuenta" class="form-control"
                                    placeholder="Número de cuenta">
                                <div class="invalid-feedback" id="error_numero_cuenta"></div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label">CLABE</label>
                                <input type="text" name="clabe" id="clabe" class="form-control"
                                    placeholder="CLABE interbancaria">
                                <div class="invalid-feedback" id="error_clabe"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Moneda</label>
                                <select name="moneda" id="moneda" class="form-select">
                                    <option value="MXN">MXN</option>
                                    <option value="USD">USD</option>
                                </select>
                                <div class="invalid-feedback" id="error_moneda"></div>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label">Saldo inicial</label>
                                <input type="number" name="saldo_inicial" id="saldo_inicial" class="form-control"
                                    step="0.01" min="0" value="0">
                                <div class="invalid-feedback" id="error_saldo_inicial"></div>
                            </div>

                            <div class="col-md-4 d-flex align-items-end">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" name="activo" id="activo"
                                        value="1" checked>
                                    <label class="form-check-label" for="activo">
                                        Activo
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="submit" class="btn scb-btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Guardar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script src="{{ asset('js/scb/cuentas.js') }}?v={{ filemtime(public_path('js/scb/cuentas.js')) }}"></script>
@endpush
