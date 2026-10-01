<!-- Modal -->
<div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h1 class="modal-title fs-5" id="exampleModalLabel">Modal title</h1>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            {!! Form::open(['route' => 'permisos.store', 'method' => 'POST']) !!}

            <div class="modal-body">
                <div class="col-xs-12 col-sm-12 col-md-12">
                    <label for="">Permiso</label>
                    <input type="text" placeholder="Permiso" class="form-control" name="name" id="name" />
                </div>
            </div>

            <div class="modal-footer">
                <button
                    type="button"
                    class="btn"
                    data-dismiss="modal"
                    style="background: {{ $configuracion->color_boton_close }}; color: #ffff"
                >
                    Close
                </button>
                <button
                    type="submit"
                    class="btn"
                    style="background: {{ $configuracion->color_boton_save }}; color: #ffff"
                >
                    Save changes
                </button>
            </div>

            {!! Form::close() !!}
        </div>
    </div>
</div>
