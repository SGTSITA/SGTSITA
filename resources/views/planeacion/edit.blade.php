<div class="modal fade" id="planeacionModal{{$cotizacion->id}}" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog  modal-lg">
      <div class="modal-content">

        <div class="modal-header">
          <h5 class="modal-title">Planeacion</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>

            <form method="POST" action="{{ route('asignacion.planeaciones') }}" id="miFormulario{{$cotizacion->id}}" enctype="multipart/form-data" role="form">

                @csrf
                <div class="modal-body">
                    <div class="row">

                        <input name="num_contenedor" value="{{$cotizacion->DocCotizacion->id}}" type="hidden">
                        <input name="cotizacion" value="{{$cotizacion->DocCotizacion->id_cotizacion}}" type="hidden">

                        <div class="col-6 form-group">
                            <label for="name">Num. Contenedor</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px">
                                </span>
                                <input id="num_contenedor" value="{{$cotizacion->DocCotizacion->num_contenedor}}" type="text" class="form-control" readonly>
                            </div>
                        </div>

                        <div class="col-6 form-group">
                            <label for="name">Num. autorización</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/persona-clave.png') }}" alt="" width="25px">
                                </span>
                                <input id="num_contenedor" value="{{$cotizacion->DocCotizacion->num_autorizacion}}" type="text" class="form-control" readonly>
                            </div>
                        </div>

                        @if ($cotizacion->id_subcliente != NULL)
                            <div class="col-12 form-group">
                                <label for="name">Subcliente *</label>
                                <input id="num_contenedor" value="{{$cotizacion->Subcliente->nombre}} / {{$cotizacion->Subcliente->telefono}}" type="text" class="form-control" readonly>
                            </div>
                        @endif

                        <div class="col-12 form-group">
                            <label for="name">Selecione Unidad</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">
                                    <img src="{{ asset('img/icon/camion.png') }}" alt="" width="25px">
                                </span>
                                <select class="form-select d-inline-block" id="viaje{{$cotizacion->id}}" name="viaje" value="{{ old('viaje') }}" onchange="mostrarDiv('{{$cotizacion->id}}')">
                                    <option>Seleccionar tipo</option>
                                    <option value="Camion Propio">Camion Propio</option>
                                    <option value="Camion Subcontratado">Camion Subcontratado</option>
                                </select>
                            </div>
                        </div>

                        <div id="camionPropioDiv{{$cotizacion->id}}" style="display: none;">
                            <div class="row">

                                <div class="col-12 form-group">
                                    <label for="name">Selecione el tipo de Unidad</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/camion.webp') }}" alt="" width="25px">
                                        </span>
                                        <select class="form-select d-inline-block" id="tipo{{$cotizacion->id}}" name="tipo"  value="{{ old('tipo') }}" onchange="mostrarDiv('{{$cotizacion->id}}')">
                                            <option>Seleccionar Opcion</option>
                                            <option  value="Sencillo">Sencillo</option>
                                            <option  value="Full">Full</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="col-12 form-group">
                                    <label for="name">Fecha inicio</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                        </span>
                                        <input name="fecha_inicio" id="fecha_inicio_{{$cotizacion->id}}" type="date" class="form-control" value="{{ date('Y-m-d')}}">
                                    </div>
                                </div>


                                <div class="col-12 form-group">
                                    <label for="name">Fecha fin</label>
                                    <div class="input-group">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                        </span>
                                        <input name="fecha_fin" id="fecha_fin_{{$cotizacion->id}}" type="date" class="form-control" value="{{ date('Y-m-d')}}">
                                    </div>
                                </div>

                                <div class="col-12 form-group">
                                    <label for="name">.</label>
                                    <div class="input-group mb-3">
                                        <button class="btn" type="button" id="btn_clientes_search{{$cotizacion->id}}" data-cotizacion-id="{{$cotizacion->id}}" style="">
                                            Buscar Disponibilidad
                                        </button>

                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group col-12 col-sm-4 col-md-4 col-lg-3 col-xl-3 px-4 w-100">
                            <div class="d-flex justify-content-center">
                                <div class="spinner-border" role="status" id="loadingSpinner" style="display:none">
                                     <span class="visually-hidden">Loading...</span>
                                 </div>
                             </div>
                         </div>

                            <div id="resultado_equipos{{ $cotizacion->id }}" class="row"></div>


                        <div id="camionSubcontratadoDiv{{$cotizacion->id}}" style="display: none;">
                            <div class="col-12 form-group">
                                <label for="name">Fecha inicio</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                    </span>
                                    <input name="fecha_inicio_proveedor" type="date" class="form-control" value="{{ date('Y-m-d')}}">
                                </div>
                            </div>


                            <div class="col-12 form-group">
                                <label for="name">Fecha fin</label>
                                <div class="input-group">
                                    <span class="input-group-text" id="basic-addon1">
                                        <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                    </span>
                                    <input name="fecha_fin_proveedor" type="date" class="form-control" value="{{ date('Y-m-d')}}">
                                </div>
                            </div>

                            <div class="col-12 form-group">
                                <label for="name">Proveedor</label>
                                <select class="form-select d-inline-block" id="id_proveedor" name="id_proveedor" value="{{ old('id_proveedor') }}">
                                    <option  value="">Seleccionar Proveedor</option>
                                    @foreach ($proveedores as $item)
                                        <option value="{{$item->id}}">{{$item->nombre}}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="row">
                                <div class="col-3 form-group">
                                    <label for="name">Costo viaje</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                        </span>
                                        <input name="precio_proveedor" id="precio_proveedor_{{$cotizacion->id}}" type="number" class="form-control fieldsCalculo" value="0">
                                    </div>
                                </div>

                                <div class="col-3 form-group">
                                    <label for="name">Burreo</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/burro.png') }}" alt="" width="25px">
                                        </span>
                                        <input name="burreo_proveedor" id="burreo_proveedor_{{$cotizacion->id}}" type="float" class="form-control fieldsCalculo">
                                    </div>
                                </div>

                                <div class="col-3 form-group">
                                    <label for="name">Maniobra</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/logistica.png') }}" alt="" width="25px">
                                        </span>
                                        <input name="maniobra_proveedor" id="maniobra_proveedor_{{$cotizacion->id}}" type="float" class="form-control fieldsCalculo">
                                    </div>
                                </div>

                                <div class="col-3 form-group">
                                    <label for="name">Estadia</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/servidor-en-la-nube.png') }}" alt="" width="25px">
                                        </span>
                                        <input name="estadia_proveedor" id="estadia_proveedor_{{$cotizacion->id}}" type="float" class="form-control fieldsCalculo">
                                    </div>
                                </div>

                                <div class="col-4 form-group">
                                    <label for="name">Otros</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/inventario.png.webp') }}" alt="" width="25px">
                                        </span>
                                        <input name="otro_proveedor" id="otro_proveedor_{{$cotizacion->id}}" type="float" class="form-control fieldsCalculo">
                                    </div>
                                </div>

                                <div class="col-4 form-group">
                                    <label for="name">IVA</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/impuesto.png') }}" alt="" width="25px">
                                        </span>
                                        <input name="iva_proveedor" id="iva_proveedor_{{$cotizacion->id}}" readonly type="number" class="form-control">
                                    </div>
                                </div>

                                <div class="col-4 form-group">
                                    <label for="name">Retención</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                        </span>
                                        <input name="retencion_proveedor" id="retencion_proveedor_{{$cotizacion->id}}" readonly type="float" class="form-control">
                                    </div>
                                </div>

                                <div class="col-6 form-group">
                                    <label for="name">Base 1</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/factura.png') }}" alt="" width="25px">
                                        </span>
                                        <input name="base_factura" id="base_factura_{{$cotizacion->id}}" type="float" class="form-control fieldsCalculo">
                                    </div>
                                </div>

                                <div class="col-6 form-group">
                                    <label for="name">Base 2</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/factura.png.webp') }}" alt="" width="25px">
                                        </span>
                                        <input name="base_taref" id="base_taref_{{$cotizacion->id}}" type="float" class="form-control">
                                    </div>
                                </div>

                                <div class="col-4 form-group">
                                    <label for="name">Sobrepeso</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/tonelada.png') }}" alt="" width="25px">
                                        </span>
                                        <input name="cantidad_sobrepeso_proveedor" id="cantidad_sobrepeso_proveedor_{{$cotizacion->id}}" type="float" class="form-control" @if($cotizacion->peso_contenedor > $cotizacion->peso_reglamentario) value="{{$cotizacion->peso_contenedor - $cotizacion->peso_reglamentario}} @else value="0" @endif" disabled>
                                    </div>
                                </div>

                                <div class="col-4 form-group">
                                    <label for="name">Precio Sobre Peso</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/pago-en-efectivo.png') }}" alt="" width="25px">
                                        </span>
                                        <input name="sobrepeso_proveedor" id="sobrepeso_proveedor_{{$cotizacion->id}}" type="float" class="form-control fieldsCalculo">
                                    </div>
                                </div>

                                <div class="col-4 form-group">
                                    <label for="name">Total</label>
                                    <div class="input-group mb-3">
                                        <span class="input-group-text" id="basic-addon1">
                                            <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                        </span>
                                        <input name="total_proveedor" id="total_proveedor_{{$cotizacion->id}}" type="number" class="form-control" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"  data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btnEnviar" id="btnEnviar{{$cotizacion->id}}">Guardar</button>
                </div>
            </form>

      </div>
    </div>
</div>



