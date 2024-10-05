<!-- Modal -->
<div wire:ignore.self class="modal fade" id="updateModal" data-backdrop="static" tabindex="-1" role="dialog" aria-labelledby="updateModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
       <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="updateModalLabel">Update Especialist</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span wire:click.prevent="cancel()" aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">
                <form>
					<input type="hidden" wire:model="selected_id">
            <div class="form-group">
                <label for="nombre"></label>
                <input wire:model="nombre" type="text" class="form-control" id="nombre" placeholder="Nombre">@error('nombre') <span class="error text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="apellido"></label>
                <input wire:model="apellido" type="text" class="form-control" id="apellido" placeholder="Apellido">@error('apellido') <span class="error text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="cedula"></label>
                <input wire:model="cedula" type="text" class="form-control" id="cedula" placeholder="Cedula">@error('cedula') <span class="error text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="especialidad"></label>
                <input wire:model="especialidad" type="text" class="form-control" id="especialidad" placeholder="Especialidad">@error('especialidad') <span class="error text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="telefono"></label>
                <input wire:model="telefono" type="text" class="form-control" id="telefono" placeholder="Telefono">@error('telefono') <span class="error text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="fecha_nacimiento"></label>
                <input wire:model="fecha_nacimiento" type="text" class="form-control" id="fecha_nacimiento" placeholder="Fecha Nacimiento">@error('fecha_nacimiento') <span class="error text-danger">{{ $message }}</span> @enderror
            </div>
            <div class="form-group">
                <label for="email"></label>
                <input wire:model="email" type="text" class="form-control" id="email" placeholder="Email">@error('email') <span class="error text-danger">{{ $message }}</span> @enderror
            </div>

                </form>
            </div>
            <div class="modal-footer">
                <button type="button" wire:click.prevent="cancel()" class="btn" data-dismiss="modal" style="background: {{$configuracion->color_boton_close}}; color: #ffff">Close</button>
                <button type="button" wire:click.prevent="update()" class="btn" data-dismiss="modal" style="background: {{$configuracion->color_boton_save}}; color: #ffff">Save</button>
            </div>
       </div>
    </div>
</div>
