@extends('layouts.app')

@section('template_title')
    Operador
@endsection

@section('content')
    <style>
        .estilos_equipo {
            background: #f5f5f5;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 6px 6px 15px -10px rgb(0 0 0 / 50%);
        }

        .titulos_bitacora {
            font-size: 12px;
        }
    </style>
    <div class="container-fluid">
        <div class="row">
            <div class="col-sm-12">
                <div class="card">
                    <div class="card-header">
                        <div style="display: flex; justify-content: space-between; align-items: center">
                            <span id="card_title">Pagos Pendientes</span>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="table-responsive">
                            <h3 class="text-center">{{ $operador->nombre }}</h3>
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
                                    @foreach ($pagos_pendientes as $item)
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
                                            <td>
                                                {{ $item->Contenedor->Cotizacion->DocCotizacion->num_contenedor }}
                                            </td>
                                            <td>{{ $item->Contenedor->Cotizacion->destino }}</td>
                                            <td>
                                                <a
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#operadoresModal_bitacora{{ $item->id }}"
                                                >
                                                    <img
                                                        src="{{ asset('img/icon/editar.webp') }}"
                                                        alt=""
                                                        width="25px"
                                                    />
                                                </a>
                                            </td>
                                        </tr>
                                        @include('operadores.modal_bitacora')
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
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
    <script>
        // Escuchar eventos de entrada en los campos de gasolina, casetas, otros y sueldo_viaje
        document.addEventListener('input', function (event) {
            if (event.target.matches('[id^="gasolina_"], [id^="casetas_"], [id^="otros_"], [id^="sueldo_viaje_"]')) {
                var itemId = event.target.id.split('_')[1];
                var gasolina = parseFloat(document.getElementById('gasolina_' + itemId).value) || 0;
                var casetas = parseFloat(document.getElementById('casetas_' + itemId).value) || 0;
                var otros = parseFloat(document.getElementById('otros_' + itemId).value) || 0;
                var sueldo_viaje = parseFloat(document.getElementById('sueldo_viaje_' + itemId).value) || 0;

                var gastos = sueldo_viaje + gasolina + casetas + otros;
                document.getElementById('gastos_' + itemId).value = gastos.toFixed(2);

                var dinero_viaje = parseFloat(document.getElementById('dinero_viaje_' + itemId).value);
                var resta = dinero_viaje - gastos;
                document.getElementById('resta_' + itemId).value = resta.toFixed(2);

                var label = document.getElementById('faltanteRestanteLabel_' + itemId);
                if (resta >= 0) {
                    label.textContent = 'Restante';
                } else {
                    label.textContent = 'Faltante';
                }
            }
        });

        document.addEventListener('DOMContentLoaded', function () {
            var agregarCampoBtn2 = document.getElementById('agregarCampo2');
            var camposContainer2 = document.getElementById('camposContainer2');
            var campoExistente2 = camposContainer2.querySelector('.campo2');

            agregarCampoBtn2.addEventListener('click', function () {
                var nuevoCampo2 = campoExistente2.cloneNode(true);
                camposContainer2.appendChild(nuevoCampo2);

                // Limpiar los valores en el nuevo campo
                nuevoCampo2.querySelector('.gasolina').value = '';
                nuevoCampo2.querySelector('.casetas').value = '';
                nuevoCampo2.querySelector('.otros').value = '';
            });
        });
    </script>
@endsection
