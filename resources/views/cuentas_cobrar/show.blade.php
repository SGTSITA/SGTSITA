@extends('layouts.app')

@section('template_title')
   Ver cuentas
@endsection

@section('content')

    <div class="contaboleta_liberacionr-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <a class="btn"  href="{{ route('index.cobrar') }}" style="background: {{$configuracion->color_boton_close}}; color: #ffff;margin-right: 3rem;">
                                <img src="{{ asset('img/icon/izquierda_white.png') }}" alt="" width="25px"> Regresar
                            </a>
                        </div>
                        <h3 class="text-center">{{$cliente->nombre}}</h3>

                        <a class="btn btn_filter" data-bs-toggle="collapse" href="#collapseFilter" role="button" aria-expanded="false" aria-controls="collapseFilter">
                            <img class="icon_search" src="{{ asset('img/icon/depositar.png') }}" alt="" width="25px"> Cobrar varios
                        </a>

                        <h5># Total de viajes: <b>{{$cotizacion->total_cotizaciones}}</b></h5>
                        <h5>Importe: <b>${{ number_format($cotizacion->total_restante, 0, '.', ',') }}</b></h5>

                        <div class="collapse container_filter " id="collapseFilter" style="background: #ffffff;">
                            <form method="POST" action="{{ route('update_varios.cobrar') }}" enctype="multipart/form-data" role="form">
                                @csrf
                                <div class="row">
                                    <input type="hidden" id="id_cliente" name="id_cliente" value="{{ $cliente->id }}">
                                    <div class="col-3" style="    display: grid;">
                                        <select class="form-control cotizaciones" name="id_cotizacion[]" id="id_cotizacion" multiple>
                                            @foreach($cotizacionesPorPagar as $item)
                                                <option value="{{ $item->id }}" data-total="{{ $item->restante }}" data-numcontenedor="{{ $item->DocCotizacion->num_contenedor }}">
                                                    {{ $item->DocCotizacion->num_contenedor }} / ${{ number_format($item->restante, 2, '.', ',') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Total a cobrar</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                </span>
                                                <input class="form-control" type="text" name="total_sum" id="total_sum" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Resta</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                </span>
                                                <input class="form-control" type="text" name="remaining_total" id="remaining_total" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="modal-title mt-3">Metodo de pago 1</h5>
                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Monto de pago 1 *</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                </span>
                                                <input type="float" id="monto1_varios" name="monto1_varios" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Metodo de pago 1 *</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                                </span>
                                                <select class="form-select cliente d-inline-block"  data-toggle="select" id="metodo_pago1_varios" name="metodo_pago1_varios" value="{{ old('metodo_pago1') }}">
                                                    <option value="">Seleccionar Metodo</option>
                                                    <option value="Tarjeta C/D">Tarjeta C/D</option>
                                                    <option value="Transferencia">Transferencia</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Banco</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                                </span>
                                                <select class="form-select cliente d-inline-block"  data-toggle="select" id="id_banco1_varios" name="id_banco1_varios" value="{{ old('id_banco1') }}">
                                                    <option value="">Selecciona</option>
                                                    @foreach ($bancos as $item)
                                                        <option value="{{$item->id}}">{{$item->nombre_banco}} - ${{$item->saldo}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Comprobante de pago 1</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/validando-billete.webp') }}" alt="" width="25px">
                                                </span>
                                                <input type="file" id="comprobante1_varios" name="comprobante1_varios" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <h5 class="modal-title mt-3">Metodo de pago 2</h5>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Monto de pago 2</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                </span>
                                                <input type="float" id="monto2_varios" name="monto2_varios" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Metodo de pago 2</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                                </span>
                                                <select class="form-select cliente d-inline-block"  data-toggle="select" id="metodo_pago2_varios" name="metodo_pago2_varios" value="{{ old('metodo_pago2') }}">
                                                    <option value="">Seleccionar Metodo</option>
                                                    <option value="Tarjeta C/D">Tarjeta C/D</option>
                                                    <option value="Transferencia">Transferencia</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Banco 2</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/metodo-de-pago.webp') }}" alt="" width="25px">
                                                </span>
                                                <select class="form-select cliente d-inline-block"  data-toggle="select" id="id_banco2_varios" name="id_banco2_varios" value="{{ old('id_banco2') }}">
                                                    <option value="">Selecciona</option>
                                                    @foreach ($bancos as $item)
                                                        <option value="{{$item->id}}">{{$item->nombre_banco}} - ${{$item->saldo}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Comprobante de pago 2</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/validando-billete.webp') }}" alt="" width="25px">
                                                </span>
                                                <input type="file" id="comprobante2_varios" name="comprobante2_varios" class="form-control">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-3">
                                        <div class="form-group">
                                            <label for="name">Total abonos</label>
                                            <div class="input-group mb-3">
                                                <span class="input-group-text" id="basic-addon1">
                                                    <img src="{{ asset('img/icon/monedas.webp') }}" alt="" width="25px">
                                                </span>
                                                <input class="form-control" type="text" name="remaining_abonos" id="remaining_abonos" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div id="cotizacion_inputs"></div>

                                    <div class="col-3">
                                        <br>
                                        <button class="btn " type="submit" style="background-color: #babd24; color: #ffffff;">Cobrar</button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    <div class="card-body">
                        <table class="table table-striped table-hover table_id" id="datatable-search">
                            <thead class="thead">
                                <tr>
                                    <th><img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px"># Contenedor</th>
                                    <th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px">Subcliente</th>
                                    <th><img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="25px">Total a pagar</th>
                                    <th><img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px">Tipo de viaje</th>
                                    <th><img src="{{ asset('img/icon/semaforos.webp') }}" alt="" width="25px">Estatus</th>
                                    <th><img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px">Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($cotizacionesPorPagar as $item)
                                    <tr>
                                        <td>{{ $item->DocCotizacion->num_contenedor }}</td>
                                        <td>
                                            @if ($item->id_subcliente != NULL)
                                                {{$item->Subcliente->nombre}} / {{$item->Subcliente->telefono}}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>$ {{ number_format($item->restante, 2, '.', ',') }}</td>
                                        <td>
                                            @if ($item->tipo_viaje == NULL || $item->tipo_viaje == 'Seleccionar Opcion')
                                                Subcontratado
                                            @else
                                                {{ $item->tipo_viaje }}
                                            @endif
                                        </td>
                                        <td>
                                            @if ($item->estatus == 'Aprobada')
                                                En curso
                                            @else
                                                {{ $item->estatus }}
                                            @endif
                                        </td>
                                        <td>
                                            <a class="btn btn-xs btn-success" data-bs-toggle="modal" data-bs-target="#cobrarModal{{ $item->id }}">
                                                <i class="fa fa-fw fa-edit"></i> Cobrar
                                            </a>
                                        </td>
                                    </tr>
                                    @include('cuentas_cobrar.pago')
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection


@section('datatable')
<script src="{{ asset('assets/admin/vendor/jquery/dist/jquery.min.js')}}"></script>
<script src="{{ asset('assets/admin/vendor/select2/dist/js/select2.min.js')}}"></script>
<script>
    const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
      searchable: true,
      fixedHeight: false
    });

    $(document).ready(function() {
        $('.cotizaciones').select2();
    });

    $(document).ready(function() {
        function updateRemainingTotal() {
            const totalSum = parseFloat($('#total_sum').val()) || 0;
            const monto1 = parseFloat($('#monto1_varios').val()) || 0;
            const monto2 = parseFloat($('#monto2_varios').val()) || 0;
            const remainingTotal = totalSum - (monto1 + monto2);

            $('#remaining_total').val(remainingTotal.toFixed(2));
        }

        function updateRemainingAbonos() {
            const monto1 = parseFloat($('#monto1_varios').val()) || 0;
            const monto2 = parseFloat($('#monto2_varios').val()) || 0;
            const totalAbonos = monto1 + monto2;

            let abonos = 0;

            $('#cotizacion_inputs input[data-type="abono"]').each(function() {
                abonos += parseFloat($(this).val()) || 0;
            });

            const remainingAbonos = totalAbonos - abonos;

            $('#total_abonos').val(totalAbonos.toFixed(2));
            $('#remaining_abonos').val(remainingAbonos.toFixed(2));
        }

        $('#id_cotizacion').on('change', function() {
            let totalSum = 0;
            $('#cotizacion_inputs').empty();

            $('#id_cotizacion option:selected').each(function() {
                const total = parseFloat($(this).data('total')) || 0;
                const id = $(this).val();
                const numContenedor = $(this).data('numcontenedor');
                totalSum += total;

                $('#cotizacion_inputs').append(`
                    <div col-4>
                        <label for="abono_${id}">Abono para ${numContenedor}:</label>
                        <input class="form-control" type="number" data-type="abono" name="abono[${id}]" id="abono_${id}" data-total="${total}" step="0.01" placeholder="Abono para ${numContenedor}">
                    </div>
                `);
            });

            $('#total_sum').val(totalSum.toFixed(2));
            updateRemainingTotal();
            updateRemainingAbonos();
        });

        $('#cotizacion_inputs').on('input', 'input[data-type="abono"]', function() {
            updateRemainingAbonos();
        });

        $('#monto1_varios, #monto2_varios').on('input', function() {
            updateRemainingTotal();
            updateRemainingAbonos();
        });
    });
</script>

@endsection
