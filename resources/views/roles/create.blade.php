@extends('layouts.app')

@section('template_title')
    Create Rol
@endsection

@section('content')

<div class="container-fluid mt-3">
      <div class="row">
        <div class="col">
          <div class="card">
            <!-- Card header -->
            <div class="card-header">
              <h3 class="mb-3">Create New Role</h3>
              <div class="d-flex justify-content-between">
                <a class="btn" href="{{ route('roles.index') }}" style="background: {{$configuracion->color_boton_close}}; color: #ffff"> Back</a>

                  <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#exampleModal">
                    Roles
                  </button>

              </div>

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

                {!! Form::open(array('route' => 'roles.store','method'=>'POST')) !!}
                <div class="row">

                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Name:</label>
                            {!! Form::text('name', null, array('placeholder' => 'Name','class' => 'form-control')) !!}
                        </div>
                    </div>

                    <div class="col-xs-12 col-sm-12 col-md-6">
                        <div class="form-group">
                            <label class="form-control-label">Permission:</label>
                            <br/>
                            @foreach($permission as $value)
                                <label>{{ Form::checkbox('permission[]', $value->id, false, array('class' => 'name')) }}
                                    {{ $value->name }}
                                </label>
                            <br/>
                            @endforeach
                        </div>

                        <div class="col-xs-12 col-sm-12 col-md-12 text-center">
                            <button type="submit" class="btn" style="background: {{$configuracion->color_boton_save}}; color: #ffff">Submit</button>
                        </div>

                    </div>

                </div>
                {!! Form::close() !!}

            </div>

          </div>
        </div>
      </div>
</div>

@include('roles.modal')

@endsection
