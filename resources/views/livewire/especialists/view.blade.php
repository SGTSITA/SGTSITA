@section('title', __('Especialists'))

<div class="container-fluid">
	<div class="row justify-content-center">
		<div class="col-md-12">
			<div class="card">
				<div class="card-header">
					<div style="display: flex; justify-content: space-between; align-items: center;">
						<div class="float-left">
							<h4><i class="fab fa-laravel text-info"></i>
							Especialist Listing </h4>
						</div>
						@if (session()->has('message'))
						<div wire:poll.4s class="btn btn-sm btn-success" style="margin-top:0px; margin-bottom:0px;"> {{ session('message') }} </div>
						@endif
						<div>
							<input wire:model='keyWord' type="text" class="form-control" name="search" id="search" placeholder="Search Especialists">
						</div>
						<div class="btn btn-sm" data-toggle="modal" data-target="#createDataModal" style="background: {{$configuracion->color_boton_add}}; color: #ffff">
						<i class="fa fa-plus"></i>  Add Especialists
						</div>
					</div>
				</div>

				<div class="card-body">
						@include('livewire.especialists.create')
						@include('livewire.especialists.update')
                <div class="table-responsive py-4" style="">
                    <table class="table table-flush" id="datatable-basic" >
						<thead class="thead">
							<tr>
								<td>#</td>
								<th>Nombre</th>
								<th>Apellido</th>
								<th>Cedula</th>
								<th>Especialidad</th>
								<th>Telefono</th>
								<th>Fecha Nacimiento</th>
								<th>Email</th>
								<td>ACTIONS</td>
							</tr>
						</thead>
						<tbody>
							@foreach($especialists as $row)
							<tr>
								<td>{{ $loop->iteration }}</td>
								<td>{{ $row->nombre }}</td>
								<td>{{ $row->apellido }}</td>
								<td>{{ $row->cedula }}</td>
								<td>{{ $row->especialidad }}</td>
								<td>{{ $row->telefono }}</td>
								<td>{{ $row->fecha_nacimiento }}</td>
								<td>{{ $row->email }}</td>
								<td width="90">
								<div class="btn-group">
									<button type="button" class="btn btn-sm dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="background: {{$configuracion->color_boton_add}}; color: #ffff">
									Actions
									</button>
									<div class="dropdown-menu dropdown-menu-right">
									<a data-toggle="modal" data-target="#updateModal" class="dropdown-item" wire:click="edit({{$row->id}})"><i class="fa fa-edit"></i> Edit </a>
									<a class="dropdown-item" onclick="confirm('Confirm Delete Especialist id {{$row->id}}? \nDeleted Especialists cannot be recovered!')||event.stopImmediatePropagation()" wire:click="destroy({{$row->id}})"><i class="fa fa-trash"></i> Delete </a>
									</div>
								</div>
								</td>
							@endforeach
						</tbody>
					</table>
					{{ $especialists->links() }}
					</div>
				</div>
			</div>
		</div>
	</div>
</div>
