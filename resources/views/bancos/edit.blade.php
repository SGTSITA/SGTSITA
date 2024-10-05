@extends('layouts.app')

@section('template_title')
    Bancos Edit
@endsection

@section('content')
<div class="row">

    @php
        $banco_entrada = 0;
        $banco_salida = 0;

        foreach ($banco_dinero_entrada as $item){
            if ($item->id_banco1 == $banco->id){
                $banco_entrada += $item->monto1;
            }
            if ($item->id_banco2 == $banco->id){
                $banco_entrada += $item->monto2;
            }
        }

        foreach ($banco_dinero_salida as $item){
            if ($item->id_banco1 == $banco->id){
                $banco_salida += $item->monto1;
            }
            if ($item->id_banco2 == $banco->id){
                $banco_salida += $item->monto2;
            }
        }

        $total = 0;

        foreach ($cotizaciones as $item){
            if ($item->id_banco1 == $banco->id){
                $total += $item->monto1;
            }
            if ($item->id_banco2 == $banco->id){
                $total += $item->monto2;
            }
        }

        $pagos = 0;
        $pagos_salida = 0;

        foreach ($proveedores as $item){
            if ($item->id_prove_banco1 == $banco->id){
                $pagos += $item->prove_monto1;
            }
            if ($item->id_prove_banco2 == $banco->id){
                $pagos += $item->prove_monto2;
            }
        }

        foreach ($banco_dinero_salida_ope as $item){
            if ($item->id_banco1 == $banco->id){
                $pagos_salida += $item->monto1;
            }
            if ($item->id_banco2 == $banco->id){
                $pagos_salida += $item->monto2;
            }
        }

        $gastos_extras = 0;
        foreach ($gastos_generales as $item){
           $gastos_extras += $item->monto1;
        }

        $total_pagos = 0;
        $total_pagos = $pagos + $pagos_salida + $banco_salida + $gastos_extras;
        $saldo = 0;
        $saldo = ($banco->saldo_inicial + $total + $banco_entrada) - $total_pagos;
        $cargo = $banco->saldo_inicial + $total + $banco_entrada;
    @endphp

    <div class="col-lg-4 col-md-6 col-12 mt-4 mt-md-0">
        <div class="card overflow-hidden shadow-lg" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/reports2.jpg'); background-size: cover;">
            <span class="mask bg-gradient-dark"></span>
            <div class="card-body p-3 position-relative">
                <div class="row">
                    <div class="col-8 text-start">
                        <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                            <img class="w-60 mt-2" src="{{ asset('img/icon/efectivo.webp') }}" alt="logo">
                        </div>
                        <h5 class="text-white font-weight-bolder mb-0 mt-3">
                            $ {{ number_format($cargo, 0, '.', ',') }}
                        </h5>
                        <span class="text-white text-sm">Cargo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 col-12 mt-4 mt-md-0">
        <div class="card overflow-hidden shadow-lg" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/reports3.jpg'); background-size: cover;">
            <span class="mask bg-gradient-dark"></span>
            <div class="card-body p-3 position-relative">
                <div class="row">
                    <div class="col-8 text-start">
                        <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                            <img class="w-60 mt-2" src="{{ asset('img/icon/gastos.png.webp') }}" alt="logo">
                        </div>
                        <h5 class="text-white font-weight-bolder mb-0 mt-3">
                            $ {{ number_format($total_pagos, 0, '.', ',') }}
                        </h5>
                        <span class="text-white text-sm">Abono</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4 col-md-6 col-12 mt-4 mt-md-0">
        <div class="card overflow-hidden shadow-lg" style="background-image: url('https://raw.githubusercontent.com/creativetimofficial/public-assets/master/argon-dashboard-pro/assets/img/reports1.jpg'); background-size: cover;">
            <span class="mask bg-gradient-dark"></span>
            <div class="card-body p-3 position-relative">
                <div class="row">
                    <div class="col-8 text-start">
                        <div class="icon icon-shape bg-white shadow text-center border-radius-md">
                            <img class="w-60 mt-2" src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="logo">
                        </div>
                        <h5 class="text-white font-weight-bolder mb-0 mt-3">
                            $ {{ number_format($saldo, 0, '.', ',') }}
                        </h5>
                        <span class="text-white text-sm">Saldo</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <div class="container-fluid py-4">
        <div class="row mt-3">

            <div class="col-12 mb-2">
                <div class="card h-100">
                    <div class="card-header pb-0 p-3">
                        <h6 class="mb-0">Filtro</h6>
                    </div>
                    <div class="card-body p-3">
                        <form action="{{ route('advance_planeaciones.buscador') }}" method="GET">
                                <div class="row">
                                    <div class="col-4">
                                        <label for="user_id">Rango de fecha DE:</label>
                                        <input class="form-control" type="date" id="fecha_de" name="fecha_de">
                                    </div>
                                    <div class="col-4">
                                        <label for="user_id">Rango de fecha Hasta:</label>
                                        <input class="form-control" type="date" id="fecha_hasta" name="fecha_hasta">
                                    </div>
                                    <div class="col-4">
                                        <button class="btn btn-sm mb-0 mt-sm-0 mt-1" type="submit" style="background-color: #F82018; color: #ffffff;">Buscar</button>
                                    </div>
                                </div>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-6 mb-4"">
                <div class="card h-100">
                    <div class="card-header pb-0 p-3">
                        <a id="backButton" class="btn" style="background: {{$configuracion->color_boton_close}}; color: #ffff; margin-right: 3rem;">
                            Regresar
                        </a>
                        <h6 class="mb-0">Cobros</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-3"><b>Fecha</b></div>
                            <div class="col-3"><b>Contenedor</b></div>
                            <div class="col-3"><b>Cliente</b></div>
                            <div class="col-3"><b>Cargo</b></div>

                            @foreach ($cotizaciones as $item)
                                <div class="col-3">
                                    {{ \Carbon\Carbon::parse($item->fecha_pago)->translatedFormat('j \d\e F') }}
                                </div>
                                <div class="col-3">
                                    @can('bancos-entrar-cotizacion')
                                    <a class="btn btn-xs btn-success" href="{{ route('edit.cotizaciones', $item->id) }}">
                                        {{ $item->DocCotizacion->num_contenedor }}
                                    </a>
                                    @endcan
                                </div>
                                <div class="col-3">{{ $item->Cliente->nombre }}</div>
                                <div class="col-3">
                                    @if ($item->id_banco1  == $banco->id)
                                        $ {{ number_format($item->monto1, 0, '.', ',') }}
                                    @else
                                        $ {{ number_format($item->monto2, 0, '.', ',') }}
                                    @endif

                                </div>
                            @endforeach
                            @foreach ($banco_dinero_entrada as $item)
                                <div class="col-3">
                                    {{ \Carbon\Carbon::parse($item->fecha_pago)->translatedFormat('j \d\e F') }}
                                </div>
                                <div class="col-3">
                                    <a data-bs-toggle="collapse" href="#pagesEntrada{{ $item->id }}" aria-controls="pagesEntrada" role="button" aria-expanded="false">
                                        Varios
                                    </a>
                                </div>
                                <div class="col-3">{{ $item->Cliente->nombre }}</div>
                                <div class="col-3">
                                    @if ($item->id_banco1  == $banco->id)
                                        $ {{ number_format($item->monto1, 0, '.', ',') }}
                                    @else
                                        $ {{ number_format($item->monto2, 0, '.', ',') }}
                                    @endif
                                </div>
                                @if ($item->contenedores != null)
                                    <div class="collapse " id="pagesEntrada{{ $item->id }}">
                                        Contenedores y Abonos
                                        <ul>
                                            @php
                                                $contenedoresAbonos = json_decode($item->contenedores, true);
                                            @endphp
                                            @foreach ($contenedoresAbonos as $contenedorAbono)
                                                <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-6 mt-md-0 mt-4">
                <div class="card h-100">
                    <div class="card-header pb-0 p-3">
                        <h6 class="mb-0">Pagos</h6>
                    </div>
                    <div class="card-body p-3">
                        <div class="row">
                            <div class="col-3"><b>Fecha</b></div>
                            <div class="col-3"><b>Contenedor</b></div>
                            <div class="col-3"><b>Cliente</b></div>
                            <div class="col-3"><b>Cargo</b></div>

                            @foreach ($proveedores as $item)
                                <div class="col-3">
                                    {{ \Carbon\Carbon::parse($item->fecha_pago_proveedor)->translatedFormat('j \d\e F') }}
                                </div>
                                <div class="col-3">
                                    @can('bancos-entrar-cotizacion')
                                    <a class="btn btn-xs btn-success" href="{{ route('edit.cotizaciones', $item->id) }}">
                                        {{ $item->DocCotizacion->num_contenedor }}
                                    </a>
                                    @endcan
                                </div>
                                <div class="col-3">{{ $item->Cliente->nombre }}</div>
                                <div class="col-3">
                                    @if ($item->id_prove_banco1  == $banco->id)
                                        $ {{ number_format($item->prove_monto1, 0, '.', ',') }}
                                    @else
                                        $ {{ number_format($item->prove_monto2, 0, '.', ',') }}
                                    @endif
                                </div>
                            @endforeach

                            @foreach ($banco_dinero_salida_ope as $item)
                                <div class="col-3">
                                    @if ($item->fecha_pago != NULL)
                                    {{ \Carbon\Carbon::parse($item->fecha_pago)->translatedFormat('j \d\e F') }}
                                    @endif
                                </div>
                                <div class="col-3">
                                    @can('bancos-entrar-cotizacion')
                                    <a class="btn btn-xs btn-info" href="{{ route('edit.cotizaciones', $item->id_cotizacion) }}">
                                        {{ $item->Asignacion->Contenedor->num_contenedor }}
                                    </a>
                                    @endcan
                                </div>
                                <div class="col-3">{{ $item->Operador->nombre }}</div>
                                <div class="col-3">
                                    @if ($item->id_banco1 == $banco->id)
                                        $ {{ number_format($item->monto1, 0, '.', ',') }}
                                    @else
                                        $ {{ number_format($item->monto2, 0, '.', ',') }}
                                    @endif
                                </div>
                            @endforeach

                            @foreach ($banco_dinero_salida_ope_varios as $item)
                                <div class="col-3">
                                    {{ \Carbon\Carbon::parse($item->fecha_pago)->translatedFormat('j \d\e F') }}
                                </div>
                                <div class="col-3">
                                    <a data-bs-toggle="collapse" href="#pagesOperadores{{ $item->id }}" aria-controls="pagesOperadores" role="button" aria-expanded="false">
                                        Liquidación Varios
                                    </a>
                                </div>
                                <div class="col-3">{{ $item->Operador->nombre }}</div>
                                <div class="col-3">
                                    @if ($item->id_banco1  == $banco->id)
                                        $ {{ number_format($item->monto1, 0, '.', ',') }}
                                    @else
                                        $ {{ number_format($item->monto2, 0, '.', ',') }}
                                    @endif

                                </div>

                                @if ($item->contenedores != null)
                                    <div class="collapse " id="pagesOperadores{{ $item->id }}">
                                        Contenedores y Abonos
                                        <ul>
                                            @php
                                                $contenedoresAbonos = json_decode($item->contenedores, true);
                                            @endphp
                                            @foreach ($contenedoresAbonos as $contenedorAbono)
                                                <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach

                            @foreach ($banco_dinero_salida as $item)
                                <div class="col-3">
                                    {{ \Carbon\Carbon::parse($item->fecha_pago)->translatedFormat('j \d\e F') }}
                                </div>
                                <div class="col-3">
                                    <a data-bs-toggle="collapse" href="#pagesExamples{{ $item->id }}" aria-controls="pagesExamples" role="button" aria-expanded="false">
                                        Varios
                                    </a>
                                </div>
                                <div class="col-3">{{ $item->Cliente->nombre }}</div>
                                <div class="col-3">
                                    @if ($item->id_banco1  == $banco->id)
                                        $ {{ number_format($item->monto1, 0, '.', ',') }}
                                    @else
                                        $ {{ number_format($item->monto2, 0, '.', ',') }}
                                    @endif

                                </div>

                                @if ($item->contenedores != null)
                                    <div class="collapse " id="pagesExamples{{ $item->id }}">
                                        Contenedores y Abonos
                                        <ul>
                                            @php
                                                $contenedoresAbonos = json_decode($item->contenedores, true);
                                            @endphp
                                            @foreach ($contenedoresAbonos as $contenedorAbono)
                                                <li>{{ $contenedorAbono['num_contenedor'] }} - ${{ number_format($contenedorAbono['abono'], 2, '.', ',') }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            @endforeach

                            @foreach ($gastos_generales as $item)
                                <div class="col-3">
                                    {{ \Carbon\Carbon::parse($item->fecha)->translatedFormat('j \d\e F') }}
                                </div>
                                <div class="col-6">{{ $item->motivo }}</div>
                                <div class="col-3">
                                    $ {{ number_format($item->monto1, 0, '.', ',') }}
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-xl-6 mt-md-0 mt-4">
                <div class="card h-100">
                    <div class="card-header pb-0 p-3">
                        <h6 class="mb-0">Datos Bancarios</h6>
                    </div>
                    <div class="card-body p-3">
                        <form method="POST" action="{{ route('update.bancos',$banco->id) }}" id="" enctype="multipart/form-data" role="form">
                            <input type="hidden" name="_method" value="PATCH">
                            @csrf

                            <div class="modal-body">
                                <div class="row">

                                    <div class="col-12 form-group">
                                        <label for="name">Nombre Beneficiario*</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="nombre_beneficiario" id="nombre_beneficiario" type="text" class="form-control" value="{{$banco->nombre_beneficiario}}">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Nombre Banco *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/sobre.png.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="nombre_banco" id="nombre_banco" type="text" class="form-control" value="{{$banco->nombre_banco}}">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Cuenta Bancaria *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/business-card-design.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="cuenta_bancaria" id="cuenta_bancaria" type="text" class="form-control" value="{{$banco->cuenta_bancaria}}">
                                        </div>
                                    </div>

                                    <div class="col-6 form-group">
                                        <label for="name">Saldo inicial *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="saldo_inicial" id="saldo_inicial" type="number" class="form-control" value="{{$banco->saldo_inicial}}">
                                        </div>
                                    </div>

                                    {{-- <div class="col-6 form-group">
                                        <label for="name">Tipo *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/pago-en-efectivo.png') }}" alt="" width="25px">
                                            </span>
                                            <select class="form-select d-inline-block" id="tipo" name="tipo">
                                                @if ($banco->tipo == null)
                                                    <option value="Selecciona una opcion">Selecciona una opcion</option>
                                                @else
                                                    <option value="{{$banco->tipo}}">{{$banco->tipo}}</option>
                                                @endif
                                                <option value="Oficial">Oficial</option>
                                                <option value="No Oficial">No Oficial</option>
                                            </select>
                                        </div>
                                    </div> --}}

                                    <div class="col-12 form-group">
                                        <label for="name">Clabe *</label>
                                        <div class="input-group mb-3">
                                            <span class="input-group-text" id="basic-addon1">
                                                <img src="{{ asset('img/icon/mapa-de-la-ciudad.webp') }}" alt="" width="25px">
                                            </span>
                                            <input name="clabe" id="clabe" type="text" class="form-control"  value="{{$banco->clabe}}">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                              </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
