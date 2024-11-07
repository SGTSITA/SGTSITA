@extends('layouts.app')

@section('template_title')
    Create Usuarios
@endsection

@section('content')

<div class="container-fluid mt-3">
      <div class="row">
        <div class="col">
          <div class="card">
            <!-- Card header -->
            <div class="card-header">
              <h3 class="mb-3">Crear nuevo usuario</h3>
               <a class="btn" href="{{ route('users.index') }}" style="background: {{$configuracion->color_boton_close}}; color: #ffff"> Back</a>
                    @if (count($errors) > 0)
                      <div class="alert alert-danger">
                        <strong>Whoops!</strong> There were some problems with your input.<br><br>
                        <ul>
                           @foreach ($errors->all() as $error)
                             <li>{{ $error }}</li>
                           @endforeach
                        </ul>
                      </div>
                    @endif
            </div>

            <div class="card-body mb-5">

                {!! Form::open(array('route' => 'users.store','method'=>'POST')) !!}
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Nombre:</label>
                            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                        </div>

                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Email:</label>
                            {!! Form::text('email', null, array('placeholder' => 'Email','class' => 'form-control')) !!}
                        </div>
                    </div>

                @if(auth()->user()->Empresa->id == 1)

                <div class="col-xs-12 col-sm-12 col-md-6">
                    <div class="form-group">
                        <label for="">Empresa</label>
                        <select name="id_empresa" id="" class="form-select">
                            <option value="">Seleciona una opcion</option>
                            @foreach ($empresas_base as  $item)
                                <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                @else

                <div class="col-xs-12 col-sm-12 col-md-6">
                    <div class="form-group">
                        <label for="">Empresa</label>
                        <select name="id_empresa" id="" class="form-select">
                            <option value="">Seleciona una opcion</option>
                            @foreach ($empresas as  $item)
                                <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>


                @endif

                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label for="">Clientes</label>
                            <select name="id_cliente" id="" class="form-select">
                                <option value="">Seleciona Cliente</option>
                                @foreach ($clientes as  $item)
                                    <option value="{{ $item->id }}">{{ $item->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Password:</label>
                            {!! Form::password('password', array('placeholder' => 'Password','class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Confirm Password:</label>
                            {!! Form::password('confirm-password', array('placeholder' => 'Confirm Password','class' => 'form-control')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <div class="form-group">
                            <label class="form-control-label">Role:</label>
                            {!! Form::select('roles[]', $roles,[], array('class' => 'form-control','multiple')) !!}
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                        <button type="submit" class="btn" style="background: {{$configuracion->color_boton_save}}; color: #ffff">Submit</button>
                    </div>
                </div>
                {!! Form::close() !!}

            </div>

          </div>
        </div>
      </div>
</div>


@endsection
