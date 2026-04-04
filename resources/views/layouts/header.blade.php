<div class="col-sm-12 mb-3">
    <div class="card">
        <div class="card-header">
            <div style="display: flex; justify-content: space-between; align-items: center">
                <span id="card_title">
                    {{ __('Total') }}
                </span>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-lg-4 col-md-4 col-12">
                    <div class="card mb-4">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Servicios</p>
                                        <h5 class="font-weight-bolder">
                                            {{ $servicios_total->filas }}
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div
                                        class="icon icon-shape bg-gradient-primary shadow-primary text-center rounded-circle"
                                    >
                                        <i class="ni ni-book-bookmark text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-4 col-md-4 col-12">
                    <div class="card mb-4">
                        <div class="card-body p-3">
                            <div class="row">
                                <div class="col-8">
                                    <div class="numbers">
                                        <p class="text-sm mb-0 text-uppercase font-weight-bold">Productos</p>
                                        <h5 class="font-weight-bolder">
                                            {{ $pedidos_total->filas }}
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-4 text-end">
                                    <div
                                        class="icon icon-shape bg-gradient-danger shadow-danger text-center rounded-circle"
                                    >
                                        <i class="ni ni-basket text-lg opacity-10" aria-hidden="true"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{--
                    <div class="col-lg-4 col-md-4 col-12">
                    <div class="card  mb-4">
                    <div class="card-body p-3">
                    <div class="row">
                    <div class="col-8">
                    <div class="numbers">
                    <p class="text-sm mb-0 text-uppercase font-weight-bold">Clientes</p>
                    <h5 class="font-weight-bolder">
                    {{$clientes_total->filas}}
                    </h5>
                    </div>
                    </div>
                    <div class="col-4 text-end">
                    <div class="icon icon-shape bg-gradient-success shadow-success text-center rounded-circle">
                    <i class="ni ni-circle-08 text-lg opacity-10" aria-hidden="true"></i>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                    </div>
                --}}
            </div>
        </div>
    </div>
</div>
