<div class="modal fade" id="subclienteShowModal-{{$client->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-lg modal-dialog-centered">

      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Subclientes</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-3 mb-3">  <img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="20px"><strong>Nombre</strong></div>
                    <div class="col-2 mb-3">  <img src="{{ asset('img/icon/telefono.png.webp') }}" alt="" width="20px"><strong>Telefono</strong></div>
                    <div class="col-3 mb-3">  <img src="{{ asset('img/icon/sobre.png.webp') }}" alt="" width="20px"><strong>Correo</strong></div>
                    <div class="col-2 mb-3">  <img src="{{ asset('img/icon/gear.webp') }}" alt="" width="20px"><strong>RFC</strong></div>
                    <div class="col-2 mb-3">  <img src="{{ asset('img/icon/editar.webp') }}" alt="" width="20px"><strong>Accion</strong></div>

                    @foreach ($subclientes as $subcliente)
                        @if ($subcliente->id_cliente == $client->id)
                            <div class="col-3">{{$subcliente->nombre}}</div>
                            <div class="col-2">{{$subcliente->telefono}}</div>
                            <div class="col-3">{{$subcliente->correo}}</div>
                            <div class="col-2">{{$subcliente->rfc}}</div>
                            <div class="col-2">
                                <a class="btn btn-sm btn-warning" href="{{ route('edit_subclientes.clients',$subcliente->id) }}"><img src="{{ asset('img/icon/edit.png') }}" alt="" width="15px">Edita</a>
                            </div>
                        @endif
                    @endforeach

                </div>
            </div>

      </div>
    </div>
  </div>
