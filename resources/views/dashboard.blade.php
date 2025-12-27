@extends('layouts.app')

@section('breadcrumb')
    <div class="row">
        @can('clientes-list')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('clients.index') }}">
                                <img src="{{ asset('img/icon/empleados.webp') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('clients.index') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>I - Clients</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto">
                            <a type="button" class="" data-bs-toggle="modal" data-bs-target="#exampleModal">
                                <img src="{{ asset('img/icon/anadir.webp') }}" alt="" width="35px" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('proovedores-list')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('index.proveedores') }}">
                                <img src="{{ asset('img/icon/edificios_ciudad.webp') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('index.proveedores') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>II - Proveedores</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto">
                            <a type="button" class="" data-bs-toggle="modal" data-bs-target="#proveedores">
                                <img src="{{ asset('img/icon/anadir.webp') }}" alt="" width="35px" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('equipos-list')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('index.equipos') }}">
                                <img src="{{ asset('img/icon/referencia.webp') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('index.equipos') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>III - Equipos</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto">
                            <a type="button" class="" data-bs-toggle="modal" data-bs-target="#equipoModal">
                                <img src="{{ asset('img/icon/anadir.webp') }}" alt="" width="35px" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('operadores-list')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('index.operadores') }}">
                                <img src="{{ asset('img/icon/camion.png') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('index.operadores') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>IV - Operadores</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto">
                            <a type="button" class="" data-bs-toggle="modal" data-bs-target="#operadoresModal">
                                <img src="{{ asset('img/icon/anadir.webp') }}" alt="" width="35px" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('cotizaciones-list')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('index.cotizaciones') }}">
                                <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('index.cotizaciones') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>V - Cotizaciones</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto">
                            <a href="{{ route('create.cotizaciones') }}">
                                <img src="{{ asset('img/icon/anadir.webp') }}" alt="" width="35px" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('planeacion-list')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('index.planeaciones') }}">
                                <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('index.planeaciones') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>VI Planeación</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto"></div>
                    </div>
                </div>
            </div>
        @endcan

        @can('bancos-list')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('index.bancos') }}">
                                <img src="{{ asset('img/icon/banco.png') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('index.bancos') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>VII Bancos</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto">
                            <a type="button" class="" data-bs-toggle="modal" data-bs-target="#bancoModal">
                                <img src="{{ asset('img/icon/anadir.webp') }}" alt="" width="35px" />
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endcan

        @can('cuentas-cobrar')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('index.cobrar') }}">
                                <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('index.cobrar') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>VIII Cuentas por cobrar</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto"></div>
                    </div>
                </div>
            </div>
        @endcan

        @can('cuentas-pagar')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('index.pagar') }}">
                                <img src="{{ asset('img/icon/gastos.png.webp') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('index.pagar') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>IX Cuentas por pagar</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto"></div>
                    </div>
                </div>
            </div>
        @endcan

        @can('gastos-generales')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('index.gastos_generales') }}">
                                <img src="{{ asset('img/icon/billetera.png') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('index.gastos_generales') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>X Gastos Generales</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto"></div>
                    </div>
                </div>
            </div>
        @endcan

        @can('liquidaciones')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('index.liquidacion') }}">
                                <img src="{{ asset('img/icon/pago-en-efectivo.png') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('index.liquidacion') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>XII Liquidaciones</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto"></div>
                    </div>
                </div>
            </div>
        @endcan

        @can('Coordenadas SGT')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto">
                            <a href="{{ route('ver.coordenadamapa') }}">
                                <img src="{{ asset('img/icon/mapa-de-la-ciudad.webp') }}" alt="" width="35px" />
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="{{ route('ver.coordenadamapa') }}">
                                <p style="margin: 0">Consulta</p>
                                <h5>XIII Coordenadas</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto"></div>
                    </div>
                </div>
            </div>
        @endcan

        @can('ver-costos-pendientes')
            <div class="col-4">
                <div class="card p-3 mb-4">
                    <div class="row">
                        <div class="col-2 my-auto text-center">
                            <a href="#" id="notificacionesMEP">
                                <i class="fas fa-bell fs-3 position-relative">
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                        id="badgeMEP"
                                        style="display: none; font-size: 0.7rem"
                                    >
                                        0
                                    </span>
                                </i>
                            </a>
                        </div>

                        <div class="col-8">
                            <a href="#" id="notificacionesMEP-text" class="text-decoration-none text-dark">
                                <p class="mb-1">Revisión</p>
                                <h5 class="mb-0">Costos MEP Pendientes</h5>
                            </a>
                        </div>

                        <div class="col-2 my-auto"></div>
                    </div>
                </div>
            </div>
        @endcan

        @can('catalogo')
            <li class="nav-item">
                <a
                    class="nav-link {{ Request::is('catalogo*') ? 'active' : '' }}"
                    href="{{ route('index.catalogo') }}"
                    target=""
                >
                    <div
                        class="icon icon-shape icon-sm text-center me-2 d-flex align-items-center justify-content-center"
                    >
                        <img src="{{ asset('img/icon/catalogo.webp') }}" alt="" width="20px" />
                    </div>
                    <span class="nav-link-text ms-1">
                        <b>XIV</b>
                        Catálogo
                    </span>
                </a>
            </li>
        @endcan
    </div>
@endsection

@section('content')
    <!--modal cambiar de empresa menu  -->
    <div
        class="modal fade"
        id="modalCambiarEmpresa"
        tabindex="-1"
        aria-labelledby="modalCambiarEmpresaLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <form method="POST" action="{{ route('usuario.cambiarEmpresa') }}">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="modalCambiarEmpresaLabel">Seleccionar Empresa</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                    </div>
                    <div class="modal-body">
                        <label for="empresa">Empresa:</label>
                        <select name="empresa_id" id="empresa_id" class="form-control" required>
                            @foreach ($empresasAsignadas as $empresa)
                                <option value="{{ $empresa->id }}" {{ $empresa->id == $empActual ? 'selected' : '' }}>
                                    {{ $empresa->id . '-' . $empresa->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Cambiar</button>
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        let pendientesCount = 0;

        document.addEventListener('DOMContentLoaded', function () {
            fetch('/mep/pendientes/count')
                .then((res) => res.json())
                .then((data) => {
                    pendientesCount = data.total || 0;

                    if (pendientesCount > 0) {
                        const badge = document.getElementById('badgeMEP');
                        badge.textContent = pendientesCount;
                        badge.style.display = 'inline-block';
                    }
                });

            const icono = document.getElementById('notificacionesMEP');
            const texto = document.getElementById('notificacionesMEP-text');

            [icono, texto].forEach((el) => {
                el.addEventListener('click', function (e) {
                    e.preventDefault();

                    if (pendientesCount > 0) {
                        Swal.fire({
                            icon: 'info',
                            title: 'Pendientes por verificar',
                            text: `Tienes ${pendientesCount} viajes pendientes por verificar.`,
                            confirmButtonText: 'Ir a revisar',
                            showCancelButton: true,
                            cancelButtonText: 'Más tarde',
                        }).then((result) => {
                            if (result.isConfirmed) {
                                window.location.href = '{{ route('vista_pendientes.costos_mep') }}';
                            }
                        });
                    } else {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sin pendientes',
                            text: 'No hay viajes pendientes por verificar.',
                        });
                    }
                });
            });
        });
    </script>
@endpush
