@extends('layouts.app')

@section('template_title', 'Cuentas Bancarias')

@section('content')
    <div class="container-fluid">

        {{-- HEADER --}}
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ asset($catBanco->logo) }}" style="height:50px"
                        onerror="this.src='{{ asset('assets/bancos/default.svg') }}'">
                    <div>
                        <h3 class="mb-0">{{ $catBanco->nombre }}</h3>
                        <small class="text-muted">Cuentas bancarias registradas</small>
                    </div>
                </div>

                <a href="{{ route('cuentas.create', ['banco' => $catBanco->id]) }}" class="btn btn-success">
                    <i class="fa fa-plus me-1"></i> Nueva cuenta
                </a>
            </div>
        </div>

        {{-- CUENTAS --}}
        <div class="row">
            @forelse ($cuentas as $cuenta)
                <div class="col-xl-4 col-lg-6 col-md-6 mb-4">
                    <div class="card shadow-sm border-0 h-100"
                        style="background: linear-gradient(135deg,
                     {{ $catBanco->color }},
                     {{ $catBanco->color_secundario ?? $catBanco->color }});">

                        <div class="card-body text-white">

                            <h5 class="fw-bold mb-1">
                                {{ $cuenta->nombre_beneficiario }}
                            </h5>

                            <div class="opacity-75 mb-3">
                                {{ $cuenta->cuenta_bancaria }}
                            </div>

                            @if ($cuenta->clabe)
                                <div class="small mb-3">
                                    CLABE: {{ chunk_split($cuenta->clabe, 4, ' ') }}
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="badge bg-light text-dark">
                                    Activa
                                </span>

                                <div class="d-flex gap-2">
                                    <a href="#" class="btn btn-light btn-sm">
                                        <i class="fa fa-list"></i>
                                    </a>

                                    <a href="#" class="btn btn-light btn-sm">
                                        <i class="fa fa-pen"></i>
                                    </a>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <i class="fa fa-credit-card fa-3x text-muted mb-3"></i>
                    <p class="text-muted">Este banco aún no tiene cuentas registradas</p>
                </div>
            @endforelse
        </div>

    </div>
@endsection
