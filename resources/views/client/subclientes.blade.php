@extends('layouts.app')

@section('template_title')
Subcliente
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <a id="backButton" class="btn" style="background: {{$configuracion->color_boton_close}}; color: #ffff; margin-right: 3rem;">
                                Regresar
                            </a>

                            <h2 id="card_title">
                               Subcliente <br>{{ $subcliente->nombre }}
                            </h2>

                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('update_subclientes.clients',$subcliente->id ) }}" id="" enctype="multipart/form-data" role="form">
                            <input type="hidden" name="_method" value="PATCH">
                            @csrf
                        <div class="row">
                            <div class="col-12 form-group">
                                <label for="name">Nombre Completo*</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">
                                    </span>
                                    <input name="nombre" id="nombre" type="text" class="form-control" value="{{ $subcliente->nombre }}">
                                </div>
                            </div>

                            <div class="col-6 form-group">
                                <label for="name">correo *</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/sobre.png.webp') }}" alt="" width="25px">
                                    </span>
                                    <input name="correo" id="correo" type="email" class="form-control" value="{{ $subcliente->correo }}">
                                </div>
                            </div>

                            <div class="col-6 form-group">
                                <label for="name">Telefono *</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/telefono.png.webp') }}" alt="" width="25px">
                                    </span>
                                    <input name="telefono" id="telefono" type="number" class="form-control" value="{{ $subcliente->telefono }}">
                                </div>
                            </div>

                            <div class="col-6 form-group">
                                <label for="name">Direccion *</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/mapa-de-la-ciudad.webp') }}" alt="" width="25px">
                                    </span>
                                    <input name="direccion" id="direccion" type="text" class="form-control" value="{{ $subcliente->direccion }}">
                                </div>
                            </div>

                            <div class="col-6 form-group">
                                <label for="name">Regimen Fiscal*</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px">
                                    </span>
                                    <input name="regimen_fiscal" id="regimen_fiscal" type="text" class="form-control" value="{{ $subcliente->regimen_fiscal }}">
                                </div>
                            </div>

                            <div class="col-6 form-group">
                                <label for="name">RFC*</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="25px">
                                    </span>
                                    <input name="rfc" id="rfc" type="text" class="form-control" value="{{ $subcliente->rfc }}">
                                </div>
                            </div>

                            <div class="col-6 form-group">
                                <label for="name">Nombre de Empresa</label>
                                <div class="input-group mb-3">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/edificios_ciudad.webp') }}" alt="" width="25px">
                                    </span>
                                    <input name="nombre_empresa" id="nombre_empresa" type="text" class="form-control" value="{{ $subcliente->nombre_empresa }}">
                                </div>
                            </div>

                            <div class="col-6">
                                <button type="submit" class="btn btn-primary">Actualizar</button>
                            </div>

                        </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>


@endsection

@section('datatable')


</script>

@endsection
