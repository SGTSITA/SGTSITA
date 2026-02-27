@extends('layouts.app')

@section('template_title')
    Cuentas por pagar
@endsection

@section('content')
    <div class="col-md-12 mb-lg-0 mb-4">
        <div class="card mt-4">
            <div class="card-header pb-0 p-3">
                <div class="row">
                    <div class="col-6 d-flex align-items-center">
                        <h6 class="mb-0">Cuentas por pagar</h6>
                    </div>
                    <div class="col-6 text-end">
                        <a class="btn btn-sm bg-gradient-warning mb-0" href="{{ route('index.pagar') }}">
                            <i class="fas fa-chevron-left" aria-hidden="true"></i>
                            &nbsp;&nbsp;Regresar
                        </a>
                    </div>
                </div>
            </div>
            <div class="card-body p-3">
                <div class="row">
                    <div class="col-md-6 mb-md-0 mb-4">
                        <ul class="list-group">
                            <li
                                class="list-group-item d-flex p-4 mb-2 bg-gray-100 border-dashed border-1 border-secondary border-radius-md">
                                <div class="d-flex flex-column text-end me-4">
                                    <div class="icon icon-shape icon-lg bg-gradient-secondary text-center border-radius-lg">
                                        <i class="fas fa-solid fa-file-invoice-dollar opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                                <div class="d-flex flex-column">
                                    {{-- <h6 class="mb-3 text-sm">Información del Proveedor</h6> --}}
                                    <span class="mb-2 text-md">
                                        Nombre Provedor:
                                        <span class="text-dark font-weight-bold ms-sm-2">{{ $proveedor->nombre }}</span>
                                    </span>
                                    <span class="mb-2 text-md">
                                        Viajes Totales:
                                        <span class="text-dark ms-sm-2 font-weight-bold" id="countViajes">0</span>
                                    </span>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6 row">
                        <div class="col-lg-4 col-6 text-center">
                            <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                                <h6 class="text-primary mb-0">Saldo Actual</h6>
                                <h4 class="font-weight-bolder">
                                    <span class="small" id="currentBalance">$ 0.00</span>
                                </h4>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6 text-center">
                            <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                                <h6 class="text-primary mb-0">Pagos</h6>
                                <h4 class="font-weight-bolder"><span class="small" id="payment">$ 0.00</span></h4>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6 text-center">
                            <div class="border-dashed border-1 border-secondary border-radius-md py-3" id="borderBalance">
                                <h6 class="text-primary mb-0">Saldo Final</h6>
                                <h4 class="font-weight-bolder"><span class="small" id="finalBalance">$ 0.00</span></h4>
                            </div>
                        </div>
                    </div>
                    <!--div class="col-md-6">
                                                                                                                                            <div class="card card-body border card-plain border-radius-lg d-flex align-items-center flex-row">
                                                                                                                                            <img class="w-10 me-3 mb-0" src="../assets/img/logos/visa.png" alt="logo">
                                                                                                                                            <h6 class="mb-0">****&nbsp;&nbsp;&nbsp;****&nbsp;&nbsp;&nbsp;****&nbsp;&nbsp;&nbsp;5248</h6>
                                                                                                                                            <i class="fas fa-pencil-alt ms-auto text-dark cursor-pointer" data-bs-toggle="tooltip" data-bs-placement="top" aria-hidden="true" aria-label="Edit Card" data-bs-original-title="Edit Card"></i><span class="sr-only">Edit Card</span>
                                                                                                                                            </div>

                                                                                                                                         </div-->
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12 mt-2">
            <div class="card">
                <div class="card-header pb-0 px-3">
                    <div class="row">
                        <div class="col-6 d-flex align-items-center">
                            <h6 class="mb-0">Viajes por liquidar</h6>
                        </div>
                        <div class="col-6 text-end">
                            <ul class="nav nav-footer justify-content-center justify-content-lg-end">
                                <li class="nav-item pe-1">
                                    <span class="border-dashed border-1 border-secondary border-radius-md py-3">
                                        <span class="text-sm opacity-8 ps-3">Pago 1</span>
                                        <span class="text-lg text-dark font-weight-bolder ps-2 pe-3" id="sumPago1">
                                            $0.00
                                        </span>
                                    </span>
                                </li>
                                <li class="nav-item">
                                    <span class="border-dashed border-1 border-secondary border-radius-md py-3">
                                        <span class="text-sm opacity-8 ps-3">Pago 2</span>
                                        <span class="text-lg text-dark font-weight-bolder ps-2 pe-3" id="sumPago2">
                                            $0.00
                                        </span>
                                    </span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                <div class="card-body pt-4 p-3">
                    <div id="pagosPendientes"></div>


                    <div class="row">
                        <div class="col-8 offset-4 text-end mt-3">
                            <div class="row">
                                <div class="col-md-5">
                                    <div class="card p-3 border">
                                        <h6>Pago 1</h6>

                                        <div class="form-group">
                                            {{-- <label>Seleccione banco</label> --}}
                                            <select name="cmbBankOne" id="cmbBankOne" class="form-control form-control-sm">
                                                <option value="">Retiro</option>
                                                @foreach ($bancos as $item)
                                                    <option value="{{ $item['id'] }}">
                                                        {{ $item['display'] }}
                                                    </option>
                                                @endforeach
                                            </select>

                                        </div>

                                        <div class="form-group">
                                            {{-- <label for="FechaAplicacionbank1">Fecha Aplicación</label> --}}
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fa fa-calendar text-danger"></i>
                                                </span>
                                                <input class="form-control dateInput" name="FechaAplicacionbank1"
                                                    id="FechaAplicacionbank1" placeholder="Fecha Aplicación banco 1"
                                                    type="text" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <select name="cmbBankProvOne" id="cmbBankProvOne"
                                                class="form-control form-control-sm">
                                                <option value="">Proveedor</option>
                                                @foreach ($banco_proveedor as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->nombre_banco }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-5">
                                    <div class="card p-3 border">
                                        <h6>Pago 2</h6>

                                        <div class="form-group">
                                            {{-- <label>Seleccione banco</label> --}}
                                            <select name="cmbBankTwo" id="cmbBankTwo"
                                                class="form-control form-control-sm">
                                                <option value="">Retiro</option>
                                                @foreach ($bancos as $item)
                                                    <option value="{{ $item['id'] }}">
                                                        {{ $item['display'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>


                                        <div class="form-group">
                                            {{-- <label for="FechaAplicacionbank2">Fecha Aplicación</label> --}}
                                            <div class="input-group">
                                                <span class="input-group-text">
                                                    <i class="fa fa-calendar text-danger"></i>
                                                </span>
                                                <input class="form-control dateInput" name="FechaAplicacionbank2"
                                                    id="FechaAplicacionbank2" placeholder="Fecha Aplicación banco 2"
                                                    type="text" />
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <select name="cmbBankProvTwo" id="cmbBankProvTwo"
                                                class="form-control form-control-sm">
                                                <option value="">Proveedor</option>
                                                @foreach ($banco_proveedor as $item)
                                                    <option value="{{ $item->id }}">
                                                        {{ $item->nombre_banco }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-2 text-end">
                                    <button class="btn bg-gradient-success px-4" id="btnAplicarPago" type="button">
                                        <i class="fas fa-check"></i>
                                        Aplicar
                                    </button>
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
    <link href="/assets/handsontable/handsontable.full.min.css" rel="stylesheet" media="screen" />
    <script src="/assets/handsontable/handsontable.full.min.js"></script>
    <script src="/assets/handsontable/all.js"></script>
    <!--script src="/js/sgt/cxp/cxp.js"></script-->
    <script src="{{ asset('js/sgt/cxp/cxp.js') }}?v={{ filemtime(public_path('js/sgt/cxp/cxp.js')) }}"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css" />
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>


    <script>
        $(document).ready(() => {
            getViajesPorPagar({{ $proveedor->id }});


            flatpickr(".dateInput", {
                dateFormat: "d/m/Y",
                locale: "es"
            });
        });
    </script>
@endpush
