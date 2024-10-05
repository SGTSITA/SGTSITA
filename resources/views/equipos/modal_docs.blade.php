<div class="modal fade" id="documenotsdigitales-{{$item->id}}" tabindex="-1" aria-labelledby="equipoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Equipo #{{$item->id}} </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

        <form method="POST" action="{{ route('update.equipos', $item->id) }}" id="" enctype="multipart/form-data" role="form">
            <input type="hidden" name="_method" value="PATCH">

            @csrf

            <div class="modal-body">
                <div class="row">

                    <div class="col-12 mb-3 mt-3">
                        <h4 class="text-center">Documentos Digitales</h4>
                    </div>

                    <div class="col-6 text-center">
                        <h5>Tarjeta de Circulacion</h5>
                        @if($item->tarjeta_circulacion == NULL)

                        <label for="name">Tarjeta de Circulacion *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/business-card-design.webp') }}" alt="" width="25px">
                            </span>
                            <input name="tarjeta_circulacion" id="tarjeta_circulacion" type="file" class="form-control" value="{{$item->acceso}}">
                        </div>

                        @else

                        @if (pathinfo($item->tarjeta_circulacion, PATHINFO_EXTENSION) == 'pdf')
                        <p class="text-center ">
                            <iframe class="mt-2" src="{{asset('equipos/'.$item->tarjeta_circulacion)}}" style="width: 80%; height: 250px;"></iframe>
                        </p>
                                <a class="btn btn-sm text-dark" href="{{asset('equipos/'.$item->tarjeta_circulacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                        @elseif (pathinfo($item->tarjeta_circulacion, PATHINFO_EXTENSION) == 'doc')
                        <p class="text-center ">
                            <img id="blah" src="{{asset('assets/user/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                        </p>
                                <a class="btn btn-sm text-dark" href="{{asset('equipos/'.$item->tarjeta_circulacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                        @elseif (pathinfo($item->tarjeta_circulacion, PATHINFO_EXTENSION) == 'docx')
                        <p class="text-center ">
                            <img id="blah" src="{{asset('assets/user/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                        </p>
                                <a class="btn btn-sm text-dark" href="{{asset('equipos/'.$item->tarjeta_circulacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                        @else
                            <p class="text-center mt-2">
                                <img id="blah" src="{{asset('equipos/'.$item->tarjeta_circulacion) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                            </p>
                                <a class="text-center text-dark btn btn-sm" href="{{asset('equipos/'.$item->tarjeta_circulacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                        @endif

                        @endif
                    </div>

                    <div class="col-6 text-center">
                        <h5>Poliza de Seguro</h5>

                        @if($item->poliza_seguro == NULL)
                        <label for="name">Poliza de Seguro *</label>
                        <div class="input-group mb-3">
                            <span class="input-group-text" id="basic-addon1">
                                <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                            </span>
                            <input name="poliza_seguro" id="poliza_seguro" type="file" class="form-control">
                        </div>
                        @else

                            @if (pathinfo($item->poliza_seguro, PATHINFO_EXTENSION) == 'pdf')
                            <p class="text-center ">
                                <iframe class="mt-2" src="{{asset('equipos/'.$item->poliza_seguro)}}" style="width: 80%; height: 250px;"></iframe>
                            </p>
                                    <a class="btn btn-sm text-dark" href="{{asset('equipos/'.$item->poliza_seguro) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                            @elseif (pathinfo($item->poliza_seguro, PATHINFO_EXTENSION) == 'doc')
                            <p class="text-center ">
                                <img id="blah" src="{{asset('assets/user/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                            </p>
                                    <a class="btn btn-sm text-dark" href="{{asset('equipos/'.$item->poliza_seguro) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                            @elseif (pathinfo($item->poliza_seguro, PATHINFO_EXTENSION) == 'docx')
                            <p class="text-center ">
                                <img id="blah" src="{{asset('assets/user/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                            </p>
                                    <a class="btn btn-sm text-dark" href="{{asset('equipos/'.$item->poliza_seguro) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                            @else
                                <p class="text-center mt-2">
                                    <img id="blah" src="{{asset('equipos/'.$item->poliza_seguro) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                </p>
                                    <a class="text-center text-dark btn btn-sm" href="{{asset('equipos/'.$item->poliza_seguro) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                            @endif

                        @endif
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
