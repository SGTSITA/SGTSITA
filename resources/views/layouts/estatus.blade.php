<!-- Modal -->
<div class="modal fade" id="estatusModal" tabindex="-1" role="dialog" aria-labelledby="edit-event-label" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Configuración de estatus</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" style="background: {{$configuracion->color_boton_close}}; color: #ffff">
                    <span aria-hidden="true">X</span>
                </button>
            </div>

                <div class="modal-body ">
                    <p>
                        <a class="btn btn-primary" data-bs-toggle="collapse" href="#collapseExample" role="button" aria-expanded="false" aria-controls="collapseExample">Agregar estatus</a>
                    </p>
                      <div class="collapse" id="collapseExample">
                        <div class="card card-body">
                            <form action="{{ route('estatus.create') }}" method="post" class="row"  enctype="multipart/form-data">
                                @csrf
                                <div class="form-group col-3">
                                    <label for="limpieza">Estatus</label>
                                    <input type="text" class="form-control" id="estatus" name="estatus">
                                </div>

                                <div class="form-group col-2">
                                    <label for="Color">Color</label>
                                    <input type="color" class="form-control" id="color" name="color">
                                </div>

                                <div class="form-group col-3">
                                    <label for="operatoria" style="opacity: 0;">-------</label>
                                    <button type="submit" class="btn btn-primary">Crear</button>
                                </div>
                            </form>
                            <hr>

                        </div>
                      </div>

                      <div class="row">
                        <div class="col-12">
                            @foreach ($estatus as $item)
                            <form action="{{ route('estatus.update_estatus',$item->id) }}" method="post" class="row"  enctype="multipart/form-data">
                              @csrf

                                <div class="form-group col-3">
                                    <label for="limpieza">estatus</label>
                                    <input type="hidden" class="form-control" id="id" name="id" value="{{$item->id}}">
                                    <input type="text" class="form-control" id="estatus" name="estatus" value="{{$item->estatus}}">
                                </div>

                                <div class="form-group col-2">
                                    <label for="Color">Color</label>
                                    <input type="color" class="form-control" id="color" name="color" value="{{$item->color}}">
                                </div>

                                <div class="form-group col-3">
                                    <label for="operatoria" style="opacity: 0;">-------</label>
                                    <button type="submit" class="btn btn-primary">guardar</button>
                                </div>
                               </form>
                            @endforeach
                        </div>
                    </div>

                </div>
                <hr>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary close-btn" data-dismiss="modal">cerrar</button>
                </div>

        </div>
    </div>
</div>


