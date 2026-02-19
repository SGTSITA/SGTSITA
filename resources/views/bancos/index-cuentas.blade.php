@extends('layouts.app')

@section('template_title', 'Cuentas bancarias')

@section('content')
    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                            {{-- TÍTULO --}}
                            <div>
                                <h2 class="mb-1">
                                    Cuentas del banco
                                    <span class="text-primary">{{ $catBanco->nombre }}</span>
                                </h2>
                                <small class="text-muted">
                                    Administración de cuentas bancarias registradas
                                </small>
                            </div>

                            {{-- ACTION BAR --}}
                            <div class="d-flex align-items-center gap-2 flex-wrap">

                                <input type="text" id="searchCuenta" class="form-control w-auto"
                                    placeholder="Buscar cuenta..." onkeyup="filtrarCuentas()">

                                <a href="{{ route('index.bancos2') }}"
                                    class="btn btn-light d-flex align-items-center gap-1">
                                    <i class="fa fa-arrow-left"></i>
                                    <span>Bancos</span>
                                </a>

                                {{-- @can('cuentas-create') --}}
                                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalCuenta"
                                    onclick="openCreateCuenta()">
                                    <i class="fa fa-credit-card me-1"></i> Nueva cuenta
                                </button>
                                {{-- @endcan --}}

                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- CONTEXTO DEL BANCO --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card border-0 shadow-sm">
                    <div class="card-body d-flex align-items-center gap-3">

                        <img src="{{ asset($catBanco->logo) }}" alt="{{ $catBanco->nombre }}"
                            style="max-height:50px; object-fit:contain"
                            onerror="this.src='{{ asset('assets/bancos/default.svg') }}'">

                        <div>
                            <h5 class="mb-0">{{ $catBanco->nombre }}</h5>
                            <small class="text-muted">
                                {{ $catBanco->razon_social ?? '—' }}
                            </small>
                        </div>

                        <span class="badge bg-light text-dark ms-auto">
                            {{ $cuentas->count() }} cuentas registradas
                        </span>

                    </div>
                </div>
            </div>
        </div>

        {{-- GRID DE CUENTAS --}}
        <div class="row">

            @forelse ($cuentas as $cuenta)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4 cuenta-item"
                    data-search="
        {{ strtolower($catBanco->nombre) }}
        {{ strtolower($cuenta->nombre_beneficiario) }}
        {{ strtolower($cuenta->cuenta_bancaria) }}
        {{ strtolower($cuenta->clabe ?? '') }}
        {{ strtolower($cuenta->moneda) }}
     ">
                    <div class="card h-100 shadow-sm border-0 cuenta-card">

                        {{-- HEADER --}}
                        <div class="card-header bg-white border-0 pb-0">
                            <div class="d-flex align-items-center gap-2">
                                <img src="{{ asset($catBanco->logo) }}" alt="{{ $catBanco->nombre }}"
                                    class="rounded-circle border" style="width:42px;height:42px;object-fit:contain;">

                                <div class="flex-grow-1">
                                    <div class="fw-bold">{{ $catBanco->nombre }}</div>
                                    <small class="text-muted text-uppercase">
                                        {{ $cuenta->tipo }}
                                    </small>
                                </div>
                                {{-- <span class="badge bg-light text-success border ms-auto">
                                    ${{ number_format($cuenta->saldo_inicial, 2) }}
                                </span> --}}

                                @if ($cuenta->principal)
                                    <span class="badge bg-success">Principal</span>
                                @endif
                            </div>
                        </div>

                        {{-- BODY --}}
                        <div class="card-body pt-2">

                            {{-- BENEFICIARIO --}}
                            <div class="fw-semibold mb-2 text-dark">
                                {{ $cuenta->nombre_beneficiario }}
                            </div>

                            {{-- CUENTA --}}
                            <div class="d-flex align-items-center gap-2 text-muted small mb-1">
                                <i class="fa fa-credit-card"></i>
                                <span>{{ $cuenta->cuenta_bancaria }}</span>
                            </div>

                            {{-- CLABE --}}
                            @if ($cuenta->clabe)
                                <div class="d-flex align-items-center gap-2 text-muted small">
                                    <i class="fa fa-hashtag"></i>
                                    <span>{{ $cuenta->clabe }}</span>
                                </div>
                            @endif
                        </div>

                        {{-- FOOTER --}}
                        <div class="card-footer bg-white border-0 pt-0">
                            <div class="d-flex justify-content-between align-items-center">

                                <span class="badge bg-light text-dark">
                                    {{ $cuenta->moneda }}
                                </span>

                                <a href="{{ route('bancoscuentas.movimientos', $cuenta->id) }}"
                                    class="badge bg-light text-success border text-decoration-none saldo-link"
                                    title="Ver movimientos">
                                    ${{ number_format($cuenta->saldo_actual, 2) }}
                                </a>

                                <div class="d-flex gap-1">
                                    <button class="btn btn-outline-primary btn-sm" onclick="openEditCuenta(this)"
                                        data-id="{{ $cuenta->id }}" data-tipo="{{ $cuenta->tipo }}"
                                        data-moneda="{{ $cuenta->moneda }}" data-numero="{{ $cuenta->cuenta_bancaria }}"
                                        data-clabe="{{ $cuenta->clabe }}"
                                        data-beneficiario="{{ $cuenta->nombre_beneficiario }}"
                                        data-saldo_inicial ="{{ $cuenta->inicial_saldo }}"
                                        data-principal="{{ $cuenta->principal }}">
                                        <i class="fa fa-pen"></i>
                                    </button>

                                    @can('cuentas-delete')
                                        <button class="btn btn-outline-danger btn-sm"
                                            onclick="confirmDelete({{ $cuenta->id }})">
                                            <i class="fa fa-trash"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            @empty
                <div class="col-12 text-center py-5">
                    <i class="fa fa-credit-card fa-3x text-muted mb-3"></i>
                    <p class="text-muted">
                        Este banco aún no tiene cuentas registradas
                    </p>

                    @can('cuentas-create')
                        <a href="{{ route('cuentas.create', ['banco' => $catBanco->id]) }}" class="btn btn-primary">
                            <i class="fa fa-plus me-1"></i>
                            Registrar primera cuenta
                        </a>
                    @endcan
                </div>
            @endforelse

        </div>
    </div>

    <div class="modal fade" id="modalCuenta" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <form id="formCuenta" data-mode="create">
                    @csrf

                    <!-- Banco fijo -->
                    <input type="hidden" name="banco_id" value="{{ $catBanco->id }}">
                    <input type="hidden" name="id" id="cuenta_id">

                    {{-- HEADER --}}
                    <div class="modal-header">
                        <h5 class="modal-title" id="cuentaModalTitle">
                            <i class="fa fa-credit-card me-2"></i>
                            Nueva cuenta bancaria
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>

                    {{-- BODY --}}
                    <div class="modal-body">
                        <div class="row g-3">

                            <!-- Tipo -->
                            <div class="col-md-6">
                                <label class="form-label">Tipo de cuenta *</label>
                                <select name="tipo_cuenta" id="tipo_cuenta" class="form-select" required>
                                    <option value="">Seleccionar</option>
                                    <option value="Oficial">Oficial</option>
                                    <option value="No Oficial">No Oficial</option>
                                    <option value="Otros">Otros</option>

                                </select>
                            </div>

                            <!-- Moneda -->
                            <div class="col-md-6">
                                <label class="form-label">Moneda *</label>
                                <select name="moneda" id="moneda" class="form-select" required>
                                    <option {{ $catBanco->moneda == 'MXN' ? 'selected' : '' }} value="MXN">MXN</option>
                                    <option {{ $catBanco->moneda == 'USD' ? 'selected' : '' }}value="USD">USD</option>
                                    <option {{ $catBanco->moneda == 'EUR' ? 'selected' : '' }}value="EUR">EUR</option>
                                </select>
                            </div>

                            <!-- Número de cuenta -->
                            <div class="col-md-6">
                                <label class="form-label">Número de cuenta *</label>
                                <input type="text" name="numero_cuenta" id="numero_cuenta" class="form-control"
                                    required>
                            </div>

                            <!-- CLABE -->
                            <div class="col-md-6">
                                <label class="form-label">CLABE</label>
                                <input type="text" name="clabe" id="clabe" class="form-control">
                            </div>

                            <!-- Beneficiario -->
                            <div class="col-md-8">
                                <label class="form-label">Beneficiario *</label>
                                <input type="text" name="beneficiario" id="beneficiario" class="form-control"
                                    required>
                            </div>

                            <!-- Saldo inicial -->
                            <div class="col-md-4">
                                <label class="form-label">Saldo inicial *</label>
                                <input type="number" step="0.01" min="0" name="saldo_inicial"
                                    id="saldo_inicial" class="form-control" required>
                            </div>

                            <!-- Cuenta principal -->
                            <div class="col-md-12">
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" name="principal" id="principal"
                                        value="1">
                                    <label class="form-check-label" for="principal">
                                        Cuenta principal
                                    </label>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- FOOTER --}}
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> Guardar
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>


