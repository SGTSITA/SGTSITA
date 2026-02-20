@extends('layouts.app')

@section('template_title', 'Bancos')

@section('content')
    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="row mb-4">
            <div class="col-12">
                <div class="card shadow-sm border-0">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">

                            <!-- TÍTULO -->
                            <div>
                                <h2 class="mb-1">Catálogo de Bancos</h2>
                                <small class="text-muted">
                                    Instituciones bancarias y sus cuentas registradas
                                </small>
                            </div>

                            <!-- ACTION BAR -->
                            <div class="d-flex align-items-center gap-2 flex-wrap">

                                <!-- Switch -->
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input" type="checkbox" id="switchConCuentas" checked>
                                    <label class="form-check-label small" for="switchConCuentas">
                                        Solo con cuentas
                                    </label>
                                </div>

                                <!-- Divider visual -->
                                <div class="vr d-none d-md-block"></div>

                                <!-- CTA secundario -->
                                {{-- @can('cuentas-create') --}}
                                <button class="btn btn-success btn-sm d-flex align-items-center gap-1"
                                    data-bs-toggle="modal" data-bs-target="#modalTransferencia">
                                    <i class="fa fa-exchange-alt"></i> Transferencias
                                </button>
                                {{-- @endcan --}}

                                <!-- CTA principal -->
                                @can('bancos-create')
                                    <button class="btn btn-primary d-flex align-items-center gap-1" data-bs-toggle="modal"
                                        data-bs-target="#modalNuevoBanco">
                                        <i class="fa fa-university"></i>
                                        <span>Nuevo banco</span>
                                    </button>
                                @endcan

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>


        {{-- GRID DE BANCOS --}}
        <div class="row">

            @forelse ($catbancos as $banco)
                <div class="col-xl-3 col-lg-4 col-md-6 mb-4 banco-item"
                    data-tiene-cuentas="{{ $banco->tiene_cuentas ? '1' : '0' }}">

                    <div class="card h-100 shadow-sm border-0 banco-card"
                        style="border-top: 5px solid {{ $banco->color ?? '#dee2e6' }}">

                        <div class="card-body text-center d-flex flex-column">

                            {{-- LOGO --}}
                            <div class="mb-3">
                                <img src="{{ asset($banco->logo) }}" alt="{{ $banco->nombre }}" class="img-fluid"
                                    style="max-height:70px; object-fit:contain;"
                                    onerror="this.src='{{ asset('assets/bancos/default.svg') }}'">
                            </div>

                            {{-- NOMBRE --}}
                            <h5 class="fw-bold mb-1">
                                {{ $banco->nombre }}
                            </h5>

                            {{-- CÓDIGO --}}
                            @if ($banco->codigo)
                                <span class="badge bg-light text-dark mb-2">
                                    {{ $banco->codigo }}
                                </span>
                            @endif

                            {{-- RAZÓN SOCIAL --}}
                            @if ($banco->razon_social)
                                <div class="text-muted small mb-3">
                                    {{ $banco->razon_social }}
                                </div>
                            @endif

                            <div class="mt-auto">
                                <div class="d-flex justify-content-center gap-2">

                                    {{-- AGREGAR CUENTA --}}
                                    {{-- @can('cuentas-create') --}}
                                    <a href="{{ route('cuentas.create', ['banco' => $banco->id]) }}"
                                        class="btn btn-outline-success btn-sm" title="Agregar cuenta bancaria">
                                        <i class="fa fa-plus"></i>
                                    </a>
                                    {{-- @endcan --}}

                                    {{-- VER CUENTAS --}}
                                    {{-- @can('bancos-ver') --}}
                                    <a href="{{ route('bancos.cuentas', $banco->id) }}"
                                        class="btn btn-outline-secondary btn-sm" title="Ver cuentas">
                                        <i class="fa fa-building-columns"></i>
                                    </a>
                                    {{-- @endcan --}}

                                    {{-- EDITAR BANCO (SOLO ADMIN) --}}
                                    {{-- @can('bancos-edit') --}}
                                    <a href="#" class="btn btn-outline-primary btn-sm btn-edit-banco"
                                        title="Editar banco" data-id="{{ $banco->id }}"
                                        data-nombre="{{ $banco->nombre }}" data-codigo="{{ $banco->codigo }}"
                                        data-razon="{{ $banco->razon_social }}" data-logo="{{ $banco->logo }}"
                                        data-color="{{ $banco->color }}" data-color-sec="{{ $banco->color_secundario }}"
                                        data-moneda="{{ $banco->moneda }}" data-pais="{{ $banco->pais }}"
                                        data-catalog_key="{{ $banco->catalog_key }}">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                    {{-- @endcan --}}

                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fa fa-university fa-3x text-muted mb-3"></i>
                    <p class="text-muted">No hay bancos registrados</p>
                </div>
            @endforelse

        </div>
    </div>


    <div class="modal fade" id="modalNuevoBanco" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">

                <form action="{{ route('cat-bancos.store') }}" method="POST" id="formBanco" data-mode="create">
                    @csrf

                    <input type="hidden" name="id" id="banco_id">
                    <input type="hidden" name="_method" id="form_method" value="POST">


                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fa fa-university me-2"></i> Registrar banco
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>


                    <div class="modal-body">

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label">Banco sugerido</label>
                                <select id="bank_suggestion" class="form-select" name="catalog_key">
                                    <option value="">Seleccionar banco sugerido</option>

                                    @foreach ($CatBancosDefault as $bank)
                                        <option value="{{ $bank['key'] }}" data-name="{{ $bank['label'] }}"
                                            data-logo="{{ $bank['logo'] }}" data-primary="{{ $bank['primary_color'] }}"
                                            data-secondary="{{ $bank['secondary_color'] }}">
                                            {{ $bank['label'] }}
                                        </option>
                                    @endforeach
                                </select>

                                <small class="text-muted">
                                    Seleccionar un banco llenará automáticamente logo y colores
                                </small>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Nombre del banco *</label>
                                <input type="text" name="nombre" id="bank_name" class="form-control" required>
                            </div>


                            <div class="col-md-3">
                                <label class="form-label">Código</label>
                                <input type="text" name="codigo" class="form-control" placeholder="BBVA">
                            </div>



                            <div class="col-md-12">
                                <label class="form-label">Razón social</label>
                                <input type="text" name="razon_social" class="form-control">
                            </div>


                            <div class="col-md-12">
                                <label class="form-label">Logo (URL o asset)</label>
                                <input type="text" name="logo" class="form-control" id="bank_logo"
                                    placeholder="/assets/bancos/bbva.svg">
                            </div>

                            <div class="col-md-12 text-center">
                                <img id="bank_logo_preview" src="{{ asset('assets/bancos/default.svg') }}"
                                    style="max-height:60px; object-fit:contain;"
                                    onerror="this.src='{{ asset('assets/bancos/default.svg') }}'">
                            </div>


                            <div class="col-md-6">
                                <input type="color" name="color" id="primary_color"
                                    class="form-control form-control-color">
                            </div>

                            <div class="col-md-6">
                                <input type="color" name="color_secundario" id="secondary_color"
                                    class="form-control form-control-color">
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">Moneda</label>
                                <select name="moneda" class="form-select">
                                    <option value="MXN">MXN - Peso Mexicano</option>
                                    <option value="USD">USD - Dólar</option>
                                    <option value="EUR">EUR - Euro</option>
                                </select>
                            </div>


                            <div class="col-md-6">
                                <label class="form-label">País</label>
                                <input type="text" name="pais" class="form-control" value="México">
                            </div>

                        </div>
                    </div>


                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">
                            Cancelar
                        </button>

                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save me-1"></i> Guardar banco
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>

    @include('bancos.modal-transferencia')

