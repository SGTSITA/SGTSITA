@extends('layouts.app')

@section('template_title')
Liquidaciones
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center;">
                            <h2 id="card_title">
                                Liquidaciones
                            </h2>

                             <div class="float-right">

                              </div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table_id text-center" id="datatable-search">
                                <thead class="thead">
                                    <tr>
										<th><img src="{{ asset('img/icon/user_predeterminado.webp') }}" alt="" width="25px"> Nombre</th>
										<th><img src="{{ asset('img/icon/phone.webp') }}" alt="" width="25px"> Telefono</th>
										<th><img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px"> Viajes realizados</th>
                                        <th><img src="{{ asset('img/icon/bolsa-de-dinero.webp') }}" alt="" width="25px"> Total a pagar</th>
                                        <th><img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px"> Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($asignacion_operador as $item)
                                        <tr>
											<td>{{ $item->Operador->nombre }}</td>
											<td>{{ $item->Operador->telefono }}</td>
											<td>{{ $item->total_cotizaciones  }}</td>
                                            <td>$ {{ number_format($item->total_pago, 2, '.', ',') }}</td>
                                            <td>
                                                <a class="btn btn-xs btn-success" href="{{ route('show.liquidacion', $item->id_operador) }}">
                                                    <i class="fa fa-fw fa-edit"></i> Ver
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@include('client.modal_create')
@endsection

@section('datatable')

<script>
    const dataTableSearch = new simpleDatatables.DataTable("#datatable-search", {
      searchable: true,
      fixedHeight: false
    });
</script>

@endsection
