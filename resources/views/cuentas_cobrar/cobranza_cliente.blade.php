@extends('layouts.app')

@section('template_title')
    Cuentas por cobrar
@endsection

@section('content')
    <div class="col-md-12 mb-lg-0 mb-4">
        <div class="card mt-4">
            <div class="card-header pb-0 p-3">
                <div class="row">
                    <div class="col-6 d-flex align-items-center">
                        <h6 class="mb-0">Cuentas por cobrar</h6>
                    </div>
                    <div class="col-6 text-end">
                        <a class="btn btn-sm bg-gradient-warning mb-0" href="{{ route('index.cobrar') }}">
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
                            <li class="list-group-item border-0 d-flex p-4 mb-2 bg-gray-100 border-radius-lg">
                                <div class="d-flex flex-column">
                                    <h6 class="mb-3 text-sm">Información del cliente</h6>
                                    <span class="mb-2 text-md">
                                        Nombre:
                                        <span class="text-dark font-weight-bold ms-sm-2">{{ $cliente->nombre }}</span>
                                    </span>
                                    <span class="mb-2 text-md">
                                        Viajes Totales:
                                        <span class="text-dark ms-sm-2 font-weight-bold">
                                            {{ $cotizacion->total_cotizaciones }}
                                        </span>
                                    </span>
                                    <!--span class="text-xs">Saldo actual: <span class="text-dark ms-sm-2 font-weight-bold">${{ number_format($cotizacion->total_restante, 0, '.', ',') }}</span></span-->
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="col-md-6 row">
                        <div class="col-lg-4 col-6 text-center">
                            <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                                <h6 class="text-primary mb-0">Saldo Original</h6>
                                <h4 class="font-weight-bolder">
                                    <span class="small" id="currentBalance">$ 0.00</span>
                                </h4>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6 text-center">
                            <div class="border-dashed border-1 border-secondary border-radius-md py-3">
                                <h6 class="text-primary mb-0">Cobranza</h6>
                                <h4 class="font-weight-bolder"><span class="small" id="payment">$ 0.00</span></h4>
                            </div>
                        </div>
                        <div class="col-lg-4 col-6 text-center">
                            <div
                                class="border-dashed border-1 border-secondary border-radius-md py-3"
                                id="borderBalance"
                            >
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
                                        <span class="text-sm opacity-8 ps-3">Cobro 1</span>
                                        <span class="text-lg text-dark font-weight-bolder ps-2 pe-3" id="sumPago1">
                                            $0.00
                                        </span>
                                    </span>
                                </li>
                                <li class="nav-item">
                                    <span class="border-dashed border-1 border-secondary border-radius-md py-3">
                                        <span class="text-sm opacity-8 ps-3">Cobro 2</span>
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
                    <div id="pendientes"></div>
                    <div class="row">
                        <div class="col-8 offset-4 text-end mt-3">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Banco 1</label>
                                        <select name="cmbBankOne" id="cmbBankOne" class="form-control">
                                            <option value="null">Seleccione banco</option>
                                            @foreach ($bancos as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->nombre_banco }}: ${{ number_format($item->saldo, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label for="example-text-input" class="form-control-label">Banco 2</label>
                                        <select name="cmbBankTwo" id="cmbBankTwo" class="form-control">
                                            <option value="null">Seleccione banco</option>
                                            @foreach ($bancos as $item)
                                                <option value="{{ $item->id }}">
                                                    {{ $item->nombre_banco }}: ${{ number_format($item->saldo, 2) }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-4 d-flex flex-column">
                                    <button
                                        class="btn btn-sm bg-gradient-success mt-auto"
                                        name="btnAplicarPago"
                                        id="btnAplicarPago"
                                        type="button"
                                    >
                                        <i class="fas fa-check" aria-hidden="true"></i>
                                        &nbsp;&nbsp;Aplicar cobro
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
    <!--script src="/js/sgt/cxc/cxc.js"></script-->
    <script src="{{ asset('js/sgt/cxc/cxc.js') }}?v={{ filemtime(public_path('js/sgt/cxc/cxc.js')) }}"></script>

    <script>
        $(document).ready(() => {
            getViajesSinLiquidar({{ $cliente->id }});
        });
    </script>
@endpush
