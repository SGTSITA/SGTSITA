
         <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
            {{ Form::label('nombre') }}
            {{ Form::text('nombre', $client->nombre, ['class' => 'form-control' . ($errors->has('nombre') ? ' is-invalid' : ''), 'placeholder' => 'Nombre']) }}
            {!! $errors->first('nombre', '<div class="invalid-feedback">:message</div>') !!}
        </div>

         <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
            {{ Form::label('apellido') }}
            {{ Form::text('apellido', $client->apellido, ['class' => 'form-control' . ($errors->has('apellido') ? ' is-invalid' : ''), 'placeholder' => 'Apellido']) }}
            {!! $errors->first('apellido', '<div class="invalid-feedback">:message</div>') !!}
        </div>

         <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
            {{ Form::label('edad') }}
            {{ Form::number('edad', $client->edad, ['class' => 'form-control' . ($errors->has('edad') ? ' is-invalid' : ''), 'placeholder' => 'Edad']) }}
            {!! $errors->first('edad', '<div class="invalid-feedback">:message</div>') !!}
        </div>

         <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
            {{ Form::label('sanguineo') }}
            {{ Form::text('sanguineo', $client->sanguineo, ['class' => 'form-control' . ($errors->has('sanguineo') ? ' is-invalid' : ''), 'placeholder' => 'Sanguineo']) }}
            {!! $errors->first('sanguineo', '<div class="invalid-feedback">:message</div>') !!}
        </div>

         <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
            {{ Form::label('ocupacion') }}
            {{ Form::text('ocupacion', $client->ocupacion, ['class' => 'form-control' . ($errors->has('ocupacion') ? ' is-invalid' : ''), 'placeholder' => 'Ocupacion']) }}
            {!! $errors->first('ocupacion', '<div class="invalid-feedback">:message</div>') !!}
        </div>

         <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
            {{ Form::label('telefono') }}
            {{ Form::number('telefono', $client->telefono, ['class' => 'form-control' . ($errors->has('telefono') ? ' is-invalid' : ''), 'placeholder' => 'Telefono']) }}
            {!! $errors->first('telefono', '<div class="invalid-feedback">:message</div>') !!}
        </div>

         <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
            {{ Form::label('fecha_nacimiento') }}
            {{ Form::date('fecha_nacimiento', $client->fecha_nacimiento, ['class' => 'form-control' . ($errors->has('fecha_nacimiento') ? ' is-invalid' : ''), 'placeholder' => 'Fecha Nacimiento']) }}
            {!! $errors->first('fecha_nacimiento', '<div class="invalid-feedback">:message</div>') !!}
        </div>

         <div class="col-xs-12 col-sm-12 col-md-6 mt-3">
            {{ Form::label('email') }}
            {{ Form::email('email', $client->email, ['class' => 'form-control' . ($errors->has('email') ? ' is-invalid' : ''), 'placeholder' => 'Email']) }}
            {!! $errors->first('email', '<div class="invalid-feedback">:message</div>') !!}
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 text-center mt-5">
           <button type="submit" class="btn" style="background: {{$configuracion->color_boton_save}}; color: #ffff">Submit</button>
        </div>

