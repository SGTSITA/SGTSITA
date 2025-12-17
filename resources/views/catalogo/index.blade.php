@extends('layouts.app')

@section('template_title')
    Catalogo
@endsection

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center">
                            <span id="card_title">Catalogo</span>

                            <div class="float-right">
                                @can('catalogo-crear')
                                    <a
                                        type="button"
                                        class="btn btn-primary"
                                        href="{{ route('create.catalogo') }}"
                                        style="background: {{ $configuracion->color_boton_add }}; color: #ffff"
                                    >
                                        Crear
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-flush" id="datatable-planeadas">
                            <thead class="thead">
                                <tr>
                                    <th>No</th>
                                    <th>
                                        <img
                                            src="{{ asset('img/icon/user_predeterminado.webp') }}"
                                            alt=""
                                            width="25px"
                                        />
                                        Cliente
                                    </th>
                                    <th>
                                        <img src="{{ asset('img/icon/gps.webp') }}" alt="" width="25px" />
                                        Total
                                    </th>
                                    <th>
                                        <img src="{{ asset('img/icon/origen.png') }}" alt="" width="25px" />
                                        Destino
                                    </th>
                                    <th>
                                        <img src="{{ asset('img/icon/contenedor.png') }}" alt="" width="25px" />
                                        # Contenedor
                                    </th>
                                    <th>
                                        <img src="{{ asset('img/icon/edit.png') }}" alt="" width="25px" />
                                        Acciones
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($catalogos as $catalogo)
                                    <tr>
                                        <td>{{ $catalogo->id }}</td>
                                        <td>{{ $catalogo->Cliente->nombre }}</td>
                                        <td>{{ $catalogo->total }}</td>
                                        <td>{{ $catalogo->destino }}</td>
                                        <td>{{ $catalogo->num_contenedor }}</td>
                                        <td>
                                            {{--
                                                @can('cotizaciones-edit')
                                                <a type="button" class="btn btn-xs" href="{{ route('edit.cotizaciones', $cotizacion->id) }}">
                                                <img src="{{ asset('img/icon/quotes.webp') }}" alt="" width="25px">
                                                </a>
                                                @endcan
                                            --}}

                                            <a
                                                type="button"
                                                class="btn btn-xs"
                                                href="{{ route('pdf.catalogo', $catalogo->id) }}"
                                            >
                                                <img src="{{ asset('img/icon/pdf.webp') }}" alt="" width="25px" />
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
@endsection

@section('datatable')
    <script>
        const dataTableSearch4 = new simpleDatatables.DataTable('#datatable-planeadas', {
            searchable: true,
            fixedHeight: false,
        });
    </script>
@endsection
