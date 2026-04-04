@extends('layouts.app')

@section('template_title')
    Cuentas Cobrar
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center">
                            <h2 id="card_title">Cuentas Cobrar</h2>

                            <div class="float-right"></div>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover table_id" id="datatable-search">
                                <thead class="thead">
                                    <tr>
                                        <th>No</th>

                                        <th>
                                            <img
                                                src="{{ asset('img/icon/user_predeterminado.webp') }}"
                                                alt=""
                                                width="25px"
                                            />
                                            Nombre
                                        </th>
                                        <th>
                                            <img src="{{ asset('img/icon/phone.webp') }}" alt="" width="25px" />
                                            Telefono
                                        </th>
                                        <th>
                                            <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px" />
                                            # Total de viajes
                                        </th>
                                        <th>
                                            <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px" />
                                            Importe
                                        </th>
                                        <th>
                                            <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px" />
                                            Acciones
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cotizacionesPorCliente as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>

                                            <td>{{ $item->Cliente->nombre }}</td>
                                            <td>{{ $item->Cliente->telefono }}</td>
                                            <td>{{ $item->total_cotizaciones }}</td>
                                            <td>${{ number_format($item->total_restante, 0, '.', ',') }}</td>
                                            <td>
                                                <a
                                                    class="btn btn-xs btn-success"
                                                    href="{{ route('show.cobrar', $item->id_cliente) }}"
                                                >
                                                    <i class="fa fa-fw fa-edit"></i>
                                                    Ver
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
        const dataTableSearch = new simpleDatatables.DataTable('#datatable-search', {
            searchable: true,
            fixedHeight: false,
        });
    </script>
@endsection
