<!DOCTYPE html>
<html>
    <style>
        body {
            font-family: sans-serif;
        }

        @page {
            margin: 160px 50px;
        }

        header {
            position: fixed;
            left: 0px;
            top: -160px;
            right: 0px;
            height: 100px;
            background-color: #47a0cd;
            color: #fff;
            text-align: center;
        }

        header h1 {
            margin: 10px 0;
        }

        header h2 {
            margin: 0 0 10px 0;
        }

        footer {
            position: fixed;
            left: 0px;
            bottom: -50px;
            right: 0px;
            height: 40px;
            border-bottom: 2px solid #47a0cd;
        }

        footer .page:after {
            content: counter(page);
        }

        footer table {
            width: 100%;
        }

        footer p {
            text-align: right;
        }

        footer .izq {
            text-align: left;
        }

        table {
            font-family: arial, sans-serif;
            border-collapse: collapse;
            width: 100%;
            border-radius: 6px;
        }

        td,
        th {
            text-align: center;
            padding: 8px;
        }

        tr:nth-child(even) {
            background-color: #47a0cd;
        }
    </style>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    <body>
        <header>
            <img alt="Bootstrap Image Preview" src="{{ asset('img/logo.jpg') }}" style="width: 25%" />
        </header>

        <footer>
            <table>
                <tr>
                    <td>
                        <p class="izq">Fecha: {{ $fechaCarbon->format('d \d\e F \d\e\l Y') }}</p>
                    </td>
                    <td>
                        <p class="page">Página</p>
                    </td>
                </tr>
            </table>
        </footer>

        <div id="content">
            <div class="row mt-5">
                <div class="col-md-12">
                    <table
                        id="ejemplo"
                        class="table text-white tabla-completa"
                        style="color: #000; width: 100%; padding: 30px"
                    >
                        <thead class="tabla-azul" style="padding: 100px">
                            <tr class="tr" style="background-color: #47a0cd; height: 40px; color: #ffffff">
                                <th>Cliente</th>
                                <th>Subcliente</th>
                                <th>Empresa</th>
                                <th>No. Contenedor</th>
                                <th>Destino</th>
                                <th>Precio x ton sobrepeso</th>
                                <th>Precio viaje</th>
                                <th>Burreo</th>
                                <th>Maniobra</th>
                                <th>Estadía</th>
                                <th>Otros</th>
                                <th>IVA</th>
                                <th>Retención</th>
                                <th>Total</th>
                            </tr>
                        </thead>

                        <tbody style="text-align: center; font-size: 120%">
                            <td>{{ $catalogo->Cliente->nombre }}</td>
                            <td>
                                @if ($catalogo->id_subcliente == null)
                                    -
                                @else
                                    {{ $catalogo->Subcliente->nombre }}
                                @endif
                            </td>
                            <td>{{ $catalogo->Empresa->nombre }}</td>
                            <td>{{ $catalogo->num_contenedor }}</td>
                            <td>{{ $catalogo->destino }}</td>
                            <td>$ {{ number_format($catalogo->precio_tonelada, 2, '.', ',') }}</td>
                            <td>$ {{ number_format($catalogo->precio_viaje, 2, '.', ',') }}</td>
                            <th>$ {{ number_format($catalogo->burreo, 2, '.', ',') }}</th>
                            <th>$ {{ number_format($catalogo->maniobra, 2, '.', ',') }}</th>
                            <th>$ {{ number_format($catalogo->estadia, 2, '.', ',') }}</th>
                            <th>$ {{ number_format($catalogo->otro, 2, '.', ',') }}</th>
                            <th>$ {{ number_format($catalogo->iva, 2, '.', ',') }}</th>
                            <th>$ {{ number_format($catalogo->retencion, 2, '.', ',') }}</th>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </body>
</html>
