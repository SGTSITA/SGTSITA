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
                        <h3 class="text-center">{{ $operador->nombre }}</h3>
                    </div>

                    <div class="card-body">
                        <table class="table table-flush" id="datatable-search">
                            <thead class="thead">
                                <tr>
                                    <th class="text-center">ID Cotizacion</th>
                                    <th class="text-center">
                                        Num. Contenedor
                                        <img
                                            src="{{ asset('img/icon/user_predeterminado.webp') }}"
                                            alt=""
                                            width="25px"
                                        />
                                    </th>
                                    <th class="text-center">
                                        Destino
                                        <img src="{{ asset('img/icon/fuente.webp') }}" alt="" width="25px" />
                                    </th>
                                    <th class="text-center">
                                        Acciones
                                        <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px" />
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="text-center">
                                @foreach ($pagos as $item)
                                    <tr>
                                        <td>
                                            <a
                                                type="button"
                                                class="btn"
                                                target="_blank"
                                                href="{{ route('edit.cotizaciones', $item->Contenedor->Cotizacion->id) }}"
                                            >
                                                {{ $item->Contenedor->Cotizacion->id }}
                                            </a>
                                        </td>
                                        <td>{{ $item->Contenedor->Cotizacion->DocCotizacion->num_contenedor }}</td>
                                        <td>{{ $item->Contenedor->Cotizacion->destino }}</td>
                                        <td>
                                            <a
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#operadoresModal_detalles{{ $item->id }}"
                                            >
                                                <img src="{{ asset('img/icon/editar.webp') }}" alt="" width="25px" />
                                            </a>
                                        </td>
                                    </tr>
                                    @include('operadores.modal_detalles')
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
    <script type="text/javascript">
        const dataTableSearch = new simpleDatatables.DataTable('#datatable-search', {
            searchable: true,
            fixedHeight: false,
        });
    </script>
@endsection