@endsection
@push('scripts')
    <script>
        document.getElementById('bank_suggestion').addEventListener('change', function() {

            const option = this.options[this.selectedIndex];
            if (!option.value) return;

            document.getElementById('bank_name').value = option.dataset.name;
            document.getElementById('bank_logo').value = option.dataset.logo;
            document.getElementById('primary_color').value = option.dataset.primary;
            document.getElementById('secondary_color').value = option.dataset.secondary;

            document.getElementById('bank_logo_preview').src = option.dataset.logo;
        });

        document.getElementById('formBanco').addEventListener('submit', async function(e) {
            e.preventDefault();

            const form = e.target;
            const formData = new FormData(form);

            const isEdit = form.dataset.mode === 'edit';
            const bancoId = document.getElementById('banco_id').value;

            const url = isEdit ?
                `/bancos/cat-bancos/edit/${bancoId}` :
                "{{ route('cat-bancos.store') }}";

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

                if (!response.ok) {
                    throw data;
                }


                Swal.fire({
                    icon: 'success',
                    title: form.dataset.mode === 'edit' ?
                        'Banco actualizado' : 'Banco registrado',
                    text: data.message ?? 'El banco se guardó correctamente',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {

                    window.location.reload();
                });


                const modalEl = document.getElementById('modalNuevoBanco'); // ID del modal
                const modal = bootstrap.Modal.getInstance(modalEl);
                modal.hide();


                form.reset();




            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: error.message ?? 'Ocurrió un error al guardar'
                });
            }
        });


        document.querySelectorAll('.btn-edit-banco').forEach(btn => {
            btn.addEventListener('click', function() {


                document.querySelector('#modalNuevoBanco .modal-title').innerHTML =
                    '<i class="fa fa-pen me-2"></i> Editar banco';

                const form = document.getElementById('formBanco');
                form.dataset.mode = 'edit';

                document.getElementById('form_method').value = 'PUT';
                document.getElementById('banco_id').value = this.dataset.id;


                document.getElementById('bank_name').value = this.dataset.nombre ?? '';
                document.querySelector('[name="codigo"]').value = this.dataset.codigo ?? '';
                document.querySelector('[name="razon_social"]').value = this.dataset.razon ?? '';
                document.getElementById('bank_logo').value = this.dataset.logo ?? '';
                document.getElementById('primary_color').value = this.dataset.color ?? '#000000';
                document.getElementById('secondary_color').value = this.dataset.colorSec ?? '#000000';
                document.querySelector('[name="moneda"]').value = this.dataset.moneda ?? 'MXN';
                document.querySelector('[name="pais"]').value = this.dataset.pais ?? 'México';
                document.querySelector('[name="catalog_key"]').value = this.dataset.catalog_key ?? '';

                document.getElementById('bank_logo_preview').src =
                    this.dataset.logo || "{{ asset('assets/bancos/default.svg') }}";


                new bootstrap.Modal(document.getElementById('modalNuevoBanco')).show();
            });
        });



        function aplicarFiltroBancos() {
            const soloConCuentas = document.getElementById('switchConCuentas').checked;

            document.querySelectorAll('.banco-item').forEach(banco => {
                const tiene = banco.dataset.tieneCuentas === '1';
                banco.style.display = (soloConCuentas && !tiene) ? 'none' : '';
            });
        }

        document.getElementById('formTransferencia').addEventListener('submit', function(e) {
            e.preventDefault();

            const form = this;
            const formData = new FormData(form);

            Swal.fire({
                title: 'Aplicando transferencia...',
                text: 'Por favor espere',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            fetch("{{ route('bancos.cuentas.transferencia') }}", {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(res => res.json())
                .then(data => {

                    if (!data.success) {
                        Swal.fire('Error', data.message, 'error');
                        return;
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Transferencia exitosa',
                        text: data.message,
                        timer: 1800,
                        showConfirmButton: false
                    });


                    const modal = bootstrap.Modal.getInstance(
                        document.getElementById('modalTransferencia')
                    );
                    modal.hide();


                    form.reset();

                    location.reload();

                })
                .catch(() => {
                    Swal.fire('Error', 'No se pudo completar la transferencia', 'error');
                });
        });



        document.getElementById('switchConCuentas')
            .addEventListener('change', aplicarFiltroBancos);


        document.addEventListener('DOMContentLoaded', aplicarFiltroBancos);
    </script>
@endpush
