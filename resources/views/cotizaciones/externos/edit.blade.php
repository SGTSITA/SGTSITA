@extends('layouts.app')

@section('template_title')
   Editar Cotizacion
@endsection

@section('content')

    <div class="contaboleta_liberacionr-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <a class="btn"  href="{{ route('index.cotizaciones_manual') }}" style="background: {{$configuracion->color_boton_close}}; color: #ffff;margin-right: 3rem;">
                                Regresar
                            </a>
                        </div>
                    </div>

                    <div class="card-body">

                        <nav class="mx-auto">
                            <div class="nav nav-tabs custom-tabs" id="nav-tab" role="tablist">
                            <button class="nav-link custom-tab active" id="nav-cotizacion-tab" data-bs-toggle="tab" data-bs-target="#nav-cotizacion" type="button" role="tab" aria-controls="nav-planeadas" aria-selected="false">
                                <img src="{{ asset('img/icon/validando-billete.webp') }}" alt="" width="40px"> Cotización
                            </button>


                              <button class="nav-link custom-tab" id="nav-Documentacion-tab" data-bs-toggle="tab" data-bs-target="#nav-Documentacion" type="button" role="tab" aria-controls="nav-Documentacion" aria-selected="false">
                                <img src="{{ asset('img/icon/pdf.webp') }}" alt="" width="40px"> Documentación
                              </button>

                            </div>
                        </nav>


                        <form method="POST" action="{{ route('update.cotizaciones', $cotizacion->id) }}" id="" enctype="multipart/form-data" role="form">
                            @csrf
                            <input type="hidden" name="_method" value="PATCH">
                            <input name="id_cliente_clientes" id="id_cliente_clientes" type="hidden" class="form-control" value="{{auth()->user()->id_cliente}}">

                            <div class="tab-content" id="nav-tabContent">
                                <div class="tab-pane fade show active" id="nav-cotizacion" role="tabpanel" aria-labelledby="nav-cotizacion-tab" tabindex="0">
                                    <h3 class="mb-5">Datos de cotizacion</h3>

                                    @error('num_contenedor') <h4 class="error text-danger">{{ $message }}</h4> @enderror


                                    <div class="row">
                                            @if ($documentacion->num_contenedor != NULL)
                                                <label style="font-size: 20px;">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                            @endif

                                            <div class="col-6 form-group">
                                                <label for="name">Cliente *</label>
                                                <select class="form-select cliente d-inline-block" data-toggle="select" id="id_cliente" name="id_cliente">
                                                    <option value="{{ $cotizacion->id_cliente }}">{{ $cotizacion->Cliente->nombre }} / {{ $cotizacion->Cliente->telefono }}</option>
                                                </select>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Subcliente *</label>
                                                <select class="form-select subcliente d-inline-block" id="id_subcliente" name="id_subcliente">
                                                    @if ($cotizacion->id_subcliente != NULL)
                                                        <option value="{{ $cotizacion->id_subcliente }}">{{ $cotizacion->Subcliente->nombre }} / {{ $cotizacion->Subcliente->telefono }}</option>
                                                    @else
                                                        <option value="">Seleccionar Subcliente</option>
                                                    @endif
                                                        @foreach ($subclientes as $item)
                                                            <option value="{{ $item->id }}">{{ $item->nombre }} / {{ $item->telefono }}</option>
                                                        @endforeach
                                                </select>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Origen</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_origen" id="cot_origen" type="text" class="form-control" value="{{$cotizacion->origen}}">
                                                </div>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Destino</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_destino" id="cot_destino" type="text" class="form-control" value="{{$cotizacion->destino}}">
                                                </div>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Fecha modulación</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_fecha_modulacion" id="cot_fecha_modulacion" type="date" class="form-control" value="{{$cotizacion->fecha_modulacion}}">
                                                </div>
                                            </div>

                                            <div class="col-6 form-group">
                                                <label for="name">Fecha entrega</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendar-dar.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="cot_fecha_entrega" id="cot_fecha_entrega" type="date" class="form-control" value="{{$cotizacion->fecha_entrega}}">
                                                </div>
                                            </div>

                                    </div>
                                </div>

                                <div class="tab-pane fade" id="nav-Documentacion" role="tabpanel" aria-labelledby="nav-Documentacion-tab" tabindex="0">
                                    <h3 class="mt-3 mb-5">Documentación</h3>
                                    @if ($documentacion->num_contenedor != NULL)
                                        <label style="font-size: 20px;">Num contenedor:  {{$documentacion->num_contenedor}} </label>
                                    @endif
                                    <div class="row">
                                        <div class="col-6 form-group">
                                            <label for="name">Num. Boleta de Liberación</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/9.webp') }}" alt="" width="25px">
                                                </span>
                                                <input name="num_boleta_liberacion" id="num_boleta_liberacion" type="text" class="form-control" value="{{$documentacion->num_boleta_liberacion}}">
                                            </div>
                                        </div>

                                        <div class="col-6 form-group">
                                            <label for="name">Boleta de Liberación</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/boleto.png') }}" alt="" width="25px">
                                                </span>
                                                <input name="boleta_liberacion" id="boleta_liberacion" type="file" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-6"></div>
                                        <div class="col-6">
                                            @if (pathinfo($documentacion->boleta_liberacion, PATHINFO_EXTENSION) == 'pdf')
                                            <p class="text-center ">
                                                <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion)}}" style="width: 100%; height: 100px;"></iframe>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                            @elseif (pathinfo($documentacion->boleta_liberacion, PATHINFO_EXTENSION) == 'doc')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @elseif (pathinfo($documentacion->boleta_liberacion, PATHINFO_EXTENSION) == 'docx')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @else
                                                <p class="text-center mt-2">
                                                    <img id="blah" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                </p>
                                                    <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->boleta_liberacion) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                                            @endif
                                        </div>

                                        <div class="col-6 form-group">
                                            <label for="name">Num. Doda</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/cero.webp') }}" alt="" width="25px">
                                                </span>
                                                <input name="num_doda" id="num_doda" type="text" class="form-control" value="{{$documentacion->num_doda}}">
                                            </div>
                                        </div>

                                        <div class="col-6 form-group">
                                            <label for="name">Doda</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/documento.png') }}" alt="" width="25px">
                                                </span>
                                                <input name="doda" id="doda" type="file" class="form-control">
                                            </div>
                                        </div>

                                        <div class="col-6"></div>
                                        <div class="col-6">
                                            @if (pathinfo($documentacion->doda, PATHINFO_EXTENSION) == 'pdf')
                                            <p class="text-center ">
                                                <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doda)}}" style="width: 100%; height: 100px;"></iframe>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doda) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                            @elseif (pathinfo($documentacion->doda, PATHINFO_EXTENSION) == 'doc')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doda) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @elseif (pathinfo($documentacion->doda, PATHINFO_EXTENSION) == 'docx')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doda) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @else
                                                <p class="text-center mt-2">
                                                    <img id="blah" src="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doda) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                </p>
                                                    <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$documentacion->doda) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                                            @endif
                                        </div>

                                        <div class="col-6 form-group">
                                            <label for="name">Num. Carta Porte</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/9.webp') }}" alt="" width="25px">
                                                </span>
                                                <input type="text" class="form-control" value="{{$documentacion->num_carta_porte}}" readonly>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <label for="name">Carta Porte</label>
                                            @if (pathinfo($cotizacion->carta_porte, PATHINFO_EXTENSION) == 'pdf')
                                            <p class="text-center ">
                                                <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte)}}" style="width: 100%; height: 100px;"></iframe>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                            @elseif (pathinfo($cotizacion->carta_porte, PATHINFO_EXTENSION) == 'doc')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @elseif (pathinfo($cotizacion->carta_porte, PATHINFO_EXTENSION) == 'docx')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            {{-- @else
                                                <p class="text-center mt-2">
                                                    <img id="blah" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                </p>
                                                    <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->carta_porte) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a> --}}
                                            @endif
                                        </div>

                                        <div class="col-2">
                                            <div class="form-group">
                                                <label>¿Prealta?</label><br>
                                                @if ($documentacion->boleta_vacio == 'si')
                                                    <input class="form-check-input" type="radio" name="boleta_vacio" value="si" id="option_si" checked> Sí<br>
                                                    <input class="form-check-input" type="radio" name="boleta_vacio" value="no" id="option_no"> No
                                                @else
                                                    <input class="form-check-input" type="radio" name="boleta_vacio" value="si" id="option_si"> Sí<br>
                                                    <input class="form-check-input" type="radio" name="boleta_vacio" value="no" id="option_no" checked> No
                                                @endif
                                            </div>
                                        </div>

                                        <div class="col-4">
                                            @if ($documentacion->boleta_vacio == 'si')
                                                <div class="form-group" id="inputField">
                                            @else
                                                <div class="form-group" id="inputField" style="display: none;">
                                            @endif
                                                <label for="input">Fecha Boleta Vacio:</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="fecha_boleta_vacio" id="fecha_boleta_vacio" type="date" class="form-control" value="{{$documentacion->fecha_boleta_vacio}}">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            @if ($documentacion->boleta_vacio == 'si')
                                                <div class="form-group" id="inputFieldIMG">
                                            @else
                                                <div class="form-group" id="inputFieldIMG" style="display: none;">
                                            @endif
                                                <label for="input">IMG Boleta Vacio:</label>
                                                <div class="input-group mb-3">
                                                    <span class="input-group-text" id="basic-addon1">
                                                        <img src="{{ asset('img/icon/calendario.webp') }}" alt="" width="25px">
                                                    </span>
                                                    <input name="img_boleta" id="img_boleta" type="file" class="form-control">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            @if (pathinfo($cotizacion->img_boleta, PATHINFO_EXTENSION) == 'pdf')
                                            <p class="text-center ">
                                                <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta)}}" style="width: 100%; height: 50px;"></iframe>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                            @elseif (pathinfo($cotizacion->img_boleta, PATHINFO_EXTENSION) == 'doc')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @elseif (pathinfo($cotizacion->img_boleta, PATHINFO_EXTENSION) == 'docx')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @else
                                                <p class="text-center mt-2">
                                                    <img id="blah" src="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                </p>
                                                    <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $cotizacion->id . '/' .$cotizacion->img_boleta) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a>
                                            @endif
                                        </div>

                                        <div class="col-6"></div>

                                        <div class="col-6">
                                            <label for="">Doc EIR</label>
                                            @if (pathinfo($documentacion->doc_eir, PATHINFO_EXTENSION) == 'pdf')
                                            <p class="text-center ">
                                                <iframe class="mt-2" src="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir)}}" style="width: 100%; height: 50px;"></iframe>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver archivo</a>
                                            @elseif (pathinfo($documentacion->doc_eir, PATHINFO_EXTENSION) == 'doc')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            @elseif (pathinfo($documentacion->doc_eir, PATHINFO_EXTENSION) == 'docx')
                                            <p class="text-center ">
                                                <img id="blah" src="{{asset('assets/icons/docx.png') }}" alt="Imagen" style="width: 150px; height: 150px;"/>
                                            </p>
                                                    <a class="btn btn-sm text-dark" href="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir) }}" target="_blank" style="background: #836262; color: #ffff!important">Descargar</a>
                                            {{-- @else
                                                <p class="text-center mt-2">
                                                    <img id="blah" src="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir) }}" alt="Imagen" style="width: 150px;height: 150%;"/><br>
                                                </p>
                                                    <a class="text-center text-dark btn btn-sm" href="{{asset('cotizaciones/cotizacion'. $documentacion->id . '/' .$documentacion->doc_eir) }}" target="_blank" style="background: #836262; color: #ffff!important">Ver Imagen</a> --}}
                                            @endif
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="modal-footer">
                                <button type="submit" class="btn btn-primary">Guardar</button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('select2')
    <script src="{{ asset('assets/vendor/jquery/dist/jquery.min.js')}}"></script>
    <script src="{{ asset('assets/vendor/select2/dist/js/select2.min.js')}}"></script>

    <script type="text/javascript">
    $(document).ready(function() {
    $('.cliente').select2();
    });
    </script>

    <script type="text/javascript">
        // ============= Agregar mas inputs dinamicamente =============
        $('.clonar').click(function() {
        // Clona el .input-group
        var $clone = $('#formulario .clonars').last().clone();

        // Borra los valores de los inputs clonados
        $clone.find(':input').each(function () {
            if ($(this).is('select')) {
            this.selectedIndex = 0;
            } else {
            this.value = '';
            }
        });

        // Agrega lo clonado al final del #formulario
        $clone.appendTo('#formulario');
        });

        function calcularTotal() {
            const precio_viaje = parseFloat(document.getElementById('cot_precio_viaje').value.replace(/,/g, '')) || 0;
            const burreo = parseFloat(document.getElementById('cot_burreo').value.replace(/,/g, '')) || 0;
            const retencion = parseFloat(document.getElementById('cot_retencion').value.replace(/,/g, '')) || 0;
            const iva = parseFloat(document.getElementById('cot_iva').value.replace(/,/g, '')) || 0;
            const otro = parseFloat(document.getElementById('cot_otro').value.replace(/,/g, '')) || 0;
            const estadia = parseFloat(document.getElementById('cot_estadia').value.replace(/,/g, '')) || 0;
            const maniobra = parseFloat(document.getElementById('cot_maniobra').value.replace(/,/g, '')) || 0;

            // Restar el valor de Retención del total
            const totalSinRetencion = precio_viaje + burreo + iva + otro + estadia + maniobra;
            const totalConRetencion = totalSinRetencion - retencion;

            // Obtener el valor de Precio Tonelada
            const precioTonelada = parseFloat(document.getElementById('precio_tonelada').value.replace(/,/g, '')) || 0;

            // Sumar el valor de Precio Tonelada al total
            const totalFinal = totalConRetencion + precioTonelada;

            // Formatear el total con comas
            const totalFormateado = totalFinal.toLocaleString('en-US');

            document.getElementById('total').value = totalFormateado;
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Obtener elementos del DOM
            var pesoReglamentarioInput = document.getElementById('peso_reglamentario');
            var pesoContenedorInput = document.getElementById('cot_peso_contenedor');
            var sobrepesoInput = document.getElementById('sobrepeso');
            var precioSobrePesoInput = document.getElementById('precio_sobre_peso');
            var precioToneladaInput = document.getElementById('precio_tonelada');

            // Agregar evento de cambio a los inputs
            pesoReglamentarioInput.addEventListener('input', calcularSobrepeso);
            pesoContenedorInput.addEventListener('input', calcularSobrepeso);

            // Función para calcular el sobrepeso
            function calcularSobrepeso() {
                var pesoReglamentario = parseFloat(pesoReglamentarioInput.value) || 0;
                var pesoContenedor = parseFloat(pesoContenedorInput.value) || 0;

                // Calcular sobrepeso
                var sobrepeso = Math.max(pesoContenedor - pesoReglamentario, 0);

                // Mostrar sobrepeso en el input correspondiente con dos decimales
                sobrepesoInput.value = sobrepeso.toFixed(2);

                // Obtener el valor de Precio Sobre Peso
                var precioSobrePeso = parseFloat(precioSobrePesoInput.value.replace(/,/g, '')) || 0;

                // Calcular el resultado de la multiplicación
                var resultado = sobrepeso * precioSobrePeso;

                // Mostrar el resultado en el campo "Precio Tonelada"
                precioToneladaInput.value = resultado.toLocaleString('en-US');

                // Calcular el total
                calcularTotal();
            }

            // Agregar evento de entrada al campo "Precio Sobre Peso"
            precioSobrePesoInput.addEventListener('input', function () {
                // Obtener el valor de Precio Sobre Peso
                var precioSobrePeso = parseFloat(precioSobrePesoInput.value.replace(/,/g, '')) || 0;

                // Calcular el resultado de la multiplicación
                var resultado = parseFloat(sobrepesoInput.value) * precioSobrePeso;

                // Mostrar el resultado en el campo "Precio Tonelada"
                precioToneladaInput.value = resultado.toLocaleString('en-US');

                // Calcular el total
                calcularTotal();
            });

            // Calcular sobrepeso inicialmente al cargar la página
            calcularSobrepeso();

            // Función para calcular base_taref
            function calcularBaseTaref() {
                // Obtener los valores de los inputs
                const total = parseFloat(document.getElementById('total').value.replace(/,/g, '')) || 0;
                const baseFactura = parseFloat(document.getElementById('base_factura').value.replace(/,/g, '')) || 0;
                const iva = parseFloat(document.getElementById('cot_iva').value.replace(/,/g, '')) || 0;
                const retencion = parseFloat(document.getElementById('cot_retencion').value.replace(/,/g, '')) || 0;

                // Realizar el cálculo
                const baseTaref = (total - baseFactura - iva) + retencion;

                // Mostrar el resultado en el input de base_taref
                document.getElementById('base_taref').value = baseTaref.toFixed(2);
            }

            // Agregar eventos de cambio a los inputs para calcular automáticamente
            document.getElementById('total').addEventListener('input', calcularBaseTaref);
            document.getElementById('base_factura').addEventListener('input', calcularBaseTaref);
            document.getElementById('cot_iva').addEventListener('input', calcularBaseTaref);
            document.getElementById('cot_retencion').addEventListener('input', calcularBaseTaref);
        });
    </script>

    <script>
        // ============= Agregar mas inputs dinamicamente =============
        $('.clonar2').click(function() {
        // Clona el .input-group
        var $clone = $('#formulario2 .clonars2').last().clone();

        // Borra los valores de los inputs clonados
        $clone.find(':input').each(function () {
            if ($(this).is('select')) {
            this.selectedIndex = 0;
            } else {
            this.value = '';
            }
        });

        // Agrega lo clonado al final del #formulario2
        $clone.appendTo('#formulario2');
        });
    </script>

    <script>
         document.addEventListener('DOMContentLoaded', function () {
            // Obtener referencias a los elementos
            var optionSi = document.getElementById('option_si_ccp');
            var optionNo = document.getElementById('option_no_ccp');
            var inputFieldIMG = document.getElementById('inputFieldccp');

            // Función para controlar la visibilidad del campo de entrada
            function toggleInputField() {
                // Si el radio button "Sí" está seleccionado, mostrar el campo de entrada
                if (optionSi.checked) {
                    inputFieldIMG.style.display = 'block';
                } else {
                    inputFieldIMG.style.display = 'none';
                }
            }

            // Agregar eventos change a los radio buttons
            optionSi.addEventListener('change', toggleInputField);
            optionNo.addEventListener('change', toggleInputField);

            // Llamar a la función inicialmente para asegurarse de que el campo se oculte o muestre correctamente
            toggleInputField();
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Obtener referencias a los elementos
            var optionSi = document.getElementById('option_si');
            var optionNo = document.getElementById('option_no');
            var inputField = document.getElementById('inputField');
            var inputFieldIMG = document.getElementById('inputFieldIMG');

            // Función para controlar la visibilidad del campo de entrada
            function toggleInputField() {
                // Si el radio button "Sí" está seleccionado, mostrar el campo de entrada
                if (optionSi.checked) {
                    inputField.style.display = 'block';
                    inputFieldIMG.style.display = 'block';
                } else {
                    inputField.style.display = 'none';
                    inputFieldIMG.style.display = 'none';
                }
            }

            // Agregar eventos change a los radio buttons
            optionSi.addEventListener('change', toggleInputField);
            optionNo.addEventListener('change', toggleInputField);

            // Llamar a la función inicialmente para asegurarse de que el campo se oculte o muestre correctamente
            toggleInputField();
        });

        document.addEventListener('DOMContentLoaded', function () {
            // Obtener referencias a los elementos
            var eirSi = document.getElementById('eir_si');
            var eirNo = document.getElementById('eir_no');
            var inputEir = document.getElementById('inputEir');
            var inputEirFecha = document.getElementById('inputEirFecha');

            // Función para controlar la visibilidad del campo de entrada
            function toggleInputEir() {
                // Si el radio button "Sí" está seleccionado, mostrar el campo de entrada
                if (eirSi.checked) {
                    inputEir.style.display = 'block';
                    inputEirFecha.style.display = 'block';
                } else {
                    inputEir.style.display = 'none';
                    inputEirFecha.style.display = 'none';
                }
            }

            // Agregar eventos change a los radio buttons
            eirSi.addEventListener('change', toggleInputEir);
            eirNo.addEventListener('change', toggleInputEir);

            // Llamar a la función inicialmente para asegurarse de que el campo se oculte o muestre correctamente
            toggleInputEir();
        });

        $(document).ready(function() {
            function loadSubclientes(clienteId, selectedSubclienteId = null) {
                if (clienteId) {
                    $.ajax({
                        type: 'GET',
                        url: '/subclientes/' + clienteId,
                        success: function(data) {
                            $('#id_subcliente').empty();
                            $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
                            $.each(data, function(key, subcliente) {
                                if (selectedSubclienteId && selectedSubclienteId == subcliente.id) {
                                    $('#id_subcliente').append('<option value="' + subcliente.id + '" selected>' + subcliente.nombre + '</option>');
                                } else {
                                    $('#id_subcliente').append('<option value="' + subcliente.id + '">' + subcliente.nombre + '</option>');
                                }
                            });
                        }
                    });
                } else {
                    $('#id_subcliente').empty();
                    $('#id_subcliente').append('<option value="">Seleccionar subcliente</option>');
                }
            }

            $('#id_cliente').change(function() {
                var clienteId = $(this).val();
                loadSubclientes(clienteId);
            });

            // Load subclientes on page load
            var initialClienteId = $('#id_cliente').val();
            var initialSubclienteId = '{{ $cotizacion->id_subcliente }}';
            loadSubclientes(initialClienteId, initialSubclienteId);
        });
    </script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const cantidadSobrepesoInput = document.getElementById('cantidad_sobrepeso_proveedor');
            const valorSobrepesoInput = document.getElementById('sobrepeso_proveedor');
            const sobrepesoProbInput = document.getElementById('total_tonelada');

            const precioInput = document.getElementById('precio_proveedor');
            const burreoInput = document.getElementById('burreo_proveedor');
            const maniobraInput = document.getElementById('maniobra_proveedor');
            const estadiaInput = document.getElementById('estadia_proveedor');
            const otroInput = document.getElementById('otro_proveedor');
            const otro2Input = document.getElementById('otro2');
            const otro3Input = document.getElementById('otro3');
            const otro4Input = document.getElementById('otro4');
            const otro5Input = document.getElementById('otro5');
            const otro6Input = document.getElementById('otro6');
            const otro7Input = document.getElementById('otro7');
            const otro8Input = document.getElementById('otro8');
            const otro9Input = document.getElementById('otro9');
            const ivaInput = document.getElementById('iva_proveedor');
            const retencionInput = document.getElementById('retencion_proveedor');
            const totalInput = document.getElementById('total_proveedor');

            // Función para actualizar el total
            function updateTotal() {
                let precio = parseFloat(precioInput.value) || 0;
                let burreo = parseFloat(burreoInput.value) || 0;
                let maniobra = parseFloat(maniobraInput.value) || 0;
                let estadia = parseFloat(estadiaInput.value) || 0;
                let sobrepesoProb = parseFloat(sobrepesoProbInput.value) || 0;
                let otro = parseFloat(otroInput.value) || 0;
                let otro2 = parseFloat(otro2Input.value) || 0;
                let otro3 = parseFloat(otro3Input.value) || 0;
                let otro4 = parseFloat(otro4Input.value) || 0;
                let otro5 = parseFloat(otro5Input.value) || 0;
                let otro6 = parseFloat(otro6Input.value) || 0;
                let otro7 = parseFloat(otro7Input.value) || 0;
                let otro8 = parseFloat(otro8Input.value) || 0;
                let otro9 = parseFloat(otro9Input.value) || 0;
                let iva = parseFloat(ivaInput.value) || 0;
                let retencion = parseFloat(retencionInput.value) || 0;

                // Sumar todos menos retencion
                let subtotal = precio + burreo + maniobra + estadia + otro +
                            otro2 + otro3 + otro4 + otro5 + otro6 +
                            otro7 + otro8 + otro9 + iva + sobrepesoProb;

                console.log(`Subtotal: ${subtotal}`);
                // Restar retencion
                let total = subtotal - retencion;
                console.log(`Total: ${total}`);

                // Actualizar el input de total
                totalInput.value = total.toFixed(2);
            }

            // Función para actualizar el resultado y el total
            function updateResultado() {
                const cantidadSobrepeso = parseFloat(cantidadSobrepesoInput.value) || 0;
                const valorSobrepeso = parseFloat(valorSobrepesoInput.value) || 0;

                console.log(`Cantidad sobrepeso: ${cantidadSobrepeso}, Valor sobrepeso: ${valorSobrepeso}`);

                // Multiplicar los valores
                const resultado = cantidadSobrepeso * valorSobrepeso;

                console.log(`Resultado sobrepeso: ${resultado}`);

                // Colocar el resultado en el input correspondiente
                sobrepesoProbInput.value = resultado.toFixed(2); // Redondear a dos decimales

                // Actualizar el total
                updateTotal();
            }

            // Asignar evento de input a todos los inputs relevantes
            const allInputs = [
                precioInput, burreoInput, maniobraInput, estadiaInput, otroInput,
                otro2Input, otro3Input, otro4Input, otro5Input, otro6Input,
                otro7Input, otro8Input, otro9Input, ivaInput, retencionInput,
                valorSobrepesoInput, cantidadSobrepesoInput
            ];

            allInputs.forEach(input => {
                input.addEventListener('input', () => {
                    updateResultado();
                    updateTotal();
                });
            });

            // Calcular el resultado y el total iniciales
            updateResultado();

                // Función para calcular base2_proveedor
            function calcularBase2Proveedor() {
                // Obtener los valores de los inputs
                const totalProveedor = parseFloat(document.getElementById('total_proveedor').value) || 0;
                const base1Proveedor = parseFloat(document.getElementById('base1_proveedor').value) || 0;
                const ivaProveedor = parseFloat(document.getElementById('iva_proveedor').value) || 0;
                const retencionProveedor = parseFloat(document.getElementById('retencion_proveedor').value) || 0;

                // Realizar el cálculo
                const base2Proveedor = (totalProveedor - base1Proveedor - ivaProveedor) + retencionProveedor;

                // Mostrar el resultado en el input de base2_proveedor
                document.getElementById('base2_proveedor').value = base2Proveedor.toFixed(2);
            }

            // Agregar eventos de cambio a los inputs para calcular automáticamente
            document.getElementById('total_proveedor').addEventListener('input', calcularBase2Proveedor);
            document.getElementById('base1_proveedor').addEventListener('input', calcularBase2Proveedor);
            document.getElementById('iva_proveedor').addEventListener('input', calcularBase2Proveedor);
            document.getElementById('retencion_proveedor').addEventListener('input', calcularBase2Proveedor);
        });

    </script>

@endsection
