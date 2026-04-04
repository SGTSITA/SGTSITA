@extends('layouts.app')

@section('template_title')
    Busqueda por criterios
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Buscador de cotizaciones</h5>
                        <p class="card-text">Seleccione los críterios de busqueda deseados y haga click en Buscar</p>

                        @if (Session::has('message'))
                            <div id="label-message" class="alert alert-warning">
                                <strong>{{ Session::get('message') }}</strong>
                            </div>
                        @endif

                        <form method="post" action="{{ route('exec.busqueda.cotizaciones') }}">
                            @csrf
                            <div class="row">
                                <div class="col-8 offset-2">
                                    <div class="mb-3">
                                        <label for="txtCliente" class="form-label">Cliente</label>
                                        <input
                                            type="text"
                                            name="txtCliente"
                                            class="form-control"
                                            id="txtCliente"
                                            placeholder="Introduzca nombre del cliente"
                                        />
                                    </div>
                                </div>
                                <div class="col-4 offset-2">
                                    <div class="mb-3">
                                        <label for="txtOrigen" class="form-label">Origen</label>
                                        <input
                                            type="text"
                                            name="txtOrigen"
                                            class="form-control"
                                            id="txtOrigen"
                                            placeholder="Introduzca Origen"
                                        />
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="mb-3">
                                        <label for="txtDestino" class="form-label">Destino</label>
                                        <input
                                            type="text"
                                            name="txtDestino"
                                            class="form-control"
                                            id="txtDestino"
                                            placeholder="Introduzca Destino"
                                        />
                                    </div>
                                </div>
                                <div class="col-4 offset-2">
                                    <div class="mb-3">
                                        <label for="txtContenedor" class="form-label"># Contenedor</label>
                                        <input
                                            type="text"
                                            name="txtContenedor"
                                            class="form-control"
                                            id="txtContenedor"
                                            placeholder="Introduzca # Contenedor"
                                        />
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary offset-2">Iniciar busqueda</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('custom-javascript')
    <script>
        $(document).ready(() => {
            setTimeout(() => {
                $('#label-message').hide();
            }, 2500);
        });
    </script>
@endpush
