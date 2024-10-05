<div class="modal fade" id="exampleModalCenter{{$value->id}}" tabindex="-1" role="dialog" aria-labelledby="exampleModalCenterTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="exampleModalLongTitle">Editar permiso {{$value->name}}</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        {!! Form::open(array('route' => ['permisos.update', $value->id],'method'=>'PATCH')) !!}

            <div class="modal-body">
                    <div class="col-xs-12 col-sm-12 col-md-12">
                        <label for="">Permiso</label>
                        <input type="text" value="{{$value->name}}" class="form-control" name="name" id="name">
                    </div>
            </div>

            <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-dismiss="modal" style="background: {{$configuracion->color_boton_close}}; color: #ffff">Close</button>
            <button type="submit" class="btn" style="background: {{$configuracion->color_boton_save}}; color: #ffff">Save changes</button>
            </div>

        {!! Form::close() !!}

      </div>
    </div>
  </div>