@endsection

@push('scripts')
    <script>
        const modalCuenta = new bootstrap.Modal(document.getElementById('modalCuenta'));
        const formCuenta = document.getElementById('formCuenta');

        function openCreateCuenta() {
            formCuenta.dataset.mode = 'create';
            formCuenta.reset();
            document.getElementById('cuenta_id').value = '';

            document.getElementById('cuentaModalTitle').innerHTML =
                '<i class="fa fa-credit-card me-2"></i> Nueva cuenta bancaria';

            modalCuenta.show();
        }

        function openEditCuenta(btn) {
            formCuenta.dataset.mode = 'edit';

            document.getElementById('cuentaModalTitle').innerHTML =
                '<i class="fa fa-pen me-2"></i> Editar cuenta bancaria';

            document.getElementById('cuenta_id').value = btn.dataset.id;
            formCuenta.tipo_cuenta.value = btn.dataset.tipo;
            formCuenta.moneda.value = btn.dataset.moneda;
            formCuenta.numero_cuenta.value = btn.dataset.numero;
            formCuenta.beneficiario.value = btn.dataset.beneficiario;
            formCuenta.saldo_inicial.value = btn.dataset.saldo_inicial
            formCuenta.clabe.value = btn.dataset.clabe ?? '';
            formCuenta.principal.checked = btn.dataset.principal == 1;

            modalCuenta.show();
        }

        function filtrarCuentas() {
            let input = document.getElementById('searchCuenta').value.toLowerCase();
            let cuentas = document.querySelectorAll('.cuenta-item');

            cuentas.forEach(function(cuenta) {
                let texto = cuenta.getAttribute('data-search');

                if (texto.includes(input)) {
                    cuenta.style.display = '';
                } else {
                    cuenta.style.display = 'none';
                }
            });
        }

        formCuenta.addEventListener('submit', async function(e) {
            e.preventDefault();

            const isEdit = formCuenta.dataset.mode === 'edit';
            const cuentaId = document.getElementById('cuenta_id').value;

            const url = isEdit ?
                `/cat-bancos/cuentas/update/${cuentaId}` :
                "{{ route('bancoscuentas.create') }}";

            const formData = new FormData(formCuenta);

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value,
                        'Accept': 'application/json'
                    },
                    body: formData
                });

                const data = await response.json();

                if (!response.ok) throw data;

                Swal.fire({
                    icon: 'success',
                    title: isEdit ? 'Cuenta actualizada' : 'Cuenta creada',
                    timer: 1400,
                    showConfirmButton: false
                }).then(() => window.location.reload());

            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message ?? 'Error al guardar la cuenta'
                });
            }
        });
    </script>
@endpush
