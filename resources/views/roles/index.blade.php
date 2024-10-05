@extends('layouts.app')

@section('template_title')
     Roles
@endsection

@section('content')

<div class="container-fluid mt-3">
      <div class="row">
        <div class="col">
          <div class="card">
            <!-- Card header -->
            <div class="card-header">
              <h3 class="mb-3">Role Management</h3>
              <div style="display: flex; justify-content: space-between; align-items: center;">
              <a href="{{ route('dashboard') }}" class="btn" style="background: {{$configuracion->color_boton_close}}; color: #ffff; margin-right: 3rem;">
                Regresar
                </a>

                @can('role-create')
                     <a class="btn" href="{{ route('roles.create') }}" style="background: {{$configuracion->color_boton_add}}; color: #ffff">Create New Role</a>
                @endcan

              </div>
            </div>

            <div class="table-responsive py-4" style="">
              <table class="table table-flush table_id" id="datatable-basic" >
                  <thead class="thead-light">
                      <tr>
                       <th>No</th>
                       <th>Name</th>
                       <th width="280px">Action</th>
                     </tr>
                  </thead>

                 @foreach ($roles as $key => $role)
                      <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $role->name }}</td>

                        <td class="text-right">
                          <div class="dropdown ">
                            <button class="btn btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-ellipsis-v"></i>
                            </button>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-arrow">
                              <a class="dropdown-item" href="{{ route('roles.edit',$role->id) }}">
                                Edit
                              </a>
                            {!! Form::open(['method' => 'DELETE','route' => ['permisos.destroy', $role->id],'style'=>'display:inline']) !!}
                                {!! Form::submit('Delete', ['class' => 'dropdown-item']) !!}
                            {!! Form::close() !!}

                            </div>
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


@endsection
