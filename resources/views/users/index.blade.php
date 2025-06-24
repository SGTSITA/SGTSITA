@extends('layouts.app')

@section('template_title')
    Usuarios
@endsection

@section('content')

<div class="container-fluid mt-3">
      <div class="row">
        <div class="col">
          <div class="card">
            <!-- Card header -->
            <div class="card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
              <a href="{{ route('dashboard') }}" class="btn" style="background: {{$configuracion->color_boton_close}}; color: #ffff; margin-right: 3rem;">
                  Regresar
              </a>

              <h3 class="mb-3">Usuarios</h3>

              <a class="btn" href="{{ route('users.create') }}" style="background: {{$configuracion->color_boton_add}}; color: #ffff"> Nuevo Usuario</a>
                </div>
            </div>

            <div class="table-responsive py-4" style="">
              <table class="table table-flush table_id" id="datatable-basic" >
                  <thead class="thead-light">
                      <tr>
                       <th>No</th>
                       <th>Name</th>
                       <th>Email</th>
                       <th>Empresa</th>
                       <th>Roles</th>
                       <th width="280px">Action</th>
                     </tr>
                  </thead>

                 @foreach ($data as $key => $user)
                      <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->Empresa->nombre }}</td>
                        <td>
                          @if(!empty($user->getRoleNames()))
                            @foreach($user->getRoleNames() as $v)
                               <label class="badge badge-success">{{ $v }}</label>
                            @endforeach
                          @endif
                        </td>

                        <td class="text-right">

                          <div class="dropdown">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <ul class="dropdown-menu">
                              <li><a class="dropdown-item" href="{{ route('users.edit',$user->id) }}">Edit</a></li>
                              <li>
                                {!! Form::open(['method' => 'DELETE','route' => ['users.destroy', $user->id],'style'=>'display:inline']) !!}
                                {!! Form::submit('Delete', ['class' => 'dropdown-item']) !!}
                                {!! Form::close() !!}
                              </li>
                            </ul>
                          </div>
                        </td>

                      </tr>
                 @endforeach

              </table>
            </div>

          </div>
        </div>
      </div>
</div>

{!! $data->render() !!}

@endsection
