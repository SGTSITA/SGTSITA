<div class="modal fade" id="equipoEditModal-{{$item->id}}" tabindex="-1" aria-labelledby="equipoEditModal-{{$item->id}}Label" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Crear Operadores</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <form method="POST" action="{{ route('update.tenants',$item) }}" id="" enctype="multipart/form-data" role="form">
            @csrf
            @method('PUT')

            <div class="modal-body">
                <div class="row">

                    <div class="col-12 form-group">
                        <label for="name">Nombre*</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">
                            </span>
                            <input name="id" id="id" type="text" class="form-control" value="{{  $item->id }}">
                        </div>
                    </div>

                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary">Guardar</button>
              </div>
        </form>
      </div>
    </div>
  </div>
